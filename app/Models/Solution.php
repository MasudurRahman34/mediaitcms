<?php

namespace App\Models;

use App\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Solution extends Model
{
    protected $fillable = [
        'slug', 'title', 'intro', 'description', 'cover_media_id', 'live_url',
        'business_benefits', 'status', 'is_featured', 'sort_order',
        'seo_title', 'seo_description', 'og_media_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
            'business_benefits' => 'array',
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

    public function features(): HasMany
    {
        return $this->hasMany(SolutionFeature::class)->orderBy('sort_order');
    }

    public function screenshots(): HasMany
    {
        return $this->hasMany(SolutionScreenshot::class)->orderBy('sort_order');
    }

    public function technologies(): BelongsToMany
    {
        return $this->belongsToMany(Technology::class);
    }

    public function url(): string
    {
        return route('solutions.show', $this->slug);
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
