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
    $newInquiriesCount = $urgentOrders->where('status', 'new')->count();
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

            <nav class="admin-sidebar-nav" style="gap: 5px;">
                <div class="sidebar-category-title">Operations</div>
                <button class="admin-nav-item active" data-tab="tab-orders">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="sidebar-icon"><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path></svg>
                    <span>Orders</span>
                    @if($newInquiriesCount > 0)<span class="nav-inquiries-badge">{{ $newInquiriesCount }}</span>@endif
                </button>
                <button class="admin-nav-item" data-tab="tab-invoices">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="sidebar-icon"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                    <span>Invoices &amp; Payments</span>
                </button>
                <button class="admin-nav-item" data-tab="tab-calendar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="sidebar-icon"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    <span>Availability &amp; Dates</span>
                </button>

                <div class="sidebar-category-title" style="margin-top: 14px;">Catalog &amp; Storefront</div>
                <button class="admin-nav-item" data-tab="tab-products">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="sidebar-icon"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5"></path><path d="M2 12l10 5 10-5"></path></svg>
                    <span>Products &amp; Menu</span>
                </button>
                <button class="admin-nav-item" data-tab="tab-form-builder">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="sidebar-icon"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    <span>Order Form Builder</span>
                </button>
                <button class="admin-nav-item" data-tab="tab-page-builder">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="sidebar-icon"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                    <span>Page Builder</span>
                </button>
                <button class="admin-nav-item" data-tab="tab-gallery-manager">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="sidebar-icon"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                    <span>Device Gallery</span>
                </button>

                <div class="sidebar-category-title" style="margin-top: 14px;">Growth &amp; Engagement</div>
                <button class="admin-nav-item" data-tab="tab-reviews">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="sidebar-icon"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                    <span>Client Reviews</span>
                </button>
                <button class="admin-nav-item" data-tab="tab-email-marketing">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="sidebar-icon"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                    <span>Email Marketing</span>
                    @if(($tenant->plan_tier ?? 'free') !== 'pro')
                        <span style="font-size:0.62rem; font-weight:800; background:rgba(255,255,255,0.25); padding:2px 6px; border-radius:10px; margin-left:auto;">PRO</span>
                    @endif
                </button>

                <div class="sidebar-category-title" style="margin-top: 14px;">Configuration</div>
                <button class="admin-nav-item" data-tab="tab-settings">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="sidebar-icon"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                    <span>Settings</span>
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
            @if($onboardingNeedsAttention ?? null)
                <div style="background:#fef3c7; border:1px solid #f59e0b; color:#92400e; padding:14px 20px; border-radius:12px; margin-bottom:20px; display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span style="font-size:1.3rem;">⚠️</span>
                        <span style="font-weight:600; font-size:0.92rem;">{{ $onboardingNeedsAttention }}</span>
                    </div>
                    <a href="{{ route('onboarding.v2.wizard', $latestOnboardingDraft ? ['draft' => $latestOnboardingDraft->id] : []) }}" style="background:#92400e; color:#fff; padding:8px 16px; border-radius:8px; font-weight:700; text-decoration:none; white-space:nowrap; font-size:0.85rem;">Finish Setup →</a>
                </div>
            @endif
            <!-- TAB 1: Orders -->
            <div id="tab-orders" class="tab-content active">
                <style>
                    /* --- Dashboard UI/UX Optimizations --- */
                    .collapsible-card {
                        transition: box-shadow 0.2s ease, border-color 0.2s ease;
                        margin-bottom: 16px;
                    }
                    .collapsible-card.expanded {
                        border-color: var(--primary) !important;
                        box-shadow: 0 10px 30px rgba(230, 115, 153, 0.15) !important;
                    }
                    .chevron-indicator {
                        font-size: 0.85rem;
                        color: var(--primary);
                        transition: transform 0.2s ease;
                        display: inline-block;
                        width: 16px;
                        text-align: center;
                        margin-right: 4px;
                    }
                    .allergy-pinned-badge {
                        background: #fee2e2;
                        color: #ef4444;
                        font-size: 0.72rem;
                        font-weight: 700;
                        padding: 3px 8px;
                        border-radius: 6px;
                        border: 1px solid #fca5a5;
                        animation: pulseAlert 2s infinite;
                        display: inline-block;
                    }
                    @keyframes pulseAlert {
                        0% { opacity: 1; }
                        50% { opacity: 0.75; }
                        100% { opacity: 1; }
                    }
                    .nav-inquiries-badge {
                        background: #ef4444;
                        color: white;
                        font-size: 0.72rem;
                        font-weight: 700;
                        padding: 2px 7px;
                        border-radius: 20px;
                        margin-left: 8px;
                        display: inline-block;
                    }
                    .header-inquiries-badge {
                        background: #ef4444;
                        color: white;
                        font-size: 0.85rem;
                        font-weight: 700;
                        padding: 4px 10px;
                        border-radius: 20px;
                        margin-left: 10px;
                        display: inline-block;
                        vertical-align: middle;
                    }

                    /* Lightbox Modal */
                    .order-modal-overlay {
                        position: fixed;
                        inset: 0;
                        background: rgba(0, 0, 0, 0.6);
                        backdrop-filter: blur(4px);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        animation: fadeInModal 0.25s ease;
                    }
                    @keyframes fadeInModal {
                        from { opacity: 0; }
                        to { opacity: 1; }
                    }

                    /* Photo count chip — jumps straight to the gallery without expanding the card */
                    .photo-count-chip {
                        background: #1e293b;
                        color: #fff;
                        border: none;
                        font-size: 0.75rem;
                        font-weight: 700;
                        padding: 5px 10px;
                        border-radius: 20px;
                        cursor: pointer;
                        min-height: 32px;
                        display: inline-flex;
                        align-items: center;
                        gap: 4px;
                    }

                    /* Lightbox close/nav controls — sized for messy-hands tapping */
                    .lightbox-close-btn {
                        position: absolute;
                        top: -48px;
                        right: 0;
                        background: rgba(0,0,0,0.4);
                        border: none;
                        color: white;
                        font-size: 1.5rem;
                        width: 44px;
                        height: 44px;
                        border-radius: 50%;
                        cursor: pointer;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }
                    .lightbox-nav-btn {
                        position: absolute;
                        top: 50%;
                        transform: translateY(-50%);
                        background: rgba(0,0,0,0.45);
                        border: none;
                        color: white;
                        font-size: 2rem;
                        line-height: 1;
                        width: 48px;
                        height: 48px;
                        border-radius: 50%;
                        cursor: pointer;
                        display: none;
                        align-items: center;
                        justify-content: center;
                    }
                    .lightbox-prev-btn { left: -8px; }
                    .lightbox-next-btn { right: -8px; }
                    .lightbox-counter {
                        position: absolute;
                        bottom: -36px;
                        left: 50%;
                        transform: translateX(-50%);
                        background: rgba(0,0,0,0.5);
                        color: white;
                        font-size: 0.8rem;
                        font-weight: 700;
                        padding: 4px 12px;
                        border-radius: 20px;
                        display: none;
                    }

                    /* Board-view order detail modal */
                    .order-detail-modal-box {
                        position: relative;
                        background: white;
                        border-radius: 16px;
                        width: 92%;
                        max-width: 480px;
                        max-height: 88vh;
                        overflow-y: auto;
                        padding: 24px 20px 20px;
                        box-shadow: 0 25px 50px rgba(0,0,0,0.3);
                    }
                    .detail-modal-close-btn {
                        position: absolute;
                        top: 10px;
                        right: 10px;
                        background: #f1f5f9;
                        border: none;
                        color: #475569;
                        font-size: 1.2rem;
                        width: 36px;
                        height: 36px;
                        border-radius: 50%;
                        cursor: pointer;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        z-index: 2;
                    }

                    /* Toggle buttons styling */
                    .active-toggle-btn {
                        background: var(--primary) !important;
                        color: white !important;
                        box-shadow: 0 4px 10px rgba(230, 115, 153, 0.25) !important;
                    }

                    /* Board columns styling */
                    .board-column {
                        box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.02);
                    }
                    .board-order-card {
                        transition: transform 0.15s ease, box-shadow 0.15s ease;
                    }
                    .board-order-card:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 6px 14px rgba(0,0,0,0.08) !important;
                    }
                    .board-urgent-border {
                        border: 2px solid #fca5a5 !important;
                        background: #fffefe !important;
                    }

                    /* --- Mobile optimizations --- */
                    .mobile-board-tabs {
                        display: none;
                        gap: 8px;
                        overflow-x: auto;
                        padding-bottom: 8px;
                        margin-top: 14px;
                        -webkit-overflow-scrolling: touch;
                    }
                    .mobile-board-tab {
                        background: #e2e8f0;
                        color: #475569;
                        font-size: 0.8rem;
                        font-weight: 700;
                        padding: 8px 14px;
                        border-radius: 20px;
                        border: none;
                        white-space: nowrap;
                        cursor: pointer;
                        transition: background 0.2s, color 0.2s;
                    }
                    .mobile-board-tab.active-tab {
                        background: var(--primary);
                        color: white;
                        box-shadow: 0 4px 10px rgba(230, 115, 153, 0.25);
                    }

                    /* Taller kanban columns on desktop — fills most of the viewport instead of stopping at 500px */
                    @media (min-width: 769px) {
                        .board-column {
                            min-height: 80vh !important;
                            max-height: 80vh !important;
                        }
                    }

                    @media (max-width: 768px) {
                        .orders-board-container {
                            grid-template-columns: 1fr !important;
                            gap: 0;
                        }
                        .board-column {
                            display: none !important;
                        }
                        .board-column.active-column {
                            display: flex !important;
                        }
                    }

                    /* A shared card rule elsewhere forces padding:36px !important on .order-card, which fights
                       the header/body's own padding (they rely on the card itself having none). Reclaim it. */
                    .order-card {
                        padding: 0 !important;
                    }

                    /* Order card header — stacked rows so it lays out predictably at every width */
                    .order-card-header {
                        cursor: pointer;
                        user-select: none;
                        width: 100%;
                        padding: 16px 20px;
                        display: flex;
                        flex-direction: column;
                        align-items: stretch;
                        justify-content: flex-start;
                        gap: 8px;
                    }
                    .oc-header-toprow {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        gap: 10px;
                    }
                    .oc-order-title {
                        margin: 0;
                        font-size: 1.05rem;
                        font-weight: 700;
                        color: #5c1d37;
                    }
                    .oc-header-bottomrow {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        flex-wrap: wrap;
                        gap: 10px;
                    }
                    .oc-badges {
                        display: flex;
                        align-items: center;
                        flex-wrap: wrap;
                        gap: 8px;
                    }
                    @media (max-width: 576px) {
                        .order-card-header {
                            padding: 14px 16px;
                        }
                        .due-badge {
                            font-size: 0.8rem;
                        }
                        .oc-header-bottomrow .status-select {
                            width: 100%;
                        }
                    }

                    /* Collapsed-card preview — thumbnails + a one-line summary, so a collapsed card
                       isn't just blank white space until you tap to expand it */
                    .order-card-preview {
                        cursor: pointer;
                        display: flex;
                        align-items: center;
                        gap: 12px;
                        padding: 0 20px 16px;
                    }
                    .oc-preview-thumbs {
                        display: flex;
                        gap: 6px;
                        flex-shrink: 0;
                    }
                    .oc-preview-thumb {
                        width: 44px;
                        height: 44px;
                        border-radius: 8px;
                        overflow: hidden;
                        border: 1px solid #e2e8f0;
                        cursor: pointer;
                        flex-shrink: 0;
                    }
                    .oc-preview-thumb img {
                        width: 100%;
                        height: 100%;
                        object-fit: cover;
                        display: block;
                    }
                    .oc-preview-summary {
                        margin: 0;
                        min-width: 0;
                        font-size: 0.9rem;
                        color: #64748b;
                        overflow: hidden;
                        text-overflow: ellipsis;
                        white-space: nowrap;
                    }
                    .collapsible-card.expanded .order-card-preview {
                        display: none;
                    }
                    @media (max-width: 576px) {
                        .oc-preview-summary {
                            white-space: normal;
                            display: -webkit-box;
                            -webkit-line-clamp: 2;
                            -webkit-box-orient: vertical;
                        }
                    }

                    @media (max-width: 576px) {
                        .order-card-actions {
                            flex-direction: column;
                            width: 100%;
                        }
                        .order-card-actions button {
                            width: 100%;
                            text-align: center;
                            justify-content: center;
                        }
                    }

                    /* Bigger touch targets for tapping with messy/floury hands */
                    @media (max-width: 768px) {
                        .admin-main-content select.status-select {
                            height: 42px !important;
                            min-width: 150px !important;
                            font-size: 0.8rem !important;
                        }
                        .advance-status-btn {
                            padding: 10px 14px !important;
                            font-size: 0.85rem !important;
                            min-height: 40px;
                        }
                        .inspiration-thumb-container {
                            width: 96px !important;
                            height: 96px !important;
                        }
                        .lightbox-close-btn {
                            top: 8px;
                            right: 8px;
                        }
                        .lightbox-prev-btn { left: 4px; }
                        .lightbox-next-btn { right: 4px; }
                        .lightbox-counter {
                            bottom: 8px;
                        }
                    }

                </style>

                <div class="section-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom: 24px;">
                    <div>
                        <h3 style="display:inline-block; vertical-align:middle; margin:0;">Orders</h3>
                        @if($newInquiriesCount > 0)
                            <span class="header-inquiries-badge">{{ $newInquiriesCount }} New</span>
                        @endif
                        <p class="subtitle" style="margin-top:4px;">Sorted by due date, soonest first.</p>
                    </div>
                    <div style="display:flex; gap:10px; background:#f1f5f9; padding:4px; border-radius:10px;">
                        <button type="button" class="btn btn-sm btn-outline active-toggle-btn" id="orders-view-list-btn" onclick="switchOrdersView('list')" style="border:none; border-radius:8px; padding:6px 14px; font-weight:600; cursor:pointer; background:transparent; color:#555;">List View</button>
                        <button type="button" class="btn btn-sm btn-outline" id="orders-view-board-btn" onclick="switchOrdersView('board')" style="border:none; border-radius:8px; padding:6px 14px; font-weight:600; cursor:pointer; background:transparent; color:#555;">Board View</button>
                    </div>
                </div>

                <!-- Mobile Column Switcher (Visible only on mobile/tablet) -->
                <div class="mobile-board-tabs" id="mobile-board-tabs-bar" style="display:none; margin-bottom: 16px;">
                    <button type="button" class="mobile-board-tab active-tab" onclick="switchMobileBoardColumn('new')">New ({{ $urgentOrders->where('status', 'new')->count() }})</button>
                    <button type="button" class="mobile-board-tab" onclick="switchMobileBoardColumn('invoiced')">Invoiced ({{ $urgentOrders->where('status', 'invoiced')->count() }})</button>
                    <button type="button" class="mobile-board-tab" onclick="switchMobileBoardColumn('paid')">Paid ({{ $urgentOrders->where('status', 'paid')->count() }})</button>
                    <button type="button" class="mobile-board-tab" onclick="switchMobileBoardColumn('in_progress')">Progress ({{ $urgentOrders->where('status', 'in_progress')->count() }})</button>
                    <button type="button" class="mobile-board-tab" onclick="switchMobileBoardColumn('ready')">Ready ({{ $urgentOrders->where('status', 'ready')->count() }})</button>
                    <button type="button" class="mobile-board-tab" onclick="switchMobileBoardColumn('completed')">Done ({{ $urgentOrders->where('status', 'completed')->count() }})</button>
                </div>

                <!-- LIST VIEW CONTAINER -->
                <div class="orders-list-grid" id="admin-orders-list">
                    @forelse($urgentOrders as $order)
                        @php
                            $dueDate = \Carbon\Carbon::parse($order->due_date);
                            $isUrgent = $dueDate->isToday() || $dueDate->isTomorrow() || $dueDate->diffInDays(now()) <= 2;
                            $orderPhotoUrls = collect($order->inspiration_files ?? [])
                                ->filter(fn($f) => is_string($f) && file_exists(public_path($f)))
                                ->map(fn($f) => asset($f))
                                ->values();

                            $previewParts = collect();
                            if (!empty($order->items) && is_array($order->items)) {
                                $previewParts->push(collect($order->items)->map(function ($item) {
                                    return is_array($item)
                                        ? trim(($item['name'] ?? 'Item') . (!empty($item['quantity']) ? ' x' . $item['quantity'] : ''))
                                        : (string) $item;
                                })->implode(', '));
                            }
                            if (!empty($order->flavors)) {
                                $previewParts->push(implode(', ', $order->flavors));
                            }
                            if ($order->special_notes) {
                                $previewParts->push($order->special_notes);
                            }
                            $previewSummary = $previewParts->implode(' • ');
                        @endphp
                        <div class="order-card collapsible-card {{ $isUrgent ? 'urgent-border' : '' }}" data-id="{{ $order->id }}" data-fulfillment="{{ $order->fulfillment_type }}" style="padding:0; overflow:hidden;">
                            <div class="order-card-header" onclick="toggleOrderCardCollapse(this)">
                                <div class="oc-header-toprow">
                                    <div class="due-badge {{ $isUrgent ? 'due-urgent' : 'due-normal' }}">
                                        DUE: {{ $dueDate->format('M d, Y') }} ({{ $order->time_slot }})
                                    </div>
                                    <span class="chevron-indicator">▼</span>
                                </div>

                                <h4 class="oc-order-title">#{{ $order->order_number }} - {{ $order->client_name }}</h4>

                                <div class="oc-header-bottomrow">
                                    <div class="oc-badges">
                                        @if($order->allergies)
                                            <span class="allergy-pinned-badge">⚠️ ALLERGIES</span>
                                        @endif

                                        @if($orderPhotoUrls->isNotEmpty())
                                            <button type="button" class="photo-count-chip" onclick='event.stopPropagation(); openOrderLightbox(@json($orderPhotoUrls), 0)' title="View reference photos">
                                                📷 {{ $orderPhotoUrls->count() }}
                                            </button>
                                        @endif
                                    </div>
                                    <div onclick="event.stopPropagation()">
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
                                </div>
                            </div>

                            @if($orderPhotoUrls->isNotEmpty() || $previewSummary)
                                <div class="order-card-preview" onclick="toggleOrderCardCollapse(this.closest('.order-card').querySelector('.order-card-header'))">
                                    @if($orderPhotoUrls->isNotEmpty())
                                        <div class="oc-preview-thumbs">
                                            @foreach($orderPhotoUrls->take(3) as $idx => $url)
                                                <div class="oc-preview-thumb" onclick='event.stopPropagation(); openOrderLightbox(@json($orderPhotoUrls), {{ $idx }})'>
                                                    <img src="{{ $url }}" alt="Inspiration {{ $idx + 1 }}" loading="lazy">
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if($previewSummary)
                                        <p class="oc-preview-summary">{{ $previewSummary }}</p>
                                    @endif
                                </div>
                            @endif

                            <div class="order-card-collapsible" style="display: none;">
                                <div class="order-card-body" style="padding: 20px; border-top:1px solid #f1f5f9;">
                                    <p><strong>Phone:</strong> {{ $order->client_phone }} | <strong>Email:</strong> {{ $order->client_email }}</p>
                                    <p><strong>Fulfillment:</strong> {{ strtoupper($order->fulfillment_type) }}
                                        @if($order->fulfillment_type == 'delivery')
                                            ({{ $order->delivery_address }})
                                        @endif
                                    </p>

                                    @if(!empty($order->items) && is_array($order->items))
                                        <div class="order-items-section" style="margin-top:12px;">
                                            <strong style="display:block; margin-bottom:6px; font-size:0.85rem; color:#64748b; text-transform:uppercase; letter-spacing:0.05em;">Order Items</strong>
                                            @foreach($order->items as $item)
                                                <p style="font-size:1.1rem; color:#1e293b; margin:2px 0; display:flex; justify-content:space-between; gap:12px;">
                                                    <span>{{ is_array($item) ? ($item['name'] ?? 'Item') : $item }}{{ is_array($item) && !empty($item['quantity']) ? ' x' . $item['quantity'] : '' }}</span>
                                                    @if(is_array($item) && isset($item['price']))
                                                        <span>${{ number_format((float) $item['price'], 2) }}</span>
                                                    @endif
                                                </p>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if(!empty($order->flavors))
                                        <p style="font-size:1.15rem; font-weight:600; color:#1e293b; margin-top:12px;"><strong>Flavors:</strong> {{ implode(', ', $order->flavors) }}</p>
                                    @endif
                                    @if(!empty($order->frosting))
                                        <p style="font-size:1.15rem; font-weight:600; color:#1e293b; margin-top:6px;"><strong>Frosting:</strong> {{ implode(', ', $order->frosting) }}</p>
                                    @endif
                                    @if(!empty($order->fillings))
                                        <p style="font-size:1.15rem; font-weight:600; color:#1e293b; margin-top:6px;"><strong>Fillings:</strong> {{ implode(', ', $order->fillings) }}</p>
                                    @endif
                                    @if($order->special_notes)
                                        <p class="notes-box" style="font-size:1.1rem; margin-top:12px; padding:10px; background:#f8fafc; border-left:4px solid var(--primary); border-radius:4px;"><strong>Special Notes:</strong> {{ $order->special_notes }}</p>
                                    @endif
                                    @if($order->allergies)
                                        <p class="allergy-warning" style="font-size:1.2rem; font-weight:700; border:2px solid #ef4444; background:#fef2f2; color:#991b1b; padding:10px; border-radius:6px; margin-top:12px;"><strong>Allergies:</strong> {{ $order->allergies }}</p>
                                    @endif
                                    @if(!empty($order->social_follows))
                                        <p style="font-size:1.05rem; color:#1e293b; margin-top:12px;"><strong>Social/Follow:</strong> {{ is_array($order->social_follows) ? implode(', ', $order->social_follows) : $order->social_follows }}</p>
                                    @endif

                                    <!-- Inspiration Photos (Thumbnails with Lightbox Zoom) -->
                                    @if($orderPhotoUrls->isNotEmpty())
                                        <div class="inspiration-section" style="margin-top:16px; border-top:1px solid #f1f5f9; padding-top:12px;">
                                            <strong style="display:block; margin-bottom:8px; font-size:0.85rem; color:#64748b; text-transform:uppercase; letter-spacing:0.05em;">Inspiration Photos</strong>
                                            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                                                @foreach($orderPhotoUrls as $idx => $url)
                                                    <div class="inspiration-thumb-container" style="position:relative; width:80px; height:80px; border-radius:8px; overflow:hidden; border:2px solid #e2e8f0; cursor:pointer;" onclick='openOrderLightbox(@json($orderPhotoUrls), {{ $idx }})'>
                                                        <img src="{{ $url }}" alt="Inspiration {{ $idx + 1 }}" style="width:100%; height:100%; object-fit:cover;">
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <div class="pricing-breakdown" style="border-top:1px solid #f1f5f9; margin-top:16px; padding-top:12px; display:flex; justify-content:space-between; flex-wrap:wrap; gap:12px;">
                                        <span>Total: <strong>${{ number_format($order->total_price, 2) }}</strong></span>
                                        <span>50% Deposit: <strong>${{ number_format($order->deposit_amount, 2) }}</strong>
                                            ({{ $order->deposit_paid ? 'Paid' : 'Pending' }})
                                        </span>
                                    </div>
                                </div>

                                <div class="order-card-actions" style="display:flex; gap:10px; border-top:1px solid #f1f5f9; padding: 14px 20px; margin-top:0; background:#fafafa;">
                                    <button class="btn btn-sm btn-primary" onclick="generateInvoiceFromOrder({{ $order->id }}, {{ $order->total_price }}, {{ $order->deposit_amount }})">Create Invoice</button>
                                    <button class="btn btn-sm btn-outline" onclick="copyClientPayLink('{{ $order->invoice ? $order->invoice->invoice_number : '' }}', {{ $order->id }})">Copy Invoice Link</button>
                                    <button class="btn btn-sm btn-outline" style="border-color:#64748b; color:#64748b;" onclick="printOrderBoxSlip({{ $order->id }})">🖨️ Print Box Slip</button>
                                </div>
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

                <!-- BOARD VIEW CONTAINER -->
                <div class="orders-board-container" id="admin-orders-board" style="display:none; grid-template-columns: repeat(6, minmax(280px, 1fr)); gap:16px; overflow-x:auto; padding-bottom:12px; margin-top:20px;">
                    @php
                        $columns = [
                            'new' => 'New Inquiries',
                            'invoiced' => 'Invoiced',
                            'paid' => 'Paid / Confirmed',
                            'in_progress' => 'In Progress',
                            'ready' => 'Ready',
                            'completed' => 'Completed'
                        ];
                    @endphp
                    @foreach($columns as $statusKey => $columnName)
                        @php
                            $statusOrders = $urgentOrders->where('status', $statusKey);
                        @endphp
                        <div class="board-column {{ $statusKey === 'new' ? 'active-column' : '' }}" data-status="{{ $statusKey }}" ondragover="allowBoardCardDrop(event)" ondragleave="handleBoardCardDragLeave(event, this)" ondrop="handleBoardCardDrop(event, this)" style="background:#f8fafc; border:2px solid #e2e8f0; border-radius:16px; padding:12px; display:flex; flex-direction:column; min-height:500px; max-height:80vh; overflow-y:auto; transition: background 0.15s ease;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; border-bottom:2px solid #cbd5e1; padding-bottom:8px; position:sticky; top:0; background:#f8fafc; z-index:10;">
                                <h4 style="margin:0; font-size:0.85rem; font-weight:700; color:#475569; text-transform:uppercase; letter-spacing:0.04em;">{{ $columnName }}</h4>
                                <span style="font-size:0.8rem; background:#cbd5e1; color:#475569; font-weight:700; padding:2px 8px; border-radius:10px;">{{ $statusOrders->count() }}</span>
                            </div>
                            
                            <div class="board-cards-list" style="display:flex; flex-direction:column; gap:12px; flex:1;">
                                @forelse($statusOrders as $order)
                                    @php
                                        $dueDate = \Carbon\Carbon::parse($order->due_date);
                                        $isUrgent = $dueDate->isToday() || $dueDate->isTomorrow() || $dueDate->diffInDays(now()) <= 2;
                                        $orderPhotoUrls = collect($order->inspiration_files ?? [])
                                            ->filter(fn($f) => is_string($f) && file_exists(public_path($f)))
                                            ->map(fn($f) => asset($f))
                                            ->values();
                                        $orderModalData = [
                                            'id' => $order->id,
                                            'orderNumber' => $order->order_number,
                                            'clientName' => $order->client_name,
                                            'clientPhone' => $order->client_phone,
                                            'clientEmail' => $order->client_email,
                                            'fulfillmentType' => $order->fulfillment_type,
                                            'deliveryAddress' => $order->delivery_address,
                                            'dueDateFormatted' => $dueDate->format('M d, Y'),
                                            'timeSlot' => $order->time_slot,
                                            'isUrgent' => $isUrgent,
                                            'items' => !empty($order->items) && is_array($order->items) ? array_map(function ($item) {
                                                return is_array($item)
                                                    ? trim(($item['name'] ?? 'Item') . (!empty($item['quantity']) ? ' x' . $item['quantity'] : ''))
                                                    : (string) $item;
                                            }, $order->items) : [],
                                            'flavors' => !empty($order->flavors) ? implode(', ', $order->flavors) : null,
                                            'frosting' => !empty($order->frosting) ? implode(', ', $order->frosting) : null,
                                            'fillings' => !empty($order->fillings) ? implode(', ', $order->fillings) : null,
                                            'specialNotes' => $order->special_notes,
                                            'allergies' => $order->allergies,
                                            'socialFollows' => !empty($order->social_follows) ? (is_array($order->social_follows) ? implode(', ', $order->social_follows) : $order->social_follows) : null,
                                            'totalPrice' => number_format((float) $order->total_price, 2),
                                            'depositAmount' => number_format((float) $order->deposit_amount, 2),
                                            'depositPaid' => (bool) $order->deposit_paid,
                                            'photos' => $orderPhotoUrls,
                                        ];
                                    @endphp
                                    <div class="board-order-card {{ $isUrgent ? 'board-urgent-border' : '' }}" data-id="{{ $order->id }}" data-order='@json($orderModalData)' draggable="true" ondragstart="handleBoardCardDragStart(event)" onclick="openOrderDetailModal(this)" style="background:white; border:1px solid #cbd5e1; border-radius:12px; padding:14px; box-shadow:0 2px 6px rgba(0,0,0,0.04); position:relative; cursor: pointer;">
                                        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
                                            <span style="font-size:0.75rem; font-weight:700; color:#64748b;">#{{ $order->order_number }}</span>
                                            <span style="font-size:0.75rem; font-weight:700; background:{{ $isUrgent ? '#fee2e2' : '#f1f5f9' }}; color:{{ $isUrgent ? '#ef4444' : '#475569' }}; padding:2px 6px; border-radius:6px;">
                                                {{ $dueDate->format('M d') }}
                                            </span>
                                        </div>
                                        
                                        <h5 style="margin:0 0 6px 0; font-size:0.95rem; font-weight:700; color:#5c1d37;">{{ $order->client_name }}</h5>
                                        
                                        @if($order->allergies)
                                            <span style="display:inline-block; font-size:0.68rem; font-weight:700; background:#fee2e2; color:#ef4444; padding:2px 6px; border-radius:4px; margin-bottom:8px; border:1px solid #fca5a5;">⚠️ ALLERGIES</span>
                                        @endif

                                        <p style="font-size:0.82rem; color:#475569; margin:0 0 8px 0; line-height:1.4; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden;">
                                            @if(!empty($order->flavors))
                                                <strong>Flavors:</strong> {{ implode(', ', $order->flavors) }}<br>
                                            @endif
                                            @if($order->special_notes)
                                                <strong>Notes:</strong> {{ $order->special_notes }}
                                            @endif
                                        </p>

                                        @if($orderPhotoUrls->isNotEmpty())
                                            <div onclick='event.stopPropagation(); openOrderLightbox(@json($orderPhotoUrls), 0)' style="width:100%; height:80px; border-radius:6px; overflow:hidden; border:1px solid #e2e8f0; margin-bottom:10px; cursor:pointer; position:relative;">
                                                <img src="{{ $orderPhotoUrls->first() }}" alt="Inspiration" style="width:100%; height:100%; object-fit:cover;">
                                                @if($orderPhotoUrls->count() > 1)
                                                    <span style="position:absolute; bottom:4px; right:4px; background:rgba(0,0,0,0.65); color:#fff; font-size:0.68rem; font-weight:700; padding:2px 6px; border-radius:10px;">+{{ $orderPhotoUrls->count() - 1 }}</span>
                                                @endif
                                            </div>
                                        @endif

                                        <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid #f1f5f9; padding-top:8px; margin-top:8px;">
                                            <span style="font-size:0.85rem; font-weight:700; color:#1e293b;">${{ number_format($order->total_price, 2) }}</span>
                                            
                                            <div style="display:flex; gap:6px;">
                                                @php
                                                    $statusKeys = ['new', 'invoiced', 'paid', 'in_progress', 'ready', 'completed'];
                                                    $currentIdx = array_search($statusKey, $statusKeys);
                                                    $nextStatus = ($currentIdx !== false && $currentIdx < count($statusKeys) - 1) ? $statusKeys[$currentIdx + 1] : null;
                                                @endphp
                                                @if($nextStatus)
                                                    <button type="button" class="btn btn-sm advance-status-btn" onclick="event.stopPropagation(); advanceOrderStatus({{ $order->id }}, '{{ $nextStatus }}')" style="background:var(--pink-bg); color:var(--primary); font-weight:700; border:none; padding:4px 8px; border-radius:6px; font-size:0.75rem; cursor:pointer;" title="Move to {{ ucfirst($nextStatus) }}">
                                                        Advance ➔
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div style="padding:20px; text-align:center; color:#94a3b8; font-size:0.8rem; border:2px dashed #cbd5e1; border-radius:12px;">
                                        No orders here
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- REUSABLE LIGHTBOX MODAL (gallery-aware: prev/next + swipe) -->
                <div id="order-lightbox-modal" class="order-modal-overlay" style="display:none; z-index:100001;" onclick="closeOrderLightbox()">
                    <div style="position:relative; max-width:92%; max-height:92%;" onclick="event.stopPropagation()">
                        <img id="order-lightbox-img" src="" alt="Zoomed Inspiration Photo" style="max-width:100%; max-height:85vh; border-radius:12px; box-shadow:0 25px 50px rgba(0,0,0,0.3); border:3px solid #fff; display:block; touch-action:pan-y;">
                        <button type="button" class="lightbox-close-btn" onclick="closeOrderLightbox()" title="Close">✕</button>
                        <button type="button" id="lightbox-prev-btn" class="lightbox-nav-btn lightbox-prev-btn" onclick="lightboxStep(-1)" title="Previous photo">‹</button>
                        <button type="button" id="lightbox-next-btn" class="lightbox-nav-btn lightbox-next-btn" onclick="lightboxStep(1)" title="Next photo">›</button>
                        <div id="lightbox-counter" class="lightbox-counter"></div>
                    </div>
                </div>

                <!-- BOARD-VIEW ORDER DETAIL MODAL — full order details in a popup, since board columns are too narrow to expand inline -->
                <div id="order-detail-modal" class="order-modal-overlay" style="display:none; z-index:100002;" onclick="closeOrderDetailModal()">
                    <div class="order-detail-modal-box" onclick="event.stopPropagation()">
                        <button type="button" class="detail-modal-close-btn" onclick="closeOrderDetailModal()" title="Close">✕</button>
                        <div id="order-detail-content"></div>
                    </div>
                </div>

                <!-- SCRIPTS SPECIFIC TO ORDERS TAB -->
                <script>
                    window.toggleOrderCardCollapse = function(headerElement) {
                        const card = headerElement.closest('.order-card');
                        if (!card) return;
                        
                        card.classList.toggle('expanded');
                        const collapsible = card.querySelector('.order-card-collapsible');
                        if (collapsible) {
                            if (card.classList.contains('expanded')) {
                                collapsible.style.display = 'block';
                                card.querySelector('.chevron-indicator').innerText = '▲';
                            } else {
                                collapsible.style.display = 'none';
                                card.querySelector('.chevron-indicator').innerText = '▼';
                            }
                        }
                    };

                    window.switchOrdersView = function(viewType) {
                        const listBtn = document.getElementById('orders-view-list-btn');
                        const boardBtn = document.getElementById('orders-view-board-btn');
                        const listContainer = document.getElementById('admin-orders-list');
                        const boardContainer = document.getElementById('admin-orders-board');
                        const mobileTabsBar = document.getElementById('mobile-board-tabs-bar');
                        
                        if (viewType === 'board') {
                            listBtn?.classList.remove('active-toggle-btn');
                            boardBtn?.classList.add('active-toggle-btn');
                            if (listContainer) listContainer.style.display = 'none';
                            if (boardContainer) boardContainer.style.display = 'grid';
                            if (mobileTabsBar && window.innerWidth <= 768) mobileTabsBar.style.display = 'flex';
                            localStorage.setItem('baker_orders_view_type', 'board');
                            
                            // Initialize active mobile column
                            if (window.innerWidth <= 768) {
                                const activeTab = document.querySelector('.mobile-board-tab.active-tab');
                                const activeStatus = activeTab ? activeTab.getAttribute('onclick').match(/'([^']+)'/)[1] : 'new';
                                switchMobileBoardColumn(activeStatus);
                            }
                        } else {
                            boardBtn?.classList.remove('active-toggle-btn');
                            listBtn?.classList.add('active-toggle-btn');
                            if (boardContainer) boardContainer.style.display = 'none';
                            if (listContainer) listContainer.style.display = 'flex';
                            if (mobileTabsBar) mobileTabsBar.style.display = 'none';
                            localStorage.setItem('baker_orders_view_type', 'list');
                        }
                    };

                    window.switchMobileBoardColumn = function(statusKey) {
                        // Deactivate all tabs
                        const tabs = document.querySelectorAll('.mobile-board-tab');
                        tabs.forEach(tab => {
                            tab.classList.remove('active-tab');
                            if (tab.getAttribute('onclick').includes(`'${statusKey}'`)) {
                                tab.classList.add('active-tab');
                            }
                        });
                        
                        // Deactivate all columns
                        const columns = document.querySelectorAll('.board-column');
                        columns.forEach(col => {
                            col.classList.remove('active-column');
                            if (col.dataset.status === statusKey) {
                                col.classList.add('active-column');
                            }
                        });
                    };

                    window.advanceOrderStatus = function(orderId, nextStatus) {
                        window.updateOrderStatus(orderId, nextStatus);
                    };

                    window.printOrderBoxSlip = function(orderId) {
                        const card = document.querySelector(`.order-card[data-id="${orderId}"]`);
                        if (!card) return;
                        
                        const number = card.querySelector('h4').innerText;
                        const clientInfo = card.querySelector('.order-card-body p:nth-child(1)').innerHTML;
                        const fulfillment = card.querySelector('.order-card-body p:nth-child(2)').innerHTML;

                        const itemsSection = card.querySelector('.order-items-section') ? card.querySelector('.order-items-section').outerHTML : '';

                        const details = Array.from(card.querySelectorAll('.order-card-body > p')).slice(2)
                            .map(p => p.outerHTML).join('');

                        const notesBox = card.querySelector('.notes-box') ? card.querySelector('.notes-box').outerHTML : '';
                        const allergyWarning = card.querySelector('.allergy-warning') ? card.querySelector('.allergy-warning').outerHTML : '';
                        const pricing = card.querySelector('.pricing-breakdown') ? card.querySelector('.pricing-breakdown').innerHTML : '';
                        
                        const photos = Array.from(card.querySelectorAll('.inspiration-thumb-container img'))
                            .map(img => `<img src="${img.src}" style="max-height:180px; margin:5px; border-radius:6px; border:1px solid #ccc;">`).join('');

                        const printWindow = window.open('', '_blank', 'width=600,height=800');
                        printWindow.document.write(`
                            <html>
                            <head>
                                <title>Box Slip - ${number}</title>
                                <style>
                                    body {
                                        font-family: 'Inter', system-ui, sans-serif;
                                        padding: 20px;
                                        color: #333;
                                        line-height: 1.4;
                                    }
                                    .slip-header {
                                        border-bottom: 2px dashed #000;
                                        padding-bottom: 12px;
                                        margin-bottom: 16px;
                                        text-align: center;
                                    }
                                    .slip-header h2 {
                                        margin: 0;
                                        font-size: 1.6rem;
                                    }
                                    .slip-section {
                                        margin-bottom: 14px;
                                    }
                                    .slip-section strong {
                                        color: #555;
                                        font-size: 0.85rem;
                                        text-transform: uppercase;
                                        display: block;
                                        margin-bottom: 2px;
                                    }
                                    .slip-section p {
                                        margin: 0;
                                        font-size: 1.1rem;
                                    }
                                    .notes-box {
                                        background: #f8fafc;
                                        border-left: 4px solid #64748b;
                                        padding: 10px;
                                        font-style: italic;
                                        border-radius: 4px;
                                    }
                                    .allergy-warning {
                                        background: #fef2f2;
                                        border: 2px solid #ef4444;
                                        color: #991b1b;
                                        padding: 10px;
                                        font-weight: bold;
                                        border-radius: 4px;
                                    }
                                    .pricing {
                                        font-size: 1.15rem;
                                        font-weight: bold;
                                        border-top: 2px dashed #000;
                                        padding-top: 10px;
                                        margin-top: 20px;
                                        display: flex;
                                        justify-content: space-between;
                                    }
                                    @media print {
                                        body { padding: 0; }
                                        button { display: none; }
                                    }
                                </style>
                            </head>
                            <body>
                                <div class="slip-header">
                                    <h2>${number}</h2>
                                    <div style="font-size:0.9rem; margin-top:4px; font-weight:bold;">${card.querySelector('.due-badge').innerText}</div>
                                </div>
                                <div class="slip-section">
                                    <strong>Customer Info</strong>
                                    <p>${clientInfo}</p>
                                </div>
                                <div class="slip-section">
                                    <strong>Fulfillment</strong>
                                    <p>${fulfillment}</p>
                                </div>
                                ${itemsSection ? `<div class="slip-section">${itemsSection}</div>` : ''}
                                <div class="slip-section">
                                    <strong>Order Specifications</strong>
                                    ${details}
                                </div>
                                ${notesBox ? `<div class="slip-section"><strong>Special Notes</strong>${notesBox}</div>` : ''}
                                ${allergyWarning ? `<div class="slip-section">${allergyWarning}</div>` : ''}
                                ${photos ? `<div class="slip-section"><strong>Reference Photos</strong><div style="display:flex; flex-wrap:wrap;">${photos}</div></div>` : ''}
                                <div class="pricing">
                                    ${pricing}
                                </div>
                                <div style="margin-top:30px; text-align:center;">
                                    <button onclick="window.print();window.close();" style="padding:10px 24px; font-weight:bold; background:#000; color:#fff; border:none; border-radius:6px; cursor:pointer;">Print Box Slip</button>
                                </div>
                                <script>
                                    window.onload = function() {
                                        setTimeout(() => {
                                            window.print();
                                        }, 600);
                                    }
                                <\/script>
                            </body>
                            </html>
                        `);
                        printWindow.document.close();
                    };

                    // Gallery state for the reference-photo lightbox
                    window._lightboxGallery = [];
                    window._lightboxIndex = 0;

                    window.openOrderLightbox = function(urlsOrSrc, startIndex) {
                        const modal = document.getElementById('order-lightbox-modal');
                        const img = document.getElementById('order-lightbox-img');
                        if (!modal || !img) return;

                        window._lightboxGallery = Array.isArray(urlsOrSrc) ? urlsOrSrc : [urlsOrSrc];
                        window._lightboxIndex = startIndex || 0;

                        window._renderLightboxImage();
                        modal.style.display = 'flex';
                    };

                    window._renderLightboxImage = function() {
                        const img = document.getElementById('order-lightbox-img');
                        const counter = document.getElementById('lightbox-counter');
                        const prevBtn = document.getElementById('lightbox-prev-btn');
                        const nextBtn = document.getElementById('lightbox-next-btn');
                        const gallery = window._lightboxGallery;
                        if (!img || !gallery.length) return;

                        img.src = gallery[window._lightboxIndex];

                        const showNav = gallery.length > 1;
                        if (prevBtn) prevBtn.style.display = showNav ? 'flex' : 'none';
                        if (nextBtn) nextBtn.style.display = showNav ? 'flex' : 'none';
                        if (counter) {
                            counter.style.display = showNav ? 'block' : 'none';
                            counter.innerText = `${window._lightboxIndex + 1} / ${gallery.length}`;
                        }
                    };

                    window.lightboxStep = function(direction) {
                        const gallery = window._lightboxGallery;
                        if (!gallery.length) return;
                        window._lightboxIndex = (window._lightboxIndex + direction + gallery.length) % gallery.length;
                        window._renderLightboxImage();
                    };

                    window.closeOrderLightbox = function() {
                        const modal = document.getElementById('order-lightbox-modal');
                        if (modal) modal.style.display = 'none';
                    };

                    // Escapes text pulled from order data (customer-submitted) before it's placed in innerHTML
                    window._escapeHtml = function(str) {
                        if (str === null || str === undefined) return '';
                        const div = document.createElement('div');
                        div.textContent = String(str);
                        return div.innerHTML;
                    };

                    // Board-view card tap: shows full order details (flavors, fillings, notes, photos) in a popup,
                    // since board columns are too narrow to expand inline like the list view can.
                    window.openOrderDetailModal = function(cardEl) {
                        let order;
                        try {
                            order = JSON.parse(cardEl.dataset.order);
                        } catch (e) {
                            return;
                        }

                        const esc = window._escapeHtml;
                        const content = document.getElementById('order-detail-content');
                        if (!content) return;

                        let photosHtml = '';
                        if (order.photos && order.photos.length) {
                            // Thumbnails get no inline onclick (URLs could contain characters that break
                            // an HTML attribute) — click listeners are wired up below via addEventListener instead.
                            const thumbs = order.photos.map((url, idx) => {
                                return `<div class="inspiration-thumb-container" data-photo-index="${idx}" style="position:relative; width:80px; height:80px; border-radius:8px; overflow:hidden; border:2px solid #e2e8f0; cursor:pointer;"><img src="${url}" alt="Inspiration ${idx + 1}" style="width:100%; height:100%; object-fit:cover;"></div>`;
                            }).join('');
                            photosHtml = `<div class="inspiration-section" style="margin-top:16px; border-top:1px solid #f1f5f9; padding-top:12px;">
                                <strong style="display:block; margin-bottom:8px; font-size:0.85rem; color:#64748b; text-transform:uppercase; letter-spacing:0.05em;">Inspiration Photos</strong>
                                <div style="display:flex; gap:10px; flex-wrap:wrap;">${thumbs}</div>
                            </div>`;
                        }

                        content.innerHTML = `
                            <div class="due-badge ${order.isUrgent ? 'due-urgent' : 'due-normal'}" style="margin-bottom:10px;">DUE: ${esc(order.dueDateFormatted)} (${esc(order.timeSlot)})</div>
                            <h4 style="margin:0 0 10px; font-size:1.15rem; font-weight:700; color:#5c1d37;">#${esc(order.orderNumber)} - ${esc(order.clientName)}</h4>
                            <p><strong>Phone:</strong> ${esc(order.clientPhone)} | <strong>Email:</strong> ${esc(order.clientEmail)}</p>
                            <p><strong>Fulfillment:</strong> ${esc((order.fulfillmentType || '').toUpperCase())} ${order.deliveryAddress ? '(' + esc(order.deliveryAddress) + ')' : ''}</p>
                            ${order.items && order.items.length ? `<div style="margin-top:12px;"><strong style="display:block; margin-bottom:6px; font-size:0.85rem; color:#64748b; text-transform:uppercase; letter-spacing:0.05em;">Order Items</strong>${order.items.map(i => `<p style="font-size:1.1rem; color:#1e293b; margin:2px 0;">${esc(i)}</p>`).join('')}</div>` : ''}
                            ${order.flavors ? `<p style="font-size:1.1rem; font-weight:600; color:#1e293b; margin-top:12px;"><strong>Flavors:</strong> ${esc(order.flavors)}</p>` : ''}
                            ${order.frosting ? `<p style="font-size:1.1rem; font-weight:600; color:#1e293b; margin-top:6px;"><strong>Frosting:</strong> ${esc(order.frosting)}</p>` : ''}
                            ${order.fillings ? `<p style="font-size:1.1rem; font-weight:600; color:#1e293b; margin-top:6px;"><strong>Fillings:</strong> ${esc(order.fillings)}</p>` : ''}
                            ${order.specialNotes ? `<p class="notes-box" style="font-size:1.1rem; margin-top:12px; padding:10px; background:#f8fafc; border-left:4px solid var(--primary); border-radius:4px;"><strong>Special Notes:</strong> ${esc(order.specialNotes)}</p>` : ''}
                            ${order.allergies ? `<p class="allergy-warning" style="font-size:1.2rem; font-weight:700; border:2px solid #ef4444; background:#fef2f2; color:#991b1b; padding:10px; border-radius:6px; margin-top:12px;"><strong>Allergies:</strong> ${esc(order.allergies)}</p>` : ''}
                            ${order.socialFollows ? `<p style="font-size:1.05rem; color:#1e293b; margin-top:12px;"><strong>Social/Follow:</strong> ${esc(order.socialFollows)}</p>` : ''}
                            ${photosHtml}
                            <div class="pricing-breakdown" style="border-top:1px solid #f1f5f9; margin-top:16px; padding-top:12px; display:flex; justify-content:space-between; flex-wrap:wrap; gap:12px;">
                                <span>Total: <strong>$${esc(order.totalPrice)}</strong></span>
                                <span>50% Deposit: <strong>$${esc(order.depositAmount)}</strong> (${order.depositPaid ? 'Paid' : 'Pending'})</span>
                            </div>
                            <div class="order-card-actions" style="border-top:1px solid #f1f5f9; padding-top:14px; margin-top:16px;">
                                <button type="button" class="btn btn-sm btn-primary" onclick="generateInvoiceFromOrder(${order.id}, ${order.totalPrice}, ${order.depositAmount})">Create Invoice</button>
                                <button type="button" class="btn btn-sm btn-outline" onclick="copyClientPayLink('', ${order.id})">Copy Invoice Link</button>
                                <button type="button" class="btn btn-sm btn-outline" style="border-color:#64748b; color:#64748b;" onclick="printOrderBoxSlip(${order.id})">🖨️ Print Box Slip</button>
                            </div>
                        `;

                        content.querySelectorAll('.inspiration-thumb-container').forEach((thumb) => {
                            thumb.addEventListener('click', () => {
                                openOrderLightbox(order.photos, parseInt(thumb.dataset.photoIndex, 10));
                            });
                        });

                        document.getElementById('order-detail-modal').style.display = 'flex';
                    };

                    window.closeOrderDetailModal = function() {
                        const modal = document.getElementById('order-detail-modal');
                        if (modal) modal.style.display = 'none';
                    };

                    // Keyboard navigation (desktop) + swipe navigation (touch/mobile)
                    document.addEventListener('keydown', (event) => {
                        const modal = document.getElementById('order-lightbox-modal');
                        if (!modal || modal.style.display !== 'flex') return;
                        if (event.key === 'Escape') window.closeOrderLightbox();
                        if (event.key === 'ArrowLeft') window.lightboxStep(-1);
                        if (event.key === 'ArrowRight') window.lightboxStep(1);
                    });

                    (function setupLightboxSwipe() {
                        let touchStartX = 0;
                        document.addEventListener('DOMContentLoaded', () => {
                            const modal = document.getElementById('order-lightbox-modal');
                            if (!modal) return;
                            modal.addEventListener('touchstart', (event) => {
                                touchStartX = event.changedTouches[0].screenX;
                            }, { passive: true });
                            modal.addEventListener('touchend', (event) => {
                                const deltaX = event.changedTouches[0].screenX - touchStartX;
                                if (Math.abs(deltaX) < 40) return;
                                window.lightboxStep(deltaX < 0 ? 1 : -1);
                            }, { passive: true });
                        });
                    })();

                    // HTML5 Drag and Drop Functions for Kanban Board
                    window.handleBoardCardDragStart = function(event) {
                        const card = event.target.closest('.board-order-card');
                        if (card) {
                            event.dataTransfer.setData("text/plain", card.dataset.id);
                            card.style.opacity = '0.4';
                        }
                    };

                    document.addEventListener('dragend', (event) => {
                        const card = event.target.closest('.board-order-card');
                        if (card) {
                            card.style.opacity = '1';
                        }
                    });

                    window.allowBoardCardDrop = function(event) {
                        event.preventDefault();
                        const column = event.target.closest('.board-column');
                        if (column) {
                            column.style.background = '#f1f5f9';
                        }
                    };

                    window.handleBoardCardDragLeave = function(event, columnElement) {
                        columnElement.style.background = '#f8fafc';
                    };

                    window.handleBoardCardDrop = function(event, columnElement) {
                        event.preventDefault();
                        columnElement.style.background = '#f8fafc';
                        
                        const cardId = event.dataTransfer.getData("text/plain");
                        const newStatus = columnElement.dataset.status;
                        
                        if (cardId && newStatus) {
                            const card = document.querySelector(`.board-order-card[data-id="${cardId}"]`);
                            if (card) {
                                const currentStatus = card.closest('.board-column').dataset.status;
                                if (currentStatus !== newStatus) {
                                    const cardsList = columnElement.querySelector('.board-cards-list');
                                    if (cardsList) {
                                        const emptyIndicator = cardsList.querySelector('div');
                                        if (emptyIndicator && emptyIndicator.innerText.includes('No orders here')) {
                                            emptyIndicator.remove();
                                        }
                                        cardsList.appendChild(card);
                                    }
                                    
                                    window.updateOrderStatus(cardId, newStatus);
                                }
                            }
                        }
                    };

                    // Initialize toggle state on page load
                    document.addEventListener('DOMContentLoaded', () => {
                        const stored = localStorage.getItem('baker_orders_view_type');
                        if (stored === 'board') {
                            switchOrdersView('board');
                        }
                    });
                </script>
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

                <div class="form-builder-workspace" style="display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 30px; align-items: start;">
                    
                    <!-- LEFT COLUMN: Editor Controls -->
                    <div class="form-builder-left-col" style="display: flex; flex-direction: column; gap: 20px;">
                        


                        <!-- ADD STEP / FIELD CARD -->
                        <div class="form-builder-card" style="margin-bottom: 0;">
                            <h4 style="color:#5c1d37; margin-bottom: 6px;">Add Step or Field</h4>
                            <p style="font-size:0.85rem; color:#666; margin-bottom:15px;">Add custom steps to gather flavors, details, or choices on your storefront order form.</p>
                            <form id="add-field-form" class="form-builder-grid">
                                <div style="grid-column: 1 / -1;">
                                    <label style="font-weight:700; color:#5c1d37; display:block; margin-bottom:10px;">Select Field Template</label>
                                    
                                    <!-- Hidden select input backing the builder state -->
                                    <select id="field-type" onchange="toggleOptionsRow(this.value)" style="display:none;">
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
                                    
                                    <!-- Visual Grid Selector Tiles -->
                                    <div class="template-selector-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 10px; max-height: 230px; overflow-y: auto; padding: 6px; border: 1px solid #f0e4ea; border-radius: 14px; background: #faf8f9; -webkit-overflow-scrolling: touch;">
                                        <div class="template-tile selected-tile" data-type="products" onclick="selectTemplateTile('products')">
                                            <span class="template-tile-icon">🧁</span>
                                            <span class="template-tile-label">Product Catalog</span>
                                        </div>
                                        <div class="template-tile" data-type="calendar" onclick="selectTemplateTile('calendar')">
                                            <span class="template-tile-icon">📅</span>
                                            <span class="template-tile-label">Booking Calendar</span>
                                        </div>
                                        <div class="template-tile" data-type="flavors" onclick="selectTemplateTile('flavors')">
                                            <span class="template-tile-icon">🍓</span>
                                            <span class="template-tile-label">Flavors Grid</span>
                                        </div>
                                        <div class="template-tile" data-type="frosting" onclick="selectTemplateTile('frosting')">
                                            <span class="template-tile-icon">🍦</span>
                                            <span class="template-tile-label">Frosting Grid</span>
                                        </div>
                                        <div class="template-tile" data-type="fillings" onclick="selectTemplateTile('fillings')">
                                            <span class="template-tile-icon">🍫</span>
                                            <span class="template-tile-label">Fillings Grid</span>
                                        </div>
                                        <div class="template-tile" data-type="fulfillment" onclick="selectTemplateTile('fulfillment')">
                                            <span class="template-tile-icon">🚚</span>
                                            <span class="template-tile-label">Fulfillment Slots</span>
                                        </div>
                                        <div class="template-tile" data-type="allergies" onclick="selectTemplateTile('allergies')">
                                            <span class="template-tile-icon">⚠️</span>
                                            <span class="template-tile-label">Allergy Notice</span>
                                        </div>
                                        <div class="template-tile" data-type="social_discount" onclick="selectTemplateTile('social_discount')">
                                            <span class="template-tile-icon">📢</span>
                                            <span class="template-tile-label">Social Discount</span>
                                        </div>
                                        <div class="template-tile" data-type="file_upload" onclick="selectTemplateTile('file_upload')">
                                            <span class="template-tile-icon">📸</span>
                                            <span class="template-tile-label">Photo Upload</span>
                                        </div>
                                        <div class="template-tile" data-type="terms" onclick="selectTemplateTile('terms')">
                                            <span class="template-tile-icon">📝</span>
                                            <span class="template-tile-label">Terms &amp; Conds</span>
                                        </div>
                                        <div class="template-tile" data-type="contact_info" onclick="selectTemplateTile('contact_info')">
                                            <span class="template-tile-icon">👤</span>
                                            <span class="template-tile-label">Contact Submit</span>
                                        </div>
                                        <div class="template-tile" data-type="text" onclick="selectTemplateTile('text')">
                                            <span class="template-tile-icon">✏️</span>
                                            <span class="template-tile-label">Single-Line Text</span>
                                        </div>
                                        <div class="template-tile" data-type="select" onclick="selectTemplateTile('select')">
                                            <span class="template-tile-icon">👇</span>
                                            <span class="template-tile-label">Dropdown Select</span>
                                        </div>
                                        <div class="template-tile" data-type="datepicker" onclick="selectTemplateTile('datepicker')">
                                            <span class="template-tile-icon">📅</span>
                                            <span class="template-tile-label">Date Picker</span>
                                        </div>
                                        <div class="template-tile" data-type="toggle" onclick="selectTemplateTile('toggle')">
                                            <span class="template-tile-icon">🔄</span>
                                            <span class="template-tile-label">Yes/No Toggle</span>
                                        </div>
                                        <div class="template-tile" data-type="textarea" onclick="selectTemplateTile('textarea')">
                                            <span class="template-tile-icon">📝</span>
                                            <span class="template-tile-label">Multi-Line Notes</span>
                                        </div>
                                    </div>
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
                        <div class="form-builder-card" style="margin-bottom: 0;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
                                <div>
                                    <h4 style="margin-bottom:4px; color:#5c1d37;">Configured Form Steps &amp; Fields</h4>
                                    <span style="font-size:0.85rem; color:#888; font-weight:500;">Drag rows, or use the arrows, to reorder steps.</span>
                                </div>
                                <div style="display:flex; align-items:center; gap:8px; background:#f0fdf4; border:1px solid #bbf7d0; padding:6px 14px; border-radius:20px; transition: all 0.3s ease;" id="autosave-indicator">
                                    <span style="font-size:0.75rem; color:#15803d; font-weight:800; display:flex; align-items:center; gap:6px;">
                                        <span style="display:inline-block; width:8px; height:8px; background:#22c55e; border-radius:50%; transition: all 0.3s ease;" id="autosave-dot"></span>
                                        <span id="autosave-text">Changes Auto-Saved</span>
                                    </span>
                                </div>
                            </div>

                            <div class="field-table-wrapper" style="border: none; background: transparent; padding: 0; box-shadow: none;">
                                <div id="custom-fields-cards-container" style="display:flex; flex-direction:column; gap:14px;">
                                    <div style="text-align:center; padding:32px; color:#aaa; font-size:0.95rem; background:#fff; border-radius:12px; border:1px dashed #f0e4ea;">
                                        Loading configured form steps…
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: Real-Time Mobile Preview Drawer -->
                    <div class="form-builder-right-col" style="position: sticky; top: 20px; display: flex; flex-direction: column; align-items: center; gap: 15px; width: 100%;">
                        
                        <div style="width:100%; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                            <div style="font-weight: 700; font-size: 0.85rem; color: #5c1d37; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 8px;">
                                <span>Storefront Form Preview</span>
                                <span style="background: #e67399; color: #fff; font-size: 0.65rem; padding: 2px 8px; border-radius: 20px; text-transform: none; font-weight: 800;">Real-Time</span>
                            </div>
                            <div class="preview-device-toggle">
                                <button type="button" class="preview-device-btn active" data-device="mobile" onclick="setOrderFormPreviewDevice('mobile', this)">Mobile</button>
                                <button type="button" class="preview-device-btn" data-device="desktop" onclick="setOrderFormPreviewDevice('desktop', this)">Desktop</button>
                            </div>
                        </div>

                        <!-- Phone/Desktop Frame Mockup -->
                        <div id="order-form-preview-frame" class="mobile-phone-frame" style="width: 100%; max-width: 320px; height: 560px; background: #ffffff; border: 12px solid #2d2419; border-radius: 40px; box-shadow: 0 20px 50px rgba(0,0,0,0.1); position: relative; overflow: hidden; display: flex; flex-direction: column; transition: max-width 0.25s ease, border-radius 0.25s ease, border-width 0.25s ease;">
                            <!-- Speaker / Notch -->
                            <div class="order-form-preview-notch" style="width: 110px; height: 18px; background: #2d2419; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px; position: absolute; top: 0; left: 50%; transform: translateX(-50%); z-index: 10; display: flex; justify-content: center; align-items: center;">
                                <div style="width: 40px; height: 4px; background: #555; border-radius: 10px;"></div>
                            </div>

                            <!-- Phone screen viewport -->
                            <div class="phone-screen-viewport" style="flex: 1; display: flex; flex-direction: column; overflow-y: auto; padding: 32px 16px 24px 16px; background: #fff5f8; font-family: 'Outfit', sans-serif;">
                                <!-- Storefront Header mockup -->
                                <div style="text-align: center; margin-bottom: 20px; border-bottom: 1px solid rgba(230,115,153,0.15); padding-bottom: 12px;">
                                    <h5 style="font-family: 'Great Vibes', cursive; font-size: 1.8rem; color: #5c1d37; margin: 0;">{{ $tenant->name }}</h5>
                                    <span style="font-size: 0.7rem; color: #888;">Storefront Order Form</span>
                                </div>

                                <!-- Live Interactive Preview Content -->
                                <div id="live-preview-viewport-content" style="flex: 1; display: flex; flex-direction: column; gap: 16px;">
                                    <!-- Populated dynamically via Javascript based on window._customFields -->
                                </div>
                            </div>

                            <!-- Home Bar -->
                            <div class="order-form-preview-home-bar" style="height: 15px; background: #ffffff; display: flex; justify-content: center; align-items: center; border-top: 1px solid #eee;">
                                <div style="width: 80px; height: 4px; background: #ccc; border-radius: 10px;"></div>
                            </div>
                        </div>

                        <!-- Quick guide -->
                        <span style="font-size: 0.78rem; color: #888; text-align: center; max-width: 280px; line-height: 1.4;">
                            Changes made to steps on the left will immediately sync inside this storefront preview mockup.
                        </span>
                    </div>
                </div>

            </div>

            <!-- TAB: Page Builder (Homepage Section & Content Accordion Studio) -->
            <div id="tab-page-builder" class="tab-content">
                <div class="section-header">
                    <h3>Page Builder</h3>
                    <p class="subtitle">Edit your homepage's text, images, and section order. Changes go live when you save.</p>
                </div>

                <div class="form-builder-workspace" id="page-builder-workspace" style="display:grid; grid-template-columns:0.8fr 1.2fr; gap:30px; align-items:start;">
                <div class="form-builder-left-col">
                <div class="form-builder-card" style="border:1px solid var(--theme-section-bg, #ddd6fe); padding:18px;">
                    <div style="display:flex; justify-content:flex-end; margin-bottom:12px;">
                        <button class="btn btn-primary" onclick="saveSectionManagerForm()" style="background:var(--primary); border-color:var(--primary);">Save All Changes</button>
                    </div>

                    <div id="section-manager-msg" style="display:none; margin-bottom:14px; background:var(--theme-section-bg, #ddd6fe); color:var(--dark-text); padding:10px 14px; border-radius:10px; font-size:0.88rem; font-weight:600; border:1px solid var(--theme-section-bg, #c4b5fd);"></div>

                    <form id="section-manager-form">
                        @csrf
                        {{-- Carries which tenant this save applies to when a superadmin is previewing
                             another bakery's CMS via /site/{subdomain}/dashboard — the save otherwise
                             posts to an un-scoped /dashboard/sections URL and would silently land on
                             the superadmin's own tenant instead. See AdminController::tenant(). --}}
                        <input type="hidden" name="subdomain" value="{{ $tenant->subdomain ?? $tenant->slug }}">
                        @php
                            $orderedSections = $tenant->getOrderedSections();
                            $siteContent = $tenant->site_content ?? App\Models\Tenant::getDefaultSiteContent();
                            $bullets = data_get($siteContent, 'whimsical_bullets', []);
                        @endphp

                        <div id="section-manager-list" style="display:flex; flex-direction:column; gap:6px;">
                            @foreach($orderedSections as $secId => $sec)
                                @php
                                    // Defensive: strips any leading emoji from section names saved to a
                                    // tenant's DB before Tenant::getDefaultSectionSettings() dropped them.
                                    $secName = trim(preg_replace('/^[^\p{L}\p{N}]+/u', '', $sec['name'] ?? $secId));
                                @endphp
                                <div class="section-manager-row" data-id="{{ $secId }}" style="background:white; border-radius:8px; border:1px solid #e5e7eb; overflow:hidden;">

                                    <!-- ACCORDION HEADER ROW -->
                                    <div class="section-accordion-header" onclick="toggleSectionAccordion(this)" style="padding:9px 12px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; cursor:pointer; background:#fafafa; user-select:none;">
                                        <div style="display:flex; align-items:center; gap:8px; min-width:0;">
                                            <span class="drag-handle" style="cursor:grab; font-weight:800; color:#a1a1aa; font-size:0.9rem; flex-shrink:0;" onclick="event.stopPropagation()">⠿</span>
                                            <input type="hidden" class="section-order-input" name="sections[{{ $secId }}][order]" value="{{ $sec['order'] ?? 1 }}">
                                            <strong style="color:#27272a; font-size:0.85rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $secName }}</strong>
                                        </div>

                                        <div style="display:flex; align-items:center; gap:0; flex-shrink:0;" onclick="event.stopPropagation()">
                                            <button type="button" class="section-move-btn" onclick="moveSectionUp(this)" aria-label="Move up" title="Move up">↑</button>
                                            <button type="button" class="section-move-btn" onclick="moveSectionDown(this)" aria-label="Move down" title="Move down">↓</button>
                                            <label class="toggle-switch" style="transform:scale(0.65); margin:0 -6px 0 -2px;">
                                                <input type="hidden" name="sections[{{ $secId }}][enabled]" value="0">
                                                <input type="checkbox" name="sections[{{ $secId }}][enabled]" value="1" {{ !empty($sec['enabled']) ? 'checked' : '' }}>
                                                <span class="toggle-slider"></span>
                                            </label>
                                            <span class="accordion-arrow" style="font-size:0.78rem; color:#a1a1aa; transition:transform 0.2s ease;">▾</span>
                                        </div>
                                    </div>

                                    <!-- EXPANDABLE ACCORDION BODY WITH SECTION COPY & CONTENT EDITORS -->
                                    <div class="section-accordion-body" style="display:none; padding:12px; border-top:1px solid #eee; background:#ffffff;">
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
                                            <div style="margin-bottom:10px;">
                                                <label style="font-weight:600; font-size:0.82rem; color:#555;">Scrolling Banner Text</label>
                                                <input type="text" name="marquee_text" value="{{ data_get($siteContent, 'marquee_text', 'Custom Cakes') }}" placeholder="e.g. Fresh Sourdough" style="width:100%; padding:9px; border-radius:8px; border:1px solid #ccc;">
                                                <span style="font-size:0.75rem; color:#888;">The repeating scrolling text banner shown on the homepage.</span>
                                            </div>
                                            <div>
                                                <label style="font-weight:600; font-size:0.82rem; color:#555; display:block; margin-bottom:6px;">Specialty Bullets</label>
                                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                                                    <input type="text" name="whimsical_bullet_1" value="{{ $bullets[0] ?? '' }}" placeholder="Bullet 1..." style="width:100%; padding:8px; border-radius:8px; border:1px solid #ccc;">
                                                    <input type="text" name="whimsical_bullet_2" value="{{ $bullets[1] ?? '' }}" placeholder="Bullet 2..." style="width:100%; padding:8px; border-radius:8px; border:1px solid #ccc;">
                                                    <input type="text" name="whimsical_bullet_3" value="{{ $bullets[2] ?? '' }}" placeholder="Bullet 3..." style="width:100%; padding:8px; border-radius:8px; border:1px solid #ccc;">
                                                    <input type="text" name="whimsical_bullet_4" value="{{ $bullets[3] ?? '' }}" placeholder="Bullet 4..." style="width:100%; padding:8px; border-radius:8px; border:1px solid #ccc;">
                                                    <input type="text" name="whimsical_bullet_5" value="{{ $bullets[4] ?? '' }}" placeholder="Bullet 5..." style="width:100%; padding:8px; border-radius:8px; border:1px solid #ccc;">
                                                </div>
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
                                                    <div>
                                                        <label style="font-weight:600; font-size:0.82rem; color:#555;">Button Links To</label>
                                                        @php $ctaBtnAction = data_get($siteContent, 'cta_btn_action', 'order'); @endphp
                                                        <select name="cta_btn_action" style="width:100%; padding:9px; border-radius:8px; border:1px solid #ccc;">
                                                            <option value="order" {{ $ctaBtnAction === 'order' ? 'selected' : '' }}>Custom Order Form</option>
                                                            <option value="menu" {{ $ctaBtnAction === 'menu' ? 'selected' : '' }}>Menu Page</option>
                                                        </select>
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

                <!-- LIVE PREVIEW PANEL -->
                <div class="form-builder-right-col" style="position:sticky; top:20px;">
                    <div class="form-builder-card" style="padding:16px; border:1px solid var(--theme-section-bg, #ddd6fe);">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; flex-wrap:wrap; gap:8px;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <strong style="font-size:0.9rem; color:#27272a;">Preview</strong>
                                <span id="page-builder-preview-status" style="display:none; font-size:0.74rem; color:#a855f7; font-weight:600;">Updating preview…</span>
                            </div>
                            <div style="display:flex; gap:6px; align-items:center; flex-wrap:wrap;">
                                <div class="preview-device-toggle">
                                    <button type="button" class="preview-device-btn active" data-device="mobile" onclick="setPreviewDevice('mobile', this)">Mobile</button>
                                    <button type="button" class="preview-device-btn" data-device="desktop" onclick="setPreviewDevice('desktop', this)">Desktop</button>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline" onclick="refreshPageBuilderPreview()" title="Discard draft preview and show what's actually live" style="padding:4px 10px; font-size:0.78rem;">↻ Show Live</button>
                                <a href="{{ $tenant->publicUrl() }}" target="_blank" class="btn btn-sm btn-outline" title="Open the live site in a new tab" style="padding:4px 10px; font-size:0.78rem;">Open ↗</a>
                            </div>
                        </div>
                        <div id="page-builder-preview-wrapper" style="border:8px solid #27272a; border-radius:18px; overflow:hidden; background:#27272a;">
                            <div id="page-builder-preview-scale-box" style="width:100%; height:640px; overflow:hidden; position:relative; background:#fff;">
                                <iframe id="page-builder-preview-iframe" src="{{ $tenant->publicUrl() }}" title="Storefront preview" style="width:100%; height:100%; border:0; display:block; background:#fff; transform-origin:top left;"></iframe>
                            </div>
                        </div>
                        <p style="font-size:0.76rem; color:#999; margin-top:8px; margin-bottom:0;">Shows your unsaved edits as you type — nothing here is public until you click <strong>Save All Changes</strong>. Use <strong>Show Live</strong> to see what's actually published right now.</p>
                    </div>
                </div>
                </div>
            </div>

            <div id="tab-products" class="tab-content">
                <div class="section-header">
                    <h3>Products</h3>
                    <p class="subtitle">Add, remove, and update prices for your order form products.</p>
                </div>

                <!-- COLLAPSIBLE ADD PRODUCT DRAWER -->
                <div class="form-builder-card" id="add-product-drawer-card" style="border:2px solid var(--primary); background:var(--theme-section-bg, #fff7fa); margin-bottom:20px; padding:0; overflow:hidden; transition: box-shadow 0.2s ease;">
                    <div onclick="toggleAddProductDrawer()" style="display:flex; justify-content:space-between; align-items:center; cursor:pointer; padding:16px 20px; user-select:none;">
                        <h4 style="color:#5c1d37; margin:0; font-size:1.1rem; font-weight:700;">+ Add New Product</h4>
                        <span id="add-product-drawer-chevron" style="font-size:0.9rem; color:var(--primary); font-weight:bold;">▼</span>
                    </div>
                    
                    <div id="add-product-drawer-content" style="display:none; padding:0 20px 20px 20px; border-top:1px solid #f0e4ea; margin-top:0;">
                        <form id="add-product-form" class="form-builder-grid" action="{{ route('admin.products.store') }}" method="POST" style="margin-top:16px;">
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
                            <div style="grid-column: 1 / -1; margin-top:8px;">
                                <button type="submit" class="btn btn-primary" style="width:100%;">+ Add Product to Catalog</button>
                            </div>
                        </form>
                    </div>
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
                        <!-- Menu Display Type Selector Cards -->
                        <div style="margin-bottom: 24px;">
                            <label style="font-weight:700; color:#5c1d37; font-size:0.95rem; display:block; margin-bottom:12px;">Menu Source Option</label>
                            <input type="hidden" name="menu_type" id="admin_menu_type" value="{{ $menuType }}">
                            
                            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:16px;">
                                <!-- Option 1: Digital Products Catalog -->
                                <div class="menu-source-card {{ $menuType !== 'image' ? 'active-source' : '' }}" onclick="switchMenuSource('text')" style="background:#fff; border:2px solid #f0e4ea; border-radius:14px; padding:18px; cursor:pointer; transition:all 0.2s ease;">
                                    <div style="display:flex; align-items:center; gap:12px; margin-bottom:8px;">
                                        <span style="font-size:1.8rem;">🍰</span>
                                        <div style="font-weight:700; color:#5c1d37; font-size:1rem;">Products Catalog Menu</div>
                                    </div>
                                    <p style="font-size:0.8rem; color:#666; margin:0; line-height:1.4;">Auto-generate a beautiful interactive menu on your public website directly from the products in your inventory.</p>
                                </div>
                                
                                <!-- Option 2: Uploaded Menu Image / PDF -->
                                <div class="menu-source-card {{ $menuType === 'image' ? 'active-source' : '' }}" onclick="switchMenuSource('image')" style="background:#fff; border:2px solid #f0e4ea; border-radius:14px; padding:18px; cursor:pointer; transition:all 0.2s ease;">
                                    <div style="display:flex; align-items:center; gap:12px; margin-bottom:8px;">
                                        <span style="font-size:1.8rem;">📸</span>
                                        <div style="font-weight:700; color:#5c1d37; font-size:1rem;">Upload Menu Image / PDF</div>
                                    </div>
                                    <p style="font-size:0.8rem; color:#666; margin:0; line-height:1.4;">Upload a picture or PDF document of your bakery's print menu to display directly on your storefront.</p>
                                </div>
                            </div>
                        </div>

                        <!-- SOURCE SECTION 1: Product Catalog Note -->
                        <div id="source-sect-catalog" style="display: {{ $menuType !== 'image' ? 'block' : 'none' }}; margin-bottom: 24px; background: #fff5f8; border: 1px solid #f8c6d7; border-radius: 12px; padding: 16px;">
                            <span style="font-weight:700; color:#5c1d37; font-size:0.9rem; display:block; margin-bottom:4px;">✓ Catalog Integration Active</span>
                            <span style="font-size:0.82rem; color:#666; line-height:1.4;">Your menu is auto-generated using items in your catalog list above. Customers can browse these goods in the styled theme design on your public <a href="/menu" target="_blank" style="color:var(--primary); font-weight:700; text-decoration:underline;">/menu</a> page.</span>
                        </div>

                        <!-- SOURCE SECTION 2: Menu File Upload -->
                        <div id="source-sect-upload" style="display: {{ $menuType === 'image' ? 'block' : 'none' }}; margin-bottom: 24px; background:#ffffff; border:1px solid #f0e4ea; border-radius:14px; padding:20px;">
                            <label style="font-weight:700; color:#5c1d37; font-size:0.9rem; display:block; margin-bottom:10px;">
                                Upload Official Bakery Menu Image/PDF
                            </label>

                            @if($menuImagePath)
                                <div style="background:#f0fdf4; border:1.5px solid #22c55e; border-radius:12px; padding:12px 16px; margin-bottom:14px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
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
                                    <label style="background:#fee2e2; color:#dc2626; border:1px solid #fca5a5; padding:4px 10px; border-radius:8px; font-weight:700; font-size:0.8rem; cursor:pointer; margin: 0;">
                                        <input type="checkbox" name="remove_menu_image" value="1"> Delete Active File
                                    </label>
                                </div>
                                <small style="color:#64748b; font-size:0.8rem; display:block; margin-bottom:6px;">Upload new file below to replace current file:</small>
                            @else
                                <div style="background:#faf8f9; border:1px dashed #f8c6d7; border-radius:8px; padding:12px; margin-bottom:12px; font-size:0.82rem; color:#888;">
                                    No official menu image/PDF uploaded yet. Select a file below to upload.
                                </div>
                            @endif

                            <input type="file" name="menu_image" id="admin_menu_image" accept="image/*,.pdf" class="form-input" style="width:100%; padding:8px; border-radius:8px; border:1px solid #cbd5e1; background:#fff;">
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
                @php $galleryCategories = $tenant->galleryCategories(); @endphp
                <script>window.galleryCategories = @json($galleryCategories);</script>
                <div class="section-header">
                    <h3>Device Gallery</h3>
                    <p class="subtitle">Upload photos from your computer, phone, or tablet. They'll publish to your public <strong>/gallery</strong> page.</p>
                </div>

                <!-- MANAGE CATEGORIES -->
                <div class="form-builder-card">
                    <h4>Gallery Categories</h4>
                    <p class="subtitle">These show up as filter buttons on your public gallery page and as options when tagging a photo.</p>
                    <div id="gallery-category-chips" style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:14px;">
                        @foreach($galleryCategories as $cat)
                            <span class="gallery-category-chip" data-category="{{ $cat }}" style="display:flex; align-items:center; gap:6px; background:var(--theme-section-bg, #fff7fa); border:1px solid #f0d4e4; color:#5c1d37; font-weight:600; font-size:0.85rem; padding:6px 8px 6px 14px; border-radius:20px;">
                                <span class="gallery-category-chip-label">{{ $cat }}</span>
                                <button type="button" onclick="removeGalleryCategory('{{ $cat }}', this)" style="background:none; border:none; color:#a1a1aa; cursor:pointer; font-size:0.95rem; line-height:1; padding:2px 4px;" title="Remove category">✕</button>
                            </span>
                        @endforeach
                    </div>
                    <form id="add-gallery-category-form" style="display:flex; gap:10px; max-width:400px;">
                        <input type="text" id="new-gallery-category-name" placeholder="e.g. Birthday Cakes" style="flex:1; padding:9px 12px; border-radius:8px; border:1px solid #e2d9de;" maxlength="50" required>
                        <button type="submit" class="btn btn-outline" style="white-space:nowrap;">+ Add Category</button>
                    </form>
                </div>

                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:20px;">
                    <div class="form-builder-card">
                        <h4>Upload Photo From Device</h4>
                        <form id="add-gallery-form" action="{{ route('admin.gallery.store') }}" method="POST" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:18px;">
                            @csrf
                            <div>
                                <label>Gallery Category</label>
                                <select id="gal-category" name="category">
                                    @foreach($galleryCategories as $cat)
                                        <option value="{{ $cat }}">{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- DEVICE FILE PICKER & DROPZONE -->
                            <div>
                                <label>Select Image Files From Your Device</label>
                                <div id="gal-device-dropzone" style="border:2px dashed var(--primary); background:var(--theme-section-bg, #fff7fa); padding:30px 20px; border-radius:16px; text-align:center; cursor:pointer;" onclick="document.getElementById('gal-image-file').click();">
                                    <p style="font-size:1.05rem; font-weight:600; color:#5c1d37;" id="gal-dropzone-text">Click to select photos from device or drag images here</p>
                                    <span style="font-size:12px; color:#888;">Select multiple at once — Supports JPG, PNG, WEBP, GIF (Up to 10MB each)</span>
                                </div>
                                <input type="file" id="gal-image-file" name="images[]" accept="image/*" multiple style="display:none;" required>
                            </div>

                            <!-- LIVE PREVIEW CONTAINER -->
                            <div id="gal-upload-preview" style="display:none;">
                                <div id="gal-preview-grid" style="display:flex; flex-wrap:wrap; gap:12px; justify-content:center;"></div>
                                <p style="font-weight:700; color:#28a745; margin-top:10px; font-size:0.9rem; text-align:center;" id="gal-preview-status">Photos ready for publish</p>
                            </div>

                            <button type="submit" id="gal-submit-btn" class="btn btn-primary" style="padding:14px;">Publish Photos to Live Gallery</button>
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
                                        <select class="gallery-item-category-select" onchange="updateGalleryItemCategory({{ $item->id }}, this)" style="padding:7px 10px; border-radius:8px; border:1px solid #e2d9de; font-size:0.85rem; font-weight:600; color:var(--primary);">
                                            @foreach($galleryCategories as $cat)
                                                <option value="{{ $cat }}" {{ $item->category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                            @endforeach
                                            @if(!in_array($item->category, $galleryCategories, true))
                                                <option value="{{ $item->category }}" selected>{{ $item->category }} (removed)</option>
                                            @endif
                                        </select>
                                    </div>
                                    <button class="btn btn-sm btn-outline" style="color:#d9534f; border-color:#d9534f;" onclick="deleteGalleryItem({{ $item->id }}, this)">Delete</button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 4: Invoices & Payment Handles Manager -->
            <div id="tab-invoices" class="tab-content">
                <div class="section-header">
                    <h3>Invoices &amp; Payments</h3>
                    <p class="subtitle">Add payment methods and generate digital client invoices.</p>
                </div>

                <script>window.tenantHasPaymentMethods = @json(!empty($tenant->normalizedPaymentMethods()));</script>

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
                        <tbody id="admin-invoices-tbody">
                            @forelse($invoices as $inv)
                                <tr id="invoice-row-{{ $inv->id }}" style="border-bottom:1px solid #f0e4ea;" data-invoice="{{ json_encode([
                                    'id' => $inv->id,
                                    'order_id' => $inv->order_id,
                                    'subtotal' => (float) ($inv->subtotal ?? $inv->total_amount),
                                    'total_amount' => (float) $inv->total_amount,
                                    'deposit_amount' => (float) ($inv->deposit_amount ?? 0),
                                    'fee_amount' => (float) ($inv->fee_amount ?? 0),
                                    'fee_label' => $inv->fee_label,
                                    'discount_amount' => (float) ($inv->discount_amount ?? 0),
                                    'discount_label' => $inv->discount_label,
                                    'misc_amount' => (float) ($inv->misc_amount ?? 0),
                                    'misc_label' => $inv->misc_label,
                                    'notes' => $inv->notes,
                                ]) }}">
                                    <td style="padding:12px 8px; font-family:monospace;">{{ $inv->invoice_number }}</td>
                                    <td style="padding:12px 8px;">{{ $inv->client_name }}</td>
                                    <td class="invoice-amount-cell" style="padding:12px 8px; font-weight:700;">${{ number_format($inv->total_amount, 2) }}</td>
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
                                        <button class="btn btn-sm btn-outline" onclick="openInvoiceEditModal(this)">Edit</button>
                                        <button class="btn btn-sm btn-primary" onclick="sendInvoice('{{ $inv->id }}')">Send</button>
                                        <button class="btn btn-sm btn-outline" style="color:#d9534f; border-color:#d9534f;" onclick="deleteInvoice({{ $inv->id }}, this)">Delete</button>
                                    </td>
                                </tr>
                            @empty
                                <tr id="no-invoices-row">
                                    <td colspan="5" style="text-align:center; padding:20px; color:#888;">No invoices created yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- ACCEPTED PAYMENT METHODS: CHECKBOX + HANDLE -->
                <div class="form-builder-card" style="border:2px solid var(--primary); background:var(--theme-section-bg, #fff7fa);">
                    <h4 style="color:#5c1d37;">Accepted Payment Methods</h4>
                    <p class="subtitle">Check the payment methods you accept, then enter your handle, username, or email for each one. Customers will see these on their invoice — you need at least one set up before you can send an invoice.</p>
                    <style>
                        .pm-icon-badge { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:1rem; flex-shrink:0; box-shadow: 0 2px 5px rgba(0,0,0,0.12); }
                        .payment-method-toggle-row { transition: border-color 0.15s ease, background 0.15s ease; }
                        .payment-method-toggle-row.pm-checked { border-color: var(--primary) !important; background: #fffafc; }
                        .payment-method-toggle-row:hover { border-color: #e0c3d1; }
                    </style>
                    <form id="payment-methods-form" style="display:flex; flex-direction:column; gap:12px;">
                        @php
                            $knownPaymentMethods = [
                                'venmo' => ['label' => 'Venmo', 'placeholder' => '@YourVenmoHandle', 'icon' => 'V', 'bg' => '#3D95CE', 'fg' => '#ffffff'],
                                'cashapp' => ['label' => 'Cash App', 'placeholder' => '$YourCashtag', 'icon' => '$', 'bg' => '#00D632', 'fg' => '#ffffff'],
                                'zelle' => ['label' => 'Zelle', 'placeholder' => 'you@email.com or phone number', 'icon' => 'Z', 'bg' => '#6D1ED4', 'fg' => '#ffffff'],
                                'paypal' => ['label' => 'PayPal', 'placeholder' => 'https://paypal.me/you or email', 'icon' => 'P', 'bg' => '#003087', 'fg' => '#ffffff'],
                                'square' => ['label' => 'Square', 'placeholder' => 'Your Square payment link', 'icon' => '■', 'bg' => '#1a1a1a', 'fg' => '#ffffff'],
                                'apple_pay' => ['label' => 'Apple Pay', 'placeholder' => 'Phone number or email', 'icon' => '', 'bg' => '#000000', 'fg' => '#ffffff'],
                                'stripe' => ['label' => 'Stripe', 'placeholder' => 'Your Stripe payment link', 'icon' => 'S', 'bg' => '#635BFF', 'fg' => '#ffffff'],
                            ];
                            $existingPayments = is_array($tenant->payment_settings ?? null) ? $tenant->payment_settings : [];
                        @endphp
                        @foreach($knownPaymentMethods as $pmKey => $pmMeta)
                            @php $pmExisting = is_string($existingPayments[$pmKey] ?? null) ? trim($existingPayments[$pmKey]) : ''; @endphp
                            <div class="payment-method-toggle-row {{ $pmExisting !== '' ? 'pm-checked' : '' }}" id="pm-row-{{ $pmKey }}" style="border:1.5px solid #eee; border-radius:12px; padding:14px 16px; background:white;">
                                <label style="display:flex; align-items:center; gap:12px; font-weight:700; color:#5c1d37; cursor:pointer; margin:0;">
                                    <input type="checkbox" class="pm-toggle" id="pm-toggle-{{ $pmKey }}" data-key="{{ $pmKey }}" {{ $pmExisting !== '' ? 'checked' : '' }} onchange="togglePaymentMethodInput('{{ $pmKey }}')" style="width:18px; height:18px; accent-color: var(--primary); cursor:pointer; flex-shrink:0;">
                                    <span class="pm-icon-badge" style="background:{{ $pmMeta['bg'] }}; color:{{ $pmMeta['fg'] }};">{{ $pmMeta['icon'] }}</span>
                                    <span style="font-size:1rem;">{{ $pmMeta['label'] }}</span>
                                </label>
                                <div class="pm-handle-wrap" id="pm-handle-wrap-{{ $pmKey }}" style="{{ $pmExisting !== '' ? '' : 'display:none;' }} margin-top:10px; padding-left:50px;">
                                    <input type="text" id="pm-handle-{{ $pmKey }}" placeholder="{{ $pmMeta['placeholder'] }}" value="{{ $pmExisting }}" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #e2d9de; font-size:0.9rem;">
                                </div>
                            </div>
                        @endforeach
                        <button type="submit" class="btn btn-primary" style="align-self:flex-start;">Save Payment Methods</button>
                    </form>
                </div>
            </div>

            <!-- TAB 5: Customer Reviews -->
            <div id="tab-reviews" class="tab-content">
                <div class="section-header">
                    <h3>Client Reviews</h3>
                    <p class="subtitle">Manage reviews and testimonials shown on your storefront.</p>
                </div>

                <div class="reviews-tab-grid" style="display:grid; grid-template-columns:minmax(280px, 380px) 1fr; gap:20px; align-items:start;">
                    <!-- ADD NEW REVIEW CARD -->
                    <div class="form-builder-card" style="border:2px solid var(--primary); background:var(--theme-section-bg, #fff7fa);">
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

                <!-- Sub-Navigation for Settings Tab (Hick's Law Optimization) -->
                <div class="settings-subnav" style="display:flex; gap:10px; overflow-x:auto; margin-bottom:24px; border-bottom:1px solid #e2e8f0; padding-bottom:12px; -webkit-overflow-scrolling: touch;">
                    <button type="button" class="btn btn-sm btn-outline active-toggle-btn" id="settings-subnav-brand" onclick="switchSettingsSection('brand')" style="border:none; border-radius:8px; padding:6px 14px; font-weight:600; cursor:pointer; background:transparent; color:#555;">Brand &amp; Theme</button>
                    <button type="button" class="btn btn-sm btn-outline" id="settings-subnav-booking" onclick="switchSettingsSection('booking')" style="border:none; border-radius:8px; padding:6px 14px; font-weight:600; cursor:pointer; background:transparent; color:#555;">Booking Rules</button>
                    <button type="button" class="btn btn-sm btn-outline" id="settings-subnav-domains" onclick="switchSettingsSection('domains')" style="border:none; border-radius:8px; padding:6px 14px; font-weight:600; cursor:pointer; background:transparent; color:#555;">Plan &amp; Domains</button>
                    <button type="button" class="btn btn-sm btn-outline" id="settings-subnav-support" onclick="switchSettingsSection('support')" style="border:none; border-radius:8px; padding:6px 14px; font-weight:600; cursor:pointer; background:transparent; color:#555;">Account &amp; Support</button>
                </div>

                <!-- SECTION 1: Brand & Theme -->
                <div id="settings-sect-brand">
                    <!-- EMAIL ROUTING SETTINGS CARD -->
                    <div class="form-builder-card" style="border: 2px solid var(--primary); background: var(--theme-section-bg, #fff7fa); margin-bottom: 20px;">
                        <h4 style="color:#5c1d37; margin-bottom: 6px;">Order Email Routing</h4>
                        <p style="font-size:0.85rem; color:#666; margin-bottom:12px;">All completed storefront order entries will be sent to this address:</p>
                        <form id="email-routing-form" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
                            <input type="email" id="admin-routing-email" value="{{ $tenant->email ?? '' }}" placeholder="e.g. baker@yourbakehouse.com" required style="flex:1; min-width:220px; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
                            <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">Save</button>
                        </form>
                        <div id="email-save-status" style="margin-top:10px; font-weight:700; color:#28a745; font-size:0.88rem; display:none;"></div>
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
                                $starterThemeIds = array_keys(\App\Models\Tenant::getStarterThemes());
                                usort($themes, fn($a, $b) => in_array($b['id'], $starterThemeIds) <=> in_array($a['id'], $starterThemeIds));
                                $currentTheme = $tenant->theme_id ?? 'sweet_elegant';
                            @endphp
                            @foreach($themes as $t)
                                @php
                                    $isStarterTheme = in_array($t['id'], $starterThemeIds);
                                    $isLockedTheme = ($tenant->plan_tier !== 'pro') && !$isStarterTheme;
                                @endphp
                                <div class="bakery-theme-card"
                                     onclick="{{ $isLockedTheme ? "alert('Upgrade to Pro ($29/mo) to unlock this premium theme!')" : "selectBakeryTheme('".$t['id']."', this, ".\Illuminate\Support\Js::from($t['name']).")" }}"
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
                </div>

                <!-- SECTION 2: Booking Rules -->
                <div id="settings-sect-booking" style="display:none;">
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
                </div>

                <!-- SECTION 3: Plan & Domains -->
                <div id="settings-sect-domains" style="display:none;">
                    <!-- SUBSCRIPTION CARD -->
                    <div class="form-builder-card" style="margin-bottom:20px;">
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
                                <p style="font-size:0.9rem; color:#555; margin-top:4px; margin-bottom:16px;">Unlock all {{ count(\App\Models\Tenant::getAllThemes()) - count(\App\Models\Tenant::getStarterThemes()) - 1 }} premium themes, custom domain support, email marketing, and priority baker support.</p>

                                <a href="https://buy.stripe.com/eVq00jeoj4aB62QanW2Ry0k?client_reference_id={{ $tenant->id }}&prefilled_email={{ urlencode($tenant->email ?? '') }}" target="_blank" class="btn btn-primary" style="background:linear-gradient(135deg, #6d28d9, #8b5cf6) !important; color:#ffffff !important; font-weight:700; border-radius:12px; text-align:center; box-shadow:0 4px 12px rgba(109,40,217,0.3); text-decoration:none; display:block; padding:12px 18px;">
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
                        <div class="form-builder-card">
                            <h4 style="font-size:1.2rem; font-weight:700; color:#1e293b; margin-bottom:12px;">Custom Domain Connection</h4>
                            <p style="font-size:0.9rem; color:#555; margin-bottom:18px;">Connect your own domain so your bakery appears on a branded address like <strong>yourbakery.com</strong>.</p>
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
                </div>

                <!-- SECTION 4: Security & Support -->
                <div id="settings-sect-support" style="display:none;">
                    <!-- CHANGE PASSWORD CARD -->
                    <div class="form-builder-card" style="margin-bottom:20px;">
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

                    <!-- SUPPORT TICKET FORM CARD -->
                    <div class="form-builder-card">
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
                                <textarea id="ticket_message" name="message" required rows="4" class="form-input" placeholder="Tell our support team how we can assist your bakery..." style="width:100%; padding:10px; border-radius:8px; border:1px solid #cbd5e1; font-family:inherit;"></textarea>
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

            // UI/UX Optimizations: Settings Section Tabs Switcher
            window.switchSettingsSection = function(sectionId) {
                const sections = ['brand', 'booking', 'domains', 'support'];
                sections.forEach(sect => {
                    const btn = document.getElementById(`settings-subnav-${sect}`);
                    const content = document.getElementById(`settings-sect-${sect}`);
                    if (sect === sectionId) {
                        btn?.classList.add('active-toggle-btn');
                        if (content) content.style.display = 'block';
                    } else {
                        btn?.classList.remove('active-toggle-btn');
                        if (content) content.style.display = 'none';
                    }
                });
                localStorage.setItem('baker_settings_active_section', sectionId);
            };

            // UI/UX Optimizations: Collapsible Add Product Drawer
            window.toggleAddProductDrawer = function() {
                const content = document.getElementById('add-product-drawer-content');
                const chevron = document.getElementById('add-product-drawer-chevron');
                const card = document.getElementById('add-product-drawer-card');
                if (content && chevron) {
                    if (content.style.display === 'none') {
                        content.style.display = 'block';
                        chevron.innerText = '▲';
                        if (card) card.style.boxShadow = '0 10px 30px rgba(230, 115, 153, 0.15)';
                    } else {
                        content.style.display = 'none';
                        chevron.innerText = '▼';
                        if (card) card.style.boxShadow = 'none';
                    }
                }
            };

            // UI/UX Optimizations: Sync Visual template tiles selector with hidden select
            window.selectTemplateTile = function(typeValue) {
                const select = document.getElementById('field-type');
                if (select) {
                    select.value = typeValue;
                    select.dispatchEvent(new Event('change'));
                }
                document.querySelectorAll('.template-tile').forEach(tile => {
                    if (tile.dataset.type === typeValue) {
                        tile.classList.add('selected-tile');
                    } else {
                        tile.classList.remove('selected-tile');
                    }
                });
            };

            // UI/UX Optimizations: Menu Source Toggle Cards Switcher
            window.switchMenuSource = function(sourceType) {
                const hiddenInput = document.getElementById('admin_menu_type');
                if (hiddenInput) {
                    hiddenInput.value = sourceType;
                }
                
                const catalogSect = document.getElementById('source-sect-catalog');
                const uploadSect = document.getElementById('source-sect-upload');
                
                if (sourceType === 'image') {
                    if (catalogSect) catalogSect.style.display = 'none';
                    if (uploadSect) uploadSect.style.display = 'block';
                } else {
                    if (catalogSect) catalogSect.style.display = 'block';
                    if (uploadSect) uploadSect.style.display = 'none';
                }
                
                // Toggle active visual CSS classes
                document.querySelectorAll('.menu-source-card').forEach(card => {
                    const clickFn = card.getAttribute('onclick') || '';
                    if (clickFn.includes(sourceType)) {
                        card.classList.add('active-source');
                    } else {
                        card.classList.remove('active-source');
                    }
                });
            };

            // Auto-restore settings subnav view state on DOMContentLoaded
            document.addEventListener('DOMContentLoaded', () => {
                const storedSect = localStorage.getItem('baker_settings_active_section');
                if (storedSect) {
                    switchSettingsSection(storedSect);
                } else {
                    switchSettingsSection('brand');
                }
            });
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
                    <label style="display:block; margin-bottom:5px; font-weight:bold; font-size:0.9rem; color:#444;">Order Subtotal ($)</label>
                    <input type="number" step="0.01" id="edit-invoice-subtotal" class="form-control" oninput="recalculateInvoiceTotal()" style="width: 100%; padding: 10px 14px; border-radius: 10px; border: 1px solid #f0e4ea; font-size:1rem;">
                </div>

                <div style="margin-bottom: 15px; padding:12px; background:#fafafa; border-radius:10px; border:1px solid #f0e4ea;">
                    <p style="font-size:0.78rem; font-weight:700; color:#888; text-transform:uppercase; letter-spacing:0.03em; margin:0 0 10px;">Adjustments (optional)</p>

                    <div style="display:flex; gap:10px; margin-bottom:10px;">
                        <input type="text" id="edit-invoice-fee-label" placeholder="Fee label (e.g. Delivery Fee)" style="flex:1; padding:9px 12px; border-radius:8px; border:1px solid #e2d9de; font-size:0.85rem;">
                        <input type="number" step="0.01" id="edit-invoice-fee-amount" placeholder="0.00" oninput="recalculateInvoiceTotal()" style="width:110px; padding:9px 12px; border-radius:8px; border:1px solid #e2d9de; font-size:0.85rem;">
                    </div>
                    <div style="display:flex; gap:10px; margin-bottom:10px;">
                        <input type="text" id="edit-invoice-discount-label" placeholder="Discount label (e.g. Coupon SWEET20)" style="flex:1; padding:9px 12px; border-radius:8px; border:1px solid #e2d9de; font-size:0.85rem;">
                        <input type="number" step="0.01" id="edit-invoice-discount-amount" placeholder="0.00" oninput="recalculateInvoiceTotal()" style="width:110px; padding:9px 12px; border-radius:8px; border:1px solid #e2d9de; font-size:0.85rem;">
                    </div>
                    <div style="display:flex; gap:10px;">
                        <input type="text" id="edit-invoice-misc-label" placeholder="Misc label (e.g. Rush order)" style="flex:1; padding:9px 12px; border-radius:8px; border:1px solid #e2d9de; font-size:0.85rem;">
                        <input type="number" step="0.01" id="edit-invoice-misc-amount" placeholder="0.00" oninput="recalculateInvoiceTotal()" style="width:110px; padding:9px 12px; border-radius:8px; border:1px solid #e2d9de; font-size:0.85rem;">
                    </div>
                    <p style="font-size:0.75rem; color:#999; margin:10px 0 0;">Fees and misc add to the total; discount subtracts. Leave amount at 0 to skip a row.</p>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom: 15px;">
                    <div>
                        <label style="display:block; margin-bottom:5px; font-weight:bold; font-size:0.9rem; color:#444;">Total Amount ($)</label>
                        <input type="number" step="0.01" id="edit-invoice-total" class="form-control" required style="width: 100%; padding: 10px 14px; border-radius: 10px; border: 1px solid #f0e4ea; font-size:1rem;">
                    </div>
                    <div>
                        <label style="display:block; margin-bottom:5px; font-weight:bold; font-size:0.9rem; color:#444;">Deposit Amount ($)</label>
                        <input type="number" step="0.01" id="edit-invoice-deposit" class="form-control" required style="width: 100%; padding: 10px 14px; border-radius: 10px; border: 1px solid #f0e4ea; font-size:1rem;">
                    </div>
                </div>
                <p style="font-size:0.78rem; color:#999; margin:-10px 0 15px;">Total is auto-filled from subtotal + adjustments above — feel free to type your own final number instead.</p>

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
