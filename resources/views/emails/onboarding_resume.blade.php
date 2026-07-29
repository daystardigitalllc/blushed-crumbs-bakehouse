<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $variant === 'reminder' ? 'Your bakery website expires soon' : 'Your bakery website is ready to review' }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #fdf7f9; color: #4a2133; margin: 0; padding: 0; line-height: 1.6; }
        .email-container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 20px; overflow: hidden; border: 1px solid #f8c6d7; box-shadow: 0 10px 30px rgba(92, 29, 55, 0.08); }
        .email-header { background: linear-gradient(135deg, #5c1d37, #7a2b4a); padding: 30px 20px; text-align: center; color: #ffffff; }
        .email-header h1 { font-size: 1.6rem; margin: 0 0 6px 0; font-family: Georgia, serif; }
        .email-body { padding: 30px 25px; text-align: center; }
        .email-body p { font-size: 1rem; color: #4a2133; }
        .cta-button { display: inline-block; background: #e67399; color: #ffffff !important; text-decoration: none; padding: 14px 32px; border-radius: 30px; font-weight: 700; font-size: 1rem; margin: 20px 0; }
        .expiry-note { font-size: 0.85rem; color: #888888; margin-top: 10px; }
        .footer { background: #faf0f4; text-align: center; padding: 18px 20px; font-size: 0.82rem; color: #888888; border-top: 1px solid #f8c6d7; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>🌸 {{ $tenant->name ?? 'Your Bakery' }}</h1>
        </div>

        <div class="email-body">
            @if($variant === 'reminder')
                <p>You started building your bakery website but haven't finished reviewing it yet.</p>
                <p><strong>This draft expires in about 12 hours</strong> — after that, your uploaded photos and AI-built content will be permanently deleted.</p>
            @else
                <p>Good news — our AI has finished reading through everything you uploaded and built a first draft of your bakery website.</p>
                <p>It's ready for you to review, edit, and make live.</p>
            @endif

            <a href="{{ $resumeUrl }}" class="cta-button">Review My Website</a>

            <p class="expiry-note">This link only works while you're logged into your account.</p>
        </div>

        <div class="footer">
            <p>Sent automatically by Doughmain</p>
        </div>
    </div>
</body>
</html>
