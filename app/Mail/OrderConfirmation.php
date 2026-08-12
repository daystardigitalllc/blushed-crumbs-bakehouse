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

class OrderConfirmation extends Mailable
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

        // Reply-to is the business's own routing address, not the platform's
        // from address -- a customer hitting "Reply" here should land in the
        // baker's inbox, same as the baker's own new-order notification puts
        // the customer's address in reply-to.
        $routingEmail = $this->tenant->email ?: $fromAddress;

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            replyTo: [
                new Address($routingEmail, $fromName),
            ],
            subject: "Your Order Request #" . $this->order->order_number . " - " . $fromName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order_confirmation',
            with: [
                'order' => $this->order,
                'tenant' => $this->tenant,
            ],
        );
    }
}
