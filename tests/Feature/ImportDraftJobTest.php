<?php

namespace Tests\Feature;

use App\Jobs\Onboarding\ImportDraftJob;
use App\Models\GalleryItem;
use App\Models\Onboarding\OnboardingDraft;
use App\Models\Onboarding\OnboardingDraftItem;
use App\Models\Onboarding\OnboardingFile;
use App\Models\Product;
use App\Models\Tenant;
use App\Services\Onboarding\TenantMediaPath;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Covers Phase 6's verification bullets: import into a tenant and diff,
 * an exception mid-transaction leaving zero rows and zero orphan files,
 * running import twice being a no-op, importing against a tenant that
 * already has products/gallery rows without duplicating or overwriting a
 * real price, and the stuck-import sweeper recovering a dead worker's
 * partial copy.
 */
class ImportDraftJobTest extends TestCase
{
    use RefreshDatabase;

    private function makeTenant(array $overrides = []): Tenant
    {
        return Tenant::create(array_merge([
            'name' => 'Import Test Bakery',
            'slug' => 'import-test-' . Str::random(8),
            'domain' => Str::random(8) . '.test',
            'owner_name' => 'Test Owner',
            'email' => Str::random(8) . '@example.com',
            'plan_tier' => 'standard',
            'theme_id' => 'rustic_kitchen',
            'is_active' => true,
        ], $overrides));
    }

    /** Seeds a gallery_image draft item with a real file on disk at the expected 'display' derivative path. */
    private function seedGalleryItem(OnboardingDraft $draft, Tenant $tenant, array $payload = []): OnboardingDraftItem
    {
        $file = OnboardingFile::create([
            'draft_id' => $draft->id,
            'tenant_id' => $tenant->id,
            'original_filename' => 'photo-' . Str::random(4) . '.jpg',
            'kind' => 'image',
            'path' => TenantMediaPath::draftOriginalsDir($tenant->id, $draft->id) . '/' . ($base = (string) Str::uuid()) . '.jpg',
            'mime_type' => 'image/jpeg',
            'content_hash' => (string) Str::uuid(),
            'status' => 'extracted',
        ]);

        $displayDir = TenantMediaPath::draftDerivativeDir($tenant->id, $draft->id, 'display');
        TenantMediaPath::ensureDir($displayDir);
        $baseFilename = pathinfo($file->path, PATHINFO_FILENAME);
        file_put_contents("{$displayDir}/{$baseFilename}.webp", 'fake-webp-bytes');

        return OnboardingDraftItem::create([
            'draft_id' => $draft->id,
            'tenant_id' => $tenant->id,
            'source_file_id' => $file->id,
            'type' => 'gallery_image',
            'dedupe_key' => 'file_' . $file->id,
            'payload_final' => array_merge(['alt_text' => 'A cake photo', 'is_hero' => false, 'quality_score' => 80], $payload),
            'status' => 'pending',
        ]);
    }

    private function seedProductItem(OnboardingDraft $draft, Tenant $tenant, array $payload): OnboardingDraftItem
    {
        return OnboardingDraftItem::create([
            'draft_id' => $draft->id,
            'tenant_id' => $tenant->id,
            'type' => 'product',
            'dedupe_key' => Str::slug($payload['name']),
            'payload_final' => $payload,
            'status' => 'pending',
        ]);
    }

    private function makeReadyDraft(Tenant $tenant): OnboardingDraft
    {
        return OnboardingDraft::create([
            'tenant_id' => $tenant->id,
            'version' => 1,
            'status' => 'ready_for_review',
            'proposed_content' => ['hero_headline' => 'Import Test Bakery'],
            'theme_id' => 'modern_bakery',
        ]);
    }

    public function test_import_creates_products_and_gallery_and_marks_tenant_complete()
    {
        $tenant = $this->makeTenant();
        $draft = $this->makeReadyDraft($tenant);
        $this->seedGalleryItem($draft, $tenant);
        $this->seedProductItem($draft, $tenant, ['name' => 'Chocolate Cake', 'price_min' => 45, 'price_max' => 60]);

        ImportDraftJob::dispatch($draft->id);

        $draft->refresh();
        $tenant->refresh();

        $this->assertSame('imported', $draft->status);
        $this->assertNotNull($draft->imported_at);
        $this->assertTrue((bool) $tenant->onboarding_completed);
        $this->assertSame('modern_bakery', $tenant->theme_id);
        $this->assertSame('Import Test Bakery', $tenant->site_content['hero_headline']);

        $this->assertSame(1, Product::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count());
        $this->assertSame(1, GalleryItem::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count());

        $gallery = GalleryItem::withoutGlobalScopes()->where('tenant_id', $tenant->id)->first();
        $this->assertFileExists(public_path($gallery->image_url));
    }

