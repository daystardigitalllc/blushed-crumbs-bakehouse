<?php

namespace Tests\Feature;

use App\Models\Onboarding\AiExtractionCache;
use App\Models\Onboarding\OnboardingDraft;
use App\Models\Onboarding\OnboardingFile;
use App\Models\Tenant;
use App\Services\Onboarding\Extraction\GeminiExtractionService;
use App\Services\Onboarding\TenantMediaPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * GeminiExtractionService never makes a real network call in this suite —
 * Http::fake() stands in for the Gemini API. Covers Phase 4's verification
 * bullets that don't require a live key/staging tenant: cache hits skip the
 * API entirely, the per-draft AI budget falls back to local-only beyond the
 * cap, and an unparseable response degrades gracefully after the one
 * repair-retry attempt. Also asserts config('services.gemini.key') absence
 * (the real state of this test/local environment) never causes an
 * exception — every file still gets a terminal, importable result.
 */
class GeminiExtractionServiceTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private OnboardingDraft $draft;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Gemini Test Bakery',
            'slug' => 'gemini-test-bakery-' . Str::random(8),
            'domain' => Str::random(8) . '.test',
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

    private function seedImageFileWithDerivative(): OnboardingFile
    {
        $file = OnboardingFile::create([
            'draft_id' => $this->draft->id,
            'tenant_id' => $this->tenant->id,
            'original_filename' => 'cake.jpg',
            'kind' => 'image',
            'path' => TenantMediaPath::draftOriginalsDir($this->tenant->id, $this->draft->id) . '/abc.jpg',
            'mime_type' => 'image/jpeg',
            'content_hash' => (string) Str::uuid(),
            'status' => 'extracting',
            'batch_id' => 'test-batch',
            'claimed_at' => now(),
        ]);

        $aiDir = TenantMediaPath::draftDerivativeDir($this->tenant->id, $this->draft->id, 'ai');
        TenantMediaPath::ensureDir($aiDir);
        file_put_contents($aiDir . '/abc.jpg', 'fake-jpeg-bytes');

        return $file;
    }

    public function test_no_api_key_falls_back_to_local_only_for_every_file()
    {
        config(['services.gemini.key' => '']);

        $file = $this->seedImageFileWithDerivative();

        $service = app(GeminiExtractionService::class);
        $results = $service->extractBatch(OnboardingFile::where('id', $file->id)->get());

        $this->assertTrue($results[$file->id]['ok']);
        $this->assertSame('local_only', $results[$file->id]['result']['source']);
        $this->assertSame(0, AiExtractionCache::count()); // never cached — no real analysis happened
    }

    public function test_successful_response_is_applied_and_cached()
    {
        config(['services.gemini.key' => 'test-key']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => ['parts' => [[
                        'text' => json_encode([[
                            'content_type' => 'product',
                            'alt_text' => 'A three-tier white wedding cake with sugar flowers',
                            'labels' => ['cake', 'wedding'],
                            'product_name' => 'Wedding Cake',
                            'price' => 250,
                        ]]),
                    ]]],
                ]],
                'usageMetadata' => ['totalTokenCount' => 123],
            ], 200),
        ]);

        $file = $this->seedImageFileWithDerivative();

        $service = app(GeminiExtractionService::class);
        $results = $service->extractBatch(OnboardingFile::where('id', $file->id)->get());

        $result = $results[$file->id];
        $this->assertTrue($result['ok']);
        $this->assertStringContainsString('wedding cake', strtolower($result['alt_text']));
        $this->assertSame('gemini', $result['result']['source']);
        $this->assertSame('Wedding Cake', $result['result']['product_name']);

        $this->assertSame(1, AiExtractionCache::where('tenant_id', $this->tenant->id)->count());

        Http::assertSentCount(1);
    }

    public function test_cache_hit_skips_the_api_call_entirely()
    {
        config(['services.gemini.key' => 'test-key']);

        $file = $this->seedImageFileWithDerivative();

        AiExtractionCache::create([
            'cache_key' => hash('sha256', "{$this->tenant->id}|{$file->content_hash}|"
                . config('services.gemini.extraction_model') . '|' . config('onboarding.ai_prompt_version') . '|extract_image'),
            'tenant_id' => $this->tenant->id,
            'content_hash' => $file->content_hash,
            'model' => config('services.gemini.extraction_model'),
            'prompt_version' => config('onboarding.ai_prompt_version'),
            'task' => 'extract_image',
            'result' => ['alt_text' => 'Cached alt text', 'labels' => ['cake'], 'result' => ['source' => 'gemini']],
        ]);

        Http::fake(); // any real call would fail loudly since nothing is stubbed

        $service = app(GeminiExtractionService::class);
        $results = $service->extractBatch(OnboardingFile::where('id', $file->id)->get());

        $this->assertSame('Cached alt text', $results[$file->id]['alt_text']);
        Http::assertNothingSent();
    }

    public function test_ai_budget_cap_falls_back_to_local_only_beyond_the_cap()
    {
        config(['services.gemini.key' => 'test-key', 'onboarding.ai_max_images_per_draft' => 2]);

        // Two files already "used" their AI budget in a prior batch.
        for ($i = 0; $i < 2; $i++) {
            OnboardingFile::create([
                'draft_id' => $this->draft->id,
                'tenant_id' => $this->tenant->id,
                'original_filename' => "prior-{$i}.jpg",
                'kind' => 'image',
                'path' => "/tmp/prior-{$i}.jpg",
                'mime_type' => 'image/jpeg',
                'content_hash' => (string) Str::uuid(),
                'status' => 'extracted',
                'ai_result' => ['source' => 'gemini'],
            ]);
        }

        Http::fake(); // over budget — must never be called

        $file = $this->seedImageFileWithDerivative();
        $service = app(GeminiExtractionService::class);
        $results = $service->extractBatch(OnboardingFile::where('id', $file->id)->get());

        $this->assertTrue($results[$file->id]['ok']);
        $this->assertSame('local_only', $results[$file->id]['result']['source']);
        Http::assertNothingSent();
    }

    public function test_unparseable_response_triggers_one_repair_retry_then_falls_back()
    {
        config(['services.gemini.key' => 'test-key']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::sequence()
                ->push(['candidates' => [['content' => ['parts' => [['text' => 'not valid json at all']]]]]], 200)
                ->push(['candidates' => [['content' => ['parts' => [['text' => 'still not valid']]]]]], 200),
        ]);

        $file = $this->seedImageFileWithDerivative();
        $service = app(GeminiExtractionService::class);
        $results = $service->extractBatch(OnboardingFile::where('id', $file->id)->get());

        $this->assertTrue($results[$file->id]['ok']);
        $this->assertSame('local_only', $results[$file->id]['result']['source']);
        Http::assertSentCount(2); // original attempt + exactly one repair retry
        $this->assertSame(0, AiExtractionCache::count());
    }

    public function test_pdf_batch_stores_line_items_under_the_single_file()
    {
        config(['services.gemini.key' => 'test-key']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => ['parts' => [[
                        'text' => json_encode([
                            ['name' => 'Chocolate Croissant', 'price_min' => 5, 'price_max' => 5],
                            ['name' => 'Sourdough Loaf', 'price_min' => 8, 'price_max' => 10],
                        ]),
                    ]]],
                ]],
            ], 200),
        ]);

        $pdfPath = sys_get_temp_dir() . '/menu-' . Str::random(8) . '.pdf';
        file_put_contents($pdfPath, '%PDF-1.4 fake');

        $file = OnboardingFile::create([
            'draft_id' => $this->draft->id,
            'tenant_id' => $this->tenant->id,
            'original_filename' => 'menu.pdf',
            'kind' => 'pdf',
            'path' => $pdfPath,
            'mime_type' => 'application/pdf',
            'content_hash' => (string) Str::uuid(),
            'status' => 'extracting',
            'batch_id' => 'pdf-batch',
            'claimed_at' => now(),
        ]);

        $service = app(GeminiExtractionService::class);
        $results = $service->extractBatch(OnboardingFile::where('id', $file->id)->get());

        $this->assertTrue($results[$file->id]['ok']);
        $this->assertCount(2, $results[$file->id]['result']['items']);
        $this->assertSame('Chocolate Croissant', $results[$file->id]['result']['items'][0]['name']);

        @unlink($pdfPath);
    }
}
