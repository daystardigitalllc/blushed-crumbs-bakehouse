<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $presaleSettings['title'] ?? 'Presale' }} - {{ $tenant->name }}</title>
    @if($tenant->logo_path)
        <link rel="icon" href="{{ asset($tenant->logo_path) }}">
    @else
        <link rel="icon" href="{{ asset('images/favicon.png') }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: #faf6f8;
            color: #2c2428;
            line-height: 1.5;
            padding: 20px 12px 140px 12px;
        }
        .presale-container { max-width: 780px; margin: 20px auto; }
        .presale-header {
            background: linear-gradient(135deg, #fff7fa 0%, #ffe6f0 100%);
            border-radius: 20px;
            border: 1px solid #f0e4ea;
            padding: 32px;
            margin-bottom: 24px;
            text-align: center;
        }
        .presale-header h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 2rem;
            color: #5c1d37;
            font-weight: 800;
            margin-bottom: 6px;
        }
        .presale-header .bakery-name { color: #e67399; font-weight: 700; font-size: 0.95rem; margin-bottom: 10px; }
        .presale-header p.subtitle { color: #666; font-size: 1rem; }
        .presale-window {
            display: inline-block;
            margin-top: 14px;
            background: #ffffff;
            border: 1px solid #f0d8e2;
            border-radius: 20px;
            padding: 8px 18px;
            font-size: 0.85rem;
            font-weight: 700;
            color: #7a2b4a;
        }

        .card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #f0e4ea;
            padding: 24px;
            margin-bottom: 20px;
        }
        .card h3 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.1rem;
            color: #5c1d37;
            margin-bottom: 16px;
        }

        .item-row {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 14px 0;
            border-bottom: 1px solid #f4e8ee;
        }
        .item-row:last-child { border-bottom: none; }
        .item-photo { width: 64px; height: 64px; border-radius: 12px; object-fit: cover; border: 1px solid #f0e4ea; flex-shrink: 0; }
        .item-photo-placeholder { width: 64px; height: 64px; border-radius: 12px; background: #fff7fa; border: 1px solid #f0e4ea; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .item-info { flex: 1; min-width: 0; }
        .item-info .item-name { font-weight: 700; color: #2c2428; }
        .item-info .item-desc { font-size: 0.85rem; color: #888; margin-top: 2px; }
        .item-info .item-price { font-size: 0.9rem; color: #e67399; font-weight: 700; margin-top: 4px; }
        .item-info .item-min { font-size: 0.78rem; color: #b45309; margin-top: 2px; }

        .qty-stepper { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
        .qty-stepper button {
            width: 30px; height: 30px; border-radius: 8px; border: 1px solid #e2c7d4;
            background: #fff7fa; color: #7a2b4a; font-weight: 700; font-size: 1.1rem; cursor: pointer;
        }
        .qty-stepper input {
            width: 44px; text-align: center; border: 1px solid #e2c7d4; border-radius: 8px; padding: 5px 2px;
            font-weight: 700;
        }

        label { display: block; font-size: 0.85rem; font-weight: 700; color: #5c1d37; margin-bottom: 6px; }
        input[type="text"], input[type="email"], input[type="tel"], input[type="date"], textarea, select {
            width: 100%; padding: 10px 12px; border: 1px solid #e2c7d4; border-radius: 10px;
            font-family: inherit; font-size: 0.95rem; margin-bottom: 16px;
        }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0 16px; }

        .fulfillment-options { display: flex; gap: 12px; margin-bottom: 16px; }
        .fulfillment-option {
            flex: 1; border: 2px solid #f0e4ea; border-radius: 12px; padding: 12px; text-align: center;
            cursor: pointer; font-weight: 700; color: #7a2b4a; transition: all 0.15s ease;
        }
        .fulfillment-option.active { border-color: #e67399; background: #fff7fa; }

        .summary-bar {
            position: fixed; bottom: 0; left: 0; right: 0; background: #ffffff;
            border-top: 2px solid #f0d8e2; box-shadow: 0 -6px 20px rgba(0,0,0,0.06);
            padding: 16px 20px; z-index: 1000;
        }
        .summary-inner { max-width: 780px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
        .summary-totals { font-size: 0.85rem; color: #666; }
        .summary-totals .grand { font-size: 1.3rem; font-weight: 800; color: #e67399; }
        .summary-totals .line { display: inline-block; margin-right: 14px; }
        .btn-checkout {
            background: #e67399; color: white; border: none; padding: 14px 28px; border-radius: 12px;
            font-weight: 700; font-size: 1rem; cursor: pointer; transition: background 0.2s ease;
        }
        .btn-checkout:hover { background: #d65c85; }
        .btn-checkout:disabled { background: #ccc; cursor: not-allowed; }

        .empty-state { text-align: center; padding: 40px 20px; color: #888; }

        #presale-success { display: none; }
        .success-card { text-align: center; padding: 40px 24px; }
        .success-card h2 { font-family: 'Outfit', sans-serif; color: #5c1d37; margin-bottom: 12px; }
        .success-card a { color: #e67399; font-weight: 700; }

        .error-msg { background: #fff1f0; border: 1px solid #ffa39e; color: #a8071a; border-radius: 10px; padding: 10px 14px; margin-bottom: 16px; font-size: 0.9rem; display: none; }
    </style>
</head>
<body>

<div class="presale-container">
    <div class="presale-header">
        <div class="bakery-name">{{ $tenant->name }}</div>
        <h1>{{ $presaleSettings['title'] ?? 'Presale' }}</h1>
        @if(!empty($presaleSettings['subtitle']))
            <p class="subtitle">{{ $presaleSettings['subtitle'] }}</p>
        @endif
        @if(!empty($presaleSettings['pickup_start_date']) && !empty($presaleSettings['pickup_end_date']))
            <div class="presale-window">
                Pickup {{ \Carbon\Carbon::parse($presaleSettings['pickup_start_date'])->format('M j') }}
                – {{ \Carbon\Carbon::parse($presaleSettings['pickup_end_date'])->format('M j, Y') }}
            </div>
        @endif
    </div>

    <div id="presale-order-form">
        <div class="card">
            <h3>Choose Your Items</h3>
            @forelse($items as $item)
                <div class="item-row" data-item-id="{{ $item->id }}" data-price="{{ $item->price }}" data-min="{{ $item->min_quantity }}" data-unit="{{ $item->unit_label }}" data-name="{{ $item->name }}">
                    @if($item->photo_path)
                        <img class="item-photo" src="{{ asset($item->photo_path) }}" alt="{{ $item->name }}">
                    @else
                        <div class="item-photo-placeholder">🧁</div>
                    @endif
                    <div class="item-info">
                        <div class="item-name">{{ $item->name }}</div>
                        @if($item->description)
                            <div class="item-desc">{{ $item->description }}</div>
                        @endif
                        <div class="item-price">${{ number_format($item->price, 2) }} / {{ $item->unit_label }}</div>
                        @if($item->min_quantity > 1)
                            <div class="item-min">Minimum order: {{ $item->min_quantity }} {{ $item->unit_label }}</div>
                        @endif
                    </div>
                    <div class="qty-stepper">
                        <button type="button" onclick="presaleDecrement({{ $item->id }})">−</button>
                        <input type="number" min="0" value="0" id="presale-qty-{{ $item->id }}" onchange="presaleUpdateQty({{ $item->id }}, this.value)">
                        <button type="button" onclick="presaleIncrement({{ $item->id }})">+</button>
                    </div>
                </div>
            @empty
                <div class="empty-state">No presale items are available right now — please check back soon!</div>
            @endforelse
        </div>

        @if($items->isNotEmpty())
        <div class="card">
            <h3>Pickup Date &amp; Fulfillment</h3>
            <div class="fulfillment-options">
                <div class="fulfillment-option active" id="fulfillment-pickup" onclick="presaleSetFulfillment('pickup')">📦 Pickup</div>
                @if(!empty($presaleSettings['delivery_enabled']))
                    <div class="fulfillment-option" id="fulfillment-delivery" onclick="presaleSetFulfillment('delivery')">🚚 Delivery</div>
                @endif
            </div>
            <div id="presale-delivery-address-wrapper" style="display:none;">
                <label>Delivery Address</label>
                <textarea id="presale-delivery-address" rows="2" placeholder="Street address, city, zip"></textarea>
            </div>
            <label>Pickup Date</label>
            <input type="date" id="presale-due-date"
                min="{{ $presaleSettings['pickup_start_date'] ?? '' }}"
                max="{{ $presaleSettings['pickup_end_date'] ?? '' }}">
        </div>

        <div class="card">
            <h3>Your Contact Info</h3>
            <div class="form-grid">
                <div>
                    <label>Full Name</label>
                    <input type="text" id="presale-client-name" placeholder="Jane Doe">
                </div>
                <div>
                    <label>Email</label>
                    <input type="email" id="presale-client-email" placeholder="jane@example.com">
                </div>
                <div>
                    <label>Phone</label>
                    <input type="tel" id="presale-client-phone" placeholder="(555) 555-5555">
                </div>
            </div>
            <label>Message, Flavor, or Theme Requests (optional)</label>
            <textarea id="presale-special-notes" rows="3" placeholder="Let us know any flavor preferences, theme ideas, or special requests..." style="margin-bottom:0;"></textarea>
        </div>

        <div class="error-msg" id="presale-error"></div>
        @endif
    </div>

    <div id="presale-success">
        <div class="card success-card">
            <h2>🎉 Order Submitted!</h2>
            <p style="color:#666; margin-bottom:16px;">Check your email for your invoice — that's how you'll submit payment to lock in your order.</p>
            <a id="presale-invoice-link" href="#">View Your Invoice →</a>
        </div>
    </div>
</div>

@if($items->isNotEmpty())
<div class="summary-bar">
    <div class="summary-inner">
        <div class="summary-totals">
            <span class="line">Subtotal: $<span id="presale-subtotal">0.00</span></span>
            @if(($presaleSettings['tax_rate'] ?? 0) > 0)
                <span class="line">Tax: $<span id="presale-tax">0.00</span></span>
            @endif
            <span class="line" id="presale-delivery-fee-line" style="display:none;">Delivery: $<span id="presale-delivery-fee-amt">0.00</span></span>
            <div class="grand">Total: $<span id="presale-total">0.00</span></div>
        </div>
        <button type="button" class="btn-checkout" id="presale-checkout-btn" onclick="presaleSubmitOrder()" disabled>Place Presale Order</button>
    </div>
</div>
@endif

<script>
(function() {
    const TAX_RATE = {{ (float) ($presaleSettings['tax_rate'] ?? 0) }};
    const DELIVERY_FEE = {{ (float) ($presaleSettings['delivery_fee'] ?? 0) }};
    const cart = {}; // { itemId: qty }
    let fulfillment = 'pickup';

    window.presaleIncrement = function(itemId) {
        const input = document.getElementById('presale-qty-' + itemId);
        const row = input.closest('.item-row');
        const min = parseInt(row.dataset.min || '1');
        const current = parseInt(input.value || '0');
        const next = current === 0 ? min : current + 1;
        input.value = next;
        presaleUpdateQty(itemId, next);
    };

    window.presaleDecrement = function(itemId) {
        const input = document.getElementById('presale-qty-' + itemId);
        const row = input.closest('.item-row');
        const min = parseInt(row.dataset.min || '1');
        const current = parseInt(input.value || '0');
        let next = current - 1;
        if (next > 0 && next < min) next = 0;
        if (next < 0) next = 0;
        input.value = next;
        presaleUpdateQty(itemId, next);
    };

    window.presaleUpdateQty = function(itemId, value) {
        const qty = Math.max(0, parseInt(value || '0'));
        if (qty === 0) {
            delete cart[itemId];
        } else {
            cart[itemId] = qty;
        }
        presaleRecalculate();
    };

    window.presaleSetFulfillment = function(type) {
        fulfillment = type;
        document.querySelectorAll('.fulfillment-option').forEach(el => el.classList.remove('active'));
        const el = document.getElementById('fulfillment-' + type);
        if (el) el.classList.add('active');
        document.getElementById('presale-delivery-address-wrapper').style.display = type === 'delivery' ? 'block' : 'none';
        presaleRecalculate();
    };

    window.presaleRecalculate = function() {
        let subtotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const id = row.dataset.itemId;
            const qty = cart[id] || 0;
            const price = parseFloat(row.dataset.price || '0');
            subtotal += price * qty;
        });

        const tax = Math.round(subtotal * (TAX_RATE / 100) * 100) / 100;
        const deliveryFee = fulfillment === 'delivery' ? DELIVERY_FEE : 0;
        const total = subtotal + tax + deliveryFee;

        const subtotalEl = document.getElementById('presale-subtotal');
        const taxEl = document.getElementById('presale-tax');
        const totalEl = document.getElementById('presale-total');
        const deliveryLine = document.getElementById('presale-delivery-fee-line');
        const deliveryAmt = document.getElementById('presale-delivery-fee-amt');

        if (subtotalEl) subtotalEl.textContent = subtotal.toFixed(2);
        if (taxEl) taxEl.textContent = tax.toFixed(2);
        if (totalEl) totalEl.textContent = total.toFixed(2);
        if (deliveryLine) deliveryLine.style.display = deliveryFee > 0 ? 'inline-block' : 'none';
        if (deliveryAmt) deliveryAmt.textContent = deliveryFee.toFixed(2);

        const btn = document.getElementById('presale-checkout-btn');
        if (btn) btn.disabled = Object.keys(cart).length === 0;
    };

    window.presaleSubmitOrder = async function() {
        const errorEl = document.getElementById('presale-error');
        errorEl.style.display = 'none';

        const items = Object.keys(cart).map(id => ({ presale_item_id: parseInt(id), quantity: cart[id] }));
        const clientName = document.getElementById('presale-client-name').value.trim();
        const clientEmail = document.getElementById('presale-client-email').value.trim();
        const clientPhone = document.getElementById('presale-client-phone').value.trim();
        const dueDate = document.getElementById('presale-due-date').value;
        const deliveryAddress = document.getElementById('presale-delivery-address')?.value.trim() || '';
        const specialNotes = document.getElementById('presale-special-notes')?.value.trim() || '';

        if (items.length === 0) {
            errorEl.textContent = 'Please select at least one item.';
            errorEl.style.display = 'block';
            return;
        }
        if (!clientName || !clientEmail || !clientPhone) {
            errorEl.textContent = 'Please fill in your name, email, and phone.';
            errorEl.style.display = 'block';
            return;
        }
        if (!dueDate) {
            errorEl.textContent = 'Please choose a pickup date.';
            errorEl.style.display = 'block';
            return;
        }
        if (fulfillment === 'delivery' && !deliveryAddress) {
            errorEl.textContent = 'Please enter a delivery address.';
            errorEl.style.display = 'block';
            return;
        }

        const btn = document.getElementById('presale-checkout-btn');
        btn.disabled = true;
        btn.textContent = 'Submitting...';

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        try {
            const response = await fetch('{{ route('storefront.presale.submit') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    client_name: clientName,
                    client_email: clientEmail,
                    client_phone: clientPhone,
                    due_date: dueDate,
                    fulfillment_type: fulfillment,
                    delivery_address: deliveryAddress || null,
                    special_notes: specialNotes || null,
                    items: items,
                })
            });
            const data = await response.json();

            if (data.success) {
                document.getElementById('presale-order-form').style.display = 'none';
                document.querySelector('.summary-bar').style.display = 'none';
                document.getElementById('presale-success').style.display = 'block';
                document.getElementById('presale-invoice-link').href = data.redirect;
            } else {
                errorEl.textContent = data.message || 'Something went wrong. Please try again.';
                errorEl.style.display = 'block';
                btn.disabled = false;
                btn.textContent = 'Place Presale Order';
            }
        } catch (err) {
            errorEl.textContent = 'A network error occurred. Please try again.';
            errorEl.style.display = 'block';
            btn.disabled = false;
            btn.textContent = 'Place Presale Order';
        }
    };
})();
</script>

</body>
</html>
