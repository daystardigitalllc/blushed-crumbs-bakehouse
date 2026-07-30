<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Free Bakery Pricing Calculator — Price Cakes, Cookies & Cupcakes | Doughmain.pro</title>
    <meta name="description" content="Free bakery pricing calculator. Instantly calculate ingredient cost, labor, packaging, delivery, and profit margin to find the perfect selling price for your cakes, cookies, and cupcakes.">
    <link rel="canonical" href="{{ route('tools.pricing-calculator') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('tools.pricing-calculator') }}">
    <meta property="og:title" content="Free Bakery Pricing Calculator — Price Cakes, Cookies & Cupcakes">
    <meta property="og:description" content="Calculate ingredient cost, labor, packaging, delivery, and profit margin instantly. Built for home bakers and custom cake businesses.">
    <meta property="og:image" content="{{ asset('images/og_image.jpg') }}">
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Free Bakery Pricing Calculator — Price Cakes, Cookies & Cupcakes">
    <meta name="twitter:description" content="Calculate ingredient cost, labor, packaging, delivery, and profit margin instantly. Built for home bakers and custom cake businesses.">
    <meta name="twitter:image" content="{{ asset('images/og_image.jpg') }}">
    <link rel="icon" href="{{ asset('images/favicon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@graph": [
            {
                "@type": "WebApplication",
                "name": "Bakery Pricing Calculator",
                "url": "{{ route('tools.pricing-calculator') }}",
                "applicationCategory": "BusinessApplication",
                "operatingSystem": "Any",
                "offers": { "@type": "Offer", "price": "0", "priceCurrency": "USD" },
                "description": "Free calculator that helps home bakers price cakes, cookies, and cupcakes by accounting for ingredients, labor, packaging, delivery, and profit margin.",
                "publisher": { "@type": "Organization", "name": "Doughmain.pro" }
            },
            {
                "@type": "FAQPage",
                "mainEntity": [
                    {
                        "@type": "Question",
                        "name": "How do I price a custom cake?",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "Add up every ingredient's actual cost, your labor time at a fair hourly rate, packaging, and delivery if you offer it. That total is your true cost. Then apply a markup or profit margin on top so you're paid for your skill, not just reimbursed for supplies."
                        }
                    },
                    {
                        "@type": "Question",
                        "name": "How much profit should a home bakery make?",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "Most custom bakers aim for a 20-35% profit margin after all costs are covered, with labor making up a similar share of the final price. Highly custom or rush orders can support higher margins."
                        }
                    },
                    {
                        "@type": "Question",
                        "name": "How do I calculate ingredient costs?",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "Divide the price you paid for a package of an ingredient by its package size to get a per-unit cost, then multiply that by the amount you actually used in the recipe. Do this for every ingredient and add them together for your total ingredient cost."
                        }
                    },
                    {
                        "@type": "Question",
                        "name": "What markup should I charge?",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "A common starting point is a 100-200% markup over total cost (ingredients, labor, and packaging combined), which translates to roughly a 30-50% profit margin depending on your market and how custom the order is."
                        }
                    }
                ]
            }
        ]
    }
    </script>

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
            --card-bg: #ffffff;
            --border-color: #f0dde4;
            --input-bg: #fdfafb;
            --page-bg: var(--soft-pink);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
            background-color: var(--page-bg);
            line-height: 1.6;
        }

        h1, h2, h3, h4 { font-family: 'Outfit', sans-serif; color: var(--dark-section); line-height: 1.2; }

        a { text-decoration: none; color: inherit; }

        .container { max-width: 1200px; margin: 0 auto; padding: 0 24px; }

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
        .btn-primary { background-color: var(--primary-pink); color: #fff; box-shadow: 0 4px 14px rgba(230, 115, 153, 0.4); }
        .btn-primary:hover { background-color: var(--primary-pink-dark); transform: translateY(-2px); }
        .btn-light { background-color: var(--white); color: var(--dark-section); border: 1px solid var(--border-color); }
        .btn-light:hover { background-color: var(--input-bg); transform: translateY(-2px); }
        .btn-outline {
            background: transparent; color: var(--primary-pink-dark); border: 2px solid var(--primary-pink);
        }
        .btn-outline:hover { background: var(--primary-pink); color: #fff; }
        .btn-small { padding: 8px 14px; font-size: 0.85rem; border-radius: 6px; }
        .btn:disabled { opacity: 0.55; cursor: not-allowed; transform: none !important; }

        /* Navigation (matches brand landing) */
        .navbar {
            position: sticky; top: 0; left: 0; width: 100%;
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .navbar .container { display: flex; justify-content: space-between; align-items: center; }
        .nav-logo img { height: 56px; width: auto; object-fit: contain; }
        .nav-links { display: flex; align-items: center; gap: 20px; }
        .nav-links a.nav-login { font-weight: 500; }
        .nav-links a.nav-login:hover { color: var(--primary-pink); }
        .nav-links a.active { color: var(--primary-pink-dark); font-weight: 700; }

        /* Nav dropdown (Free Tools) */
        .nav-dropdown { position: relative; }
        .nav-dropdown-toggle {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: none;
            border: none;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            cursor: pointer;
            padding: 0;
        }
        .nav-dropdown-toggle .caret { font-size: 0.6rem; transition: transform 0.2s ease; }
        .nav-dropdown.open .nav-dropdown-toggle .caret { transform: rotate(180deg); }
        .nav-dropdown-menu {
            display: none;
            position: absolute;
            top: calc(100% + 14px);
            left: 0;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
            padding: 8px;
            min-width: 210px;
            z-index: 1001;
        }
        .nav-dropdown.open .nav-dropdown-menu { display: block; }
        .nav-dropdown-menu a {
            display: block;
            padding: 10px 14px;
            border-radius: 6px;
            font-size: 0.92rem;
            font-weight: 500;
            color: var(--text-dark);
            white-space: nowrap;
        }
        .nav-dropdown-menu a:hover { background: var(--input-bg); color: var(--primary-pink-dark); }
        .nav-dropdown-menu a.active { color: var(--primary-pink-dark); font-weight: 700; }

        /* Hero */
        .tool-hero { padding: 56px 0 24px; text-align: center; }
        .tool-hero h1 { font-size: 2.4rem; margin-bottom: 14px; }
        .tool-hero p { color: var(--text-gray); font-size: 1.15rem; max-width: 640px; margin: 0 auto; }

        /* Calculator layout */
        .calc-wrap { padding: 32px 0 64px; }
        .calc-grid { display: grid; grid-template-columns: 1fr 380px; gap: 24px; align-items: start; }
        @media (max-width: 960px) {
            .calc-grid { grid-template-columns: 1fr; }
        }

        .calc-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        }
        .calc-card h2 {
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 6px;
        }
        .step-num {
            background: var(--primary-pink);
            color: #fff;
            width: 28px; height: 28px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
            flex-shrink: 0;
        }
        .step-hint { color: var(--text-gray); font-size: 0.9rem; margin-bottom: 18px; }

        label { display: block; font-size: 0.82rem; font-weight: 600; color: var(--text-gray); margin-bottom: 4px; }

        input[type="text"], input[type="number"], select {
            width: 100%;
            padding: 12px 14px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background: var(--input-bg);
            color: var(--text-dark);
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
        }
        input:focus, select:focus { outline: 2px solid var(--primary-pink); outline-offset: 1px; }

        /* Ingredient import */
        .import-toolbar { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
        .import-panel {
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
            background: var(--input-bg);
        }
        .import-panel textarea {
            width: 100%;
            padding: 12px 14px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background: var(--card-bg);
            color: var(--text-dark);
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            resize: vertical;
        }
        .import-panel-actions { display: flex; gap: 8px; margin-top: 10px; }
        .import-status {
            font-size: 0.85rem;
            padding: 10px 14px;
            border-radius: 8px;
            margin-bottom: 16px;
        }
        .import-status.info { background: var(--input-bg); color: var(--text-gray); }
        .import-status.error { background: #fde2e2; color: #a12727; }
        .import-status.success { background: #e2f5e8; color: #1f7a3f; }

        /* Ingredient rows */
        .ing-row {
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 14px;
            background: var(--input-bg);
        }
        .ing-row-top { display: flex; gap: 10px; margin-bottom: 12px; align-items: flex-end; }
        .ing-row-top .field { flex: 1; }
        .ing-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
        @media (max-width: 640px) { .ing-grid { grid-template-columns: 1fr; } }
        .ing-grid .field-group { display: flex; gap: 6px; min-width: 0; }
        .ing-grid .field-group input { flex: 1.3; min-width: 0; padding-left: 10px; padding-right: 8px; font-size: 0.9rem; }
        .ing-grid .field-group select { flex: 1; min-width: 0; padding-left: 8px; padding-right: 24px; font-size: 0.85rem; }
        .ing-cost-line {
            margin-top: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
            color: var(--primary-pink-dark);
            font-size: 0.95rem;
        }
        .remove-row-btn {
            background: none;
            border: none;
            color: var(--text-gray);
            cursor: pointer;
            font-size: 1.2rem;
            line-height: 1;
            padding: 6px 10px;
        }
        .remove-row-btn:hover { color: #d64545; }

        /* Generic simple rows (packaging) */
        .simple-row { display: flex; gap: 10px; align-items: flex-end; margin-bottom: 12px; }
        .simple-row .field { flex: 1; }
        .simple-row .field-name { flex: 1.4; }
        .toggle-group { display: flex; border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden; }
        .toggle-group button {
            flex: 1;
            padding: 11px 10px;
            border: none;
            background: var(--input-bg);
            color: var(--text-gray);
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .toggle-group button.active { background: var(--primary-pink); color: #fff; }

        .add-row-btn {
            width: 100%;
            padding: 12px;
            border: 2px dashed var(--border-color);
            border-radius: 10px;
            background: transparent;
            color: var(--primary-pink-dark);
            font-weight: 600;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
        }
        .add-row-btn:hover { background: var(--input-bg); }

        /* Radio pill selectors */
        .pill-select { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 16px; }
        .pill-select label {
            display: flex; align-items: center; gap: 6px;
            border: 1px solid var(--border-color);
            border-radius: 999px;
            padding: 9px 16px;
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--text-gray);
            cursor: pointer;
            margin: 0;
        }
        .pill-select input { width: auto; }
        .pill-select label:has(input:checked) { background: var(--primary-pink); color: #fff; border-color: var(--primary-pink); }

        .inline-fields { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        @media (max-width: 500px) { .inline-fields { grid-template-columns: 1fr; } }

        .tooltip-wrap { position: relative; display: inline-flex; align-items: center; }
        .tooltip-icon {
            display: inline-flex; align-items: center; justify-content: center;
            width: 18px; height: 18px; border-radius: 50%;
            background: var(--border-color); color: var(--text-gray);
            font-size: 0.72rem; font-weight: 700; cursor: help; margin-left: 6px;
        }
        .tooltip-text {
            display: none;
            position: absolute; bottom: 130%; left: 50%; transform: translateX(-50%);
            width: 260px; background: var(--dark-section); color: #fff;
            padding: 12px 14px; border-radius: 8px; font-size: 0.8rem; font-weight: 400;
            line-height: 1.5; z-index: 10; box-shadow: 0 6px 20px rgba(0,0,0,0.25);
        }
        .tooltip-wrap:hover .tooltip-text, .tooltip-wrap:focus-within .tooltip-text { display: block; }

        /* Results sidebar */
        .calc-sidebar { position: relative; }
        .results-card { position: sticky; top: 88px; }
        @media (max-width: 960px) { .results-card { position: static; } }
        .results-card h2 { margin-bottom: 16px; }
        .result-line {
            display: flex; justify-content: space-between; align-items: baseline;
            padding: 8px 0;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.92rem;
        }
        .result-line span:first-child { color: var(--text-gray); }
        .result-line.total {
            font-weight: 700; font-size: 1.05rem; color: var(--dark-section);
            border-bottom: none; border-top: 2px solid var(--border-color); margin-top: 6px; padding-top: 14px;
        }
        .price-highlight {
            text-align: center;
            background: linear-gradient(135deg, var(--primary-pink), var(--deep-purple));
            border-radius: 14px;
            padding: 22px 16px;
            margin: 18px 0;
            color: #fff;
        }
        .price-highlight .label { font-size: 0.82rem; opacity: 0.9; font-weight: 600; letter-spacing: 0.03em; text-transform: uppercase; }
        .price-highlight .amount { font-family: 'Outfit', sans-serif; font-size: 2.4rem; font-weight: 800; margin: 4px 0; }
        .price-highlight .sub { font-size: 0.85rem; opacity: 0.9; }

        .round-select { margin-bottom: 6px; }

        .action-row { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 18px; }
        .action-row button, .action-row a { flex: 1; min-width: 120px; }

        .tips-card ul { list-style: none; }
        .tips-card li {
            display: flex; gap: 10px; align-items: flex-start;
            padding: 10px 0; border-bottom: 1px solid var(--border-color); font-size: 0.9rem;
        }
        .tips-card li:last-child { border-bottom: none; }
        .tips-card li .icon { flex-shrink: 0; }

        /* CTA */
        .cta-section {
            background: var(--dark-section);
            color: #fff;
            padding: 60px 24px;
            text-align: center;
            border-radius: 24px;
            margin-bottom: 40px;
        }
        .cta-section h2 { color: #fff; font-size: 2rem; margin-bottom: 12px; }
        .cta-section p { color: #d9c9d3; max-width: 560px; margin: 0 auto 24px; }
        .cta-buttons { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }

        /* FAQ */
        .faq-section { padding: 40px 0 60px; }
        .faq-section h2 { text-align: center; margin-bottom: 28px; }
        .faq-item { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 4px 20px; margin-bottom: 12px; }
        .faq-item summary { padding: 16px 0; font-weight: 600; cursor: pointer; }
        .faq-item p { padding-bottom: 16px; color: var(--text-gray); }

        footer.site-footer { text-align: center; padding: 30px 24px; color: var(--text-gray); font-size: 0.85rem; }
        footer.site-footer a { text-decoration: underline; }

        .copy-toast {
            position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%) translateY(20px);
            background: var(--dark-section); color: #fff; padding: 12px 20px; border-radius: 8px;
            font-size: 0.9rem; opacity: 0; pointer-events: none; transition: all 0.25s ease; z-index: 2000;
        }
        .copy-toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }

        @media print {
            .navbar, .cta-section, .faq-section, .action-row, .calc-main, footer.site-footer, .tool-hero { display: none !important; }
            body { background: #fff; }
            .calc-grid { display: block; }
            .results-card { position: static; box-shadow: none; border: 1px solid #ccc; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="container">
            <a href="/" class="nav-logo">
                <img src="{{ asset('images/doughmain_logo.png') }}" alt="Doughmain.pro Logo">
            </a>
            <div class="nav-links">
                <div class="nav-dropdown" id="free-tools-dropdown">
                    <button type="button" class="nav-login nav-dropdown-toggle active" aria-haspopup="true" aria-expanded="false">
                        Free Tools <span class="caret">▾</span>
                    </button>
                    <div class="nav-dropdown-menu">
                        <a href="{{ route('tools.pricing-calculator') }}" class="active">Bakery Pricing Calculator</a>
                    </div>
                </div>
                <a href="/login" class="nav-login">Login</a>
                <a href="/register" class="btn btn-primary btn-small">Build Your Free Site →</a>
            </div>
        </div>
    </nav>

    <header class="tool-hero container">
        <h1>Bakery Pricing Calculator</h1>
        <p>Find the perfect price for your cakes, cookies, cupcakes, and other baked goods in seconds.</p>
    </header>

    <div class="calc-wrap container">
        <div class="calc-grid">

            <!-- Main steps -->
            <div class="calc-main">

                <!-- Step 1: Ingredients -->
                <div class="calc-card">
                    <h2><span class="step-num">1</span> Ingredients</h2>
                    <p class="step-hint">Add every ingredient you use. We'll work out the exact cost from what you paid for the package.</p>

                    <div class="import-toolbar">
                        <button type="button" class="btn btn-light btn-small" id="btn-import-paste">📋 Paste a List</button>
                        <button type="button" class="btn btn-light btn-small" id="btn-import-photo">📷 Scan a Photo</button>
                        <input type="file" id="import-photo-input" accept="image/*" style="display:none;">
                    </div>

                    <div class="import-panel" id="import-paste-panel" style="display:none;">
                        <label for="import-paste-text">Paste your ingredient list</label>
                        <textarea id="import-paste-text" rows="5" placeholder="e.g.&#10;Butter, 100g used, $5.99 for 454g&#10;Sugar, 200g used, $3.49 for 907g"></textarea>
                        <div class="import-panel-actions">
                            <button type="button" class="btn btn-primary btn-small" id="btn-import-paste-submit">Fill In Ingredients</button>
                            <button type="button" class="btn btn-light btn-small" id="btn-import-paste-cancel">Cancel</button>
                        </div>
                    </div>

                    <div class="import-status" id="import-status" style="display:none;"></div>

                    <div id="ingredients-list"></div>
                    <button type="button" class="add-row-btn" id="add-ingredient">+ Add Ingredient</button>
                </div>

                <!-- Step 2: Labor -->
                <div class="calc-card">
                    <h2><span class="step-num">2</span> Labor</h2>
                    <p class="step-hint">How long did this order take you, start to finish?</p>
                    <div class="inline-fields" style="grid-template-columns: 1fr 1fr 1fr;">
                        <div class="field">
                            <label for="labor-hours">Hours Worked</label>
                            <input type="number" id="labor-hours" min="0" step="0.5" value="0">
                        </div>
                        <div class="field">
                            <label for="labor-minutes">Minutes Worked</label>
                            <input type="number" id="labor-minutes" min="0" max="59" step="1" value="0">
                        </div>
                        <div class="field">
                            <label for="labor-rate">Hourly Rate ($)</label>
                            <input type="number" id="labor-rate" min="0" step="0.5" value="20">
                        </div>
                    </div>
                    <div class="ing-cost-line"><span>Labor Cost</span><span id="labor-cost-display">$0.00</span></div>
                </div>

                <!-- Step 3: Packaging -->
                <div class="calc-card">
                    <h2><span class="step-num">3</span> Packaging</h2>
                    <p class="step-hint">Boxes, ribbon, stickers, cake boards — anything you use to present or transport the order.</p>
                    <div id="packaging-list"></div>
                    <button type="button" class="add-row-btn" id="add-packaging">+ Add Packaging Item</button>
                    <div class="ing-cost-line"><span>Packaging Total</span><span id="packaging-total-display">$0.00</span></div>
                </div>

                <!-- Step 4: Delivery -->
                <div class="calc-card">
                    <h2><span class="step-num">4</span> Delivery</h2>
                    <p class="step-hint">Are you delivering this order?</p>
                    <div class="pill-select" id="delivery-mode-select">
                        <label><input type="radio" name="delivery-mode" value="none" checked> No Delivery</label>
                        <label><input type="radio" name="delivery-mode" value="flat"> Flat Fee</label>
                        <label><input type="radio" name="delivery-mode" value="mileage"> Mileage</label>
                    </div>
                    <div class="inline-fields" id="delivery-flat-fields" style="display:none;">
                        <div class="field">
                            <label for="delivery-flat-fee">Flat Delivery Fee ($)</label>
                            <input type="number" id="delivery-flat-fee" min="0" step="0.5" value="0">
                        </div>
                    </div>
                    <div class="inline-fields" id="delivery-mileage-fields" style="display:none;">
                        <div class="field">
                            <label for="delivery-miles">Miles</label>
                            <input type="number" id="delivery-miles" min="0" step="1" value="0">
                        </div>
                        <div class="field">
                            <label for="delivery-mileage-rate">Mileage Rate ($/mile)</label>
                            <input type="number" id="delivery-mileage-rate" min="0" step="0.01" value="0.67">
                        </div>
                    </div>
                    <div class="ing-cost-line"><span>Delivery Cost</span><span id="delivery-cost-display">$0.00</span></div>
                </div>

                <!-- Step 5: Profit -->
                <div class="calc-card">
                    <h2><span class="step-num">5</span> Desired Profit</h2>
                    <p class="step-hint">
                        Choose how you'd like to add profit on top of your costs.
                        <span class="tooltip-wrap" tabindex="0">
                            <span class="tooltip-icon">?</span>
                            <span class="tooltip-text">Markup is a percentage added on top of your total cost (Cost × 1.X). Profit margin is the percentage of your final selling price that is profit (Price = Cost ÷ (1 − X%)). A 50% markup is a 33% margin — margin is always the smaller number.</span>
                        </span>
                    </p>
                    <div class="pill-select" id="profit-mode-select">
                        <label><input type="radio" name="profit-mode" value="markup" checked> Markup %</label>
                        <label><input type="radio" name="profit-mode" value="margin"> Profit Margin %</label>
                    </div>
                    <div class="field" style="max-width:220px;">
                        <label for="profit-value" id="profit-value-label">Markup Percentage (%)</label>
                        <input type="number" id="profit-value" min="0" step="1" value="50">
                    </div>
                </div>

            </div>

            <!-- Sidebar: Results -->
            <div class="calc-sidebar">
                <div class="calc-card results-card">
                    <h2>Your Price Summary</h2>

                    <div class="field round-select">
                        <label for="round-mode">Round Suggested Price To</label>
                        <select id="round-mode">
                            <option value="0.25">Nearest $0.25</option>
                            <option value="0.5">Nearest $0.50</option>
                            <option value="1">Nearest Whole Dollar</option>
                        </select>
                    </div>

                    <div class="result-line"><span>Ingredient Cost</span><span id="r-ingredients">$0.00</span></div>
                    <div class="result-line"><span>Labor Cost</span><span id="r-labor">$0.00</span></div>
                    <div class="result-line"><span>Packaging</span><span id="r-packaging">$0.00</span></div>
                    <div class="result-line"><span>Delivery</span><span id="r-delivery">$0.00</span></div>
                    <div class="result-line total"><span>Total Cost</span><span id="r-total-cost">$0.00</span></div>
                    <div class="result-line"><span>Desired Profit</span><span id="r-desired-profit">$0.00</span></div>

                    <div class="price-highlight">
                        <div class="label">Suggested Selling Price</div>
                        <div class="amount" id="r-suggested-price">$0.00</div>
                        <div class="sub" id="r-profit-sub">Profit: $0.00 (0%)</div>
                    </div>

                    <div class="action-row">
                        <button type="button" class="btn btn-light btn-small" id="btn-print">🖨️ Print</button>
                        <button type="button" class="btn btn-light btn-small" id="btn-pdf">⬇️ Save PDF</button>
                        <button type="button" class="btn btn-light btn-small" id="btn-copy">📋 Copy Results</button>
                        <button type="button" class="btn btn-light btn-small" id="btn-share">🔗 Share Link</button>
                    </div>
                </div>

                <div class="calc-card tips-card">
                    <h2>Pricing Tips</h2>
                    <ul id="tips-list"></ul>
                </div>
            </div>

        </div>
    </div>

    <section class="cta-section container">
        <h2>Ready to Start Selling More?</h2>
        <p>Create your free bakery website with Doughmain.pro. Accept orders online, showcase your menu, and grow your bakery.</p>
        <div class="cta-buttons">
            <a href="/register" class="btn btn-primary">Create Free Website</a>
            <a href="/landing" class="btn btn-outline">Learn More</a>
        </div>
    </section>

    <section class="faq-section container">
        <h2>Frequently Asked Questions</h2>

        <details class="faq-item">
            <summary>How do I price a custom cake?</summary>
            <p>Add up every ingredient's actual cost, your labor time at a fair hourly rate, packaging, and delivery if you offer it. That total is your true cost. Then apply a markup or profit margin on top so you're paid for your skill, not just reimbursed for supplies.</p>
        </details>
        <details class="faq-item">
            <summary>How much profit should a home bakery make?</summary>
            <p>Most custom bakers aim for a 20-35% profit margin after all costs are covered, with labor making up a similar share of the final price. Highly custom or rush orders can support higher margins.</p>
        </details>
        <details class="faq-item">
            <summary>How do I calculate ingredient costs?</summary>
            <p>Divide the price you paid for a package of an ingredient by its package size to get a per-unit cost, then multiply that by the amount you actually used in the recipe. Do this for every ingredient and add them together for your total ingredient cost.</p>
        </details>
        <details class="faq-item">
            <summary>What markup should I charge?</summary>
            <p>A common starting point is a 100-200% markup over total cost (ingredients, labor, and packaging combined), which translates to roughly a 30-50% profit margin depending on your market and how custom the order is.</p>
        </details>
    </section>

    <footer class="site-footer">
        &copy; {{ date('Y') }} Doughmain Pro Platform, a <a href="https://daystardigital.co">Daystar Digital LLC</a> product. &middot; <a href="/legal">Legal Center</a>
    </footer>

    <div class="copy-toast" id="copy-toast">Copied!</div>

    <script>
    (function () {
        'use strict';

        var STORAGE_KEY = 'doughmain_pricing_calculator_v1';

        var UNIT_OPTIONS = ['grams', 'ounces', 'pounds', 'cups', 'teaspoons', 'tablespoons', 'pieces', 'custom'];

        var PARSE_INGREDIENTS_URL = '{{ route('tools.pricing-calculator.parse') }}';
        var CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function defaultState() {
            return {
                ingredients: [
                    { name: '', qty: '', unit: 'grams', pkgCost: '', pkgSize: '', pkgUnit: 'grams' }
                ],
                labor: { hours: 0, minutes: 0, rate: 20 },
                packaging: [
                    { name: 'Box', cost: 0 },
                    { name: 'Ribbon', cost: 0 },
                    { name: 'Sticker', cost: 0 },
                    { name: 'Cake Board', cost: 0 }
                ],
                delivery: { mode: 'none', flatFee: 0, miles: 0, mileageRate: 0.67 },
                profit: { mode: 'markup', value: 50 },
                roundMode: '0.25'
            };
        }

        // Load state: URL share-link takes priority, then localStorage, then defaults.
        function loadState() {
            var params = new URLSearchParams(window.location.search);
            if (params.has('state')) {
                try {
                    var decoded = JSON.parse(atob(decodeURIComponent(params.get('state'))));
                    return Object.assign(defaultState(), decoded);
                } catch (e) { /* fall through to localStorage */ }
            }
            try {
                var saved = localStorage.getItem(STORAGE_KEY);
                if (saved) return Object.assign(defaultState(), JSON.parse(saved));
            } catch (e) { /* localStorage unavailable */ }
            return defaultState();
        }

        var state = loadState();

        function persist() {
            try { localStorage.setItem(STORAGE_KEY, JSON.stringify(state)); } catch (e) { /* ignore */ }
        }

        function num(v) {
            var n = parseFloat(v);
            return isNaN(n) || n < 0 ? 0 : n;
        }

        function fmt(n) {
            return '$' + (Math.round(n * 100) / 100).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function unitOptionsHtml(selected) {
            return UNIT_OPTIONS.map(function (u) {
                var label = u.charAt(0).toUpperCase() + u.slice(1);
                return '<option value="' + u + '"' + (u === selected ? ' selected' : '') + '>' + label + '</option>';
            }).join('');
        }

        // ---------- Rendering ----------

        function renderIngredients() {
            var list = document.getElementById('ingredients-list');
            list.innerHTML = state.ingredients.map(function (row, i) {
                return '' +
                    '<div class="ing-row" data-index="' + i + '">' +
                        '<div class="ing-row-top">' +
                            '<div class="field">' +
                                '<label>Ingredient Name</label>' +
                                '<input type="text" class="ing-name" placeholder="e.g. Butter" value="' + escapeHtml(row.name) + '">' +
                            '</div>' +
                            (state.ingredients.length > 1 ? '<button type="button" class="remove-row-btn" data-remove="ingredient" title="Remove ingredient">✕</button>' : '') +
                        '</div>' +
                        '<div class="ing-grid">' +
                            '<div class="field-group"><input type="number" class="ing-qty" min="0" step="0.01" placeholder="Qty used" value="' + row.qty + '">' +
                                '<select class="ing-unit">' + unitOptionsHtml(row.unit) + '</select></div>' +
                            '<div class="field-group"><span style="align-self:center;padding-right:4px;">$</span><input type="number" class="ing-pkgcost" min="0" step="0.01" placeholder="Pkg cost" value="' + row.pkgCost + '"></div>' +
                            '<div class="field-group"><input type="number" class="ing-pkgsize" min="0" step="0.01" placeholder="Pkg size" value="' + row.pkgSize + '">' +
                                '<select class="ing-pkgunit">' + unitOptionsHtml(row.pkgUnit) + '</select></div>' +
                        '</div>' +
                        '<div class="ing-cost-line"><span>Ingredient Cost</span><span class="ing-cost-value">' + fmt(ingredientCost(row)) + '</span></div>' +
                    '</div>';
            }).join('');
        }

        function ingredientCost(row) {
            var qty = num(row.qty), pkgCost = num(row.pkgCost), pkgSize = num(row.pkgSize);
            if (pkgSize <= 0) return 0;
            return (qty / pkgSize) * pkgCost;
        }

        function renderPackaging() {
            var list = document.getElementById('packaging-list');
            list.innerHTML = state.packaging.map(function (row, i) {
                return '' +
                    '<div class="simple-row" data-index="' + i + '">' +
                        '<div class="field field-name"><label>Item</label><input type="text" class="pkg-name" value="' + escapeHtml(row.name) + '"></div>' +
                        '<div class="field"><label>Cost ($)</label><input type="number" class="pkg-cost" min="0" step="0.01" value="' + row.cost + '"></div>' +
                        '<button type="button" class="remove-row-btn" data-remove="packaging" title="Remove item">✕</button>' +
                    '</div>';
            }).join('');
        }

        function escapeHtml(str) {
            return String(str == null ? '' : str).replace(/[&<>"']/g, function (c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
            });
        }

        // ---------- Wiring inputs (event delegation) ----------

        document.getElementById('ingredients-list').addEventListener('input', function (e) {
            var rowEl = e.target.closest('.ing-row');
            if (!rowEl) return;
            var i = parseInt(rowEl.getAttribute('data-index'), 10);
            var row = state.ingredients[i];
            if (e.target.classList.contains('ing-name')) row.name = e.target.value;
            if (e.target.classList.contains('ing-qty')) row.qty = e.target.value;
            if (e.target.classList.contains('ing-pkgcost')) row.pkgCost = e.target.value;
            if (e.target.classList.contains('ing-pkgsize')) row.pkgSize = e.target.value;
            if (e.target.classList.contains('ing-unit')) row.unit = e.target.value;
            if (e.target.classList.contains('ing-pkgunit')) row.pkgUnit = e.target.value;
            rowEl.querySelector('.ing-cost-value').textContent = fmt(ingredientCost(row));
            recalculate();
        });
        document.getElementById('ingredients-list').addEventListener('click', function (e) {
            if (e.target.getAttribute && e.target.getAttribute('data-remove') === 'ingredient') {
                var rowEl = e.target.closest('.ing-row');
                state.ingredients.splice(parseInt(rowEl.getAttribute('data-index'), 10), 1);
                renderIngredients();
                recalculate();
            }
        });
        document.getElementById('add-ingredient').addEventListener('click', function () {
            state.ingredients.push({ name: '', qty: '', unit: 'grams', pkgCost: '', pkgSize: '', pkgUnit: 'grams' });
            renderIngredients();
            recalculate();
        });

        document.getElementById('packaging-list').addEventListener('input', function (e) {
            var rowEl = e.target.closest('.simple-row');
            if (!rowEl) return;
            var i = parseInt(rowEl.getAttribute('data-index'), 10);
            var row = state.packaging[i];
            if (e.target.classList.contains('pkg-name')) row.name = e.target.value;
            if (e.target.classList.contains('pkg-cost')) row.cost = e.target.value;
            recalculate();
        });
        document.getElementById('packaging-list').addEventListener('click', function (e) {
            if (e.target.getAttribute && e.target.getAttribute('data-remove') === 'packaging') {
                var rowEl = e.target.closest('.simple-row');
                state.packaging.splice(parseInt(rowEl.getAttribute('data-index'), 10), 1);
                renderPackaging();
                recalculate();
            }
        });
        document.getElementById('add-packaging').addEventListener('click', function () {
            state.packaging.push({ name: 'Other', cost: 0 });
            renderPackaging();
            recalculate();
        });

        // Labor
        document.getElementById('labor-hours').addEventListener('input', function (e) { state.labor.hours = e.target.value; recalculate(); });
        document.getElementById('labor-minutes').addEventListener('input', function (e) { state.labor.minutes = e.target.value; recalculate(); });
        document.getElementById('labor-rate').addEventListener('input', function (e) { state.labor.rate = e.target.value; recalculate(); });

        // Delivery
        document.getElementById('delivery-mode-select').addEventListener('change', function (e) {
            if (e.target.name === 'delivery-mode') {
                state.delivery.mode = e.target.value;
                updateDeliveryVisibility();
                recalculate();
            }
        });
        document.getElementById('delivery-flat-fee').addEventListener('input', function (e) { state.delivery.flatFee = e.target.value; recalculate(); });
        document.getElementById('delivery-miles').addEventListener('input', function (e) { state.delivery.miles = e.target.value; recalculate(); });
        document.getElementById('delivery-mileage-rate').addEventListener('input', function (e) { state.delivery.mileageRate = e.target.value; recalculate(); });

        function updateDeliveryVisibility() {
            document.getElementById('delivery-flat-fields').style.display = state.delivery.mode === 'flat' ? 'grid' : 'none';
            document.getElementById('delivery-mileage-fields').style.display = state.delivery.mode === 'mileage' ? 'grid' : 'none';
        }

        // Profit
        document.getElementById('profit-mode-select').addEventListener('change', function (e) {
            if (e.target.name === 'profit-mode') {
                state.profit.mode = e.target.value;
                document.getElementById('profit-value-label').textContent = state.profit.mode === 'markup' ? 'Markup Percentage (%)' : 'Profit Margin (%)';
                recalculate();
            }
        });
        document.getElementById('profit-value').addEventListener('input', function (e) { state.profit.value = e.target.value; recalculate(); });

        document.getElementById('round-mode').addEventListener('change', function (e) { state.roundMode = e.target.value; recalculate(); });

        // ---------- Calculation ----------

        function laborCost() {
            var hours = num(state.labor.hours), minutes = num(state.labor.minutes), rate = num(state.labor.rate);
            return (hours + minutes / 60) * rate;
        }

        function ingredientsTotal() {
            return state.ingredients.reduce(function (sum, row) { return sum + ingredientCost(row); }, 0);
        }

        function packagingTotal() {
            return state.packaging.reduce(function (sum, row) { return sum + num(row.cost); }, 0);
        }

        function deliveryCost() {
            if (state.delivery.mode === 'flat') return num(state.delivery.flatFee);
            if (state.delivery.mode === 'mileage') return num(state.delivery.miles) * num(state.delivery.mileageRate);
            return 0;
        }

        function roundPrice(price, step) {
            step = parseFloat(step) || 0.25;
            return Math.round(price / step) * step;
        }

        function recalculate() {
            var ingCost = ingredientsTotal();
            var labCost = laborCost();
            var pkgCost = packagingTotal();
            var delCost = deliveryCost();
            var totalCost = ingCost + labCost + pkgCost + delCost;

            var profitVal = num(state.profit.value);
            var rawPrice;
            if (state.profit.mode === 'margin') {
                var marginFraction = Math.min(profitVal, 95) / 100;
                rawPrice = marginFraction >= 1 ? totalCost : totalCost / (1 - marginFraction);
            } else {
                rawPrice = totalCost * (1 + profitVal / 100);
            }

            var suggestedPrice = roundPrice(rawPrice, state.roundMode);
            var profitDollars = suggestedPrice - totalCost;
            var profitPercentOfPrice = suggestedPrice > 0 ? (profitDollars / suggestedPrice) * 100 : 0;

            document.getElementById('labor-cost-display').textContent = fmt(labCost);
            document.getElementById('packaging-total-display').textContent = fmt(pkgCost);
            document.getElementById('delivery-cost-display').textContent = fmt(delCost);

            document.getElementById('r-ingredients').textContent = fmt(ingCost);
            document.getElementById('r-labor').textContent = fmt(labCost);
            document.getElementById('r-packaging').textContent = fmt(pkgCost);
            document.getElementById('r-delivery').textContent = fmt(delCost);
            document.getElementById('r-total-cost').textContent = fmt(totalCost);
            document.getElementById('r-desired-profit').textContent = fmt(rawPrice - totalCost);
            document.getElementById('r-suggested-price').textContent = fmt(suggestedPrice);
            document.getElementById('r-profit-sub').textContent = 'Profit: ' + fmt(profitDollars) + ' (' + profitPercentOfPrice.toFixed(1) + '%)';

            renderTips({
                ingCost: ingCost, labCost: labCost, pkgCost: pkgCost, delCost: delCost,
                totalCost: totalCost, suggestedPrice: suggestedPrice, profitDollars: profitDollars, profitPercentOfPrice: profitPercentOfPrice
            });

            persist();
        }

        function renderTips(d) {
            var tips = [];
            var price = d.suggestedPrice || 0;

            if (price <= 0) {
                tips.push('Enter your ingredient, labor, and other costs above to get personalized pricing tips.');
            } else {
                var laborPct = (d.labCost / price) * 100;
                var ingPct = (d.ingCost / price) * 100;
                var profitPct = d.profitPercentOfPrice;

                if (laborPct < 20) {
                    tips.push('Your labor is only ' + laborPct.toFixed(0) + '% of your selling price. Most custom bakers target between 20% and 35% for labor.');
                } else if (laborPct > 45) {
                    tips.push('Labor makes up ' + laborPct.toFixed(0) + '% of your price. Double check your hourly rate and hours — that is a high share even for custom work.');
                }

                if (ingPct > 35) {
                    tips.push('Ingredient costs are ' + ingPct.toFixed(0) + '% of your price. If this exceeds 35%, consider reviewing your pricing or sourcing.');
                }

                if (profitPct < 15) {
                    tips.push('Your profit margin is only ' + profitPct.toFixed(0) + '%. You may be undercharging for custom work — most bakers target 20-35%.');
                } else if (profitPct > 50) {
                    tips.push('Nice margin at ' + profitPct.toFixed(0) + '%! Just double check your price still feels fair for your local market.');
                } else {
                    tips.push('Your profit margin of ' + profitPct.toFixed(0) + '% is right in the healthy range for custom bakers.');
                }

                if (d.delCost > 0 && (d.delCost / price) * 100 > 15) {
                    tips.push('Delivery is a meaningful chunk of this price — consider a minimum order size for delivery orders.');
                }
            }

            document.getElementById('tips-list').innerHTML = tips.map(function (t) {
                return '<li><span class="icon">💡</span><span>' + t + '</span></li>';
            }).join('');
        }

        // ---------- Actions ----------

        function showToast(msg) {
            var toast = document.getElementById('copy-toast');
            toast.textContent = msg;
            toast.classList.add('show');
            setTimeout(function () { toast.classList.remove('show'); }, 2200);
        }

        function resultsText() {
            var lines = [
                'Bakery Pricing Calculator — Doughmain.pro',
                'Ingredient Cost: ' + document.getElementById('r-ingredients').textContent,
                'Labor Cost: ' + document.getElementById('r-labor').textContent,
                'Packaging: ' + document.getElementById('r-packaging').textContent,
                'Delivery: ' + document.getElementById('r-delivery').textContent,
                'Total Cost: ' + document.getElementById('r-total-cost').textContent,
                'Suggested Selling Price: ' + document.getElementById('r-suggested-price').textContent,
                document.getElementById('r-profit-sub').textContent
            ];
            return lines.join('\n');
        }

        document.getElementById('btn-print').addEventListener('click', function () { window.print(); });
        document.getElementById('btn-pdf').addEventListener('click', function () {
            showToast('Choose "Save as PDF" in the print dialog');
            setTimeout(function () { window.print(); }, 400);
        });
        document.getElementById('btn-copy').addEventListener('click', function () {
            var text = resultsText();
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function () { showToast('Results copied to clipboard'); });
            } else {
                showToast('Copy not supported in this browser');
            }
        });
        document.getElementById('btn-share').addEventListener('click', function () {
            var encoded = encodeURIComponent(btoa(JSON.stringify(state)));
            var url = window.location.origin + window.location.pathname + '?state=' + encoded;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(function () { showToast('Share link copied to clipboard'); });
            } else {
                window.prompt('Copy this share link:', url);
            }
        });

        // ---------- Ingredient import (paste list / scan photo) ----------

        var importStatusEl = document.getElementById('import-status');
        var importPastePanel = document.getElementById('import-paste-panel');
        var importPasteText = document.getElementById('import-paste-text');
        var importPhotoInput = document.getElementById('import-photo-input');

        function setImportStatus(kind, message) {
            if (!message) {
                importStatusEl.style.display = 'none';
                return;
            }
            importStatusEl.className = 'import-status ' + kind;
            importStatusEl.textContent = message;
            importStatusEl.style.display = 'block';
        }

        function isEmptyIngredientRow(row) {
            return !row.name && !row.qty && !row.pkgCost && !row.pkgSize;
        }

        function normalizedUnit(u) {
            return UNIT_OPTIONS.indexOf(u) !== -1 ? u : 'grams';
        }

        // The AI doesn't always name the same ingredient identically across
        // recipe sections (e.g. plain "unsalted butter" in one call, then
        // "unsalted butter (buttercream)" in another) — strip a trailing
        // parenthetical qualifier before matching so those still combine.
        // This only affects the matching key, never what's displayed.
        function ingredientMatchName(name) {
            return name.trim().replace(/\s*\([^)]*\)\s*$/, '').trim().toLowerCase();
        }

        // Same ingredient appearing more than once (e.g. butter used in both
        // a cake and its buttercream) gets combined into one row instead of
        // duplicated. Only rows with the same name AND unit are combined —
        // quantities in different units (e.g. cups vs. grams) aren't safely
        // addable without a conversion, so those are deliberately left as
        // separate rows rather than guessed at.
        function mergeIngredientRows(rows) {
            var merged = [];
            var indexByKey = {};

            rows.forEach(function (row) {
                var matchName = ingredientMatchName(row.name);
                if (!matchName) {
                    merged.push(row);
                    return;
                }

                var key = matchName + '|' + row.unit;
                var existingIndex = indexByKey[key];
                if (existingIndex === undefined) {
                    indexByKey[key] = merged.length;
                    merged.push(row);
                    return;
                }

                var existing = merged[existingIndex];
                existing.qty = num(existing.qty) + num(row.qty);
                if (!existing.pkgCost && row.pkgCost) existing.pkgCost = row.pkgCost;
                if (!existing.pkgSize && row.pkgSize) existing.pkgSize = row.pkgSize;
                // Prefer the plainer name (no parenthetical qualifier) for display.
                if (/\([^)]*\)\s*$/.test(existing.name) && !/\([^)]*\)\s*$/.test(row.name)) {
                    existing.name = row.name;
                }
            });

            return merged;
        }

        function applyImportedIngredients(imported) {
            if (!Array.isArray(imported) || imported.length === 0) {
                setImportStatus('error', "We couldn't find any ingredients in that — try adding a bit more detail.");
                return;
            }

            var newRows = imported.map(function (item) {
                return {
                    name: item.name || '',
                    qty: (item.qty !== null && item.qty !== undefined) ? item.qty : '',
                    unit: normalizedUnit(item.unit),
                    pkgCost: (item.pkgCost !== null && item.pkgCost !== undefined) ? item.pkgCost : '',
                    pkgSize: (item.pkgSize !== null && item.pkgSize !== undefined) ? item.pkgSize : '',
                    pkgUnit: normalizedUnit(item.pkgUnit || item.unit)
                };
            });

            var keptExisting = state.ingredients.filter(function (row) { return !isEmptyIngredientRow(row); });
            var combinedCount = keptExisting.length + newRows.length;
            state.ingredients = mergeIngredientRows(keptExisting.concat(newRows));

            var mergedCount = combinedCount - state.ingredients.length;

            renderIngredients();
            recalculate();
            setImportStatus('success', 'Added ' + newRows.length + ' ingredient' + (newRows.length === 1 ? '' : 's')
                + (mergedCount > 0 ? ' (combined ' + mergedCount + ' duplicate' + (mergedCount === 1 ? '' : 's') + ')' : '')
                + '. Double check quantities and costs, then adjust as needed.');

            // Close out the paste flow on success so the same text can't be
            // left sitting there and accidentally resubmitted (which used to
            // double every quantity).
            importPastePanel.style.display = 'none';
            importPasteText.value = '';
        }

        var importBusy = false;
        var importPasteSubmitBtn = document.getElementById('btn-import-paste-submit');
        var importPhotoBtn = document.getElementById('btn-import-photo');

        function setImportBusy(busy) {
            importBusy = busy;
            importPasteSubmitBtn.disabled = busy;
            importPhotoBtn.disabled = busy;
        }

        function submitImport(formData, busyMessage) {
            if (importBusy) return;
            setImportBusy(true);
            setImportStatus('info', busyMessage);
            fetch(PARSE_INGREDIENTS_URL, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                body: formData
            })
                .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
                .then(function (result) {
                    if (!result.ok || result.data.error) {
                        setImportStatus('error', (result.data && result.data.error) || 'Something went wrong reading that. Please try again.');
                        return;
                    }
                    applyImportedIngredients(result.data.ingredients);
                })
                .catch(function () {
                    setImportStatus('error', 'Could not reach the server. Check your connection and try again.');
                })
                .finally(function () {
                    setImportBusy(false);
                });
        }

        document.getElementById('btn-import-paste').addEventListener('click', function () {
            importPastePanel.style.display = importPastePanel.style.display === 'none' ? 'block' : 'none';
            setImportStatus(null, null);
        });
        document.getElementById('btn-import-paste-cancel').addEventListener('click', function () {
            importPastePanel.style.display = 'none';
            importPasteText.value = '';
            setImportStatus(null, null);
        });
        importPasteSubmitBtn.addEventListener('click', function () {
            var text = importPasteText.value.trim();
            if (!text) {
                setImportStatus('error', 'Paste your ingredient list first.');
                return;
            }
            var formData = new FormData();
            formData.append('text', text);
            submitImport(formData, 'Reading your list…');
        });

        importPhotoBtn.addEventListener('click', function () {
            importPhotoInput.click();
        });
        importPhotoInput.addEventListener('change', function () {
            var file = importPhotoInput.files && importPhotoInput.files[0];
            if (!file) return;
            var formData = new FormData();
            formData.append('image', file);
            submitImport(formData, 'Scanning your photo…');
            importPhotoInput.value = '';
        });

        // ---------- Init ----------

        function init() {
            renderIngredients();
            renderPackaging();

            document.getElementById('labor-hours').value = state.labor.hours;
            document.getElementById('labor-minutes').value = state.labor.minutes;
            document.getElementById('labor-rate').value = state.labor.rate;

            document.querySelector('input[name="delivery-mode"][value="' + state.delivery.mode + '"]').checked = true;
            document.getElementById('delivery-flat-fee').value = state.delivery.flatFee;
            document.getElementById('delivery-miles').value = state.delivery.miles;
            document.getElementById('delivery-mileage-rate').value = state.delivery.mileageRate;
            updateDeliveryVisibility();

            document.querySelector('input[name="profit-mode"][value="' + state.profit.mode + '"]').checked = true;
            document.getElementById('profit-value').value = state.profit.value;
            document.getElementById('profit-value-label').textContent = state.profit.mode === 'markup' ? 'Markup Percentage (%)' : 'Profit Margin (%)';

            document.getElementById('round-mode').value = state.roundMode;

            recalculate();

            // Clean the share-link param from the address bar without reloading, now that it's loaded into state.
            if (new URLSearchParams(window.location.search).has('state')) {
                history.replaceState(null, '', window.location.pathname);
            }
        }

        function initNavDropdown() {
            var dropdown = document.getElementById('free-tools-dropdown');
            if (!dropdown) return;
            var toggle = dropdown.querySelector('.nav-dropdown-toggle');
            toggle.addEventListener('click', function (e) {
                e.stopPropagation();
                var isOpen = dropdown.classList.toggle('open');
                toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
            document.addEventListener('click', function (e) {
                if (!dropdown.contains(e.target)) {
                    dropdown.classList.remove('open');
                    toggle.setAttribute('aria-expanded', 'false');
                }
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    dropdown.classList.remove('open');
                    toggle.setAttribute('aria-expanded', 'false');
                }
            });
        }

        init();
        initNavDropdown();
    })();
    </script>
</body>
</html>
