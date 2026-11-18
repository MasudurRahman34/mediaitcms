<?php

namespace App\Services;

use App\Models\NavigationItem;
use App\Models\NavigationMenu;
use Illuminate\Support\Facades\Cache;

class NavigationRepository
{
    /** @return array<int, NavigationItem> */
    public function for(string $key): array
    {
        return Cache::rememberForever('navigation.'.$key, function () use ($key) {
            $menu = NavigationMenu::query()->where('key', $key)->first();

            if (! $menu) {
                return [];
            }

            return $menu->items()
                ->with(['page', 'children.page'])
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->with(['children' => fn ($query) => $query->where('is_active', true)])
                ->get()
                ->all();
        });
    }

    public function flush(?string $key = null): void
    {
        if ($key) {
            Cache::forget('navigation.'.$key);

            return;
        }

        foreach (['header', 'footer_company', 'footer_services', 'footer_legal'] as $menuKey) {
            Cache::forget('navigation.'.$menuKey);
        }
    }
}
