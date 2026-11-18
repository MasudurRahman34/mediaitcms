<?php

namespace App\Enums;

enum PlacementLocation: string
{
    case News = 'news';
    case HomeFeatured = 'home_featured';
    case HomeLatest = 'home_latest';

    public function label(): string
    {
        return match ($this) {
            self::News => 'News listing',
            self::HomeFeatured => 'Home — Featured',
            self::HomeLatest => 'Home — Latest',
        };
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
