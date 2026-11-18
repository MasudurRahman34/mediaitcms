@extends('site.layouts.app')

@section('content')
    <x-site.page-hero
        eyebrow="Contact"
        title="Let's talk about your project"
        description="Tell us what you are building and we will come back with a clear, honest view of how we can help."
        :breadcrumbs="[['label' => 'Home', 'url' => route('home')], ['label' => 'Contact']]" />

    <section class="section">
        <div class="container-page grid gap-12 lg:grid-cols-[0.9fr_1.1fr]">
            <div>
                <div class="space-y-4">
                    @foreach ($settings->contactDetails() as $detail)
                        @if ($detail['value'])
                            <div class="glass-card flex items-start gap-4 p-5">
                                <span class="icon-chip size-10 rounded-xl">
                                    <x-site.icon :name="match ($detail['label']) { 'Phone' => 'phone', 'Email' => 'mail', default => 'map-pin' }" :size="18" />
                                </span>
                                <div>
                                    <p class="text-xs font-medium tracking-wide text-ink/45 uppercase">{{ $detail['label'] }}</p>
                                    @if ($detail['href'])
                                        <a href="{{ $detail['href'] }}" class="mt-1 block text-sm font-medium text-ink transition hover:text-accent-soft">{{ $detail['value'] }}</a>
                                    @else
                                        <p class="mt-1 text-sm text-ink/75">{{ $detail['value'] }}</p>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                @if ($hours = $settings->get('contact_hours'))
                    <p class="mt-6 flex items-center gap-2 text-sm text-ink/50">
                        <x-site.icon name="clock" :size="16" />
                        {{ $hours }}
                    </p>
                @endif

                @if ($map = $settings->get('contact_map_url'))
                    <div class="glass-card mt-8 overflow-hidden p-2">
                        <iframe src="{{ $map }}" title="Office location map"
                                class="aspect-[4/3] w-full rounded-2xl"
                                loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                @endif

                @if ($socials = $settings->socialLinks())
                    <div class="mt-8 flex flex-wrap gap-2">
                        @foreach ($socials as $social)
                            <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer"
                               aria-label="{{ $social['label'] }}"
                               class="inline-flex size-10 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-ink/70 transition hover:border-accent/40 hover:bg-accent/10 hover:text-ink">
                                <x-site.icon :name="$social['icon']" :size="18" />
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <x-react name="ContactForm" :props="[
                'submitUrl' => route('contact.store'),
                'errors' => $errors->toArray(),
                'old' => [
                    'name' => old('name'),
                    'email' => old('email'),
                    'phone' => old('phone'),
                    'company' => old('company'),
                    'subject' => old('subject'),
                    'message' => old('message'),
                ],
            ]" />
        </div>
    </section>
@endsection
