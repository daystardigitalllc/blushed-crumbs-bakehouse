<?php

// This file is read through config:cache in production — every tunable an
// onboarding job needs must live here, never behind a bare env() call
// outside this file (see deploy/post-deploy.sh).

return [

    // ─── Upload limits ───
    'max_file_size_kb' => env('ONBOARDING_MAX_FILE_SIZE_KB', 65536), // 64MB, matches the raised nginx/php limits
    'allowed_image_mimes' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/heic', 'image/heif'],
    'allowed_doc_mimes' => ['application/pdf'],

    // ─── Per-draft quota (Risks: a full disk takes down MySQL and every storefront at once) ───
    'max_files_per_draft' => env('ONBOARDING_MAX_FILES_PER_DRAFT', 500),
    'max_bytes_per_draft' => env('ONBOARDING_MAX_BYTES_PER_DRAFT', 2 * 1024 * 1024 * 1024), // 2GB

    // ─── Image derivatives — generated once in ImageProcessor ───
    'derivatives' => [
        'thumb' => ['width' => 400, 'format' => 'webp', 'quality' => 80],
        'display' => ['width' => 1920, 'format' => 'webp', 'quality' => 82],
        'ai' => ['width' => 1568, 'format' => 'jpeg', 'quality' => 82], // JPEG deliberately — sized for token efficiency, thrown away after extraction
    ],

    // ─── Retention (full two-tier policy lands in a later phase; the TTL lives here from day one) ───
    'incomplete_draft_ttl_hours' => env('ONBOARDING_INCOMPLETE_DRAFT_TTL_HOURS', 48),
    'imported_draft_file_ttl_days' => env('ONBOARDING_IMPORTED_DRAFT_FILE_TTL_DAYS', 7),
    'extraction_cache_ttl_days' => env('ONBOARDING_EXTRACTION_CACHE_TTL_DAYS', 30),
    'events_ttl_days' => env('ONBOARDING_EVENTS_TTL_DAYS', 30),

    // ─── Upload signed-URL lifetime ───
    'upload_url_ttl_minutes' => env('ONBOARDING_UPLOAD_URL_TTL_MINUTES', 180),
];
