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

use Illuminate\Mail\Mailables\Attachment;

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
        $fromAddress = strtolower($tenantPrefix) . '@daystardigital.co';
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

    public function attachments(): array
    {
        $attachments = [];
        if (!empty($this->order->inspiration_files) && is_array($this->order->inspiration_files)) {
            foreach ($this->order->inspiration_files as $filePath) {
                $fullPath = public_path($filePath);
                if (file_exists($fullPath)) {
                    $attachments[] = Attachment::fromPath($fullPath);
                }
            }
        }
        return $attachments;
    }
}
