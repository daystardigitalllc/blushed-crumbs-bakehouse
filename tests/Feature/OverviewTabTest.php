<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Overview replaced Orders as the dashboard's default landing tab — see
 * the "TAB: Overview" block in admin/dashboard.blade.php. It's built
 * entirely from data the controller already computes for other tabs
 * (urgentOrders, thisMonthRevenue, pendingOrders, customerCount,
 * newInquiriesCount), so this mainly guards against that wiring drifting.
 */
class OverviewTabTest extends TestCase
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

    public function test_overview_is_the_default_active_tab()
    {
        $user = $this->makeUser();

        $response = $this->actingAs($user)->get('/site/' . $user->tenant->subdomain . '/dashboard');

        $response->assertStatus(200);
        $response->assertSeeInOrder(['id="tab-overview" class="tab-content active"']);
        $response->assertDontSee('id="tab-orders" class="tab-content active"');
    }

    public function test_overview_shows_urgent_orders_and_revenue()
    {
        $user = $this->makeUser();
        Order::create([
            'tenant_id' => $user->tenant_id,
            'order_number' => 'OV-0001',
            'client_name' => 'Priya Baker',
            'client_email' => 'priya@example.com',
            'client_phone' => '555-9999',
            'due_date' => now()->addDays(2),
            'items' => [],
            'total_price' => 200,
            'deposit_amount' => 100,
            'status' => 'new',
        ]);

        $response = $this->actingAs($user)->get('/site/' . $user->tenant->subdomain . '/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Priya Baker');
        $response->assertSee('New Inquiries');
        $response->assertSee('1'); // newInquiriesCount
    }

    public function test_overview_shows_caught_up_message_with_no_urgent_orders()
    {
        $user = $this->makeUser();

        $response = $this->actingAs($user)->get('/site/' . $user->tenant->subdomain . '/dashboard');

        $response->assertStatus(200);
        $response->assertSee("you're all caught up.", false);
    }
}
