<?php

namespace Tests\Feature;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The Phase 9 Stripe fix: only a signature-verified webhook can grant Pro
 * now — the old GET /stripe/callback?client_reference_id=... bypass is
 * inert. Covers a bad signature being rejected, a valid signed
 * checkout.session.completed actually upgrading the tenant (and applying
 * any stashed pending_pro_theme_id), and confirming the legacy callback no
 * longer mutates anything from the query string.
 */
class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'whsec_test_secret';

    private function makeTenant(array $overrides = []): Tenant
    {
        return Tenant::create(array_merge([
            'name' => 'Stripe Test Bakery',
            'slug' => 'stripe-test-' . Str::random(8),
            'owner_name' => 'Test Owner',
            'email' => Str::random(8) . '@example.com',
            'plan_tier' => 'standard',
            'theme_id' => 'rustic_kitchen',
            'is_active' => true,
        ], $overrides));
    }

    private function signedHeader(string $payload, string $secret, ?int $timestamp = null): string
    {
        $timestamp ??= time();
        $signature = hash_hmac('sha256', "{$timestamp}.{$payload}", $secret);

        return "t={$timestamp},v1={$signature}";
    }

    public function test_invalid_signature_is_rejected()
    {
        config(['services.stripe.webhook_secret' => self::SECRET]);
        $payload = json_encode(['id' => 'evt_1', 'type' => 'checkout.session.completed', 'data' => ['object' => []]]);

        $this->postJson('/stripe/webhook', json_decode($payload, true), [
            'Stripe-Signature' => 't=' . time() . ',v1=not-a-real-signature',
        ])->assertStatus(400);
    }

    public function test_valid_signature_upgrades_tenant_to_pro_and_applies_stashed_theme()
    {
        config(['services.stripe.webhook_secret' => self::SECRET]);
        $tenant = $this->makeTenant(['plan_tier' => 'standard', 'pending_pro_theme_id' => 'sweet_elegant', 'subdomain' => 'stripetest']);

        $payload = json_encode([
            'id' => 'evt_test_1',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_1',
                    'object' => 'checkout.session',
                    'payment_status' => 'paid',
                    'client_reference_id' => (string) $tenant->id,
                    'customer' => 'cus_test_1',
                    'subscription' => 'sub_test_1',
                ],
            ],
        ]);

        $this->call('POST', '/stripe/webhook', [], [], [], [
            'HTTP_Stripe-Signature' => $this->signedHeader($payload, self::SECRET),
            'CONTENT_TYPE' => 'application/json',
        ], $payload)->assertStatus(200);

        $tenant->refresh();
        $this->assertSame('pro', $tenant->plan_tier);
        $this->assertSame('sweet_elegant', $tenant->theme_id);
        $this->assertNull($tenant->pending_pro_theme_id);
        $this->assertSame('cus_test_1', $tenant->stripe_customer_id);
    }

    public function test_unpaid_session_does_not_upgrade_tenant()
    {
        config(['services.stripe.webhook_secret' => self::SECRET]);
        $tenant = $this->makeTenant();

        $payload = json_encode([
            'id' => 'evt_test_2',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_2',
                    'object' => 'checkout.session',
                    'payment_status' => 'unpaid',
                    'client_reference_id' => (string) $tenant->id,
                ],
            ],
        ]);

        $this->call('POST', '/stripe/webhook', [], [], [], [
            'HTTP_Stripe-Signature' => $this->signedHeader($payload, self::SECRET),
            'CONTENT_TYPE' => 'application/json',
        ], $payload)->assertStatus(200);

        $this->assertSame('standard', $tenant->fresh()->plan_tier);
    }

    public function test_legacy_get_callback_no_longer_grants_pro_from_query_string()
    {
        $tenant = $this->makeTenant();

        $this->get('/stripe/callback?client_reference_id=' . $tenant->id)
            ->assertRedirect('/login');

        $this->assertSame('standard', $tenant->fresh()->plan_tier);
        $this->assertFalse((bool) $tenant->fresh()->onboarding_completed);
    }
}
