<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blushed Crumbs Bakehouse | Where Every Celebration Gets Its Sweet Ending</title>
    <meta name="description" content="Custom artisanal cakes, cupcakes, treat boxes & wedding baking in Tennessee. Order custom cakes online with ease.">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS Assets -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="theme-{{ $tenant->theme_id ?? 'sweet_elegant' }}">

<header class="site-header">
    <div class="header-container">
        <a href="{{ route('storefront.index') }}" class="logo">
            <img src="{{ asset('images/blushedlogo.png') }}" alt="Blushed Crumbs Bakehouse Logo">
        </a>
        <nav class="nav-links">
            <a href="{{ route('storefront.index') }}">Home</a>
            <a href="{{ route('storefront.about') }}">About</a>
            <a href="{{ route('storefront.gallery') }}">Gallery</a>
            <a href="#" onclick="openOrderModal()" class="nav-order-btn">Order</a>
            <a href="{{ route('admin.dashboard') }}" class="admin-btn">🔑 Baker Admin Portal</a>
        </nav>
    </div>
</header>

<div id="storefront-view">
    <!-- Official Elementor WordPress Top Cloud Divider -->
    <img src="{{ asset('images/clouds.svg') }}" class="hero-cloud-elementor-top" alt="Top Cloud Divider">

    <!-- Hero Section with 5 Raining Cakes Encircling Headline & Buttons -->
    <section class="hero-section">
        <img src="{{ asset('images/7281AA41-A119-4BA3-A024-887E9580F7A2-removebg-preview (1).png') }}" class="raining-cake hero-cake-top-right" alt="Top Right Lavender Crown Cake">
        <img src="{{ asset('images/4ee97017-0b48-4f55-95ed-8811da81d74d-removebg-preview.png') }}" class="raining-cake hero-cake-middle-left" alt="Middle Left Pink Crown Heart Cake">
        <img src="{{ asset('images/96CABFE2-736F-4865-AA15-7FEB14C9D0BE-removebg-preview.png') }}" class="raining-cake hero-cake-far-right" alt="Chocolate 2-Tier Ruffles Cake">
        <img src="{{ asset('images/25cfe8e0-d9bf-406c-8c2a-fdb6ef4692e6-removebg-preview.png') }}" class="raining-cake hero-cake-bottom-left" alt="Bottom Left White Heart Cake">
        <img src="{{ asset('images/7281AA41-A119-4BA3-A024-887E9580F7A2-removebg-preview (1).png') }}" class="raining-cake hero-cake-bottom-right" alt="Bottom Right Floral Vintage Cake">

        <div class="hero-wrapper">
            <span class="subheading">{{ $tenant->getSiteContent('hero_subheading', 'Welcome to ' . ($tenant->name ?? 'Blushed Crumbs Bakehouse')) }}</span>
            <h1>{{ $tenant->getSiteContent('hero_headline', 'Where Every Celebration Gets Its Sweet Ending.') }}</h1>
            <div class="hero-buttons">
                <button onclick="openOrderModal()" class="btn btn-primary">{{ $tenant->getSiteContent('hero_cta_primary', 'Custom Order') }}</button>
                <a href="{{ route('storefront.gallery') }}" class="btn btn-secondary">{{ $tenant->getSiteContent('hero_cta_secondary', 'Browse Our Sweets') }}</a>
            </div>
        </div>
    </section>

    <!-- Official Elementor WordPress Bottom Cloud Divider -->
    <img src="{{ asset('images/clouds.svg') }}" class="hero-cloud-elementor-bottom" alt="Bottom Cloud Divider">

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
            <source src="{{ asset('images/download (2) (1).mp4') }}" type="video/mp4">
        </video>
        <div class="video-overlay-content">
            <h2>$10 Off Your First Order!</h2>
            <p>Follow us on social media or join our community for instant discounts.</p>
            <button onclick="openOrderModal()" class="btn btn-dark">Order Now</button>
        </div>
    </section>

    <!-- Categories Section -->
    <section id="categories" class="categories-section">
        <h2 class="section-title-script">Our Categories</h2>
        <div class="categories-grid-exact">
            <div class="category-card-exact">
                <div class="category-image-frame">
                    <img src="{{ asset('images/IMG_8117.jpg') }}" alt="Single Tier Cakes">
                </div>
                <h3>Single Tier Cakes</h3>
            </div>
            <div class="category-card-exact">
                <div class="category-image-frame">
                    <img src="{{ asset('images/IMG_8084.jpg') }}" alt="Multi Tier Cakes">
                </div>
                <h3>Multi Tier Cakes</h3>
            </div>
            <div class="category-card-exact">
                <div class="category-image-frame">
                    <img src="{{ asset('images/IMG_8042.jpg') }}" alt="By The Dozen">
                </div>
                <h3>By The Dozen</h3>
            </div>
        </div>
    </section>

    <!-- Whimsical Section -->
    <section class="whimsical-section">
        <div class="whimsical-two-column">
            <div class="whimsical-col-left">
                <img src="{{ asset('images/96CABFE2-736F-4865-AA15-7FEB14C9D0BE-removebg-preview.png') }}" alt="Whimsical Mermaid Cake on Silver Stand">
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

    <!-- How It Works Section -->
    <section class="how-it-works-section" style="padding:70px 25px; text-align:center; background:var(--pink-bg);">
        <h2 class="section-title-script" style="margin-bottom:15px;">How Custom Ordering Works</h2>
        <p style="max-width:600px; margin:0 auto 40px auto; color:var(--dark-text); font-size:1.05rem;">Ordering your dream cake in 3 simple steps</p>
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:25px; max-width:1100px; margin:0 auto;">
            <div class="category-card-exact" style="padding:25px; background:white; border-radius:18px; box-shadow:0 6px 20px rgba(0,0,0,0.05);">
                <span style="font-size:2.5rem; display:block; margin-bottom:10px;">1️⃣</span>
                <h3 style="font-size:1.25rem; font-weight:700; margin-bottom:8px; color:var(--dark-text);">Pick Your Date &amp; Flavors</h3>
                <p style="font-size:0.9rem; color:#666;">Use our 12-step form to choose your size, cake flavor, frosting, and upload your inspiration images.</p>
            </div>
            <div class="category-card-exact" style="padding:25px; background:white; border-radius:18px; box-shadow:0 6px 20px rgba(0,0,0,0.05);">
                <span style="font-size:2.5rem; display:block; margin-bottom:10px;">2️⃣</span>
                <h3 style="font-size:1.25rem; font-weight:700; margin-bottom:8px; color:var(--dark-text);">Approve Design &amp; Deposit</h3>
                <p style="font-size:0.9rem; color:#666;">Receive your custom invoice &amp; quote via email. Place a 50% deposit to lock in your date on our calendar.</p>
            </div>
            <div class="category-card-exact" style="padding:25px; background:white; border-radius:18px; box-shadow:0 6px 20px rgba(0,0,0,0.05);">
                <span style="font-size:2.5rem; display:block; margin-bottom:10px;">3️⃣</span>
                <h3 style="font-size:1.25rem; font-weight:700; margin-bottom:8px; color:var(--dark-text);">Fresh Pickup or Delivery</h3>
                <p style="font-size:0.9rem; color:#666;">We bake your creation fresh right before your event. Pick up at our kitchen or get venue delivery!</p>
            </div>
        </div>
    </section>

    <!-- Customer Reviews Section -->
    <section id="reviews" class="reviews-section">
        <h2 class="section-title-script">What Our Customers Say</h2>
        <div class="reviews-grid" id="public-reviews-grid">
            <div class="cloud-review-card">
                <p>"Absolutely breathtaking work!! The detail put into this cake was insane and it tasted unbelievable!! You made me look like the best sister ever, thank you so much for your talent and hard work!!"</p>
                <h4>Kristen Ramirez</h4>
            </div>
            <div class="cloud-review-card">
                <p>"I ordered a strawberry smash cake for my 1 year old, with strawberries on top and custom icing and she devoured it ✨ The cake was so moist &amp; icing wasn’t too sweet! Pick up process was super easy."</p>
                <h4>Lynne Escue</h4>
            </div>
            <div class="cloud-review-card">
                <p>"Not only was I extremely shocked at how cute this cake was, I was truly SO surprised with how delicious it was! I tried to get a slice and having trouble cutting the back, I was SO CONFUSED."</p>
                <h4>Alexis</h4>
            </div>
            <div class="cloud-review-card">
                <p>"She was super friendly and easy to work with! The cake looked awesome, everything I was hoping for! ❤️"</p>
                <h4>Pamela Cortes</h4>
            </div>
        </div>
    </section>

    <!-- FAQ & Bakery Policies Section -->
    <section class="faq-policies-section" style="padding:70px 25px; background:#ffffff; text-align:center;">
        <h2 class="section-title-script" style="margin-bottom:15px;">Frequently Asked Questions</h2>
        <div style="max-width:850px; margin:0 auto; text-align:left; display:flex; flex-direction:column; gap:18px;">
            <div style="background:var(--pink-bg); padding:20px; border-radius:14px; border-left:4px solid var(--primary);">
                <h4 style="font-size:1.1rem; font-weight:700; color:var(--dark-text); margin-bottom:6px;">📅 How far in advance should I order?</h4>
                <p style="font-size:0.92rem; color:#555; line-height:1.6;">We require at least 3 days advance notice for custom orders. For weddings and large multi-tier events, we recommend booking 2-4 weeks in advance to reserve your date.</p>
            </div>
            <div style="background:var(--pink-bg); padding:20px; border-radius:14px; border-left:4px solid var(--primary);">
                <h4 style="font-size:1.1rem; font-weight:700; color:var(--dark-text); margin-bottom:6px;">💳 What is the deposit requirement?</h4>
                <p style="font-size:0.92rem; color:#555; line-height:1.6;">A 50% non-refundable deposit is required at booking to secure your date. Remaining balance is due prior to pickup or delivery.</p>
            </div>
            <div style="background:var(--pink-bg); padding:20px; border-radius:14px; border-left:4px solid var(--primary);">
                <h4 style="font-size:1.1rem; font-weight:700; color:var(--dark-text); margin-bottom:6px;">⚠️ Allergy Information</h4>
                <p style="font-size:0.92rem; color:#555; line-height:1.6;">We operate under Tennessee cottage food laws. Our kitchen processes wheat, eggs, dairy, and nuts. Please disclose all food allergies during checkout!</p>
            </div>
        </div>
    </section>

    <!-- Footer Call to Action Video Banner -->
    <section class="cta-video-banner">
        <video autoplay loop muted playsinline>
            <source src="{{ asset('images/34d48b27-1dd9-4784-8c8d-b378c3388060.mp4') }}" type="video/mp4">
        </video>
        <div class="cta-content">
            <h2>Ready For Your Perfect Cake?</h2>
            <p>Order your plan or custom order now</p>
            <button onclick="openOrderModal()" class="btn btn-dark">Order Now</button>
        </div>
    </section>
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
    <p class="copyright-text">Copyright © 2026 {{ $tenant->name ?? 'Blushed Crumbs Bakehouse' }} | Powered by <a href="https://bakery.pro" target="_blank" style="color:var(--primary); font-weight:700; text-decoration:none;">Bakery.Pro</a> — <em>Want your own bakery website?</em></p>
</footer>

<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
