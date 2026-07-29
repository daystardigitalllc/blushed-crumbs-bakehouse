<?php

namespace Tests\Feature\Onboarding;

use App\Models\Onboarding\OnboardingDraft;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * GET /onboarding/resume/{token} — requires BOTH the token AND a matching
 * login (Phase 9: the email could be forwarded, so the token alone must
 * never be sufficient). A missing, foreign, or expired draft all render the
 * same friendly page rather than leaking which case it was or 500ing.
 */
class OnboardingResumeTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(): User
    {
        $tenant = Tenant::create([
            'name' => 'Resume Test Bakery',
            'slug' => 'resume-test-' . Str::random(8),
            'owner_name' => 'Test Owner',
            'email' => Str::random(8) . '@example.com',
            'plan_tier' => 'standard',
            'theme_id' => 'rustic_kitchen',
            'is_active' => true,
        ]);

        return User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Test Owner',
            'email' => Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);
    }

    public function test_valid_token_and_owner_resumes_and_refreshes_activity_clock()
    {
        $user = $this->makeUser();
        $draft = OnboardingDraft::create([
            'tenant_id' => $user->tenant_id,
            'version' => 1,
            'status' => 'ready_for_review',
            'resume_token' => 'valid-token-123',
            'last_activity_at' => now()->subHours(10),
        ]);

        $this->actingAs($user)
            ->get(route('onboarding.resume', ['token' => 'valid-token-123']))
            ->assertRedirect(route('onboarding.v2.wizard', ['draft' => $draft->id]));

        $draft->refresh();
        $this->assertTrue($draft->last_activity_at->gt(now()->subMinute()));
    }

    public function test_unknown_token_shows_friendly_expired_page_not_500()
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->get(route('onboarding.resume', ['token' => 'no-such-token']))
            ->assertOk()
            ->assertSee('expired');
    }

    public function test_another_tenants_token_shows_friendly_expired_page()
    {
        $owner = $this->makeUser();
        $intruder = $this->makeUser();
        OnboardingDraft::create([
            'tenant_id' => $owner->tenant_id,
            'version' => 1,
            'status' => 'ready_for_review',
            'resume_token' => 'owner-token',
            'last_activity_at' => now(),
        ]);

        $this->actingAs($intruder)
            ->get(route('onboarding.resume', ['token' => 'owner-token']))
            ->assertOk()
            ->assertSee('expired');
    }

    public function test_incomplete_draft_past_the_ttl_shows_expired_even_if_row_still_exists()
    {
        $user = $this->makeUser();
        OnboardingDraft::create([
            'tenant_id' => $user->tenant_id,
            'version' => 1,
            'status' => 'ready_for_review',
            'resume_token' => 'stale-token',
            'last_activity_at' => now()->subHours(49), // past the 48h default TTL
        ]);

        $this->actingAs($user)
            ->get(route('onboarding.resume', ['token' => 'stale-token']))
            ->assertOk()
            ->assertSee('expired');
    }

    public function test_imported_draft_is_not_on_the_expiry_clock()
    {
        $user = $this->makeUser();
        $draft = OnboardingDraft::create([
            'tenant_id' => $user->tenant_id,
            'version' => 1,
            'status' => 'imported',
            'resume_token' => 'imported-token',
            'last_activity_at' => now()->subDays(30), // long past 48h, but 'imported' is exempt
            'imported_at' => now()->subDays(30),
        ]);

        $this->actingAs($user)
            ->get(route('onboarding.resume', ['token' => 'imported-token']))
            ->assertRedirect(route('onboarding.v2.wizard', ['draft' => $draft->id]));
    }
}
