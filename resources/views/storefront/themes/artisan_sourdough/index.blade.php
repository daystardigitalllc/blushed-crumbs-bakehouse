<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('storefront.partials.seo_head', ['page' => 'home'])

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

    <!-- CSS Assets -->
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
            <a href="{{ route('storefront.about') }}">About</a>
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

<div id="storefront-view">
    @php
        $sections = $tenant->getOrderedSections();
    @endphp

    @foreach($sections as $secId => $sec)
        @if(!empty($sec['enabled']))
            @if($secId === 'hero')
                <!-- Hero — rounded card floating on the mauve page background -->
                @php
                    $heroBg = $tenant->getSiteContent('hero_bg_url');
                    $heroIsVideo = !empty($heroBg) && str_ends_with(strtolower($heroBg), '.mp4');
                @endphp
                <section class="petal-hero">
                    <div class="petal-hero-copy">
                        <span class="subheading">{{ $tenant->getSiteContent('hero_subheading', 'Welcome to ' . ($tenant->name ?? 'our bakehouse')) }}</span>
                        <h1>{{ $tenant->getSiteContent('hero_headline', $tenant->name ?? 'Artisanal Bakehouse') }}</h1>
                        <div class="hero-buttons">
                            <button onclick="openOrderModal()" class="btn btn-primary">{{ $tenant->getSiteContent('hero_cta_primary', 'Order Now') }}</button>
                        <a href="{{ route('storefront.gallery') }}" class="btn btn-secondary">{{ $tenant->getSiteContent('hero_cta_secondary', 'Our Treats') }}</a>
                        </div>
                    </div>
                    <div class="petal-hero-media">
                        @if($heroIsVideo)
                            <video autoplay loop muted playsinline>
                                <source src="{{ asset($heroBg) }}" type="video/mp4">
                            </video>
                        @elseif(!empty($heroBg))
                            <img src="{{ asset($heroBg) }}" alt="{{ $tenant->name }}" fetchpriority="high">
                        @else
                            <div class="petal-hero-media-placeholder"><span class="material-symbols-outlined" style="font-size:5rem; color:#fff;">bakery_dining</span></div>
                        @endif
                    </div>
                </section>
            @elseif($secId === 'about')
                <!-- About teaser - matches the reference's text/photo second
                     section, using the same about_bio/about_title fields the
                     About page reads. Its own toggleable Page Builder section. -->
                <section class="petal-about">
                    <div class="petal-about-copy">
                        <span class="subheading">Our Story</span>
                        <h2>{{ $tenant->getSiteContent('about_title', 'Sweet Moments, Delivered Here') }}</h2>
                        <p>{{ $tenant->getSiteContent('about_bio', 'Welcome to ' . ($tenant->name ?? 'our bakehouse') . '! We specialize in artisanal baked goods, made fresh with real ingredients and a whole lot of care.') }}</p>
                        <a href="{{ route('storefront.about') }}" class="btn btn-primary">Our Story</a>
                    </div>
                    <div class="petal-about-media">
                        @php
                            $aboutImg = !empty($tenant->gallery_images[0]) ? asset($tenant->gallery_images[0]) : null;
                        @endphp
                        @if($aboutImg)
                            <img src="{{ $aboutImg }}" alt="{{ $tenant->name }}" loading="lazy" decoding="async">
                        @else
                            <div class="petal-about-placeholder">
                                <span class="material-symbols-outlined" style="font-size:3.5rem; color:#ffffff;">cake</span>
                            </div>
                        @endif
                    </div>
                </section>
            @elseif($secId === 'highlights')
                <!-- Highlights — 3 feature cards, first one accent-highlighted -->
                <section class="petal-features-row">
                    @php $highlights = $tenant->getSiteContent('highlights', []); @endphp
                    @foreach($highlights as $idx => $hl)
                        <div class="petal-feature-card {{ $idx === 0 ? 'is-accent' : '' }}">
                            <h4>{{ $hl['title'] ?? '' }}</h4>
                            <p>{{ $hl['desc'] ?? '' }}</p>
                        </div>
                    @endforeach
                </section>
            @elseif($secId === 'categories')
                <!-- Categories — "Popular Picks" card grid -->
                <section id="categories" class="categories-section">
                    <h2 class="section-title-script" style="text-align:center;">Our Popular Picks</h2>
                    <div class="petal-picks-grid">
                        @php
                            $catList = $tenant->getSiteContent('categories', [
                                ['title' => 'Cakes'],
                                ['title' => 'Cupcakes'],
                                ['title' => 'Treats'],
                            ]);
                            $userImages = $tenant->gallery_images ?? [];
                        @endphp
                        @foreach($catList as $idx => $cat)
                            @php
                                $catImg = !empty($cat['image_url']) ? $cat['image_url'] : (!empty($userImages[$idx]) ? $userImages[$idx] : null);
                            @endphp
                            <div class="petal-pick-card">
                                <div class="petal-pick-frame">
                                    @if($catImg)
                                        <img src="{{ asset($catImg) }}" alt="{{ $cat['title'] ?? ($tenant->name . ' Bakery Category') }}" loading="lazy" decoding="async">
                                    @else
                                        <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:linear-gradient(160deg, #8b5a45 0%, #2b2118 100%);">
                                            <span class="material-symbols-outlined" style="font-size:2.5rem; color:#ffffff;">cake</span>
                                        </div>
                                    @endif
                                    <span class="petal-pick-badge">New</span>
                                </div>
                                <h4>{{ $cat['title'] ?? 'Category' }}</h4>
                                @if(!empty($cat['desc']))
                                    <p>{{ $cat['desc'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @elseif($secId === 'promo_video')
                <!-- Promo, re-purposed as a full-width soft-mauve banner -->
                @php
                    $promoMedia = $tenant->getSiteContent('promo_video_url') ?: $tenant->getSiteContent('promo_bg_image_url');
                    $promoVid = (!empty($promoMedia) && str_ends_with(strtolower($promoMedia), '.mp4')) ? $promoMedia : null;
                    $promoBg = (!empty($promoMedia) && !$promoVid) ? $promoMedia : null;
                @endphp
                @if(!empty($promoVid))
                    <section class="video-promo-banner">
                        <video autoplay loop muted playsinline>
                            <source src="{{ asset($promoVid) }}" type="video/mp4">
                        </video>
                        <div class="video-overlay-content">
                            <h2>{{ $tenant->getSiteContent('promo_headline', 'Special Custom Orders!') }}</h2>
                            <p>{{ $tenant->getSiteContent('promo_subtext', 'Order online directly from our kitchen.') }}</p>
                            <button onclick="openOrderModal()" class="btn btn-dark">Order Now</button>
                        </div>
                    </section>
                @else
                    <section class="petal-promo">
                        <span class="subheading">Freshly Made</span>
                        <h2>{{ $tenant->getSiteContent('promo_headline', 'Baked With Love, Every Time') }}</h2>
                        <p style="max-width:520px; margin:14px auto 0 auto; color:#4a3e33;">{{ $tenant->getSiteContent('promo_subtext', 'Order online directly from our kitchen for your upcoming celebration.') }}</p>
                        <div class="petal-promo-media">
                            @if(!empty($promoBg))
                                <img src="{{ asset($promoBg) }}" alt="{{ $tenant->name }}" loading="lazy" decoding="async">
                            @else
                                <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:linear-gradient(160deg, #8b5a45 0%, #2b2118 100%);">
                                    <span class="material-symbols-outlined" style="font-size:3rem; color:#ffffff;">redeem</span>
                                </div>
                            @endif
                        </div>
                        <button onclick="openOrderModal()" class="btn btn-primary" style="margin-top:28px;">Order Now</button>
                    </section>
                @endif
            @elseif($secId === 'whimsical')
                <!-- Whimsical Section -->
                <section class="whimsical-section">
                    <div class="whimsical-two-column">
                        <div class="whimsical-col-left">
                            @php
                                $wImg = $tenant->getSiteContent('whimsical_image_url');
                                if (empty($wImg) && !empty($tenant->gallery_images[0])) {
                                    $wImg = $tenant->gallery_images[0];
                                }
                            @endphp
                            @if($wImg)
                                <img src="{{ asset($wImg) }}" alt="{{ $tenant->name }} Whimsical Creation" style="border-radius:20px;" loading="lazy" decoding="async">
                            @else
                                <div style="text-align:center; padding:40px 20px; background:rgba(255,255,255,0.4); border-radius:20px;">
                                    <span class="material-symbols-outlined" style="font-size:4rem; display:block; margin-bottom:12px; color:#2b2118;">auto_awesome</span>
                                </div>
                            @endif
                        </div>
                        <div class="whimsical-col-right">
                            <h2>{{ $tenant->getSiteContent('whimsical_title', 'Whimsical Creations for Every Milestone') }}</h2>
                            <ul class="whimsical-bullet-list" style="color:#4a3e33;">
                                @php
                                    $bullets = $tenant->getSiteContent('whimsical_bullets', []);
                                @endphp
                                @foreach($bullets as $bullet)
                                    <li style="color:#4a3e33;">{{ $bullet }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </section>
            @elseif($secId === 'how_it_works')
                <!-- How It Works — elegant numbered list -->
                <section style="padding:90px 25px; text-align:center;">
                    <span class="subheading">Getting Started</span>
                    <h2 class="section-title-script" style="margin:10px 0 40px 0;">How Ordering Works</h2>
                    <div class="petal-steps-list">
                        @php $steps = $tenant->getSiteContent('how_it_works', []); @endphp
                        @foreach($steps as $idx => $step)
                            <div class="petal-step-row">
                                <span class="petal-step-num">{{ $idx + 1 }}</span>
                                <div>
                                    <h3>{{ $step['title'] ?? '' }}</h3>
                                    <p>{{ $step['desc'] ?? '' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @elseif($secId === 'reviews')
                @php
                    $dbReviews = (isset($reviews) && count($reviews) > 0) ? $reviews : [];
                    $aiReviews = $tenant->getSiteContent('reviews', []);
                    $displayReviews = !empty($dbReviews) ? $dbReviews : $aiReviews;
                @endphp
                @if(!empty($displayReviews))
                <!-- Customer Reviews -->
                <section id="reviews" class="reviews-section">
                    <h2 class="section-title-script" style="text-align:center;">What Our Customers Say</h2>
                    <div class="reviews-grid" id="public-reviews-grid">
                        @foreach($displayReviews as $rev)
                            <div class="cloud-review-card">
                                <p>"{{ is_array($rev) ? ($rev['quote'] ?? $rev['text'] ?? '') : ($rev->review_text ?? '') }}"</p>
                                <h4>{{ is_array($rev) ? ($rev['name'] ?? '') : ($rev->client_name ?? '') }}</h4>
                            </div>
                        @endforeach
                    </div>
                </section>
                @endif
            @elseif($secId === 'faq')
                <!-- FAQ — soft accordion -->
                <section style="padding:90px 25px; background:#f3e3dc; text-align:center;">
                    <h2 class="section-title-script" style="margin-bottom:35px;">Frequently Asked Questions</h2>
                    <div class="petal-accordion">
                        @php $faqs = $tenant->getSiteContent('faqs', []); @endphp
                        @foreach($faqs as $faq)
                            <details class="petal-accordion-item">
                                <summary>{{ $faq['q'] ?? '' }}</summary>
                                <p>{{ $faq['a'] ?? '' }}</p>
                            </details>
                        @endforeach
                    </div>
                </section>
            @elseif($secId === 'featured_gallery')
                <!-- Featured Photos Gallery -->
                @php $featuredImages = $tenant->getSiteContent('featured_gallery_images', []); @endphp
                @if(!empty($featuredImages))
                    <section class="featured-gallery-section" style="padding:80px 25px; background:#ffffff; text-align:center;">
                        <h2 class="section-title-script" style="margin-bottom:35px;">{{ $tenant->getSiteContent('featured_gallery_title', 'Featured Creations') }}</h2>
                        <div class="gallery-masonry-grid petal-grid" style="max-width:1150px; margin:0 auto;">
                            @foreach($featuredImages as $fImg)
                                @php $fSrc = $fImg['path'] ?? null; @endphp
                                @if($fSrc)
                                    <div class="gallery-card" onclick="openLightbox(@js(asset($fSrc)), @js($fImg['title'] ?? ''))">
                                        <div class="gallery-card-img-wrap">
                                            <img src="{{ asset($fSrc) }}" alt="{{ $fImg['title'] ?? ($tenant->name . ' Featured Cake Creation') }}" loading="lazy" decoding="async">
                                        </div>
                                        @if(!empty($fImg['title']))
                                            <div class="gallery-card-info">
                                                <h4>{{ $fImg['title'] }}</h4>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </section>
                @endif
            @elseif($secId === 'cta_banner')
                <!-- Closing CTA banner -->
                @php
                    $ctaBg = $tenant->getSiteContent('cta_banner_url') ?: $tenant->getSiteContent('cta_bg_image_url');
                @endphp
                <section class="cta-video-banner" style="position:relative; background: {{ !empty($ctaBg) ? 'url(' . asset($ctaBg) . ') center/cover no-repeat' : 'linear-gradient(135deg, #8b5a45 0%, #2b2118 100%)' }}; padding: 70px 25px; text-align: center;">
                    <div class="cta-content" style="max-width:750px; margin:0 auto; position:relative; z-index:2;">
                        <h2 style="font-size: 2.6rem; margin-bottom: 10px;">{{ $tenant->getSiteContent('cta_headline', 'Ready to Order?') }}</h2>
                        <p style="font-size: 1.05rem; opacity: 0.95; margin-bottom: 24px;">{{ $tenant->getSiteContent('cta_subtext', "Let's bring your order to life.") }}</p>
                        <button onclick="openOrderModal()" class="btn btn-primary" style="padding: 14px 34px; font-size: 1rem;">{{ $tenant->getSiteContent('cta_btn_text', 'Order Now') }}</button>
                    </div>
                </section>
            @endif
        @endif
    @endforeach
</div>

@include('storefront.partials.order_modal')

<!-- LIGHTBOX MODAL FOR FEATURED GALLERY PREVIEWS -->
<div id="lightbox-modal" class="lightbox-modal" style="display:none;" onclick="closeLightbox()">
    <div class="lightbox-content" onclick="event.stopPropagation()">
        <button class="modal-close-btn" onclick="closeLightbox()"><span class="material-symbols-outlined" style="font-size:1.2rem; vertical-align:-2px;">close</span></button>
        <img id="lightbox-img" src="" alt="Gallery Preview" loading="lazy" decoding="async">
        <div id="lightbox-caption" class="lightbox-caption"></div>
    </div>
</div>

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
    <p class="copyright-text">Copyright &copy; 2026 {{ $tenant->name ?? 'Blushed Crumbs Bakehouse' }} | <a href="{{ route('legal.index') }}" class="footer-link">Legal Hub</a> &middot; <a href="{{ route('storefront.privacy') }}" class="footer-link">Privacy</a> &middot; <a href="{{ route('storefront.terms') }}" class="footer-link">Terms</a> | Powered by <a href="https://doughmain.pro" target="_blank" class="footer-link footer-brand-link">Doughmain.pro</a></p>
</footer>

<script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}"></script>
</body>
</html>
