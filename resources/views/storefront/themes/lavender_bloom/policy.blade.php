<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('storefront.partials.seo_head', ['page' => 'policy'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700;800&family=Poppins:wght@400;500;600;700;800&family=Great+Vibes&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/storefront-base.css') }}">
    <link rel="stylesheet" href="{{ asset($tenant->themeCssPath()) }}">
</head>
<body class="theme-{{ $tenant->theme_id ?? 'sweet_elegant' }}">

<header class="site-header lb-two-tier-header">
    <div class="lb-header-top">
        <div class="header-container">
            <a href="{{ route('storefront.index') }}" class="logo">
                @if(!empty($tenant->logo_path))
                    <img src="{{ asset($tenant->logo_path) }}" alt="{{ $tenant->name }} Logo" style="max-height:48px; width:auto; object-fit:contain;">
                @else
                    <span class="lb-logo-stamp"><span class="material-symbols-outlined" style="font-size:1.2rem; vertical-align:-3px;">local_florist</span> {{ $tenant->name }}</span>
                @endif
            </a>
            @if(Auth::check() && Auth::user()->tenant_id === $tenant->id)
                @php
                    $navBakerSub = request()->route('subdomain') ?? $tenant->subdomain ?? $tenant->slug;
                    $navBakerPortalUrl = request()->is('site/*')
                        ? url('/site/' . $navBakerSub . '/dashboard')
                        : route('baker.dashboard');
                @endphp
                <a href="{{ $navBakerPortalUrl }}" class="lb-header-dashboard-link">Dashboard</a>
            @endif
        </div>
    </div>
    <div class="lb-header-nav-band">
        <div class="lb-header-nav-inner">
            <nav class="nav-links">
                <a href="{{ route('storefront.index') }}">Home</a>
                <a href="{{ route('storefront.about') }}">About</a>
                <a href="{{ route('storefront.menu') }}">Menu</a>
                <a href="{{ route('storefront.gallery') }}">Gallery</a>
                <a href="{{ route('storefront.policy') }}" class="active">Policy</a>
                <a href="#" onclick="openOrderModal()" class="nav-order-btn">Order Now</a>
            </nav>
        </div>
    </div>
</header>

<div id="policy-page-view">
    <!-- HERO -->
    <section class="lb-page-hero">
        <span class="lb-page-hero-kicker">Terms &amp; Guidelines</span>
        <h1 class="lb-page-hero-title">Bakery Policy</h1>
        <p style="font-size:1.05rem; color:var(--lb-ink); opacity:0.85; max-width:650px; margin:14px auto 24px auto; padding:0 15px;">
            Please read carefully before placing your order. These policies ensure every custom order receives the highest standard of care and quality.
        </p>
        <button onclick="openOrderModal()" class="btn btn-primary" style="padding:12px 32px; font-size:1.05rem; border-radius:30px;">
            I Agree — Order Now
        </button>
    </section>

    <!-- POLICY CONTENT -->
    <section class="lb-section lb-band-white">
        <div class="lb-policy-container">

            <!-- PAYMENT CARD -->
            <div class="lb-policy-card">
                <div class="lb-policy-card-header">
                    <span class="material-symbols-outlined lb-policy-card-icon">credit_card</span>
                    <h3>Payment Terms</h3>
                </div>
                <ul class="lb-policy-list">
                    <li><strong>{{ $tenant->getSiteContent('policy_deposit_percentage', '50') }}% Deposit Required:</strong> Due immediately upon receipt of invoice to lock in your date.</li>
                    <li><strong>Final Balance:</strong> Due 2 days before pickup or delivery.</li>
                    <li><strong>Late Payment Fee:</strong> There is a 1-day grace period for late payments on final balances. If balance is not met after grace period, an additional {{ $tenant->getSiteContent('policy_late_fee_percentage', '10') }}% late fee will be charged.</li>
                    <li><strong>Pricing Terms:</strong> All prices listed are base pricing and are subject to change due to sales tax, labor, delivery, decor, or intricate custom elements.</li>
                </ul>
            </div>

            <!-- REFUNDS CARD -->
            <div class="lb-policy-card">
                <div class="lb-policy-card-header">
                    <span class="material-symbols-outlined lb-policy-card-icon">block</span>
                    <h3>Refund &amp; Cancellation Policy</h3>
                </div>
                <ul class="lb-policy-list">
                    <li><strong>All Sales Are Final:</strong> Refunds are not provided for any reason. Each order requires hours of planning and preparation well before baking &amp; decorating begins.</li>
                    <li><strong>Minor Design Variations:</strong> Minor variations of color or handcrafted design will not be eligible for a refund.</li>
                    <li><strong>Major Flavor Errors:</strong> If a major flavor error is discovered after pickup (e.g. an entirely different flavor than ordered), please contact me immediately. If confirmed, a refund or store credit will be issued for the portion of the cake that was incorrect.</li>
                    <li><strong>Non-Refundable Scenarios:</strong> Refunds are not given for a change of mind, flavor preference, or failure to read this policy.</li>
                </ul>
                <div class="lb-policy-note-box">
                    <span class="material-symbols-outlined" style="font-size:1.1rem; vertical-align:-3px;">push_pin</span>
                    <strong>Default Flavor Note:</strong> If no flavor choice is provided at the time of booking, vanilla will be used as the default flavor. No refunds will be given for this issue.
                </div>
            </div>

            <!-- DELIVERY CARD -->
            <div class="lb-policy-card">
                <div class="lb-policy-card-header">
                    <span class="material-symbols-outlined lb-policy-card-icon">local_shipping</span>
                    <h3>Delivery Rules</h3>
                </div>
                <ul class="lb-policy-list">
                    <li><strong>Default Option:</strong> All orders are pickup by default.</li>
                    <li><strong>Delivery Rates:</strong> Delivery starts at <strong>${{ $tenant->getSiteContent('policy_delivery_base_fee', '30') }} plus ${{ $tenant->getSiteContent('policy_delivery_per_mile', '2') }} per mile</strong>. Send your event address when ordering to confirm the exact delivery fee.</li>
                    <li><strong>Advance Scheduling:</strong> Delivery must be arranged when placing your order. Switching from pickup to delivery within 4 days of your event incurs a <strong>${{ $tenant->getSiteContent('policy_delivery_change_fee', '15') }} change fee</strong>.</li>
                </ul>
            </div>

            <!-- ORDER CHANGES CARD -->
            <div class="lb-policy-card">
                <div class="lb-policy-card-header">
                    <span class="material-symbols-outlined lb-policy-card-icon">edit</span>
                    <h3>Order Changes</h3>
                </div>
                <ul class="lb-policy-list">
                    <li><strong>Notice Requirement:</strong> Minor design changes may be accommodated if requested at least 7 days before pickup.</li>
                    <li><strong>Significant Changes:</strong> Major structural or flavor changes require an additional fee.</li>
                    <li><strong>No Guarantee:</strong> Requested changes are subject to schedule availability and are not guaranteed.</li>
                </ul>
            </div>

            <!-- DESIGN POLICY CARD -->
            <div class="lb-policy-card">
                <div class="lb-policy-card-header">
                    <span class="material-symbols-outlined lb-policy-card-icon">palette</span>
                    <h3>Custom Design Policy</h3>
                </div>
                <ul class="lb-policy-list">
                    <li><strong>Inspiration Photos:</strong> Inspiration photos are always welcome to help convey your vision.</li>
                    <li><strong>Handcrafted Originals:</strong> I do not create exact copies of another baker's work. Every cake is handcrafted and will feature its own unique artistic details.</li>
                    <li><strong>Artistic Variance:</strong> Exact color matches, decor placement, and handmade elements may vary slightly from inspiration photos.</li>
                </ul>
            </div>

            <!-- PICKUP CARD -->
            <div class="lb-policy-card">
                <div class="lb-policy-card-header">
                    <span class="material-symbols-outlined lb-policy-card-icon">schedule</span>
                    <h3>Pickup Rules &amp; Schedule</h3>
                </div>
                <ul class="lb-policy-list">
                    <li><strong>Pickup Hours:</strong> Pickup is available only between <strong>{{ $tenant->getSiteContent('policy_pickup_hours', '10:00am – 4:00pm') }}</strong>.</li>
                    <li><strong>Time Approval:</strong> Your pickup date and exact time must be approved in advance and is not guaranteed until confirmed.</li>
                    <li><strong>Punctuality:</strong> Please arrive on time within your agreed window.</li>
                    <li><strong>Transport Liability:</strong> I am not responsible for any damage once the order has left my kitchen/hands.</li>
                    <li><strong>Closed Days:</strong> Pickup and delivery orders are not accepted on {{ $tenant->getSiteContent('policy_closed_days', 'Sundays or Mondays') }}.</li>
                    <li><strong>Unclaimed Orders:</strong> Orders not picked up within 30 minutes of the set time frame will need to be rescheduled. If not rescheduled, they will be donated without refund.</li>
                </ul>
            </div>

            <!-- CAKES & ALLERGY CARD -->
            <div class="lb-policy-card">
                <div class="lb-policy-card-header">
                    <span class="material-symbols-outlined lb-policy-card-icon">cake</span>
                    <h3>Cakes &amp; Allergy Disclosure</h3>
                </div>
                <ul class="lb-policy-list">
                    <li><strong>Layer Construction:</strong> All standard cakes start at 2 layers. For taller cakes, additional layers can be added for <strong>${{ $tenant->getSiteContent('policy_extra_layer_fee', '20') }} per layer</strong>.</li>
                    <li><strong>Internal Support:</strong> All tiered and large custom cakes include internal dowels and bubble straws for structural support. Please be mindful of internal supports when cutting &amp; serving.</li>
                    <li><strong>Allergy Responsibility:</strong> It is your sole responsibility to state ANY food allergies when placing your order. We will NOT be held responsible for any allergy-related issues if not properly stated in the order form.</li>
                </ul>
            </div>

        </div>
    </section>
</div>

@include('storefront.partials.order_modal')

<footer class="site-footer">
    <div class="footer-logo">
        @if(!empty($tenant->logo_path))
            <img src="{{ asset($tenant->logo_path) }}" alt="{{ $tenant->name }} Logo" style="max-height:60px; width:auto; object-fit:contain;">
        @else
            <span class="lb-logo-stamp"><span class="material-symbols-outlined" style="font-size:1.4rem; vertical-align:-3px;">local_florist</span> {{ $tenant->name }}</span>
        @endif
    </div>
    <div class="footer-nav">
        <a href="{{ route('storefront.index') }}" class="footer-link">Home</a>
        <a href="{{ route('storefront.about') }}" class="footer-link">About</a>
        <a href="{{ route('storefront.menu') }}" class="footer-link">Menu</a>
        <a href="{{ route('storefront.gallery') }}" class="footer-link">Gallery</a>
            <a href="{{ route('storefront.policy') }}" class="footer-link">Policy</a>
        @php
            $sub = request()->route('subdomain') ?? $tenant->subdomain ?? $tenant->slug;
            $bakerPortalUrl = request()->is('site/*')
                ? url('/site/' . $sub . '/dashboard')
                : route('baker.dashboard');
        @endphp
        <a href="{{ $bakerPortalUrl }}" class="footer-link">Baker Login</a>
    </div>
    @include('storefront.partials.footer_nap')
    <p class="copyright-text">Copyright &copy; 2026 {{ $tenant->name ?? 'Bakery' }} | <a href="{{ route('legal.index') }}" class="footer-link">Legal Hub</a> &middot; <a href="{{ route('storefront.privacy') }}" class="footer-link">Privacy</a> &middot; <a href="{{ route('storefront.terms') }}" class="footer-link">Terms</a> | Powered by <a href="https://doughmain.pro" target="_blank" class="footer-link footer-brand-link">Doughmain.pro</a></p>
</footer>

<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
