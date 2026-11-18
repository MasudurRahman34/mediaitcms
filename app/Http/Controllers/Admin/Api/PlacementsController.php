<?php

namespace App\Http\Controllers\Admin\Api;

use App\Enums\PlacementLocation;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticlePlacement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlacementsController extends Controller
{
    /**
     * Replace an article's publishing placements. One article record can be
     * surfaced in several locations at once (news listing, home featured, etc.).
     */
    public function sync(Request $request, Article $article): JsonResponse
    {
        $data = $request->validate([
            'placements' => ['present', 'array'],
            'placements.*.location' => ['required', Rule::in(PlacementLocation::values())],
            'placements.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'placements.*.starts_at' => ['nullable', 'date'],
            'placements.*.ends_at' => ['nullable', 'date', 'after_or_equal:placements.*.starts_at'],
        ]);

        $kept = [];

        foreach ($data['placements'] as $placement) {
            $kept[] = $placement['location'];

            ArticlePlacement::query()->updateOrCreate(
                ['article_id' => $article->id, 'location' => $placement['location']],
                [
                    'sort_order' => $placement['sort_order'] ?? 0,
                    'starts_at' => $placement['starts_at'] ?? null,
                    'ends_at' => $placement['ends_at'] ?? null,
                ],
            );
        }

        $article->placements()->whereNotIn('location', $kept ?: ['__none__'])->delete();

        return response()->json(['data' => ['ok' => true]]);
    }
}
