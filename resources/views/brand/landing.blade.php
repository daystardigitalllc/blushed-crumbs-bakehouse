<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doughmain.pro — Build, Host, &amp; Manage Your Bakery Website Completely Free</title>
    <meta name="description" content="Build, host, and manage your bakery website completely free. AI website builder, 0% commissions, custom cake order forms, and instant payment requests for custom bakers.">
    @if(isset($tenant) && $tenant->logo_path)
        <link rel="icon" href="{{ asset($tenant->logo_path) }}">
    @else
        <link rel="icon" href="{{ asset('images/favicon.png') }}">
    @endif
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-pink: #e67399;
            --primary-pink-dark: #d25a80;
            --deep-purple: #6d28d9;
            --soft-pink: #fff7fa;
            --dark-section: #1a0a2e;
            --text-dark: #333333;
            --text-gray: #666666;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
            background-color: var(--soft-pink);
            line-height: 1.6;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Outfit', sans-serif;
            color: var(--dark-section);
            line-height: 1.2;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* Utility */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .btn {
            display: inline-block;
            padding: 14px 28px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            font-size: 1rem;
            text-align: center;
        }

        .btn-primary {
            background-color: var(--primary-pink);
            color: var(--white);
            box-shadow: 0 4px 14px rgba(230, 115, 153, 0.4);
        }

        .btn-primary:hover {
            background-color: var(--primary-pink-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(230, 115, 153, 0.5);
        }

        .btn-light {
            background-color: var(--white);
            color: var(--dark-section);
        }
        
        .btn-light:hover {
            background-color: #f1f1f1;
            transform: translateY(-2px);
        }

        /* Animations */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s ease, transform 0.8s ease;
        }

        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Navigation */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 16px 0;
        }

        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-logo {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--dark-section);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .nav-login {
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .nav-login:hover {
            color: var(--primary-pink);
        }

        /* Hero Section */
        .hero {
            padding: 180px 0 140px;
            background: url("{{ asset('images/hero_bakery_baking.jpg') }}") center center / cover no-repeat;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(26, 10, 46, 0.85) 0%, rgba(64, 24, 41, 0.80) 100%);
            z-index: 1;
        }

        .hero .container {
            position: relative;
            z-index: 2;
        }

        .hero h1 {
            font-size: 3.8rem;
            font-weight: 800;
            max-width: 900px;
            margin: 0 auto 24px;
            letter-spacing: -0.02em;
            color: #ffffff;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
        }

        .hero p.subheadline {
            font-size: 1.3rem;
            color: rgba(255, 255, 255, 0.92);
            max-width: 720px;
            margin: 0 auto 36px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.4);
        }

        .hero-badges {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 36px;
        }

        .hero-badge-pill {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #ffffff;
            font-weight: 600;
            font-size: 0.95rem;
            padding: 8px 18px;
            border-radius: 30px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .hero .secondary-text {
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.85);
            margin-top: 20px;
            display: block;
        }

        /* Value Prop Bar */
        .value-prop-bar {
            background-color: #ffffff;
            padding: 30px 0;
            border-bottom: 1px solid #eef2f6;
            box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        }

        .value-prop-grid {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 20px;
            text-align: center;
        }

        .value-prop-item {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            font-size: 1.05rem;
            color: var(--dark-section);
        }

        .value-prop-item span.icon {
            font-size: 1.6rem;
            background: #fff7fa;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(230,115,153,0.15);
        }

        /* Custom Domain Trust Section */
        .domain-trust-section {
            padding: 90px 0;
            background: linear-gradient(135deg, #1a0a2e 0%, #2e1052 100%);
            color: #ffffff;
        }

        .domain-trust-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .domain-trust-content h2 {
            color: #ffffff;
            font-size: 2.8rem;
            margin-bottom: 20px;
        }

        .domain-trust-content p {
            font-size: 1.15rem;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 28px;
        }

        .domain-trust-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 32px;
            border-radius: 20px;
        }

        .trust-stat-box {
            display: flex;
            gap: 20px;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
        }

        .trust-stat {
            flex: 1;
        }

        .trust-stat .stat-number {
            font-family: 'Outfit', sans-serif;
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--primary-pink);
        }

        .trust-stat .stat-label {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
        }

        /* How It Works */
        .how-it-works {
            padding: 100px 0;
            background-color: var(--white);
        }

        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-header h2 {
            font-size: 2.6rem;
            margin-bottom: 16px;
        }

        .steps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 32px;
        }

        .step-card {
            background: var(--soft-pink);
            padding: 40px 32px;
            border-radius: 20px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
        }

        .step-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
        }

        .step-number {
            position: absolute;
            top: 20px;
            left: 20px;
            background: var(--primary-pink);
            color: var(--white);
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .step-icon {
            font-size: 3rem;
            margin-bottom: 24px;
            display: inline-flex;
            background: var(--white);
            width: 80px;
            height: 80px;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            box-shadow: 0 10px 20px rgba(230, 115, 153, 0.1);
        }

        .step-card h3 {
            font-size: 1.4rem;
            margin-bottom: 12px;
        }

        .step-card p {
            color: var(--text-gray);
            font-size: 1rem;
        }

        /* Features Section */
        .features {
            padding: 100px 0;
            background-color: var(--soft-pink);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 32px;
        }

        .feature-item {
            background: var(--white);
            padding: 32px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
            transition: transform 0.3s ease;
        }

        .feature-item:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 16px;
        }

        .feature-item h3 {
            font-size: 1.25rem;
            margin-bottom: 8px;
        }

        .feature-item p {
            color: var(--text-gray);
            font-size: 0.95rem;
            line-height: 1.5;
        }

        /* Pricing Section */
        .pricing {
            padding: 100px 0;
            background-color: var(--white);
        }

        .pricing-grid {
            display: grid;
            grid-template-columns: 1fr 1.05fr;
            gap: 40px;
            max-width: 1000px;
            margin: 0 auto;
            align-items: stretch;
        }

        .pricing-card {
            background: var(--soft-pink);
            border-radius: 24px;
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            border: 2px solid transparent;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .pricing-card.featured {
            background: #ffffff;
            border-color: var(--primary-pink);
            box-shadow: 0 20px 50px rgba(230, 115, 153, 0.2);
            transform: scale(1.02);
        }

        .pricing-card:hover {
            transform: translateY(-5px);
        }

        .plan-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 16px;
        }

        .badge-free {
            background: #e0f2fe;
            color: #0369a1;
        }

        .badge-pro {
            background: var(--primary-pink);
            color: var(--white);
        }

        .pricing-card h3 {
            font-size: 2rem;
            margin-bottom: 8px;
        }

        .pricing-card .tagline {
            color: var(--text-gray);
            font-size: 0.95rem;
            margin-bottom: 24px;
            min-height: 48px;
        }

        .price {
            font-family: 'Outfit', sans-serif;
            font-size: 3.5rem;
            font-weight: 800;
            color: var(--dark-section);
            margin-bottom: 24px;
            line-height: 1;
        }

        .price span {
            font-size: 1rem;
            font-weight: 500;
            color: #888888;
        }

        .pricing-features {
            list-style: none;
            text-align: left;
            margin-bottom: 40px;
        }

        .pricing-features li {
            margin-bottom: 14px;
            padding-left: 28px;
            position: relative;
            font-size: 0.98rem;
        }

        .pricing-features li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: var(--primary-pink);
            font-weight: bold;
            font-size: 1.1rem;
        }

        .pricing-card .btn {
            width: 100%;
            padding: 16px;
            font-size: 1.1rem;
        }
        
        .pricing-note {
            margin-top: 32px;
            font-size: 1rem;
            color: var(--text-gray);
            text-align: center;
        }

        /* ROI Comparison Card */
        .roi-comparison-card {
            max-width: 900px;
            margin: 60px auto 0;
            background: linear-gradient(135deg, #fff7fa 0%, #f3e8ff 100%);
            border: 2px solid #e67399;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(230, 115, 153, 0.12);
        }

        .roi-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 24px;
        }

        .roi-column {
            background: #ffffff;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        }

        /* ─── FEATURE SHOWCASE SLIDER ─── */
        .feature-slider-section {
            padding: 100px 0;
            background: linear-gradient(180deg, #ffffff 0%, #fff7fa 100%);
            overflow: hidden;
        }

        .badge-feature-pill {
            display: inline-block;
            background: #f3e8ff;
            color: #6d28d9;
            font-weight: 700;
            font-size: 0.85rem;
            padding: 6px 16px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 12px;
        }

        .slider-tabs-nav {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 40px;
        }

        .slider-tab-btn {
            background: #ffffff;
            border: 1.5px solid #eef2f6;
            color: var(--text-dark);
            padding: 12px 24px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        }

        .slider-tab-btn:hover {
            border-color: var(--primary-pink);
            color: var(--primary-pink);
            transform: translateY(-2px);
        }

        .slider-tab-btn.active {
            background: var(--dark-section);
            border-color: var(--dark-section);
            color: #ffffff;
            box-shadow: 0 8px 20px rgba(26, 10, 46, 0.25);
        }

        .slider-stage-wrapper {
            position: relative;
            max-width: 1050px;
            margin: 0 auto;
            min-height: 480px;
        }

        .feature-slide-card {
            display: none;
            grid-template-columns: 1fr 1.1fr;
            gap: 40px;
            align-items: center;
            background: #ffffff;
            border-radius: 24px;
            padding: 48px;
            box-shadow: 0 20px 60px rgba(26, 10, 46, 0.08);
            border: 1px solid rgba(230, 115, 153, 0.2);
            animation: fadeInSlide 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .feature-slide-card.active {
            display: grid;
        }

        @keyframes fadeInSlide {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .slide-info h3 {
            font-size: 2.2rem;
            margin-bottom: 16px;
            color: var(--dark-section);
        }

        .slide-info p {
            font-size: 1.1rem;
            color: var(--text-gray);
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .slide-badge-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 32px;
        }

        .slide-pill {
            background: #fff7fa;
            border: 1px solid #fbcfe8;
            color: #be185d;
            font-weight: 600;
            font-size: 0.88rem;
            padding: 6px 14px;
            border-radius: 20px;
        }

        /* Mockup Screens */
        .mockup-preview-container {
            background: linear-gradient(135deg, #1a0a2e 0%, #2e1052 100%);
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
            position: relative;
        }

        .mockup-header-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .mockup-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .mockup-body {
            background: #ffffff;
            border-radius: 12px;
            padding: 20px;
            color: #1f2937;
        }

        /* Slider Controls */
        .slider-controls {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            margin-top: 36px;
        }

        .slider-arrow {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            width: 46px;
            height: 46px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .slider-arrow:hover {
            background: var(--primary-pink);
            color: #ffffff;
            border-color: var(--primary-pink);
            transform: scale(1.08);
        }

        .slider-dots {
            display: flex;
            gap: 8px;
        }

        .slider-dots .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #cbd5e1;
            cursor: pointer;
            transition: all 0.3s;
        }

        .slider-dots .dot.active {
            background: var(--primary-pink);
            width: 30px;
            border-radius: 10px;
        }

        /* Footer CTA */
        .footer-cta {
            background-color: var(--dark-section);
            color: var(--white);
            padding: 100px 0 40px;
            text-align: center;
        }

        .footer-cta h2 {
            color: var(--white);
            font-size: 3rem;
            margin-bottom: 24px;
        }

        .footer-cta p {
            font-size: 1.2rem;
            color: #aaa;
            max-width: 600px;
            margin: 0 auto 40px;
        }

        .copyright {
            margin-top: 80px;
            padding-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #666;
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .domain-trust-container, .pricing-grid, .roi-grid {
                grid-template-columns: 1fr;
            }
            .hero h1 {
                font-size: 2.8rem;
            }
        }

        @media (max-width: 768px) {
            .features-grid {
                grid-template-columns: 1fr;
            }
            .hero {
                padding: 140px 0 80px;
            }
            .hero h1 {
                font-size: 2.2rem;
            }
            .pricing-card {
                padding: 32px 24px;
            }
            .footer-cta h2 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>

    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container">
            <a href="/" class="nav-logo">
                <img src="{{ asset('images/doughmain_logo.png') }}" alt="Doughmain.pro Logo" style="height: 90px; width: auto; object-fit: contain;">
                <span>Doughmain.pro</span>
            </a>
            <div class="nav-links">
                <a href="/login" class="nav-login">Login</a>
                <a href="/register" class="btn btn-primary">Build Your Free Site →</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container fade-in">
            <h1>Build, host, and manage your bakery website completely free.</h1>
            <p class="subheadline">The easiest way for a bakery to get a beautiful website that actually takes orders—for free. Upgrade anytime when you're ready to look like an established brand with your own domain.</p>
            
            <div class="hero-badges">
                <div class="hero-badge-pill">✨ 0% Commissions (Keep 100% of sales)</div>
                <div class="hero-badge-pill">☁️ Free Web Hosting &amp; AI Builder</div>
                <div class="hero-badge-pill">⚡ 0% Platform Fees Forever</div>
            </div>

            <a href="/register" class="btn btn-primary" style="font-size: 1.15rem; padding: 18px 36px;">Build Your Free Bakery Site →</a>
            <span class="secondary-text">No credit card required &middot; Free forever &middot; Upgrade anytime &middot; Cancel anytime</span>
        </div>
    </section>

    <!-- Value Prop Bar -->
    <section class="value-prop-bar">
        <div class="container">
            <div class="value-prop-grid">
                <div class="value-prop-item">
                    <span class="icon">💰</span>
                    <span>Keep 100% Of Your Profit</span>
                </div>
                <div class="value-prop-item">
                    <span class="icon">🎂</span>
                    <span>12-Step Custom Cake Builder</span>
                </div>
                <div class="value-prop-item">
                    <span class="icon">🤖</span>
                    <span>AI Website Copywriter</span>
                </div>
                <div class="value-prop-item">
                    <span class="icon">⚡</span>
                    <span>Free Web Hosting Included</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Custom Domain Authority & Trust Section -->
    <section class="domain-trust-section">
        <div class="container">
            <div class="domain-trust-container fade-in">
                <div class="domain-trust-content">
                    <span style="color:var(--primary-pink); font-weight:700; text-transform:uppercase; letter-spacing:0.1em; font-size:0.9rem;">The Brand Authority Advantage</span>
                    <h2>Ready to Look Like a Real Business?</h2>
                    <p>Clients booking $500+ custom wedding cakes and corporate events don't want to place orders through a temporary social media link. They trust an established business with its own domain: <strong>yourbakery.com</strong>.</p>
                    <p>Build your brand on <em>land you own</em>, not rented space. Upgrade to Pro whenever you're ready to claim your custom domain and command the prices your talent deserves.</p>
                </div>
                <div class="domain-trust-card">
                    <h3 style="color:#fff; font-size:1.5rem; margin-bottom:16px;">Why High-End Bakers Choose Pro:</h3>
                    <ul style="list-style:none; color:rgba(255,255,255,0.9); line-height:2;">
                        <li>🔒 <strong>4x Higher Buyer Trust</strong> with <code>yourbakery.com</code></li>
                        <li>💎 <strong>Command Higher Prices</strong> for custom cake orders</li>
                        <li>🎨 <strong>Unlimited Access to All Themes</strong> (+ all future releases)</li>
                        <li>📊 <strong>Baker CRM &amp; Revenue Analytics</strong></li>
                        <li>⭐ <strong>Client Reviews &amp; Social Proof Showcase</strong></li>
                    </ul>
                    <div class="trust-stat-box">
                        <div class="trust-stat">
                            <div class="stat-number">4x</div>
                            <div class="stat-label">Higher Client Trust</div>
                        </div>
                        <div class="trust-stat">
                            <div class="stat-number">$0</div>
                            <div class="stat-label">Sales Commission</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="how-it-works">
        <div class="container">
            <div class="section-header fade-in">
                <h2>How It Works</h2>
                <p style="color:var(--text-gray); margin-top:8px;">Built from scratch, specifically crafted for bakery owners.</p>
            </div>
            <div class="steps-grid">
                <div class="step-card fade-in">
                    <div class="step-number">1</div>
                    <div class="step-icon">📝</div>
                    <h3>Answer A Few Questions</h3>
                    <p>Share your bakery name, location, specialties, and style preferences in under 2 minutes.</p>
                </div>
                <div class="step-card fade-in" style="transition-delay: 0.1s;">
                    <div class="step-number">2</div>
                    <div class="step-icon">🤖</div>
                    <h3>AI Generates Your Site</h3>
                    <p>Our AI Website Builder creates your homepage, writes high-converting copy, and suggests themes.</p>
                </div>
                <div class="step-card fade-in" style="transition-delay: 0.2s;">
                    <div class="step-number">3</div>
                    <div class="step-icon">🚀</div>
                    <h3>Launch &amp; Take Orders</h3>
                    <p>Publish your site free. Accept custom inquiries, send payment requests, and grow your business!</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ─── INTERACTIVE FEATURE SHOWCASE SLIDER ─── -->
    <section class="feature-slider-section">
        <div class="container">
            <div class="section-header fade-in">
                <span class="badge-feature-pill">🔥 Platform Deep-Dive</span>
                <h2>Features Cooked Up Exclusively For Bakeries</h2>
                <p style="color:var(--text-gray); margin-top:8px;">Click any feature tab below to see live UI previews of how Doughmain elevates your bakery.</p>
            </div>

            <!-- Slider Nav Tabs -->
            <div class="slider-tabs-nav fade-in">
                <button class="slider-tab-btn active" onclick="goToFeatureSlide(0)">📷 Device Gallery</button>
                <button class="slider-tab-btn" onclick="goToFeatureSlide(1)">📝 12-Step Cake Builder</button>
                <button class="slider-tab-btn" onclick="goToFeatureSlide(2)">💳 Invoices &amp; Payments</button>
                <button class="slider-tab-btn" onclick="goToFeatureSlide(3)">🧁 Bakery Menu Catalog</button>
                <button class="slider-tab-btn" onclick="goToFeatureSlide(4)">📊 Baker Customer CRM</button>
                <button class="slider-tab-btn" onclick="goToFeatureSlide(5)">📅 Calendar Lead Times</button>
            </div>

            <!-- Slider Stage -->
            <div class="slider-stage-wrapper fade-in">

                <!-- SLIDE 0: Device Gallery -->
                <div class="feature-slide-card active" data-slide-index="0">
                    <div class="slide-info">
                        <span style="color:#d25a80; font-weight:700; font-size:0.85rem; text-transform:uppercase; letter-spacing:0.06em;">Mobile &amp; Device Uploads</span>
                        <h3>Snap &amp; Publish Photos Live To Your Gallery</h3>
                        <p>No clunky FTP or complex web tools. Snap photos of custom cakes, cupcakes, or dessert boxes directly on your phone or tablet and publish them instantly to your public gallery with one tap.</p>
                        <div class="slide-badge-list">
                            <span class="slide-pill">📱 Mobile Friendly</span>
                            <span class="slide-pill">⚡ Instant Live Updates</span>
                            <span class="slide-pill">🏷️ Category Tagging</span>
                        </div>
                        <a href="/register" class="btn btn-primary">Try Device Gallery Free →</a>
                    </div>
                    <div class="mockup-preview-container">
                        <div class="mockup-header-bar">
                            <div class="mockup-dot" style="background:#ef4444;"></div>
                            <div class="mockup-dot" style="background:#f59e0b;"></div>
                            <div class="mockup-dot" style="background:#10b981;"></div>
                            <span style="color:rgba(255,255,255,0.7); font-size:0.75rem; margin-left: auto;">Baker Admin &gt; Device Gallery</span>
                        </div>
                        <div class="mockup-body">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                                <span style="font-weight:700; font-size:0.95rem;">📱 Device Camera Upload</span>
                                <span style="background:#d1fae5; color:#065f46; font-size:0.75rem; padding:3px 10px; border-radius:12px; font-weight:700;">Live Sync</span>
                            </div>
                            <div style="border:2px dashed #e67399; background:#fff7fa; border-radius:10px; padding:16px; text-align:center; margin-bottom:14px;">
                                <span style="font-size:1.8rem; display:block;">📸</span>
                                <strong style="font-size:0.88rem; color:#1a0a2e;">Tap to select photos from phone or tablet</strong>
                            </div>
                            <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:8px;">
                                <div style="border-radius:8px; overflow:hidden; position:relative; background:#eee;">
                                    <img src="{{ asset('images/elmo_cake_isolated_1784837218834.jpg') }}" alt="Elmo Cake" style="width:100%; height:75px; object-fit:cover;">
                                    <span style="position:absolute; bottom:4px; right:4px; background:rgba(0,0,0,0.6); color:#fff; font-size:0.65rem; padding:1px 4px; border-radius:4px;">Custom</span>
                                </div>
                                <div style="border-radius:8px; overflow:hidden; position:relative; background:#eee;">
                                    <img src="{{ asset('images/cherry_cake_isolated_1784837230270.jpg') }}" alt="Cherry Cake" style="width:100%; height:75px; object-fit:cover;">
                                    <span style="position:absolute; bottom:4px; right:4px; background:rgba(0,0,0,0.6); color:#fff; font-size:0.65rem; padding:1px 4px; border-radius:4px;">Cakes</span>
                                </div>
                                <div style="border-radius:8px; overflow:hidden; position:relative; background:#eee;">
                                    <img src="{{ asset('images/sample_cake_inspiration_1784655186840.jpg') }}" alt="Pastry" style="width:100%; height:75px; object-fit:cover;">
                                    <span style="position:absolute; bottom:4px; right:4px; background:rgba(0,0,0,0.6); color:#fff; font-size:0.65rem; padding:1px 4px; border-radius:4px;">Treats</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SLIDE 1: 12-Step Custom Cake Inquiry Builder -->
                <div class="feature-slide-card" data-slide-index="1">
                    <div class="slide-info">
                        <span style="color:#d25a80; font-weight:700; font-size:0.85rem; text-transform:uppercase; letter-spacing:0.06em;">Intelligent Order Intake</span>
                        <h3>12-Step Custom Cake Builder &amp; Inquiry Form</h3>
                        <p>Eliminate back-and-forth messaging! Guide clients step-by-step through choosing cake sizes, tiers, sponges, fillings, pickup dates, and inspiration image uploads.</p>
                        <div class="slide-badge-list">
                            <span class="slide-pill">🎂 Tier &amp; Size Selectors</span>
                            <span class="slide-pill">🍓 Flavor &amp; Filling Chips</span>
                            <span class="slide-pill">🖼️ Inspiration Uploads</span>
                        </div>
                        <a href="/register" class="btn btn-primary">Try Order Builder Free →</a>
                    </div>
                    <div class="mockup-preview-container">
                        <div class="mockup-header-bar">
                            <div class="mockup-dot" style="background:#ef4444;"></div>
                            <div class="mockup-dot" style="background:#f59e0b;"></div>
                            <div class="mockup-dot" style="background:#10b981;"></div>
                            <span style="color:rgba(255,255,255,0.7); font-size:0.75rem; margin-left: auto;">Storefront &gt; Custom Order Form</span>
                        </div>
                        <div class="mockup-body">
                            <div style="font-weight:700; font-size:0.95rem; margin-bottom:6px;">Step 3 of 12: Select Flavor &amp; Fillings</div>
                            <p style="font-size:0.8rem; color:#666; margin-bottom:12px;">Choose your signature sponge &amp; gourmet buttercream:</p>
                            <div style="display:flex; flex-wrap:wrap; gap:6px; margin-bottom:14px;">
                                <span style="background:#e67399; color:#fff; font-size:0.75rem; font-weight:700; padding:6px 12px; border-radius:16px;">✓ Vanilla Bean Velvet</span>
                                <span style="background:#f3f4f6; color:#374151; font-size:0.75rem; font-weight:600; padding:6px 12px; border-radius:16px;">Rich Dark Chocolate</span>
                                <span style="background:#e67399; color:#fff; font-size:0.75rem; font-weight:700; padding:6px 12px; border-radius:16px;">✓ Strawberry Compote</span>
                                <span style="background:#f3f4f6; color:#374151; font-size:0.75rem; font-weight:600; padding:6px 12px; border-radius:16px;">Salted Caramel</span>
                            </div>
                            <div style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:10px; font-size:0.78rem;">
                                📅 <strong>Selected Event Date:</strong> Saturday, Oct 24th <span style="color:#059669; font-weight:700;">(Available)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SLIDE 2: Invoices & Payments -->
                <div class="feature-slide-card" data-slide-index="2">
                    <div class="slide-info">
                        <span style="color:#d25a80; font-weight:700; font-size:0.85rem; text-transform:uppercase; letter-spacing:0.06em;">0% Commission Sales</span>
                        <h3>Invoices with Venmo, CashApp, Credit Card, &amp; Zelle</h3>
                        <p>Send clean, branded invoices directly to your client's inbox. Set deposit amounts and accept direct payments via Venmo, CashApp, PayPal, Zelle, or Credit Cards with zero fees.</p>
                        <div class="slide-badge-list">
                            <span class="slide-pill">💚 Venmo &amp; CashApp</span>
                            <span class="slide-pill">⚡ Zelle &amp; Credit Cards</span>
                            <span class="slide-pill">💰 0% Platform Fees</span>
                        </div>
                        <a href="/register" class="btn btn-primary">Send Invoices Free →</a>
                    </div>
                    <div class="mockup-preview-container">
                        <div class="mockup-header-bar">
                            <div class="mockup-dot" style="background:#ef4444;"></div>
                            <div class="mockup-dot" style="background:#f59e0b;"></div>
                            <div class="mockup-dot" style="background:#10b981;"></div>
                            <span style="color:rgba(255,255,255,0.7); font-size:0.75rem; margin-left: auto;">Invoice #INV-1048</span>
                        </div>
                        <div class="mockup-body">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                                <strong style="font-size:0.95rem;">🎂 Custom 2-Tier Birthday Cake</strong>
                                <span style="font-weight:800; font-size:1.1rem; color:#1a0a2e;">$145.00</span>
                            </div>
                            <div style="background:#ecfdf5; border:1px solid #a7f3d0; border-radius:8px; padding:8px 12px; font-size:0.78rem; color:#065f46; margin-bottom:14px;">
                                🛡️ 50% Deposit Paid ($72.50) &middot; Remaining Balance Due at Pickup
                            </div>
                            <span style="font-size:0.78rem; color:#666; font-weight:700; display:block; margin-bottom:8px;">Choose Preferred Payment Option:</span>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px;">
                                <div style="background:#008CFF; color:#fff; padding:6px 10px; border-radius:6px; font-size:0.72rem; font-weight:700; text-align:center;">💙 Venmo</div>
                                <div style="background:#00D632; color:#fff; padding:6px 10px; border-radius:6px; font-size:0.72rem; font-weight:700; text-align:center;">💚 CashApp</div>
                                <div style="background:#7414CA; color:#fff; padding:6px 10px; border-radius:6px; font-size:0.72rem; font-weight:700; text-align:center;">💜 Zelle</div>
                                <div style="background:#1a0a2e; color:#fff; padding:6px 10px; border-radius:6px; font-size:0.72rem; font-weight:700; text-align:center;">💳 Credit Card</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SLIDE 3: Bakery Menu Catalog -->
                <div class="feature-slide-card" data-slide-index="3">
                    <div class="slide-info">
                        <span style="color:#d25a80; font-weight:700; font-size:0.85rem; text-transform:uppercase; letter-spacing:0.06em;">Artisanal Product Catalog</span>
                        <h3>Interactive Bakery Menu &amp; Pastry Showcase</h3>
                        <p>Display your signature baked goods, custom cupcakes, wedding tasting boxes, and chocolate treats in a sleek, responsive product showcase menu.</p>
                        <div class="slide-badge-list">
                            <span class="slide-pill">🧁 Pastry Grid</span>
                            <span class="slide-pill">🏷️ Category Badges</span>
                            <span class="slide-pill">✨ Flavor Tagging</span>
                        </div>
                        <a href="/register" class="btn btn-primary">Build Your Menu Free →</a>
                    </div>
                    <div class="mockup-preview-container">
                        <div class="mockup-header-bar">
                            <div class="mockup-dot" style="background:#ef4444;"></div>
                            <div class="mockup-dot" style="background:#f59e0b;"></div>
                            <div class="mockup-dot" style="background:#10b981;"></div>
                            <span style="color:rgba(255,255,255,0.7); font-size:0.75rem; margin-left: auto;">Bakery Menu</span>
                        </div>
                        <div class="mockup-body">
                            <div style="display:flex; gap:8px; margin-bottom:12px; font-size:0.75rem;">
                                <span style="background:#1a0a2e; color:#fff; padding:4px 10px; border-radius:12px; font-weight:700;">All Items</span>
                                <span style="background:#f3f4f6; color:#374151; padding:4px 10px; border-radius:12px;">Custom Cakes</span>
                                <span style="background:#f3f4f6; color:#374151; padding:4px 10px; border-radius:12px;">Cupcakes</span>
                            </div>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                <div style="border:1px solid #e5e7eb; border-radius:8px; padding:10px; background:#fafafa;">
                                    <strong style="font-size:0.82rem; display:block;">Gourmet Cupcake Box</strong>
                                    <span style="font-size:0.75rem; color:#6d28d9; font-weight:700;">$36.00 / Dozen</span>
                                </div>
                                <div style="border:1px solid #e5e7eb; border-radius:8px; padding:10px; background:#fafafa;">
                                    <strong style="font-size:0.82rem; display:block;">Wedding Cake Tier</strong>
                                    <span style="font-size:0.75rem; color:#6d28d9; font-weight:700;">From $250.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SLIDE 4: Baker Customer CRM -->
                <div class="feature-slide-card" data-slide-index="4">
                    <div class="slide-info">
                        <span style="color:#d25a80; font-weight:700; font-size:0.85rem; text-transform:uppercase; letter-spacing:0.06em;">Client Relationship Management</span>
                        <h3>Baker Customer CRM &amp; Lifetime Spend Tracker</h3>
                        <p>Keep track of repeat clients, past order preferences, milestone birthday dates, and total lifetime spend automatically in one organized Baker Admin CRM.</p>
                        <div class="slide-badge-list">
                            <span class="slide-pill">👥 Client History</span>
                            <span class="slide-pill">💎 VIP Spend Tracking</span>
                            <span class="slide-pill">📅 Order Records</span>
                        </div>
                        <a href="/register" class="btn btn-primary">Try Baker CRM Free →</a>
                    </div>
                    <div class="mockup-preview-container">
                        <div class="mockup-header-bar">
                            <div class="mockup-dot" style="background:#ef4444;"></div>
                            <div class="mockup-dot" style="background:#f59e0b;"></div>
                            <div class="mockup-dot" style="background:#10b981;"></div>
                            <span style="color:rgba(255,255,255,0.7); font-size:0.75rem; margin-left: auto;">Baker Dashboard &gt; CRM</span>
                        </div>
                        <div class="mockup-body">
                            <div style="font-weight:700; font-size:0.88rem; margin-bottom:10px;">VIP Client Roster</div>
                            <div style="border-bottom:1px solid #eee; padding-bottom:8px; margin-bottom:8px; display:flex; justify-content:space-between; font-size:0.78rem;">
                                <div>
                                    <strong>Sarah Jenkins</strong> <span style="background:#fef3c7; color:#92400e; font-size:0.65rem; padding:1px 6px; border-radius:4px; font-weight:700;">VIP</span>
                                    <div style="color:#888; font-size:0.7rem;">4 Orders &middot; Last: 2 wks ago</div>
                                </div>
                                <div style="text-align:right; font-weight:800; color:#059669;">$580.00</div>
                            </div>
                            <div style="display:flex; justify-content:space-between; font-size:0.78rem;">
                                <div>
                                    <strong>Michael Torres</strong>
                                    <div style="color:#888; font-size:0.7rem;">2 Orders &middot; Birthday Smash Cake</div>
                                </div>
                                <div style="text-align:right; font-weight:800; color:#059669;">$210.00</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SLIDE 5: Calendar Lead Times -->
                <div class="feature-slide-card" data-slide-index="5">
                    <div class="slide-info">
                        <span style="color:#d25a80; font-weight:700; font-size:0.85rem; text-transform:uppercase; letter-spacing:0.06em;">Booking &amp; Availability</span>
                        <h3>Calendar Lead Times &amp; Blackout Rules</h3>
                        <p>Never get overbooked! Set minimum advance notice (e.g. 3 days notice required), block off vacation dates, and control recurring closed days effortlessly.</p>
                        <div class="slide-badge-list">
                            <span class="slide-pill">⏳ Lead Time Controls</span>
                            <span class="slide-pill">🚫 Blackout Dates</span>
                            <span class="slide-pill">🗓️ Recurring Off Days</span>
                        </div>
                        <a href="/register" class="btn btn-primary">Try Calendar Controls Free →</a>
                    </div>
                    <div class="mockup-preview-container">
                        <div class="mockup-header-bar">
                            <div class="mockup-dot" style="background:#ef4444;"></div>
                            <div class="mockup-dot" style="background:#f59e0b;"></div>
                            <div class="mockup-dot" style="background:#10b981;"></div>
                            <span style="color:rgba(255,255,255,0.7); font-size:0.75rem; margin-left: auto;">Settings &gt; Availability</span>
                        </div>
                        <div class="mockup-body">
                            <div style="font-weight:700; font-size:0.88rem; margin-bottom:10px;">Order Lead Time &amp; Notice Rules</div>
                            <div style="background:#f3f4f6; border-radius:8px; padding:10px; margin-bottom:10px; font-size:0.78rem;">
                                ⏱️ <strong>Minimum Advance Notice:</strong> <span style="color:#e67399; font-weight:700;">3 Days Notice</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; align-items:center; font-size:0.78rem;">
                                <span>🚫 Blocked Vacation Dates:</span>
                                <span style="background:#fee2e2; color:#b91c1c; font-weight:700; font-size:0.7rem; padding:2px 8px; border-radius:6px;">Nov 24 - Nov 28</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Controls (Prev / Next & Dots) -->
            <div class="slider-controls fade-in">
                <button class="slider-arrow" onclick="prevFeatureSlide()">←</button>
                <div class="slider-dots" id="feature-slider-dots">
                    <span class="dot active" onclick="goToFeatureSlide(0)"></span>
                    <span class="dot" onclick="goToFeatureSlide(1)"></span>
                    <span class="dot" onclick="goToFeatureSlide(2)"></span>
                    <span class="dot" onclick="goToFeatureSlide(3)"></span>
                    <span class="dot" onclick="goToFeatureSlide(4)"></span>
                    <span class="dot" onclick="goToFeatureSlide(5)"></span>
                </div>
                <button class="slider-arrow" onclick="nextFeatureSlide()">→</button>
            </div>
        </div>
    </section>

    <!-- Features Grid -->
    <section class="features">
        <div class="container">
            <div class="section-header fade-in">
                <h2>Everything Your Bakery Needs To Succeed</h2>
                <p style="color:var(--text-gray); margin-top:8px;">Turn casual visitors into loyal custom cake clients.</p>
            </div>
            <div class="features-grid">
                <div class="feature-item fade-in">
                    <div class="feature-icon">🎨</div>
                    <h3>Artisanal Themes</h3>
                    <p>Core themes on Starter (Free), or unlock Unlimited Access to All Themes with Pro.</p>
                    <span style="font-size:0.75rem; font-weight:700; color:#6d28d9; background:#f3e8ff; padding:4px 12px; border-radius:12px; margin-top:12px; display:inline-block;">Starter &amp; Pro Plans</span>
                </div>
                <div class="feature-item fade-in" style="transition-delay: 0.1s;">
                    <div class="feature-icon">🛒</div>
                    <h3>Custom Cake Inquiry Builder</h3>
                    <p>12-step custom cake form with flavor options, tier selections, calendar booking, and inspiration photo uploads.</p>
                    <span style="font-size:0.75rem; font-weight:700; color:#065f46; background:#d1fae5; padding:4px 12px; border-radius:12px; margin-top:12px; display:inline-block;">Free Forever</span>
                </div>
                <div class="feature-item fade-in" style="transition-delay: 0.2s;">
                    <div class="feature-icon">📧</div>
                    <h3>Invoices &amp; 0% Commission Payments</h3>
                    <p>Send custom quotes and accept Venmo, CashApp, Zelle, or PayPal with ZERO platform fees.</p>
                    <span style="font-size:0.75rem; font-weight:700; color:#065f46; background:#d1fae5; padding:4px 12px; border-radius:12px; margin-top:12px; display:inline-block;">Free Forever</span>
                </div>
                <div class="feature-item fade-in">
                    <div class="feature-icon">👥</div>
                    <h3>Baker Customer CRM</h3>
                    <p>Track repeat buyers, order histories, and total client spend automatically in your dashboard.</p>
                    <span style="font-size:0.75rem; font-weight:700; color:#92400e; background:#fef3c7; padding:4px 12px; border-radius:12px; margin-top:12px; display:inline-block;">Pro Feature 🌟</span>
                </div>
                <div class="feature-item fade-in" style="transition-delay: 0.1s;">
                    <div class="feature-icon">⭐</div>
                    <h3>Client Reviews &amp; Testimonials</h3>
                    <p>Showcase glowing customer reviews and rating stars directly on your storefront to boost conversions.</p>
                    <span style="font-size:0.75rem; font-weight:700; color:#92400e; background:#fef3c7; padding:4px 12px; border-radius:12px; margin-top:12px; display:inline-block;">Pro Feature 🌟</span>
                </div>
                <div class="feature-item fade-in" style="transition-delay: 0.2s;">
                    <div class="feature-icon">🌐</div>
                    <h3>Custom Domain Connection</h3>
                    <p>Use <code>yourbakery.doughmain.pro</code> free, or connect your own domain (<code>yourbakery.com</code>).</p>
                    <span style="font-size:0.75rem; font-weight:700; color:#92400e; background:#fef3c7; padding:4px 12px; border-radius:12px; margin-top:12px; display:inline-block;">Pro Feature 🌟</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="pricing">
        <div class="container">
            <div class="section-header fade-in">
                <h2>Simple, Transparent Product Tiers</h2>
                <p style="color:var(--text-gray); margin-top:8px;">Start free. Build your bakery. Upgrade anytime when your business grows.</p>
            </div>
            
            <div class="pricing-grid fade-in">
                <!-- TIER 1: STARTER (FREE) -->
                <div class="pricing-card">
                    <div>
                        <span class="plan-badge badge-free">Free Forever</span>
                        <h3>Starter</h3>
                        <p class="tagline">Everything you need to build your bakery website, list products, and take custom orders completely free.</p>
                        <div class="price">$0<span>/forever</span></div>
                        <ul class="pricing-features">
                            <li><code>yourbakery.doughmain.pro</code> free subdomain</li>
                            <li>Free Web Hosting Included</li>
                            <li>AI Website Builder &amp; Auto Copywriting</li>
                            <li>3 Core Bakery Themes</li>
                            <li>12-Step Custom Cake Inquiry Form</li>
                            <li>Pastry Product &amp; Menu Catalog</li>
                            <li>Invoice Creation &amp; Direct Payment Requests</li>
                            <li><strong>0% Platform Commissions (Keep 100% of Sales)</strong></li>
                        </ul>
                    </div>
                    <a href="/register" class="btn btn-light" style="margin-top:24px;">Build Your Free Site</a>
                </div>

                <!-- TIER 2: PRO ($29/MO) - FEATURED -->
                <div class="pricing-card featured">
                    <div>
                        <span class="plan-badge badge-pro">Recommended 🔥</span>
                        <h3>Pro</h3>
                        <p class="tagline">Look like an established business with your custom domain (yourbakery.com) and professional tools.</p>
                        <div class="price">$29<span>/month</span></div>
                        <ul class="pricing-features">
                            <li><strong>Everything in Starter, plus:</strong></li>
                            <li><strong>Custom Domain Connection (<code>yourbakery.com</code>)</strong></li>
                            <li><strong>Unlimited Access to All Themes (+ All Future Releases)</strong></li>
                            <li>Baker Customer CRM &amp; Lifetime Spend Analytics</li>
                            <li>Advanced Calendar Rules (Lead times &amp; Blackout dates)</li>
                            <li>Client Reviews &amp; Social Proof Showcase</li>
                            <li>Remove "Powered by Doughmain" (White Labeling)</li>
                            <li>Priority Support &amp; Faster AI Generations</li>
                        </ul>
                    </div>
                    <a href="/register" class="btn btn-primary" style="margin-top:24px;">Start Free &rarr; Upgrade Anytime</a>
                </div>
            </div>

            <p class="pricing-note fade-in">✨ <strong>Upgrade anytime. Cancel anytime.</strong> No lock-in contracts or hidden fees.</p>

            <!-- ROI Comparison Box -->
            <div class="roi-comparison-card fade-in">
                <div style="text-align:center;">
                    <span style="color:#d25a80; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; font-size:0.85rem;">Why $29/Month Is A No-Brainer</span>
                    <h3 style="font-size:1.8rem; margin-top:4px;">One Single Birthday Cake Order Pays For Your Month of Pro</h3>
                    <p style="color:var(--text-gray); font-size:1rem; max-width:650px; margin:8px auto 0;">Sell just one custom cake a month (or 1 wedding cake a year) and Doughmain Pro is 100% paid for!</p>
                </div>
                <div class="roi-grid">
                    <div class="roi-column">
                        <h4 style="color:#991b1b; font-size:1.1rem; margin-bottom:12px;">❌ Traditional Software Stack</h4>
                        <ul style="list-style:none; font-size:0.92rem; line-height:1.8; color:#555;">
                            <li>• Web Design Agency Quote: <strong>$2,500 – $5,000</strong></li>
                            <li>• Separate Web Hosting: <strong>$20/mo</strong></li>
                            <li>• Forms &amp; File Upload Plugin: <strong>$15/mo</strong></li>
                            <li>• Booking &amp; Calendar Tool: <strong>$20/mo</strong></li>
                            <li>• Customer CRM App: <strong>$25/mo</strong></li>
                            <li style="border-top:1px solid #eee; padding-top:8px; margin-top:8px; font-weight:700; color:#111;">Total Cost: $3,500+ upfront + $80+/mo</li>
                        </ul>
                    </div>
                    <div class="roi-column" style="border:2px solid #e67399; background:#fff7fa;">
                        <h4 style="color:#065f46; font-size:1.1rem; margin-bottom:12px;">✅ Doughmain Pro</h4>
                        <ul style="list-style:none; font-size:0.92rem; line-height:1.8; color:#1a0a2e;">
                            <li>• Free AI Website Builder &amp; Hosting</li>
                            <li>• 12-Step Custom Cake Builder Included</li>
                            <li>• <code>yourbakery.com</code> Custom Domain Included</li>
                            <li>• All Themes &amp; Future Theme Releases</li>
                            <li>• Built-in Baker CRM &amp; Client Reviews</li>
                            <li style="border-top:1px solid #e67399; padding-top:8px; margin-top:8px; font-weight:700; color:#e67399; font-size:1.05rem;">Only $29/mo (Less than 1 cake profit!)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer CTA -->
    <footer class="footer-cta">
        <div class="container">
            <div class="fade-in">
                <h2>Build your bakery website today.</h2>
                <p>Give your brand the perfect environment to rise online. Build, host, and manage your website completely free.</p>
                <a href="/register" class="btn btn-light" style="font-size: 1.15rem; padding: 18px 36px;">Build Your Free Bakery Site →</a>
            </div>
            <div class="copyright fade-in" style="line-height: 1.8;">
                &copy; 2026 Doughmain Pro Platform, a <a href="https://daystardigital.co" style="color:inherit; text-decoration:underline;">Daystar Digital LLC</a> product. &middot; <a href="/legal" style="color:inherit; text-decoration:underline; font-weight: 600;">Legal Center &amp; Policies</a> &middot; <a href="/legal/terms" style="color:inherit; text-decoration:underline;">Terms of Service</a> &middot; <a href="/legal/privacy" style="color:inherit; text-decoration:underline;">Privacy Policy</a> &middot; <a href="/legal/acceptable-use" style="color:inherit; text-decoration:underline;">Acceptable Use</a>
            </div>
        </div>
    </footer>

    <!-- Scripts for animations & Feature Slider -->
    <script>
        // Feature Slider Logic
        let currentFeatureIndex = 0;
        const totalFeatureSlides = 6;
        let featureAutoSlideTimer = null;

        function goToFeatureSlide(index) {
            currentFeatureIndex = index;
            
            document.querySelectorAll('.feature-slide-card').forEach((slide, i) => {
                slide.classList.toggle('active', i === index);
            });
            
            document.querySelectorAll('.slider-tab-btn').forEach((tab, i) => {
                tab.classList.toggle('active', i === index);
            });
            
            document.querySelectorAll('#feature-slider-dots .dot').forEach((dot, i) => {
                dot.classList.toggle('active', i === index);
            });

            resetFeatureAutoSlide();
        }

        function nextFeatureSlide() {
            let nextIndex = (currentFeatureIndex + 1) % totalFeatureSlides;
            goToFeatureSlide(nextIndex);
        }

        function prevFeatureSlide() {
            let prevIndex = (currentFeatureIndex - 1 + totalFeatureSlides) % totalFeatureSlides;
            goToFeatureSlide(prevIndex);
        }

        function startFeatureAutoSlide() {
            featureAutoSlideTimer = setInterval(() => {
                let nextIndex = (currentFeatureIndex + 1) % totalFeatureSlides;
                goToFeatureSlide(nextIndex);
            }, 6000);
        }

        function resetFeatureAutoSlide() {
            if (featureAutoSlideTimer) clearInterval(featureAutoSlideTimer);
            startFeatureAutoSlide();
        }

        document.addEventListener("DOMContentLoaded", () => {
            const observerOptions = {
                root: null,
                rootMargin: "0px",
                threshold: 0.1
            };

            const observer = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add("visible");
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            document.querySelectorAll(".fade-in").forEach(element => {
                observer.observe(element);
            });
            
            setTimeout(() => {
                const heroElements = document.querySelectorAll(".hero .fade-in");
                heroElements.forEach(el => el.classList.add("visible"));
            }, 100);

            startFeatureAutoSlide();
        });
    </script>
</body>
</html>
