<?php

namespace App\Services\Pages;

use App\Enums\PageStatus;
use App\Models\AuditLog;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\PageVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PagePublisher
{
    /**
     * Freeze the current draft sections into an immutable version and
     * publish it for the public site.
     */
    public function publish(Page $page, ?User $user = null, ?string $label = null): PageVersion
    {
        return DB::transaction(function () use ($page, $user, $label) {
            $sections = $page->draftSections()->get();

            $payload = $sections->map(fn (PageSection $section) => [
                'type' => $section->type,
                'layout' => $section->layout,
                'sort_order' => $section->sort_order,
                'is_enabled' => (bool) $section->is_enabled,
                'content' => $section->content ?? [],
            ])->values()->all();

            $nextVersion = (int) PageVersion::query()
                ->where('page_id', $page->id)
                ->max('version_number') + 1;

            $version = PageVersion::create([
                'page_id' => $page->id,
                'version_number' => $nextVersion,
                'label' => $label,
                'payload' => $payload,
                'created_by' => $user?->id,
                'published_at' => now(),
            ]);

            $page->forceFill([
                'published_version_id' => $version->id,
                'status' => PageStatus::Published,
            ])->save();

            $this->audit($user, $page, 'page.published', [
                'version' => $nextVersion,
                'sections' => count($payload),
            ]);

            return $version;
        });
    }

    /**
     * Copy a historical version back into the working draft.
     */
    public function restoreDraft(Page $page, PageVersion $version, ?User $user = null): void
    {
        if ($version->page_id !== $page->id) {
            throw new \InvalidArgumentException('Version does not belong to this page.');
        }

        DB::transaction(function () use ($page, $version, $user) {
            $page->draftSections()->delete();

            foreach ($version->payload ?? [] as $index => $section) {
                PageSection::create([
                    'page_id' => $page->id,
                    'version_id' => null,
                    'type' => $section['type'],
                    'layout' => $section['layout'] ?? null,
                    'sort_order' => $section['sort_order'] ?? $index,
                    'is_enabled' => $section['is_enabled'] ?? true,
                    'content' => $section['content'] ?? [],
                ]);
            }

            $this->audit($user, $page, 'page.version-restored', [
                'version' => $version->version_number,
            ]);
        });
    }

    /**
     * Seed the draft from a fresh payload (used by seeders and page creation).
     *
     * @param  array<int, array<string, mixed>>  $sections
     */
    public function writeDraft(Page $page, array $sections): void
    {
        DB::transaction(function () use ($page, $sections) {
            $page->draftSections()->delete();

            foreach ($sections as $index => $section) {
                PageSection::create([
                    'page_id' => $page->id,
                    'version_id' => null,
                    'type' => $section['type'],
                    'layout' => $section['layout'] ?? null,
                    'sort_order' => $section['sort_order'] ?? $index,
                    'is_enabled' => $section['is_enabled'] ?? true,
                    'content' => $section['content'] ?? [],
                ]);
            }
        });
    }

    private function audit(?User $user, Page $page, string $action, array $changes = []): void
    {
        AuditLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'description' => $page->title,
            'auditable_type' => $page->getMorphClass(),
            'auditable_id' => $page->id,
            'changes' => $changes,
            'ip' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 500),
        ]);
    }
}
