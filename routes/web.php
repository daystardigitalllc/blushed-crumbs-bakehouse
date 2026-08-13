<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\PresaleController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\OnboardingUploadController;

use App\Http\Controllers\LegalController;
use App\Http\Controllers\ToolsController;
use App\Http\Controllers\CottageFoodLawsController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\ExamplesController;

// ─── SEO: sitemap.xml / robots.txt (host-aware — main domain vs. tenant) ───
Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

// ─── Marketing: showcase of demo bakery websites built with Doughmain ───
Route::get('/examples', [ExamplesController::class, 'index'])->name('examples');

// ─── Authentication Routes ───
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// ─── Legal Hub & Policy Routes ───
Route::get('/legal', [LegalController::class, 'index'])->name('legal.index');
Route::get('/legal/{slug}', [LegalController::class, 'show'])->name('legal.show');

// ───  SaaS Landing Page ───
Route::get('/landing', [BrandController::class, 'landing'])->name('brand.landing');

// ─── SEO Landing Pages ───
Route::get('/bakery-website-builder', [\App\Http\Controllers\MarketingController::class, 'bakeryWebsiteBuilder'])->name('brand.seo.bakery-website-builder');
Route::get('/bakery-website-design', [\App\Http\Controllers\MarketingController::class, 'bakeryWebsiteDesign'])->name('brand.seo.bakery-website-design');
Route::get('/home-bakery-website', [\App\Http\Controllers\MarketingController::class, 'homeBakeryWebsite'])->name('brand.seo.home-bakery-website');
Route::get('/custom-cake-website', [\App\Http\Controllers\MarketingController::class, 'customCakeWebsite'])->name('brand.seo.custom-cake-website');
Route::get('/bakesy-alternative', [\App\Http\Controllers\MarketingController::class, 'bakesyAlternative'])->name('brand.seo.bakesy-alternative');
Route::get('/bakebug-alternative', [\App\Http\Controllers\MarketingController::class, 'bakebugAlternative'])->name('brand.seo.bakebug-alternative');

// ─── Blog System ───
Route::get('/blog', [\App\Http\Controllers\MarketingController::class, 'blogIndex'])->name('blog.index');
Route::get('/blog/{slug}', [\App\Http\Controllers\MarketingController::class, 'blogPost'])->name('blog.show');

// ─── Free Tools (SEO lead-gen calculators) ───
Route::get('/tools/bakery-pricing-calculator', [ToolsController::class, 'pricingCalculator'])->name('tools.pricing-calculator');
Route::post('/tools/bakery-pricing-calculator/parse-ingredients', [ToolsController::class, 'parseIngredients'])
    ->middleware('throttle:10,1')
    ->name('tools.pricing-calculator.parse');
Route::get('/cottage-food-laws', [CottageFoodLawsController::class, 'index'])->name('cottage-food-laws.index');
Route::get('/cottage-food-laws/{state}', [CottageFoodLawsController::class, 'show'])->name('cottage-food-laws.show');

// ─── Storefront Routes (Public Bakery Website) ───
Route::get('/', [StorefrontController::class, 'index'])->name('storefront.index');
// Auth + tenant.owner required - these render the real baker admin
// dashboard (orders, customers, settings), not a public preview, despite
// living under /site/{subdomain}/*.
Route::middleware(['auth', 'tenant.owner'])->group(function () {
    Route::get('/site/{subdomain}/dashboard', [AdminController::class, 'dashboard'])->name('storefront.preview.dashboard');
    Route::get('/site/{subdomain}/admin', [AdminController::class, 'dashboard']);
});
// Every /site/{subdomain}/... public storefront URL (index/about/menu/gallery/
// policy/privacy/terms/legal docs) used to render a second, fully indexable
// copy of a tenant's site alongside their real subdomain.doughmain.pro (or
// custom domain) — duplicate content that splits SEO ranking signal. The
// real site is the only copy we want crawled/ranked now, so anything else
// under /site/{subdomain}/* 301s to the tenant's canonical publicUrl(),
// preserving any old bookmarks/backlinks instead of 404ing them. This must
// stay below the dashboard/admin routes above so those keep matching first.
Route::get('/site/{subdomain}/{path?}', function (\Illuminate\Http\Request $request, string $subdomain, string $path = '') {
    $tenant = \App\Models\Tenant::where('subdomain', $subdomain)->orWhere('slug', $subdomain)->where('is_active', true)->first();
    if (!$tenant) {
        abort(404, 'Bakery website not found.');
    }

    $target = $tenant->publicUrl($path);
    if ($query = $request->getQueryString()) {
        $target .= '?' . $query;
    }

    return redirect()->away($target, 301);
})->where('path', '.*')->name('storefront.preview.redirect');
Route::get('/about', [StorefrontController::class, 'about'])->name('storefront.about');
Route::get('/menu', [StorefrontController::class, 'menu'])->name('storefront.menu');
Route::get('/gallery', [StorefrontController::class, 'gallery'])->name('storefront.gallery');
Route::get('/policy', [StorefrontController::class, 'policy'])->name('storefront.policy');
Route::get('/privacy', [StorefrontController::class, 'privacy'])->name('storefront.privacy');
Route::get('/terms', [StorefrontController::class, 'terms'])->name('storefront.terms');
Route::post('/order', [StorefrontController::class, 'submitOrder'])->name('storefront.order.submit');
Route::get('/presale', [PresaleController::class, 'show'])->name('storefront.presale');
Route::post('/presale/checkout', [PresaleController::class, 'submit'])->name('storefront.presale.submit');
Route::get('/invoices/{invoiceNumber}', [StorefrontController::class, 'showInvoice'])->name('invoices.show');
Route::post('/newsletter/subscribe', [\App\Http\Controllers\EmailMarketingController::class, 'subscribe'])->name('newsletter.subscribe');
Route::get('/newsletter/unsubscribe/{token}', [\App\Http\Controllers\EmailMarketingController::class, 'unsubscribe'])->name('newsletter.unsubscribe');

