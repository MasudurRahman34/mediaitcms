@extends('site.layouts.app')

@section('content')
    <x-site.page-hero
        :title="$service->title"
        :description="$service->excerpt"
        :breadcrumbs="[
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'Services', 'url' => route('services.index')],
            ['label' => $service->title],
        ]">
        <div class="mt-8">
            <x-site.button :href="route('contact')" icon="arrow-right">Talk to an expert</x-site.button>
        </div>
    </x-site.page-hero>

    <section class="section">
        <div class="container-page grid gap-14 lg:grid-cols-[1.15fr_0.85fr]">
            <div>
                @if ($service->description)
                    <div class="rich-text">
                        @richtext($service->description)
                    </div>
                @endif

                @if (! empty($service->benefits))
                    <div class="mt-12">
                        <h2 class="heading-md">What you get</h2>
                        <div class="mt-6 grid gap-4 sm:grid-cols-2">
                            @foreach ($service->benefits as $benefit)
                                <div class="glass-card p-5">
                                    <div class="flex items-start gap-3">
                                        <span class="mt-0.5 text-accent-soft"><x-site.icon name="check-circle" :size="18" /></span>
                                        <div>
                                            <h3 class="text-sm font-semibold text-ink">{{ $benefit['title'] ?? '' }}</h3>
                                            @if (! empty($benefit['description']))
                                                <p class="mt-1.5 text-sm leading-relaxed text-ink/60">{{ $benefit['description'] }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if (! empty($service->faqs))
                    <div class="mt-14">
                        <h2 class="heading-md">Frequently asked</h2>
                        <div class="mt-6 divide-y divide-white/10">
                            @foreach ($service->faqs as $faq)
                                <details class="group py-5" @if ($loop->first) open @endif>
                                    <summary class="flex cursor-pointer items-start justify-between gap-4 font-semibold text-ink marker:content-none">
                                        {{ $faq['question'] ?? '' }}
                                        <span class="mt-0.5 text-accent-soft transition group-open:rotate-45">
                                            <x-site.icon name="plus" :size="18" />
                                        </span>
                                    </summary>
                                    <div class="rich-text mt-4 text-sm">
                                        @richtext($faq['answer'] ?? '')
                                    </div>
                                </details>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <aside class="space-y-6">
                @if ($service->cover)
                    <div class="glass-card overflow-hidden p-2">
                        <x-site.media-image :media="$service->cover" :alt="$service->title"
                                            class="aspect-[4/3] w-full rounded-2xl object-cover" />
                    </div>
                @endif

                <div class="glass-card p-6">
                    <h2 class="text-sm font-semibold tracking-wide text-ink/90 uppercase">Related services</h2>
                    <ul class="mt-4 space-y-3">
                        @forelse ($related as $item)
                            <li>
                                <a href="{{ $item->url() }}" class="group flex items-center justify-between gap-3 text-sm text-ink/65 transition hover:text-ink">
                                    {{ $item->title }}
                                    <x-site.icon name="arrow-right" :size="15" class="transition group-hover:translate-x-0.5" />
                                </a>
                            </li>
                        @empty
                            <li class="text-sm text-ink/45">More services coming soon.</li>
                        @endforelse
                    </ul>
                </div>

                <div class="glass-card glow-accent p-6">
                    <h2 class="text-base font-semibold text-ink">Need this delivered?</h2>
                    <p class="mt-2 text-sm text-ink/60">Book a no-obligation call with a senior engineer.</p>
                    <x-site.button :href="route('contact')" class="mt-5 w-full" icon="arrow-right">Get in touch</x-site.button>
                </div>
            </aside>
        </div>
    </section>

    @if ($faqJsonLd)
        @push('head')
            <script type="application/ld+json">{!! json_encode($faqJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
        @endpush
    @endif

    <x-site.cta-fallback />
@endsection
