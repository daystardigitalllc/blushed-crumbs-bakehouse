<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * $customers was queried by AdminController::dashboard() (sorted by
 * total_spent desc) with no view ever rendering it, and storeCustomer()
 * had no form anywhere pointing at it. Covers the new tab-customers view
 * and its wiring to that pre-existing endpoint.
 */
class CustomersTabTest extends TestCase
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

    public function test_customers_tab_lists_existing_customers_sorted_by_spend()
    {
        $user = $this->makeUser();
        Customer::create(['tenant_id' => $user->tenant_id, 'name' => 'Low Spender', 'total_spent' => 20, 'order_count' => 1]);
        Customer::create(['tenant_id' => $user->tenant_id, 'name' => 'Big Spender', 'total_spent' => 500, 'order_count' => 4]);

        $response = $this->actingAs($user)->get('/site/' . $user->tenant->subdomain . '/dashboard');

        $response->assertStatus(200);
        $response->assertSeeInOrder(['Big Spender', 'Low Spender']);
        $response->assertSee('$500.00');
    }

    public function test_customers_tab_shows_empty_state_with_no_customers()
    {
        $user = $this->makeUser();

        $response = $this->actingAs($user)->get('/site/' . $user->tenant->subdomain . '/dashboard');

        $response->assertStatus(200);
        $response->assertSee('No customers yet', false);
    }

    public function test_add_customer_form_posts_to_the_existing_store_endpoint()
    {
        $user = $this->makeUser();

        $response = $this->actingAs($user)->postJson('/dashboard/customers', [
            'name' => 'Priya Patel',
            'email' => 'priya@example.com',
            'phone' => '555-1234',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('customers', [
            'tenant_id' => $user->tenant_id,
            'name' => 'Priya Patel',
            'email' => 'priya@example.com',
        ]);
    }
}
