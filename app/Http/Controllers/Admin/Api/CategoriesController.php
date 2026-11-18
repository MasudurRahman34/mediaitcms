<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\ArticleCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoriesController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => ArticleCategory::query()
                ->withCount('articles')
                ->orderBy('name')
                ->get()
                ->map(fn (ArticleCategory $category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'articles_count' => $category->articles_count,
                ]),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $category = ArticleCategory::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'description' => $data['description'] ?? null,
        ]);

        return response()->json(['data' => ['id' => $category->id]], 201);
    }

    public function update(Request $request, ArticleCategory $category): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $category->update([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'description' => $data['description'] ?? null,
        ]);

        return response()->json(['data' => ['ok' => true]]);
    }

    public function destroy(ArticleCategory $category): JsonResponse
    {
        $category->delete();

        return response()->json(['data' => ['ok' => true]]);
    }
}
