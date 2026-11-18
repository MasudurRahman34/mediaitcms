@php
    $content = $section['content'] ?? [];
    $limit = (int) ($content['limit'] ?? 3);
    $source = $content['source'] ?? 'featured';

    $solutions = \App\Models\Solution::query()
        ->published()
        ->when($source === 'featured', fn ($q) => $q->where('is_featured', true))
        ->ordered()
        ->limit($limit)
        ->get();

    if ($solutions->isEmpty() && $source === 'featured') {
        $solutions = \App\Models\Solution::query()->published()->ordered()->limit($limit)->get();
    }
@endphp

@if ($solutions->isNotEmpty())
    <section class="section relative">
        <div class="container-page">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                <x-site.section-heading
                    :eyebrow="$content['eyebrow'] ?? null"
                    :heading="$content['heading'] ?? null"
                    :intro="$content['intro'] ?? null" />

                @if ($content['show_cta'] ?? true)
                    <x-site.button :href="route('solutions.index')" variant="secondary" icon="arrow-right" class="shrink-0">
                        Our work
                    </x-site.button>
                @endif
            </div>

            <div class="mt-12 grid gap-7 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($solutions as $solution)
                    <x-site.solution-card :solution="$solution" />
                @endforeach
            </div>
        </div>
    </section>
@endif
