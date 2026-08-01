@extends('layouts.brand')

@section('title', 'Best Bakery Website Builder: Launch in Minutes')
@section('meta_description', 'Build a professional bakery website with Doughmain. Custom cake order forms, calendar lead times, Stripe payments, and 0% commission. Try for free!')

@section('content')
<section class="page-hero">
    <div class="container">
        <span class="eyebrow">✨ The Ultimate Bakery Builder</span>
        <h1>The Only Website Builder Built Specifically for Bakers</h1>
        <p>Most website builders are made for bloggers or ecommerce stores selling t-shirts. Doughmain is the only drag-and-drop website builder designed from the ground up for custom cake makers and artisanal bakers.</p>
        <div style="display: flex; justify-content: center; gap: 16px; flex-wrap: wrap;">
            <a href="/register" class="btn btn-primary">Start Building Free →</a>
            <a href="{{ route('examples') }}" class="btn btn-light">View Example Websites</a>
        </div>
    </div>
</section>

<section class="section-padding" style="background-color: #fff;">
    <div class="container">
        <div class="section-header">
            <span>Built For Your Kitchen</span>
            <h2>Why Bakers Love Doughmain over generic builders</h2>
            <p>We built the tools you actually need to run your business, not generic templates.</p>
        </div>

        <div class="grid-3">
            <div class="card">
                <span class="card-icon">🎂</span>
                <h3>Custom Cake Form Builder</h3>
                <p>Let clients choose flavors, fillings, frostings, tiers, and even upload reference images directly on your site.</p>
            </div>
            <div class="card">
                <span class="card-icon">📅</span>
                <h3>Lead Time Calendar</h3>
                <p>Prevent last-minute bookings automatically. Set minimum lead times (e.g. 3 days) and block out fully booked dates.</p>
            </div>
            <div class="card">
                <span class="card-icon">💰</span>
                <h3>0% Transaction Fees</h3>
                <p>Unlike third-party marketplaces, we charge absolute 0% commission on your sales. Keep 100% of your hard-earned profit.</p>
            </div>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="grid-2">
            <div>
                <span style="color:var(--primary-pink); font-weight:700; text-transform:uppercase; font-size:0.9rem;">Simple 3-Step Setup</span>
                <h2 style="margin-top: 10px;">How to build your bakery website in 5 minutes</h2>
                <p style="margin-bottom: 24px; color: var(--text-gray);">You don't need design skills or coding experience. Our onboarding wizard guides you through setting up a professional website optimized for search engines and high conversion.</p>
                
                <div style="margin-bottom: 20px;">
                    <strong style="color: var(--deep-purple); font-size: 1.1rem; display: block; margin-bottom: 4px;">1. Connect Your Bakery Details</strong>
                    <span style="color: var(--text-gray);">Enter your business name, contact info, pickup/delivery options, and social media handles.</span>
                </div>
                <div style="margin-bottom: 20px;">
                    <strong style="color: var(--deep-purple); font-size: 1.1rem; display: block; margin-bottom: 4px;">2. Add Your Cakes &amp; Treats</strong>
                    <span style="color: var(--text-gray);">Upload mouth-watering photos of your custom cakes, cookies, and pastries. Define base prices or customized quote forms.</span>
                </div>
                <div style="margin-bottom: 24px;">
                    <strong style="color: var(--deep-purple); font-size: 1.1rem; display: block; margin-bottom: 4px;">3. Set Your Schedule &amp; Launch</strong>
                    <span style="color: var(--text-gray);">Connect your Stripe account to collect deposits, choose your theme color, and publish instantly on your free subdomain or custom domain.</span>
                </div>
                
                <a href="/register" class="btn btn-secondary">Get Started Now →</a>
            </div>
            <div style="background-color: var(--white); padding: 40px; border-radius: 20px; border: 1.5px solid var(--border-color); box-shadow: var(--shadow-md);">
                <h3 style="margin-bottom: 20px; text-align: center; border-bottom: 2px solid var(--primary-pink-light); padding-bottom: 12px;">Website Features Included</h3>
                <ul style="list-style: none; line-height: 2.2;">
                    <li>✅ Free SSL Security Certificate</li>
                    <li>✅ Mobile Responsive Design Templates</li>
                    <li>✅ High-Speed Free Web Hosting</li>
                    <li>✅ Direct Client Invoice Generator</li>
                    <li>✅ Interactive Bakery Gallery</li>
                    <li>✅ Stripe Checkout Integration</li>
                    <li>✅ Customer Booking Calendar Management</li>
                    <li>✅ Built-in Baker CRM &amp; Contact list</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="section-padding" style="background-color: var(--white);">
    <div class="container">
        <div class="section-header">
            <span>Common Questions</span>
            <h2>Frequently Asked Questions</h2>
            <p>Everything you need to know about the Doughmain website builder.</p>
        </div>

        <div class="faq-grid">
            <div class="faq-item">
                <h3>Is the website builder really free?</h3>
                <p>Yes! Our base plan is free forever. You get a fully functional bakery website on our subdomain (e.g. yourbakery.doughmain.pro). You can upgrade to our Pro plan anytime to connect your own custom domain (e.g. yourbakery.com) and unlock advanced features.</p>
            </div>
            <div class="faq-item">
                <h3>Can I accept custom cake orders and deposits?</h3>
                <p>Absolutely. Doughmain has a built-in custom order form system. Clients can submit cake specifications, and you can generate a custom invoice and collect online payments/deposits securely via Stripe.</p>
            </div>
            <div class="faq-item">
                <h3>Do I need web hosting or design experience?</h3>
                <p>No design or hosting experience needed! We host the site for you for free on our fast servers, and our setup wizard automatically designs a beautiful layout based on your bakery details.</p>
            </div>
        </div>
    </div>
</section>
@endsection
