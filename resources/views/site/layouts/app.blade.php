<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('site.partials.seo')

    <meta name="theme-color" content="#0E0F11">

    @if ($favicon = $settings->imageUrl('favicon'))
        <link rel="icon" href="{{ $favicon }}">
    @else
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @endif

    <link rel="preconnect" href="{{ url('/') }}">
    @vite(['resources/css/app.css', 'resources/js/public.tsx'])
    @stack('head')

    @if ($analytics = $settings->get('seo_analytics_id'))
        @production
            <script async src="https://www.googletagmanager.com/gtag/js?id={{ $analytics }}"></script>
            <script>
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());
                gtag('config', @json($analytics));
            </script>
        @endproduction
    @endif
</head>
<body class="min-h-dvh bg-near-black text-ink">
    <a href="#main"
       class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[100] focus:rounded-lg focus:bg-accent focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">
        Skip to content
    </a>

    @include('site.partials.header')

    <main id="main">
        @yield('content')
    </main>

    @include('site.partials.footer')

    @include('site.partials.flash')
</body>
</html>
