@extends('site.layouts.app')

@section('content')
    <section class="relative overflow-hidden">
        <div class="pointer-events-none absolute inset-0 grid-pattern opacity-30"></div>

        <div class="container-page relative py-24 sm:py-32">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-[6rem] leading-none font-extrabold tracking-tight text-white/10 sm:text-[9rem]">
                    {{ $code ?? 500 }}
                </p>

                <h1 class="heading-lg mt-2">
                    {{ ($code ?? 500) === 503 ? 'We will be right back' : 'Something went wrong' }}
                </h1>

                <p class="body-lg mt-5">
                    @if (($code ?? 500) === 503)
                        The site is briefly unavailable for maintenance. Please try again in a moment.
                    @else
                        An unexpected error occurred. We have been notified and are looking into it.
                    @endif
                </p>

                <div class="mt-9 flex flex-wrap items-center justify-center gap-4">
                    <x-site.button :href="route('home')" icon="arrow-right">Back to home</x-site.button>
                    <x-site.button :href="route('contact')" variant="secondary">Contact us</x-site.button>
                </div>
            </div>
        </div>
    </section>
@endsection
