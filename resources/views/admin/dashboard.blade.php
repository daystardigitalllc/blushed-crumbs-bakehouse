@extends('layouts.app')

@section('title', 'Baker Admin Portal | ' . ($tenant->name ?? 'Doughmain.pro'))

@section('content')
@php
    $serverFormSchema = $tenant->form_schema ?? [];
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

            @unless($onboardingComplete)
                @php $onboardingTotal = count($onboardingChecklist); @endphp
                <div id="onboarding-checklist-sidebar" style="background:rgba(255,255,255,0.08); border-radius:10px; padding:10px 12px; margin-bottom:14px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <strong style="font-size:0.7rem; color:rgba(255,255,255,0.9); text-transform:uppercase; letter-spacing:0.04em;">Setup Checklist</strong>
                        <button type="button" onclick="dismissOnboardingChecklist()" title="Hide this checklist" style="background:none; border:none; color:rgba(255,255,255,0.45); cursor:pointer; font-size:0.85rem; line-height:1; padding:0;">✕</button>
                    </div>
                    <div style="background:rgba(255,255,255,0.15); border-radius:20px; height:4px; overflow:hidden; margin-bottom:8px;">
                        <div id="onboarding-progress-bar" style="background:#ffffff; height:100%; width:0%; transition:width 0.3s ease;"></div>
                    </div>
                    <div id="onboarding-checklist-items" style="display:flex; flex-direction:column; gap:4px;">
                        @foreach($onboardingChecklist as $key => $step)
                            @unless($step['done'])
                                <div class="onboarding-checklist-item" data-step="{{ $key }}" onclick="goToOnboardingTab('{{ $step['tab'] }}')" style="font-size:0.76rem; color:rgba(255,255,255,0.85); cursor:pointer; padding:5px 8px; border-radius:6px; background:rgba(255,255,255,0.05); line-height:1.3;">
                                    {{ $step['label'] }}
                                </div>
                            @endunless
                        @endforeach
                    </div>
                </div>
                <script>
                    (function () {
                        var totalSteps = {{ $onboardingTotal }};
                        var dismissKey = 'onboarding_checklist_dismissed_{{ $tenant->id }}';
                        var widget = document.getElementById('onboarding-checklist-sidebar');

                        function remainingCount() {
                            return document.querySelectorAll('#onboarding-checklist-items .onboarding-checklist-item').length;
                        }

                        function updateProgressBar() {
                            var bar = document.getElementById('onboarding-progress-bar');
                            if (!bar) return;
                            var doneCount = totalSteps - remainingCount();
                            bar.style.width = (totalSteps > 0 ? Math.round((doneCount / totalSteps) * 100) : 0) + '%';
                        }
                        updateProgressBar();

                        if (localStorage.getItem(dismissKey) === '1' && widget) {
                            widget.style.display = 'none';
                        }

                        window.dismissOnboardingChecklist = function () {
                            localStorage.setItem(dismissKey, '1');
                            if (widget) widget.style.display = 'none';
                        };

                        // Called from save-success handlers across the dashboard (form
                        // builder, products, gallery, page builder, calendar) so a
                        // completed step disappears immediately instead of waiting for
                        // the next full page load. The server-computed list on reload
                        // remains the source of truth — this is just instant feedback.
                        window.markOnboardingStepDone = function (stepKey) {
                            var item = document.querySelector('#onboarding-checklist-items .onboarding-checklist-item[data-step="' + stepKey + '"]');
                            if (item) item.remove();
                            updateProgressBar();
                            if (remainingCount() === 0 && widget) {
                                widget.style.display = 'none';
                            }
                        };
                    })();

                    window.goToOnboardingTab = function (tabId) {
                        var btn = document.querySelector('.admin-sidebar-nav .admin-nav-item[data-tab="' + tabId + '"]');
                        if (btn) btn.click();
                    };
                </script>
            @endunless

            <nav class="admin-sidebar-nav">
                <button class="admin-nav-item active" data-tab="tab-orders">
                    Orders
                </button>
                <button class="admin-nav-item" data-tab="tab-form-builder">
                    Order Form
                </button>
                <button class="admin-nav-item" data-tab="tab-page-builder">
                    Page Builder
                </button>
                <button class="admin-nav-item" data-tab="tab-products">
                    Products
                </button>
                <button class="admin-nav-item" data-tab="tab-gallery-manager">
                    Device Gallery
                </button>
                <button class="admin-nav-item" data-tab="tab-invoices">
                    Invoices &amp; Payments
                </button>
                <button class="admin-nav-item" data-tab="tab-reviews">
                    Client Reviews
                </button>
                <button class="admin-nav-item" data-tab="tab-email-marketing">
                    Email Marketing @if(($tenant->plan_tier ?? 'free') !== 'pro')<span style="font-size:0.68rem; background:rgba(255,255,255,0.2); padding:2px 8px; border-radius:10px; margin-left:4px;">PRO</span>@endif
                </button>
                <button class="admin-nav-item" data-tab="tab-calendar">
                    Availability &amp; Blackouts
                </button>
                <button class="admin-nav-item" data-tab="tab-settings">
                    Settings
                </button>
                @if(($tenant->plan_tier ?? 'free') !== 'pro')
                    <a href="https://buy.stripe.com/eVq00jeoj4aB62QanW2Ry0k?client_reference_id={{ $tenant->id }}&prefilled_email={{ urlencode($tenant->email ?? '') }}" target="_blank" class="admin-nav-item" style="background:linear-gradient(135deg, #6d28d9, #8b5cf6); color:#ffffff !important; font-weight:700; margin-top:12px; border-radius:12px; text-align:center; box-shadow:0 4px 12px rgba(109,40,217,0.3); text-decoration:none; display:block;">
                        Upgrade to Pro ($29/mo)
                    </a>
                @endif
            </nav>

            <div class="admin-sidebar-footer">
                <button type="button" onclick="openAdminTour()" class="btn btn-outline" style="display:block; width:100%; text-align:center; border-color:rgba(255,255,255,0.3); color:white; margin-bottom:10px;">Take the Tour</button>
                <a href="/" target="_blank" class="btn btn-outline" style="display:block; text-align:center; width:100%; border-color:rgba(255,255,255,0.3); color:white; text-decoration:none; margin-bottom:10px;">← Exit to Storefront</a>
                <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn btn-outline" style="display:block; text-align:center; width:100%; border-color:rgba(255,255,255,0.3); color:white;">Sign Out</button>
                </form>
            </div>
        </aside>

        <!-- RIGHT MAIN CONTENT -->
        <main class="admin-main-content">
            <!-- TAB 1: Orders -->
            <div id="tab-orders" class="tab-content active">
                <div class="section-header">
                    <h3>Orders</h3>
                    <p class="subtitle">Sorted by due date, soonest first.</p>
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
                                    DUE: {{ $dueDate->format('M d, Y') }} ({{ $order->time_slot }})
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
                                    <p class="allergy-warning"><strong>Allergies:</strong> {{ $order->allergies }}</p>
                                @endif

                                <div class="pricing-breakdown">
                                    <span>Total: <strong>${{ number_format($order->total_price, 2) }}</strong></span>
                                    <span>50% Deposit: <strong>${{ number_format($order->deposit_amount, 2) }}</strong>
                                        ({{ $order->deposit_paid ? 'Paid' : 'Pending' }})
                                    </span>
                                </div>
                            </div>

                            <div class="order-card-actions">
                                <button class="btn btn-sm btn-primary" onclick="generateInvoiceFromOrder({{ $order->id }}, {{ $order->total_price }}, {{ $order->deposit_amount }})">Create Invoice</button>
                                <button class="btn btn-sm btn-outline" onclick="copyClientPayLink('{{ $order->invoice ? $order->invoice->invoice_number : '' }}', {{ $order->id }})">Copy Invoice Link</button>
                            </div>
                        </div>
                    @empty
                        <div style="background:#ffffff; border:2px dashed #e2e8f0; border-radius:16px; padding:48px; text-align:center; color:#64748b; grid-column: 1 / -1;">
                            <h4 style="font-size:1.2rem; font-weight:700; color:#1e293b; margin-bottom:6px;">No Customer Orders Yet</h4>
                            <p style="font-size:0.95rem; margin-bottom:18px;">When customers submit cake inquiries or place orders on your storefront, they will appear here in order of due date!</p>
                            <a href="{{ url('/') }}" target="_blank" class="btn btn-primary" style="display:inline-block; padding:10px 20px; font-size:0.9rem; text-decoration:none;">View Your Storefront →</a>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- TAB 2: Order Form -->
            <div id="tab-form-builder" class="tab-content">
                @php
                    $serverFormSchema = $tenant->form_schema ?? [];
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
                    <h3>Order Form</h3>
                    <p class="subtitle">Customize the steps and fields customers fill out when placing an order.</p>
                </div>

                <!-- EMAIL ROUTING SETTINGS CARD -->
                <div class="form-builder-card" style="border: 2px solid var(--primary); background: var(--theme-section-bg, #fff7fa);">
                    <h4 style="color:#5c1d37;">Order Email Routing</h4>
                    <p style="font-size:0.9rem; color:#666; margin-bottom:15px;">All completed order form entries will be sent to this address:</p>
                    <form id="email-routing-form" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
                        <input type="email" id="admin-routing-email" value="{{ $tenant->email ?? '' }}" placeholder="e.g. baker@yourbakehouse.com" required style="flex:1; min-width:220px;">
                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                    <div id="email-save-status" style="margin-top:10px; font-weight:700; color:#28a745; font-size:0.88rem; display:none;"></div>
                </div>

                <!-- ADD STEP / FIELD CARD -->
                <div class="form-builder-card">
                    <h4>Add Step or Field</h4>
                    <form id="add-field-form" class="form-builder-grid">
                        <div style="grid-column: 1 / -1;">
                            <label style="font-weight:700; color:#5c1d37;">Field Type / Template</label>
                            <select id="field-type" onchange="toggleOptionsRow(this.value)" style="width:100%; max-width:100%; box-sizing:border-box; text-overflow:ellipsis;">
                                <option value="products">Product Catalog</option>
                                <option value="calendar">Booking Calendar</option>
                                <option value="flavors">Flavors Grid</option>
                                <option value="frosting">Frosting Grid</option>
                                <option value="fillings">Fillings Grid</option>
                                <option value="textarea">Textarea / Notes</option>
                                <option value="fulfillment">Fulfillment &amp; Time Slots</option>
                                <option value="allergies">Allergy Notice</option>
                                <option value="social_discount">Social Discounts</option>
                                <option value="file_upload">Inspiration Photo Upload</option>
                                <option value="terms">Terms &amp; Conditions</option>
                                <option value="contact_info">Contact Info &amp; Submit</option>
                                <option value="text">Single-Line Text</option>
                                <option value="select">Select Dropdown</option>
                                <option value="datepicker">Date Picker</option>
                                <option value="toggle">Yes / No Toggle</option>
                            </select>
                        </div>
                        <div>
                            <label>Step Header / Title</label>
                            <input type="text" id="field-label" placeholder="e.g. Choose Your Flavors, Select Crust Type…" required>
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <label>Step Subtext / Directions</label>
                            <textarea id="field-description" placeholder="e.g. Select all options that apply... (for Terms & Conditions steps, add the actual policy text afterward using the Edit Policy Text button below)" style="width:100%; height:80px; padding:9px; border-radius:8px; border:1px solid #ccc; font-family:inherit;"></textarea>
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

                            <button type="button" class="btn btn-outline btn-sm" onclick="addAdminOptionRow()" style="border-radius:12px; font-weight:700; color:var(--primary); border-color:var(--theme-section-bg, #f8c6d7);">
                                + Add Option Choice
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
                            <h4 style="margin-bottom:4px;">Configured Form Steps &amp; Fields</h4>
                            <span style="font-size:0.85rem; color:#888; font-weight:500;">Drag rows, or use the arrows, to reorder steps.</span>
                        </div>
                        <button id="save-form-schema-btn" class="btn btn-primary" onclick="saveFormSchemaToServer()">Save Order Form Layout</button>
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

            <!-- TAB: Page Builder (Homepage Section & Content Accordion Studio) -->
            <div id="tab-page-builder" class="tab-content">
                <div class="section-header">
                    <h3>Page Builder</h3>
                    <p class="subtitle">Edit your homepage's text, images, and section order. Changes go live when you save.</p>
                </div>

                <div class="form-builder-card" style="border:1px solid var(--theme-section-bg, #ddd6fe);">
                    <div style="display:flex; justify-content:flex-end; margin-bottom:14px;">
                        <button class="btn btn-primary" onclick="saveSectionManagerForm()" style="background:var(--primary); border-color:var(--primary);">Save All Changes</button>
                    </div>

                    <div id="section-manager-msg" style="display:none; margin-bottom:14px; background:var(--theme-section-bg, #ddd6fe); color:var(--dark-text); padding:10px 14px; border-radius:10px; font-size:0.88rem; font-weight:600; border:1px solid var(--theme-section-bg, #c4b5fd);"></div>

                    <form id="section-manager-form">
                        @csrf
                        @php
                            $orderedSections = $tenant->getOrderedSections();
                            $siteContent = $tenant->site_content ?? App\Models\Tenant::getDefaultSiteContent();
                            $bullets = data_get($siteContent, 'whimsical_bullets', []);
                        @endphp

                        <div id="section-manager-list" style="display:flex; flex-direction:column; gap:10px;">
                            @foreach($orderedSections as $secId => $sec)
                                @php
                                    // Defensive: strips any leading emoji from section names saved to a
                                    // tenant's DB before Tenant::getDefaultSectionSettings() dropped them.
                                    $secName = trim(preg_replace('/^[^\p{L}\p{N}]+/u', '', $sec['name'] ?? $secId));
                                @endphp
                                <div class="section-manager-row" data-id="{{ $secId }}" style="background:white; border-radius:10px; border:1px solid #e5e7eb; overflow:hidden;">

                                    <!-- ACCORDION HEADER ROW -->
                                    <div class="section-accordion-header" onclick="toggleSectionAccordion(this)" style="padding:14px 18px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; cursor:pointer; background:#fafafa; user-select:none;">
                                        <div style="display:flex; align-items:center; gap:12px;">
                                            <span class="drag-handle" style="cursor:grab; font-weight:800; color:#a1a1aa; font-size:1.1rem;" onclick="event.stopPropagation()">⠿</span>
                                            <input type="hidden" class="section-order-input" name="sections[{{ $secId }}][order]" value="{{ $sec['order'] ?? 1 }}">
                                            <strong style="color:#27272a; font-size:0.95rem;">{{ $secName }}</strong>
                                        </div>

                                        <div style="display:flex; align-items:center; gap:8px;" onclick="event.stopPropagation()">
                                            <button type="button" class="btn btn-sm btn-outline" onclick="moveSectionUp(this)" style="padding:3px 9px; font-size:0.78rem;" aria-label="Move up">↑</button>
                                            <button type="button" class="btn btn-sm btn-outline" onclick="moveSectionDown(this)" style="padding:3px 9px; font-size:0.78rem;" aria-label="Move down">↓</button>
                                            <label class="toggle-switch" style="transform:scale(0.8);">
                                                <input type="checkbox" name="sections[{{ $secId }}][enabled]" value="1" {{ !empty($sec['enabled']) ? 'checked' : '' }}>
                                                <span class="toggle-slider"></span>
                                            </label>
                                            <span class="accordion-arrow" style="font-size:0.9rem; color:#a1a1aa; margin-left:4px; transition:transform 0.2s ease;">▾</span>
                                        </div>
                                    </div>

                                    <!-- EXPANDABLE ACCORDION BODY WITH SECTION COPY & CONTENT EDITORS -->
                                    <div class="section-accordion-body" style="display:none; padding:18px; border-top:1px solid #eee; background:#ffffff;">
                                        @if($secId === 'hero')
                                            <div style="margin-bottom:12px; padding:12px; border-radius:10px; border:1px solid #eee;">
                                                <label style="font-weight:600; font-size:0.82rem; color:#555;">Hero Background (Image or Video)</label>
                                                <div style="display:flex; gap:10px; align-items:center; margin-top:4px;">
                                                    <input type="text" id="hero_bg_url" name="hero_bg_url" value="{{ data_get($siteContent, 'hero_bg_url', '') }}" placeholder="URL or uploaded path (e.g. storage/hero.mp4)" style="flex:1; padding:8px; border-radius:8px; border:1px solid #ccc; font-family:monospace; font-size:0.85rem;">
                                                    <label class="btn btn-sm btn-outline" style="cursor:pointer; padding:6px 12px; border-color:var(--primary); color:var(--dark-text); font-size:0.8rem;">
                                                        Upload File
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

                                        @elseif($secId === 'about')
                                            <div>
                                                <label style="font-weight:600; font-size:0.82rem; color:#555;">Section Title</label>
                                                <input type="text" name="about_title" value="{{ data_get($siteContent, 'about_title') }}" style="width:100%; padding:9px; border-radius:8px; border:1px solid #ccc; margin-bottom:12px;">
                                            </div>
                                            <div>
                                                <label style="font-weight:600; font-size:0.82rem; color:#555;">Story / Bio</label>
                                                <textarea name="about_bio" rows="4" style="width:100%; padding:9px; border-radius:8px; border:1px solid #ccc; font-family:inherit;">{{ data_get($siteContent, 'about_bio') }}</textarea>
                                            </div>

                                        @elseif($secId === 'highlights')
                                            @php $hlList = data_get($siteContent, 'highlights', []); @endphp
                                            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:12px;">
                                                @for($h = 0; $h < 4; $h++)
                                                    <div style="padding:14px; border-radius:10px; border:1px solid #eee; display:flex; flex-direction:column; gap:8px;">
                                                        <label style="font-weight:700; font-size:0.85rem; color:var(--dark-text);">Highlight {{ $h+1 }}</label>

                                                        <div>
                                                            <label style="font-size:0.78rem; color:#666; display:block; margin-bottom:3px; font-weight:600;">Icon</label>
                                                            <div style="display:flex; gap:8px; align-items:center;">
                                                                <input type="text" id="hl-icon-input-{{ $h }}" name="highlights[{{ $h }}][icon]" value="{{ $hlList[$h]['icon'] ?? '🎂' }}" style="width:50px; text-align:center; padding:6px; border-radius:6px; border:1px solid #ccc; font-size:1.1rem;">
                                                                <button type="button" class="btn btn-sm btn-outline" onclick="openIconPicker(document.getElementById('hl-icon-input-{{ $h }}'))" style="padding:5px 10px; font-size:0.8rem; border-color:var(--primary); color:var(--dark-text);">Select Icon</button>
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

                                        @elseif($secId === 'categories')
                                            @php
                                                $catList = data_get($siteContent, 'categories', [
                                                    ['title' => 'Single Tier Cakes', 'desc' => 'Perfect for birthdays & intimate gatherings', 'image_url' => ''],
                                                    ['title' => 'Multi Tier Custom Cakes', 'desc' => 'Bespoke designs for weddings & celebrations', 'image_url' => ''],
                                                    ['title' => 'Treats & Sweets By The Dozen', 'desc' => 'Cupcakes, macarons, and dessert tables', 'image_url' => '']
                                                ]);
                                            @endphp

                                            <div id="accordion-categories-list" style="display:flex; flex-direction:column; gap:12px;">
                                                @foreach($catList as $cIdx => $cat)
                                                    <div class="accordion-category-item" style="padding:16px; border-radius:10px; border:1px solid #eee; display:flex; flex-direction:column; gap:10px;">
                                                        <div style="display:flex; justify-content:space-between; align-items:center; gap:10px;">
                                                            <input type="text" name="categories[{{ $cIdx }}][title]" value="{{ $cat['title'] ?? '' }}" placeholder="Category Title (e.g. Single Tier Cakes)" style="flex:1; padding:8px 12px; border-radius:8px; border:1px solid #ccc; font-weight:700; font-size:0.95rem;">
                                                            <button type="button" class="btn btn-sm btn-outline" onclick="this.closest('.accordion-category-item').remove()" style="color:#dc2626; border-color:#fca5a5; padding:4px 10px; font-size:0.8rem;">Delete</button>
                                                        </div>

                                                        <div>
                                                            <label style="font-size:0.8rem; font-weight:600; color:#555; display:block; margin-bottom:4px;">Short Description</label>
                                                            <input type="text" name="categories[{{ $cIdx }}][desc]" value="{{ $cat['desc'] ?? '' }}" placeholder="Category Description..." style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid #ccc; font-size:0.88rem;">
                                                        </div>

                                                        <div>
                                                            <label style="font-size:0.8rem; font-weight:600; color:#555; display:block; margin-bottom:4px;">Category Image</label>
                                                            <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                                                                <input type="text" id="cat_img_input_{{ $cIdx }}" name="categories[{{ $cIdx }}][image_url]" value="{{ $cat['image_url'] ?? '' }}" placeholder="Select photo or upload..." style="flex:1; padding:8px; border-radius:8px; border:1px solid #ccc; font-size:0.85rem;">
                                                                <button type="button" class="btn btn-sm btn-outline" onclick="openGalleryPicker(document.getElementById('cat_img_input_{{ $cIdx }}'), 'cat_preview_{{ $cIdx }}')" style="border-color:var(--primary); color:var(--dark-text); font-size:0.8rem; font-weight:700;">Device Gallery</button>
                                                                <label class="btn btn-sm btn-outline" style="cursor:pointer; padding:6px 12px; border-color:var(--primary); color:var(--dark-text); font-size:0.8rem;">
                                                                    Upload File
                                                                    <input type="file" name="category_image_{{ $cIdx }}" accept="image/*" style="display:none;" onchange="uploadSectionMedia(this, 'cat_img_input_{{ $cIdx }}', 'cat_preview_{{ $cIdx }}')">
                                                                </label>
                                                            </div>
                                                            <div id="cat_preview_{{ $cIdx }}" style="margin-top:8px; {{ !empty($cat['image_url']) ? 'display:flex;' : 'display:none;' }} align-items:center; gap:8px;">
                                                                <img src="{{ !empty($cat['image_url']) ? asset($cat['image_url']) : '' }}" style="width:38px; height:38px; object-fit:cover; border-radius:6px; border:1px solid #ddd;">
                                                                <span style="font-size:0.78rem; color:#15803d; font-weight:600;">Photo attached</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline" onclick="addAccordionCategoryItem()" style="margin-top:12px; border-color:var(--primary); color:var(--dark-text); font-weight:700;">
                                                + Add Category
                                            </button>

                                        @elseif($secId === 'whimsical')
                                            <div style="margin-bottom:12px; padding:12px; border-radius:10px; border:1px solid #eee;">
                                                <label style="font-weight:600; font-size:0.82rem; color:#555;">Section Photo</label>
                                                <div style="display:flex; gap:10px; align-items:center; margin-top:4px;">
                                                    <input type="text" id="whimsical_image_url" name="whimsical_image_url" value="{{ data_get($siteContent, 'whimsical_image_url', '') }}" placeholder="Select photo or upload..." style="flex:1; padding:8px; border-radius:8px; border:1px solid #ccc; font-size:0.85rem;">
                                                    <button type="button" class="btn btn-sm btn-outline" onclick="openGalleryPicker(document.getElementById('whimsical_image_url'), 'whimsical_preview')" style="border-color:var(--primary); color:var(--dark-text); font-size:0.8rem; font-weight:700;">Device Gallery</button>
                                                    <label class="btn btn-sm btn-outline" style="cursor:pointer; padding:6px 12px; border-color:var(--primary); color:var(--dark-text); font-size:0.8rem;">
                                                        Upload File
                                                        <input type="file" accept="image/*" onchange="uploadSectionMedia(this, 'whimsical_image_url', 'whimsical_preview')" style="display:none;">
                                                    </label>
                                                </div>
                                                <div id="whimsical_preview" style="margin-top:8px; {{ !empty(data_get($siteContent, 'whimsical_image_url')) ? 'display:flex;' : 'display:none;' }} align-items:center; gap:10px;">
                                                    <img src="{{ !empty(data_get($siteContent, 'whimsical_image_url')) ? asset(data_get($siteContent, 'whimsical_image_url')) : '' }}" style="height:48px; width:48px; object-fit:cover; border-radius:8px; border:1px solid #ddd;">
                                                    <span style="font-size:0.8rem; color:#15803d; font-weight:600;">Photo attached</span>
                                                </div>
                                            </div>
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
                                            <div style="display:flex; flex-direction:column; gap:10px;">
                                                <div style="padding:12px; border-radius:10px; border:1px solid #eee;">
                                                    <label style="font-weight:600; font-size:0.82rem; color:#555;">Video / Image Background</label>
                                                    <div style="display:flex; gap:10px; align-items:center; margin-top:4px;">
                                                        <input type="text" id="promo_video_url" name="promo_video_url" value="{{ data_get($siteContent, 'promo_video_url', '') }}" placeholder="Upload custom video or image URL..." style="flex:1; padding:8px; border-radius:8px; border:1px solid #ccc; font-family:monospace; font-size:0.85rem;">
                                                        <label class="btn btn-sm btn-outline" style="cursor:pointer; padding:6px 12px; border-color:var(--primary); color:var(--dark-text); font-size:0.8rem;">
                                                            Upload File
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
                                            @php $hwList = data_get($siteContent, 'how_it_works', []); @endphp
                                            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:12px;">
                                                @for($s = 0; $s < 3; $s++)
                                                    <div style="padding:12px; border-radius:10px; border:1px solid #eee;">
                                                        <label style="font-weight:700; font-size:0.8rem; color:var(--dark-text);">Step {{ $s+1 }}</label>
                                                        <input type="text" name="how_it_works[{{ $s }}][title]" value="{{ $hwList[$s]['title'] ?? '' }}" placeholder="Step Title..." style="width:100%; margin-top:6px; padding:6px 10px; border-radius:6px; border:1px solid #ccc; font-weight:600; font-size:0.85rem;">
                                                        <textarea name="how_it_works[{{ $s }}][desc]" rows="2" placeholder="Step Description..." style="width:100%; margin-top:6px; padding:6px 10px; border-radius:6px; border:1px solid #ccc; font-size:0.82rem; font-family:inherit;">{{ $hwList[$s]['desc'] ?? '' }}</textarea>
                                                    </div>
                                                @endfor
                                            </div>

                                        @elseif($secId === 'reviews')
                                            @php $revList = data_get($siteContent, 'reviews', []); @endphp
                                            @if($reviews->isNotEmpty())
                                                <p style="font-size:0.82rem; color:#92400e; background:#fef3c7; padding:8px 12px; border-radius:8px; margin-bottom:10px;">You have real reviews in the <strong>Client Reviews</strong> tab — those are shown on your site instead of the placeholders below. Edit or add reviews there instead.</p>
                                            @endif
                                            <div id="accordion-reviews-list" style="display:flex; flex-direction:column; gap:10px;">
                                                @foreach($revList as $rIdx => $rev)
                                                    <div class="accordion-review-item" style="padding:12px; border-radius:10px; border:1px solid #eee; display:flex; flex-direction:column; gap:8px;">
                                                        <div style="display:flex; justify-content:space-between; align-items:center;">
                                                            <input type="text" name="reviews[{{ $rIdx }}][name]" value="{{ $rev['name'] ?? '' }}" placeholder="Customer Name (e.g. Kristen Ramirez)" style="width:240px; padding:6px 10px; border-radius:6px; border:1px solid #ccc; font-weight:700;">
                                                            <button type="button" class="btn btn-sm btn-outline" onclick="this.closest('.accordion-review-item').remove()" style="color:#dc2626; border-color:#fca5a5; padding:2px 8px; font-size:0.78rem;">Delete</button>
                                                        </div>
                                                        <textarea name="reviews[{{ $rIdx }}][quote]" rows="2" placeholder="Customer Quote / Testimonial..." style="width:100%; padding:6px 10px; border-radius:6px; border:1px solid #ccc; font-size:0.85rem; font-family:inherit;">{{ $rev['quote'] ?? '' }}</textarea>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline" onclick="addAccordionReviewItem()" style="margin-top:10px; border-color:var(--primary); color:var(--dark-text);">+ Add Review</button>

                                        @elseif($secId === 'faq')
                                            @php $faqList = data_get($siteContent, 'faqs', []); @endphp
                                            <div id="accordion-faqs-list" style="display:flex; flex-direction:column; gap:10px;">
                                                @foreach($faqList as $fIdx => $faq)
                                                    <div class="accordion-faq-item" style="padding:12px; border-radius:10px; border:1px solid #eee; display:flex; flex-direction:column; gap:8px;">
                                                        <div style="display:flex; justify-content:space-between; align-items:center; gap:10px;">
                                                            <input type="text" name="faqs[{{ $fIdx }}][q]" value="{{ $faq['q'] ?? '' }}" placeholder="Question (e.g. How far in advance should I order?)" style="flex:1; padding:6px 10px; border-radius:6px; border:1px solid #ccc; font-weight:700;">
                                                            <button type="button" class="btn btn-sm btn-outline" onclick="this.closest('.accordion-faq-item').remove()" style="color:#dc2626; border-color:#fca5a5; padding:2px 8px; font-size:0.78rem;">Delete</button>
                                                        </div>
                                                        <textarea name="faqs[{{ $fIdx }}][a]" rows="2" placeholder="Answer / Bakery Policy..." style="width:100%; padding:6px 10px; border-radius:6px; border:1px solid #ccc; font-size:0.85rem; font-family:inherit;">{{ $faq['a'] ?? '' }}</textarea>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline" onclick="addAccordionFaqItem()" style="margin-top:10px; border-color:var(--primary); color:var(--dark-text);">+ Add FAQ</button>

                                        @elseif($secId === 'cta_banner')
                                            <div style="display:flex; flex-direction:column; gap:10px;">
                                                <div style="padding:12px; border-radius:10px; border:1px solid #eee;">
                                                    <label style="font-weight:600; font-size:0.82rem; color:#555;">Video / Image Background</label>
                                                    <div style="display:flex; gap:10px; align-items:center; margin-top:4px;">
                                                        <input type="text" id="cta_banner_url" name="cta_banner_url" value="{{ data_get($siteContent, 'cta_banner_url', '') }}" placeholder="Upload custom background media URL..." style="flex:1; padding:8px; border-radius:8px; border:1px solid #ccc; font-family:monospace; font-size:0.85rem;">
                                                        <label class="btn btn-sm btn-outline" style="cursor:pointer; padding:6px 12px; border-color:var(--primary); color:var(--dark-text); font-size:0.8rem;">
                                                            Upload File
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

                                        @elseif($secId === 'featured_gallery')
                                            @php
                                                $featuredTitle = data_get($siteContent, 'featured_gallery_title', 'Featured Creations');
                                                $featuredImages = data_get($siteContent, 'featured_gallery_images', []);
                                            @endphp

                                            <div style="margin-bottom:14px;">
                                                <label style="font-weight:600; font-size:0.82rem; color:#555;">Section Title</label>
                                                <input type="text" name="featured_gallery_title" value="{{ $featuredTitle }}" style="width:100%; padding:9px; border-radius:8px; border:1px solid #ccc;">
                                            </div>

                                            <input type="hidden" id="featured_gallery_images_input" name="featured_gallery_images" value='{{ json_encode($featuredImages) }}'>

                                            <button type="button" class="btn btn-sm btn-outline" onclick="openFeaturedGalleryPicker()" style="border-color:var(--primary); color:var(--dark-text); font-weight:700; margin-bottom:12px;">Select Photos from Device Gallery</button>

                                            <div id="featured-gallery-preview-strip" style="display:flex; flex-wrap:wrap; gap:10px;"></div>

                                        @else
                                            <p style="font-size:0.85rem; color:#666; margin:0;">No additional settings for this section — use Save to apply its order and visibility.</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </form>
                </div>
            </div>

            <div id="tab-products" class="tab-content">
                <div class="section-header">
                    <h3>Products</h3>
                    <p class="subtitle">Add, remove, and update prices for your order form products.</p>
                </div>

                <div class="form-builder-card" style="border:2px solid var(--primary); background:var(--theme-section-bg, #fff7fa);">
                    <h4>Add New Product</h4>
                    <form id="add-product-form" class="form-builder-grid" action="{{ route('admin.products.store') }}" method="POST">
                        @csrf
                        <div>
                            <label>Product Name</label>
                            <input type="text" id="new-prod-name" name="name" placeholder="e.g. 6″ Heart Cake" required>
                        </div>
                        <div>
                            <label>Price ($)</label>
                            <input type="number" id="new-prod-price" name="price" placeholder="45.00" step="0.01" required>
                        </div>
                        <div>
                            <label>Category</label>
                            <select id="new-prod-category" name="category" onchange="if(this.value === 'custom_new'){ document.getElementById('new-prod-category-custom').style.display='block'; document.getElementById('new-prod-category-custom').setAttribute('required', 'true'); } else { document.getElementById('new-prod-category-custom').style.display='none'; document.getElementById('new-prod-category-custom').removeAttribute('required'); }">
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
                    <h4>Current Product Catalog</h4>
                    <div id="products-admin-grid">
                        @foreach($products as $prod)
                            <div class="product-item-row" data-id="{{ $prod->id }}" style="display:flex; justify-content:space-between; align-items:center; padding:13px 16px; border-bottom:1px solid #f0e4ea;">
                                <div>
                                    <strong style="color:#5c1d37;">{{ $prod->name }}</strong>
                                    <span style="background:#f9e0eb; color:#7a2b4a; font-size:0.75rem; font-weight:700; padding:2px 8px; border-radius:20px; margin-left:8px;">{{ $prod->category }}</span>
                                </div>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span style="font-size:0.85rem; color:#999;">$</span>
                                    <input type="number" step="0.01" value="{{ number_format($prod->price, 2, '.', '') }}" class="prod-price-input" style="width:80px;">
                                    <button class="btn btn-sm btn-secondary" onclick="updateProductPrice({{ $prod->id }}, this)">Save</button>
                                    <button class="btn btn-sm btn-outline" style="color:#d9534f; border-color:#d9534f;" onclick="deleteProduct({{ $prod->id }}, this)">✕</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- MENU STUDIO & UPLOAD CARD -->
                <div class="form-builder-card" style="border:2px solid var(--primary); background:linear-gradient(135deg, #ffffff, #fdf4ff); margin-top:25px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:16px;">
                        <div>
                            <h4 style="color:var(--dark-text); font-size:1.25rem; margin-bottom:4px;">Menu Page &amp; Uploads</h4>
                            <p style="font-size:0.88rem; color:#555;">Upload your official menu image/PDF, or write custom menu text. Shown on your public <code>/menu</code> page.</p>
                        </div>
                        <span style="background:var(--primary); color:white; font-size:0.75rem; font-weight:800; padding:4px 12px; border-radius:12px; text-transform:uppercase;">Public Storefront Menu</span>
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
                                    <option value="both" {{ $menuType === 'both' ? 'selected' : '' }}>Both (Uploaded Menu Image + Catalog Grid + Custom Notes)</option>
                                    <option value="text" {{ $menuType === 'text' ? 'selected' : '' }}>Styled Theme Menu Grid + Custom Notes</option>
                                    <option value="image" {{ $menuType === 'image' ? 'selected' : '' }}>Uploaded Menu Image / PDF + Custom Notes</option>
                                </select>
                                <small style="color:#64748b; font-size:0.8rem; display:block; margin-top:4px;">
                                    Custom notes (editor below) appear at the bottom of your public menu page.
                                </small>
                            </div>

                            <!-- Upload Menu File -->
                            <div>
                                <label style="font-weight:700; color:#334155; font-size:0.9rem; display:block; margin-bottom:6px;">
                                    Upload Official Bakery Menu Image/PDF
                                </label>

                                @if($menuImagePath)
                                    <div style="background:#f0fdf4; border:1.5px solid #22c55e; border-radius:12px; padding:12px 16px; margin-bottom:10px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                                        <div style="display:flex; align-items:center; gap:12px;">
                                            @if(!Str::endsWith(strtolower($menuImagePath), '.pdf'))
                                                <img src="{{ asset($menuImagePath) }}" alt="Current Menu Thumbnail" style="width:48px; height:48px; object-fit:cover; border-radius:8px; border:1px solid #bbf7d0;">
                                            @endif
                                            <div>
                                                <div style="font-weight:700; color:#15803d; font-size:0.88rem;">Menu file active on storefront</div>
                                                <a href="{{ asset($menuImagePath) }}" target="_blank" style="color:#166534; font-size:0.82rem; font-weight:600; text-decoration:underline;">
                                                    View uploaded file ↗
                                                </a>
                                            </div>
                                        </div>
                                        <label style="background:#fee2e2; color:#dc2626; border:1px solid #fca5a5; padding:4px 10px; border-radius:8px; font-weight:700; font-size:0.8rem; cursor:pointer;">
                                            <input type="checkbox" name="remove_menu_image" value="1"> Delete Active File
                                        </label>
                                    </div>
                                    <small style="color:#64748b; font-size:0.8rem; display:block; margin-bottom:6px;">Upload new file below to replace current file:</small>
                                @else
                                    <div style="background:#f8fafc; border:1px dashed #cbd5e1; border-radius:8px; padding:8px 12px; margin-bottom:8px; font-size:0.82rem; color:#64748b;">
                                        No official menu image/PDF uploaded yet. Select a file below to upload.
                                    </div>
                                @endif

                                <input type="file" name="menu_image" id="admin_menu_image" accept="image/*,.pdf" class="form-input" style="width:100%; padding:8px; border-radius:8px; border:1px solid #cbd5e1; background:#fff;">
                            </div>
                        </div>

                        <!-- Menu WYSIWYG Rich Text Editor -->
                        <div style="margin-bottom:20px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                                <label style="font-weight:700; color:#334155; font-size:0.9rem;">
                                    Custom Menu &amp; Pricing Text
                                </label>
                                <button type="button" class="btn btn-sm btn-outline" style="font-size:0.75rem; padding:2px 10px; color:#dc2626; border-color:#fca5a5;" onclick="clearMenuQuillEditor()">
                                    Clear Editor
                                </button>
                            </div>
                            <input type="hidden" name="menu_text" id="admin_menu_text" value="{{ $menuText }}">
                            <div id="quill-menu-editor-container" style="background:#ffffff; min-height:240px; border-radius:0 0 10px 10px; font-size:0.95rem;">{!! $menuText !!}</div>
                        </div>

                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <a href="{{ route('storefront.menu') }}" target="_blank" style="color:var(--dark-text); font-size:0.9rem; font-weight:600; text-decoration:none;">
                                Preview Public Menu Page ↗
                            </a>
                            <button type="submit" class="btn btn-primary" style="background:var(--primary); border:none; padding:10px 24px; font-weight:700; border-radius:8px;">
                                Save Menu &amp; Pricing Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>


            <div id="tab-gallery-manager" class="tab-content">
                <div class="section-header">
                    <h3>Device Gallery</h3>
                    <p class="subtitle">Upload photos from your computer, phone, or tablet. They'll publish to your public <strong>/gallery</strong> page.</p>
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
                            <div id="gal-device-dropzone" style="border:2px dashed var(--primary); background:var(--theme-section-bg, #fff7fa); padding:30px 20px; border-radius:16px; text-align:center; cursor:pointer;" onclick="document.getElementById('gal-image-file').click();">
                                <p style="font-size:1.05rem; font-weight:600; color:#5c1d37;" id="gal-dropzone-text">Click to select photo from device or drag image here</p>
                                <span style="font-size:12px; color:#888;">Supports JPG, PNG, WEBP, GIF (Up to 10MB)</span>
                            </div>
                            <input type="file" id="gal-image-file" name="image" accept="image/*" style="display:none;" required>
                        </div>

                        <!-- LIVE PREVIEW CONTAINER -->
                        <div id="gal-upload-preview" style="display:none; text-align:center;">
                            <img id="gal-preview-img" src="" style="max-width:200px; height:140px; object-fit:cover; border-radius:14px; border:2px solid var(--primary); box-shadow:0 4px 15px rgba(0,0,0,0.1);">
                            <p style="font-weight:700; color:#28a745; margin-top:6px; font-size:0.9rem;">Photo ready for publish</p>
                        </div>

                        <button type="submit" id="gal-submit-btn" class="btn btn-primary" style="padding:14px;">Publish Photo to Live Gallery</button>
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
                                        <span style="font-size:0.8rem; color:var(--primary); font-weight:600;">{{ $item->category }}</span>
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
                    <h3>Invoices &amp; Payments</h3>
                    <p class="subtitle">Add payment methods and generate digital client invoices.</p>
                </div>

                <!-- RECENT INVOICES TRACKER -->
                <div class="form-builder-card" style="margin-bottom:20px;">
                    <h4>Recent Invoices</h4>
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
                                        <button class="btn btn-sm btn-outline" onclick="copyClientPayLink('{{ $inv->invoice_number }}')">Copy Link</button>
                                        <button class="btn btn-sm btn-outline" onclick="openInvoiceEditModal({{ $inv->id }}, {{ $inv->total_amount }}, {{ $inv->deposit_amount ?? 0 }}, '{{ addslashes($inv->notes ?? '') }}', {{ $inv->order_id ?? 'null' }})">Edit</button>
                                        <button class="btn btn-sm btn-primary" onclick="sendInvoice('{{ $inv->id }}')">Send</button>
                                        <button class="btn btn-sm btn-outline" style="color:#d9534f; border-color:#d9534f;" onclick="deleteInvoice({{ $inv->id }}, this)">Delete</button>
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
                <div class="form-builder-card" style="border:2px solid var(--primary); background:var(--theme-section-bg, #fff7fa);">
                    <h4 style="color:#5c1d37;">Add Custom Payment Option</h4>
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
                                <strong style="color:#5c1d37; font-size:1.05rem;">Venmo</strong>: <code>{{ !empty($tenant->payment_settings['venmo']) ? $tenant->payment_settings['venmo'] : 'Not Configured' }}</code>
                                <p style="font-size:0.85rem; color:#666; margin-top:2px;">Include Order # in payment memo</p>
                            </div>
                            <button class="btn btn-sm btn-outline" style="color:#d9534f; border-color:#d9534f;" onclick="this.parentElement.remove()">Remove</button>
                        </div>
                        <div class="payment-method-row" style="display:flex; justify-content:space-between; align-items:center; background:white; padding:15px; border-radius:12px; margin-bottom:10px; border:1px solid #eee;">
                            <div>
                                <strong style="color:#5c1d37; font-size:1.05rem;">CashApp</strong>: <code>{{ !empty($tenant->payment_settings['cashapp']) ? $tenant->payment_settings['cashapp'] : 'Not Configured' }}</code>
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
                    <h3>Client Reviews</h3>
                    <p class="subtitle">Manage reviews and testimonials shown on your storefront.</p>
                </div>

                <!-- ADD NEW REVIEW CARD -->
                <div class="form-builder-card" style="margin-bottom:20px; border:2px solid var(--primary); background:var(--theme-section-bg, #fff7fa);">
                    <h4 style="color:#5c1d37; margin-bottom:12px;">Add New Client Review</h4>
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
                    <h4>Published Reviews</h4>
                    <p style="font-size:0.85rem; color:#666; margin-bottom:14px;">Currently live on your storefront:</p>

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
                                <button class="btn btn-sm btn-outline" style="color:#d9534f; border-color:#d9534f; flex-shrink:0;" onclick="deleteReview({{ $rev->id }}, this)">Delete</button>
                            </div>
                        @empty
                            <p style="color:#888; text-align:center; padding:20px;">No reviews added yet. Use the form above to publish client reviews!</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- TAB: Email Marketing (Pro only) -->
            <div id="tab-email-marketing" class="tab-content">
                <div class="section-header">
                    <h3>Email Marketing</h3>
                    <p class="subtitle">Build a subscriber list and send offers or coupons straight to your customers' inboxes.</p>
                </div>

                @if(($tenant->plan_tier ?? 'free') === 'pro')
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:25px; margin-bottom:25px;">
                        <!-- SUBSCRIBER LIST CARD -->
                        <div class="form-builder-card">
                            <h4 style="color:#5c1d37; margin-bottom:4px;">Subscriber List</h4>
                            <p style="font-size:0.85rem; color:#666; margin-bottom:14px;"><span id="email-subscriber-count">{{ $emailSubscribers->count() }}</span> active subscriber{{ $emailSubscribers->count() === 1 ? '' : 's' }}</p>

                            <form id="add-subscriber-form" style="display:flex; flex-wrap:wrap; gap:10px; margin-bottom:14px;">
                                <input type="email" id="sub-email" placeholder="customer@email.com" required style="flex:1; min-width:180px; padding:10px 14px; border-radius:10px; border:1px solid #ddd;">
                                <input type="text" id="sub-name" placeholder="Name (optional)" style="flex:1; min-width:140px; padding:10px 14px; border-radius:10px; border:1px solid #ddd;">
                                <button type="submit" class="btn btn-primary">Add</button>
                            </form>
                            <button type="button" class="btn btn-outline" onclick="importCustomersToSubscribers()" style="width:100%; margin-bottom:14px;">Import All Customers With Emails</button>

                            {{-- Fixed height (not max-height) so this box is always the same size —
                                 whether there are 0 subscribers or 500, the card around it never grows;
                                 ~3 rows fit before the list itself scrolls. --}}
                            <div id="admin-subscribers-list" style="display:flex; flex-direction:column; gap:8px; height:190px; overflow-y:auto; padding-right:4px;">
                                @forelse($emailSubscribers as $sub)
                                    <div class="subscriber-item-row" data-id="{{ $sub->id }}" style="background:white; padding:10px 14px; border-radius:10px; border:1px solid #f0e4ea; display:flex; justify-content:space-between; align-items:center; gap:10px;">
                                        <div style="overflow:hidden;">
                                            <strong style="font-size:0.9rem; color:#5c1d37;">{{ $sub->name ?: $sub->email }}</strong>
                                            @if($sub->name)<div style="font-size:0.78rem; color:#888;">{{ $sub->email }}</div>@endif
                                        </div>
                                        <button class="btn btn-sm btn-outline" style="color:#d9534f; border-color:#d9534f; flex-shrink:0;" onclick="deleteSubscriber({{ $sub->id }}, this)">Remove</button>
                                    </div>
                                @empty
                                    <p style="color:#888; text-align:center; padding:16px;" id="no-subscribers-msg">No subscribers yet. Add one above or import your customers.</p>
                                @endforelse
                            </div>
                        </div>

                        <!-- COMPOSE CAMPAIGN CARD -->
                        <div class="form-builder-card" style="border:2px solid var(--primary); background:var(--theme-section-bg, #fff7fa);">
                            <h4 style="color:#5c1d37; margin-bottom:12px;">Send a New Offer</h4>
                            <form id="send-campaign-form" style="display:flex; flex-direction:column; gap:12px;">
                                <div>
                                    <label style="font-weight:700; font-size:0.85rem; color:#5c1d37; display:block; margin-bottom:4px;">Subject Line</label>
                                    <input type="text" id="campaign-subject" placeholder="e.g. 20% Off This Weekend Only!" required style="width:100%; padding:10px 14px; border-radius:10px; border:1px solid #ddd;">
                                </div>
                                <div>
                                    <label style="font-weight:700; font-size:0.85rem; color:#5c1d37; display:block; margin-bottom:4px;">Message</label>
                                    <textarea id="campaign-body" placeholder="Tell your customers about the offer..." required style="width:100%; height:120px; padding:10px 14px; border-radius:10px; border:1px solid #ddd; font-family:inherit;"></textarea>
                                </div>
                                <div>
                                    <label style="font-weight:700; font-size:0.85rem; color:#5c1d37; display:block; margin-bottom:4px;">Coupon Code (optional)</label>
                                    <input type="text" id="campaign-coupon" placeholder="e.g. SWEET20" style="width:100%; padding:10px 14px; border-radius:10px; border:1px solid #ddd;">
                                </div>
                                <button type="submit" id="send-campaign-btn" class="btn btn-primary" style="align-self:flex-start;">Send to {{ $emailSubscribers->count() }} Subscriber{{ $emailSubscribers->count() === 1 ? '' : 's' }}</button>
                            </form>
                        </div>
                    </div>

                    <!-- CAMPAIGN HISTORY -->
                    <div class="form-builder-card">
                        <h4>Past Campaigns</h4>
                        <div id="admin-campaigns-list" style="display:flex; flex-direction:column; gap:10px; margin-top:10px;">
                            @forelse($emailCampaigns as $camp)
                                <div style="background:white; padding:14px 16px; border-radius:10px; border:1px solid #f0e4ea; display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
                                    <div>
                                        <strong style="font-size:0.92rem; color:#5c1d37;">{{ $camp->subject }}</strong>
                                        <div style="font-size:0.78rem; color:#888;">{{ $camp->created_at->format('M j, Y g:ia') }}</div>
                                    </div>
                                    <span style="font-size:0.78rem; font-weight:700; padding:4px 10px; border-radius:12px; background:{{ $camp->status === 'sent' ? '#d1fae5' : ($camp->status === 'failed' ? '#fee2e2' : '#f3f4f6') }}; color:{{ $camp->status === 'sent' ? '#065f46' : ($camp->status === 'failed' ? '#b91c1c' : '#374151') }};">
                                        {{ ucfirst($camp->status) }} — {{ $camp->sent_count }}/{{ $camp->recipient_count }}
                                    </span>
                                </div>
                            @empty
                                <p style="color:#888; text-align:center; padding:16px;" id="no-campaigns-msg">No campaigns sent yet.</p>
                            @endforelse
                        </div>
                    </div>
                @else
                    <div style="background:linear-gradient(135deg, #FAF8FF, #f5f3ff); border:2px solid var(--primary); padding:28px; border-radius:16px; max-width:560px;">
                        <span style="background:var(--primary); color:white; font-size:0.75rem; font-weight:800; padding:4px 10px; border-radius:12px; text-transform:uppercase;">Pro Feature</span>
                        <h4 style="color:var(--dark-text); margin-top:10px; font-size:1.3rem;">Upgrade to Doughmain Pro ($29/month)</h4>
                        <p style="font-size:0.92rem; color:#555; margin-top:6px; margin-bottom:18px;">Build a subscriber list from your customers and send email offers, coupons, and announcements — included with Pro.</p>
                        <a href="https://buy.stripe.com/eVq00jeoj4aB62QanW2Ry0k?client_reference_id={{ $tenant->id }}&prefilled_email={{ urlencode($tenant->email ?? '') }}" target="_blank" style="background:linear-gradient(135deg, #6d28d9, #8b5cf6); color:#ffffff; font-weight:700; padding:12px 24px; border-radius:12px; text-align:center; box-shadow:0 4px 12px rgba(109,40,217,0.3); text-decoration:none; display:inline-block;">
                            Upgrade to Pro ($29/mo)
                        </a>
                    </div>
                @endif
            </div>

            <!-- TAB: Settings -->
            <div id="tab-settings" class="tab-content">
                <div class="section-header">
                    <h3>Settings</h3>
                    <p class="subtitle">Theme, logo, booking rules, and support.</p>
                </div>

                <!-- BAKERY LOGO MANAGEMENT CARD -->
                <div class="form-builder-card" style="border:2px solid var(--primary); background:var(--theme-section-bg, #f5f3ff); margin-bottom:20px;">
                    <h4 style="color:var(--dark-text); margin-bottom:6px;">Brand Logo</h4>
                    <p style="font-size:0.88rem; color:#666; margin-bottom:16px;">Shown in the header and footer across all your storefront pages.</p>

                    <div style="display:flex; align-items:center; gap:20px; flex-wrap:wrap;">
                        <div style="width:90px; height:90px; border-radius:16px; background:#ffffff; border:2px dashed var(--theme-section-bg, #c4b5fd); display:flex; align-items:center; justify-content:center; overflow:hidden; padding:6px;">
                            <img id="bakery-logo-preview" src="{{ $tenant->logo_path ? asset($tenant->logo_path) : asset('images/doughmain_logo.png') }}" alt="Bakery Logo" style="max-width:100%; max-height:100%; object-fit:contain;">
                        </div>
                        <div style="flex:1; min-width:240px;">
                            <form id="bakery-logo-form" onsubmit="uploadBakeryLogo(event)" style="display:flex; flex-direction:column; gap:10px;">
                                <input type="file" id="bakery-logo-file" name="logo" accept="image/*" required onchange="previewBakeryLogoFile(this)" style="font-size:0.88rem;">
                                <div style="display:flex; gap:10px; align-items:center;">
                                    <button type="submit" class="btn btn-primary" style="background:var(--primary); border-color:var(--primary);">
                                        Save Logo
                                    </button>
                                    <span id="logo-upload-status" style="font-size:0.85rem; font-weight:600; color:#059669; display:none;">Logo updated!</span>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- BUSINESS INFO & SEO CARD -->
                <div class="form-builder-card" style="border:2px solid var(--primary); background:var(--theme-section-bg, #f0fdf4); margin-bottom:20px;">
                    <h4 style="color:var(--dark-text); margin-bottom:6px;">Business Info &amp; SEO</h4>
                    <p style="font-size:0.88rem; color:#666; margin-bottom:16px;">Contact details shown on your storefront, plus the title &amp; description search engines show for your site.</p>

                    <div id="business-info-msg" style="display:none; margin-bottom:14px; background:var(--theme-section-bg, #d1fae5); color:var(--dark-text); padding:10px 14px; border-radius:10px; font-size:0.88rem; font-weight:600; border:1px solid var(--theme-section-bg, #a7f3d0);"></div>

                    <form id="business-info-form">
                        @csrf
                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:12px; margin-bottom:16px;">
                            <div>
                                <label style="font-weight:600; font-size:0.82rem; color:#555;">Business Hours</label>
                                <input type="text" name="contact_hours" value="{{ data_get($siteContent, 'contact_hours') }}" placeholder="Mon-Sat: 8:00 AM - 6:00 PM" style="width:100%; padding:9px; border-radius:8px; border:1px solid #ccc;">
                            </div>
                            <div>
                                <label style="font-weight:600; font-size:0.82rem; color:#555;">Service Area / Pickup Note</label>
                                <input type="text" name="contact_location" value="{{ data_get($siteContent, 'contact_location') }}" placeholder="Local Delivery & Pickup Available" style="width:100%; padding:9px; border-radius:8px; border:1px solid #ccc;">
                            </div>
                            <div>
                                <label style="font-weight:600; font-size:0.82rem; color:#555;">Phone</label>
                                <input type="text" name="phone" value="{{ $tenant->phone }}" style="width:100%; padding:9px; border-radius:8px; border:1px solid #ccc;">
                            </div>
                            <div>
                                <label style="font-weight:600; font-size:0.82rem; color:#555;">Address Line 1</label>
                                <input type="text" name="address_line1" value="{{ $tenant->address_line1 }}" style="width:100%; padding:9px; border-radius:8px; border:1px solid #ccc;">
                            </div>
                            <div>
                                <label style="font-weight:600; font-size:0.82rem; color:#555;">Address Line 2</label>
                                <input type="text" name="address_line2" value="{{ $tenant->address_line2 }}" style="width:100%; padding:9px; border-radius:8px; border:1px solid #ccc;">
                            </div>
                            <div>
                                <label style="font-weight:600; font-size:0.82rem; color:#555;">City</label>
                                <input type="text" name="city" value="{{ $tenant->city }}" style="width:100%; padding:9px; border-radius:8px; border:1px solid #ccc;">
                            </div>
                            <div>
                                <label style="font-weight:600; font-size:0.82rem; color:#555;">State</label>
                                <input type="text" name="state" value="{{ $tenant->state }}" style="width:100%; padding:9px; border-radius:8px; border:1px solid #ccc;">
                            </div>
                            <div>
                                <label style="font-weight:600; font-size:0.82rem; color:#555;">ZIP / Postal Code</label>
                                <input type="text" name="postal_code" value="{{ $tenant->postal_code }}" style="width:100%; padding:9px; border-radius:8px; border:1px solid #ccc;">
                            </div>
                            <div>
                                <label style="font-weight:600; font-size:0.82rem; color:#555;">Instagram URL</label>
                                <input type="text" name="instagram_url" value="{{ $tenant->instagram_url }}" placeholder="https://instagram.com/yourbakery" style="width:100%; padding:9px; border-radius:8px; border:1px solid #ccc;">
                            </div>
                            <div>
                                <label style="font-weight:600; font-size:0.82rem; color:#555;">Facebook URL</label>
                                <input type="text" name="facebook_url" value="{{ $tenant->facebook_url }}" placeholder="https://facebook.com/yourbakery" style="width:100%; padding:9px; border-radius:8px; border:1px solid #ccc;">
                            </div>
                        </div>

                        <div style="border-top:1px solid var(--theme-section-bg, #a7f3d0); padding-top:14px; margin-bottom:16px;">
                            <h5 style="font-size:0.9rem; color:var(--dark-text); margin-bottom:10px;">Search Engine (SEO)</h5>
                            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:12px;">
                                <div>
                                    <label style="font-weight:600; font-size:0.82rem; color:#555;">Page Title</label>
                                    <input type="text" name="seo_title" value="{{ data_get($siteContent, 'seo_title') }}" placeholder="{{ $tenant->name }} | Custom Cakes & Baked Goods" style="width:100%; padding:9px; border-radius:8px; border:1px solid #ccc;">
                                </div>
                                <div>
                                    <label style="font-weight:600; font-size:0.82rem; color:#555;">Meta Description</label>
                                    <input type="text" name="seo_description" value="{{ data_get($siteContent, 'seo_description') }}" placeholder="Custom cakes, cupcakes & desserts made fresh to order." style="width:100%; padding:9px; border-radius:8px; border:1px solid #ccc;">
                                </div>
                            </div>
                        </div>

                        <div style="border-top:1px solid var(--theme-section-bg, #a7f3d0); padding-top:14px; margin-bottom:16px;">
                            <h5 style="font-size:0.9rem; color:var(--dark-text); margin-bottom:4px;">Policy Page Numbers</h5>
                            <p style="font-size:0.8rem; color:#666; margin-bottom:10px;">Used on your <a href="{{ route('storefront.policy') }}" target="_blank" style="color:var(--primary); text-decoration:underline;">Policy page</a> — the rest of that page's wording is shared, but these numbers are yours to correct.</p>
                            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:12px;">
                                <div>
                                    <label style="font-weight:600; font-size:0.82rem; color:#555;">Deposit %</label>
                                    <input type="text" name="policy_deposit_percentage" value="{{ data_get($siteContent, 'policy_deposit_percentage', '50') }}" style="width:100%; padding:9px; border-radius:8px; border:1px solid #ccc;">
                                </div>
                                <div>
                                    <label style="font-weight:600; font-size:0.82rem; color:#555;">Late Fee %</label>
                                    <input type="text" name="policy_late_fee_percentage" value="{{ data_get($siteContent, 'policy_late_fee_percentage', '10') }}" style="width:100%; padding:9px; border-radius:8px; border:1px solid #ccc;">
                                </div>
                                <div>
                                    <label style="font-weight:600; font-size:0.82rem; color:#555;">Delivery Base Fee ($)</label>
                                    <input type="text" name="policy_delivery_base_fee" value="{{ data_get($siteContent, 'policy_delivery_base_fee', '30') }}" style="width:100%; padding:9px; border-radius:8px; border:1px solid #ccc;">
                                </div>
                                <div>
                                    <label style="font-weight:600; font-size:0.82rem; color:#555;">Delivery Rate ($/mile)</label>
                                    <input type="text" name="policy_delivery_per_mile" value="{{ data_get($siteContent, 'policy_delivery_per_mile', '2') }}" style="width:100%; padding:9px; border-radius:8px; border:1px solid #ccc;">
                                </div>
                                <div>
                                    <label style="font-weight:600; font-size:0.82rem; color:#555;">Delivery Change Fee ($)</label>
                                    <input type="text" name="policy_delivery_change_fee" value="{{ data_get($siteContent, 'policy_delivery_change_fee', '15') }}" style="width:100%; padding:9px; border-radius:8px; border:1px solid #ccc;">
                                </div>
                                <div>
                                    <label style="font-weight:600; font-size:0.82rem; color:#555;">Pickup Hours</label>
                                    <input type="text" name="policy_pickup_hours" value="{{ data_get($siteContent, 'policy_pickup_hours', '10:00am – 4:00pm') }}" style="width:100%; padding:9px; border-radius:8px; border:1px solid #ccc;">
                                </div>
                                <div>
                                    <label style="font-weight:600; font-size:0.82rem; color:#555;">Closed Days</label>
                                    <input type="text" name="policy_closed_days" value="{{ data_get($siteContent, 'policy_closed_days', 'Sundays or Mondays') }}" style="width:100%; padding:9px; border-radius:8px; border:1px solid #ccc;">
                                </div>
                                <div>
                                    <label style="font-weight:600; font-size:0.82rem; color:#555;">Extra Cake Layer Fee ($)</label>
                                    <input type="text" name="policy_extra_layer_fee" value="{{ data_get($siteContent, 'policy_extra_layer_fee', '20') }}" style="width:100%; padding:9px; border-radius:8px; border:1px solid #ccc;">
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-primary" onclick="saveBusinessInfoForm()" style="background:var(--primary); border-color:var(--primary);">Save Business Info & SEO</button>
                    </form>
                </div>

                <!-- CURATED BAKERY THEMES CARD -->
                <div class="form-builder-card" style="border:2px solid var(--primary); background:var(--theme-section-bg, #fff7fa);">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:12px;">
                        <div>
                            <h4 style="color:#5c1d37; margin:0;">Storefront Theme</h4>
                            <p style="font-size:0.88rem; color:#666; margin-top:4px;">Pick a design template. Colors and layout update automatically.</p>
                        </div>
                        <a href="{{ $tenant->publicUrl() }}" target="_blank" class="btn btn-outline btn-sm" style="font-weight:700; border-color:var(--primary); color:var(--primary);">View Live Storefront ↗</a>
                    </div>

                    <div id="theme-status-msg" style="display:none; margin-bottom:14px; background:#d4edda; color:#155724; padding:10px 14px; border-radius:10px; font-size:0.88rem; font-weight:600; border:1px solid #c3e6cb;"></div>

                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:20px;">
                        @php
                            $themes = $tenant->getAvailableThemesForTenant();
                            $starterThemeIds = ['rustic_kitchen', 'modern_bakery', 'country_farmhouse'];
                            // Free/starter themes first, Pro themes after - usort() is a
                            // stable sort as of PHP 8.0, so ties (both free or both pro)
                            // keep their original registry order.
                            usort($themes, fn($a, $b) => in_array($b['id'], $starterThemeIds) <=> in_array($a['id'], $starterThemeIds));
                            $currentTheme = $tenant->theme_id ?? 'sweet_elegant';
                        @endphp
                        @foreach($themes as $t)
                            @php
                                $isStarterTheme = in_array($t['id'], ['rustic_kitchen', 'modern_bakery', 'country_farmhouse']);
                                $isLockedTheme = ($tenant->plan_tier !== 'pro') && !$isStarterTheme;
                            @endphp
                            <div class="bakery-theme-card" 
                                 onclick="{{ $isLockedTheme ? "alert('Upgrade to Pro ($29/mo) to unlock this premium theme!')" : "selectBakeryTheme('".$t['id']."', this)" }}" 
                                 style="border:{{ $currentTheme === $t['id'] ? '3px solid var(--primary)' : '2px solid #ddd' }}; background:white; padding:22px; border-radius:14px; cursor:{{ $isLockedTheme ? 'not-allowed' : 'pointer' }}; position:relative; transition:transform 0.15s ease, border-color 0.15s ease; box-shadow:0 4px 12px rgba(0,0,0,0.05); {{ $isLockedTheme ? 'opacity:0.65; filter:grayscale(25%);' : '' }}">
                                <div style="height:80px; background:{{ $t['preview_bg'] }}; border-radius:10px; margin-bottom:12px; display:flex; align-items:center; justify-content:center; border:1px solid #eee;">
                                    <span style="font-weight:800; color:{{ $t['preview_accent'] }}; font-size:1.1rem;">{{ $t['name'] }}</span>
                                </div>
                                <h5 style="font-size:1rem; font-weight:700; color:#5c1d37; margin-bottom:4px;">{{ $t['name'] }}</h5>
                                <p style="font-size:0.8rem; color:#666; line-height:1.4;">{{ $t['subtitle'] }}</p>
                                @if($currentTheme === $t['id'])
                                    <span class="theme-badge" style="display:inline-block; margin-top:8px; font-size:0.75rem; background:var(--primary); color:white; padding:3px 10px; border-radius:20px; font-weight:700;">Active Theme</span>
                                @elseif($tenant->plan_tier === 'pro' && !$isStarterTheme)
                                    <span style="display:inline-block; margin-top:8px; font-size:0.75rem; background:#c7d2fe; color:#4338ca; padding:3px 10px; border-radius:20px; font-weight:700;">Pro Tier</span>
                                @elseif($isLockedTheme)
                                    <span style="display:inline-block; margin-top:8px; font-size:0.75rem; background:#fef3c7; color:#92400e; padding:3px 10px; border-radius:20px; font-weight:700;">Pro Only ($29/mo)</span>
                                @else
                                    <span style="display:inline-block; margin-top:8px; font-size:0.75rem; background:#d1fae5; color:#065f46; padding:3px 10px; border-radius:20px; font-weight:700;">Free Tier</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- BOOKING RULES CARD -->
                <div class="form-builder-card">
                    <h4>Order Lead Time</h4>
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
                            <span id="lead-time-save-msg" style="font-size:0.85rem; color:#28a745; display:none;">Saved!</span>
                        </div>
                    </div>
                </div>

                <!-- BAKER SUPPORT CARD -->
                <div class="form-builder-card" style="margin-top:20px; border:2px solid var(--primary); background:var(--theme-section-bg, #f5f3ff);">
                    <h4 style="color:var(--dark-text); margin-bottom:4px;">Support &amp; Custom Code Requests</h4>
                    <p style="font-size:0.88rem; color:#666; margin-bottom:14px;">Request custom features, theme tweaks, or code assistance (Pro Tier perk).</p>
                    <form id="support-request-form" style="display:flex; flex-direction:column; gap:12px;">
                        <div>
                            <label style="font-weight:700; font-size:0.85rem; color:var(--dark-text); display:block; margin-bottom:4px;">Subject</label>
                            <input type="text" class="form-control" placeholder="e.g. Custom theme tweak request" required style="width:100%; padding:10px 14px; border-radius:10px; border:1px solid #ddd;">
                        </div>
                        <div>
                            <label style="font-weight:700; font-size:0.85rem; color:var(--dark-text); display:block; margin-bottom:4px;">Description</label>
                            <textarea class="form-control" placeholder="Describe custom code or support request..." required style="width:100%; height:100px; padding:10px 14px; border-radius:10px; border:1px solid #ddd; font-family:inherit;"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="background:var(--primary); border-color:var(--primary); align-self:flex-start;">Send Support Request</button>
                    </form>
                </div>

                <!-- Subscription & Support was its own sidebar tab; merged in here to cut down the nav. -->
                <div class="section-header" style="margin-top:32px; border-top:1px solid #f0e4ea; padding-top:24px;">
                    <h3>Subscription &amp; Support</h3>
                    <p class="subtitle">Manage your plan and get help from Doughmain.pro.</p>
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
                                Update Password
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
                                <span style="font-weight:800; color:var(--primary); text-transform:uppercase;">{{ $tenant->plan_tier === 'pro' ? 'PRO ($29/mo)' : 'FREE ($0/mo)' }}</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-weight:600; color:#475569;">Account Status:</span>
                                <span style="color:#059669; font-weight:700;">● {{ $tenant->is_active ? 'Active' : 'Suspended/Canceled' }}</span>
                            </div>
                        </div>

                        @if(($tenant->plan_tier ?? 'free') !== 'pro')
                            <div style="background:linear-gradient(135deg, #FAF8FF, #f5f3ff); border:2px solid var(--primary); padding:20px; border-radius:14px; margin-bottom:16px;">
                                <span style="background:var(--primary); color:white; font-size:0.75rem; font-weight:800; padding:4px 10px; border-radius:12px; text-transform:uppercase;">Unlock All Features</span>
                                <h4 style="color:var(--dark-text); margin-top:8px; font-size:1.3rem;">Upgrade to Doughmain Pro ($29/month)</h4>
                                <p style="font-size:0.9rem; color:#555; margin-top:4px; margin-bottom:16px;">Unlock all 7 premium themes, custom domain support, and priority baker support.</p>

                    <a href="https://buy.stripe.com/eVq00jeoj4aB62QanW2Ry0k?client_reference_id={{ $tenant->id }}&prefilled_email={{ urlencode($tenant->email ?? '') }}" target="_blank" class="admin-nav-item" style="background:linear-gradient(135deg, #6d28d9, #8b5cf6) !important; color:#ffffff !important; font-weight:700; margin-top:12px; border-radius:12px; text-align:center; box-shadow:0 4px 12px rgba(109,40,217,0.3); text-decoration:none; display:block;">
                        Upgrade to Pro ($29/mo)
                    </a>


                            </div>
                        @endif
                        <form onsubmit="handleCancelSubscription(event)">
                            <button type="submit" class="btn" style="background:#ef4444; color:#fff; width:100%; padding:12px; font-weight:600; border-radius:10px; border:none; cursor:pointer;">
                                End Subscription / Cancel Account
                            </button>
                        </form>
                    </div>
                        @if(($tenant->plan_tier ?? 'free') == 'pro')

                    <!-- CUSTOM DOMAIN CARD -->
                    <div style="background:#ffffff; border-radius:16px; padding:24px; box-shadow:0 4px 15px rgba(0,0,0,0.05); border:1px solid #e2e8f0;">
                        <h4 style="font-size:1.2rem; font-weight:700; color:#1e293b; margin-bottom:12px;">Custom Domain Connection</h4>
                        <p style="font-size:0.9rem; color:#555; margin-bottom:18px;">If you&rsquo;re on Doughmain Pro, connect your own domain so your bakery appears on a branded address like <strong>blushedcrumbsbakehouse.com</strong>.</p>
                        <div style="display:flex; flex-direction:column; gap:14px;"
                             data-custom-domain-status="{{ $tenant->custom_domain_status ?? 'unverified' }}"
                             data-custom-domain-token="{{ $tenant->custom_domain_token ?? '' }}">
                            <input type="text" id="custom-domain-input" value="{{ $tenant->custom_domain ?? '' }}" placeholder="yourbakery.com" style="width:100%; padding:12px; border-radius:10px; border:1px solid #cbd5e1;">
                            <div style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
                                <button type="button" class="btn btn-primary" onclick="saveCustomDomain()" style="padding:12px 18px;">Save Domain</button>
                                <button type="button" class="btn btn-outline" onclick="verifyCustomDomain()" style="padding:12px 18px;">Verify DNS</button>
                                <span id="custom-domain-status" style="font-size:0.9rem; color:#475569;"></span>
                            </div>
                            <div id="custom-domain-txt-instructions" style="background:#fffbeb; border:1px solid #fde68a; border-radius:12px; padding:14px; {{ $tenant->custom_domain_token ? '' : 'display:none;' }}">
                                <p style="font-size:0.85rem; color:#334155; margin:0 0 8px 0; font-weight:600;">Step 1 — Prove you own this domain</p>
                                <p style="font-size:0.82rem; color:#475569; margin:0 0 8px 0;">Add this TXT record at your domain registrar (GoDaddy, Namecheap, etc.):</p>
                                <p style="font-size:0.8rem; color:#334155; margin:0;">Host: <code>_doughmain-verify</code></p>
                                <p style="font-size:0.8rem; color:#334155; margin:4px 0 0 0;">Value: <code id="custom-domain-txt-value">doughmain-verify={{ $tenant->custom_domain_token }}</code></p>
                            </div>
                            <div style="background:#f8fafc; border:1px solid #cbd5e1; border-radius:12px; padding:14px;">
                                <p style="font-size:0.85rem; color:#334155; margin:0 0 8px 0; font-weight:600;">Step 2 — Point the domain at us</p>
                                <ul style="font-size:0.85rem; color:#475569; line-height:1.6; margin:0; padding-left:18px;">
                                    <li><strong>www</strong> CNAME → <code>{{ $tenant->subdomain }}.doughmain.pro</code></li>
                                    <li><strong>@</strong> root A record → follow your registrar's instructions for root domains, or use their "ALIAS"/"ANAME" option pointed at the same address</li>
                                </ul>
                                <p style="font-size:0.82rem; color:#64748b; margin:10px 0 0 0;">After adding both records, click Verify DNS. This checks in the background and can take a few minutes — DNS changes aren't always instant.</p>
                            </div>
                        </div>
                    </div>
