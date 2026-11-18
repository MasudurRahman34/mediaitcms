@php
    $content = $section['content'] ?? [];
    $limit = (int) ($content['limit'] ?? 3);
    $source = $content['source'] ?? 'featured';

    $services = \App\Models\Service::query()
        ->published()
        ->when($source === 'featured', fn ($q) => $q->where('is_featured', true))
        ->ordered()
        ->limit($limit)
        ->get();

    if ($services->isEmpty() && $source === 'featured') {
        $services = \App\Models\Service::query()->published()->ordered()->limit($limit)->get();
    }
@endphp

@if ($services->isNotEmpty())
    <section class="section">
        <div class="container-page">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                <x-site.section-heading
                    :eyebrow="$content['eyebrow'] ?? null"
                    :heading="$content['heading'] ?? null"
                    :intro="$content['intro'] ?? null" />

                @if ($content['show_cta'] ?? true)
                    <x-site.button :href="route('services.index')" variant="secondary" icon="arrow-right" class="shrink-0">
                        All services
                    </x-site.button>
                @endif
            </div>

            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($services as $service)
                    <x-site.service-card :service="$service" />
                @endforeach
            </div>
        </div>
    </section>
@endif
