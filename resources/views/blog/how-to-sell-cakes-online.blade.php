@extends('layouts.brand')

@section('title', $post['title'] . ' | Doughmain Blog')
@section('meta_description', $post['description'])

@section('content')
<div class="blog-post-container">
    <div class="blog-post-header">
        <div class="blog-post-meta">
            <span>{{ $post['category'] }}</span> &middot; <span>{{ $post['date'] }}</span> &middot; <span>{{ $post['read_time'] }}</span>
        </div>
        <h1>{{ $post['title'] }}</h1>
        <div class="blog-author-card">
            <div class="blog-author-avatar">D</div>
            <div>
                <strong>Doughmain Editorial Team</strong>
                <span style="display: block; font-size: 0.8rem;">Baking Business Experts</span>
            </div>
        </div>
    </div>

    <div class="blog-content">
        <p>Custom baking is one of the fastest-growing culinary business sectors. But if you want to scale your operations, you have to transition from taking orders in casual text messages to a structured online store. Knowing <strong>how to sell cakes online</strong> is the key to boosting your sales and reclaim your evenings.</p>

        <p>Unlike standard ecommerce products, cakes are fragile, customizable, and have strict delivery schedules. In this step-by-step guide, we will lay out the blueprint for launching a high-converting, legally compliant online cake ordering system.</p>

        <h2>Step 1: Understand Your Local Regulations</h2>
        <p>Before you take your first online order, verify your local laws. In the United States, most states allow home bakers to operate under **Cottage Food Laws**. These regulations define:</p>
        <ul>
            <li>What types of baked goods you can sell (typically non-hazardous products like cakes, cookies, and breads).</li>
            <li>Annual sales limits (ranging from $5,000 to unlimited depending on the state).</li>
            <li>Required packaging labels and kitchen inspections.</li>
        </ul>
        <p>Ensure your website includes any legally required disclaimers about being prepared in a home kitchen.</p>

        <h2>Step 2: Move Away from Instagram DMs</h2>
        <p>Many custom cake makers launch their business on social media. While Instagram is perfect for visual marketing, taking order inquiries in your DMs creates huge operational headaches:</p>
        <blockquote style="background-color: var(--primary-pink-light); border-left-color: var(--primary-pink);">
            "What date did you need the cake for?" "What size?" "Any allergies?" "Here is my price..." "Are you still interested?"
        </blockquote>
        <p>This endless back-and-forth leads to lost inquiries, scheduling mistakes, and customer frustration. Moving your booking process to a professional, structured website form allows you to capture all these details automatically.</p>

        <h2>Step 3: Build a Structured Order Funnel</h2>
        <p>A cake ordering form should guide your customer step-by-step. To prepare an accurate quote, your website must collect:</p>
        <ol>
            <li><strong>Fulfillment Date:</strong> Integrate a calendar so clients cannot book holidays or busy weekends.</li>
            <li><strong>Sizing &amp; Serving Guide:</strong> Let clients select the serving count they need, reducing size confusion.</li>
            <li><strong>Flavor Combinations:</strong> Standardize lists of sponge flavors, frostings, and fillings.</li>
            <li><strong>Inspiration uploads:</strong> Allow uploading Pinterest photos or sketches so you can visualize the theme.</li>
        </ol>

        <h2>Step 4: Secure Deposits to Prevent No-Shows</h2>
        <p>Every home baker has a horror story of a client ordering a custom cake and never showing up to pick it up. **Always require a deposit (usually 50%) to secure a booking date.**</p>
        <p>Using online payment processors like Stripe allows you to generate invoices and collect credit card payments instantly, ensuring you are compensated for ingredients and prep work before you turn on your oven.</p>

        <h2>Step 5: Set Strict Delivery &amp; Pickup Logistics</h2>
        <p>Define your pickup address and instructions clearly, and specify delivery zones (e.g., within a 20-mile radius) with appropriate mileage charges calculated at checkout.</p>

        <div class="blog-cta-box">
            <h3>Start Selling Cakes Online Effortlessly</h3>
            <p>Doughmain provides custom cake order forms, automated deposit requests, booking calendar blockers, and 0% commission storefronts. Build your cake website completely free.</p>
            <a href="/register" class="btn btn-primary">Create Your Cake Store Free →</a>
        </div>
    </div>
</div>
@endsection
