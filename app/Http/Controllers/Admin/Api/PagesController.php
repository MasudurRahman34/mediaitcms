<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\Pages\PageService;
use App\Services\Sections\SectionRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PagesController extends Controller
{
    public function __construct(private readonly PageService $pages) {}

    public function index(): JsonResponse
    {
        $pages = Page::query()
            ->withCount('draftSections')
            ->orderByDesc('is_system')
            ->orderBy('title')
            ->get()
            ->map(fn (Page $page) => [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'template' => $page->template,
                'status' => $page->status->value,
                'is_system' => $page->is_system,
                'sections_count' => $page->draft_sections_count,
                'published_at' => $page->publishedVersion?->published_at,
                'updated_at' => $page->updated_at,
                'url' => $page->url(),
            ]);

        return response()->json(['data' => $pages]);
    }

    public function show(Page $page): JsonResponse
    {
        $page->load(['draftSections', 'versions' => fn ($q) => $q->limit(10), 'publishedVersion', 'ogMedia']);

        return response()->json([
            'data' => [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'template' => $page->template,
                'status' => $page->status->value,
                'is_system' => $page->is_system,
                'seo_title' => $page->seo_title,
                'seo_description' => $page->seo_description,
                'og_media_id' => $page->og_media_id,
                'og_media_url' => $page->ogMedia?->url(),
                'updated_at' => $page->updated_at,
                'published_at' => $page->publishedVersion?->published_at,
                'has_unpublished_changes' => $this->hasUnpublishedChanges($page),
                'sections' => $page->draftSections->map->toPayload()->values(),
                'versions' => $page->versions->map(fn ($version) => [
                    'id' => $version->id,
                    'version_number' => $version->version_number,
                    'label' => $version->label,
                    'published_at' => $version->published_at,
                    'sections_count' => count($version->payload ?? []),
                ]),
            ],
            'registry' => app(SectionRegistry::class)->all(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:200', 'alpha_dash', 'unique:pages,slug'],
            'template' => ['nullable', 'in:standard,home,about'],
        ]);

        $page = $this->pages->create($data, $request->user());

        return response()->json(['data' => ['id' => $page->id]], 201);
    }

    public function update(Request $request, Page $page): JsonResponse
    {
        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:200'],
            'slug' => ['sometimes', 'required', 'string', 'max:200', 'alpha_dash', 'unique:pages,slug,'.$page->id],
            'seo_title' => ['nullable', 'string', 'max:200'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'og_media_id' => ['nullable', 'integer', 'exists:media,id'],
        ]);

        $this->pages->update($page, $data, $request->user());

        return response()->json(['data' => ['ok' => true]]);
    }

    public function destroy(Page $page): JsonResponse
    {
        abort_if($page->is_system, 422, 'System pages cannot be deleted.');

        $page->delete();

        return response()->json(['data' => ['ok' => true]]);
    }

    public function publish(Request $request, Page $page): JsonResponse
    {
        $this->pages->publish($page, $request->user());

        return response()->json(['data' => ['ok' => true, 'published_at' => $page->fresh()->publishedVersion?->published_at]]);
    }

    public function unpublish(Page $page): JsonResponse
    {
        $this->pages->unpublish($page);

        return response()->json(['data' => ['ok' => true]]);
    }

    public function versions(Page $page): JsonResponse
    {
        return response()->json([
            'data' => $page->versions()->with('author:id,name')->get()->map(fn ($version) => [
                'id' => $version->id,
                'version_number' => $version->version_number,
                'label' => $version->label,
                'author' => $version->author?->name,
                'sections_count' => count($version->payload ?? []),
                'published_at' => $version->published_at,
            ]),
        ]);
    }

    public function restore(Request $request, Page $page, int $version): JsonResponse
    {
        $this->pages->restore($page, $version, $request->user());

        return response()->json(['data' => ['ok' => true]]);
    }

    private function hasUnpublishedChanges(Page $page): bool
    {
        $published = $page->publishedVersion?->payload ?? [];
        $draft = $page->draftSections->map->toPayload()->values()->all();

        $normalise = fn (array $sections) => collect($sections)->map(fn ($section) => [
            'type' => $section['type'] ?? null,
            'layout' => $section['layout'] ?? null,
            'is_enabled' => (bool) ($section['is_enabled'] ?? true),
            'content' => $section['content'] ?? [],
        ])->values()->all();

        return $normalise($published) !== $normalise($draft);
    }
}
