@php
    $companyName = $settings->get('company_name', config('app.name'));
    $footerCompany = $navigation->for('footer_company');
    $footerServices = $navigation->for('footer_services');
    $footerLegal = $navigation->for('footer_legal');
    $socials = $settings->socialLinks();
@endphp

<footer class="relative overflow-hidden border-t border-white/5 bg-surface-900">
    <div class="pointer-events-none absolute inset-x-0 -top-24 h-48 bg-[radial-gradient(ellipse_at_center,rgba(59,130,246,0.16),transparent_65%)]"></div>

    <div class="container-page relative py-16">
        <div class="grid gap-12 lg:grid-cols-[1.4fr_1fr_1fr_1fr]">
            <div>
                <a href="{{ route('home') }}" class="inline-flex" aria-label="{{ $companyName }} home">
                    <x-site.logo />
                </a>
                <p class="mt-5 max-w-xs text-sm leading-relaxed text-ink/60">
                    {{ $settings->get('footer_text', 'Software, AI and IT services that move your business forward.') }}
                </p>

                <div class="mt-6 space-y-3 text-sm text-ink/65">
                    @if ($phone = $settings->get('contact_phone'))
                        <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="flex items-center gap-3 transition hover:text-ink">
                            <x-site.icon name="phone" :size="16" class="text-accent-soft" />
                            {{ $phone }}
                        </a>
                    @endif
                    @if ($email = $settings->get('contact_email'))
                        <a href="mailto:{{ $email }}" class="flex items-center gap-3 transition hover:text-ink">
                            <x-site.icon name="mail" :size="16" class="text-accent-soft" />
                            {{ $email }}
                        </a>
                    @endif
                    @if ($address = $settings->get('contact_address'))
                        <p class="flex items-start gap-3">
                            <x-site.icon name="map-pin" :size="16" class="mt-0.5 text-accent-soft" />
                            <span>{{ $address }}</span>
                        </p>
                    @endif
                </div>
            </div>

            <div>
                <h3 class="text-sm font-semibold tracking-wide text-ink/90 uppercase">Company</h3>
                <ul class="mt-4 space-y-3 text-sm">
                    @foreach ($footerCompany as $item)
                        <li>
                            <a href="{{ $item->resolvedUrl() }}" class="text-ink/60 transition hover:text-ink">{{ $item->label }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-semibold tracking-wide text-ink/90 uppercase">Services</h3>
                <ul class="mt-4 space-y-3 text-sm">
                    @foreach ($footerServices as $item)
                        <li>
                            <a href="{{ $item->resolvedUrl() }}" class="text-ink/60 transition hover:text-ink">{{ $item->label }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-semibold tracking-wide text-ink/90 uppercase">Legal</h3>
                <ul class="mt-4 space-y-3 text-sm">
                    @foreach ($footerLegal as $item)
                        <li>
                            <a href="{{ $item->resolvedUrl() }}" class="text-ink/60 transition hover:text-ink">{{ $item->label }}</a>
                        </li>
                    @endforeach
                </ul>

                @if ($socials)
                    <div class="mt-6 flex flex-wrap gap-2">
                        @foreach ($socials as $social)
                            <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer"
                               aria-label="{{ $social['label'] }}"
                               class="inline-flex size-9 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-ink/70 transition hover:border-accent/40 hover:bg-accent/10 hover:text-ink">
                                <x-site.icon :name="$social['icon']" :size="16" />
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-14 flex flex-col gap-4 border-t border-white/5 pt-8 text-xs text-ink/45 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ now()->year }} {{ $companyName }}. All rights reserved.</p>
            <p>
                {{ $settings->get('contact_address', 'Swindon, United Kingdom') }}
            </p>
        </div>
    </div>
</footer>
