<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageSection extends Model
{
    protected $fillable = [
        'page_id', 'version_id', 'type', 'layout', 'sort_order', 'is_enabled', 'content',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'is_enabled' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /** Shape consumed by the renderer and the admin builder. */
    public function toPayload(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'layout' => $this->layout,
            'sort_order' => $this->sort_order,
            'is_enabled' => (bool) $this->is_enabled,
            'content' => $this->content ?? [],
        ];
    }
}
