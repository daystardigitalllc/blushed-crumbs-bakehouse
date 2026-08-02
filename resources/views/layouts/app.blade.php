<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Blushed Crumbs Bakehouse | Where Every Celebration Gets Its Sweet Ending')</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon.png') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <meta name="description" content="Custom artisanal cakes, cupcakes, treat boxes & wedding baking in Tennessee. Order custom cakes online with ease.">
    <meta name="csrf-token" content="{{ csrf_token() ?? '' }}">
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', 'Blushed Crumbs Bakehouse | Where Every Celebration Gets Its Sweet Ending')">
    <meta property="og:description" content="@yield('og_description', 'Custom artisanal cakes, cupcakes, treat boxes & wedding baking in Tennessee. Order custom cakes online with ease.')">
    <meta property="og:image" content="@yield('og_image', asset('images/og_image.jpg'))">
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'Blushed Crumbs Bakehouse | Where Every Celebration Gets Its Sweet Ending')">
    <meta name="twitter:description" content="@yield('og_description', 'Custom artisanal cakes, cupcakes, treat boxes & wedding baking in Tennessee. Order custom cakes online with ease.')">
    <meta name="twitter:image" content="@yield('og_image', asset('images/og_image.jpg'))">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700;800&family=Oswald:wght@400;500;600;700&family=Open+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS Assets -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">
</head>
<body class="{{ isset($tenant) && $tenant->theme_id ? 'theme-' . $tenant->theme_id : 'theme-sweet_elegant' }}">
    @yield('content')

    <!-- JavaScript Assets -->
    <script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}"></script>
</body>
</html>
