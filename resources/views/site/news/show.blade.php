@extends('site.layouts.app')

@section('content')
    <article>
        <header class="relative overflow-hidden border-b border-white/5">
            <div class="pointer-events-none absolute inset-0 grid-pattern opacity-30"></div>
            <div class="pointer-events-none absolute -top-32 right-1/4 size-[26rem] rounded-full bg-accent/10 blur-[100px]"></div>

            <div class="container-page relative py-14 sm:py-20">
                <nav aria-label="Breadcrumb" class="mb-6">
                    <ol class="flex flex-wrap items-center gap-2 text-xs text-ink/45">
                        <li><a href="{{ route('home') }}" class="hover:text-ink">Home</a></li>
                        <li aria-hidden="true">/</li>
                        <li><a href="{{ route('news.index') }}" class="hover:text-ink">News</a></li>
                        <li aria-hidden="true">/</li>
                        <li aria-current="page" class="text-ink/70">{{ \Illuminate\Support\Str::limit($article->title, 40) }}</li>
                    </ol>
                </nav>

                <div class="max-w-3xl">
                    <div class="flex flex-wrap items-center gap-2 text-xs text-ink/50">
                        @foreach ($article->categories as $category)
                            <a href="{{ $category->url() }}" class="rounded-full border border-accent/25 bg-accent/10 px-2.5 py-1 font-medium text-accent-soft">
                                {{ $category->name }}
                            </a>
                        @endforeach
                        <time datetime="{{ $article->published_at?->toAtomString() }}">
                            {{ $article->published_at?->format('j F Y') }}
                        </time>
                        <span aria-hidden="true">·</span>
                        <span>{{ $article->readingTime() }} min read</span>
                    </div>

                    <h1 class="heading-xl mt-5">{{ $article->title }}</h1>

                    @if ($article->excerpt)
                        <p class="body-lg mt-5">{{ $article->excerpt }}</p>
                    @endif

                    @if ($article->author)
                        <div class="mt-8 flex items-center gap-3 text-sm text-ink/60">
                            <span class="grid size-10 place-items-center rounded-full bg-accent/15 text-xs font-semibold text-accent-soft">
                                {{ $article->author->initials() }}
                            </span>
                            <span>By {{ $article->author->name }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </header>

        @if ($article->featuredMedia)
            <div class="container-page -mt-2 pt-10">
                <div class="glass-card overflow-hidden p-2">
                    <x-site.media-image :media="$article->featuredMedia" :alt="$article->title"
                                        :eager="true"
                                        sizes="(min-width: 1024px) 72rem, 100vw"
                                        class="aspect-[16/9] w-full rounded-2xl object-cover" />
                </div>
            </div>
        @endif

        <div class="container-page py-14">
            <div class="grid gap-14 lg:grid-cols-[1fr_18rem]">
                <div class="rich-text max-w-3xl text-base">
                    @richtext($article->body)
                </div>

                <aside class="space-y-8">
                    @if ($article->tags->isNotEmpty())
                        <div>
                            <h2 class="text-sm font-semibold tracking-wide text-ink/90 uppercase">Tags</h2>
                            <div class="mt-4 flex flex-wrap gap-2">
                                @foreach ($article->tags as $tag)
                                    <a href="{{ $tag->url() }}"
                                       class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-ink/60 transition hover:border-white/25 hover:text-ink">
                                        {{ $tag->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="glass-card p-6">
                        <h2 class="text-base font-semibold text-ink">Work with us</h2>
                        <p class="mt-2 text-sm text-ink/60">Have a problem worth solving? Let's talk.</p>
                        <x-site.button :href="route('contact')" class="mt-5 w-full" icon="arrow-right">Contact</x-site.button>
                    </div>
                </aside>
            </div>

            @if ($related->isNotEmpty())
                <div class="mt-20 border-t border-white/10 pt-14">
                    <h2 class="heading-md">Related articles</h2>
                    <div class="mt-8 grid gap-7 md:grid-cols-3">
                        @foreach ($related as $item)
                            <x-site.article-card :article="$item" />
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </article>

    <x-site.cta-fallback />
@endsection
