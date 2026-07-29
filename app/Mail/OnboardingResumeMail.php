<?php

namespace App\Mail;

use App\Models\Onboarding\OnboardingDraft;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The onboarding resume link, sent at most twice per draft (see
 * OnboardingDraft::ensureResumeToken(), SynthesizeDraftJob, and
 * onboarding:send-resume-reminders): once when extraction finishes if the
 * baker has navigated away, once more at 36h inactive if still unreviewed.
 * The link requires both the token AND a fresh login — see
 * OnboardingController::resume() — so a forwarded email alone isn't enough.
 */
class OnboardingResumeMail extends Mailable
{
    use Queueable, SerializesModels;

    public OnboardingDraft $draft;
    public Tenant $tenant;
    public string $variant;

    /**
     * @param string $variant 'ready' (extraction just finished) or 'reminder' (36h inactive, expiring soon)
     */
    public function __construct(OnboardingDraft $draft, Tenant $tenant, string $variant = 'ready')
    {
        $this->draft = $draft;
        $this->tenant = $tenant;
        $this->variant = $variant;
    }

    public function envelope(): Envelope
    {
        $subject = $this->variant === 'reminder'
            ? '⏳ Your bakery website expires in 12 hours'
            : '🎉 Your bakery website is ready to review';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.onboarding_resume',
            with: [
                'tenant' => $this->tenant,
                'variant' => $this->variant,
                'resumeUrl' => route('onboarding.resume', ['token' => $this->draft->resume_token]),
            ],
        );
    }
}
