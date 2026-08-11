<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\Tenant;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Review;
use App\Models\Customer;
use App\Models\GalleryItem;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;
use App\Models\SupportTicket;

class AdminController extends Controller
{
    /**
     * Resolve the target tenant for the Baker Dashboard.
     */
    private function tenant(?Request $request = null, ?string $subdomain = null): Tenant
    {
        if ($subdomain) {
            $t = Tenant::where('subdomain', $subdomain)->orWhere('slug', $subdomain)->first();
            if ($t) return $t;
        }

        if ($request && $request->attributes->get('tenant')) {
            return $request->attributes->get('tenant');
        }

        // A superadmin previewing another bakery's CMS via /site/{subdomain}/dashboard gets the
        // right tenant on page load (via the $subdomain route param above), but the dashboard's
        // AJAX saves all POST to un-scoped /dashboard/* routes with no subdomain in the URL — so
        // without this, a save would silently fall through to the superadmin's own tenant below.
        // Only trusted for superadmins: a regular baker's request must never be able to redirect
        // a save to another tenant just by adding a "subdomain" field to the form.
        if ($request && $request->filled('subdomain') && auth()->user()?->isSuperAdmin()) {
            $t = Tenant::where('subdomain', $request->input('subdomain'))
                ->orWhere('slug', $request->input('subdomain'))
                ->first();
            if ($t) return $t;
        }

        return auth()->user()?->tenant ?? Tenant::first();
    }

    public function dashboard(Request $request, ?string $subdomain = null)
    {
        // 1. If accessed on root domain without tenant context, redirect appropriately
        if (!$subdomain && !$request->attributes->get('tenant') && !$request->route('subdomain')) {
            $user = auth()->user();
            if ($user && $user->isSuperAdmin()) {
                return redirect('/admin');
            }
            if ($user && $user->tenant) {
                $domain = $user->tenant->domains()->first()?->domain;
                if ($domain) {
                    return redirect('https://' . $domain . '/dashboard');
                }
                $sub = $user->tenant->subdomain ?? $user->tenant->slug;
                return redirect('/site/' . $sub . '/dashboard');
            }
            return redirect('/login');
        }

        $tenant = $this->tenant($request, $subdomain);

        // Fallback default booking settings if empty
        if (empty($tenant->booking_settings)) {
            $tenant->booking_settings = [
                'lead_time_enabled' => true,
                'lead_time_days' => 3,
                'recurring_closed_days' => [0, 1],
                'blocked_dates' => ['2026-07-04', '2026-07-25']
            ];
            $tenant->save();
        }

        // Key feature: Orders sorted by due_date ASC so the baker sees what is due first!
        $urgentOrders = Order::where('tenant_id', $tenant->id)
            ->whereIn('status', ['new', 'invoiced', 'paid', 'in_progress', 'ready'])
            ->orderBy('due_date', 'asc')
            ->get();

        $allOrders = Order::where('tenant_id', $tenant->id)->orderBy('due_date', 'asc')->get();
        $invoices = Invoice::where('tenant_id', $tenant->id)->latest()->get();
        $products = Product::where('tenant_id', $tenant->id)->orderBy('sort_order')->get();
        $reviews = Review::where('tenant_id', $tenant->id)->latest()->get();
        $gallery = GalleryItem::where('tenant_id', $tenant->id)->latest()->get();
        $supportTickets = SupportTicket::where('tenant_id', $tenant->id)->latest()->get();
        $customers = Customer::where('tenant_id', $tenant->id)->orderBy('total_spent', 'desc')->get();
        $emailSubscribers = \App\Models\EmailSubscriber::where('tenant_id', $tenant->id)->active()->latest()->get();
        $emailCampaigns = \App\Models\EmailCampaign::where('tenant_id', $tenant->id)->latest()->get();

        // Revenue stats
        $totalRevenue = Order::where('tenant_id', $tenant->id)
            ->whereIn('status', ['completed', 'in_progress', 'ready', 'paid'])
            ->sum('total_price');
        $pendingOrders = Order::where('tenant_id', $tenant->id)
            ->whereIn('status', ['new', 'invoiced'])
            ->count();
        $customerCount = Customer::where('tenant_id', $tenant->id)->count();

        $serverBookingSettings = $tenant->booking_settings ?? [];
        $siteContent = $tenant->site_content ?? \App\Models\Tenant::getDefaultSiteContent();

        // "Finish setting up your site" checklist — each step is derived from
        // real data the baker has actually saved, not a fragile client-side
        // flag, so it stays correct across devices/sessions and for anyone
        // who set these up before this checklist existed. calendar is the one
        // exception: booking_settings auto-populates with hardcoded defaults
        // on first dashboard load (above), so its mere presence can't signal
        // intent — calendar_configured_at is only set inside
        // saveBookingSettings() itself, when a baker actually saves something.
        $onboardingChecklist = [
            'order_form' => [
                'label' => 'Set up your custom order form',
                'done' => !empty($tenant->form_schema),
                'tab' => 'tab-form-builder',
            ],
            'product' => [
                'label' => 'Add a product to your menu',
                'done' => $products->isNotEmpty(),
                'tab' => 'tab-products',
            ],
            'gallery' => [
                'label' => 'Upload a photo to your gallery',
                'done' => $gallery->isNotEmpty(),
                'tab' => 'tab-gallery-manager',
            ],
            'page_builder' => [
                'label' => 'Customize your Page Builder sections',
                'done' => !is_null($tenant->section_settings),
                'tab' => 'tab-page-builder',
            ],
            'calendar' => [
                'label' => 'Set up your availability calendar',
                'done' => !is_null($tenant->calendar_configured_at),
                'tab' => 'tab-calendar',
            ],
        ];
        $onboardingComplete = collect($onboardingChecklist)->every(fn ($step) => $step['done']);

        // "Your site isn't finished" banner — a tenant can be technically live
        // (registration creates one immediately) while its AI-generated content
        // never actually finished or came back mostly empty defaults, with no
        // other signal anywhere that anything needs attention. Surfaced here
        // rather than baked into $onboardingComplete since that flag tracks
        // manual dashboard setup steps, a separate concern from the AI draft.
        $latestOnboardingDraft = \App\Models\Onboarding\OnboardingDraft::where('tenant_id', $tenant->id)
            ->orderByDesc('id')
            ->first();
        $onboardingNeedsAttention = null;
        if ($latestOnboardingDraft) {
            if ($latestOnboardingDraft->status === 'failed') {
                $onboardingNeedsAttention = 'AI generation failed while building your site — some sections may still have placeholder content.';
            } elseif (!$tenant->onboarding_completed && in_array($latestOnboardingDraft->status, \App\Models\Onboarding\OnboardingDraft::INCOMPLETE_STATUSES, true)) {
                $onboardingNeedsAttention = 'Your site setup was never finished — some sections likely still have placeholder content.';
            } elseif ($latestOnboardingDraft->status === 'imported' && $latestOnboardingDraft->confidence_overall !== null && (float) $latestOnboardingDraft->confidence_overall < 0.3) {
                $onboardingNeedsAttention = 'AI generation had trouble reading your photos/menu — review your site copy and images for accuracy.';
            }
        } elseif (!$tenant->onboarding_completed) {
            $onboardingNeedsAttention = 'Your site setup was never finished — some sections likely still have placeholder content.';
        }

        return view('admin.dashboard', compact(
            'tenant', 'urgentOrders', 'allOrders', 'invoices',
            'products', 'reviews', 'gallery', 'supportTickets',
            'customers', 'totalRevenue', 'pendingOrders', 'customerCount',
            'serverBookingSettings', 'siteContent', 'emailSubscribers', 'emailCampaigns',
            'onboardingChecklist', 'onboardingComplete',
            'latestOnboardingDraft', 'onboardingNeedsAttention'
        ));
    }

