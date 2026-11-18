<?php

namespace App\Services;

use App\Models\Redirect;

class SlugRedirector
{
    /**
     * Remember the old public URL so old links keep working (301).
     * `base` is the path prefix, e.g. "services" or "news".
     */
    public function record(?string $oldSlug, string $newSlug, string $base = ''): void
    {
        if (! $oldSlug || $oldSlug === $newSlug) {
            return;
        }

        $from = trim($base.'/'.$oldSlug, '/');
        $to = trim($base.'/'.$newSlug, '/');

        if ($from === '' || $from === $to) {
            return;
        }

        Redirect::query()->updateOrCreate(
            ['from_path' => $from],
            ['to_path' => $to, 'status_code' => 301],
        );
    }
}
