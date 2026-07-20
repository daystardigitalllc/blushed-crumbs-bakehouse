@extends('layouts.app')

@section('title', 'Baker Mobile Admin Portal | Blushed Crumbs Bakehouse')

@section('content')
<div class="admin-wrapper">
    <!-- Top Mobile Navigation Header -->
    <header class="admin-header">
        <div class="admin-brand">
            <span class="logo-icon">🌸</span>
            <div>
                <h2>Blushed Crumbs Admin</h2>
                <span class="badge badge-pro">Pro Tier Plan ($50/mo)</span>
            </div>
        </div>
        <a href="/" class="btn-store-link">View Public Site ↗</a>
    </header>

    <!-- Mobile Navigation Tabs -->
    <div class="admin-tabs">
        <button class="tab-btn active" data-tab="tab-orders">📅 Orders Queue</button>
        <button class="tab-btn" data-tab="tab-invoices">💳 Invoices & Payments</button>
        <button class="tab-btn" data-tab="tab-products">🧁 Products & Prices</button>
        <button class="tab-btn" data-tab="tab-reviews">⭐ Customer Reviews</button>
        <button class="tab-btn" data-tab="tab-gallery">🖼️ Gallery Photos</button>
        <button class="tab-btn" data-tab="tab-support">💬 Custom Support</button>
    </div>

    <div class="admin-content">

        <!-- TAB 1: Priority Orders Queue (Sorted by Due Date) -->
        <div id="tab-orders" class="tab-content active">
            <div class="section-header">
                <h3>📅 Priority Order Queue</h3>
                <p class="subtitle">Sorted chronologically by <strong>due date</strong> so you know exactly what is due first!</p>
            </div>

            <div class="filter-pills">
                <button class="pill active" onclick="filterAdminOrders('all')">All Active</button>
                <button class="pill" onclick="filterAdminOrders('urgent')">🔥 Urgent / Due Next</button>
                <button class="pill" onclick="filterAdminOrders('pickup')">Pickup Only</button>
                <button class="pill" onclick="filterAdminOrders('delivery')">Delivery Only</button>
            </div>

            <div class="orders-list-grid" id="admin-orders-list">
                @foreach($urgentOrders as $order)
                    @php
                        $dueDate = \Carbon\Carbon::parse($order->due_date);
                        $isUrgent = $dueDate->isToday() || $dueDate->isTomorrow() || $dueDate->diffInDays(now()) <= 2;
                    @endphp
                    <div class="order-card {{ $isUrgent ? 'urgent-border' : '' }}" data-fulfillment="{{ $order->fulfillment_type }}">
                        <div class="order-card-header">
                            <div class="due-badge {{ $isUrgent ? 'due-urgent' : 'due-normal' }}">
                                ⏰ DUE: {{ $dueDate->format('M d, Y') }} ({{ $order->time_slot }})
                            </div>
                            <span class="status-tag status-{{ $order->status }}">{{ strtoupper($order->status) }}</span>
                        </div>

                        <div class="order-card-body">
                            <h4>#{{ $order->order_number }} - {{ $order->client_name }}</h4>
                            <p><strong>Phone:</strong> {{ $order->client_phone }} | <strong>Email:</strong> {{ $order->client_email }}</p>
                            <p><strong>Fulfillment:</strong> {{ strtoupper($order->fulfillment_type) }} 
                                @if($order->fulfillment_type == 'delivery')
                                    ({{ $order->delivery_address }})
                                @endif
                            </p>

                            <div class="order-items-summary">
                                <strong>Ordered Items:</strong>
                                <ul>
                                    @foreach($order->items as $item)
                                        <li>{{ $item['name'] }} - ${{ number_format($item['price'], 2) }}</li>
                                    @endforeach
                                </ul>
                            </div>

                            @if(!empty($order->flavors))
                                <p><strong>Flavors:</strong> {{ implode(', ', $order->flavors) }}</p>
                            @endif
                            @if(!empty($order->frosting))
                                <p><strong>Frosting:</strong> {{ implode(', ', $order->frosting) }}</p>
                            @endif
                            @if(!empty($order->fillings))
                                <p><strong>Fillings:</strong> {{ implode(', ', $order->fillings) }}</p>
                            @endif
                            @if($order->special_notes)
                                <p class="notes-box"><strong>Special Notes:</strong> {{ $order->special_notes }}</p>
                            @endif
                            @if($order->allergies)
                                <p class="allergy-warning"><strong>⚠️ Allergies:</strong> {{ $order->allergies }}</p>
                            @endif

                            <div class="pricing-breakdown">
                                <span>Total: <strong>${{ number_format($order->total_price, 2) }}</strong></span>
                                <span>50% Deposit: <strong>${{ number_format($order->deposit_amount, 2) }}</strong> 
                                    ({{ $order->deposit_paid ? '✅ Paid' : '⏳ Pending' }})
                                </span>
                            </div>
                        </div>

                        <div class="order-card-actions">
                            <button class="btn btn-sm btn-primary" onclick="generateInvoiceFromOrder('{{ $order->order_number }}', '{{ $order->client_name }}', {{ $order->total_price }}, {{ $order->deposit_amount }})">💳 Create Invoice</button>
                            <button class="btn btn-sm btn-secondary" onclick="markDepositPaid(this)">✅ Mark Deposit Paid</button>
                            <button class="btn btn-sm btn-outline" onclick="copyClientPayLink('{{ $order->order_number }}')">📋 Copy Invoice Link</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- TAB 2: Invoices & Payment Links -->
        <div id="tab-invoices" class="tab-content">
            <div class="section-header">
                <h3>💳 Invoices & 3rd-Party Payment Links</h3>
                <p class="subtitle">Send digital invoices & accept payments directly via Venmo, CashApp, PayPal, Zelle, or Stripe!</p>
            </div>

            <div class="payment-methods-card">
                <h4>Your Active Payment Methods</h4>
                <div class="methods-grid">
                    <div class="method-box"><strong>Venmo:</strong> {{ $tenant->payment_settings['venmo'] ?? '@Blushed_Crumbs' }}</div>
                    <div class="method-box"><strong>CashApp:</strong> {{ $tenant->payment_settings['cashapp'] ?? '$BlushedCrumbs' }}</div>
                    <div class="method-box"><strong>PayPal:</strong> {{ $tenant->payment_settings['paypal'] ?? 'paypal.me/BlushedCrumbs' }}</div>
                    <div class="method-box"><strong>Zelle:</strong> {{ $tenant->payment_settings['zelle'] ?? 'orders@blushedcrumbsbakehouse.com' }}</div>
                </div>
            </div>

            <div class="create-invoice-box">
                <h4>Quick Create New Invoice</h4>
                <form id="quick-invoice-form" class="admin-form-grid">
                    <input type="text" id="inv-client-name" placeholder="Client Name" required>
                    <input type="email" id="inv-client-email" placeholder="Client Email" required>
                    <input type="number" id="inv-amount" placeholder="Total Amount ($)" step="0.01" required>
                    <button type="submit" class="btn btn-primary">Generate Invoice</button>
                </form>
            </div>

            <div class="invoices-list-table">
                <h4>Existing Invoices</h4>
                <div id="invoices-container">
                    @foreach($invoices as $inv)
                        <div class="invoice-row">
                            <div>
                                <strong>#{{ $inv->invoice_number }}</strong> - {{ $inv->client_name }}
                                <br><small>${{ number_format($inv->total_amount, 2) }} (Deposit: ${{ number_format($inv->deposit_amount, 2) }})</small>
                            </div>
                            <div>
                                <span class="status-tag status-{{ $inv->status }}">{{ strtoupper($inv->status) }}</span>
                                <button class="btn btn-sm btn-outline" onclick="viewInvoiceModal('{{ $inv->invoice_number }}', '{{ $inv->client_name }}', {{ $inv->total_amount }}, {{ $inv->deposit_amount }})">View / Print</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- TAB 3: Products & Prices Manager -->
        <div id="tab-products" class="tab-content">
            <div class="section-header">
                <h3>🧁 Product & Price Manager</h3>
                <p class="subtitle">Add new bakery items or adjust prices instantly—no technical tickets needed!</p>
            </div>

            <div class="add-product-box">
                <h4>Add New Baked Good / Product</h4>
                <form id="add-product-form" class="admin-form-grid">
                    <input type="text" id="new-prod-name" placeholder="Product Name (e.g. 6” Custom Heart Cake)" required>
                    <input type="number" id="new-prod-price" placeholder="Price ($)" step="0.01" required>
                    <select id="new-prod-category">
                        <option value="Single Tier">Single Tier</option>
                        <option value="Multi-Tier">Multi-Tier</option>
                        <option value="By The Dozen">By The Dozen</option>
                        <option value="Treats">Treats</option>
                        <option value="Party Packs">Party Packs</option>
                    </select>
                    <button type="submit" class="btn btn-primary">+ Add Product</button>
                </form>
            </div>

            <div class="products-manage-list">
                <h4>Current Storefront Products</h4>
                <div id="products-admin-grid">
                    @foreach($products as $prod)
                        <div class="product-item-row">
                            <span><strong>{{ $prod->name }}</strong> ({{ $prod->category }})</span>
                            <div>
                                <input type="number" class="price-input" value="{{ number_format($prod->price, 2) }}" data-id="{{ $prod->id }}">
                                <button class="btn btn-sm btn-secondary" onclick="updateProductPrice(this)">Save Price</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- TAB 4: Customer Reviews Manager -->
        <div id="tab-reviews" class="tab-content">
            <div class="section-header">
                <h3>⭐ Customer Reviews Manager</h3>
                <p class="subtitle">Add client reviews directly from your phone to display on the storefront!</p>
            </div>

            <div class="add-review-box">
                <h4>Add New Customer Review</h4>
                <form id="add-review-form" class="admin-form">
                    <input type="text" id="rev-client-name" placeholder="Client Name (e.g. Kristen Ramirez)" required>
                    <select id="rev-rating">
                        <option value="5">⭐⭐⭐⭐⭐ (5 Stars)</option>
                        <option value="4">⭐⭐⭐⭐ (4 Stars)</option>
                    </select>
                    <textarea id="rev-text" placeholder="Write or paste client review here..." required></textarea>
                    <button type="submit" class="btn btn-primary">Publish Review to Storefront</button>
                </form>
            </div>
        </div>

        <!-- TAB 5: Gallery Manager -->
        <div id="tab-gallery" class="tab-content">
            <div class="section-header">
                <h3>🖼️ Gallery Image Manager</h3>
                <p class="subtitle">Add custom cake photos to your portfolio gallery.</p>
            </div>

            <div class="add-gallery-box">
                <h4>Add Photo to Gallery</h4>
                <form id="add-gallery-form" class="admin-form">
                    <input type="text" id="gal-title" placeholder="Cake Title (e.g. 2-Tiered Floral Wedding Cake)" required>
                    <select id="gal-category">
                        <option value="Single Tier">Single Tier</option>
                        <option value="Multi-Tier">Multi-Tier</option>
                        <option value="By The Dozen">By The Dozen</option>
                        <option value="Party Packs">Party Packs</option>
                    </select>
                    <input type="url" id="gal-image-url" placeholder="Image URL (e.g. https://...)" required>
                    <button type="submit" class="btn btn-primary">+ Add Photo to Gallery</button>
                </form>
            </div>
        </div>

        <!-- TAB 6: Custom Code & Support Request -->
        <div id="tab-support" class="tab-content">
            <div class="section-header">
                <h3>💬 Custom Code & Support Request</h3>
                <p class="subtitle">Need a custom feature, layout change, or technical assistance? Send us a direct request!</p>
            </div>

            <div class="support-box">
                <form id="support-request-form" class="admin-form">
                    <label>Request Type:</label>
                    <select id="support-type">
                        <option value="custom_code">✨ Request Custom Code / Feature</option>
                        <option value="support">🛠️ Technical Support</option>
                        <option value="billing">💳 Subscription / Billing Question</option>
                    </select>

                    <label>Subject:</label>
                    <input type="text" id="support-subject" placeholder="e.g. Add custom flavor option to step 3" required>

                    <label>Describe what you need:</label>
                    <textarea id="support-message" placeholder="Details about what you would like custom built or fixed..." required></textarea>

                    <button type="submit" class="btn btn-primary">Send Support Ticket</button>
                </form>
            </div>
        </div>

    </div>
