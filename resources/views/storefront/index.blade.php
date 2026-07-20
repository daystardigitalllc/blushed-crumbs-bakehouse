<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blushed Crumbs Bakehouse | Where Every Celebration Gets Its Sweet Ending</title>
    <meta name="description" content="Custom artisanal cakes, cupcakes, treat boxes & wedding baking in Tennessee. Order custom cakes online with ease.">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS Assets -->
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
            <a href="{{ route('storefront.index') }}#categories">About</a>
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
    <section id="home" class="hero-section">
        <img src="{{ asset('images/7281AA41-A119-4BA3-A024-887E9580F7A2-removebg-preview (1).png') }}" class="raining-cake hero-cake-top-right" alt="Top Right Lavender Crown Cake">
        <img src="{{ asset('images/4ee97017-0b48-4f55-95ed-8811da81d74d-removebg-preview.png') }}" class="raining-cake hero-cake-middle-left" alt="Middle Left Pink Crown Heart Cake">
        <img src="{{ asset('images/96CABFE2-736F-4865-AA15-7FEB14C9D0BE-removebg-preview.png') }}" class="raining-cake hero-cake-far-right" alt="Chocolate 2-Tier Ruffles Cake">
        <img src="{{ asset('images/25cfe8e0-d9bf-406c-8c2a-fdb6ef4692e6-removebg-preview.png') }}" class="raining-cake hero-cake-bottom-left" alt="Bottom Left White Heart Cake">
        <img src="{{ asset('images/7281AA41-A119-4BA3-A024-887E9580F7A2-removebg-preview (1).png') }}" class="raining-cake hero-cake-bottom-right" alt="Bottom Right Floral Vintage Cake">

        <div class="hero-wrapper">
            <span class="subheading">Welcome to Blushed Crumbs Bakehouse</span>
            <h1>Where Every Celebration<br>Gets Its Sweet Ending.</h1>
            <div class="hero-buttons">
                <button onclick="openOrderModal()" class="btn btn-primary">Custom Order</button>
                <a href="#categories" class="btn btn-secondary">Browse Our Sweets</a>
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
                <h2>Whimsical Creations<br>for Every Milestone</h2>
                <ul class="whimsical-bullet-list">
                    <li><strong>Custom Wedding Cakes:</strong> Elegant, timeless, and tailored entirely to your love story. We work with you to design a showstopper that tastes even better than it looks.</li>
                    <li><strong>Birthday & Party Cakes:</strong> From whimsical children's themes to sleek, modern adult designs. If you can dream it, we can bake it.</li>
                    <li><strong>Anniversary Cakes:</strong> Recommence your vows with a beautiful, nostalgic dessert that honors your journey together.</li>
                    <li><strong>Signature Sheet Cakes:</strong> Perfect for larger crowds, school events, or casual get-togethers. Infinitely customizable, incredibly moist, and crowd-pleasingly delicious.</li>
                    <li><strong>Gourmet Chocolate-Covered Strawberries:</strong> Ripe, juicy berries hand-dipped in premium, velvety chocolate. The perfect add-on gift, party favor, or elegant treat.</li>
                </ul>
                <hr class="whimsical-hr">
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
                <p>"I ordered a strawberry smash cake for my 1 year old, with strawberries on top and custom icing and she devoured it ✨ The cake was so moist & icing wasn’t too sweet! Pick up process was super easy."</p>
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

<!-- POPUP MODAL ORDER FORM BUILDER -->
<div id="order-modal-popup" class="order-modal-overlay" style="display:none;">
    <div class="order-modal-card">
        <button class="modal-close-btn" onclick="closeOrderModal()">✕</button>

        <div id="cake-order-builder">
            <div class="order-content" id="form-container-toggle">
                <!-- Step 1: Products -->
                <section class="step active" id="step-1">
                    <h2>Build Your Order</h2>
                    <div id="product-grid">
                        <div class="product" data-name="4” Cake" data-price="45"><strong>4” Cake</strong><br>$45</div>
                        <div class="product" data-name="6” Cake" data-price="65"><strong>6” Cake</strong><br>$65</div>
                        <div class="product" data-name="7” Cake" data-price="75"><strong>7” Cake</strong><br>$75</div>
                        <div class="product" data-name="8” Cake" data-price="85"><strong>8” Cake</strong><br>$85</div>
                        <div class="product" data-name="9” Cake" data-price="95"><strong>9” Cake</strong><br>$95</div>
                        <div class="product" data-name="10” Cake" data-price="115"><strong>10” Cake</strong><br>$115</div>
                        <div class="product" data-name="Bento Box" data-price="45"><strong>Bento Box</strong><br>$45</div>
                        <div class="product" data-name="Smash Cake" data-price="35"><strong>Smash Cake</strong><br>$35</div>
                    </div>
                    
                    <div class="cart-bar">
                        <div id="cart-items-list">No items selected</div>
                        <div id="cart-summary">Items: 0 <br> <strong>Total: $0</strong></div>
                        <div class="nav-buttons">
                            <button class="next-btn" id="to-step-2" disabled>Continue</button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>

<footer class="site-footer">
    <div class="footer-logo">
        <img src="{{ asset('images/blushedlogo.png') }}" alt="Blushed Crumbs Logo">
    </div>
    <div class="footer-nav">
        <a href="{{ route('storefront.index') }}">Home</a>
        <a href="{{ route('storefront.index') }}#categories">About</a>
        <a href="{{ route('storefront.gallery') }}">Gallery</a>
    </div>
    <p class="copyright-text">Copyright © 2026 Blushed Crumbs Bakehouse | Powered by Daystar Digital</p>
</footer>

<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
