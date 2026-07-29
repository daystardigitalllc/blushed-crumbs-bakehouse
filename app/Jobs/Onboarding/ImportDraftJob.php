<?php

namespace App\Jobs\Onboarding;

use App\Models\AuditLog;
use App\Models\GalleryItem;
use App\Models\Onboarding\OnboardingDraft;
use App\Models\Onboarding\OnboardingDraftItem;
use App\Models\Onboarding\OnboardingEvent;
use App\Models\Product;
use App\Models\Tenant;
use App\Services\Onboarding\TenantMediaPath;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * The ONLY writer to production tenant tables in the whole onboarding flow.
 * `tries=1` deliberately — a half-finished import must never silently retry
 * and double-copy; see onboarding:sweep-stuck-imports for the real recovery
 * path instead.
 *
 * Three phases, because a DB transaction can't roll back a copy():
 *   (A) validation gates + write the full copy manifest to the draft, before
 *       a single byte moves;
 *   (B) copy files outside the transaction, collecting every created path;
 *   (C) one short DB-only transaction. On any failure, the transaction rolls
 *       back itself and this job unlink()s every path phase B created —
 *       net effect is byte-identical to before the job ran, no orphans.
 */
class ImportDraftJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 300;

    public function __construct(public int $draftId)
    {
        $this->onQueue('ai-import');
    }

    public function handle(): void
    {
        $draft = OnboardingDraft::find($this->draftId);
        if (!$draft || !in_array($draft->status, ['ready_for_review', 'failed'], true)) {
            return; // already imported/importing, or not ready — idempotent no-op
        }

        $tenant = Tenant::find($draft->tenant_id);
        if (!$tenant) {
            return;
        }

        $manifest = $this->buildManifest($draft, $tenant);

        if (empty($manifest['gallery']) && empty($manifest['products']) && empty($draft->proposed_content)) {
            $this->markFailed($draft, 'Nothing to import — no approved gallery images, products, or site content on this draft.');

            return;
        }

        $draft->import_manifest = $manifest;
        $draft->status = 'importing';
        $draft->save();

        OnboardingEvent::create([
            'draft_id' => $draft->id,
            'tenant_id' => $draft->tenant_id,
            'type' => 'import_started',
            'message' => 'Copying approved files.',
            'payload' => ['gallery_count' => count($manifest['gallery']), 'product_count' => count($manifest['products'])],
        ]);

        $createdPaths = [];

        try {
            $createdPaths = $this->copyFiles($manifest);
        } catch (\Throwable $e) {
            $this->rollbackCopies($createdPaths);
            $this->markFailed($draft, 'File copy failed: ' . $e->getMessage());

            throw $e;
        }

        try {
            DB::transaction(function () use ($draft, $tenant, $manifest) {
                $this->applyProducts($tenant, $manifest['products']);
                $this->applyGallery($tenant, $manifest['gallery']);
                $this->applySiteContent($tenant, $draft);

                $tenant->onboarding_completed = true;
                $tenant->onboarding_completed_at = now();
                $tenant->active_onboarding_draft_id = $draft->id;
                $tenant->save();

                $draft->status = 'imported';
                $draft->imported_at = now();
                $draft->save();
            });
        } catch (\Throwable $e) {
            // The transaction already rolled back every DB write; the bytes
            // copied in phase B are the only thing left to clean up.
            $this->rollbackCopies($createdPaths);
            $this->markFailed($draft, 'Import transaction failed: ' . $e->getMessage());

            throw $e;
        }

        OnboardingEvent::create([
            'draft_id' => $draft->id,
            'tenant_id' => $draft->tenant_id,
            'type' => 'import_completed',
            'message' => 'Site built.',
            'payload' => ['gallery_count' => count($manifest['gallery']), 'product_count' => count($manifest['products'])],
        ]);

        AuditLog::logEvent('onboarding.import.completed', $tenant->id, null, [
            'draft_id' => $draft->id,
            'gallery_count' => count($manifest['gallery']),
            'product_count' => count($manifest['products']),
        ]);
    }

    // ─── Phase A: validation gates + manifest ───

    private function buildManifest(OnboardingDraft $draft, Tenant $tenant): array
    {
        $galleryItems = OnboardingDraftItem::where('draft_id', $draft->id)
            ->where('type', 'gallery_image')
            ->where('status', '!=', 'rejected')
            ->with('sourceFile')
            ->get();

        $productItems = OnboardingDraftItem::where('draft_id', $draft->id)
            ->where('type', 'product')
            ->where('status', '!=', 'rejected')
            ->get();

        return [
            'built_at' => now()->toISOString(),
            'gallery' => $this->planGallery($tenant, $galleryItems),
            'products' => $this->planProducts($tenant, $productItems),
        ];
    }

    private function planGallery(Tenant $tenant, $items): array
    {
        $existingHashes = GalleryItem::withoutGlobalScopes()->where('tenant_id', $tenant->id)
            ->whereNotNull('image_hash')
            ->pluck('id', 'image_hash');

        $plan = [];

        foreach ($items as $item) {
            $file = $item->sourceFile;
            if (!$file) {
                continue;
            }

            if (isset($existingHashes[$file->content_hash])) {
                $plan[] = ['action' => 'skip_duplicate', 'draft_item_id' => $item->id, 'file_id' => $file->id, 'image_hash' => $file->content_hash];

                continue;
            }

            $baseFilename = pathinfo($file->path, PATHINFO_FILENAME);
            $sourcePath = TenantMediaPath::draftDerivativeDir($tenant->id, $file->draft_id, 'display') . "/{$baseFilename}.webp";

            if (!is_file($sourcePath)) {
                continue; // missing derivative — skip this one item rather than fail the whole import
            }

            $destFilename = Str::uuid() . '.webp';
            $destDir = TenantMediaPath::galleryUploadDir($tenant->id);

            $plan[] = [
                'action' => 'copy',
                'draft_item_id' => $item->id,
                'file_id' => $file->id,
                'image_hash' => $file->content_hash,
                'source_path' => $sourcePath,
                'dest_path' => "{$destDir}/{$destFilename}",
                'public_path' => TenantMediaPath::galleryDisplayPath($tenant->id, $destFilename),
                'payload' => $item->payload_final ?? $item->payload_ai ?? [],
            ];
        }

        return $plan;
    }

    private function planProducts(Tenant $tenant, $items): array
    {
        $existing = Product::withoutGlobalScopes()->where('tenant_id', $tenant->id)
            ->get(['id', 'slug', 'name', 'description', 'price', 'price_min', 'price_max', 'category'])
            ->keyBy('slug');

        $plan = [];
        $seenSlugs = [];

        foreach ($items as $item) {
            $payload = $item->payload_final ?? $item->payload_ai ?? [];
            $name = trim((string) ($payload['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $slug = Str::slug($name);
            if (isset($seenSlugs[$slug])) {
                continue; // shouldn't happen (draft_items are unique per dedupe_key already) — defensive
            }
            $seenSlugs[$slug] = true;

            $existingProduct = $existing->get($slug);

            $plan[] = [
                'action' => $existingProduct ? 'fill_empty' : 'create',
                'draft_item_id' => $item->id,
                'slug' => $slug,
                'existing_id' => $existingProduct?->id,
                'payload' => $payload,
                'conflict' => $existingProduct && $this->hasPriceConflict($existingProduct, $payload),
            ];
        }

        return $plan;
    }

    private function hasPriceConflict($existingProduct, array $payload): bool
    {
        $existingHasPrice = $existingProduct->price !== null || $existingProduct->price_min !== null;
        $detectedHasPrice = ($payload['price_min'] ?? null) !== null;

        return $existingHasPrice && $detectedHasPrice && (float) $existingProduct->price_min !== (float) $payload['price_min'];
    }

    // ─── Phase B: copy bytes ───

    /**
     * @return array<string> every destination path actually created, in order
     */
    private function copyFiles(array $manifest): array
    {
        $created = [];

        foreach ($manifest['gallery'] as $entry) {
            if ($entry['action'] !== 'copy') {
                continue;
            }

            TenantMediaPath::ensureDir(dirname($entry['dest_path']));

            if (!@copy($entry['source_path'], $entry['dest_path'])) {
                throw new \RuntimeException("Failed to copy {$entry['source_path']} to {$entry['dest_path']}.");
            }

            $created[] = $entry['dest_path'];
        }

        return $created;
    }

    private function rollbackCopies(array $paths): void
    {
        foreach ($paths as $path) {
            @unlink($path);
        }
    }

    // ─── Phase C: DB writes (inside the transaction) ───

    private function applyProducts(Tenant $tenant, array $plan): void
    {
        foreach ($plan as $entry) {
            $payload = $entry['payload'];

            if ($entry['action'] === 'create') {
                Product::withoutGlobalScopes()->create([
                    'tenant_id' => $tenant->id,
                    'name' => $payload['name'],
                    'description' => $payload['description'] ?? null,
                    'slug' => $entry['slug'],
                    'price' => $payload['price_min'] ?? null,
                    'price_min' => $payload['price_min'] ?? null,
                    'price_max' => $payload['price_max'] ?? null,
                    'category' => $payload['category'] ?? 'Cake', // matches the column's own DB default — explicit null would bypass it
                    'is_active' => true,
                    'source' => 'ai_onboarding',
                    'ai_confidence' => null,
                    'onboarding_file_id' => $payload['source_file_id'] ?? null,
                ]);

                continue;
            }

            // fill_empty: a baker's real, already-entered price/description
            // must never be overwritten by one read off a photographed menu.
            $existing = Product::withoutGlobalScopes()->find($entry['existing_id']);
            if (!$existing) {
                continue;
            }

            if (blank($existing->description) && !empty($payload['description'])) {
                $existing->description = $payload['description'];
            }
            if ($existing->price === null && $existing->price_min === null && ($payload['price_min'] ?? null) !== null) {
                $existing->price = $payload['price_min'];
                $existing->price_min = $payload['price_min'];
                $existing->price_max = $payload['price_max'] ?? null;
            }
            if (blank($existing->category) && !empty($payload['category'])) {
                $existing->category = $payload['category'];
            }

            $existing->save();
        }
    }

    private function applyGallery(Tenant $tenant, array $plan): void
    {
        foreach ($plan as $entry) {
            if ($entry['action'] !== 'copy') {
                continue;
            }

            $payload = $entry['payload'];

            GalleryItem::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'title' => $payload['alt_text'] ?? 'Gallery Photo', // galleries.title is NOT NULL, no DB default
                'category' => $payload['category'] ?? 'Single Tier', // matches the column's own DB default
                'image_url' => $entry['public_path'],
                'alt_text' => $payload['alt_text'] ?? null,
                'quality_score' => $payload['quality_score'] ?? null,
                'is_hero' => (bool) ($payload['is_hero'] ?? false),
                'is_visible' => true,
                'image_hash' => $entry['image_hash'],
                'source' => 'ai_onboarding',
                'onboarding_file_id' => $entry['file_id'],
            ]);
        }
    }

    /**
     * Full overwrite, not fill-only-empty — the baker explicitly approved
     * this whole proposal before triggering the import. Fill-only-empty is
     * specifically a products safeguard (see applyProducts).
     */
    private function applySiteContent(Tenant $tenant, OnboardingDraft $draft): void
    {
        if (!empty($draft->proposed_content)) {
            $tenant->site_content = $draft->proposed_content;
        }

        if (!empty($draft->theme_id)) {
            $this->applyThemeChoice($tenant, $draft->theme_id);
        }
    }

    /**
     * The real gate on what theme ends up live. ReviewPanel's picker
     * (onboardingAvailableThemes()) intentionally previews what a Pro plan
     * *would* unlock based on the draft's self-reported `basics.selected_plan`
     * — that self-report is never proof of payment, so it must never be
     * trusted here. Import checks the tenant's actual plan_tier only; an
     * unpaid Pro pick falls back to a starter theme with the real choice
     * stashed on pending_pro_theme_id for StripeWebhookController to apply
     * automatically the moment a real payment lands (Phase 9's fix for the
     * same free-Pro-theme bypass that existed in the legacy v1 wizard).
     */
    private function applyThemeChoice(Tenant $tenant, string $themeId): void
    {
        if ($tenant->plan_tier === 'pro' || array_key_exists($themeId, Tenant::getStarterThemes())) {
            $tenant->theme_id = $themeId;

            return;
        }

        $tenant->pending_pro_theme_id = $themeId;
        if (empty($tenant->theme_id) || !array_key_exists($tenant->theme_id, Tenant::getStarterThemes())) {
            $tenant->theme_id = 'rustic_kitchen';
        }
    }

    private function markFailed(OnboardingDraft $draft, string $reason): void
    {
        $draft->status = 'failed';
        $draft->save();

        Log::warning('Onboarding import failed.', ['draft_id' => $draft->id, 'reason' => $reason]);

        OnboardingEvent::create([
            'draft_id' => $draft->id,
            'tenant_id' => $draft->tenant_id,
            'type' => 'import_failed',
            'message' => $reason,
        ]);
    }
}
