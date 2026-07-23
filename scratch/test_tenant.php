<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'orders@blushedcrumbsbakehouse.com')->first();
if (!$user) {
    echo "User not found\n";
    exit;
}
$tenant = $user->tenant;
echo "Tenant Subdomain: " . $tenant->subdomain . "\n";
echo "Available Themes: " . implode(',', array_keys($tenant->getAvailableThemesForTenant())) . "\n";
