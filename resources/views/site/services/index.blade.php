@extends('site.layouts.app')

@section('content')
    <x-site.page-hero
        title="Services built around outcomes"
        description="From discovery to delivery and support, we plug in where you need senior engineering capability."
        :breadcrumbs="[['label' => 'Home', 'url' => route('home')], ['label' => 'Services']]">
        <div class="mt-8">
            <x-site.button :href="route('contact')" icon="arrow-right">Discuss your project</x-site.button>
        </div>
    </x-site.page-hero>

    <section class="section">
        <div class="container-page">
            @if ($services->isEmpty())
                <p class="text-ink/60">Services will appear here once published from the CMS.</p>
            @else
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($services as $service)
                        <x-site.service-card :service="$service" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <x-site.cta-fallback />
@endsection
