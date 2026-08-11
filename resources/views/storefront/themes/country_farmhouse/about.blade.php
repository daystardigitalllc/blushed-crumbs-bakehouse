<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('storefront.partials.seo_head', ['page' => 'about'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Oswald:wght@400;500;600;700&family=Open+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/storefront-base.css') }}">
    <link rel="stylesheet" href="{{ asset($tenant->themeCssPath()) }}">
    @include('storefront.partials.color_override')
</head>
<body class="theme-{{ $tenant->theme_id ?? 'sweet_elegant' }}">

<header class="site-header">
    <div class="header-container">
        <a href="{{ route('storefront.index') }}" class="logo">
            @if(!empty($tenant->logo_path))
                <img src="{{ asset($tenant->logo_path) }}" alt="{{ $tenant->name }} Logo" style="max-height:52px; width:auto; object-fit:contain;">
            @else
                <span style="font-family:'Oswald',sans-serif; font-weight:700; text-transform:uppercase; letter-spacing:1px; font-size:1.2rem;"><span class="material-symbols-outlined farmhouse-icon" style="font-size:1.2rem; vertical-align:-3px;">lunch_dining</span> {{ $tenant->name }}</span>
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
    <section class="farmhouse-page-hero">
        <span class="farmhouse-page-hero-kicker">About Us</span>
        <h1 class="farmhouse-page-hero-title">{{ $tenant->getSiteContent('about_hero_title', $tenant->name) }}</h1>
    </section>

    <section class="farmhouse-about" style="padding-top:0;">
        <div class="farmhouse-about-media">
            @php
                $founderImg = !empty($tenant->gallery_images[0]) ? asset($tenant->gallery_images[0]) : (!empty($tenant->logo_path) ? asset($tenant->logo_path) : null);
            @endphp
            @if($founderImg)
                <img src="{{ $founderImg }}" alt="About {{ $tenant->name }}" loading="lazy" decoding="async">
            @else
                <div class="farmhouse-about-placeholder">
                    <span class="material-symbols-outlined" style="font-size:3.5rem; color:#ffffff;">storefront</span>
                </div>
            @endif
        </div>
        <div class="farmhouse-about-copy">
            <h2>{{ $tenant->getSiteContent('about_title', 'About Our Bakery') }}</h2>
            <p>{{ $tenant->getSiteContent('about_bio', 'Welcome to ' . ($tenant->name ?? 'our bakehouse') . '! We specialize in artisanal baked goods, made fresh daily with real ingredients and a whole lot of care.') }}</p>
            <a href="#" onclick="openOrderModal()" class="btn btn-primary">Order Now</a>
        </div>
    </section>

    <section>
        <h2 class="section-title-script" style="text-align:center; margin-bottom:35px;">{{ $tenant->getSiteContent('about_ingredients_title', 'Why ' . $tenant->name) }}</h2>
        <div class="farmhouse-ingredients-list">
            <div class="farmhouse-ingredient-row">
                <div class="farmhouse-icon-circle"><span class="material-symbols-outlined">home</span></div>
                <div>
                    <h3>{{ $tenant->getSiteContent('about_ingredients.0.title', 'Made From Scratch') }}</h3>
                    <p>{{ $tenant->getSiteContent('about_ingredients.0.text', 'Baked fresh daily using traditional techniques and real, quality ingredients.') }}</p>
                </div>
            </div>
            <div class="farmhouse-ingredient-row">
                <div class="farmhouse-icon-circle"><span class="material-symbols-outlined">eco</span></div>
                <div>
                    <h3>{{ $tenant->getSiteContent('about_ingredients.1.title', 'Quality Ingredients') }}</h3>
                    <p>{{ $tenant->getSiteContent('about_ingredients.1.text', 'Locally sourced where possible, with no shortcuts on flavor or freshness.') }}</p>
                </div>
            </div>
            <div class="farmhouse-ingredient-row">
                <div class="farmhouse-icon-circle"><span class="material-symbols-outlined">storefront</span></div>
                <div>
                    <h3>{{ $tenant->getSiteContent('about_ingredients.2.title', 'Local & Proud') }}</h3>
                    <p>{{ $tenant->getSiteContent('about_ingredients.2.text', 'A neighborhood spot built on real relationships with real customers.') }}</p>
                </div>
            </div>
            <div class="farmhouse-ingredient-row">
                <div class="farmhouse-icon-circle"><span class="material-symbols-outlined">event</span></div>
                <div>
                    <h3>{{ $tenant->getSiteContent('about_ingredients.3.title', 'Custom Orders Welcome') }}</h3>
                    <p>{{ $tenant->getSiteContent('about_ingredients.3.text', "Have something specific in mind? We'll work with you to make it happen.") }}</p>
                </div>
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
            <span style="font-family:'Oswald',sans-serif; font-weight:700; text-transform:uppercase; letter-spacing:1px; font-size:1.3rem; color:var(--footer-text, #ffffff);"><span class="material-symbols-outlined" style="font-size:1.3rem; vertical-align:-3px;">lunch_dining</span> {{ $tenant->name }}</span>
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
