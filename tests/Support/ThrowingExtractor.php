<?php

namespace Tests\Support;

use App\Services\Onboarding\Extraction\ExtractorInterface;
use App\Services\Onboarding\Extraction\StubExtractor;
use Illuminate\Support\Collection;

/**
 * Test-only extractor for the "one file fails" drill — reports a failure
 * for a file whose original_filename is the sentinel '__throw__', delegates
 * to the real StubExtractor for everything else so the rest of a batch
 * behaves exactly as it would in production. Mirrors how GeminiExtractionService
 * itself represents a single bad item within an otherwise-successful batch
 * response, rather than literally throwing.
 */
class ThrowingExtractor implements ExtractorInterface
{
    public function extractBatch(Collection $files): array
    {
        $sentinel = $files->filter(fn ($file) => $file->original_filename === '__throw__');
        $rest = $files->diff($sentinel);

        $results = [];
        foreach ($sentinel as $file) {
            $results[$file->id] = ['ok' => false, 'error' => 'Simulated extraction failure for test coverage.'];
        }

        return $results + (new StubExtractor())->extractBatch($rest);
    }
}