    /**
     * Regression: synthesis deliberately never sets hero_bg_url/promo_bg_image_url/
     * etc (Phase 5) — nothing resolved them to a real imported photo until now,
     * so every storefront background slot silently stayed blank even though
     * the baker uploaded plenty of usable images. Import must resolve them
     * from the actually-copied gallery files.
     */
    public function test_import_resolves_background_image_slots_from_real_gallery_photos()
    {
        $tenant = $this->makeTenant();
        $draft = $this->makeReadyDraft($tenant);
        $hero = $this->seedGalleryItem($draft, $tenant, ['is_hero' => true]);
        $this->seedGalleryItem($draft, $tenant, ['is_hero' => false]);

        ImportDraftJob::dispatch($draft->id);

        $tenant->refresh();
        $content = $tenant->site_content;

        $heroGallery = GalleryItem::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('is_hero', true)->first();
        $this->assertNotNull($heroGallery);
        $this->assertSame($heroGallery->image_url, $content['hero_bg_url']);

        foreach (['promo_bg_image_url', 'cta_bg_image_url', 'whimsical_image_url', 'cta_banner_url'] as $key) {
            $this->assertNotEmpty($content[$key] ?? null);
        }
    }

    public function test_exception_mid_transaction_leaves_zero_rows_and_zero_orphan_files()
    {
        $tenant = $this->makeTenant();
        $draft = $this->makeReadyDraft($tenant);
        $this->seedGalleryItem($draft, $tenant);
        $this->seedProductItem($draft, $tenant, ['name' => 'Chocolate Cake', 'price_min' => 45]);

        Product::creating(function () {
            throw new \RuntimeException('Simulated failure for test coverage.');
        });

        try {
            ImportDraftJob::dispatch($draft->id);
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Simulated failure', $e->getMessage());
        } finally {
            Product::flushEventListeners();
        }

        $draft->refresh();
        $this->assertSame('failed', $draft->status);
        $this->assertSame(0, Product::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count());
        $this->assertSame(0, GalleryItem::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count());

        // The gallery copy in phase B succeeded before the transaction ran —
        // it must have been cleaned up, not left as an orphan. Check the
        // exact path this run's manifest planned (tenant IDs can repeat
        // across tests since RefreshDatabase doesn't reset the filesystem).
        $plannedPath = $draft->import_manifest['gallery'][0]['dest_path'] ?? null;
        $this->assertNotNull($plannedPath);
        $this->assertFileDoesNotExist($plannedPath);
    }

    public function test_running_import_twice_is_a_no_op_second_time()
    {
        $tenant = $this->makeTenant();
        $draft = $this->makeReadyDraft($tenant);
        $this->seedGalleryItem($draft, $tenant);
        $this->seedProductItem($draft, $tenant, ['name' => 'Chocolate Cake', 'price_min' => 45]);

        ImportDraftJob::dispatch($draft->id);
        ImportDraftJob::dispatch($draft->id); // draft is now 'imported' — must be a no-op

        $this->assertSame(1, Product::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count());
        $this->assertSame(1, GalleryItem::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count());
    }

