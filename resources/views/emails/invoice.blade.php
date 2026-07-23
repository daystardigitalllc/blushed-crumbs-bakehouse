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
        .invoice-details { background: #f9f5ff; border-radius: 10px; padding: 20px; margin: 20px 0; }
        .invoice-details h3 { margin-top: 0; color: #6d28d9; }
        .detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #ede9f5; }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { font-weight: 600; color: #555; }
        .detail-value { color: #333; }
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
                <div class="detail-row">
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

            <div class="payment-section">
                <h3>💳 Payment Methods</h3>
                <p>Please use one of the following methods to submit your payment:</p>
                
                @if(!empty($paymentSettings['venmo']))
                <div class="payment-method">
                    <strong>Venmo:</strong> {{ $paymentSettings['venmo'] }}
                </div>
                @endif

                @if(!empty($paymentSettings['cashapp']))
                <div class="payment-method">
                    <strong>Cash App:</strong> {{ $paymentSettings['cashapp'] }}
                </div>
                @endif

                @if(!empty($paymentSettings['paypal']))
                <div class="payment-method">
                    <strong>PayPal:</strong> <a href="{{ $paymentSettings['paypal'] }}" style="color:#e67399;">{{ $paymentSettings['paypal'] }}</a>
                </div>
                @endif

                @if(!empty($paymentSettings['zelle']))
                <div class="payment-method">
                    <strong>Zelle:</strong> {{ $paymentSettings['zelle'] }}
                </div>
                @endif

                @if(!empty($paymentSettings['bank_name']))
                <div class="payment-method">
                    <strong>Bank Transfer:</strong> {{ $paymentSettings['bank_name'] }}
                </div>
                @endif
            </div>

            <p style="text-align:center; color:#888; font-size:14px;">
                After payment, please reply to this email with confirmation so we can mark your invoice as paid.
            </p>
        </div>

        <div class="footer">
            <p>This invoice was sent by <strong>{{ $tenant->name }}</strong></p>
            <p>Powered by BakeryPro</p>
        </div>
    </div>
</body>
</html>
