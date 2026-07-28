<?php

namespace App\Jobs\Onboarding;

use App\Models\Onboarding\OnboardingDraft;
use App\Models\Onboarding\OnboardingDraftItem;
use App\Models\Onboarding\OnboardingEvent;
use App\Models\Onboarding\OnboardingFile;
use App\Models\Tenant;
use App\Services\Onboarding\DraftSynthesisService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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

    public int $tries = 2;
    public int $timeout = 90;

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
