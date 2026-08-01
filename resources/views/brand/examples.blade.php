<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bakery Website Examples | Built With Doughmain</title>
    <meta name="description" content="See real bakery websites built with Doughmain — custom cake menus, galleries, and order forms live on real bakery subdomains. Build your own free.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="Bakery Website Examples | Built With Doughmain">
    <meta property="og:description" content="See real bakery websites built with Doughmain — custom cake menus, galleries, and order forms live on real bakery subdomains.">
    <meta property="og:image" content="{{ asset('images/og_image.jpg') }}">
    <link rel="icon" href="{{ asset('images/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-pink: #e67399;
            --primary-pink-dark: #d25a80;
            --deep-purple: #6d28d9;
            --soft-pink: #fff7fa;
            --dark-section: #1a0a2e;
            --text-dark: #333333;
            --text-gray: #666666;
            --white: #ffffff;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; color: var(--text-dark); background-color: var(--soft-pink); line-height: 1.6; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Outfit', sans-serif; color: var(--dark-section); line-height: 1.2; }
        a { text-decoration: none; color: inherit; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 24px; }
        .btn { display: inline-block; padding: 14px 28px; border-radius: 8px; font-weight: 600; transition: all 0.3s ease; cursor: pointer; border: none; font-size: 1rem; text-align: center; }
        .btn-primary { background-color: var(--primary-pink); color: var(--white); box-shadow: 0 4px 14px rgba(230, 115, 153, 0.4); }
        .btn-primary:hover { background-color: var(--primary-pink-dark); transform: translateY(-2px); box-shadow: 0 6px 20px rgba(230, 115, 153, 0.5); }
        .btn-light { background-color: var(--white); color: var(--dark-section); border: 1.5px solid #eef2f6; }
        .btn-light:hover { background-color: #f7f7f7; transform: translateY(-2px); }

        .navbar { position: fixed; top: 0; left: 0; width: 100%; background-color: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); z-index: 1000; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); }
        .navbar .container { display: flex; justify-content: space-between; align-items: center; padding-top: 14px; padding-bottom: 14px; }
        .nav-logo img { height: 50px; width: auto; object-fit: contain; }
        .nav-links { display: flex; align-items: center; gap: 24px; }
        .nav-login { font-weight: 500; transition: color 0.3s; }
        .nav-login:hover { color: var(--primary-pink); }

        .page-hero { padding: 160px 0 70px; text-align: center; background: linear-gradient(180deg, #ffffff 0%, #fff7fa 100%); }
        .page-hero .eyebrow { display: inline-block; background: #f3e8ff; color: #6d28d9; font-weight: 700; font-size: 0.85rem; padding: 6px 16px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 20px; }
        .page-hero h1 { font-size: 3rem; max-width: 800px; margin: 0 auto 20px; }
        .page-hero p { font-size: 1.15rem; color: var(--text-gray); max-width: 640px; margin: 0 auto 32px; }

        .examples-grid-section { padding: 20px 0 100px; }
        .examples-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 32px; }

        .bakery-card { background: var(--white); border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05); transition: transform 0.3s ease, box-shadow 0.3s ease; display: flex; flex-direction: column; }
        .bakery-card:hover { transform: translateY(-6px); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1); }
        .bakery-card-image { width: 100%; height: 200px; background-size: cover; background-position: center; background-color: #f1e6ec; position: relative; }
        .bakery-card-badge { position: absolute; top: 14px; left: 14px; background: rgba(26, 10, 46, 0.75); backdrop-filter: blur(4px); color: #fff; font-size: 0.72rem; font-weight: 700; padding: 5px 12px; border-radius: 20px; }
        .bakery-card-body { padding: 24px; display: flex; flex-direction: column; gap: 6px; flex: 1; }
        .bakery-card-body h3 { font-size: 1.35rem; }
        .bakery-card-meta { color: var(--text-gray); font-size: 0.92rem; margin-bottom: 6px; }
        .bakery-card-tagline { color: var(--text-gray); font-size: 0.9rem; margin-bottom: 18px; flex: 1; }
        .bakery-card-actions { display: flex; gap: 10px; margin-top: auto; }
        .bakery-card-actions .btn { flex: 1; padding: 11px 16px; font-size: 0.92rem; }

        .footer-cta { background-color: var(--dark-section); color: var(--white); padding: 90px 0 40px; text-align: center; }
        .footer-cta h2 { color: var(--white); font-size: 2.6rem; margin-bottom: 20px; }
        .footer-cta p { font-size: 1.15rem; color: #aaa; max-width: 600px; margin: 0 auto 36px; }
        .copyright { margin-top: 70px; padding-top: 24px; border-top: 1px solid rgba(255, 255, 255, 0.1); color: #666; font-size: 0.85rem; line-height: 1.8; }

        @media (max-width: 768px) {
            .page-hero { padding: 120px 0 50px; }
            .page-hero h1 { font-size: 2.1rem; }
            .footer-cta h2 { font-size: 2rem; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="container">
            <a href="/" class="nav-logo">
                <img src="{{ asset('images/doughmain_logo.png') }}" alt="Doughmain.pro Logo">
            </a>
            <div class="nav-links">
                <a href="/#pricing" class="nav-login">Pricing</a>
                <a href="/login" class="nav-login">Login</a>
                <a href="/register" class="btn btn-primary">Build Your Free Site →</a>
            </div>
        </div>
    </nav>

    <section class="page-hero">
        <div class="container">
            <span class="eyebrow">🍰 Real Sites, Real Bakers</span>
            <h1>Bakery Websites Built With Doughmain</h1>
            <p>Every site below was built and launched with Doughmain's free AI website builder — custom menus, galleries, and order forms included. See what your bakery could look like.</p>
            <a href="/register" class="btn btn-primary" style="font-size: 1.05rem; padding: 16px 32px;">Build Your Free Bakery Site →</a>
        </div>
    </section>

    <section class="examples-grid-section">
        <div class="container">
            <div class="examples-grid">
                @foreach ($bakeries as $bakery)
                    <div class="bakery-card">
                        <div class="bakery-card-image" style="background-image:url('{{ $bakery['image_url'] }}');">
                            <span class="bakery-card-badge">{{ $bakery['theme_name'] }}</span>
                        </div>
                        <div class="bakery-card-body">
                            <h3>🍰 {{ $bakery['name'] }}</h3>
                            <div class="bakery-card-meta">{{ $bakery['location'] }}</div>
                            <p class="bakery-card-tagline">{{ $bakery['specialty'] }}</p>
                            <div class="bakery-card-actions">
                                <a href="{{ $bakery['url'] }}" class="btn btn-light" target="_blank" rel="noopener">View Website</a>
                                <a href="/register" class="btn btn-primary">Create Yours</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <footer class="footer-cta">
        <div class="container">
            <h2>Your bakery could be next.</h2>
            <p>Build a site just as beautiful as these — free, in minutes, with zero platform fees.</p>
            <a href="/register" class="btn btn-light" style="font-size: 1.1rem; padding: 18px 36px;">Build Your Free Bakery Site →</a>
            <div class="copyright">
                &copy; 2026 Doughmain Pro Platform, a <a href="https://daystardigital.co" style="color:inherit; text-decoration:underline;">Daystar Digital LLC</a> product. &middot; <a href="/legal" style="color:inherit; text-decoration:underline;">Legal Center &amp; Policies</a>
            </div>
        </div>
    </footer>

</body>
</html>
