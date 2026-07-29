<?php

namespace App\Console\Commands;

use App\Mail\OnboardingResumeMail;
use App\Models\Onboarding\OnboardingDraft;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * The second and final resume email: "expires in 12 hours", sent once a
 * still-unreviewed draft has been inactive for resume_reminder_inactive_hours
 * (36h by default), leaving roughly 12h before onboarding:prune-drafts
 * purges it at incomplete_draft_ttl_hours (48h). Guarded by reminder_sent_at
 * so a draft only ever gets this email once.
 */
class OnboardingSendResumeReminders extends Command
{
    protected $signature = 'onboarding:send-resume-reminders';

    protected $description = 'Email a resume link to bakers with an unreviewed onboarding draft about to expire';

    public function handle(): int
    {
        $inactiveHours = (int) config('onboarding.resume_reminder_inactive_hours', 36);
        $cutoff = now()->subHours($inactiveHours);

        $drafts = OnboardingDraft::whereIn('status', OnboardingDraft::INCOMPLETE_STATUSES)
            ->whereNull('reminder_sent_at')
            ->where('last_activity_at', '<=', $cutoff)
            ->get();

        if ($drafts->isEmpty()) {
            $this->line('No drafts due for a resume reminder.');

            return self::SUCCESS;
        }

        $sent = 0;

        foreach ($drafts as $draft) {
            try {
                $tenant = Tenant::find($draft->tenant_id);
                $user = $tenant ? User::where('tenant_id', $tenant->id)->first() : null;

                if (!$tenant || !$user || !$user->email) {
                    continue;
                }

                $draft->ensureResumeToken();
                Mail::to($user->email)->queue(new OnboardingResumeMail($draft, $tenant, 'reminder'));

                $draft->reminder_sent_at = now();
                $draft->save();

                $sent++;
            } catch (\Throwable $e) {
                Log::warning('Onboarding resume reminder failed for one draft.', [
                    'draft_id' => $draft->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Sent {$sent} resume reminder(s) of {$drafts->count()} eligible draft(s).");

        return self::SUCCESS;
    }
}
