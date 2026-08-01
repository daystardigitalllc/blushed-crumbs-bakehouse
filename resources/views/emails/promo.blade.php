<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $campaign->subject }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f7f3f4; color: #333333; margin: 0; padding: 0; line-height: 1.6; }
        .email-container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 20px; overflow: hidden; border: 1px solid #eeeeee; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06); }
        .email-header { background: linear-gradient(135deg, #5c1d37, #7a2b4a); padding: 28px 20px; text-align: center; color: #ffffff; }
        .email-header img { height: 48px; width: auto; border-radius: 6px; margin-bottom: 10px; }
        .email-header h1 { font-size: 1.4rem; margin: 0; font-family: Georgia, serif; }
        .email-body { padding: 30px 25px; font-size: 1rem; color: #333333; white-space: pre-line; }
        .coupon-box { background: #fff7fa; border: 2px dashed #e67399; border-radius: 12px; padding: 18px; text-align: center; margin: 20px 0; }
        .coupon-box .code { font-size: 1.4rem; font-weight: 800; letter-spacing: 0.08em; color: #5c1d37; }
        .footer { background: #faf0f4; text-align: center; padding: 18px 20px; font-size: 0.8rem; color: #888888; border-top: 1px solid #f0e4ea; }
        .footer a { color: #888888; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            @if($tenant->logo_path)
                <img src="{{ asset($tenant->logo_path) }}" alt="{{ $tenant->name }}">
            @endif
            <h1>{{ $tenant->name }}</h1>
        </div>

        <div class="email-body">{{ $campaign->body }}</div>

        @if($campaign->coupon_code)
            <div style="padding:0 25px 10px;">
                <div class="coupon-box">
                    <div style="font-size:0.85rem; color:#888; margin-bottom:6px;">Use code at checkout</div>
                    <div class="code">{{ $campaign->coupon_code }}</div>
                </div>
            </div>
        @endif

        <div class="footer">
            You're receiving this because you're subscribed to updates from {{ $tenant->name }}.<br>
            <a href="{{ $unsubscribeUrl }}">Unsubscribe from these emails</a>
        </div>
    </div>
</body>
</html>
