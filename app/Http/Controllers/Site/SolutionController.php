<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Solution;
use App\Services\Seo\SeoManager;
use Illuminate\View\View;

class SolutionController extends Controller
{
    public function index(SeoManager $seo): View
    {
        $solutions = Solution::query()
            ->published()
            ->ordered()
            ->with(['cover'])
            ->withCount('screenshots')
            ->get();

        return view('site.solutions.index', [
            'solutions' => $solutions,
            'seo' => $seo->simple(
                'Our Work',
                'Software platforms, AI assistants and digital products built by Media IT Soft.',
            ),
        ]);
    }

    public function show(Solution $solution, SeoManager $seo): View
    {
        abort_unless($solution->status->value === 'published', 404);

        $solution->load(['cover', 'features.media', 'screenshots.media', 'technologies']);

        $others = Solution::query()
            ->published()
            ->whereKeyNot($solution->id)
            ->ordered()
            ->with('cover')
            ->limit(3)
            ->get();

        return view('site.solutions.show', [
            'solution' => $solution,
            'others' => $others,
            'seo' => $seo->solution($solution),
        ]);
    }
}
