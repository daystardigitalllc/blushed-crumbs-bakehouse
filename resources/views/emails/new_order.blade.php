<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Order Request #{{ $order->order_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #fdf7f9; color: #4a2133; margin: 0; padding: 0; line-height: 1.6; }
        .email-container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 20px; overflow: hidden; border: 1px solid #f8c6d7; box-shadow: 0 10px 30px rgba(92, 29, 55, 0.08); }
        .email-header { background: linear-gradient(135deg, #5c1d37, #7a2b4a); padding: 30px 20px; text-align: center; color: #ffffff; }
        .email-header h1 { font-size: 1.8rem; margin: 0 0 6px 0; font-family: Georgia, serif; }
        .email-header p { margin: 0; opacity: 0.9; font-size: 0.95rem; }
        .order-badge { display: inline-block; background: #e67399; color: #ffffff; padding: 4px 14px; border-radius: 20px; font-weight: 700; font-size: 0.85rem; margin-top: 10px; }
        .email-body { padding: 30px 25px; }
        .section-title { font-size: 1.1rem; color: #5c1d37; font-weight: 700; margin-bottom: 12px; border-bottom: 2px solid #fff0f5; padding-bottom: 6px; }
        .info-grid { display: table; width: 100%; margin-bottom: 20px; }
        .info-row { display: table-row; }
        .info-label { display: table-cell; font-weight: 700; color: #7a2b4a; padding: 6px 10px 6px 0; width: 35%; font-size: 0.9rem; }
        .info-value { display: table-cell; color: #333333; padding: 6px 0; font-size: 0.95rem; }
        .items-box { background: #fff7fa; border: 1px solid #f8c6d7; border-radius: 12px; padding: 16px; margin-bottom: 20px; }
        .item-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px dashed #f0e4ea; font-size: 0.95rem; }
        .item-row:last-child { border-bottom: none; }
        .total-box { background: #5c1d37; color: #ffffff; padding: 18px 20px; border-radius: 14px; text-align: center; margin-top: 20px; }
        .total-box strong { font-size: 1.4rem; color: #ffb3d1; }
        .footer { background: #faf0f4; text-align: center; padding: 18px 20px; font-size: 0.82rem; color: #888888; border-top: 1px solid #f8c6d7; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>🌸 {{ $tenant->name ?? 'Blushed Crumbs Bakehouse' }}</h1>
            <p>New Customer Order Request Submitted</p>
            <span class="order-badge">Order #{{ $order->order_number }}</span>
        </div>

        <div class="email-body">
            <div class="section-title">👤 Customer Details</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Customer Name:</div>
                    <div class="info-value"><strong>{{ $order->client_name }}</strong></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Email:</div>
                    <div class="info-value"><a href="mailto:{{ $order->client_email }}" style="color:#e67399;">{{ $order->client_email }}</a></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Phone:</div>
                    <div class="info-value"><a href="tel:{{ $order->client_phone }}" style="color:#e67399;">{{ $order->client_phone }}</a></div>
                </div>
            </div>

            <div class="section-title">📅 Fulfillment & Date</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Completion Date:</div>
                    <div class="info-value"><strong style="color:#e67399; font-size:1.05rem;">{{ $order->due_date }}</strong></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Time Frame:</div>
                    <div class="info-value">{{ $order->time_slot }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Fulfillment:</div>
                    <div class="info-value"><strong>{{ strtoupper($order->fulfillment_type) }}</strong></div>
                </div>
                @if($order->delivery_address)
                <div class="info-row">
                    <div class="info-label">Delivery Address:</div>
                    <div class="info-value">{{ $order->delivery_address }}</div>
                </div>
                @endif
            </div>

            <div class="section-title">🍰 Selected Order Items</div>
            <div class="items-box">
                @if(is_array($order->items) && count($order->items) > 0)
                    @foreach($order->items as $item)
                        <div class="item-row">
                            <span><strong>{{ $item['name'] ?? $item }}</strong> {{ isset($item['quantity']) ? 'x'.$item['quantity'] : '' }}</span>
                            <span>${{ number_format($item['price'] ?? 0, 2) }}</span>
                        </div>
                    @endforeach
                @else
                    <p style="margin:0; font-size:0.9rem; color:#888;">No items listed</p>
                @endif
            </div>

            @if(!empty($order->flavors))
            <div class="section-title">🎂 Selected Flavors</div>
            <p style="background:#fff7fa; padding:10px 14px; border-radius:10px; border:1px solid #f8c6d7; font-size:0.95rem; margin-top:4px;">
                {{ is_array($order->flavors) ? implode(', ', $order->flavors) : $order->flavors }}
            </p>
            @endif

            @if(!empty($order->frosting))
            <div class="section-title">🧁 Frosting Choice</div>
            <p style="background:#fff7fa; padding:10px 14px; border-radius:10px; border:1px solid #f8c6d7; font-size:0.95rem; margin-top:4px;">
                {{ is_array($order->frosting) ? implode(', ', $order->frosting) : $order->frosting }}
            </p>
            @endif

            @if(!empty($order->fillings))
            <div class="section-title">🍫 Fillings Choice</div>
            <p style="background:#fff7fa; padding:10px 14px; border-radius:10px; border:1px solid #f8c6d7; font-size:0.95rem; margin-top:4px;">
                {{ is_array($order->fillings) ? implode(', ', $order->fillings) : $order->fillings }}
            </p>
            @endif

            @if(!empty($order->special_notes))
            <div class="section-title">📄 Special Requests / Notes</div>
            <p style="background:#fff; padding:12px; border-radius:10px; border:1px solid #ddd; font-size:0.92rem; font-style:italic;">
                "{{ $order->special_notes }}"
            </p>
            @endif

            @if(!empty($order->allergies))
            <div class="section-title">⚠️ Allergy Disclaimer Notes</div>
            <p style="background:#fff0f3; padding:12px; border-radius:10px; border:1px solid #fcc6d4; color:#9b2c2c; font-size:0.92rem;">
                {{ $order->allergies }}
            </p>
            @endif

            <div class="total-box">
                <div>Estimated Total: <strong>${{ number_format($order->total_price, 2) }}</strong></div>
                <div style="font-size:0.9rem; opacity:0.9; margin-top:4px;">50% Deposit Required: ${{ number_format($order->deposit_amount, 2) }}</div>
            </div>
        </div>

        <div class="footer">
            <p>Sent automatically by {{ $tenant->name ?? 'Blushed Crumbs Bakehouse' }} Order Builder</p>
            <p>To reply directly to the customer, click "Reply" in your email client.</p>
        </div>
    </div>
</body>
</html>
