<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SESSION_DOMAIN is set to `.doughmain.pro` in production so a logged-in
 * baker's session (and CSRF cookie) is shared across every
 * `{subdomain}.doughmain.pro` page — that's what lets the "Baker Login" link
 * on the storefront and the dashboard work without re-authenticating per
 * subdomain (see EnsureBakerOwnsTenant's docblock).
 *
 * But a tenant's own custom domain (e.g. blushedcrumbsbakehouse.com) is not
 * a subdomain of doughmain.pro, and a browser will silently refuse to store
 * a cookie whose Domain attribute isn't the current host or a parent of it
 * (RFC 6265). With SESSION_DOMAIN fixed to `.doughmain.pro`, every session
 * (and CSRF) cookie Laravel tries to set on a custom domain is dropped by
 * the browser — the visitor never actually gets a session, so every POST
 * (order submission, newsletter signup, anything requiring CSRF) fails with
 * a 419 "Page Expired", 100% reproducible, on every custom domain. Confirmed
 * live on blushedcrumbsbakehouse.com: `Set-Cookie: ...; domain=.doughmain.pro`
 * on a page served from a completely different domain.
 *
 * Fix: only keep the shared `.doughmain.pro` cookie domain for requests
 * actually on that domain (or its subdomains, or local dev hosts). For any
 * other host — a tenant's custom domain — fall back to session.domain=null,
 * which makes Laravel scope the cookie to the exact current host instead,
 * so the session/CSRF cookie actually gets stored and round-trips normally.
 *
 * Must run before Illuminate\Session\Middleware\StartSession (which reads
 * config('session.domain') when queuing the session cookie) — registered
 * via $middleware->web(prepend: ...) in bootstrap/app.php, not append.
 */
class ScopeSessionDomainToRequestHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredDomain = config('session.domain');

        if ($configuredDomain) {
            $host = strtolower($request->getHost());
            $bareDomain = ltrim($configuredDomain, '.');
            $isOnConfiguredDomain = $host === $bareDomain || str_ends_with($host, '.' . $bareDomain);

            if (!$isOnConfiguredDomain) {
                config(['session.domain' => null]);
            }
        }

        return $next($request);
    }
}
