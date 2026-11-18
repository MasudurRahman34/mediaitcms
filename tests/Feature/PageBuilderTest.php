<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Services\Pages\PagePublisher;
use App\Services\Sections\SectionRegistry;
use Tests\TestCase;

class PageBuilderTest extends TestCase
{
    public function test_home_page_renders_the_hero_section(): void
    {
        $this->publishedPage('home', [
            [
                'type' => 'hero',
                'content' => [
                    'heading' => 'Unique home headline for testing',
                    'subheading' => 'Supporting copy.',
                    'primary_cta' => ['label' => 'Get started', 'url' => '/contact'],
                ],
            ],
        ]);

        $this->get('/')->assertOk()->assertSee('Unique home headline for testing');
    }

    public function test_draft_pages_are_not_publicly_visible(): void
    {
        Page::query()->create(['slug' => 'secret-draft', 'title' => 'Secret', 'template' => 'standard']);

        $this->get('/secret-draft')->assertNotFound();
    }

    public function test_publishing_makes_a_page_public(): void
    {
        $page = Page::query()->create([
            'slug' => 'pricing',
            'title' => 'Pricing',
            'template' => 'standard',
        ]);

        $this->get('/pricing')->assertNotFound();

        app(PagePublisher::class)->writeDraft($page, [
            ['type' => 'rich_text', 'content' => ['heading' => 'Simple pricing', 'body' => '<p>Body copy</p>']],
        ]);
        app(PagePublisher::class)->publish($page);

        $this->get('/pricing')->assertOk()->assertSee('Simple pricing');
    }

    public function test_publishing_creates_an_immutable_version_snapshot(): void
    {
        $page = Page::query()->create(['slug' => 'about', 'title' => 'About', 'template' => 'about']);

        app(PagePublisher::class)->writeDraft($page, [
            ['type' => 'rich_text', 'content' => ['heading' => 'Version one']],
        ]);
        $version = app(PagePublisher::class)->publish($page);

        $this->assertSame(1, $version->version_number);
        $this->assertSame('Version one', $version->payload[0]['content']['heading']);
        $this->assertSame($version->id, $page->fresh()->published_version_id);

        // Editing the draft must not mutate the published version.
        app(PagePublisher::class)->writeDraft($page, [
            ['type' => 'rich_text', 'content' => ['heading' => 'Version two']],
        ]);

        $this->assertSame('Version one', $version->fresh()->payload[0]['content']['heading']);
        $this->get('/about')->assertSee('Version one')->assertDontSee('Version two');
    }

    public function test_restoring_a_version_replaces_the_draft(): void
    {
        $page = Page::query()->create(['slug' => 'restore-me', 'title' => 'Restore', 'template' => 'standard']);

        app(PagePublisher::class)->writeDraft($page, [
            ['type' => 'rich_text', 'content' => ['heading' => 'Original heading']],
        ]);
        $version = app(PagePublisher::class)->publish($page);

        app(PagePublisher::class)->writeDraft($page, [
            ['type' => 'rich_text', 'content' => ['heading' => 'Changed heading']],
        ]);

        app(PagePublisher::class)->restoreDraft($page, $version);

        $this->assertSame('Original heading', $page->fresh()->draftSections()->first()->content['heading']);
    }

    public function test_disabled_sections_are_not_rendered(): void
    {
        $this->publishedPage('home', [
            ['type' => 'hero', 'content' => ['heading' => 'Visible headline']],
            ['type' => 'rich_text', 'is_enabled' => false, 'content' => ['heading' => 'Hidden headline']],
        ]);

        $this->get('/')->assertSee('Visible headline')->assertDontSee('Hidden heading');
    }

    public function test_section_order_is_preserved_in_output(): void
    {
        $this->publishedPage('home', [
            ['type' => 'rich_text', 'sort_order' => 0, 'content' => ['heading' => 'First section']],
            ['type' => 'rich_text', 'sort_order' => 1, 'content' => ['heading' => 'Second section']],
        ]);

        $html = $this->get('/')->getContent();

        $this->assertLessThan(
            strpos($html, 'Second section'),
            strpos($html, 'First section'),
        );
    }

    public function test_every_registered_section_type_renders_without_error(): void
    {
        $registry = app(SectionRegistry::class);
        $media = $this->media();

        foreach ($registry->types() as $type) {
            $defaults = $registry->defaults($type);

            // Provide plausible content so list-driven sections have data.
            $defaults = array_merge($defaults, [
                'heading' => $type.' heading',
                'body' => '<p>Body</p>',
                'image' => $type === 'hero' ? $media->id : null,
                'items' => [],
            ]);

            $slug = 'section-'.str_replace('_', '-', $type);
            $this->publishedPage($slug, [['type' => $type, 'content' => $defaults]]);

            $this->get('/'.$slug)->assertOk();
        }
    }

    public function test_invalid_section_payload_is_rejected(): void
    {
        $page = Page::query()->create(['slug' => 'validated', 'title' => 'Validated', 'template' => 'standard']);

        $this->actingAs($this->admin())
            ->putJson('/admin/api/pages/'.$page->id.'/sections', [
                'sections' => [
                    ['type' => 'not-a-real-section', 'content' => []],
                ],
            ])
            ->assertStatus(422);
    }

    public function test_admin_can_sync_sections_and_publish(): void
    {
        $admin = $this->admin();
        $page = Page::query()->create(['slug' => 'managed', 'title' => 'Managed', 'template' => 'standard']);

        $this->actingAs($admin)
            ->putJson('/admin/api/pages/'.$page->id.'/sections', [
                'sections' => [
                    ['type' => 'rich_text', 'is_enabled' => true, 'content' => ['heading' => 'Managed heading', 'body' => '<p>Managed body</p>']],
                ],
            ])
            ->assertOk();

        $this->actingAs($admin)
            ->postJson('/admin/api/pages/'.$page->id.'/publish')
            ->assertOk();

        $this->get('/managed')->assertOk()->assertSee('Managed heading');
    }

    public function test_rich_text_is_sanitised_on_save(): void
    {
        $admin = $this->admin();
        $page = Page::query()->create(['slug' => 'sanitised', 'title' => 'Sanitised', 'template' => 'standard']);

        $this->actingAs($admin)
            ->putJson('/admin/api/pages/'.$page->id.'/sections', [
                'sections' => [
                    [
                        'type' => 'rich_text',
                        'content' => [
                            'heading' => 'XSS test',
                            'body' => '<p>Safe</p><script>alert(1)</script><img src=x onerror=alert(1)>',
                        ],
                    ],
                ],
            ])
            ->assertOk();

        $stored = $page->fresh()->draftSections()->first()->content['body'];

        $this->assertStringNotContainsString('<script', $stored);
        $this->assertStringNotContainsString('onerror', $stored);
    }

    public function test_changing_a_page_slug_creates_a_redirect(): void
    {
        $admin = $this->admin();
        $page = Page::query()->create(['slug' => 'old-url', 'title' => 'Old', 'template' => 'standard']);

        app(PagePublisher::class)->writeDraft($page, [['type' => 'rich_text', 'content' => ['heading' => 'Moved']]]);
        app(PagePublisher::class)->publish($page);

        $this->actingAs($admin)
            ->putJson('/admin/api/pages/'.$page->id, ['slug' => 'new-url'])
            ->assertOk();

        $this->assertDatabaseHas('redirects', ['from_path' => 'old-url', 'to_path' => 'new-url']);
        $this->get('/old-url')->assertRedirect('/new-url');
    }
}
