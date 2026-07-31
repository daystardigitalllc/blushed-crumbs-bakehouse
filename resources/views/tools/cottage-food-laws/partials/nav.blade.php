<nav class="navbar">
    <div class="container">
        <a href="/" class="nav-logo">
            <img src="{{ asset('images/doughmain_logo.png') }}" alt="Doughmain.pro Logo">
        </a>
        <button type="button" class="nav-hamburger" id="nav-hamburger" aria-label="Menu" aria-expanded="false">☰</button>
        <div class="nav-links" id="nav-links">
            <div class="nav-dropdown" id="free-tools-dropdown">
                <button type="button" class="nav-login nav-dropdown-toggle{{ isset($active) ? ' active' : '' }}" aria-haspopup="true" aria-expanded="false">
                    Free Tools <span class="caret">▾</span>
                </button>
                <div class="nav-dropdown-menu">
                    <a href="{{ route('tools.pricing-calculator') }}">Bakery Pricing Calculator</a>
                    <a href="{{ route('cottage-food-laws.index') }}" class="{{ isset($active) ? 'active' : '' }}">Cottage Food Law Lookup</a>
                </div>
            </div>
            <a href="/login" class="nav-login">Login</a>
            <a href="/register" class="btn btn-primary btn-small">Build Your Free Site →</a>
        </div>
    </div>
</nav>
