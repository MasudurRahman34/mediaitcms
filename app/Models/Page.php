<?php

namespace App\Models;

use App\Enums\PageStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    protected $fillable = [
        'slug', 'title', 'template', 'status', 'published_version_id',
        'seo_title', 'seo_description', 'og_media_id', 'is_system',
    ];

    protected function casts(): array
    {
        return [
            'status' => PageStatus::class,
            'is_system' => 'boolean',
        ];
    }

    public function sections(): HasMany
    {
        return $this->hasMany(PageSection::class)->orderBy('sort_order');
    }

    /** Draft (working) sections: version_id is null. */
    public function draftSections(): HasMany
    {
        return $this->hasMany(PageSection::class)
            ->whereNull('version_id')
            ->orderBy('sort_order');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(PageVersion::class)->latest('version_number');
    }

    public function publishedVersion(): BelongsTo
    {
        return $this->belongsTo(PageVersion::class, 'published_version_id');
    }

    public function ogMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'og_media_id');
    }

    public function isPublished(): bool
    {
        return $this->status === PageStatus::Published && $this->published_version_id !== null;
    }

    public function url(): string
    {
        return $this->slug === 'home' ? route('home') : url('/'.$this->slug);
    }

    public function scopePublished($query)
    {
        return $query->where('status', PageStatus::Published);
    }
}
