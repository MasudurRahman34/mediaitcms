@php
    $content = $section['content'] ?? [];
    $items = $content['items'] ?? [];
@endphp

@if (count($items))
    <section class="py-14">
        <div class="container-page">
            @if (! empty($content['heading']))
                <p class="text-center text-xs font-semibold tracking-[0.2em] text-ink/40 uppercase">{{ $content['heading'] }}</p>
            @endif

            <div class="mt-8 grid grid-cols-2 items-center gap-x-8 gap-y-8 sm:grid-cols-3 lg:grid-cols-5">
                @foreach ($items as $item)
                    @php $logo = \App\Support\MediaLookup::find($item['media_id'] ?? null); @endphp
                    <div class="flex items-center justify-center opacity-60 transition hover:opacity-100">
                        @if ($logo)
                            <img src="{{ $logo->url() }}" alt="{{ $item['label'] ?? $logo->altText() }}"
                                 class="max-h-10 w-auto grayscale" loading="lazy" decoding="async">
                        @elseif (! empty($item['label']))
                            <span class="text-sm font-semibold tracking-wide text-ink/60">{{ $item['label'] }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
