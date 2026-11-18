<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Enums\PlacementLocation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends Model
{
    protected $fillable = [
        'slug', 'title', 'excerpt', 'body', 'featured_media_id', 'author_id',
        'status', 'published_at', 'is_featured', 'read_time', 'views',
        'seo_title', 'seo_description', 'canonical_url', 'og_media_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'read_time' => 'integer',
            'views' => 'integer',
        ];
    }

    public function featuredMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }

    public function ogMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'og_media_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ArticleCategory::class, 'article_category');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ArticleTag::class, 'article_tag');
    }

    public function placements(): HasMany
    {
        return $this->hasMany(ArticlePlacement::class);
    }

    public function url(): string
    {
        return route('news.show', $this->slug);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', ContentStatus::Published)
            ->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function scopeLatestPublished(Builder $query): Builder
    {
        return $query->published()->orderByDesc('published_at')->orderByDesc('id');
    }

    /** Articles placed in a given location, honouring schedule + manual order. */
    public function scopeForPlacement(Builder $query, PlacementLocation $location): Builder
    {
        return $query->published()
            ->whereHas('placements', function (Builder $q) use ($location) {
                $q->where('location', $location->value)
                    ->where(fn (Builder $inner) => $inner->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                    ->where(fn (Builder $inner) => $inner->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
            })
            ->orderByRaw(
                '(select coalesce(sum(article_placements.sort_order), 0) from article_placements where article_placements.article_id = articles.id and article_placements.location = ?) asc',
                [$location->value],
            )
            ->orderByDesc('published_at');
    }

    public function related(int $limit = 3)
    {
        $categoryIds = $this->categories->pluck('id');

        return static::published()
            ->whereKeyNot($this->getKey())
            ->when($categoryIds->isNotEmpty(), fn (Builder $q) => $q->whereHas(
                'categories',
                fn (Builder $inner) => $inner->whereIn('article_categories.id', $categoryIds),
            ))
            ->latestPublished()
            ->limit($limit)
            ->get();
    }

    public function readingTime(): int
    {
        if ($this->read_time > 0) {
            return $this->read_time;
        }

        $words = str_word_count(strip_tags((string) $this->body));

        return max(1, (int) ceil($words / 200));
    }
}
