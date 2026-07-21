@extends('layouts.app')

@section('title', 'Baker Admin Portal | Blushed Crumbs Bakehouse')

@section('content')
<!-- PAGE 4: BAKER ADMIN PORTAL VIEW WITH MODERN SIDEBAR -->
<div id="admin-portal-view">
    <div class="admin-layout-container">
        <!-- LEFT SIDEBAR -->
        <aside class="admin-sidebar">
            <div class="admin-sidebar-brand">
                <span class="logo-icon" style="font-size:2.2rem;">🌸</span>
                <div>
                    <h3>Blushed Crumbs</h3>
                    <span class="badge-pro">Pro Baker CMS</span>
                </div>
            </div>

            <nav class="admin-sidebar-nav">
                <button class="admin-nav-item active" data-tab="tab-orders">
                    <span>📅</span> Priority Queue
                </button>
                <button class="admin-nav-item" data-tab="tab-form-builder">
                    <span>⚙️</span> Form Studio
                </button>
                <button class="admin-nav-item" data-tab="tab-gallery-manager">
                    <span>📷</span> Device Gallery
                </button>
                <button class="admin-nav-item" data-tab="tab-invoices">
                    <span>💳</span> Invoices & Payments
                </button>
                <button class="admin-nav-item" data-tab="tab-reviews">
                    <span>⭐</span> Client Reviews
                </button>
                <button class="admin-nav-item" data-tab="tab-support">
                    <span>💬</span> Baker Support
                </button>
            </nav>

            <div class="admin-sidebar-footer">
                <a href="{{ route('storefront.index') }}" class="btn btn-outline" style="display:block; text-align:center; width:100%; border-color:rgba(255,255,255,0.3); color:white; text-decoration:none;">← Exit to Storefront</a>
            </div>
        </aside>

        <!-- RIGHT MAIN CONTENT -->
        <main class="admin-main-content">
            <!-- TAB 1: Priority Orders Queue -->
            <div id="tab-orders" class="tab-content active">
                <div class="section-header">
                    <h3>📅 Priority Order Queue</h3>
                    <p class="subtitle">Sorted chronologically by <strong>due date</strong> so you know exactly what is due first!</p>
                </div>
                <div class="orders-list-grid" id="admin-orders-list">
                    @forelse($urgentOrders as $order)
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
                                <button class="btn btn-sm btn-outline" onclick="copyClientPayLink('{{ $order->order_number }}')">📋 Copy Invoice Link</button>
                            </div>
                        </div>
                    @empty
                        <div class="order-card urgent-border">
                            <div class="order-card-header">
                                <div class="due-badge due-urgent">⏰ DUE: Tomorrow (9:30 AM)</div>
                                <span class="status-tag status-in_progress">IN PROGRESS</span>
                            </div>
                            <div class="order-card-body">
                                <h4>#BC-1092 - Sarah Jenkins</h4>
                                <p><strong>Phone:</strong> (555) 234-8890 | <strong>Email:</strong> sarah.j@example.com</p>
                                <p><strong>Item:</strong> 6” Cake ($65.00) | <strong>Flavor:</strong> Strawberry Bliss | <strong>Frosting:</strong> Vanilla Buttercream</p>
                                <p class="notes-box" style="background:#fff7fa; padding:8px 12px; border-radius:8px; margin-top:8px;"><strong>Special Notes:</strong> "Happy 30th Birthday Emma!" in gold lettering</p>
                                <div class="pricing-breakdown" style="margin-top:10px;">
                                    <span>Total: <strong>$60.00</strong></span> | <span>50% Deposit: <strong>$30.00</strong> (✅ Paid)</span>
                                </div>
                            </div>
                            <div class="order-card-actions">
                                <button class="btn btn-sm btn-primary" onclick="generateInvoiceFromOrder('BC-1092', 'Sarah Jenkins', 60, 30)">💳 Create Invoice</button>
                                <button class="btn btn-sm btn-outline" onclick="copyClientPayLink('BC-1092')">📋 Copy Invoice Link</button>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- TAB 2: CMS Form Builder & Email Routing Studio -->
            <div id="tab-form-builder" class="tab-content">
                <div class="section-header">
                    <h3>⚙️ CMS Form Builder & Email Routing Studio</h3>
                    <p class="subtitle">Customize order form fields, add custom questions, edit live pricing, and set your order notification email address!</p>
                </div>

                <!-- EMAIL ROUTING SETTINGS CARD -->
                <div class="form-builder-card" style="border: 2px solid #e67399; background: #fff7fa;">
                    <h4 style="color:#5c1d37; display:flex; align-items:center; gap:8px;">✉️ Order Email Routing & Notifications</h4>
                    <p style="font-size:0.9rem; color:#666; margin-bottom:15px;">Set the email address where all completed order form entries and custom requests will be automatically sent:</p>
                    <form id="email-routing-form" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
                        <input type="email" id="admin-routing-email" value="{{ $tenant->email ?? 'orders@blushedcrumbsbakehouse.com' }}" placeholder="e.g. baker@yourbakehouse.com" required style="flex:1; min-width:280px;">
                        <button type="submit" class="btn btn-primary" style="padding:12px 24px;">💾 Save Email Routing</button>
                    </form>
                    <div id="email-save-status" style="margin-top:10px; font-weight:700; color:#28a745; font-size:0.88rem; display:none;"></div>
                </div>

                <!-- ADD CUSTOM QUESTION / FIELD CARD -->
                <div class="form-builder-card">
                    <h4>➕ Add Custom Question or Field to Order Form</h4>
                    <form id="add-field-form" class="form-builder-grid">
                        <div>
                            <label>Target Step</label>
                            <select id="field-target-step">
                                <option value="step-6">Step 6: Special Requests</option>
                                <option value="step-3">Step 3: Flavors</option>
                                <option value="step-4">Step 4: Frosting</option>
                                <option value="step-5">Step 5: Fillings</option>
                                <option value="step-8">Step 8: Allergies</option>
                                <option value="step-10">Step 10: Inspiration Files</option>
                            </select>
                        </div>
                        <div>
                            <label>Question Title</label>
                            <input type="text" id="field-label" placeholder="e.g. Color Theme / Event Name" required>
                        </div>
                        <div>
                            <label>Field Type</label>
                            <select id="field-type">
                                <option value="text">Single Line Text Input</option>
                                <option value="textarea">Multi-line Textarea</option>
                                <option value="select">Multiple Choice Select</option>
                                <option value="file">File / Photo Upload</option>
                            </select>
                        </div>
                        <div>
                            <label>Options (if Select)</label>
                            <input type="text" id="field-options" placeholder="Gold, Rose Gold, Baby Pink">
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <button type="submit" class="btn btn-primary">+ Add Question to Order Form</button>
                        </div>
                    </form>
                </div>

                <!-- ADD NEW PRODUCT & LIVE PRICE CARD -->
                <div class="form-builder-card">
                    <h4>🎂 Add New Product & Price to Order Form</h4>
                    <form id="add-product-form" class="form-builder-grid">
                        <div>
                            <label>Product Name</label>
                            <input type="text" id="new-prod-name" placeholder="e.g. 6” Heart Cake" required>
                        </div>
                        <div>
                            <label>Price ($)</label>
                            <input type="number" id="new-prod-price" placeholder="45.00" step="0.01" required>
                        </div>
                        <div>
                            <label>Category</label>
                            <select id="new-prod-category">
                                <option value="Single Tier">Single Tier</option>
                                <option value="Multi-Tier">Multi-Tier</option>
                                <option value="By The Dozen">By The Dozen</option>
                                <option value="Treats">Treats</option>
                                <option value="Party Packs">Party Packs</option>
                            </select>
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <button type="submit" class="btn btn-primary">+ Add Product Item</button>
                        </div>
                    </form>
                </div>

                <!-- LIVE PRODUCTS CATALOG LIST -->
                <div class="form-builder-card">
                    <h4>Current Product Catalog & Live Prices</h4>
                    <div id="products-admin-grid">
                        @foreach($products as $prod)
                            <div class="product-item-row" style="display:flex; justify-content:space-between; align-items:center; padding:12px; border-bottom:1px solid #eee;">
                                <span><strong>{{ $prod->name }}</strong> (${{ number_format($prod->price, 2) }})</span>
                                <div>
                                    <input type="number" value="{{ number_format($prod->price, 2) }}" style="width:80px;">
                                    <button class="btn btn-sm btn-secondary" onclick="alert('Price updated!')">Save Price</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- TAB 3: Device Gallery Photo Uploader -->
            <div id="tab-gallery-manager" class="tab-content">
                <div class="section-header">
                    <h3>📷 Device Gallery Uploader</h3>
                    <p class="subtitle">Upload photos directly from your computer, phone, or tablet to publish live to your public <strong>/gallery</strong> page!</p>
                </div>

                <div class="form-builder-card">
                    <h4>Upload Photo From Device</h4>
                    <form id="add-gallery-form" style="display:flex; flex-direction:column; gap:18px;">
                        <div class="form-builder-grid">
                            <div>
                                <label>Photo Title</label>
                                <input type="text" id="gal-title" placeholder="e.g. Lavender Crown Vintage Cake" required>
                            </div>
                            <div>
                                <label>Gallery Category</label>
                                <select id="gal-category">
                                    <option value="Cakes">Custom Cakes</option>
                                    <option value="Cupcakes">Cupcakes & Shooters</option>
                                    <option value="Treats">Chocolate Treats</option>
                                    <option value="Weddings">Weddings</option>
                                </select>
                            </div>
                        </div>

                        <!-- DEVICE FILE PICKER & DROPZONE -->
                        <div>
                            <label>Select Image File From Your Device</label>
                            <div id="gal-device-dropzone" style="border:2px dashed #e67399; background:#fff7fa; padding:30px 20px; border-radius:16px; text-align:center; cursor:pointer;" onclick="document.getElementById('gal-image-file').click();">
                                <span style="font-size:2.5rem; color:#e67399; display:block; margin-bottom:8px;">📷</span>
                                <p style="font-size:1.05rem; font-weight:600; color:#5c1d37;">Click to select photo from device or drag image here</p>
                                <span style="font-size:12px; color:#888;">Supports JPG, PNG, WEBP</span>
                            </div>
                            <input type="file" id="gal-image-file" accept="image/*" style="display:none;">
                        </div>

                        <!-- LIVE PREVIEW CONTAINER -->
                        <div id="gal-upload-preview" style="display:none; text-align:center;">
                            <img id="gal-preview-img" src="" style="max-width:200px; height:140px; object-fit:cover; border-radius:14px; border:2px solid #e67399; box-shadow:0 4px 15px rgba(0,0,0,0.1);">
                            <p style="font-weight:700; color:#28a745; margin-top:6px; font-size:0.9rem;">✅ Photo ready for publish</p>
                        </div>

                        <button type="submit" class="btn btn-primary" style="padding:14px;">🚀 Publish Photo to Live Gallery</button>
                    </form>
                </div>

                <div class="form-builder-card">
                    <h4>Current Published Gallery Photos</h4>
                    <div id="admin-gallery-list">
                        @foreach($gallery as $item)
                            <div style="display:flex; align-items:center; justify-content:space-between; background:white; padding:12px; border-radius:12px; margin-bottom:10px; box-shadow:0 4px 12px rgba(0,0,0,0.05);">
                                <div style="display:flex; align-items:center; gap:15px;">
                                    <img src="{{ asset($item->image_path) }}" style="width:55px; height:55px; object-fit:cover; border-radius:10px;">
                                    <div>
                                        <strong style="color:#5c1d37;">{{ $item->title }}</strong><br>
                                        <span style="font-size:0.8rem; color:#e67399; font-weight:600;">{{ $item->category }}</span>
                                    </div>
                                </div>
                                <button class="btn btn-sm btn-outline" style="color:#d9534f; border-color:#d9534f;" onclick="this.parentElement.remove()">Delete</button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- TAB 4: Invoices & Payment Handles Manager -->
            <div id="tab-invoices" class="tab-content">
                <div class="section-header">
                    <h3>💳 Invoices & Payment Options Studio</h3>
                    <p class="subtitle">Add your custom payment methods, set payout usernames, and generate digital client invoices!</p>
                </div>

                <!-- ADD CUSTOM PAYMENT METHOD CARD -->
                <div class="form-builder-card" style="border:2px solid #e67399; background:#fff7fa;">
                    <h4 style="color:#5c1d37;">➕ Add Custom Payment Option</h4>
                    <form id="add-payment-method-form" class="form-builder-grid">
                        <div>
                            <label>Payment Method Name</label>
                            <input type="text" id="pay-method-name" placeholder="e.g. Venmo, CashApp, Zelle, Apple Pay, Cash" required>
                        </div>
                        <div>
                            <label>Handle / Username / Email</label>
                            <input type="text" id="pay-method-handle" placeholder="e.g. @Blushed_Crumbs or $BlushedCrumbs" required>
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <label>Payment Instructions for Clients</label>
                            <input type="text" id="pay-method-instructions" placeholder="e.g. Please include your Order # in the memo line!">
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <button type="submit" class="btn btn-primary">+ Add Payment Method</button>
                        </div>
                    </form>
                </div>

                <!-- ACTIVE PAYMENT METHODS LIST -->
                <div class="form-builder-card">
                    <h4>Active Payment Handles & Methods</h4>
                    <div id="payment-methods-list">
                        <div class="payment-method-row" style="display:flex; justify-content:space-between; align-items:center; background:white; padding:15px; border-radius:12px; margin-bottom:10px; border:1px solid #eee;">
                            <div>
                                <strong style="color:#5c1d37; font-size:1.05rem;">🟣 Venmo</strong>: <code>{{ $tenant->payment_settings['venmo'] ?? '@Blushed_Crumbs' }}</code>
                                <p style="font-size:0.85rem; color:#666; margin-top:2px;">Include Order # in payment memo</p>
                            </div>
                            <button class="btn btn-sm btn-outline" style="color:#d9534f; border-color:#d9534f;" onclick="this.parentElement.remove()">Remove</button>
                        </div>
                        <div class="payment-method-row" style="display:flex; justify-content:space-between; align-items:center; background:white; padding:15px; border-radius:12px; margin-bottom:10px; border:1px solid #eee;">
                            <div>
                                <strong style="color:#5c1d37; font-size:1.05rem;">🟢 CashApp</strong>: <code>{{ $tenant->payment_settings['cashapp'] ?? '$BlushedCrumbs' }}</code>
                                <p style="font-size:0.85rem; color:#666; margin-top:2px;">Include Order # in payment memo</p>
                            </div>
                            <button class="btn btn-sm btn-outline" style="color:#d9534f; border-color:#d9534f;" onclick="this.parentElement.remove()">Remove</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 5: Customer Reviews -->
            <div id="tab-reviews" class="tab-content">
                <div class="section-header">
                    <h3>⭐ Customer Reviews Manager</h3>
                    <p class="subtitle">Add client reviews directly to your homepage!</p>
                </div>
                <div class="form-builder-card">
                    <form id="add-review-form" style="display:flex; flex-direction:column; gap:12px;">
                        <div>
                            <label>Client Name</label>
                            <input type="text" id="rev-client-name" placeholder="Client Name" required>
                        </div>
                        <div>
                            <label>Review Text</label>
                            <textarea id="rev-text" placeholder="Paste review text here..." required style="height:90px;"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Publish Review to Storefront</button>
                    </form>
                </div>
            </div>

            <!-- TAB 6: Support -->
            <div id="tab-support" class="tab-content">
                <div class="section-header">
                    <h3>💬 Custom Code & Support Request</h3>
                    <p class="subtitle">Direct support request form for custom features ($50/mo Pro Tier perk)</p>
                </div>
                <div class="form-builder-card">
                    <form id="support-request-form" style="display:flex; flex-direction:column; gap:12px;">
                        <div>
                            <label>Subject</label>
                            <input type="text" placeholder="e.g. Custom theme tweak request" required>
                        </div>
                        <div>
                            <label>Description</label>
                            <textarea placeholder="Describe custom code or support request..." required style="height:110px;"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Support Request</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection
