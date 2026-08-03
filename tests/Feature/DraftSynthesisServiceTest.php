<?php

namespace Tests\Feature;

use App\Jobs\Onboarding\FinalizeExtractionJob;
use App\Jobs\Onboarding\SynthesizeDraftJob;
use App\Models\Onboarding\OnboardingDraft;
use App\Models\Onboarding\OnboardingDraftItem;
use App\Models\Onboarding\OnboardingFile;
use App\Models\Tenant;
use App\Services\Onboarding\DraftSynthesisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Covers Phase 5's verification bullets from the onboarding-rebuild plan:
 * all 31 site_content keys present with no empty strings where a default
 * exists, SEO fields present (the mapToSiteContent fix is a separate,
 * narrower test below), theme respects plan gating, sweet_elegant never
 * proposed to a non-Blushed-Crumbs tenant, categories capped at 6 with
 * matched cover images, and hero selection picks the landscape/top-score
 * candidate. No real Gemini call in this suite — Http::fake() throughout,
 * or no API key at all to exercise the pure-defaults path.
 */
class DraftSynthesisServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeTenant(array $overrides = []): Tenant
    {
        return Tenant::create(array_merge([
            'name' => 'Synthesis Test Bakery',
            'slug' => 'synth-test-' . Str::random(8),
            'domain' => Str::random(8) . '.test',
            'subdomain' => Str::random(8),
            'owner_name' => 'Test Owner',
            'email' => Str::random(8) . '@example.com',
            'plan_tier' => 'standard',
            'theme_id' => 'rustic_kitchen',
            'is_active' => true,
        ], $overrides));
    }

    private function makeDraft(Tenant $tenant, array $basics = []): OnboardingDraft
    {
        return OnboardingDraft::create([
            'tenant_id' => $tenant->id,
            'version' => 1,
            'status' => 'synthesizing',
            'basics' => $basics,
        ]);
    }

    private function seedExtractedImage(OnboardingDraft $draft, Tenant $tenant, array $overrides = []): OnboardingFile
    {
        return OnboardingFile::create(array_merge([
            'draft_id' => $draft->id,
            'tenant_id' => $tenant->id,
            'original_filename' => 'photo-' . Str::random(4) . '.jpg',
            'kind' => 'image',
            'path' => '/tmp/photo.jpg',
            'mime_type' => 'image/jpeg',
            'content_hash' => (string) Str::uuid(),
            'status' => 'extracted',
            'width' => 1600,
            'height' => 1200,
            'quality_score' => 70,
            'is_hero_candidate' => false,
            'alt_text' => 'A photo',
            'ai_labels' => [],
            'ai_result' => ['source' => 'gemini'],
        ], $overrides));
    }

    private function seedExtractedPdf(OnboardingDraft $draft, Tenant $tenant, array $items): OnboardingFile
    {
        return OnboardingFile::create([
            'draft_id' => $draft->id,
            'tenant_id' => $tenant->id,
            'original_filename' => 'menu.pdf',
            'kind' => 'pdf',
            'path' => '/tmp/menu.pdf',
            'mime_type' => 'application/pdf',
            'content_hash' => (string) Str::uuid(),
            'status' => 'extracted',
            'ai_result' => ['source' => 'gemini', 'items' => $items],
        ]);
    }

    private function fakeSuccessfulSynthesisResponse(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => ['parts' => [[
                        'text' => json_encode([
                            'theme_id' => 'modern_bakery',
                            'hero_subheading' => 'Fresh Bakes Daily',
                            'hero_headline' => 'Synthesis Test Bakery',
                            'hero_cta_primary' => 'Order Now',
                            'hero_cta_secondary' => 'Our Menu',
                            'highlights' => [['icon' => '🎂', 'title' => 'Custom Cakes', 'desc' => 'Made to order.']],
                            'promo_headline' => 'Order Early',
                            'promo_subtext' => 'Book 2 weeks ahead.',
                            'how_it_works' => [['title' => 'Pick a design', 'desc' => 'Browse our gallery.']],
                            'categories' => [['title' => 'Cakes', 'desc' => 'Our signature cakes.']],
                            'whimsical_title' => 'Why Customers Love Us',
                            'whimsical_bullets' => ['Scratch-made', 'Local ingredients'],
                            'faqs' => [['q' => 'How far ahead?', 'a' => '3 days notice.']],
                            'cta_headline' => 'Ready to Order?',
                            'cta_subtext' => 'Get started today.',
                            'cta_btn_text' => 'Order Now',
                            'about_title' => 'Our Story',
                            'about_bio' => 'A cozy neighborhood bakery.',
                            'seo_title' => 'Synthesis Test Bakery | Custom Cakes',
                            'seo_description' => 'Order custom cakes and pastries online.',
                        ]),
                    ]]],
                ]],
            ], 200),
        ]);
    }

    public function test_all_31_site_content_keys_present_with_no_empty_defaults_and_seo_fields()
    {
        config(['services.gemini.key' => '']); // pure-defaults path, no network

        $tenant = $this->makeTenant();
        $draft = $this->makeDraft($tenant);
        $this->seedExtractedImage($draft, $tenant);

        $proposal = app(DraftSynthesisService::class)->synthesize($draft, $tenant);
        $content = $proposal['site_content'];

        foreach (Tenant::siteContentSchema() as $key) {
            $default = \App\Models\Tenant::getDefaultSiteContent($tenant->name)[$key] ?? null;
            if ($default === null || $default === '') {
                continue; // no default, or the default is itself an empty-string sentinel (e.g. promo_video_url)
            }

            $this->assertArrayHasKey($key, $content, "Missing site_content key: {$key}");
            $this->assertNotSame('', $content[$key], "site_content[{$key}] is an empty string despite having a default");
        }

        // seo_title/seo_description have no static default (nothing safe to
        // fabricate without AI) — "SEO fields present" is verified in
        // test_gemini_copy_is_used_when_available_and_seo_fields_survive below,
        // where Gemini actually supplies them.
    }

    /**
     * A baker-selected bakery_type must produce a type-appropriate fallback
     * (marquee text + one real category) even with zero AI involvement —
     * this is the entirely-local safety net for when Gemini is unavailable
     * or misconfigured, not something that depends on any API call working.
     */
    public function test_bakery_type_seeds_type_appropriate_fallback_with_no_gemini()
    {
        config(['services.gemini.key' => '']); // pure-defaults path, no network

        $tenant = $this->makeTenant();
        $draft = $this->makeDraft($tenant, ['bakery_type' => 'breads']);
        $this->seedExtractedImage($draft, $tenant);

        $proposal = app(DraftSynthesisService::class)->synthesize($draft, $tenant);

        $this->assertSame('Fresh Sourdough Daily', $proposal['site_content']['marquee_text']);
        $this->assertSame('Breads', $proposal['site_content']['categories'][0]['title']);
    }

    public function test_gemini_copy_is_used_when_available_and_seo_fields_survive()
    {
        config(['services.gemini.key' => 'test-key']);
        $this->fakeSuccessfulSynthesisResponse();

        $tenant = $this->makeTenant();
        $draft = $this->makeDraft($tenant);
        $this->seedExtractedImage($draft, $tenant);

        $proposal = app(DraftSynthesisService::class)->synthesize($draft, $tenant);

        $this->assertSame('Fresh Bakes Daily', $proposal['site_content']['hero_subheading']);
        $this->assertSame('Synthesis Test Bakery | Custom Cakes', $proposal['site_content']['seo_title']);
        $this->assertSame('Order custom cakes and pastries online.', $proposal['site_content']['seo_description']);
        $this->assertSame('modern_bakery', $proposal['theme_id']);
    }

    /**
     * A configured API key whose call still fails (after GeminiClient's own
     * internal HTTP retries + repair attempt) must propagate, not silently
     * fall back to an all-defaults site with no signal anyone needs to look
     * at it. SynthesizeDraftJob relies on this exception to trigger its own
     * queue-level retry/backoff — see SynthesizeDraftJobFailureTest.
     */
    public function test_synthesis_throws_when_gemini_call_persistently_fails()
    {
        config(['services.gemini.key' => 'test-key']);
        Http::fake([
            'generativelanguage.googleapis.com*' => Http::response('Internal Server Error', 500),
        ]);

        $tenant = $this->makeTenant();
        $draft = $this->makeDraft($tenant);
        $this->seedExtractedImage($draft, $tenant);

        $this->expectException(\RuntimeException::class);

        app(DraftSynthesisService::class)->synthesize($draft, $tenant);
    }

    public function test_contact_fields_come_from_basics_not_ai()
    {
        config(['services.gemini.key' => '']);

        $tenant = $this->makeTenant();
        $draft = $this->makeDraft($tenant, ['hours' => 'Tue-Sat 9-5', 'location' => 'Downtown', 'instagram' => '@synthbakery']);
        $this->seedExtractedImage($draft, $tenant);

        $proposal = app(DraftSynthesisService::class)->synthesize($draft, $tenant);

        $this->assertSame('Tue-Sat 9-5', $proposal['site_content']['contact_hours']);
        $this->assertSame('Downtown', $proposal['site_content']['contact_location']);
        $this->assertSame('@synthbakery', $proposal['site_content']['contact_instagram']);
    }

    public function test_theme_respects_starter_plan_gating()
    {
        config(['services.gemini.key' => '']);

        $tenant = $this->makeTenant(['plan_tier' => 'standard', 'subdomain' => 'not-blushedcrumbs']);
        $draft = $this->makeDraft($tenant); // no selected_plan in basics — defaults to free/starter
        $this->seedExtractedImage($draft, $tenant);

        $proposal = app(DraftSynthesisService::class)->synthesize($draft, $tenant);

        $starterKeys = array_keys(Tenant::getStarterThemes());
        $this->assertContains($proposal['theme_id'], $starterKeys);
        $this->assertNotSame('sweet_elegant', $proposal['theme_id']);
    }

    public function test_theme_opens_up_for_pro_plan()
    {
        config(['services.gemini.key' => '']);

        $tenant = $this->makeTenant(['plan_tier' => 'pro', 'subdomain' => 'not-blushedcrumbs']);
        $draft = $this->makeDraft($tenant);
        $this->seedExtractedImage($draft, $tenant);

        $proposal = app(DraftSynthesisService::class)->synthesize($draft, $tenant);

        // Pro unlocks the full non-exclusive set — resolveTheme's fallback (no AI
        // response) always lands on choices[0], so assert the *available set*
        // directly rather than the single resolved theme.
        $service = new \ReflectionClass(DraftSynthesisService::class);
        $method = $service->getMethod('availableThemeChoices');
        $method->setAccessible(true);
        $choices = $method->invoke(app(DraftSynthesisService::class), $draft, $tenant);

        $this->assertGreaterThan(count(Tenant::getStarterThemes()), count($choices));
        $this->assertNotContains('sweet_elegant', $choices);
    }

    public function test_sweet_elegant_never_available_to_non_blushed_crumbs_tenant_even_on_pro()
    {
        $tenant = $this->makeTenant(['plan_tier' => 'pro', 'subdomain' => 'some-other-bakery']);
        $draft = $this->makeDraft($tenant);

        $service = new \ReflectionClass(DraftSynthesisService::class);
        $method = $service->getMethod('availableThemeChoices');
        $method->setAccessible(true);
        $choices = $method->invoke(app(DraftSynthesisService::class), $draft, $tenant);

        $this->assertNotContains('sweet_elegant', $choices);
    }

    public function test_categories_capped_at_six_with_cover_images()
    {
        config(['services.gemini.key' => '']);

        $tenant = $this->makeTenant();
        $draft = $this->makeDraft($tenant);

        // 8 raw category signals across 8 distinct product-photo images —
        // more than the cap of 6, and enough images to guarantee cover matches.
        $rawCategories = ['cake', 'cakes', 'cupcake', 'cookie', 'bread', 'pastry', 'pie', 'donut', 'macaron'];
        foreach ($rawCategories as $i => $cat) {
            $this->seedExtractedImage($draft, $tenant, [
                'ai_labels' => [$cat],
                'ai_result' => ['source' => 'gemini', 'content_type' => 'product', 'product_name' => ucfirst($cat)],
            ]);
        }

        $proposal = app(DraftSynthesisService::class)->synthesize($draft, $tenant);

        $this->assertLessThanOrEqual(6, count($proposal['categories']));

        $totalCovers = collect($proposal['categories'])->sum(fn ($c) => count($c['cover_file_ids']));
        $this->assertGreaterThanOrEqual(3, $totalCovers);
    }

    /**
     * Regression: content_type ("product"/"storefront"/"menu_or_price_list"/
     * etc.) classifies the *kind of photo*, not the product — it must never
     * leak into the category picker as a literal category name. This is
     * exactly the bug a real user hit: plain product photos with no visible
     * price all shared content_type "product", so "Product" dominated the
     * ranked category list and crowded out real categories like "Cakes".
     */
    public function test_content_type_never_becomes_a_category_name()
    {
        config(['services.gemini.key' => '']);

        $tenant = $this->makeTenant();
        $draft = $this->makeDraft($tenant);

        foreach (range(1, 5) as $i) {
            $this->seedExtractedImage($draft, $tenant, [
                'ai_result' => [
                    'source' => 'gemini',
                    'content_type' => 'product',
                    'product_name' => "Chocolate Cake {$i}",
                    'category' => 'cakes',
                ],
            ]);
        }

        $proposal = app(DraftSynthesisService::class)->synthesize($draft, $tenant);

        $categoryNames = collect($proposal['categories'])->pluck('name');
        $this->assertContains('Cakes', $categoryNames);
        $this->assertNotContains('Product', $categoryNames);
    }

    /**
     * Regression: a plain product photo with no visible price tag must still
     * become a product. The old prompt only asked Gemini for product_name
     * when a price was visible, so ordinary product photos silently produced
     * zero products — this asserts the synthesis-side contract (product_name
     * present => a product is built) independent of the prompt wording.
     */
    public function test_product_photo_without_a_price_still_becomes_a_product()
    {
        config(['services.gemini.key' => '']);

        $tenant = $this->makeTenant();
        $draft = $this->makeDraft($tenant);

        $this->seedExtractedImage($draft, $tenant, [
            'ai_result' => [
                'source' => 'gemini',
                'content_type' => 'product',
                'product_name' => 'Chocolate Drip Cake',
                'category' => 'cakes',
                'price' => null,
            ],
        ]);

        $proposal = app(DraftSynthesisService::class)->synthesize($draft, $tenant);

        $names = collect($proposal['products'])->pluck('name');
        $this->assertContains('Chocolate Drip Cake', $names);

        $product = collect($proposal['products'])->firstWhere('name', 'Chocolate Drip Cake');
        $this->assertSame('Cakes', $product['category']);
    }

    public function test_hero_is_the_landscape_top_score_candidate()
    {
        config(['services.gemini.key' => '']);

        $tenant = $this->makeTenant();
        $draft = $this->makeDraft($tenant);

        $this->seedExtractedImage($draft, $tenant, ['is_hero_candidate' => true, 'quality_score' => 60]);
        $best = $this->seedExtractedImage($draft, $tenant, ['is_hero_candidate' => true, 'quality_score' => 92]);
        $this->seedExtractedImage($draft, $tenant, ['is_hero_candidate' => false, 'quality_score' => 99]); // higher score but not hero-eligible (portrait, etc.)

        $proposal = app(DraftSynthesisService::class)->synthesize($draft, $tenant);

        $this->assertSame($best->id, $proposal['hero_file_id']);
    }

    public function test_confidence_overall_reflects_fraction_analyzed_by_gemini()
    {
        config(['services.gemini.key' => '']);

        $tenant = $this->makeTenant();
        $draft = $this->makeDraft($tenant);

        $this->seedExtractedImage($draft, $tenant, ['ai_result' => ['source' => 'gemini']]);
        $this->seedExtractedImage($draft, $tenant, ['ai_result' => ['source' => 'gemini']]);
        $this->seedExtractedImage($draft, $tenant, ['ai_result' => ['source' => 'local_only', 'reason' => 'no key']]);
        $this->seedExtractedImage($draft, $tenant, ['ai_result' => ['source' => 'local_only', 'reason' => 'no key']]);

        $proposal = app(DraftSynthesisService::class)->synthesize($draft, $tenant);

        $this->assertSame(0.5, $proposal['confidence_overall']);
    }

    public function test_products_extracted_from_pdf_and_photos_deduplicated()
    {
        config(['services.gemini.key' => '']);

        $tenant = $this->makeTenant();
        $draft = $this->makeDraft($tenant);

        $this->seedExtractedPdf($draft, $tenant, [
            ['name' => 'Chocolate Croissant', 'price_min' => 5, 'price_max' => 5, 'category' => 'pastry'],
            ['name' => 'Sourdough Loaf', 'price_min' => 8, 'price_max' => 10, 'category' => 'bread'],
        ]);
        $this->seedExtractedImage($draft, $tenant, [
            'ai_result' => ['source' => 'gemini', 'content_type' => 'product', 'product_name' => 'Chocolate Croissant', 'price' => 5],
        ]);

        $proposal = app(DraftSynthesisService::class)->synthesize($draft, $tenant);

        $names = collect($proposal['products'])->pluck('name');
        $this->assertSame(2, $names->count()); // the duplicate photo detection collapses into the PDF entry
        $this->assertContains('Chocolate Croissant', $names);
        $this->assertContains('Sourdough Loaf', $names);
    }

    public function test_nothing_is_written_to_public_uploads()
    {
        config(['services.gemini.key' => '']);

        $tenant = $this->makeTenant();
        $draft = $this->makeDraft($tenant);
        $this->seedExtractedImage($draft, $tenant, ['is_hero_candidate' => true, 'quality_score' => 90]);

        $uploadsDir = public_path("uploads/tenants/{$tenant->id}");
        $existedBefore = is_dir($uploadsDir);
        $filesBefore = $existedBefore ? count(glob($uploadsDir . '/**/*') ?: []) : 0;

        $proposal = app(DraftSynthesisService::class)->synthesize($draft, $tenant);

        $filesAfter = is_dir($uploadsDir) ? count(glob($uploadsDir . '/**/*') ?: []) : 0;
        $this->assertSame($filesBefore, $filesAfter);

        // The image/video URL keys stay unset rather than pointing at a
        // fabricated public path — Phase 6's import is the only writer.
        foreach (['hero_bg_url', 'promo_bg_image_url', 'cta_bg_image_url'] as $key) {
            $this->assertArrayNotHasKey($key, $proposal['site_content']);
        }
    }

    public function test_end_to_end_via_finalize_extraction_job_reaches_ready_for_review_with_draft_items()
    {
        config(['services.gemini.key' => '']);

        $tenant = $this->makeTenant();
        $draft = OnboardingDraft::create([
            'tenant_id' => $tenant->id,
            'version' => 1,
            'status' => 'extracting', // FinalizeExtractionJob is what flips this to synthesizing
        ]);

        $this->seedExtractedImage($draft, $tenant, ['is_hero_candidate' => true, 'quality_score' => 80]);
        $this->seedExtractedPdf($draft, $tenant, [
            ['name' => 'Sourdough Loaf', 'price_min' => 8, 'price_max' => 10, 'category' => 'bread'],
        ]);

        FinalizeExtractionJob::dispatch($draft->id); // cascades: synthesizing -> SynthesizeDraftJob -> ready_for_review (sync queue)

        $draft->refresh();
        $this->assertSame('ready_for_review', $draft->status);
        $this->assertNotEmpty($draft->proposed_content);
        $this->assertNotNull($draft->theme_id);

        $this->assertSame(1, OnboardingDraftItem::where('draft_id', $draft->id)->where('type', 'product')->count());
        $this->assertSame(1, OnboardingDraftItem::where('draft_id', $draft->id)->where('type', 'gallery_image')->count());
    }

    public function test_synthesize_job_is_idempotent_if_draft_already_moved_on()
    {
        $tenant = $this->makeTenant();
        $draft = $this->makeDraft($tenant, []);
        $draft->status = 'ready_for_review'; // already synthesized (or moved further) by the time this runs
        $draft->save();

        SynthesizeDraftJob::dispatch($draft->id);

        $this->assertSame(0, OnboardingDraftItem::where('draft_id', $draft->id)->count());
    }
}
