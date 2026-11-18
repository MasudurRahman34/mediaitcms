<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\ArticleTag;
use App\Services\Seo\SeoManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request, SeoManager $seo): View
    {
        $query = Article::query()
            ->published()
            ->with(['featuredMedia', 'categories', 'author:id,name']);

        $this->applySearch($query, $request);

        $articles = $query->latestPublished()->paginate(9)->withQueryString();

        return view('site.news.index', [
            'articles' => $articles,
            'categories' => ArticleCategory::query()->withCount(['articles' => fn ($q) => $q->published()])->orderBy('name')->get(),
            'tags' => ArticleTag::query()->withCount('articles')->orderBy('name')->limit(20)->get(),
            'activeCategory' => null,
            'activeTag' => null,
            'search' => $request->string('q')->toString(),
            'seo' => $seo->simple(
                'News & Insights',
                'Articles on software engineering, AI, cloud and digital product delivery from Media IT Soft.',
                'website',
            ),
        ]);
    }

    public function category(Request $request, ArticleCategory $category, SeoManager $seo): View
    {
        $query = Article::query()
            ->published()
            ->whereHas('categories', fn ($q) => $q->where('article_categories.id', $category->id))
            ->with(['featuredMedia', 'categories', 'author:id,name']);

        $this->applySearch($query, $request);

        return view('site.news.index', [
            'articles' => $query->latestPublished()->paginate(9)->withQueryString(),
            'categories' => ArticleCategory::query()->withCount(['articles' => fn ($q) => $q->published()])->orderBy('name')->get(),
            'tags' => ArticleTag::query()->withCount('articles')->orderBy('name')->limit(20)->get(),
            'activeCategory' => $category,
            'activeTag' => null,
            'search' => $request->string('q')->toString(),
            'seo' => $seo->simple($category->name.' — News', $category->description),
        ]);
    }

    public function tag(Request $request, ArticleTag $tag, SeoManager $seo): View
    {
        $query = Article::query()
            ->published()
            ->whereHas('tags', fn ($q) => $q->where('article_tags.id', $tag->id))
            ->with(['featuredMedia', 'categories', 'author:id,name']);

        $this->applySearch($query, $request);

        return view('site.news.index', [
            'articles' => $query->latestPublished()->paginate(9)->withQueryString(),
            'categories' => ArticleCategory::query()->withCount(['articles' => fn ($q) => $q->published()])->orderBy('name')->get(),
            'tags' => ArticleTag::query()->withCount('articles')->orderBy('name')->limit(20)->get(),
            'activeCategory' => null,
            'activeTag' => $tag,
            'search' => $request->string('q')->toString(),
            'seo' => $seo->simple('Articles tagged '.$tag->name),
        ]);
    }

    public function show(Article $article, SeoManager $seo): View
    {
        abort_unless(
            $article->status->value === 'published'
            && ($article->published_at === null || $article->published_at->isPast()),
            404,
        );

        $article->loadMissing(['featuredMedia', 'categories', 'tags', 'author:id,name']);

        // Lightweight, non-blocking view counter.
        Article::query()->whereKey($article->id)->increment('views');

        return view('site.news.show', [
            'article' => $article,
            'related' => $article->related(3),
            'seo' => $seo->article($article),
        ]);
    }

    private function applySearch(Builder $query, Request $request): void
    {
        $search = trim($request->string('q')->toString());

        if ($search === '') {
            return;
        }

        $query->where(function (Builder $q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('excerpt', 'like', "%{$search}%")
                ->orWhere('body', 'like', "%{$search}%");
        });
    }
}
