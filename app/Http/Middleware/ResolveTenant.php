<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Tenant;
use App\Models\Brand;

class ResolveTenant
{
    /**
     * Resolve the current tenant from the request hostname.
     *
     * Resolution order:
     * 1. Custom domain match (e.g. www.sweetmagnoliabakery.com)
     * 2. Subdomain extraction from brand domain (e.g. blushedcrumbs.doughmain.pro)
     * 3. Slug-based lookup (for local dev with .test domains)
     * 4. For /admin and /onboarding routes ONLY: fall back to authenticated user's tenant
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower($request->getHost());
        $tenant = null;

        // List of main SaaS platform brand domains that should NEVER resolve a single tenant for public routes
        $mainBrandDomains = [
            'doughmain.pro',
            'doughmain.pro.test',
            'localhost',
            '127.0.0.1',
        ];

        $isMainDomain = in_array($host, $mainBrandDomains);

        // ─── 1. Check for custom domain match (e.g. www.sweetmagnoliabakery.com) ───
        if (!$isMainDomain) {
            $tenant = Tenant::where('custom_domain', $host)->where('is_active', true)->first();
        }

        // ─── 2. Check for subdomain on a brand domain ───
        if (!$tenant && !$isMainDomain) {
            $brands = Brand::where('is_active', true)->get();
            foreach ($brands as $b) {
                $brandDomain = strtolower($b->domain); // e.g. "doughmain.pro"
                $subdomain = null;

                if (str_ends_with($host, '.' . $brandDomain)) {
                    $subdomain = str_replace('.' . $brandDomain, '', $host);
                } elseif (str_ends_with($host, '.' . $brandDomain . '.test')) {
                    $subdomain = str_replace('.' . $brandDomain . '.test', '', $host);
                }

                if ($subdomain && !in_array($subdomain, ['www', 'app', 'mail', 'admin', 'doughmain'])) {
                    $tenant = Tenant::where('subdomain', $subdomain)
                        ->where('is_active', true)
                        ->first();
                    if ($tenant) {
                        $request->attributes->set('brand', $b);
                        break;
                    }
                }
            }
        }

        // ─── 3. For local development: match by exact domain or slug ───
        if (!$tenant && !$isMainDomain) {
            $tenant = Tenant::where('domain', $host)->where('is_active', true)->first();
        }

        if (!$tenant && !$isMainDomain && str_ends_with($host, '.test')) {
            $parts = explode('.', $host);
            // e.g. mybakery.test -> subdomain 'mybakery'
            if (count($parts) === 2 && !in_array($parts[0], ['doughmain'])) {
                $subdomain = $parts[0];
                $tenant = Tenant::where('subdomain', $subdomain)->orWhere('slug', $subdomain)->where('is_active', true)->first();
            }
        }

        // Support ?bakery=subdomain or ?tenant=subdomain query parameter for easy local testing
        if (!$tenant && ($request->has('bakery') || $request->has('tenant'))) {
            $querySub = $request->query('bakery') ?: $request->query('tenant');
            $tenant = Tenant::where('subdomain', $querySub)->orWhere('slug', $querySub)->where('is_active', true)->first();
        }

        // ─── 4. For /admin and /onboarding routes ONLY: fall back to authenticated user's tenant ───
        if (!$tenant && auth()->check()) {
            $path = $request->path();
            if (str_starts_with($path, 'admin') || str_starts_with($path, 'onboarding')) {
                $tenant = auth()->user()->tenant;
            }
        }

        // ─── Store resolved tenant ───
        if ($tenant) {
            $request->attributes->set('tenant', $tenant);
            app()->instance('tenant', $tenant);

            if (!$request->attributes->has('brand') && $tenant->brand_id) {
                $request->attributes->set('brand', $tenant->brand);
            }
        }

        return $next($request);
    }
}
