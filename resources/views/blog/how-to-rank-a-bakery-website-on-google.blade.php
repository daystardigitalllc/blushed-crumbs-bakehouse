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
        <p>You've built a beautiful website, uploaded photos of your delicious creations, and connected Stripe. But there's a problem: your website is sitting on page 5 of search results, and no one is visiting it. Knowing <strong>how to rank a bakery website on Google</strong> is what turns a quiet kitchen into a thriving business.</p>

        <p>For custom bakers and local pastry shops, general SEO doesn't matter. What you need is **Local SEO**—the process of optimizing your website so you show up when hungry clients search for bakeries in your specific city or neighborhood. Here is your local SEO checklist.</p>

        <h2>1. Optimize Your Google Business Profile</h2>
        <p>Your Google Business Profile (formerly Google My Business) is the single most important tool for local SEO. When clients search "bakery near me," Google displays a map pack of three local profiles.</p>
        <ul>
            <li>Claim your profile (it's free) and fill out every section.</li>
            <li>Link directly to your bakery's website.</li>
            <li>List your correct business hours, address (or service area if you are a home baker), and phone number.</li>
            <li>Upload high-quality, named photos of your cakes and cookies regularly.</li>
            <li>**Collect Google Reviews:** Send a review link to every happy customer. More positive reviews directly correlate with higher rankings.</li>
        </ul>

        <h2>2. Target Local Keywords</h2>
        <p>Search engines need to know where your business is located. Sprinkle local keywords throughout your site's heading tags (`<h1>`, `<h2>`) and paragraph text. For example, instead of just targeting "custom cakes," optimize for:</p>
        <blockquote style="background-color: var(--primary-pink-light); border-left-color: var(--primary-pink);">
            "Custom cakes in [City Name, State]" or "Best home bakery in [Neighborhood Name]"
        </blockquote>
        <p>Add these terms to your meta titles, descriptions, and homepage headers.</p>

        <h2>3. Focus on Mobile Optimization &amp; Loading Speed</h2>
        <p>Google prioritizes websites that load quickly and look great on mobile devices. If your website takes more than 3 seconds to load, visitors will click away, and Google will penalize your rankings.</p>
        <p>Ensure your images are compressed, your code is clean, and your menus are easily readable on touchscreens.</p>

        <h2>4. Build Local Citations</h2>
        <p>A citation is any online mention of your business's Name, Address, and Phone number (known as NAP). Google looks at directories to verify your legitimacy. Ensure your business information is identical across platforms like:</p>
        <ul>
            <li>Yelp</li>
            <li>YellowPages</li>
            <li>Facebook Business Page</li>
            <li>Local Chambers of Commerce directories</li>
        </ul>

        <h2>5. Create Targeted Landing Pages</h2>
        <p>If you offer different specialties, create separate pages for them. Having individual pages for `custom-cake-website`, `bakery-website-design`, or specialized pricing calculators gives Google more relevant content to index, helping you rank for specific high-intent search queries.</p>

        <div class="blog-cta-box">
            <h3>Get Found on Google Automatically</h3>
            <p>Doughmain websites are built from the ground up with clean HTML5 code, mobile responsiveness, fast-loading speeds, and SEO-friendly structures to help you rank locally.</p>
            <a href="/register" class="btn btn-primary">Start My Free Website →</a>
        </div>
    </div>
</div>
@endsection
