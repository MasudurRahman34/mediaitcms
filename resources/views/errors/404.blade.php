@extends('site.layouts.app')

@section('content')
    <section class="relative overflow-hidden">
        <div class="pointer-events-none absolute inset-0 grid-pattern opacity-30"></div>
        <div class="pointer-events-none absolute -top-32 left-1/3 size-[28rem] rounded-full bg-accent/10 blur-[100px]"></div>

        <div class="container-page relative py-24 sm:py-32">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-[6rem] leading-none font-extrabold tracking-tight text-white/10 sm:text-[9rem]">
                    {{ $code ?? 404 }}
                </p>

                <h1 class="heading-lg mt-2">This page cannot be found</h1>

                <p class="body-lg mt-5">
                    The page may have moved, or the link may be out of date. Try one of these instead.
                </p>

                <div class="mt-9 flex flex-wrap items-center justify-center gap-4">
                    <x-site.button :href="route('home')" icon="arrow-right">Back to home</x-site.button>
                    <x-site.button :href="route('services.index')" variant="secondary">Browse services</x-site.button>
                </div>

                <div class="mt-12 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-sm text-ink/50">
                    <a href="{{ route('solutions.index') }}" class="transition hover:text-ink">Our work</a>
                    <a href="{{ route('news.index') }}" class="transition hover:text-ink">News</a>
                    <a href="{{ route('careers.index') }}" class="transition hover:text-ink">Careers</a>
                    <a href="{{ route('contact') }}" class="transition hover:text-ink">Contact</a>
                </div>
            </div>
        </div>
    </section>
@endsection
