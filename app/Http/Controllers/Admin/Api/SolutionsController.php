<?php

namespace App\Http\Controllers\Admin\Api;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Models\Solution;
use App\Models\SolutionScreenshot;
use App\Services\SlugRedirector;
use App\Support\RichText;
use App\Support\Slugs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SolutionsController extends Controller
{
    public function __construct(private readonly SlugRedirector $redirects) {}

    public function index(): JsonResponse
    {
        $solutions = Solution::query()
            ->with('cover')
            ->withCount(['features', 'screenshots'])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->map(fn (Solution $solution) => $this->present($solution));

        return response()->json(['data' => $solutions]);
    }

    public function show(Solution $solution): JsonResponse
    {
        $solution->load(['cover', 'ogMedia', 'features.media', 'screenshots.media', 'technologies']);

        return response()->json(['data' => $this->present($solution, detailed: true)]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $solution = Solution::create([
            ...$this->attributes($data),
            'slug' => Slugs::unique(Solution::class, $data['slug'] ?? $data['title']),
            'sort_order' => (int) Solution::query()->max('sort_order') + 1,
        ]);

        $this->syncFeatures($solution, $data['features'] ?? []);
        $this->syncTechnologies($solution, $data['technology_ids'] ?? []);

        return response()->json(['data' => $this->present($solution->fresh(['features', 'technologies']))], 201);
    }

    public function update(Request $request, Solution $solution): JsonResponse
    {
        $data = $this->validated($request, $solution);
        $originalSlug = $solution->slug;
        $attributes = $this->attributes($data);

        if (! empty($data['slug']) || (! empty($data['title']) && $request->boolean('regenerate_slug'))) {
            $attributes['slug'] = Slugs::unique(
                Solution::class,
                $data['slug'] ?? $data['title'],
                $solution->id,
            );
        }

        DB::transaction(function () use ($solution, $attributes, $data) {
            $solution->fill($attributes)->save();

            if (array_key_exists('features', $data)) {
                $this->syncFeatures($solution, $data['features'] ?? []);
            }

            if (array_key_exists('technology_ids', $data)) {
                $this->syncTechnologies($solution, $data['technology_ids'] ?? []);
            }
        });

        if (isset($attributes['slug'])) {
            $this->redirects->record($originalSlug, $solution->slug, 'solutions');
        }

        $solution->load(['features.media', 'screenshots.media', 'technologies']);

        return response()->json(['data' => $this->present($solution, detailed: true)]);
    }

    public function destroy(Solution $solution): JsonResponse
    {
        $solution->delete();

        return response()->json(['data' => ['ok' => true]]);
    }

    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:solutions,id'],
        ]);

        foreach ($data['ids'] as $index => $id) {
            Solution::query()->whereKey($id)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['data' => ['ok' => true]]);
    }

    public function storeScreenshot(Request $request, Solution $solution): JsonResponse
    {
        $data = $request->validate([
            'media_id' => ['required', 'integer', 'exists:media,id'],
            'caption' => ['nullable', 'string', 'max:200'],
        ]);

        $screenshot = $solution->screenshots()->create([
            'media_id' => $data['media_id'],
            'caption' => $data['caption'] ?? null,
            'sort_order' => (int) $solution->screenshots()->max('sort_order') + 1,
        ]);

        return response()->json([
            'data' => [
                'id' => $screenshot->id,
                'media_id' => $screenshot->media_id,
                'url' => $screenshot->media?->url(),
                'caption' => $screenshot->caption,
            ],
        ], 201);
    }

    public function syncScreenshots(Request $request, Solution $solution): JsonResponse
    {
        $data = $request->validate([
            'screenshots' => ['present', 'array'],
            'screenshots.*.id' => ['required', 'integer'],
            'screenshots.*.caption' => ['nullable', 'string', 'max:200'],
        ]);

        foreach ($data['screenshots'] as $index => $row) {
            SolutionScreenshot::query()
                ->where('solution_id', $solution->id)
                ->whereKey($row['id'])
                ->update([
                    'sort_order' => $index,
                    'caption' => $row['caption'] ?? null,
                ]);
        }

        return response()->json(['data' => ['ok' => true]]);
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?Solution $solution = null): array
    {
        return $request->validate([
            'title' => [$solution ? 'sometimes' : 'required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:200', 'alpha_dash'],
            'intro' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'cover_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'live_url' => ['nullable', 'url', 'max:255'],
            'business_benefits' => ['nullable', 'array'],
            'business_benefits.*.title' => ['required_with:business_benefits', 'string', 'max:200'],
            'business_benefits.*.description' => ['nullable', 'string', 'max:1000'],
            'features' => ['nullable', 'array'],
            'features.*.title' => ['required_with:features', 'string', 'max:200'],
            'features.*.description' => ['nullable', 'string', 'max:2000'],
            'features.*.media_id' => ['nullable', 'integer', 'exists:media,id'],
            'technology_ids' => ['nullable', 'array'],
            'technology_ids.*' => ['integer', 'exists:technologies,id'],
            'status' => ['nullable', 'in:'.implode(',', ContentStatus::values())],
            'is_featured' => ['boolean'],
            'seo_title' => ['nullable', 'string', 'max:200'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'og_media_id' => ['nullable', 'integer', 'exists:media,id'],
        ]);
    }

    /** @param array<string, mixed> $data */
    private function attributes(array $data): array
    {
        $attributes = $data;
        unset($attributes['features'], $attributes['technology_ids']);

        if (array_key_exists('description', $attributes)) {
            $attributes['description'] = RichText::clean($attributes['description']);
        }

        if (array_key_exists('business_benefits', $attributes) && is_array($attributes['business_benefits'])) {
            $attributes['business_benefits'] = array_values(array_map(fn ($benefit) => [
                'title' => (string) ($benefit['title'] ?? ''),
                'description' => (string) ($benefit['description'] ?? ''),
            ], $attributes['business_benefits']));
        }

        return $attributes;
    }

    /** @param array<int, array<string, mixed>> $features */
    private function syncFeatures(Solution $solution, array $features): void
    {
        $solution->features()->delete();

        foreach (array_values($features) as $index => $feature) {
            $solution->features()->create([
                'title' => (string) ($feature['title'] ?? ''),
                'description' => RichText::clean((string) ($feature['description'] ?? '')),
                'media_id' => $feature['media_id'] ?? null,
                'sort_order' => $index,
            ]);
        }
    }

    /** @param array<int, int> $technologyIds */
    private function syncTechnologies(Solution $solution, array $technologyIds): void
    {
        $solution->technologies()->sync($technologyIds);
    }

    /** @return array<string, mixed> */
    private function present(Solution $solution, bool $detailed = false): array
    {
        return array_filter([
            'id' => $solution->id,
            'title' => $solution->title,
            'slug' => $solution->slug,
            'intro' => $solution->intro,
            'description' => $detailed ? $solution->description : null,
            'cover_media_id' => $solution->cover_media_id,
            'cover_url' => $solution->cover?->url(),
            'live_url' => $solution->live_url,
            'business_benefits' => $detailed ? ($solution->business_benefits ?? []) : null,
            'status' => $solution->status->value,
            'is_featured' => $solution->is_featured,
            'sort_order' => $solution->sort_order,
            'seo_title' => $solution->seo_title,
            'seo_description' => $solution->seo_description,
            'og_media_id' => $solution->og_media_id,
            'features_count' => $solution->features_count ?? null,
            'screenshots_count' => $solution->screenshots_count ?? null,
            'features' => $detailed ? $solution->features->map(fn ($feature) => [
                'id' => $feature->id,
                'title' => $feature->title,
                'description' => $feature->description,
                'media_id' => $feature->media_id,
                'media_url' => $feature->media?->url(),
            ])->values() : null,
            'screenshots' => $detailed ? $solution->screenshots->map(fn ($shot) => [
                'id' => $shot->id,
                'media_id' => $shot->media_id,
                'url' => $shot->media?->url(),
                'caption' => $shot->caption,
            ])->values() : null,
            'technologies' => $detailed ? $solution->technologies->map(fn ($tech) => [
                'id' => $tech->id,
                'name' => $tech->name,
            ])->values() : null,
            'updated_at' => $solution->updated_at,
            'url' => $solution->url(),
        ], fn ($value) => $value !== null || ! $detailed);
    }
}
