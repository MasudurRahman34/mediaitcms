<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NavigationItem extends Model
{
    protected $fillable = [
        'menu_id', 'parent_id', 'label', 'url', 'page_id', 'target', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(NavigationMenu::class, 'menu_id');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function resolvedUrl(): string
    {
        if ($this->page) {
            return $this->page->url();
        }

        $url = (string) $this->url;

        if ($url === '' || str_starts_with($url, 'http') || str_starts_with($url, '/')) {
            return $url === '' ? '#' : $url;
        }

        return url($url);
    }

    public function isExternal(): bool
    {
        return str_starts_with((string) $this->url, 'http');
    }
}
