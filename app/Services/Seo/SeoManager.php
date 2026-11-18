<?php

namespace App\Services\Seo;

use App\Models\Article;
use App\Models\Job;
use App\Models\Page;
use App\Models\Service;
use App\Models\Solution;
use App\Services\SettingsRepository;
use Illuminate\Support\Str;

class SeoManager
{
    public function __construct(private readonly SettingsRepository $settings) {}

    public function page(?Page $page, string $fallbackTitle, ?string $description = null): SeoData
    {
        $title = $page?->seo_title ?: $page?->title ?: $fallbackTitle;
        $desc = $page?->seo_description ?: ($description ?: $this->settings->get('seo_default_description'));
        $image = $page?->ogMedia?->url() ?? $this->defaultImage();

        return new SeoData(
            title: $this->composeTitle($title),
            description: $desc,
            canonical: $page ? $page->url() : url()->current(),
            image: $image,
            jsonLd: [$this->organization()],
        );
    }

    public function article(Article $article): SeoData
    {
        $image = $article->ogMedia?->url() ?? $article->featuredMedia?->url() ?? $this->defaultImage();

        return new SeoData(
            title: $this->composeTitle($article->seo_title ?: $article->title),
            description: $article->seo_description ?: $article->excerpt,
            canonical: $article->canonical_url ?: $article->url(),
            image: $image,
            type: 'article',
            jsonLd: [
                $this->organization(),
                $this->breadcrumbs([
                    ['Home', route('home')],
                    ['News', route('news.index')],
                    [$article->title, $article->url()],
                ]),
                array_filter([
                    '@context' => 'https://schema.org',
                    '@type' => 'Article',
                    'headline' => $article->title,
                    'description' => $article->seo_description ?: $article->excerpt,
                    'image' => $image ? [$image] : null,
                    'datePublished' => $article->published_at?->toIso8601String(),
                    'dateModified' => $article->updated_at?->toIso8601String(),
                    'author' => ['@type' => 'Organization', 'name' => $this->companyName()],
                    'publisher' => [
                        '@type' => 'Organization',
                        'name' => $this->companyName(),
                        'logo' => $this->defaultImage() ? ['@type' => 'ImageObject', 'url' => $this->defaultImage()] : null,
                    ],
                    'mainEntityOfPage' => $article->url(),
                ]),
            ],
        );
    }

    public function service(Service $service): SeoData
    {
        return new SeoData(
            title: $this->composeTitle($service->seo_title ?: $service->title),
            description: $service->seo_description ?: $service->excerpt,
            canonical: $service->url(),
            image: $service->ogMedia?->url() ?? $service->cover?->url() ?? $this->defaultImage(),
            jsonLd: [
                $this->organization(),
                $this->breadcrumbs([
                    ['Home', route('home')],
                    ['Services', route('services.index')],
                    [$service->title, $service->url()],
                ]),
                array_filter([
                    '@context' => 'https://schema.org',
                    '@type' => 'Service',
                    'name' => $service->title,
                    'description' => $service->seo_description ?: $service->excerpt,
                    'provider' => ['@type' => 'Organization', 'name' => $this->companyName()],
                    'url' => $service->url(),
                ]),
            ],
        );
    }

    public function solution(Solution $solution): SeoData
    {
        return new SeoData(
            title: $this->composeTitle($solution->seo_title ?: $solution->title),
            description: $solution->seo_description ?: $solution->intro,
            canonical: $solution->url(),
            image: $solution->ogMedia?->url() ?? $solution->cover?->url() ?? $this->defaultImage(),
            jsonLd: [
                $this->organization(),
                $this->breadcrumbs([
                    ['Home', route('home')],
                    ['Our Work', route('solutions.index')],
                    [$solution->title, $solution->url()],
                ]),
            ],
        );
    }

    public function job(Job $job): SeoData
    {
        return new SeoData(
            title: $this->composeTitle($job->seo_title ?: ($job->title.' — Careers')),
            description: $job->seo_description ?: Str::limit(strip_tags((string) $job->description), 155),
            canonical: $job->url(),
            image: $this->defaultImage(),
            type: 'website',
            jsonLd: [
                $this->organization(),
                $this->breadcrumbs([
                    ['Home', route('home')],
                    ['Careers', route('careers.index')],
                    [$job->title, $job->url()],
                ]),
                array_filter([
                    '@context' => 'https://schema.org',
                    '@type' => 'JobPosting',
                    'title' => $job->title,
                    'description' => strip_tags((string) $job->description),
                    'datePosted' => $job->created_at?->toIso8601String(),
                    'validThrough' => $job->closing_at?->toIso8601String(),
                    'employmentType' => strtoupper(str_replace('-', '_', (string) $job->employment_type)),
                    'hiringOrganization' => [
                        '@type' => 'Organization',
                        'name' => $this->companyName(),
                        'sameAs' => $this->settings->get('social_linkedin'),
                    ],
                    'jobLocation' => [
                        '@type' => 'Place',
                        'address' => [
                            '@type' => 'PostalAddress',
                            'streetAddress' => $this->settings->get('contact_address', ''),
                            'addressCountry' => 'GB',
                        ],
                    ],
                ]),
            ],
        );
    }

    public function simple(string $title, ?string $description = null, string $type = 'website'): SeoData
    {
        return new SeoData(
            title: $this->composeTitle($title),
            description: $description ?: $this->settings->get('seo_default_description'),
            canonical: url()->current(),
            image: $this->defaultImage(),
            type: $type,
            jsonLd: [$this->organization(), $this->website()],
        );
    }

    /**
     * @param  array<int, array{0: string, 1: string}>  $items
     * @return array<string, mixed>
     */
    public function breadcrumbs(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($items)->values()->map(fn ($item, $index) => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item[0],
                'item' => $item[1],
            ])->all(),
        ];
    }

    /** @return array<string, mixed> */
    public function organization(): array
    {
        $socials = array_values(array_filter(array_map(
            fn ($link) => $link['url'],
            $this->settings->socialLinks(),
        )));

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $this->companyName(),
            'url' => url('/'),
            'logo' => $this->defaultImage(),
            'description' => $this->settings->get('seo_default_description'),
            'email' => $this->settings->get('contact_email'),
            'telephone' => $this->settings->get('contact_phone'),
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $this->settings->get('contact_address', ''),
                'addressCountry' => 'GB',
            ],
            'sameAs' => $socials ?: null,
        ]);
    }

    /** @return array<string, mixed> */
    public function website(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $this->companyName(),
            'url' => url('/'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => route('news.index').'?q={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    /** @return array<string, mixed> */
    public function faqPage(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => collect($items)->map(fn ($item) => [
                '@type' => 'Question',
                'name' => $item['question'] ?? '',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => strip_tags($item['answer'] ?? ''),
                ],
            ])->values()->all(),
        ];
    }

    public function companyName(): string
    {
        return (string) $this->settings->get('company_name', config('app.name'));
    }

    public function defaultImage(): ?string
    {
        return $this->settings->imageUrl('seo_default_image')
            ?? $this->settings->imageUrl('logo')
            ?? asset('media/brand/og-default.svg');
    }

    private function composeTitle(string $title): string
    {
        $company = $this->companyName();
        $suffix = (string) $this->settings->get('seo_title_suffix', '');
        $suffix = $suffix !== '' ? $suffix : $company;

        if (str_contains($title, $company)) {
            return $title;
        }

        return $title.' | '.$suffix;
    }
}
