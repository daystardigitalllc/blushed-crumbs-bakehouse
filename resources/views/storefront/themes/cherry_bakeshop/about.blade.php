<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('storefront.partials.seo_head', ['page' => 'about'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
                <span class="cb-logo-stamp"><span class="material-symbols-outlined" style="font-size:1.2rem; vertical-align:-3px;">cake</span> {{ $tenant->name }}</span>
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
            @if($tenant->normalizedPresaleSettings()['enabled'] ?? false)
            <a href="{{ route('storefront.presale') }}" class="nav-presale-btn">Shop Presale</a>
            @endif
        </nav>
    </div>
</header>

<div id="about-page-view">
    <!-- HERO -->
    <section class="cb-page-hero">
        <span class="cb-page-hero-kicker">About Us</span>
        <h1 class="cb-page-hero-title">{{ $tenant->getSiteContent('about_hero_title', 'Meet ' . $tenant->name) }}</h1>
    </section>

    <!-- MEET THE BAKER -->
    <section class="cb-section cb-band-white">
        <div class="cb-meet-baker">
            <div class="cb-badge-frame cb-badge-frame-md">
                @php
                    $founderImg = !empty($tenant->logo_path) ? asset($tenant->logo_path) : (!empty($tenant->gallery_images[0]) ? asset($tenant->gallery_images[0]) : null);
                @endphp
                @if($founderImg)
                    <img src="{{ $founderImg }}" alt="About {{ $tenant->name }}" loading="lazy" decoding="async">
                @else
                    <div class="cb-media-placeholder">
                        <span class="material-symbols-outlined" style="font-size:3.5rem;">cake</span>
                    </div>
                @endif
            </div>
            <div>
                <span class="cb-kicker">Our Story</span>
                <h2>{{ $tenant->getSiteContent('about_title', 'About Our Bakery') }}</h2>
                <p>{{ $tenant->getSiteContent('about_bio', 'Welcome to ' . ($tenant->name ?? 'our bakehouse') . '! We specialize in handcrafted baked goods made fresh with premium ingredients and a whole lot of care.') }}</p>
                <div class="cb-quote-card">
                    <p>"Ordering from {{ $tenant->name }} was such a treat — every bite tasted like it came straight from a bakeshop in the movies."</p>
                    <span>Happy Customer</span>
                </div>
            </div>
        </div>
    </section>

    <!-- WHY US — icon list -->
    <section class="cb-section cb-band-cream">
        <span class="cb-kicker" style="display:block; text-align:center;">Our Promise</span>
        <h2 class="cb-section-title" style="text-align:center;">{{ $tenant->getSiteContent('about_ingredients_title', 'The Ingredients Behind ' . $tenant->name) }}</h2>
        <div class="cb-ingredients-list">
            <div class="cb-ingredient-row">
                <div class="cb-icon-circle"><span class="material-symbols-outlined">home</span></div>
                <div>
                    <h3>{{ $tenant->getSiteContent('about_ingredients.0.title', '100% Homemade') }}</h3>
                    <p>{{ $tenant->getSiteContent('about_ingredients.0.text', 'Baked completely from scratch using traditional techniques and premium real ingredients.') }}</p>
                </div>
            </div>
            <div class="cb-ingredient-row">
                <div class="cb-icon-circle"><span class="material-symbols-outlined">cake</span></div>
                <div>
                    <h3>{{ $tenant->getSiteContent('about_ingredients.1.title', 'Custom Design') }}</h3>
                    <p>{{ $tenant->getSiteContent('about_ingredients.1.text', 'Every order is designed uniquely to match your vision, theme, and celebration style.') }}</p>
                </div>
            </div>
            <div class="cb-ingredient-row">
                <div class="cb-icon-circle"><span class="material-symbols-outlined">favorite</span></div>
                <div>
                    <h3>{{ $tenant->getSiteContent('about_ingredients.2.title', 'Made With Love') }}</h3>
                    <p>{{ $tenant->getSiteContent('about_ingredients.2.text', 'Rich flavors, real ingredients, and a whole lot of heart in every batch.') }}</p>
                </div>
            </div>
            <div class="cb-ingredient-row">
                <div class="cb-icon-circle"><span class="material-symbols-outlined">event</span></div>
                <div>
                    <h3>{{ $tenant->getSiteContent('about_ingredients.3.title', 'Reliable Booking') }}</h3>
                    <p>{{ $tenant->getSiteContent('about_ingredients.3.text', 'Easy custom order scheduling with guaranteed calendar availability for your date.') }}</p>
                </div>
            </div>
            <div class="cb-ingredient-row">
                <div class="cb-icon-circle"><span class="material-symbols-outlined">auto_awesome</span></div>
                <div>
                    <h3>{{ $tenant->getSiteContent('about_ingredients.4.title', 'Attention to Detail') }}</h3>
                    <p>{{ $tenant->getSiteContent('about_ingredients.4.text', 'Careful finishing touches and quality checks in every single bite.') }}</p>
                </div>
            </div>
            <div class="cb-ingredient-row">
                <div class="cb-icon-circle"><span class="material-symbols-outlined">chat</span></div>
                <div>
                    <h3>{{ $tenant->getSiteContent('about_ingredients.5.title', 'Personalized Service') }}</h3>
                    <p>{{ $tenant->getSiteContent('about_ingredients.5.text', 'Direct communication with the baker to make your order stress-free.') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- SPECIALTIES -->
    <section class="cb-section cb-band-cherry">
        <span class="cb-kicker cb-kicker-light" style="display:block; text-align:center;">Fan Favorites</span>
        <h2 class="cb-section-title" style="text-align:center;">{{ $tenant->getSiteContent('about_specialties_title', 'What We Bake Best') }}</h2>
        <div class="cb-specialties-row">
            <div class="cb-specialty-card">
                <span class="cb-specialty-badge">{{ $tenant->getSiteContent('about_specialties.0.badge', 'POPULAR') }}</span>
                <h3>{{ $tenant->getSiteContent('about_specialties.0.title', 'Custom Celebration Cakes') }}</h3>
                <p>{{ $tenant->getSiteContent('about_specialties.0.text', 'Multi-tiered birthday, baby shower, and milestone cakes baked fresh for your big moment.') }}</p>
            </div>
            <div class="cb-specialty-card">
                <span class="cb-specialty-badge">{{ $tenant->getSiteContent('about_specialties.1.badge', 'CLASSIC') }}</span>
                <h3>{{ $tenant->getSiteContent('about_specialties.1.title', 'Retro Cupcakes & Pies') }}</h3>
                <p>{{ $tenant->getSiteContent('about_specialties.1.text', 'Old-fashioned favorites made the way they used to be — from scratch, every time.') }}</p>
            </div>
            <div class="cb-specialty-card">
                <span class="cb-specialty-badge">{{ $tenant->getSiteContent('about_specialties.2.badge', 'PARTY') }}</span>
                <h3>{{ $tenant->getSiteContent('about_specialties.2.title', 'Dessert Bars & Treat Boxes') }}</h3>
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
