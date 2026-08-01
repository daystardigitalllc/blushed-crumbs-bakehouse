<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

class NewOrderNotification extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;
    public Tenant $tenant;

    public function __construct(Order $order, Tenant $tenant)
    {
        $this->order = $order;
        $this->tenant = $tenant;
    }

    public function envelope(): Envelope
    {
        $tenantPrefix = !empty($this->tenant->slug) ? preg_replace('/[^a-z0-9_-]/i', '', $this->tenant->slug) : 'orders';
        $fromAddress = strtolower($tenantPrefix) . '@' . config('mail.tenant_from_domain');
        $fromName = !empty($this->tenant->name) ? $this->tenant->name : config('app.name', 'Bakehouse Platform');

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            replyTo: [
                new Address($this->order->client_email, $this->order->client_name)
            ],
            subject: "🛍️ New Order Request #" . $this->order->order_number . " - " . $this->order->client_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new_order',
            with: [
                'order' => $this->order,
                'tenant' => $this->tenant,
            ],
        );
    }

    // No attachments() override — inspiration photos render as inline public
    // image URLs in emails.new_order instead (see that view). Brevo's
    // attachment API has repeatedly rejected the *entire* email over a
    // single attachment it didn't like (first CID-embedded images, then a
    // plain .webp file), and since the request is one combined payload,
    // there's no way to drop just the bad attachment and still send the
    // rest — the whole notification was silently lost either way. A public
    // URL <img> has no such failure mode: it's rendered by the recipient's
    // mail client, not validated by Brevo at send time.
}
