@props(['eyebrow' => null, 'heading' => null, 'intro' => null, 'align' => 'left', 'level' => 2, 'class' => ''])

@php
    $alignment = $align === 'center' ? 'mx-auto max-w-3xl text-center' : 'max-w-2xl';
@endphp

<div class="{{ $class }} {{ $alignment }}">
    @if ($eyebrow)
        <span class="eyebrow">{{ $eyebrow }}</span>
    @endif

    @if ($heading)
        <h{{ $level }} class="{{ $eyebrow ? 'mt-5' : '' }} heading-lg">{{ $heading }}</h{{ $level }}>
    @endif

    @if ($intro)
        <p class="body-lg mt-4">{{ $intro }}</p>
    @endif
</div>
