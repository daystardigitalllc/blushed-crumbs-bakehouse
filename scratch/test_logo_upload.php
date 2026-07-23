<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;
use App\Models\User;
use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

echo "=== TESTING BAKERY LOGO UPLOAD IN ADMIN AREA ===\n";

$tenant = Tenant::first();
$bakerUser = User::where('tenant_id', $tenant->id)->first();
Auth::login($bakerUser);

// Create a dummy image file
$tempPath = sys_get_temp_dir() . '/test_logo.png';
$im = imagecreatetruecolor(100, 100);
$bgColor = imagecolorallocate($im, 230, 115, 153);
imagefill($im, 0, 0, $bgColor);
imagepng($im, $tempPath);
imagedestroy($im);

$uploadedFile = new UploadedFile(
    $tempPath,
    'my_bakery_logo.png',
    'image/png',
    null,
    true
);

$adminCtrl = app(AdminController::class);
$req = Request::create('/dashboard/settings/logo', 'POST', [], [], ['logo' => $uploadedFile]);

$response = $adminCtrl->saveLogo($req);
$content = json_decode($response->getContent(), true);

echo "Response Success: " . ($content['success'] ? 'YES ✅' : 'NO ❌') . "\n";
echo "Response Message: " . $content['message'] . "\n";
echo "New Logo Path: " . ($content['logo_path'] ?? 'N/A') . "\n";

$tenant->refresh();
echo "Database Tenant logo_path: " . ($tenant->logo_path ?? 'NULL') . "\n";
echo "Logo file exists on disk: " . (file_exists(public_path($tenant->logo_path)) ? 'YES ✅' : 'NO ❌') . "\n";

echo "\n=== LOGO UPLOAD TEST COMPLETE ===\n";
