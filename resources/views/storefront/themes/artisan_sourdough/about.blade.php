<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('storefront.partials.seo_head', ['page' => 'about'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                <span style="font-family:'Fraunces',serif; font-weight:700; font-size:1.5rem; color:var(--dark-text, #2b2118);"><span class="material-symbols-outlined petal-icon" style="font-size:1.2rem; vertical-align:-3px;">cake</span> {{ $tenant->name }}</span>
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
            <a href="#" onclick="openOrderModal()" class="nav-order-btn">Order</a>
        </nav>
    </div>
</header>

<div id="about-page-view">
    <section class="petal-page-hero">
        <span class="subheading">Our Story</span>
        <h1 class="petal-page-hero-title">{{ $tenant->name }}</h1>
    </section>

    <section class="petal-about" style="padding-top:0;">
        <div class="petal-about-copy">
            <h2>{{ $tenant->getSiteContent('about_title', 'About Our Bakery') }}</h2>
            <p>{{ $tenant->getSiteContent('about_bio', 'Welcome to ' . ($tenant->name ?? 'our bakehouse') . '! We specialize in artisanal baked goods, made fresh with real ingredients and a whole lot of care.') }}</p>
            <button onclick="openOrderModal()" class="btn btn-primary">Order Now</button>
        </div>
        <div class="petal-about-media">
            @php
                $founderImg = !empty($tenant->gallery_images[0]) ? asset($tenant->gallery_images[0]) : (!empty($tenant->logo_path) ? asset($tenant->logo_path) : null);
            @endphp
            @if($founderImg)
                <img src="{{ $founderImg }}" alt="About {{ $tenant->name }}" loading="lazy" decoding="async">
            @else
                <div class="petal-about-placeholder">
                    <span class="material-symbols-outlined" style="font-size:3.5rem; color:#ffffff;">cake</span>
                </div>
            @endif
        </div>
    </section>

    <section>
        <h2 class="section-title-script" style="text-align:center; margin-bottom:35px;">Why {{ $tenant->name }}</h2>
        <div class="petal-ingredients-list">
            <div class="petal-ingredient-row">
                <div class="petal-icon-circle"><span class="material-symbols-outlined">home</span></div>
                <div>
                    <h3>Made From Scratch</h3>
                    <p>Baked fresh using traditional techniques and real, quality ingredients.</p>
                </div>
            </div>
            <div class="petal-ingredient-row">
                <div class="petal-icon-circle"><span class="material-symbols-outlined">eco</span></div>
                <div>
                    <h3>Premium Ingredients</h3>
                    <p>Carefully sourced, with no shortcuts on flavor or freshness.</p>
                </div>
            </div>
            <div class="petal-ingredient-row">
                <div class="petal-icon-circle"><span class="material-symbols-outlined">favorite</span></div>
                <div>
                    <h3>Made With Care</h3>
                    <p>Every order is crafted with the same attention to detail, every time.</p>
                </div>
            </div>
            <div class="petal-ingredient-row">
                <div class="petal-icon-circle"><span class="material-symbols-outlined">event</span></div>
                <div>
                    <h3>Custom Orders Welcome</h3>
                    <p>Have something specific in mind? We'll work with you to make it happen.</p>
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
            <span style="font-family:'Fraunces',serif; font-weight:700; font-size:1.6rem; color:var(--footer-text, #ffffff);"><span class="material-symbols-outlined" style="font-size:1.3rem; vertical-align:-3px;">cake</span> {{ $tenant->name }}</span>
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
