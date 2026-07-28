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

/**
 * Runs the configured extractor (config('onboarding.extractor') — a
 * deterministic stub until Phase 4 swaps in Gemini) over every file in one
 * claimed batch. Each file is wrapped in its own try/catch: one bad file
 * must not sink the rest of its batch or block the draft's finalize check.
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

        foreach ($files as $file) {
            $this->extractOne($extractor, $file);
        }

        FinalizeExtractionJob::dispatch($this->draftId);
    }

    private function extractOne(ExtractorInterface $extractor, OnboardingFile $file): void
    {
        try {
            $extracted = $extractor->extract($file);

            $file->status = 'extracted';
            $file->alt_text = $extracted['alt_text'] ?? $file->alt_text;
            $file->ai_labels = $extracted['labels'] ?? [];
            $file->ai_result = $extracted['result'] ?? [];
            $file->extracted_at = now();
            $file->save();

            OnboardingEvent::create([
                'draft_id' => $file->draft_id,
                'tenant_id' => $file->tenant_id,
                'type' => 'file_extracted',
                'message' => $file->original_filename,
                'payload' => ['file_id' => $file->id, 'batch_id' => $this->batchId],
            ]);
        } catch (\Throwable $e) {
            $file->status = 'failed';
            $file->error_message = $e->getMessage();
            $file->save();

            OnboardingEvent::create([
                'draft_id' => $file->draft_id,
                'tenant_id' => $file->tenant_id,
                'type' => 'file_extraction_failed',
                'message' => $file->original_filename,
                'payload' => ['file_id' => $file->id, 'batch_id' => $this->batchId, 'error' => $e->getMessage()],
            ]);
        }
    }
}
