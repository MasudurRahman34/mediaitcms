<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\Page;
use App\Models\User;
use App\Services\Pages\PagePublisher;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(PagePublisher $publisher): void
    {
        $mediaId = fn (string $key) => MediaSeeder::id($key);
        $admin = User::query()->where('email', 'admin@mediaitsoft.co.uk')->first();

        $pages = [
            [
                'slug' => 'home',
                'title' => 'Home',
                'template' => 'home',
                'seo_title' => 'Media IT Soft — Software, AI & IT Solutions',
                'seo_description' => 'Media IT Soft designs, builds and supports software, AI solutions and IT services for ambitious organisations across the UK.',
                'sections' => [
                    [
                        'type' => 'hero',
                        'content' => [
                            'eyebrow' => 'Software · AI · IT services',
                            'heading' => 'We build the software your business runs on',
                            'subheading' => 'A senior UK team designing, engineering and supporting digital products — from custom platforms to production AI.',
                            'primary_cta' => ['label' => 'Start a project', 'url' => '/contact'],
                            'secondary_cta' => ['label' => 'See our work', 'url' => '/solutions'],
                            'image' => $mediaId('hero-team'),
                            'align' => 'left',
                            'show_gradient' => true,
                        ],
                    ],
                    [
                        'type' => 'logos',
                        'content' => [
                            'heading' => 'Trusted by teams across the UK',
                            'items' => [
                                ['media_id' => null, 'label' => 'PrintLab'],
                                ['media_id' => null, 'label' => 'LexAssist'],
                                ['media_id' => null, 'label' => 'Atlas Field Ops'],
                                ['media_id' => null, 'label' => 'Northgate Group'],
                                ['media_id' => null, 'label' => 'Wessex Care'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'rich_text',
                        'content' => [
                            'eyebrow' => 'Who we are',
                            'heading' => 'A small team with a long track record',
                            'body' => '<p>Media IT Soft is a software engineering company based in Swindon. Since 2014 we have delivered platforms, integrations and AI features for organisations that need them to work reliably in production.</p><p>We keep the team deliberately senior. The people who scope your project are the people who build it, which means fewer surprises and a system that is maintainable long after we hand it over.</p>',
                            'image' => $mediaId('office'),
                            'image_position' => 'right',
                        ],
                    ],
                    [
                        'type' => 'services_grid',
                        'content' => [
                            'eyebrow' => 'Services',
                            'heading' => 'What we do',
                            'intro' => 'Four capabilities that cover most of what our clients need — and the depth to go further.',
                            'source' => 'featured',
                            'limit' => 3,
                            'show_cta' => true,
                        ],
                    ],
                    [
                        'type' => 'stats',
                        'content' => [
                            'eyebrow' => 'By the numbers',
                            'heading' => 'Delivery you can measure',
                            'intro' => '',
                            'items' => [
                                ['value' => '120', 'suffix' => '+', 'label' => 'Projects delivered'],
                                ['value' => '11', 'suffix' => '', 'label' => 'Years in business'],
                                ['value' => '98', 'suffix' => '%', 'label' => 'Client retention'],
                                ['value' => '24', 'suffix' => 'h', 'label' => 'Support response'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'solutions_grid',
                        'content' => [
                            'eyebrow' => 'Our work',
                            'heading' => 'Products we have built',
                            'intro' => 'Real systems, in production, solving specific problems.',
                            'source' => 'featured',
                            'limit' => 3,
                            'show_cta' => true,
                        ],
                    ],
                    [
                        'type' => 'features_grid',
                        'content' => [
                            'eyebrow' => 'Why choose us',
                            'heading' => 'What working with us is like',
                            'intro' => 'No account layers, no offshore handoffs, no mystery estimates.',
                            'columns' => '3',
                            'items' => [
                                ['icon' => 'users', 'title' => 'Senior team, start to finish', 'description' => 'The engineers who scope your project are the ones who deliver it. Average experience is over a decade.'],
                                ['icon' => 'zap', 'title' => 'Working software early', 'description' => 'We ship a usable slice in weeks, not a status report in months. You can see progress, not just hear about it.'],
                                ['icon' => 'shield', 'title' => 'Built to be maintained', 'description' => 'Tests, documentation and clear architecture. You or another team can pick it up without archaeology.'],
                                ['icon' => 'chart', 'title' => 'Measured outcomes', 'description' => 'Every engagement defines what success means in numbers before we start building.'],
                                ['icon' => 'globe', 'title' => 'UK-based and accountable', 'description' => 'Swindon-based, UK hours, direct access to the people doing the work.'],
                                ['icon' => 'lock', 'title' => 'Security by default', 'description' => 'Least privilege, private storage for personal data and audit trails on everything sensitive.'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'articles_grid',
                        'content' => [
                            'eyebrow' => 'News & insights',
                            'heading' => 'Featured articles',
                            'intro' => 'Practical notes from the work we do.',
                            'source' => 'home_featured',
                            'limit' => 3,
                            'show_cta' => true,
                        ],
                    ],
                    [
                        'type' => 'testimonials',
                        'content' => [
                            'eyebrow' => 'Client feedback',
                            'heading' => 'What clients say',
                            'items' => [
                                [
                                    'quote' => 'They replaced a process that took two days with something that takes four minutes. The team understood our production constraints better than we did.',
                                    'name' => 'Rachel Simmons',
                                    'role' => 'Operations Director, PrintLab',
                                    'avatar' => null,
                                ],
                                [
                                    'quote' => 'The pilot spoke for itself. Every answer cited its source, which is the only reason our partners would approve the rollout.',
                                    'name' => 'Ian Fielding',
                                    'role' => 'Managing Partner, legal client',
                                    'avatar' => null,
                                ],
                                [
                                    'quote' => 'We had eleven microservices and no idea where the money went. We now have one well-structured application and a much smaller bill.',
                                    'name' => 'Deborah Okafor',
                                    'role' => 'CTO, facilities group',
                                    'avatar' => null,
                                ],
                            ],
                        ],
                    ],
                    [
                        'type' => 'cta',
                        'content' => [
                            'eyebrow' => 'Next step',
                            'heading' => 'Ready to build something exceptional?',
                            'body' => 'Tell us what you are working on. We will come back with a clear view of scope, cost and the first milestone.',
                            'primary_cta' => ['label' => 'Start a conversation', 'url' => '/contact'],
                            'secondary_cta' => ['label' => 'Explore our services', 'url' => '/services'],
                            'style' => 'gradient',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'about',
                'title' => 'About Us',
                'template' => 'about',
                'seo_title' => 'About Media IT Soft',
                'seo_description' => 'A Swindon-based software engineering company building platforms, AI features and IT systems since 2014.',
                'sections' => [
                    [
                        'type' => 'hero',
                        'content' => [
                            'eyebrow' => 'About us',
                            'heading' => 'Built on engineering, not sales',
                            'subheading' => 'We are a team of engineers and designers who would rather ship something real than talk about it.',
                            'primary_cta' => ['label' => 'Meet the team', 'url' => '#team'],
                            'secondary_cta' => ['label' => 'Contact us', 'url' => '/contact'],
                            'image' => $mediaId('office'),
                            'align' => 'left',
                            'show_gradient' => true,
                        ],
                    ],
                    [
                        'type' => 'rich_text',
                        'content' => [
                            'eyebrow' => 'Our story',
                            'heading' => 'Eleven years of building things that stay built',
                            'body' => '<p>Media IT Soft started in 2014 with a simple observation: most software projects fail on communication, not technology. So we built a company with no layers between the people who agree the work and the people who do it.</p><p>Today we work with organisations across the UK — from print and fulfilment to professional services and facilities management — on platforms that run their day-to-day operations.</p><h2>Mission</h2><p>To make dependable software accessible to organisations that are not software companies, and to leave every client with a system their own team can run.</p><h2>Vision</h2><p>To be the engineering partner UK businesses call first when something complex, valuable or regulated needs to work properly.</p>',
                            'image' => $mediaId('hero-team'),
                            'image_position' => 'right',
                        ],
                    ],
                    [
                        'type' => 'features_grid',
                        'content' => [
                            'eyebrow' => 'Our values',
                            'heading' => 'How we work',
                            'intro' => '',
                            'columns' => '2',
                            'items' => [
                                ['icon' => 'target', 'title' => 'Clarity over cleverness', 'description' => 'The simplest solution that solves the real problem beats an impressive one that does not.'],
                                ['icon' => 'check-circle', 'title' => 'Say the difficult thing early', 'description' => 'If a deadline or a budget will not work, you hear it in week one, not week twelve.'],
                                ['icon' => 'rocket', 'title' => 'Ship, then improve', 'description' => 'Working software in front of real users teaches you more than any specification.'],
                                ['icon' => 'lock', 'title' => 'Treat data as a responsibility', 'description' => 'Personal data is stored deliberately, accessed deliberately and logged always.'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'stats',
                        'content' => [
                            'eyebrow' => 'Achievements',
                            'heading' => 'Where we have got to',
                            'intro' => '',
                            'items' => [
                                ['value' => '120', 'suffix' => '+', 'label' => 'Projects delivered'],
                                ['value' => '11', 'suffix' => '', 'label' => 'Years trading'],
                                ['value' => '98', 'suffix' => '%', 'label' => 'Client retention'],
                                ['value' => '6', 'suffix' => '', 'label' => 'Senior specialists'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'team_grid',
                        'content' => [
                            'eyebrow' => 'Our people',
                            'heading' => 'The team',
                            'intro' => 'Small by design. Everyone here has shipped production software for at least eight years.',
                            'limit' => 6,
                        ],
                    ],
                    [
                        'type' => 'cta',
                        'content' => [
                            'eyebrow' => 'Work with us',
                            'heading' => 'Let us talk about what you need',
                            'body' => 'A short call is usually enough to tell whether we are the right fit.',
                            'primary_cta' => ['label' => 'Get in touch', 'url' => '/contact'],
                            'secondary_cta' => ['label' => 'Read our articles', 'url' => '/news'],
                            'style' => 'gradient',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'privacy-policy',
                'title' => 'Privacy Policy',
                'template' => 'standard',
                'seo_title' => 'Privacy Policy',
                'seo_description' => 'How Media IT Soft collects, uses and protects personal data.',
                'sections' => [
                    [
                        'type' => 'rich_text',
                        'content' => [
                            'eyebrow' => 'Legal',
                            'heading' => 'Privacy Policy',
                            'body' => '<p>Media IT Soft ("we", "us") respects your privacy and is committed to protecting your personal data. This policy explains what we collect and why.</p><h2>What we collect</h2><ul><li>Contact details you submit through our enquiry form</li><li>Application details and CVs you submit for a vacancy</li><li>Basic technical data such as IP address for security and rate limiting</li></ul><h2>How we use it</h2><p>Enquiry details are used solely to respond to your message. Application data is used solely to assess your application for the role you applied to.</p><h2>Storage and retention</h2><p>Applicant documents are stored on private storage, accessible only to authorised hiring staff. We retain applications for twelve months unless you ask us to delete them sooner.</p><h2>Your rights</h2><p>You may request access to, correction of, or deletion of your personal data at any time by emailing info@mediaitsoft.co.uk.</p>',
                            'image' => null,
                            'image_position' => 'right',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'terms-of-service',
                'title' => 'Terms of Service',
                'template' => 'standard',
                'seo_title' => 'Terms of Service',
                'seo_description' => 'Terms governing use of the Media IT Soft website.',
                'sections' => [
                    [
                        'type' => 'rich_text',
                        'content' => [
                            'eyebrow' => 'Legal',
                            'heading' => 'Terms of Service',
                            'body' => '<p>By using this website you agree to these terms.</p><h2>Website content</h2><p>Content is provided for general information. Case studies describe work delivered for clients; specific results depend on individual circumstances.</p><h2>Intellectual property</h2><p>All content, marks and materials on this site are owned by Media IT Soft unless stated otherwise, and may not be reproduced without permission.</p><h2>Engagements</h2><p>Client work is governed by a separate written agreement, not by these website terms.</p>',
                            'image' => null,
                            'image_position' => 'right',
                        ],
                    ],
                ],
            ],
        ];

        foreach ($pages as $index => $definition) {
            $page = Page::query()->updateOrCreate(
                ['slug' => $definition['slug']],
                [
                    'title' => $definition['title'],
                    'template' => $definition['template'],
                    'seo_title' => $definition['seo_title'],
                    'seo_description' => $definition['seo_description'],
                    'is_system' => in_array($definition['slug'], ['home', 'about'], true),
                ],
            );

            $publisher->writeDraft($page, $definition['sections']);
            $publisher->publish($page, $admin, 'Initial content');
        }
    }
}
