<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('storefront.partials.seo_head', ['page' => 'home'])

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

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
                <span style="font-family:'Inter',sans-serif; font-weight:800; font-size:1.4rem; color:var(--dark-text, #1e1b4b);"><span class="material-symbols-outlined modern-icon" style="font-size:1.3rem; vertical-align:-3px;">cake</span> {{ $tenant->name }}</span>
            @endif
        </a>
        <nav class="nav-links">
            <a href="{{ route('storefront.index') }}">Home</a>
            <a href="{{ route('storefront.about') }}">About</a>
            <a href="{{ route('storefront.menu') }}">Menu</a>
            <a href="{{ route('storefront.gallery') }}">Gallery</a>
            @if(isset($tenant) && $tenant->subdomain === 'blushedcrumbs')
                <a href="{{ route('storefront.policy') }}">Policy</a>
            @endif
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
                <!-- Hero — split photo/copy with a small circular badge overlapping the photo -->
                @php
                    $heroBg = $tenant->getSiteContent('hero_bg_url');
                    $heroIsVideo = !empty($heroBg) && str_ends_with(strtolower($heroBg), '.mp4');
                @endphp
                <section class="modern-hero">
                    <div class="modern-hero-media">
                        @if($heroIsVideo)
                            <video autoplay loop muted playsinline>
                                <source src="{{ asset($heroBg) }}" type="video/mp4">
                            </video>
                        @elseif(!empty($heroBg))
                            <img src="{{ asset($heroBg) }}" alt="{{ $tenant->name }}" fetchpriority="high">
                        @else
                            <div class="modern-hero-media-placeholder"><span class="material-symbols-outlined" style="font-size:5rem; color:#fff;">bakery_dining</span></div>
                        @endif
                        <span class="modern-hero-badge">{{ $tenant->name }}</span>
                    </div>
                    <div class="modern-hero-copy">
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
            @elseif($secId === 'categories')
                <!-- Categories — "Our Shop" grid -->
                <section id="categories" class="categories-section">
                    <h2 class="section-title-script">Our Shop</h2>
                    <p style="text-align:center; max-width:550px; margin:0 auto 34px auto; color:var(--dark-text); opacity:0.75;">Something for all occasions! Shop our range of custom baked goods.</p>
                    <div class="modern-shop-grid">
                        @php
                            $catList = $tenant->getSiteContent('categories', [
                                ['title' => 'Single Tier Cakes'],
                                ['title' => 'Multi Tier Custom Cakes'],
                                ['title' => 'Treats & Sweets By The Dozen']
                            ]);
                            $userImages = $tenant->gallery_images ?? [];
                        @endphp
                        @foreach($catList as $idx => $cat)
                            @php
                                $catImg = !empty($cat['image_url']) ? $cat['image_url'] : (!empty($userImages[$idx]) ? $userImages[$idx] : null);
                                $imgUrl = !empty($catImg) ? asset($catImg) : null;
                            @endphp
                            <div class="modern-shop-card">
                                <div class="modern-shop-card-frame">
                                    @if($imgUrl)
                                        <img src="{{ $imgUrl }}" alt="{{ $cat['title'] ?? ($tenant->name . ' Bakery Category') }}" loading="lazy" decoding="async">
                                    @else
                                        <span class="material-symbols-outlined modern-icon" style="font-size:3rem;">cake</span>
                                    @endif
                                </div>
                                <h3>{{ $cat['title'] ?? 'Category' }}</h3>
                            </div>
                        @endforeach
                    </div>
                    <div style="text-align:center;">
                        <a href="{{ route('storefront.gallery') }}" class="btn btn-primary">Shop</a>
                    </div>
                </section>
            @elseif($secId === 'whimsical')
                <!-- Whimsical, re-purposed as an oval-photo "Custom Cakes" promo banner -->
                <div class="modern-marquee"><div class="modern-marquee-track">
                    @for($i = 0; $i < 8; $i++)<span>Custom Cakes</span>@endfor
                </div></div>
                <section class="whimsical-section modern-cakes-banner">
                    <div class="whimsical-two-column">
                        <div class="whimsical-col-left">
                            @php
                                $wImg = $tenant->getSiteContent('whimsical_image_url');
                                if (empty($wImg) && !empty($tenant->gallery_images[0])) {
                                    $wImg = $tenant->gallery_images[0];
                                }
                            @endphp
                            @if($wImg)
                                <div class="modern-cakes-media">
                                    <img src="{{ asset($wImg) }}" alt="{{ $tenant->name }} Custom Cake" loading="lazy" decoding="async">
                                </div>
                            @else
                                <div class="modern-cakes-media" style="display:flex; align-items:center; justify-content:center; background:linear-gradient(160deg, #4338ca 0%, #ec4899 100%);">
                                    <span class="material-symbols-outlined" style="font-size:4rem; color:#ffffff;">auto_awesome</span>
                                </div>
                            @endif
                        </div>
                        <div class="whimsical-col-right">
                            <h2>{{ $tenant->getSiteContent('whimsical_title', 'Custom Cakes') }}</h2>
                            @php $bullets = $tenant->getSiteContent('whimsical_bullets', []); @endphp
                            @if(!empty($bullets))
                                @foreach($bullets as $bullet)
                                    <p style="color:var(--dark-text); font-size:1rem; line-height:1.7; margin-bottom:10px;">{{ $bullet }}</p>
                                @endforeach
                            @else
                                <p style="color:var(--dark-text); font-size:1rem; line-height:1.7; margin-bottom:18px;">A custom cake is perfect for a big anniversary, a special event, or any time you want something extra special.</p>
                            @endif
                            <button onclick="openOrderModal()" class="btn btn-primary" style="margin-top:8px;">Custom Order</button>
                        </div>
                    </div>
                </section>
            @elseif($secId === 'promo_video')
                <!-- Promo, re-purposed as a text-left/image-right "Macarons"-style teaser -->
                @php
                    $promoVid = $tenant->getSiteContent('promo_video_url');
                    $promoBg = $tenant->getSiteContent('promo_bg_image_url');
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
                    <section class="modern-promo-teaser">
                        <div>
                            <h2>{{ $tenant->getSiteContent('promo_headline', 'Sweet Extras Worth Shopping') }}</h2>
                            <p style="color:var(--dark-text); font-size:1rem; line-height:1.7; margin-bottom:20px; opacity:0.85;">{{ $tenant->getSiteContent('promo_subtext', 'Dazzle your guests with an assortment of treats, ready to display and enjoy.') }}</p>
                            <a href="{{ route('storefront.gallery') }}" class="btn btn-primary">Shop</a>
                        </div>
                        <div class="modern-promo-media">
                            @if(!empty($promoBg))
                                <img src="{{ asset($promoBg) }}" alt="{{ $tenant->name }} Treats" loading="lazy" decoding="async">
                            @else
                                <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:linear-gradient(160deg, #4338ca 0%, #38bdf8 100%);">
                                    <span class="material-symbols-outlined" style="font-size:4rem; color:#ffffff;">redeem</span>
                                </div>
                            @endif
                        </div>
                    </section>
                @endif
            @elseif($secId === 'how_it_works')
                <!-- How It Works -->
                <section class="how-it-works-section" style="padding:70px 25px; text-align:center;">
                    <h2 class="section-title-script" style="margin-bottom:15px;">How Custom Ordering Works</h2>
                    <p style="max-width:600px; margin:0 auto 40px auto; color:var(--dark-text); font-size:1.05rem;">Ordering your dream cake in 3 simple steps</p>
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:25px; max-width:1100px; margin:0 auto;">
                        @php $steps = $tenant->getSiteContent('how_it_works', []); @endphp
                        @foreach($steps as $idx => $step)
                            <div class="category-card-exact" style="padding:25px;">
                                <div class="modern-icon-circle" style="margin:0 auto 14px auto;"><span style="color:#ffffff; font-weight:800; font-family:'Inter',sans-serif;">{{ $idx + 1 }}</span></div>
                                <h3 style="font-size:1.15rem; font-weight:700; margin-bottom:8px; color:var(--dark-text);">{{ $step['title'] ?? '' }}</h3>
                                <p style="font-size:0.9rem; color:#555;">{{ $step['desc'] ?? '' }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>
            @elseif($secId === 'reviews')
                <!-- Customer Reviews -->
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
                <!-- FAQ, re-purposed as the "Custom Orders" accordion section -->
                <div class="modern-marquee"><div class="modern-marquee-track">
                    @for($i = 0; $i < 8; $i++)<span>Custom Orders</span>@endfor
                </div></div>
                <section class="faq-policies-section modern-orders-section" style="padding:70px 25px;">
                    <div class="modern-orders-grid">
                        <div class="modern-orders-media">
                            @php
                                $ordersImg = !empty($tenant->gallery_images[1]) ? $tenant->gallery_images[1] : (!empty($tenant->gallery_images[0]) ? $tenant->gallery_images[0] : null);
                            @endphp
                            @if($ordersImg)
                                <img src="{{ asset($ordersImg) }}" alt="{{ $tenant->name }} Custom Orders" loading="lazy" decoding="async">
                            @else
                                <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:linear-gradient(160deg, #ec4899 0%, #4338ca 100%);">
                                    <span class="material-symbols-outlined" style="font-size:4rem; color:#ffffff;">cake</span>
                                </div>
                            @endif
                        </div>
                        <div>
                            <h2 class="section-title-script" style="margin-bottom:15px;">Custom Orders</h2>
                            <p style="color:var(--dark-text); margin-bottom:20px; opacity:0.8;">Looking for something unique, beautiful and designed especially for your event?</p>
                            <div class="modern-accordion">
                                @php $faqs = $tenant->getSiteContent('faqs', []); @endphp
                                @foreach($faqs as $faq)
                                    <details class="modern-accordion-item">
                                        <summary>{{ $faq['q'] ?? '' }}</summary>
                                        <p>{{ $faq['a'] ?? '' }}</p>
                                    </details>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>
            @elseif($secId === 'featured_gallery')
                <!-- Featured Photos Gallery -->
                @php $featuredImages = $tenant->getSiteContent('featured_gallery_images', []); @endphp
                @if(!empty($featuredImages))
                    <section class="featured-gallery-section" style="padding:70px 25px; text-align:center;">
                        <h2 class="section-title-script" style="margin-bottom:35px;">{{ $tenant->getSiteContent('featured_gallery_title', 'Featured Creations') }}</h2>
                        <div class="gallery-masonry-grid modern-grid" style="max-width:1150px; margin:0 auto;">
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
                    $ctaBg = $tenant->getSiteContent('cta_bg_image_url');
                @endphp
                <section class="cta-video-banner" style="position:relative; background: {{ !empty($ctaBg) ? 'linear-gradient(135deg, rgba(30,27,75,0.82) 0%, rgba(236,72,153,0.6) 100%), url(' . asset($ctaBg) . ') center/cover no-repeat' : 'linear-gradient(135deg, #4338ca 0%, #ec4899 100%)' }}; padding: 65px 25px; text-align: center;">
                    <div class="cta-content" style="max-width:750px; margin:0 auto; position:relative; z-index:2;">
                        <h2 style="font-size: 2.6rem; color: #ffffff; margin-bottom: 10px;">{{ $tenant->getSiteContent('cta_headline', 'Ready For Your Perfect Cake?') }}</h2>
                        <p style="font-size: 1.1rem; color: #ffffff; opacity: 0.95; margin-bottom: 24px;">{{ $tenant->getSiteContent('cta_subtext', 'Order your plan or custom order now') }}</p>
                        <button onclick="openOrderModal()" class="btn btn-primary" style="padding: 14px 34px; font-size: 1.1rem;">{{ $tenant->getSiteContent('cta_btn_text', 'Order Now') }}</button>
                    </div>
                </section>
            @endif

            @if($secId === 'about')
                <!-- Homepage "Meet the Baker" teaser — its own toggleable Page
                     Builder section, drawn from the same about_bio/about_title
                     data used on the About page -->
                <section style="padding:70px 25px;">
                    <div class="modern-meet-baker">
                        <div class="modern-meet-baker-media">
                            @php
                                $founderImg = !empty($tenant->logo_path) ? asset($tenant->logo_path) : (!empty($tenant->gallery_images[0]) ? asset($tenant->gallery_images[0]) : null);
                            @endphp
                            @if($founderImg)
                                <img src="{{ $founderImg }}" alt="{{ $tenant->name }}" loading="lazy" decoding="async">
                            @else
                                <div class="modern-meet-baker-placeholder">
                                    <span class="material-symbols-outlined" style="font-size:3.5rem; color:#ffffff;">cake</span>
                                </div>
                            @endif
                        </div>
                        <div>
                            <h2>Meet {{ Str::of($tenant->name ?? 'Us')->words(1, '') }}</h2>
                            <p>{{ $tenant->getSiteContent('about_bio', 'The baker behind the scenes at ' . ($tenant->name ?? 'our bakehouse') . ' specializes in custom designed cakes and treats for weddings, birthdays, showers, and every other occasion.') }}</p>
                            <a href="{{ route('storefront.about') }}" class="btn btn-primary">About</a>
                        </div>
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
            <span style="font-family:'Inter',sans-serif; font-weight:800; font-size:1.5rem; color:var(--footer-text, #ffffff);"><span class="material-symbols-outlined" style="font-size:1.4rem; vertical-align:-3px;">cake</span> {{ $tenant->name }}</span>
        @endif
    </div>
    <div class="footer-nav">
        <a href="{{ route('storefront.index') }}" class="footer-link">Home</a>
        <a href="{{ route('storefront.about') }}" class="footer-link">About</a>
        <a href="{{ route('storefront.menu') }}" class="footer-link">Menu</a>
        <a href="{{ route('storefront.gallery') }}" class="footer-link">Gallery</a>
        @if(isset($tenant) && $tenant->subdomain === 'blushedcrumbs')
            <a href="{{ route('storefront.policy') }}" class="footer-link">Policy</a>
        @endif
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
