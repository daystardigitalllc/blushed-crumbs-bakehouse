<?php

namespace App\Services\Onboarding\Extraction;

use App\Models\Onboarding\AiExtractionCache;
use App\Models\Onboarding\OnboardingFile;
use App\Services\Onboarding\TenantMediaPath;
use Illuminate\Support\Collection;

/**
 * The real extractor. Never throws and never leaves a file unresolved —
 * every file in the batch gets a result, falling back to a local-only one
 * (kept, not blocked) whenever: no API key is configured, the draft's AI
 * image budget is spent, a file is too large for one request, or Gemini's
 * response can't be repaired into valid JSON after one retry.
 *
 * Caches by content hash (ai_extraction_cache) before spending any budget,
 * so re-uploading the same photo — or re-running a batch after a partial
 * failure — never pays for the same image twice.
 */
class GeminiExtractionService implements ExtractorInterface
{
    private const TASK_IMAGE = 'extract_image';
    private const TASK_PDF = 'extract_pdf';

    public function __construct(private GeminiClient $client)
    {
    }

    public function extractBatch(Collection $files): array
    {
        if ($files->isEmpty()) {
            return [];
        }

        return $files->first()->kind === 'pdf'
            ? $this->extractPdfBatch($files)
            : $this->extractImageBatch($files);
    }

    // ─── Images ───

    private function extractImageBatch(Collection $files): array
    {
        $results = [];
        $toCall = collect();

        foreach ($files as $file) {
            $cached = $this->cacheLookup($file, self::TASK_IMAGE);
            if ($cached !== null) {
                $results[$file->id] = $cached;

                continue;
            }

            if ($this->overAiBudget($file)) {
                $results[$file->id] = $this->localOnlyResult($file, 'AI image budget for this draft reached — imported with local score only.');

                continue;
            }

            $toCall->push($file);
        }

        if ($toCall->isEmpty()) {
            return $results;
        }

        if (!$this->client->hasApiKey()) {
            foreach ($toCall as $file) {
                $results[$file->id] = $this->localOnlyResult($file, 'Gemini API key not configured — imported with local score only.');
            }

            return $results;
        }

        $parts = [];
        $ordered = [];
        $budgetBytes = (int) config('onboarding.ai_max_request_bytes', 18 * 1024 * 1024);
        $usedBytes = 0;

        foreach ($toCall as $file) {
            $imagePath = $this->aiDerivativePath($file);
            $bytes = ($imagePath && is_file($imagePath)) ? @filesize($imagePath) : false;

            if ($bytes === false || ($usedBytes + $bytes) > $budgetBytes) {
                $results[$file->id] = $this->localOnlyResult($file, 'Skipped the Gemini call — missing derivative or would exceed the per-request size cap.');

                continue;
            }

            $parts[] = ['inline_data' => ['mime_type' => 'image/jpeg', 'data' => base64_encode(file_get_contents($imagePath))]];
            $ordered[] = $file;
            $usedBytes += $bytes;
        }

        if (empty($ordered)) {
            return $results;
        }

        $decoded = $this->callGemini(
            [['role' => 'user', 'parts' => $parts]],
            $this->imageSystemInstruction(),
            $this->imageResponseSchema(),
            count($ordered)
        );

        if ($decoded === null) {
            foreach ($ordered as $file) {
                $results[$file->id] = $this->localOnlyResult($file, "Gemini response couldn't be parsed into valid JSON after a repair attempt — imported with local score only.");
            }

            return $results;
        }

        foreach ($ordered as $i => $file) {
            $entry = $decoded[$i] ?? null;

            $results[$file->id] = $entry
                ? $this->successResult($file, self::TASK_IMAGE, [
                    'alt_text' => $entry['alt_text'] ?? null,
                    'labels' => array_values(array_filter(array_merge(
                        [$entry['content_type'] ?? null],
                        $entry['labels'] ?? []
                    ))),
                    'result' => [
                        'source' => 'gemini',
                        'content_type' => $entry['content_type'] ?? null,
                        'product_name' => $entry['product_name'] ?? null,
                        'price' => $entry['price'] ?? null,
                    ],
                ])
                : $this->localOnlyResult($file, 'Gemini returned fewer results than images sent — this one fell through.');
        }

        return $results;
    }

    // ─── PDFs — one per batch (see DispatchPendingExtractionsJob's batch size) ───

    private function extractPdfBatch(Collection $files): array
    {
        $file = $files->first();

        $cached = $this->cacheLookup($file, self::TASK_PDF);
        if ($cached !== null) {
            return [$file->id => $cached];
        }

        if (!$this->client->hasApiKey()) {
            return [$file->id => $this->localOnlyResult($file, 'Gemini API key not configured — imported with local score only.')];
        }

        $budgetBytes = (int) config('onboarding.ai_max_request_bytes', 18 * 1024 * 1024);
        $size = is_file($file->path) ? @filesize($file->path) : false;

        if ($size === false || $size > $budgetBytes) {
            return [$file->id => $this->localOnlyResult($file, 'PDF too large for a single Gemini request — imported with local score only.')];
        }

        $parts = [['inline_data' => ['mime_type' => 'application/pdf', 'data' => base64_encode(file_get_contents($file->path))]]];

        $decoded = $this->callGemini(
            [['role' => 'user', 'parts' => $parts]],
            $this->pdfSystemInstruction(),
            $this->pdfResponseSchema(),
            null
        );

        if ($decoded === null) {
            return [$file->id => $this->localOnlyResult($file, "Gemini response couldn't be parsed into valid JSON after a repair attempt — imported with local score only.")];
        }

        return [$file->id => $this->successResult($file, self::TASK_PDF, [
            'alt_text' => $file->original_filename,
            'labels' => ['menu'],
            'result' => ['source' => 'gemini', 'items' => $decoded],
        ])];
    }

