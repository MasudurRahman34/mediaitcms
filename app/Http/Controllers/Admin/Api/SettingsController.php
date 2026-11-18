<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Setting;
use App\Services\Seo\SitemapBuilder;
use App\Services\SettingsRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct(private readonly SettingsRepository $settings) {}

    public function index(): JsonResponse
    {
        $schema = config('settings.groups');
        $values = [];

        foreach ($schema as $group) {
            foreach ($group['fields'] as $key => $field) {
                $stored = $this->settings->get($key, $field['default'] ?? null);

                $values[$key] = [
                    'value' => $stored,
                    'type' => $field['type'],
                    'media_url' => $field['type'] === 'media' && $stored
                        ? Media::find($stored)?->url()
                        : null,
                ];
            }
        }

        return response()->json([
            'data' => $values,
            'schema' => $schema,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $fields = collect(config('settings.groups'))->flatMap(fn ($group) => $group['fields']);

        $data = $request->validate([
            'settings' => ['required', 'array'],
        ]);

        foreach ($data['settings'] as $key => $value) {
            if (! $fields->has($key)) {
                continue;
            }

            $fieldType = $fields->get($key)['type'] ?? 'text';

            $normalised = match ($fieldType) {
                'media' => $value === null || $value === '' ? null : (int) $value,
                'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
                default => $value === null ? null : trim((string) $value),
            };

            Setting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'group' => $this->groupFor($key),
                    'value' => ['value' => $normalised],
                    'type' => $fieldType,
                ],
            );
        }

        $this->settings->flush();
        app(SitemapBuilder::class)->flush();

        return response()->json(['data' => ['ok' => true]]);
    }

    private function groupFor(string $key): string
    {
        foreach (config('settings.groups') as $groupKey => $group) {
            if (array_key_exists($key, $group['fields'])) {
                return $groupKey;
            }
        }

        return 'general';
    }
}
