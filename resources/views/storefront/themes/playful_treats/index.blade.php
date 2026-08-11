<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('storefront.partials.seo_head', ['page' => 'home'])

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&family=Oswald:wght@400;500;600;700&family=Open+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
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
                <span style="font-family:'Outfit',sans-serif; font-weight:700; font-size:1.4rem; color:var(--dark-text, #1A1A2E);"><span class="material-symbols-outlined playful-icon" style="font-size:1.3rem; vertical-align:-3px;">cake</span> {{ $tenant->name }}</span>
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
                <!-- Hero — blob-framed photo + copy, floating decorative shapes -->
                @php
                    $heroBg = $tenant->getSiteContent('hero_bg_url');
                    $heroIsVideo = !empty($heroBg) && str_ends_with(strtolower($heroBg), '.mp4');
                @endphp
                <section class="playful-hero">
                    <span class="playful-hero-blob playful-hero-blob-1" aria-hidden="true"></span>
                    <span class="playful-hero-blob playful-hero-blob-2" aria-hidden="true"></span>
                    <span class="playful-hero-blob playful-hero-blob-3" aria-hidden="true"></span>
                    <div class="playful-hero-media">
                        @if($heroIsVideo)
                            <video autoplay loop muted playsinline>
                                <source src="{{ asset($heroBg) }}" type="video/mp4">
                            </video>
                        @elseif(!empty($heroBg))
                            <img src="{{ asset($heroBg) }}" alt="{{ $tenant->name }}" fetchpriority="high">
                        @else
                            <div class="playful-hero-media-placeholder"><span class="material-symbols-outlined" style="font-size:5rem; color:#fff;">bakery_dining</span></div>
                        @endif
                    </div>
                    <div class="playful-hero-copy">
                        <span class="subheading">{{ $tenant->getSiteContent('hero_subheading', 'Welcome to ' . ($tenant->name ?? 'our bakehouse')) }}</span>
                        <h1>{{ $tenant->getSiteContent('hero_headline', $tenant->name ?? 'Artisanal Bakehouse') }}</h1>
                        <div class="hero-buttons">
                            <button onclick="openOrderModal()" class="btn btn-primary">{{ $tenant->getSiteContent('hero_cta_primary', 'Custom Order') }}</button>
                            <a href="{{ route('storefront.gallery') }}" class="btn btn-secondary">{{ $tenant->getSiteContent('hero_cta_secondary', 'Our Treats') }}</a>
                        </div>
                    </div>
                </section>
            @elseif($secId === 'about')
                <!-- About Teaser -->
                <section class="about-teaser-section" style="padding:80px 25px; background:var(--theme-section-bg, var(--pink-bg, #fff1f5)); text-align:center;">
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
                <!-- Highlights re-purposed as a floating "voted best" badge cluster -->
                <section class="playful-badges-row">
                    @php $highlights = $tenant->getSiteContent('highlights', []); @endphp
                    @foreach($highlights as $hl)
                        <div class="playful-badge-circle">
                            <span class="playful-badge-emoji">{{ $hl['icon'] ?? '🏆' }}</span>
                            <h4>{{ $hl['title'] ?? '' }}</h4>
                            <p>{{ $hl['desc'] ?? '' }}</p>
                        </div>
                    @endforeach
                </section>
            @elseif($secId === 'promo_video')
                <!-- Video Background Promo Banner -->
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
                            <h2>{{ $tenant->getSiteContent('promo_headline', 'Special Custom Bakery Orders!') }}</h2>
                            <p>{{ $tenant->getSiteContent('promo_subtext', 'Order online directly from our kitchen for your upcoming celebration.') }}</p>
                            <button onclick="openOrderModal()" class="btn btn-dark">Order Now</button>
                        </div>
                    </section>
                @else
                    <section class="video-promo-banner" style="position:relative; background: {{ !empty($promoBg) ? 'linear-gradient(135deg, rgba(26,26,46,0.82) 0%, rgba(255,79,160,0.65) 100%), url(' . asset($promoBg) . ') center/cover no-repeat' : 'linear-gradient(135deg, #1A1A2E 0%, #4a1531 100%)' }}; padding: 75px 25px; text-align: center;">
                        <div class="video-overlay-content" style="position:relative; z-index:2; max-width:720px; margin:0 auto;">
                            <span class="material-symbols-outlined" style="font-size:2.2rem; display:block; margin-bottom:10px; color:#ffffff;">redeem</span>
                            <h2 style="font-size:2.4rem; font-weight:800; color:#ffffff; margin-bottom:12px;">{{ $tenant->getSiteContent('promo_headline', 'Special Custom Bakery Orders!') }}</h2>
                            <p style="font-size:1.05rem; color:rgba(255,255,255,0.9); margin-bottom:24px;">{{ $tenant->getSiteContent('promo_subtext', 'Order online directly from our kitchen for your upcoming celebration.') }}</p>
                            <button onclick="openOrderModal()" class="btn btn-primary" style="padding:13px 34px; font-size:1.05rem; border-radius:30px; box-shadow:0 4px 15px rgba(0,0,0,0.2);">Order Now</button>
                        </div>
                    </section>
                @endif
            @elseif($secId === 'categories')
                <!-- Categories — shelf row of blob-framed cards -->
                <section id="categories" class="categories-section">
                    <h2 class="section-title-script">Our Categories</h2>
                    <div class="playful-shelf-row">
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
                            <div class="playful-shelf-card">
                                <div class="playful-shelf-frame">
                                    @if($imgUrl)
                                        <img src="{{ $imgUrl }}" alt="{{ $cat['title'] ?? ($tenant->name . ' Bakery Category') }}" loading="lazy" decoding="async">
                                    @else
                                        <span class="material-symbols-outlined playful-icon">cake</span>
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
                                <div class="playful-whimsical-img">
                                    <img src="{{ asset($wImg) }}" alt="{{ $tenant->name }} Whimsical Creation" loading="lazy" decoding="async">
                                </div>
                            @else
                                <div style="text-align:center; padding:40px 20px; background:rgba(255,255,255,0.15); border-radius:24px;">
                                    <span class="material-symbols-outlined" style="font-size:4rem; display:block; margin-bottom:12px; color:#ffffff;">auto_awesome</span>
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
                <!-- How It Works — one bold blob callout next to numbered steps -->
                <section class="how-it-works-section playful-order-section">
                    <div class="playful-order-callout">
                        <span class="material-symbols-outlined">local_shipping</span>
                        <h2>{{ $tenant->getSiteContent('how_it_works_title', 'How To Order') }}</h2>
                        <p>{{ $tenant->getSiteContent('how_it_works_subtitle', 'Learn more about our ordering process') }}</p>
                    </div>
                    <div class="playful-steps-list">
                        @php $steps = $tenant->getSiteContent('how_it_works', []); @endphp
                        @foreach($steps as $idx => $step)
                            <div class="playful-step-row">
                                <span class="playful-step-num"></span>
                                <div class="playful-step-copy">
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
                <!-- Reviews — swipeable carousel -->
                <section id="reviews" class="reviews-section">
                    <h2 class="section-title-script">Sweet Words from Our Customers</h2>
                    <div class="playful-carousel">
                        <button type="button" class="playful-carousel-btn" aria-label="Previous reviews" onclick="document.getElementById('playful-reviews-track').scrollBy({left:-320, behavior:'smooth'})">‹</button>
                        <div class="playful-carousel-track" id="playful-reviews-track">
                            @foreach($displayReviews as $rev)
                                <div class="playful-review-card">
                                    <p>"{{ is_array($rev) ? ($rev['quote'] ?? $rev['text'] ?? '') : ($rev->review_text ?? '') }}"</p>
                                    <h4>{{ is_array($rev) ? ($rev['name'] ?? '') : ($rev->client_name ?? '') }}</h4>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="playful-carousel-btn" aria-label="Next reviews" onclick="document.getElementById('playful-reviews-track').scrollBy({left:320, behavior:'smooth'})">›</button>
                    </div>
                </section>
                @endif
            @elseif($secId === 'faq')
                <!-- FAQ & Bakery Policies -->
                <section class="faq-policies-section" style="padding:70px 25px; background:#ffffff; text-align:center;">
                    <h2 class="section-title-script" style="margin-bottom:15px;">Frequently Asked Questions</h2>
                    <div style="max-width:850px; margin:0 auto; text-align:left; display:flex; flex-direction:column; gap:18px;">
                        @php $faqs = $tenant->getSiteContent('faqs', []); @endphp
                        @foreach($faqs as $faq)
                            <div class="playful-faq-card">
                                <h4 style="font-size:1.1rem; font-weight:700; color:var(--dark-text); margin-bottom:6px;">{{ $faq['q'] ?? '' }}</h4>
                                <p style="font-size:0.92rem; color:#555; line-height:1.6;">{{ $faq['a'] ?? '' }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>
            @elseif($secId === 'featured_gallery')
                <!-- Featured Photos Gallery -->
                @php $featuredImages = $tenant->getSiteContent('featured_gallery_images', []); @endphp
                @if(!empty($featuredImages))
                    <section class="featured-gallery-section" style="padding:70px 25px; background:#ffffff; text-align:center;">
                        <h2 class="section-title-script" style="margin-bottom:35px;">{{ $tenant->getSiteContent('featured_gallery_title', 'Featured Creations') }}</h2>
                        <div class="gallery-masonry-grid playful-instagrid" style="max-width:1150px; margin:0 auto;">
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
                <!-- Footer Call to Action Banner -->
                @php
                    $ctaBg = $tenant->getSiteContent('cta_banner_url') ?: $tenant->getSiteContent('cta_bg_image_url');
                @endphp
                <section class="cta-video-banner playful-cta-banner" style="position:relative; background: {{ !empty($ctaBg) ? 'linear-gradient(135deg, rgba(26,26,46,0.75) 0%, rgba(255,79,160,0.6) 100%), url(' . asset($ctaBg) . ') center/cover no-repeat' : 'linear-gradient(135deg, #1AD1CB 0%, #FF4FA0 100%)' }}; padding: 70px 25px; text-align: center;">
                    <div class="cta-content" style="max-width:750px; margin:0 auto; position:relative; z-index:2;">
                        <h2 style="font-size: 2.6rem; color: #ffffff; margin-bottom: 10px;">{{ $tenant->getSiteContent('cta_headline', 'Ready For Your Perfect Cake?') }}</h2>
                        <p style="font-size: 1.1rem; color: #ffffff; opacity: 0.95; margin-bottom: 24px;">{{ $tenant->getSiteContent('cta_subtext', 'Order your plan or custom order now') }}</p>
                        <button onclick="openOrderModal()" class="btn btn-primary" style="padding: 14px 34px; font-size: 1.1rem; border-radius: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.15);">{{ $tenant->getSiteContent('cta_btn_text', 'Order Now') }}</button>
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
            <span style="font-family:'Outfit',sans-serif; font-weight:700; font-size:1.5rem; color:var(--footer-text, #ffffff);"><span class="material-symbols-outlined" style="font-size:1.4rem; vertical-align:-3px;">cake</span> {{ $tenant->name }}</span>
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
