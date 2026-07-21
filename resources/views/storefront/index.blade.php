<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blushed Crumbs Bakehouse | Where Every Celebration Gets Its Sweet Ending</title>
    <meta name="description" content="Custom artisanal cakes, cupcakes, treat boxes & wedding baking in Tennessee. Order custom cakes online with ease.">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS Assets -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="theme-{{ $tenant->theme_id ?? 'sweet_elegant' }}">

<header class="site-header">
    <div class="header-container">
        <a href="{{ route('storefront.index') }}" class="logo">
            <img src="{{ asset('images/blushedlogo.png') }}" alt="Blushed Crumbs Bakehouse Logo">
        </a>
        <nav class="nav-links">
            <a href="{{ route('storefront.index') }}">Home</a>
            <a href="{{ route('storefront.about') }}">About</a>
            <a href="{{ route('storefront.gallery') }}">Gallery</a>
            <a href="#" onclick="openOrderModal()" class="nav-order-btn">Order</a>
            <a href="{{ route('admin.dashboard') }}" class="admin-btn">🔑 Baker Admin Portal</a>
        </nav>
    </div>
</header>

<div id="storefront-view">
    <!-- Official Elementor WordPress Top Cloud Divider -->
    <img src="{{ asset('images/clouds.svg') }}" class="hero-cloud-elementor-top" alt="Top Cloud Divider">

    @php
        $sections = $tenant->getOrderedSections();
    @endphp

    @foreach($sections as $secId => $sec)
        @if(!empty($sec['enabled']))
            @if($secId === 'hero')
                <!-- Hero Section -->
                @php $heroBg = $tenant->getSiteContent('hero_bg_url'); @endphp
                <section class="hero-section" style="{{ !empty($heroBg) && !str_ends_with(strtolower($heroBg), '.mp4') ? 'background: linear-gradient(rgba(255,255,255,0.75), rgba(255,255,255,0.75)), url(' . asset($heroBg) . ') center/cover no-repeat;' : '' }}">
                    @if(!empty($heroBg) && str_ends_with(strtolower($heroBg), '.mp4'))
                        <video autoplay loop muted playsinline style="position:absolute; top:0; left:0; width:100%; height:100%; object-fit:cover; opacity:0.35; z-index:0;">
                            <source src="{{ asset($heroBg) }}" type="video/mp4">
                        </video>
                    @endif
                    <img src="{{ asset('images/7281AA41-A119-4BA3-A024-887E9580F7A2-removebg-preview (1).png') }}" class="raining-cake hero-cake-top-right" alt="Top Right Lavender Crown Cake">
                    <img src="{{ asset('images/4ee97017-0b48-4f55-95ed-8811da81d74d-removebg-preview.png') }}" class="raining-cake hero-cake-middle-left" alt="Middle Left Pink Crown Heart Cake">
                    <img src="{{ asset('images/96CABFE2-736F-4865-AA15-7FEB14C9D0BE-removebg-preview.png') }}" class="raining-cake hero-cake-far-right" alt="Chocolate 2-Tier Ruffles Cake">
                    <img src="{{ asset('images/25cfe8e0-d9bf-406c-8c2a-fdb6ef4692e6-removebg-preview.png') }}" class="raining-cake hero-cake-bottom-left" alt="Bottom Left White Heart Cake">
                    <img src="{{ asset('images/chocolatecake.png') }}" class="raining-cake hero-cake-bottom-right" alt="Bottom Right Floral Vintage Cake">

                    <div class="hero-wrapper" style="position:relative; z-index:2;">
                        <span class="subheading">{{ $tenant->getSiteContent('hero_subheading', 'Welcome to ' . ($tenant->name ?? 'Blushed Crumbs Bakehouse')) }}</span>
                        <h1>{{ $tenant->getSiteContent('hero_headline', 'Where Every Celebration Gets Its Sweet Ending.') }}</h1>
                        <div class="hero-buttons">
                            <button onclick="openOrderModal()" class="btn btn-primary">{{ $tenant->getSiteContent('hero_cta_primary', 'Custom Order') }}</button>
                            <a href="{{ route('storefront.gallery') }}" class="btn btn-secondary">{{ $tenant->getSiteContent('hero_cta_secondary', 'Browse Our Sweets') }}</a>
                        </div>
                    </div>
                </section>
            @elseif($secId === 'highlights')
                <!-- Highlights Bar -->
                <section class="highlights-bar">
                    @php $highlights = $tenant->getSiteContent('highlights', []); @endphp
                    @foreach($highlights as $hl)
                        <div class="highlight-item">
                            <div class="icon-circle">{{ $hl['icon'] ?? '🎂' }}</div>
                            <h4>{{ $hl['title'] ?? '' }}</h4>
                            <p>{{ $hl['desc'] ?? '' }}</p>
                        </div>
                    @endforeach
                </section>
            @elseif($secId === 'promo_video')
                <!-- Video Background Promo Banner -->
                <section class="video-promo-banner">
                    <video autoplay loop muted playsinline>
                        <source src="{{ asset($tenant->getSiteContent('promo_video_url', 'images/download (2) (1).mp4')) }}" type="video/mp4">
                    </video>
                    <div class="video-overlay-content">
                        <h2>{{ $tenant->getSiteContent('promo_headline', '$10 Off Your First Order!') }}</h2>
                        <p>{{ $tenant->getSiteContent('promo_subtext', 'Follow us on social media or join our community for instant discounts.') }}</p>
                        <button onclick="openOrderModal()" class="btn btn-dark">Order Now</button>
                    </div>
                </section>
            @elseif($secId === 'categories')
                <!-- Categories Section -->
                <section id="categories" class="categories-section">
                    <h2 class="section-title-script">Our Categories</h2>
                    <div class="categories-grid-exact">
                        <div class="category-card-exact">
                            <div class="category-image-frame">
                                <img src="{{ asset('images/IMG_8117.jpg') }}" alt="Single Tier Cakes">
                            </div>
                            <h3>Single Tier Cakes</h3>
                        </div>
                        <div class="category-card-exact">
                            <div class="category-image-frame">
                                <img src="{{ asset('images/IMG_8084.jpg') }}" alt="Multi Tier Cakes">
                            </div>
                            <h3>Multi Tier Cakes</h3>
                        </div>
                        <div class="category-card-exact">
                            <div class="category-image-frame">
                                <img src="{{ asset('images/IMG_8042.jpg') }}" alt="By The Dozen">
                            </div>
                            <h3>By The Dozen</h3>
                        </div>
                    </div>
                </section>
            @elseif($secId === 'whimsical')
                <!-- Whimsical Section -->
                <section class="whimsical-section">
                    <div class="whimsical-two-column">
                        <div class="whimsical-col-left">
                            <img src="{{ asset('images/96CABFE2-736F-4865-AA15-7FEB14C9D0BE-removebg-preview.png') }}" alt="Whimsical Mermaid Cake on Silver Stand">
                        </div>
                        <div class="whimsical-col-right">
                            <h2>{{ $tenant->getSiteContent('whimsical_title', 'Whimsical Creations for Every Milestone') }}</h2>
                            <ul class="whimsical-bullet-list">
                                @php
                                    $bullets = $tenant->getSiteContent('whimsical_bullets', []);
                                @endphp
                                @foreach($bullets as $bullet)
                                    <li>{{ $bullet }}</li>
                                @endforeach
                            </ul>
                            <hr class="whimsical-hr">
                        </div>
                    </div>
                </section>
            @elseif($secId === 'how_it_works')
                <!-- How It Works Section -->
                <section class="how-it-works-section" style="padding:70px 25px; text-align:center; background:var(--pink-bg);">
                    <h2 class="section-title-script" style="margin-bottom:15px;">How Custom Ordering Works</h2>
                    <p style="max-width:600px; margin:0 auto 40px auto; color:var(--dark-text); font-size:1.05rem;">Ordering your dream cake in 3 simple steps</p>
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:25px; max-width:1100px; margin:0 auto;">
                        @php $steps = $tenant->getSiteContent('how_it_works', []); @endphp
                        @foreach($steps as $idx => $step)
                            <div class="category-card-exact" style="padding:25px; background:white; border-radius:18px; box-shadow:0 6px 20px rgba(0,0,0,0.05);">
                                <span style="font-size:2.5rem; display:block; margin-bottom:10px;">{{ ['1️⃣','2️⃣','3️⃣'][$idx] ?? '✨' }}</span>
                                <h3 style="font-size:1.25rem; font-weight:700; margin-bottom:8px; color:var(--dark-text);">{{ $step['title'] ?? '' }}</h3>
                                <p style="font-size:0.9rem; color:#666;">{{ $step['desc'] ?? '' }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>
            @elseif($secId === 'reviews')
                <!-- Customer Reviews Section -->
                <section id="reviews" class="reviews-section">
                    <h2 class="section-title-script">What Our Customers Say</h2>
                    <div class="reviews-grid" id="public-reviews-grid">
                        @php $reviews = $tenant->getSiteContent('reviews', []); @endphp
                        @foreach($reviews as $rev)
                            <div class="cloud-review-card">
                                <p>"{{ $rev['quote'] ?? '' }}"</p>
                                <h4>{{ $rev['name'] ?? '' }}</h4>
                            </div>
                        @endforeach
                    </div>
                </section>
            @elseif($secId === 'faq')
                <!-- FAQ & Bakery Policies Section -->
                <section class="faq-policies-section" style="padding:70px 25px; background:#ffffff; text-align:center;">
                    <h2 class="section-title-script" style="margin-bottom:15px;">Frequently Asked Questions</h2>
                    <div style="max-width:850px; margin:0 auto; text-align:left; display:flex; flex-direction:column; gap:18px;">
                        @php $faqs = $tenant->getSiteContent('faqs', []); @endphp
                        @foreach($faqs as $faq)
                            <div style="background:var(--pink-bg); padding:20px; border-radius:14px; border-left:4px solid var(--primary);">
                                <h4 style="font-size:1.1rem; font-weight:700; color:var(--dark-text); margin-bottom:6px;">{{ $faq['q'] ?? '' }}</h4>
                                <p style="font-size:0.92rem; color:#555; line-height:1.6;">{{ $faq['a'] ?? '' }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>
            @elseif($secId === 'cta_banner')
                <!-- Footer Call to Action Video Banner -->
                <section class="cta-video-banner">
                    <video autoplay loop muted playsinline>
                        <source src="{{ asset($tenant->getSiteContent('cta_banner_url', 'images/34d48b27-1dd9-4784-8c8d-b378c3388060.mp4')) }}" type="video/mp4">
                    </video>
                    <div class="cta-content">
                        <h2>{{ $tenant->getSiteContent('cta_headline', 'Ready For Your Perfect Cake?') }}</h2>
                        <p>{{ $tenant->getSiteContent('cta_subtext', 'Order your plan or custom order now') }}</p>
                        <button onclick="openOrderModal()" class="btn btn-dark">{{ $tenant->getSiteContent('cta_btn_text', 'Order Now') }}</button>
                    </div>
                </section>
            @endif
        @endif
    @endforeach
</div>

@include('storefront.partials.order_modal')

<footer class="site-footer">
    <div class="footer-logo">
        <img src="{{ asset('images/blushedlogo.png') }}" alt="Blushed Crumbs Logo">
    </div>
    <div class="footer-nav">
        <a href="{{ route('storefront.index') }}" class="footer-link">Home</a>
        <a href="{{ route('storefront.about') }}" class="footer-link">About</a>
        <a href="{{ route('storefront.gallery') }}" class="footer-link">Gallery</a>
    </div>
    <p class="copyright-text">Copyright &copy; 2026 {{ $tenant->name ?? 'Blushed Crumbs Bakehouse' }} | Powered by <a href="https://bakery.pro" target="_blank" class="footer-link footer-brand-link">Bakery.Pro</a> &mdash; <em>Want your own bakery website?</em></p>
</footer>

<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
