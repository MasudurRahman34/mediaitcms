<?php

namespace App\Services\Pages;

use App\Enums\PageStatus;
use App\Models\AuditLog;
use App\Models\Page;
use App\Models\PageVersion;
use App\Models\Redirect;
use App\Models\User;
use App\Support\Slugs;
use Illuminate\Support\Str;

class PageService
{
    public function __construct(private readonly PagePublisher $publisher) {}

    /** @param array<string, mixed> $data */
    public function create(array $data, ?User $user = null): Page
    {
        $page = Page::create([
            'title' => $data['title'],
            'slug' => Slugs::unique(Page::class, $data['slug'] ?? $data['title']),
            'template' => $data['template'] ?? 'standard',
            'status' => PageStatus::Draft,
        ]);

        $this->audit($user, $page, 'page.created');

        return $page;
    }

    /** @param array<string, mixed> $data */
    public function update(Page $page, array $data, ?User $user = null): Page
    {
        $originalSlug = $page->slug;

        if (isset($data['slug']) && $data['slug'] !== $page->slug) {
            $data['slug'] = Slugs::unique(Page::class, $data['slug'], $page->id);
        }

        $page->fill($data)->save();

        if (isset($data['slug']) && $data['slug'] !== $originalSlug) {
            $this->recordRedirect($originalSlug, $data['slug']);
        }

        $this->audit($user, $page, 'page.updated', ['fields' => array_keys($data)]);

        return $page;
    }

    public function publish(Page $page, ?User $user = null): PageVersion
    {
        return $this->publisher->publish($page, $user);
    }

    public function unpublish(Page $page): void
    {
        $page->forceFill(['status' => PageStatus::Draft])->save();
    }

    public function restore(Page $page, int $versionId, ?User $user = null): void
    {
        $version = PageVersion::query()
            ->where('page_id', $page->id)
            ->findOrFail($versionId);

        $this->publisher->restoreDraft($page, $version, $user);
    }

    /** @param array<string, mixed> $sections */
    public function writeDraft(Page $page, array $sections): void
    {
        $this->publisher->writeDraft($page, $sections);
    }

    private function recordRedirect(string $fromSlug, string $toSlug): void
    {
        if ($fromSlug === 'home') {
            return;
        }

        Redirect::query()->updateOrCreate(
            ['from_path' => trim($fromSlug, '/')],
            ['to_path' => trim($toSlug, '/'), 'status_code' => 301],
        );
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
            'user_agent' => Str::limit((string) request()->userAgent(), 500),
        ]);
    }
}
