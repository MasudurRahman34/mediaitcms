<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\TeamMember;
use App\Services\Pages\PageRenderer;
use App\Services\Seo\SeoManager;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(PageRenderer $renderer, SeoManager $seo): View
    {
        $page = Page::query()->where('slug', 'about')->where('status', 'published')->firstOrFail();

        return view('site.about', [
            'page' => $page,
            'sections' => $renderer->sections($page),
            'team' => TeamMember::query()->active()->get(),
            'seo' => $seo->page($page, 'About us'),
        ]);
    }

    public function show(Request $request, PageRenderer $renderer, SeoManager $seo): View
    {
        $slug = (string) $request->route('slug');

        $page = Page::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        return view('site.page', [
            'page' => $page,
            'sections' => $renderer->sections($page),
            'seo' => $seo->page($page, $page->title),
        ]);
    }
}
