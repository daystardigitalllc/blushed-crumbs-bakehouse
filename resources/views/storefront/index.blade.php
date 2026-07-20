@extends('layouts.app')

@section('title', 'Blushed Crumbs Bakehouse | Where Every Celebration Gets Its Sweet Ending')

@section('content')
<!-- Header & Navigation -->
<header class="site-header">
    <div class="header-container">
        <a href="#home" class="logo">
            <img src="/images/blushedlogo.png" alt="Blushed Crumbs Bakehouse Logo">
        </a>
        <nav class="nav-links">
            <a href="#home">Home</a>
            <a href="#categories">Work</a>
            <a href="#gallery">Gallery</a>
            <a href="#order-form-section">Order</a>
            <a href="/admin" class="admin-btn">🔑 Baker Admin Portal</a>
        </nav>
    </div>
</header>

<!-- Hero Section with Floating Transparent Cakes -->
<section id="home" class="hero-section">
    <img src="/images/4ee97017-0b48-4f55-95ed-8811da81d74d-removebg-preview.png" class="floating-cake cake-top-left" alt="Cake Floating">
    <img src="/images/7281AA41-A119-4BA3-A024-887E9580F7A2-removebg-preview (1).png" class="floating-cake cake-top-right" alt="Cake Floating">
    <img src="/images/25cfe8e0-d9bf-406c-8c2a-fdb6ef4692e6-removebg-preview.png" class="floating-cake cake-bottom-left" alt="Heart Cake Floating">
    <img src="/images/96CABFE2-736F-4865-AA15-7FEB14C9D0BE-removebg-preview.png" class="floating-cake cake-bottom-right" alt="Mermaid Cake Floating">

    <div class="hero-wrapper">
        <span class="subheading">Welcome to Blushed Crumbs Bakehouse</span>
        <h1>Where Every Celebration Gets Its Sweet Ending.</h1>
        <div class="hero-buttons">
            <a href="#order-form-section" class="btn btn-primary">Custom Order</a>
            <a href="#categories" class="btn btn-secondary">Explore Our Menu</a>
        </div>
    </div>
</section>

<!-- Cloud Scallop Divider -->
<svg class="cloud-svg-divider" viewBox="0 0 1440 60" preserveAspectRatio="none"><path d="M0,32 C150,60 350,-10 500,40 C650,90 850,10 1000,45 C1150,80 1350,10 1440,32 L1440,60 L0,60 Z"></path></svg>

<!-- Highlights Bar -->
<section class="highlights-bar">
    <div class="highlight-item">
        <div class="icon-circle">🎂</div>
        <h4>Easy Catering</h4>
        <p>Add custom baked goods to any occasion</p>
    </div>
    <div class="highlight-item">
        <div class="icon-circle">🚚</div>
        <h4>Freshly Baked</h4>
        <p>Made to order right before your event</p>
    </div>
    <div class="highlight-item">
        <div class="icon-circle">📦</div>
        <h4>Local Delivery</h4>
        <p>Flexible pickup & delivery options</p>
    </div>
    <div class="highlight-item">
        <div class="icon-circle">💖</div>
        <h4>Baked with Love</h4>
        <p>Cottage bakery crafted with care</p>
    </div>
</section>

<!-- Video Background Promo Banner -->
<section class="video-promo-banner">
    <video autoplay loop muted playsinline>
        <source src="/images/download (2) (1).mp4" type="video/mp4">
    </video>
    <div class="video-overlay-content">
        <h2>$10 Off Your First Order!</h2>
        <p>Follow us on social media or join our community for instant discounts.</p>
        <a href="#order-form-section" class="btn btn-dark">Order Now</a>
    </div>
</section>

<!-- Categories Section -->
<section id="categories" class="categories-section">
    <h2 class="section-title-script">Our Categories</h2>
    <div class="categories-grid">
        <div class="category-card">
            <img src="https://images.unsplash.com/photo-1535141192574-5d4897c13136?auto=format&fit=crop&w=600&q=80" alt="Single Tier Cakes">
            <h3>Single Tier Cakes</h3>
        </div>
        <div class="category-card">
            <img src="https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=600&q=80" alt="Multi Tier Cakes">
            <h3>Multi Tier Cakes</h3>
        </div>
        <div class="category-card">
            <img src="https://images.unsplash.com/photo-1587314168485-3236d6710814?auto=format&fit=crop&w=600&q=80" alt="By The Dozen">
            <h3>By The Dozen</h3>
        </div>
    </div>
