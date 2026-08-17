<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('storefront.partials.seo_head', ['page' => 'about'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700;800&family=Oswald:wght@400;500;600;700&family=Open+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/storefront-base.css') }}?v={{ filemtime(public_path('css/storefront-base.css')) }}">
    <link rel="stylesheet" href="{{ asset($tenant->themeCssPath()) }}?v={{ filemtime(public_path($tenant->themeCssPath())) }}">
    @include('storefront.partials.color_override')
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
    <!-- HERO SECTION -->
    <section class="about-hero-section">
        <span class="about-hero-subtitle">ABOUT US</span>
        <h1 class="about-hero-title">{{ $tenant->getSiteContent('about_hero_title', 'Who is ' . $tenant->name . '?') }}</h1>
    </section>

    <!-- MEET THE FOUNDER SECTION -->
    <section class="meet-founder-section">
        <div class="meet-founder-container">
            <div class="meet-founder-img-wrap">
                @php
                    $founderImg = !empty($tenant->logo_path) ? asset($tenant->logo_path) : (!empty($tenant->gallery_images[0]) ? asset($tenant->gallery_images[0]) : null);
                @endphp
                @if($founderImg)
                    <img src="{{ $founderImg }}" alt="About {{ $tenant->name }}" loading="lazy" decoding="async">
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
                    <p>"{{ $tenant->getSiteContent('about_testimonial_quote', 'Ordering from ' . $tenant->name . ' was absolute perfection! The cake was breathtaking and tasted amazing.') }}"</p>
                    <span class="founder-author-name">{{ $tenant->getSiteContent('about_testimonial_name', 'Happy Client') }}</span>
                    <span class="founder-author-role">{{ $tenant->getSiteContent('about_testimonial_role', 'Verified Customer') }}</span>
                </div>
            </div>
        </div>
    </section>

    <!-- THE INGREDIENTS BEHIND SECTION -->
    <section class="ingredients-section">
        <h2 class="ingredients-title">{{ $tenant->getSiteContent('about_ingredients_title', 'The Ingredients Behind ' . $tenant->name) }}</h2>
        <div class="ingredients-grid-6">
            <div class="ingredient-card">
                <div class="ingredient-icon-circle">👩‍🍳</div>
                <h3>{{ $tenant->getSiteContent('about_ingredients.0.title', '100% Homemade') }}</h3>
                <p>{{ $tenant->getSiteContent('about_ingredients.0.text', 'Baked completely from scratch using traditional family techniques and premium real ingredients.') }}</p>
            </div>
            <div class="ingredient-card">
                <div class="ingredient-icon-circle">🎂</div>
                <h3>{{ $tenant->getSiteContent('about_ingredients.1.title', 'Custom Design') }}</h3>
                <p>{{ $tenant->getSiteContent('about_ingredients.1.text', 'Every cake is designed uniquely to match your vision, theme, and celebration style.') }}</p>
            </div>
            <div class="ingredient-card">
                <div class="ingredient-icon-circle">🍓</div>
                <h3>{{ $tenant->getSiteContent('about_ingredients.2.title', 'Fresh Flavors') }}</h3>
                <p>{{ $tenant->getSiteContent('about_ingredients.2.text', 'Real fruit preserves, rich cocoa, real vanilla beans, and signature velvet frostings.') }}</p>
            </div>
            <div class="ingredient-card">
                <div class="ingredient-icon-circle">📅</div>
                <h3>{{ $tenant->getSiteContent('about_ingredients.3.title', 'Reliable Booking') }}</h3>
                <p>{{ $tenant->getSiteContent('about_ingredients.3.text', 'Easy custom order scheduling with guaranteed calendar availability for your date.') }}</p>
            </div>
            <div class="ingredient-card">
                <div class="ingredient-icon-circle">✨</div>
                <h3>{{ $tenant->getSiteContent('about_ingredients.4.title', 'Attention to Detail') }}</h3>
                <p>{{ $tenant->getSiteContent('about_ingredients.4.text', 'Intricate piping, elegant edible details, and perfection in every single bite.') }}</p>
            </div>
            <div class="ingredient-card">
                <div class="ingredient-icon-circle">💬</div>
                <h3>{{ $tenant->getSiteContent('about_ingredients.5.title', 'Personalized Service') }}</h3>
                <p>{{ $tenant->getSiteContent('about_ingredients.5.text', 'Direct communication with the baker to ensure your event dessert is stress-free.') }}</p>
            </div>
        </div>
    </section>

    <!-- SPECIALTIES SHOWCASE -->
    <section class="about-specialties-section">
        <h2 class="about-specialties-title">{{ $tenant->getSiteContent('about_specialties_title', 'What We Bake Best') }}</h2>
        <div class="specialties-cards-container">
            <div class="specialty-item-card">
                <span class="specialty-badge">{{ $tenant->getSiteContent('about_specialties.0.badge', 'POPULAR') }}</span>
                <h3>{{ $tenant->getSiteContent('about_specialties.0.title', 'Custom Celebration Cakes') }}</h3>
                <p>{{ $tenant->getSiteContent('about_specialties.0.text', 'Multi-tiered birthday, baby shower, and milestone cakes baked fresh for your big moment.') }}</p>
            </div>
            <div class="specialty-item-card">
                <span class="specialty-badge">{{ $tenant->getSiteContent('about_specialties.1.badge', 'LUXURY') }}</span>
                <h3>{{ $tenant->getSiteContent('about_specialties.1.title', 'Wedding Cake Experience') }}</h3>
                <p>{{ $tenant->getSiteContent('about_specialties.1.text', 'Elegantly crafted wedding tiers, tasting boxes, and full dessert table styling.') }}</p>
            </div>
            <div class="specialty-item-card">
                <span class="specialty-badge">{{ $tenant->getSiteContent('about_specialties.2.badge', 'PARTY') }}</span>
                <h3>{{ $tenant->getSiteContent('about_specialties.2.title', 'Cupcakes & Dessert Bars') }}</h3>
                <p>{{ $tenant->getSiteContent('about_specialties.2.text', 'Gourmet filled cupcakes, dessert shooters, and chocolate-covered treat boxes.') }}</p>
            </div>
        </div>
    </section>
</div>

@include('storefront.partials.order_modal')

<!-- ABOUT PAGE FOOTER -->
<footer class="site-footer">
    <div class="footer-logo">
        @if(!empty($tenant->logo_path))
            <img src="{{ asset($tenant->logo_path) }}" alt="{{ $tenant->name }} Logo" style="max-height:60px; width:auto; object-fit:contain;">
        @else
            <span style="font-family:'Outfit',sans-serif; font-weight:700; font-size:1.5rem; color:var(--footer-text, #ffffff);">🧁 {{ $tenant->name }}</span>
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
