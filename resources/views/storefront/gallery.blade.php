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
                <div class="gallery-card" data-category="{{ $item->category }}" onclick="openLightbox('{{ asset($item->image_path) }}', '{{ $item->title }}')">
                    <div class="gallery-card-img-wrap">
                        <img src="{{ asset($item->image_path) }}" alt="{{ $item->title }}">
                    </div>
                    <div class="gallery-card-info">
                        <h4>{{ $item->title }}</h4>
                        <span class="gallery-tag">{{ $item->category }}</span>
                    </div>
                </div>
            @empty
                <div class="gallery-card" data-category="Cakes" onclick="openLightbox('{{ asset('images/IMG_8117.jpg') }}', 'Lavender Princess Birthday Crown Cake')">
                    <div class="gallery-card-img-wrap">
                        <img src="{{ asset('images/IMG_8117.jpg') }}" alt="Lavender Princess Birthday Crown Cake">
                    </div>
                    <div class="gallery-card-info">
                        <h4>Lavender Princess Birthday Crown</h4>
                        <span class="gallery-tag">Cakes</span>
                    </div>
                </div>
                <div class="gallery-card" data-category="Cakes" onclick="openLightbox('{{ asset('images/IMG_8084.jpg') }}', 'Minecraft TNT Tiered Cake')">
                    <div class="gallery-card-img-wrap">
                        <img src="{{ asset('images/IMG_8084.jpg') }}" alt="Minecraft TNT Tiered Cake">
                    </div>
                    <div class="gallery-card-info">
                        <h4>Minecraft TNT Multi-Tier</h4>
                        <span class="gallery-tag">Cakes</span>
                    </div>
                </div>
                <div class="gallery-card" data-category="Cupcakes" onclick="openLightbox('{{ asset('images/IMG_8042.jpg') }}', 'Frozen Snowflake Cupcake Dozen Box')">
                    <div class="gallery-card-img-wrap">
                        <img src="{{ asset('images/IMG_8042.jpg') }}" alt="Frozen Snowflake Cupcake Dozen Box">
                    </div>
                    <div class="gallery-card-info">
                        <h4>Frozen Snowflake Cupcake Box</h4>
                        <span class="gallery-tag">Cupcakes</span>
                    </div>
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
