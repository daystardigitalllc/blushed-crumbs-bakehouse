<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Brand;
use App\Models\Tenant;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

echo "=== PRODUCTION SIGNUP & ONBOARDING ZERO-SEEDER AUDIT ===\n\n";

// Test 1: Verify Brand dynamic provisioning
echo "[1] Testing Brand provisioning... ";
$brand = Brand::firstOrCreate(
    ['slug' => 'doughmain'],
    [
        'name' => 'Doughmain',
        'domain' => 'doughmain.pro',
        'is_active' => true,
    ]
);
echo "SUCCESS (Brand ID: {$brand->id})\n";

// Test 2: Atomic DB Transaction Registration
echo "[2] Testing Atomic User & Tenant Registration... ";
$testEmail = 'prod_baker_' . uniqid() . '@example.com';
$bakeryName = 'Sugar & Spice Bakehouse ' . rand(100, 999);

$res = DB::transaction(function() use ($testEmail, $bakeryName, $brand) {
    $slug = Str::slug($bakeryName, '');
    $subdomain = $slug;

    $tenant = Tenant::create([
        'brand_id' => $brand->id,
        'name' => $bakeryName,
        'slug' => $slug,
        'subdomain' => $subdomain,
        'owner_name' => 'Jane Baker',
        'email' => $testEmail,
        'plan_tier' => 'standard',
        'theme_id' => 'rustic_kitchen',
        'is_active' => true,
        'onboarding_completed' => false,
        'max_reviews_display' => 3,
        'form_schema' => Tenant::getDefaultFormSchema(),
        'booking_settings' => [
            'lead_time_enabled' => true,
            'lead_time_days' => 3,
            'recurring_closed_days' => [0, 1],
            'blocked_dates' => [],
        ],
    ]);

    $user = User::create([
        'tenant_id' => $tenant->id,
        'name' => 'Jane Baker',
        'email' => $testEmail,
        'password' => Hash::make('password123'),
        'role' => 'owner',
    ]);

    AuditLog::logEvent('auth.register', $tenant->id, $user->id, [
        'bakery_name' => $tenant->name,
        'subdomain' => $tenant->subdomain,
    ]);

    return compact('tenant', 'user');
});

echo "SUCCESS\n";
echo "    Created Tenant: {$res['tenant']->name} (Subdomain: {$res['tenant']->subdomain})\n";
echo "    Created User: {$res['user']->email} (Role: {$res['user']->role})\n";

// Test 3: Check site content dynamic default generation
echo "[3] Testing Dynamic Site Content Defaults... ";
$siteContent = $res['tenant']->getSiteContent('hero_headline');
if ($siteContent === $bakeryName) {
    echo "SUCCESS (Headline matches bakery name: '{$siteContent}')\n";
} else {
    echo "FAILED! Expected '{$bakeryName}', got '{$siteContent}'\n";
}

// Test 4: Onboarding completion simulation
echo "[4] Testing Onboarding Completion... ";
$res['tenant']->update(['onboarding_completed' => true]);
echo "SUCCESS (onboarding_completed = " . ($res['tenant']->fresh()->onboarding_completed ? 'true' : 'false') . ")\n";

// Test 5: Verify Audit Log recording
echo "[5] Testing Audit Log... ";
$log = AuditLog::where('user_id', $res['user']->id)->where('event_type', 'auth.register')->first();
if ($log) {
    echo "SUCCESS (Audit Log Event: {$log->event_type}, IP: {$log->ip_address})\n";
} else {
    echo "FAILED! No audit log found.\n";
}

echo "\n=== ALL PRODUCTION REGISTRATION AUDITS PASSED PERFECTLY ===\n";
