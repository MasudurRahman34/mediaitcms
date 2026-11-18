<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticlePlacement;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\NavigationMenu;
use App\Services\SettingsRepository;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminContentTest extends TestCase
{
    public function test_service_can_be_created_updated_and_deleted(): void
    {
        $admin = $this->admin();

        $create = $this->actingAs($admin)->postJson('/admin/api/services', [
            'title' => 'Cloud Engineering',
            'excerpt' => 'Cloud work.',
            'description' => '<p>Description</p>',
            'status' => 'published',
            'is_featured' => true,
        ])->assertCreated();

        $id = $create->json('data.id');
        $this->assertDatabaseHas('services', ['id' => $id, 'title' => 'Cloud Engineering']);

        $this->actingAs($admin)->putJson('/admin/api/services/'.$id, [
            'title' => 'Cloud & Platform Engineering',
        ])->assertOk();

        $this->assertDatabaseHas('services', ['id' => $id, 'title' => 'Cloud & Platform Engineering']);

        $this->actingAs($admin)->deleteJson('/admin/api/services/'.$id)->assertOk();
        $this->assertDatabaseMissing('services', ['id' => $id]);
    }

    public function test_solution_features_and_screenshots_round_trip(): void
    {
        $admin = $this->admin();
        $media = $this->media();

        $response = $this->actingAs($admin)->postJson('/admin/api/solutions', [
            'title' => 'Atlas Ops',
            'intro' => 'Field operations.',
            'status' => 'published',
            'features' => [
                ['title' => 'Offline sync', 'description' => '<p>Works offline</p>', 'media_id' => $media->id],
            ],
            'technology_ids' => [],
        ])->assertCreated();

        $solutionId = $response->json('data.id');

        $this->actingAs($admin)
            ->postJson('/admin/api/solutions/'.$solutionId.'/screenshots', ['media_id' => $media->id, 'caption' => 'Dash'])
            ->assertCreated();

        $detail = $this->actingAs($admin)
            ->getJson('/admin/api/solutions/'.$solutionId)
            ->assertOk();

        $this->assertCount(1, $detail->json('data.features'));
        $this->assertCount(1, $detail->json('data.screenshots'));
    }

    public function test_article_creation_persists_placements_across_locations(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->postJson('/admin/api/articles', [
            'title' => 'Placement article',
            'excerpt' => 'Excerpt',
            'body' => '<p>Body</p>',
            'status' => 'published',
        ])->assertCreated();

        $articleId = $response->json('data.id');

        $this->actingAs($admin)->putJson('/admin/api/articles/'.$articleId.'/placements', [
            'placements' => [
                ['location' => 'news'],
                ['location' => 'home_featured'],
            ],
        ])->assertOk();

        $this->assertSame(1, Article::query()->whereKey($articleId)->count());
        $this->assertSame(2, ArticlePlacement::query()->where('article_id', $articleId)->count());
    }

    public function test_placements_can_be_removed(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->postJson('/admin/api/articles', [
            'title' => 'Removable placement',
            'status' => 'published',
        ])->assertCreated();

        $articleId = $response->json('data.id');

        $this->actingAs($admin)->putJson('/admin/api/articles/'.$articleId.'/placements', [
            'placements' => [['location' => 'news'], ['location' => 'home_latest']],
        ])->assertOk();

        $this->actingAs($admin)->putJson('/admin/api/articles/'.$articleId.'/placements', [
            'placements' => [['location' => 'news']],
        ])->assertOk();

        $this->assertSame(1, ArticlePlacement::query()->where('article_id', $articleId)->count());
    }

    public function test_article_accepts_only_one_placement_per_location(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->postJson('/admin/api/articles', [
            'title' => 'Duplicate placement',
            'status' => 'published',
        ]);

        $articleId = $response->json('data.id');

        $this->actingAs($admin)->putJson('/admin/api/articles/'.$articleId.'/placements', [
            'placements' => [['location' => 'news'], ['location' => 'news']],
        ])->assertOk();

        $this->assertSame(1, ArticlePlacement::query()->where('article_id', $articleId)->count());
    }

    public function test_media_can_be_uploaded_and_in_use_files_cannot_be_deleted(): void
    {
        $admin = $this->admin();

        $upload = $this->actingAs($admin)->postJson('/admin/api/media', [
            'file' => UploadedFile::fake()->image('photo.jpg', 900, 600),
            'folder' => 'uploads',
        ])->assertCreated();

        $mediaId = $upload->json('data.id');
        $this->assertDatabaseHas('media', ['id' => $mediaId]);

        // Attach it to content, then deletion must be refused.
        $service = $this->service(['cover_media_id' => $mediaId]);
        $this->assertSame($mediaId, $service->cover_media_id);

        $this->actingAs($admin)
            ->deleteJson('/admin/api/media/'.$mediaId)
            ->assertStatus(422);

        $this->assertDatabaseHas('media', ['id' => $mediaId]);
    }

    public function test_unused_media_can_be_deleted(): void
    {
        $admin = $this->admin();

        $upload = $this->actingAs($admin)->postJson('/admin/api/media', [
            'file' => UploadedFile::fake()->image('unused.jpg', 400, 400),
        ])->assertCreated();

        $mediaId = $upload->json('data.id');

        $this->actingAs($admin)->deleteJson('/admin/api/media/'.$mediaId)->assertOk();
        $this->assertDatabaseMissing('media', ['id' => $mediaId]);
    }

    public function test_media_alt_text_can_be_updated(): void
    {
        $admin = $this->admin();
        $media = $this->media();

        $this->actingAs($admin)->putJson('/admin/api/media/'.$media->id, [
            'alt' => 'Descriptive alt text',
            'title' => 'A title',
        ])->assertOk();

        $this->assertSame('Descriptive alt text', $media->fresh()->alt);
    }

    public function test_settings_can_be_updated(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->putJson('/admin/api/settings', [
            'settings' => [
                'company_name' => 'Media IT Soft',
                'contact_email' => 'hello@mediaitsoft.co.uk',
            ],
        ])->assertOk();

        $this->assertDatabaseHas('settings', ['key' => 'contact_email']);

        app(SettingsRepository::class)->flush();
        $this->assertSame('hello@mediaitsoft.co.uk', app(SettingsRepository::class)->get('contact_email'));
    }

    public function test_job_can_be_created_and_closed(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->postJson('/admin/api/jobs', [
            'title' => 'Platform Engineer',
            'location' => 'Remote',
            'employment_type' => 'full-time',
            'status' => 'open',
            'description' => '<p>Description</p>',
        ])->assertCreated();

        $jobId = $response->json('data.id');

        $this->actingAs($admin)->putJson('/admin/api/jobs/'.$jobId, [
            'status' => 'closed',
        ])->assertOk();

        $this->assertSame('closed', Job::query()->find($jobId)->status->value);
    }

    public function test_audit_log_records_application_status_changes(): void
    {
        $admin = $this->admin();

        $application = JobApplication::query()->create([
            'job_id' => $this->job()->id,
            'first_name' => 'Audit',
            'last_name' => 'Trail',
            'email' => 'audit@example.com',
            'cv_path' => 'applicant-documents/a.pdf',
            'cv_original_name' => 'a.pdf',
            'cv_mime' => 'application/pdf',
            'cv_size' => 10,
            'status' => 'new',
        ]);

        $this->actingAs($admin)
            ->patchJson('/admin/api/applications/'.$application->id, ['status' => 'reviewing'])
            ->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'application.status-updated',
            'auditable_id' => $application->id,
        ]);
    }

    public function test_admin_dashboard_returns_statistics(): void
    {
        $this->service();
        $this->solution();
        $this->article();
        $this->job();

        $response = $this->actingAs($this->admin())
            ->getJson('/admin/api/dashboard')
            ->assertOk();

        $this->assertSame(1, $response->json('stats.services'));
        $this->assertSame(1, $response->json('stats.solutions'));
        $this->assertSame(1, $response->json('stats.articles'));
        $this->assertSame(1, $response->json('stats.active_jobs'));
    }

    public function test_navigation_menus_can_be_updated(): void
    {
        $admin = $this->admin();

        $menu = NavigationMenu::query()->create(['key' => 'header', 'name' => 'Header']);

        $this->actingAs($admin)->putJson('/admin/api/navigation/'.$menu->id, [
            'items' => [
                ['label' => 'Home', 'url' => '/'],
                [
                    'label' => 'Services',
                    'url' => '/services',
                    'children' => [['label' => 'Cloud', 'url' => '/services/cloud']],
                ],
            ],
        ])->assertOk();

        $this->assertSame(2, $menu->items()->whereNull('parent_id')->count());
        $this->assertSame(1, $menu->items()->whereNotNull('parent_id')->count());
    }

    public function test_validation_errors_are_returned_as_json(): void
    {
        $this->actingAs($this->admin())
            ->postJson('/admin/api/services', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('title');
    }
}