@endif
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
                                <textarea id="ticket_message" name="message" required rows="4" class="form-input" placeholder="Tell our support team how we can assist your bakery..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary" style="width:100%; padding:12px; font-weight:700; border-radius:10px;">
                                Submit Support Ticket
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- TAB: Calendar & Availability Manager -->
            <div id="tab-calendar" class="tab-content">
                <div class="section-header">
                    <h3>Availability &amp; Blackouts</h3>
                    <p class="subtitle">Set weekly closed days and block off specific dates.</p>
                </div>

                <!-- CARD 1: RECURRING WEEKLY CLOSED DAYS -->
                <div class="form-builder-card" style="border:2px solid var(--primary); background:var(--theme-section-bg, #fff7fa);">
                    <h4 style="color:#5c1d37;">Weekly Recurring Closed Days</h4>
                    <p style="font-size:0.88rem; color:#666; margin-bottom:16px;">Days you're regularly closed (e.g. Saturdays &amp; Sundays) are automatically blocked on the order form calendar.</p>

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
                        <button class="btn btn-primary" onclick="saveRecurringClosedDays()">Save Recurring Schedule</button>
                        <span id="recurring-save-msg" style="font-size:0.85rem; color:#28a745; display:none;">Saved!</span>
                    </div>
                </div>

                <!-- CARD 2: INTERACTIVE CALENDAR DATE BLACKOUT MANAGER -->
                <div class="form-builder-card">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px; margin-bottom:20px;">
                        <div>
                            <h4 style="margin-bottom:4px;">Date Blackout Calendar</h4>
                            <p style="font-size:0.85rem; color:#888; margin:0;">Click any date below to toggle it blocked or available.</p>
                        </div>
                        <!-- Month Navigation -->
                        <div style="display:flex; align-items:center; gap:10px; background:#fff0f5; padding:6px 14px; border-radius:14px; border:1px solid var(--theme-section-bg, #f8c6d7);">
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
                        <button class="btn btn-primary" onclick="addManualBlockedDate()">Block Date</button>
                    </div>
                </div>

                <!-- CARD 3: LIST OF CURRENTLY BLOCKED DATES -->
                <div class="form-builder-card">
                    <h4>Currently Blocked Custom Dates</h4>
                    <p style="font-size:0.85rem; color:#666; margin-bottom:14px;">Blacked out for client orders:</p>
                    <div id="admin-blocked-dates-list" style="display:flex; flex-wrap:wrap; gap:10px;">
                        @forelse($serverBookingSettings['blocked_dates'] ?? ['2026-07-04', '2026-07-25'] as $bDate)
                            <div class="blocked-date-badge">
                                <span>{{ $bDate }}</span>
                                <button title="Unblock Date" onclick="removeBlockedDate('{{ $bDate }}')">✕</button>
                            </div>
                        @empty
                            <span style="color:#aaa; font-size:0.9rem;">No custom blocked dates added yet. Click any calendar date above or use the manual input!</span>
                        @endforelse
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

            let customDomainPollTimer = null;

            function showCustomDomainTxtRecord(token) {
                const box = document.getElementById('custom-domain-txt-instructions');
                const valueEl = document.getElementById('custom-domain-txt-value');
                if (token) {
                    valueEl.innerText = 'doughmain-verify=' + token;
                    box.style.display = '';
                } else {
                    box.style.display = 'none';
                }
            }

            async function saveCustomDomain() {
                const input = document.getElementById('custom-domain-input');
                const statusEl = document.getElementById('custom-domain-status');
                const domain = input?.value?.trim();
                if (!domain) {
                    alert('Enter a custom domain before saving.');
                    return;
                }

                try {
                    const res = await fetch('/dashboard/settings/domain', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ custom_domain: domain })
                    });

                    const data = await res.json();
                    if (data.success) {
                        statusEl.innerText = data.message;
                        statusEl.style.color = '#047857';
                        showCustomDomainTxtRecord(data.verification_token);
                    } else {
                        statusEl.innerText = data.message || 'Unable to save custom domain.';
                        statusEl.style.color = '#b91c1c';
                    }
                } catch (err) {
                    console.error(err);
                    statusEl.innerText = 'Error saving domain.';
                    statusEl.style.color = '#b91c1c';
                }
            }

            async function verifyCustomDomain() {
                const statusEl = document.getElementById('custom-domain-status');

                statusEl.innerText = 'Verification queued…';
                statusEl.style.color = '#2563eb';

                try {
                    const res = await fetch('/dashboard/settings/domain/verify', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    });

                    const data = await res.json();
                    statusEl.innerText = data.message || (data.success ? 'Verification queued.' : 'Could not queue verification.');
                    statusEl.style.color = data.success ? '#2563eb' : '#b91c1c';

                    if (data.success) {
                        pollCustomDomainStatus();
                    }
                } catch (err) {
                    console.error(err);
                    statusEl.innerText = 'Error queuing verification.';
                    statusEl.style.color = '#b91c1c';
                }
            }

            async function pollCustomDomainStatus() {
                const statusEl = document.getElementById('custom-domain-status');
                if (customDomainPollTimer) {
                    clearInterval(customDomainPollTimer);
                }

                customDomainPollTimer = setInterval(async () => {
                    try {
                        const res = await fetch('/dashboard/settings/domain/status', {
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await res.json();

                        if (data.status === 'verified') {
                            statusEl.innerText = 'Domain verified and live!';
                            statusEl.style.color = '#047857';
                            clearInterval(customDomainPollTimer);
                        } else if (data.status === 'failed') {
                            statusEl.innerText = data.last_error || 'Verification failed — check your DNS settings.';
                            statusEl.style.color = '#b91c1c';
                            clearInterval(customDomainPollTimer);
                        }
                        // still 'pending' -> keep polling
                    } catch (err) {
                        console.error(err);
                    }
                }, 15000);
            }

            document.addEventListener('DOMContentLoaded', function () {
                const domainCard = document.querySelector('[data-custom-domain-status]');
                if (domainCard && domainCard.dataset.customDomainStatus === 'pending') {
                    pollCustomDomainStatus();
                }
            });

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
                        statusEl.innerText = 'Logo saved!';
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
            function initQuillMenuEditor() {
                const el = document.getElementById('quill-menu-editor-container');
                if (el && !quillMenuEditor) {
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
                    quillMenuEditor.on('text-change', function() {
                        const html = quillMenuEditor.root.innerHTML;
                        const cleanText = quillMenuEditor.getText().trim();
                        document.getElementById('admin_menu_text').value = cleanText ? html : '';
                    });
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initQuillMenuEditor);
            } else {
                initQuillMenuEditor();
            }

            let activeGalleryPickerTarget = null;
            let activeGalleryPickerPreview = null;
            let galleryPickerMode = 'single'; // 'single' (attach one photo to an input) or 'multi' (Featured Photos)
            let featuredGallerySelection = [];

            (function initFeaturedGallerySelection() {
                const hidden = document.getElementById('featured_gallery_images_input');
                try {
                    featuredGallerySelection = hidden ? (JSON.parse(hidden.value || '[]') || []) : [];
                } catch (e) {
                    featuredGallerySelection = [];
                }
                renderFeaturedPreviewStrip();
            })();

            function openGalleryPicker(targetInput, previewElId = null) {
                galleryPickerMode = 'single';
                if (typeof targetInput === 'string') {
                    activeGalleryPickerTarget = document.getElementById(targetInput);
                } else {
                    activeGalleryPickerTarget = targetInput;
                }
                activeGalleryPickerPreview = previewElId ? document.getElementById(previewElId) : null;
                setGalleryPickerFooterMode('single');
                const modal = document.getElementById('gallery-picker-modal');
                if (modal) {
                    modal.style.display = 'flex';
                }
            }

            function openFeaturedGalleryPicker() {
                galleryPickerMode = 'multi';
                setGalleryPickerFooterMode('multi');
                document.querySelectorAll('.gallery-picker-item').forEach(el => {
                    const isSelected = featuredGallerySelection.some(i => i.path === el.dataset.path);
                    el.classList.toggle('picker-selected', isSelected);
                });
                const modal = document.getElementById('gallery-picker-modal');
                if (modal) {
                    modal.style.display = 'flex';
                }
            }

            function setGalleryPickerFooterMode(mode) {
                const doneBtn = document.getElementById('gallery-picker-done-btn');
                const clearBtn = document.getElementById('gallery-picker-clear-btn');
                const hint = document.getElementById('gallery-picker-multi-hint');
                if (doneBtn) doneBtn.style.display = mode === 'multi' ? 'inline-block' : 'none';
                if (clearBtn) clearBtn.style.display = mode === 'multi' ? 'none' : 'inline-block';
                if (hint) hint.style.display = mode === 'multi' ? 'block' : 'none';
            }

            function closeGalleryPickerModal() {
                const modal = document.getElementById('gallery-picker-modal');
                if (modal) modal.style.display = 'none';
                galleryPickerMode = 'single';
                setGalleryPickerFooterMode('single');
                document.querySelectorAll('.gallery-picker-item').forEach(el => el.classList.remove('picker-selected'));
            }

            function selectGalleryPickerImage(fullAssetUrl, relativePath) {
                if (activeGalleryPickerTarget) {
                    activeGalleryPickerTarget.value = relativePath;
                    activeGalleryPickerTarget.dispatchEvent(new Event('change'));
                }
                if (activeGalleryPickerPreview) {
                    if (fullAssetUrl) {
                        activeGalleryPickerPreview.style.display = 'flex';
                        const img = activeGalleryPickerPreview.querySelector('img');
                        if (img) img.src = fullAssetUrl;
                    } else {
                        activeGalleryPickerPreview.style.display = 'none';
                    }
                }
                closeGalleryPickerModal();
            }

            function handleGalleryPickerItemClick(el, fullAssetUrl, relativePath, title) {
                if (galleryPickerMode === 'multi') {
                    const idx = featuredGallerySelection.findIndex(i => i.path === relativePath);
                    if (idx > -1) {
                        featuredGallerySelection.splice(idx, 1);
                        el.classList.remove('picker-selected');
                    } else {
                        featuredGallerySelection.push({ path: relativePath, title: title || '' });
                        el.classList.add('picker-selected');
                    }
                } else {
                    selectGalleryPickerImage(fullAssetUrl, relativePath);
                }
            }

            function confirmFeaturedGallerySelection() {
                const hidden = document.getElementById('featured_gallery_images_input');
                if (hidden) hidden.value = JSON.stringify(featuredGallerySelection);
                renderFeaturedPreviewStrip();
                closeGalleryPickerModal();
            }

            function renderFeaturedPreviewStrip() {
                const wrap = document.getElementById('featured-gallery-preview-strip');
                if (!wrap) return;
                if (featuredGallerySelection.length === 0) {
                    wrap.innerHTML = '<p style="color:#888; font-size:0.85rem; margin:0;">No featured photos selected yet.</p>';
                    return;
                }
                wrap.innerHTML = featuredGallerySelection.map((img, i) => `
                    <div class="featured-preview-thumb" style="position:relative; width:80px; height:80px;">
                        <img src="${location.origin}/${img.path}" style="width:100%; height:100%; object-fit:cover; border-radius:8px; border:1px solid #ddd;">
                        <button type="button" onclick="removeFeaturedPreviewItem(${i})" title="Remove" style="position:absolute; top:-6px; right:-6px; background:#dc2626; color:white; border:none; border-radius:50%; width:20px; height:20px; font-size:11px; cursor:pointer; line-height:1;">✕</button>
                    </div>
                `).join('');
            }

            function removeFeaturedPreviewItem(i) {
                featuredGallerySelection.splice(i, 1);
                const hidden = document.getElementById('featured_gallery_images_input');
                if (hidden) hidden.value = JSON.stringify(featuredGallerySelection);
                renderFeaturedPreviewStrip();
            }

            function addAccordionCategoryItem() {
                const list = document.getElementById('accordion-categories-list');
                if (!list) return;
                const idx = list.querySelectorAll('.accordion-category-item').length;
                const div = document.createElement('div');
                div.className = 'accordion-category-item';
                div.style.cssText = 'padding:16px; border-radius:10px; border:1px solid #eee; display:flex; flex-direction:column; gap:10px;';
                div.innerHTML = `
                    <div style="display:flex; justify-content:space-between; align-items:center; gap:10px;">
                        <input type="text" name="categories[${idx}][title]" placeholder="Category Title (e.g. Gourmet Cupcakes)" style="flex:1; padding:8px 12px; border-radius:8px; border:1px solid #ccc; font-weight:700; font-size:0.95rem;">
                        <button type="button" class="btn btn-sm btn-outline" onclick="this.closest('.accordion-category-item').remove()" style="color:#dc2626; border-color:#fca5a5; padding:4px 10px; font-size:0.8rem;">Delete</button>
                    </div>
                    <div>
                        <label style="font-size:0.8rem; font-weight:600; color:#555; display:block; margin-bottom:4px;">Short Description</label>
                        <input type="text" name="categories[${idx}][desc]" placeholder="Category Description..." style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid #ccc; font-size:0.88rem;">
                    </div>
                    <div>
                        <label style="font-size:0.8rem; font-weight:600; color:#555; display:block; margin-bottom:4px;">Category Image</label>
                        <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                            <input type="text" id="cat_img_input_${idx}" name="categories[${idx}][image_url]" placeholder="Select photo or upload..." style="flex:1; padding:8px; border-radius:8px; border:1px solid #ccc; font-size:0.85rem;">
                            <button type="button" class="btn btn-sm btn-outline" onclick="openGalleryPicker(document.getElementById('cat_img_input_${idx}'), 'cat_preview_${idx}')" style="border-color:var(--primary); color:var(--dark-text); font-size:0.8rem; font-weight:700;">Device Gallery</button>
                            <label class="btn btn-sm btn-outline" style="cursor:pointer; padding:6px 12px; border-color:var(--primary); color:var(--dark-text); font-size:0.8rem;">
                                Upload File
                                <input type="file" name="category_image_${idx}" accept="image/*" style="display:none;" onchange="uploadSectionMedia(this, 'cat_img_input_${idx}', 'cat_preview_${idx}')">
                            </label>
                        </div>
                        <div id="cat_preview_${idx}" style="margin-top:8px; display:none; align-items:center; gap:8px;">
                            <img src="" style="width:38px; height:38px; object-fit:cover; border-radius:6px; border:1px solid #ddd;">
                            <span style="font-size:0.78rem; color:#15803d; font-weight:600;">Photo attached</span>
                        </div>
                    </div>
                `;
                list.appendChild(div);
            }

            function clearMenuQuillEditor() {
                if (quillMenuEditor) {
                    quillMenuEditor.setText('');
                }
                document.getElementById('admin_menu_text').value = '';
            }

            // Terms & Policy Text WYSIWYG Edit Modal
            let quillTermsModalEditor = null;
            let quillTermsEditingIdx = null;

            function initQuillTermsModalEditor() {
                const el = document.getElementById('quill-terms-modal-editor');
                if (el && !quillTermsModalEditor) {
                    quillTermsModalEditor = new Quill('#quill-terms-modal-editor', {
                        theme: 'snow',
                        placeholder: 'Enter your custom terms, deposit rules, cancellation policy, etc...',
                        modules: {
                            toolbar: [
                                [{ 'header': [2, 3, false] }],
                                ['bold', 'italic', 'underline'],
                                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                                ['link'],
                                ['clean']
                            ]
                        }
                    });
                }
            }

            window.openTermsEditModal = function(idx) {
                initQuillTermsModalEditor();
                quillTermsEditingIdx = idx;
                quillTermsModalEditor.root.innerHTML = window._customFields[idx]?.description || '';
                const modal = document.getElementById('terms-edit-modal');
                if (modal) modal.style.display = 'flex';
            };

            window.closeTermsEditModal = function() {
                const modal = document.getElementById('terms-edit-modal');
                if (modal) modal.style.display = 'none';
                quillTermsEditingIdx = null;
            };

            window.clearTermsEditModal = function() {
                if (quillTermsModalEditor) {
                    quillTermsModalEditor.setText('');
                }
            };

            window.saveTermsEditModal = function() {
                if (quillTermsEditingIdx === null || !quillTermsModalEditor) return;
                const cleanText = quillTermsModalEditor.getText().trim();
                const html = quillTermsModalEditor.root.innerHTML;
                window._customFields[quillTermsEditingIdx].description = cleanText ? html : '';
                if (typeof renderFieldsTable === 'function') {
                    renderFieldsTable();
                } else if (typeof window.renderFieldsTable === 'function') {
                    window.renderFieldsTable();
                }
                closeTermsEditModal();

                // Persist immediately — editing text in this modal should not
                // require a separate click on "Save Order Form Layout Live".
                if (typeof window.saveFormSchemaToServer === 'function') {
                    window.saveFormSchemaToServer();
                }
            };

            // Guided Admin Tour (5 steps: Form Builder, Products, Gallery, Invoices, Settings)
            const ADMIN_TOUR_STORAGE_KEY = 'doughmain_admin_tour_seen_{{ $tenant->id }}';
            const ADMIN_TOUR_STEPS = [
                {
                    icon: '📋',
                    title: 'Order Form',
                    body: "This is where you build your custom order form. Add steps for cake sizes, flavors, frosting, terms &amp; conditions, and more — drag rows to reorder, then click \"Save Order Form Layout\" to publish instantly to your storefront."
                },
                {
                    icon: '🎂',
                    title: 'Products',
                    body: 'Add every cake, cookie, or treat you sell here with pricing and categories. These show up in your order form\'s product catalog step for customers to choose from.'
                },
                {
                    icon: '📷',
                    title: 'Device Gallery',
                    body: 'Upload photos of your best work straight from your phone or computer. They publish live to your public Gallery page and can be reused as category or section images elsewhere on your site.'
                },
                {
                    icon: '💳',
                    title: 'Invoices &amp; Payments',
                    body: 'Every order automatically generates an invoice here. Track deposits, send reminders, and manage the payment methods (Venmo, CashApp, Stripe, etc.) your customers can pay with.'
                },
                {
                    icon: '🔧',
                    title: 'Settings',
                    body: 'Your control room: business info, email routing, custom domain setup, and subscription plan. Come back here anytime to update how your bakery site runs.'
                }
            ];
            let tourStepIndex = 0;

            function renderAdminTourStep() {
                const step = ADMIN_TOUR_STEPS[tourStepIndex];
                document.getElementById('tour-step-icon').innerText = step.icon;
                document.getElementById('tour-step-title').innerHTML = step.title;
                document.getElementById('tour-step-body').innerHTML = step.body;

                const dotsEl = document.getElementById('tour-step-dots');
                dotsEl.innerHTML = ADMIN_TOUR_STEPS.map((_, i) =>
                    `<span style="width:8px; height:8px; border-radius:50%; background:${i === tourStepIndex ? 'var(--primary)' : 'var(--theme-section-bg, #e9d5ff)'}; display:inline-block; transition:background 0.2s;"></span>`
                ).join('');

                document.getElementById('tour-back-btn').style.visibility = tourStepIndex === 0 ? 'hidden' : 'visible';
                document.getElementById('tour-next-btn').innerText = (tourStepIndex === ADMIN_TOUR_STEPS.length - 1) ? "Got it!" : 'Next →';
            }

            window.openAdminTour = function() {
                tourStepIndex = 0;
                renderAdminTourStep();
                document.getElementById('admin-tour-modal').style.display = 'flex';
            };

            window.nextAdminTourStep = function() {
                if (tourStepIndex < ADMIN_TOUR_STEPS.length - 1) {
                    tourStepIndex++;
                    renderAdminTourStep();
                } else {
                    finishAdminTour();
                }
            };

            window.prevAdminTourStep = function() {
                if (tourStepIndex > 0) {
                    tourStepIndex--;
                    renderAdminTourStep();
                }
            };

            function finishAdminTour() {
                document.getElementById('admin-tour-modal').style.display = 'none';
                try { localStorage.setItem(ADMIN_TOUR_STORAGE_KEY, '1'); } catch (e) {}
            }

            window.skipAdminTour = finishAdminTour;

            // Auto-launch once per baker on first visit
            (function autoLaunchAdminTour() {
                let alreadySeen = false;
                try { alreadySeen = !!localStorage.getItem(ADMIN_TOUR_STORAGE_KEY); } catch (e) {}
                if (!alreadySeen) {
                    setTimeout(() => window.openAdminTour(), 700);
                }
            })();

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
                const menuTextInput = document.getElementById('admin_menu_text');
                if (menuTextInput) {
                    formData.set('menu_text', menuTextInput.value);
                }

                const btn = form.querySelector('button[type="submit"]');

                btn.disabled = true;
                btn.innerText = 'Saving Menu Settings...';

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
                        alert(data.message);
                    } else {
                        alert(data.message || 'Error saving menu settings.');
                    }
                } catch(err) {
                    console.error(err);
                    alert('An error occurred while saving menu settings.');
                } finally {
                    btn.disabled = false;
                    btn.innerText = 'Save Menu & Pricing Settings';
                }
            }
        </script>

    <!-- INVOICE EDIT / CREATION MODAL -->
