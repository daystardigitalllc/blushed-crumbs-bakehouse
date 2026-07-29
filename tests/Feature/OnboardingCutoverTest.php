<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Phase 10 cutover: v2 is the default for brand-new signups, but existing
 * tenants who started on the legacy wizard (onboarding_flow_version still
 * 'v1', the column's DB default) keep finishing there rather than being
 * switched mid-flow.
 */
class OnboardingCutoverTest extends TestCase
{
    use RefreshDatabase;

    public function test_fresh_signup_gets_v2_flow_version_and_lands_on_v2_wizard()
    {
        $response = $this->post('/register', [
            'owner_name' => 'Test Owner',
            'email' => Str::random(8) . '@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'bakery_name' => 'Cutover Test Bakery',
        ]);

        $response->assertRedirect(route('onboarding.v2.wizard'));

        $tenant = Tenant::where('name', 'Cutover Test Bakery')->firstOrFail();
        $this->assertSame('v2', $tenant->onboarding_flow_version);
        $this->assertNotNull($tenant->onboarding_started_at);
    }

    public function test_existing_v1_tenant_still_lands_on_legacy_wizard()
    {
        $tenant = Tenant::create([
            'name' => 'Legacy Test Bakery',
            'slug' => 'legacy-test-' . Str::random(8),
            'owner_name' => 'Test Owner',
            'email' => Str::random(8) . '@example.com',
            'plan_tier' => 'free',
            'theme_id' => 'rustic_kitchen',
            'is_active' => true,
            'onboarding_completed' => false,
            // onboarding_flow_version left unset — defaults to 'v1' at the DB level
        ]);
        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Test Owner',
            'email' => Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);

        $this->assertSame('v1', $tenant->fresh()->onboarding_flow_version);

        $this->actingAs($user)
            ->get('/login')
            ->assertRedirect('/onboarding');
    }

    public function test_v2_tenant_lands_on_v2_wizard_on_login()
    {
        $tenant = Tenant::create([
            'name' => 'V2 Test Bakery',
            'slug' => 'v2-test-' . Str::random(8),
            'owner_name' => 'Test Owner',
            'email' => Str::random(8) . '@example.com',
            'plan_tier' => 'free',
            'theme_id' => 'rustic_kitchen',
            'is_active' => true,
            'onboarding_completed' => false,
            'onboarding_flow_version' => 'v2',
        ]);
        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Test Owner',
            'email' => Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);

        $this->actingAs($user)
            ->get('/login')
            ->assertRedirect(route('onboarding.v2.wizard'));
    }

    public function test_completed_tenant_lands_on_dashboard_regardless_of_flow_version()
    {
        $tenant = Tenant::create([
            'name' => 'Done Test Bakery',
            'slug' => 'done-test-' . Str::random(8),
            'subdomain' => 'done-test-' . Str::random(8),
            'owner_name' => 'Test Owner',
            'email' => Str::random(8) . '@example.com',
            'plan_tier' => 'free',
            'theme_id' => 'rustic_kitchen',
            'is_active' => true,
            'onboarding_completed' => true,
            'onboarding_flow_version' => 'v2',
        ]);
        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Test Owner',
            'email' => Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);

        $this->actingAs($user)
            ->get('/login')
            ->assertRedirect('/dashboard');
    }
}
