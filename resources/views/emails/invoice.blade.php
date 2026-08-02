<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #e67399, #d63384); color: white; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .header p { margin: 5px 0 0; opacity: 0.9; }
        .content { padding: 30px; }
        .invoice-details { background: #f9f5ff; border-radius: 10px; padding: 24px; margin: 20px 0; }
        .invoice-details h3 { margin-top: 0; margin-bottom: 16px; color: #6d28d9; }
        .detail-section { padding: 4px 0 16px; }
        .detail-section + .detail-section { border-top: 1px solid #ede9f5; padding-top: 16px; }
        .detail-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; }
        .detail-label { font-weight: 600; color: #555; }
        .detail-value { color: #333; font-weight: 500; }
        .adjustment-row .detail-label { font-weight: 500; color: #777; font-size: 0.92rem; text-transform: capitalize; padding-left: 4px; }
        .adjustment-row .detail-value { font-size: 0.92rem; }
        .adjustment-row .detail-value.is-fee { color: #b45309; }
        .adjustment-row .detail-value.is-discount { color: #16a34a; }
        .adjustment-row .detail-value.is-misc { color: #555; }
        .total-row .detail-label, .total-row .detail-value { font-size: 1.15rem; font-weight: 700; color: #1f1f1f; }
        .total-row { padding: 14px 0; }
        .amount { font-size: 28px; font-weight: 700; color: #e67399; text-align: center; margin: 20px 0; }
        .payment-section { background: #fff7fa; border: 2px solid #f0d4e4; border-radius: 10px; padding: 20px; margin: 20px 0; }
        .payment-section h3 { margin-top: 0; color: #d63384; }
        .payment-method { padding: 8px 0; font-size: 15px; }
        .payment-method strong { color: #333; }
        .footer { background: #f9f5ff; padding: 20px 30px; text-align: center; font-size: 13px; color: #888; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Invoice from {{ $tenant->name }}</h1>
            <p>Invoice #{{ $invoice->invoice_number }}</p>
        </div>

        <div class="content">
            <p>Hi {{ $invoice->client_name }},</p>
            <p>Here's your invoice for your recent order. Please review the details below and submit payment using one of the available methods.</p>

            <div class="amount">
                ${{ number_format($invoice->total_amount, 2) }}
            </div>

            <div class="invoice-details">
                <h3>📋 Invoice Details</h3>

                <div class="detail-section">
                    <div class="detail-row">
                        <span class="detail-label">Invoice #</span>
                        <span class="detail-value">{{ $invoice->invoice_number }}</span>
                    </div>
                    @if($invoice->order)
                    <div class="detail-row">
                        <span class="detail-label">Order #</span>
                        <span class="detail-value">{{ $invoice->order->order_number }}</span>
                    </div>
                    @endif
                </div>

                @php
                    $hasAdjustments = ($invoice->fee_amount ?? 0) > 0 || ($invoice->discount_amount ?? 0) > 0 || ($invoice->misc_amount ?? 0) > 0;
                @endphp
                @if($hasAdjustments)
                    <div class="detail-section">
                        <div class="detail-row">
                            <span class="detail-label">Order Subtotal</span>
                            <span class="detail-value">${{ number_format($invoice->subtotal ?? $invoice->total_amount, 2) }}</span>
                        </div>
                        @if(($invoice->fee_amount ?? 0) > 0)
                            <div class="detail-row adjustment-row">
                                <span class="detail-label">{{ $invoice->fee_label ?: 'Fee' }}</span>
                                <span class="detail-value is-fee">+${{ number_format($invoice->fee_amount, 2) }}</span>
                            </div>
                        @endif
                        @if(($invoice->discount_amount ?? 0) > 0)
                            <div class="detail-row adjustment-row">
                                <span class="detail-label">{{ $invoice->discount_label ?: 'Discount' }}</span>
                                <span class="detail-value is-discount">-${{ number_format($invoice->discount_amount, 2) }}</span>
                            </div>
                        @endif
                        @if(($invoice->misc_amount ?? 0) > 0)
                            <div class="detail-row adjustment-row">
                                <span class="detail-label">{{ $invoice->misc_label ?: 'Misc' }}</span>
                                <span class="detail-value is-misc">+${{ number_format($invoice->misc_amount, 2) }}</span>
                            </div>
                        @endif
                    </div>
                @endif

                <div class="detail-section">
                    <div class="detail-row total-row">
                        <span class="detail-label">Total Amount</span>
                        <span class="detail-value">${{ number_format($invoice->total_amount, 2) }}</span>
                    </div>
                    @if($invoice->deposit_amount > 0)
                    <div class="detail-row">
                        <span class="detail-label">Deposit Required</span>
                        <span class="detail-value">${{ number_format($invoice->deposit_amount, 2) }}</span>
                    </div>
                    @endif
                    @if($invoice->due_date)
                    <div class="detail-row">
                        <span class="detail-label">Due Date</span>
                        <span class="detail-value">{{ \Carbon\Carbon::parse($invoice->due_date)->format('M j, Y') }}</span>
                    </div>
                    @endif
                    @if($invoice->notes)
                    <div class="detail-row">
                        <span class="detail-label">Notes</span>
                        <span class="detail-value">{{ $invoice->notes }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <div class="payment-section">
                <h3>💳 Payment Methods</h3>
                <p>Please use one of the following methods to submit your payment:</p>
                
                @forelse($paymentSettings as $pm)
                <div class="payment-method">
                    <strong>{{ $pm['name'] }}:</strong>
                    @if(strtolower($pm['name']) === 'paypal')
                        <a href="{{ $pm['handle'] }}" style="color:#e67399;">{{ $pm['handle'] }}</a>
                    @else
                        {{ $pm['handle'] }}
                    @endif
                    @if(!empty($pm['instructions']))
                        <div style="font-size:0.8rem; color:#888; margin-top:2px;">{{ $pm['instructions'] }}</div>
                    @endif
                </div>
                @empty
                <p style="font-size:0.9rem; color:#888;">No payment methods have been configured yet.</p>
                @endforelse
            </div>

            <p style="text-align:center; color:#888; font-size:14px;">
                After payment, please reply to this email with confirmation so we can mark your invoice as paid.
            </p>
        </div>

        <div class="footer">
            <p>This invoice was sent by <strong>{{ $tenant->name }}</strong></p>
            <p>Powered by DoughMain.Pro</p>
        </div>
    </div>
</body>
</html>
