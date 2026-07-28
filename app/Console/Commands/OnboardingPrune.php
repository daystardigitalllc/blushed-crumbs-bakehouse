<?php

namespace App\Console\Commands;

use App\Models\Onboarding\OnboardingDraft;
use App\Services\Onboarding\TenantMediaPath;
use Illuminate\Console\Command;

/**
 * Disk hygiene for onboarding draft storage — not the full two-tier
 * retention policy (that's a later phase's onboarding:prune-drafts, which
 * also purges DB rows on a TTL). This command only removes storage that has
 * no corresponding draft row at all: directories orphaned by a failed
 * import, a manually deleted draft, or interrupted testing.
 */
class OnboardingPrune extends Command
{
    protected $signature = 'onboarding:prune
        {--draft= : Delete one specific draft\'s storage by draft ID, regardless of orphan status}
        {--force : Actually delete. Without this flag, only reports what would be removed.}';

    protected $description = 'Remove onboarding draft storage directories that have no matching onboarding_drafts row (or a specific draft\'s storage via --draft)';

    public function handle(): int
    {
        $force = (bool) $this->option('force');
        $this->info($force ? 'LIVE RUN — matching directories will be deleted.' : 'DRY RUN — pass --force to actually delete.');

        if ($draftId = $this->option('draft')) {
            return $this->pruneSingleDraft((int) $draftId, $force);
        }

        return $this->pruneOrphans($force);
    }

    private function pruneSingleDraft(int $draftId, bool $force): int
    {
        $draft = OnboardingDraft::find($draftId);
        if (!$draft) {
            $this->error("No onboarding_drafts row with id {$draftId}.");

            return self::FAILURE;
        }

        $root = TenantMediaPath::draftRoot($draft->tenant_id, $draft->id);
        if (!is_dir($root)) {
            $this->line("Nothing on disk for draft {$draftId} ({$root}).");

            return self::SUCCESS;
        }

        $this->line("Draft {$draftId} storage: {$root}");
        if ($force) {
            TenantMediaPath::deleteDraftRoot($draft->tenant_id, $draft->id);
            $this->info('Deleted.');
        }

        return self::SUCCESS;
    }

    private function pruneOrphans(bool $force): int
    {
        $root = storage_path('app/onboarding');
        if (!is_dir($root)) {
            $this->line('No onboarding storage directory exists yet.');

            return self::SUCCESS;
        }

        $existingDraftIds = OnboardingDraft::pluck('id', 'id');
        $removed = 0;
        $kept = 0;

        foreach (glob($root . '/*', GLOB_ONLYDIR) ?: [] as $tenantDir) {
            $tenantId = (int) basename($tenantDir);

            foreach (glob($tenantDir . '/*', GLOB_ONLYDIR) ?: [] as $draftDir) {
                $draftId = (int) basename($draftDir);

                if (isset($existingDraftIds[$draftId])) {
                    $kept++;
                    continue;
                }

                $this->line("Orphaned: tenant {$tenantId} / draft {$draftId} ({$draftDir})");
                $removed++;

                if ($force) {
                    TenantMediaPath::deleteDraftRoot($tenantId, $draftId);
                }
            }
        }

        $this->info("Orphaned draft directories: {$removed} " . ($force ? 'deleted' : 'would be deleted') . '. Kept: ' . $kept . '.');

        return self::SUCCESS;
    }
}
