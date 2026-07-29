@php
    $isPublicPage = $isPublicPage ?? false;
    $seoTitle = $seoTitle ?? config('app.name', 'DezeStore');
    $seoDescription = $seoDescription ?? null;
    $canonicalUrl = $canonicalUrl ?? url()->current();
    $ogTitle = $ogTitle ?? $seoTitle;
    $ogDescription = $ogDescription ?? $seoDescription;
    $ogImage = $ogImage ?? 'https://dezestore.com/images/dezeiconlogo.webp';
    $ogType = $ogType ?? 'website';
    $seoKeywords = $seoKeywords ?? '';
    $robotsContent = $robotsContent ?? 'index, follow';
    $socialLinks = $socialLinks ?? [];
    $siteLogo = 'https://dezestore.com/images/dezeiconlogo.webp';
@endphp

<title inertia>{{ $seoTitle }}</title>

@if ($isPublicPage)
    <meta name="description" content="{{ $seoDescription }}">
    <meta name="keywords" content="{{ $seoKeywords }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta name="robots" content="{{ $robotsContent }}">

    <meta property="og:site_name" content="DezeStore">
    <meta property="og:title" content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $ogDescription }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:alt" content="DezeStore logo and online shopping brand mark">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:type" content="{{ $ogType }}">

    <meta name="thumbnail" content="{{ $siteLogo }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $ogTitle }}">
    <meta name="twitter:description" content="{{ $ogDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">
    <meta name="twitter:image:alt" content="DezeStore logo and online shopping brand mark">

    @if (config('services.google.site_verification'))
        <meta name="google-site-verification" content="{{ config('services.google.site_verification') }}">
    @endif

    @foreach ($socialLinks as $socialLink)
        <link rel="me" href="{{ $socialLink }}">
    @endforeach
@endif
