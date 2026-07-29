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

    // ─── Retention (Phase 9 — the two-tier policy this TTL always described) ───
    'incomplete_draft_ttl_hours' => env('ONBOARDING_INCOMPLETE_DRAFT_TTL_HOURS', 48),
    'imported_draft_file_ttl_days' => env('ONBOARDING_IMPORTED_DRAFT_FILE_TTL_DAYS', 7),
    'extraction_cache_ttl_days' => env('ONBOARDING_EXTRACTION_CACHE_TTL_DAYS', 30),
    'events_ttl_days' => env('ONBOARDING_EVENTS_TTL_DAYS', 30),

    // ─── Resume (Phase 9) — the resume window must equal the retention window above ───
    // Second/final reminder email fires once a still-unreviewed draft has been
    // inactive this long — chosen so the email's "expires in 12 hours" claim
    // stays true against the 48h incomplete_draft_ttl_hours above.
    'resume_reminder_inactive_hours' => env('ONBOARDING_RESUME_REMINDER_INACTIVE_HOURS', 36),
    // The first "ready to review" email only fires if the baker looks to have
    // actually navigated away, not just paused between clicks.
    'resume_ready_email_inactive_minutes' => env('ONBOARDING_RESUME_READY_EMAIL_INACTIVE_MINUTES', 3),

    // ─── Upload signed-URL lifetime ───
    'upload_url_ttl_minutes' => env('ONBOARDING_UPLOAD_URL_TTL_MINUTES', 180),

    // ─── Extraction job graph (Phase 3) ───
    // Phase 4: points at the real Gemini extractor now that it exists. Falls
    // back to local-only results per file whenever no API key is configured,
    // so tests and any environment without GEMINI_API_KEY never make a real
    // network call — see GeminiExtractionService::hasApiKey().
    'extractor' => env('ONBOARDING_EXTRACTOR_CLASS', \App\Services\Onboarding\Extraction\GeminiExtractionService::class),
    'extraction_batch_size_images' => env('ONBOARDING_EXTRACTION_BATCH_SIZE_IMAGES', 6),
    'extraction_batch_size_pdfs' => env('ONBOARDING_EXTRACTION_BATCH_SIZE_PDFS', 1),
    // How long IngestOnboardingFileJob waits before triggering a dispatch pass — lets a
    // burst of uploads land together so batches actually fill up rather than claiming 1 at a time.
    'extraction_dispatch_debounce_seconds' => env('ONBOARDING_EXTRACTION_DISPATCH_DEBOUNCE_SECONDS', 5),
    // A file still 'extracting' longer than this means its worker died mid-batch —
    // FinalizeExtractionJob's stuck sweep resets it to 'pending' for reclaiming.
    'extraction_stuck_minutes' => env('ONBOARDING_EXTRACTION_STUCK_MINUTES', 10),

    // ─── Gemini extraction (Phase 4) ───
    // "~50 vision calls per 300-image onboarding" per the plan's Risks section —
    // local quality score decides which images get semantic analysis, best-first
    // (see DispatchPendingExtractionsJob's claim ordering); beyond this cap,
    // images still import with local score + a filename-derived alt text.
    'ai_max_images_per_draft' => env('ONBOARDING_AI_MAX_IMAGES_PER_DRAFT', 50),
    // Bump this to invalidate ai_extraction_cache after a prompt/schema change
    // without touching content_hash or the model name.
    'ai_prompt_version' => env('ONBOARDING_AI_PROMPT_VERSION', 'v1'),
    // Gemini's own per-request inline-data cap — a batch (or a single large PDF)
    // that would exceed this falls back to a local-only result instead of erroring.
    'ai_max_request_bytes' => env('ONBOARDING_AI_MAX_REQUEST_BYTES', 18 * 1024 * 1024),

    // ─── Synthesis (Phase 5) ───
    'synthesis_max_categories' => env('ONBOARDING_SYNTHESIS_MAX_CATEGORIES', 6),
    // 85% per the plan ("fuzzy collapse at 85%") — how similar two raw category
    // names need to be (via similar_text()) to merge into the same canonical bucket.
    'synthesis_category_similarity_threshold' => env('ONBOARDING_SYNTHESIS_CATEGORY_SIMILARITY_THRESHOLD', 85.0),
    // Copywriting needs some creativity, unlike extraction's 0.2 (transcription).
    'synthesis_temperature' => env('ONBOARDING_SYNTHESIS_TEMPERATURE', 0.7),

    // ─── Import (Phase 6) ───
    // A draft stuck in 'importing' longer than this means its worker died
    // mid-copy — onboarding:sweep-stuck-imports cleans up and resets it.
    'import_stuck_minutes' => env('ONBOARDING_IMPORT_STUCK_MINUTES', 15),
];