    public function saveFormSchema(Request $request)
    {
        $tenant = $this->tenant($request);
        $request->validate([
            'schema' => 'required|array',
        ]);

        $tenant->form_schema = $request->schema;
        $tenant->save();

        return response()->json([
            'success' => true,
            'message' => 'Form steps and layout saved live!',
            'schema' => $tenant->form_schema,
        ]);
    }

    public function saveBookingSettings(Request $request)
    {
        $tenant = $this->tenant($request);
        
        $settings = [
            'lead_time_enabled' => $request->boolean('lead_time_enabled'),
            'lead_time_days' => (int) $request->input('lead_time_days', 3),
            'recurring_closed_days' => $request->input('recurring_closed_days', []),
            'blocked_dates' => $request->input('blocked_dates', []),
        ];

        $tenant->booking_settings = $settings;
        if (!$tenant->calendar_configured_at) {
            $tenant->calendar_configured_at = now();
        }
        $tenant->save();

        return response()->json([
            'success' => true,
            'message' => 'Booking availability settings saved!',
            'settings' => $tenant->booking_settings,
        ]);
    }

    public function saveEmailRouting(Request $request)
    {
        $tenant = $this->tenant($request);
        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $tenant->email = $validated['email'];
        $tenant->save();

        return response()->json([
            'success' => true,
            'message' => 'Order notification routing email saved live!',
            'email' => $tenant->email,
        ]);
    }

