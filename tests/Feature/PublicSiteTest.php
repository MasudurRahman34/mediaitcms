<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Enums\PlacementLocation;
use App\Models\Article;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    public function test_home_page_is_publicly_accessible(): void
    {
        $this->publishedPage('home', [
            ['type' => 'hero', 'content' => ['heading' => 'Home hero heading']],
        ]);

        $this->get('/')->assertOk()->assertSee('Home hero heading');
    }

    public function test_about_page_is_publicly_accessible(): void
    {
        $this->publishedPage('about', [
            ['type' => 'rich_text', 'content' => ['heading' => 'About heading']],
        ]);

        $this->get('/about')->assertOk()->assertSee('About heading');
    }

    public function test_services_index_and_detail_pages_work(): void
    {
        $service = $this->service(['title' => 'Custom Software']);

        $this->get('/services')->assertOk()->assertSee('Custom Software');
        $this->get('/services/'.$service->slug)->assertOk()->assertSee('Custom Software');
    }

    public function test_draft_services_return_404(): void
    {
        $service = $this->service([
            'slug' => 'draft-service',
            'status' => ContentStatus::Draft,
        ]);

        $this->get('/services/'.$service->slug)->assertNotFound();
    }

    public function test_solutions_index_and_detail_pages_work(): void
    {
        $solution = $this->solution(['title' => 'Print Platform']);

        $this->get('/solutions')->assertOk()->assertSee('Print Platform');
        $this->get('/solutions/'.$solution->slug)->assertOk()->assertSee('Print Platform');
    }

    public function test_draft_solutions_return_404(): void
    {
        $solution = $this->solution([
            'slug' => 'draft-solution',
            'status' => ContentStatus::Draft,
        ]);

        $this->get('/solutions/'.$solution->slug)->assertNotFound();
    }

    public function test_news_listing_and_detail_pages_work(): void
    {
        $article = $this->article(['title' => 'Published article title']);

        $this->get('/news')->assertOk()->assertSee('Published article title');
        $this->get('/news/'.$article->slug)->assertOk()->assertSee('Published article title');
    }

    public function test_draft_articles_are_not_public(): void
    {
        $article = $this->article([
            'slug' => 'draft-article',
            'status' => ContentStatus::Draft,
        ]);

        $this->get('/news/'.$article->slug)->assertNotFound();
        $this->get('/news')->assertDontSee('Draft article title');
    }

    public function test_future_dated_articles_are_not_public(): void
    {
        $article = $this->article([
            'slug' => 'scheduled-article',
            'published_at' => now()->addWeek(),
        ]);

        $this->get('/news/'.$article->slug)->assertNotFound();
    }

    public function test_article_search_filters_results(): void
    {
        $this->article(['title' => 'Kubernetes deployment patterns', 'slug' => 'k8s']);
        $this->article(['title' => 'Designing accessible forms', 'slug' => 'forms']);

        $this->get('/news?q=Kubernetes')
            ->assertOk()
            ->assertSee('Kubernetes deployment patterns')
            ->assertDontSee('Designing accessible forms');
    }

    public function test_careers_listing_and_detail_work(): void
    {
        $job = $this->job(['title' => 'Senior Engineer']);

        $this->get('/careers')->assertOk()->assertSee('Senior Engineer');
        $this->get('/careers/'.$job->slug)->assertOk()->assertSee('Senior Engineer');
    }

    public function test_contact_page_is_publicly_accessible(): void
    {
        $this->get('/contact')->assertOk()->assertSee('Contact');
    }

    public function test_article_placements_surface_one_record_in_multiple_locations(): void
    {
        $featured = $this->article([
            'title' => 'Featured placement article',
            'slug' => 'featured-placement',
            'published_at' => now()->subDays(3),
        ]);

        $news = $this->article([
            'title' => 'News only article',
            'slug' => 'news-only',
            'published_at' => now()->subDay(),
        ]);

        $this->placeArticle($featured, PlacementLocation::HomeFeatured->value);
        $this->placeArticle($featured, PlacementLocation::News->value);
        $this->placeArticle($news, PlacementLocation::News->value);

        // One record, no duplicates in the database.
        $this->assertSame(1, Article::query()->where('slug', 'featured-placement')->count());

        // Home shows the article placed in the Home Featured slot.
        $this->publishedPage('home', [
            ['type' => 'articles_grid', 'content' => ['source' => 'home_featured', 'limit' => 3, 'heading' => 'Featured']],
        ]);

        $this->get('/')->assertOk()->assertSee('Featured placement article');
    }

    public function test_sitemap_lists_only_published_content(): void
    {
        $published = $this->service(['title' => 'Published service', 'slug' => 'published-service']);
        $draft = $this->service(['title' => 'Draft service', 'slug' => 'draft-service-2', 'status' => ContentStatus::Draft]);

        $response = $this->get('/sitemap.xml')->assertOk();

        $response->assertSee($published->slug);
        $response->assertDontSee($draft->slug);
    }

    public function test_robots_txt_disallows_admin(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /admin');
    }

    public function test_pages_render_seo_metadata(): void
    {
        $this->publishedPage('home', [['type' => 'hero', 'content' => ['heading' => 'SEO home']]]);

        $response = $this->get('/');

        $response->assertSee('<title>', false);
        $response->assertSee('name="description"', false);
        $response->assertSee('rel="canonical"', false);
        $response->assertSee('og:title', false);
        $response->assertSee('application/ld+json', false);
    }

    public function test_article_pages_emit_article_structured_data(): void
    {
        $article = $this->article(['title' => 'Structured data article', 'slug' => 'structured']);

        $this->get('/news/'.$article->slug)
            ->assertOk()
            ->assertSee('"@type":"Article"', false);
    }

    public function test_job_pages_emit_job_posting_structured_data(): void
    {
        $job = $this->job(['title' => 'Structured job', 'slug' => 'structured-job']);

        $this->get('/careers/'.$job->slug)
            ->assertOk()
            ->assertSee('"@type":"JobPosting"', false);
    }

    public function test_unknown_routes_return_404(): void
    {
        $this->get('/this-does-not-exist')->assertNotFound();
    }
}
