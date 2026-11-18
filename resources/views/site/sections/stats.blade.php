@php
    $content = $section['content'] ?? [];
    $items = $content['items'] ?? [];
@endphp

@if (count($items))
    <section class="section relative overflow-hidden">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top,rgba(59,130,246,0.10),transparent_60%)]"></div>

        <div class="container-page relative">
            @if (! empty($content['heading']) || ! empty($content['intro']))
                <x-site.section-heading
                    :eyebrow="$content['eyebrow'] ?? null"
                    :heading="$content['heading'] ?? null"
                    :intro="$content['intro'] ?? null"
                    align="center" />
            @endif

            <dl class="mt-14 grid grid-cols-2 gap-6 lg:grid-cols-4">
                @foreach ($items as $index => $item)
                    <div class="glass-card p-6 text-center sm:p-8">
                        <dd class="text-4xl font-bold tracking-tight text-ink sm:text-5xl">
                            <x-react name="Counter"
                                     :props="[
                                         'value' => $item['value'] ?? '0',
                                         'suffix' => $item['suffix'] ?? '',
                                     ]"
                                     tag="span"
                                     class="inline-block" />
                        </dd>
                        <dt class="mt-3 text-sm leading-snug text-ink/60">{{ $item['label'] ?? '' }}</dt>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>
@endif
