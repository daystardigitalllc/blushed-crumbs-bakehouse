<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewThemeRenderTest extends TestCase
{
    use RefreshDatabase;

    private function tenantWithTheme(string $themeId, string $planTier): Tenant
    {
        return Tenant::create([
            'name' => 'Theme Check Bakery',
            'owner_name' => 'Tester',
            'email' => "theme-check-{$themeId}-{$planTier}@example.com",
            'slug' => "theme-check-{$themeId}-{$planTier}",
            'subdomain' => "theme-check-{$themeId}-{$planTier}",
            'plan_tier' => $planTier,
            'theme_id' => $themeId,
            'form_schema' => Tenant::getDefaultFormSchema(),
            'booking_settings' => ['lead_time_days' => 3, 'blackout_dates' => []],
            'is_active' => true,
        ]);
    }

    public function test_new_themes_are_registered_as_starter_tier(): void
    {
        $starter = array_keys(Tenant::getStarterThemes());
        $this->assertContains('sage_sourdough', $starter);
        $this->assertContains('cherry_bakeshop', $starter);
    }

    /** @dataProvider themeProvider */
    public function test_storefront_pages_render_for_new_theme(string $themeId): void
    {
        $tenant = $this->tenantWithTheme($themeId, 'free');

        foreach (['/', '/about', '/menu', '/gallery', '/policy'] as $path) {
            $response = $this->get("{$path}?bakery={$tenant->subdomain}");
            $response->assertStatus(200);
            $response->assertSee("theme-{$themeId}", false);
        }
    }

    /** @dataProvider themeProvider */
    public function test_admin_dashboard_renders_for_new_theme_on_free_and_pro(string $themeId): void
    {
        foreach (['free', 'pro'] as $planTier) {
            $tenant = $this->tenantWithTheme($themeId, $planTier);
            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => 'Tester',
                'email' => "theme-check-user-{$themeId}-{$planTier}@example.com",
                'password' => bcrypt('password'),
                'role' => 'owner',
                'email_verified_at' => now(),
            ]);

            $response = $this->actingAs($user)->get("/site/{$tenant->subdomain}/dashboard");
            $response->assertStatus(200);
            $response->assertSee("theme-{$themeId}", false);
        }
    }

    public static function themeProvider(): array
    {
        return [
            'sage_sourdough' => ['sage_sourdough'],
            'cherry_bakeshop' => ['cherry_bakeshop'],
        ];
    }
}
