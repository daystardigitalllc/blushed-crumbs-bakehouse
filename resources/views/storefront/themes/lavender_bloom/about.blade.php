<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('storefront.partials.seo_head', ['page' => 'about'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700;800&family=Poppins:wght@400;500;600;700;800&family=Great+Vibes&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/storefront-base.css') }}">
    <link rel="stylesheet" href="{{ asset($tenant->themeCssPath()) }}">
    @include('storefront.partials.color_override')
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
                <a href="{{ route('storefront.about') }}" class="active">About</a>
                <a href="{{ route('storefront.menu') }}">Menu</a>
                <a href="{{ route('storefront.gallery') }}">Gallery</a>
                <a href="{{ route('storefront.policy') }}">Policy</a>
                <a href="#" onclick="openOrderModal()" class="nav-order-btn">Order Now</a>
            </nav>
        </div>
    </div>
</header>

<div id="about-page-view">
    <!-- HERO -->
    <section class="lb-page-hero">
        <span class="lb-page-hero-kicker">About Us</span>
        <h1 class="lb-page-hero-title">{{ $tenant->getSiteContent('about_hero_title', 'Meet ' . $tenant->name) }}</h1>
    </section>

    <!-- MEET THE BAKER — offset dual-layer photo frame + copy card -->
    <section class="lb-section lb-band-lavender">
        <div class="lb-meet-baker">
            <div class="lb-meet-baker-img-wrap">
                <div class="lb-meet-baker-img-backdrop"></div>
                <div class="lb-meet-baker-img">
                    @php
                        $founderImg = !empty($tenant->logo_path) ? asset($tenant->logo_path) : (!empty($tenant->gallery_images[0]) ? asset($tenant->gallery_images[0]) : null);
                    @endphp
                    @if($founderImg)
                        <img src="{{ $founderImg }}" alt="About {{ $tenant->name }}" loading="lazy" decoding="async">
                    @else
                        <div class="lb-photo-placeholder">
                            <span class="material-symbols-outlined" style="font-size:3.5rem;">cake</span>
                        </div>
                    @endif
                </div>
            </div>
            <div class="lb-meet-baker-copy-card">
                <h2>{{ $tenant->getSiteContent('about_title', 'About Our Bakery') }}</h2>
                <p>{{ $tenant->getSiteContent('about_bio', 'Welcome to ' . ($tenant->name ?? 'our bakehouse') . '! We specialize in artisanal custom cakes, gourmet treats, and unforgettable dessert experiences. Every order is baked fresh with love and attention to detail.') }}</p>
                <div class="lb-quote-card">
                    <p>"{{ $tenant->getSiteContent('about_testimonial_quote', 'Ordering from ' . $tenant->name . ' was absolute perfection! The cake was breathtaking and tasted amazing.') }}"</p>
                    <span>{{ $tenant->getSiteContent('about_testimonial_name', 'Happy Client') }}, {{ $tenant->getSiteContent('about_testimonial_role', 'Verified Customer') }}</span>
                </div>
            </div>
        </div>
    </section>

    <!-- WHY US — icon-topped card grid (not icon-left rows) -->
    <section class="lb-section lb-band-white">
        <h2 class="lb-section-title" style="text-align:center;">{{ $tenant->getSiteContent('about_ingredients_title', 'The Ingredients Behind ' . $tenant->name) }}</h2>
        <div class="lb-ingredients-grid">
            <div class="lb-ingredient-card">
                <div class="lb-icon-circle"><span class="material-symbols-outlined">home</span></div>
                <h3>{{ $tenant->getSiteContent('about_ingredients.0.title', '100% Homemade') }}</h3>
                <p>{{ $tenant->getSiteContent('about_ingredients.0.text', 'Baked completely from scratch using traditional family techniques and premium real ingredients.') }}</p>
            </div>
            <div class="lb-ingredient-card">
                <div class="lb-icon-circle"><span class="material-symbols-outlined">cake</span></div>
                <h3>{{ $tenant->getSiteContent('about_ingredients.1.title', 'Custom Design') }}</h3>
                <p>{{ $tenant->getSiteContent('about_ingredients.1.text', 'Every cake is designed uniquely to match your vision, theme, and celebration style.') }}</p>
            </div>
            <div class="lb-ingredient-card">
                <div class="lb-icon-circle"><span class="material-symbols-outlined">eco</span></div>
                <h3>{{ $tenant->getSiteContent('about_ingredients.2.title', 'Fresh Flavors') }}</h3>
                <p>{{ $tenant->getSiteContent('about_ingredients.2.text', 'Real fruit preserves, rich cocoa, real vanilla beans, and signature velvet frostings.') }}</p>
            </div>
            <div class="lb-ingredient-card">
                <div class="lb-icon-circle"><span class="material-symbols-outlined">event</span></div>
                <h3>{{ $tenant->getSiteContent('about_ingredients.3.title', 'Reliable Booking') }}</h3>
                <p>{{ $tenant->getSiteContent('about_ingredients.3.text', 'Easy custom order scheduling with guaranteed calendar availability for your date.') }}</p>
            </div>
            <div class="lb-ingredient-card">
                <div class="lb-icon-circle"><span class="material-symbols-outlined">auto_awesome</span></div>
                <h3>{{ $tenant->getSiteContent('about_ingredients.4.title', 'Attention to Detail') }}</h3>
                <p>{{ $tenant->getSiteContent('about_ingredients.4.text', 'Intricate piping, elegant edible details, and perfection in every single bite.') }}</p>
            </div>
            <div class="lb-ingredient-card">
                <div class="lb-icon-circle"><span class="material-symbols-outlined">chat</span></div>
                <h3>{{ $tenant->getSiteContent('about_ingredients.5.title', 'Personalized Service') }}</h3>
                <p>{{ $tenant->getSiteContent('about_ingredients.5.text', 'Direct communication with the baker to ensure your event dessert is stress-free.') }}</p>
            </div>
        </div>
    </section>

    <!-- SPECIALTIES — solid gradient cards, not white-card-top-border -->
    <section class="lb-section lb-band-purple">
        <h2 class="lb-section-title" style="text-align:center;">{{ $tenant->getSiteContent('about_specialties_title', 'What We Bake Best') }}</h2>
        <div class="lb-specialties-row">
            <div class="lb-specialty-card">
                <span class="lb-specialty-badge">{{ $tenant->getSiteContent('about_specialties.0.badge', 'POPULAR') }}</span>
                <h3>{{ $tenant->getSiteContent('about_specialties.0.title', 'Custom Celebration Cakes') }}</h3>
                <p>{{ $tenant->getSiteContent('about_specialties.0.text', 'Multi-tiered birthday, baby shower, and milestone cakes baked fresh for your big moment.') }}</p>
            </div>
            <div class="lb-specialty-card">
                <span class="lb-specialty-badge">{{ $tenant->getSiteContent('about_specialties.1.badge', 'LUXURY') }}</span>
                <h3>{{ $tenant->getSiteContent('about_specialties.1.title', 'Wedding Cake Experience') }}</h3>
                <p>{{ $tenant->getSiteContent('about_specialties.1.text', 'Elegantly crafted wedding tiers, tasting boxes, and full dessert table styling.') }}</p>
            </div>
            <div class="lb-specialty-card">
                <span class="lb-specialty-badge">{{ $tenant->getSiteContent('about_specialties.2.badge', 'PARTY') }}</span>
                <h3>{{ $tenant->getSiteContent('about_specialties.2.title', 'Cupcakes & Dessert Bars') }}</h3>
                <p>{{ $tenant->getSiteContent('about_specialties.2.text', 'Gourmet filled cupcakes, dessert shooters, and chocolate-covered treat boxes.') }}</p>
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
    @include('storefront.partials.footer_newsletter')
    @include('storefront.partials.footer_copyright')
</footer>

<script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}"></script>
</body>
</html>
