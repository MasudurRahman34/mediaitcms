@php
    $content = $section['content'] ?? [];
    $limit = (int) ($content['limit'] ?? 3);
    $source = $content['source'] ?? 'latest';

    $articles = match ($source) {
        'home_featured' => \App\Models\Article::query()->forPlacement(\App\Enums\PlacementLocation::HomeFeatured),
        'home_latest' => \App\Models\Article::query()->forPlacement(\App\Enums\PlacementLocation::HomeLatest),
        default => \App\Models\Article::query()->latestPublished(),
    };

    $articles = $articles->with(['featuredMedia', 'categories', 'tags', 'author'])->limit($limit)->get();

    if ($articles->isEmpty() && $source !== 'latest') {
        $articles = \App\Models\Article::query()->latestPublished()->with(['featuredMedia', 'categories'])->limit($limit)->get();
    }
@endphp

@if ($articles->isNotEmpty())
    <section class="section">
        <div class="container-page">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                <x-site.section-heading
                    :eyebrow="$content['eyebrow'] ?? null"
                    :heading="$content['heading'] ?? null"
                    :intro="$content['intro'] ?? null" />

                @if ($content['show_cta'] ?? true)
                    <x-site.button :href="route('news.index')" variant="secondary" icon="arrow-right" class="shrink-0">
                        All articles
                    </x-site.button>
                @endif
            </div>

            <div class="mt-12 grid gap-7 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($articles as $article)
                    <x-site.article-card :article="$article" :eager="$loop->first" />
                @endforeach
            </div>
        </div>
    </section>
@endif
