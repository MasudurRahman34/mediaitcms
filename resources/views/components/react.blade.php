@props([
    'name',
    'props' => [],
    'fallback' => null,
    'tag' => 'div',
    'class' => '',
])

<{{ $tag }}
    data-react="{{ $name }}"
    data-props="{{ json_encode($props, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) }}"
    class="{{ $class }}"
>
    @if ($fallback)
        {{ $fallback }}
    @endif
</{{ $tag }}>
