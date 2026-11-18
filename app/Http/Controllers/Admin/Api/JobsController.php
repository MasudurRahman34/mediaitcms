<?php

namespace App\Http\Controllers\Admin\Api;

use App\Enums\JobStatus;
use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Services\SlugRedirector;
use App\Support\RichText;
use App\Support\Slugs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobsController extends Controller
{
    public function __construct(private readonly SlugRedirector $redirects) {}

    public function index(Request $request): JsonResponse
    {
        $query = Job::query()->withCount('applications');

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(fn ($q) => $q->where('title', 'like', "%{$search}%")
                ->orWhere('department', 'like', "%{$search}%")
                ->orWhere('location', 'like', "%{$search}%"));
        }

        return response()->json([
            'data' => $query->latest()->get()->map(fn (Job $job) => $this->present($job))->values(),
        ]);
    }

    public function show(Job $job): JsonResponse
    {
        return response()->json(['data' => $this->present($job, detailed: true)]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $job = Job::create([
            ...$this->attributes($data),
            'slug' => Slugs::unique(Job::class, $data['slug'] ?? $data['title']),
        ]);

        return response()->json(['data' => $this->present($job)], 201);
    }

    public function update(Request $request, Job $job): JsonResponse
    {
        $data = $this->validated($request, $job);
        $originalSlug = $job->slug;
        $attributes = $this->attributes($data);

        if (! empty($data['slug']) || (! empty($data['title']) && $request->boolean('regenerate_slug'))) {
            $attributes['slug'] = Slugs::unique(Job::class, $data['slug'] ?? $data['title'], $job->id);
        }

        $job->fill($attributes)->save();

        if (isset($attributes['slug'])) {
            $this->redirects->record($originalSlug, $job->slug, 'careers');
        }

        return response()->json(['data' => $this->present($job->fresh())]);
    }

    public function destroy(Job $job): JsonResponse
    {
        $job->delete();

        return response()->json(['data' => ['ok' => true]]);
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?Job $job = null): array
    {
        return $request->validate([
            'title' => [$job ? 'sometimes' : 'required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:200', 'alpha_dash'],
            'department' => ['nullable', 'string', 'max:120'],
            'location' => ['nullable', 'string', 'max:160'],
            'employment_type' => ['nullable', 'in:full-time,part-time,contract,temporary,internship'],
            'experience_level' => ['nullable', 'string', 'max:80'],
            'salary_range' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'requirements' => ['nullable', 'string'],
            'status' => ['nullable', 'in:'.implode(',', JobStatus::values())],
            'closing_at' => ['nullable', 'date'],
            'is_featured' => ['boolean'],
            'seo_title' => ['nullable', 'string', 'max:200'],
            'seo_description' => ['nullable', 'string', 'max:500'],
        ]);
    }

    /** @param array<string, mixed> $data */
    private function attributes(array $data): array
    {
        foreach (['description', 'requirements'] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = RichText::clean($data[$field]);
            }
        }

        return $data;
    }

    /** @return array<string, mixed> */
    private function present(Job $job, bool $detailed = false): array
    {
        return array_filter([
            'id' => $job->id,
            'title' => $job->title,
            'slug' => $job->slug,
            'department' => $job->department,
            'location' => $job->location,
            'employment_type' => $job->employment_type,
            'experience_level' => $job->experience_level,
            'salary_range' => $job->salary_range,
            'description' => $detailed ? $job->description : null,
            'requirements' => $detailed ? $job->requirements : null,
            'status' => $job->status->value,
            'closing_at' => $job->closing_at,
            'is_featured' => $job->is_featured,
            'applications_count' => $job->applications_count ?? null,
            'seo_title' => $job->seo_title,
            'seo_description' => $job->seo_description,
            'updated_at' => $job->updated_at,
            'url' => $job->url(),
        ], fn ($value) => $value !== null || ! $detailed);
    }
}
