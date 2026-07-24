<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms & Conditions - {{ isset($tenant) ? $tenant->name : 'BakeryPro' }}</title>
    @if(isset($tenant) && $tenant->logo_path)
        <link rel="icon" href="{{ asset($tenant->logo_path) }}">
    @else
        <link rel="icon" href="{{ asset('images/favicon.png') }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #e67399;
            --dark: #111827;
            --gray-700: #374151;
            --gray-500: #6b7280;
            --bg-light: #fdfbfd;
            --card-bg: #ffffff;
            --border: #e5e7eb;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--dark);
            line-height: 1.7;
            padding: 0;
            margin: 0;
        }
        .header {
            background: var(--card-bg);
            border-bottom: 1px solid var(--border);
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header a {
            text-decoration: none;
            color: var(--dark);
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 1.35rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .back-link {
            font-size: 0.95rem;
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
        }
        .container {
            max-width: 900px;
            margin: 3rem auto;
            background: var(--card-bg);
            padding: 3rem;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
            border: 1px solid var(--border);
        }
        h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }
        .updated-date {
            color: var(--gray-500);
            font-size: 0.9rem;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border);
        }
        h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.35rem;
            margin-top: 2rem;
            margin-bottom: 0.75rem;
            color: var(--dark);
        }
        p, ul {
            color: var(--gray-700);
            margin-bottom: 1.25rem;
            font-size: 1rem;
        }
        ul {
            padding-left: 1.5rem;
        }
        li {
            margin-bottom: 0.5rem;
        }
        .footer-text {
            margin-top: 3rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border);
            text-align: center;
            font-size: 0.875rem;
            color: var(--gray-500);
        }
        @media (max-width: 768px) {
            .container { padding: 1.5rem; margin: 1rem; }
            h1 { font-size: 1.75rem; }
        }
    </style>
</head>
<body>

    <header class="header">
        <a href="{{ isset($tenant) ? url('/') : url('/') }}">
            @if(isset($tenant) && $tenant->logo_path)
                <img src="{{ asset($tenant->logo_path) }}" alt="{{ $tenant->name }}" style="height: 36px; border-radius: 4px;">
            @else
                🧁
            @endif
            <span>{{ isset($tenant) ? $tenant->name : 'BakeryPro' }}</span>
        </a>
        <a href="{{ url('/') }}" class="back-link">← Return to Website</a>
    </header>

    <div class="container">
        <h1>Terms & Conditions</h1>
        <div class="updated-date">Last Updated: July 24, 2026</div>

        <p>Welcome to <strong>{{ isset($tenant) ? $tenant->name : 'BakeryPro' }}</strong>. By accessing or submitting custom order requests on our website, you agree to comply with and be bound by the following Terms and Conditions.</p>

        <h2>1. Custom Orders & Booking Lead Times</h2>
        <p>All custom cake and bakery inquiries submitted through our online builder represent booking requests. Orders are confirmed only after review, date verification, and invoice deposit payment.</p>
        <ul>
            <li>Lead time requirements apply based on calendar availability.</li>
            <li>Inspiration images submitted serve as design guidance; exact color matching may vary slightly due to artistic craftsmanship and food-grade colorants.</li>
        </ul>

        <h2>2. Payments & Deposit Policy</h2>
        <p>Invoices issued for custom orders detail the required deposit and final balance due dates:</p>
        <ul>
            <li>Deposits are non-refundable once ingredient preparation and custom design work begin.</li>
            <li>Final balance payments must be settled prior to pickup or delivery as specified in your invoice.</li>
        </ul>

        <h2>3. Allergen Notice & Liability</h2>
        <p>While we accommodate specific flavor and dietary requests, our baking environment processes common allergens including wheat, dairy, eggs, nuts, and soy. Customers with severe allergies must notify us during order inquiry submission.</p>

        <h2>4. Acceptable Use & Security Policy</h2>
        <p>Users agree not to attempt unauthorized access to platform servers, inject harmful scripts, perform automated scraping, or transmit malicious code. All system forms are monitored and protected against automated attacks, SQL injection, and unauthorized data extraction.</p>

        <h2>5. Intellectual Property</h2>
        <p>All content, designs, custom cake photos, logos, and website layouts are protected under intellectual property rights. Reproduction without explicit written permission is prohibited.</p>

        <h2>6. Modifications to Terms</h2>
        <p>We reserve the right to modify these Terms & Conditions at any time. Continued use of our website constitutes acceptance of updated terms.</p>

        <h2>7. Contact Information</h2>
        <p>For questions regarding custom orders or these terms, please contact:</p>
        <p><strong>Email:</strong> {{ isset($tenant) && $tenant->email ? $tenant->email : 'support@bakerypro.com' }}</p>

        <div class="footer-text">
            © 2026 {{ isset($tenant) ? $tenant->name : 'BakeryPro' }}. All rights reserved. Terms of Service.
        </div>
    </div>

</body>
</html>
