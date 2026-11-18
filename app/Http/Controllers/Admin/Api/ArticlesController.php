<?php

namespace App\Http\Controllers\Admin\Api;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\SlugRedirector;
use App\Support\RichText;
use App\Support\Slugs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArticlesController extends Controller
{
    public function __construct(private readonly SlugRedirector $redirects) {}

    public function index(Request $request): JsonResponse
    {
        $query = Article::query()
            ->with(['featuredMedia', 'author:id,name', 'categories:id,name'])
            ->withCount('placements');

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($categoryId = $request->integer('category_id')) {
            $query->whereHas('categories', fn ($q) => $q->where('article_categories.id', $categoryId));
        }

        $articles = $query->latest('updated_at')->paginate(20)->withQueryString();

        return response()->json([
            'data' => $articles->getCollection()->map(fn (Article $article) => $this->present($article))->values(),
            'meta' => [
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
                'total' => $articles->total(),
                'per_page' => $articles->perPage(),
            ],
        ]);
    }

    public function show(Article $article): JsonResponse
    {
        $article->load(['featuredMedia', 'ogMedia', 'categories', 'tags', 'placements']);

        return response()->json(['data' => $this->present($article, detailed: true)]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $article = DB::transaction(function () use ($data, $request) {
            $article = Article::create([
                ...$this->attributes($data),
                'slug' => Slugs::unique(Article::class, $data['slug'] ?? $data['title']),
                'author_id' => $request->user()->id,
            ]);

            $article->categories()->sync($data['category_ids'] ?? []);
            $article->tags()->sync($data['tag_ids'] ?? []);

            return $article;
        });

        return response()->json(['data' => $this->present($article->fresh(['categories', 'tags']), detailed: true)], 201);
    }

    public function update(Request $request, Article $article): JsonResponse
    {
        $data = $this->validated($request, $article);
        $originalSlug = $article->slug;
        $attributes = $this->attributes($data);

        if (! empty($data['slug']) || (! empty($data['title']) && $request->boolean('regenerate_slug'))) {
            $attributes['slug'] = Slugs::unique(
                Article::class,
                $data['slug'] ?? $data['title'],
                $article->id,
            );
        }

        DB::transaction(function () use ($article, $attributes, $data) {
            $article->fill($attributes)->save();

            if (array_key_exists('category_ids', $data)) {
                $article->categories()->sync($data['category_ids'] ?? []);
            }

            if (array_key_exists('tag_ids', $data)) {
                $article->tags()->sync($data['tag_ids'] ?? []);
            }
        });

        if (isset($attributes['slug'])) {
            $this->redirects->record($originalSlug, $article->slug, 'news');
        }

        $article->load(['featuredMedia', 'ogMedia', 'categories', 'tags', 'placements']);

        return response()->json(['data' => $this->present($article, detailed: true)]);
    }

    public function destroy(Article $article): JsonResponse
    {
        $article->delete();

        return response()->json(['data' => ['ok' => true]]);
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?Article $article = null): array
    {
        return $request->validate([
            'title' => [$article ? 'sometimes' : 'required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:200', 'alpha_dash'],
            'excerpt' => ['nullable', 'string', 'max:600'],
            'body' => ['nullable', 'string'],
            'featured_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'status' => ['nullable', 'in:'.implode(',', ContentStatus::values())],
            'published_at' => ['nullable', 'date'],
            'is_featured' => ['boolean'],
            'read_time' => ['nullable', 'integer', 'min:0', 'max:120'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:article_categories,id'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:article_tags,id'],
            'seo_title' => ['nullable', 'string', 'max:200'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'canonical_url' => ['nullable', 'url', 'max:255'],
            'og_media_id' => ['nullable', 'integer', 'exists:media,id'],
        ]);
    }

    /** @param array<string, mixed> $data */
    private function attributes(array $data): array
    {
        $attributes = $data;
        unset($attributes['category_ids'], $attributes['tag_ids']);

        if (array_key_exists('body', $attributes)) {
            $attributes['body'] = RichText::clean($attributes['body']);
        }

        if (($attributes['status'] ?? null) === ContentStatus::Published->value && empty($attributes['published_at'])) {
            $attributes['published_at'] = now();
        }

        return $attributes;
    }

    /** @return array<string, mixed> */
    private function present(Article $article, bool $detailed = false): array
    {
        return array_filter([
            'id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'excerpt' => $article->excerpt,
            'body' => $detailed ? $article->body : null,
            'featured_media_id' => $article->featured_media_id,
            'featured_media_url' => $article->featuredMedia?->url(),
            'og_media_id' => $article->og_media_id,
            'author' => $article->author?->name,
            'status' => $article->status->value,
            'published_at' => $article->published_at,
            'is_featured' => $article->is_featured,
            'read_time' => $article->read_time,
            'views' => $article->views,
            'placements_count' => $article->placements_count ?? null,
            'categories' => $article->categories->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
            ])->values(),
            'tags' => $detailed ? $article->tags->map(fn ($tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
            ])->values() : null,
            'placements' => $detailed ? $article->placements->map(fn ($placement) => [
                'id' => $placement->id,
                'location' => $placement->location->value,
                'sort_order' => $placement->sort_order,
                'starts_at' => $placement->starts_at,
                'ends_at' => $placement->ends_at,
            ])->values() : null,
            'seo_title' => $article->seo_title,
            'seo_description' => $article->seo_description,
            'canonical_url' => $article->canonical_url,
            'updated_at' => $article->updated_at,
            'url' => $article->url(),
        ], fn ($value) => $value !== null || ! $detailed);
    }
}