</div>

<!-- Modal for Viewing & Printing Invoices -->
<div id="invoice-modal" class="modal-overlay" style="display:none;">
    <div class="modal-card">
        <button class="modal-close" onclick="closeInvoiceModal()">✕</button>
        <div id="printable-invoice">
            <div class="inv-header">
                <h2>🌸 Blushed Crumbs Bakehouse</h2>
                <p>Tennessee Cottage Bakery | Official Invoice</p>
            </div>
            <hr>
            <div class="inv-details">
                <p><strong>Invoice #:</strong> <span id="modal-inv-num">BC-INV-101</span></p>
                <p><strong>Client:</strong> <span id="modal-inv-client">Client Name</span></p>
                <p><strong>Date:</strong> {{ date('F d, Y') }}</p>
            </div>
            <table class="inv-table">
                <thead>
                    <tr><th>Description</th><th>Amount</th></tr>
                </thead>
                <tbody>
                    <tr><td>Custom Bakery Order & Event Prep</td><td id="modal-inv-total">$0.00</td></tr>
                    <tr><td><strong>50% Non-Refundable Deposit Due</strong></td><td id="modal-inv-deposit"><strong>$0.00</strong></td></tr>
                </tbody>
            </table>
            <div class="inv-pay-methods">
                <h4>Payable via:</h4>
                <p>• <strong>Venmo:</strong> {{ $tenant->payment_settings['venmo'] ?? '@Blushed_Crumbs' }}</p>
                <p>• <strong>CashApp:</strong> {{ $tenant->payment_settings['cashapp'] ?? '$BlushedCrumbs' }}</p>
                <p>• <strong>PayPal:</strong> {{ $tenant->payment_settings['paypal'] ?? 'paypal.me/BlushedCrumbs' }}</p>
                <p>• <strong>Zelle:</strong> {{ $tenant->payment_settings['zelle'] ?? 'orders@blushedcrumbsbakehouse.com' }}</p>
            </div>
            <div class="inv-terms">
                <p><em>*A 50% non-refundable deposit is required to lock in your order date. Thank you for supporting Blushed Crumbs Bakehouse!</em></p>
            </div>
        </div>
        <button class="btn btn-primary" onclick="window.print()">🖨️ Print Invoice</button>
    </div>
</div>
@endsection
