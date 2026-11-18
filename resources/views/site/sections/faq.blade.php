@php
    $content = $section['content'] ?? [];
    $items = $content['items'] ?? [];
@endphp

@if (count($items))
    <section class="section">
        <div class="container-page">
            <div class="grid gap-12 lg:grid-cols-[0.9fr_1.1fr]">
                <x-site.section-heading
                    :eyebrow="$content['eyebrow'] ?? null"
                    :heading="$content['heading'] ?? null"
                    :intro="$content['intro'] ?? null"
                    level="2" />

                <div class="divide-y divide-white/10">
                    @foreach ($items as $item)
                        <details class="group py-5" @if ($loop->first) open @endif>
                            <summary class="flex cursor-pointer items-start justify-between gap-4 text-base font-semibold text-ink marker:content-none">
                                {{ $item['question'] ?? '' }}
                                <span class="mt-0.5 text-accent-soft transition group-open:rotate-45">
                                    <x-site.icon name="plus" :size="18" />
                                </span>
                            </summary>
                            <div class="rich-text mt-4 text-sm">
                                @richtext($item['answer'] ?? '')
                            </div>
                        </details>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif
