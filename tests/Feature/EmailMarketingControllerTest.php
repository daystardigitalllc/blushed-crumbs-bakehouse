<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\EmailCampaign;
use App\Models\EmailSubscriber;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Collection (add/import/remove subscribers) is free for every tenant;
 * only actually sending a campaign requires Pro. Free bakers should be
 * able to build a real list before ever hitting a paywall — see the
 * requirePro() docblock in EmailMarketingController.
 */
class EmailMarketingControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(array $tenantOverrides = []): User
    {
        $tenant = Tenant::create(array_merge([
            'name' => 'Test Bakery',
            'slug' => 'test-bakery-' . Str::random(8),
            'subdomain' => 'test-bakery-' . Str::random(8),
            'owner_name' => 'Test Owner',
            'email' => Str::random(8) . '@example.com',
            'plan_tier' => 'free',
            'theme_id' => 'rustic_kitchen',
            'is_active' => true,
        ], $tenantOverrides));

        return User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Test Owner',
            'email' => Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'email_verified_at' => now(),
        ]);
    }

    public function test_free_tenant_can_add_a_subscriber()
    {
        $user = $this->makeUser(['plan_tier' => 'free']);

        $response = $this->actingAs($user)->postJson('/dashboard/email-marketing/subscribers', [
            'email' => 'fan@example.com',
            'name' => 'Cake Fan',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('email_subscribers', [
            'tenant_id' => $user->tenant_id,
            'email' => 'fan@example.com',
        ]);
    }

    public function test_free_tenant_can_import_customers_as_subscribers()
    {
        $user = $this->makeUser(['plan_tier' => 'free']);
        Customer::create([
            'tenant_id' => $user->tenant_id,
            'name' => 'Jane Baker',
            'email' => 'jane@example.com',
        ]);

        $response = $this->actingAs($user)->postJson('/dashboard/email-marketing/import-customers');

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'imported' => 1]);
        $this->assertDatabaseHas('email_subscribers', [
            'tenant_id' => $user->tenant_id,
            'email' => 'jane@example.com',
        ]);
    }

    public function test_free_tenant_can_remove_a_subscriber()
    {
        $user = $this->makeUser(['plan_tier' => 'free']);
        $subscriber = EmailSubscriber::create([
            'tenant_id' => $user->tenant_id,
            'email' => 'remove-me@example.com',
        ]);

        $response = $this->actingAs($user)->deleteJson("/dashboard/email-marketing/subscribers/{$subscriber->id}");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('email_subscribers', ['id' => $subscriber->id]);
    }

    public function test_free_tenant_is_blocked_from_sending_a_campaign()
    {
        $user = $this->makeUser(['plan_tier' => 'free']);
        EmailSubscriber::create(['tenant_id' => $user->tenant_id, 'email' => 'fan@example.com']);

        $response = $this->actingAs($user)->postJson('/dashboard/email-marketing/campaigns', [
            'subject' => 'Big Sale',
            'body' => 'Everything is 20% off this weekend!',
        ]);

        $response->assertStatus(403);
        $response->assertJson(['success' => false]);
        $this->assertDatabaseCount('email_campaigns', 0);
    }

    public function test_pro_tenant_can_send_a_campaign()
    {
        Bus::fake();

        $user = $this->makeUser(['plan_tier' => 'pro']);
        EmailSubscriber::create(['tenant_id' => $user->tenant_id, 'email' => 'fan@example.com']);

        $response = $this->actingAs($user)->postJson('/dashboard/email-marketing/campaigns', [
            'subject' => 'Big Sale',
            'body' => 'Everything is 20% off this weekend!',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('email_campaigns', [
            'tenant_id' => $user->tenant_id,
            'subject' => 'Big Sale',
        ]);
    }
}
