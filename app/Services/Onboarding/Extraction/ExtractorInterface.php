<?php

namespace App\Services\Onboarding\Extraction;

use Illuminate\Support\Collection;

/**
 * The extraction backend ExtractBatchJob calls for one claimed batch (all
 * the same kind — 'image' or 'pdf' — since DispatchPendingExtractionsJob
 * claims per-kind). Swapped via config('onboarding.extractor') — currently
 * GeminiExtractionService, with StubExtractor as the deterministic
 * no-network stand-in tests pin explicitly.
 *
 * Batch-shaped (not per-file) because the real Gemini call genuinely sends
 * up to 6 images in one multimodal request for token efficiency — but each
 * entry in the returned array still isolates one file's outcome, so a
 * validation failure on a single image in the batch doesn't have to fail
 * the other 5.
 */
interface ExtractorInterface
{
    /**
     * @param Collection<int,\App\Models\Onboarding\OnboardingFile> $files
     * @return array<int,array{ok:bool,alt_text?:?string,labels?:array,result?:array,error?:string}> keyed by OnboardingFile id
     */
    public function extractBatch(Collection $files): array;
}