Route::get('/stripe/callback', [OnboardingController::class, 'stripeCallback'])->name('stripe.callback');

// Signature-verified — the only thing that can actually grant Pro (Phase 9).
// CSRF-exempt (see bootstrap/app.php): Stripe calls this server-to-server
// with no session/CSRF token, and the Stripe-Signature header is the real
// authenticity check here.
Route::post('/stripe/webhook', [\App\Http\Controllers\StripeWebhookController::class, 'handle'])->name('stripe.webhook');

// ─── Onboarding Wizard (Authenticated, Post-Signup) ───
Route::middleware('auth')->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding.show');
    Route::post('/onboarding/save', [OnboardingController::class, 'save'])->name('onboarding.save');
    Route::post('/onboarding/import-social', [OnboardingController::class, 'importSocial'])->name('onboarding.social.import');
    Route::post('/onboarding/generate', [OnboardingController::class, 'generate'])->name('onboarding.generate');
    Route::post('/onboarding/publish', [OnboardingController::class, 'publish'])->name('onboarding.publish');

    // Phase 9 — resume-from-email link. Token AND auth both required (the
    // email could be forwarded); an expired/foreign/missing draft all render
    // the same friendly page rather than a 500 — see OnboardingController::resume().
    Route::get('/onboarding/resume/{token}', [OnboardingController::class, 'resume'])->name('onboarding.resume');

    // v2 rebuild — authenticated preview streaming for private draft files.
    // Stays under /onboarding/* so ResolveTenant's tenant binding still fires.
    Route::get('/onboarding/v2/files/{file}/preview/{derivative?}', [OnboardingUploadController::class, 'preview'])
        ->name('onboarding.v2.file.preview');

    // Phase 8 — the real wizard. Real URL (not query params/session state)
    // so a resume email is a plain deep link; {draft?} lets a fresh visit
    // resume/create automatically (see Wizard::mount()).
    Route::get('/onboarding/v2/{draft?}', \App\Livewire\Onboarding\Wizard::class)
        ->name('onboarding.v2.wizard');

    // Data Privacy & Compliance Endpoints (GDPR/CCPA Data Export & Deletion)
    Route::get('/account/data-export', [LegalController::class, 'exportData'])->name('account.data.export');
    Route::post('/account/delete-request', [LegalController::class, 'requestDeletion'])->name('account.delete.request');
});

// ─── Super Admin Brand Portal (Platform SuperAdmin Only) ───
Route::middleware(['auth', \App\Http\Middleware\SuperAdminMiddleware::class])->prefix('admin')->group(function () {
    Route::get('/', [BrandController::class, 'superAdminDashboard'])->name('superadmin.dashboard');
    Route::post('/tenants/{tenant}/toggle', [BrandController::class, 'toggleTenantStatus'])->name('superadmin.tenant.toggle');
    Route::post('/users/{user}/role', [BrandController::class, 'updateUserRole'])->name('superadmin.user.role');
    Route::delete('/users/{user}', [BrandController::class, 'deleteUser'])->name('superadmin.user.delete');
    Route::post('/tickets/{ticket}/status', [BrandController::class, 'updateTicketStatus'])->name('superadmin.ticket.status');

    // Phase 7 of the onboarding rebuild — de-risks the Livewire dependency
    // (composer install, package discovery, an actual live component)
    // before Phase 8 builds the real /onboarding/v2 wizard on top of it.
    // Not meant to survive past that; super-admin-only, not linked from
    // anywhere a baker would see.
    Route::get('/onboarding-livewire-check', \App\Livewire\OnboardingLivewireCheck::class)
        ->name('superadmin.onboarding-livewire-check');
});

