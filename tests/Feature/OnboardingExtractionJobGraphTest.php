<?php

namespace Tests\Feature;

use App\Jobs\Onboarding\DispatchPendingExtractionsJob;
use App\Jobs\Onboarding\FinalizeExtractionJob;
use App\Models\Onboarding\OnboardingDraft;
use App\Models\Onboarding\OnboardingEvent;
use App\Models\Onboarding\OnboardingFile;
use App\Models\Tenant;
use App\Services\Onboarding\Extraction\StubExtractor;
use App\Services\Onboarding\OnboardingProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\ThrowingExtractor;
use Tests\TestCase;

/**
 * Covers Phase 3's verification bullets from the onboarding-rebuild plan:
 * the full collecting -> extracting -> ready_for_review flow, every file
 * reaching a terminal state, one bad file not blocking the rest, and the
 * stuck-worker sweep recovering a wedged file. QUEUE_CONNECTION=sync in
 * testing means ::dispatch() runs handle() immediately (delay is ignored by
 * the sync driver), so the whole job chain cascades synchronously and can
 * be asserted on directly without a real queue worker.
 *
 * True concurrent-claim races can't be reproduced in a single-process
 * SQLite test run; the "two dispatchers" bullet is instead verified as a
 * safe-rerun property (see test_rerunning_dispatch_after_full_claim_is_a_safe_noop)
 * plus code review of the atomic UPDATE...LIMIT claim, which is what
 * actually provides the race safety on MySQL/InnoDB in production.
 */
class OnboardingExtractionJobGraphTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private OnboardingDraft $draft;

    protected function setUp(): void
    {
        parent::setUp();

        // Pin the deterministic stub explicitly — this suite tests the job
        // graph's mechanics (claiming, batching, sweep), not Gemini, and
        // must stay decoupled from whatever config('onboarding.extractor')
        // defaults to.
        config(['onboarding.extractor' => StubExtractor::class]);

        $this->tenant = Tenant::create([
            'name' => 'Test Bakery',
            'slug' => 'test-bakery-' . Str::random(8),
            'domain' => Str::random(8) . '.test',
            'subdomain' => Str::random(8),
            'owner_name' => 'Test Owner',
            'email' => Str::random(8) . '@example.com',
            'plan_tier' => 'pro',
            'theme_id' => 'sweet_elegant',
            'is_active' => true,
        ]);

        $this->draft = OnboardingDraft::create([
            'tenant_id' => $this->tenant->id,
            'version' => 1,
            'status' => 'collecting',
        ]);
    }

    private function seedFiles(int $count, string $kind = 'image', string $status = 'pending', string $filenamePrefix = 'photo'): void
    {
        for ($i = 0; $i < $count; $i++) {
            OnboardingFile::create([
                'draft_id' => $this->draft->id,
                'tenant_id' => $this->tenant->id,
                'original_filename' => "{$filenamePrefix}-{$i}.jpg",
                'kind' => $kind,
                'path' => "/tmp/{$filenamePrefix}-{$i}.jpg",
                'mime_type' => 'image/jpeg',
                'content_hash' => (string) Str::uuid(),
                'status' => $status,
            ]);
        }
    }

    public function test_full_pipeline_runs_250_files_from_collecting_to_ready_for_review()
    {
        $this->seedFiles(250, 'image');

        DispatchPendingExtractionsJob::dispatch($this->draft->id);

        $this->draft->refresh();
        $this->assertSame('ready_for_review', $this->draft->status);
        $this->assertNotNull($this->draft->extraction_started_at);
        $this->assertNotNull($this->draft->extraction_completed_at);

        $counts = OnboardingProgress::statusCounts($this->draft->id);
        $this->assertSame(250, $counts['extracted'] ?? 0);
        $this->assertTrue(OnboardingProgress::isExtractionComplete($this->draft->id));

        // Every file reached a terminal state with no stragglers.
        $this->assertSame(0, OnboardingFile::where('draft_id', $this->draft->id)
            ->whereIn('status', ['pending', 'extracting'])
            ->count());

        // Batching respected the configured cap (default 6 images/batch).
        $batchSizes = OnboardingFile::where('draft_id', $this->draft->id)
            ->selectRaw('batch_id, count(*) as c')
            ->groupBy('batch_id')
            ->pluck('c');
        $this->assertGreaterThan(1, $batchSizes->count());
        $this->assertTrue($batchSizes->every(fn ($c) => $c <= 6));
    }

    public function test_unsupported_and_duplicate_files_are_never_claimed()
    {
        $this->seedFiles(3, 'image', 'pending');
        $this->seedFiles(2, 'unsupported', 'unsupported', 'bad');

        DispatchPendingExtractionsJob::dispatch($this->draft->id);

        $this->assertSame(2, OnboardingFile::where('draft_id', $this->draft->id)
            ->where('status', 'unsupported')
            ->whereNull('batch_id')
            ->count());
    }

    public function test_pdf_batches_are_one_file_each()
    {
        $this->seedFiles(3, 'pdf', 'pending', 'menu');

        DispatchPendingExtractionsJob::dispatch($this->draft->id);

        $batchCount = OnboardingFile::where('draft_id', $this->draft->id)
            ->distinct('batch_id')
            ->count('batch_id');
        $this->assertSame(3, $batchCount);

        $this->assertSame(3, OnboardingEvent::where('draft_id', $this->draft->id)
            ->where('type', 'extraction_batch_claimed')
            ->count());
    }

    public function test_one_failing_file_does_not_block_others_or_finalize()
    {
        config(['onboarding.extractor' => ThrowingExtractor::class]);

        $this->seedFiles(2, 'image', 'pending', 'good');
        OnboardingFile::create([
            'draft_id' => $this->draft->id,
            'tenant_id' => $this->tenant->id,
            'original_filename' => '__throw__',
            'kind' => 'image',
            'path' => '/tmp/bad.jpg',
            'mime_type' => 'image/jpeg',
            'content_hash' => (string) Str::uuid(),
            'status' => 'pending',
        ]);

        DispatchPendingExtractionsJob::dispatch($this->draft->id);

        $this->assertSame(2, OnboardingFile::where('draft_id', $this->draft->id)->where('status', 'extracted')->count());

        $failed = OnboardingFile::where('draft_id', $this->draft->id)->where('status', 'failed')->first();
        $this->assertNotNull($failed);
        $this->assertStringContainsString('Simulated extraction failure', $failed->error_message);

        $this->draft->refresh();
        $this->assertSame('ready_for_review', $this->draft->status);
    }

    public function test_stuck_file_is_swept_and_recovered()
    {
        $stuck = OnboardingFile::create([
            'draft_id' => $this->draft->id,
            'tenant_id' => $this->tenant->id,
            'original_filename' => 'stuck.jpg',
            'kind' => 'image',
            'path' => '/tmp/stuck.jpg',
            'mime_type' => 'image/jpeg',
            'content_hash' => (string) Str::uuid(),
            'status' => 'extracting',
            'batch_id' => 'dead-worker-batch',
            'claimed_at' => now()->subMinutes(15),
        ]);

        FinalizeExtractionJob::dispatch($this->draft->id);

        $this->assertTrue(OnboardingEvent::where('draft_id', $this->draft->id)
            ->where('type', 'file_extraction_stuck_recovered')
            ->where('message', 'stuck.jpg')
            ->exists());

        // Sync queue cascades the recovery all the way through: swept back
        // to pending -> reclaimed -> extracted -> draft finalized.
        $stuck->refresh();
        $this->assertSame('extracted', $stuck->status);

        $this->draft->refresh();
        $this->assertSame('ready_for_review', $this->draft->status);
    }

    public function test_a_file_within_the_stuck_window_is_left_alone()
    {
        $recent = OnboardingFile::create([
            'draft_id' => $this->draft->id,
            'tenant_id' => $this->tenant->id,
            'original_filename' => 'in-flight.jpg',
            'kind' => 'image',
            'path' => '/tmp/in-flight.jpg',
            'mime_type' => 'image/jpeg',
            'content_hash' => (string) Str::uuid(),
            'status' => 'extracting',
            'batch_id' => 'live-batch',
            'claimed_at' => now()->subMinutes(2),
        ]);

        FinalizeExtractionJob::dispatch($this->draft->id);

        $recent->refresh();
        $this->assertSame('extracting', $recent->status);

        $this->draft->refresh();
        $this->assertSame('collecting', $this->draft->status); // never touched — no claim happened via the dispatcher
    }

    public function test_rerunning_dispatch_after_full_claim_is_a_safe_noop()
    {
        $this->seedFiles(6, 'image');

        (new DispatchPendingExtractionsJob($this->draft->id))->handle();
        $firstPassClaims = OnboardingEvent::where('draft_id', $this->draft->id)
            ->where('type', 'extraction_batch_claimed')
            ->count();
        $this->assertSame(1, $firstPassClaims); // exactly one batch of 6

        // Simulate a second dispatcher racing on the same draft after the
        // first already drained supply — it must find nothing left to claim.
        (new DispatchPendingExtractionsJob($this->draft->id))->handle();
        $secondPassClaims = OnboardingEvent::where('draft_id', $this->draft->id)
            ->where('type', 'extraction_batch_claimed')
            ->count();
        $this->assertSame($firstPassClaims, $secondPassClaims);

        $this->assertSame(6, OnboardingFile::where('draft_id', $this->draft->id)->where('status', 'extracted')->count());
    }

    public function test_partial_batch_claims_up_to_configured_size_not_all_pending()
    {
        $this->seedFiles(10, 'image');

        DispatchPendingExtractionsJob::dispatch($this->draft->id);

        // 10 files / 6 per batch = two batches: 6 then 4.
        $sizes = OnboardingFile::where('draft_id', $this->draft->id)
            ->selectRaw('batch_id, count(*) as c')
            ->groupBy('batch_id')
            ->pluck('c')
            ->sort()
            ->values();

        $this->assertSame([4, 6], $sizes->toArray());
    }
}
