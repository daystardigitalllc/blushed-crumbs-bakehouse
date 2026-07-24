<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery | {{ $tenant->name ?? 'Blushed Crumbs Bakehouse' }}</title>
    <!-- Favicon -->
    @if(isset($tenant) && $tenant->logo_path)
        <link rel="icon" href="{{ asset($tenant->logo_path) }}">
    @else
        <link rel="icon" href="{{ asset('images/favicon.png') }}">
    @endif
    <meta name="description" content="Explore custom artisanal cakes, cupcakes, and treat boxes from {{ $tenant->name ?? 'Blushed Crumbs Bakehouse' }}.">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
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
            <a href="{{ route('storefront.gallery') }}" class="active">Gallery</a>
            <a href="#" onclick="openOrderModal()" class="nav-order-btn">Order Now</a>
        </nav>
    </div>
</header>

<div id="gallery-page-view">
    <section class="gallery-page-section">
        <div style="text-align:center; margin-bottom:40px;">
            <h1 class="section-title-script" style="font-size:4.5rem; margin-bottom:10px;">Our Gallery</h1>
            <p style="font-size:1.1rem; color:#4a2133; max-width:600px; margin:0 auto;">Explore our latest custom creations uploaded directly from our kitchen!</p>
        </div>

        <div class="gallery-filter-bar">
            <button class="filter-btn active" data-filter="all">All Sweets</button>
            <button class="filter-btn" data-filter="Cakes">Custom Cakes</button>
            <button class="filter-btn" data-filter="Cupcakes">Cupcakes & Shooters</button>
            <button class="filter-btn" data-filter="Treats">Chocolate Treats</button>
            <button class="filter-btn" data-filter="Weddings">Weddings</button>
        </div>

        <div class="gallery-masonry-grid" id="public-gallery-grid">
            @forelse($gallery as $item)
                @php $src = $item->image_url ?? $item->image_path; @endphp
                <div class="gallery-card" data-category="{{ $item->category }}" onclick="openLightbox('{{ asset($src) }}', '{{ $item->title }}')">
                    <div class="gallery-card-img-wrap">
                        <img src="{{ asset($src) }}" alt="{{ $item->title }}">
                    </div>
                    <div class="gallery-card-info">
                        <h4>{{ $item->title }}</h4>
                        <span class="gallery-tag">{{ $item->category }}</span>
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: #888;">
                    <span style="font-size: 3rem; display: block; margin-bottom: 12px;">📷</span>
                    <h3 style="color: #5c1d37; margin-bottom: 8px;">No Gallery Photos Published Yet</h3>
                    <p style="font-size: 0.95rem;">Upload photos directly from your phone, tablet, or computer in the Baker Admin Portal under <strong>Device Gallery</strong> to display them here live!</p>
                </div>
            @endforelse

        </div>
    </section>
</div>

<!-- LIGHTBOX MODAL FOR GALLERY PREVIEWS -->
<div id="lightbox-modal" class="lightbox-modal" style="display:none;" onclick="closeLightbox()">
    <div class="lightbox-content" onclick="event.stopPropagation()">
        <button class="modal-close-btn" onclick="closeLightbox()">✕</button>
        <img id="lightbox-img" src="" alt="Gallery Preview">
        <div id="lightbox-caption" class="lightbox-caption"></div>
    </div>
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
    <p class="copyright-text">Copyright &copy; 2026 {{ $tenant->name ?? 'Bakery' }} | <a href="{{ route('legal.index') }}" class="footer-link">Legal Hub</a> &middot; <a href="{{ route('storefront.privacy') }}" class="footer-link">Privacy</a> &middot; <a href="{{ route('storefront.terms') }}" class="footer-link">Terms</a> | Powered by <a href="https://doughmain.pro" target="_blank" class="footer-link footer-brand-link">Doughmain.pro</a></p>
</footer>

<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
