<?php

namespace App\Jobs\Onboarding;

use App\Models\Onboarding\OnboardingDraft;
use App\Models\Onboarding\OnboardingEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Claims every currently-pending file in a draft into extraction batches (6
 * images / 1 PDF each, config-driven) via an atomic single-UPDATE claim per
 * batch. Two dispatchers racing on the same draft can never grab the same
 * file: the UPDATE...LIMIT's WHERE status='pending' is re-checked under
 * InnoDB's row lock, so the loser of the race simply claims nothing.
 *
 * ShouldBeUnique + the delay IngestOnboardingFileJob adds is the "debounce":
 * a burst of uploads finishing within a few seconds each try to dispatch
 * this job, but only the first actually gets pushed onto the queue — the
 * rest are silently dropped while it's still pending — so one dispatch pass
 * drains the whole burst instead of one pass per file.
 */
class DispatchPendingExtractionsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;
    public int $uniqueFor = 30;

    public function __construct(public int $draftId)
    {
        $this->onQueue('ingest');
    }

    public function uniqueId(): string
    {
        return (string) $this->draftId;
    }

    public function handle(): void
    {
        $draft = OnboardingDraft::find($this->draftId);
        if (!$draft) {
            return;
        }

        $claimedAny = false;

        foreach (['image', 'pdf'] as $kind) {
            $batchSize = (int) config($kind === 'image'
                ? 'onboarding.extraction_batch_size_images'
                : 'onboarding.extraction_batch_size_pdfs');

            while ($batchId = $this->claimBatch($draft->id, $kind, $batchSize)) {
                $claimedAny = true;

                OnboardingEvent::create([
                    'draft_id' => $draft->id,
                    'tenant_id' => $draft->tenant_id,
                    'type' => 'extraction_batch_claimed',
                    'message' => "{$kind} batch {$batchId}",
                ]);

                ExtractBatchJob::dispatch($draft->id, $batchId);
            }
        }

        if (!$claimedAny) {
            return;
        }

        if ($draft->status === 'collecting') {
            $draft->status = 'extracting';
            $draft->extraction_started_at ??= now();
            $draft->save();
        }

        // Safety-net sweep in case a batch's ExtractBatchJob dies before it
        // can dispatch its own FinalizeExtractionJob — one per dispatch pass
        // is enough since FinalizeExtractionJob re-derives from the whole
        // draft, not just the batches claimed here.
        FinalizeExtractionJob::dispatch($draft->id)
            ->delay(now()->addMinutes((int) config('onboarding.extraction_stuck_minutes', 10) + 1));
    }

    /**
     * One atomic UPDATE claims up to $limit pending rows of $kind into a
     * fresh batch id; a plain SELECT (in ExtractBatchJob) then fetches
     * exactly those rows. Race safety comes entirely from the UPDATE's
     * WHERE status='pending' being re-evaluated under the row lock, not
     * from any subsequent SELECT.
     */
    private function claimBatch(int $draftId, string $kind, int $limit): ?string
    {
        if ($limit < 1) {
            return null;
        }

        $batchId = (string) Str::uuid();

        $query = DB::table('onboarding_files')
            ->where('draft_id', $draftId)
            ->where('kind', $kind)
            ->where('status', 'pending');

        // Best-first for images: local quality score decides which images get
        // semantic analysis first (Phase 4's AI budget cap spends on the best
        // photos, not just whichever uploaded first). Nulls sort last in both
        // MySQL and SQLite's DESC ordering, so unscored images fall to the back.
        if ($kind === 'image') {
            $query->orderByDesc('quality_score')->orderBy('id');
        } else {
            $query->orderBy('id');
        }

        $claimed = $query->limit($limit)->update([
            'status' => 'extracting',
            'batch_id' => $batchId,
            'claimed_at' => now(),
            'updated_at' => now(),
        ]);

        return $claimed > 0 ? $batchId : null;
    }
}
