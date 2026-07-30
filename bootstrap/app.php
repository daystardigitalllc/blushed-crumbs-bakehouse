<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Deliberately NOT part of the `web` group — see routes/onboarding.php.
            Route::middleware([])->group(base_path('routes/onboarding.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\ResolveTenant::class,
        ]);

        $middleware->alias([
            'tenant.owner' => \App\Http\Middleware\EnsureBakerOwnsTenant::class,
        ]);

        // Stripe calls this server-to-server with no session/CSRF token —
        // the Stripe-Signature header (verified in StripeWebhookController)
        // is the real authenticity check for this one route.
        $middleware->validateCsrfTokens(except: [
            'stripe/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();