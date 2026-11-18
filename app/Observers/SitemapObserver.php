<?php

namespace App\Observers;

use App\Services\Seo\SitemapBuilder;
use Illuminate\Database\Eloquent\Model;

/**
 * Public URLs and lastmod dates change whenever content changes, so the
 * cached sitemap is invalidated on save/delete for all public entities.
 */
class SitemapObserver
{
    public function __construct(private readonly SitemapBuilder $sitemap) {}

    public function saved(Model $model): void
    {
        $this->sitemap->flush();
    }

    public function deleted(Model $model): void
    {
        $this->sitemap->flush();
    }
}