</section>

<!-- Cloud Scallop Divider -->
<svg class="cloud-svg-divider cloud-svg-burgundy" viewBox="0 0 1440 60" preserveAspectRatio="none"><path d="M0,20 C200,60 400,0 600,40 C800,80 1000,10 1200,50 C1350,20 1400,40 1440,30 L1440,60 L0,60 Z"></path></svg>

<!-- Whimsical Creations Section -->
<section class="whimsical-section">
    <div class="whimsical-container">
        <div class="whimsical-img">
            <img src="/images/96CABFE2-736F-4865-AA15-7FEB14C9D0BE-removebg-preview.png" alt="Whimsical Mermaid Cake">
        </div>
        <div class="whimsical-text">
            <h2>Whimsical Creations for Every Milestone</h2>
            <ul>
                <li><strong>Custom Wedding Cakes:</strong> Elegant 2 & 3 tiered creations tailored to your theme.</li>
                <li><strong>Birthday & Theme Cakes:</strong> Fun children's and adult milestone designs.</li>
                <li><strong>Anniversary & Party Packs:</strong> Treat packages featuring cupcakes, cake shooters, and cake sickles.</li>
                <li><strong>Gourmet Flavors:</strong> Premium luxury options including Almond Elegance, Pistachio Ice, and Cookie Butter.</li>
            </ul>
        </div>
    </div>
</section>

<!-- Cloud Scallop Divider -->
<svg class="cloud-svg-divider cloud-svg-pink" viewBox="0 0 1440 60" preserveAspectRatio="none"><path d="M0,40 C180,10 380,50 580,20 C780,60 980,10 1180,40 C1320,10 1400,30 1440,25 L1440,60 L0,60 Z"></path></svg>

<!-- 12-Step Cake Order Builder Section -->
<section id="order-form-section" class="order-section">
    <div class="ast-container">
        <div id="cake-order-builder">
            <div class="order-content" id="form-container-toggle">
                <!-- Step 1: Products -->
                <section class="step active" id="step-1">
                    <h2>Build Your Order</h2>
                    <div id="product-grid">
                        @foreach($products as $product)
                            <div class="product" data-name="{{ $product->name }}" data-price="{{ $product->price }}">
                                <strong>{{ $product->name }}</strong><br>${{ number_format($product->price, 0) }}
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="cart-bar">
                        <div id="cart-items-list">No items selected</div>
                        <div id="cart-summary">Items: 0 <br> <strong>Total: $0</strong></div>
                        <div class="nav-buttons">
                            <button class="next-btn" id="to-step-2" disabled>Continue</button>
                        </div>
                    </div>
                </section>
                <!-- Further steps... -->
            </div>
        </div>
    </div>
</section>

<!-- Customer Reviews Section -->
<section id="reviews" class="reviews-section">
    <h2 class="section-title-script">What Our Customers Say</h2>
    <div class="reviews-grid" id="public-reviews-grid">
        @foreach($reviews as $rev)
            <div class="cloud-review-card">
                <p>"{{ $rev->review_text }}"</p>
                <h4>{{ $rev->client_name }}</h4>
            </div>
        @endforeach
    </div>
</section>

<!-- Footer Call to Action Video Banner -->
<section class="cta-video-banner">
    <video autoplay loop muted playsinline>
        <source src="/images/34d48b27-1dd9-4784-8c8d-b378c3388060.mp4" type="video/mp4">
    </video>
    <div class="cta-content">
        <h2>Ready For Your Perfect Cake?</h2>
        <p>Order your plan or custom order now</p>
        <a href="#order-form-section" class="btn btn-dark">Order Now</a>
    </div>
</section>

<!-- Site Footer -->
<footer class="site-footer">
    <div class="footer-logo">
        <img src="/images/blushedlogo.png" alt="Blushed Crumbs Logo">
    </div>
    <div class="footer-nav">
        <a href="#home">Home</a>
        <a href="#categories">Work</a>
        <a href="#gallery">Gallery</a>
        <a href="#order-form-section">Order</a>
    </div>
    <p class="copyright-text">Copyright © {{ date('Y') }} Blushed Crumbs Bakehouse | Powered by Daystar Digital</p>
</footer>
@endsection
