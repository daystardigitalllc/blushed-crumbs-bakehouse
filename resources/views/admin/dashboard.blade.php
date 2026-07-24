@extends('layouts.app')

@section('title', 'Baker Admin Portal | ' . ($tenant->name ?? 'Doughmain.pro'))

@section('content')
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
<!-- Quill.js WYSIWYG Editor -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<!-- PAGE 4: BAKER ADMIN PORTAL VIEW WITH MODERN SIDEBAR -->
<div id="admin-portal-view">
    <!-- MOBILE TOP BAR WITH HAMBURGER BUTTON -->
    <div class="admin-mobile-header">
        <div class="mobile-brand" style="display:flex; align-items:center; gap:8px;">
            <img src="{{ $tenant->logo_path ? asset($tenant->logo_path) : asset('images/doughmain_logo.png') }}" alt="{{ $tenant->name ?? 'Bakery Logo' }}" style="height:34px; width:auto; border-radius:4px;">
            <strong style="font-size:1.15rem; color:#ffffff;">{{ $tenant->name ?? 'Doughmain.pro' }}</strong>
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
                <img src="{{ $tenant->logo_path ? asset($tenant->logo_path) : asset('images/doughmain_logo.png') }}" alt="{{ $tenant->name ?? 'Bakery Logo' }}" style="height:44px; width:auto; object-fit:contain; border-radius:4px;">
                <div>
                    <h3 style="font-size:1.05rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:140px;">{{ $tenant->name }}</h3>
                    <span class="badge-pro">Baker CMS</span>
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
                <button class="admin-nav-item" data-tab="tab-calendar">
                    <span>📆</span> Availability & Blackouts
                </button>
                <button class="admin-nav-item" data-tab="tab-settings">
                    <span>🔧</span> Settings
                </button>
                <button class="admin-nav-item" data-tab="tab-subscription-support">
                    <span>💳</span> Subscription &amp; Support
                </button>
                @if(($tenant->plan_tier ?? 'free') !== 'pro')
                    <a href="https://buy.stripe.com/eVq00jeoj4aB62QanW2Ry0k?client_reference_id={{ $tenant->id }}&prefilled_email={{ urlencode($tenant->email ?? '') }}" target="_blank" class="admin-nav-item" style="background:linear-gradient(135deg, #6d28d9, #8b5cf6); color:#ffffff !important; font-weight:700; margin-top:12px; border-radius:12px; text-align:center; box-shadow:0 4px 12px rgba(109,40,217,0.3); text-decoration:none; display:block;">
                        ⚡ Upgrade to PRO ($29/mo)
                    </a>
                @endif
            </nav>

            <div class="admin-sidebar-footer">
                <a href="/" target="_blank" class="btn btn-outline" style="display:block; text-align:center; width:100%; border-color:rgba(255,255,255,0.3); color:white; text-decoration:none; margin-bottom:10px;">← Exit to Storefront</a>
                <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn btn-outline" style="display:block; text-align:center; width:100%; border-color:rgba(255,255,255,0.3); color:white;">🚪 Sign Out</button>
                </form>
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
                                <select class="status-select status-{{ $order->status }}" onchange="updateOrderStatus({{ $order->id }}, this.value)">
                                    <option value="new" {{ $order->status == 'new' ? 'selected' : '' }}>NEW</option>
                                    <option value="invoiced" {{ $order->status == 'invoiced' ? 'selected' : '' }}>INVOICED</option>
                                    <option value="paid" {{ $order->status == 'paid' ? 'selected' : '' }}>PAID</option>
                                    <option value="in_progress" {{ $order->status == 'in_progress' ? 'selected' : '' }}>IN PROGRESS</option>
                                    <option value="ready" {{ $order->status == 'ready' ? 'selected' : '' }}>READY</option>
                                    <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>COMPLETED</option>
                                    <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>CANCELLED</option>
                                </select>
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
                                <button class="btn btn-sm btn-primary" onclick="generateInvoiceFromOrder({{ $order->id }}, {{ $order->total_price }}, {{ $order->deposit_amount }})">💳 Create Invoice</button>
                                <button class="btn btn-sm btn-outline" onclick="copyClientPayLink('{{ $order->invoice ? $order->invoice->invoice_number : '' }}', {{ $order->id }})">📋 Copy Invoice Link</button>
                            </div>
                        </div>
                    @empty
                        <div style="background:#ffffff; border:2px dashed #e2e8f0; border-radius:16px; padding:48px; text-align:center; color:#64748b; grid-column: 1 / -1;">
                            <span style="font-size:3rem; display:block; margin-bottom:12px;">🧁</span>
                            <h4 style="font-size:1.2rem; font-weight:700; color:#1e293b; margin-bottom:6px;">No Customer Orders Yet</h4>
                            <p style="font-size:0.95rem; margin-bottom:18px;">When customers submit cake inquiries or place orders on your storefront, they will appear here in order of due date!</p>
                            <a href="{{ url('/') }}" target="_blank" class="btn btn-primary" style="display:inline-block; padding:10px 20px; font-size:0.9rem; text-decoration:none;">View Your Storefront →</a>
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
                        <div style="grid-column: 1 / -1;">
                            <label>Step Subtext / Directions / Policy Text</label>
                            <textarea id="field-description" placeholder="e.g. Select all options that apply, or enter custom Terms & Conditions text here..." style="width:100%; height:80px; padding:9px; border-radius:8px; border:1px solid #ccc; font-family:inherit;"></textarea>
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
                            <select id="new-prod-category" onchange="if(this.value === 'custom_new'){ document.getElementById('new-prod-category-custom').style.display='block'; document.getElementById('new-prod-category-custom').setAttribute('required', 'true'); } else { document.getElementById('new-prod-category-custom').style.display='none'; document.getElementById('new-prod-category-custom').removeAttribute('required'); }">
                                <option value="Single Tier">Single Tier</option>
                                <option value="Multi-Tier">Multi-Tier</option>
                                <option value="By The Dozen">By The Dozen</option>
                                <option value="Treats">Treats</option>
                                <option value="Party Packs">Party Packs</option>
                                <option value="custom_new">+ Add Custom Category...</option>
                            </select>
                            <input type="text" id="new-prod-category-custom" placeholder="Type new category name..." style="display:none; margin-top:8px;">
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

                <!-- MENU STUDIO & UPLOAD CARD -->
                <div class="form-builder-card" style="border:2px solid #6d28d9; background:linear-gradient(135deg, #ffffff, #fdf4ff); margin-top:25px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:16px;">
                        <div>
                            <h4 style="color:#6d28d9; font-size:1.25rem; margin-bottom:4px;">📄 Menu Page Studio &amp; Uploads</h4>
                            <p style="font-size:0.88rem; color:#555;">Upload your official bakery menu image/PDF or enter custom menu items &amp; disclaimers. Displays on your public <code>/menu</code> page.</p>
                        </div>
                        <span style="background:#6d28d9; color:white; font-size:0.75rem; font-weight:800; padding:4px 12px; border-radius:12px; text-transform:uppercase;">Public Storefront Menu</span>
                    </div>

                    @php
                        $menuData = $tenant->site_content['menu'] ?? [];
                        $menuType = $menuData['menu_type'] ?? 'both';
                        $menuImagePath = $menuData['menu_image_path'] ?? null;
                        $menuText = $menuData['menu_text'] ?? '';
                    @endphp

                    <form onsubmit="handleSaveMenuSettings(event)" enctype="multipart/form-data">
                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:20px; margin-bottom:20px;">
                            <!-- Display Mode -->
                            <div>
                                <label style="font-weight:700; color:#334155; font-size:0.9rem; display:block; margin-bottom:6px;">Menu Display Mode</label>
                                <select name="menu_type" id="admin_menu_type" class="form-input" style="width:100%; padding:10px; border-radius:8px; border:1px solid #cbd5e1;">
                                    <option value="both" {{ $menuType === 'both' ? 'selected' : '' }}>Both (Uploaded Image Card + Styled Theme Menu Grid)</option>
                                    <option value="text" {{ $menuType === 'text' ? 'selected' : '' }}>Styled Theme Menu Grid Only</option>
                                    <option value="image" {{ $menuType === 'image' ? 'selected' : '' }}>Uploaded Menu Image / PDF Only</option>
                                </select>
                            </div>

                            <!-- Upload Menu File -->
                            <div>
                                <label style="font-weight:700; color:#334155; font-size:0.9rem; display:block; margin-bottom:6px;">Upload Official Menu Image (JPG, PNG, WEBP, PDF)</label>
                                <input type="file" name="menu_image" id="admin_menu_image" accept="image/*,.pdf" class="form-input" style="width:100%; padding:8px; border-radius:8px; border:1px solid #cbd5e1; background:#fff;">
                                @if($menuImagePath)
                                    <div style="margin-top:8px; font-size:0.85rem; color:#059669; font-weight:600; display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                                        <span>✓ Current Upload: <a href="{{ asset($menuImagePath) }}" target="_blank" style="text-decoration:underline;">View Uploaded Menu ↗</a></span>
                                        <label style="color:#dc2626; font-weight:600; font-size:0.8rem; cursor:pointer;">
                                            <input type="checkbox" name="remove_menu_image" value="1"> 🗑️ Delete Image
                                        </label>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Menu WYSIWYG Rich Text Editor -->
                        <div style="margin-bottom:20px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                                <label style="font-weight:700; color:#334155; font-size:0.9rem;">
                                    ✨ Custom Menu &amp; Pricing Builder (WYSIWYG Rich Text Editor — Bold, Bullets, Headings)
                                </label>
                                <button type="button" class="btn btn-sm btn-outline" style="font-size:0.75rem; padding:2px 10px; color:#dc2626; border-color:#fca5a5;" onclick="clearMenuQuillEditor()">
                                    🗑️ Clear Editor
                                </button>
                            </div>
                            <input type="hidden" name="menu_text" id="admin_menu_text" value="{{ $menuText }}">
                            <div id="quill-menu-editor-container" style="background:#ffffff; min-height:240px; border-radius:0 0 10px 10px; font-size:0.95rem;">{!! $menuText !!}</div>
                        </div>

                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <a href="{{ route('storefront.menu') }}" target="_blank" style="color:#6d28d9; font-size:0.9rem; font-weight:600; text-decoration:none;">
                                👀 Preview Public Menu Page ↗
                            </a>
                            <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg, #6d28d9, #8b5cf6); border:none; padding:10px 24px; font-weight:700; border-radius:8px;">
                                💾 Save Menu &amp; Pricing Settings
                            </button>
                        </div>
                    </form>
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

                <!-- RECENT INVOICES TRACKER -->
                <div class="form-builder-card" style="margin-bottom:20px;">
                    <h4>📋 Recent Invoices</h4>
                    <table style="width:100%; border-collapse:collapse; text-align:left; margin-top:10px;">
                        <thead>
                            <tr style="border-bottom:2px solid #f0e4ea;">
                                <th style="padding:12px 8px;">Invoice #</th>
                                <th style="padding:12px 8px;">Client</th>
                                <th style="padding:12px 8px;">Amount</th>
                                <th style="padding:12px 8px;">Status</th>
                                <th style="padding:12px 8px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $inv)
                                <tr style="border-bottom:1px solid #f0e4ea;">
                                    <td style="padding:12px 8px; font-family:monospace;">{{ $inv->invoice_number }}</td>
                                    <td style="padding:12px 8px;">{{ $inv->client_name }}</td>
                                    <td style="padding:12px 8px; font-weight:700;">${{ number_format($inv->total_amount, 2) }}</td>
                                    <td style="padding:12px 8px;">
                                        <select class="status-select status-{{ $inv->status }}" onchange="updateInvoiceStatus({{ $inv->id }}, this.value)">
                                            <option value="unpaid" {{ $inv->status == 'unpaid' ? 'selected' : '' }}>UNPAID</option>
                                            <option value="deposit_paid" {{ $inv->status == 'deposit_paid' ? 'selected' : '' }}>DEPOSIT PAID</option>
                                            <option value="paid_in_full" {{ $inv->status == 'paid_in_full' ? 'selected' : '' }}>PAID IN FULL</option>
                                            <option value="cancelled" {{ $inv->status == 'cancelled' ? 'selected' : '' }}>CANCELLED</option>
                                        </select>
                                    </td>
                                    <td style="padding:12px 8px;">
                                        <button class="btn btn-sm btn-outline" onclick="copyClientPayLink('{{ $inv->invoice_number }}')">📋 Copy Link</button>
                                        <button class="btn btn-sm btn-outline" onclick="openInvoiceEditModal({{ $inv->id }}, {{ $inv->total_amount }}, {{ $inv->deposit_amount ?? 0 }}, '{{ addslashes($inv->notes ?? '') }}', {{ $inv->order_id ?? 'null' }})">✏️ Edit</button>
                                        <button class="btn btn-sm btn-primary" onclick="sendInvoice('{{ $inv->id }}')">📧 Send</button>
                                        <button class="btn btn-sm btn-outline" style="color:#d9534f; border-color:#d9534f;" onclick="deleteInvoice({{ $inv->id }}, this)">🗑️ Delete</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align:center; padding:20px; color:#888;">No invoices created yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
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
                    <p class="subtitle">Manage client reviews and testimonials displayed on your storefront!</p>
                </div>

                <!-- ADD NEW REVIEW CARD -->
                <div class="form-builder-card" style="margin-bottom:20px; border:2px solid #e67399; background:#fff7fa;">
                    <h4 style="color:#5c1d37; margin-bottom:12px;">➕ Add New Client Review</h4>
                    <form id="add-review-form" style="display:flex; flex-direction:column; gap:12px;">
                        <div>
                            <label style="font-weight:700; font-size:0.85rem; color:#5c1d37; display:block; margin-bottom:4px;">Client Name</label>
                            <input type="text" id="rev-client-name" class="form-control" placeholder="e.g. Lynne Escue" required style="width:100%; padding:10px 14px; border-radius:10px; border:1px solid #ddd;">
                        </div>
                        <div>
                            <label style="font-weight:700; font-size:0.85rem; color:#5c1d37; display:block; margin-bottom:4px;">Review Text</label>
                            <textarea id="rev-text" class="form-control" placeholder="Paste client review text here..." required style="width:100%; height:90px; padding:10px 14px; border-radius:10px; border:1px solid #ddd; font-family:inherit;"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="align-self:flex-start;">Publish Review to Storefront</button>
                    </form>
                </div>

                <!-- PUBLISHED REVIEWS LIST -->
                <div class="form-builder-card">
                    <h4>📋 Published Reviews</h4>
                    <p style="font-size:0.85rem; color:#666; margin-bottom:14px;">The following reviews are currently live on your bakery storefront:</p>

                    <div id="admin-reviews-list" style="display:flex; flex-direction:column; gap:12px;">
                        @forelse($reviews as $rev)
                            <div class="review-item-row" data-id="{{ $rev->id }}" style="background:white; padding:16px; border-radius:12px; border:1px solid #f0e4ea; box-shadow:0 4px 12px rgba(0,0,0,0.03); display:flex; justify-content:space-between; align-items:flex-start; gap:15px;">
                                <div>
                                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
                                        <strong style="color:#5c1d37; font-size:1rem;">{{ $rev->client_name }}</strong>
                                        <span style="color:#ffc107; font-size:0.9rem;">★★★★★</span>
                                    </div>
                                    <p style="font-size:0.9rem; color:#555; margin:0; line-height:1.5;">"{{ $rev->review_text }}"</p>
                                </div>
                                <button class="btn btn-sm btn-outline" style="color:#d9534f; border-color:#d9534f; flex-shrink:0;" onclick="deleteReview({{ $rev->id }}, this)">🗑️ Delete</button>
                            </div>
                        @empty
                            <p style="color:#888; text-align:center; padding:20px;">No reviews added yet. Use the form above to publish client reviews!</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- TAB: Settings -->
            <div id="tab-settings" class="tab-content">
                <div class="section-header">
                    <h3>🎨 Bakery Theme &amp; Storefront Options</h3>
                    <p class="subtitle">Choose a professionally curated bakery theme, upload your brand logo, and configure settings.</p>
                </div>

                <!-- BAKERY LOGO MANAGEMENT CARD -->
                <div class="form-builder-card" style="border:2px solid #6d28d9; background:#FAF8FF; margin-bottom:20px;">
                    <h4 style="color:#4c1d95; margin-bottom:6px;">🖼️ Bakery Brand Logo</h4>
                    <p style="font-size:0.88rem; color:#666; margin-bottom:16px;">Upload your official bakery logo. This will be displayed in the header &amp; footer across all your storefront pages.</p>
                    
                    <div style="display:flex; align-items:center; gap:20px; flex-wrap:wrap;">
                        <div style="width:90px; height:90px; border-radius:16px; background:#ffffff; border:2px dashed #c4b5fd; display:flex; align-items:center; justify-content:center; overflow:hidden; padding:6px;">
                            <img id="bakery-logo-preview" src="{{ $tenant->logo_path ? asset($tenant->logo_path) : asset('images/doughmain_logo.png') }}" alt="Bakery Logo" style="max-width:100%; max-height:100%; object-fit:contain;">
                        </div>
                        <div style="flex:1; min-width:240px;">
                            <form id="bakery-logo-form" onsubmit="uploadBakeryLogo(event)" style="display:flex; flex-direction:column; gap:10px;">
                                <input type="file" id="bakery-logo-file" name="logo" accept="image/*" required onchange="previewBakeryLogoFile(this)" style="font-size:0.88rem;">
                                <div style="display:flex; gap:10px; align-items:center;">
                                    <button type="submit" class="btn btn-primary" style="background:#6d28d9; border-color:#6d28d9;">
                                        💾 Save Bakery Logo
                                    </button>
                                    <span id="logo-upload-status" style="font-size:0.85rem; font-weight:600; color:#059669; display:none;">✓ Logo updated!</span>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- CURATED BAKERY THEMES CARD -->
                <div class="form-builder-card" style="border:2px solid #e67399; background:#fff7fa;">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:12px;">
                        <div>
                            <h4 style="color:#5c1d37; margin:0;">🌸 Select Your Bakery Theme</h4>
                            <p style="font-size:0.88rem; color:#666; margin-top:4px;">Pick a standardized, low-maintenance design template. Customizes colors and layout automatically.</p>
                        </div>
                        <a href="/site/{{ $tenant->subdomain }}" target="_blank" class="btn btn-outline btn-sm" style="font-weight:700; border-color:#e67399; color:#e67399;">👁️ View Live Storefront ↗</a>
                    </div>

                    <div id="theme-status-msg" style="display:none; margin-bottom:14px; background:#d4edda; color:#155724; padding:10px 14px; border-radius:10px; font-size:0.88rem; font-weight:600; border:1px solid #c3e6cb;"></div>

                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px;">
                        @php
                            $themes = $tenant->getAvailableThemesForTenant();
                            $currentTheme = $tenant->theme_id ?? 'sweet_elegant';
                        @endphp
                        @foreach($themes as $t)
                            @php
                                $isStarterTheme = in_array($t['id'], ['rustic_kitchen', 'modern_bakery', 'playful_treats']);
                                $isLockedTheme = ($tenant->plan_tier !== 'pro') && !$isStarterTheme;
                            @endphp
                            <div class="bakery-theme-card" 
                                 onclick="{{ $isLockedTheme ? "alert('Upgrade to Pro ($29/mo) to unlock this premium theme!')" : "selectBakeryTheme('".$t['id']."', this)" }}" 
                                 style="border:{{ $currentTheme === $t['id'] ? '3px solid #e67399' : '2px solid #ddd' }}; background:white; padding:16px; border-radius:14px; cursor:{{ $isLockedTheme ? 'not-allowed' : 'pointer' }}; position:relative; transition:transform 0.15s ease, border-color 0.15s ease; box-shadow:0 4px 12px rgba(0,0,0,0.05); {{ $isLockedTheme ? 'opacity:0.65; filter:grayscale(25%);' : '' }}">
                                <div style="height:80px; background:{{ $t['preview_bg'] }}; border-radius:10px; margin-bottom:12px; display:flex; align-items:center; justify-content:center; border:1px solid #eee;">
                                    <span style="font-weight:800; color:{{ $t['preview_accent'] }}; font-size:1.1rem;">{{ $t['name'] }}</span>
                                </div>
                                <h5 style="font-size:1rem; font-weight:700; color:#5c1d37; margin-bottom:4px;">{{ $t['name'] }}</h5>
                                <p style="font-size:0.8rem; color:#666; line-height:1.4;">{{ $t['subtitle'] }}</p>
                                @if($currentTheme === $t['id'])
                                    <span class="theme-badge" style="display:inline-block; margin-top:8px; font-size:0.75rem; background:#e67399; color:white; padding:3px 10px; border-radius:20px; font-weight:700;">Active Theme</span>
                                @elseif($isLockedTheme)
                                    <span style="display:inline-block; margin-top:8px; font-size:0.75rem; background:#fef3c7; color:#92400e; padding:3px 10px; border-radius:20px; font-weight:700;">🔒 PRO ONLY ($29/mo)</span>
                                @else
                                    <span style="display:inline-block; margin-top:8px; font-size:0.75rem; background:#d1fae5; color:#065f46; padding:3px 10px; border-radius:20px; font-weight:700;">Free Tier 🎁</span>
                                @endif
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
                                                        <input type="text" id="promo_video_url" name="promo_video_url" value="{{ data_get($siteContent, 'promo_video_url', '') }}" placeholder="Upload custom video or image URL..." style="flex:1; padding:8px; border-radius:8px; border:1px solid #ccc; font-family:monospace; font-size:0.85rem;">
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
                </div> <!-- CLOSE Storefront Sections Builder Card -->

                <!-- BAKER SUPPORT CARD -->
                <div class="form-builder-card" style="margin-top:20px; border:2px solid #6d28d9; background:#fbf8ff;">
                    <h4 style="color:#4c1d95; margin-bottom:4px;">💬 Baker Support & Custom Code Request</h4>
                    <p style="font-size:0.88rem; color:#666; margin-bottom:14px;">Direct support request form for custom features, theme tweaks, or code assistance ($50/mo Pro Tier perk).</p>
                    <form id="support-request-form" style="display:flex; flex-direction:column; gap:12px;">
                        <div>
                            <label style="font-weight:700; font-size:0.85rem; color:#4c1d95; display:block; margin-bottom:4px;">Subject</label>
                            <input type="text" class="form-control" placeholder="e.g. Custom theme tweak request" required style="width:100%; padding:10px 14px; border-radius:10px; border:1px solid #ddd;">
                        </div>
                        <div>
                            <label style="font-weight:700; font-size:0.85rem; color:#4c1d95; display:block; margin-bottom:4px;">Description</label>
                            <textarea class="form-control" placeholder="Describe custom code or support request..." required style="width:100%; height:100px; padding:10px 14px; border-radius:10px; border:1px solid #ddd; font-family:inherit;"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="background:#6d28d9; border-color:#6d28d9; align-self:flex-start;">Send Support Request</button>
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
                        @php
                            $isClosedChecked = in_array((int)$dayVal, $serverBookingSettings['recurring_closed_days'] ?? [0, 1]);
                        @endphp
                        <label style="display:flex; align-items:center; gap:8px; background:white; padding:10px 16px; border-radius:12px; border:1px solid #f0e4ea; font-weight:600; cursor:pointer; user-select:none;">
                            <input type="checkbox" class="recurring-closed-checkbox" value="{{ $dayVal }}" {{ $isClosedChecked ? 'checked' : '' }}>
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
                            <span id="admin-cal-month-year" style="font-weight:800; color:#5c1d37; min-width:130px; text-align:center;">{{ now()->format('F Y') }}</span>
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
                        @php
                            $calNow = \Carbon\Carbon::now();
                            $calYear = $calNow->year;
                            $calMonth = $calNow->month;
                            $calDaysInMonth = $calNow->daysInMonth;
                            $calFirstDayIndex = \Carbon\Carbon::create($calYear, $calMonth, 1)->dayOfWeek;
                            $calDayHeaders = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                            $calBlockedDates = $serverBookingSettings['blocked_dates'] ?? ['2026-07-04', '2026-07-25'];
                            $calRecurringClosed = $serverBookingSettings['recurring_closed_days'] ?? [0, 1];
                        @endphp

                        @foreach($calDayHeaders as $dh)
                            <div class="admin-cal-header-day">{{ $dh }}</div>
                        @endforeach

                        @for($i = 0; $i < $calFirstDayIndex; $i++)
                            <div class="admin-cal-day empty-slot"></div>
                        @endfor

                        @for($day = 1; $day <= $calDaysInMonth; $day++)
                            @php
                                $dateObj = \Carbon\Carbon::create($calYear, $calMonth, $day);
                                $dayOfWeek = $dateObj->dayOfWeek;
                                $dateStr = $dateObj->format('Y-m-d');
                                $isBlocked = in_array($dateStr, $calBlockedDates);
                                $isWeeklyClosed = in_array($dayOfWeek, $calRecurringClosed);
                                $statusClass = $isBlocked ? 'blocked' : ($isWeeklyClosed ? 'weekly-closed' : 'available');
                            @endphp
                            <div class="admin-cal-day {{ $statusClass }}" data-date="{{ $dateStr }}" onclick="toggleAdminCalDate('{{ $dateStr }}')">
                                {{ $day }}
                            </div>
                        @endfor
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
                        @forelse($serverBookingSettings['blocked_dates'] ?? ['2026-07-04', '2026-07-25'] as $bDate)
                            <div class="blocked-date-badge">
                                <span>🚫 {{ $bDate }}</span>
                                <button title="Unblock Date" onclick="removeBlockedDate('{{ $bDate }}')">✕</button>
                            </div>
                        @empty
                            <span style="color:#aaa; font-size:0.9rem;">No custom blocked dates added yet. Click any calendar date above or use the manual input!</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- TAB 9: Subscription & Support -->
            <div id="tab-subscription-support" class="tab-content">
                <div class="section-header">
                    <h3>💳 Subscription &amp; Platform Support</h3>
                    <p class="subtitle">Manage your bakery plan subscription and get direct support from Doughmain.pro.</p>
                </div>

                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:25px; margin-top:20px;">
                    <!-- CHANGE PASSWORD CARD -->
                    <div style="background:#ffffff; border-radius:16px; padding:24px; box-shadow:0 4px 15px rgba(0,0,0,0.05); border:1px solid #e2e8f0;">
                        <h4 style="font-size:1.2rem; font-weight:700; color:#1e293b; margin-bottom:12px;">Account Security</h4>
                        <form action="{{ route('admin.settings.password') }}" method="POST">
                            @csrf
                            <div class="form-group" style="margin-bottom:12px;">
                                <label style="font-weight:600; font-size:0.85rem; color:#475569;">Current Password</label>
                                <input type="password" name="current_password" required class="form-input" style="width:100%; padding:10px; border-radius:8px; border:1px solid #cbd5e1;">
                                @error('current_password') <span style="color:#ef4444; font-size:0.8rem;">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group" style="margin-bottom:12px;">
                                <label style="font-weight:600; font-size:0.85rem; color:#475569;">New Password</label>
                                <input type="password" name="new_password" required minlength="8" class="form-input" style="width:100%; padding:10px; border-radius:8px; border:1px solid #cbd5e1;">
                                @error('new_password') <span style="color:#ef4444; font-size:0.8rem;">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group" style="margin-bottom:16px;">
                                <label style="font-weight:600; font-size:0.85rem; color:#475569;">Confirm New Password</label>
                                <input type="password" name="new_password_confirmation" required minlength="8" class="form-input" style="width:100%; padding:10px; border-radius:8px; border:1px solid #cbd5e1;">
                            </div>
                            <button type="submit" class="btn btn-primary" style="width:100%; padding:12px; font-weight:700; border-radius:10px;">
                                Update Password 🔒
                            </button>
                            @if(session('success'))
                                <div style="margin-top:10px; color:#059669; font-size:0.9rem; font-weight:600; text-align:center;">{{ session('success') }}</div>
                            @endif
                        </form>
                    </div>

                    <!-- SUBSCRIPTION CARD -->
                    <div style="background:#ffffff; border-radius:16px; padding:24px; box-shadow:0 4px 15px rgba(0,0,0,0.05); border:1px solid #e2e8f0;">
                        <h4 style="font-size:1.2rem; font-weight:700; color:#1e293b; margin-bottom:12px;">Bakery Plan &amp; Billing</h4>
                        <div style="background:#f8fafc; padding:16px; border-radius:12px; border:1px solid #e2e8f0; margin-bottom:16px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                                <span style="font-weight:600; color:#475569;">Current Plan:</span>
                                <span style="font-weight:800; color:#e67399; text-transform:uppercase;">{{ $tenant->plan_tier === 'pro' ? 'PRO ($29/mo)' : 'FREE ($0/mo)' }}</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-weight:600; color:#475569;">Account Status:</span>
                                <span style="color:#059669; font-weight:700;">● {{ $tenant->is_active ? 'Active' : 'Suspended/Canceled' }}</span>
                            </div>
                        </div>

                        @if(($tenant->plan_tier ?? 'free') !== 'pro')
                            <div style="background:linear-gradient(135deg, #FAF8FF, #f5f3ff); border:2px solid #6d28d9; padding:20px; border-radius:14px; margin-bottom:16px;">
                                <span style="background:#6d28d9; color:white; font-size:0.75rem; font-weight:800; padding:4px 10px; border-radius:12px; text-transform:uppercase;">Unlock All Features</span>
                                <h4 style="color:#6d28d9; margin-top:8px; font-size:1.3rem;">Upgrade to BakeryPro PRO ($29/month)</h4>
                                <p style="font-size:0.9rem; color:#555; margin-top:4px; margin-bottom:16px;">Unlock all 7 premium themes, custom domain support, and priority baker support.</p>
                                <a href="https://buy.stripe.com/eVq00jeoj4aB62QanW2Ry0k?client_reference_id={{ $tenant->id }}&prefilled_email={{ urlencode($tenant->email ?? '') }}" target="_blank" class="btn" style="background:linear-gradient(135deg, #6d28d9, #8b5cf6); color:white; text-decoration:none; display:inline-block; padding:12px 24px; font-weight:700; border-radius:10px; width:100%; text-align:center;">
                                    ⚡ Upgrade Now on Stripe
                                </a>
                            </div>
                        @endif
                        <form onsubmit="handleCancelSubscription(event)">
                            <button type="submit" class="btn" style="background:#ef4444; color:#fff; width:100%; padding:12px; font-weight:600; border-radius:10px; border:none; cursor:pointer;">
                                End Subscription / Cancel Account
                            </button>
                        </form>
                    </div>

                    <!-- SUPPORT TICKET FORM CARD -->
                    <div style="background:#ffffff; border-radius:16px; padding:24px; box-shadow:0 4px 15px rgba(0,0,0,0.05); border:1px solid #e2e8f0;">
                        <h4 style="font-size:1.2rem; font-weight:700; color:#1e293b; margin-bottom:12px;">Submit Support Ticket</h4>
                        <form onsubmit="handleSubmitSupportTicket(event)">
                            <div class="form-group" style="margin-bottom:12px;">
                                <label style="font-weight:600; font-size:0.85rem; color:#475569;">Ticket Subject</label>
                                <input type="text" id="ticket_subject" name="subject" required class="form-input" placeholder="e.g. Need help updating custom domain" style="width:100%; padding:10px; border-radius:8px; border:1px solid #cbd5e1;">
                            </div>
                            <div class="form-group" style="margin-bottom:12px;">
                                <label style="font-weight:600; font-size:0.85rem; color:#475569;">Category</label>
                                <select id="ticket_type" name="type" class="form-input" style="width:100%; padding:10px; border-radius:8px; border:1px solid #cbd5e1;">
                                    <option value="support">General Support</option>
                                    <option value="billing">Billing &amp; Subscription</option>
                                    <option value="custom_code">Theme &amp; Customization</option>
                                    <option value="feature_request">Feature Request</option>
                                </select>
                            </div>
                            <div class="form-group" style="margin-bottom:16px;">
                                <label style="font-weight:600; font-size:0.85rem; color:#475569;">Describe Your Request</label>
                                <textarea id="ticket_message" name="message" required rows="4" class="form-input" placeholder="Tell our support team how we can assist your bakery..." style="width:100%; padding:10px; border-radius:8px; border:1px solid #cbd5e1;"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary" style="width:100%; padding:12px; font-weight:700; border-radius:10px;">
                                Submit Support Ticket 🚀
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </main>

        <script>
            async function handleCancelSubscription(e) {
                e.preventDefault();
                if (!confirm('Are you sure you want to end your bakery plan subscription? Your website will be deactivated.')) return;
                try {
                    const res = await fetch('/dashboard/subscription/cancel', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                    });
                    const data = await res.json();
                    alert(data.message || 'Subscription canceled.');
                    window.location.reload();
                } catch(err) {
                    console.error(err);
                    alert('Error canceling subscription.');
                }
            }

            async function handleSubmitSupportTicket(e) {
                e.preventDefault();
                const subject = document.getElementById('ticket_subject').value;
                const type = document.getElementById('ticket_type').value;
                const message = document.getElementById('ticket_message').value;

                try {
                    const res = await fetch('/dashboard/support/ticket', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ subject, type, message })
                    });
                    const data = await res.json();
                    alert(data.message || 'Support ticket submitted!');
                    document.getElementById('ticket_subject').value = '';
                    document.getElementById('ticket_message').value = '';
                } catch(err) {
                    console.error(err);
                    alert('Error submitting support ticket.');
                }
            }

            function previewBakeryLogoFile(input) {
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('bakery-logo-preview').src = e.target.result;
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            }

            async function uploadBakeryLogo(e) {
                e.preventDefault();
                const fileInput = document.getElementById('bakery-logo-file');
                if (!fileInput.files || !fileInput.files[0]) {
                    alert('Please select a logo image file to upload.');
                    return;
                }

                const formData = new FormData();
                formData.append('logo', fileInput.files[0]);

                const statusEl = document.getElementById('logo-upload-status');
                statusEl.style.display = 'none';

                try {
                    const res = await fetch('/dashboard/settings/logo', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });
                    const data = await res.json();
                    if (data.success) {
                        if (data.logo_path) {
                            document.getElementById('bakery-logo-preview').src = data.logo_path;
                        }
                        statusEl.innerText = '✓ Logo saved!';
                        statusEl.style.display = 'inline-block';
                        setTimeout(() => { statusEl.style.display = 'none'; }, 4000);
                    } else {
                        alert(data.message || 'Error uploading logo.');
                    }
                } catch(err) {
                    console.error(err);
                    alert('Failed to upload logo.');
                }
            }

            let quillMenuEditor = null;
            document.addEventListener('DOMContentLoaded', function() {
                if (document.getElementById('quill-menu-editor-container')) {
                    quillMenuEditor = new Quill('#quill-menu-editor-container', {
                        theme: 'snow',
                        placeholder: 'Type or paste your custom menu, bullet points, headers, and pricing notes here...',
                        modules: {
                            toolbar: [
                                [{ 'header': [2, 3, false] }],
                                ['bold', 'italic', 'underline', 'strike'],
                                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                [{ 'color': [] }, { 'background': [] }],
                                ['clean']
                            ]
                        }
                    });
                }
            });

            function clearMenuQuillEditor() {
                if (quillMenuEditor) {
                    quillMenuEditor.setText('');
                }
                document.getElementById('admin_menu_text').value = '';
            }

            async function handleSaveMenuSettings(e) {
                e.preventDefault();
                const form = e.target;

                // Sync Quill editor content to hidden input
                if (quillMenuEditor) {
                    const html = quillMenuEditor.root.innerHTML;
                    const cleanText = quillMenuEditor.getText().trim();
                    const finalHtml = cleanText ? html : '';
                    document.getElementById('admin_menu_text').value = finalHtml;
                }

                const formData = new FormData(form);
                if (quillMenuEditor) {
                    formData.set('menu_text', document.getElementById('admin_menu_text').value);
                }

                const btn = form.querySelector('button[type="submit"]');

                btn.disabled = true;
                btn.innerText = '⏳ Saving Menu Settings...';

                try {
                    const res = await fetch('/dashboard/settings/menu', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });
                    const data = await res.json();
                    if (data.success) {
                        alert('🎉 ' + data.message);
                        window.location.reload();
                    } else {
                        alert(data.message || 'Error saving menu settings.');
                    }
                } catch(err) {
                    console.error(err);
                    alert('Menu settings saved!');
                    window.location.reload();
                } finally {
                    btn.disabled = false;
                    btn.innerText = '💾 Save Menu & Pricing Settings';
                }
            }
        </script>

    <!-- INVOICE EDIT / CREATION MODAL -->
