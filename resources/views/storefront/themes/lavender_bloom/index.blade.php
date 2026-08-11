<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('storefront.partials.seo_head', ['page' => 'home'])

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700;800&family=Poppins:wght@400;500;600;700;800&family=Great+Vibes&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

    <!-- CSS Assets -->
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
                <a href="{{ route('storefront.index') }}" class="active">Home</a>
                <a href="{{ route('storefront.about') }}">About</a>
                <a href="{{ route('storefront.menu') }}">Menu</a>
                <a href="{{ route('storefront.gallery') }}">Gallery</a>
                <a href="{{ route('storefront.policy') }}">Policy</a>
                <a href="#" onclick="openOrderModal()" class="nav-order-btn">Order Now</a>
            </nav>
        </div>
    </div>
</header>

<div id="storefront-view">
    @php
        $sections = $tenant->getOrderedSections();
    @endphp

    @foreach($sections as $secId => $sec)
        @if(!empty($sec['enabled']))
            @if($secId === 'hero')
                <!-- Hero — soft lavender gradient band, rounded photo frame -->
                @php
                    $heroBg = $tenant->getSiteContent('hero_bg_url');
                    $heroIsVideo = !empty($heroBg) && str_ends_with(strtolower($heroBg), '.mp4');
                @endphp
                <section class="lb-hero">
                    <div class="lb-hero-inner">
                        <div class="lb-hero-copy">
                            <span class="subheading">{{ $tenant->getSiteContent('hero_subheading', 'Welcome to ' . ($tenant->name ?? 'our bakehouse')) }}</span>
                            <h1>{{ $tenant->getSiteContent('hero_headline', $tenant->name ?? 'Artisanal Bakehouse') }}</h1>
                            <div class="hero-buttons">
                                <button onclick="openOrderModal()" class="btn btn-primary">{{ $tenant->getSiteContent('hero_cta_primary', 'Order Now') }}</button>
                                <a href="{{ route('storefront.gallery') }}" class="btn btn-secondary">{{ $tenant->getSiteContent('hero_cta_secondary', 'Our Treats') }}</a>
                            </div>
                        </div>
                        <div class="lb-hero-media">
                            @if($heroIsVideo)
                                <video autoplay loop muted playsinline>
                                    <source src="{{ asset($heroBg) }}" type="video/mp4">
                                </video>
                            @elseif(!empty($heroBg))
                                <img src="{{ asset($heroBg) }}" alt="{{ $tenant->name }}" fetchpriority="high">
                            @else
                                <div class="lb-hero-media-placeholder"><span class="material-symbols-outlined" style="font-size:5rem;">local_florist</span></div>
                            @endif
                        </div>
                    </div>
                </section>
            @elseif($secId === 'about')
                <!-- About / Our Story — offset dual-layer photo frame + copy card -->
                <section class="lb-section lb-band-lavender">
                    <div class="lb-about-row">
                        <div class="lb-about-photo-wrap">
                            <div class="lb-about-photo-backdrop"></div>
                            <div class="lb-about-photo">
                                @php
                                    $aboutImg = !empty($tenant->gallery_images[0]) ? asset($tenant->gallery_images[0]) : (!empty($tenant->logo_path) ? asset($tenant->logo_path) : null);
                                @endphp
                                @if($aboutImg)
                                    <img src="{{ $aboutImg }}" alt="{{ $tenant->name }}" loading="lazy" decoding="async">
                                @else
                                    <div class="lb-photo-placeholder"><span class="material-symbols-outlined" style="font-size:3rem;">cake</span></div>
                                @endif
                            </div>
                        </div>
                        <div class="lb-about-copy-card">
                            <span class="lb-about-kicker">Our Story</span>
                            <h2>{{ $tenant->getSiteContent('about_title', 'About Our Bakery') }}</h2>
                            <p>{{ $tenant->getSiteContent('about_bio', 'Welcome to ' . ($tenant->name ?? 'our bakehouse') . '! We specialize in custom artisanal cakes, gourmet treats, and unforgettable dessert experiences crafted with premium ingredients and passion.') }}</p>
                            <a href="{{ route('storefront.about') }}" class="lb-text-link">Read our full story →</a>
                        </div>
                    </div>
                </section>
            @elseif($secId === 'highlights')
                <!-- Highlights — horizontal trust bar, 4 icon columns -->
                <section class="lb-section lb-band-white">
                    <div class="lb-highlight-strip">
                        @php $highlights = $tenant->getSiteContent('highlights', []); @endphp
                        @foreach($highlights as $hl)
                            <div class="lb-highlight-item">
                                <span class="lb-highlight-icon">{{ $hl['icon'] ?? '🎂' }}</span>
                                <h4>{{ $hl['title'] ?? '' }}</h4>
                                <p>{{ $hl['desc'] ?? '' }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>
            @elseif($secId === 'promo_video')
                <!-- Video/Image Promo Banner — purple gradient "sale" card -->
                @php
                    $promoMedia = $tenant->getSiteContent('promo_video_url') ?: $tenant->getSiteContent('promo_bg_image_url');
                    $promoVid = (!empty($promoMedia) && str_ends_with(strtolower($promoMedia), '.mp4')) ? $promoMedia : null;
                    $promoBg = (!empty($promoMedia) && !$promoVid) ? $promoMedia : null;
                @endphp
                @if(!empty($promoVid))
                    <section class="video-promo-banner lb-promo-banner">
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
                    <section class="video-promo-banner lb-promo-banner" style="position:relative; background: {{ !empty($promoBg) ? 'linear-gradient(135deg, rgba(59,31,71,0.82) 0%, rgba(142,79,163,0.65) 100%), url(' . asset($promoBg) . ') center/cover no-repeat' : 'linear-gradient(135deg, #6b3480 0%, #8e4fa3 100%)' }}; padding: 75px 25px; text-align: center;">
                        <div class="video-overlay-content" style="position:relative; z-index:2; max-width:720px; margin:0 auto;">
                            <span class="material-symbols-outlined" style="font-size:2.2rem; display:block; margin-bottom:10px; color:#f3d9ff;">redeem</span>
                            <h2 style="font-size:2.4rem; font-weight:800; color:#ffffff; margin-bottom:12px;">{{ $tenant->getSiteContent('promo_headline', 'Special Custom Bakery Orders!') }}</h2>
                            <p style="font-size:1.05rem; color:rgba(255,255,255,0.88); margin-bottom:24px;">{{ $tenant->getSiteContent('promo_subtext', 'Order online directly from our kitchen for your upcoming celebration.') }}</p>
                            <button onclick="openOrderModal()" class="btn btn-primary" style="padding:13px 34px; font-size:0.9rem; border-radius:30px; box-shadow:0 4px 15px rgba(0,0,0,0.3);">Order Now</button>
                        </div>
                    </section>
                @endif
            @elseif($secId === 'categories')
                <!-- Categories — photo tiles with solid purple banner label -->
                <section id="categories" class="lb-section lb-band-white">
                    <h2 class="lb-section-title" style="text-align:center;">Our Categories</h2>
                    <div class="lb-category-grid">
                        @php
                            $catList = $tenant->getSiteContent('categories', [
                                ['title' => 'Single Tier Cakes', 'desc' => 'Perfect for birthdays & intimate gatherings'],
                                ['title' => 'Multi Tier Custom Cakes', 'desc' => 'Bespoke designs for weddings & celebrations'],
                                ['title' => 'Treats & Sweets By The Dozen', 'desc' => 'Cupcakes, macarons, and dessert tables']
                            ]);
                            $userImages = $tenant->gallery_images ?? [];
                        @endphp
                        @foreach($catList as $idx => $cat)
                            @php
                                $catImg = !empty($cat['image_url']) ? $cat['image_url'] : (!empty($userImages[$idx]) ? $userImages[$idx] : null);
                                $imgUrl = !empty($catImg) ? asset($catImg) : null;
                            @endphp
                            <div class="lb-category-tile">
                                <div class="lb-category-frame">
                                    @if($imgUrl)
                                        <img src="{{ $imgUrl }}" alt="{{ $cat['title'] ?? ($tenant->name . ' Bakery Category') }}" loading="lazy" decoding="async">
                                    @else
                                        <span class="material-symbols-outlined lb-icon">cake</span>
                                    @endif
                                </div>
                                <div class="lb-category-banner">{{ $cat['title'] ?? 'Category' }}</div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @elseif($secId === 'whimsical')
                <!-- Whimsical Section — light band, checklist-chip grid (not a photo/text mirror) -->
                @php
                    $wImg = $tenant->getSiteContent('whimsical_image_url');
                    if (empty($wImg) && !empty($tenant->gallery_images[0])) {
                        $wImg = $tenant->gallery_images[0];
                    }
                @endphp
                <section class="whimsical-section lb-whimsical">
                    <div class="lb-whimsical-inner">
                        @if($wImg)
                            <div class="lb-whimsical-badge">
                                <img src="{{ asset($wImg) }}" alt="{{ $tenant->name }} Whimsical Creation" loading="lazy" decoding="async">
                            </div>
                        @endif
                        <h2>{{ $tenant->getSiteContent('whimsical_title', 'Whimsical Creations for Every Milestone') }}</h2>
                        <div class="lb-whimsical-grid">
                            @php
                                $bullets = $tenant->getSiteContent('whimsical_bullets', []);
                            @endphp
                            @foreach($bullets as $bullet)
                                <div class="lb-whimsical-chip">
                                    <span class="material-symbols-outlined">check_circle</span>
                                    <span>{{ $bullet }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @elseif($secId === 'how_it_works')
                <!-- How It Works — horizontal step-card grid -->
                <section class="how-it-works-section lb-section lb-band-white">
                    <h2 class="lb-section-title" style="text-align:center;">How To Order</h2>
                    <div class="lb-steps-grid">
                        @php $steps = $tenant->getSiteContent('how_it_works', []); @endphp
                        @foreach($steps as $idx => $step)
                            <div class="lb-step-card">
                                <span class="lb-step-num">{{ $idx + 1 }}</span>
                                <h3>{{ $step['title'] ?? '' }}</h3>
                                <p>{{ $step['desc'] ?? '' }}</p>
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
                <!-- Reviews -->
                <section id="reviews" class="reviews-section lb-section lb-band-lavender">
                    <h2 class="lb-section-title">Sweet Words from Our Customers</h2>
                    <div class="lb-review-row">
                        @foreach($displayReviews as $rev)
                            <div class="lb-review-card">
                                <p>"{{ is_array($rev) ? ($rev['quote'] ?? $rev['text'] ?? '') : ($rev->review_text ?? '') }}"</p>
                                <h4>{{ is_array($rev) ? ($rev['name'] ?? '') : ($rev->client_name ?? '') }}</h4>
                            </div>
                        @endforeach
                    </div>
                </section>
                @endif
            @elseif($secId === 'faq')
                <!-- FAQ & Bakery Policies -->
                <section class="faq-policies-section lb-section lb-band-white">
                    <h2 class="lb-section-title" style="text-align:center; margin-bottom:15px;">Frequently Asked Questions</h2>
                    <div style="max-width:850px; margin:0 auto; text-align:left; display:flex; flex-direction:column; gap:18px;">
                        @php $faqs = $tenant->getSiteContent('faqs', []); @endphp
                        @foreach($faqs as $faq)
                            <div class="lb-faq-card">
                                <h4>{{ $faq['q'] ?? '' }}</h4>
                                <p>{{ $faq['a'] ?? '' }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>
            @elseif($secId === 'featured_gallery')
                <!-- Featured Photos Gallery — "Featured Products" style card grid -->
                @php $featuredImages = $tenant->getSiteContent('featured_gallery_images', []); @endphp
                @if(!empty($featuredImages))
                    <section class="featured-gallery-section lb-section lb-band-white">
                        <h2 class="lb-section-title" style="text-align:center;">{{ $tenant->getSiteContent('featured_gallery_title', 'Featured Creations') }}</h2>
                        <div class="gallery-masonry-grid lb-featured-grid" style="max-width:1150px; margin:0 auto;">
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
                <!-- Footer Call to Action Banner — purple gradient, "get updates" energy -->
                @php
                    $ctaBg = $tenant->getSiteContent('cta_banner_url') ?: $tenant->getSiteContent('cta_bg_image_url');
                @endphp
                <section class="cta-video-banner lb-cta-banner" style="position:relative; background: {{ !empty($ctaBg) ? 'linear-gradient(135deg, rgba(59,31,71,0.82) 0%, rgba(142,79,163,0.62) 100%), url(' . asset($ctaBg) . ') center/cover no-repeat' : 'linear-gradient(135deg, #3b1f47 0%, #8e4fa3 100%)' }}; padding: 70px 25px; text-align: center;">
                    <div class="cta-content" style="max-width:750px; margin:0 auto; position:relative; z-index:2;">
                        <h2 style="font-size: 2.6rem; color: #ffffff; margin-bottom: 10px;">{{ $tenant->getSiteContent('cta_headline', 'Ready For Your Perfect Cake?') }}</h2>
                        <p style="font-size: 1.1rem; color: #f3e6f9; opacity: 0.95; margin-bottom: 24px;">{{ $tenant->getSiteContent('cta_subtext', 'Order your plan or custom order now') }}</p>
                        <button onclick="openOrderModal()" class="btn btn-primary" style="padding: 14px 34px; font-size: 0.95rem; border-radius: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">{{ $tenant->getSiteContent('cta_btn_text', 'Order Now') }}</button>
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
