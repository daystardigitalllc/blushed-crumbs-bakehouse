<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DoughMain — AI-Powered Bakery Website Builder</title>
    <meta name="description" content="Create your bakery website with AI in minutes. No coding required. Manage products, accept orders, and grow your bakery business online.">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    
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
            background: linear-gradient(135deg, rgba(26, 10, 46, 0.78) 0%, rgba(64, 24, 41, 0.72) 100%);
            z-index: 1;
        }

        .hero .container {
            position: relative;
            z-index: 2;
        }

        .hero h1 {
            font-size: 4rem;
            font-weight: 800;
            max-width: 850px;
            margin: 0 auto 24px;
            letter-spacing: -0.02em;
            color: #ffffff;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
        }

        .hero p.subheadline {
            font-size: 1.25rem;
            color: rgba(255, 255, 255, 0.9);
            max-width: 680px;
            margin: 0 auto 40px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.4);
        }

        .hero .secondary-text {
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.8);
            margin-top: 20px;
            display: block;
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
            font-size: 2.5rem;
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
        
        .step-number {
            position: absolute;
            top: -15px;
            right: -15px;
            background: var(--deep-purple);
            color: var(--white);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 1.2rem;
            box-shadow: 0 4px 10px rgba(109, 40, 217, 0.3);
        }

        .step-card h3 {
            font-size: 1.5rem;
            margin-bottom: 16px;
        }

        .step-card p {
            color: var(--text-gray);
        }

        /* Features */
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
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            transition: transform 0.3s;
            border: 1px solid rgba(0,0,0,0.02);
        }

        .feature-item:hover {
            transform: translateY(-5px);
            border-color: rgba(230, 115, 153, 0.2);
        }

        .feature-icon {
            font-size: 2rem;
            margin-bottom: 20px;
        }

        .feature-item h3 {
            font-size: 1.25rem;
            margin-bottom: 12px;
            color: var(--deep-purple);
        }

        .feature-item p {
            color: var(--text-gray);
            font-size: 0.95rem;
        }

        /* Pricing */
        .pricing {
            padding: 120px 0;
            background-color: var(--white);
            position: relative;
        }

        /* Pricing Grid */
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 32px;
            margin: 40px auto 0;
            max-width: 850px;
            align-items: stretch;
        }

        .pricing-card {
            background-color: var(--dark-section);
            color: var(--white);
            border-radius: 24px;
            padding: 40px 32px;
            text-align: left;
            position: relative;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .pricing-card.featured {
            border: 2px solid var(--primary-pink);
        }
        
        .pricing-card.featured::before {
            content: '';
            position: absolute;
            top: -3px; left: -3px; right: -3px; bottom: -3px;
            background: linear-gradient(45deg, var(--primary-pink), var(--deep-purple), var(--primary-pink));
            border-radius: 27px;
            z-index: -1;
            animation: borderGlow 3s ease-in-out infinite alternate;
        }

        .plan-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }

        .badge-free { background: rgba(255, 255, 255, 0.15); color: #fff; }
        .badge-pro { background: var(--primary-pink); color: #fff; }
        .badge-master { background: var(--deep-purple); color: #fff; }

        .pricing-card h3 {
            color: var(--white);
            font-size: 1.8rem;
            margin-bottom: 6px;
        }

        .pricing-card .tagline {
            font-size: 0.9rem;
            color: #aaa;
            margin-bottom: 20px;
            min-height: 42px;
            line-height: 1.4;
        }

        .price {
            font-size: 3.2rem;
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            margin-bottom: 24px;
            color: var(--primary-pink);
            line-height: 1;
        }

        .price span {
            font-size: 1rem;
            font-weight: 500;
            color: #ccc;
        }

        .pricing-features {
            list-style: none;
            text-align: left;
            margin-bottom: 40px;
        }

        .pricing-features li {
            margin-bottom: 16px;
            padding-left: 32px;
            position: relative;
            font-size: 1.05rem;
        }

        .pricing-features li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: var(--primary-pink);
            font-weight: bold;
            font-size: 1.2rem;
        }

        .pricing-card .btn {
            width: 100%;
            padding: 16px;
            font-size: 1.1rem;
        }
        
        .pricing-note {
            margin-top: 24px;
            font-size: 0.95rem;
            color: var(--text-gray);
            text-align: center;
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
            .hero h1 {
                font-size: 3rem;
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
                font-size: 2.5rem;
            }
            .pricing-card {
                padding: 32px 24px;
            }
            .footer-cta h2 {
                font-size: 2rem;
            }
            .nav-links {
                gap: 16px;
            }
        }
    </style>
</head>
<body>

    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container">
            <a href="/" class="nav-logo">
                <img src="{{ asset('images/doughmain_logo.png') }}" alt="Doughmain.pro Logo" style="height: 46px; width: auto; object-fit: contain;">
                <span>Doughmain.pro</span>
            </a>
            <div class="nav-links">
                <a href="/login" class="nav-login">Login</a>
                <a href="/register" class="btn btn-primary">Get Started Free</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container fade-in">
            <h1>The Only Website Builder Your Bakery Will Ever Knead.</h1>
            <p class="subheadline">We handle the code. You handle the dough. Claim your slice of the web with a professional Doughmain—AI-powered, zero coding, built specifically for bakers.</p>
            <a href="/register" class="btn btn-primary" style="font-size: 1.1rem; padding: 16px 32px;">Start Baking Your Site →</a>
            <span class="secondary-text">No credit card required &middot; Free to start &middot; Proof your site in minutes</span>
        </div>
    </section>

    <!-- How It Works -->
    <section class="how-it-works">
        <div class="container">
            <div class="section-header fade-in">
                <h2>How It Works</h2>
                <p style="color:var(--text-gray); margin-top:8px;">Built from scratch, just like your best recipes.</p>
            </div>
            <div class="steps-grid">
                <div class="step-card fade-in">
                    <div class="step-number">1</div>
                    <div class="step-icon">📝</div>
                    <h3>Tell Us What You Knead</h3>
                    <p>Share your bakery name, specialties, and style. Everything you knead to get started.</p>
                </div>
                <div class="step-card fade-in" style="transition-delay: 0.1s;">
                    <div class="step-number">2</div>
                    <div class="step-icon">🤖</div>
                    <h3>Watch It Proof &amp; Rise with AI</h3>
                    <p>Our AI Website Builder proofs your digital presence, writes your copy, and bakes your site layout.</p>
                </div>
                <div class="step-card fade-in" style="transition-delay: 0.2s;">
                    <div class="step-number">3</div>
                    <div class="step-icon">🚀</div>
                    <h3>Fresh Out Of The Oven</h3>
                    <p>Pick a theme, add your pastries, and publish. Your bakery website is hot and ready!</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Grid -->
    <section class="features">
        <div class="container">
            <div class="section-header fade-in">
                <h2>Fully Baked Features for Brilliant Bakers</h2>
                <p style="color:var(--text-gray); margin-top:8px;">Don't let your online presence go stale. Get a fresh Doughmain.</p>
            </div>
            <div class="features-grid">
                <div class="feature-item fade-in">
                    <div class="feature-icon">🎨</div>
                    <h3>Artisanal Themes</h3>
                    <p>Choose from 3 core themes on Free, or unlock all themes with Pro.</p>
                    <span style="font-size:0.75rem; font-weight:700; color:#6d28d9; background:#f3e8ff; padding:3px 10px; border-radius:12px; margin-top:10px; display:inline-block;">Free &amp; Pro Plans</span>
                </div>
                <div class="feature-item fade-in" style="transition-delay: 0.1s;">
                    <div class="feature-icon">🛒</div>
                    <h3>Custom Cake Builder</h3>
                    <p>Cake order intake form with flavors, fillings, date selection, and photo uploads.</p>
                    <span style="font-size:0.75rem; font-weight:700; color:#065f46; background:#d1fae5; padding:3px 10px; border-radius:12px; margin-top:10px; display:inline-block;">Free Forever</span>
                </div>
                <div class="feature-item fade-in" style="transition-delay: 0.2s;">
                    <div class="feature-icon">📧</div>
                    <h3>Invoices &amp; Quick Payments</h3>
                    <p>Send custom invoices and accept Venmo, CashApp, Zelle, or PayPal with zero platform fees.</p>
                    <span style="font-size:0.75rem; font-weight:700; color:#065f46; background:#d1fae5; padding:3px 10px; border-radius:12px; margin-top:10px; display:inline-block;">Free Forever</span>
                </div>
                <div class="feature-item fade-in">
                    <div class="feature-icon">👥</div>
                    <h3>Baker Customer CRM</h3>
                    <p>Track repeat buyers, full order histories, and lifetime spend automatically in one dashboard.</p>
                    <span style="font-size:0.75rem; font-weight:700; color:#92400e; background:#fef3c7; padding:3px 10px; border-radius:12px; margin-top:10px; display:inline-block;">Pro Feature 🌟</span>
                </div>
                <div class="feature-item fade-in" style="transition-delay: 0.1s;">
                    <div class="feature-icon">⭐</div>
                    <h3>Client Review Proofing</h3>
                    <p>Showcase glowing client reviews and testimonials directly on your live storefront.</p>
                    <span style="font-size:0.75rem; font-weight:700; color:#92400e; background:#fef3c7; padding:3px 10px; border-radius:12px; margin-top:10px; display:inline-block;">Pro Feature 🌟</span>
                </div>
                <div class="feature-item fade-in" style="transition-delay: 0.2s;">
                    <div class="feature-icon">🌐</div>
                    <h3>Custom Domain Connection</h3>
                    <p>Use yourbakery.doughmain.pro free, or upgrade to Pro to connect your custom domain (yourbakery.com).</p>
                    <span style="font-size:0.75rem; font-weight:700; color:#92400e; background:#fef3c7; padding:3px 10px; border-radius:12px; margin-top:10px; display:inline-block;">Pro Feature 🌟</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="pricing">
        <div class="container">
            <div class="section-header fade-in">
                <h2>Simple, Transparent Pricing</h2>
                <p style="color:var(--text-gray); margin-top:8px;">Everything you need to grow your bakery online. The proof is in the platform.</p>
            </div>
            
            <div class="pricing-grid fade-in">
                <!-- TIER 1: FREE BAKER ($0) -->
                <div class="pricing-card">
                    <div>
                        <span class="plan-badge badge-free">Free Forever</span>
                        <h3>Free Baker</h3>
                        <p class="tagline">Run your entire bakery business 100% free with zero monthly costs.</p>
                        <div class="price">$0<span>/forever</span></div>
                        <ul class="pricing-features">
                            <li><code>yourbakery.doughmain.pro</code> subdomain</li>
                            <li>3 Core Themes (Rustic, Modern, Playful)</li>
                            <li>Full Invoices &amp; Payment Requests (Venmo, CashApp, Zelle, PayPal)</li>
                            <li>AI Website Builder</li>
                            <li>Online Custom Cake Order Form</li>
                            <li>Pastry &amp; Menu Product Catalog</li>
                            <li>Standard Online Order Intake</li>
                            <li>Zero Monthly Fees or Commissions</li>
                        </ul>
                    </div>
                    <a href="/register" class="btn btn-light" style="margin-top:24px;">Start Free Forever</a>
                </div>

                <!-- TIER 2: PRO BAKER ($29/MO) - FEATURED -->
                <div class="pricing-card featured">
                    <div>
                        <span class="plan-badge badge-pro">Most Popular 🔥</span>
                        <h3>Pro Baker</h3>
                        <p class="tagline">Connect your custom domain, unlock all themes, CRM analytics, and white-labeling.</p>
                        <div class="price">$29<span>/month</span></div>
                        <ul class="pricing-features">
                            <li><strong>Everything in Free, plus:</strong></li>
                            <li>Custom Domain Connection (<code>yourbakery.com</code>)</li>
                            <li>Access to All Themes (+ All Future Releases)</li>
                            <li>Baker Customer CRM &amp; Lifetime Spend Analytics</li>
                            <li>Advanced Calendar Rules (Lead times &amp; Blackout dates)</li>
                            <li>Client Reviews &amp; Social Proofing</li>
                            <li>Remove "Powered by Doughmain" (White-Label)</li>
                        </ul>
                    </div>
                    <a href="/register" class="btn btn-primary" style="margin-top:24px;">Start 14-Day Free Trial</a>
                </div>
            </div>

            <p class="pricing-note fade-in">Switch or cancel plans anytime in your Baker Admin Dashboard.</p>
        </div>
    </section>

    <!-- Footer CTA -->
    <footer class="footer-cta">
        <div class="container">
            <div class="fade-in">
                <h2>Stop loafing around with generic site builders.</h2>
                <p>Give your brand the perfect environment to rise online. Create your Doughmain today.</p>
                <a href="/register" class="btn btn-light" style="font-size: 1.1rem; padding: 16px 32px;">Create Your Free Account</a>
            </div>
            <div class="copyright fade-in">
                &copy; 2026 Daystar Pro Platform. Doughmain.pro is a Daystar Digital product.
            </div>
        </div>
    </footer>

    <!-- Scripts for animations -->
    <script>
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
                        observer.unobserve(entry.target); // Unobserve after animation triggers
                    }
                });
            }, observerOptions);

            document.querySelectorAll(".fade-in").forEach(element => {
                observer.observe(element);
            });
            
            // Trigger hero animation immediately
            setTimeout(() => {
                const heroElements = document.querySelectorAll(".hero .fade-in");
                heroElements.forEach(el => el.classList.add("visible"));
            }, 100);
        });
    </script>
</body>
</html>
