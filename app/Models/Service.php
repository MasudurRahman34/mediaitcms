<?php

namespace App\Models;

use App\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    protected $fillable = [
        'slug', 'title', 'excerpt', 'description', 'icon', 'cover_media_id',
        'benefits', 'faqs', 'status', 'is_featured', 'sort_order',
        'seo_title', 'seo_description', 'og_media_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
            'benefits' => 'array',
            'faqs' => 'array',
        ];
    }

    public function cover(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    public function ogMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'og_media_id');
    }

    public function url(): string
    {
        return route('services.show', $this->slug);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::Published);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }
}
