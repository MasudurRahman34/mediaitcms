<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\ArticleTag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagsController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => ArticleTag::query()
                ->withCount('articles')
                ->orderBy('name')
                ->get()
                ->map(fn (ArticleTag $tag) => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                    'articles_count' => $tag->articles_count,
                ]),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:60']]);

        $tag = ArticleTag::firstOrCreate(
            ['slug' => Str::slug($data['name'])],
            ['name' => $data['name']],
        );

        return response()->json(['data' => ['id' => $tag->id, 'name' => $tag->name]], 201);
    }

    public function update(Request $request, ArticleTag $tag): JsonResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:60']]);

        $tag->update(['name' => $data['name'], 'slug' => Str::slug($data['name'])]);

        return response()->json(['data' => ['ok' => true]]);
    }

    public function destroy(ArticleTag $tag): JsonResponse
    {
        $tag->delete();

        return response()->json(['data' => ['ok' => true]]);
    }
}