<div id="invoice-edit-modal" class="order-modal-overlay" style="display:none; z-index:9999;">
    <div class="order-modal-card" style="max-width: 500px; width:90%;">
        <div class="order-modal-header" style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #eee; padding-bottom:12px; margin-bottom:16px;">
            <h2 style="font-size:1.25rem; font-family:'Outfit',sans-serif; color:#5c1d37; margin:0;">Invoice Details</h2>
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
                    <button type="button" class="btn btn-outline" onclick="saveInvoiceEdits()" style="border-color:var(--primary); color:var(--primary);">Save Invoice</button>
                    <button type="button" class="btn btn-primary" onclick="saveAndSendInvoice()">Save &amp; Send</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DEVICE GALLERY MEDIA PICKER MODAL -->
<div id="gallery-picker-modal" class="order-modal-overlay" style="display:none; z-index:99999;">
    <div class="order-modal-card" style="max-width: 650px; width:92%; max-height:85vh; display:flex; flex-direction:column; background:#ffffff; border-radius:16px; border:2px solid var(--primary); padding:20px; box-shadow:0 20px 50px rgba(109,40,217,0.2);">
        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--theme-section-bg, #e9d5ff); padding-bottom:12px; margin-bottom:16px;">
            <div>
                <h3 style="margin:0; color:var(--dark-text); font-size:1.2rem; font-family:'Outfit',sans-serif;">Device Gallery Media Picker</h3>
                <p style="margin:2px 0 0 0; font-size:0.82rem; color:#666;">Click any photo thumbnail to attach it to this section.</p>
            </div>
            <button type="button" class="btn btn-outline" style="border:none; font-size:1.2rem; cursor:pointer;" onclick="closeGalleryPickerModal()">✕</button>
        </div>
        
        <div id="gallery-picker-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(130px, 1fr)); gap:12px; overflow-y:auto; padding-right:6px; flex:1; max-height:50vh;">
            @forelse($gallery as $gItem)
                @php $gSrc = $gItem->image_url ?? $gItem->image_path; @endphp
                <div class="gallery-picker-item" data-path="{{ $gSrc }}" onclick="handleGalleryPickerItemClick(this, @js(asset($gSrc)), @js($gSrc), @js($gItem->title))" style="cursor:pointer; border:2px solid var(--theme-section-bg, #e9d5ff); border-radius:10px; overflow:hidden; background:#ffffff; transition:all 0.2s ease; text-align:center; padding:6px; position:relative;">
                    <span class="gallery-picker-checkmark" style="display:none; position:absolute; top:4px; right:4px; background:#16a34a; color:white; width:22px; height:22px; border-radius:50%; align-items:center; justify-content:center; font-size:0.8rem; font-weight:800; z-index:2;">✓</span>
                    <img src="{{ asset($gSrc) }}" style="width:100%; height:90px; object-fit:cover; border-radius:6px; margin-bottom:4px;">
                    <strong style="font-size:0.75rem; color:var(--dark-text); display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $gItem->title }}</strong>
                    <span style="font-size:0.7rem; color:var(--primary);">{{ $gItem->category }}</span>
                </div>
            @empty
                <div style="grid-column:1 / -1; text-align:center; padding:30px; color:#666;">
                    <p style="margin:0; font-weight:600;">No images in Device Gallery yet.</p>
                    <p style="font-size:0.8rem; color:#888;">Upload photos under the <strong>Device Gallery</strong> sidebar tab first or upload directly below.</p>
                </div>
            @endforelse
        </div>

        <div style="margin-top:16px; border-top:1px solid var(--theme-section-bg, #e9d5ff); padding-top:12px; display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
            <button type="button" id="gallery-picker-clear-btn" class="btn btn-outline" onclick="selectGalleryPickerImage('', '')" style="color:#dc2626; border-color:#fca5a5; font-size:0.82rem;">Clear Selection (Theme Default)</button>
            <div id="gallery-picker-multi-hint" style="display:none; font-size:0.82rem; color:var(--dark-text); font-weight:600;">Click photos to select or deselect them, then hit Done.</div>
            <div style="display:flex; gap:8px;">
                <button type="button" id="gallery-picker-done-btn" class="btn btn-primary" style="display:none; background:var(--primary); border-color:var(--primary);" onclick="confirmFeaturedGallerySelection()">Done — Use Selected Photos</button>
                <button type="button" class="btn btn-outline" onclick="closeGalleryPickerModal()">Cancel</button>
            </div>
        </div>
    </div>
