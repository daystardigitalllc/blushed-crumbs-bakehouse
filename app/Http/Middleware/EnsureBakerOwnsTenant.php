<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards every baker-admin route against cross-tenant access.
 *
 * ResolveTenant resolves the "current" tenant purely from the request's
 * domain/subdomain (or a {subdomain} route param), independent of who is
 * actually logged in. Because the login session cookie is shared across
 * every *.doughmain.pro subdomain (SESSION_DOMAIN=.doughmain.pro, so the
 * onboarding/login flow works seamlessly), an authenticated baker who
 * simply visits a *different* tenant's subdomain while still logged in
 * would otherwise land in that tenant's real admin dashboard - full read
 * and (via the POST/PUT/DELETE routes under the same route group) write
 * access to another bakery's orders, customers, settings, and Stripe
 * subscription. Confirmed live in production, not hypothetical.
 */
class EnsureBakerOwnsTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $subdomain = $request->route('subdomain');
        $tenant = $subdomain
            ? Tenant::where('subdomain', $subdomain)->orWhere('slug', $subdomain)->first()
            : $request->attributes->get('tenant');

        if ($tenant && $user->tenant_id !== $tenant->id) {
            return redirect()->route('baker.dashboard');
        }

        return $next($request);
    }
}
