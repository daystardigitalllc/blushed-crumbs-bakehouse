<?php

namespace App\Console\Commands;

use App\Models\Onboarding\OnboardingDraft;
use App\Models\Onboarding\OnboardingEvent;
use Illuminate\Console\Command;

/**
 * Recovers a draft whose ImportDraftJob died mid-copy (kill -9, OOM, deploy)
 * — the transactional phase never runs in that case, so the database was
 * never touched; the only mess left behind is whatever bytes phase B
 * managed to copy before the worker died. Cleans those up from the
 * manifest (import_manifest was written before any byte moved, so it lists
 * every planned destination regardless of how far the copy actually got —
 * unlink is safe to call on paths that were never created) and resets the
 * draft to 'ready_for_review' so a retry can be dispatched.
 */
class OnboardingSweepStuckImports extends Command
{
    protected $signature = 'onboarding:sweep-stuck-imports
        {--force : Actually clean up and reset. Without this flag, only reports what would happen.}';

    protected $description = 'Recover onboarding drafts stuck in the importing status because their worker died mid-copy';

    public function handle(): int
    {
        $force = (bool) $this->option('force');
        $this->info($force ? 'LIVE RUN — stuck drafts will be reset.' : 'DRY RUN — pass --force to actually reset.');

        $stuckMinutes = (int) config('onboarding.import_stuck_minutes', 15);
        $cutoff = now()->subMinutes($stuckMinutes);

        $stuck = OnboardingDraft::where('status', 'importing')
            ->where('updated_at', '<', $cutoff)
            ->get();

        if ($stuck->isEmpty()) {
            $this->line('No stuck imports found.');

            return self::SUCCESS;
        }

        foreach ($stuck as $draft) {
            $this->recover($draft, $force);
        }

        return self::SUCCESS;
    }

    private function recover(OnboardingDraft $draft, bool $force): void
    {
        $manifest = $draft->import_manifest ?? [];
        $destPaths = collect($manifest['gallery'] ?? [])
            ->where('action', 'copy')
            ->pluck('dest_path')
            ->filter();

        $existing = $destPaths->filter(fn ($path) => is_file($path));

        $this->line("Draft {$draft->id}: {$existing->count()} orphaned file(s) to clean up.");

        if (!$force) {
            return;
        }

        foreach ($existing as $path) {
            @unlink($path);
        }

        $draft->status = 'ready_for_review';
        $draft->save();

        OnboardingEvent::create([
            'draft_id' => $draft->id,
            'tenant_id' => $draft->tenant_id,
            'type' => 'import_stuck_recovered',
            'message' => "Cleaned up {$existing->count()} orphaned file(s), reset to ready_for_review.",
        ]);

        $this->info("Draft {$draft->id}: recovered.");
    }
}
