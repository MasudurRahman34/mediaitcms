<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\Solution;
use App\Models\Technology;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SolutionSeeder extends Seeder
{
    public function run(): void
    {
        $technologies = [
            'Laravel', 'React', 'TypeScript', 'MySQL', 'Redis', 'AWS',
            'OpenAI', 'Python', 'Node.js', 'Tailwind CSS', 'Docker', 'Stripe',
        ];

        $techModels = collect($technologies)->mapWithKeys(fn ($name) => [
            $name => Technology::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            ),
        ]);

        $media = fn (string $key) => MediaSeeder::id($key);

        $solutions = [
            [
                'title' => 'PrintLab — Print on Demand Platform',
                'slug' => 'printlab-print-on-demand-platform',
                'intro' => 'A multi-tenant print-on-demand platform connecting customers, print partners and fulfilment into one order pipeline.',
                'description' => '<p>PrintLab replaced a patchwork of spreadsheets and email with a single platform covering product configuration, quoting, proofing and fulfilment.</p><h2>The problem</h2><p>Orders arrived by email, artwork lived in shared drives, and print partners had no visibility of their queue. Reprints from production errors were running at nearly 8%.</p><h2>What we built</h2><ul><li>A customer portal with live quoting based on stock, quantity and finishing options</li><li>Automated print-ready PDF generation with preflight checks</li><li>A partner dashboard with per-job queue, status and dispatch tracking</li><li>Stripe-based payments with invoicing for account customers</li></ul><p>Within two quarters, production reprints fell to under 1% and average quote turnaround went from 2 days to under 4 minutes.</p>',
                'live_url' => 'https://example.com/printlab',
                'featured' => true,
                'cover' => 'solution-printlab',
                'benefits' => [
                    ['title' => '8% → 0.9% reprint rate', 'description' => 'Automated preflight checks catch artwork problems before a job reaches the press.'],
                    ['title' => '2 days → 4 minutes', 'description' => 'Instant quoting removed the manual estimation bottleneck.'],
                    ['title' => '3 print partners onboarded', 'description' => 'One pipeline now routes work by capacity and capability.'],
                ],
                'features' => [
                    ['title' => 'Live quoting engine', 'description' => '<p>Customers configure stock, size, quantity and finishing and get a priced quote instantly, with volume tiers applied automatically.</p>', 'media' => 'shot-orders'],
                    ['title' => 'Automated preflight', 'description' => '<p>Uploaded artwork is checked for bleed, resolution and colour profile before it can be ordered — catching issues that used to be found at press time.</p>', 'media' => 'shot-analytics'],
                    ['title' => 'Partner fulfilment queue', 'description' => '<p>Each print partner sees only their jobs, with capacity-aware routing and dispatch confirmation that feeds back into customer tracking.</p>', 'media' => 'shot-schedule'],
                ],
                'screenshots' => ['shot-orders', 'shot-analytics', 'shot-schedule'],
                'tech' => ['Laravel', 'React', 'TypeScript', 'MySQL', 'Redis', 'AWS', 'Stripe', 'Tailwind CSS'],
            ],
            [
                'title' => 'LexAssist — AI Legal Assistant',
                'slug' => 'lexassist-ai-legal-assistant',
                'intro' => 'A retrieval-augmented assistant that helps fee earners review contracts and draft first-pass advice with citations to source documents.',
                'description' => '<p>LexAssist gives a mid-sized UK law firm an assistant grounded in their own precedent bank. Every answer cites the clause it came from, and nothing leaves the firm\'s tenancy.</p><h2>The problem</h2><p>Associates spent hours locating comparable clauses and re-drafting the same advice. Generic AI tools were unusable: no citations, no confidentiality guarantees, no audit trail.</p><h2>What we built</h2><ul><li>Secure document ingestion with OCR and clause-level chunking</li><li>Retrieval over a private vector index hosted in the firm\'s AWS account</li><li>Cited drafting with an evaluation harness measuring groundedness</li><li>Review workflow: every draft is explicitly approved by a fee earner</li></ul>',
                'live_url' => null,
                'featured' => true,
                'cover' => 'solution-lexai',
                'benefits' => [
                    ['title' => '4.5 hours saved per matter', 'description' => 'Measured across a 6-week pilot with two practice groups.'],
                    ['title' => 'Every answer cited', 'description' => 'Clause-level references mean output can be verified, not trusted blindly.'],
                    ['title' => 'Zero data egress', 'description' => 'Documents and embeddings stay inside the firm\'s own AWS tenancy.'],
                ],
                'features' => [
                    ['title' => 'Cited answers', 'description' => '<p>Responses link back to the specific paragraph of the source document, with a side-by-side viewer so lawyers can verify in seconds.</p>', 'media' => 'shot-documents'],
                    ['title' => 'Clause retrieval', 'description' => '<p>Contract ingestion splits documents at clause boundaries, so retrieval returns the relevant term rather than a whole agreement.</p>', 'media' => 'shot-chat'],
                    ['title' => 'Evaluation harness', 'description' => '<p>A curated question set runs on every prompt change, scoring groundedness and citation accuracy before release.</p>', 'media' => 'shot-analytics'],
                ],
                'screenshots' => ['shot-documents', 'shot-chat', 'shot-analytics'],
                'tech' => ['Laravel', 'React', 'Python', 'OpenAI', 'AWS', 'MySQL', 'Docker'],
            ],
            [
                'title' => 'Atlas Field Ops',
                'slug' => 'atlas-field-ops',
                'intro' => 'An offline-first mobile web app for field engineers: job sheets, photos, signatures and parts tracking with no signal required.',
                'description' => '<p>Atlas replaced paper job sheets for a 60-engineer maintenance operation. Engineers work fully offline; everything syncs when they reach a network.</p><h2>The problem</h2><p>Job sheets were completed on paper and posted back. Billing ran weeks behind, and parts usage was invisible until stock ran out.</p><h2>What we built</h2><ul><li>Offline-first React app with background sync and conflict resolution</li><li>Photo capture, customer signature and asset history</li><li>Parts consumption tied to van stock levels</li><li>Automatic job completion → invoicing handoff</li></ul>',
                'live_url' => null,
                'featured' => true,
                'cover' => 'solution-atlas',
                'benefits' => [
                    ['title' => 'Invoicing 3 weeks → 2 days', 'description' => 'Jobs complete and sync the same day, so billing starts immediately.'],
                    ['title' => 'Works with no signal', 'description' => 'Full offline capability for basements, rural sites and plant rooms.'],
                    ['title' => 'Live van stock', 'description' => 'Parts are deducted as they are used, so replenishment is automatic.'],
                ],
                'features' => [
                    ['title' => 'Offline-first sync', 'description' => '<p>Every action is queued locally with an idempotent sync model, so duplicate submissions and lost work are impossible.</p>', 'media' => 'shot-schedule'],
                    ['title' => 'Digital job sheets', 'description' => '<p>Photos, readings, parts and customer signature captured in one flow — no paperwork to post back.</p>', 'media' => 'shot-orders'],
                    ['title' => 'Asset history', 'description' => '<p>Every visit appends to the asset record, giving engineers context before they arrive on site.</p>', 'media' => 'shot-analytics'],
                ],
                'screenshots' => ['shot-schedule', 'shot-orders', 'shot-analytics'],
                'tech' => ['React', 'TypeScript', 'Laravel', 'MySQL', 'Redis', 'Tailwind CSS'],
            ],
        ];

        foreach ($solutions as $index => $definition) {
            $solution = Solution::query()->updateOrCreate(
                ['slug' => $definition['slug']],
                [
                    'title' => $definition['title'],
                    'intro' => $definition['intro'],
                    'description' => $definition['description'],
                    'cover_media_id' => $media($definition['cover']),
                    'live_url' => $definition['live_url'],
                    'business_benefits' => $definition['benefits'],
                    'status' => ContentStatus::Published,
                    'is_featured' => $definition['featured'],
                    'sort_order' => $index + 1,
                    'seo_title' => $definition['title'].' — Case study',
                    'seo_description' => Str::limit($definition['intro'], 155),
                ],
            );

            $solution->features()->delete();

            foreach ($definition['features'] as $featureIndex => $feature) {
                $solution->features()->create([
                    'title' => $feature['title'],
                    'description' => $feature['description'],
                    'media_id' => $media($feature['media']),
                    'sort_order' => $featureIndex,
                ]);
            }

            $solution->screenshots()->delete();

            foreach ($definition['screenshots'] as $shotIndex => $shot) {
                $mediaId = $media($shot);

                if ($mediaId) {
                    $solution->screenshots()->create([
                        'media_id' => $mediaId,
                        'caption' => Str::headline(str_replace('shot-', '', $shot)),
                        'sort_order' => $shotIndex,
                    ]);
                }
            }

            $solution->technologies()->sync(
                collect($definition['tech'])->map(fn ($name) => $techModels[$name]->id)->all(),
            );
        }
    }
}