    // ─── Gemini call + one repair retry ───

    /**
     * @return array|null decoded JSON array, or null if unrecoverable after one repair attempt
     */
    private function callGemini(array $contents, string $systemInstruction, array $schema, ?int $expectedCount): ?array
    {
        return $this->client->generateJsonWithRepair($contents, $systemInstruction, $schema, $expectedCount, temperature: 0.2);
    }

    // ─── Prompts / schemas ───

    private function imageSystemInstruction(): string
    {
        return 'You are analyzing photos uploaded by a bakery owner during website onboarding. For each image, '
            . 'in the exact order given, classify it and write a short, natural, accessibility-friendly alt text. '
            . 'If the image shows a specific baked good with a visible price (e.g. a menu board or price tag), '
            . 'extract the product name and price. Never invent a price you cannot see. Return one JSON object '
            . 'per image, in input order.';
    }

    private function imageResponseSchema(): array
    {
        return [
            'type' => 'ARRAY',
            'items' => [
                'type' => 'OBJECT',
                'properties' => [
                    'content_type' => ['type' => 'STRING', 'enum' => ['product', 'storefront', 'ambiance', 'menu_or_price_list', 'other']],
                    'alt_text' => ['type' => 'STRING'],
                    'labels' => ['type' => 'ARRAY', 'items' => ['type' => 'STRING']],
                    'product_name' => ['type' => 'STRING', 'nullable' => true],
                    'price' => ['type' => 'NUMBER', 'nullable' => true],
                ],
                'required' => ['content_type', 'alt_text', 'labels'],
            ],
        ];
    }

    private function pdfSystemInstruction(): string
    {
        return 'You are reading a bakery menu or price list PDF uploaded during website onboarding. Extract every '
            . 'distinct item as a product: name, a short description if present, and its price (as '
            . 'price_min/price_max — use the same value for both if there is a single price; e.g. "$45-60" '
            . 'becomes 45 and 60). Skip section headers and anything that is not an individually purchasable '
            . 'item. Never invent a price you cannot see.';
    }

    private function pdfResponseSchema(): array
    {
        return [
            'type' => 'ARRAY',
            'items' => [
                'type' => 'OBJECT',
                'properties' => [
                    'name' => ['type' => 'STRING'],
                    'description' => ['type' => 'STRING', 'nullable' => true],
                    'price_min' => ['type' => 'NUMBER', 'nullable' => true],
                    'price_max' => ['type' => 'NUMBER', 'nullable' => true],
                    'category' => ['type' => 'STRING', 'nullable' => true],
                ],
                'required' => ['name'],
            ],
        ];
    }

    // ─── Cache, budget, fallback helpers ───

    private function cacheLookup(OnboardingFile $file, string $task): ?array
    {
        $cached = AiExtractionCache::where('cache_key', $this->cacheKey($file, $task))->first();

        return $cached ? $this->successResult($file, $task, $cached->result, fromCache: true) : null;
    }

    private function writeCache(OnboardingFile $file, string $task, array $result): void
    {
        AiExtractionCache::updateOrCreate(
            ['cache_key' => $this->cacheKey($file, $task)],
            [
                'tenant_id' => $file->tenant_id,
                'content_hash' => $file->content_hash,
                'model' => (string) config('services.gemini.extraction_model', 'gemini-3.5-flash'),
                'prompt_version' => (string) config('onboarding.ai_prompt_version', 'v1'),
                'task' => $task,
                'result' => $result,
            ]
        );
    }

    private function cacheKey(OnboardingFile $file, string $task): string
    {
        $model = (string) config('services.gemini.extraction_model', 'gemini-3.5-flash');
        $promptVersion = (string) config('onboarding.ai_prompt_version', 'v1');

        return hash('sha256', "{$file->tenant_id}|{$file->content_hash}|{$model}|{$promptVersion}|{$task}");
    }

    private function overAiBudget(OnboardingFile $file): bool
    {
        $cap = (int) config('onboarding.ai_max_images_per_draft', 50);

        $used = OnboardingFile::where('draft_id', $file->draft_id)
            ->where('ai_result->source', 'gemini')
            ->count();

        return $used >= $cap;
    }

    private function aiDerivativePath(OnboardingFile $file): ?string
    {
        $baseFilename = pathinfo($file->path, PATHINFO_FILENAME);

        return TenantMediaPath::draftDerivativeDir($file->tenant_id, $file->draft_id, 'ai') . "/{$baseFilename}.jpg";
    }

    private function successResult(OnboardingFile $file, string $task, array $result, bool $fromCache = false): array
    {
        if (!$fromCache) {
            $this->writeCache($file, $task, $result);
        }

        return [
            'ok' => true,
            'alt_text' => $result['alt_text'] ?? null,
            'labels' => $result['labels'] ?? [],
            'result' => $result['result'] ?? $result,
        ];
    }

    private function localOnlyResult(OnboardingFile $file, string $reason): array
    {
        $name = pathinfo($file->original_filename ?? 'upload', PATHINFO_FILENAME);
        $name = trim(preg_replace('/[_\-]+/', ' ', $name) ?? '');

        return [
            'ok' => true,
            'alt_text' => $name !== '' ? ucfirst($name) : 'Untitled upload',
            'labels' => [$file->kind ?? 'file'],
            'result' => ['source' => 'local_only', 'reason' => $reason],
        ];
    }
}
