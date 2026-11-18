@php
    $content = $section['content'] ?? [];
    $align = $content['align'] ?? 'left';
    $image = \App\Support\MediaLookup::find($content['image'] ?? null);
    $hasImage = $image !== null;
    $gradient = $content['show_gradient'] ?? true;
@endphp

<section class="relative overflow-hidden {{ $gradient ? 'bg-brand-gradient' : 'bg-near-black' }}">
    <div class="pointer-events-none absolute inset-0 grid-pattern opacity-30"></div>
    <div class="pointer-events-none absolute -top-40 -right-20 size-[32rem] rounded-full bg-accent/15 blur-[100px]"></div>
    <div class="pointer-events-none absolute bottom-0 left-1/4 h-64 w-96 rounded-full bg-navy-500/20 blur-[90px]"></div>

    <div class="container-page relative py-20 sm:py-24 lg:py-32">
        <div class="grid items-center gap-12 {{ $hasImage ? 'lg:grid-cols-[1.05fr_0.95fr]' : 'mx-auto max-w-4xl text-center' }}">
            <div class="{{ $align === 'center' && ! $hasImage ? 'mx-auto' : '' }} reveal">
                @if (! empty($content['eyebrow']))
                    <span class="eyebrow">{{ $content['eyebrow'] }}</span>
                @endif

                @if (! empty($content['heading']))
                    <h1 class="heading-xl {{ ! empty($content['eyebrow']) ? 'mt-6' : '' }}">
                        {{ $content['heading'] }}
                    </h1>
                @endif

                @if (! empty($content['subheading']))
                    <p class="body-lg mt-6 {{ $hasImage ? 'max-w-xl' : 'mx-auto max-w-2xl' }}">
                        {{ $content['subheading'] }}
                    </p>
                @endif

                @php
                    $ctas = array_filter([
                        $content['primary_cta'] ?? null,
                        $content['secondary_cta'] ?? null,
                    ], fn ($cta) => is_array($cta) && ! empty($cta['label']));
                @endphp

                @if ($ctas)
                    <div class="mt-9 flex flex-wrap items-center gap-4 {{ $align === 'center' && ! $hasImage ? 'justify-center' : '' }}">
                        @foreach ($ctas as $index => $cta)
                            <x-site.button
                                :href="$cta['url'] ?? '#'"
                                :variant="$index === 0 ? 'primary' : 'secondary'"
                                size="lg"
                                :icon="$index === 0 ? 'arrow-right' : null">
                                {{ $cta['label'] }}
                            </x-site.button>
                        @endforeach
                    </div>
                @endif
            </div>

            @if ($hasImage)
                <div class="relative">
                    <div class="absolute -inset-6 rounded-[2.5rem] bg-accent/10 blur-2xl"></div>
                    <div class="glass-card relative overflow-hidden p-2 shadow-2xl shadow-black/50">
                        <x-site.media-image :media="$image" :eager="true"
                                            sizes="(min-width: 1024px) 44vw, 100vw"
                                            class="aspect-[4/3] w-full rounded-[1.35rem] object-cover" />
                    </div>
                    <div class="absolute -bottom-5 -left-5 hidden rounded-2xl border border-white/10 bg-surface-800/90 px-5 py-4 shadow-xl backdrop-blur sm:block">
                        <p class="text-xs font-medium tracking-wide text-accent-soft uppercase">UK-based team</p>
                        <p class="mt-1 text-sm text-ink/70">Building since 2014</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
