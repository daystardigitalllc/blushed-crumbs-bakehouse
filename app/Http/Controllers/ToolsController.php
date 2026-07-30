<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ToolsController extends Controller
{
    // The free-tools marketing pages belong only on Doughmain's own domain,
    // never on a tenant's storefront domain (e.g. blushedcrumbsbakehouse.com)
    // even if that domain happens to fall through to this same app instance.
    public const ALLOWED_HOSTS = [
        'doughmain.pro',
        'doughmain.pro.test',
        'staging.doughmain.pro',
        'localhost',
        '127.0.0.1',
    ];

    public static function isAllowedHost(?string $host): bool
    {
        return in_array(strtolower((string) $host), self::ALLOWED_HOSTS, true);
    }

    public function pricingCalculator(Request $request)
    {
        if (!self::isAllowedHost($request->getHost())) {
            abort(404);
        }

        return view('tools.pricing-calculator');
    }
}
