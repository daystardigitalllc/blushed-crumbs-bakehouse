<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Tenant;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Review;
use App\Models\GalleryItem;
use App\Models\SupportTicket;

class AdminController extends Controller
{
    public function dashboard(Request $request)
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

        return view('admin.dashboard', compact(
            'tenant', 'urgentOrders', 'allOrders', 'invoices', 
            'products', 'reviews', 'gallery', 'supportTickets'
        ));
    }

    public function saveFormSchema(Request $request)
    {
        $tenant = Tenant::where('slug', 'blushedcrumbs')->firstOrFail();
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
        $tenant = Tenant::where('slug', 'blushedcrumbs')->firstOrFail();
        
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

    public function storeGallery(Request $request)
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

        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:10240',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = time() . '_' . Str::slug($request->title) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('gallery', $fileName, 'public');
            $imageUrl = 'storage/' . $path;

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
        $tenant = Tenant::where('slug', 'blushedcrumbs')->first();
        if (!$tenant) {
            return response()->json(['success' => false, 'message' => 'Tenant not found.'], 404);
        }

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
}

