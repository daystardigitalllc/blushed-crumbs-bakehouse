<?php

namespace App\Services\Onboarding\Extraction;

use App\Models\Onboarding\OnboardingFile;

/**
 * Deterministic stand-in for the real Gemini extractor (Phase 4) — same
 * input always produces the same output, no network call, no randomness.
 * Lets Phase 3's job graph (claiming, batching, failure recovery) be built
 * and verified without depending on AI output or spending API budget.
 */
class StubExtractor implements ExtractorInterface
{
    public function extract(OnboardingFile $file): array
    {
        $label = $this->deriveLabel($file);

        return [
            'alt_text' => $label,
            'labels' => [$label, $file->kind ?? 'file'],
            'result' => [
                'stub' => true,
                'source_content_hash' => $file->content_hash,
                'label' => $label,
            ],
        ];
    }

    private function deriveLabel(OnboardingFile $file): string
    {
        $name = pathinfo($file->original_filename ?? 'upload', PATHINFO_FILENAME);
        $name = trim(preg_replace('/[_\-]+/', ' ', $name) ?? '');

        return $name !== '' ? ucfirst($name) : 'Untitled upload';
    }
}
