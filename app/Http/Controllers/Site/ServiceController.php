<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\Seo\SeoManager;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(SeoManager $seo): View
    {
        $services = Service::query()->published()->ordered()->get();

        return view('site.services.index', [
            'services' => $services,
            'seo' => $seo->simple(
                'Services',
                'Custom software development, AI automation, cloud engineering and IT support from Media IT Soft.',
            ),
        ]);
    }

    public function show(Service $service, SeoManager $seo): View
    {
        abort_unless($service->status->value === 'published', 404);

        $related = Service::query()
            ->published()
            ->whereKeyNot($service->id)
            ->ordered()
            ->limit(3)
            ->get();

        return view('site.services.show', [
            'service' => $service->loadMissing('cover'),
            'related' => $related,
            'seo' => $seo->service($service),
            'faqJsonLd' => $service->faqs ? $seo->faqPage($service->faqs) : null,
        ]);
    }
}
