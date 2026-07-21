@php
    $seo = $seo ?? [];
    $metaTitle = $seo['meta_title'] ?? $seo['title'] ?? config('seo.title') ?? config('app.name');
    $metaDescription = $seo['meta_description'] ?? config('seo.description');
    $metaUrl = $seo['url'] ?? url()->current();
    $rawImage = $seo['image'] ?? config('seo.image');
    $metaImage = $rawImage ? asset(ltrim($rawImage, '/')) : null;
@endphp

<meta name="description" content="{{ $metaDescription }}">
<link rel="canonical" href="{{ $metaUrl }}">

<meta property="og:site_name" content="{{ config('seo.site_name', config('app.name')) }}">
<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:url" content="{{ $metaUrl }}">
<meta property="og:type" content="website">
@if($metaImage)
    <meta property="og:image" content="{{ $metaImage }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="{{ $metaImage }}">
@endif

@if(config('seo.twitter.site'))
    <meta name="twitter:site" content="{{ config('seo.twitter.site') }}">
@endif
