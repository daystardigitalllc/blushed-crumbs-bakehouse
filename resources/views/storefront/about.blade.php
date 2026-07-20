<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | Blushed Crumbs Bakehouse</title>
    <meta name="description" content="Learn about Blushed Crumbs Bakehouse, our cottage bakery story, passion for custom wedding cakes, and artisanal baking in Tennessee.">

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
            <a href="{{ route('storefront.about') }}" class="active">About</a>
            <a href="{{ route('storefront.gallery') }}">Gallery</a>
            <a href="#" onclick="openOrderModal()" class="nav-order-btn">Order</a>
            <a href="{{ route('admin.dashboard') }}" class="admin-btn">🔑 Baker Admin Portal</a>
        </nav>
    </div>
</header>

<div id="about-page-view">
    <section class="gallery-page-section">
        <div style="text-align:center; margin-bottom:50px;">
            <span class="subheading" style="font-size:2.2rem; display:block;">Our Story & Passion</span>
            <h1 class="section-title-script" style="font-size:4.5rem; margin-bottom:15px;">About Blushed Crumbs</h1>
            <p style="font-size:1.15rem; color:#4a2133; max-width:750px; margin:0 auto; line-height:1.7;">
                Welcome to <strong>Blushed Crumbs Bakehouse</strong>! We are a boutique cottage bakery dedicated to creating handcrafted, artisanal custom cakes, cupcakes, and gourmet treats for life’s sweetest milestones.
            </p>
        </div>

        <!-- Whimsical Two Column Feature -->
        <div class="whimsical-two-column" style="margin-bottom:60px;">
            <div class="whimsical-col-left">
                <img src="{{ asset('images/IMG_8117.jpg') }}" alt="Blushed Crumbs Bakehouse Creation" style="border-radius:24px; box-shadow:var(--card-shadow); max-width:100%;">
            </div>
            <div class="whimsical-col-right">
                <h2 style="font-size:3.2rem; margin-bottom:20px;">Baked With Love & Premium Ingredients</h2>
                <p style="margin-bottom:15px; font-size:1.05rem; color:#4a2133;">
                    Every single order that leaves our kitchen is baked fresh to order right before your event. We believe that a great cake should not only look breathtaking, but also taste incredibly moist, rich, and unforgettable.
                </p>
                <ul class="whimsical-bullet-list">
                    <li><strong>Custom Wedding & Celebration Cakes:</strong> Tailored to match your theme, color scheme, and personal taste.</li>
                    <li><strong>Scratch-Baked Goodness:</strong> Prepared using premium flours, pure vanilla, real butter, and velvety custom buttercream frostings.</li>
                    <li><strong>Local Delivery & Easy Pickups:</strong> Convenient, stress-free scheduling for your birthdays, weddings, and special events.</li>
                </ul>
                <hr class="whimsical-hr">
            </div>
        </div>

        <!-- Highlights Grid -->
        <div class="highlights-bar" style="border-radius:25px; margin-bottom:60px;">
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
        </div>

        <div style="text-align:center; padding:40px 20px; background:white; border-radius:24px; box-shadow:var(--card-shadow);">
            <h2 class="section-title-script" style="font-size:3.8rem; margin-bottom:15px;">Ready for Your Dream Cake?</h2>
            <p style="font-size:1.1rem; color:#666; margin-bottom:25px;">Let’s create something sweet together for your next celebration.</p>
            <button onclick="openOrderModal()" class="btn btn-primary" style="padding:14px 40px; font-size:1.1rem;">Place a Custom Order</button>
        </div>
    </section>
</div>

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
