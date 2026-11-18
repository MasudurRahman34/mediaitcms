@php
    $content = $section['content'] ?? [];
    $style = $content['style'] ?? 'gradient';

    $wrapper = match ($style) {
        'minimal' => 'bg-near-black',
        'panel' => 'bg-surface-900',
        default => 'bg-brand-gradient',
    };

    $ctas = array_filter([
        $content['primary_cta'] ?? null,
        $content['secondary_cta'] ?? null,
    ], fn ($cta) => is_array($cta) && ! empty($cta['label']));
@endphp

<section class="{{ $wrapper }} relative overflow-hidden border-y border-white/5">
    <div class="pointer-events-none absolute inset-0 grid-pattern opacity-25"></div>
    <div class="pointer-events-none absolute -top-24 left-1/2 size-[26rem] -translate-x-1/2 rounded-full bg-accent/15 blur-[90px]"></div>

    <div class="container-page relative py-16 sm:py-20">
        <div class="mx-auto max-w-3xl text-center">
            @if (! empty($content['eyebrow']))
                <span class="eyebrow">{{ $content['eyebrow'] }}</span>
            @endif

            <h2 class="heading-lg {{ ! empty($content['eyebrow']) ? 'mt-5' : '' }}">
                {{ $content['heading'] ?? '' }}
            </h2>

            @if (! empty($content['body']))
                <p class="body-lg mt-4">{{ $content['body'] }}</p>
            @endif

            <div class="mt-9 flex flex-wrap items-center justify-center gap-4">
                @foreach ($ctas as $index => $cta)
                    <x-site.button :href="$cta['url'] ?? '#'"
                                   :variant="$index === 0 ? 'primary' : 'secondary'"
                                   size="lg"
                                   :icon="$index === 0 ? 'arrow-right' : null">
                        {{ $cta['label'] }}
                    </x-site.button>
                @endforeach
            </div>
        </div>
    </div>
</section>
