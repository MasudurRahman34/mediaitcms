<?php

namespace App\Console\Commands;

use App\Models\Page;
use App\Models\PageVersion;
use Illuminate\Console\Command;

class PrunePageVersions extends Command
{
    protected $signature = 'cms:prune-versions {--keep=20 : Versions to retain per page}';

    protected $description = 'Delete old page versions, always keeping the published one';

    public function handle(): int
    {
        $keep = max(1, (int) $this->option('keep'));
        $deleted = 0;

        Page::query()->each(function (Page $page) use ($keep, &$deleted) {
            $stale = $page->versions()
                ->where('id', '!=', $page->published_version_id ?? 0)
                ->skip($keep)
                ->take(PHP_INT_MAX)
                ->pluck('id');

            if ($stale->isEmpty()) {
                return;
            }

            $deleted += PageVersion::query()->whereIn('id', $stale)->delete();
        });

        $this->info("Pruned {$deleted} old page version(s).");

        return self::SUCCESS;
    }
}
