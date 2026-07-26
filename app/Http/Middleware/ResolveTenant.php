<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Tenant;
use App\Models\Brand;
use Stancl\Tenancy\Database\Models\Domain;

class ResolveTenant
{
    /**
     * Resolve the current tenant from the request hostname.
     *
     * PHASE 2: both the original host-parsing logic and the new domains-table
     * lookup are computed on every request and compared. Which one actually
     * drives the response is controlled by config('tenancy.resolution_strategy')
     * (env TENANT_RESOLUTION_STRATEGY, default 'legacy') so the cutover can be
     * flipped and reverted without a deploy.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower($request->getHost());

        // List of main SaaS platform brand domains that should NEVER resolve a single tenant for public routes
        $mainBrandDomains = [
            'doughmain.pro',
            'doughmain.pro.test',
            'localhost',
            '127.0.0.1',
        ];
        $isMainDomain = in_array($host, $mainBrandDomains);

        [$legacyTenant, $legacyBrand] = $this->resolveLegacy($host, $isMainDomain);
        $stanclTenant = $this->resolveViaDomainsTable($host, $isMainDomain);

        $strategy = config('tenancy.resolution_strategy', 'legacy');

        if (($legacyTenant?->id) !== ($stanclTenant?->id)) {
            Log::warning('Tenant resolution mismatch between legacy and stancl strategies', [
                'host' => $host,
                'path' => $request->path(),
                'legacy_tenant_id' => $legacyTenant?->id,
                'stancl_tenant_id' => $stanclTenant?->id,
                'active_strategy' => $strategy,
            ]);
        }

        $tenant = $strategy === 'stancl'
            ? ($stanclTenant ?? $legacyTenant)
            : $legacyTenant;
        $brand = $legacyBrand;

        // ─── Shared fallbacks (not host-resolution, so identical under either strategy) ───

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

        // ─── Store resolved tenant ───
        if ($tenant) {
            $request->attributes->set('tenant', $tenant);
            app()->instance('tenant', $tenant);

            if ($brand) {
                $request->attributes->set('brand', $brand);
            } elseif (!$request->attributes->has('brand') && $tenant->brand_id) {
                $request->attributes->set('brand', $tenant->brand);
            }
        }

        return $next($request);
    }

    /**
     * Original host-parsing resolution: custom domain, brand-subdomain stripping,
     * exact domain column match, and .test local-dev subdomain match.
     *
     * @return array{0: ?Tenant, 1: ?Brand}
     */
    private function resolveLegacy(string $host, bool $isMainDomain): array
    {
        $tenant = null;
        $brand = null;

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
                    $normalizedSub = str_replace('-', '', $subdomain);
                    $tenant = Tenant::where(function ($q) use ($subdomain, $normalizedSub) {
                        $q->where('subdomain', $subdomain)
                          ->orWhere('subdomain', $normalizedSub)
                          ->orWhere('slug', $subdomain)
                          ->orWhere('slug', $normalizedSub);
                    })->where('is_active', true)->first();

                    if ($tenant) {
                        $brand = $b;
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

        return [$tenant, $brand];
    }

    /**
     * PHASE 2 candidate resolution: look the host up directly in the domains
     * table populated by `tenants:backfill-domains`.
     */
    private function resolveViaDomainsTable(string $host, bool $isMainDomain): ?Tenant
    {
        if ($isMainDomain) {
            return null;
        }

        $tenant = Domain::where('domain', $host)->first()?->tenant;

        return ($tenant && $tenant->is_active) ? $tenant : null;
    }
}
