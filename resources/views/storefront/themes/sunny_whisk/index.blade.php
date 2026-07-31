<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('storefront.partials.seo_head', ['page' => 'home'])

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700;800&family=Oswald:wght@400;500;600;700&family=Open+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
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
                <span style="font-family:'Poppins',sans-serif; font-weight:800; font-size:1.4rem; color:var(--sw-ink, #14213d);"><span class="material-symbols-outlined sw-icon" style="font-size:1.3rem; vertical-align:-3px;">cake</span> {{ $tenant->name }}</span>
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
                <!-- Hero — sky-blue diagonal band behind an offset white photo card -->
                @php
                    $heroBg = $tenant->getSiteContent('hero_bg_url');
                    $heroIsVideo = !empty($heroBg) && str_ends_with(strtolower($heroBg), '.mp4');
                @endphp
                <section class="sw-hero">
                    <div class="sw-hero-band" aria-hidden="true"></div>
                    <div class="sw-hero-inner">
                        <div class="sw-hero-copy">
                            <span class="subheading">{{ $tenant->getSiteContent('hero_subheading', 'Welcome to ' . ($tenant->name ?? 'our bakehouse')) }}</span>
                            <h1>{{ $tenant->getSiteContent('hero_headline', $tenant->name ?? 'Artisanal Bakehouse') }}</h1>
                            <div class="hero-buttons">
                                <button onclick="openOrderModal()" class="btn btn-primary">{{ $tenant->getSiteContent('hero_cta_primary', 'Order Now') }}</button>
                                <a href="{{ route('storefront.gallery') }}" class="btn btn-secondary">{{ $tenant->getSiteContent('hero_cta_secondary', 'Our Treats') }}</a>
                            </div>
                        </div>
                        <div class="sw-hero-media">
                            @if($heroIsVideo)
                                <video autoplay loop muted playsinline>
                                    <source src="{{ asset($heroBg) }}" type="video/mp4">
                                </video>
                            @elseif(!empty($heroBg))
                                <img src="{{ asset($heroBg) }}" alt="{{ $tenant->name }}" fetchpriority="high">
                            @else
                                <div class="sw-hero-media-placeholder"><span class="material-symbols-outlined" style="font-size:5rem;">bakery_dining</span></div>
                            @endif
                        </div>
                    </div>
                </section>
            @elseif($secId === 'about')
                <!-- About / Our Story — "How It Started" two-column bio on a floating white card -->
                <section class="sw-section sw-band-sky">
                    <div class="sw-about-card">
                        <div class="sw-about-copy">
                            <span class="sw-kicker">How It Started</span>
                            <h2>{{ $tenant->getSiteContent('about_title', 'About Our Bakery') }}</h2>
                            <p>{{ $tenant->getSiteContent('about_bio', 'Welcome to ' . ($tenant->name ?? 'our bakehouse') . '! We specialize in custom artisanal cakes, gourmet treats, and unforgettable dessert experiences crafted with premium ingredients and passion.') }}</p>
                            <a href="{{ route('storefront.about') }}" class="sw-text-link">Read our full story →</a>
                        </div>
                        <div class="sw-about-media">
                            @php
                                $aboutImg = !empty($tenant->gallery_images[0]) ? asset($tenant->gallery_images[0]) : (!empty($tenant->logo_path) ? asset($tenant->logo_path) : null);
                            @endphp
                            @if($aboutImg)
                                <img src="{{ $aboutImg }}" alt="{{ $tenant->name }}" loading="lazy" decoding="async">
                            @else
                                <div class="sw-about-media-placeholder"><span class="material-symbols-outlined" style="font-size:3rem;">cake</span></div>
                            @endif
                        </div>
                    </div>
                </section>
            @elseif($secId === 'highlights')
                <!-- Highlights — repurposed as "The Process" bullet row with an Order Now button -->
                <section class="sw-section sw-band-white">
                    <div class="sw-process">
                        <span class="sw-kicker">The Process</span>
                        <div class="sw-process-list">
                            @php $highlights = $tenant->getSiteContent('highlights', []); @endphp
                            @foreach($highlights as $hl)
                                <div class="sw-process-row">
                                    <span class="sw-process-emoji">{{ $hl['icon'] ?? '🎂' }}</span>
                                    <div>
                                        <h4>{{ $hl['title'] ?? '' }}</h4>
                                        <p>{{ $hl['desc'] ?? '' }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button onclick="openOrderModal()" class="btn btn-primary">Shop Now</button>
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
                    <section class="video-promo-banner sw-promo-banner">
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
                    <section class="video-promo-banner sw-promo-banner" style="position:relative; background: {{ !empty($promoBg) ? 'linear-gradient(135deg, rgba(20,33,61,0.75) 0%, rgba(255,79,158,0.55) 100%), url(' . asset($promoBg) . ') center/cover no-repeat' : 'linear-gradient(135deg, #14213d 0%, #ff4f9e 100%)' }}; padding: 75px 25px; text-align: center;">
                        <div class="video-overlay-content" style="position:relative; z-index:2; max-width:720px; margin:0 auto;">
                            <span class="material-symbols-outlined" style="font-size:2.2rem; display:block; margin-bottom:10px; color:#ffffff;">redeem</span>
                            <h2 style="font-size:2.4rem; font-weight:800; color:#ffffff; margin-bottom:12px;">{{ $tenant->getSiteContent('promo_headline', 'Special Custom Bakery Orders!') }}</h2>
                            <p style="font-size:1.05rem; color:rgba(255,255,255,0.9); margin-bottom:24px;">{{ $tenant->getSiteContent('promo_subtext', 'Order online directly from our kitchen for your upcoming celebration.') }}</p>
                            <button onclick="openOrderModal()" class="btn btn-primary" style="padding:13px 34px; font-size:1.05rem; border-radius:30px; box-shadow:0 4px 15px rgba(0,0,0,0.2);">Order Now</button>
                        </div>
                    </section>
                @endif
            @elseif($secId === 'categories')
                <!-- Categories -->
                <section id="categories" class="sw-section sw-band-pink">
                    <h2 class="sw-section-title">Our Categories</h2>
                    <div class="sw-shelf-row">
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
                            <div class="sw-shelf-card">
                                <div class="sw-shelf-frame">
                                    @if($imgUrl)
                                        <img src="{{ $imgUrl }}" alt="{{ $cat['title'] ?? ($tenant->name . ' Bakery Category') }}" loading="lazy" decoding="async">
                                    @else
                                        <span class="material-symbols-outlined sw-icon">cake</span>
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
                <section class="whimsical-section sw-whimsical">
                    <div class="whimsical-two-column">
                        <div class="whimsical-col-left">
                            @php
                                $wImg = $tenant->getSiteContent('whimsical_image_url');
                                if (empty($wImg) && !empty($tenant->gallery_images[0])) {
                                    $wImg = $tenant->gallery_images[0];
                                }
                            @endphp
                            @if($wImg)
                                <div class="sw-whimsical-img">
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
                <!-- How It Works — numbered steps on a white band -->
                <section class="how-it-works-section sw-section sw-band-white">
                    <h2 class="sw-section-title" style="text-align:center;">How To Order</h2>
                    <div class="sw-steps-list">
                        @php $steps = $tenant->getSiteContent('how_it_works', []); @endphp
                        @foreach($steps as $idx => $step)
                            <div class="sw-step-row">
                                <span class="sw-step-num"></span>
                                <div class="sw-step-copy">
                                    <h3>{{ $step['title'] ?? '' }}</h3>
                                    <p>{{ $step['desc'] ?? '' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @elseif($secId === 'reviews')
                <!-- Reviews -->
                <section id="reviews" class="reviews-section sw-section sw-band-sky">
                    <h2 class="sw-section-title">Sweet Words from Our Customers</h2>
                    <div class="sw-review-row">
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
                            <div class="sw-review-card">
                                <p>"{{ is_array($rev) ? ($rev['quote'] ?? $rev['text'] ?? '') : ($rev->review_text ?? '') }}"</p>
                                <h4>{{ is_array($rev) ? ($rev['name'] ?? '') : ($rev->client_name ?? '') }}</h4>
                            </div>
                        @endforeach
                    </div>
                </section>
            @elseif($secId === 'faq')
                <!-- FAQ & Bakery Policies -->
                <section class="faq-policies-section sw-section sw-band-white">
                    <h2 class="sw-section-title" style="text-align:center; margin-bottom:15px;">Frequently Asked Questions</h2>
                    <div style="max-width:850px; margin:0 auto; text-align:left; display:flex; flex-direction:column; gap:18px;">
                        @php $faqs = $tenant->getSiteContent('faqs', []); @endphp
                        @foreach($faqs as $faq)
                            <div class="sw-faq-card">
                                <h4>{{ $faq['q'] ?? '' }}</h4>
                                <p>{{ $faq['a'] ?? '' }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>
            @elseif($secId === 'featured_gallery')
                <!-- Featured Photos Gallery — the big mustard diagonal photo grid -->
                @php $featuredImages = $tenant->getSiteContent('featured_gallery_images', []); @endphp
                @if(!empty($featuredImages))
                    <section class="featured-gallery-section sw-section sw-band-mustard">
                        <h2 class="sw-section-title" style="text-align:center;">{{ $tenant->getSiteContent('featured_gallery_title', 'Featured Creations') }}</h2>
                        <div class="gallery-masonry-grid sw-featured-grid" style="max-width:1150px; margin:0 auto;">
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
                <!-- Footer Call to Action Banner — bubblegum pink -->
                @php
                    $ctaBg = $tenant->getSiteContent('cta_banner_url') ?: $tenant->getSiteContent('cta_bg_image_url');
                @endphp
                <section class="cta-video-banner sw-cta-banner" style="position:relative; background: {{ !empty($ctaBg) ? 'linear-gradient(135deg, rgba(20,33,61,0.65) 0%, rgba(255,182,213,0.55) 100%), url(' . asset($ctaBg) . ') center/cover no-repeat' : 'linear-gradient(135deg, #ff4f9e 0%, #ffb6d5 100%)' }}; padding: 70px 25px; text-align: center;">
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
            <span style="font-family:'Poppins',sans-serif; font-weight:800; font-size:1.5rem; color:var(--footer-text, #ffffff);"><span class="material-symbols-outlined" style="font-size:1.4rem; vertical-align:-3px;">cake</span> {{ $tenant->name }}</span>
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
    <p class="copyright-text">Copyright &copy; 2026 {{ $tenant->name ?? 'Blushed Crumbs Bakehouse' }} | <a href="{{ route('legal.index') }}" class="footer-link">Legal Hub</a> &middot; <a href="{{ route('storefront.privacy') }}" class="footer-link">Privacy</a> &middot; <a href="{{ route('storefront.terms') }}" class="footer-link">Terms</a> | Powered by <a href="https://doughmain.pro" target="_blank" class="footer-link footer-brand-link">Doughmain.pro</a></p>
</footer>

<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
