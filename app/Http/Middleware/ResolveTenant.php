<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;

class ResolveTenant
{
    /**
     * Resolve the current tenant from the request hostname using the
     * domains table (Stancl\Tenancy\Database\Models\Domain). This replaced
     * the old host-parsing logic in Phase 2, after a clean shadow-mode
     * comparison period showed no mismatches.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower($request->getHost());

        $mainBrandDomains = [
            'doughmain.pro',
            'doughmain.pro.test',
            'localhost',
            '127.0.0.1',
        ];
        $isMainDomain = in_array($host, $mainBrandDomains);

        $tenant = null;
        if (!$isMainDomain) {
            $tenant = Domain::where('domain', $host)->first()?->tenant;
            if ($tenant && !$tenant->is_active) {
                $tenant = null;
            }
        }

        // Support ?bakery=subdomain or ?tenant=subdomain query parameter for easy local testing
        if (!$tenant && ($request->has('bakery') || $request->has('tenant'))) {
            $querySub = $request->query('bakery') ?: $request->query('tenant');
            $tenant = Tenant::where('subdomain', $querySub)->orWhere('slug', $querySub)->where('is_active', true)->first();
        }

        // For /admin and /onboarding routes ONLY: fall back to authenticated user's tenant
        if (!$tenant && auth()->check()) {
            $path = $request->path();
            if (str_starts_with($path, 'admin') || str_starts_with($path, 'onboarding')) {
                $tenant = auth()->user()->tenant;
            }
        }

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
