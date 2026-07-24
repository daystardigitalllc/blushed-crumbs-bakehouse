<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - {{ isset($tenant) ? $tenant->name : 'BakeryPro' }}</title>
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
        <h1>Privacy Policy</h1>
        <div class="updated-date">Last Updated: July 24, 2026</div>

        <p>At <strong>{{ isset($tenant) ? $tenant->name : 'BakeryPro' }}</strong>, we are committed to protecting your privacy and ensuring your personal information is handled with care and high security standards.</p>

        <h2>1. Information We Collect</h2>
        <p>When you place custom baking orders, create an account, or contact us through our website, we may collect personal information including:</p>
        <ul>
            <li>Full Name</li>
            <li>Email Address and Phone Number</li>
            <li>Event dates, delivery addresses, and custom order requests</li>
            <li>Dietary preferences and allergy information provided for custom baking</li>
            <li>Payment confirmation details and transaction history</li>
        </ul>

        <h2>2. How We Use Your Information</h2>
        <p>Your information is used strictly to provide custom bakery services and enhance your experience, including:</p>
        <ul>
            <li>Fulfilling, processing, and delivering your custom cake and pastry orders</li>
            <li>Sending order confirmations, invoice notices, and delivery updates</li>
            <li>Communicating regarding design details, dietary specifications, and schedule availability</li>
            <li>Improving our website performance, security, and customer service</li>
        </ul>

        <h2>3. Data Protection & SQL Security</h2>
        <p>We enforce strict security practices to safeguard customer data against unauthorized access, loss, or manipulation. All database queries utilize parameterized statement protections to eliminate vulnerability to SQL injection attacks, and strict CSRF and XSS filters are maintained across all input forms.</p>

        <h2>4. Data Sharing & Third Parties</h2>
        <p>We do not sell, rent, or trade your personal information to third-party marketers. We may share necessary details only with trusted service providers (e.g., payment gateways, email deliverability services) strictly to process your transactions.</p>

        <h2>5. Your Rights & Data Retention</h2>
        <p>You have the right to request access to, correction of, or deletion of your personal data stored in our system. If you wish to exercise these rights, please contact us directly via our support channels.</p>

        <h2>6. Cookies & Tracking</h2>
        <p>Our website uses essential session cookies to remember your active bakery order state, administrative session, and user preferences. No intrusive tracking cookies are placed without consent.</p>

        <h2>7. Contact Us</h2>
        <p>If you have questions regarding this Privacy Policy or data handling practices, please contact us at:</p>
        <p><strong>Email:</strong> {{ isset($tenant) && $tenant->email ? $tenant->email : 'privacy@bakerypro.com' }}</p>

        <div class="footer-text">
            © 2026 {{ isset($tenant) ? $tenant->name : 'BakeryPro' }}. All rights reserved. Security & Privacy Compliant.
        </div>
    </div>

</body>
</html>
