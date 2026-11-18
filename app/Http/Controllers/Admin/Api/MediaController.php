<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\Media\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function __construct(private readonly MediaService $media) {}

    public function index(Request $request): JsonResponse
    {
        $query = Media::query()->latest();

        if ($folder = $request->string('folder')->toString()) {
            $query->where('folder', $folder);
        }

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(fn ($q) => $q->where('filename', 'like', "%{$search}%")
                ->orWhere('alt', 'like', "%{$search}%")
                ->orWhere('title', 'like', "%{$search}%"));
        }

        if ($request->boolean('images_only')) {
            $query->images();
        }

        $media = $query->paginate(36)->withQueryString();

        return response()->json([
            'data' => $media->getCollection()->map(fn (Media $item) => $this->present($item))->values(),
            'folders' => Media::query()->distinct()->orderBy('folder')->pluck('folder'),
            'meta' => [
                'current_page' => $media->currentPage(),
                'last_page' => $media->lastPage(),
                'total' => $media->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,svg,gif,pdf,doc,docx'],
            'folder' => ['nullable', 'string', 'max:64', 'regex:/^[a-z0-9\-\/]+$/'],
            'alt' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        $media = $this->media->upload(
            $request->file('file'),
            $data['folder'] ?? 'uploads',
            config('filesystems.media_disk', 'public'),
            $request->user()->id,
        );

        if (! empty($data['alt']) || ! empty($data['title'])) {
            $media->update(array_filter([
                'alt' => $data['alt'] ?? null,
                'title' => $data['title'] ?? null,
            ]));
        }

        return response()->json(['data' => $this->present($media->fresh())], 201);
    }

    public function update(Request $request, Media $medium): JsonResponse
    {
        $data = $request->validate([
            'alt' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'folder' => ['nullable', 'string', 'max:64'],
        ]);

        $medium->update($data);

        return response()->json(['data' => $this->present($medium->fresh())]);
    }

    public function destroy(Media $medium): JsonResponse
    {
        if ($this->media->isInUse($medium)) {
            return response()->json([
                'message' => 'This file is still used by content. Remove it from those items first.',
            ], 422);
        }

        $medium->delete();

        return response()->json(['data' => ['ok' => true]]);
    }

    /** @return array<string, mixed> */
    private function present(Media $media): array
    {
        return [
            'id' => $media->id,
            'url' => $media->url(),
            'thumbnail' => $media->isImage() ? $media->variantUrl('400') : null,
            'srcset' => $media->srcset(),
            'filename' => $media->filename,
            'mime' => $media->mime,
            'size' => $media->size,
            'human_size' => $media->humanSize(),
            'width' => $media->width,
            'height' => $media->height,
            'alt' => $media->alt,
            'title' => $media->title,
            'folder' => $media->folder,
            'disk' => $media->disk,
            'is_image' => $media->isImage(),
            'created_at' => $media->created_at,
        ];
    }
}
