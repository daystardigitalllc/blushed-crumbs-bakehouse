<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Covers the per-theme Blade template mechanism: Tenant::themeView() must
 * route a rustic_kitchen tenant to its own real template, fall back to
 * sweet_elegant for any theme without one (or bad data), and — the core
 * requirement — switching a tenant's theme_id must never drop content,
 * since every theme template reads the same site_content/getOrderedSections
 * contract.
 */
class ThemeViewResolutionTest extends TestCase
{
    use RefreshDatabase;

    protected function makeTenant(array $overrides = []): Tenant
    {
        return Tenant::create(array_merge([
            'name' => 'Theme Test Bakery',
            'domain' => 'theme-test.test',
            'subdomain' => 'themetest',
            'slug' => 'themetest',
            'owner_name' => 'Test Owner',
            'email' => 'themetest@example.test',
            'plan_tier' => 'pro',
            'theme_id' => 'sweet_elegant',
            'form_schema' => Tenant::getDefaultFormSchema(),
            'booking_settings' => ['lead_time_days' => 3, 'blackout_dates' => []],
            'is_active' => true,
        ], $overrides));
    }

    public function test_rustic_kitchen_tenant_renders_its_own_template()
    {
        $tenant = $this->makeTenant([
            'subdomain' => 'rustictest',
            'slug' => 'rustictest',
            'theme_id' => 'rustic_kitchen',
        ]);

        $response = $this->get('/site/rustictest');

        $response->assertStatus(200);
        $response->assertSee('rustic-hero-split', false);
        $response->assertDontSee('hero-cloud-elementor-top', false);
    }

    public function test_unknown_theme_id_falls_back_to_sweet_elegant_without_error()
    {
        $tenant = $this->makeTenant([
            'subdomain' => 'badtheme',
            'slug' => 'badtheme',
            'theme_id' => 'does_not_exist',
        ]);

        $response = $this->get('/site/badtheme');

        $response->assertStatus(200);
        $response->assertSee('hero-cloud-elementor-top', false);
        $response->assertDontSee('rustic-hero-split', false);
    }

    public function test_switching_theme_preserves_all_content()
    {
        $tenant = $this->makeTenant([
            'subdomain' => 'switchtest',
            'slug' => 'switchtest',
            'theme_id' => 'sweet_elegant',
            'site_content' => [
                'hero_headline' => 'Totally Unique Headline Text',
                'hero_subheading' => 'A Very Specific Subheading',
                'reviews' => [
                    ['name' => 'Distinctive Reviewer Name', 'quote' => 'An extremely specific review quote.'],
                ],
            ],
        ]);

        $before = $this->get('/site/switchtest');
        $before->assertStatus(200);
        $before->assertSee('Totally Unique Headline Text');
        $before->assertSee('A Very Specific Subheading');
        $before->assertSee('Distinctive Reviewer Name');
        $before->assertSee('An extremely specific review quote.');

        $tenant->update(['theme_id' => 'rustic_kitchen']);

        $after = $this->get('/site/switchtest');
        $after->assertStatus(200);
        $after->assertSee('rustic-hero-split', false);
        $after->assertSee('Totally Unique Headline Text');
        $after->assertSee('A Very Specific Subheading');
        $after->assertSee('Distinctive Reviewer Name');
        $after->assertSee('An extremely specific review quote.');
    }

    public function test_rustic_kitchen_about_menu_gallery_pages_load()
    {
        $this->makeTenant([
            'subdomain' => 'rusticpages',
            'slug' => 'rusticpages',
            'theme_id' => 'rustic_kitchen',
        ]);

        $this->get('/site/rusticpages/about')->assertStatus(200);
        $this->get('/site/rusticpages/menu')->assertStatus(200);
        $this->get('/site/rusticpages/gallery')->assertStatus(200);
    }
}
