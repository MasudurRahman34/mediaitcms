@extends('site.layouts.app')

@section('content')
    <x-site.page-hero
        eyebrow="Careers"
        title="Build things that matter"
        description="We are a small, senior team in Swindon working on software and AI products for clients across the UK."
        :breadcrumbs="[['label' => 'Home', 'url' => route('home')], ['label' => 'Careers']]" />

    <section class="section">
        <div class="container-page">
            @if ($jobs->isEmpty())
                <div class="glass-card p-10 text-center">
                    <h2 class="text-xl font-semibold text-ink">No open roles right now</h2>
                    <p class="mt-3 text-sm text-ink/60">
                        We are always happy to hear from strong engineers. Send us a speculative application.
                    </p>
                    <x-site.button :href="route('contact')" class="mt-6" icon="arrow-right">Get in touch</x-site.button>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($jobs as $job)
                        <article class="group glass-card glass-card-hover relative p-6 sm:p-7">
                            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2 text-xs text-ink/45">
                                        @if ($job->department)
                                            <span class="rounded-full border border-white/10 bg-white/5 px-2.5 py-1">{{ $job->department }}</span>
                                        @endif
                                        @if ($job->location)
                                            <span class="flex items-center gap-1"><x-site.icon name="map-pin" :size="13" />{{ $job->location }}</span>
                                        @endif
                                        <span class="flex items-center gap-1"><x-site.icon name="clock" :size="13" />{{ ucfirst($job->employment_type) }}</span>
                                    </div>

                                    <h2 class="mt-3 text-xl font-semibold text-ink">
                                        <a href="{{ $job->url() }}" class="after:absolute after:inset-0">{{ $job->title }}</a>
                                    </h2>

                                    @if ($job->salary_range)
                                        <p class="mt-2 text-sm text-ink/60">{{ $job->salary_range }}</p>
                                    @endif
                                </div>

                                <div class="flex items-center gap-4">
                                    @if ($job->closing_at)
                                        <span class="text-xs text-ink/45">Closes {{ $job->closing_at->format('j M Y') }}</span>
                                    @endif
                                    <span class="inline-flex items-center gap-1.5 text-sm font-medium text-accent-soft transition group-hover:gap-2.5 group-hover:text-ink">
                                        View role
                                        <x-site.icon name="arrow-right" :size="16" />
                                    </span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <x-site.cta-fallback />
@endsection
