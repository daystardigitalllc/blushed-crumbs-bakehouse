<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        $tenant = Tenant::where('slug', 'blushedcrumbs')->firstOrFail();

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
}
