@php
    $content = $section['content'] ?? [];
    $showForm = (bool) ($content['show_form'] ?? false);
    $details = $settings->contactDetails();
@endphp

<section class="section">
    <div class="container-page">
        <div class="grid gap-12 {{ $showForm ? 'lg:grid-cols-2' : '' }}">
            <div>
                <x-site.section-heading
                    :heading="$content['heading'] ?? 'Talk to us'"
                    :intro="$content['intro'] ?? null" />

                <div class="mt-8 space-y-4">
                    @foreach ($details as $detail)
                        @if ($detail['value'])
                            <div class="glass-card flex items-start gap-4 p-5">
                                <span class="icon-chip size-10 rounded-xl">
                                    <x-site.icon :name="match ($detail['label']) { 'Phone' => 'phone', 'Email' => 'mail', default => 'map-pin' }" :size="18" />
                                </span>
                                <div>
                                    <p class="text-xs font-medium tracking-wide text-ink/45 uppercase">{{ $detail['label'] }}</p>
                                    @if ($detail['href'])
                                        <a href="{{ $detail['href'] }}" class="mt-1 block text-sm font-medium text-ink transition hover:text-accent-soft">{{ $detail['value'] }}</a>
                                    @else
                                        <p class="mt-1 text-sm text-ink/75">{{ $detail['value'] }}</p>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                @if ($hours = $settings->get('contact_hours'))
                    <p class="mt-6 flex items-center gap-2 text-sm text-ink/50">
                        <x-site.icon name="clock" :size="16" />
                        {{ $hours }}
                    </p>
                @endif
            </div>

            @if ($showForm)
                <x-react name="ContactForm" :props="['submitUrl' => route('contact.store')]" />
            @endif
        </div>
    </div>
</section>
