<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('storefront.partials.seo_head', ['page' => 'home'])

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                <span style="font-family:'Playfair Display',serif; font-weight:700; font-size:1.3rem; color:var(--dark-text, #14304a);"><span class="material-symbols-outlined midnight-icon" style="font-size:1.2rem; vertical-align:-3px;">cake</span> {{ $tenant->name }}</span>
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
                <!-- Hero — deep navy band, oval photo left, copy right -->
                @php
                    $heroBg = $tenant->getSiteContent('hero_bg_url');
                    $heroIsVideo = !empty($heroBg) && str_ends_with(strtolower($heroBg), '.mp4');
                @endphp
                <section class="midnight-hero">
                    <div class="midnight-hero-media">
                        @if($heroIsVideo)
                            <video autoplay loop muted playsinline>
                                <source src="{{ asset($heroBg) }}" type="video/mp4">
                            </video>
                        @elseif(!empty($heroBg))
                            <img src="{{ asset($heroBg) }}" alt="{{ $tenant->name }}" fetchpriority="high">
                        @else
                            <div class="midnight-hero-media-placeholder"><span class="material-symbols-outlined" style="font-size:5rem; color:#fff;">bakery_dining</span></div>
                        @endif
                    </div>
                    <div class="midnight-hero-copy">
                        <span class="subheading">{{ $tenant->getSiteContent('hero_subheading', 'Welcome to ' . ($tenant->name ?? 'our bakehouse')) }}</span>
                        <h1>{{ $tenant->getSiteContent('hero_headline', $tenant->name ?? 'Artisanal Bakehouse') }}</h1>
                        <p>{{ $tenant->getSiteContent('about_bio', 'Handcrafted baked goods, made fresh and delivered right to your door.') }}</p>
                        <div class="hero-buttons">
                            <button onclick="openOrderModal()" class="btn btn-primary">{{ $tenant->getSiteContent('hero_cta_primary', 'Order Now') }}</button>
                        <a href="{{ route('storefront.gallery') }}" class="btn btn-secondary">{{ $tenant->getSiteContent('hero_cta_secondary', 'Our Treats') }}</a>
                        </div>
                    </div>
                </section>
            @elseif($secId === 'about')
                <!-- About Teaser -->
                <section class="about-teaser-section" style="padding:80px 25px; background:var(--theme-section-bg, var(--pink-bg, #fafafa)); text-align:center;">
                    <div style="max-width:900px; margin:0 auto; display:flex; flex-wrap:wrap; align-items:center; gap:40px; text-align:left;">
                        @php
                            $aboutImg = !empty($tenant->gallery_images[0]) ? asset($tenant->gallery_images[0]) : null;
                        @endphp
                        @if($aboutImg)
                            <div style="flex:1; min-width:260px;">
                                <img src="{{ $aboutImg }}" alt="{{ $tenant->name }}" loading="lazy" decoding="async" style="width:100%; border-radius:16px; box-shadow:0 10px 30px rgba(0,0,0,0.08);">
                            </div>
                        @endif
                        <div style="flex:1; min-width:260px;">
                            <span class="subheading" style="display:block; margin-bottom:8px;">Our Story</span>
                            <h2 style="margin-bottom:16px;">{{ $tenant->getSiteContent('about_title', 'Baking With Heart') }}</h2>
                            <p style="font-size:1.05rem; color:var(--dark-text); line-height:1.7; margin-bottom:24px;">{{ $tenant->getSiteContent('about_bio', 'Welcome to ' . ($tenant->name ?? 'our bakehouse') . '! We specialize in artisanal baked goods, made fresh with real ingredients and a whole lot of care.') }}</p>
                            <a href="{{ route('storefront.about') }}" class="btn btn-primary">Read Our Story</a>
                        </div>
                    </div>
                </section>
            @elseif($secId === 'highlights')
                <!-- Highlights, re-purposed as "What's Included" -->
                <section class="midnight-included">
                    <span class="subheading">What's Included</span>
                    <div class="midnight-included-grid">
                        @php $highlights = $tenant->getSiteContent('highlights', []); @endphp
                        @foreach($highlights as $hl)
                            <div>
                                <div class="midnight-included-icon">{{ $hl['icon'] ?? '🎂' }}</div>
                                <h4>{{ $hl['title'] ?? '' }}</h4>
                                <p>{{ $hl['desc'] ?? '' }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>
            @elseif($secId === 'how_it_works')
                <!-- How It Works, re-purposed as a white process card on navy -->
                <section class="midnight-process-wrap">
                    <div class="midnight-process-card">
                        <span class="subheading">Farm to Table</span>
                        <p>{{ $tenant->getSiteContent('how_it_works_subtitle', 'Ordering your custom order in 3 simple steps') }}</p>
                        @php $steps = $tenant->getSiteContent('how_it_works', []); @endphp
                        @foreach($steps as $idx => $step)
                            <div class="midnight-process-step">
                                <span class="midnight-process-num">{{ $idx + 1 }}</span>
                                <div>
                                    <h3>{{ $step['title'] ?? '' }}</h3>
                                    <p>{{ $step['desc'] ?? '' }}</p>
                                </div>
                                <span class="material-symbols-outlined">arrow_forward</span>
                            </div>
                        @endforeach
                    </div>
                </section>
            @elseif($secId === 'promo_video')
                <!-- Promo, re-purposed as a centered "Premium Spotlight" callout -->
                @php
                    $promoMedia = $tenant->getSiteContent('promo_video_url') ?: $tenant->getSiteContent('promo_bg_image_url');
                    $promoVid = (!empty($promoMedia) && str_ends_with(strtolower($promoMedia), '.mp4')) ? $promoMedia : null;
                    $promoBg = (!empty($promoMedia) && !$promoVid) ? $promoMedia : null;
                    $spotlightProduct = isset($products) ? $products->first() : null;
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
                    <section class="midnight-spotlight">
                        <span class="subheading">Premium</span>
                        <h2>{{ $tenant->getSiteContent('promo_headline', $spotlightProduct->name ?? 'Featured Creation') }}</h2>
                        <div class="midnight-spotlight-media">
                            @if(!empty($promoBg))
                                <img src="{{ asset($promoBg) }}" alt="{{ $tenant->name }}" loading="lazy" decoding="async">
                            @else
                                <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:linear-gradient(160deg, #14304a 0%, #b8935a 100%);">
                                    <span class="material-symbols-outlined" style="font-size:3rem; color:#ffffff;">bakery_dining</span>
                                </div>
                            @endif
                        </div>
                        @if($spotlightProduct)
                            <span class="midnight-spotlight-price">${{ number_format($spotlightProduct->price, 2) }}</span>
                        @endif
                        <button onclick="openOrderModal()" class="btn btn-primary">Order Now</button>
                        <div class="midnight-trust-row">
                            <div>
                                <span class="material-symbols-outlined">verified</span>
                                <p>Premium quality, always fresh</p>
                            </div>
                            <div>
                                <span class="material-symbols-outlined">eco</span>
                                <p>Only fresh and healthy ingredients</p>
                            </div>
                            <div>
                                <span class="material-symbols-outlined">local_shipping</span>
                                <p>Sourced and shipped with care</p>
                            </div>
                        </div>
                    </section>
                @endif
            @elseif($secId === 'categories')
                <!-- Categories, re-purposed as a "Featured Recipes"-style grid -->
                <section id="categories" class="categories-section">
                    <h2 class="section-title-script" style="text-align:center;">Featured Creations</h2>
                    <div class="midnight-recipe-grid">
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
                            <div>
                                <div class="midnight-recipe-card-frame">
                                    @if($catImg)
                                        <img src="{{ asset($catImg) }}" alt="{{ $cat['title'] ?? ($tenant->name . ' Bakery Category') }}" loading="lazy" decoding="async">
                                    @else
                                        <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:linear-gradient(160deg, #14304a 0%, #b8935a 100%);">
                                            <span class="material-symbols-outlined" style="font-size:2.5rem; color:#ffffff;">cake</span>
                                        </div>
                                    @endif
                                    <span class="midnight-recipe-badge">Featured</span>
                                </div>
                                <h4 style="font-family:'Playfair Display',serif; color:var(--dark-text);">{{ $cat['title'] ?? 'Category' }}</h4>
                            </div>
                        @endforeach
                    </div>
                </section>
            @elseif($secId === 'whimsical')
                <!-- Whimsical, re-purposed as a full-bleed dark editorial section -->
                @php
                    $wImg = $tenant->getSiteContent('whimsical_image_url');
                    if (empty($wImg) && !empty($tenant->gallery_images[0])) {
                        $wImg = $tenant->gallery_images[0];
                    }
                    $bullets = $tenant->getSiteContent('whimsical_bullets', []);
                @endphp
                <section class="midnight-editorial" @if($wImg) style="background-image:url({{ asset($wImg) }});" @endif>
                    <div class="midnight-editorial-content">
                        <h2>{{ $tenant->getSiteContent('whimsical_title', 'Crafted With Care') }}</h2>
                        <p>{{ $bullets[0] ?? 'Every item is baked fresh, using time-honored techniques and the finest ingredients we can find.' }}</p>
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
                <!-- FAQ, re-purposed as a two-column "Why Choose Us" feature list -->
                @php $faqs = $tenant->getSiteContent('faqs', []); @endphp
                <section class="midnight-features">
                    <div class="midnight-features-col">
                        <h3>Why {{ $tenant->name }}</h3>
                        @foreach($faqs as $idx => $faq)
                            @if($idx % 2 === 0)
                                <div class="midnight-feature-item">
                                    <h4>{{ $faq['q'] ?? '' }}</h4>
                                    <p>{{ $faq['a'] ?? '' }}</p>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    <div class="midnight-features-col">
                        <h3>Good to Know</h3>
                        @foreach($faqs as $idx => $faq)
                            @if($idx % 2 === 1)
                                <div class="midnight-feature-item">
                                    <h4>{{ $faq['q'] ?? '' }}</h4>
                                    <p>{{ $faq['a'] ?? '' }}</p>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </section>
            @elseif($secId === 'featured_gallery')
                <!-- Featured Photos Gallery -->
                @php $featuredImages = $tenant->getSiteContent('featured_gallery_images', []); @endphp
                @if(!empty($featuredImages))
                    <section class="featured-gallery-section" style="padding:80px 25px; background:#ffffff; text-align:center;">
                        <h2 class="section-title-script" style="margin-bottom:35px;">{{ $tenant->getSiteContent('featured_gallery_title', 'Featured Creations') }}</h2>
                        <div class="gallery-masonry-grid midnight-grid" style="max-width:1150px; margin:0 auto;">
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
                <section class="cta-video-banner" style="position:relative; background: {{ !empty($ctaBg) ? 'url(' . asset($ctaBg) . ') center/cover no-repeat' : 'linear-gradient(135deg, #0f2438 0%, #14304a 100%)' }}; padding: 70px 25px; text-align: center;">
                    <div class="cta-content" style="max-width:750px; margin:0 auto; position:relative; z-index:2;">
                        <h2 style="font-size: 2.4rem; color: #ffffff; margin-bottom: 10px;">{{ $tenant->getSiteContent('cta_headline', 'Ready to Order?') }}</h2>
                        <p style="font-size: 1.05rem; color: #ffffff; opacity: 0.9; margin-bottom: 24px;">{{ $tenant->getSiteContent('cta_subtext', "Let's bring your order to life.") }}</p>
                        <button onclick="openOrderModal()" class="btn btn-primary" style="padding: 14px 34px; font-size: 1rem; border-color:#ffffff; color:#ffffff;">{{ $tenant->getSiteContent('cta_btn_text', 'Order Now') }}</button>
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
            <span style="font-family:'Playfair Display',serif; font-weight:700; font-size:1.4rem; color:var(--footer-text, #ffffff);"><span class="material-symbols-outlined" style="font-size:1.3rem; vertical-align:-3px;">cake</span> {{ $tenant->name }}</span>
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
