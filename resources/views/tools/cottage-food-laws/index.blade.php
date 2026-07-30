<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cottage Food Laws by State (2026) — Free Lookup Tool | Doughmain.pro</title>
    <meta name="description" content="Find out what you need to know before starting your home bakery. Check cottage food selling limits, approved foods, labeling requirements, and permits for all 50 states.">
    <link rel="canonical" href="{{ route('cottage-food-laws.index') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('cottage-food-laws.index') }}">
    <meta property="og:title" content="Cottage Food Laws by State (2026) — Free Lookup Tool">
    <meta property="og:description" content="Selling limits, approved foods, labeling requirements, and permits for home bakers in all 50 states.">
    <meta property="og:image" content="{{ asset('images/og_image.jpg') }}">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="icon" href="{{ asset('images/favicon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebPage",
        "name": "Cottage Food Laws by State",
        "url": "{{ route('cottage-food-laws.index') }}",
        "description": "A free lookup tool covering cottage food law selling limits, approved foods, labeling requirements, and permits for all 50 U.S. states.",
        "publisher": { "@type": "Organization", "name": "Doughmain.pro" }
    }
    </script>

    @include('tools.cottage-food-laws.partials.styles')
</head>
<body>

    @include('tools.cottage-food-laws.partials.nav', ['active' => 'index'])

    <header class="tool-hero container">
        <h1>Cottage Food Laws by State</h1>
        <p>Find out what you need to know before starting your home bakery. Check selling limits, approved foods, labeling requirements, and permits for your state.</p>
    </header>

    <div class="container">
        <div class="state-picker-card">
            <label for="state-search">Find your state</label>
            <div class="state-search-wrap">
                <input type="text" id="state-search" placeholder="Type a state name…" autocomplete="off" role="combobox" aria-expanded="false" aria-controls="state-search-results">
                <div class="state-search-results" id="state-search-results"></div>
            </div>
            <p class="state-picker-hint">Or choose from the full list below.</p>
        </div>

        <div class="recent-states" id="recent-states" style="display:none;">
            <h2>Recently Viewed</h2>
            <div class="recent-chips" id="recent-chips"></div>
        </div>

        <div class="compare-bar" id="compare-bar" style="display:none;">
            <span id="compare-count-label">0 states selected</span>
            <div>
                <button type="button" class="btn btn-primary btn-small" id="compare-btn" disabled>Compare Selected</button>
                <button type="button" class="btn btn-light btn-small" id="compare-clear-btn">Clear</button>
            </div>
        </div>

        <div id="compare-panel" class="compare-panel" style="display:none;">
            <h2>State Comparison</h2>
            <div class="compare-table-wrap">
                <table class="compare-table" id="compare-table"></table>
            </div>
        </div>

        <div class="state-grid" id="state-grid">
            @foreach($states as $s)
                <div class="state-card" data-name="{{ strtolower($s['name']) }}" data-slug="{{ $s['slug'] }}">
                    <label class="state-compare-check">
                        <input type="checkbox" class="compare-checkbox" value="{{ $s['slug'] }}" data-name="{{ $s['name'] }}">
                        <span class="visually-hidden">Select {{ $s['name'] }} to compare</span>
                    </label>
                    <a href="{{ route('cottage-food-laws.show', $s['slug']) }}" class="state-card-link">
                        <span class="state-abbr">{{ $s['abbr'] }}</span>
                        <span class="state-name">{{ $s['name'] }}</span>
                        <span class="state-limit">{{ $s['sales_limit'] }}</span>
                    </a>
                </div>
            @endforeach
        </div>
    </div>

    <div class="disclaimer-box container">
        <strong>Legal disclaimer:</strong> This information is provided for educational purposes only and should not be considered legal advice. Cottage food laws change frequently. Always verify requirements with your state's official government resources.
    </div>

    <section class="cta-section container">
        <h2>Ready to Start Selling More?</h2>
        <p>Create your free bakery website with Doughmain.pro. Accept orders online, showcase your menu, and grow your bakery.</p>
        <div class="cta-buttons">
            <a href="/register" class="btn btn-primary">Create Free Bakery Website</a>
            <a href="/landing" class="btn btn-outline">Learn More</a>
        </div>
    </section>

    <footer class="site-footer">
        &copy; {{ date('Y') }} Doughmain Pro Platform, a <a href="https://daystardigital.co">Daystar Digital LLC</a> product. &middot; <a href="/legal">Legal Center</a>
    </footer>

    <script>
    (function () {
        'use strict';

        var STATES = @json($states);
        var STORAGE_KEY = 'doughmain_cottage_food_recent';
        var BASE_URL = '{{ url('/cottage-food-laws') }}';

        // ---------- Search ----------
        var searchInput = document.getElementById('state-search');
        var resultsBox = document.getElementById('state-search-results');

        function renderResults(query) {
            var q = query.trim().toLowerCase();
            if (!q) { resultsBox.style.display = 'none'; resultsBox.innerHTML = ''; return; }

            var matches = STATES.filter(function (s) { return s.name.toLowerCase().indexOf(q) !== -1; }).slice(0, 8);
            if (matches.length === 0) {
                resultsBox.innerHTML = '<div class="state-search-empty">No states match "' + q.replace(/[<>&]/g, '') + '"</div>';
                resultsBox.style.display = 'block';
                return;
            }

            resultsBox.innerHTML = matches.map(function (s) {
                return '<a href="' + BASE_URL + '/' + s.slug + '" class="state-search-item">' + s.name + '<span>' + s.sales_limit + '</span></a>';
            }).join('');
            resultsBox.style.display = 'block';
        }

        searchInput.addEventListener('input', function () { renderResults(searchInput.value); });
        searchInput.addEventListener('focus', function () { if (searchInput.value.trim()) renderResults(searchInput.value); });
        document.addEventListener('click', function (e) {
            if (!e.target.closest('.state-search-wrap')) { resultsBox.style.display = 'none'; }
        });

        // Also filter the full grid below as a fallback / mobile-friendly view
        searchInput.addEventListener('input', function () {
            var q = searchInput.value.trim().toLowerCase();
            document.querySelectorAll('.state-card').forEach(function (card) {
                card.style.display = !q || card.getAttribute('data-name').indexOf(q) !== -1 ? '' : 'none';
            });
        });

        // ---------- Recently viewed (localStorage, written by the state show page) ----------
        function renderRecent() {
            var raw = [];
            try { raw = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]'); } catch (e) { raw = []; }
            if (!raw.length) return;

            var chips = raw.map(function (slug) {
                var s = STATES.find(function (x) { return x.slug === slug; });
                if (!s) return '';
                return '<a href="' + BASE_URL + '/' + s.slug + '" class="recent-chip">' + s.name + '</a>';
            }).join('');

            if (chips) {
                document.getElementById('recent-chips').innerHTML = chips;
                document.getElementById('recent-states').style.display = 'block';
            }
        }
        renderRecent();

        // ---------- Compare states ----------
        var selected = [];
        var compareBar = document.getElementById('compare-bar');
        var compareBtn = document.getElementById('compare-btn');
        var countLabel = document.getElementById('compare-count-label');

        document.querySelectorAll('.compare-checkbox').forEach(function (cb) {
            cb.addEventListener('change', function () {
                if (cb.checked) {
                    if (selected.length >= 3) { cb.checked = false; return; }
                    selected.push(cb.value);
                } else {
                    selected = selected.filter(function (s) { return s !== cb.value; });
                }
                countLabel.textContent = selected.length + ' state' + (selected.length === 1 ? '' : 's') + ' selected (max 3)';
                compareBtn.disabled = selected.length < 2;
                compareBar.style.display = selected.length > 0 ? 'flex' : 'none';
            });
        });

        document.getElementById('compare-clear-btn').addEventListener('click', function () {
            selected = [];
            document.querySelectorAll('.compare-checkbox').forEach(function (cb) { cb.checked = false; });
            compareBar.style.display = 'none';
            document.getElementById('compare-panel').style.display = 'none';
        });

        document.getElementById('compare-btn').addEventListener('click', function () {
            var rows = selected.map(function (slug) { return STATES.find(function (s) { return s.slug === slug; }); }).filter(Boolean);
            var table = document.getElementById('compare-table');
            table.innerHTML =
                '<tr><th></th>' + rows.map(function (s) { return '<th>' + s.name + '</th>'; }).join('') + '</tr>' +
                '<tr><td>Annual sales limit</td>' + rows.map(function (s) { return '<td>' + s.sales_limit + '</td>'; }).join('') + '</tr>' +
                '<tr><td>Full details</td>' + rows.map(function (s) { return '<td><a href="' + BASE_URL + '/' + s.slug + '" class="btn btn-outline btn-small">View ' + s.abbr + '</a></td>'; }).join('') + '</tr>';
            document.getElementById('compare-panel').style.display = 'block';
            document.getElementById('compare-panel').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    })();
    </script>

    @include('tools.cottage-food-laws.partials.nav-script')
</body>
</html>
