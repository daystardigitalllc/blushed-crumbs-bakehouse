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
        $tenant = Tenant::first();
        $response = $this->get("/?bakery={$tenant->subdomain}");
        $response->assertStatus(200);
        $response->assertSee('Blushed Crumbs Bakehouse');
    }

    public function test_admin_dashboard_loads_successfully()
    {
        $tenant = Tenant::first();
        $user = \App\Models\User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Austin Hayes',
            'email' => 'austin@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get("/site/{$tenant->subdomain}/dashboard");
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
        $tenant = Tenant::first();
        $user = \App\Models\User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Austin Hayes',
            'email' => 'austineditor@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->postJson("/dashboard/sections", [
            'hero_headline' => 'Nashville Premium Custom Cakes',
            'hero_subheading' => 'Artisanal Bakery & Desserts',
            'about_title' => 'Our Artisan Story',
            'about_bio' => 'Crafting luxury wedding & birthday cakes in Tennessee.',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $tenant = Tenant::first();
        $this->assertEquals('Nashville Premium Custom Cakes', $tenant->fresh()->getSiteContent('hero_headline'));
        $this->assertEquals('Our Artisan Story', $tenant->fresh()->getSiteContent('about_title'));

        $homeResponse = $this->get("/?bakery={$tenant->subdomain}");
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('Nashville Premium Custom Cakes');
    }

    public function test_admin_section_manager_toggles_and_reorders_sections()
    {
        $tenant = Tenant::first();
        $user = \App\Models\User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Austin Hayes',
            'email' => 'austinsections@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->postJson("/dashboard/sections", [
            'sections' => [
                'hero' => ['enabled' => true, 'order' => 2],
                'promo_video' => ['enabled' => false, 'order' => 1],
                'how_it_works' => ['enabled' => true, 'order' => 3],
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $tenant = Tenant::first();
        $ordered = $tenant->fresh()->getOrderedSections();
        $this->assertFalse($ordered['promo_video']['enabled']);
    }

    public function test_save_order_form_design()
    {
        $tenant = Tenant::first();
        $user = \App\Models\User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Austin Hayes',
            'email' => 'austindesign@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->postJson("/dashboard/form-builder/design", [
            'colors' => [
                'modal_bg' => '#112233',
                'heading' => '#445566',
                'text' => '#778899',
                'accent' => '#aabbcc',
                'btn_bg' => '#ddeeff',
                'btn_text' => '#001122',
            ],
            'typography' => [
                'font_family' => 'Poppins',
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $tenant = $tenant->fresh();
        $colors = $tenant->orderFormColors();
        $typo = $tenant->orderFormTypography();

        $this->assertEquals('#112233', $colors['modal_bg']);
        $this->assertEquals('Poppins', $typo['font_family']);
    }
}
