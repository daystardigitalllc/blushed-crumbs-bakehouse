<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\Product;
use App\Models\Review;
use App\Models\GalleryItem;
use App\Models\Order;
use App\Mail\NewOrderNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class StorefrontController extends Controller
{
    public function index(Request $request)
    {
        $host = $request->getHost();

        // 1. Multi-Tenant Domain Routing
        $tenant = Tenant::where('domain', $host)
            ->orWhere('slug', 'blushedcrumbs')
            ->first();

        // Safe Fallback: Auto-create tenant if database is empty or fresh
        if (!$tenant) {
            $tenant = Tenant::firstOrCreate(
                ['slug' => 'blushedcrumbs'],
                [
                    'name' => 'Blushed Crumbs Bakehouse',
                    'domain' => 'blushed-crumbs-bakehouse.test',
                    'subdomain' => 'blushedcrumbs',
                    'owner_name' => 'Baker',
                    'email' => 'orders@blushedcrumbsbakehouse.com',
                    'plan_tier' => 'pro',
                ]
            );
        }

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

        // 2. If visiting the main SaaS domain (e.g. bakebox.daystardigital.co), render SaaS Landing Page
        if ($host === 'bakebox.daystardigital.co' && !$request->has('tenant')) {
            return view('saas.landing', [
                'pricing' => [
                    'standard' => 29,
                    'pro' => 50,
                ]
            ]);
        }

        // Render client storefront for Blushed Crumbs Bakehouse
        $products = Product::where('tenant_id', $tenant->id)->where('is_active', true)->orderBy('sort_order')->get();
        $reviews = Review::where('tenant_id', $tenant->id)->where('is_featured', true)->latest()->get();
        $gallery = GalleryItem::where('tenant_id', $tenant->id)->latest()->get();

        return view('storefront.index', compact('tenant', 'products', 'reviews', 'gallery'));
    }

    public function about(Request $request)
    {
        $tenant = Tenant::where('slug', 'blushedcrumbs')->first();
        if (!$tenant) {
            $tenant = Tenant::firstOrCreate(
                ['slug' => 'blushedcrumbs'],
                [
                    'name' => 'Blushed Crumbs Bakehouse',
                    'domain' => 'blushed-crumbs-bakehouse.test',
                    'subdomain' => 'blushedcrumbs',
                    'owner_name' => 'Baker',
                    'email' => 'orders@blushedcrumbsbakehouse.com',
                    'plan_tier' => 'pro',
                ]
            );
        }

        return view('storefront.about', compact('tenant'));
    }

    public function gallery(Request $request)
    {
        $tenant = Tenant::where('slug', 'blushedcrumbs')->first();
        if (!$tenant) {
            $tenant = Tenant::firstOrCreate(
                ['slug' => 'blushedcrumbs'],
                [
                    'name' => 'Blushed Crumbs Bakehouse',
                    'domain' => 'blushed-crumbs-bakehouse.test',
                    'subdomain' => 'blushedcrumbs',
                    'owner_name' => 'Baker',
                    'email' => 'orders@blushedcrumbsbakehouse.com',
                    'plan_tier' => 'pro',
                ]
            );
        }

        $gallery = GalleryItem::where('tenant_id', $tenant->id)->latest()->get();
        return view('storefront.gallery', compact('tenant', 'gallery'));
    }

    public function submitOrder(Request $request)
    {
        $tenant = Tenant::where('slug', 'blushedcrumbs')->first();

        if (!$tenant) {
            $tenant = Tenant::firstOrCreate(
                ['slug' => 'blushedcrumbs'],
                [
                    'name' => 'Blushed Crumbs Bakehouse',
                    'domain' => 'blushed-crumbs-bakehouse.test',
                    'subdomain' => 'blushedcrumbs',
                    'owner_name' => 'Baker',
                    'email' => 'orders@blushedcrumbsbakehouse.com',
                    'plan_tier' => 'pro',
                ]
            );
        }

        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'client_email' => 'required|email|max:255',
            'client_phone' => 'required|string|max:50',
            'due_date' => 'nullable|string',
            'time_slot' => 'nullable|string',
            'fulfillment_type' => 'nullable|string',
            'delivery_address' => 'nullable|string',
            'special_notes' => 'nullable|string',
            'allergies' => 'nullable|string',
            'total_price' => 'nullable|numeric',
        ]);

        // Parse array parameters if passed as JSON strings via FormData
        $items = $request->input('items');
        if (is_string($items)) { $items = json_decode($items, true); }
        
        $flavors = $request->input('flavors');
        if (is_string($flavors)) { $flavors = json_decode($flavors, true); }

        $frosting = $request->input('frosting');
        if (is_string($frosting)) { $frosting = json_decode($frosting, true); }

        $fillings = $request->input('fillings');
        if (is_string($fillings)) { $fillings = json_decode($fillings, true); }

        $socialFollows = $request->input('social_follows');
        if (is_string($socialFollows)) { $socialFollows = json_decode($socialFollows, true); }

        // Process file uploads
        $inspirationFiles = [];
        if ($request->hasFile('inspiration_files')) {
            $destinationPath = public_path('uploads/inspiration');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            foreach ($request->file('inspiration_files') as $file) {
                if ($file->isValid()) {
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move($destinationPath, $filename);
                    $inspirationFiles[] = 'uploads/inspiration/' . $filename;
                }
            }
        }

        $orderNumber = 'BC-' . rand(1000, 9999);
        $totalPrice = (float) ($request->input('total_price') ?? 0.00);
        $depositAmount = round($totalPrice * 0.5, 2);
        $dueDate = $request->input('due_date') ?: now()->addDays(7)->format('Y-m-d');

        $order = Order::create([
            'tenant_id' => $tenant->id,
            'order_number' => $orderNumber,
            'client_name' => $request->input('client_name'),
            'client_email' => $request->input('client_email'),
            'client_phone' => $request->input('client_phone'),
            'due_date' => $dueDate,
            'time_slot' => $request->input('time_slot', '8:30 AM'),
            'fulfillment_type' => $request->input('fulfillment_type', 'pickup'),
            'delivery_address' => $request->input('delivery_address'),
            'items' => $items ?? [],
            'flavors' => $flavors ?? [],
            'frosting' => $frosting ?? [],
            'fillings' => $fillings ?? [],
            'special_notes' => $request->input('special_notes'),
            'allergies' => $request->input('allergies'),
            'social_follows' => $socialFollows ?? [],
            'inspiration_files' => $inspirationFiles,
            'total_price' => $totalPrice,
            'deposit_amount' => $depositAmount,
            'status' => 'new',
        ]);

        // Send Email Notification via SMTP to baker
        $routingEmail = $tenant->email ?? 'orders@blushedcrumbsbakehouse.com';
        $emailSent = false;
        $emailError = null;

        try {
            Mail::to($routingEmail)->send(new NewOrderNotification($order, $tenant));
            $emailSent = true;
        } catch (\Exception $e) {
            $emailError = $e->getMessage();
            Log::error('SMTP Email Order Error: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Order submitted successfully!',
            'order_number' => $order->order_number,
            'email_sent' => $emailSent,
            'email_error' => $emailError,
            'routing_email' => $routingEmail,
            'order' => $order,
        ]);
    }
}
