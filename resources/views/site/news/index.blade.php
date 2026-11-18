@extends('site.layouts.app')

@section('content')
    <x-site.page-hero
        :eyebrow="$activeCategory?->name ?? ($activeTag ? 'Tagged' : null)"
        :title="$activeCategory?->name ?? ($activeTag ? '#'.$activeTag->name : 'News & insights')"
        :description="$activeCategory?->description ?? 'Practical thinking on software engineering, AI and digital delivery.'"
        :breadcrumbs="[['label' => 'Home', 'url' => route('home')], ['label' => 'News']]">
        <form action="{{ route('news.index') }}" method="GET" class="mt-8 flex max-w-xl gap-3" role="search">
            <label for="article-search" class="sr-only">Search articles</label>
            <div class="relative flex-1">
                <span class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/2 text-ink/40">
                    <x-site.icon name="search" :size="18" />
                </span>
                <input id="article-search" type="search" name="q" value="{{ $search }}"
                       placeholder="Search articles…"
                       class="w-full rounded-full border border-white/10 bg-white/5 py-3 pr-4 pl-11 text-sm text-ink placeholder:text-ink/40 focus:border-accent/60 focus:outline-none">
            </div>
            <button type="submit"
                    class="rounded-full bg-accent px-5 py-3 text-sm font-semibold text-white transition hover:bg-accent-deep">
                Search
            </button>
        </form>
    </x-site.page-hero>

    <section class="section">
        <div class="container-page grid gap-12 lg:grid-cols-[1fr_16rem]">
            <div>
                @if ($articles->isEmpty())
                    <div class="glass-card p-10 text-center">
                        <p class="text-ink/70">No articles match your search yet.</p>
                        <x-site.button :href="route('news.index')" variant="secondary" class="mt-6">Clear filters</x-site.button>
                    </div>
                @else
                    <div class="grid gap-7 sm:grid-cols-2">
                        @foreach ($articles as $article)
                            <x-site.article-card :article="$article" :eager="$loop->first" />
                        @endforeach
                    </div>

                    <div class="mt-12">
                        {{ $articles->links() }}
                    </div>
                @endif
            </div>

            <aside class="space-y-8">
                <div>
                    <h2 class="text-sm font-semibold tracking-wide text-ink/90 uppercase">Categories</h2>
                    <ul class="mt-4 space-y-2.5">
                        <li>
                            <a href="{{ route('news.index') }}"
                               class="flex items-center justify-between text-sm transition {{ ! $activeCategory && ! $activeTag ? 'text-ink' : 'text-ink/55 hover:text-ink' }}">
                                All articles
                                <span class="text-xs text-ink/35">{{ $articles->total() }}</span>
                            </a>
                        </li>
                        @foreach ($categories as $category)
                            <li>
                                <a href="{{ $category->url() }}"
                                   class="flex items-center justify-between text-sm transition {{ $activeCategory?->is($category) ? 'text-accent-soft' : 'text-ink/55 hover:text-ink' }}">
                                    {{ $category->name }}
                                    <span class="text-xs text-ink/35">{{ $category->articles_count }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                @if ($tags->isNotEmpty())
                    <div>
                        <h2 class="text-sm font-semibold tracking-wide text-ink/90 uppercase">Tags</h2>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ($tags as $tag)
                                <a href="{{ $tag->url() }}"
                                   class="rounded-full border px-3 py-1.5 text-xs transition {{ $activeTag?->is($tag) ? 'border-accent/50 bg-accent/15 text-accent-soft' : 'border-white/10 bg-white/5 text-ink/60 hover:border-white/25 hover:text-ink' }}">
                                    {{ $tag->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="glass-card p-5">
                    <h2 class="text-sm font-semibold text-ink">Newsletter</h2>
                    <p class="mt-2 text-xs leading-relaxed text-ink/55">
                        Occasional notes on engineering and AI. No noise.
                    </p>
                    <x-site.button :href="route('contact')" variant="secondary" size="sm" class="mt-4 w-full">
                        Get in touch
                    </x-site.button>
                </div>
            </aside>
        </div>
    </section>
@endsection
