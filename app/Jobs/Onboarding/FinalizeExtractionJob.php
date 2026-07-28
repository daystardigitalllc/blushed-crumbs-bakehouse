<?php

namespace App\Jobs\Onboarding;

use App\Models\Onboarding\OnboardingDraft;
use App\Models\Onboarding\OnboardingEvent;
use App\Models\Onboarding\OnboardingFile;
use App\Services\Onboarding\OnboardingProgress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * "Release and recheck": holds no running counter, just re-derives the
 * draft's state from onboarding_files.status (via OnboardingProgress) every
 * time it runs, so it can be dispatched liberally — after every batch, plus
 * a delayed safety-net copy per dispatch pass — with no risk of drifting
 * from the truth.
 *
 * Also the stuck sweep: any file still 'extracting' more than
 * onboarding.extraction_stuck_minutes after being claimed means whatever
 * worker had it died (kill -9, OOM, deploy) before finishing — reset it to
 * 'pending' so the next DispatchPendingExtractionsJob run picks it back up.
 */
class FinalizeExtractionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(public int $draftId)
    {
        $this->onQueue('ai-import');
    }

    public function handle(): void
    {
        $draft = OnboardingDraft::find($this->draftId);
        if (!$draft) {
            return;
        }

        $recovered = $this->sweepStuckFiles($draft);
        if ($recovered > 0) {
            DispatchPendingExtractionsJob::dispatch($draft->id);

            return; // recovered files are non-terminal again — not done yet
        }

        if (in_array($draft->status, ['synthesizing', 'ready_for_review', 'importing', 'imported'], true)) {
            return; // extraction outcome no longer relevant once synthesis or import has started
        }

        if (!OnboardingProgress::isExtractionComplete($draft->id)) {
            return;
        }

        // Extraction's done — hand off to synthesis (Phase 5). 'ready_for_review'
        // means the full proposal (site content, theme, products) is ready for
        // the baker, not just that extraction finished — that's what
        // 'synthesizing' is for in between.
        $draft->status = 'synthesizing';
        $draft->extraction_completed_at = now();
        $draft->save();

        OnboardingEvent::create([
            'draft_id' => $draft->id,
            'tenant_id' => $draft->tenant_id,
            'type' => 'draft_extraction_completed',
            'message' => 'All files reached a terminal state.',
            'payload' => OnboardingProgress::statusCounts($draft->id),
        ]);

        SynthesizeDraftJob::dispatch($draft->id);
    }

    private function sweepStuckFiles(OnboardingDraft $draft): int
    {
        $stuckMinutes = (int) config('onboarding.extraction_stuck_minutes', 10);
        $cutoff = now()->subMinutes($stuckMinutes);

        $stuckFiles = OnboardingFile::where('draft_id', $draft->id)
            ->where('status', 'extracting')
            ->where('claimed_at', '<', $cutoff)
            ->get();

        foreach ($stuckFiles as $file) {
            $file->status = 'pending';
            $file->batch_id = null;
            $file->claimed_at = null;
            $file->save();

            OnboardingEvent::create([
                'draft_id' => $file->draft_id,
                'tenant_id' => $file->tenant_id,
                'type' => 'file_extraction_stuck_recovered',
                'message' => $file->original_filename,
                'payload' => ['file_id' => $file->id],
            ]);
        }

        return $stuckFiles->count();
    }
}
