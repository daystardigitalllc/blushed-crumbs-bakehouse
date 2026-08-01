<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Doughmain.pro — Build, Host, & Manage Your Bakery Website')</title>
    <meta name="description" content="@yield('meta_description')">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', 'Doughmain.pro — Build, Host, & Manage Your Bakery Website')">
    <meta property="og:description" content="@yield('meta_description')">
    <meta property="og:image" content="{{ asset('images/og_image.jpg') }}">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'Doughmain.pro — Build, Host, & Manage Your Bakery Website')">
    <meta name="twitter:description" content="@yield('meta_description')">
    <meta name="twitter:image" content="{{ asset('images/og_image.jpg') }}">
    
    <!-- Google Schema Markup -->
    @yield('schema')
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon.png') }}">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Custom Brand CSS -->
    <link rel="stylesheet" href="{{ asset('css/brand.css') }}">
    @yield('styles')
</head>
<body>

    <!-- Header Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="/" class="nav-logo">
                <img src="{{ asset('images/doughmain_logo.png') }}" alt="Doughmain Logo">
            </a>
            <button type="button" class="nav-hamburger" id="nav-hamburger" aria-label="Menu" aria-expanded="false">☰</button>
            <div class="nav-links" id="nav-links">
                @if(\App\Http\Controllers\ToolsController::isAllowedHost(request()->getHost()))
                    <div class="nav-dropdown" id="free-tools-dropdown">
                        <button type="button" class="nav-dropdown-toggle" aria-haspopup="true" aria-expanded="false">
                            Free Tools <span class="caret">▾</span>
                        </button>
                        <div class="nav-dropdown-menu">
                            <a href="{{ route('tools.pricing-calculator') }}">Bakery Pricing Calculator</a>
                            <a href="{{ route('cottage-food-laws.index') }}">Cottage Food Law Lookup</a>
                        </div>
                    </div>
                @endif
                <a href="{{ route('examples') }}" class="nav-link">Examples</a>
                <a href="/blog" class="nav-link">Blog</a>
                <a href="/login" class="nav-link">Login</a>
                <a href="/register" class="btn btn-primary">Build Your Free Site →</a>
            </div>
        </div>
    </nav>

    <!-- Main Content Yield -->
    <main>
        @yield('content')
    </main>

    <!-- Footer Section -->
    <footer class="footer-cta">
        <div class="container">
            <div style="margin-bottom: 50px;">
                <h2>Build your bakery website today.</h2>
                <p>Give your brand the perfect environment to rise online. Build, host, and manage your website completely free.</p>
                <a href="/register" class="btn btn-light" style="font-size: 1.1rem; padding: 16px 32px;">Build Your Free Bakery Site →</a>
            </div>
            <div class="copyright">
                &copy; {{ date('Y') }} Doughmain Pro Platform, a <a href="https://daystardigital.co" target="_blank" rel="noopener">Daystar Digital LLC</a> product. &middot; <a href="/legal">Legal Center &amp; Policies</a> &middot; <a href="/legal/terms">Terms of Service</a> &middot; <a href="/legal/privacy">Privacy Policy</a>
            </div>
        </div>
    </footer>

    <!-- Mobile Hamburger and Dropdown script -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const hamburger = document.getElementById("nav-hamburger");
            const navLinks = document.getElementById("nav-links");
            
            if (hamburger && navLinks) {
                hamburger.addEventListener("click", (e) => {
                    e.stopPropagation();
                    const isOpen = navLinks.classList.toggle("open");
                    hamburger.setAttribute("aria-expanded", isOpen ? "true" : "false");
                });
                
                document.addEventListener("click", (e) => {
                    if (!navLinks.contains(e.target) && e.target !== hamburger) {
                        navLinks.classList.remove("open");
                        hamburger.setAttribute("aria-expanded", "false");
                    }
                });
            }

            const dropdown = document.getElementById("free-tools-dropdown");
            if (dropdown) {
                const toggle = dropdown.querySelector(".nav-dropdown-toggle");
                toggle.addEventListener("click", (e) => {
                    e.stopPropagation();
                    const isOpen = dropdown.classList.toggle("open");
                    toggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
                });

                document.addEventListener("click", (e) => {
                    if (!dropdown.contains(e.target)) {
                        dropdown.classList.remove("open");
                        toggle.setAttribute("aria-expanded", "false");
                    }
                });
            }
        });
    </script>
    @yield('scripts')
</body>
</html>