    public function storeGallery(Request $request)
    {
        $tenant = $this->tenant($request);

        // Accepts either a single 'image' file (legacy) or multiple 'images[]'
        // files, so old form posts and the multi-select dropzone both work.
        $files = $request->file('images') ?? ($request->hasFile('image') ? [$request->file('image')] : []);

        if (empty($files)) {
            return response()->json(['success' => false, 'message' => 'No image file uploaded.'], 422);
        }

        $request->validate([
            'title' => 'nullable|string|max:255',
            'category' => 'required|string|max:255',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp,gif|max:10240',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:10240',
        ]);

        $destPath = public_path('uploads/tenants/' . $tenant->id . '/gallery');
        if (!file_exists($destPath)) {
            mkdir($destPath, 0755, true);
        }

        $baseTitle = trim((string) $request->input('title', ''));
        $createdItems = [];

        foreach (array_values($files) as $index => $file) {
            if ($baseTitle !== '') {
                $title = $index === 0 ? $baseTitle : $baseTitle . ' (' . ($index + 1) . ')';
            } else {
                $title = Str::title(str_replace(['-', '_'], ' ', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)));
            }

            $fileName = time() . '_' . Str::random(6) . '_' . Str::slug($title) . '.' . $file->getClientOriginalExtension();
            $file->move($destPath, $fileName);
            $imageUrl = 'uploads/tenants/' . $tenant->id . '/gallery/' . $fileName;

            $galleryItem = GalleryItem::create([
                'tenant_id' => $tenant->id,
                'title' => $title,
                'category' => $request->category,
                'image_url' => $imageUrl,
            ]);

            $createdItems[] = [
                'id' => $galleryItem->id,
                'title' => $galleryItem->title,
                'category' => $galleryItem->category,
                'image_url' => asset($galleryItem->image_url),
                'raw_url' => $galleryItem->image_url,
            ];
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => count($createdItems) > 1
                    ? count($createdItems) . ' photos published to live gallery!'
                    : 'Photo published to live gallery!',
                'items' => $createdItems,
            ]);
        }

        return redirect()->route('admin.dashboard')->with('success', 'Photos published to live gallery!');
    }

    public function updateGalleryCategory(Request $request, $id)
    {
        $tenant = $this->tenant($request);
        $item = GalleryItem::where('tenant_id', $tenant->id)->findOrFail($id);

        $validated = $request->validate([
            'category' => 'required|string|max:255',
        ]);

        $item->update(['category' => $validated['category']]);

        return response()->json([
            'success' => true,
            'message' => 'Category updated!',
            'item' => [
                'id' => $item->id,
                'category' => $item->category,
            ],
        ]);
    }

    public function addGalleryCategory(Request $request)
    {
        $tenant = $this->tenant($request);

        $validated = $request->validate([
            'name' => 'required|string|max:50',
        ]);

        $name = trim($validated['name']);
        $categories = $tenant->galleryCategories();

        if (!in_array(strtolower($name), array_map('strtolower', $categories), true)) {
            $categories[] = $name;
            $tenant->update(['gallery_categories' => $categories]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Category added!',
            'categories' => $tenant->fresh()->galleryCategories(),
        ]);
    }

    public function removeGalleryCategory(Request $request)
    {
        $tenant = $this->tenant($request);

        $validated = $request->validate([
            'name' => 'required|string|max:50',
        ]);

        $existing = $tenant->galleryCategories();

        if (count($existing) <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'You need at least one gallery category.',
            ], 422);
        }

        $categories = array_values(array_filter(
            $existing,
            fn ($c) => strtolower($c) !== strtolower(trim($validated['name']))
        ));

        // Photos already tagged with the removed category keep that value
        // untouched in the DB - they just won't appear in the option list
        // going forward until re-tagged (the per-row dropdown still shows
        // their current value as a fallback option so nothing looks broken).
        $tenant->update(['gallery_categories' => $categories]);

        return response()->json([
            'success' => true,
            'message' => 'Category removed.',
            'categories' => $tenant->fresh()->galleryCategories(),
        ]);
    }

    public function destroyGallery(Request $request, $id)
    {
        $tenant = $this->tenant($request);
        $item = GalleryItem::where('tenant_id', $tenant->id)->findOrFail($id);

        \App\Services\Onboarding\TenantMediaPath::deleteLegacy($item->image_url);

        $item->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Gallery photo deleted successfully.',
            ]);
        }

        return redirect()->route('admin.dashboard')->with('success', 'Gallery photo deleted successfully.');
    }

    public function saveTheme(Request $request)
    {
        $tenant = $this->tenant($request);
        // Plan-gated, not just getAvailableThemesForTenant() - the dashboard UI
        // already locks non-starter themes behind a Pro upsell, but that's
        // client-side only. Without this check here, a direct POST to this
        // endpoint could still set a Pro-only theme_id on a free tenant.
        $availableThemes = array_keys($tenant->onboardingAvailableThemes());

        \Log::info('saveTheme called', [
            'tenant_id' => $tenant->id,
            'tenant_subdomain' => $tenant->subdomain,
            'requested_theme' => $request->theme_id,
            'available' => $availableThemes
        ]);

        try {
            $request->validate([
                'theme_id' => 'required|string|in:' . implode(',', $availableThemes),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('saveTheme validation failed', ['errors' => $e->errors()]);
            throw $e;
        }

        $tenant->update([
            'theme_id' => $request->theme_id,
        ]);
        
        \Log::info('saveTheme updated successfully', ['new_theme' => $tenant->theme_id]);

        return response()->json([
            'success' => true,
            'message' => 'Bakery theme updated successfully!',
            'theme_id' => $tenant->theme_id,
        ]);
    }

    // Replaces the old saveContent()/admin.content.save — that route was never
    // called from any view or JS (confirmed dead code) despite being the only
    // place contact_hours/location/instagram/facebook were ever persisted.
    // This is the real, reachable home for those fields plus the tenant's
    // structured contact/address columns and SEO title/description, none of
    // which previously had any admin form field at all despite rendering on
    // the live storefront (contact_*) or feeding every page's meta tags and
    // LocalBusiness structured data (address/phone/socials, seo_title/description).
    public function saveBusinessInfo(Request $request)
    {
        $tenant = $this->tenant($request);

        $data = $request->validate([
            'contact_hours' => 'nullable|string|max:255',
            'contact_location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:120',
            'state' => 'nullable|string|max:120',
            'postal_code' => 'nullable|string|max:20',
            'instagram_url' => 'nullable|string|max:255',
            'facebook_url' => 'nullable|string|max:255',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'policy_deposit_percentage' => 'nullable|string|max:10',
            'policy_late_fee_percentage' => 'nullable|string|max:10',
            'policy_delivery_base_fee' => 'nullable|string|max:10',
            'policy_delivery_per_mile' => 'nullable|string|max:10',
            'policy_delivery_change_fee' => 'nullable|string|max:10',
            'policy_pickup_hours' => 'nullable|string|max:100',
            'policy_closed_days' => 'nullable|string|max:100',
            'policy_extra_layer_fee' => 'nullable|string|max:10',
            'about_testimonial_quote' => 'nullable|string|max:500',
            'about_testimonial_name' => 'nullable|string|max:100',
            'about_testimonial_role' => 'nullable|string|max:100',
            'menu_hero_subtitle' => 'nullable|string|max:100',
            'menu_hero_title' => 'nullable|string|max:150',
            'menu_hero_text' => 'nullable|string|max:500',
            'menu_empty_title' => 'nullable|string|max:150',
            'menu_empty_text' => 'nullable|string|max:500',
            'gallery_hero_title' => 'nullable|string|max:150',
            'gallery_hero_text' => 'nullable|string|max:500',
            'gallery_empty_title' => 'nullable|string|max:150',
            'gallery_empty_text' => 'nullable|string|max:1000',
            'policy_intro_text' => 'nullable|string|max:600',
        ]);

        $tenant->update([
            'phone' => $data['phone'] ?? $tenant->phone,
            'address_line1' => $data['address_line1'] ?? $tenant->address_line1,
            'address_line2' => $data['address_line2'] ?? $tenant->address_line2,
            'city' => $data['city'] ?? $tenant->city,
            'state' => $data['state'] ?? $tenant->state,
            'postal_code' => $data['postal_code'] ?? $tenant->postal_code,
            'instagram_url' => $data['instagram_url'] ?? $tenant->instagram_url,
            'facebook_url' => $data['facebook_url'] ?? $tenant->facebook_url,
        ]);

        $currentContent = $tenant->site_content ?? Tenant::getDefaultSiteContent();
        $updatedContent = array_merge($currentContent, [
            'contact_hours' => $data['contact_hours'] ?? ($currentContent['contact_hours'] ?? ''),
            'contact_location' => $data['contact_location'] ?? ($currentContent['contact_location'] ?? ''),
            'seo_title' => $data['seo_title'] ?? ($currentContent['seo_title'] ?? ''),
            'seo_description' => $data['seo_description'] ?? ($currentContent['seo_description'] ?? ''),
            'policy_deposit_percentage' => $data['policy_deposit_percentage'] ?? ($currentContent['policy_deposit_percentage'] ?? '50'),
            'policy_late_fee_percentage' => $data['policy_late_fee_percentage'] ?? ($currentContent['policy_late_fee_percentage'] ?? '10'),
            'policy_delivery_base_fee' => $data['policy_delivery_base_fee'] ?? ($currentContent['policy_delivery_base_fee'] ?? '30'),
            'policy_delivery_per_mile' => $data['policy_delivery_per_mile'] ?? ($currentContent['policy_delivery_per_mile'] ?? '2'),
            'policy_delivery_change_fee' => $data['policy_delivery_change_fee'] ?? ($currentContent['policy_delivery_change_fee'] ?? '15'),
            'policy_pickup_hours' => $data['policy_pickup_hours'] ?? ($currentContent['policy_pickup_hours'] ?? '10:00am – 4:00pm'),
            'policy_closed_days' => $data['policy_closed_days'] ?? ($currentContent['policy_closed_days'] ?? 'Sundays or Mondays'),
            'policy_extra_layer_fee' => $data['policy_extra_layer_fee'] ?? ($currentContent['policy_extra_layer_fee'] ?? '20'),
            'about_testimonial_quote' => $data['about_testimonial_quote'] ?? ($currentContent['about_testimonial_quote'] ?? ''),
            'about_testimonial_name' => $data['about_testimonial_name'] ?? ($currentContent['about_testimonial_name'] ?? ''),
            'about_testimonial_role' => $data['about_testimonial_role'] ?? ($currentContent['about_testimonial_role'] ?? ''),
            'menu_hero_subtitle' => $data['menu_hero_subtitle'] ?? ($currentContent['menu_hero_subtitle'] ?? ''),
            'menu_hero_title' => $data['menu_hero_title'] ?? ($currentContent['menu_hero_title'] ?? ''),
            'menu_hero_text' => $data['menu_hero_text'] ?? ($currentContent['menu_hero_text'] ?? ''),
            'menu_empty_title' => $data['menu_empty_title'] ?? ($currentContent['menu_empty_title'] ?? ''),
            'menu_empty_text' => $data['menu_empty_text'] ?? ($currentContent['menu_empty_text'] ?? ''),
            'gallery_hero_title' => $data['gallery_hero_title'] ?? ($currentContent['gallery_hero_title'] ?? ''),
            'gallery_hero_text' => $data['gallery_hero_text'] ?? ($currentContent['gallery_hero_text'] ?? ''),
            'gallery_empty_title' => $data['gallery_empty_title'] ?? ($currentContent['gallery_empty_title'] ?? ''),
            'gallery_empty_text' => $data['gallery_empty_text'] ?? ($currentContent['gallery_empty_text'] ?? ''),
            'policy_intro_text' => $data['policy_intro_text'] ?? ($currentContent['policy_intro_text'] ?? ''),
        ]);
        $tenant->update(['site_content' => $updatedContent]);

        return response()->json([
            'success' => true,
            'message' => 'Business info & SEO saved!',
            'site_content' => $tenant->fresh()->site_content,
        ]);
    }

    /**
     * Persist the tenant's custom brand colors (primary/secondary/button/text).
     * Each field is nullable -- clearing a color picker back to blank restores
     * that theme's own default, since the storefront override partial only
     * emits a <style> rule for colors that are actually set here.
     */
    public function saveBrandColors(Request $request)
    {
        $tenant = $this->tenant($request);

        // Blade sends an empty string (not an absent field) for a color the
        // baker has toggled off / reset to the theme default -- normalize
        // to null first so "nullable" actually short-circuits the regex
        // instead of failing validation on ''.
        $request->merge([
            'primary_color' => $request->input('primary_color') ?: null,
            'secondary_color' => $request->input('secondary_color') ?: null,
            'button_color' => $request->input('button_color') ?: null,
            'text_color' => $request->input('text_color') ?: null,
        ]);

        $data = $request->validate([
            'primary_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'button_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'text_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $tenant->update([
            'primary_color' => $data['primary_color'] ?? null,
            'secondary_color' => $data['secondary_color'] ?? null,
            'button_color' => $data['button_color'] ?? null,
            'text_color' => $data['text_color'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Brand colors saved!',
        ]);
    }

    public function saveSectionSettings(Request $request)
    {
        $tenant = $this->tenant($request);

        // 1. Process Section Order & Enabled status
        $sectionsData = $request->input('sections', []);
        $defaults = Tenant::getDefaultSectionSettings();

        $updatedSections = [];
        foreach ($defaults as $secId => $defaultSec) {
            $incoming = $sectionsData[$secId] ?? [];
            $updatedSections[$secId] = [
                'id' => $secId,
                'name' => $defaultSec['name'],
                'enabled' => filter_var($incoming['enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
                // (float), not (int) — the 'about' section defaults to order
                // 1.5 (between hero=1 and highlights=2) so it can be inserted
                // without renumbering every other section's already-persisted
                // order; truncating to int on save would tie it with hero.
                'order' => isset($incoming['order']) ? (float) $incoming['order'] : ($defaultSec['order'] ?? 1),
            ];
        }

        // 2. Process Section Copy & Dynamic Sub-Arrays
        $currentContent = $tenant->site_content ?? Tenant::getDefaultSiteContent();

        // Highlights array processing (up to 4 items)
        $highlightsInput = $request->input('highlights', []);
        $processedHighlights = [];
        foreach ($highlightsInput as $hl) {
            if (!empty($hl['title'])) {
                $processedHighlights[] = [
                    'icon' => $hl['icon'] ?? '🎂',
                    'title' => $hl['title'],
                    'desc' => $hl['desc'] ?? '',
                ];
            }
        }

        // How It Works array processing (3 steps)
        $howInput = $request->input('how_it_works', []);
        $processedHow = [];
        foreach ($howInput as $step) {
            if (!empty($step['title'])) {
                $processedHow[] = [
                    'title' => $step['title'],
                    'desc' => $step['desc'] ?? '',
                ];
            }
        }

        // Bullets processing
        $bullets = [];
        for ($i = 1; $i <= 5; $i++) {
            $bulletVal = $request->input("whimsical_bullet_$i");
            if (!empty($bulletVal)) {
                $bullets[] = $bulletVal;
            }
        }

        // Reviews array processing
        $reviewsInput = $request->input('reviews', []);
        $processedReviews = [];
        foreach ($reviewsInput as $rev) {
            if (!empty($rev['name']) && !empty($rev['quote'])) {
                $processedReviews[] = [
                    'name' => $rev['name'],
                    'quote' => $rev['quote'],
                    'stars' => isset($rev['stars']) ? (int)$rev['stars'] : 5,
                ];
            }
        }

        // FAQs array processing
        $faqsInput = $request->input('faqs', []);
        $processedFaqs = [];
        foreach ($faqsInput as $faq) {
            if (!empty($faq['q']) && !empty($faq['a'])) {
                $processedFaqs[] = [
                    'q' => $faq['q'],
                    'a' => $faq['a'],
                ];
            }
        }

        // Categories array processing (Title, Desc, Gallery Image select or direct upload)
        $categoriesInput = $request->input('categories', []);
        $processedCategories = [];
        foreach ($categoriesInput as $cIdx => $cat) {
            if (!empty($cat['title'])) {
                $imgUrl = $cat['image_url'] ?? '';

                if ($request->hasFile("category_image_$cIdx")) {
                    $cFile = $request->file("category_image_$cIdx");
                    if ($cFile->isValid()) {
                        $cFilename = 'cat_' . $tenant->id . '_' . time() . '_' . $cIdx . '.' . $cFile->getClientOriginalExtension();
                        $cDest = public_path('uploads/tenants/' . $tenant->id . '/categories');
                        if (!file_exists($cDest)) {
                            mkdir($cDest, 0755, true);
                        }
                        $cFile->move($cDest, $cFilename);
                        $imgUrl = 'uploads/tenants/' . $tenant->id . '/categories/' . $cFilename;
                    }
                }

                $processedCategories[] = [
                    'title' => $cat['title'],
                    'desc' => $cat['desc'] ?? '',
                    'image_url' => $imgUrl,
                ];
            }
        }

        // Featured Gallery selections (JSON array of {path, title} chosen from the Device Gallery picker)
        $featuredImagesRaw = $request->input('featured_gallery_images', '[]');
        $featuredImagesDecoded = json_decode($featuredImagesRaw, true);
        $processedFeaturedImages = [];
        if (is_array($featuredImagesDecoded)) {
            foreach ($featuredImagesDecoded as $fImg) {
                if (!empty($fImg['path'])) {
                    $processedFeaturedImages[] = [
                        'path' => $fImg['path'],
                        'title' => $fImg['title'] ?? '',
                    ];
                }
            }
        }

        $updatedContent = array_merge($currentContent, [
            'hero_subheading' => $request->input('hero_subheading', $currentContent['hero_subheading'] ?? ''),
            'hero_headline' => $request->input('hero_headline', $currentContent['hero_headline'] ?? ''),
            'hero_cta_primary' => $request->input('hero_cta_primary', $currentContent['hero_cta_primary'] ?? ''),
            'hero_cta_secondary' => $request->input('hero_cta_secondary', $currentContent['hero_cta_secondary'] ?? ''),
            'hero_bg_url' => $request->input('hero_bg_url', $currentContent['hero_bg_url'] ?? ''),
            'about_title' => $request->input('about_title', $currentContent['about_title'] ?? ''),
            'about_bio' => $request->input('about_bio', $currentContent['about_bio'] ?? ''),
            'categories' => !empty($processedCategories) ? $processedCategories : ($currentContent['categories'] ?? []),
            'highlights' => !empty($processedHighlights) ? $processedHighlights : ($currentContent['highlights'] ?? []),
            'promo_video_url' => $request->input('promo_video_url', $currentContent['promo_video_url'] ?? ''),
            'promo_headline' => $request->input('promo_headline', $currentContent['promo_headline'] ?? ''),
            'promo_subtext' => $request->input('promo_subtext', $currentContent['promo_subtext'] ?? ''),
            'how_it_works' => !empty($processedHow) ? $processedHow : ($currentContent['how_it_works'] ?? []),
            'whimsical_title' => $request->input('whimsical_title', $currentContent['whimsical_title'] ?? ''),
            'whimsical_image_url' => $request->input('whimsical_image_url', $currentContent['whimsical_image_url'] ?? ''),
            'whimsical_bullets' => !empty($bullets) ? $bullets : ($currentContent['whimsical_bullets'] ?? []),
            'reviews' => !empty($processedReviews) ? $processedReviews : ($currentContent['reviews'] ?? []),
            'faqs' => !empty($processedFaqs) ? $processedFaqs : ($currentContent['faqs'] ?? []),
            'cta_banner_url' => $request->input('cta_banner_url', $currentContent['cta_banner_url'] ?? ''),
            'cta_headline' => $request->input('cta_headline', $currentContent['cta_headline'] ?? ''),
            'cta_subtext' => $request->input('cta_subtext', $currentContent['cta_subtext'] ?? ''),
            'cta_btn_text' => $request->input('cta_btn_text', $currentContent['cta_btn_text'] ?? ''),
            'cta_btn_action' => $request->input('cta_btn_action', $currentContent['cta_btn_action'] ?? 'order'),
            'marquee_text' => $request->input('marquee_text', $currentContent['marquee_text'] ?? ''),
            'featured_gallery_title' => $request->input('featured_gallery_title', $currentContent['featured_gallery_title'] ?? ''),
            'featured_gallery_images' => $processedFeaturedImages,
        ]);

        $tenant->update([
            'section_settings' => $updatedSections,
            'site_content' => $updatedContent,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Homepage sections, order, media & copy saved successfully!',
            'section_settings' => $tenant->getOrderedSections(),
            'site_content' => $tenant->site_content,
        ]);
    }

    /**
     * Renders the real storefront theme template using whatever is currently
     * typed into the Page Builder form -- WITHOUT persisting anything -- so the
     * dashboard preview iframe can show unsaved edits. Field-parsing logic is
     * intentionally kept in lockstep with saveSectionSettings() above; the only
     * difference is the final step mutates $tenant in memory instead of calling
     * ->update(). Never add a ->save()/->update() call to this method.
     */
    public function previewSectionSettings(Request $request)
    {
        $tenant = $this->tenant($request);

        $sectionsData = $request->input('sections', []);
        $defaults = Tenant::getDefaultSectionSettings();

        $updatedSections = [];
        foreach ($defaults as $secId => $defaultSec) {
            $incoming = $sectionsData[$secId] ?? [];
            $updatedSections[$secId] = [
                'id' => $secId,
                'name' => $defaultSec['name'],
                'enabled' => filter_var($incoming['enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'order' => isset($incoming['order']) ? (float) $incoming['order'] : ($defaultSec['order'] ?? 1),
            ];
        }

        $currentContent = $tenant->site_content ?? Tenant::getDefaultSiteContent();

        $highlightsInput = $request->input('highlights', []);
        $processedHighlights = [];
        foreach ($highlightsInput as $hl) {
            if (!empty($hl['title'])) {
                $processedHighlights[] = [
                    'icon' => $hl['icon'] ?? '🎂',
                    'title' => $hl['title'],
                    'desc' => $hl['desc'] ?? '',
                ];
            }
        }

        $howInput = $request->input('how_it_works', []);
        $processedHow = [];
        foreach ($howInput as $step) {
            if (!empty($step['title'])) {
                $processedHow[] = [
                    'title' => $step['title'],
                    'desc' => $step['desc'] ?? '',
                ];
            }
        }

        $bullets = [];
        for ($i = 1; $i <= 5; $i++) {
            $bulletVal = $request->input("whimsical_bullet_$i");
            if (!empty($bulletVal)) {
                $bullets[] = $bulletVal;
            }
        }

        $reviewsInput = $request->input('reviews', []);
        $processedReviews = [];
        foreach ($reviewsInput as $rev) {
            if (!empty($rev['name']) && !empty($rev['quote'])) {
                $processedReviews[] = [
                    'name' => $rev['name'],
                    'quote' => $rev['quote'],
                    'stars' => isset($rev['stars']) ? (int)$rev['stars'] : 5,
                ];
            }
        }

        $faqsInput = $request->input('faqs', []);
        $processedFaqs = [];
        foreach ($faqsInput as $faq) {
            if (!empty($faq['q']) && !empty($faq['a'])) {
                $processedFaqs[] = [
                    'q' => $faq['q'],
                    'a' => $faq['a'],
                ];
            }
        }

        // Preview never accepts file uploads (this endpoint fires on every
        // keystroke and must not write to disk) -- the client strips file
        // inputs before sending, so this always reflects the existing
        // image_url that a real upload would already have populated.
        $categoriesInput = $request->input('categories', []);
        $processedCategories = [];
        foreach ($categoriesInput as $cat) {
            if (!empty($cat['title'])) {
                $processedCategories[] = [
                    'title' => $cat['title'],
                    'desc' => $cat['desc'] ?? '',
                    'image_url' => $cat['image_url'] ?? '',
                ];
            }
        }

        $featuredImagesRaw = $request->input('featured_gallery_images', '[]');
        $featuredImagesDecoded = json_decode($featuredImagesRaw, true);
        $processedFeaturedImages = [];
        if (is_array($featuredImagesDecoded)) {
            foreach ($featuredImagesDecoded as $fImg) {
                if (!empty($fImg['path'])) {
                    $processedFeaturedImages[] = [
                        'path' => $fImg['path'],
                        'title' => $fImg['title'] ?? '',
                    ];
                }
            }
        }

        $updatedContent = array_merge($currentContent, [
            'hero_subheading' => $request->input('hero_subheading', $currentContent['hero_subheading'] ?? ''),
            'hero_headline' => $request->input('hero_headline', $currentContent['hero_headline'] ?? ''),
            'hero_cta_primary' => $request->input('hero_cta_primary', $currentContent['hero_cta_primary'] ?? ''),
            'hero_cta_secondary' => $request->input('hero_cta_secondary', $currentContent['hero_cta_secondary'] ?? ''),
            'hero_bg_url' => $request->input('hero_bg_url', $currentContent['hero_bg_url'] ?? ''),
            'about_title' => $request->input('about_title', $currentContent['about_title'] ?? ''),
            'about_bio' => $request->input('about_bio', $currentContent['about_bio'] ?? ''),
            'categories' => !empty($processedCategories) ? $processedCategories : ($currentContent['categories'] ?? []),
            'highlights' => !empty($processedHighlights) ? $processedHighlights : ($currentContent['highlights'] ?? []),
            'promo_video_url' => $request->input('promo_video_url', $currentContent['promo_video_url'] ?? ''),
            'promo_headline' => $request->input('promo_headline', $currentContent['promo_headline'] ?? ''),
            'promo_subtext' => $request->input('promo_subtext', $currentContent['promo_subtext'] ?? ''),
            'how_it_works' => !empty($processedHow) ? $processedHow : ($currentContent['how_it_works'] ?? []),
            'whimsical_title' => $request->input('whimsical_title', $currentContent['whimsical_title'] ?? ''),
            'whimsical_image_url' => $request->input('whimsical_image_url', $currentContent['whimsical_image_url'] ?? ''),
            'whimsical_bullets' => !empty($bullets) ? $bullets : ($currentContent['whimsical_bullets'] ?? []),
            'reviews' => !empty($processedReviews) ? $processedReviews : ($currentContent['reviews'] ?? []),
            'faqs' => !empty($processedFaqs) ? $processedFaqs : ($currentContent['faqs'] ?? []),
            'cta_banner_url' => $request->input('cta_banner_url', $currentContent['cta_banner_url'] ?? ''),
            'cta_headline' => $request->input('cta_headline', $currentContent['cta_headline'] ?? ''),
            'cta_subtext' => $request->input('cta_subtext', $currentContent['cta_subtext'] ?? ''),
            'cta_btn_text' => $request->input('cta_btn_text', $currentContent['cta_btn_text'] ?? ''),
            'cta_btn_action' => $request->input('cta_btn_action', $currentContent['cta_btn_action'] ?? 'order'),
            'marquee_text' => $request->input('marquee_text', $currentContent['marquee_text'] ?? ''),
            'featured_gallery_title' => $request->input('featured_gallery_title', $currentContent['featured_gallery_title'] ?? ''),
            'featured_gallery_images' => $processedFeaturedImages,
        ]);

        // In-memory only -- deliberately no ->save()/->update() call.
        $tenant->section_settings = $updatedSections;
        $tenant->site_content = $updatedContent;

        if (empty($tenant->booking_settings)) {
            $tenant->booking_settings = [
                'lead_time_enabled' => true,
                'lead_time_days' => 3,
                'recurring_closed_days' => [0, 1],
                'blocked_dates' => [],
            ];
        }

        $products = Product::where('tenant_id', $tenant->id)->where('is_active', true)->orderBy('sort_order')->get();
        $maxReviews = $tenant->max_reviews_display ?? 3;
        $reviews = Review::where('tenant_id', $tenant->id)->where('is_featured', true)->latest()->limit($maxReviews)->get();
        $gallery = GalleryItem::where('tenant_id', $tenant->id)->latest()->get();

        return view($tenant->themeView('index'), compact('tenant', 'products', 'reviews', 'gallery'));
    }

    public function uploadMedia(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200',
        ]);

        if ($request->hasFile('file')) {
            $tenant = $this->tenant($request);
            $file = $request->file('file');
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
            $destinationPath = public_path('uploads/tenants/' . $tenant->id . '/media');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);
            $url = 'uploads/tenants/' . $tenant->id . '/media/' . $filename;

            return response()->json([
                'success' => true,
                'url' => $url,
                'message' => 'Media uploaded successfully!',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No file uploaded.',
        ], 400);
    }

    // ─── New: Order Status Management ───

    public function updateOrderStatus(Request $request, Order $order)
    {
        $tenant = $this->tenant($request);

        // Security: ensure order belongs to this tenant
        if ($order->tenant_id !== $tenant->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => 'required|string|in:new,invoiced,in_progress,ready,completed,cancelled,paid',
        ]);

        $order->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Order status updated to: ' . ucfirst(str_replace('_', ' ', $request->status)),
            'status' => $order->status,
        ]);
    }

    // ─── New: Review Management ───

    public function storeReview(Request $request)
    {
        $tenant = $this->tenant($request);

        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'review_text' => 'required|string|max:2000',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        $review = Review::create([
            'tenant_id' => $tenant->id,
            'client_name' => $validated['client_name'],
            'review_text' => $validated['review_text'],
            'rating' => $validated['rating'] ?? 5,
            'is_featured' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review published successfully!',
            'review' => $review,
        ]);
    }

    public function deleteReview(Request $request, Review $review)
    {
        $tenant = $this->tenant($request);

        if ($review->tenant_id !== $tenant->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully.',
        ]);
    }

    // ─── New: Customer Management ───

    public function storeCustomer(Request $request)
    {
        $tenant = $this->tenant($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:2000',
        ]);

        $customer = Customer::create([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'notes' => $validated['notes'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Customer added successfully!',
            'customer' => $customer,
        ]);
    }

    // ─── New: Invoice Management ───

    public function createInvoice(Request $request)
    {
        $tenant = $this->tenant($request);

        if (empty($tenant->normalizedPaymentMethods())) {
            return response()->json([
                'success' => false,
                'requires_payment_setup' => true,
                'message' => 'Set up at least one payment method before invoicing a customer.',
            ], 422);
        }

        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'subtotal' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'fee_amount' => 'nullable|numeric|min:0',
            'fee_label' => 'nullable|string|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_label' => 'nullable|string|max:100',
            'misc_amount' => 'nullable|numeric|min:0',
            'misc_label' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:2000',
            'mark_invoiced' => 'nullable|boolean',
        ]);

        $order = Order::where('tenant_id', $tenant->id)->findOrFail($validated['order_id']);

        $invoiceNumber = 'INV-' . strtoupper(Str::random(6));
        $subtotal = $validated['subtotal'] ?? $order->total_price;

        $invoice = Invoice::create([
            'tenant_id' => $tenant->id,
            'order_id' => $order->id,
            'invoice_number' => $invoiceNumber,
            'client_name' => $order->client_name,
            'client_email' => $order->client_email,
            'subtotal' => $subtotal,
            // Total defaults to the plain subtotal+adjustments math, but the
            // baker can still submit their own total_amount to override it —
            // the fields are assistive, not a locked formula, since the whole
            // point is giving them freedom to adjust the final number.
            'total_amount' => $validated['total_amount'] ?? (
                $subtotal + ($validated['fee_amount'] ?? 0) - ($validated['discount_amount'] ?? 0) + ($validated['misc_amount'] ?? 0)
            ),
            'fee_amount' => $validated['fee_amount'] ?? 0,
            'fee_label' => $validated['fee_label'] ?? null,
            'discount_amount' => $validated['discount_amount'] ?? 0,
            'discount_label' => $validated['discount_label'] ?? null,
            'misc_amount' => $validated['misc_amount'] ?? 0,
            'misc_label' => $validated['misc_label'] ?? null,
            'deposit_amount' => $validated['deposit_amount'] ?? $order->deposit_amount,
            'status' => 'unpaid',
            'due_date' => $order->due_date,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Only update order status to invoiced if explicitly requested
        if ($request->boolean('mark_invoiced', false)) {
            $order->update(['status' => 'invoiced']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Invoice ' . $invoiceNumber . ' created!',
            'invoice' => $invoice,
        ]);
    }

    public function sendInvoice(Request $request, Invoice $invoice)
    {
        $tenant = $this->tenant($request);

        if ($invoice->tenant_id !== $tenant->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Build payment methods from tenant settings
        $paymentSettings = $tenant->normalizedPaymentMethods();

        if (empty($paymentSettings)) {
            return response()->json([
                'success' => false,
                'requires_payment_setup' => true,
                'message' => 'Set up at least one payment method before sending this invoice.',
            ], 422);
        }

        try {
            Mail::send('emails.invoice', [
                'invoice' => $invoice,
                'tenant' => $tenant,
                'paymentSettings' => $paymentSettings,
            ], function ($message) use ($invoice, $tenant) {
                $message->to($invoice->client_email)
                    ->subject('Invoice ' . $invoice->invoice_number . ' from ' . $tenant->name)
                    ->from(config('mail.from.address'), $tenant->name);
            });

            // We do not change status to "sent" because our enum does not have "sent", 
            // and we rely on the dropdown now. It stays "unpaid" or whatever it was.

            return response()->json([
                'success' => true,
                'message' => 'Invoice sent to ' . $invoice->client_email,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateInvoice(Request $request, Invoice $invoice)
    {
        $tenant = $this->tenant($request);

        if ($invoice->tenant_id !== $tenant->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'subtotal' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'deposit_amount' => 'required|numeric|min:0',
            'fee_amount' => 'nullable|numeric|min:0',
            'fee_label' => 'nullable|string|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_label' => 'nullable|string|max:100',
            'misc_amount' => 'nullable|numeric|min:0',
            'misc_label' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:2000',
        ]);

        $invoice->update([
            'subtotal' => $validated['subtotal'] ?? $invoice->subtotal,
            'total_amount' => $validated['total_amount'],
            'deposit_amount' => $validated['deposit_amount'],
            'fee_amount' => $validated['fee_amount'] ?? 0,
            'fee_label' => $validated['fee_label'] ?? null,
            'discount_amount' => $validated['discount_amount'] ?? 0,
            'discount_label' => $validated['discount_label'] ?? null,
            'misc_amount' => $validated['misc_amount'] ?? 0,
            'misc_label' => $validated['misc_label'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Invoice updated successfully!',
            'invoice' => $invoice,
        ]);
    }

    public function destroyInvoice(Request $request, Invoice $invoice)
    {
        $tenant = $this->tenant($request);

        if ($invoice->tenant_id !== $tenant->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $invoiceNumber = $invoice->invoice_number;
        $invoice->delete();

        return response()->json([
            'success' => true,
            'message' => 'Invoice ' . $invoiceNumber . ' deleted successfully!',
        ]);
    }

    public function updateInvoiceStatus(Request $request, Invoice $invoice)
    {
        $tenant = $this->tenant($request);

        if ($invoice->tenant_id !== $tenant->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|string|in:unpaid,deposit_paid,paid_in_full,cancelled'
        ]);

        $invoice->update([
            'status' => $validated['status']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Invoice status updated!',
            'status' => $invoice->status,
        ]);
    }

    // ─── Payment Methods ───

    /**
     * Known rails the checkbox UI offers. Anything else previously stored
     * under payment_settings (e.g. a legacy custom entry) is left untouched.
     */
    private const KNOWN_PAYMENT_METHODS = ['venmo', 'cashapp', 'zelle', 'paypal', 'square', 'apple_pay', 'stripe'];

    public function savePaymentMethods(Request $request)
    {
        $tenant = $this->tenant($request);

        $validated = $request->validate([
            'methods' => 'required|array',
            'methods.*' => 'nullable|string|max:150',
        ]);

        $settings = $tenant->payment_settings ?? [];

        foreach (self::KNOWN_PAYMENT_METHODS as $key) {
            $handle = trim((string) ($validated['methods'][$key] ?? ''));
            if ($handle !== '') {
                $settings[$key] = $handle;
            } else {
                unset($settings[$key]);
            }
        }

        $tenant->update(['payment_settings' => $settings]);

        return response()->json([
            'success' => true,
            'message' => 'Payment methods updated!',
            'methods' => $tenant->refresh()->normalizedPaymentMethods(),
        ]);
    }

    // ─── New: Custom Domain ───

    public function saveCustomDomain(Request $request)
    {
        $tenant = $this->tenant($request);

        $validated = $request->validate([
            'custom_domain' => 'nullable|string|max:255',
        ]);

        $domain = strtolower(trim($validated['custom_domain'] ?? ''));
        $domain = preg_replace('#^https?://#', '', $domain);
        $domain = trim($domain, '/');

        if ($domain && $tenant->plan_tier !== 'pro') {
            return response()->json([
                'success' => false,
                'message' => 'Custom domains are available on Doughmain Pro only. Upgrade to connect your own domain.',
            ], 403);
        }

        // Basic validation: no spaces, has a dot
        if ($domain && (!str_contains($domain, '.') || str_contains($domain, ' '))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid domain format.',
            ], 422);
        }

        // Ensure uniqueness
        if ($domain && Tenant::where('custom_domain', $domain)->where('id', '!=', $tenant->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This domain is already connected to another account.',
            ], 422);
        }

        $previousDomain = $tenant->custom_domain;

        if ($domain !== $previousDomain) {
            // A changed or removed domain means the old routing entry (if any) is stale.
            if ($previousDomain) {
                $tenant->domains()->where('domain', strtolower($previousDomain))->delete();
            }

            $tenant->update([
                'custom_domain' => $domain ?: null,
                'custom_domain_status' => $domain ? 'pending' : 'unverified',
                'custom_domain_token' => $domain ? Str::random(32) : null,
                'custom_domain_verified_at' => null,
                'custom_domain_last_checked_at' => null,
                'custom_domain_last_error' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $domain ? 'Custom domain saved. Add the TXT record shown below, then click Verify DNS.' : 'Custom domain removed.',
            'status' => $tenant->custom_domain_status,
            'verification_token' => $tenant->custom_domain_token,
        ]);
    }

    public function verifyCustomDomain(Request $request)
    {
        $tenant = $this->tenant($request);

        if ($tenant->plan_tier !== 'pro') {
            return response()->json([
                'success' => false,
                'message' => 'Custom domain verification is available only for Pro users.',
            ], 403);
        }

        if (!$tenant->custom_domain) {
            return response()->json([
                'success' => false,
                'message' => 'Save a custom domain first, then verify it.',
            ], 422);
        }

        $tenant->update(['custom_domain_status' => 'pending']);

        \App\Jobs\VerifyCustomDomainJob::dispatch($tenant->id, $tenant->custom_domain);

        return response()->json([
            'success' => true,
            'message' => 'Verification queued — this checks in the background and usually finishes within a few minutes. Refresh the status below.',
            'status' => 'pending',
        ]);
    }

    public function customDomainStatus(Request $request)
    {
        $tenant = $this->tenant($request);

        return response()->json([
            'success' => true,
            'custom_domain' => $tenant->custom_domain,
            'status' => $tenant->custom_domain_status,
            'verification_token' => $tenant->custom_domain_token,
            'verified_at' => $tenant->custom_domain_verified_at,
            'last_checked_at' => $tenant->custom_domain_last_checked_at,
            'last_error' => $tenant->custom_domain_last_error,
        ]);
    }

    // ─── New: Review Display Settings ───

    public function saveReviewSettings(Request $request)
    {
        $tenant = $this->tenant($request);

        $validated = $request->validate([
            'max_reviews_display' => 'required|integer|min:1|max:50',
        ]);

        $tenant->update([
            'max_reviews_display' => $validated['max_reviews_display'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review settings updated!',
        ]);
    }

    /**
     * Cancel Bakery Subscription (Baker Portal).
     */
    public function cancelSubscription(Request $request)
    {
        $tenant = $this->tenant($request);

        if ($tenant->stripe_subscription_id) {
            try {
                $stripe = new StripeClient(config('services.stripe.secret'));
                $subscription = $stripe->subscriptions->retrieve($tenant->stripe_subscription_id);
                if ($subscription->status !== 'canceled') {
                    $stripe->subscriptions->cancel($tenant->stripe_subscription_id);
                }
            } catch (ApiErrorException $e) {
                \Illuminate\Support\Facades\Log::error('Stripe subscription cancel failed', [
                    'tenant_id' => $tenant->id,
                    'stripe_subscription_id' => $tenant->stripe_subscription_id,
                    'error' => $e->getMessage(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'We could not cancel your subscription with our payment provider. Please contact support.',
                ], 502);
            }
        }

        $tenant->update([
            'is_active' => false,
            'plan_tier' => 'canceled',
        ]);

        \App\Models\AuditLog::logEvent('billing.cancel_subscription', $tenant->id, $request->user()?->id, [
            'stripe_subscription_id' => $tenant->stripe_subscription_id,
        ], 'info');

        return response()->json([
            'success' => true,
            'message' => 'Subscription canceled successfully.',
        ]);
    }

    /**
     * Submit Support Ticket (Baker Portal).
     */
    public function submitSupportTicket(Request $request)
    {
        $tenant = $this->tenant($request);
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:3000',
            'type' => 'nullable|string|in:support,billing,custom_code,feature_request',
        ]);

        $ticket = SupportTicket::create([
            'tenant_id' => $tenant->id,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'type' => $validated['type'] ?? 'support',
            'status' => 'open',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Support ticket submitted! Our team will respond shortly.',
            'ticket' => $ticket,
        ]);
    }

    /**
     * Upload and update Bakery Logo (Baker Portal Settings).
     */
    public function saveLogo(Request $request)
    {
        $tenant = $this->tenant($request);

        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
        ]);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'logo_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $destPath = public_path('uploads/tenants/' . $tenant->id . '/logos');
            if (!file_exists($destPath)) {
                mkdir($destPath, 0755, true);
            }
            $file->move($destPath, $filename);

            $logoPath = 'uploads/tenants/' . $tenant->id . '/logos/' . $filename;
            $tenant->update(['logo_path' => $logoPath]);

            return response()->json([
                'success' => true,
                'message' => 'Bakery logo updated successfully!',
                'logo_path' => asset($logoPath),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No image file selected.',
        ], 400);
    }

    public function saveMenuSettings(Request $request)
    {
        $tenant = $this->tenant($request);

        $request->validate([
            'menu_type' => 'nullable|string|in:text,image,both',
            'menu_text' => 'nullable|string',
            'menu_image' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:10240',
        ]);

        $siteContent = $tenant->site_content ?? [];
        $menuContent = $siteContent['menu'] ?? [];

        $menuContent['menu_type'] = $request->input('menu_type', 'both');
        $menuContent['menu_text'] = $request->input('menu_text', '') ?? '';

        if ($request->boolean('remove_menu_image')) {
            $menuContent['menu_image_path'] = null;
        }

        if ($request->hasFile('menu_image')) {
            $file = $request->file('menu_image');
            $filename = 'menu_' . $tenant->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $destPath = public_path('uploads/tenants/' . $tenant->id . '/menu');
            if (!file_exists($destPath)) {
                mkdir($destPath, 0755, true);
            }
            $file->move($destPath, $filename);
            $menuContent['menu_image_path'] = 'uploads/tenants/' . $tenant->id . '/menu/' . $filename;
        }

        $siteContent['menu'] = $menuContent;
        $tenant->site_content = $siteContent;
        $tenant->update(['site_content' => $siteContent]);

        return response()->json([
            'success' => true,
            'message' => 'Bakery menu and pricing settings saved successfully!',
            'menu' => $menuContent,
        ]);
    }

    public function storeProduct(Request $request, $subdomain = null)
    {
        $tenant = $this->tenant($request, $subdomain);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category' => 'nullable|string|max:255',
        ]);

        $maxSort = Product::where('tenant_id', $tenant->id)->max('sort_order') ?? 0;
        $category = !empty($validated['category']) && $validated['category'] !== 'custom_new' ? $validated['category'] : 'General';

        $product = Product::create([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'price' => $validated['price'],
            'category' => $category,
            'is_active' => true,
            'sort_order' => $maxSort + 1,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Product added successfully!',
                'product' => $product,
            ]);
        }

        return redirect()->back()->with('success', 'Product added successfully!');
    }

    public function updateProduct(Request $request, $id, $subdomain = null)
    {
        $tenant = $this->tenant($request, $subdomain);
        $product = Product::where('tenant_id', $tenant->id)->where('id', $id)->firstOrFail();

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'category' => 'sometimes|nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        $product->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully!',
                'product' => $product,
            ]);
        }

        return redirect()->back()->with('success', 'Product updated successfully!');
    }

    public function destroyProduct(Request $request, $id, $subdomain = null)
    {
        $tenant = $this->tenant($request, $subdomain);
        $product = Product::where('tenant_id', $tenant->id)->where('id', $id)->firstOrFail();
        $product->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully!',
            ]);
        }

        return redirect()->back()->with('success', 'Product deleted successfully!');
    }
}
