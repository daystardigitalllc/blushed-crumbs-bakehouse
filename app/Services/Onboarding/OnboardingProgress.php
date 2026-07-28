<?php

namespace App\Services\Onboarding;

use App\Models\Onboarding\OnboardingFile;
use Illuminate\Support\Facades\DB;

/**
 * Extraction progress is always derived from onboarding_files.status via
 * GROUP BY — never stored as a counter on the draft — so it can't drift
 * from the rows it describes, even under concurrent workers.
 */
class OnboardingProgress
{
    public const TERMINAL_STATUSES = ['extracted', 'failed', 'unsupported', 'duplicate'];

    /**
     * @return array<string,int> status => count
     */
    public static function statusCounts(int $draftId): array
    {
        return OnboardingFile::where('draft_id', $draftId)
            ->select('status', DB::raw('count(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->all();
    }

    public static function isExtractionComplete(int $draftId): bool
    {
        $counts = self::statusCounts($draftId);

        if (empty($counts)) {
            return false;
        }

        $nonTerminal = array_diff_key($counts, array_flip(self::TERMINAL_STATUSES));

        return array_sum($nonTerminal) === 0;
    }
}
