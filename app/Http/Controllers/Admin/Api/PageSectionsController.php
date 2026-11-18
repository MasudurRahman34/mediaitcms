<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageSection;
use App\Services\Sections\SectionRegistry;
use App\Support\RichText;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PageSectionsController extends Controller
{
    public function __construct(private readonly SectionRegistry $registry) {}

    /**
     * Replace the page's working draft with the posted ordered section list.
     */
    public function sync(Request $request, Page $page): JsonResponse
    {
        $data = $request->validate([
            'sections' => ['present', 'array'],
            'sections.*.type' => ['required', 'string', 'in:'.implode(',', $this->registry->types())],
            'sections.*.layout' => ['nullable', 'string', 'max:64'],
            'sections.*.is_enabled' => ['boolean'],
            'sections.*.content' => ['nullable', 'array'],
        ]);

        // Validate each section's content against its own schema.
        foreach ($data['sections'] as $index => $section) {
            $validator = validator(
                ['content' => $section['content'] ?? []],
                $this->registry->rules($section['type']),
                [],
                ['content' => 'section '.($index + 1)],
            );

            if ($validator->fails()) {
                throw ValidationException::withMessages([
                    'sections.'.$index => $validator->errors()->all(),
                ]);
            }
        }

        DB::transaction(function () use ($page, $data) {
            $page->draftSections()->delete();

            foreach (array_values($data['sections']) as $index => $section) {
                PageSection::create([
                    'page_id' => $page->id,
                    'version_id' => null,
                    'type' => $section['type'],
                    'layout' => $section['layout'] ?? null,
                    'sort_order' => $index,
                    'is_enabled' => $section['is_enabled'] ?? true,
                    'content' => $this->normalise($section['type'], $section['content'] ?? []),
                ]);
            }
        });

        return response()->json([
            'data' => [
                'sections' => $page->fresh()->draftSections()->get()->map->toPayload()->values(),
            ],
        ]);
    }

    /**
     * Strip unknown keys and sanitise rich text before persisting.
     *
     * @param  array<string, mixed>  $content
     * @return array<string, mixed>
     */
    private function normalise(string $type, array $content): array
    {
        $definition = $this->registry->definition($type) ?? [];
        $allowed = collect($definition['fields'] ?? [])->keyBy('name');
        $clean = [];

        foreach ($allowed as $name => $field) {
            $value = $content[$name] ?? null;

            $clean[$name] = match ($field['type']) {
                'richtext' => RichText::clean(is_string($value) ? $value : null),
                'boolean' => (bool) $value,
                'number' => $value === null || $value === '' ? null : (int) $value,
                'repeater' => is_array($value) ? $this->cleanRepeater($field['fields'] ?? [], $value) : [],
                'cta' => [
                    'label' => is_array($value) ? (string) ($value['label'] ?? '') : '',
                    'url' => is_array($value) ? (string) ($value['url'] ?? '') : '',
                ],
                'image' => $value === null || $value === '' ? null : (int) $value,
                default => is_string($value) ? trim($value) : $value,
            };
        }

        return $clean;
    }

    /**
     * @param  array<int, array<string, mixed>>  $fields
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function cleanRepeater(array $fields, array $rows): array
    {
        $map = collect($fields)->keyBy('name');

        return collect($rows)->map(function (array $row) use ($map) {
            $result = [];

            foreach ($map as $name => $field) {
                $value = $row[$name] ?? null;

                $result[$name] = match ($field['type']) {
                    'richtext' => RichText::clean(is_string($value) ? $value : null),
                    'image' => $value === null || $value === '' ? null : (int) $value,
                    'number' => $value === null || $value === '' ? null : (int) $value,
                    'boolean' => (bool) $value,
                    default => is_string($value) ? trim($value) : $value,
                };
            }

            return $result;
        })->values()->all();
    }
}
