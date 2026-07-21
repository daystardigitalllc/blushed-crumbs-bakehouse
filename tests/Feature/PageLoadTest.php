<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PageLoadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Tenant::firstOrCreate(
            ['slug' => 'blushedcrumbs'],
            [
                'name' => 'Blushed Crumbs Bakehouse',
                'domain' => 'blushed-crumbs-bakehouse.test',
                'subdomain' => 'blushedcrumbs',
                'owner_name' => 'Austin Hayes',
                'email' => 'blushedcrumbs@daystardigital.co',
                'plan_tier' => 'pro',
                'theme_id' => 'sweet_elegant',
                'form_schema' => Tenant::getDefaultFormSchema(),
                'booking_settings' => ['lead_time_days' => 3, 'blackout_dates' => []],
                'is_active' => true,
            ]
        );
    }

    public function test_storefront_home_page_loads_successfully()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Blushed Crumbs Bakehouse');
    }

    public function test_admin_dashboard_loads_successfully()
    {
        $response = $this->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('Blushed Crumbs');
    }

    public function test_tenant_model_theme_defaults()
    {
        $tenant = Tenant::first();
        $this->assertEquals('sweet_elegant', $tenant->theme_id);
        $themes = Tenant::getAvailableThemes();
        $this->assertArrayHasKey('sweet_elegant', $themes);
        $this->assertArrayHasKey('rustic_kitchen', $themes);
        $this->assertArrayHasKey('modern_bakery', $themes);
        $this->assertArrayHasKey('playful_treats', $themes);
    }

    public function test_admin_content_editor_updates_site_content()
    {
        $response = $this->postJson('/admin/content', [
            'hero_headline' => 'Nashville Premium Custom Cakes',
            'hero_subheading' => 'Artisanal Bakery & Desserts',
            'about_title' => 'Our Artisan Story',
            'about_bio' => 'Crafting luxury wedding & birthday cakes in Tennessee.',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $tenant = Tenant::first();
        $this->assertEquals('Nashville Premium Custom Cakes', $tenant->getSiteContent('hero_headline'));
        $this->assertEquals('Our Artisan Story', $tenant->getSiteContent('about_title'));

        $homeResponse = $this->get('/');
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('Nashville Premium Custom Cakes');
    }
}
