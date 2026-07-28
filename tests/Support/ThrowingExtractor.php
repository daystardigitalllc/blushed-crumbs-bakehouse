<?php

namespace Tests\Support;

use App\Models\Onboarding\OnboardingFile;
use App\Services\Onboarding\Extraction\ExtractorInterface;
use App\Services\Onboarding\Extraction\StubExtractor;

/**
 * Test-only extractor for the "one file throws" failure drill — throws for
 * a file whose original_filename is the sentinel '__throw__', delegates to
 * the real StubExtractor for everything else so the rest of a batch behaves
 * exactly as it would in production.
 */
class ThrowingExtractor implements ExtractorInterface
{
    public function extract(OnboardingFile $file): array
    {
        if ($file->original_filename === '__throw__') {
            throw new \RuntimeException('Simulated extraction failure for test coverage.');
        }

        return (new StubExtractor())->extract($file);
    }
}
