<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | Blushed Crumbs Bakehouse</title>
    <meta name="description" content="Learn about Blushed Crumbs Bakehouse, our founder story, passion for custom wedding cakes, and artisanal baking in Tennessee.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

<!-- HEADER NAVIGATION -->
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
    <!-- HERO SECTION -->
    <section class="about-hero-section">
        <span class="about-hero-subtitle">ABOUT US</span>
        <h1 class="about-hero-title">Who is Blushed Crumbs?</h1>
    </section>

    <!-- MEET THE FOUNDER SECTION -->
    <section class="meet-founder-section">
        <div class="meet-founder-container">
            <div class="meet-founder-img-wrap">
                <img src="{{ asset('images/baker_founder_portrait.jpg') }}" alt="Meet The Founder">
            </div>
            <div class="meet-founder-content">
                <h2>Meet The Founder</h2>
                <p>At Blushed Crumbs Bakehouse, we specialize in homemade custom cakes and treats that are as beautiful as they are delicious. Every single order is made fresh with quality ingredients and attention to every detail, creating unforgettable treats for your sweetest moments.</p>
                <div class="founder-testimonial-quote">
                    <p>"She was very friendly & easy to work with. The cake tasted amazing. Everything I was hoping for ❤️"</p>
                    <span class="founder-author-name">Pamela Cortes</span>
                    <span class="founder-author-role">Loyal Customer</span>
                </div>
            </div>
        </div>
    </section>

    <!-- THE INGREDIENTS BEHIND SECTION -->
    <section class="ingredients-section">
        <h2 class="ingredients-title">The Ingredients Behind Blushed Crumbs Bakehouse</h2>
        <div class="ingredients-grid-6">
            <div class="ingredient-card">
                <div class="ingredient-card-header">
                    <span>💖</span>
                    <h4>Baked with Passion</h4>
                </div>
                <p>Baking isn't just a job for us – it's our true calling. We bring endless energy, enthusiasm, and love into the kitchen every single day to create edible art that makes your heart skip a beat.</p>
            </div>
            <div class="ingredient-card">
                <div class="ingredient-card-header">
                    <span>🎨</span>
                    <h4>Artistry & Imagination</h4>
                </div>
                <p>Our talented bakers thrive on custom design. From delicate buttercream piping to show-stopping cake crowns, we turn your wildest, sweetest daydreams into stunning, delicious reality.</p>
            </div>
            <div class="ingredient-card">
                <div class="ingredient-card-header">
                    <span>✨</span>
                    <h4>Sweet Innovation</h4>
                </div>
                <p>We love putting a modern, playful twist on classic traditions. Whether we are crafting a trend-setting heart cake or inventing irresistible new flavor pairings, we constantly push the boundaries of luxury baking.</p>
            </div>
            <div class="ingredient-card">
                <div class="ingredient-card-header">
                    <span>🍓</span>
                    <h4>Premium Ingredients</h4>
                </div>
                <p>A beautiful cake is nothing without an unforgettable taste. We meticulously source the finest, highest-quality ingredients to ensure every single crumb satisfies your sweet tooth and tastes absolutely flawless.</p>
            </div>
            <div class="ingredient-card">
                <div class="ingredient-card-header">
                    <span>🍦</span>
                    <h4>Spreading Pure Joy</h4>
                </div>
                <p>Your celebration is our celebration, and your happiness means everything to us. From your very first inquiry to the very last bite, we are dedicated to giving you a magical, stress-free experience.</p>
            </div>
            <div class="ingredient-card">
                <div class="ingredient-card-header">
                    <span>👑</span>
                    <h4>Seamless Celebration</h4>
                </div>
                <p>We believe ordering a custom cake should be a piece of cake. We keep our process simple, clear, and perfectly tailored, taking the stress out of event planning so you can focus entirely on celebrating.</p>
            </div>
        </div>
    </section>

    <!-- OUR MISSION SECTION -->
    <section class="mission-section">
        <div class="mission-container">
            <div class="mission-content">
                <h2>Our Mission</h2>
                <p>Our mission is to make the world a sweeter, more beautiful place one celebration at a time. We strive to provide our clients with jaw-dropping, custom cake designs and unforgettable flavor experiences, crafted with luxury ingredients and a seamless, personalized process that lets you focus entirely on making memories.</p>
                <div class="mission-bullets-2x2">
                    <div>✨ Artistry & Flavorful</div>
                    <div>🍓 Premium Ingredients</div>
                    <div>👑 Custom Experience</div>
                    <div>💖 Made to Celebrate</div>
                </div>
            </div>
            <div class="mission-img-wrap">
                <img src="{{ asset('images/bento_cake_mission.jpg') }}" alt="Our Mission Bento Cake">
            </div>
        </div>
    </section>

    <!-- CTA BANNER -->
    <section class="about-cta-banner">
        <h2>Ready For Your Perfect Cake?</h2>
        <p>Browse our gallery or submit a custom order to get started</p>
        <button onclick="openOrderModal()" class="about-cta-btn">Submit Order</button>
    </section>
</div>

@include('storefront.partials.order_modal')

<!-- ABOUT PAGE FOOTER -->
<footer class="about-footer">
    <div class="about-footer-logo">
        <img src="{{ asset('images/blushedlogo.png') }}" alt="Blushed Crumbs Logo">
    </div>
    <div class="about-footer-nav">
        <a href="{{ route('storefront.index') }}">Home</a>
        <a href="{{ route('storefront.about') }}" class="active">About</a>
        <a href="{{ route('storefront.gallery') }}">Gallery</a>
        <a href="#" onclick="openOrderModal()">Order</a>
    </div>
    <div class="about-social-icons">
        <a href="#">🌐</a>
        <a href="#">✉️</a>
        <a href="#">🎵</a>
    </div>
    <p class="about-copyright">Copyright © 2026 Blushed Crumbs Bakehouse | Powered By <span>Daystar Digital</span></p>
</footer>

<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
