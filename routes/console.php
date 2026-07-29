<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment('Bake with love!');
})->purpose('Display an inspiring quote');

// ─── Onboarding sweepers (Phase 9) ───
// Nothing ran these automatically before this — see deploy/cron.php, which
// now invokes `schedule:run` on every deploy-watch pass so these actually fire.
Schedule::command('onboarding:sweep-stuck-imports --force')->hourly()->withoutOverlapping();
Schedule::command('onboarding:prune-drafts')->hourly()->withoutOverlapping();
Schedule::command('onboarding:send-resume-reminders')->hourly()->withoutOverlapping();