</div>

<style>
    .gallery-picker-item.picker-selected {
        border-color: #16a34a !important;
        box-shadow: 0 0 0 2px rgba(22, 163, 74, 0.25);
    }
    .gallery-picker-item.picker-selected .gallery-picker-checkmark {
        display: flex !important;
    }
</style>

<!-- TERMS & POLICY TEXT WYSIWYG EDIT MODAL -->
<style>
    #terms-edit-modal .ql-editor {
        min-height: 220px;
        max-height: 42vh;
        overflow-y: auto;
    }
</style>
<div id="terms-edit-modal" class="order-modal-overlay" style="display:none; z-index:99999;">
    <div class="order-modal-card" style="max-width: 650px; width:92%; max-height:85vh; overflow-y:auto; display:flex; flex-direction:column; background:#ffffff; border-radius:16px; border:2px solid var(--primary); padding:20px; box-shadow:0 20px 50px rgba(230,115,153,0.2);">
        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--theme-section-bg, #f8c6d7); padding-bottom:12px; margin-bottom:16px;">
            <div>
                <h3 style="margin:0; color:#5c1d37; font-size:1.2rem; font-family:'Outfit',sans-serif;">Edit Terms &amp; Policy Text</h3>
                <p style="margin:2px 0 0 0; font-size:0.82rem; color:#666;">Leave blank to show the default message: <em>"Please consult the bakery directly for their order policies and terms."</em></p>
            </div>
            <button type="button" class="btn btn-outline" style="border:none; font-size:1.2rem; cursor:pointer;" onclick="closeTermsEditModal()">✕</button>
        </div>

        <div id="quill-terms-modal-editor" style="background:#ffffff; border-radius:0 0 8px 8px; font-size:0.95rem;"></div>

        <div style="margin-top:16px; border-top:1px solid var(--theme-section-bg, #f8c6d7); padding-top:12px; display:flex; justify-content:space-between; align-items:center; flex-shrink:0;">
            <button type="button" class="btn btn-outline" onclick="clearTermsEditModal()" style="color:#dc2626; border-color:#fca5a5; font-size:0.82rem;">Clear (Use Default)</button>
            <div style="display:flex; gap:10px;">
                <button type="button" class="btn btn-outline" onclick="closeTermsEditModal()">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveTermsEditModal()">Save Text</button>
            </div>
        </div>
    </div>
