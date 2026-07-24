<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $tenant->name ?? 'Artisanal Bakehouse' }} | {{ $tenant->getSiteContent('hero_subheading', 'Where Every Celebration Gets Its Sweet Ending') }}</title>
    <!-- Favicon -->
    @if(isset($tenant) && $tenant->logo_path)
        <link rel="icon" href="{{ asset($tenant->logo_path) }}">
    @else
        <link rel="icon" href="{{ asset('images/favicon.png') }}">
    @endif
    <meta name="description" content="{{ $tenant->getSiteContent('about_bio', 'Custom artisanal cakes, cupcakes, treat boxes & wedding baking. Order custom cakes online with ease.') }}">
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
            @if(!empty($tenant->logo_path))
                <img src="{{ asset($tenant->logo_path) }}" alt="{{ $tenant->name }} Logo" style="max-height:52px; width:auto; object-fit:contain;">
            @else
                <span style="font-family:'Outfit',sans-serif; font-weight:700; font-size:1.4rem; color:var(--dark-text, #2c2419);">🧁 {{ $tenant->name }}</span>
            @endif
        </a>
        <nav class="nav-links">
            <a href="{{ route('storefront.index') }}">Home</a>
            <a href="{{ route('storefront.about') }}">About</a>
            <a href="{{ route('storefront.menu') }}">Menu</a>
            <a href="{{ route('storefront.gallery') }}">Gallery</a>
            <a href="{{ route('storefront.policy') }}">Policy</a>
            <a href="#" onclick="openOrderModal()" class="nav-order-btn">Order Now</a>
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
                    <img src="{{ asset('images/cherry_cake-removebg-preview.png') }}" class="raining-cake hero-cake-top-right" alt="Top Right Cherry Cake">
                    <img src="{{ asset('images/4ee97017-0b48-4f55-95ed-8811da81d74d-removebg-preview.png') }}" class="raining-cake hero-cake-middle-left" alt="Middle Left Pink Crown Heart Cake">
                    <img src="{{ asset('images/elmo_cake-removebg-preview.png') }}" class="raining-cake hero-cake-far-right" alt="Chocolate 2-Tier Ruffles Cake">
                    <img src="{{ asset('images/25cfe8e0-d9bf-406c-8c2a-fdb6ef4692e6-removebg-preview.png') }}" class="raining-cake hero-cake-bottom-left" alt="Bottom Left White Heart Cake">
                    <img src="{{ asset('images/chocolatecake.png') }}" class="raining-cake hero-cake-bottom-right" alt="Bottom Right Floral Vintage Cake">

                    <div class="hero-wrapper" style="position:relative; z-index:2;">
                        <span class="subheading">{{ $tenant->getSiteContent('hero_subheading', 'Welcome to ' . ($tenant->name ?? 'our bakehouse')) }}</span>
                        <h1>{{ $tenant->getSiteContent('hero_headline', $tenant->name ?? 'Artisanal Bakehouse') }}</h1>
                        <div class="hero-buttons">
                            <button onclick="openOrderModal()" class="btn btn-primary">{{ $tenant->getSiteContent('hero_cta_primary', 'Custom Order') }}</button>
                            <a href="{{ route('storefront.gallery') }}" class="btn btn-secondary">Our Treats</a>
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
                @php
                    $promoVid = $tenant->getSiteContent('promo_video_url');
                @endphp
                @if(!empty($promoVid))
                    <section class="video-promo-banner">
                        <video autoplay loop muted playsinline>
                            <source src="{{ asset($promoVid) }}" type="video/mp4">
                        </video>
                        <div class="video-overlay-content">
                            <h2>{{ $tenant->getSiteContent('promo_headline', 'Special Custom Bakery Orders!') }}</h2>
                            <p>{{ $tenant->getSiteContent('promo_subtext', 'Order online directly from our kitchen for your upcoming celebration.') }}</p>
                            <button onclick="openOrderModal()" class="btn btn-dark">Order Now</button>
                        </div>
                    </section>
                @else
                    <section class="video-promo-banner" style="background: linear-gradient(135deg, var(--dark-text, #2a0818) 0%, #4a1531 100%); padding: 75px 25px; text-align: center;">
                        <div class="video-overlay-content" style="position:relative; z-index:2; max-width:720px; margin:0 auto;">
                            <span style="font-size:2.2rem; display:block; margin-bottom:10px;">🎁</span>
                            <h2 style="font-size:2.4rem; font-weight:800; color:#ffffff; margin-bottom:12px;">{{ $tenant->getSiteContent('promo_headline', 'Special Custom Bakery Orders!') }}</h2>
                            <p style="font-size:1.05rem; color:rgba(255,255,255,0.9); margin-bottom:24px;">{{ $tenant->getSiteContent('promo_subtext', 'Order online directly from our kitchen for your upcoming celebration.') }}</p>
                            <button onclick="openOrderModal()" class="btn btn-primary" style="padding:13px 34px; font-size:1.05rem; border-radius:30px; box-shadow:0 4px 15px rgba(0,0,0,0.2);">Order Now</button>
                        </div>
                    </section>
                @endif
            @elseif($secId === 'categories')
                <!-- Categories Section -->
                <section id="categories" class="categories-section">
                    <h2 class="section-title-script">Our Categories</h2>
                    <div class="categories-grid-exact">
                        @php
                            $catList = $tenant->getSiteContent('categories', [
                                ['title' => 'Single Tier Cakes', 'desc' => 'Perfect for birthdays & intimate gatherings'],
                                ['title' => 'Multi Tier Custom Cakes', 'desc' => 'Bespoke designs for weddings & celebrations'],
                                ['title' => 'Treats & Sweets By The Dozen', 'desc' => 'Cupcakes, macarons, and dessert tables']
                            ]);
                            $userImages = $tenant->gallery_images ?? [];
                            $tenantLogo = !empty($tenant->logo_path) ? asset($tenant->logo_path) : null;
                        @endphp
                        @foreach($catList as $idx => $cat)
                            @php
                                $imgUrl = !empty($userImages[$idx]) ? asset($userImages[$idx]) : ($tenantLogo ?? null);
                            @endphp
                            <div class="category-card-exact">
                                <div class="category-image-frame" style="display:flex; align-items:center; justify-content:center; background:var(--pink-bg); min-height:220px;">
                                    @if($imgUrl)
                                        <img src="{{ $imgUrl }}" alt="{{ $cat['title'] ?? 'Category' }}">
                                    @else
                                        <div style="text-align:center; padding:30px 15px;">
                                            <span style="font-size:3rem; display:block; margin-bottom:8px;">🧁</span>
                                            <h4 style="margin:0; font-size:1.1rem; color:var(--dark-text);">{{ $cat['title'] ?? 'Category' }}</h4>
                                        </div>
                                    @endif
                                </div>
                                <h3>{{ $cat['title'] ?? 'Category' }}</h3>
                                @if(!empty($cat['desc']))
                                    <p style="font-size:0.88rem; color:#666; margin-top:4px; text-align:center;">{{ $cat['desc'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @elseif($secId === 'whimsical')
                <!-- Whimsical Section -->
                <section class="whimsical-section">
                    <div class="whimsical-two-column">
                        <div class="whimsical-col-left">
                            @php
                                $wImg = $tenant->getSiteContent('whimsical_image_url');
                                if (empty($wImg) && $tenant->subdomain === 'blushedcrumbs') {
                                    $wImg = 'images/96CABFE2-736F-4865-AA15-7FEB14C9D0BE-removebg-preview.png';
                                } elseif (empty($wImg) && !empty($tenant->gallery_images[0])) {
                                    $wImg = $tenant->gallery_images[0];
                                }
                            @endphp
                            @if($wImg)
                                <img src="{{ asset($wImg) }}" alt="{{ $tenant->name }} Whimsical Creation">
                            @else
                                <div style="text-align:center; padding:40px 20px; background:rgba(255,255,255,0.15); border-radius:24px;">
                                    <span style="font-size:4rem; display:block; margin-bottom:12px;">✨</span>
                                    <h3 style="color:#ffffff;">Handcrafted Excellence</h3>
                                </div>
                            @endif
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
                        @php 
                            $defaultReviews = [
                                ['name' => 'Sarah M.', 'quote' => 'The custom cake for our celebration was absolute perfection! Tasted even better than it looked!'],
                                ['name' => 'Jessica & David K.', 'quote' => 'Hands down the best pastries and baked goods in town. Fresh, flavorful, and stunning presentation!'],
                                ['name' => 'Emily R.', 'quote' => 'Ordering online was effortless and pickup was smooth. Our guests raved about the dessert table!']
                            ];
                            $dbReviews = (isset($reviews) && count($reviews) > 0) ? $reviews : [];
                            $aiReviews = $tenant->getSiteContent('reviews', []);
                            $displayReviews = !empty($dbReviews) ? $dbReviews : (!empty($aiReviews) ? $aiReviews : $defaultReviews);
                        @endphp
                        @foreach($displayReviews as $rev)
                            <div class="cloud-review-card">
                                <p>"{{ is_array($rev) ? ($rev['quote'] ?? $rev['text'] ?? '') : ($rev->review_text ?? '') }}"</p>
                                <h4>{{ is_array($rev) ? ($rev['name'] ?? '') : ($rev->client_name ?? '') }}</h4>
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
                <!-- Footer Call to Action Banner -->
                <section class="cta-video-banner" style="background: linear-gradient(135deg, var(--primary, #e67399) 0%, var(--pink-bg, #fff7fa) 100%); padding: 65px 25px; text-align: center; border-top: 1px solid rgba(0,0,0,0.04);">
                    <div class="cta-content" style="max-width:750px; margin:0 auto;">
                        <h2 style="font-family: 'Great Vibes', cursive; font-size: 3.8rem; color: var(--dark-text, #2c2419); margin-bottom: 10px;">{{ $tenant->getSiteContent('cta_headline', 'Ready For Your Perfect Cake?') }}</h2>
                        <p style="font-size: 1.1rem; color: var(--dark-text, #2c2419); opacity: 0.9; margin-bottom: 24px;">{{ $tenant->getSiteContent('cta_subtext', 'Order your plan or custom order now') }}</p>
                        <button onclick="openOrderModal()" class="btn btn-primary" style="padding: 14px 34px; font-size: 1.1rem; border-radius: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.15);">{{ $tenant->getSiteContent('cta_btn_text', 'Order Now') }}</button>
                    </div>
                </section>
            @endif
        @endif
    @endforeach
</div>

@include('storefront.partials.order_modal')

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
        <a href="{{ route('storefront.gallery') }}" class="footer-link">Gallery</a>
        @php
            $sub = request()->route('subdomain') ?? $tenant->subdomain ?? $tenant->slug;
            $bakerPortalUrl = request()->is('site/*') 
                ? url('/site/' . $sub . '/dashboard') 
                : route('baker.dashboard');
        @endphp
        <a href="{{ $bakerPortalUrl }}" class="footer-link">Baker Login</a>
    </div>
    <p class="copyright-text">Copyright &copy; 2026 {{ $tenant->name ?? 'Blushed Crumbs Bakehouse' }} | <a href="{{ route('legal.index') }}" class="footer-link">Legal Hub</a> &middot; <a href="{{ route('storefront.privacy') }}" class="footer-link">Privacy</a> &middot; <a href="{{ route('storefront.terms') }}" class="footer-link">Terms</a> | Powered by <a href="https://doughmain.pro" target="_blank" class="footer-link footer-brand-link">Doughmain.pro</a></p>
</footer>

<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
