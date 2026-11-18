@props(['title' => null, 'description' => null, 'breadcrumbs' => []])

<section class="relative overflow-hidden border-b border-white/5">
    <div class="pointer-events-none absolute inset-0 grid-pattern opacity-40"></div>
    <div class="pointer-events-none absolute -top-32 right-0 size-[28rem] rounded-full bg-accent/10 blur-3xl"></div>

    <div class="container-page relative py-16 sm:py-20">
        @if ($breadcrumbs)
            <nav aria-label="Breadcrumb" class="mb-6">
                <ol class="flex flex-wrap items-center gap-2 text-xs text-ink/45">
                    @foreach ($breadcrumbs as $crumb)
                        <li class="flex items-center gap-2">
                            @if (! $loop->first)
                                <span aria-hidden="true">/</span>
                            @endif
                            @if (! empty($crumb['url']) && ! $loop->last)
                                <a href="{{ $crumb['url'] }}" class="transition hover:text-ink">{{ $crumb['label'] }}</a>
                            @else
                                <span @if ($loop->last) aria-current="page" class="text-ink/70" @endif>{{ $crumb['label'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        @endif

        @if ($title)
            <h1 class="heading-xl max-w-4xl">{{ $title }}</h1>
        @endif

        @if ($description)
            <p class="body-lg mt-5 max-w-2xl">{{ $description }}</p>
        @endif

        {{ $slot }}
    </div>
</section>
