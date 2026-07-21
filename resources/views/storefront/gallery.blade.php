<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery | Blushed Crumbs Bakehouse</title>
    <meta name="description" content="Explore custom artisanal cakes, cupcakes, and treat boxes from Blushed Crumbs Bakehouse.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

<header class="site-header">
    <div class="header-container">
        <a href="{{ route('storefront.index') }}" class="logo">
            <img src="{{ asset('images/blushedlogo.png') }}" alt="Blushed Crumbs Bakehouse Logo">
        </a>
        <nav class="nav-links">
            <a href="{{ route('storefront.index') }}">Home</a>
            <a href="{{ route('storefront.about') }}">About</a>
            <a href="{{ route('storefront.gallery') }}" class="active">Gallery</a>
            <a href="#" onclick="openOrderModal()" class="nav-order-btn">Order</a>
            <a href="{{ route('admin.dashboard') }}" class="admin-btn">🔑 Baker Admin Portal</a>
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
        <img src="{{ asset('images/blushedlogo.png') }}" alt="Blushed Crumbs Logo">
    </div>
    <div class="footer-nav">
        <a href="{{ route('storefront.index') }}">Home</a>
        <a href="{{ route('storefront.about') }}">About</a>
        <a href="{{ route('storefront.gallery') }}">Gallery</a>
    </div>
    <p class="copyright-text">Copyright © 2026 Blushed Crumbs Bakehouse | Powered by Daystar Digital</p>
</footer>

<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