// ─── Baker Portal Dashboard (Authenticated Bakery Owner) ───
Route::middleware(['auth', 'tenant.owner'])->prefix('dashboard')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('baker.dashboard');
    Route::post('/gallery', [AdminController::class, 'storeGallery'])->name('admin.gallery.store');
    Route::put('/gallery/{id}', [AdminController::class, 'updateGalleryCategory'])->name('admin.gallery.category.update');
    Route::delete('/gallery/{id}', [AdminController::class, 'destroyGallery'])->name('admin.gallery.destroy');
    Route::post('/gallery-categories', [AdminController::class, 'addGalleryCategory'])->name('admin.gallery.categories.store');
    Route::delete('/gallery-categories', [AdminController::class, 'removeGalleryCategory'])->name('admin.gallery.categories.destroy');
    Route::post('/form-builder', [AdminController::class, 'saveFormSchema'])->name('admin.form.schema.save');
    Route::post('/settings/booking', [AdminController::class, 'saveBookingSettings'])->name('admin.settings.booking.save');
    Route::post('/settings/presale', [AdminController::class, 'savePresaleSettings'])->name('admin.settings.presale.save');
    Route::post('/presale-items', [AdminController::class, 'storePresaleItem'])->name('admin.presale-items.store');
    Route::put('/presale-items/{id}', [AdminController::class, 'updatePresaleItem'])->name('admin.presale-items.update');
    Route::delete('/presale-items/{id}', [AdminController::class, 'destroyPresaleItem'])->name('admin.presale-items.destroy');
    Route::post('/settings/email', [AdminController::class, 'saveEmailRouting'])->name('admin.settings.email.save');
    Route::post('/theme', [AdminController::class, 'saveTheme'])->name('admin.theme.save');
    Route::post('/settings/business', [AdminController::class, 'saveBusinessInfo'])->name('admin.settings.business');
    Route::post('/sections', [AdminController::class, 'saveSectionSettings'])->name('admin.sections.save');
    Route::post('/sections/preview', [AdminController::class, 'previewSectionSettings'])->name('admin.sections.preview');
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
    Route::post('/settings/domain/verify', [AdminController::class, 'verifyCustomDomain'])->name('admin.settings.domain.verify');
    Route::get('/settings/domain/status', [AdminController::class, 'customDomainStatus'])->name('admin.settings.domain.status');
    Route::post('/settings/reviews', [AdminController::class, 'saveReviewSettings'])->name('admin.settings.reviews');
    Route::post('/settings/menu', [AdminController::class, 'saveMenuSettings'])->name('admin.settings.menu');
    Route::post('/settings/logo', [AdminController::class, 'saveLogo'])->name('admin.settings.logo');
    Route::post('/settings/password', [AdminController::class, 'updatePassword'])->name('admin.settings.password');
    Route::post('/products', [AdminController::class, 'storeProduct'])->name('admin.products.store');
    Route::put('/products/{id}', [AdminController::class, 'updateProduct'])->name('admin.products.update');
    Route::delete('/products/{id}', [AdminController::class, 'destroyProduct'])->name('admin.products.destroy');
    Route::post('/site/{subdomain}/products', [AdminController::class, 'storeProduct']);
    Route::put('/site/{subdomain}/products/{id}', [AdminController::class, 'updateProduct']);
    Route::delete('/site/{subdomain}/products/{id}', [AdminController::class, 'destroyProduct']);
    Route::post('/settings/payment-methods', [AdminController::class, 'savePaymentMethods'])->name('admin.settings.payment-methods');
    Route::post('/subscription/cancel', [AdminController::class, 'cancelSubscription'])->name('admin.subscription.cancel');
    Route::post('/support/ticket', [AdminController::class, 'submitSupportTicket'])->name('admin.support.ticket');

    // ─── Email Marketing (Pro only — enforced in the controller, same pattern as custom domains) ───
    Route::post('/email-marketing/subscribers', [\App\Http\Controllers\EmailMarketingController::class, 'storeSubscriber'])->name('admin.email-marketing.subscribers.store');
    Route::delete('/email-marketing/subscribers/{subscriber}', [\App\Http\Controllers\EmailMarketingController::class, 'destroySubscriber'])->name('admin.email-marketing.subscribers.destroy');
    Route::post('/email-marketing/import-customers', [\App\Http\Controllers\EmailMarketingController::class, 'importCustomers'])->name('admin.email-marketing.import-customers');
    Route::post('/email-marketing/campaigns', [\App\Http\Controllers\EmailMarketingController::class, 'storeCampaign'])->name('admin.email-marketing.campaigns.store');
});

// Alias for backwards compatibility
Route::middleware(['auth', 'tenant.owner'])->get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