</div>

<!-- GUIDED ADMIN TOUR MODAL -->
<div id="admin-tour-modal" class="order-modal-overlay" style="display:none; z-index:100000;">
    <div class="order-modal-card" style="max-width: 480px; width:92%; background:#ffffff; border-radius:18px; border:2px solid var(--primary); padding:26px; box-shadow:0 20px 50px rgba(109,40,217,0.25); text-align:center;">
        <div id="tour-step-icon" style="font-size:2.6rem; margin-bottom:10px;"></div>
        <h3 id="tour-step-title" style="margin:0 0 12px 0; color:var(--dark-text); font-family:'Outfit',sans-serif; font-size:1.3rem;"></h3>
        <p id="tour-step-body" style="color:#555; font-size:0.95rem; line-height:1.6; margin-bottom:20px;"></p>

        <div id="tour-step-dots" style="display:flex; justify-content:center; gap:8px; margin-bottom:22px;"></div>

        <div style="display:flex; justify-content:space-between; align-items:center; gap:10px;">
            <button type="button" class="btn btn-outline" onclick="skipAdminTour()" style="font-size:0.82rem; color:#888; border-color:#ddd;">Skip Tour</button>
            <div style="display:flex; gap:10px;">
                <button type="button" class="btn btn-outline" id="tour-back-btn" onclick="prevAdminTourStep()">← Back</button>
                <button type="button" class="btn btn-primary" id="tour-next-btn" onclick="nextAdminTourStep()" style="background:var(--primary); border:none;">Next →</button>
            </div>
        </div>
    </div>
</div>

@endsection
