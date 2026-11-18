@props([])

@php
    $heading = $settings->get('cta_heading', 'Ready to build something exceptional?');
    $body = $settings->get('cta_body');
    $label = $settings->get('cta_button_label', 'Start a conversation');
    $url = $settings->get('cta_button_url', '/contact');
@endphp

@if ($heading)
    <section class="bg-brand-gradient relative overflow-hidden border-t border-white/5">
        <div class="pointer-events-none absolute inset-0 grid-pattern opacity-25"></div>
        <div class="pointer-events-none absolute -top-24 left-1/2 size-[26rem] -translate-x-1/2 rounded-full bg-accent/15 blur-[90px]"></div>

        <div class="container-page relative py-16 sm:py-20">
            <div class="mx-auto max-w-3xl text-center">
                <h2 class="heading-lg">{{ $heading }}</h2>
                @if ($body)
                    <p class="body-lg mt-4">{{ $body }}</p>
                @endif
                <div class="mt-8 flex justify-center">
                    <x-site.button :href="str_starts_with((string) $url, 'http') ? $url : url($url)" size="lg" icon="arrow-right">
                        {{ $label }}
                    </x-site.button>
                </div>
            </div>
        </div>
    </section>
@endif
