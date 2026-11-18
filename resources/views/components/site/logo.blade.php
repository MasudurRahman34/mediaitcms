@props(['size' => 'md', 'variant' => 'light'])

@php
    $logoUrl = $settings->imageUrl('logo');
    $markSize = $size === 'sm' ? 'size-8' : ($size === 'lg' ? 'size-12' : 'size-9');
    $wordSize = $size === 'lg' ? 'text-2xl' : 'text-xl';
@endphp

@if ($logoUrl)
    <img src="{{ $logoUrl }}" alt="{{ $settings->get('company_name', config('app.name')) }}"
         class="{{ $size === 'lg' ? 'h-12' : 'h-9' }} w-auto" loading="eager" decoding="async">
@else
    <span class="inline-flex items-center gap-2.5">
        <svg class="{{ $markSize }} {{ $variant === 'navy' ? 'text-navy-800' : 'text-ink' }}" viewBox="0 0 56 56" fill="none" aria-hidden="true">
            <path d="M7 50V21.5a10.5 10.5 0 0 1 21 0V50M28 50V21.5a10.5 10.5 0 0 1 21 0V50"
                  stroke="currentColor" stroke-width="9" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span class="flex flex-col leading-none">
            <span class="font-extrabold tracking-tight {{ $wordSize }} {{ $variant === 'navy' ? 'text-navy-800' : 'text-ink' }}">
                MEDIA
            </span>
            <span class="mt-0.5 text-[0.6rem] font-semibold tracking-[0.45em] {{ $variant === 'navy' ? 'text-navy-600' : 'text-accent-soft' }}">
                IT&nbsp;SOFT
            </span>
        </span>
    </span>
@endif
