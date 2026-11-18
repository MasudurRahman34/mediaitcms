<?php

namespace App\Services;

use App\Models\Media;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Typed access to the CMS settings table, cached for the public site.
 */
class SettingsRepository
{
    private const CACHE_KEY = 'settings.all';

    /** @var array<string, mixed>|null */
    private ?array $values = null;

    /** @return array<string, mixed> */
    public function all(): array
    {
        if ($this->values !== null) {
            return $this->values;
        }

        $this->values = Cache::rememberForever(self::CACHE_KEY, function () {
            return Setting::query()
                ->get()
                ->mapWithKeys(fn (Setting $setting) => [$setting->key => $this->unwrap($setting)])
                ->all();
        });

        return $this->values;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->all()[$key] ?? null;

        return ($value === null || $value === '') ? $default : $value;
    }

    public function bool(string $key, bool $default = false): bool
    {
        $value = $this->get($key);

        if ($value === null) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public function imageUrl(string $key, ?string $fallback = null): ?string
    {
        $mediaId = $this->get($key);

        if ($mediaId) {
            $media = Media::find($mediaId);
            if ($media) {
                return $media->url();
            }
        }

        return $fallback;
    }

    /** @return array<int, array{label: string, url: string, icon: string}> */
    public function socialLinks(): array
    {
        $networks = ['linkedin', 'twitter', 'x', 'facebook', 'instagram', 'youtube', 'github', 'dribbble'];
        $links = [];

        foreach ($networks as $network) {
            $url = $this->get('social_'.$network);
            if (is_string($url) && $url !== '') {
                $links[] = [
                    'label' => ucfirst($network === 'x' ? 'X' : $network),
                    'url' => $url,
                    'icon' => $network,
                ];
            }
        }

        return $links;
    }

    /** @return array<int, array{label: string, value: string, href: string|null}> */
    public function contactDetails(): array
    {
        return [
            [
                'label' => 'Phone',
                'value' => (string) $this->get('contact_phone', ''),
                'href' => $this->get('contact_phone') ? 'tel:'.preg_replace('/\s+/', '', (string) $this->get('contact_phone')) : null,
            ],
            [
                'label' => 'Email',
                'value' => (string) $this->get('contact_email', ''),
                'href' => $this->get('contact_email') ? 'mailto:'.(string) $this->get('contact_email') : null,
            ],
            [
                'label' => 'Address',
                'value' => (string) $this->get('contact_address', ''),
                'href' => null,
            ],
        ];
    }

    public function flush(): void
    {
        $this->values = null;
        Cache::forget(self::CACHE_KEY);
    }

    private function unwrap(Setting $setting): mixed
    {
        $value = $setting->value;

        if (is_array($value) && array_key_exists('value', $value)) {
            return $value['value'];
        }

        return $value;
    }
}
