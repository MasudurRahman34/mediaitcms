@props(['href' => '#', 'variant' => 'primary', 'size' => 'md', 'icon' => null, 'external' => false])

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-full font-semibold transition duration-300';
    $sizes = [
        'sm' => 'px-4 py-2 text-sm',
        'md' => 'px-6 py-3 text-sm',
        'lg' => 'px-7 py-3.5 text-base',
    ];
    $variants = [
        'primary' => 'bg-accent text-white shadow-lg shadow-accent/25 hover:bg-accent-deep hover:shadow-accent/40',
        'secondary' => 'border border-white/15 bg-white/5 text-ink hover:border-white/30 hover:bg-white/10',
        'ghost' => 'text-ink/75 hover:text-ink',
    ];
@endphp

<a href="{{ $href }}"
   @if ($external) target="_blank" rel="noopener noreferrer" @endif
   {{ $attributes->merge(['class' => trim($base.' '.($sizes[$size] ?? $sizes['md']).' '.($variants[$variant] ?? $variants['primary']))]) }}>
    {{ $slot }}
    @if ($icon)
        <x-site.icon :name="$icon" :size="16" />
    @endif
</a>
