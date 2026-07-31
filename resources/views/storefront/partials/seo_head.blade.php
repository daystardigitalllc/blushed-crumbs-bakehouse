@php
    $seoPage = $page ?? 'home';
    $seoPath = match ($seoPage) {
        'about' => '/about',
        'menu' => '/menu',
        'gallery' => '/gallery',
        'policy' => '/policy',
        default => '',
    };
    $seoCanonical = $tenant->publicUrl($seoPath);
    $seoTitle = $tenant->seoTitle($seoPage);
    $seoDescription = $tenant->seoDescription($seoPage);
    $seoImage = !empty($tenant->logo_path)
        ? asset($tenant->logo_path)
        : (!empty($tenant->gallery_images[0]) ? asset($tenant->gallery_images[0]) : asset('images/favicon.png'));
@endphp
<title>{{ $seoTitle }}</title>
<!-- Favicon -->
@if(isset($tenant) && $tenant->logo_path)
    <link rel="icon" href="{{ asset($tenant->logo_path) }}">
@else
    <link rel="icon" href="{{ asset('images/favicon.png') }}">
@endif
<meta name="description" content="{{ $seoDescription }}">
<link rel="canonical" href="{{ $seoCanonical }}">
<meta name="csrf-token" content="{{ csrf_token() }}">

<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $tenant->name }}">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:url" content="{{ $seoCanonical }}">
<meta property="og:image" content="{{ $seoImage }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $seoDescription }}">
<meta name="twitter:image" content="{{ $seoImage }}">

<script type="application/ld+json">{!! json_encode($tenant->localBusinessSchema(), JSON_UNESCAPED_SLASHES) !!}</script>
