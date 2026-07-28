<?php

use App\Http\Controllers\OnboardingUploadController;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;

// Registered outside the `web` middleware group (see bootstrap/app.php) —
// no session, no CSRF. Signed-URL only. This is deliberate: the `web` group's
// SESSION_DRIVER=file takes an exclusive per-session lock for the whole
// request, which would serialize the bulk uploader's concurrent requests
// into one at a time.
//
// SubstituteBindings must be listed explicitly — it's normally part of the
// `web`/`api` groups this route deliberately skips, and without it {draft}
// never resolves to a model: the controller's type-hint just container-resolves
// a blank, unsaved OnboardingDraft instead of 404ing or binding the real row.
Route::post('/onboarding/uploads/{draft}', [OnboardingUploadController::class, 'store'])
    ->middleware([SubstituteBindings::class, 'signed', 'throttle:120,1'])
    ->name('onboarding.upload.store');
