<?php

namespace App\Models;

use App\Enums\JobStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Job extends Model
{
    protected $fillable = [
        'slug', 'title', 'department', 'location', 'employment_type', 'experience_level',
        'salary_range', 'description', 'requirements', 'status', 'closing_at',
        'is_featured', 'seo_title', 'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'status' => JobStatus::class,
            'closing_at' => 'datetime',
            'is_featured' => 'boolean',
        ];
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function url(): string
    {
        return route('careers.show', $this->slug);
    }

    public function isAcceptingApplications(): bool
    {
        if ($this->status !== JobStatus::Open) {
            return false;
        }

        return $this->closing_at === null || $this->closing_at->isFuture();
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', JobStatus::Open)
            ->where(fn (Builder $q) => $q->whereNull('closing_at')->orWhere('closing_at', '>=', now()));
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('is_featured', 'desc')->orderByDesc('created_at');
    }
}
