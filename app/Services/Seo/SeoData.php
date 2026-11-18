<?php

namespace App\Services\Seo;

class SeoData
{
    /**
     * @param  array<int, array<string, mixed>>  $jsonLd
     */
    public function __construct(
        public string $title,
        public ?string $description = null,
        public ?string $canonical = null,
        public ?string $image = null,
        public string $type = 'website',
        public array $jsonLd = [],
        public bool $noindex = false,
    ) {}

    public function withImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function withJsonLd(array $jsonLd): self
    {
        $this->jsonLd = array_merge($this->jsonLd, $jsonLd);

        return $this;
    }

    /** @return array<int, array<string, mixed>> */
    public function graph(): array
    {
        return $this->jsonLd;
    }
}
