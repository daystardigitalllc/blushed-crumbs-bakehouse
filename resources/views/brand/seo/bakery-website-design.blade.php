@extends('layouts.brand')

@section('title', 'Bakery Website Design: Themes & Inspiration')
@section('meta_description', 'Discover stunning bakery website design templates. Learn how to design a high-converting website for your custom cake or retail bakery. Create yours free!')

@section('content')
<section class="page-hero">
    <div class="container">
        <span class="eyebrow">🎨 Premium Aesthetics</span>
        <h1>Bakery Website Designs Built to Sell More Treats</h1>
        <p>A great bakery website doesn't just look pretty—it makes ordering your cakes and pastries effortless. Explore our premium, responsive design themes created specifically for bakers.</p>
        <div style="display: flex; justify-content: center; gap: 16px; flex-wrap: wrap;">
            <a href="/register" class="btn btn-primary">Choose a Design Free →</a>
            <a href="{{ route('examples') }}" class="btn btn-light">See Live Demo Sites</a>
        </div>
    </div>
</section>

<section class="section-padding" style="background-color: var(--white);">
    <div class="container">
        <div class="section-header">
            <span>Essential Design Elements</span>
            <h2>What makes a bakery website design successful?</h2>
            <p>We build these fundamental conversion boosters directly into every Doughmain design template.</p>
        </div>

        <div class="grid-3">
            <div class="card">
                <span class="card-icon">📸</span>
                <h3>Visual Portfolio Galleries</h3>
                <p>High-resolution, fast-loading image grids that showcase the details of your wedding cakes, cupcakes, and cookies beautifully.</p>
            </div>
            <div class="card">
                <span class="card-icon">📱</span>
                <h3>Mobile-First Layouts</h3>
                <p>Over 80% of local bakery searches happen on mobile phones. Our designs are optimized for flawless navigation and checkout on screens of all sizes.</p>
            </div>
            <div class="card">
                <span class="card-icon">🛒</span>
                <h3>Frictionless Custom Orders</h3>
                <p>Structured selectors that replace vague contact forms and endless Instagram DMs. Guide customers step-by-step through order details.</p>
            </div>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="section-header">
            <span>Design Themes Showcase</span>
            <h2>Premium templates ready to customize</h2>
            <p>Select a design structure and make it uniquely yours. Change colors, logos, and fonts with a single click.</p>
        </div>

        <div class="grid-3">
            <div class="card" style="border-top: 4px solid var(--primary-pink);">
                <div style="background-color: var(--primary-pink-light); height: 180px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin-bottom: 20px;">🌸</div>
                <h3>Sweet Elegant</h3>
                <p>Soft colors, delicate script typography, and spacious grids. Perfect for high-end wedding cake boutiques and custom sugar cookie artists.</p>
            </div>
            <div class="card" style="border-top: 4px solid var(--deep-purple);">
                <div style="background-color: var(--deep-purple-light); height: 180px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin-bottom: 20px;">⚡</div>
                <h3>Modern Bold</h3>
                <p>High contrast headers, dark overlay menus, and asymmetrical grids. Excellent for trendy city bakeries, cupcake bars, and pastry startups.</p>
            </div>
            <div class="card" style="border-top: 4px solid #854d0e;">
                <div style="background-color: #fef9c3; height: 180px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin-bottom: 20px;">🌾</div>
                <h3>Rustic Artisanal</h3>
                <p>Warm tones, serif typography, and textured borders. Ideal for sourdough micro-bakeries, farm-to-table bakeshops, and classic family pastry shops.</p>
            </div>
        </div>
    </div>
</section>

<section class="section-padding" style="background-color: var(--white);">
    <div class="container">
        <div class="grid-2">
            <div>
                <span style="color:var(--primary-pink); font-weight:700; text-transform:uppercase; font-size:0.9rem;">Branding Made Simple</span>
                <h2 style="margin-top: 10px;">Customize your website to match your baking brand</h2>
                <p style="margin-bottom: 24px; color: var(--text-gray);">You don't need a professional graphic designer or developer. Doughmain makes it easy to apply your branding directly to your storefront.</p>
                
                <ul style="list-style: none; line-height: 2.2; margin-bottom: 28px;">
                    <li>✨ <strong>Instant Logo Upload:</strong> Center or align your logo file dynamically.</li>
                    <li>✨ <strong>Tailored Color Palettes:</strong> Match your frosting box, brand identity, or theme perfectly.</li>
                    <li>✨ <strong>Google Fonts Integration:</strong> Choose clean sans-serifs or elegant handwriting fonts for headings.</li>
                    <li>✨ <strong>Instagram Syncing:</strong> Keep your gallery fresh by embedding your Instagram posts.</li>
                </ul>
                
                <a href="/register" class="btn btn-secondary">Design Your Site Free →</a>
            </div>
            <div style="background-color: var(--bg-light); border-radius: 20px; padding: 40px; border: 1.5px solid var(--border-color); text-align: center;">
                <h3 style="margin-bottom: 12px; color: var(--deep-purple);">"Doughmain helped me look like a professional storefront overnight."</h3>
                <p style="font-style: italic; color: var(--text-gray); margin-bottom: 24px;">"I was taking custom wedding cake orders through my Facebook inbox. Once I set up my website, clients started trusting my prices and paying deposits online without questions."</p>
                <strong>- Sarah Mitchell</strong>
                <span style="display: block; color: var(--text-muted); font-size: 0.85rem;">Owner, Sugar &amp; Slate Bakery</span>
            </div>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="section-header">
            <span>Design FAQs</span>
            <h2>Frequently Asked Questions</h2>
            <p>Got questions about customization? We have answers.</p>
        </div>

        <div class="faq-grid">
            <div class="faq-item">
                <h3>Can I change my website theme later?</h3>
                <p>Yes. You can switch your site's theme or styling configuration at any time from your admin dashboard without losing your products, reviews, or orders.</p>
            </div>
            <div class="faq-item">
                <h3>Are the designs responsive for mobile phones?</h3>
                <p>Absolutely. Every single block, menu layout, form element, and checkout button is specifically engineered to adapt to smartphones, tablets, and desktops.</p>
            </div>
            <div class="faq-item">
                <h3>Can I add a custom logo and matching brand colors?</h3>
                <p>Yes, our dashboard allows you to upload high-quality PNG logos, choose your layout theme, and select cohesive accent colors to make the site fit your exact brand look.</p>
            </div>
        </div>
    </div>
</section>
@endsection
