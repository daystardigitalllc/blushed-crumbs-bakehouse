@extends('layouts.brand')

@section('title', 'Home Bakery Website Builder: Cottage Food Optimized')
@section('meta_description', 'Start your home bakery website for free. Built for cottage food operations with weekly order capping, lead times, local pickup slots, and Stripe. No code required!')

@section('content')
<section class="page-hero">
    <div class="container">
        <span class="eyebrow">🏡 Built for Home Bakers</span>
        <h1>Launch Your Home Bakery Website (For Free)</h1>
        <p>Running a bakery from your home kitchen requires unique tools. Doughmain is optimized for cottage food micro-bakers. Manage local pickups, restrict order capacity, and collect deposits with ease.</p>
        <div style="display: flex; justify-content: center; gap: 16px; flex-wrap: wrap;">
            <a href="/register" class="btn btn-primary">Start Your Home Bakery Free →</a>
            <a href="{{ route('cottage-food-laws.index') }}" class="btn btn-light">Lookup Cottage Food Laws</a>
        </div>
    </div>
</section>

<section class="section-padding" style="background-color: var(--white);">
    <div class="container">
        <div class="section-header">
            <span>Tailored for Cottage Food</span>
            <h2>Features home-based micro-bakers actually need</h2>
            <p>Generic storefronts assume you ship nationwide. Doughmain is built for local pickups, custom orders, and kitchen capacity management.</p>
        </div>

        <div class="grid-3">
            <div class="card">
                <span class="card-icon">🛑</span>
                <h3>Weekly Order Limits</h3>
                <p>Prevent burnout. Set maximum order capacities per week or weekend. Once reached, your checkout automatically blocks new orders.</p>
            </div>
            <div class="card">
                <span class="card-icon">🗺️</span>
                <h3>Cottage Food Compliant</h3>
                <p>Add required disclaimers (e.g. "Made in a home kitchen that is not subject to state inspection") directly at checkout and on invoices.</p>
            </div>
            <div class="card">
                <span class="card-icon">📍</span>
                <h3>Secure Pickup Schedules</h3>
                <p>Only show your street address *after* a client pays a deposit. Manage structured pickup windows (e.g. Saturday 10 AM - 12 PM) to protect your home privacy.</p>
            </div>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="grid-2">
            <div>
                <span style="color:var(--primary-pink); font-weight:700; text-transform:uppercase; font-size:0.9rem;">Own Your Brand</span>
                <h2 style="margin-top: 10px;">Why home bakeries fail relying on Instagram DMs</h2>
                <p style="margin-bottom: 24px; color: var(--text-gray);">Taking orders via direct messages or comments is hard to scale and looks amateur. Here is how owning a dedicated home bakery website changes your business:</p>
                
                <div style="margin-bottom: 16px; border-left: 3px solid var(--primary-pink); padding-left: 16px;">
                    <strong>Double Your Booking Rates:</strong> Clients can see your menu, pricing, and availability immediately, ordering without waiting hours for a reply.
                </div>
                <div style="margin-bottom: 16px; border-left: 3px solid var(--primary-pink); padding-left: 16px;">
                    <strong>Eliminate Non-Payers:</strong> Require a 50% deposit via Stripe to confirm a booking. No more baking cakes that clients never pick up.
                </div>
                <div style="margin-bottom: 24px; border-left: 3px solid var(--primary-pink); padding-left: 16px;">
                    <strong>Professional Look:</strong> A custom domain name (e.g. yourbakery.com) gives you instant credibility, allowing you to charge professional prices.
                </div>
                
                <a href="/register" class="btn btn-secondary">Create My Home Bakery Site →</a>
            </div>
            <div style="background-color: var(--white); border-radius: 20px; padding: 40px; border: 1.5px solid var(--border-color); box-shadow: var(--shadow-sm);">
                <h3 style="margin-bottom: 20px; text-align: center; border-bottom: 2px solid var(--primary-pink-light); padding-bottom: 12px;">Cottage Food Specific Tools</h3>
                <ul style="list-style: none; line-height: 2.2;">
                    <li>🧁 Custom state food law helper links</li>
                    <li>🧁 Automated 50% deposit requests</li>
                    <li>🧁 Customizable delivery radiuses</li>
                    <li>🧁 Blackout dates for vacation/prep</li>
                    <li>🧁 Customer order history dashboard</li>
                    <li>🧁 Mobile invoicing on the go</li>
                    <li>🧁 Standard terms and conditions template</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="section-padding" style="background-color: var(--white);">
    <div class="container">
        <div class="section-header">
            <span>Home Baker FAQs</span>
            <h2>Frequently Asked Questions</h2>
            <p>Answers to common questions about running a home bakery website.</p>
        </div>

        <div class="faq-grid">
            <div class="faq-item">
                <h3>Do I need a commercial kitchen to use Doughmain?</h3>
                <p>No! Doughmain is specifically designed for cottage food home bakers, micro-bakers, and commercial operations alike. We have settings tailored for home-based pickups.</p>
            </div>
            <div class="faq-item">
                <h3>Can I control my pickup schedule?</h3>
                <p>Yes. You can configure specific pickup slots and block out holidays, vacation days, or busy weekdays so clients can only select dates when you are baking.</p>
            </div>
            <div class="faq-item">
                <h3>Is credit card processing secure?</h3>
                <p>We integrate directly with Stripe, a global leader in payment processing. All payment details are encrypted, and funds go directly to your personal bank account.</p>
            </div>
        </div>
    </div>
</section>
@endsection
