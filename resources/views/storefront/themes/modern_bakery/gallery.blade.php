<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('storefront.partials.seo_head', ['page' => 'gallery'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">
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
                <span style="font-family:'Inter',sans-serif; font-weight:800; font-size:1.4rem; color:var(--dark-text, #1e1b4b);"><span class="material-symbols-outlined modern-icon" style="font-size:1.3rem; vertical-align:-3px;">cake</span> {{ $tenant->name }}</span>
            @endif
        </a>
        <nav class="nav-links">
            <a href="{{ route('storefront.index') }}">Home</a>
            <a href="{{ route('storefront.about') }}">About</a>
            <a href="{{ route('storefront.menu') }}">Menu</a>
            <a href="{{ route('storefront.gallery') }}" class="active">Gallery</a>
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

<div id="gallery-page-view">
    <section class="modern-page-hero">
        <span class="modern-page-hero-kicker">Our Shop</span>
        <h1 class="modern-page-hero-title">{{ $tenant->getSiteContent('gallery_hero_title', 'Gallery') }}</h1>
        <p style="font-size:1.05rem; color:var(--dark-text); max-width:600px; margin:14px auto 0 auto; padding:0 15px; opacity:0.75;">{{ $tenant->getSiteContent('gallery_hero_text', 'Explore our latest custom creations uploaded directly from our kitchen!') }}</p>
    </section>

    <section class="gallery-page-section" style="padding-top:0;">
        <div class="gallery-filter-bar">
            <button class="filter-btn active" data-filter="all">All Sweets</button>
            @foreach($tenant->galleryCategories() as $galCat)
                <button class="filter-btn" data-filter="{{ $galCat }}">{{ $galCat }}</button>
            @endforeach
        </div>

        <!-- gallery-masonry-grid is kept (not renamed) since app.js's filter
             and add-photo logic selects cards by that class name; the square
             shop-grid crop is layered on via modern-grid. -->
        <div class="gallery-masonry-grid modern-grid" id="public-gallery-grid">
            @forelse($gallery as $item)
                @php $src = $item->image_url ?? $item->image_path; @endphp
                <div class="gallery-card" data-category="{{ $item->category }}" onclick="openLightbox('{{ asset($src) }}', '')">
                    <div class="gallery-card-img-wrap">
                        <img src="{{ asset($src) }}" alt="{{ $item->title ?: $tenant->name . ' Custom Cake Design' }}" loading="lazy" decoding="async">
                    </div>
                    <div class="gallery-card-info">
                        <span class="gallery-tag">{{ $item->category }}</span>
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: #888;">
                    <span class="material-symbols-outlined modern-icon" style="font-size: 3rem; display: block; margin-bottom: 12px;">photo_camera</span>
                    <h3 style="color: var(--dark-text); margin-bottom: 8px;">{{ $tenant->getSiteContent('gallery_empty_title', 'No Gallery Photos Published Yet') }}</h3>
                    <p style="font-size: 0.95rem;">{!! $tenant->getSiteContent('gallery_empty_text', 'Upload photos directly from your phone, tablet, or computer in the Baker Admin Portal under <strong>Device Gallery</strong> to display them here live!') !!}</p>
                </div>
            @endforelse
        </div>
    </section>
</div>

<!-- LIGHTBOX MODAL FOR GALLERY PREVIEWS -->
<div id="lightbox-modal" class="lightbox-modal" style="display:none;" onclick="closeLightbox()">
    <div class="lightbox-content" onclick="event.stopPropagation()">
        <button class="modal-close-btn" onclick="closeLightbox()"><span class="material-symbols-outlined" style="font-size:1.2rem; vertical-align:-2px;">close</span></button>
        <img id="lightbox-img" src="" alt="Gallery Preview" loading="lazy" decoding="async">
        <div id="lightbox-caption" class="lightbox-caption"></div>
    </div>
</div>

@include('storefront.partials.order_modal')

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