    public function test_existing_product_price_is_never_overwritten_and_no_duplicate_created()
    {
        $tenant = $this->makeTenant();

        Product::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Chocolate Cake',
            'slug' => 'chocolate-cake',
            'price' => 55,
            'price_min' => 55,
            'price_max' => 55,
            'is_active' => true,
            'source' => 'manual',
        ]);

        $draft = $this->makeReadyDraft($tenant);
        $this->seedProductItem($draft, $tenant, ['name' => 'Chocolate Cake', 'price_min' => 45, 'price_max' => 60, 'description' => 'AI-written description']);

        ImportDraftJob::dispatch($draft->id);

        $this->assertSame(1, Product::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count());
        $product = Product::withoutGlobalScopes()->where('tenant_id', $tenant->id)->first();
        $this->assertEquals(55, (float) $product->price_min); // the baker's real price survives
        $this->assertSame('AI-written description', $product->description); // description was empty — fill-only-empty still applies here
    }

    public function test_existing_gallery_image_hash_is_not_duplicated()
    {
        $tenant = $this->makeTenant();
        $draft = $this->makeReadyDraft($tenant);
        $item = $this->seedGalleryItem($draft, $tenant);

        GalleryItem::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'title' => 'Already here',
            'image_url' => 'uploads/tenants/' . $tenant->id . '/gallery/already-here.webp',
            'image_hash' => $item->sourceFile->content_hash,
            'is_visible' => true,
        ]);

        ImportDraftJob::dispatch($draft->id);

        $this->assertSame(1, GalleryItem::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count());
    }

    public function test_sweep_recovers_a_stuck_import_and_cleans_up_orphaned_files()
    {
        $tenant = $this->makeTenant();
        $draft = $this->makeReadyDraft($tenant);
        $draft->status = 'importing';
        $draft->updated_at = now()->subMinutes(30);

        $galleryDir = TenantMediaPath::galleryUploadDir($tenant->id);
        TenantMediaPath::ensureDir($galleryDir);
        $orphanPath = $galleryDir . '/' . Str::uuid() . '.webp';
        file_put_contents($orphanPath, 'orphaned-bytes');

        $draft->import_manifest = ['gallery' => [['action' => 'copy', 'dest_path' => $orphanPath]], 'products' => []];
        $draft->save();
        // updated_at is touched by save() — force the backdated timestamp back via a raw update.
        DB::table('onboarding_drafts')->where('id', $draft->id)->update(['updated_at' => now()->subMinutes(30)]);

        $this->artisan('onboarding:sweep-stuck-imports', ['--force' => true])->assertExitCode(Command::SUCCESS);

        $draft->refresh();
        $this->assertSame('ready_for_review', $draft->status);
        $this->assertFileDoesNotExist($orphanPath);
    }

    public function test_sweep_leaves_recently_updated_imports_alone()
    {
        $tenant = $this->makeTenant();
        $draft = $this->makeReadyDraft($tenant);
        $draft->status = 'importing';
        $draft->save(); // updated_at is "now" — well within the stuck window

        $this->artisan('onboarding:sweep-stuck-imports', ['--force' => true])->assertExitCode(Command::SUCCESS);

        $draft->refresh();
        $this->assertSame('importing', $draft->status);
    }

    /**
     * Phase 9's theme-bypass fix: ReviewPanel's picker previews what a
     * self-reported `selected_plan: pro` would unlock (see
     * Tenant::onboardingAvailableThemes()), but that self-report is never
     * proof of payment. Import must gate on the tenant's real plan_tier
     * only, falling back to a starter theme with the real choice stashed.
     */
    public function test_unpaid_tenant_picking_a_pro_theme_falls_back_to_starter_and_stashes_choice()
    {
        $tenant = $this->makeTenant(['plan_tier' => 'standard', 'theme_id' => 'rustic_kitchen']);
        $draft = OnboardingDraft::create([
            'tenant_id' => $tenant->id,
            'version' => 1,
            'status' => 'ready_for_review',
            'proposed_content' => ['hero_headline' => 'Test Bakery'],
            'theme_id' => 'sweet_elegant', // a Pro-only theme, picked while basics.selected_plan === 'pro' but never paid
        ]);

        ImportDraftJob::dispatch($draft->id);

        $tenant->refresh();
        $this->assertNotSame('sweet_elegant', $tenant->theme_id);
        $this->assertContains($tenant->theme_id, array_keys(Tenant::getStarterThemes()));
        $this->assertSame('sweet_elegant', $tenant->pending_pro_theme_id);
        $this->assertTrue((bool) $tenant->onboarding_completed); // import still completes — no deadlock
    }

    public function test_paid_pro_tenant_gets_their_chosen_theme_applied_directly()
    {
        $tenant = $this->makeTenant(['plan_tier' => 'pro', 'theme_id' => 'rustic_kitchen']);
        $draft = OnboardingDraft::create([
            'tenant_id' => $tenant->id,
            'version' => 1,
            'status' => 'ready_for_review',
            'proposed_content' => ['hero_headline' => 'Test Bakery'],
            'theme_id' => 'sweet_elegant',
        ]);

        ImportDraftJob::dispatch($draft->id);

        $tenant->refresh();
        $this->assertSame('sweet_elegant', $tenant->theme_id);
        $this->assertNull($tenant->pending_pro_theme_id);
    }
}
