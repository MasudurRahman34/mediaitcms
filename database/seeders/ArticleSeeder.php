<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Enums\PlacementLocation;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\ArticlePlacement;
use App\Models\ArticleTag;
use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect([
            'Engineering' => 'How we build and ship software.',
            'AI & Automation' => 'Practical notes on applied AI.',
            'Cloud' => 'Infrastructure, reliability and cost.',
            'Design' => 'Product design and accessibility.',
        ])->mapWithKeys(fn ($description, $name) => [
            $name => ArticleCategory::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'description' => $description],
            ),
        ]);

        $author = User::query()->where('email', 'editor@mediaitsoft.co.uk')->first()
            ?? User::query()->first();

        $mediaId = fn (string $key) => MediaSeeder::id($key);

        $articles = [
            [
                'title' => 'Shipping AI features that people actually trust',
                'excerpt' => 'Confidence scores are not a user experience. Here is how we design AI features with review loops, citations and graceful failure.',
                'category' => 'AI & Automation',
                'tags' => ['AI', 'Product', 'UX'],
                'media' => 'article-ai',
                'placements' => [PlacementLocation::HomeFeatured, PlacementLocation::HomeLatest],
                'body' => '<p>Most AI features fail for a boring reason: nobody accounted for being wrong. A model that is right 92% of the time sounds impressive until you remember the other 8% reaches a customer.</p><h2>Design for the wrong answer first</h2><p>Before we write a prompt, we write down what happens when the output is incorrect. Is it recoverable? Is it visible? Can the user tell? That single exercise changes the interface more than any model choice.</p><h2>Three patterns that work</h2><ul><li><strong>Cite the source.</strong> If the answer came from a document, show the clause. Verification takes seconds instead of minutes.</li><li><strong>Surface uncertainty where it matters.</strong> Not a percentage badge — route low-confidence output to a review queue.</li><li><strong>Make correction cheap.</strong> The user should be able to fix the output inline, and that correction should be captured as evaluation data.</li></ul><h2>Measure before you scale</h2><p>We build a small evaluation set before the prototype. Twenty judged examples will tell you more about whether a prompt change helped than a month of anecdotal feedback.</p>',
            ],
            [
                'title' => 'The case against a microservices-first architecture',
                'excerpt' => 'For most teams under fifty engineers, a well-structured monolith ships faster and costs less. Here is where we draw the line.',
                'category' => 'Engineering',
                'tags' => ['Architecture', 'Laravel', 'Scaling'],
                'media' => 'article-legacy',
                'placements' => [PlacementLocation::HomeLatest],
                'body' => '<p>Microservices solve an organisational problem, not a technical one. If you have fewer than a handful of teams shipping independently, you are likely paying coordination costs without getting the benefit.</p><h2>What a monolith actually gives you</h2><ul><li>Database transactions across your domain, without sagas</li><li>One deployment, one log stream, one place to look when something breaks</li><li>Refactoring across boundaries while they are still cheap to move</li></ul><h2>When to split</h2><p>Split when a specific part of the system has a genuinely different scaling profile, a different compliance boundary, or a team that needs to deploy several times a day without waiting.</p><p>We recently moved a client from eleven services back to a single Laravel application with a queue worker. Infrastructure cost fell 61% and deploy time went from fourteen minutes to ninety seconds.</p>',
            ],
            [
                'title' => 'Core Web Vitals are an engineering problem',
                'excerpt' => 'Server rendering, image pipelines and font strategy will do more for your Lighthouse scores than any plugin.',
                'category' => 'Cloud',
                'tags' => ['Performance', 'SEO'],
                'media' => 'article-cloud',
                'placements' => [PlacementLocation::HomeLatest],
                'body' => '<p>Every performance audit we run starts the same way: the largest contentful paint element is a hero image served at 2400px wide on a 390px screen.</p><h2>The highest-leverage fixes</h2><ul><li><strong>Responsive images.</strong> Generate real width variants and serve them with <code>srcset</code> and correct <code>sizes</code>.</li><li><strong>Server-render content.</strong> A client-side rendered marketing page will always lose to a Blade response.</li><li><strong>Self-host fonts.</strong> One preloaded variable font file beats three requests to a third-party CDN.</li><li><strong>Split your bundles.</strong> Admin JavaScript has no business on a public page.</li></ul><h2>Measure the field, not the lab</h2><p>Lab scores move first. Real-user metrics — LCP at the 75th percentile — are what search engines and your customers actually experience.</p>',
            ],
            [
                'title' => 'Design systems that survive contact with engineering',
                'excerpt' => 'A component library is only useful if it encodes decisions. Token-first systems age far better than page-level design files.',
                'category' => 'Design',
                'tags' => ['Design Systems', 'Accessibility'],
                'media' => 'article-design',
                'placements' => [],
                'body' => '<p>The design systems that last share one trait: they encode decisions, not pixels. Spacing, colour and type are defined once as tokens; components consume tokens rather than hardcoded values.</p><h2>What we put in the first version</h2><ul><li>A colour palette with semantic names, not <code>blue-500</code></li><li>A type scale using fluid <code>clamp()</code> values</li><li>Focus states defined alongside hover states</li><li>Two or three layout primitives instead of twelve card variants</li></ul><p>Accessibility is not a later phase. Contrast, focus visibility and reduced-motion handling belong in the tokens, because that is the only place you can enforce them.</p>',
            ],
            [
                'title' => 'Securing file uploads in a CMS',
                'excerpt' => 'CVs, artwork and documents are the most commonly exploited part of any content-managed site. A practical checklist.',
                'category' => 'Engineering',
                'tags' => ['Security', 'Laravel'],
                'media' => 'article-security',
                'placements' => [],
                'body' => '<p>Applicant CVs are personally identifiable information and often the weakest link in a recruitment workflow. Treat them as private data, not uploads.</p><h2>The checklist</h2><ul><li>Validate MIME type and extension server-side, never trust the client</li><li>Randomise filenames; never derive paths from user input</li><li>Store on a private disk with no public URL</li><li>Serve downloads through an authorising controller, and log every access</li><li>Set size limits and rate-limit the endpoint</li></ul><p>Also sanitise rich text on write and again on render. HTMLPurifier with an allowlist is unglamorous and effective.</p>',
            ],
            [
                'title' => 'Legacy modernisation without a big-bang rewrite',
                'excerpt' => 'Strangler-fig patterns let you replace a legacy system incrementally, with value delivered every few weeks.',
                'category' => 'Engineering',
                'tags' => ['Modernisation', 'Strategy'],
                'media' => 'article-legacy',
                'placements' => [],
                'body' => '<p>Full rewrites fail predictably: the business changes underneath you while the new system is invisible. We prefer to strangle the old system one capability at a time.</p><h2>How it works in practice</h2><ol><li>Put a thin routing layer in front of the legacy system</li><li>Rebuild one vertical slice in the new stack</li><li>Route only that slice to the new code</li><li>Repeat, measuring each migration</li></ol><p>The legacy system keeps earning while it shrinks, and every milestone is a working release rather than a status update.</p>',
            ],
            [
                'title' => 'What we look for when hiring engineers',
                'excerpt' => 'Not leetcode. We care about how you reason about trade-offs, communicate uncertainty and handle being wrong.',
                'category' => 'Engineering',
                'tags' => ['Careers', 'Culture'],
                'media' => 'article-team',
                'placements' => [],
                'body' => '<p>Our interview process is deliberately short: a conversation about your experience, a practical pairing session on a realistic problem, and a discussion about trade-offs.</p><h2>What we are listening for</h2><ul><li>Can you explain a decision you would now make differently?</li><li>Do you ask clarifying questions before writing code?</li><li>How do you communicate uncertainty to a non-technical stakeholder?</li></ul><p>We do not ask anyone to invert a binary tree on a whiteboard. We do ask you to review a pull request and say what you would change.</p>',
            ],
            [
                'title' => 'Data platforms for teams that are not data companies',
                'excerpt' => 'You do not need a lakehouse to answer four business questions. Start with the questions, then build the smallest pipeline.',
                'category' => 'Cloud',
                'tags' => ['Data', 'Analytics'],
                'media' => 'article-data',
                'placements' => [],
                'body' => '<p>Most reporting problems are definition problems wearing a technology costume. Two teams disagree about what "active customer" means, so no warehouse will fix the dashboard.</p><h2>Start here</h2><ol><li>Write the four questions the business actually asks weekly</li><li>Agree the definitions in writing</li><li>Build the smallest pipeline that answers them reliably</li><li>Add governance only when someone needs it</li></ol><p>A scheduled query writing to a well-modelled table beats an unfinished lakehouse every time.</p>',
            ],
        ];

        foreach ($articles as $index => $definition) {
            $article = Article::query()->updateOrCreate(
                ['slug' => Str::slug($definition['title'])],
                [
                    'title' => $definition['title'],
                    'excerpt' => $definition['excerpt'],
                    'body' => $definition['body'],
                    'featured_media_id' => $mediaId($definition['media']),
                    'author_id' => $author?->id,
                    'status' => ContentStatus::Published,
                    'published_at' => now()->subDays(($index + 1) * 6),
                    'is_featured' => in_array(PlacementLocation::HomeFeatured, $definition['placements'], true),
                    'read_time' => max(3, (int) round(str_word_count(strip_tags($definition['body'])) / 200)),
                    'seo_title' => $definition['title'].' — Media IT Soft',
                    'seo_description' => Str::limit($definition['excerpt'], 155),
                ],
            );

            $article->categories()->sync([$categories[$definition['category']]->id]);

            $article->tags()->sync(
                collect($definition['tags'])->map(fn ($tag) => ArticleTag::query()->updateOrCreate(
                    ['slug' => Str::slug($tag)],
                    ['name' => $tag],
                )->id)->all(),
            );

            ArticlePlacement::query()->where('article_id', $article->id)->delete();

            foreach ($definition['placements'] as $order => $location) {
                ArticlePlacement::query()->create([
                    'article_id' => $article->id,
                    'location' => $location,
                    'sort_order' => $order,
                ]);
            }
        }
    }
}
