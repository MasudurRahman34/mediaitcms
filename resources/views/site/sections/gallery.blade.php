@php
    $content = $section['content'] ?? [];
    $items = collect($content['items'] ?? [])
        ->map(function ($item) {
            $media = \App\Support\MediaLookup::find($item['media_id'] ?? null);

            return $media ? [
                'src' => $media->variantUrl('1600'),
                'thumb' => $media->variantUrl('800'),
                'alt' => $item['caption'] ?? $media->altText(),
                'caption' => $item['caption'] ?? null,
            ] : null;
        })
        ->filter()
        ->values();
@endphp

@if ($items->isNotEmpty())
    <section class="section">
        <div class="container-page">
            <x-site.section-heading
                :eyebrow="$content['eyebrow'] ?? null"
                :heading="$content['heading'] ?? null"
                align="center" />

            <div class="mt-12">
                <x-react name="Gallery" :props="['items' => $items]" />
            </div>
        </div>
    </section>
@endif
