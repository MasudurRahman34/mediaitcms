<?php

namespace App\Models;

use App\Enums\PlacementLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticlePlacement extends Model
{
    protected $fillable = ['article_id', 'location', 'sort_order', 'starts_at', 'ends_at'];

    protected function casts(): array
    {
        return [
            'location' => PlacementLocation::class,
            'sort_order' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
