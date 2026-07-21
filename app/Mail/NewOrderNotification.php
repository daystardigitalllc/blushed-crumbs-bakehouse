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
        return new Envelope(
            from: new Address(
                config('mail.from.address', 'orders@blushedcrumbsbakehouse.com'),
                $this->tenant->name ?? 'Bakehouse Orders'
            ),
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
}
