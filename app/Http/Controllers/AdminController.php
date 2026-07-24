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
                $sub = $user->tenant->subdomain ?? $user->tenant->slug;
                return redirect('/site/' . $sub . '/dashboard');
            }
            return redirect('/login');
        }

        $tenant = $this->tenant($request, $subdomain);

        // Fallback default form schema & booking settings if empty
        if (empty($tenant->form_schema)) {
            $tenant->form_schema = Tenant::getDefaultFormSchema();
            $tenant->save();
        }
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
            ->whereIn('status', ['new', 'invoiced', 'in_progress', 'ready'])
            ->orderBy('due_date', 'asc')
            ->get();

        $allOrders = Order::where('tenant_id', $tenant->id)->orderBy('due_date', 'asc')->get();
        $invoices = Invoice::where('tenant_id', $tenant->id)->latest()->get();
        $products = Product::where('tenant_id', $tenant->id)->orderBy('sort_order')->get();
        $reviews = Review::where('tenant_id', $tenant->id)->latest()->get();
        $gallery = GalleryItem::where('tenant_id', $tenant->id)->latest()->get();
        $supportTickets = SupportTicket::where('tenant_id', $tenant->id)->latest()->get();
        $customers = Customer::where('tenant_id', $tenant->id)->orderBy('total_spent', 'desc')->get();

        // Revenue stats
        $totalRevenue = Order::where('tenant_id', $tenant->id)
            ->whereIn('status', ['completed', 'in_progress', 'ready'])
            ->sum('total_price');
        $pendingOrders = Order::where('tenant_id', $tenant->id)
            ->whereIn('status', ['new', 'invoiced'])
            ->count();
        $customerCount = Customer::where('tenant_id', $tenant->id)->count();

        $serverBookingSettings = $tenant->booking_settings ?? [];
        $siteContent = $tenant->site_content ?? \App\Models\Tenant::getDefaultSiteContent();

        return view('admin.dashboard', compact(
            'tenant', 'urgentOrders', 'allOrders', 'invoices', 
            'products', 'reviews', 'gallery', 'supportTickets',
            'customers', 'totalRevenue', 'pendingOrders', 'customerCount',
            'serverBookingSettings', 'siteContent'
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

        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:10240',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = time() . '_' . Str::slug($request->title) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/gallery'), $fileName);
            $imageUrl = '/uploads/gallery/' . $fileName;

            $galleryItem = GalleryItem::create([
                'tenant_id' => $tenant->id,
                'title' => $request->title,
                'category' => $request->category,
                'image_url' => $imageUrl,
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Photo published to live gallery!',
                    'item' => [
                        'id' => $galleryItem->id,
                        'title' => $galleryItem->title,
                        'category' => $galleryItem->category,
                        'image_url' => asset($galleryItem->image_url),
                        'raw_url' => $galleryItem->image_url,
                    ],
                ]);
            }

            return redirect()->route('admin.dashboard')->with('success', 'Photo published to live gallery!');
        }

        return response()->json(['success' => false, 'message' => 'No image file uploaded.'], 422);
    }

    public function destroyGallery(Request $request, $id)
    {
        $tenant = $this->tenant($request);
        $item = GalleryItem::where('tenant_id', $tenant->id)->findOrFail($id);

        if ($item->image_url && str_starts_with($item->image_url, 'storage/')) {
            $relativePath = str_replace('storage/', '', $item->image_url);
            Storage::disk('public')->delete($relativePath);
        }

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
        $availableThemes = array_keys($tenant->getAvailableThemesForTenant());
        
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

    public function saveContent(Request $request)
    {
        $tenant = $this->tenant($request);

        $data = $request->validate([
            'hero_subheading' => 'nullable|string|max:255',
            'hero_headline' => 'nullable|string|max:255',
            'hero_cta_primary' => 'nullable|string|max:255',
            'hero_cta_secondary' => 'nullable|string|max:255',
            'whimsical_title' => 'nullable|string|max:255',
            'whimsical_bullet_1' => 'nullable|string|max:500',
            'whimsical_bullet_2' => 'nullable|string|max:500',
            'whimsical_bullet_3' => 'nullable|string|max:500',
            'whimsical_bullet_4' => 'nullable|string|max:500',
            'whimsical_bullet_5' => 'nullable|string|max:500',
            'about_title' => 'nullable|string|max:255',
            'about_bio' => 'nullable|string|max:2000',
            'contact_hours' => 'nullable|string|max:255',
            'contact_location' => 'nullable|string|max:255',
            'contact_instagram' => 'nullable|string|max:255',
            'contact_facebook' => 'nullable|string|max:255',
        ]);

        $currentContent = $tenant->site_content ?? Tenant::getDefaultSiteContent();

        $bullets = [];
        for ($i = 1; $i <= 5; $i++) {
            if (!empty($data["whimsical_bullet_$i"])) {
                $bullets[] = $data["whimsical_bullet_$i"];
            }
        }

        $updatedContent = array_merge($currentContent, [
            'hero_subheading' => $data['hero_subheading'] ?? $currentContent['hero_subheading'],
            'hero_headline' => $data['hero_headline'] ?? $currentContent['hero_headline'],
            'hero_cta_primary' => $data['hero_cta_primary'] ?? $currentContent['hero_cta_primary'],
            'hero_cta_secondary' => $data['hero_cta_secondary'] ?? $currentContent['hero_cta_secondary'],
            'whimsical_title' => $data['whimsical_title'] ?? $currentContent['whimsical_title'],
            'whimsical_bullets' => !empty($bullets) ? $bullets : ($currentContent['whimsical_bullets'] ?? []),
            'about_title' => $data['about_title'] ?? $currentContent['about_title'],
            'about_bio' => $data['about_bio'] ?? $currentContent['about_bio'],
            'contact_hours' => $data['contact_hours'] ?? $currentContent['contact_hours'],
            'contact_location' => $data['contact_location'] ?? $currentContent['contact_location'],
            'contact_instagram' => $data['contact_instagram'] ?? $currentContent['contact_instagram'],
            'contact_facebook' => $data['contact_facebook'] ?? $currentContent['contact_facebook'],
        ]);

        $tenant->update([
            'site_content' => $updatedContent,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bakery site copy & content saved successfully!',
            'site_content' => $tenant->site_content,
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
                'order' => isset($incoming['order']) ? (int) $incoming['order'] : ($defaultSec['order'] ?? 1),
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

        $updatedContent = array_merge($currentContent, [
            'hero_subheading' => $request->input('hero_subheading', $currentContent['hero_subheading'] ?? ''),
            'hero_headline' => $request->input('hero_headline', $currentContent['hero_headline'] ?? ''),
            'hero_cta_primary' => $request->input('hero_cta_primary', $currentContent['hero_cta_primary'] ?? ''),
            'hero_cta_secondary' => $request->input('hero_cta_secondary', $currentContent['hero_cta_secondary'] ?? ''),
            'hero_bg_url' => $request->input('hero_bg_url', $currentContent['hero_bg_url'] ?? ''),
            'highlights' => !empty($processedHighlights) ? $processedHighlights : ($currentContent['highlights'] ?? []),
            'promo_video_url' => $request->input('promo_video_url', $currentContent['promo_video_url'] ?? ''),
            'promo_headline' => $request->input('promo_headline', $currentContent['promo_headline'] ?? ''),
            'promo_subtext' => $request->input('promo_subtext', $currentContent['promo_subtext'] ?? ''),
            'how_it_works' => !empty($processedHow) ? $processedHow : ($currentContent['how_it_works'] ?? []),
            'whimsical_title' => $request->input('whimsical_title', $currentContent['whimsical_title'] ?? ''),
            'whimsical_bullets' => !empty($bullets) ? $bullets : ($currentContent['whimsical_bullets'] ?? []),
            'reviews' => !empty($processedReviews) ? $processedReviews : ($currentContent['reviews'] ?? []),
            'faqs' => !empty($processedFaqs) ? $processedFaqs : ($currentContent['faqs'] ?? []),
            'cta_banner_url' => $request->input('cta_banner_url', $currentContent['cta_banner_url'] ?? ''),
            'cta_headline' => $request->input('cta_headline', $currentContent['cta_headline'] ?? ''),
            'cta_subtext' => $request->input('cta_subtext', $currentContent['cta_subtext'] ?? ''),
            'cta_btn_text' => $request->input('cta_btn_text', $currentContent['cta_btn_text'] ?? ''),
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

    public function uploadMedia(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
            $destinationPath = public_path('uploads');
            
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            
            $file->move($destinationPath, $filename);
            $url = 'uploads/' . $filename;

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

        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'total_amount' => 'nullable|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
            'mark_invoiced' => 'nullable|boolean',
        ]);

        $order = Order::where('tenant_id', $tenant->id)->findOrFail($validated['order_id']);

        $invoiceNumber = 'INV-' . strtoupper(Str::random(6));

        $invoice = Invoice::create([
            'tenant_id' => $tenant->id,
            'order_id' => $order->id,
            'invoice_number' => $invoiceNumber,
            'client_name' => $order->client_name,
            'client_email' => $order->client_email,
            'total_amount' => $validated['total_amount'] ?? $order->total_price,
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
        $paymentSettings = $tenant->payment_settings ?? [];

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
            'total_amount' => 'required|numeric|min:0',
            'deposit_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
        ]);

        $invoice->update([
            'total_amount' => $validated['total_amount'],
            'deposit_amount' => $validated['deposit_amount'],
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


    // ─── New: Custom Domain ───

    public function saveCustomDomain(Request $request)
    {
        $tenant = $this->tenant($request);

        $validated = $request->validate([
            'custom_domain' => 'nullable|string|max:255',
        ]);

        $domain = $validated['custom_domain'];

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

        $tenant->update(['custom_domain' => $domain ?: null]);

        return response()->json([
            'success' => true,
            'message' => $domain ? 'Custom domain saved! Point your DNS to our server.' : 'Custom domain removed.',
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
        $tenant->update([
            'is_active' => false,
            'plan_tier' => 'canceled',
        ]);

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
            $file->move(public_path('uploads/logos'), $filename);
            
            $logoPath = '/uploads/logos/' . $filename;
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
}
