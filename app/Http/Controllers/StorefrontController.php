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
}
