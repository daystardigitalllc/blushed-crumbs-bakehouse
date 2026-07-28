<?php

namespace Tests\Feature;

use App\Livewire\OnboardingLivewireCheck;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Phase 7's whole purpose: prove Livewire actually works in this app
 * (composer install, package discovery, a live component round-trip)
 * before Phase 8 commits to it for the real wizard.
 */
class OnboardingLivewireCheckTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role): User
    {
        $tenant = Tenant::create([
            'name' => 'Livewire Check Bakery',
            'slug' => 'lw-check-' . Str::random(8),
            'owner_name' => 'Test Owner',
            'email' => Str::random(8) . '@example.com',
            'is_active' => true,
        ]);

        return User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Test User',
            'email' => Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => $role,
        ]);
    }

    public function test_super_admin_can_load_the_page_and_increment_the_counter()
    {
        $admin = $this->makeUser('superadmin');

        $this->actingAs($admin)
            ->get(route('superadmin.onboarding-livewire-check'))
            ->assertStatus(200)
            ->assertSee('Livewire dependency check');

        Livewire::actingAs($admin)
            ->test(OnboardingLivewireCheck::class)
            ->assertSet('count', 0)
            ->call('increment')
            ->assertSet('count', 1)
            ->call('increment')
            ->assertSet('count', 2);
    }

    public function test_non_super_admin_is_redirected_away()
    {
        $baker = $this->makeUser('owner');

        $this->actingAs($baker)
            ->get(route('superadmin.onboarding-livewire-check'))
            ->assertRedirect('/dashboard');
    }
}
