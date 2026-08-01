<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribed | {{ $tenant->name ?? 'Doughmain.pro' }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background:#f7f3f4; color:#333; display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; padding:24px; box-sizing:border-box; }
        .card { background:#fff; border-radius:20px; padding:40px 32px; max-width:440px; text-align:center; box-shadow:0 10px 30px rgba(0,0,0,0.06); }
        h1 { font-size:1.4rem; color:#5c1d37; margin:0 0 12px; }
        p { color:#666; font-size:0.95rem; line-height:1.6; }
    </style>
</head>
<body>
    <div class="card">
        <h1>You've been unsubscribed</h1>
        <p>You won't receive any more promotional emails from {{ $tenant->name ?? 'this bakery' }}. You can still place orders on their website any time.</p>
    </div>
</body>
</html>
