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
  "datePublished": "2026-07-19",
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
        <p>If you are baking delicious treats out of your home kitchen, you've probably asked yourself: <strong>Do home bakers need a website?</strong> Or is a free Instagram profile and Facebook page enough?</p>

        <p>When starting out, relying on social media DMs is a great way to test your recipes with friends and family. However, if you want to run a profitable, sustainable business, renting space on social networks will quickly limit your growth. Here is why home-based cottage bakers need a dedicated website.</p>

        <h2>The Limits of Social Media Ordering</h2>
        <p>Taking order requests through Instagram comments or Facebook direct messages causes several operational hurdles:</p>
        <ul>
            <li><strong>Cluttered Inboxes:</strong> Chat threads get buried, leading to forgotten orders and missed revenue.</li>
            <li><strong>Algorithm Changes:</strong> Instagram can change its algorithm overnight, meaning your posts reach less than 10% of your followers.</li>
            <li><strong>Lack of Structured Data:</strong> You have to manually type the same questions (date? size? flavors?) for every single inquiry.</li>
            <li><strong>No Payment Security:</strong> You have to send Venmo links manually, which lacks professional trust and invoicing records.</li>
        </ul>

        <h2>4 Reasons Home Bakers Need a Dedicated Website</h2>

        <div class="highlight-box">
            <h3>1. Automate Your Inquiries and Save Hours</h3>
            <p>Instead of chatting back-and-forth for days to finalize a simple cookie order, a website structured with custom forms gathers date, serving size, flavor choices, and inspiration photos in one go. You just review and click "approve."</p>
        </div>

        <div class="highlight-box" style="background-color: #f5f2ff; border-left-color: #7c3aed;">
            <h3>2. Secure Your Income with Deposit Invoicing</h3>
            <p>Requiring a 50% deposit before confirming an order eliminates the risk of "no-shows" (clients booking a cake and disappearing on pickup day). Direct Stripe integrations collect these retainers securely and automatically.</p>
        </div>

        <div class="highlight-box">
            <h3>3. Command Professional, Higher Prices</h3>
            <p>Clients are willing to pay $100+ for custom cakes from an established brand with a custom domain (e.g. <code>yourbakery.com</code>). Operating exclusively out of Instagram comments makes your business look casual, leading to clients negotiating or questioning your rates.</p>
        </div>

        <div class="highlight-box" style="background-color: var(--primary-pink-light); border-left-color: var(--primary-pink);">
            <h3>4. Get Discovered on Google Search</h3>
            <p>When locals search "custom bakers near me" or "home bakery in [City Name]", social media posts rarely show up in Google's top results. A search-engine-optimized website sits on the front page, driving consistent free traffic.</p>
        </div>

        <h2>Social Media vs. Standalone Website</h2>
        <p>Think of social media as your billboard, and your website as your checkout register. You use Instagram to display pretty photos, but direct followers to your website to submit order details and secure dates. Owning your site gives you complete control over your client records and business guidelines.</p>

        <div class="blog-cta-box">
            <h3>Build Your Home Bakery Website Free</h3>
            <p>Doughmain is built for cottage food home bakers. Restrict weekly orders, configure pickup availability calendars, and collect Stripe deposits free.</p>
            <a href="/register" class="btn btn-primary">Launch My Website Free →</a>
        </div>
    </div>
</div>
@endsection
