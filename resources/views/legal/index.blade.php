<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($document) ? $document['title'] : 'Legal Center & Compliance' }} - {{ isset($tenant) ? $tenant->name : 'Doughmain.pro' }}</title>
    @if(isset($tenant) && $tenant->logo_path)
        <link rel="icon" href="{{ asset($tenant->logo_path) }}">
    @else
        <link rel="icon" href="{{ asset('images/favicon.png') }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #e67399;
            --primary-dark: #d63384;
            --accent: #6d28d9;
            --dark: #0f172a;
            --gray-900: #1e293b;
            --gray-700: #334155;
            --gray-500: #64748b;
            --gray-300: #cbd5e1;
            --gray-100: #f8fafc;
            --bg: #f1f5f9;
            --card: #ffffff;
            --border: #e2e8f0;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--gray-900);
            line-height: 1.75;
            margin: 0;
            padding: 0;
        }
        header.legal-header {
            background: #ffffff;
            border-bottom: 1px solid var(--border);
            padding: 1.25rem 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .header-inner {
            max-width: 1280px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .brand-link {
            text-decoration: none;
            color: var(--dark);
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 1.35rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .nav-link {
            color: var(--accent);
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .nav-link:hover { text-decoration: underline; }
        .legal-layout {
            max-width: 1280px;
            margin: 2.5rem auto;
            padding: 0 1.5rem;
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 2rem;
        }
        .legal-sidebar {
            background: var(--card);
            border-radius: 16px;
            padding: 1.5rem;
            border: 1px solid var(--border);
            height: fit-content;
            position: sticky;
            top: 5rem;
        }
        .sidebar-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--dark);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-menu {
            list-style: none;
        }
        .doc-menu-item {
            margin-bottom: 0.35rem;
        }
        .doc-menu-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0.65rem 0.85rem;
            border-radius: 10px;
            text-decoration: none;
            color: var(--gray-700);
            font-size: 0.92rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .doc-menu-link:hover {
            background: var(--gray-100);
            color: var(--accent);
        }
        .doc-menu-link.active {
            background: rgba(109, 40, 217, 0.08);
            color: var(--accent);
            font-weight: 700;
            border-left: 3px solid var(--accent);
        }
        .legal-main {
            background: var(--card);
            border-radius: 16px;
            padding: 3rem;
            border: 1px solid var(--border);
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        }
        .doc-badge {
            display: inline-block;
            background: #fdf2f8;
            color: var(--primary-dark);
            font-weight: 700;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 4px 12px;
            border-radius: 20px;
            margin-bottom: 1rem;
        }
        h1.doc-title {
            font-family: 'Outfit', sans-serif;
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 0.5rem;
            line-height: 1.25;
        }
        .doc-meta {
            color: var(--gray-500);
            font-size: 0.9rem;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border);
        }
        .legal-notice-box {
            background: #fff8f6;
            border: 1px solid #fed7aa;
            border-left: 4px solid #f97316;
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 2rem;
            font-size: 0.92rem;
            color: #9a3412;
            line-height: 1.6;
        }
        .legal-notice-box strong { font-weight: 700; color: #7c2d12; }
        .content-section {
            margin-bottom: 2.5rem;
        }
        h2.section-heading {
            font-family: 'Outfit', sans-serif;
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--dark);
            margin-top: 2rem;
            margin-bottom: 0.85rem;
        }
        h3.subsection-heading {
            font-family: 'Outfit', sans-serif;
            font-size: 1.15rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-top: 1.5rem;
            margin-bottom: 0.5rem;
        }
        p, ul, ol {
            color: var(--gray-700);
            font-size: 0.98rem;
            margin-bottom: 1.25rem;
        }
        ul, ol { padding-left: 1.5rem; }
        li { margin-bottom: 0.5rem; }
        .legal-footer-credit {
            margin-top: 4rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border);
            text-align: center;
            font-size: 0.88rem;
            color: var(--gray-500);
        }
        .print-btn {
            background: var(--gray-100);
            border: 1px solid var(--border);
            padding: 6px 14px;
            border-radius: 8px;
            color: var(--gray-700);
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            float: right;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .print-btn:hover { background: var(--gray-300); }
        .grid-docs {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.25rem;
            margin-top: 1.5rem;
        }
        .doc-card {
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.5rem;
            text-decoration: none;
            color: var(--dark);
            transition: all 0.2s ease;
        }
        .doc-card:hover {
            border-color: var(--accent);
            box-shadow: 0 8px 24px rgba(109, 40, 217, 0.08);
            transform: translateY(-2px);
        }
        .doc-card-icon { font-size: 1.8rem; margin-bottom: 0.75rem; }
        .doc-card-title { font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 1.1rem; margin-bottom: 0.35rem; }
        .doc-card-sub { color: var(--gray-500); font-size: 0.85rem; line-height: 1.4; }

        @media (max-width: 992px) {
            .legal-layout { grid-template-columns: 1fr; }
            .legal-sidebar { position: static; }
        }
        @media print {
            header.legal-header, .legal-sidebar, .print-btn { display: none !important; }
            .legal-layout { grid-template-columns: 1fr; margin: 0; padding: 0; }
            .legal-main { border: none; box-shadow: none; padding: 0; }
        }
    </style>
</head>
<body>

    <header class="legal-header">
        <div class="header-inner">
            <a href="{{ isset($tenant) ? url('/') : url('/') }}" class="brand-link">
                @if(isset($tenant) && $tenant->logo_path)
                    <img src="{{ asset($tenant->logo_path) }}" alt="{{ $tenant->name }}" style="height: 36px; border-radius: 4px;">
                @else
                    🧁
                @endif
                <span>{{ isset($tenant) ? $tenant->name : 'Doughmain.pro' }}</span>
            </a>
            <a href="{{ url('/') }}" class="nav-link">← Return to Main Site</a>
        </div>
    </header>

    <div class="legal-layout">
        <!-- Sidebar Navigation -->
        <aside class="legal-sidebar">
            <div class="sidebar-title">Legal Center</div>
            <ul class="doc-menu">
                @foreach($documents as $docSlug => $doc)
                    @php
                        $isCurrent = (isset($slug) && $slug === $docSlug) || (!isset($slug) && $docSlug === 'terms');
                        $linkUrl = isset($tenant) && $tenant->subdomain 
                            ? url('/site/' . $tenant->subdomain . '/legal/' . $docSlug)
                            : url('/legal/' . $docSlug);
                    @endphp
                    <li class="doc-menu-item">
                        <a href="{{ $linkUrl }}" class="doc-menu-link {{ $isCurrent ? 'active' : '' }}">
                            <span>{{ $doc['icon'] }}</span>
                            <span>{{ $doc['title'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </aside>

        <!-- Main Document Area -->
        <main class="legal-main">
            @php
                $activeSlug = $slug ?? 'terms';
                $activeDoc = $documents[$activeSlug] ?? $documents['terms'];
            @endphp

            <button class="print-btn" onclick="window.print();">🖨️ Print Document</button>
            <span class="doc-badge">{{ $activeDoc['category'] }}</span>
            <h1 class="doc-title">{{ $activeDoc['title'] }}</h1>
            <div class="doc-meta">
                <span>Doughmain.pro Legal &amp; Governance Standard &middot; Last Updated: {{ $activeDoc['updated'] }}</span>
            </div>

            <!-- Mandatory Legal Notice Header -->
            <div class="legal-notice-box">
                <strong>Important Legal Notice &amp; Ownership Disclosures:</strong><br>
                Doughmain.pro is a software product and service owned and operated exclusively by <strong>Daystar Digital LLC</strong> (a Delaware / US business entity). Use of any software, website builder tools, AI content generators, domain management features, or custom bakery storefronts hosted on Doughmain.pro is governed by these Terms and Policies.
            </div>

            <!-- DOCUMENT BODY SWITCHER -->
            @if($activeSlug === 'terms')
                <div class="content-section">
                    <h2 class="section-heading">1. Acceptance of Terms &amp; Scope of Agreement</h2>
                    <p>By registering, accessing, creating a bakery storefront, or utilizing any services provided at Doughmain.pro, you ("Customer", "User", or "Subscriber") enter into a binding legal contract with <strong>Daystar Digital LLC</strong> ("Company", "We", "Us", or "Doughmain.pro"). If you are entering into this Agreement on behalf of a company, bakery, or legal entity, you represent that you have full authority to bind such entity.</p>

                    <h2 class="section-heading">2. Description of Software Service</h2>
                    <p>Doughmain.pro provides an automated, AI-assisted software platform and website hosting builder tailored for bakeries and custom food businesses ("Software"). Doughmain.pro provides <strong>software tools only</strong>. Daystar Digital LLC does not manufacture food products, process physical deliveries, manage customer store operations, or manage customer business licenses.</p>

                    <h2 class="section-heading">3. Explicit Disclaimer of Business Results &amp; Warranties</h2>
                    <p>Purchasing a subscription to Doughmain.pro or creating a bakery website does <strong>NOT</strong> guarantee or promise any of the following:</p>
                    <ul>
                        <li>Increased sales, revenue, or business profitability</li>
                        <li>Increased website traffic, visits, or customer inquiries</li>
                        <li>Improved Search Engine Optimization (SEO) rankings or search placement</li>
                        <li>Indexing by Google, Bing, or any third-party search engines</li>
                        <li>AI generation accuracy, completeness, or flawlessness</li>
                        <li>100% uninterrupted platform uptime or fault-free server operations</li>
                    </ul>
                    <p>All marketing materials, templates, sample sites, performance calculators, and AI outputs are strictly <em>illustrative and non-binding</em>. Customer commercial success depends on marketing, product quality, pricing, and market conditions entirely outside Daystar Digital LLC's control.</p>

                    <h2 class="section-heading">4. AI-Generated Content Disclaimer</h2>
                    <p>Doughmain.pro incorporates artificial intelligence algorithms to generate sample website copy, product descriptions, and design suggestions. <strong>Customers are solely responsible</strong> for reviewing, editing, verifying, and approving all AI-generated content before publication. Customers are strictly responsible for food allergen disclosures, ingredient accuracy, pricing accuracy, copyright compliance, trademark compliance, and local health department regulations.</p>

                    <h2 class="section-heading">5. Account Termination &amp; Suspension at Sole Discretion</h2>
                    <p>Daystar Digital LLC reserves the right to suspend or terminate any account, customer website, domain routing, or subscription <strong>at its sole discretion, with or without notice, for any reason permitted by law</strong>, including but not limited to violation of these Terms, suspected security compromises, non-payment, or to protect platform infrastructure. Upon termination, website access will cease, and unpaid balances remain immediately due.</p>

                    <h2 class="section-heading">6. Customer Content Ownership &amp; Indemnification</h2>
                    <p>Customers retain ownership of original text, logos, and custom photography uploaded to the platform. Customers grant Daystar Digital LLC a worldwide, non-exclusive, royalty-free license to host, cache, transmit, and display customer content solely to provide the Service. Customers warrant they hold all necessary intellectual property rights to uploaded assets and agree to <strong>fully indemnify and hold harmless Daystar Digital LLC</strong> against any third-party copyright, trademark, or liability claims arising from customer content or business operations.</p>

                    <h2 class="section-heading">7. Limitation of Liability</h2>
                    <p>TO THE FULLEST EXTENT PERMITTED BY APPLICABLE U.S. LAW, DAYSTAR DIGITAL LLC AND DOUGHMAIN.PRO SHALL NOT BE LIABLE FOR ANY LOST PROFITS, LOST REVENUE, DATA LOSS, SEO PENALTIES, GOOGLE DE-INDEXING, THIRD-PARTY OUTAGES, AI ERRORS, OR ANY INDIRECT, INCIDENTAL, CONSEQUENTIAL, SPECIAL, OR PUNITIVE DAMAGES. TOTAL AGGREGATE LIABILITY FOR ALL CLAIMS ARISING UNDER THIS AGREEMENT IS LIMITED STRICTLY TO THE LESSER OF THE TOTAL SUBSCRIPTION FEES PAID BY THE CUSTOMER IN THE PRECEDING TWELVE (12) MONTHS OR ONE HUNDRED U.S. DOLLARS ($100.00 USD).</p>
                </div>

            @elseif($activeSlug === 'privacy')
                <div class="content-section">
                    <h2 class="section-heading">1. Information Collection &amp; Scope</h2>
                    <p>Doughmain.pro (Daystar Digital LLC) collects information necessary to provide SaaS website hosting and customer order management. We collect user names, emails, billing records, custom order submission data, IP addresses, and operational server logs.</p>

                    <h2 class="section-heading">2. Data Security &amp; Parameterized Query Protections</h2>
                    <p>We maintain technical safeguards against unauthorized access. All database interaction uses PDO parameterized binding (Eloquent ORM) to eliminate SQL injection vulnerabilities. Cross-Site Scripting (XSS) filters, secure HTTPS transport, and strict input sanitization are maintained continuously.</p>

                    <h2 class="section-heading">3. Customer Data Export &amp; Deletion Rights</h2>
                    <p>Under applicable U.S. and state privacy laws (such as CCPA/CPRA), users can request a full machine-readable export of stored data or request permanent account deletion via our Legal Data Endpoints (`/account/data-export` and `/account/delete-request`).</p>
                </div>

            @elseif($activeSlug === 'acceptable-use')
                <div class="content-section">
                    <h2 class="section-heading">1. Prohibited Conduct &amp; Content Standards</h2>
                    <p>Subscribers and visitors must not utilize Doughmain.pro servers or hosted bakery sites for any unlawful purpose. The following are strictly prohibited and result in immediate account termination:</p>
                    <ul>
                        <li>Malware distribution, phishing schemes, or spam transmission</li>
                        <li>Copyright or trademark infringement</li>
                        <li>Fraudulent bakery order schemes or deceptive billing practices</li>
                        <li>Automated scraping, reverse engineering, or platform security compromise attempts</li>
                        <li>Excessive resource utilization intended to cause Denial of Service (DoS)</li>
                    </ul>
                </div>

            @elseif($activeSlug === 'cookie-policy')
                <div class="content-section">
                    <h2 class="section-heading">1. Essential Cookies &amp; Local Storage</h2>
                    <p>Doughmain.pro uses essential first-party HTTP cookies (`doughmain_pro_session`, `XSRF-TOKEN`) solely for secure authentication, CSRF validation, and active shopping cart states. We do not sell tracking cookies to third-party ad brokers.</p>
                </div>

            @elseif($activeSlug === 'dmca')
                <div class="content-section">
                    <h2 class="section-heading">1. DMCA Copyright Takedown Procedure</h2>
                    <p>Daystar Digital LLC respects intellectual property rights. Copyright owners who believe content hosted on a Doughmain.pro site infringes their copyright may submit a formal DMCA Takedown Notice to our Designated Agent at <strong>dmca@daystardigital.co</strong> detailing the copyrighted work, URL location, and a statement under penalty of perjury.</p>
                </div>

            @elseif($activeSlug === 'refund-policy')
                <div class="content-section">
                    <h2 class="section-heading">1. Subscription Renewals &amp; Refund Rules</h2>
                    <p>Subscriptions renew automatically on a recurring monthly or annual basis until canceled via the Baker Admin Portal. Payments are non-refundable once a billing cycle commences. Cancellations take effect at the conclusion of the paid term.</p>
                </div>

            @elseif($activeSlug === 'sla')
                <div class="content-section">
                    <h2 class="section-heading">1. Target Uptime &amp; Service Disclaimer</h2>
                    <p>Daystar Digital LLC targets 99.5% core platform availability. Maintenance windows, upstream cloud DNS propagation, and third-party ISP outages are excluded from uptime metrics. Uptime is provided as a best-effort operational goal without monetary penalty guarantees.</p>
                </div>

            @elseif($activeSlug === 'dpa')
                <div class="content-section">
                    <h2 class="section-heading">1. Data Processing Addendum (DPA)</h2>
                    <p>This DPA governs the processing of customer order details and personal data on behalf of Subscribers. Daystar Digital LLC acts as a Data Processor, processing personal data strictly to deliver cloud hosting services in accordance with customer instructions.</p>
                </div>

            @elseif($activeSlug === 'ai-policy')
                <div class="content-section">
                    <h2 class="section-heading">1. AI Content Generation &amp; Allergen Warning</h2>
                    <p>AI tools on Doughmain.pro are provided as automated drafting aids. Artificial intelligence outputs may produce inaccurate or hallucinated text. <strong>Subscribers assume full liability</strong> for reviewing all AI-generated ingredient lists, allergy warnings, pricing, and text prior to publishing online.</p>
                </div>

            @elseif($activeSlug === 'domain-policy')
                <div class="content-section">
                    <h2 class="section-heading">1. Domain Ownership &amp; DNS Configuration</h2>
                    <p>Subscribers maintain full ownership of custom domains purchased through third-party registrars. DNS propagation delays (up to 48 hours) are inherent to internet protocol and outside Daystar Digital LLC's control. Automatic SSL certificate issuance requires correct DNS A/CNAME record configuration.</p>
                </div>
            @endif

            <div class="legal-footer-credit">
                Doughmain.pro is a product and service of <strong>Daystar Digital LLC</strong> &middot; US Legal Governance Standard &middot; © 2026 Daystar Digital LLC. All rights reserved.
            </div>
        </main>
    </div>

</body>
</html>
