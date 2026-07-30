<!DOCTYPE html>
<html lang="en">
@php
    $pageTitle = "Cottage Food Laws in {$state['name']}: What Home Bakers Need to Know";
    $pageDescription = "Learn {$state['name']} cottage food laws including allowed foods, sales limits, labeling requirements, and permits for starting a home bakery.";
    $canonical = route('cottage-food-laws.show', $state['slug']);
@endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }} | Doughmain.pro</title>
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ $canonical }}">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:image" content="{{ asset('images/og_image.jpg') }}">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="icon" href="{{ asset('images/favicon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@graph": [
            {
                "@type": "BreadcrumbList",
                "itemListElement": [
                    { "@type": "ListItem", "position": 1, "name": "Home", "item": "{{ url('/') }}" },
                    { "@type": "ListItem", "position": 2, "name": "Cottage Food Laws", "item": "{{ route('cottage-food-laws.index') }}" },
                    { "@type": "ListItem", "position": 3, "name": "{{ $state['name'] }}", "item": "{{ $canonical }}" }
                ]
            },
            {
                "@type": "FAQPage",
                "mainEntity": [
                    {
                        "@type": "Question",
                        "name": "Can I sell baked goods from home in {{ $state['name'] }}?",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "Yes. {{ $state['name'] }} allows home bakers to sell approved homemade goods directly to consumers under its cottage food rules. {{ $state['summary'] }}"
                        }
                    },
                    {
                        "@type": "Question",
                        "name": "What foods can I sell under cottage food laws in {{ $state['name'] }}?",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "Typically allowed items include {{ collect($state['allowed_foods'])->take(5)->implode(', ') }}, and similar non-perishable homemade goods. Foods that require refrigeration, such as cream pies and cheesecakes, are generally not allowed. Always confirm the current list with {{ $state['official_source_name'] }}."
                        }
                    },
                    {
                        "@type": "Question",
                        "name": "Do I need a permit to sell homemade cakes in {{ $state['name'] }}?",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "{{ $state['permits']['permit_required'] }}"
                        }
                    },
                    {
                        "@type": "Question",
                        "name": "How much can a home bakery make in {{ $state['name'] }}?",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "{{ $state['name'] }}'s cottage food annual sales limit is: {{ $state['sales_limit'] }}."
                        }
                    }
                ]
            }
        ]
    }
    </script>

    @include('tools.cottage-food-laws.partials.styles')
