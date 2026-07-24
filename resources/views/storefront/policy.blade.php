<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bakery Policy &amp; Order Terms | {{ $tenant->name ?? 'Blushed Crumbs Bakehouse' }}</title>
    <!-- Favicon -->
    @if(isset($tenant) && $tenant->logo_path)
        <link rel="icon" href="{{ asset($tenant->logo_path) }}">
    @else
        <link rel="icon" href="{{ asset('images/favicon.png') }}">
    @endif
    <meta name="description" content="Official order terms, payment details, pickup hours, delivery rules, and allergen disclosure for {{ $tenant->name ?? 'our bakery' }}.">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700;800&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        .policy-hero-section {
            background: var(--theme-section-bg, var(--pink-bg, #fff7fa));
            text-align: center;
            padding: 100px 20px 70px 20px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        .policy-hero-subtitle {
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 3px;
            font-weight: 700;
            color: var(--primary, #e67399);
            margin-bottom: 12px;
            display: block;
        }
        .policy-hero-title {
            font-family: var(--theme-heading-font, 'Poppins', sans-serif);
            font-size: 3.5rem;
            font-weight: 800;
            color: var(--dark-text, #2c2419);
            line-height: 1.15;
            max-width: 800px;
            margin: 0 auto 16px auto;
        }
        .policy-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 60px 25px;
        }
        .policy-card {
            background: var(--theme-card-bg, #ffffff);
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 20px;
            padding: 32px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .policy-card:hover {
            box-shadow: 0 14px 34px rgba(0, 0, 0, 0.07);
        }
        .policy-card-header {
            display: flex;
            align-items: center;
            gap: 14px;
            border-bottom: 2px dashed var(--primary, #e67399);
            padding-bottom: 16px;
            margin-bottom: 20px;
        }
        .policy-card-header h3 {
            font-family: var(--theme-heading-font, 'Poppins', sans-serif);
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-text, #2c2419);
            margin: 0;
        }
        .policy-card-icon {
            font-size: 1.8rem;
        }
        .policy-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .policy-list li {
            position: relative;
            padding-left: 28px;
            margin-bottom: 14px;
            font-size: 1rem;
            color: var(--dark-text, #2c2419);
            line-height: 1.6;
        }
        .policy-list li::before {
            content: "•";
            position: absolute;
            left: 8px;
            top: 0;
            color: var(--primary, #e67399);
            font-size: 1.4rem;
            font-weight: 700;
        }
        .policy-note-box {
            background: var(--pink-bg, #fff7fa);
            border: 1px solid rgba(230, 115, 153, 0.3);
            border-radius: 12px;
            padding: 14px 18px;
            margin-top: 16px;
            font-size: 0.92rem;
            color: var(--dark-text, #2c2419);
            font-style: italic;
        }
    </style>
</head>
<body class="theme-{{ $tenant->theme_id ?? 'sweet_elegant' }}">

<!-- HEADER NAVIGATION -->
<header class="site-header">
    <div class="header-container">
        <a href="{{ route('storefront.index') }}" class="logo">
            @if(!empty($tenant->logo_path))
                <img src="{{ asset($tenant->logo_path) }}" alt="{{ $tenant->name }} Logo" style="max-height:52px; width:auto; object-fit:contain;">
            @else
                <span style="font-family:'Outfit',sans-serif; font-weight:700; font-size:1.4rem; color:var(--dark-text, #2c2419);">🧁 {{ $tenant->name }}</span>
            @endif
        </a>
        <nav class="nav-links">
            <a href="{{ route('storefront.index') }}">Home</a>
            <a href="{{ route('storefront.about') }}">About</a>
            <a href="{{ route('storefront.menu') }}">Menu</a>
            <a href="{{ route('storefront.gallery') }}">Gallery</a>
            <a href="{{ route('storefront.policy') }}" class="active">Policy</a>
            <a href="#" onclick="openOrderModal()" class="nav-order-btn">Order Now</a>
        </nav>
    </div>
</header>

<div id="policy-page-view">
    <!-- HERO SECTION -->
    <section class="policy-hero-section">
        <span class="policy-hero-subtitle">TERMS &amp; GUIDELINES</span>
        <h1 class="policy-hero-title">Bakery Policy</h1>
        <p style="font-size:1.05rem; color:var(--dark-text); opacity:0.85; max-width:650px; margin:0 auto 24px auto;">
            Please read carefully before placing your order. These policies ensure every custom order receives the highest standard of care and quality.
        </p>
        <button onclick="openOrderModal()" class="btn btn-primary" style="padding:12px 32px; font-size:1.05rem; border-radius:30px;">
            I Agree — Order Now 🍰
        </button>
    </section>

    <!-- POLICY CONTENT -->
    <div class="policy-container">
        
        <!-- PAYMENT CARD -->
        <div class="policy-card">
            <div class="policy-card-header">
                <span class="policy-card-icon">💳</span>
                <h3>Payment Terms</h3>
            </div>
            <ul class="policy-list">
                <li><strong>50% Deposit Required:</strong> Due immediately upon receipt of invoice to lock in your date.</li>
                <li><strong>Final Balance:</strong> Due 2 days before pickup or delivery.</li>
                <li><strong>Late Payment Fee:</strong> There is a 1-day grace period for late payments on final balances. If balance is not met after grace period, an additional 10% late fee will be charged.</li>
                <li><strong>Pricing Terms:</strong> All prices listed are base pricing and are subject to change due to sales tax, labor, delivery, decor, or intricate custom elements.</li>
            </ul>
        </div>

        <!-- REFUNDS CARD -->
        <div class="policy-card">
            <div class="policy-card-header">
                <span class="policy-card-icon">🚫</span>
                <h3>Refund &amp; Cancellation Policy</h3>
            </div>
            <ul class="policy-list">
                <li><strong>All Sales Are Final:</strong> Refunds are not provided for any reason. Each order requires hours of planning and preparation well before baking &amp; decorating begins.</li>
                <li><strong>Minor Design Variations:</strong> Minor variations of color or handcrafted design will not be eligible for a refund.</li>
                <li><strong>Major Flavor Errors:</strong> If a major flavor error is discovered after pickup (e.g. an entirely different flavor than ordered), please contact me immediately. If confirmed, a refund or store credit will be issued for the portion of the cake that was incorrect.</li>
                <li><strong>Non-Refundable Scenarios:</strong> Refunds are not given for a change of mind, flavor preference, or failure to read this policy.</li>
            </ul>
            <div class="policy-note-box">
                📌 <strong>Default Flavor Note:</strong> If no flavor choice is provided at the time of booking, vanilla will be used as the default flavor. No refunds will be given for this issue.
            </div>
        </div>

        <!-- DELIVERY CARD -->
        <div class="policy-card">
            <div class="policy-card-header">
                <span class="policy-card-icon">🚚</span>
                <h3>Delivery Rules</h3>
            </div>
            <ul class="policy-list">
                <li><strong>Default Option:</strong> All orders are pickup by default.</li>
                <li><strong>Delivery Rates:</strong> Delivery starts at <strong>$30 plus $2 per mile</strong>. Send your event address when ordering to confirm the exact delivery fee.</li>
                <li><strong>Advance Scheduling:</strong> Delivery must be arranged when placing your order. Switching from pickup to delivery within 4 days of your event incurs a <strong>$15 change fee</strong>.</li>
            </ul>
        </div>

        <!-- ORDER CHANGES CARD -->
        <div class="policy-card">
            <div class="policy-card-header">
                <span class="policy-card-icon">✏️</span>
                <h3>Order Changes</h3>
            </div>
            <ul class="policy-list">
                <li><strong>Notice Requirement:</strong> Minor design changes may be accommodated if requested at least 7 days before pickup.</li>
                <li><strong>Significant Changes:</strong> Major structural or flavor changes require an additional fee.</li>
                <li><strong>No Guarantee:</strong> Requested changes are subject to schedule availability and are not guaranteed.</li>
            </ul>
        </div>

        <!-- DESIGN POLICY CARD -->
        <div class="policy-card">
            <div class="policy-card-header">
                <span class="policy-card-icon">🎨</span>
                <h3>Custom Design Policy</h3>
            </div>
            <ul class="policy-list">
                <li><strong>Inspiration Photos:</strong> Inspiration photos are always welcome to help convey your vision.</li>
                <li><strong>Handcrafted Originals:</strong> I do not create exact copies of another baker's work. Every cake is handcrafted and will feature its own unique artistic details.</li>
                <li><strong>Artistic Variance:</strong> Exact color matches, decor placement, and handmade elements may vary slightly from inspiration photos.</li>
            </ul>
        </div>

        <!-- PICKUP CARD -->
        <div class="policy-card">
            <div class="policy-card-header">
                <span class="policy-card-icon">⏰</span>
                <h3>Pickup Rules &amp; Schedule</h3>
            </div>
            <ul class="policy-list">
                <li><strong>Pickup Hours:</strong> Pickup is available only between <strong>10:00am – 4:00pm</strong>.</li>
                <li><strong>Time Approval:</strong> Your pickup date and exact time must be approved in advance and is not guaranteed until confirmed.</li>
                <li><strong>Punctuality:</strong> Please arrive on time within your agreed window.</li>
                <li><strong>Transport Liability:</strong> I am not responsible for any damage once the order has left my kitchen/hands.</li>
                <li><strong>Closed Days:</strong> Pickup and delivery orders are not accepted on Sundays or Mondays.</li>
                <li><strong>Unclaimed Orders:</strong> Orders not picked up within 30 minutes of the set time frame will need to be rescheduled. If not rescheduled, they will be donated without refund.</li>
            </ul>
        </div>

        <!-- CAKES & ALLERGY CARD -->
        <div class="policy-card">
            <div class="policy-card-header">
                <span class="policy-card-icon">🍰</span>
                <h3>Cakes &amp; Allergy Disclosure</h3>
            </div>
            <ul class="policy-list">
                <li><strong>Layer Construction:</strong> All standard cakes start at 2 layers. For taller cakes, additional layers can be added for <strong>$20 per layer</strong>.</li>
                <li><strong>Internal Support:</strong> All tiered and large custom cakes include internal dowels and bubble straws for structural support. Please be mindful of internal supports when cutting &amp; serving.</li>
                <li><strong>Allergy Responsibility:</strong> It is your sole responsibility to state ANY food allergies when placing your order. We will NOT be held responsible for any allergy-related issues if not properly stated in the order form.</li>
            </ul>
        </div>

    </div>
</div>

@include('storefront.partials.order_modal')

<!-- POLICY PAGE FOOTER -->
<footer class="about-footer">
    <div class="about-footer-logo">
        @if(!empty($tenant->logo_path))
            <img src="{{ asset($tenant->logo_path) }}" alt="{{ $tenant->name }} Logo" style="max-height:60px; width:auto; object-fit:contain;">
        @else
            <span style="font-family:'Outfit',sans-serif; font-weight:700; font-size:1.5rem; color:#ffffff;">🧁 {{ $tenant->name }}</span>
        @endif
    </div>
    <div class="about-footer-nav">
        <a href="{{ route('storefront.index') }}">Home</a>
        <a href="{{ route('storefront.about') }}">About</a>
        <a href="{{ route('storefront.menu') }}">Menu</a>
        <a href="{{ route('storefront.gallery') }}">Gallery</a>
        <a href="{{ route('storefront.policy') }}" class="active">Policy</a>
        @php
            $sub = request()->route('subdomain') ?? $tenant->subdomain ?? $tenant->slug;
            $bakerPortalUrl = request()->is('site/*') 
                ? url('/site/' . $sub . '/dashboard') 
                : route('baker.dashboard');
        @endphp
        <a href="{{ $bakerPortalUrl }}">Baker Login</a>
    </div>
    <p class="about-copyright">Copyright © 2026 {{ $tenant->name }} | <a href="{{ route('legal.index') }}" style="color:inherit;">Legal Hub</a> &middot; <a href="{{ route('storefront.privacy') }}" style="color:inherit;">Privacy</a> &middot; <a href="{{ route('storefront.terms') }}" style="color:inherit;">Terms</a> | Powered By <span>Doughmain.pro</span></p>
</footer>

<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
