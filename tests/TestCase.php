<?php

namespace Tests;

use App\Enums\ContentStatus;
use App\Enums\JobStatus;
use App\Models\Article;
use App\Models\ArticlePlacement;
use App\Models\Job;
use App\Models\Media;
use App\Models\Page;
use App\Models\Role;
use App\Models\Service;
use App\Models\Solution;
use App\Models\User;
use App\Services\Pages\PagePublisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

abstract class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::fake('local');
    }

    protected function userWithRole(string $roleSlug = Role::SUPER_ADMIN): User
    {
        $role = Role::query()->firstOrCreate(
            ['slug' => $roleSlug],
            ['name' => ucfirst($roleSlug), 'description' => 'Test role', 'is_system' => true],
        );

        return User::query()->create([
            'name' => 'Test '.$roleSlug,
            'email' => $roleSlug.'-'.uniqid().'@example.com',
            'password' => Hash::make('Password!234'),
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }

    protected function admin(): User
    {
        return $this->userWithRole(Role::SUPER_ADMIN);
    }

    protected function media(string $name = 'image.png'): Media
    {
        return Media::query()->create([
            'disk' => 'public',
            'path' => 'demo/'.$name,
            'filename' => $name,
            'mime' => 'image/png',
            'size' => 1024,
            'width' => 1200,
            'height' => 800,
            'alt' => 'Test image',
            'folder' => 'demo',
        ]);
    }

    protected function service(array $overrides = []): Service
    {
        return Service::query()->create(array_merge([
            'slug' => 'test-service-'.uniqid(),
            'title' => 'Test Service',
            'excerpt' => 'A service used in tests.',
            'description' => '<p>Service body</p>',
            'icon' => 'code',
            'status' => ContentStatus::Published,
            'is_featured' => true,
            'sort_order' => 1,
        ], $overrides));
    }

    protected function solution(array $overrides = []): Solution
    {
        return Solution::query()->create(array_merge([
            'slug' => 'test-solution-'.uniqid(),
            'title' => 'Test Solution',
            'intro' => 'A solution used in tests.',
            'description' => '<p>Solution body</p>',
            'status' => ContentStatus::Published,
            'is_featured' => true,
            'sort_order' => 1,
        ], $overrides));
    }

    protected function article(array $overrides = []): Article
    {
        return Article::query()->create(array_merge([
            'slug' => 'test-article-'.uniqid(),
            'title' => 'Test Article',
            'excerpt' => 'An article used in tests.',
            'body' => '<p>Article body</p>',
            'status' => ContentStatus::Published,
            'published_at' => now()->subDay(),
            'read_time' => 3,
        ], $overrides));
    }

    protected function placeArticle(Article $article, string $location): ArticlePlacement
    {
        return ArticlePlacement::query()->create([
            'article_id' => $article->id,
            'location' => $location,
            'sort_order' => 0,
        ]);
    }

    protected function job(array $overrides = []): Job
    {
        return Job::query()->create(array_merge([
            'slug' => 'test-job-'.uniqid(),
            'title' => 'Test Job',
            'location' => 'Swindon',
            'employment_type' => 'full-time',
            'description' => '<p>Job description</p>',
            'requirements' => '<p>Requirements</p>',
            'status' => JobStatus::Open,
            'closing_at' => now()->addDays(14),
        ], $overrides));
    }

    /**
     * Create and publish a page with the given sections.
     *
     * @param  array<int, array<string, mixed>>  $sections
     */
    protected function publishedPage(string $slug, array $sections): Page
    {
        $page = Page::query()->create([
            'slug' => $slug,
            'title' => ucfirst($slug),
            'template' => in_array($slug, ['home', 'about'], true) ? $slug : 'standard',
        ]);

        app(PagePublisher::class)->writeDraft($page, $sections);
        app(PagePublisher::class)->publish($page);

        return $page->fresh();
    }
}
