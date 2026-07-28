<?php

namespace App\Jobs\Onboarding;

use App\Models\Onboarding\OnboardingEvent;
use App\Models\Onboarding\OnboardingFile;
use App\Services\Onboarding\Extraction\ExtractorInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Runs the configured extractor (config('onboarding.extractor') —
 * GeminiExtractionService, or StubExtractor in tests) over one claimed
 * batch in a single call. The extractor is expected to never throw and to
 * return a result for every file it was given; the try/catch here is a
 * defensive backstop so a bug in a future extractor implementation degrades
 * to "this batch's files go back through the pipeline" rather than crashing
 * the job and leaving them wedged in 'extracting' until the stuck sweep.
 */
class ExtractBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 120;

    public function __construct(public int $draftId, public string $batchId)
    {
        $this->onQueue('ai-import');
    }

    public function handle(): void
    {
        $files = OnboardingFile::where('draft_id', $this->draftId)
            ->where('batch_id', $this->batchId)
            ->where('status', 'extracting')
            ->get();

        if ($files->isEmpty()) {
            // Already swept back to 'pending' by the stuck sweep, or the
            // draft was pruned mid-flight. Still recheck the draft in case
            // this was the last thing blocking it.
            FinalizeExtractionJob::dispatch($this->draftId);

            return;
        }

        $extractor = app(config('onboarding.extractor'));
        if (!$extractor instanceof ExtractorInterface) {
            throw new \RuntimeException('config(onboarding.extractor) must implement ExtractorInterface.');
        }

        try {
            $results = $extractor->extractBatch($files);
        } catch (\Throwable $e) {
            Log::warning('Extractor threw for a whole batch — marking every file in it failed.', [
                'batch_id' => $this->batchId,
                'message' => $e->getMessage(),
            ]);

            $results = [];
            foreach ($files as $file) {
                $results[$file->id] = ['ok' => false, 'error' => $e->getMessage()];
            }
        }

        foreach ($files as $file) {
            $this->applyResult($file, $results[$file->id] ?? ['ok' => false, 'error' => 'No result returned for this file.']);
        }

        FinalizeExtractionJob::dispatch($this->draftId);
    }

    private function applyResult(OnboardingFile $file, array $result): void
    {
        if ($result['ok'] ?? false) {
            $file->status = 'extracted';
            $file->alt_text = $result['alt_text'] ?? $file->alt_text;
            $file->ai_labels = $result['labels'] ?? [];
            $file->ai_result = $result['result'] ?? [];
            $file->extracted_at = now();
            $file->save();

            OnboardingEvent::create([
                'draft_id' => $file->draft_id,
                'tenant_id' => $file->tenant_id,
                'type' => 'file_extracted',
                'message' => $file->original_filename,
                'payload' => ['file_id' => $file->id, 'batch_id' => $this->batchId],
            ]);

            return;
        }

        $file->status = 'failed';
        $file->error_message = $result['error'] ?? 'Extraction failed.';
        $file->save();

        OnboardingEvent::create([
            'draft_id' => $file->draft_id,
            'tenant_id' => $file->tenant_id,
            'type' => 'file_extraction_failed',
            'message' => $file->original_filename,
            'payload' => ['file_id' => $file->id, 'batch_id' => $this->batchId, 'error' => $file->error_message],
        ]);
    }
}
