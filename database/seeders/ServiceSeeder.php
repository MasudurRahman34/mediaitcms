<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'title' => 'Custom Software Development',
                'icon' => 'code',
                'excerpt' => 'Web platforms, internal tools and APIs engineered to your exact process — built to last, not to demo.',
                'description' => '<p>We design and build production software that fits the way your business actually works. That means understanding the process first, then choosing the simplest architecture that will still hold up in three years.</p><h2>What we build</h2><ul><li>Customer-facing web platforms and portals</li><li>Internal operations and workflow tools</li><li>APIs and third-party integrations</li><li>Legacy system replacements and modernisation</li></ul><p>Every project ships with automated tests, documentation and a deployment pipeline — so your team is never locked out of their own product.</p>',
                'featured' => true,
                'benefits' => [
                    ['title' => 'Senior-only delivery', 'description' => 'Every line of code is written by engineers with 8+ years of production experience.'],
                    ['title' => 'Fixed, visible scope', 'description' => 'You see the backlog, the estimates and the progress in a shared board from day one.'],
                    ['title' => 'You own everything', 'description' => 'Source code, infrastructure and documentation are yours from the first commit.'],
                ],
                'faqs' => [
                    ['question' => 'How long does a typical build take?', 'answer' => '<p>Most first versions take 8–14 weeks. We scope a milestone plan up front so you have something usable early, then iterate.</p>'],
                    ['question' => 'Do you work with our existing team?', 'answer' => '<p>Yes. We regularly embed with in-house teams, pairing on delivery and handing over knowledge as we go.</p>'],
                ],
            ],
            [
                'title' => 'AI & Automation Solutions',
                'icon' => 'sparkles',
                'excerpt' => 'Practical AI that removes real work — document processing, assistants and decision support with humans in control.',
                'description' => '<p>We build AI features that earn their place: automating the repetitive work your team does by hand today, with clear guardrails and auditability.</p><h2>Where we help most</h2><ul><li>Document extraction, classification and drafting</li><li>Domain assistants over your own knowledge base</li><li>Workflow automation with human review steps</li><li>Model evaluation and safety guardrails</li></ul><p>We are pragmatic about models: the right answer is often a well-designed retrieval pipeline and a small, evaluated model — not the largest one available.</p>',
                'featured' => true,
                'benefits' => [
                    ['title' => 'Measured outcomes', 'description' => 'We agree the baseline and measure accuracy, time saved and cost per task before scaling.'],
                    ['title' => 'Human in the loop', 'description' => 'Confidence thresholds and review queues keep people in control of high-stakes decisions.'],
                    ['title' => 'Your data stays yours', 'description' => 'Private deployment options, no training on your content, and full audit trails.'],
                ],
                'faqs' => [
                    ['question' => 'Will our data be used to train models?', 'answer' => '<p>No. We use API providers with zero-retention terms or deploy open models inside your own infrastructure.</p>'],
                    ['question' => 'Can you improve an AI feature we already have?', 'answer' => '<p>Often, yes. We start with an evaluation set so improvements are measurable rather than anecdotal.</p>'],
                ],
            ],
            [
                'title' => 'Cloud & DevOps Engineering',
                'icon' => 'cloud',
                'excerpt' => 'Infrastructure as code, CI/CD pipelines and observability so releases stop being an event.',
                'description' => '<p>We make delivery boring in the best way: reproducible environments, automated pipelines and monitoring that tells you about problems before customers do.</p><h2>Typical engagements</h2><ul><li>Infrastructure as code on AWS or Azure</li><li>Containerisation and orchestration</li><li>CI/CD pipelines with quality gates</li><li>Cost and performance optimisation</li></ul>',
                'featured' => true,
                'benefits' => [
                    ['title' => 'Deploy on any day', 'description' => 'Small, reversible releases with automated rollback.'],
                    ['title' => 'Predictable cost', 'description' => 'Right-sized infrastructure with tagging and budget alerts.'],
                    ['title' => 'No mystery incidents', 'description' => 'Dashboards, alerts and runbooks your team can actually use.'],
                ],
                'faqs' => [
                    ['question' => 'Do you support on-premise?', 'answer' => '<p>Yes — we run the same practices on Kubernetes or virtualised infrastructure inside your network.</p>'],
                ],
            ],
            [
                'title' => 'IT Support & Managed Services',
                'icon' => 'shield',
                'excerpt' => 'Responsive support and proactive maintenance for the systems your business runs on.',
                'description' => '<p>A named team that knows your stack, with clear response targets and monthly reporting. No anonymous ticket queues.</p><h2>Included</h2><ul><li>Helpdesk support for your team</li><li>Server, network and endpoint monitoring</li><li>Patching, backups and disaster recovery</li><li>Security reviews and access management</li></ul>',
                'featured' => false,
                'benefits' => [
                    ['title' => 'Named engineers', 'description' => 'The same people every time, who already understand your environment.'],
                    ['title' => 'Proactive, not reactive', 'description' => 'We fix the pattern behind repeat issues, not just the symptom.'],
                    ['title' => 'Clear SLAs', 'description' => 'Documented response and resolution targets, measured monthly.'],
                ],
                'faqs' => [],
            ],
            [
                'title' => 'Digital Product Design',
                'icon' => 'layers',
                'excerpt' => 'Research, UX and interface design that makes complex products feel obvious.',
                'description' => '<p>We design with engineers in the room, so what we design is what gets built. Discovery, prototyping and a design system your product can grow into.</p><h2>Our process</h2><ul><li>User research and journey mapping</li><li>Wireframes and interactive prototypes</li><li>Accessible interface design</li><li>Component libraries and design systems</li></ul>',
                'featured' => false,
                'benefits' => [
                    ['title' => 'Evidence-led', 'description' => 'Decisions are grounded in research and usability testing, not opinion.'],
                    ['title' => 'Accessible by default', 'description' => 'WCAG-minded contrast, focus states and semantics baked into components.'],
                ],
                'faqs' => [],
            ],
            [
                'title' => 'SEO & Performance Engineering',
                'icon' => 'chart',
                'excerpt' => 'Technical SEO, Core Web Vitals and analytics done properly — measurable, not magical.',
                'description' => '<p>Search visibility is an engineering problem as much as a content one. We fix the technical foundations: server rendering, structured data, crawlability and page speed.</p><h2>What we cover</h2><ul><li>Technical SEO audits and remediation</li><li>Core Web Vitals and image pipeline work</li><li>Structured data and sitemap strategy</li><li>Analytics and conversion tracking</li></ul>',
                'featured' => false,
                'benefits' => [
                    ['title' => 'Real Core Web Vitals', 'description' => 'Field data improvements, not just lab scores.'],
                    ['title' => 'No dark patterns', 'description' => 'Sustainable technical SEO that survives algorithm updates.'],
                ],
                'faqs' => [],
            ],
        ];

        $covers = collect(['office', 'hero-team', 'solution-atlas', 'shot-analytics', 'article-design', 'article-cloud']);

        foreach ($services as $index => $definition) {
            $coverId = MediaSeeder::id($covers[$index % $covers->count()]);

            Service::query()->updateOrCreate(
                ['slug' => Str::slug($definition['title'])],
                [
                    'title' => $definition['title'],
                    'excerpt' => $definition['excerpt'],
                    'description' => $definition['description'],
                    'icon' => $definition['icon'],
                    'cover_media_id' => $coverId,
                    'benefits' => $definition['benefits'],
                    'faqs' => $definition['faqs'],
                    'status' => ContentStatus::Published,
                    'is_featured' => $definition['featured'],
                    'sort_order' => $index + 1,
                    'seo_title' => $definition['title'].' — Media IT Soft',
                    'seo_description' => Str::limit($definition['excerpt'], 155),
                ],
            );
        }
    }
}
