<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     * Restricts access to the Brand Admin Area (/admin) to superadmins only.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect('/login');
        }

        if ($user->role === 'superadmin' || $user->isSuperAdmin()) {
            return $next($request);
        }

        // Redirect regular bakery owners to their Baker Portal dashboard
        return redirect('/dashboard')->with('error', 'Access Denied: The Brand Admin Area is restricted to platform administrators.');
    }
}
