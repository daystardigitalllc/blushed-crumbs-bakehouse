<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\OnboardingController;

// ─── Authentication Routes ───
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ───  SaaS Landing Page ───
// This route is hit when the user visits the main brand domain (doughmain.pro)
// The StorefrontController checks the host and renders the landing page or storefront
Route::get('/landing', [BrandController::class, 'landing'])->name('brand.landing');

// ─── Storefront Routes (Public Bakery Website) ───
Route::get('/', [StorefrontController::class, 'index'])->name('storefront.index');
Route::get('/site/{subdomain}', [StorefrontController::class, 'preview'])->name('storefront.preview');
Route::get('/site/{subdomain}/dashboard', [AdminController::class, 'dashboard'])->name('storefront.preview.dashboard');
Route::get('/site/{subdomain}/admin', [AdminController::class, 'dashboard']);
Route::get('/site/{subdomain}/about', [StorefrontController::class, 'previewAbout'])->name('storefront.preview.about');
Route::get('/site/{subdomain}/gallery', [StorefrontController::class, 'previewGallery'])->name('storefront.preview.gallery');
Route::get('/about', [StorefrontController::class, 'about'])->name('storefront.about');
Route::get('/gallery', [StorefrontController::class, 'gallery'])->name('storefront.gallery');
Route::post('/order', [StorefrontController::class, 'submitOrder'])->name('storefront.order.submit');
Route::get('/invoices/{invoiceNumber}', [StorefrontController::class, 'showInvoice'])->name('invoices.show');

// ─── Onboarding Wizard (Authenticated, Post-Signup) ───
Route::middleware('auth')->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding.show');
    Route::post('/onboarding/save', [OnboardingController::class, 'save'])->name('onboarding.save');
    Route::post('/onboarding/import-social', [OnboardingController::class, 'importSocial'])->name('onboarding.social.import');
    Route::post('/onboarding/generate', [OnboardingController::class, 'generate'])->name('onboarding.generate');
    Route::post('/onboarding/publish', [OnboardingController::class, 'publish'])->name('onboarding.publish');
});

// ─── Super Admin Brand Portal (Platform SuperAdmin Only) ───
Route::middleware(['auth', \App\Http\Middleware\SuperAdminMiddleware::class])->prefix('admin')->group(function () {
    Route::get('/', [BrandController::class, 'superAdminDashboard'])->name('superadmin.dashboard');
    Route::post('/tenants/{tenant}/toggle', [BrandController::class, 'toggleTenantStatus'])->name('superadmin.tenant.toggle');
    Route::post('/users/{user}/role', [BrandController::class, 'updateUserRole'])->name('superadmin.user.role');
    Route::delete('/users/{user}', [BrandController::class, 'deleteUser'])->name('superadmin.user.delete');
    Route::post('/tickets/{ticket}/status', [BrandController::class, 'updateTicketStatus'])->name('superadmin.ticket.status');
});

// ─── Baker Portal Dashboard (Authenticated Bakery Owner) ───
Route::middleware('auth')->prefix('dashboard')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('baker.dashboard');
    Route::post('/gallery', [AdminController::class, 'storeGallery'])->name('admin.gallery.store');
    Route::delete('/gallery/{id}', [AdminController::class, 'destroyGallery'])->name('admin.gallery.destroy');
    Route::post('/form-builder', [AdminController::class, 'saveFormSchema'])->name('admin.form.schema.save');
    Route::post('/settings/booking', [AdminController::class, 'saveBookingSettings'])->name('admin.settings.booking.save');
    Route::post('/settings/email', [AdminController::class, 'saveEmailRouting'])->name('admin.settings.email.save');
    Route::post('/theme', [AdminController::class, 'saveTheme'])->name('admin.theme.save');
    Route::post('/content', [AdminController::class, 'saveContent'])->name('admin.content.save');
    Route::post('/sections', [AdminController::class, 'saveSectionSettings'])->name('admin.sections.save');
    Route::post('/upload-media', [AdminController::class, 'uploadMedia'])->name('admin.media.upload');

    Route::post('/orders/{order}/status', [AdminController::class, 'updateOrderStatus'])->name('admin.order.status');
    Route::delete('/reviews/{review}', [AdminController::class, 'deleteReview'])->name('admin.review.delete');
    Route::post('/reviews', [AdminController::class, 'storeReview'])->name('admin.review.store');
    Route::post('/customers', [AdminController::class, 'storeCustomer'])->name('admin.customer.store');
    Route::post('/invoices', [AdminController::class, 'createInvoice'])->name('admin.invoice.create');
    Route::put('/invoices/{invoice}', [AdminController::class, 'updateInvoice'])->name('admin.invoice.update');
    Route::delete('/invoices/{invoice}', [AdminController::class, 'destroyInvoice'])->name('admin.invoice.destroy');
    Route::post('/invoices/{invoice}/status', [AdminController::class, 'updateInvoiceStatus'])->name('admin.invoice.status');
    Route::post('/invoices/{invoice}/send', [AdminController::class, 'sendInvoice'])->name('admin.invoice.send');
    Route::post('/settings/domain', [AdminController::class, 'saveCustomDomain'])->name('admin.settings.domain');
    Route::post('/settings/reviews', [AdminController::class, 'saveReviewSettings'])->name('admin.settings.reviews');
    Route::post('/settings/logo', [AdminController::class, 'saveLogo'])->name('admin.settings.logo');
    Route::post('/subscription/cancel', [AdminController::class, 'cancelSubscription'])->name('admin.subscription.cancel');
    Route::post('/support/ticket', [AdminController::class, 'submitSupportTicket'])->name('admin.support.ticket');
});

// Alias for backwards compatibility
Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
