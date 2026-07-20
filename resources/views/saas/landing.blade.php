@extends('layouts.app')

@section('title', 'BakeBox SaaS | All-in-One Bakery Management & Custom Storefront Platform')

@section('content')
<header class="site-header">
    <div class="header-container">
        <a href="/" class="logo">🧁 BakeBox SaaS</a>
        <nav class="nav-links">
            <a href="#features">Features</a>
            <a href="#pricing">Pricing</a>
            <a href="#demo">Live Demo</a>
            <a href="https://blushedcrumbsbakehouse.com" target="_blank" class="admin-btn">View Client Site ↗</a>
        </nav>
    </div>
</header>

<section class="hero-section" style="background: linear-gradient(135deg, #6f42c1 0%, #b35978 100%); color: white;">
    <div class="hero-content">
        <span class="subheading" style="color: #ffd6e0;">Powered by Daystar Digital</span>
        <h1 style="color: white; font-family: 'Poppins', sans-serif; font-size: 3rem;">The Bakesy Competitor Built for Artisanal Bakers</h1>
        <p style="font-size: 1.2rem; max-width: 700px; margin: 20px auto;">Everything in one place: custom order builders, due-date priority order queues, mobile invoicing with Venmo/CashApp, product management, and reviews.</p>
        <div class="hero-buttons">
            <a href="#pricing" class="btn btn-primary" style="background: white; color: #6f42c1;">Start 14-Day Free Trial</a>
        </div>
    </div>
</section>

<section id="pricing" class="categories-section" style="max-width: 1000px; margin: 60px auto;">
    <h2 class="section-title">Simple Subscription Pricing</h2>
    <div class="categories-grid">
        <div class="category-card" style="padding: 30px; text-align: left;">
            <h3>Standard Tier</h3>
            <h2 style="font-size: 2.5rem; color: var(--primary); margin: 15px 0;">$29 <span style="font-size: 1rem; color: #666;">/ month</span></h2>
            <ul>
                <li>✓ Custom Branded Bakery Website</li>
                <li>✓ 12-Step Custom Cake Builder</li>
                <li>✓ Priority Due-Date Order Queue</li>
                <li>✓ Digital Invoicing & Payment Links</li>
                <li>✓ No-Code Product & Pricing Editor</li>
            </ul>
        </div>
        <div class="category-card" style="padding: 30px; text-align: left; border: 3px solid #6f42c1;">
            <span class="badge badge-pro" style="float: right;">MOST POPULAR</span>
            <h3>Pro Tier</h3>
            <h2 style="font-size: 2.5rem; color: #6f42c1; margin: 15px 0;">$50 <span style="font-size: 1rem; color: #666;">/ month</span></h2>
            <ul>
                <li>✓ Everything in Standard</li>
                <li>✓ Custom Domain Integration</li>
                <li>✓ Automated SMS Reminders</li>
                <li>✓ Priority Custom Code Concierge</li>
            </ul>
        </div>
    </div>
</section>
@endsection
