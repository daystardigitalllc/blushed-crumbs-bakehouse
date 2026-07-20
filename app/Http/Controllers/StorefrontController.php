<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\Product;
use App\Models\Review;
use App\Models\GalleryItem;

class StorefrontController extends Controller
{
    public function index(Request $request)
    {
        // Fetch Tenant #1 (Blushed Crumbs Bakehouse)
        $tenant = Tenant::where('slug', 'blushedcrumbs')->firstOrFail();
        
        $products = Product::where('tenant_id', $tenant->id)->where('is_active', true)->orderBy('sort_order')->get();
        $reviews = Review::where('tenant_id', $tenant->id)->where('is_featured', true)->latest()->get();
        $gallery = GalleryItem::where('tenant_id', $tenant->id)->latest()->get();

        return view('storefront.index', compact('tenant', 'products', 'reviews', 'gallery'));
    }
}
