<?php

namespace Tests\Feature\Onboarding;

use App\Mail\OnboardingResumeMail;
use App\Models\Onboarding\OnboardingDraft;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * onboarding:send-resume-reminders — the second/final "expires in 12 hours"
 * email, sent once per draft at 36h inactive while still unreviewed.
 */
class OnboardingSendResumeRemindersTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(): User
    {
        $tenant = Tenant::create([
            'name' => 'Reminder Test Bakery',
            'slug' => 'reminder-test-' . Str::random(8),
            'owner_name' => 'Test Owner',
            'email' => Str::random(8) . '@example.com',
            'plan_tier' => 'free',
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

    public function test_sends_reminder_for_unreviewed_draft_inactive_36h()
    {
        Mail::fake();
        $user = $this->makeUser();
        $draft = OnboardingDraft::create([
            'tenant_id' => $user->tenant_id,
            'version' => 1,
            'status' => 'ready_for_review',
            'last_activity_at' => now()->subHours(37),
        ]);

        $this->artisan('onboarding:send-resume-reminders')->assertSuccessful();

        Mail::assertQueued(OnboardingResumeMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email) && $mail->variant === 'reminder';
        });
        $this->assertNotNull($draft->fresh()->reminder_sent_at);
        $this->assertNotNull($draft->fresh()->resume_token);
    }

    public function test_does_not_send_twice_for_the_same_draft()
    {
        Mail::fake();
        $user = $this->makeUser();
        OnboardingDraft::create([
            'tenant_id' => $user->tenant_id,
            'version' => 1,
            'status' => 'ready_for_review',
            'last_activity_at' => now()->subHours(40),
            'reminder_sent_at' => now()->subHours(2),
        ]);

        $this->artisan('onboarding:send-resume-reminders')->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_does_not_send_for_a_draft_still_within_the_36h_window()
    {
        Mail::fake();
        $user = $this->makeUser();
        OnboardingDraft::create([
            'tenant_id' => $user->tenant_id,
            'version' => 1,
            'status' => 'ready_for_review',
            'last_activity_at' => now()->subHours(10),
        ]);

        $this->artisan('onboarding:send-resume-reminders')->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_does_not_send_for_an_already_imported_draft()
    {
        Mail::fake();
        $user = $this->makeUser();
        OnboardingDraft::create([
            'tenant_id' => $user->tenant_id,
            'version' => 1,
            'status' => 'imported',
            'last_activity_at' => now()->subHours(40),
        ]);

        $this->artisan('onboarding:send-resume-reminders')->assertSuccessful();

        Mail::assertNothingSent();
    }
}
