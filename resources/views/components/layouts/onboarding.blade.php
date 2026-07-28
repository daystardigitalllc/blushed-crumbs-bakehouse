@props(['title' => 'Build Your Bakery Website', 'progress' => null])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $title }} · DoughMain</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/onboarding.css') }}">

    @livewireStyles
</head>
<body class="ob-shell">
    <div class="ob-page">
        <header class="ob-header">
            <span class="ob-logo">DoughMain</span>
        </header>

        @if ($progress !== null)
            <div class="ob-progress" role="progressbar" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                <div class="ob-progress-track">
                    <div class="ob-progress-fill" style="width: {{ $progress }}%"></div>
                </div>
            </div>
        @endif

        <main class="ob-main">
            {{ $slot }}
        </main>

        <footer class="ob-footer">
            &copy; {{ date('Y') }} DoughMain
        </footer>
    </div>

    @livewireScripts
</body>
</html>
