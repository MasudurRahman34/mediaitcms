@extends('site.layouts.app')

@section('content')
    <x-site.page-hero
        eyebrow="Our work"
        title="Software we have designed, built and shipped"
        description="Real products solving real problems — from print-on-demand platforms to AI legal assistants."
        :breadcrumbs="[['label' => 'Home', 'url' => route('home')], ['label' => 'Our Work']]" />

    <section class="section">
        <div class="container-page">
            @if ($solutions->isEmpty())
                <p class="text-ink/60">Case studies will appear here once published from the CMS.</p>
            @else
                <div class="grid gap-7 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($solutions as $solution)
                        <x-site.solution-card :solution="$solution" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <x-site.cta-fallback />
@endsection
