@props(['solution'])

<article class="group relative overflow-hidden rounded-3xl border border-white/10 bg-surface-800/60 transition duration-500 hover:-translate-y-1 hover:border-accent/30 hover:shadow-2xl hover:shadow-accent/10">
    <div class="relative aspect-[16/10] overflow-hidden bg-navy-900/40">
        @if ($solution->cover)
            <x-site.media-image :media="$solution->cover"
                                :alt="$solution->title.' — project preview'"
                                class="size-full object-cover transition duration-700 group-hover:scale-[1.04]" />
        @else
            <div class="grid-pattern flex size-full items-center justify-center bg-brand-gradient">
                <span class="text-3xl font-extrabold tracking-tight text-white/20">{{ $solution->title }}</span>
            </div>
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-near-black via-near-black/20 to-transparent"></div>
    </div>

    <div class="p-7">
        <h3 class="text-xl font-semibold text-ink">
            <a href="{{ $solution->url() }}" class="after:absolute after:inset-0">{{ $solution->title }}</a>
        </h3>
        <p class="mt-3 line-clamp-3 text-sm leading-relaxed text-ink/60">{{ $solution->intro }}</p>

        <span class="mt-6 inline-flex items-center gap-1.5 text-sm font-medium text-accent-soft transition group-hover:gap-2.5 group-hover:text-ink">
            View case study
            <x-site.icon name="arrow-right" :size="16" />
        </span>
    </div>
</article>