</head>
<body data-state-slug="{{ $state['slug'] }}">

    @include('tools.cottage-food-laws.partials.nav', ['active' => 'show'])

    <div class="container breadcrumbs">
        <a href="/">Home</a><span class="sep">/</span>
        <a href="{{ route('cottage-food-laws.index') }}">Cottage Food Laws</a><span class="sep">/</span>
        <span>{{ $state['name'] }}</span>
    </div>

    <header class="tool-hero container">
        <h1>Cottage Food Laws in {{ $state['name'] }}</h1>
        <p>{{ $state['summary'] }}</p>
        <div class="page-actions">
            <button type="button" class="btn btn-light btn-small" id="btn-print">🖨️ Print</button>
            <button type="button" class="btn btn-light btn-small" id="btn-copy-link">🔗 Copy Link</button>
            <button type="button" class="btn btn-light btn-small" id="btn-share">📤 Share</button>
        </div>
    </header>

    <div class="container" style="max-width: 900px;">

        <div class="card">
            <h2>Overview</h2>
            <p>{{ $state['summary'] }}</p>
            <div class="overview-meta">
                <span><strong>Last verified:</strong> {{ \Illuminate\Support\Carbon::parse($state['last_updated'])->format('F j, Y') }}</span>
                <span><strong>Official source:</strong> <a href="{{ $state['official_source'] }}" target="_blank" rel="noopener noreferrer" style="text-decoration:underline;">{{ $state['official_source_name'] }} ↗</a></span>
            </div>
        </div>

        <div class="card">
            <h2>What Foods Can I Sell?</h2>
            <div class="food-columns">
                <div>
                    <h3 style="font-size:1rem;margin-bottom:6px;">Allowed</h3>
                    <ul class="food-list allowed">
                        @foreach($state['allowed_foods'] as $food)
                            <li>{{ $food }}</li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h3 style="font-size:1rem;margin-bottom:6px;">Not Allowed</h3>
                    <ul class="food-list prohibited">
                        @foreach($state['prohibited_foods'] as $food)
                            <li>{{ $food }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @if($state['prohibited_foods_note'])
                <div class="food-note">{{ $state['prohibited_foods_note'] }}</div>
            @endif
        </div>

        <div class="card">
            <h2>Where Can I Sell?</h2>
            <div class="locations-grid">
                @php
                    $locationLabels = [
                        'direct_to_consumer' => 'Direct to Consumers',
                        'farmers_markets' => 'Farmers Markets',
                        'online_orders' => 'Online Orders',
                        'pickup' => 'Pickup',
                        'delivery' => 'Delivery',
                        'events' => 'Events',
                    ];
                @endphp
                @foreach($locationLabels as $key => $label)
                    <div class="location-pill {{ $state['selling_locations'][$key] ? 'yes' : 'no' }}">
                        {{ $state['selling_locations'][$key] ? '✓' : '✕' }} {{ $label }}
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <h2>Revenue Limits</h2>
            <div class="sales-limit-highlight">
                <div class="label">Annual Sales Limit</div>
                <div class="amount">{{ $state['sales_limit'] }}</div>
            </div>
        </div>

        <div class="card">
            <h2>Permits and Licensing</h2>
            <div class="permit-grid">
                <div class="permit-item">
                    <h4>Required Permits</h4>
                    <p>{{ $state['permits']['permit_required'] }}</p>
                </div>
                <div class="permit-item">
                    <h4>Food Handler Requirements</h4>
                    <p>{{ $state['permits']['food_handler'] }}</p>
                </div>
                <div class="permit-item">
                    <h4>Business License</h4>
                    <p>{{ $state['permits']['business_license'] }}</p>
                </div>
                <div class="permit-item">
                    <h4>Kitchen Inspection</h4>
                    <p>{{ $state['permits']['kitchen_inspection'] }}</p>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>Labeling Requirements</h2>
            <ol class="labeling-list">
                @foreach($state['labeling_requirements'] as $req)
                    <li>{{ $req }}</li>
                @endforeach
            </ol>
        </div>

        <div class="card">
            <h2>Starting Your Bakery Checklist</h2>
            <ul class="checklist" id="bakery-checklist">
                <li><label><input type="checkbox" data-item="review"><span>Review {{ $state['name'] }}'s cottage food requirements</span></label></li>
                <li><label><input type="checkbox" data-item="products"><span>Choose products to sell</span></label></li>
                <li><label><input type="checkbox" data-item="labels"><span>Create compliant labels</span></label></li>
                <li><label><input type="checkbox" data-item="pricing"><span>Set pricing</span></label></li>
                <li><label><input type="checkbox" data-item="website"><span>Create your bakery website</span></label></li>
                <li><label><input type="checkbox" data-item="orders"><span>Start accepting orders</span></label></li>
            </ul>
        </div>

    </div>

    <div class="disclaimer-box container" style="max-width: 900px;">
        <strong>Legal disclaimer:</strong> This information is provided for educational purposes only and should not be considered legal advice. Cottage food laws change frequently. Always verify requirements with your state's official government resources.
    </div>

    <section class="cta-section container">
        <h2>Turn Your Home Bakery Into a Business</h2>
        <p>Doughmain helps home bakers create a professional bakery website, accept orders online, showcase their menu, and grow their customer base.</p>
        <div class="cta-buttons">
            <a href="/register" class="btn btn-primary">Create Free Bakery Website</a>
            <a href="/register" class="btn btn-outline">Build My Bakery Site</a>
        </div>
    </section>

    <section class="faq-section container">
        <h2>Frequently Asked Questions</h2>

        <details class="faq-item">
            <summary>Can I sell baked goods from home in {{ $state['name'] }}?</summary>
            <p>Yes. {{ $state['name'] }} allows home bakers to sell approved homemade goods directly to consumers under its cottage food rules. {{ $state['summary'] }}</p>
        </details>
        <details class="faq-item">
            <summary>What foods can I sell under cottage food laws in {{ $state['name'] }}?</summary>
            <p>Typically allowed items include {{ collect($state['allowed_foods'])->take(5)->implode(', ') }}, and similar non-perishable homemade goods. Foods that require refrigeration, such as cream pies and cheesecakes, are generally not allowed. Always confirm the current list with {{ $state['official_source_name'] }}.</p>
        </details>
        <details class="faq-item">
            <summary>Do I need a permit to sell homemade cakes in {{ $state['name'] }}?</summary>
            <p>{{ $state['permits']['permit_required'] }}</p>
        </details>
        <details class="faq-item">
            <summary>How much can a home bakery make in {{ $state['name'] }}?</summary>
            <p>{{ $state['name'] }}'s cottage food annual sales limit is: {{ $state['sales_limit'] }}.</p>
        </details>
    </section>

    <section class="other-states container">
        <h2>Browse Other States</h2>
        <div class="other-states-list">
            @foreach(collect($allStates)->where('slug', '!=', $state['slug'])->shuffle()->take(12) as $s)
                <a href="{{ route('cottage-food-laws.show', $s['slug']) }}">{{ $s['name'] }}</a>
            @endforeach
            <a href="{{ route('cottage-food-laws.index') }}" style="background:var(--dark-section);color:#fff;">View All States →</a>
        </div>
    </section>

    <footer class="site-footer">
        &copy; {{ date('Y') }} Doughmain Pro Platform, a <a href="https://daystardigital.co">Daystar Digital LLC</a> product. &middot; <a href="/legal">Legal Center</a>
    </footer>

    <div class="copy-toast" id="copy-toast">Copied!</div>

    <script>
    (function () {
        'use strict';

        var STATE_SLUG = document.body.getAttribute('data-state-slug');
        var STATE_NAME = @json($state['name']);
        var RECENT_KEY = 'doughmain_cottage_food_recent';
        var CHECKLIST_KEY = 'doughmain_cottage_food_checklist_' + STATE_SLUG;

        // ---------- Recently viewed ----------
        try {
            var recent = JSON.parse(localStorage.getItem(RECENT_KEY) || '[]');
            recent = recent.filter(function (s) { return s !== STATE_SLUG; });
            recent.unshift(STATE_SLUG);
            recent = recent.slice(0, 6);
            localStorage.setItem(RECENT_KEY, JSON.stringify(recent));
        } catch (e) { /* localStorage unavailable */ }

        // ---------- Checklist persistence ----------
        var checklist = document.getElementById('bakery-checklist');
        var checkedState = {};
        try { checkedState = JSON.parse(localStorage.getItem(CHECKLIST_KEY) || '{}'); } catch (e) { checkedState = {}; }

        checklist.querySelectorAll('input[type="checkbox"]').forEach(function (cb) {
            var key = cb.getAttribute('data-item');
            if (checkedState[key]) {
                cb.checked = true;
                cb.closest('label').classList.add('checked');
            }
            cb.addEventListener('change', function () {
                checkedState[key] = cb.checked;
                cb.closest('label').classList.toggle('checked', cb.checked);
                try { localStorage.setItem(CHECKLIST_KEY, JSON.stringify(checkedState)); } catch (e) { /* ignore */ }
            });
        });

        // ---------- Actions ----------
        function showToast(msg) {
            var toast = document.getElementById('copy-toast');
            toast.textContent = msg;
            toast.classList.add('show');
            setTimeout(function () { toast.classList.remove('show'); }, 2200);
        }

        document.getElementById('btn-print').addEventListener('click', function () { window.print(); });

        document.getElementById('btn-copy-link').addEventListener('click', function () {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(window.location.href).then(function () { showToast('Link copied to clipboard'); });
            } else {
                window.prompt('Copy this link:', window.location.href);
            }
        });

        document.getElementById('btn-share').addEventListener('click', function () {
            var shareData = {
                title: 'Cottage Food Laws in ' + STATE_NAME,
                text: 'Cottage food laws for home bakers in ' + STATE_NAME + ' — selling limits, permits, and labeling requirements.',
                url: window.location.href
            };
            if (navigator.share) {
                navigator.share(shareData).catch(function () { /* user cancelled */ });
            } else if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(window.location.href).then(function () { showToast('Link copied — paste it anywhere to share'); });
            }
        });
    })();
    </script>

    @include('tools.cottage-food-laws.partials.nav-script')
</body>
</html>
