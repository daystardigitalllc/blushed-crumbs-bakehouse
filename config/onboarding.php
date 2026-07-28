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

    // ─── Extraction job graph (Phase 3) ───
    // Swapped to a real GeminiExtractionService class-string in Phase 4 — ExtractBatchJob
    // only knows it gets something implementing ExtractorInterface.
    'extractor' => env('ONBOARDING_EXTRACTOR_CLASS', \App\Services\Onboarding\Extraction\StubExtractor::class),
    'extraction_batch_size_images' => env('ONBOARDING_EXTRACTION_BATCH_SIZE_IMAGES', 6),
    'extraction_batch_size_pdfs' => env('ONBOARDING_EXTRACTION_BATCH_SIZE_PDFS', 1),
    // How long IngestOnboardingFileJob waits before triggering a dispatch pass — lets a
    // burst of uploads land together so batches actually fill up rather than claiming 1 at a time.
    'extraction_dispatch_debounce_seconds' => env('ONBOARDING_EXTRACTION_DISPATCH_DEBOUNCE_SECONDS', 5),
    // A file still 'extracting' longer than this means its worker died mid-batch —
    // FinalizeExtractionJob's stuck sweep resets it to 'pending' for reclaiming.
    'extraction_stuck_minutes' => env('ONBOARDING_EXTRACTION_STUCK_MINUTES', 10),
];
