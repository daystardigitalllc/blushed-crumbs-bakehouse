<?php

namespace App\Jobs\Onboarding;

use App\Models\Onboarding\OnboardingDraft;
use App\Models\Onboarding\OnboardingDraftItem;
use App\Models\Onboarding\OnboardingEvent;
use App\Models\Onboarding\OnboardingFile;
use App\Models\Tenant;
use App\Models\User;
use App\Mail\OnboardingResumeMail;
use App\Services\Onboarding\DraftSynthesisService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * The one Gemini text call that turns extraction's output into a full
 * reviewable proposal: site_content (all 31 keys), theme, canonicalized
 * categories with cover images, and detected products — persisted as
 * onboarding_draft_items so the review step (Phase 8) can diff/revert each
 * one independently. Nothing here touches a live tenant table or
 * public/uploads; ImportDraftJob (Phase 6) is still the only writer to
 * production.
 */
class SynthesizeDraftJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // 3 attempts total: GeminiClient already retries transient HTTP failures
    // internally (3x with backoff) plus one JSON-repair call per attempt, so
    // this is a second, coarser layer — for failures that persist across an
    // entire call (e.g. a quota window that hasn't reset yet). Spaced out
    // with backoff() below rather than retried back-to-back, since a
    // same-second retry is unlikely to see different results.
    public int $tries = 3;

    // Must comfortably exceed GeminiClient's worst case: one primary call
    // (up to 3 attempts x 60s HTTP timeout) plus, if that doesn't parse, one
    // repair call (another up to 3 x 60s) - roughly 360s worst case. The
    // previous 90s here caused a real production incident: the job got
    // killed mid-request by Laravel's own timeout enforcement, leaving the
    // draft permanently stuck in 'synthesizing' with no retry left.
    public int $timeout = 400;

    public function backoff(): array
    {
        return [30, 180]; // 30s before attempt 2, 3min before attempt 3
    }

    public function __construct(public int $draftId)
    {
        $this->onQueue('ai-import');
    }

    public function handle(DraftSynthesisService $synthesisService): void
    {
        $draft = OnboardingDraft::find($this->draftId);
        if (!$draft || $draft->status !== 'synthesizing') {
            return; // already synthesized, imported, or the draft is gone — idempotent
        }

        $tenant = Tenant::find($draft->tenant_id);
        if (!$tenant) {
            return;
        }

        $proposal = $synthesisService->synthesize($draft, $tenant);

        $this->persistCategories($draft, $proposal['categories']);
        $this->persistProducts($draft, $proposal['products']);
        $this->persistGalleryImages($draft, $proposal['hero_file_id']);

        $draft->proposed_content = $proposal['site_content'];
        $draft->theme_id = $proposal['theme_id'];
        $draft->confidence_overall = $proposal['confidence_overall'];
        $draft->model_versions = $proposal['model_versions'];
        $draft->status = 'ready_for_review';
        $draft->save();

        OnboardingEvent::create([
            'draft_id' => $draft->id,
            'tenant_id' => $draft->tenant_id,
            'type' => 'draft_synthesized',
            'message' => 'Proposal ready for review.',
            'payload' => [
                'category_count' => count($proposal['categories']),
                'product_count' => count($proposal['products']),
                'confidence_overall' => $proposal['confidence_overall'],
                'theme_id' => $proposal['theme_id'],
                'hero_file_id' => $proposal['hero_file_id'],
            ],
        ]);

        $this->maybeSendReadyEmail($draft, $tenant);
    }

    /**
     * Called once all $tries are exhausted. Marks the draft 'failed' (an
     * existing INCOMPLETE_STATUSES value) so the baker dashboard's
     * needs-attention banner can pick it up and offer to retry, instead of
     * the draft silently sitting in 'synthesizing' forever with no signal
     * anywhere that AI generation never actually completed.
     */
    public function failed(\Throwable $exception): void
    {
        $draft = OnboardingDraft::find($this->draftId);
        if (!$draft) {
            return;
        }

        $draft->status = 'failed';
        $draft->save();

        Log::error('Onboarding draft synthesis failed after all retries.', [
            'draft_id' => $draft->id,
            'tenant_id' => $draft->tenant_id,
            'error' => $exception->getMessage(),
        ]);

        OnboardingEvent::create([
            'draft_id' => $draft->id,
            'tenant_id' => $draft->tenant_id,
            'type' => 'draft_synthesis_failed',
            'message' => 'AI copy generation failed after retries: ' . $exception->getMessage(),
        ]);
    }

    /**
     * "On extraction complete (if they've navigated away)" per the Phase 9
     * plan. last_activity_at is only refreshed while the wizard tab is open
     * and visible (Wizard::checkProgress(), driven by wire:poll.visible), so
     * a stale timestamp here is a reasonable proxy for "not watching anymore"
     * rather than requiring real presence detection. Best-effort — a mail
     * failure must never fail an otherwise-successful synthesis.
     */
    private function maybeSendReadyEmail(OnboardingDraft $draft, Tenant $tenant): void
    {
        if ($draft->ready_notified_at !== null) {
            return; // already sent for this draft
        }

        $inactiveMinutes = (int) config('onboarding.resume_ready_email_inactive_minutes', 3);
        if ($draft->last_activity_at !== null && $draft->last_activity_at->gt(now()->subMinutes($inactiveMinutes))) {
            return; // still actively watching — no need to email
        }

        $user = User::where('tenant_id', $tenant->id)->first();
        if (!$user || !$user->email) {
            return;
        }

        try {
            $draft->ensureResumeToken();
            Mail::to($user->email)->queue(new OnboardingResumeMail($draft, $tenant, 'ready'));
            $draft->ready_notified_at = now();
            $draft->save();
        } catch (\Throwable $e) {
            Log::warning('Onboarding resume-ready email failed to send.', [
                'draft_id' => $draft->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function persistCategories(OnboardingDraft $draft, array $categories): void
    {
        foreach ($categories as $sort => $category) {
            OnboardingDraftItem::updateOrCreate(
                ['draft_id' => $draft->id, 'type' => 'category', 'dedupe_key' => Str::slug($category['name'])],
                [
                    'tenant_id' => $draft->tenant_id,
                    'payload_ai' => $category,
                    'payload_final' => $category,
                    'status' => 'pending',
                    'sort_order' => $sort,
                ]
            );
        }
    }

    private function persistProducts(OnboardingDraft $draft, array $products): void
    {
        foreach ($products as $sort => $product) {
            OnboardingDraftItem::updateOrCreate(
                ['draft_id' => $draft->id, 'type' => 'product', 'dedupe_key' => Str::slug($product['name'])],
                [
                    'tenant_id' => $draft->tenant_id,
                    'source_file_id' => $product['source_file_id'] ?? null,
                    'payload_ai' => $product,
                    'payload_final' => $product,
                    'status' => 'pending',
                    'sort_order' => $sort,
                ]
            );
        }
    }

    /**
     * Every extracted image becomes a gallery candidate for the review grid
     * — Phase 6's import is what actually copies any of them into
     * public/uploads, and only after the baker approves.
     */
    private function persistGalleryImages(OnboardingDraft $draft, ?int $heroFileId): void
    {
        $images = OnboardingFile::where('draft_id', $draft->id)
            ->where('kind', 'image')
            ->where('status', 'extracted')
            ->get();

        foreach ($images as $sort => $file) {
            $payload = [
                'alt_text' => $file->alt_text,
                'is_hero' => $file->id === $heroFileId,
                'quality_score' => $file->quality_score,
            ];

            OnboardingDraftItem::updateOrCreate(
                ['draft_id' => $draft->id, 'type' => 'gallery_image', 'dedupe_key' => 'file_' . $file->id],
                [
                    'tenant_id' => $draft->tenant_id,
                    'source_file_id' => $file->id,
                    'payload_ai' => $payload,
                    'payload_final' => $payload,
                    'status' => 'pending',
                    'sort_order' => $sort,
                ]
            );
        }
    }
}
