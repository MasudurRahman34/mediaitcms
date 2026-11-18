@extends('site.layouts.app')

@section('content')
    @foreach ($sections as $section)
        @includeIf('site.sections.'.$section['type'], ['section' => $section])
    @endforeach

    @if ($team->isNotEmpty() && ! collect($sections)->contains('type', 'team_grid'))
        <section class="section">
            <div class="container-page">
                <x-site.section-heading
                    eyebrow="Our people"
                    heading="The team behind the work"
                    intro="Senior engineers, designers and AI specialists who care about the details."
                    align="center" />

                <div class="mt-14 grid gap-7 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($team as $member)
                        <article class="glass-card glass-card-hover overflow-hidden">
                            <div class="aspect-[4/5] overflow-hidden bg-navy-900/50">
                                @if ($member->photo)
                                    <x-site.media-image :media="$member->photo" :alt="$member->name"
                                                        sizes="(min-width: 1024px) 24vw, (min-width: 640px) 48vw, 100vw"
                                                        class="size-full object-cover" />
                                @else
                                    <div class="grid size-full place-items-center bg-brand-gradient">
                                        <span class="text-3xl font-bold text-white/30">{{ mb_substr($member->name, 0, 1) }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="p-6">
                                <h3 class="font-semibold text-ink">{{ $member->name }}</h3>
                                <p class="mt-1 text-sm text-accent-soft">{{ $member->role_title }}</p>
                                @if ($member->bio)
                                    <p class="mt-3 line-clamp-3 text-sm leading-relaxed text-ink/55">{{ $member->bio }}</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <x-site.cta-fallback />
@endsection
