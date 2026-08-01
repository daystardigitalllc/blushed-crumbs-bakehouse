<?php

namespace App\Mail;

use App\Models\EmailCampaign;
use App\Models\EmailSubscriber;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

class PromoEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Tenant $tenant,
        public EmailCampaign $campaign,
        public EmailSubscriber $subscriber,
    ) {
    }

    public function envelope(): Envelope
    {
        $tenantPrefix = !empty($this->tenant->slug) ? preg_replace('/[^a-z0-9_-]/i', '', $this->tenant->slug) : 'orders';
        $fromAddress = strtolower($tenantPrefix) . '@daystardigital.co';
        $fromName = !empty($this->tenant->name) ? $this->tenant->name : config('app.name', 'Bakehouse Platform');

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            subject: $this->campaign->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.promo',
            with: [
                'tenant' => $this->tenant,
                'campaign' => $this->campaign,
                'subscriber' => $this->subscriber,
                'unsubscribeUrl' => route('newsletter.unsubscribe', $this->subscriber->unsubscribe_token),
            ],
        );
    }
}
