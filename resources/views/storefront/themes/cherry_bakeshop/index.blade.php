<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('storefront.partials.seo_head', ['page' => 'home'])

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

    <!-- CSS Assets -->
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
            <a href="#" onclick="openOrderModal()" class="nav-order-btn">Order Now</a>
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
                <!-- Hero — cream polka-dot band, badge photo frame with ribbon corner -->
                @php
                    $heroBg = $tenant->getSiteContent('hero_bg_url');
                    $heroIsVideo = !empty($heroBg) && str_ends_with(strtolower($heroBg), '.mp4');
                @endphp
                <section class="cb-hero">
                    <div class="cb-hero-inner">
                        <div class="cb-hero-copy">
                            <span class="subheading">{{ $tenant->getSiteContent('hero_subheading', 'Welcome to ' . ($tenant->name ?? 'our bakehouse')) }}</span>
                            <h1>{{ $tenant->getSiteContent('hero_headline', $tenant->name ?? 'Artisanal Bakehouse') }}</h1>
                            <div class="hero-buttons">
                                <button onclick="openOrderModal()" class="btn btn-primary">{{ $tenant->getSiteContent('hero_cta_primary', 'Order Now') }}</button>
                                <a href="{{ route('storefront.gallery') }}" class="btn btn-secondary">{{ $tenant->getSiteContent('hero_cta_secondary', 'Our Treats') }}</a>
                            </div>
                        </div>
                        <div class="cb-hero-media">
                            <div class="cb-badge-frame">
                                @if($heroIsVideo)
                                    <video autoplay loop muted playsinline>
                                        <source src="{{ asset($heroBg) }}" type="video/mp4">
                                    </video>
                                @elseif(!empty($heroBg))
                                    <img src="{{ asset($heroBg) }}" alt="{{ $tenant->name }}" fetchpriority="high">
                                @else
                                    <div class="cb-media-placeholder"><span class="material-symbols-outlined" style="font-size:4.5rem;">bakery_dining</span></div>
                                @endif
                            </div>
                            <span class="cb-ribbon">Fresh Daily!</span>
                        </div>
                    </div>
                    <div class="cb-scallop-divider"></div>
                </section>
            @elseif($secId === 'about')
                <!-- About / Our Story — badge photo left, bio right -->
                <section class="cb-section cb-band-white">
                    <div class="cb-about-row">
                        <div class="cb-badge-frame cb-badge-frame-sm">
                            @php
                                $aboutImg = !empty($tenant->gallery_images[0]) ? asset($tenant->gallery_images[0]) : (!empty($tenant->logo_path) ? asset($tenant->logo_path) : null);
                            @endphp
                            @if($aboutImg)
                                <img src="{{ $aboutImg }}" alt="{{ $tenant->name }}" loading="lazy" decoding="async">
                            @else
                                <div class="cb-media-placeholder"><span class="material-symbols-outlined" style="font-size:3rem;">cake</span></div>
                            @endif
                        </div>
                        <div class="cb-about-copy">
                            <span class="cb-kicker">Our Story</span>
                            <h2>{{ $tenant->getSiteContent('about_title', 'About Our Bakery') }}</h2>
                            <p>{{ $tenant->getSiteContent('about_bio', 'Welcome to ' . ($tenant->name ?? 'our bakehouse') . '! We specialize in handcrafted baked goods made fresh with premium ingredients and a whole lot of care.') }}</p>
                            <a href="{{ route('storefront.about') }}" class="cb-text-link">Read our full story →</a>
                        </div>
                    </div>
                </section>
            @elseif($secId === 'highlights')
                <!-- Highlights — mirrored row -->
                <section class="cb-section cb-band-cream">
                    <div class="cb-about-row cb-about-row-reverse">
                        <div class="cb-badge-frame cb-badge-frame-sm">
                            @php
                                $hlImg = !empty($tenant->gallery_images[1]) ? asset($tenant->gallery_images[1]) : (!empty($tenant->gallery_images[0]) ? asset($tenant->gallery_images[0]) : (!empty($tenant->logo_path) ? asset($tenant->logo_path) : null));
                            @endphp
                            @if($hlImg)
                                <img src="{{ $hlImg }}" alt="{{ $tenant->name }}" loading="lazy" decoding="async">
                            @else
                                <div class="cb-media-placeholder"><span class="material-symbols-outlined" style="font-size:3rem;">auto_awesome</span></div>
                            @endif
                        </div>
                        <div class="cb-about-copy">
                            <span class="cb-kicker">Why Choose Us</span>
                            <h2>Why {{ $tenant->name }}</h2>
                            <ul class="cb-highlight-list">
                                @php $highlights = $tenant->getSiteContent('highlights', []); @endphp
                                @foreach($highlights as $hl)
                                    <li><span class="cb-highlight-emoji">{{ $hl['icon'] ?? '🍒' }}</span> <strong>{{ $hl['title'] ?? '' }}</strong> — {{ $hl['desc'] ?? '' }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </section>
            @elseif($secId === 'promo_video')
                <!-- Video/Image Promo Banner -->
                @php
                    $promoMedia = $tenant->getSiteContent('promo_video_url') ?: $tenant->getSiteContent('promo_bg_image_url');
                    $promoVid = (!empty($promoMedia) && str_ends_with(strtolower($promoMedia), '.mp4')) ? $promoMedia : null;
                    $promoBg = (!empty($promoMedia) && !$promoVid) ? $promoMedia : null;
                @endphp
                @if(!empty($promoVid))
                    <section class="video-promo-banner cb-promo-banner">
                        <video autoplay loop muted playsinline>
                            <source src="{{ asset($promoVid) }}" type="video/mp4">
                        </video>
                        <div class="video-overlay-content">
                            <h2>{{ $tenant->getSiteContent('promo_headline', 'Special Bakery Orders!') }}</h2>
                            <p>{{ $tenant->getSiteContent('promo_subtext', 'Order online directly from our kitchen for pickup or delivery.') }}</p>
                            <button onclick="openOrderModal()" class="btn btn-primary">Order Now</button>
                        </div>
                    </section>
                @else
                    <section class="video-promo-banner cb-promo-banner" style="position:relative; background: {{ !empty($promoBg) ? 'linear-gradient(135deg, rgba(160,41,63,0.82) 0%, rgba(160,41,63,0.55) 100%), url(' . asset($promoBg) . ') center/cover no-repeat' : 'linear-gradient(135deg, #a0293f 0%, #c94b60 100%)' }}; padding: 75px 25px; text-align: center;">
                        <div class="video-overlay-content" style="position:relative; z-index:2; max-width:720px; margin:0 auto;">
                            <span class="material-symbols-outlined" style="font-size:2.2rem; display:block; margin-bottom:10px; color:#fff5f0;">redeem</span>
                            <h2 style="font-size:2.4rem; font-weight:700; color:#ffffff; margin-bottom:12px;">{{ $tenant->getSiteContent('promo_headline', 'Special Bakery Orders!') }}</h2>
                            <p style="font-size:1.05rem; color:rgba(255,255,255,0.92); margin-bottom:24px;">{{ $tenant->getSiteContent('promo_subtext', 'Order online directly from our kitchen for pickup or delivery.') }}</p>
                            <button onclick="openOrderModal()" class="btn btn-primary" style="padding:13px 34px; font-size:0.9rem; border-radius:30px; box-shadow:0 4px 15px rgba(0,0,0,0.25);">Order Now</button>
                        </div>
                    </section>
                @endif
            @elseif($secId === 'categories')
                <!-- Categories — badge card row with price-tag ribbons -->
                <section id="categories" class="cb-section cb-band-cherry">
                    <span class="cb-kicker cb-kicker-light">What We Make</span>
                    <h2 class="cb-section-title">Our Categories</h2>
                    <div class="cb-shelf-row">
                        @php
                            $catList = $tenant->getSiteContent('categories', [
                                ['title' => 'Signature Cakes', 'desc' => 'Layered, filled, and finished by hand'],
                                ['title' => 'Cupcakes & Pastries', 'desc' => 'Perfect for parties and everyday treats'],
                                ['title' => 'Custom Orders', 'desc' => 'Made to order for every celebration']
                            ]);
                            $userImages = $tenant->gallery_images ?? [];
                        @endphp
                        @foreach($catList as $idx => $cat)
                            @php
                                $catImg = !empty($cat['image_url']) ? $cat['image_url'] : (!empty($userImages[$idx]) ? $userImages[$idx] : null);
                                $imgUrl = !empty($catImg) ? asset($catImg) : null;
                            @endphp
                            <div class="cb-shelf-card">
                                <div class="cb-shelf-frame">
                                    @if($imgUrl)
                                        <img src="{{ $imgUrl }}" alt="{{ $cat['title'] ?? ($tenant->name . ' Bakery Category') }}" loading="lazy" decoding="async">
                                    @else
                                        <span class="material-symbols-outlined cb-icon">cake</span>
                                    @endif
                                </div>
                                <h3>{{ $cat['title'] ?? 'Category' }}</h3>
                                @if(!empty($cat['desc']))
                                    <p>{{ $cat['desc'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @elseif($secId === 'whimsical')
                <!-- Whimsical Section — polka-dot cream band -->
                <section class="whimsical-section cb-whimsical">
                    <div class="whimsical-two-column">
                        <div class="whimsical-col-left">
                            @php
                                $wImg = $tenant->getSiteContent('whimsical_image_url');
                                if (empty($wImg) && !empty($tenant->gallery_images[0])) {
                                    $wImg = $tenant->gallery_images[0];
                                }
                            @endphp
                            @if($wImg)
                                <div class="cb-badge-frame cb-badge-frame-md">
                                    <img src="{{ asset($wImg) }}" alt="{{ $tenant->name }} Creation" loading="lazy" decoding="async">
                                </div>
                            @else
                                <div class="cb-badge-frame cb-badge-frame-md cb-media-placeholder">
                                    <span class="material-symbols-outlined" style="font-size:4rem; color:#a0293f;">auto_awesome</span>
                                </div>
                            @endif
                        </div>
                        <div class="whimsical-col-right">
                            <span class="cb-kicker">Handmade With Love</span>
                            <h2>{{ $tenant->getSiteContent('whimsical_title', 'Handcrafted for Every Occasion') }}</h2>
                            <ul class="whimsical-bullet-list">
                                @php
                                    $bullets = $tenant->getSiteContent('whimsical_bullets', []);
                                @endphp
                                @foreach($bullets as $bullet)
                                    <li>{{ $bullet }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </section>
            @elseif($secId === 'how_it_works')
                <!-- How It Works — numbered cherry-circle rows -->
                <section class="how-it-works-section cb-section cb-band-white">
                    <span class="cb-kicker" style="display:block; text-align:center;">Getting Started</span>
                    <h2 class="cb-section-title" style="text-align:center;">How To Order</h2>
                    <div class="cb-steps-list">
                        @php $steps = $tenant->getSiteContent('how_it_works', []); @endphp
                        @foreach($steps as $idx => $step)
                            <div class="cb-step-row">
                                <span class="cb-step-num"></span>
                                <div class="cb-step-copy">
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
                <!-- Reviews — speech-bubble cards -->
                <section id="reviews" class="reviews-section cb-section cb-band-cream">
                    <span class="cb-kicker" style="display:block; text-align:center;">Kind Words</span>
                    <h2 class="cb-section-title" style="text-align:center;">What Our Customers Say</h2>
                    <div class="cb-review-row">
                        @foreach($displayReviews as $rev)
                            <div class="cb-review-card">
                                <p>"{{ is_array($rev) ? ($rev['quote'] ?? $rev['text'] ?? '') : ($rev->review_text ?? '') }}"</p>
                                <h4>{{ is_array($rev) ? ($rev['name'] ?? '') : ($rev->client_name ?? '') }}</h4>
                            </div>
                        @endforeach
                    </div>
                </section>
                @endif
            @elseif($secId === 'faq')
                <!-- FAQ & Bakery Policies -->
                <section class="faq-policies-section cb-section cb-band-white">
                    <span class="cb-kicker" style="display:block; text-align:center;">Good To Know</span>
                    <h2 class="cb-section-title" style="text-align:center; margin-bottom:15px;">Frequently Asked Questions</h2>
                    <div style="max-width:850px; margin:0 auto; text-align:left; display:flex; flex-direction:column; gap:16px;">
                        @php $faqs = $tenant->getSiteContent('faqs', []); @endphp
                        @foreach($faqs as $faq)
                            <div class="cb-faq-card">
                                <h4>{{ $faq['q'] ?? '' }}</h4>
                                <p>{{ $faq['a'] ?? '' }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>
            @elseif($secId === 'featured_gallery')
                <!-- Featured Photos Gallery -->
                @php $featuredImages = $tenant->getSiteContent('featured_gallery_images', []); @endphp
                @if(!empty($featuredImages))
                    <section class="featured-gallery-section cb-section cb-band-cream">
                        <h2 class="cb-section-title" style="text-align:center;">{{ $tenant->getSiteContent('featured_gallery_title', 'Featured Creations') }}</h2>
                        <div class="gallery-masonry-grid cb-featured-grid" style="max-width:1150px; margin:0 auto;">
                            @foreach($featuredImages as $fImg)
                                @php $fSrc = $fImg['path'] ?? null; @endphp
                                @if($fSrc)
                                    <div class="gallery-card" onclick="openLightbox(@js(asset($fSrc)), @js($fImg['title'] ?? ''))">
                                        <div class="gallery-card-img-wrap">
                                            <img src="{{ asset($fSrc) }}" alt="{{ $fImg['title'] ?? ($tenant->name . ' Featured Creation') }}" loading="lazy" decoding="async">
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
                <!-- Footer CTA Banner — bold cherry with scalloped top edge -->
                @php
                    $ctaBg = $tenant->getSiteContent('cta_banner_url') ?: $tenant->getSiteContent('cta_bg_image_url');
                @endphp
                <section class="cta-video-banner cb-cta-banner" style="position:relative; background: {{ !empty($ctaBg) ? 'linear-gradient(135deg, rgba(160,41,63,0.85) 0%, rgba(160,41,63,0.6) 100%), url(' . asset($ctaBg) . ') center/cover no-repeat' : '#a0293f' }};">
                    <div class="cb-scallop-divider cb-scallop-divider-top"></div>
                    <div class="cta-content" style="max-width:750px; margin:0 auto; position:relative; z-index:2; padding: 55px 25px 70px 25px; text-align:center;">
                        <h2 style="font-size: 2.6rem; color: #ffffff; margin-bottom: 10px;">{{ $tenant->getSiteContent('cta_headline', 'Ready to Order?') }}</h2>
                        <p style="font-size: 1.1rem; color: #fff5f0; opacity: 0.92; margin-bottom: 24px;">{{ $tenant->getSiteContent('cta_subtext', 'Order your favorites now') }}</p>
                        <button onclick="openOrderModal()" class="btn btn-primary" style="padding: 14px 34px; font-size: 0.95rem; border-radius: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.25);">{{ $tenant->getSiteContent('cta_btn_text', 'Order Now') }}</button>
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
