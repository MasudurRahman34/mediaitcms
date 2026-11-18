<?php

namespace App\Http\Controllers\Site;

use App\Enums\PlacementLocation;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Page;
use App\Models\Service;
use App\Models\Solution;
use App\Models\TeamMember;
use App\Services\Pages\PageRenderer;
use App\Services\Seo\SeoManager;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(PageRenderer $renderer, SeoManager $seo): View
    {
        $page = Page::query()->where('slug', 'home')->where('status', 'published')->firstOrFail();

        $featuredArticles = Article::query()
            ->forPlacement(PlacementLocation::HomeFeatured)
            ->with(['featuredMedia', 'categories'])
            ->limit(3)
            ->get();

        $latestArticles = Article::query()
            ->forPlacement(PlacementLocation::HomeLatest)
            ->with(['featuredMedia', 'categories'])
            ->limit(3)
            ->get();

        if ($latestArticles->isEmpty()) {
            $latestArticles = Article::query()->latestPublished()->with(['featuredMedia', 'categories'])->limit(3)->get();
        }

        return view('site.home', [
            'page' => $page,
            'sections' => $renderer->sections($page),
            'featuredArticles' => $featuredArticles,
            'latestArticles' => $latestArticles,
            'featuredServices' => Service::query()->published()->where('is_featured', true)->ordered()->limit(6)->get(),
            'featuredSolutions' => Solution::query()->published()->where('is_featured', true)->ordered()->limit(3)->get(),
            'team' => TeamMember::query()->active()->limit(4)->get(),
            'seo' => $seo->page($page, 'Software, AI & IT solutions'),
        ]);
    }
}
