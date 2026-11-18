@php
    $content = $section['content'] ?? [];
    $items = $content['items'] ?? [];
    $columns = (int) ($content['columns'] ?? 3);
    $grid = match ($columns) {
        2 => 'sm:grid-cols-2',
        4 => 'sm:grid-cols-2 lg:grid-cols-4',
        default => 'sm:grid-cols-2 lg:grid-cols-3',
    };
@endphp

@if (count($items))
    <section class="section">
        <div class="container-page">
            <x-site.section-heading
                :eyebrow="$content['eyebrow'] ?? null"
                :heading="$content['heading'] ?? null"
                :intro="$content['intro'] ?? null"
                align="center" />

            <div class="mt-14 grid gap-6 {{ $grid }}">
                @foreach ($items as $item)
                    <article class="glass-card glass-card-hover p-7">
                        <span class="icon-chip">
                            <x-site.icon :name="$item['icon'] ?? 'sparkles'" :size="22" />
                        </span>
                        <h3 class="mt-5 text-lg font-semibold text-ink">{{ $item['title'] ?? '' }}</h3>
                        <p class="mt-2.5 text-sm leading-relaxed text-ink/60">{{ $item['description'] ?? '' }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif
