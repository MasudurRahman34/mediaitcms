@php
    $headerItems = $navigation->for('header');
    $companyName = $settings->get('company_name', config('app.name'));
@endphp

<header class="sticky top-0 z-50 border-b border-white/5 bg-near-black/70 backdrop-blur-xl">
    <div class="container-page flex h-20 items-center justify-between gap-6">
        <a href="{{ route('home') }}" class="group flex items-center gap-3" aria-label="{{ $companyName }} home">
            <x-site.logo />
        </a>

        <nav class="hidden items-center gap-1 lg:flex" aria-label="Main navigation">
            @forelse ($headerItems as $item)
                @php
                    $isActive = $item->page_id && ($item->page?->slug === 'home'
                        ? request()->routeIs('home')
                        : request()->url() === $item->resolvedUrl());
                @endphp

                @if ($item->children->isNotEmpty())
                    <div class="group relative">
                        <button type="button"
                                class="nav-link inline-flex items-center gap-1 {{ $isActive ? 'nav-link-active' : '' }}">
                            {{ $item->label }}
                            <x-site.icon name="chevron-down" :size="14" />
                        </button>
                        <div class="invisible absolute left-0 top-full w-60 translate-y-1 pt-3 opacity-0 transition group-hover:visible group-hover:translate-y-0 group-hover:opacity-100 group-focus-within:visible group-focus-within:translate-y-0 group-focus-within:opacity-100">
                            <div class="glass-card overflow-hidden p-2">
                                @foreach ($item->children as $child)
                                    <a href="{{ $child->resolvedUrl() }}"
                                       @if ($child->isExternal()) target="_blank" rel="noopener" @endif
                                       class="block rounded-xl px-4 py-2.5 text-sm text-ink/75 transition hover:bg-white/10 hover:text-ink">
                                        {{ $child->label }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ $item->resolvedUrl() }}"
                       @if ($item->isExternal()) target="_blank" rel="noopener" @endif
                       @if ($isActive) aria-current="page" @endif
                       class="nav-link {{ $isActive ? 'nav-link-active' : '' }}">
                        {{ $item->label }}
                    </a>
                @endif
            @empty
                <a href="{{ route('services.index') }}" class="nav-link">Services</a>
                <a href="{{ route('solutions.index') }}" class="nav-link">Our Work</a>
                <a href="{{ route('news.index') }}" class="nav-link">News</a>
                <a href="{{ route('careers.index') }}" class="nav-link">Careers</a>
                <a href="{{ route('contact') }}" class="nav-link">Contact</a>
            @endforelse
        </nav>

        <div class="flex items-center gap-3">
            <a href="{{ route('contact') }}"
               class="hidden items-center gap-2 rounded-full bg-accent px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-accent/25 transition hover:bg-accent-deep sm:inline-flex">
                Get in touch
                <x-site.icon name="arrow-right" :size="16" />
            </a>

            <x-react name="MobileNav" :props="[
                'items' => collect($headerItems)->map(fn ($item) => [
                    'label' => $item->label,
                    'url' => $item->resolvedUrl(),
                    'external' => $item->isExternal(),
                    'children' => $item->children->map(fn ($child) => [
                        'label' => $child->label,
                        'url' => $child->resolvedUrl(),
                        'external' => $child->isExternal(),
                    ])->values(),
                ])->values(),
                'ctaUrl' => route('contact'),
            ]" />
        </div>
    </div>
</header>
