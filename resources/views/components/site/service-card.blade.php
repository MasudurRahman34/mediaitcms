@props(['service'])

@php
    $icon = $service->icon ?: 'code';
@endphp

<article class="group glass-card glass-card-hover flex h-full flex-col p-7">
    <span class="icon-chip">
        <x-site.icon :name="$icon" :size="22" />
    </span>

    <h3 class="mt-6 text-lg font-semibold text-ink">
        <a href="{{ $service->url() }}" class="after:absolute after:inset-0 relative">{{ $service->title }}</a>
    </h3>

    <p class="mt-3 flex-1 text-sm leading-relaxed text-ink/60">
        {{ $service->excerpt }}
    </p>

    <span class="mt-6 inline-flex items-center gap-1.5 text-sm font-medium text-accent-soft transition group-hover:gap-2.5 group-hover:text-ink">
        Explore service
        <x-site.icon name="arrow-right" :size="16" />
    </span>
</article>
