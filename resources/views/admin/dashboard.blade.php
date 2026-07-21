@extends('layouts.app')

@section('title', 'Baker Admin Portal | Blushed Crumbs Bakehouse')

@section('content')
<!-- PAGE 4: BAKER ADMIN PORTAL VIEW WITH MODERN SIDEBAR -->
<div id="admin-portal-view">
    <!-- MOBILE TOP BAR WITH HAMBURGER BUTTON -->
    <div class="admin-mobile-header">
        <div class="mobile-brand">
            <span style="font-size:1.8rem;">🌸</span>
            <strong style="font-size:1.15rem; color:#ffffff;">Blushed Crumbs Admin</strong>
        </div>
        <button class="mobile-hamburger-btn" id="mobile-hamburger-trigger" onclick="toggleAdminMobileSidebar()" aria-label="Open Navigation Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>

    <!-- BACKDROP OVERLAY FOR MOBILE SIDEBAR DRAWER -->
    <div class="admin-sidebar-overlay" id="admin-sidebar-overlay" onclick="toggleAdminMobileSidebar()"></div>

    <div class="admin-layout-container">
        <!-- LEFT SIDEBAR -->
        <aside class="admin-sidebar" id="admin-sidebar-drawer">
            <div class="admin-sidebar-brand">
                <span class="logo-icon" style="font-size:2.2rem;">🌸</span>
                <div>
                    <h3>Blushed Crumbs</h3>
                    <span class="badge-pro">Pro Baker CMS</span>
                </div>
                <button class="drawer-close-btn" onclick="toggleAdminMobileSidebar()">✕</button>
            </div>

            <nav class="admin-sidebar-nav">
                <button class="admin-nav-item active" data-tab="tab-orders">
                    <span>📅</span> Priority Queue
                </button>
                <button class="admin-nav-item" data-tab="tab-form-builder">
                    <span>⚙️</span> Form Studio
                </button>
                <button class="admin-nav-item" data-tab="tab-products">
                    <span>🎂</span> Products
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
                <button class="admin-nav-item" data-tab="tab-calendar">
                    <span>📆</span> Availability & Blackouts
                </button>
                <button class="admin-nav-item" data-tab="tab-settings">
                    <span>🔧</span> Settings
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

            <!-- TAB 2: Form Studio -->
            <div id="tab-form-builder" class="tab-content">
                @php
                    $serverFormSchema = $tenant->form_schema ?? \App\Models\Tenant::getDefaultFormSchema();
                    $serverBookingSettings = $tenant->booking_settings ?? [
                        'lead_time_enabled' => true,
                        'lead_time_days' => 3,
                        'recurring_closed_days' => [0, 1],
                        'blocked_dates' => ['2026-07-04', '2026-07-25']
                    ];
                @endphp
                <script>
                    window._serverFormSchema = @json($serverFormSchema);
                    window._serverBookingSettings = @json($serverBookingSettings);
                </script>
                <div class="section-header">
                    <h3>⚙️ Form Studio</h3>
                    <p class="subtitle">Customize step headers, directions, and form fields for your bakery (cakes, cookies, sourdough, etc.). Step 1 is anchored to your Product Catalog.</p>
                </div>

                <!-- EMAIL ROUTING SETTINGS CARD -->
                <div class="form-builder-card" style="border: 2px solid #e67399; background: #fff7fa;">
                    <h4 style="color:#5c1d37;">✉️ Order Email Routing</h4>
                    <p style="font-size:0.9rem; color:#666; margin-bottom:15px;">All completed order form entries will be sent to this address:</p>
                    <form id="email-routing-form" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
                        <input type="email" id="admin-routing-email" value="{{ $tenant->email ?? 'orders@blushedcrumbsbakehouse.com' }}" placeholder="e.g. baker@yourbakehouse.com" required style="flex:1; min-width:220px;">
                        <button type="submit" class="btn btn-primary">💾 Save</button>
                    </form>
                    <div id="email-save-status" style="margin-top:10px; font-weight:700; color:#28a745; font-size:0.88rem; display:none;"></div>
                </div>

                <!-- ADD STEP / FIELD CARD -->
                <div class="form-builder-card">
                    <h4>➕ Add Step or Field to Order Builder</h4>
                    <form id="add-field-form" class="form-builder-grid">
                        <div style="grid-column: 1 / -1;">
                            <label style="font-weight:700; color:#5c1d37;">Field Type / Template</label>
                            <select id="field-type" onchange="toggleOptionsRow(this.value)" style="width:100%; max-width:100%; box-sizing:border-box; text-overflow:ellipsis;">
                                <option value="products">🛒 Product Catalog</option>
                                <option value="calendar">📅 Booking Calendar</option>
                                <option value="flavors">🍰 Flavors Grid</option>
                                <option value="frosting">🧁 Frosting Grid</option>
                                <option value="fillings">🍫 Fillings Grid</option>
                                <option value="textarea">📄 Textarea / Notes</option>
                                <option value="fulfillment">🚚 Fulfillment &amp; Time Slots</option>
                                <option value="allergies">⚠️ Allergy Notice</option>
                                <option value="social_discount">🎁 Social Discounts</option>
                                <option value="file_upload">📎 Inspiration Photo Upload</option>
                                <option value="terms">📜 Terms &amp; Conditions</option>
                                <option value="contact_info">👤 Contact Info &amp; Submit</option>
                                <option value="text">📝 Single-Line Text</option>
                                <option value="select">☑️ Select Dropdown</option>
                                <option value="datepicker">📅 Date Picker</option>
                                <option value="toggle">🔘 Yes / No Toggle</option>
                            </select>
                        </div>
                        <div>
                            <label>Step Header / Title</label>
                            <input type="text" id="field-label" placeholder="e.g. Choose Your Flavors, Select Crust Type…" required>
                        </div>
                        <div>
                            <label>Step Subtext / Directions</label>
                            <input type="text" id="field-description" placeholder="e.g. Select all options that apply to your order">
                        </div>
                        <div id="field-options-row" style="grid-column: 1 / -1; margin-top: 10px;">
                            <label style="font-weight:700; color:#5c1d37; display:block; margin-bottom:8px;">
                                Step Options &amp; Extra Charges
                                <span style="font-weight:500; font-size:0.85rem; color:#888;">(Separate inputs for option names and optional extra charges)</span>
                            </label>
                            
                            <!-- Dynamic Option Rows Container -->
                            <div id="option-rows-container" style="display:flex; flex-direction:column; gap:10px; margin-bottom:12px;">
                                <!-- Option rows rendered dynamically by JS -->
                            </div>

                            <input type="hidden" id="field-options">

                            <button type="button" class="btn btn-outline btn-sm" onclick="addAdminOptionRow()" style="border-radius:12px; font-weight:700; color:#e67399; border-color:#f8c6d7;">
                                ➕ Add Option Choice
                            </button>
                        </div>
                        <div style="grid-column: 1 / -1; margin-top:10px;">
                            <button type="submit" class="btn btn-primary" style="width:100%;">+ Add Step to Order Form</button>
                        </div>
                    </form>
                </div>

                <!-- LIVE FIELDS TABLE WITH REORDER & SAVE -->
                <div class="form-builder-card">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
                        <div>
                            <h4 style="margin-bottom:4px;">📋 Configured Form Steps &amp; Fields</h4>
                            <span style="font-size:0.85rem; color:#888; font-weight:500;">Step 1 is anchored to Product Catalog. Use ↑↓ or drag rows to reorder steps.</span>
                        </div>
                        <button id="save-form-schema-btn" class="btn btn-primary" onclick="saveFormSchemaToServer()">💾 Save Order Form Layout Live</button>
                    </div>

                    <div class="field-table-wrapper">
                        <table class="field-table" id="custom-fields-table">
                            <thead>
                                <tr>
                                    <th style="width:36px;"></th>
                                    <th>Step #</th>
                                    <th>Step Header / Title</th>
                                    <th>Template / Type</th>
                                    <th>Subtext / Options</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="custom-fields-tbody">
                                <tr class="empty-row" id="fields-empty-row">
                                    <td colspan="6" style="text-align:center; padding:32px; color:#aaa; font-size:0.95rem;">
                                        Loading configured form steps…
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- TAB: Products -->
            <div id="tab-products" class="tab-content">
                <div class="section-header">
                    <h3>🎂 Product Catalog &amp; Pricing</h3>
                    <p class="subtitle">Add, remove, and update prices for your order form products. Changes reflect immediately on the storefront.</p>
                </div>

                <div class="form-builder-card" style="border:2px solid #e67399; background:#fff7fa;">
                    <h4>➕ Add New Product</h4>
                    <form id="add-product-form" class="form-builder-grid">
                        <div>
                            <label>Product Name</label>
                            <input type="text" id="new-prod-name" placeholder="e.g. 6″ Heart Cake" required>
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
                            <button type="submit" class="btn btn-primary" style="width:100%;">+ Add Product to Catalog</button>
                        </div>
                    </form>
                </div>

                <div class="form-builder-card">
                    <h4>📋 Current Product Catalog</h4>
                    <div id="products-admin-grid">
                        @foreach($products as $prod)
                            <div class="product-item-row" style="display:flex; justify-content:space-between; align-items:center; padding:13px 16px; border-bottom:1px solid #f0e4ea;">
                                <div>
                                    <strong style="color:#5c1d37;">{{ $prod->name }}</strong>
                                    <span style="background:#f9e0eb; color:#7a2b4a; font-size:0.75rem; font-weight:700; padding:2px 8px; border-radius:20px; margin-left:8px;">{{ $prod->category }}</span>
                                </div>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span style="font-size:0.85rem; color:#999;">$</span>
                                    <input type="number" value="{{ number_format($prod->price, 2) }}" style="width:80px;">
                                    <button class="btn btn-sm btn-secondary" onclick="showToast('Price updated successfully!')">Save</button>
                                    <button class="btn btn-sm btn-outline" style="color:#d9534f; border-color:#d9534f;" onclick="this.closest('.product-item-row').remove()">✕</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>


            <div id="tab-gallery-manager" class="tab-content">
                <div class="section-header">
                    <h3>📷 Device Gallery Uploader</h3>
                    <p class="subtitle">Upload photos directly from your computer, phone, or tablet to publish live to your public <strong>/gallery</strong> page!</p>
                </div>

                <div class="form-builder-card">
                    <h4>Upload Photo From Device</h4>
                    <form id="add-gallery-form" action="{{ route('admin.gallery.store') }}" method="POST" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:18px;">
                        @csrf
                        <div class="form-builder-grid">
                            <div>
                                <label>Photo Title</label>
                                <input type="text" id="gal-title" name="title" placeholder="e.g. Lavender Crown Vintage Cake" required>
                            </div>
                            <div>
                                <label>Gallery Category</label>
                                <select id="gal-category" name="category">
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
                                <p style="font-size:1.05rem; font-weight:600; color:#5c1d37;" id="gal-dropzone-text">Click to select photo from device or drag image here</p>
                                <span style="font-size:12px; color:#888;">Supports JPG, PNG, WEBP, GIF (Up to 10MB)</span>
                            </div>
                            <input type="file" id="gal-image-file" name="image" accept="image/*" style="display:none;" required>
                        </div>

                        <!-- LIVE PREVIEW CONTAINER -->
                        <div id="gal-upload-preview" style="display:none; text-align:center;">
                            <img id="gal-preview-img" src="" style="max-width:200px; height:140px; object-fit:cover; border-radius:14px; border:2px solid #e67399; box-shadow:0 4px 15px rgba(0,0,0,0.1);">
                            <p style="font-weight:700; color:#28a745; margin-top:6px; font-size:0.9rem;">✅ Photo ready for publish</p>
                        </div>

                        <button type="submit" id="gal-submit-btn" class="btn btn-primary" style="padding:14px;">🚀 Publish Photo to Live Gallery</button>
                    </form>
                </div>

                <div class="form-builder-card">
                    <h4>Current Published Gallery Photos</h4>
                    <div id="admin-gallery-list">
                        @foreach($gallery as $item)
                            <div class="admin-gallery-item-row" data-id="{{ $item->id }}" style="display:flex; align-items:center; justify-content:space-between; background:white; padding:12px; border-radius:12px; margin-bottom:10px; box-shadow:0 4px 12px rgba(0,0,0,0.05);">
                                <div style="display:flex; align-items:center; gap:15px;">
                                    @php $src = $item->image_url ?? $item->image_path; @endphp
                                    <img src="{{ asset($src) }}" style="width:55px; height:55px; object-fit:cover; border-radius:10px;">
                                    <div>
                                        <strong style="color:#5c1d37;">{{ $item->title }}</strong><br>
                                        <span style="font-size:0.8rem; color:#e67399; font-weight:600;">{{ $item->category }}</span>
                                    </div>
                                </div>
                                <button class="btn btn-sm btn-outline" style="color:#d9534f; border-color:#d9534f;" onclick="deleteGalleryItem({{ $item->id }}, this)">Delete</button>
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

            <!-- TAB: Settings -->
            <div id="tab-settings" class="tab-content">
                <div class="section-header">
                    <h3>🎨 Bakery Theme & Storefront Options</h3>
                    <p class="subtitle">Choose a professionally curated bakery theme and configure lead times for your site.</p>
                </div>

                <!-- CURATED BAKERY THEMES CARD -->
                <div class="form-builder-card" style="border:2px solid #e67399; background:#fff7fa;">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:12px;">
                        <div>
                            <h4 style="color:#5c1d37; margin:0;">🌸 Select Your Bakery Theme</h4>
                            <p style="font-size:0.88rem; color:#666; margin-top:4px;">Pick a standardized, low-maintenance design template. Customizes colors and layout automatically.</p>
                        </div>
                        <a href="{{ route('storefront.index') }}" target="_blank" class="btn btn-outline btn-sm" style="font-weight:700; border-color:#e67399; color:#e67399;">👁️ View Live Storefront ↗</a>
                    </div>

                    <div id="theme-status-msg" style="display:none; margin-bottom:14px; background:#d4edda; color:#155724; padding:10px 14px; border-radius:10px; font-size:0.88rem; font-weight:600; border:1px solid #c3e6cb;"></div>

                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px;">
                        @php
                            $themes = App\Models\Tenant::getAvailableThemes();
                            $currentTheme = $tenant->theme_id ?? 'sweet_elegant';
                        @endphp
                        @foreach($themes as $t)
                            <div class="bakery-theme-card" onclick="selectBakeryTheme('{{ $t['id'] }}', this)" style="border:{{ $currentTheme === $t['id'] ? '3px solid #e67399' : '2px solid #ddd' }}; background:white; padding:16px; border-radius:14px; cursor:pointer; position:relative; transition:transform 0.15s ease, border-color 0.15s ease; box-shadow:0 4px 12px rgba(0,0,0,0.05);">
                                <div style="height:80px; background:{{ $t['preview_bg'] }}; border-radius:10px; margin-bottom:12px; display:flex; align-items:center; justify-content:center; border:1px solid #eee;">
                                    <span style="font-weight:800; color:{{ $t['preview_accent'] }}; font-size:1.1rem;">{{ $t['name'] }}</span>
                                </div>
                                <h5 style="font-size:1rem; font-weight:700; color:#5c1d37; margin-bottom:4px;">{{ $t['name'] }}</h5>
                                <p style="font-size:0.8rem; color:#666; line-height:1.4;">{{ $t['subtitle'] }}</p>
                                <span class="theme-badge" style="display:{{ $currentTheme === $t['id'] ? 'inline-block' : 'none' }}; margin-top:8px; font-size:0.75rem; background:#e67399; color:white; padding:3px 10px; border-radius:20px; font-weight:700;">Active Theme</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- BOOKING RULES CARD -->
                <div class="form-builder-card">
                    <h4>📅 Order Lead Time</h4>
                    <p style="font-size:0.9rem; color:#666; margin-bottom:18px;">Prevent customers from selecting a completion date that is too soon to fulfill.</p>

                    <div class="settings-toggle-row" id="lead-time-toggle-row">
                        <div>
                            <strong>Block orders within 3 days of today</strong>
                            <p style="font-size:0.82rem; color:#888; margin-top:2px;">Customers cannot pick a date within 3 days of placing their order.</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="lead-time-enabled" checked onchange="toggleLeadTimeInput(this)">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div id="custom-lead-days-wrapper" style="display:none; margin-top:16px;">
                        <label>Days to auto-block from today</label>
                        <div style="display:flex; align-items:center; gap:12px; margin-top:8px;">
                            <input type="number" id="custom-lead-days" min="0" max="60" value="3" style="width:100px;">
                            <button class="btn btn-primary" onclick="saveLeadTime()">Save Setting</button>
                            <span id="lead-time-save-msg" style="font-size:0.85rem; color:#28a745; display:none;">✅ Saved!</span>
                        </div>
                    </div>
                </div>

                <!-- UNIFIED ACCORDION HOMEPAGE SECTION & CONTENT STUDIO -->
                <div class="form-builder-card" style="border:2px solid #8b5cf6; background:#f5f3ff; margin-top:20px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:14px;">
                        <div>
                            <h4 style="color:#6d28d9; margin:0;">☰ Homepage Section &amp; Content Accordion Studio</h4>
                            <p style="font-size:0.88rem; color:#666; margin-top:4px;">Click any section below to expand and edit its copy, images, and text. Reorder or toggle sections ON/OFF in real time.</p>
                        </div>
                        <button class="btn btn-primary" onclick="saveSectionManagerForm()" style="background:#7c3aed; border-color:#6d28d9;">💾 Save All Sections &amp; Copy</button>
                    </div>

                    <div id="section-manager-msg" style="display:none; margin-bottom:14px; background:#ddd6fe; color:#4c1d95; padding:10px 14px; border-radius:10px; font-size:0.88rem; font-weight:600; border:1px solid #c4b5fd;"></div>

                    <form id="section-manager-form">
                        @csrf
                        @php
                            $orderedSections = $tenant->getOrderedSections();
                            $siteContent = $tenant->site_content ?? App\Models\Tenant::getDefaultSiteContent();
                            $bullets = data_get($siteContent, 'whimsical_bullets', []);
                        @endphp

                        <div id="section-manager-list" style="display:flex; flex-direction:column; gap:12px;">
                            @foreach($orderedSections as $secId => $sec)
                                <div class="section-manager-row" data-id="{{ $secId }}" style="background:white; border-radius:12px; border:1px solid #ddd6fe; overflow:hidden; box-shadow:0 2px 8px rgba(109, 40, 217, 0.05);">
                                    
                                    <!-- ACCORDION HEADER ROW -->
                                    <div class="section-accordion-header" onclick="toggleSectionAccordion(this)" style="padding:14px 18px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; cursor:pointer; background:#FAF8FF; user-select:none;">
                                        <div style="display:flex; align-items:center; gap:12px;">
                                            <span class="drag-handle" style="cursor:grab; font-weight:800; color:#8b5cf6; font-size:1.2rem;" onclick="event.stopPropagation()">☰</span>
                                            <input type="hidden" class="section-order-input" name="sections[{{ $secId }}][order]" value="{{ $sec['order'] ?? 1 }}">
                                            <strong style="color:#4c1d95; font-size:1rem;">{{ $sec['name'] ?? $secId }}</strong>
                                        </div>

                                        <div style="display:flex; align-items:center; gap:10px;" onclick="event.stopPropagation()">
                                            <button type="button" class="btn btn-sm btn-outline" onclick="moveSectionUp(this)" style="padding:3px 8px; font-size:0.78rem;">⬆️ Up</button>
                                            <button type="button" class="btn btn-sm btn-outline" onclick="moveSectionDown(this)" style="padding:3px 8px; font-size:0.78rem;">⬇️ Down</button>
                                            <label class="toggle-switch" style="transform:scale(0.8);">
                                                <input type="checkbox" name="sections[{{ $secId }}][enabled]" value="1" {{ !empty($sec['enabled']) ? 'checked' : '' }}>
                                                <span class="toggle-slider"></span>
                                            </label>
                                            <span class="accordion-arrow" style="font-size:1rem; color:#8b5cf6; font-weight:800; margin-left:6px; transition:transform 0.2s ease;">🔽</span>
                                        </div>
                                    </div>

                                    <!-- EXPANDABLE ACCORDION BODY WITH SECTION COPY & CONTENT EDITORS -->
                                    <div class="section-accordion-body" style="display:none; padding:18px; border-top:1px solid #e9d5ff; background:#ffffff;">
                                        @if($secId === 'hero')
                                            <h6 style="color:#6d28d9; margin-bottom:10px; font-weight:700;">Edit Hero Copy, Buttons &amp; Background Media</h6>
                                            <div style="margin-bottom:12px; background:#FAF8FF; padding:12px; border-radius:10px; border:1px solid #e9d5ff;">
                                                <label style="font-weight:600; font-size:0.82rem; color:#555;">Hero Background Media (Image or Video)</label>
                                                <div style="display:flex; gap:10px; align-items:center; margin-top:4px;">
                                                    <input type="text" id="hero_bg_url" name="hero_bg_url" value="{{ data_get($siteContent, 'hero_bg_url', '') }}" placeholder="URL or uploaded path (e.g. uploads/hero.mp4)" style="flex:1; padding:8px; border-radius:8px; border:1px solid #ccc; font-family:monospace; font-size:0.85rem;">
                                                    <label class="btn btn-sm btn-outline" style="cursor:pointer; padding:6px 12px; border-color:#8b5cf6; color:#6d28d9; font-size:0.8rem; display:inline-flex; align-items:center; gap:4px;">
                                                        📁 Upload File
                                                        <input type="file" accept="image/*,video/*" onchange="uploadSectionMedia(this, 'hero_bg_url')" style="display:none;">
                                                    </label>
                                                </div>
                                            </div>
                                            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:12px;">
                                                <div>
                                                    <label style="font-weight:600; font-size:0.82rem; color:#555;">Hero Subheading</label>
                                                    <input type="text" name="hero_subheading" value="{{ data_get($siteContent, 'hero_subheading') }}" style="width:100%; padding:9px; border-radius:8px; border:1px solid #ccc;">
                                                </div>
                                                <div>
                                                    <label style="font-weight:600; font-size:0.82rem; color:#555;">Main Headline</label>
                                                    <input type="text" name="hero_headline" value="{{ data_get($siteContent, 'hero_headline') }}" style="width:100%; padding:9px; border-radius:8px; border:1px solid #ccc;">
                                                </div>
                                                <div>
                                                    <label style="font-weight:600; font-size:0.82rem; color:#555;">Primary Button Text</label>
                                                    <input type="text" name="hero_cta_primary" value="{{ data_get($siteContent, 'hero_cta_primary') }}" style="width:100%; padding:9px; border-radius:8px; border:1px solid #ccc;">
                                                </div>
                                                <div>
                                                    <label style="font-weight:600; font-size:0.82rem; color:#555;">Secondary Button Text</label>
                                                    <input type="text" name="hero_cta_secondary" value="{{ data_get($siteContent, 'hero_cta_secondary') }}" style="width:100%; padding:9px; border-radius:8px; border:1px solid #ccc;">
                                                </div>
                                            </div>

                                        @elseif($secId === 'highlights')
                                            <h6 style="color:#6d28d9; margin-bottom:10px; font-weight:700;">🛡️ Edit Trust Highlights Bar Text &amp; Icons</h6>
                                            @php $hlList = data_get($siteContent, 'highlights', []); @endphp
                                            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:12px;">
                                                @for($h = 0; $h < 4; $h++)
                                                    <div style="background:#FAF8FF; padding:14px; border-radius:10px; border:1px solid #e9d5ff; display:flex; flex-direction:column; gap:8px;">
                                                        <label style="font-weight:700; font-size:0.85rem; color:#6d28d9;">Highlight Badge {{ $h+1 }}</label>
                                                        
                                                        <div>
                                                            <label style="font-size:0.78rem; color:#666; display:block; margin-bottom:3px; font-weight:600;">Icon</label>
                                                            <div style="display:flex; gap:8px; align-items:center;">
                                                                <input type="text" id="hl-icon-input-{{ $h }}" name="highlights[{{ $h }}][icon]" value="{{ $hlList[$h]['icon'] ?? '🎂' }}" style="width:50px; text-align:center; padding:6px; border-radius:6px; border:1px solid #ccc; font-size:1.1rem;">
                                                                <button type="button" class="btn btn-sm btn-outline" onclick="openIconPicker(document.getElementById('hl-icon-input-{{ $h }}'))" style="padding:5px 10px; font-size:0.8rem; border-color:#8b5cf6; color:#6d28d9;">🎨 Select Icon</button>
                                                            </div>
                                                        </div>

                                                        <div>
                                                            <label style="font-size:0.78rem; color:#666; display:block; margin-bottom:3px; font-weight:600;">Title</label>
                                                            <input type="text" name="highlights[{{ $h }}][title]" value="{{ $hlList[$h]['title'] ?? '' }}" placeholder="Badge Title (e.g. Easy Catering)" style="width:100%; padding:8px 10px; border-radius:6px; border:1px solid #ccc; font-weight:600; font-size:0.88rem; background:white;">
                                                        </div>

                                                        <div>
                                                            <label style="font-size:0.78rem; color:#666; display:block; margin-bottom:3px; font-weight:600;">Description</label>
                                                            <input type="text" name="highlights[{{ $h }}][desc]" value="{{ $hlList[$h]['desc'] ?? '' }}" placeholder="Badge Subtext..." style="width:100%; padding:8px 10px; border-radius:6px; border:1px solid #ccc; font-size:0.85rem; background:white;">
                                                        </div>
                                                    </div>
                                                @endfor
                                            </div>

                                        @elseif($secId === 'whimsical')
                                            <h6 style="color:#6d28d9; margin-bottom:10px; font-weight:700;">Edit Whimsical Creations Title &amp; Bullets</h6>
                                            <div style="margin-bottom:10px;">
                                                <label style="font-weight:600; font-size:0.82rem; color:#555;">Section Title</label>
                                                <input type="text" name="whimsical_title" value="{{ data_get($siteContent, 'whimsical_title') }}" style="width:100%; padding:9px; border-radius:8px; border:1px solid #ccc;">
                                            </div>
                                            <div style="display:flex; flex-direction:column; gap:6px;">
                                                <label style="font-weight:600; font-size:0.82rem; color:#555;">Specialty Bullets</label>
                                                <input type="text" name="whimsical_bullet_1" value="{{ $bullets[0] ?? '' }}" placeholder="Bullet 1..." style="width:100%; padding:8px; border-radius:8px; border:1px solid #ccc;">
                                                <input type="text" name="whimsical_bullet_2" value="{{ $bullets[1] ?? '' }}" placeholder="Bullet 2..." style="width:100%; padding:8px; border-radius:8px; border:1px solid #ccc;">
                                                <input type="text" name="whimsical_bullet_3" value="{{ $bullets[2] ?? '' }}" placeholder="Bullet 3..." style="width:100%; padding:8px; border-radius:8px; border:1px solid #ccc;">
                                                <input type="text" name="whimsical_bullet_4" value="{{ $bullets[3] ?? '' }}" placeholder="Bullet 4..." style="width:100%; padding:8px; border-radius:8px; border:1px solid #ccc;">
                                                <input type="text" name="whimsical_bullet_5" value="{{ $bullets[4] ?? '' }}" placeholder="Bullet 5..." style="width:100%; padding:8px; border-radius:8px; border:1px solid #ccc;">
                                            </div>

                                        @elseif($secId === 'promo_video')
                                            <h6 style="color:#6d28d9; margin-bottom:10px; font-weight:700;">🎥 Edit Video/Image Banner Background &amp; Text</h6>
                                            <div style="display:flex; flex-direction:column; gap:10px;">
                                                <div style="background:#FAF8FF; padding:12px; border-radius:10px; border:1px solid #e9d5ff;">
                                                    <label style="font-weight:600; font-size:0.82rem; color:#555;">Video / Image Background Media</label>
                                                    <div style="display:flex; gap:10px; align-items:center; margin-top:4px;">
                                                        <input type="text" id="promo_video_url" name="promo_video_url" value="{{ data_get($siteContent, 'promo_video_url', 'images/download (2) (1).mp4') }}" placeholder="images/download (2) (1).mp4" style="flex:1; padding:8px; border-radius:8px; border:1px solid #ccc; font-family:monospace; font-size:0.85rem;">
                                                        <label class="btn btn-sm btn-outline" style="cursor:pointer; padding:6px 12px; border-color:#8b5cf6; color:#6d28d9; font-size:0.8rem; display:inline-flex; align-items:center; gap:4px;">
                                                            📁 Upload File
                                                            <input type="file" accept="image/*,video/*" onchange="uploadSectionMedia(this, 'promo_video_url')" style="display:none;">
                                                        </label>
                                                    </div>
                                                </div>
                                                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:10px;">
                                                    <div>
                                                        <label style="font-weight:600; font-size:0.82rem; color:#555;">Banner Headline</label>
                                                        <input type="text" name="promo_headline" value="{{ data_get($siteContent, 'promo_headline', '$10 Off Your First Order!') }}" style="width:100%; padding:9px; border-radius:8px; border:1px solid #ccc;">
                                                    </div>
                                                    <div>
                                                        <label style="font-weight:600; font-size:0.82rem; color:#555;">Subtext</label>
                                                        <input type="text" name="promo_subtext" value="{{ data_get($siteContent, 'promo_subtext', 'Follow us on social media or join our community for instant discounts.') }}" style="width:100%; padding:9px; border-radius:8px; border:1px solid #ccc;">
                                                    </div>
                                                </div>
                                            </div>

                                        @elseif($secId === 'how_it_works')
                                            <h6 style="color:#6d28d9; margin-bottom:10px; font-weight:700;">📝 Edit 3-Step Custom Ordering Guide Copy</h6>
                                            @php $hwList = data_get($siteContent, 'how_it_works', []); @endphp
                                            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:12px;">
                                                @for($s = 0; $s < 3; $s++)
                                                    <div style="background:#FAF8FF; padding:12px; border-radius:10px; border:1px solid #e9d5ff;">
                                                        <label style="font-weight:700; font-size:0.8rem; color:#6d28d9;">Step {{ $s+1 }}</label>
                                                        <input type="text" name="how_it_works[{{ $s }}][title]" value="{{ $hwList[$s]['title'] ?? '' }}" placeholder="Step Title..." style="width:100%; margin-top:6px; padding:6px 10px; border-radius:6px; border:1px solid #ccc; font-weight:600; font-size:0.85rem;">
                                                        <textarea name="how_it_works[{{ $s }}][desc]" rows="2" placeholder="Step Description..." style="width:100%; margin-top:6px; padding:6px 10px; border-radius:6px; border:1px solid #ccc; font-size:0.82rem; font-family:inherit;">{{ $hwList[$s]['desc'] ?? '' }}</textarea>
                                                    </div>
                                                @endfor
                                            </div>

                                        @elseif($secId === 'reviews')
                                            <h6 style="color:#6d28d9; margin-bottom:10px; font-weight:700;">⭐ Customer Reviews &amp; Social Proof (Add / Edit / Delete)</h6>
                                            @php $revList = data_get($siteContent, 'reviews', []); @endphp
                                            <div id="accordion-reviews-list" style="display:flex; flex-direction:column; gap:10px;">
                                                @foreach($revList as $rIdx => $rev)
                                                    <div class="accordion-review-item" style="background:#FAF8FF; padding:12px; border-radius:10px; border:1px solid #e9d5ff; display:flex; flex-direction:column; gap:8px;">
                                                        <div style="display:flex; justify-content:space-between; align-items:center;">
                                                            <input type="text" name="reviews[{{ $rIdx }}][name]" value="{{ $rev['name'] ?? '' }}" placeholder="Customer Name (e.g. Kristen Ramirez)" style="width:240px; padding:6px 10px; border-radius:6px; border:1px solid #ccc; font-weight:700;">
                                                            <button type="button" class="btn btn-sm btn-outline" onclick="this.closest('.accordion-review-item').remove()" style="color:#dc2626; border-color:#fca5a5; padding:2px 8px; font-size:0.78rem;">🗑️ Delete</button>
                                                        </div>
                                                        <textarea name="reviews[{{ $rIdx }}][quote]" rows="2" placeholder="Customer Quote / Testimonial..." style="width:100%; padding:6px 10px; border-radius:6px; border:1px solid #ccc; font-size:0.85rem; font-family:inherit;">{{ $rev['quote'] ?? '' }}</textarea>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline" onclick="addAccordionReviewItem()" style="margin-top:10px; border-color:#8b5cf6; color:#6d28d9;">+ Add New Customer Review</button>

                                        @elseif($secId === 'faq')
                                            <h6 style="color:#6d28d9; margin-bottom:10px; font-weight:700;">❓ FAQ Questions &amp; Bakery Policies (Add / Edit / Delete)</h6>
                                            @php $faqList = data_get($siteContent, 'faqs', []); @endphp
                                            <div id="accordion-faqs-list" style="display:flex; flex-direction:column; gap:10px;">
                                                @foreach($faqList as $fIdx => $faq)
                                                    <div class="accordion-faq-item" style="background:#FAF8FF; padding:12px; border-radius:10px; border:1px solid #e9d5ff; display:flex; flex-direction:column; gap:8px;">
                                                        <div style="display:flex; justify-content:space-between; align-items:center; gap:10px;">
                                                            <input type="text" name="faqs[{{ $fIdx }}][q]" value="{{ $faq['q'] ?? '' }}" placeholder="Question (e.g. 📅 How far in advance should I order?)" style="flex:1; padding:6px 10px; border-radius:6px; border:1px solid #ccc; font-weight:700;">
                                                            <button type="button" class="btn btn-sm btn-outline" onclick="this.closest('.accordion-faq-item').remove()" style="color:#dc2626; border-color:#fca5a5; padding:2px 8px; font-size:0.78rem;">🗑️ Delete</button>
                                                        </div>
                                                        <textarea name="faqs[{{ $fIdx }}][a]" rows="2" placeholder="Answer / Bakery Policy..." style="width:100%; padding:6px 10px; border-radius:6px; border:1px solid #ccc; font-size:0.85rem; font-family:inherit;">{{ $faq['a'] ?? '' }}</textarea>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline" onclick="addAccordionFaqItem()" style="margin-top:10px; border-color:#8b5cf6; color:#6d28d9;">+ Add New FAQ Question</button>

                                        @elseif($secId === 'cta_banner')
                                            <h6 style="color:#6d28d9; margin-bottom:10px; font-weight:700;">🎬 Edit Footer Booking CTA Banner Text &amp; Background</h6>
                                            <div style="display:flex; flex-direction:column; gap:10px;">
                                                <div style="background:#FAF8FF; padding:12px; border-radius:10px; border:1px solid #e9d5ff;">
                                                    <label style="font-weight:600; font-size:0.82rem; color:#555;">Video / Image Background Media</label>
                                                    <div style="display:flex; gap:10px; align-items:center; margin-top:4px;">
                                                        <input type="text" id="cta_banner_url" name="cta_banner_url" value="{{ data_get($siteContent, 'cta_banner_url', 'images/34d48b27-1dd9-4784-8c8d-b378c3388060.mp4') }}" placeholder="images/34d48b27-1dd9-4784-8c8d-b378c3388060.mp4" style="flex:1; padding:8px; border-radius:8px; border:1px solid #ccc; font-family:monospace; font-size:0.85rem;">
                                                        <label class="btn btn-sm btn-outline" style="cursor:pointer; padding:6px 12px; border-color:#8b5cf6; color:#6d28d9; font-size:0.8rem; display:inline-flex; align-items:center; gap:4px;">
                                                            📁 Upload File
                                                            <input type="file" accept="image/*,video/*" onchange="uploadSectionMedia(this, 'cta_banner_url')" style="display:none;">
                                                        </label>
                                                    </div>
                                                </div>
                                                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:10px;">
                                                    <div>
                                                        <label style="font-weight:600; font-size:0.82rem; color:#555;">CTA Headline</label>
                                                        <input type="text" name="cta_headline" value="{{ data_get($siteContent, 'cta_headline', 'Ready For Your Perfect Cake?') }}" style="width:100%; padding:9px; border-radius:8px; border:1px solid #ccc;">
                                                    </div>
                                                    <div>
                                                        <label style="font-weight:600; font-size:0.82rem; color:#555;">Subtitle</label>
                                                        <input type="text" name="cta_subtext" value="{{ data_get($siteContent, 'cta_subtext', 'Order your plan or custom order now') }}" style="width:100%; padding:9px; border-radius:8px; border:1px solid #ccc;">
                                                    </div>
                                                    <div>
                                                        <label style="font-weight:600; font-size:0.82rem; color:#555;">Button Text</label>
                                                        <input type="text" name="cta_btn_text" value="{{ data_get($siteContent, 'cta_btn_text', 'Order Now') }}" style="width:100%; padding:9px; border-radius:8px; border:1px solid #ccc;">
                                                    </div>
                                                </div>
                                            </div>

                                        @else
                                            <p style="font-size:0.85rem; color:#666; margin:0;">Standard section enabled. Click Save to apply section order &amp; visibility state.</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </form>
                </div>
            </div>

            <!-- TAB: Calendar & Availability Manager -->
            <div id="tab-calendar" class="tab-content">
                <div class="section-header">
                    <h3>📆 Calendar &amp; Availability Manager</h3>
                    <p class="subtitle">Set recurring weekly closed days, block off specific dates for holidays or vacations, and manage order availability live!</p>
                </div>

                <!-- CARD 1: RECURRING WEEKLY CLOSED DAYS -->
                <div class="form-builder-card" style="border:2px solid #e67399; background:#fff7fa;">
                    <h4 style="color:#5c1d37;">🔄 Weekly Recurring Closed Days</h4>
                    <p style="font-size:0.88rem; color:#666; margin-bottom:16px;">Select days of the week when your bakery is regularly closed (e.g. Saturdays &amp; Sundays). These will automatically be blocked on the order form calendar.</p>
                    
                    <div style="display:flex; flex-wrap:wrap; gap:12px; margin-bottom:18px;">
                        @foreach([
                            ['0', 'Sunday'],
                            ['1', 'Monday'],
                            ['2', 'Tuesday'],
                            ['3', 'Wednesday'],
                            ['4', 'Thursday'],
                            ['5', 'Friday'],
                            ['6', 'Saturday']
                        ] as [$dayVal, $dayName])
                        <label style="display:flex; align-items:center; gap:8px; background:white; padding:10px 16px; border-radius:12px; border:1px solid #f0e4ea; font-weight:600; cursor:pointer; user-select:none;">
                            <input type="checkbox" class="recurring-closed-checkbox" value="{{ $dayVal }}">
                            <span>{{ $dayName }}</span>
                        </label>
                        @endforeach
                    </div>

                    <div style="display:flex; gap:12px; align-items:center;">
                        <button class="btn btn-primary" onclick="saveRecurringClosedDays()">💾 Save Recurring Schedule</button>
                        <span id="recurring-save-msg" style="font-size:0.85rem; color:#28a745; display:none;">✅ Saved!</span>
                    </div>
                </div>

                <!-- CARD 2: INTERACTIVE CALENDAR DATE BLACKOUT MANAGER -->
                <div class="form-builder-card">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px; margin-bottom:20px;">
                        <div>
                            <h4 style="margin-bottom:4px;">🚫 Interactive Date Blackout Calendar</h4>
                            <p style="font-size:0.85rem; color:#888; margin:0;">Click any date below to toggle it blocked 🔴 or available 🟢</p>
                        </div>
                        <!-- Month Navigation -->
                        <div style="display:flex; align-items:center; gap:10px; background:#fff0f5; padding:6px 14px; border-radius:14px; border:1px solid #f8c6d7;">
                            <button class="btn btn-sm btn-outline" style="padding:4px 10px;" onclick="changeAdminCalMonth(-1)">◀ Prev</button>
                            <span id="admin-cal-month-year" style="font-weight:800; color:#5c1d37; min-width:130px; text-align:center;">July 2026</span>
                            <button class="btn btn-sm btn-outline" style="padding:4px 10px;" onclick="changeAdminCalMonth(1)">Next ▶</button>
                        </div>
                    </div>

                    <!-- Legend -->
                    <div style="display:flex; gap:16px; flex-wrap:wrap; font-size:0.82rem; margin-bottom:18px; padding:10px 14px; background:#fafafa; border-radius:10px; border:1px solid #eee;">
                        <span style="display:flex; align-items:center; gap:6px;"><span style="width:12px; height:12px; border-radius:50%; background:#28a745; display:inline-block;"></span> Available</span>
                        <span style="display:flex; align-items:center; gap:6px;"><span style="width:12px; height:12px; border-radius:50%; background:#d9534f; display:inline-block;"></span> Custom Blocked Date</span>
                        <span style="display:flex; align-items:center; gap:6px;"><span style="width:12px; height:12px; border-radius:50%; background:#6f42c1; display:inline-block;"></span> Weekly Closed Day</span>
                    </div>

                    <!-- Interactive Admin Calendar Grid -->
                    <div id="admin-calendar-grid" class="admin-cal-grid">
                        <!-- Rendered by JS -->
                    </div>

                    <!-- Manual Date Picker Quick Add -->
                    <div style="margin-top:24px; padding-top:20px; border-top:1px solid #f0e4ea; display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end;">
                        <div>
                            <label style="font-size:0.85rem; font-weight:700; color:#5c1d37; display:block; margin-bottom:6px;">Block Specific Date Manually</label>
                            <input type="date" id="manual-block-date" style="padding:10px 14px; border-radius:10px; border:1px solid #f0e4ea;">
                        </div>
                        <button class="btn btn-primary" onclick="addManualBlockedDate()">🚫 Block Date</button>
                    </div>
                </div>

                <!-- CARD 3: LIST OF CURRENTLY BLOCKED DATES -->
                <div class="form-builder-card">
                    <h4>📋 Currently Blocked Custom Dates</h4>
                    <p style="font-size:0.85rem; color:#666; margin-bottom:14px;">These specific dates are currently blacked out for client orders:</p>
                    <div id="admin-blocked-dates-list" style="display:flex; flex-wrap:wrap; gap:10px;">
                        <!-- Rendered by JS -->
                    </div>
                </div>
            </div>

        </main>

    </div>
</div>
@endsection
