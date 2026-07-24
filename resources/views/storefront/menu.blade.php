<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu &amp; Pricing | {{ $tenant->name ?? 'Blushed Crumbs Bakehouse' }}</title>
    <!-- Favicon -->
    @if(isset($tenant) && $tenant->logo_path)
        <link rel="icon" href="{{ asset($tenant->logo_path) }}">
    @else
        <link rel="icon" href="{{ asset('images/favicon.png') }}">
    @endif
    <meta name="description" content="Explore the menu, cake sizes, dessert options, custom flavors, fillings, and pricing for {{ $tenant->name ?? 'our bakery' }}.">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700;800&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        .menu-hero-section {
            background: var(--theme-section-bg, var(--pink-bg, #fff7fa));
            text-align: center;
            padding: 100px 20px 70px 20px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        .menu-hero-subtitle {
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 3px;
            font-weight: 700;
            color: var(--primary, #e67399);
            margin-bottom: 12px;
            display: block;
        }
        .menu-hero-title {
            font-family: var(--theme-heading-font, 'Poppins', sans-serif);
            font-size: 3.5rem;
            font-weight: 800;
            color: var(--dark-text, #2c2419);
            line-height: 1.15;
            max-width: 800px;
            margin: 0 auto 16px auto;
        }
        .menu-container-grid {
            max-width: 1180px;
            margin: 0 auto;
            padding: 70px 25px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 35px;
        }
        .menu-category-card {
            background: var(--theme-card-bg, #ffffff);
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 20px;
            padding: 32px 28px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            position: relative;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .menu-category-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.08);
        }
        .menu-category-header {
            border-bottom: 2px dashed var(--primary, #e67399);
            padding-bottom: 14px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .menu-category-header h3 {
            font-family: var(--theme-heading-font, 'Poppins', sans-serif);
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--dark-text, #2c2419);
            margin: 0;
        }
        .menu-item-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 14px;
            font-size: 0.98rem;
        }
        .menu-item-name {
            font-weight: 600;
            color: var(--dark-text, #2c2419);
        }
        .menu-item-dots {
            flex: 1;
            border-bottom: 1px dotted rgba(0,0,0,0.2);
            margin: 0 10px;
        }
        .menu-item-price {
            font-weight: 800;
            color: var(--primary, #e67399);
            font-size: 1.05rem;
        }
        .menu-item-servings {
            font-size: 0.8rem;
            color: #777;
            margin-left: 6px;
            font-weight: 500;
        }
        .flavor-pill-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .flavor-pill {
            background: var(--pink-bg, #fff7fa);
            border: 1px solid rgba(230, 115, 153, 0.25);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--dark-text, #2c2419);
        }
        .image-menu-wrapper {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 20px;
            text-align: center;
        }
        .image-menu-wrapper img {
            width: 100%;
            max-width: 850px;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.12);
            border: 1px solid rgba(0,0,0,0.08);
            cursor: pointer;
        }
    </style>
</head>
<body class="theme-{{ $tenant->theme_id ?? 'sweet_elegant' }}">

<!-- HEADER NAVIGATION -->
<header class="site-header">
    <div class="header-container">
        <a href="{{ route('storefront.index') }}" class="logo">
            @if(!empty($tenant->logo_path))
                <img src="{{ asset($tenant->logo_path) }}" alt="{{ $tenant->name }} Logo" style="max-height:52px; width:auto; object-fit:contain;">
            @else
                <span style="font-family:'Outfit',sans-serif; font-weight:700; font-size:1.4rem; color:var(--dark-text, #2c2419);">🧁 {{ $tenant->name }}</span>
            @endif
        </a>
        <nav class="nav-links">
            <a href="{{ route('storefront.index') }}">Home</a>
            <a href="{{ route('storefront.about') }}">About</a>
            <a href="{{ route('storefront.menu') }}" class="active">Menu</a>
            <a href="{{ route('storefront.gallery') }}">Gallery</a>
            <a href="#" onclick="openOrderModal()" class="nav-order-btn">Order Now</a>
        </nav>
    </div>
</header>

<div id="menu-page-view">
    <!-- HERO SECTION -->
    <section class="menu-hero-section">
        <span class="menu-hero-subtitle">FRESH OUT OF THE OVEN</span>
        <h1 class="menu-hero-title">Our Menu &amp; Pricing</h1>
        <p style="font-size:1.05rem; color:var(--dark-text); opacity:0.85; max-width:650px; margin:0 auto 24px auto;">Explore our artisanal cakes, party packs, custom flavors, and gourmet dessert offerings.</p>
        <button onclick="openOrderModal()" class="btn btn-primary" style="padding:12px 32px; font-size:1.05rem; border-radius:30px;">
            Order Custom Creation 🎂
        </button>
    </section>

    @php
        $menuData = $tenant->site_content['menu'] ?? [];
        $menuType = $menuData['menu_type'] ?? 'both';
        $hasImage = !empty($menuData['menu_image_path']);
        $customText = $menuData['menu_text'] ?? '';
    @endphp

    <!-- UPLOADED IMAGE MENU DISPLAY -->
    @if(($menuType === 'image' || $menuType === 'both') && $hasImage)
        <section class="image-menu-wrapper">
            <h2 style="font-family:var(--theme-heading-font); color:var(--dark-text); margin-bottom:16px;">📄 Official Bakery Menu Card</h2>
            <p style="color:#666; font-size:0.92rem; margin-bottom:20px;">Click the menu below to view full-screen</p>
            <a href="{{ asset($menuData['menu_image_path']) }}" target="_blank">
                <img src="{{ asset($menuData['menu_image_path']) }}" alt="{{ $tenant->name }} Menu Card">
            </a>
        </section>
    @endif

    <!-- FORMATTED THEME MENU GRID -->
    @if($menuType === 'text' || $menuType === 'both' || !$hasImage)
        <div class="menu-container-grid">
            
            <!-- CAKES & SIZES CARD -->
            <div class="menu-category-card">
                <div class="menu-category-header">
                    <h3>🎂 Custom Celebration Cakes</h3>
                    <span style="font-size:1.3rem;">✨</span>
                </div>
                <div class="menu-item-row"><span class="menu-item-name">4" Cake</span><span class="menu-item-dots"></span><span class="menu-item-price">$45</span><span class="menu-item-servings">(2–4 servings)</span></div>
                <div class="menu-item-row"><span class="menu-item-name">6" Cake</span><span class="menu-item-dots"></span><span class="menu-item-price">$65</span><span class="menu-item-servings">(8–10 servings)</span></div>
                <div class="menu-item-row"><span class="menu-item-name">7" Cake</span><span class="menu-item-dots"></span><span class="menu-item-price">$85</span><span class="menu-item-servings">(10–12 servings)</span></div>
                <div class="menu-item-row"><span class="menu-item-name">8" Cake</span><span class="menu-item-dots"></span><span class="menu-item-price">$95</span><span class="menu-item-servings">(15–20 servings)</span></div>
                <div class="menu-item-row"><span class="menu-item-name">9" Cake</span><span class="menu-item-dots"></span><span class="menu-item-price">$115</span><span class="menu-item-servings">(20–25 servings)</span></div>
                <div class="menu-item-row"><span class="menu-item-name">10" Cake</span><span class="menu-item-dots"></span><span class="menu-item-price">$140</span><span class="menu-item-servings">(25–35 servings)</span></div>
                <div class="menu-item-row"><span class="menu-item-name">1/4 Sheet Cake</span><span class="menu-item-dots"></span><span class="menu-item-price">$120</span><span class="menu-item-servings">(20–25 servings)</span></div>
            </div>

            <!-- DESSERTS BY THE DOZEN -->
            <div class="menu-category-card">
                <div class="menu-category-header">
                    <h3>🧁 Desserts By The Dozen</h3>
                    <span style="font-size:1.3rem;">🍓</span>
                </div>
                <div class="menu-item-row"><span class="menu-item-name">Dozen Gourmet Cupcakes</span><span class="menu-item-dots"></span><span class="menu-item-price">$35</span></div>
                <div class="menu-item-row"><span class="menu-item-name">Dozen Cakesicles</span><span class="menu-item-dots"></span><span class="menu-item-price">$50</span></div>
                <div class="menu-item-row"><span class="menu-item-name">Dozen Chocolate Covered Strawberries</span><span class="menu-item-dots"></span><span class="menu-item-price">$45</span></div>
                <div class="menu-item-row"><span class="menu-item-name">Dozen Chocolate Covered Oreos</span><span class="menu-item-dots"></span><span class="menu-item-price">$45</span></div>
                <div class="menu-item-row"><span class="menu-item-name">Dozen Jumbo Marshmallows</span><span class="menu-item-dots"></span><span class="menu-item-price">$30</span></div>
                <div class="menu-item-row"><span class="menu-item-name">Dozen Pretzel Rods</span><span class="menu-item-dots"></span><span class="menu-item-price">$30</span></div>
            </div>

            <!-- TIERED & WEDDING CAKES -->
            <div class="menu-category-card">
                <div class="menu-category-header">
                    <h3>💒 Tiered &amp; Wedding Cakes</h3>
                    <span style="font-size:1.3rem;">💍</span>
                </div>
                <div class="menu-item-row"><span class="menu-item-name">Small 2 Tier (4", 6")</span><span class="menu-item-dots"></span><span class="menu-item-price">$160</span><span class="menu-item-servings">(12–16)</span></div>
                <div class="menu-item-row"><span class="menu-item-name">Medium 2 Tier (6", 8")</span><span class="menu-item-dots"></span><span class="menu-item-price">$225</span><span class="menu-item-servings">(20–30)</span></div>
                <div class="menu-item-row"><span class="menu-item-name">Large 2 Tier (8", 10")</span><span class="menu-item-dots"></span><span class="menu-item-price">$345</span><span class="menu-item-servings">(42–56)</span></div>
                <div class="menu-item-row"><span class="menu-item-name">Small 3 Tier (4", 6", 8")</span><span class="menu-item-dots"></span><span class="menu-item-price">$425</span><span class="menu-item-servings">(24–34)</span></div>
                <div style="background:var(--pink-bg); padding:12px 16px; border-radius:12px; margin-top:16px; border:1px solid rgba(230,115,153,0.3);">
                    <strong style="color:var(--primary); font-size:0.95rem;">Wedding Tasting Box</strong>
                    <div style="font-size:0.85rem; color:#555; margin-top:2px;">4 Cake Flavors &amp; 4 Fillings — <strong>$50</strong> (serves 4–10)</div>
                </div>
            </div>

            <!-- PARTY PACKS -->
            <div class="menu-category-card">
                <div class="menu-category-header">
                    <h3>🎈 Party &amp; Event Bundles</h3>
                    <span style="font-size:1.3rem;">🎉</span>
                </div>
                <div class="menu-item-row"><span class="menu-item-name">Small Party Pack (6" + 2 doz)</span><span class="menu-item-dots"></span><span class="menu-item-price">$165</span></div>
                <div class="menu-item-row"><span class="menu-item-name">Medium Party Pack (6" + 3 doz)</span><span class="menu-item-dots"></span><span class="menu-item-price">$220</span></div>
                <div class="menu-item-row"><span class="menu-item-name">Large Party Pack (8" + 4 doz)</span><span class="menu-item-dots"></span><span class="menu-item-price">$300</span></div>
                <div class="menu-item-row"><span class="menu-item-name">XL Party Pack (8" + 6 doz)</span><span class="menu-item-dots"></span><span class="menu-item-price">$485</span></div>
                <div class="menu-item-row"><span class="menu-item-name">Jumbo Pack (2 tier + 6 doz)</span><span class="menu-item-dots"></span><span class="menu-item-price">$550</span></div>
            </div>

            <!-- FLAVORS CARD -->
            <div class="menu-category-card">
                <div class="menu-category-header">
                    <h3>🍰 Signature Cake Flavors</h3>
                    <span style="font-size:1.3rem;">✨</span>
                </div>
                <strong style="font-size:0.85rem; color:var(--primary); text-transform:uppercase; letter-spacing:1px; display:block; margin-bottom:8px;">Base Flavors (Included)</strong>
                <div class="flavor-pill-grid">
                    <span class="flavor-pill">Vanilla Bean</span>
                    <span class="flavor-pill">Strawberry Bliss</span>
                    <span class="flavor-pill">Chocolate Dream</span>
                    <span class="flavor-pill">Confetti Explosion</span>
                    <span class="flavor-pill">Red Velvet</span>
                    <span class="flavor-pill">Lemon Zest</span>
                </div>

                <strong style="font-size:0.85rem; color:var(--primary); text-transform:uppercase; letter-spacing:1px; display:block; margin:16px 0 8px 0;">Specialty Flavors (+$10)</strong>
                <div class="flavor-pill-grid">
                    <span class="flavor-pill">Carrot &amp; Pecan</span>
                    <span class="flavor-pill">Creamy Hazelnut</span>
                    <span class="flavor-pill">Chocolate Lovers</span>
                    <span class="flavor-pill">Swirly Marble</span>
                    <span class="flavor-pill">Banana Cream</span>
                    <span class="flavor-pill">Cherry Bark</span>
                    <span class="flavor-pill">Pistachio</span>
                    <span class="flavor-pill">Cinnamon Spice</span>
                </div>
            </div>

            <!-- FILLINGS & FROSTING CARD -->
            <div class="menu-category-card">
                <div class="menu-category-header">
                    <h3>🍫 Fillings &amp; Frostings</h3>
                    <span style="font-size:1.3rem;">👩‍🍳</span>
                </div>
                <strong style="font-size:0.85rem; color:var(--primary); text-transform:uppercase; letter-spacing:1px; display:block; margin-bottom:8px;">Gourmet Fillings (+$12 / tier)</strong>
                <div class="flavor-pill-grid">
                    <span class="flavor-pill">Rich Fudge</span>
                    <span class="flavor-pill">Cookies &amp; Cream</span>
                    <span class="flavor-pill">Strawberries &amp; Cream</span>
                    <span class="flavor-pill">Cream Cheese Frosting</span>
                    <span class="flavor-pill">Cheesecake Layer</span>
                    <span class="flavor-pill">Dulce De Leche</span>
                    <span class="flavor-pill">Cookie Dough</span>
                    <span class="flavor-pill">Peanut Butter</span>
                    <span class="flavor-pill">Nutella Spread</span>
                    <span class="flavor-pill">Lemon Curd</span>
                    <span class="flavor-pill">Raspberry Jam</span>
                </div>
                
                <p style="font-size:0.88rem; color:#666; margin-top:16px; font-style:italic;">* All frosting used is signature vanilla buttercream by default unless requested otherwise.</p>
            </div>

        </div>
    @endif

    <!-- CUSTOM TEXT OVERRIDE IF ENTERED -->
    @if(!empty($customText))
        <section style="max-width:1000px; margin:0 auto 60px auto; padding:30px; background:var(--theme-card-bg); border-radius:20px; border:1px solid rgba(0,0,0,0.08); box-shadow:0 8px 30px rgba(0,0,0,0.04);">
            <h3 style="font-family:var(--theme-heading-font); color:var(--dark-text); margin-bottom:14px;">📝 Additional Menu Notes &amp; Policies</h3>
            <div style="font-size:0.95rem; color:var(--dark-text); line-height:1.7;">
                {!! nl2br(e($customText)) !!}
            </div>
        </section>
    @endif
</div>

@include('storefront.partials.order_modal')

<!-- MENU PAGE FOOTER -->
<footer class="about-footer">
    <div class="about-footer-logo">
        @if(!empty($tenant->logo_path))
            <img src="{{ asset($tenant->logo_path) }}" alt="{{ $tenant->name }} Logo" style="max-height:60px; width:auto; object-fit:contain;">
        @else
            <span style="font-family:'Outfit',sans-serif; font-weight:700; font-size:1.5rem; color:#ffffff;">🧁 {{ $tenant->name }}</span>
        @endif
    </div>
    <div class="about-footer-nav">
        <a href="{{ route('storefront.index') }}">Home</a>
        <a href="{{ route('storefront.about') }}">About</a>
        <a href="{{ route('storefront.menu') }}" class="active">Menu</a>
        <a href="{{ route('storefront.gallery') }}">Gallery</a>
        @php
            $sub = request()->route('subdomain') ?? $tenant->subdomain ?? $tenant->slug;
            $bakerPortalUrl = request()->is('site/*') 
                ? url('/site/' . $sub . '/dashboard') 
                : route('baker.dashboard');
        @endphp
        <a href="{{ $bakerPortalUrl }}">Baker Login</a>
    </div>
    <p class="about-copyright">Copyright © 2026 {{ $tenant->name }} | <a href="{{ route('legal.index') }}" style="color:inherit;">Legal Hub</a> &middot; <a href="{{ route('storefront.privacy') }}" style="color:inherit;">Privacy</a> &middot; <a href="{{ route('storefront.terms') }}" style="color:inherit;">Terms</a> | Powered By <span>Doughmain.pro</span></p>
</footer>

<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
