<?php

namespace App\Http\Controllers\Admin\Api;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\SlugRedirector;
use App\Support\RichText;
use App\Support\Slugs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServicesController extends Controller
{
    public function __construct(private readonly SlugRedirector $redirects) {}

    public function index(): JsonResponse
    {
        $services = Service::query()
            ->with('cover')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->map(fn (Service $service) => $this->present($service));

        return response()->json(['data' => $services]);
    }

    public function show(Service $service): JsonResponse
    {
        $service->load('cover', 'ogMedia');

        return response()->json(['data' => $this->present($service, detailed: true)]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $service = Service::create([
            ...$this->attributes($data),
            'slug' => Slugs::unique(Service::class, $data['slug'] ?? $data['title']),
            'sort_order' => (int) Service::query()->max('sort_order') + 1,
        ]);

        return response()->json(['data' => $this->present($service)], 201);
    }

    public function update(Request $request, Service $service): JsonResponse
    {
        $data = $this->validated($request, $service);
        $originalSlug = $service->slug;

        $attributes = $this->attributes($data);

        if (! empty($data['slug']) || (! empty($data['title']) && $request->boolean('regenerate_slug'))) {
            $attributes['slug'] = Slugs::unique(
                Service::class,
                $data['slug'] ?? $data['title'],
                $service->id,
            );
        }

        $service->fill($attributes)->save();

        if (isset($attributes['slug'])) {
            $this->redirects->record($originalSlug, $service->slug, 'services');
        }

        return response()->json(['data' => $this->present($service->fresh())]);
    }

    public function destroy(Service $service): JsonResponse
    {
        $service->delete();

        return response()->json(['data' => ['ok' => true]]);
    }

    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:services,id'],
        ]);

        foreach ($data['ids'] as $index => $id) {
            Service::query()->whereKey($id)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['data' => ['ok' => true]]);
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?Service $service = null): array
    {
        return $request->validate([
            'title' => [$service ? 'sometimes' : 'required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:200', 'alpha_dash'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:64'],
            'cover_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'benefits' => ['nullable', 'array'],
            'benefits.*.title' => ['required_with:benefits', 'string', 'max:200'],
            'benefits.*.description' => ['nullable', 'string', 'max:1000'],
            'faqs' => ['nullable', 'array'],
            'faqs.*.question' => ['required_with:faqs', 'string', 'max:300'],
            'faqs.*.answer' => ['required_with:faqs', 'string'],
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

        if (array_key_exists('description', $attributes)) {
            $attributes['description'] = RichText::clean($attributes['description']);
        }

        if (array_key_exists('faqs', $attributes) && is_array($attributes['faqs'])) {
            $attributes['faqs'] = array_values(array_map(fn ($faq) => [
                'question' => (string) ($faq['question'] ?? ''),
                'answer' => RichText::clean((string) ($faq['answer'] ?? '')),
            ], $attributes['faqs']));
        }

        if (array_key_exists('benefits', $attributes) && is_array($attributes['benefits'])) {
            $attributes['benefits'] = array_values(array_map(fn ($benefit) => [
                'title' => (string) ($benefit['title'] ?? ''),
                'description' => (string) ($benefit['description'] ?? ''),
            ], $attributes['benefits']));
        }

        return $attributes;
    }

    /** @return array<string, mixed> */
    private function present(Service $service, bool $detailed = false): array
    {
        return array_filter([
            'id' => $service->id,
            'title' => $service->title,
            'slug' => $service->slug,
            'excerpt' => $service->excerpt,
            'description' => $detailed ? $service->description : null,
            'icon' => $service->icon,
            'cover_media_id' => $service->cover_media_id,
            'cover_url' => $service->cover?->url(),
            'benefits' => $detailed ? ($service->benefits ?? []) : null,
            'faqs' => $detailed ? ($service->faqs ?? []) : null,
            'status' => $service->status->value,
            'is_featured' => $service->is_featured,
            'sort_order' => $service->sort_order,
            'seo_title' => $service->seo_title,
            'seo_description' => $service->seo_description,
            'og_media_id' => $service->og_media_id,
            'updated_at' => $service->updated_at,
            'url' => $service->url(),
        ], fn ($value) => $value !== null || ! $detailed);
    }
}
