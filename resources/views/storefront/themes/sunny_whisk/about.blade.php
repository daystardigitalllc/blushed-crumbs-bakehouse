<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('storefront.partials.seo_head', ['page' => 'about'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700;800&family=Oswald:wght@400;500;600;700&family=Open+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/storefront-base.css') }}">
    <link rel="stylesheet" href="{{ asset($tenant->themeCssPath()) }}">
</head>
<body class="theme-{{ $tenant->theme_id ?? 'sweet_elegant' }}">

<header class="site-header">
    <div class="header-container">
        <a href="{{ route('storefront.index') }}" class="logo">
            @if(!empty($tenant->logo_path))
                <img src="{{ asset($tenant->logo_path) }}" alt="{{ $tenant->name }} Logo" style="max-height:52px; width:auto; object-fit:contain;">
            @else
                <span style="font-family:'Poppins',sans-serif; font-weight:800; font-size:1.4rem; color:var(--sw-ink, #14213d);"><span class="material-symbols-outlined sw-icon" style="font-size:1.3rem; vertical-align:-3px;">cake</span> {{ $tenant->name }}</span>
            @endif
        </a>
        <nav class="nav-links">
            <a href="{{ route('storefront.index') }}">Home</a>
            <a href="{{ route('storefront.about') }}" class="active">About</a>
            <a href="{{ route('storefront.menu') }}">Menu</a>
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

<div id="about-page-view">
    <!-- HERO -->
    <section class="sw-page-hero">
        <span class="sw-page-hero-kicker">About Us</span>
        <h1 class="sw-page-hero-title">Meet {{ $tenant->name }}</h1>
    </section>

    <!-- MEET THE BAKER — photo + bio + testimonial quote -->
    <section class="sw-section sw-band-white">
        <div class="sw-meet-baker">
            <div class="sw-meet-baker-img">
                @php
                    $founderImg = !empty($tenant->logo_path) ? asset($tenant->logo_path) : (!empty($tenant->gallery_images[0]) ? asset($tenant->gallery_images[0]) : null);
                @endphp
                @if($founderImg)
                    <img src="{{ $founderImg }}" alt="About {{ $tenant->name }}" loading="lazy" decoding="async">
                @else
                    <div class="sw-meet-baker-placeholder">
                        <span class="material-symbols-outlined" style="font-size:3.5rem;">cake</span>
                        <h3>{{ $tenant->name }}</h3>
                    </div>
                @endif
            </div>
            <div>
                <h2>{{ $tenant->getSiteContent('about_title', 'About Our Bakery') }}</h2>
                <p>{{ $tenant->getSiteContent('about_bio', 'Welcome to ' . ($tenant->name ?? 'our bakehouse') . '! We specialize in artisanal custom cakes, gourmet treats, and unforgettable dessert experiences. Every order is baked fresh with love and attention to detail.') }}</p>
                <div class="sw-quote-card">
                    <p>"Ordering from {{ $tenant->name }} was absolute perfection! The cake was breathtaking and tasted amazing."</p>
                    <span>Happy Client, Verified Customer</span>
                </div>
            </div>
        </div>
    </section>

    <!-- WHY US — icon list -->
    <section class="sw-section sw-band-sky">
        <h2 class="sw-section-title" style="text-align:center;">The Ingredients Behind {{ $tenant->name }}</h2>
        <div class="sw-ingredients-list">
            <div class="sw-ingredient-row">
                <div class="sw-icon-circle"><span class="material-symbols-outlined">home</span></div>
                <div>
                    <h3>100% Homemade</h3>
                    <p>Baked completely from scratch using traditional family techniques and premium real ingredients.</p>
                </div>
            </div>
            <div class="sw-ingredient-row">
                <div class="sw-icon-circle"><span class="material-symbols-outlined">cake</span></div>
                <div>
                    <h3>Custom Design</h3>
                    <p>Every cake is designed uniquely to match your vision, theme, and celebration style.</p>
                </div>
            </div>
            <div class="sw-ingredient-row">
                <div class="sw-icon-circle"><span class="material-symbols-outlined">eco</span></div>
                <div>
                    <h3>Fresh Flavors</h3>
                    <p>Real fruit preserves, rich cocoa, real vanilla beans, and signature velvet frostings.</p>
                </div>
            </div>
            <div class="sw-ingredient-row">
                <div class="sw-icon-circle"><span class="material-symbols-outlined">event</span></div>
                <div>
                    <h3>Reliable Booking</h3>
                    <p>Easy custom order scheduling with guaranteed calendar availability for your date.</p>
                </div>
            </div>
            <div class="sw-ingredient-row">
                <div class="sw-icon-circle"><span class="material-symbols-outlined">auto_awesome</span></div>
                <div>
                    <h3>Attention to Detail</h3>
                    <p>Intricate piping, elegant edible details, and perfection in every single bite.</p>
                </div>
            </div>
            <div class="sw-ingredient-row">
                <div class="sw-icon-circle"><span class="material-symbols-outlined">chat</span></div>
                <div>
                    <h3>Personalized Service</h3>
                    <p>Direct communication with the baker to ensure your event dessert is stress-free.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- SPECIALTIES -->
    <section class="sw-section sw-band-mustard">
        <h2 class="sw-section-title" style="text-align:center;">What We Bake Best</h2>
        <div class="sw-specialties-row">
            <div class="sw-specialty-card">
                <span class="sw-specialty-badge">POPULAR</span>
                <h3>Custom Celebration Cakes</h3>
                <p>Multi-tiered birthday, baby shower, and milestone cakes baked fresh for your big moment.</p>
            </div>
            <div class="sw-specialty-card">
                <span class="sw-specialty-badge">LUXURY</span>
                <h3>Wedding Cake Experience</h3>
                <p>Elegantly crafted wedding tiers, tasting boxes, and full dessert table styling.</p>
            </div>
            <div class="sw-specialty-card">
                <span class="sw-specialty-badge">PARTY</span>
                <h3>Cupcakes &amp; Dessert Bars</h3>
                <p>Gourmet filled cupcakes, dessert shooters, and chocolate-covered treat boxes.</p>
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
            <span style="font-family:'Poppins',sans-serif; font-weight:800; font-size:1.5rem; color:var(--footer-text, #ffffff);"><span class="material-symbols-outlined" style="font-size:1.4rem; vertical-align:-3px;">cake</span> {{ $tenant->name }}</span>
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
