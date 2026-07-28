<?php

namespace App\Jobs\Onboarding;

use App\Models\Onboarding\OnboardingFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched once per successful upload (see OnboardingFileStore::store()).
 * Local processing — mime sniff, dims, derivatives, quality score, dedupe —
 * already happened synchronously in the upload request so the baker sees it
 * immediately; this job's only remaining work is handing the file off to
 * the batching/extraction pipeline without making the upload response wait.
 */
class IngestOnboardingFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 30;

    public function __construct(public int $fileId)
    {
        $this->onQueue('ingest');
    }

    public function handle(): void
    {
        $file = OnboardingFile::find($this->fileId);

        // Gone, or already past 'pending' (claimed by a batch, draft pruned or
        // imported) — nothing to do. Safe to be a no-op since jobs can retry.
        if (!$file || $file->status !== 'pending') {
            return;
        }

        DispatchPendingExtractionsJob::dispatch($file->draft_id)
            ->delay(now()->addSeconds((int) config('onboarding.extraction_dispatch_debounce_seconds', 5)));
    }
}
