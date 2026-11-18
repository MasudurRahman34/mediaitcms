@extends('site.layouts.app')

@section('content')
    <x-site.page-hero
        :eyebrow="$solution->intro ? 'Case study' : null"
        :title="$solution->title"
        :description="$solution->intro"
        :breadcrumbs="[
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'Our Work', 'url' => route('solutions.index')],
            ['label' => $solution->title],
        ]">
        <div class="mt-8 flex flex-wrap gap-4">
            @if ($solution->live_url)
                <x-site.button :href="$solution->live_url" icon="external-link" external>Visit live product</x-site.button>
            @endif
            <x-site.button :href="route('contact')" variant="secondary">Build something similar</x-site.button>
        </div>
    </x-site.page-hero>

    @if ($solution->cover)
        <section class="relative -mt-4">
            <div class="container-page">
                <div class="glass-card overflow-hidden p-2 shadow-2xl shadow-black/50">
                    <x-site.media-image :media="$solution->cover" :alt="$solution->title.' cover'"
                                        :eager="true"
                                        sizes="(min-width: 1024px) 80vw, 100vw"
                                        class="aspect-[16/9] w-full rounded-2xl object-cover" />
                </div>
            </div>
        </section>
    @endif

    <section class="section">
        <div class="container-page grid gap-14 lg:grid-cols-[1.15fr_0.85fr]">
            <div>
                <h2 class="heading-md">The challenge & solution</h2>
                @if ($solution->description)
                    <div class="rich-text mt-6">
                        @richtext($solution->description)
                    </div>
                @endif
            </div>

            <aside class="space-y-6">
                @if ($solution->technologies->isNotEmpty())
                    <div class="glass-card p-6">
                        <h2 class="text-sm font-semibold tracking-wide text-ink/90 uppercase">Technologies</h2>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ($solution->technologies as $technology)
                                <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-xs font-medium text-ink/70">
                                    {{ $technology->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if (! empty($solution->business_benefits))
                    <div class="glass-card p-6">
                        <h2 class="text-sm font-semibold tracking-wide text-ink/90 uppercase">Business benefits</h2>
                        <ul class="mt-4 space-y-4">
                            @foreach ($solution->business_benefits as $benefit)
                                <li class="flex items-start gap-3">
                                    <span class="mt-0.5 text-accent-soft"><x-site.icon name="check-circle" :size="18" /></span>
                                    <div>
                                        <p class="text-sm font-semibold text-ink">{{ $benefit['title'] ?? '' }}</p>
                                        @if (! empty($benefit['description']))
                                            <p class="mt-1 text-xs leading-relaxed text-ink/55">{{ $benefit['description'] }}</p>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="glass-card glow-accent p-6">
                    <h2 class="text-base font-semibold text-ink">Have a similar challenge?</h2>
                    <p class="mt-2 text-sm text-ink/60">We can scope a first version and a delivery plan.</p>
                    <x-site.button :href="route('contact')" class="mt-5 w-full" icon="arrow-right">Start a conversation</x-site.button>
                </div>
            </aside>
        </div>
    </section>

    @if ($solution->features->isNotEmpty())
        <section class="section border-t border-white/5">
            <div class="container-page">
                <x-site.section-heading eyebrow="Key features" heading="What the product does" align="center" />

                <div class="mt-14 space-y-16">
                    @foreach ($solution->features as $feature)
                        <div class="grid items-center gap-10 lg:grid-cols-2">
                            <div class="{{ $loop->odd ? '' : 'lg:order-2' }}">
                                <span class="text-xs font-semibold tracking-[0.2em] text-accent-soft uppercase">
                                    Feature {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </span>
                                <h3 class="heading-md mt-3">{{ $feature->title }}</h3>
                                @if ($feature->description)
                                    <div class="rich-text mt-4 text-sm">
                                        @richtext($feature->description)
                                    </div>
                                @endif
                            </div>

                            <div class="{{ $loop->odd ? 'lg:order-2' : 'lg:order-1' }}">
                                @if ($feature->media)
                                    <div class="glass-card overflow-hidden p-2">
                                        <x-site.media-image :media="$feature->media" :alt="$feature->title"
                                                            sizes="(min-width: 1024px) 46vw, 100vw"
                                                            class="aspect-[16/10] w-full rounded-2xl object-cover" />
                                    </div>
                                @else
                                    <div class="glass-card grid aspect-[16/10] place-items-center bg-brand-gradient">
                                        <span class="text-lg font-semibold text-white/25">{{ $feature->title }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($solution->screenshots->isNotEmpty())
        @php
            $shots = $solution->screenshots->map(fn ($shot) => [
                'src' => $shot->media?->variantUrl('1600'),
                'thumb' => $shot->media?->variantUrl('800'),
                'alt' => $shot->caption ?: ($shot->media?->altText() ?? $solution->title),
                'caption' => $shot->caption,
            ])->filter()->values();
        @endphp

        <section class="section border-t border-white/5">
            <div class="container-page">
                <x-site.section-heading eyebrow="Gallery" heading="Inside the product" align="center" />
                <div class="mt-12">
                    <x-react name="Gallery" :props="['items' => $shots]" />
                </div>
            </div>
        </section>
    @endif

    @if ($others->isNotEmpty())
        <section class="section border-t border-white/5">
            <div class="container-page">
                <x-site.section-heading eyebrow="More work" heading="Other projects" />
                <div class="mt-12 grid gap-7 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($others as $other)
                        <x-site.solution-card :solution="$other" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <x-site.cta-fallback />
@endsection
