<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('storefront.partials.seo_head', ['page' => 'menu'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/storefront-base.css') }}">
    <link rel="stylesheet" href="{{ asset($tenant->themeCssPath()) }}">
    <style>
        .cb-menu-hero-section {
            background: var(--cb-cream, #fff5f0);
            text-align: center;
            padding: 90px 20px 60px 20px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        .cb-menu-hero-subtitle {
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 3px;
            font-weight: 700;
            color: var(--cb-cherry, #a0293f);
            margin-bottom: 12px;
            display: block;
        }
        .cb-menu-hero-title {
            font-family: var(--theme-heading-font, 'Fredoka', sans-serif);
            font-size: 3.5rem;
            font-weight: 700;
            color: var(--cb-cherry, #a0293f);
            line-height: 1.15;
            max-width: 800px;
            margin: 0 auto 16px auto;
        }
        .cb-image-menu-wrapper {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            text-align: center;
        }
        .cb-image-menu-wrapper img {
            width: 100%;
            max-width: 850px;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(160,41,63,0.15);
            border: 4px solid #ffffff;
            outline: 2px solid var(--cb-cherry, #a0293f);
            cursor: pointer;
        }
        .cb-menu-text-section {
            max-width: 950px;
            margin: 40px auto 60px auto;
            padding: 40px;
            background: var(--theme-card-bg, #ffffff);
            border-radius: 24px;
            border: 1px solid rgba(0,0,0,0.08);
            box-shadow: 0 10px 30px rgba(160,41,63,0.06);
        }
        .cb-menu-text-section h2,
        .cb-menu-text-section h3 {
            font-family: var(--theme-heading-font, 'Fredoka', sans-serif);
            color: var(--cb-cherry, #a0293f);
            margin-top: 32px;
            margin-bottom: 16px;
            padding-bottom: 10px;
            border-bottom: 2px dashed var(--cb-cherry, #a0293f);
            font-size: 1.6rem;
            font-weight: 700;
        }
        .cb-menu-text-section h2:first-child,
        .cb-menu-text-section h3:first-child {
            margin-top: 0;
        }
        .cb-menu-text-section p {
            font-size: 1.05rem;
            line-height: 1.8;
            color: #4a2833;
            margin-bottom: 16px;
        }
        .cb-menu-text-section ul,
        .cb-menu-text-section ol {
            margin: 14px 0 24px 0;
            padding-left: 10px;
            list-style-type: none;
        }
        .cb-menu-text-section li {
            position: relative;
            padding-left: 26px;
            margin-bottom: 10px;
            font-size: 1.02rem;
            color: #4a2833;
            line-height: 1.7;
        }
        .cb-menu-text-section ul li::before {
            content: "🍒";
            position: absolute;
            left: 0;
            top: 0;
            font-size: 0.9rem;
        }
        .cb-menu-text-section strong {
            color: var(--cb-cherry, #a0293f);
            font-weight: 700;
        }
    </style>
</head>
<body class="theme-{{ $tenant->theme_id ?? 'sweet_elegant' }}">

<header class="site-header">
    <div class="header-container">
        <a href="{{ route('storefront.index') }}" class="logo">
            @if(!empty($tenant->logo_path))
                <img src="{{ asset($tenant->logo_path) }}" alt="{{ $tenant->name }} Logo" style="max-height:52px; width:auto; object-fit:contain;">
            @else
                <span class="cb-logo-stamp"><span class="material-symbols-outlined" style="font-size:1.2rem; vertical-align:-3px;">cake</span> {{ $tenant->name }}</span>
            @endif
        </a>
        <nav class="nav-links">
            <a href="{{ route('storefront.index') }}">Home</a>
            <a href="{{ route('storefront.about') }}">About</a>
            <a href="{{ route('storefront.menu') }}" class="active">Menu</a>
            <a href="{{ route('storefront.gallery') }}">Gallery</a>
            <a href="{{ route('storefront.policy') }}">Policy</a>
            @if(Auth::check() && Auth::user()->tenant_id === $tenant->id)
                @php
                    $navBakerSub = request()->route('subdomain') ?? $tenant->subdomain ?? $tenant->slug;
                    $navBakerPortalUrl = request()->is('site/*')
                        ? url('/site/' . $navBakerSub . '/dashboard')
                        : route('baker.dashboard');
                @endphp
                <a href="{{ $navBakerPortalUrl }}">Dashboard</a>
            @endif
            <a href="#" onclick="openOrderModal()" class="nav-order-btn">Order Now</a>
        </nav>
    </div>
</header>

<div id="menu-page-view">
    <section class="cb-menu-hero-section">
        <span class="cb-menu-hero-subtitle">OUR OFFERINGS</span>
        <h1 class="cb-menu-hero-title">Menu &amp; Pricing</h1>
        <p style="font-size:1.05rem; color:#4a2833; opacity:0.85; max-width:650px; margin:0 auto 24px auto;">Explore our handcrafted baked goods and custom options.</p>
        <button onclick="openOrderModal()" class="btn btn-primary" style="padding:12px 32px; font-size:0.95rem; border-radius:30px;">
            Order Custom Creation <span class="material-symbols-outlined" style="font-size:1.1rem; vertical-align:-3px;">cake</span>
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
        <section style="max-width:700px; margin:70px auto; padding:60px 30px; text-align:center; background:var(--theme-card-bg, #ffffff); border-radius:24px; border:2px dashed var(--cb-cherry, #a0293f); box-shadow:0 10px 30px rgba(160,41,63,0.06);">
            <div class="material-symbols-outlined cb-icon" style="font-size:3.5rem; margin-bottom:16px;">bakery_dining</div>
            <h2 style="font-family:var(--theme-heading-font); color:var(--cb-cherry); font-size:1.8rem; margin-bottom:12px;">Menu Coming Soon</h2>
            <p style="font-size:1.05rem; color:#666; max-width:500px; margin:0 auto 24px auto; line-height:1.6;">
                This bakery hasn't set up their menu yet. Please check back later or request a custom quote directly!
            </p>
            <button onclick="openOrderModal()" class="btn btn-primary" style="padding:12px 30px; font-size:0.9rem; border-radius:30px;">
                Request Custom Order Quote <span class="material-symbols-outlined" style="font-size:1rem; vertical-align:-2px;">cake</span>
            </button>
        </section>
    @else

        <!-- 1. UPLOADED MENU FILE CARD (IMAGE OR PDF) -->
        @if(($menuType === 'image' || $menuType === 'both') && $hasImage)
            <section class="cb-image-menu-wrapper">
                <h2 style="font-family:var(--theme-heading-font); color:var(--cb-cherry); margin-bottom:12px; font-size:1.8rem;"><span class="material-symbols-outlined" style="vertical-align:-4px;">description</span> Official Bakery Menu</h2>
                @if($isPdf)
                    <div style="background:var(--theme-card-bg, #ffffff); border:1px solid rgba(0,0,0,0.1); border-radius:20px; padding:40px 20px; box-shadow:0 10px 30px rgba(160,41,63,0.05); max-width:700px; margin:20px auto;">
                        <div class="material-symbols-outlined cb-icon" style="font-size:4rem; margin-bottom:12px;">picture_as_pdf</div>
                        <h3 style="font-family:var(--theme-heading-font); color:var(--cb-cherry); margin-bottom:8px;">Official Menu PDF</h3>
                        <p style="color:#666; font-size:0.95rem; margin-bottom:20px;">Click below to view or download our full menu PDF</p>
                        <a href="{{ asset($imagePath) }}" target="_blank" class="btn btn-primary" style="padding:12px 28px; font-size:0.9rem; border-radius:30px; display:inline-block; text-decoration:none;">
                            <span class="material-symbols-outlined" style="vertical-align:-4px;">picture_as_pdf</span> Open Official Menu PDF ↗
                        </a>
                    </div>
                @else
                    <p style="color:#666; font-size:0.92rem; margin-bottom:20px;">Click the menu image below to view full-screen</p>
                    <a href="{{ asset($imagePath) }}" target="_blank">
                        <img src="{{ asset($imagePath) }}" alt="{{ $tenant->name }} Menu Card" loading="lazy" decoding="async">
                    </a>
                @endif
            </section>
        @endif

        <!-- 2. CATALOG PRODUCTS -->
        @if($hasProducts && ($menuType === 'text' || $menuType === 'both'))
            <section style="max-width:1100px; margin:40px auto 40px auto; padding:0 20px;">
                <h2 style="font-family:var(--theme-heading-font); color:var(--cb-cherry); text-align:center; margin-bottom:25px; font-size:2rem;"><span class="material-symbols-outlined" style="vertical-align:-5px;">cake</span> Featured Catalog &amp; Pricing</h2>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:20px;">
                    @foreach($products as $p)
                        <div class="category-card-exact" style="padding:22px; background:var(--theme-card-bg, #ffffff); border-radius:18px; box-shadow:0 6px 20px rgba(160,41,63,0.06); display:flex; align-items:center; justify-content:space-between; gap:12px;">
                            <div>
                                <h4 style="margin:0 0 4px 0; font-size:1.1rem; color:#4a2833;">{{ $p->name }}</h4>
                                <span style="font-size:0.85rem; color:#777;">Base Price</span>
                            </div>
                            <div style="font-size:1.3rem; font-weight:700; color:var(--cb-cherry);">${{ number_format($p->price, 2) }}</div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- 3. CUSTOM MENU COPY & PRICING TEXT -->
        @if(!empty($customText))
            <section class="cb-menu-text-section">
                <div class="menu-formatted-content">
                    {!! $customText !!}
                </div>
            </section>
        @endif

    @endif
</div>

@include('storefront.partials.order_modal')

<footer class="site-footer">
    <div class="footer-logo">
        @if(!empty($tenant->logo_path))
            <img src="{{ asset($tenant->logo_path) }}" alt="{{ $tenant->name }} Logo" style="max-height:60px; width:auto; object-fit:contain;">
        @else
            <span class="cb-logo-stamp"><span class="material-symbols-outlined" style="font-size:1.4rem; vertical-align:-3px;">cake</span> {{ $tenant->name }}</span>
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
    @include('storefront.partials.footer_newsletter')
    @include('storefront.partials.footer_copyright')
</footer>

<script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}"></script>
</body>
</html>
