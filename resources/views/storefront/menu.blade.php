<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu &amp; Pricing | {{ $tenant->name ?? 'Bakery' }}</title>
    <!-- Favicon -->
    @if(isset($tenant) && $tenant->logo_path)
        <link rel="icon" href="{{ asset($tenant->logo_path) }}">
    @else
        <link rel="icon" href="{{ asset('images/favicon.png') }}">
    @endif
    <meta name="description" content="Explore the menu, cake options, and pricing for {{ $tenant->name ?? 'our bakery' }}.">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700;800&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        .menu-hero-section {
            background: var(--theme-section-bg, var(--pink-bg, #fff7fa));
            text-align: center;
            padding: 90px 20px 60px 20px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        .menu-hero-subtitle {
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 3px;
            font-weight: 700;
            color: var(--primary, #e67399);
            margin-bottom: 12px;
            display: block;
        }
        .menu-hero-title {
            font-family: var(--theme-heading-font, 'Poppins', sans-serif);
            font-size: 3.5rem;
            font-weight: 800;
            color: var(--dark-text, #2c2419);
            line-height: 1.15;
            max-width: 800px;
            margin: 0 auto 16px auto;
        }
        .image-menu-wrapper {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            text-align: center;
        }
        .image-menu-wrapper img {
            width: 100%;
            max-width: 850px;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.12);
            border: 1px solid rgba(0,0,0,0.08);
            cursor: pointer;
        }
        .menu-text-section {
            max-width: 950px;
            margin: 40px auto 60px auto;
            padding: 40px;
            background: var(--theme-card-bg, #ffffff);
            border-radius: 24px;
            border: 1px solid rgba(0,0,0,0.08);
            box-shadow: 0 10px 30px rgba(0,0,0,0.04);
        }
        .menu-text-section h2,
        .menu-text-section h3 {
            font-family: var(--theme-heading-font, 'Poppins', sans-serif);
            color: var(--dark-text, #2c2419);
            margin-top: 32px;
            margin-bottom: 16px;
            padding-bottom: 10px;
            border-bottom: 2px dashed var(--primary, #e67399);
            font-size: 1.6rem;
            font-weight: 700;
        }
        .menu-text-section h2:first-child,
        .menu-text-section h3:first-child {
            margin-top: 0;
        }
        .menu-text-section p {
            font-size: 1.05rem;
            line-height: 1.8;
            color: var(--dark-text, #2c2419);
            margin-bottom: 16px;
        }
        .menu-text-section ul,
        .menu-text-section ol {
            margin: 14px 0 24px 0;
            padding-left: 10px;
            list-style-type: none;
        }
        .menu-text-section li {
            position: relative;
            padding-left: 26px;
            margin-bottom: 10px;
            font-size: 1.02rem;
            color: var(--dark-text, #2c2419);
            line-height: 1.7;
        }
        .menu-text-section ul li::before {
            content: "•";
            position: absolute;
            left: 6px;
            top: 0;
            color: var(--primary, #e67399);
            font-size: 1.5rem;
            font-weight: 800;
        }
        .menu-text-section strong {
            color: var(--dark-text, #2c2419);
            font-weight: 700;
        }
        .product-menu-grid {
            max-width: 1100px;
            margin: 40px auto 60px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
        }
        .product-menu-card {
            background: var(--theme-card-bg, #ffffff);
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.03);
            display: flex;
            justify-content: space-between;
            align-items: center;
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
            <a href="{{ route('storefront.menu') }}" class="active">Menu</a>
            <a href="{{ route('storefront.gallery') }}">Gallery</a>
            <a href="{{ route('storefront.policy') }}">Policy</a>
            <a href="#" onclick="openOrderModal()" class="nav-order-btn">Order Now</a>
        </nav>
    </div>
</header>

<div id="menu-page-view">
    <!-- HERO SECTION -->
    <section class="menu-hero-section">
        <span class="menu-hero-subtitle">OUR OFFERINGS</span>
        <h1 class="menu-hero-title">Menu &amp; Pricing</h1>
        <p style="font-size:1.05rem; color:var(--dark-text); opacity:0.85; max-width:650px; margin:0 auto 24px auto;">Explore our handcrafted baked goods and custom options.</p>
        <button onclick="openOrderModal()" class="btn btn-primary" style="padding:12px 32px; font-size:1.05rem; border-radius:30px;">
            Order Custom Creation 🎂
        </button>
    </section>

    @php
        $menuData = $tenant->site_content['menu'] ?? [];
        $menuType = $menuData['menu_type'] ?? 'both';
        $hasImage = !empty($menuData['menu_image_path']);
        $customText = trim($menuData['menu_text'] ?? '');
        $hasProducts = isset($products) && count($products) > 0;
        $hasMenuContent = $hasImage || !empty($customText) || $hasProducts;
        $imagePath = $menuData['menu_image_path'] ?? '';
        $isPdf = Str::endsWith(strtolower($imagePath), '.pdf');
    @endphp

    @if(!$hasMenuContent)
        <!-- NO MENU CONFIGURED PLACEHOLDER -->
        <section style="max-width:700px; margin:70px auto; padding:60px 30px; text-align:center; background:var(--theme-card-bg, #ffffff); border-radius:24px; border:2px dashed var(--primary, #e67399); box-shadow:0 10px 30px rgba(0,0,0,0.04);">
            <div style="font-size:3.5rem; margin-bottom:16px;">🧁</div>
            <h2 style="font-family:var(--theme-heading-font); color:var(--dark-text); font-size:1.8rem; margin-bottom:12px;">Menu Coming Soon</h2>
            <p style="font-size:1.05rem; color:#666; max-width:500px; margin:0 auto 24px auto; line-height:1.6;">
                This bakery hasn't set up their menu yet. Please check back later or request a custom quote directly!
            </p>
            <button onclick="openOrderModal()" class="btn btn-primary" style="padding:12px 30px; font-size:1rem; border-radius:30px;">
                Request Custom Order Quote 🍰
            </button>
        </section>
    @else

        <!-- 1. UPLOADED MENU FILE CARD (IMAGE OR PDF) -->
        @if(($menuType === 'image' || $menuType === 'both') && $hasImage)
            <section class="image-menu-wrapper">
                <h2 style="font-family:var(--theme-heading-font); color:var(--dark-text); margin-bottom:12px; font-size:1.8rem;">📄 Official Bakery Menu</h2>
                @if($isPdf)
                    <div style="background:var(--theme-card-bg, #ffffff); border:1px solid rgba(0,0,0,0.1); border-radius:20px; padding:40px 20px; box-shadow:0 10px 30px rgba(0,0,0,0.05); max-width:700px; margin:20px auto;">
                        <div style="font-size:4rem; margin-bottom:12px;">📄</div>
                        <h3 style="font-family:var(--theme-heading-font); color:var(--dark-text); margin-bottom:8px;">Official Menu PDF</h3>
                        <p style="color:#666; font-size:0.95rem; margin-bottom:20px;">Click below to view or download our full menu PDF</p>
                        <a href="{{ asset($imagePath) }}" target="_blank" class="btn btn-primary" style="padding:12px 28px; font-size:1rem; border-radius:30px; display:inline-block; text-decoration:none;">
                            📄 Open Official Menu PDF ↗
                        </a>
                    </div>
                @else
                    <p style="color:#666; font-size:0.92rem; margin-bottom:20px;">Click the menu image below to view full-screen</p>
                    <a href="{{ asset($imagePath) }}" target="_blank">
                        <img src="{{ asset($imagePath) }}" alt="{{ $tenant->name }} Menu Card">
                    </a>
                @endif
            </section>
        @endif

        <!-- 2. CATALOG PRODUCTS GRID IF ADDED BY BAKER -->
        @if($hasProducts && ($menuType === 'text' || $menuType === 'both'))
            <section style="max-width:1100px; margin:40px auto 40px auto; padding:0 20px;">
                <h2 style="font-family:var(--theme-heading-font); color:var(--dark-text); text-align:center; margin-bottom:25px; font-size:2rem;">🎂 Featured Catalog &amp; Pricing</h2>
                <div class="product-menu-grid">
                    @foreach($products as $p)
                        <div class="product-menu-card">
                            <div>
                                <h4 style="margin:0 0 6px 0; font-size:1.1rem; color:var(--dark-text);">{{ $p->name }}</h4>
                                <span style="font-size:0.85rem; color:#777;">Base Price</span>
                            </div>
                            <div style="font-size:1.2rem; font-weight:800; color:var(--primary);">${{ number_format($p->price, 2) }}</div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- 3. CUSTOM MENU COPY & PRICING TEXT (WYSIWYG RICH HTML — ALWAYS BELOW IMAGE/CARDS) -->
        @if(!empty($customText))
            <section class="menu-text-section">
                <div class="menu-formatted-content">
                    {!! $customText !!}
                </div>
            </section>
        @endif

    @endif
</div>

@include('storefront.partials.order_modal')

<!-- MENU PAGE FOOTER -->
<footer class="site-footer">
    <div class="footer-logo">
        @if(!empty($tenant->logo_path))
            <img src="{{ asset($tenant->logo_path) }}" alt="{{ $tenant->name }} Logo" style="max-height:60px; width:auto; object-fit:contain;">
        @else
            <span style="font-family:'Outfit',sans-serif; font-weight:700; font-size:1.5rem; color:#ffffff;">🧁 {{ $tenant->name }}</span>
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
    <p class="copyright-text">Copyright &copy; 2026 {{ $tenant->name ?? 'Bakery' }} | <a href="{{ route('legal.index') }}" class="footer-link">Legal Hub</a> &middot; <a href="{{ route('storefront.privacy') }}" class="footer-link">Privacy</a> &middot; <a href="{{ route('storefront.terms') }}" class="footer-link">Terms</a> | Powered by <a href="https://doughmain.pro" target="_blank" class="footer-link footer-brand-link">Doughmain.pro</a></p>
</footer>

<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
