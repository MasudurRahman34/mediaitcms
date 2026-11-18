@props(['media' => null, 'alt' => null, 'class' => '', 'sizes' => '(min-width: 1024px) 50vw, 100vw', 'eager' => false])

@if ($media)
    <img src="{{ $media->variantUrl('800') }}"
         @if ($media->srcset()) srcset="{{ $media->srcset() }}" sizes="{{ $sizes }}" @endif
         alt="{{ $alt ?? $media->altText() }}"
         width="{{ $media->width }}" height="{{ $media->height }}"
         loading="{{ $eager ? 'eager' : 'lazy' }}"
         decoding="async"
         {{ $attributes->merge(['class' => $class]) }}>
@endif
