<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\NavigationRepository;
use App\Services\Pages\PageRenderer;
use App\Services\Seo\SeoData;
use App\Services\SettingsRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Draft preview. Renders the working draft through the same Blade section
 * partials as the live site, restricted to authenticated CMS users.
 */
class PreviewController extends Controller
{
    public function __invoke(Request $request, Page $page, PageRenderer $renderer): View
    {
        abort_unless($request->user()->hasPermission('pages.manage'), 403);

        return view('site.page', [
            'page' => $page,
            'sections' => $renderer->sections($page, preview: true),
            'settings' => app(SettingsRepository::class),
            'navigation' => app(NavigationRepository::class),
            'seo' => new SeoData(
                title: 'Preview — '.$page->title,
                description: null,
                canonical: $page->url(),
                noindex: true,
            ),
            'preview' => true,
        ]);
    }
}
