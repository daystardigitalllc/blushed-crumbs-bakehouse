<?php

namespace App\Services\Onboarding\Extraction;

use App\Models\Onboarding\OnboardingFile;
use Illuminate\Support\Collection;

/**
 * Deterministic stand-in for GeminiExtractionService — same input always
 * produces the same output, no network call, no randomness. Tests pin this
 * explicitly via config('onboarding.extractor') so the job-graph mechanics
 * (claiming, batching, failure recovery) stay decoupled from Gemini.
 */
class StubExtractor implements ExtractorInterface
{
    public function extractBatch(Collection $files): array
    {
        $results = [];

        foreach ($files as $file) {
            $label = $this->deriveLabel($file);

            $results[$file->id] = [
                'ok' => true,
                'alt_text' => $label,
                'labels' => [$label, $file->kind ?? 'file'],
                'result' => [
                    'stub' => true,
                    'source_content_hash' => $file->content_hash,
                    'label' => $label,
                ],
            ];
        }

        return $results;
    }

    private function deriveLabel(OnboardingFile $file): string
    {
        $name = pathinfo($file->original_filename ?? 'upload', PATHINFO_FILENAME);
        $name = trim(preg_replace('/[_\-]+/', ' ', $name) ?? '');

        return $name !== '' ? ucfirst($name) : 'Untitled upload';
    }
}
