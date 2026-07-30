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
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
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
            <a href="{{ route('storefront.gallery') }}" class="active">Gallery</a>
            @if(isset($tenant) && $tenant->subdomain === 'blushedcrumbs')
                <a href="{{ route('storefront.policy') }}">Policy</a>
            @endif
            <a href="#" onclick="openOrderModal()" class="nav-order-btn">Order</a>
        </nav>
    </div>
</header>

<div id="gallery-page-view">
    <section class="petal-page-hero">
        <span class="subheading">Take A Look</span>
        <h1 class="petal-page-hero-title">Gallery</h1>
    </section>

    <section class="gallery-page-section" style="padding-top:60px;">
        <div class="gallery-filter-bar">
            <button class="filter-btn active" data-filter="all">All</button>
            <button class="filter-btn" data-filter="Cakes">Cakes</button>
            <button class="filter-btn" data-filter="Cupcakes">Cupcakes</button>
            <button class="filter-btn" data-filter="Treats">Treats</button>
            <button class="filter-btn" data-filter="Weddings">Weddings</button>
        </div>

        <!-- gallery-masonry-grid is kept (not renamed) since app.js's filter
             and add-photo logic selects cards by that class name; the
             soft rounded-corner crop is layered on via petal-grid. -->
        <div class="gallery-masonry-grid petal-grid" id="public-gallery-grid">
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
                    <span class="material-symbols-outlined petal-icon" style="font-size: 3rem; display: block; margin-bottom: 12px;">photo_camera</span>
                    <h3 style="color: var(--dark-text); margin-bottom: 8px;">No Gallery Photos Published Yet</h3>
                    <p style="font-size: 0.95rem;">Upload photos directly from your phone, tablet, or computer in the Baker Admin Portal under <strong>Device Gallery</strong> to display them here live!</p>
                </div>
            @endforelse
        </div>
    </section>
</div>

<!-- LIGHTBOX MODAL FOR GALLERY PREVIEWS -->
<div id="lightbox-modal" class="lightbox-modal" style="display:none;" onclick="closeLightbox()">
    <div class="lightbox-content" onclick="event.stopPropagation()">
        <button class="modal-close-btn" onclick="closeLightbox()"><span class="material-symbols-outlined" style="font-size:1.2rem; vertical-align:-2px;">close</span></button>
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
            <span style="font-family:'Fraunces',serif; font-weight:700; font-size:1.6rem; color:var(--footer-text, #ffffff);"><span class="material-symbols-outlined" style="font-size:1.3rem; vertical-align:-3px;">cake</span> {{ $tenant->name }}</span>
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
    <p class="copyright-text">Copyright &copy; 2026 {{ $tenant->name ?? 'Bakery' }} | <a href="{{ route('legal.index') }}" class="footer-link">Legal Hub</a> &middot; <a href="{{ route('storefront.privacy') }}" class="footer-link">Privacy</a> &middot; <a href="{{ route('storefront.terms') }}" class="footer-link">Terms</a> | Powered by <a href="https://doughmain.pro" target="_blank" class="footer-link footer-brand-link">Doughmain.pro</a></p>
</footer>

<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
