<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DoughMain</title>
    <!-- Favicon -->
    @if(isset($tenant) && $tenant->logo_path)
        <link rel="icon" href="{{ asset($tenant->logo_path) }}">
    @else
        <link rel="icon" href="{{ asset('images/favicon.png') }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #e67399;
            --primary-dark: #d63384;
            --accent: #6d28d9;
            --bg-start: #fff7fa;
            --bg-end: #f0e6ff;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --error-bg: #fee2e2;
            --error-text: #b91c1c;
            --font-body: 'Inter', sans-serif;
            --font-heading: 'Outfit', sans-serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-body);
            background: linear-gradient(135deg, var(--bg-start) 0%, var(--bg-end) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-main);
            padding: 1.5rem;
        }

        .auth-container {
            width: 100%;
            max-width: 420px;
            animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo {
            font-family: var(--font-heading);
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .tagline {
            color: var(--text-muted);
            font-size: 1rem;
            font-weight: 400;
        }

        .card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 16px;
            padding: 2.5rem 2rem;
            box-shadow: 0 10px 40px rgba(109, 40, 217, 0.08);
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--text-main);
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            font-family: var(--font-body);
            font-size: 1rem;
            color: var(--text-main);
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02) inset;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(230, 115, 153, 0.15);
        }

        .form-control::placeholder {
            color: #9ca3af;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .remember-me input[type="checkbox"] {
            width: 1rem;
            height: 1rem;
            border-radius: 4px;
            border: 1px solid #e5e7eb;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .remember-me label {
            font-size: 0.9rem;
            color: var(--text-muted);
            cursor: pointer;
            user-select: none;
        }

        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-family: var(--font-heading);
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(230, 115, 153, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(230, 115, 153, 0.4);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .links {
            text-align: center;
            margin-top: 1.5rem;
        }

        .links a {
            color: var(--accent);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .links a:hover {
            color: var(--primary);
            text-decoration: underline;
        }

        .errors {
            background: var(--error-bg);
            color: var(--error-text);
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            border: 1px solid rgba(185, 28, 28, 0.2);
        }

        .errors ul {
            margin: 0;
            padding-left: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="header">
            <div class="logo" style="display:flex; align-items:center; justify-content:center; gap:10px;">
                <img src="{{ asset('images/doughmain_logo.png') }}" alt="Doughmain Logo" style="height:52px; width:auto;">
                <span>Doughmain.pro</span>
            </div>
            <p class="tagline">Manage your bakery website</p>
        </div>

        <div class="card">
            @if($errors->any())
                <div class="errors">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus placeholder="baker@example.com">
                </div>

                <div class="form-group">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <label for="password" class="form-label" style="margin-bottom:0;">Password</label>
                        <a href="{{ route('password.request') }}" style="font-size:0.85rem; color:var(--accent); text-decoration:none; font-weight:500;">Forgot Password?</a>
                    </div>
                    <input id="password" type="password" class="form-control" name="password" required placeholder="••••••••" style="margin-top:0.5rem;">
                </div>

                <div class="remember-me">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember">Remember me</label>
                </div>

                <button type="submit" class="btn-submit">Sign In</button>
            </form>

            <div class="links">
                <a href="/register">Don't have an account? Sign up</a>
                <div style="margin-top: 1.25rem; font-size: 0.8rem; color: var(--text-muted);">
                    <a href="/legal" style="color: var(--accent); font-weight: 600;">Legal Center</a> &middot; 
                    <a href="/legal/privacy" style="color: var(--text-muted);">Privacy Policy</a> &middot; 
                    <a href="/legal/terms" style="color: var(--text-muted);">Terms &amp; Conditions</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
