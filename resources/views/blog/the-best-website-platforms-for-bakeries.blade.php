@extends('layouts.brand')

@section('title', $post['title'] . ' | Doughmain Blog')
@section('meta_description', $post['description'])

@section('schema')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BlogPosting",
  "headline": "{{ $post['title'] }}",
  "description": "{{ $post['description'] }}",
  "image": "{{ asset('images/og_image.jpg') }}",
  "author": {
    "@type": "Organization",
    "name": "Doughmain"
  },
  "publisher": {
    "@type": "Organization",
    "name": "Doughmain",
    "logo": {
      "@type": "ImageObject",
      "url": "{{ asset('images/doughmain_logo.png') }}"
    }
  },
  "datePublished": "2026-07-16",
  "mainEntityOfPage": {
    "@type": "WebPage",
    "@id": "{{ url()->current() }}"
  }
}
</script>
@endsection

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
        <p>Choosing the right online platform for your bakery is one of the most important decisions you'll make. A slow, complicated website will turn hungry customers away, while a platform that lacks scheduling features will leave you overwhelmed with last-minute order requests.</p>

        <p>In this guide, we review and compare <strong>the best website platforms for bakeries</strong>—analyzing Squarespace, Shopify, Wix, Bakesy, and Doughmain—to help you find the perfect match for your business model.</p>

        <h2>1. Squarespace (Best for General Portfolios)</h2>
        <p>Squarespace is famous for its clean, image-heavy design templates. It's a favorite among local service businesses and artists.</p>
        <ul>
            <li><strong>Pros:</strong> Stunning templates, simple drag-and-drop editor, built-in blogging tools.</li>
            <li><strong>Cons:</strong> Starting at $23/mo (billed annually), it lacks specific bakery scheduling rules, custom cake builder step-forms, or cottage food compliance options. You must pay extra for scheduling integrations.</li>
        </ul>

        <h2>2. Shopify (Best for Nationwide Shipping)</h2>
        <p>Shopify is the undisputed king of general ecommerce. If you are shipping cookies or baking mixes nationwide, Shopify is an excellent choice.</p>
        <ul>
            <li><strong>Pros:</strong> Powerful inventory, dozens of payment processors, thousands of app extensions.</li>
            <li><strong>Cons:</strong> Expensive ($39/mo base + theme costs + app costs), complex setup, and not designed for local pickups or custom cake quoting.</li>
        </ul>

        <h2>3. Wix (Best for Complete Layout Control)</h2>
        <p>Wix is a highly flexible website builder with pixel-perfect design control.</p>
        <ul>
            <li><strong>Pros:</strong> Huge selection of templates, complete editing freedom, free plan available.</li>
            <li><strong>Cons:</strong> Editor can be slow and overwhelming, templates are not interchangeable (you can't change themes without starting over), and it lacks custom order inquiry pipelines.</li>
        </ul>

        <h2>4. Bakesy (Best for Closed App Cataloging)</h2>
        <p>Bakesy is a mobile-first app designed to help cottage bakers track order bookings.</p>
        <ul>
            <li><strong>Pros:</strong> Simple mobile interface, direct customer messaging, fast setup.</li>
            <li><strong>Cons:</strong> Closed ecosystem. You cannot connect your own custom domain (e.g. <code>yourbakery.com</code>), sites are not optimized for Google SEO ranking, and they charge additional platform transaction commissions.</li>
        </ul>

        <h2>5. Doughmain (Best for Custom &amp; Cottage Bakers)</h2>
        <p>Doughmain is the only platform built exclusively for custom cake artists, home bakers, and artisanal micro-bakeshops.</p>
        <ul>
            <li><strong>Pros:</strong> 0% platform commission fees, 12-step custom cake builder forms, calendar lead times and weekly order limits, custom domain connections, and a free-forever hosting option.</li>
            <li><strong>Cons:</strong> Focused solely on bakeries and pastry chefs, not general ecommerce.</li>
        </ul>

        <h2>Comparison Summary</h2>
        <div class="compare-container" style="margin: 30px 0;">
            <table class="compare-table">
                <thead>
                    <tr>
                        <th>Platform</th>
                        <th>Base Price</th>
                        <th>0% Fees</th>
                        <th>Bakery Tools</th>
                        <th>Custom Domain</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="feature-title">Squarespace</td>
                        <td>$23 - $49/mo</td>
                        <td><span class="check-icon">✓</span></td>
                        <td><span class="cross-icon">✗</span> (Needs apps)</td>
                        <td><span class="check-icon">✓</span></td>
                    </tr>
                    <tr>
                        <td class="feature-title">Shopify</td>
                        <td>$39 - $399/mo</td>
                        <td><span class="cross-icon">✗</span> (Transaction fees)</td>
                        <td><span class="cross-icon">✗</span> (Needs apps)</td>
                        <td><span class="check-icon">✓</span></td>
                    </tr>
                    <tr>
                        <td class="feature-title">Bakesy</td>
                        <td>$20/mo</td>
                        <td><span class="cross-icon">✗</span> (Platform commission)</td>
                        <td><span class="check-icon">✓</span></td>
                        <td><span class="cross-icon">✗</span></td>
                    </tr>
                    <tr class="highlight">
                        <td class="feature-title">Doughmain Pro</td>
                        <td>$0 - $29/mo</td>
                        <td><span class="check-icon">✓</span></td>
                        <td><span class="check-icon">✓</span></td>
                        <td><span class="check-icon">✓</span></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h2>The Verdict</h2>
        <p>If you are running a general retail store, Shopify is the industry standard. But if you are a **home baker or custom cake designer**, Doughmain is the clear winner. It eliminates expensive subscription bills, automates cake details, and lets you host your brand on a professional standalone domain for free.</p>

        <div class="blog-cta-box">
            <h3>Build Your Bakery Site on Doughmain</h3>
            <p>Ready to try the only website builder created for bakers? Launch your site today for free with no credit card required.</p>
            <a href="/register" class="btn btn-primary">Start Building Free →</a>
        </div>
    </div>
</div>
@endsection