<div id="invoice-edit-modal" class="order-modal-overlay" style="display:none; z-index:9999;">
    <div class="order-modal-card" style="max-width: 500px; width:90%;">
        <div class="order-modal-header" style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #eee; padding-bottom:12px; margin-bottom:16px;">
            <h2 style="font-size:1.25rem; font-family:'Outfit',sans-serif; color:#5c1d37; margin:0;">💳 Invoice Details</h2>
            <button class="btn btn-outline" style="border:none; font-size:1.2rem; cursor:pointer;" onclick="closeInvoiceEditModal()">✕</button>
        </div>
        <div class="order-modal-body">
            <form id="invoice-edit-form" onsubmit="event.preventDefault();">
                <input type="hidden" id="edit-invoice-id">
                <input type="hidden" id="edit-order-id">
                
                <div style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:bold; font-size:0.9rem; color:#444;">Total Amount ($)</label>
                    <input type="number" step="0.01" id="edit-invoice-total" class="form-control" required style="width: 100%; padding: 10px 14px; border-radius: 10px; border: 1px solid #f0e4ea; font-size:1rem;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:bold; font-size:0.9rem; color:#444;">Required Deposit Amount ($)</label>
                    <input type="number" step="0.01" id="edit-invoice-deposit" class="form-control" required style="width: 100%; padding: 10px 14px; border-radius: 10px; border: 1px solid #f0e4ea; font-size:1rem;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display:block; margin-bottom:5px; font-weight:bold; font-size:0.9rem; color:#444;">Baker Notes & Payment Instructions</label>
                    <textarea id="edit-invoice-notes" class="form-control" rows="3" placeholder="e.g. Please send Venmo deposit to @Blushed_Crumbs with Order # in memo..." style="width: 100%; padding: 10px 14px; border-radius: 10px; border: 1px solid #f0e4ea; font-size:0.9rem; font-family:inherit;"></textarea>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; border-top:1px solid #eee; padding-top:16px;">
                    <button type="button" class="btn btn-outline" onclick="closeInvoiceEditModal()">Cancel</button>
                    <button type="button" class="btn btn-outline" onclick="saveInvoiceEdits()" style="border-color:#e67399; color:#e67399;">💾 Save Invoice</button>
                    <button type="button" class="btn btn-primary" onclick="saveAndSendInvoice()">📧 Save & Send</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
