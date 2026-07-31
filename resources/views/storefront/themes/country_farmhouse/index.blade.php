<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('storefront.partials.seo_head', ['page' => 'home'])

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Oswald:wght@400;500;600;700&family=Open+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
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
                <span style="font-family:'Oswald',sans-serif; font-weight:700; text-transform:uppercase; letter-spacing:1px; font-size:1.2rem;"><span class="material-symbols-outlined farmhouse-icon" style="font-size:1.2rem; vertical-align:-3px;">lunch_dining</span> {{ $tenant->name }}</span>
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
        $heroEnabled = !empty($sections['hero']['enabled']);
    @endphp

    @if($heroEnabled)
        @php
            $heroBg = $tenant->getSiteContent('hero_bg_url');
            $heroIsVideo = !empty($heroBg) && str_ends_with(strtolower($heroBg), '.mp4');
        @endphp
        <!-- Hero — full-bleed photo, dark scrim, bold condensed uppercase type -->
        <section class="farmhouse-hero">
            <div class="farmhouse-hero-media">
                @if($heroIsVideo)
                    <video autoplay loop muted playsinline>
                        <source src="{{ asset($heroBg) }}" type="video/mp4">
                    </video>
                @elseif(!empty($heroBg))
                    <img src="{{ asset($heroBg) }}" alt="{{ $tenant->name }}">
                @else
                    <div style="width:100%; height:100%; background:linear-gradient(160deg, #2b2117 0%, #c1810a 100%);"></div>
                @endif
            </div>
            <div class="farmhouse-hero-copy">
                <span class="subheading">{{ $tenant->getSiteContent('hero_subheading', 'Welcome to ' . ($tenant->name ?? 'our bakehouse')) }}</span>
                <h1>{{ $tenant->getSiteContent('hero_headline', $tenant->name ?? 'Artisanal Bakehouse') }}</h1>
                <div class="hero-buttons">
                    <button onclick="openOrderModal()" class="btn btn-primary">{{ $tenant->getSiteContent('hero_cta_primary', 'Custom Order') }}</button>
                    <a href="{{ route('storefront.gallery') }}" class="btn btn-secondary">Our Treats</a>
                </div>
                <div class="farmhouse-scroll-cue">
                    <span class="material-symbols-outlined">expand_more</span>
                    Scroll Down
                </div>
            </div>
        </section>
    @endif

    <!-- About teaser — always shown, uses the same about_bio/about_title
         fields the About page reads (this reference treats About as a
         homepage section, not a separate nav destination) -->
    <section class="farmhouse-about">
        <div class="farmhouse-about-media">
            @php
                $aboutImg = !empty($tenant->gallery_images[0]) ? asset($tenant->gallery_images[0]) : (!empty($tenant->logo_path) ? asset($tenant->logo_path) : null);
            @endphp
            @if($aboutImg)
                <img src="{{ $aboutImg }}" alt="{{ $tenant->name }}">
            @else
                <div class="farmhouse-about-placeholder">
                    <span class="material-symbols-outlined" style="font-size:3.5rem; color:#ffffff;">storefront</span>
                </div>
            @endif
        </div>
        <div class="farmhouse-about-copy">
            <h2>{{ $tenant->getSiteContent('about_title', 'About Us') }}</h2>
            <p>{{ $tenant->getSiteContent('about_bio', 'Welcome to ' . ($tenant->name ?? 'our bakehouse') . '! We specialize in artisanal baked goods, made fresh daily with real ingredients and a whole lot of care.') }}</p>
            <a href="{{ route('storefront.about') }}" class="btn btn-primary">Read Our Story</a>
        </div>
    </section>

    @if(!empty($sections['categories']['enabled']))
        <!-- Categories — quick-nav "menu index": photo + stacked tabs, list alongside -->
        <section class="farmhouse-menu-index">
            @php
                $catList = $tenant->getSiteContent('categories', [
                    ['title' => 'Cakes'],
                    ['title' => 'Cupcakes'],
                    ['title' => 'Treats'],
                ]);
                // !empty(), not ?? - an unset image field is stored as an
                // empty string, not null, so ?? was stopping at the first
                // option every time and never actually falling through.
                $catImg = !empty($catList[0]['image_url'])
                    ? $catList[0]['image_url']
                    : (!empty($tenant->gallery_images[0])
                        ? $tenant->gallery_images[0]
                        : $tenant->getSiteContent('hero_bg_url'));
            @endphp
            <div class="farmhouse-menu-index-media">
                @if($catImg)
                    <img src="{{ asset($catImg) }}" alt="{{ $tenant->name }} Menu">
                @else
                    <div class="farmhouse-menu-index-media-placeholder">
                        <span class="material-symbols-outlined" style="font-size:4rem; color:#ffffff;">restaurant</span>
                    </div>
                @endif
                <div class="farmhouse-tabs">
                    @foreach(array_slice($catList, 0, 3) as $idx => $cat)
                        <span class="farmhouse-tab {{ $idx === 0 ? 'is-accent' : '' }}">{{ $cat['title'] ?? 'Menu' }}</span>
                    @endforeach
                </div>
            </div>
            <div class="farmhouse-menu-index-list">
                <h2>Our Menu</h2>
                @foreach($catList as $cat)
                    <div class="farmhouse-menu-item">
                        <h4>{{ $cat['title'] ?? 'Category' }}</h4>
                        @if(!empty($cat['desc']))
                            <p>{{ $cat['desc'] }}</p>
                        @endif
                    </div>
                @endforeach
                <a href="{{ route('storefront.menu') }}" class="btn" style="background:#2b2117; color:#ffffff; align-self:flex-start;">View Full Menu</a>
            </div>
        </section>
    @endif

    <!-- Contact + Hours + Map — always shown, built from real tenant fields -->
    <section class="farmhouse-contact">
        <div class="farmhouse-contact-grid">
            <div class="farmhouse-contact-info">
                <h2 style="margin-bottom:18px;">Contact Us</h2>
                @php $location = $tenant->getSiteContent('contact_location'); @endphp
                @if(!empty($location))
                    <p>{{ $location }}</p>
                @endif
                @if(!empty($tenant->email))
                    <p>{{ $tenant->email }}</p>
                @endif
                @if(!empty($tenant->phone))
                    <span class="farmhouse-contact-phone">{{ $tenant->phone }}</span>
                @endif
                <div class="farmhouse-social-row">
                    @if(!empty($tenant->getSiteContent('contact_facebook')))
                        <a href="{{ $tenant->getSiteContent('contact_facebook') }}" target="_blank" class="farmhouse-social-icon" aria-label="Facebook">
                            <span class="material-symbols-outlined" style="font-size:1.2rem;">thumb_up</span>
                        </a>
                    @endif
                    @if(!empty($tenant->getSiteContent('contact_instagram')))
                        <a href="{{ $tenant->getSiteContent('contact_instagram') }}" target="_blank" class="farmhouse-social-icon" aria-label="Instagram">
                            <span class="material-symbols-outlined" style="font-size:1.2rem;">photo_camera</span>
                        </a>
                    @endif
                    <a href="#" onclick="openOrderModal()" class="farmhouse-social-icon" aria-label="Order Now">
                        <span class="material-symbols-outlined" style="font-size:1.2rem;">shopping_bag</span>
                    </a>
                </div>
                <div class="farmhouse-hours-block">
                    <h3>Hours</h3>
                    <p>{{ $tenant->getSiteContent('contact_hours', 'Mon-Sat: 8:00 AM - 6:00 PM | Sun: Closed') }}</p>
                </div>
            </div>
            <div class="farmhouse-map-panel">
                @if(!empty($location))
                    <iframe src="https://www.google.com/maps?q={{ urlencode($location) }}&output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="{{ $tenant->name }} Location"></iframe>
                @else
                    <div class="farmhouse-map-placeholder">
                        <span class="material-symbols-outlined" style="font-size:3rem; color:#ffffff;">location_on</span>
                        <p>Add a location in your dashboard to show a map here.</p>
                    </div>
                @endif
            </div>
        </div>
    </section>

    @if(!empty($sections['cta_banner']['enabled']))
        <!-- Closing CTA banner -->
        @php
            $ctaBg = $tenant->getSiteContent('cta_bg_image_url');
        @endphp
        <section class="cta-video-banner" style="position:relative; background: {{ !empty($ctaBg) ? 'linear-gradient(135deg, rgba(43,33,23,0.82) 0%, rgba(193,129,10,0.55) 100%), url(' . asset($ctaBg) . ') center/cover no-repeat' : 'linear-gradient(135deg, #2b2117 0%, #c1810a 100%)' }}; padding: 70px 25px; text-align: center;">
            <div class="cta-content" style="max-width:750px; margin:0 auto; position:relative; z-index:2;">
                <h2 style="font-size: 2.6rem; color: #ffffff; margin-bottom: 10px;">{{ $tenant->getSiteContent('cta_headline', 'Ready to Order?') }}</h2>
                <p style="font-size: 1.1rem; color: #ffffff; opacity: 0.95; margin-bottom: 24px;">{{ $tenant->getSiteContent('cta_subtext', "Let's bring your order to life.") }}</p>
                <button onclick="openOrderModal()" class="btn btn-primary" style="padding: 14px 34px; font-size: 1.1rem;">{{ $tenant->getSiteContent('cta_btn_text', 'Order Now') }}</button>
            </div>
        </section>
    @endif
</div>

@include('storefront.partials.order_modal')

<!-- LIGHTBOX MODAL FOR FEATURED GALLERY PREVIEWS -->
<div id="lightbox-modal" class="lightbox-modal" style="display:none;" onclick="closeLightbox()">
    <div class="lightbox-content" onclick="event.stopPropagation()">
        <button class="modal-close-btn" onclick="closeLightbox()"><span class="material-symbols-outlined" style="font-size:1.2rem; vertical-align:-2px;">close</span></button>
        <img id="lightbox-img" src="" alt="Gallery Preview">
        <div id="lightbox-caption" class="lightbox-caption"></div>
    </div>
</div>

<footer class="site-footer">
    <div class="footer-logo">
        @if(!empty($tenant->logo_path))
            <img src="{{ asset($tenant->logo_path) }}" alt="{{ $tenant->name }} Logo" style="max-height:60px; width:auto; object-fit:contain;">
        @else
            <span style="font-family:'Oswald',sans-serif; font-weight:700; text-transform:uppercase; letter-spacing:1px; font-size:1.3rem; color:var(--footer-text, #ffffff);"><span class="material-symbols-outlined" style="font-size:1.3rem; vertical-align:-3px;">lunch_dining</span> {{ $tenant->name }}</span>
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
