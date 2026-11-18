@extends('site.layouts.app')

@section('content')
    <x-site.page-hero
        :eyebrow="$job->department ?? 'Careers'"
        :title="$job->title"
        :breadcrumbs="[
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'Careers', 'url' => route('careers.index')],
            ['label' => $job->title],
        ]">
        <div class="mt-7 flex flex-wrap items-center gap-x-6 gap-y-3 text-sm text-ink/60">
            @if ($job->location)
                <span class="flex items-center gap-2"><x-site.icon name="map-pin" :size="16" class="text-accent-soft" />{{ $job->location }}</span>
            @endif
            <span class="flex items-center gap-2"><x-site.icon name="briefcase" :size="16" class="text-accent-soft" />{{ ucfirst($job->employment_type) }}</span>
            @if ($job->experience_level)
                <span class="flex items-center gap-2"><x-site.icon name="chart" :size="16" class="text-accent-soft" />{{ $job->experience_level }}</span>
            @endif
            @if ($job->salary_range)
                <span class="flex items-center gap-2"><x-site.icon name="star" :size="16" class="text-accent-soft" />{{ $job->salary_range }}</span>
            @endif
            @if ($job->closing_at)
                <span class="flex items-center gap-2"><x-site.icon name="calendar" :size="16" class="text-accent-soft" />Closes {{ $job->closing_at->format('j M Y') }}</span>
            @endif
        </div>
    </x-site.page-hero>

    <section class="section">
        <div class="container-page grid gap-14 lg:grid-cols-[1.1fr_0.9fr]">
            <div class="rich-text">
                @richtext($job->description)

                @if ($job->requirements)
                    <h2 class="mt-10 text-2xl font-semibold text-ink">Requirements</h2>
                    @richtext($job->requirements)
                @endif
            </div>

            <aside>
                @if (! $acceptingApplications)
                    <div class="glass-card p-7">
                        <h2 class="text-lg font-semibold text-ink">Applications closed</h2>
                        <p class="mt-2 text-sm text-ink/60">
                            This vacancy is no longer accepting applications. We would still like to hear from you.
                        </p>
                        <x-site.button :href="route('contact')" variant="secondary" class="mt-5 w-full">Contact us</x-site.button>
                    </div>
                @else
                    <x-react name="JobApplicationForm" :props="[
                        'submitUrl' => route('careers.apply', $job->slug),
                        'jobTitle' => $job->title,
                        'errors' => $errors->toArray(),
                    ]" />
                @endif
            </aside>
        </div>
    </section>

    <x-site.cta-fallback />
@endsection
