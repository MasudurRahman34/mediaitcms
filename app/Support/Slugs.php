<?php

namespace App\Support;

use Illuminate\Support\Str;

class Slugs
{
    /**
     * Produce a unique slug for a table, ignoring the record being updated.
     */
    public static function unique(string $modelClass, string $value, ?int $ignoreId = null, string $column = 'slug'): string
    {
        $base = Str::slug($value) ?: Str::random(8);
        $slug = $base;
        $suffix = 2;

        while (
            $modelClass::query()
                ->where($column, $slug)
                ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
