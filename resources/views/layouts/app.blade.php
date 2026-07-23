<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Blushed Crumbs Bakehouse | Where Every Celebration Gets Its Sweet Ending')</title>
    <!-- Favicon -->
    @if(isset($tenant) && $tenant->logo_path)
        <link rel="icon" href="{{ asset($tenant->logo_path) }}">
    @else
        <link rel="icon" href="{{ asset('images/favicon.png') }}">
    @endif
    <meta name="description" content="Custom artisanal cakes, cupcakes, treat boxes & wedding baking in Tennessee. Order custom cakes online with ease.">
    <meta name="csrf-token" content="{{ csrf_token() ?? '' }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- CSS Assets -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="{{ isset($tenant) && $tenant->theme_id ? 'theme-' . $tenant->theme_id : 'theme-sweet_elegant' }}">
    @yield('content')

    <!-- JavaScript Assets -->
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
