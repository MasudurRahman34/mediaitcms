@php
    $content = $section['content'] ?? [];
    $items = $content['items'] ?? [];
@endphp

@if (count($items))
    <section class="section">
        <div class="container-page">
            <x-site.section-heading
                :eyebrow="$content['eyebrow'] ?? null"
                :heading="$content['heading'] ?? null"
                align="center" />

            <div class="mt-14 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($items as $item)
                    @php $avatar = \App\Support\MediaLookup::find($item['avatar'] ?? null); @endphp
                    <figure class="glass-card flex h-full flex-col p-7">
                        <div class="flex gap-1 text-accent-soft" aria-hidden="true">
                            @for ($i = 0; $i < 5; $i++)
                                <x-site.icon name="star" :size="15" />
                            @endfor
                        </div>
                        <blockquote class="mt-5 flex-1 text-sm leading-relaxed text-ink/75">
                            “{{ $item['quote'] ?? '' }}”
                        </blockquote>
                        <figcaption class="mt-6 flex items-center gap-3 border-t border-white/10 pt-5">
                            @if ($avatar)
                                <img src="{{ $avatar->variantUrl('400') }}" alt="{{ $item['name'] ?? '' }}"
                                     class="size-10 rounded-full object-cover" loading="lazy" decoding="async">
                            @else
                                <span class="grid size-10 place-items-center rounded-full bg-accent/15 text-sm font-semibold text-accent-soft">
                                    {{ mb_substr($item['name'] ?? '?', 0, 1) }}
                                </span>
                            @endif
                            <div>
                                <p class="text-sm font-semibold text-ink">{{ $item['name'] ?? '' }}</p>
                                <p class="text-xs text-ink/50">{{ $item['role'] ?? '' }}</p>
                            </div>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </div>
    </section>
@endif
