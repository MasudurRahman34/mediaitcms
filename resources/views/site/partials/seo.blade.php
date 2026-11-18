@php
    /** @var \App\Services\Seo\SeoData|null $seo */
    $seo = $seo ?? null;
    $title = $seo?->title ?? $settings->get('company_name', config('app.name'));
    $description = $seo?->description ?: $settings->get('seo_default_description');
    $canonical = $seo?->canonical ?? url()->current();
    $image = $seo?->image ?: asset('media/brand/og-default.svg');
    $type = $seo?->type ?? 'website';
    $noindex = $seo?->noindex ?? false;
    $company = $settings->get('company_name', config('app.name'));
@endphp

<title>{{ $title }}</title>
<meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags((string) $description), 160) }}">
<link rel="canonical" href="{{ $canonical }}">
<meta name="robots" content="{{ $noindex ? 'noindex, nofollow' : 'index, follow, max-image-preview:large' }}">

<meta property="og:type" content="{{ $type }}">
<meta property="og:site_name" content="{{ $company }}">
<meta property="og:title" content="{{ $title }}">
@if ($description)
    <meta property="og:description" content="{{ \Illuminate\Support\Str::limit(strip_tags((string) $description), 200) }}">
@endif
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $image }}">
<meta property="og:locale" content="en_GB">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
@if ($description)
    <meta name="twitter:description" content="{{ \Illuminate\Support\Str::limit(strip_tags((string) $description), 200) }}">
@endif
<meta name="twitter:image" content="{{ $image }}">

@foreach (($seo?->graph() ?? []) as $schema)
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endforeach
