<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Onboarding\OnboardingDraft;
use App\Services\Onboarding\TenantMediaPath;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * The full two-tier retention policy described in the plan (see
 * config/onboarding.php's Retention block, which has carried these TTLs
 * since day one waiting for this command):
 *
 *   - Incomplete drafts (collecting/extracting/synthesizing/ready_for_review/
 *     failed) are purged entirely — files AND the row — 48h after their last
 *     activity. The clock is last_activity_at, not created_at, so a baker who
 *     comes back and does anything gets a fresh window.
 *   - Imported drafts keep their row/metadata forever (the read-only "what
 *     did the AI do" record) but lose their private original files 7 days
 *     after import — the WebP copies already in public/uploads are what
 *     actually serve the live site by then.
 *   - Drafts mid-import ('importing') are never touched here at all —
 *     onboarding:sweep-stuck-imports is the only thing that recovers those.
 *
 * Files are always deleted before rows: if this crashes between the two, the
 * result is an orphaned directory (which onboarding:prune already knows how
 * to sweep up), never a DB row pointing at bytes that no longer exist.
 */
class OnboardingPruneDrafts extends Command
{
    protected $signature = 'onboarding:prune-drafts
        {--dry-run : Report what would be purged without deleting anything.}';

    protected $description = 'Purge abandoned onboarding drafts (48h) and imported drafts\' original files (7 days) per the retention policy';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $this->info($dryRun ? 'DRY RUN — nothing will be deleted.' : 'LIVE RUN — matching drafts/files will be deleted.');

        $incompleteCount = $this->pruneIncomplete($dryRun);
        $importedCount = $this->pruneImportedOriginals($dryRun);

        $this->info("Incomplete drafts purged: {$incompleteCount}. Imported drafts with originals purged: {$importedCount}.");

        return self::SUCCESS;
    }

    private function pruneIncomplete(bool $dryRun): int
    {
        $ttlHours = (int) config('onboarding.incomplete_draft_ttl_hours', 48);
        $cutoff = now()->subHours($ttlHours);
        $count = 0;

        OnboardingDraft::whereIn('status', OnboardingDraft::INCOMPLETE_STATUSES)
            ->where('last_activity_at', '<', $cutoff)
            ->orderBy('id')
            ->chunkById(50, function ($drafts) use (&$count, $dryRun) {
                foreach ($drafts as $draft) {
                    try {
                        $this->line("Incomplete draft {$draft->id} (tenant {$draft->tenant_id}, status {$draft->status}, last activity {$draft->last_activity_at}) — expired.");

                        if ($dryRun) {
                            $count++;

                            continue;
                        }

                        TenantMediaPath::deleteDraftRoot($draft->tenant_id, $draft->id);

                        AuditLog::logEvent('onboarding.draft.purged', $draft->tenant_id, null, [
                            'draft_id' => $draft->id,
                            'status' => $draft->status,
                            'last_activity_at' => optional($draft->last_activity_at)->toISOString(),
                        ]);

                        $draft->files()->delete();
                        $draft->items()->delete();
                        $draft->events()->delete();
                        $draft->delete();

                        $count++;
                    } catch (\Throwable $e) {
                        Log::warning('onboarding:prune-drafts failed for one incomplete draft.', [
                            'draft_id' => $draft->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

        return $count;
    }

    private function pruneImportedOriginals(bool $dryRun): int
    {
        $ttlDays = (int) config('onboarding.imported_draft_file_ttl_days', 7);
        $cutoff = now()->subDays($ttlDays);
        $count = 0;

        OnboardingDraft::where('status', 'imported')
            ->whereNotNull('imported_at')
            ->where('imported_at', '<', $cutoff)
            ->orderBy('id')
            ->chunkById(50, function ($drafts) use (&$count, $dryRun) {
                foreach ($drafts as $draft) {
                    try {
                        $originalsDir = TenantMediaPath::draftOriginalsDir($draft->tenant_id, $draft->id);
                        if (!is_dir($originalsDir)) {
                            continue; // already purged — idempotent, no row change needed
                        }

                        $this->line("Imported draft {$draft->id} (tenant {$draft->tenant_id}) — purging original files.");

                        if ($dryRun) {
                            $count++;

                            continue;
                        }

                        TenantMediaPath::deleteDirectory($originalsDir);

                        AuditLog::logEvent('onboarding.draft.originals_purged', $draft->tenant_id, null, [
                            'draft_id' => $draft->id,
                            'imported_at' => optional($draft->imported_at)->toISOString(),
                        ]);

                        $count++;
                    } catch (\Throwable $e) {
                        Log::warning('onboarding:prune-drafts failed to purge one imported draft\'s originals.', [
                            'draft_id' => $draft->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

        return $count;
    }
}
