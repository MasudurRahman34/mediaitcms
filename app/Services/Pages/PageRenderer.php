<?php

namespace App\Services\Pages;

use App\Models\Page;
use App\Services\Sections\SectionRegistry;

class PageRenderer
{
    public function __construct(private readonly SectionRegistry $registry) {}

    /**
     * Sections for the published page, or the working draft when previewing.
     *
     * @return array<int, array<string, mixed>>
     */
    public function sections(Page $page, bool $preview = false): array
    {
        if (! $preview) {
            $version = $page->publishedVersion;
            $payload = $version?->payload ?? [];

            return $this->prepare($payload);
        }

        return $this->prepare(
            $page->draftSections()->get()->map->toPayload()->all(),
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $sections
     * @return array<int, array<string, mixed>>
     */
    private function prepare(array $sections): array
    {
        $prepared = [];

        foreach ($sections as $section) {
            $type = $section['type'] ?? null;

            if (! $type || ! $this->registry->has($type)) {
                continue;
            }

            if (! ($section['is_enabled'] ?? true)) {
                continue;
            }

            $prepared[] = [
                'type' => $type,
                'layout' => $section['layout'] ?? null,
                'content' => $section['content'] ?? [],
            ];
        }

        return $prepared;
    }
}
