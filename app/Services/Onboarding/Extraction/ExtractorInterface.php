<?php

namespace App\Services\Onboarding\Extraction;

use App\Models\Onboarding\OnboardingFile;

/**
 * The extraction backend ExtractBatchJob calls per file. Swapped via
 * config('onboarding.extractor') — StubExtractor until Phase 4 points it
 * at a real GeminiExtractionService. ExtractBatchJob never knows which.
 */
interface ExtractorInterface
{
    /**
     * @return array{alt_text:?string,labels:array,result:array}
     */
    public function extract(OnboardingFile $file): array;
}
