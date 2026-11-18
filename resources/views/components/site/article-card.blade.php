@props(['article', 'eager' => false])

<article class="group flex h-full flex-col overflow-hidden rounded-3xl border border-white/10 bg-surface-800/50 transition duration-300 hover:-translate-y-1 hover:border-white/20 hover:bg-surface-800">
    <a href="{{ $article->url() }}" class="relative block aspect-[16/10] overflow-hidden bg-navy-900/40" tabindex="-1" aria-hidden="true">
        @if ($article->featuredMedia)
            <x-site.media-image :media="$article->featuredMedia" :eager="$eager"
                                class="size-full object-cover transition duration-700 group-hover:scale-[1.04]" />
        @else
            <div class="grid-pattern flex size-full items-center justify-center bg-brand-gradient">
                <x-site.icon name="newspaper" :size="36" class="text-white/20" />
            </div>
        @endif
    </a>

    <div class="flex flex-1 flex-col p-6">
        <div class="flex flex-wrap items-center gap-2 text-xs text-ink/45">
            @if ($category = $article->categories->first())
                <span class="rounded-full border border-accent/25 bg-accent/10 px-2.5 py-1 font-medium text-accent-soft">
                    {{ $category->name }}
                </span>
            @endif
            <time datetime="{{ $article->published_at?->toAtomString() }}">
                {{ $article->published_at?->format('j M Y') }}
            </time>
            <span aria-hidden="true">·</span>
            <span>{{ $article->readingTime() }} min read</span>
        </div>

        <h3 class="mt-4 text-lg leading-snug font-semibold text-ink">
            <a href="{{ $article->url() }}" class="transition group-hover:text-accent-soft">{{ $article->title }}</a>
        </h3>

        <p class="mt-3 line-clamp-3 flex-1 text-sm leading-relaxed text-ink/60">{{ $article->excerpt }}</p>

        <span class="mt-5 inline-flex items-center gap-1.5 text-sm font-medium text-accent-soft">
            Read article
            <x-site.icon name="arrow-right" :size="16" />
        </span>
    </div>
</article>
