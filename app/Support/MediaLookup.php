<?php

namespace App\Support;

use App\Models\Media;

/**
 * Request-scoped memoised media lookups for CMS section payloads.
 */
class MediaLookup
{
    /** @var array<int, Media|null> */
    private static array $cache = [];

    public static function find(mixed $id): ?Media
    {
        if (! $id) {
            return null;
        }

        $id = (int) $id;

        if (! array_key_exists($id, self::$cache)) {
            self::$cache[$id] = Media::find($id);
        }

        return self::$cache[$id];
    }
}
