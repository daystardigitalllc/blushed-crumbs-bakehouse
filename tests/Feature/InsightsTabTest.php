<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Free tenants see one real teaser number (this month's revenue) and a
 * blurred/locked panel for the rest; Pro tenants see everything unlocked.
 * See AdminController::dashboard()'s Insights stats block and the
 * `tab-insights` section of admin/dashboard.blade.php.
 */
class InsightsTabTest extends TestCase
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

    public function test_free_tenant_sees_revenue_teaser_but_blurred_breakdown()
    {
        $user = $this->makeUser(['plan_tier' => 'free']);
        Order::create([
            'tenant_id' => $user->tenant_id,
            'order_number' => 'TB-0001',
            'client_name' => 'Jane Doe',
            'client_email' => 'jane@example.com',
            'client_phone' => '555-1234',
            'due_date' => now()->addDays(3),
            'items' => [],
            'total_price' => 120,
            'deposit_amount' => 60,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($user)->get('/site/' . $user->tenant->subdomain . '/dashboard');

        $response->assertStatus(200);
        $response->assertSee('$120.00'); // the real, unblurred teaser number
        $response->assertSee('Pro Feature');
        $response->assertSee('filter:blur', false);
    }

    public function test_pro_tenant_sees_full_insights_breakdown_unblurred()
    {
        $user = $this->makeUser(['plan_tier' => 'pro']);
        Order::create([
            'tenant_id' => $user->tenant_id,
            'order_number' => 'TB-0001',
            'client_name' => 'Jane Doe',
            'client_email' => 'jane@example.com',
            'client_phone' => '555-1234',
            'due_date' => now()->addDays(3),
            'items' => [],
            'total_price' => 120,
            'deposit_amount' => 60,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($user)->get('/site/' . $user->tenant->subdomain . '/dashboard');

        $response->assertStatus(200);
        $response->assertSee('$120.00');
        $response->assertSee('Average Order Value');
        $response->assertDontSee('Upgrade to Pro ($29/mo) to unlock', false);
    }

    public function test_repeat_customer_rate_counts_customers_with_more_than_one_order()
    {
        $user = $this->makeUser(['plan_tier' => 'pro']);
        $tenant = $user->tenant;

        Customer::create(['tenant_id' => $tenant->id, 'name' => 'Repeat Customer', 'order_count' => 3]);
        Customer::create(['tenant_id' => $tenant->id, 'name' => 'One-Time Customer', 'order_count' => 1]);

        $response = $this->actingAs($user)->get('/site/' . $tenant->subdomain . '/dashboard');

        $response->assertStatus(200);
        $response->assertSee('50%'); // 1 of 2 customers is a repeat customer
    }

    /**
     * Regression test for the orders.status CHECK constraint on SQLite: the
     * 2026_08_02_182724_add_paid_to_orders_status_enum migration must actually
     * widen the constraint on SQLite (not just MySQL), since the Insights
     * revenue calculations above rely on 'paid' being a valid order status.
     */
    public function test_order_can_be_created_with_paid_status(): void
    {
        $tenant = Tenant::create([
            'name' => 'Insights Test Bakery',
            'slug' => 'insights-test-' . Str::random(8),
            'owner_name' => 'Test Owner',
            'email' => Str::random(8) . '@example.com',
            'plan_tier' => 'free',
            'theme_id' => 'rustic_kitchen',
            'is_active' => true,
        ]);

        $order = Order::create([
            'tenant_id' => $tenant->id,
            'order_number' => 'ORD-' . Str::random(8),
            'client_name' => 'Jane Doe',
            'client_email' => 'jane@example.com',
            'client_phone' => '555-1234',
            'due_date' => now()->addDays(7),
            'items' => [['name' => 'Custom Cake', 'qty' => 1]],
            'subtotal' => 100.00,
            'discount_amount' => 0,
            'total_price' => 100.00,
            'deposit_amount' => 50.00,
            'deposit_paid' => true,
            'status' => 'paid',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'paid',
        ]);
    }
}
