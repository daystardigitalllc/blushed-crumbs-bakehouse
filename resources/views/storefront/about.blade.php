<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | {{ $tenant->name ?? 'Blushed Crumbs Bakehouse' }}</title>
    <!-- Favicon -->
    @if(isset($tenant) && $tenant->logo_path)
        <link rel="icon" href="{{ asset($tenant->logo_path) }}">
    @else
        <link rel="icon" href="{{ asset('images/favicon.png') }}">
    @endif
    <meta name="description" content="Learn about {{ $tenant->name ?? 'Blushed Crumbs Bakehouse' }}, our founder story, passion for custom wedding cakes, and artisanal baking.">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
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
            <a href="{{ route('storefront.about') }}" class="active">About</a>
            <a href="{{ route('storefront.gallery') }}">Gallery</a>
            <a href="#" onclick="openOrderModal()" class="nav-order-btn">Order Now</a>
        </nav>
    </div>
</header>

<div id="about-page-view">
    <!-- HERO SECTION -->
    <section class="about-hero-section">
        <span class="about-hero-subtitle">ABOUT US</span>
        <h1 class="about-hero-title">Who is {{ $tenant->name }}?</h1>
    </section>

    <!-- MEET THE FOUNDER SECTION -->
    <section class="meet-founder-section">
        <div class="meet-founder-container">
            <div class="meet-founder-img-wrap">
                @php
                    $founderImg = !empty($tenant->logo_path) ? asset($tenant->logo_path) : (!empty($tenant->gallery_images[0]) ? asset($tenant->gallery_images[0]) : null);
                @endphp
                @if($founderImg)
                    <img src="{{ $founderImg }}" alt="About {{ $tenant->name }}">
                @else
                    <div class="founder-placeholder-card">
                        <span style="font-size:3.5rem;">🧁</span>
                        <h3>{{ $tenant->name }}</h3>
                        <p>Artisanal Bakehouse</p>
                    </div>
                @endif
            </div>
            <div class="meet-founder-content">
                <h2>{{ $tenant->getSiteContent('about_title', 'About Our Bakery') }}</h2>
                <p>{{ $tenant->getSiteContent('about_bio', 'Welcome to ' . ($tenant->name ?? 'our bakehouse') . '! We specialize in artisanal custom cakes, gourmet treats, and unforgettable dessert experiences. Every order is baked fresh with love and attention to detail.') }}</p>
                <div class="founder-testimonial-quote">
                    <p>"Ordering from {{ $tenant->name }} was absolute perfection! The cake was breathtaking and tasted amazing."</p>
                    <span class="founder-author-name">Happy Client</span>
                    <span class="founder-author-role">Verified Customer</span>
                </div>
            </div>
        </div>
    </section>

    <!-- THE INGREDIENTS BEHIND SECTION -->
    <section class="ingredients-section">
        <h2 class="ingredients-title">The Ingredients Behind {{ $tenant->name }}</h2>
        <div class="ingredients-grid-6">
            <div class="ingredient-card">
                <div class="ingredient-icon-circle">👩‍🍳</div>
                <h3>100% Homemade</h3>
                <p>Baked completely from scratch using traditional family techniques and premium real ingredients.</p>
            </div>
            <div class="ingredient-card">
                <div class="ingredient-icon-circle">🎂</div>
                <h3>Custom Design</h3>
                <p>Every cake is designed uniquely to match your vision, theme, and celebration style.</p>
            </div>
            <div class="ingredient-card">
                <div class="ingredient-icon-circle">🍓</div>
                <h3>Fresh Flavors</h3>
                <p>Real fruit preserves, rich cocoa, real vanilla beans, and signature velvet frostings.</p>
            </div>
            <div class="ingredient-card">
                <div class="ingredient-icon-circle">📅</div>
                <h3>Reliable Booking</h3>
                <p>Easy custom order scheduling with guaranteed calendar availability for your date.</p>
            </div>
            <div class="ingredient-card">
                <div class="ingredient-icon-circle">✨</div>
                <h3>Attention to Detail</h3>
                <p>Intricate piping, elegant edible details, and perfection in every single bite.</p>
            </div>
            <div class="ingredient-card">
                <div class="ingredient-icon-circle">💬</div>
                <h3>Personalized Service</h3>
                <p>Direct communication with the baker to ensure your event dessert is stress-free.</p>
            </div>
        </div>
    </section>

    <!-- SPECIALTIES SHOWCASE -->
    <section class="about-specialties-section">
        <h2 class="about-specialties-title">What We Bake Best</h2>
        <div class="specialties-cards-container">
            <div class="specialty-item-card">
                <span class="specialty-badge">POPULAR</span>
                <h3>Custom Celebration Cakes</h3>
                <p>Multi-tiered birthday, baby shower, and milestone cakes baked fresh for your big moment.</p>
            </div>
            <div class="specialty-item-card">
                <span class="specialty-badge">LUXURY</span>
                <h3>Wedding Cake Experience</h3>
                <p>Elegantly crafted wedding tiers, tasting boxes, and full dessert table styling.</p>
            </div>
            <div class="specialty-item-card">
                <span class="specialty-badge">PARTY</span>
                <h3>Cupcakes & Dessert Bars</h3>
                <p>Gourmet filled cupcakes, dessert shooters, and chocolate-covered treat boxes.</p>
            </div>
        </div>
    </section>
</div>

@include('storefront.partials.order_modal')

<!-- ABOUT PAGE FOOTER -->
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
        <a href="{{ route('storefront.about') }}" class="active">About</a>
        <a href="{{ route('storefront.gallery') }}">Gallery</a>
        @php
            $sub = request()->route('subdomain') ?? $tenant->subdomain ?? $tenant->slug;
            $bakerPortalUrl = request()->is('site/*') 
                ? url('/site/' . $sub . '/dashboard') 
                : route('baker.dashboard');
        @endphp
        <a href="{{ $bakerPortalUrl }}">Baker Login</a>
    </div>
    <div class="about-social-icons">
        <a href="#">🌐</a>
        <a href="#">✉️</a>
        <a href="#">🎵</a>
    </div>
    <p class="about-copyright">Copyright © 2026 {{ $tenant->name }} | <a href="{{ route('legal.index') }}" style="color:inherit;">Legal Hub</a> &middot; <a href="{{ route('storefront.privacy') }}" style="color:inherit;">Privacy</a> &middot; <a href="{{ route('storefront.terms') }}" style="color:inherit;">Terms</a> | Powered By <span>Doughmain.pro</span></p>
</footer>

<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
