<?php

namespace App\Services\Seo;

use App\Enums\JobStatus;
use App\Enums\PageStatus;
use App\Models\Article;
use App\Models\Job;
use App\Models\Page;
use App\Models\Service;
use App\Models\Solution;
use Illuminate\Support\Facades\Cache;

class SitemapBuilder
{
    public const CACHE_KEY = 'sitemap.xml';

    /** @return array<int, array{loc: string, lastmod: string|null, changefreq: string, priority: string}> */
    public function entries(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addHour(), function () {
            $entries = [
                $this->entry(route('home'), now(), 'daily', '1.0'),
                $this->entry(route('about'), null, 'monthly', '0.8'),
                $this->entry(route('services.index'), null, 'weekly', '0.9'),
                $this->entry(route('solutions.index'), null, 'weekly', '0.9'),
                $this->entry(route('news.index'), null, 'daily', '0.8'),
                $this->entry(route('careers.index'), null, 'weekly', '0.7'),
                $this->entry(route('contact'), null, 'monthly', '0.6'),
            ];

            Page::query()
                ->where('status', PageStatus::Published)
                ->whereNotIn('slug', ['home', 'about'])
                ->get()
                ->each(function (Page $page) use (&$entries) {
                    $entries[] = $this->entry($page->url(), $page->updated_at, 'monthly', '0.6');
                });

            Service::query()->published()->get()->each(function (Service $service) use (&$entries) {
                $entries[] = $this->entry($service->url(), $service->updated_at, 'monthly', '0.8');
            });

            Solution::query()->published()->get()->each(function (Solution $solution) use (&$entries) {
                $entries[] = $this->entry($solution->url(), $solution->updated_at, 'monthly', '0.8');
            });

            Article::query()->published()->get()->each(function (Article $article) use (&$entries) {
                $entries[] = $this->entry($article->url(), $article->updated_at, 'monthly', '0.7');
            });

            Job::query()
                ->where('status', JobStatus::Open)
                ->where(fn ($q) => $q->whereNull('closing_at')->orWhere('closing_at', '>=', now()))
                ->get()
                ->each(function (Job $job) use (&$entries) {
                    $entries[] = $this->entry($job->url(), $job->updated_at, 'weekly', '0.6');
                });

            return $entries;
        });
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array{loc: string, lastmod: string|null, changefreq: string, priority: string}
     */
    private function entry(string $loc, mixed $lastmod, string $changefreq, string $priority): array
    {
        return [
            'loc' => $loc,
            'lastmod' => $lastmod?->toAtomString(),
            'changefreq' => $changefreq,
            'priority' => $priority,
        ];
    }
}
