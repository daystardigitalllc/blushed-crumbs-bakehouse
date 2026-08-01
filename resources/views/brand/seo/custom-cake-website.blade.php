@extends('layouts.brand')

@section('title', 'Custom Cake Website: Build Your Order Forms')
@section('meta_description', 'Launch a professional custom cake website. Collect serving sizes, cake flavors, frosting options, inspiration photos, and Stripe deposits easily. Get started for free!')

@section('content')
<section class="page-hero">
    <div class="container">
        <span class="eyebrow">🎂 Designed for Cake Artists</span>
        <h1>The Ultimate Website Builder for Custom Cake Bakers</h1>
        <p>Custom cakes aren't standard retail products. They require detailed planning, flavor consultations, and reference images. Doughmain builds multi-step order forms right into your website so you collect the right details the first time.</p>
        <div style="display: flex; justify-content: center; gap: 16px; flex-wrap: wrap;">
            <a href="/register" class="btn btn-primary">Build Your Cake Website Free →</a>
            <a href="{{ route('examples') }}" class="btn btn-light">View Demo Cake Sites</a>
        </div>
    </div>
</section>

<section class="section-padding" style="background-color: var(--white);">
    <div class="container">
        <div class="section-header">
            <span>Structured Inquiries</span>
            <h2>Why custom cake bakers need specialized websites</h2>
            <p>Traditional checkout carts fail for custom baking. Here is how Doughmain solves cake order management.</p>
        </div>

        <div class="grid-3">
            <div class="card">
                <span class="card-icon">🍰</span>
                <h3>Sizing &amp; Serving Selector</h3>
                <p>Allow clients to select single tier, multi-tier, or sheet cakes, automatically estimating serving counts based on industry averages.</p>
            </div>
            <div class="card">
                <span class="card-icon">🎨</span>
                <h3>Inspiration Image Uploads</h3>
                <p>Clients can upload up to 5 reference photos (from Pinterest or Instagram) directly through your contact form so you can see their exact design intent.</p>
            </div>
            <div class="card">
                <span class="card-icon">💳</span>
                <h3>Deposit &amp; Invoicing</h3>
                <p>Accept custom cake inquiries, review the details, calculate the final price, and send a click-to-pay Stripe invoice with a required 50% deposit.</p>
            </div>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="grid-2">
            <div>
                <span style="color:var(--primary-pink); font-weight:700; text-transform:uppercase; font-size:0.9rem;">Perfecting Custom Quotes</span>
                <h2 style="margin-top: 10px;">Stop wasting hours on endless back-and-forth emails</h2>
                <p style="margin-bottom: 24px; color: var(--text-gray);">Custom cake makers spend hours answering basic questions about flavor, pricing, and availability. Doughmain automates this inquiry funnel:</p>
                
                <div style="margin-bottom: 20px;">
                    <strong style="color: var(--deep-purple); font-size: 1.1rem; display: block; margin-bottom: 4px;">1. Client Submits Structured Form</strong>
                    <span style="color: var(--text-gray);">The client selects their due date, serving size, delivery preferences, sponge flavor, filling, frosting, and uploads inspiration files.</span>
                </div>
                <div style="margin-bottom: 20px;">
                    <strong style="color: var(--deep-purple); font-size: 1.1rem; display: block; margin-bottom: 4px;">2. Review &amp; Edit Quote</strong>
                    <span style="color: var(--text-gray);">You receive a clean quote in your baker CRM. Adjust the final price based on the complexity of the custom decorations.</span>
                </div>
                <div style="margin-bottom: 24px;">
                    <strong style="color: var(--deep-purple); font-size: 1.1rem; display: block; margin-bottom: 4px;">3. Direct Invoicing &amp; Retainers</strong>
                    <span style="color: var(--text-gray);">Send an invoice with a single click. The client receives an email link to pay their deposit online via Stripe, securing the date on your calendar.</span>
                </div>
                
                <a href="/register" class="btn btn-secondary">Get Your Custom Cake Form →</a>
            </div>
            <div style="background-color: var(--white); border-radius: 20px; padding: 40px; border: 1.5px solid var(--border-color); box-shadow: var(--shadow-sm);">
                <h3 style="margin-bottom: 20px; text-align: center; border-bottom: 2px solid var(--primary-pink-light); padding-bottom: 12px;">Cake Form Customization</h3>
                <ul style="list-style: none; line-height: 2.2;">
                    <li>🎂 Customizable Flavor Lists (e.g. Vanilla, Red Velvet)</li>
                    <li>🎂 Filling Options (e.g. Raspberry Compote, Lemon Curd)</li>
                    <li>🎂 Frosting Customizer (e.g. Swiss Meringue Buttercream)</li>
                    <li>🎂 Dietary Restrictions checkboxes (Gluten-Free, Vegan)</li>
                    <li>🎂 Allergy Warnings disclosure</li>
                    <li>🎂 Custom Terms and Cancellation Policies</li>
                    <li>🎂 Auto-saving drafts to prevent customer exit</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="section-padding" style="background-color: var(--white);">
    <div class="container">
        <div class="section-header">
            <span>Cake Baker FAQs</span>
            <h2>Frequently Asked Questions</h2>
            <p>Everything you need to know about setting up a website for your custom cake business.</p>
        </div>

        <div class="faq-grid">
            <div class="faq-item">
                <h3>Can I customize the cake sizes and flavors?</h3>
                <p>Yes. You have complete control over the options presented to the customer. You can add, edit, or remove flavors, fillings, and sizes from your admin settings panel.</p>
            </div>
            <div class="faq-item">
                <h3>How do clients send me reference cake photos?</h3>
                <p>Our custom cake form allows users to upload file attachments (JPG, PNG, WebP) directly when submitting their order inquiry, which are stored securely in your dashboard.</p>
            </div>
            <div class="faq-item">
                <h3>Can I require a deposit to hold a date?</h3>
                <p>Yes. The platform allows you to automatically calculate a deposit amount (e.g. 50%) and require payment online before the order changes status to "confirmed".</p>
            </div>
        </div>
    </div>
</section>
@endsection
