<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // ─── Google Gemini API (for AI website generation, copy + background images) ───
    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-3.5-flash-lite'),
        'image_model' => env('GEMINI_IMAGE_MODEL', 'gemini-2.5-flash-image'),
        // Used by the onboarding extraction pipeline (Phase 4) — separate from
        // 'model' above since reading a menu photo and writing marketing copy
        // have very different accuracy/cost tradeoffs.
        'extraction_model' => env('GEMINI_EXTRACTION_MODEL', 'gemini-3.5-flash'),
        // AiContentService::generateWebsiteContent() disables TLS verification
        // on its Gemini calls (see the onboarding-rebuild plan's "Known debt" —
        // deliberately left alone there, not reproduced here). GeminiClient
        // verifies TLS by default; this only exists as an emergency escape
        // hatch and should stay true.
        'verify_tls' => env('GEMINI_VERIFY_TLS', true),
    ],

    // ─── Laravel Forge API (auto-provisioning SSL for verified custom domains) ───
    'forge' => [
        'token' => env('FORGE_API_TOKEN'),
        'server_id' => env('FORGE_SERVER_ID'),
        'site_id' => env('FORGE_SITE_ID'),
    ],

    // ─── Stripe (Pro plan billing) ───
    'stripe' => [
        'secret' => env('STRIPE_SECRET'),
    ],

];
