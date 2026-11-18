@php
    $content = $section['content'] ?? [];
    $image = \App\Support\MediaLookup::find($content['image'] ?? null);
    $position = $content['image_position'] ?? 'right';
@endphp

<section class="section">
    <div class="container-page">
        <div class="{{ $image ? 'grid items-start gap-12 lg:grid-cols-2' : 'mx-auto max-w-3xl' }}">
            <div class="{{ $image && $position === 'left' ? 'lg:order-2' : '' }}">
                <x-site.section-heading
                    :eyebrow="$content['eyebrow'] ?? null"
                    :heading="$content['heading'] ?? null" />

                @if (! empty($content['body']))
                    <div class="rich-text {{ ! empty($content['heading']) ? 'mt-6' : '' }}">
                        @richtext($content['body'])
                    </div>
                @endif
            </div>

            @if ($image)
                <div class="glass-card overflow-hidden p-2 {{ $position === 'left' ? 'lg:order-1' : '' }}">
                    <x-site.media-image :media="$image"
                                        :sizes="'(min-width: 1024px) 46vw, 100vw'"
                                        class="aspect-[4/3] w-full rounded-2xl object-cover" />
                </div>
            @endif
        </div>
    </div>
</section>
