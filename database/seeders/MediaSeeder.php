<?php

namespace Database\Seeders;

use App\Models\Media;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Generates demo imagery locally with GD so the seeded site has no external
 * image dependencies. Keys are stable and referenced by the other seeders.
 * Real uploads replace these through the Media Library.
 */
class MediaSeeder extends Seeder
{
    private const PALETTES = [
        ['#09215E', '#0D1220', '#3B82F6'],
        ['#0A1C48', '#0E0F11', '#60A5FA'],
        ['#132B5E', '#0B1120', '#2563EB'],
        ['#0D1220', '#09215E', '#38BDF8'],
        ['#1C3169', '#0D1220', '#7DD3FC'],
    ];

    /** @var array<string, array{0: string, 1: int, 2: int}> */
    private const IMAGES = [
        'hero-team' => ['Digital team collaborating', 1600, 1200],
        'office' => ['Media IT Soft studio', 1600, 1000],
        'solution-printlab' => ['Print on Demand platform dashboard', 1600, 1000],
        'solution-lexai' => ['AI legal assistant interface', 1600, 1000],
        'solution-atlas' => ['Field operations app', 1600, 1000],
        'shot-analytics' => ['Order analytics screen', 1600, 1000],
        'shot-orders' => ['Order management screen', 1600, 1000],
        'shot-chat' => ['Conversational assistant screen', 1600, 1000],
        'shot-documents' => ['Document review screen', 1600, 1000],
        'shot-schedule' => ['Scheduling screen', 1600, 1000],
        'article-ai' => ['AI in software delivery', 1600, 900],
        'article-cloud' => ['Cloud architecture', 1600, 900],
        'article-design' => ['Design systems', 1600, 900],
        'article-security' => ['Security engineering', 1600, 900],
        'article-data' => ['Data platforms', 1600, 900],
        'article-automation' => ['Business automation', 1600, 900],
        'article-legacy' => ['Legacy modernisation', 1600, 900],
        'article-team' => ['Engineering culture', 1600, 900],
    ];

    public function run(): void
    {
        $disk = Storage::disk('public');
        $disk->makeDirectory('demo');

        foreach (array_values(self::IMAGES) as $index => [$label, $width, $height]) {
            $key = array_keys(self::IMAGES)[$index];
            $path = 'demo/'.$key.'.png';

            if (! $disk->exists($path)) {
                $disk->put($path, $this->gradient($width, $height, self::PALETTES[$index % count(self::PALETTES)], $label));
            }

            Media::query()->updateOrCreate(
                ['path' => $path],
                [
                    'disk' => 'public',
                    'filename' => basename($path),
                    'mime' => 'image/png',
                    'size' => $disk->size($path),
                    'width' => $width,
                    'height' => $height,
                    'alt' => $label,
                    'title' => $label,
                    'folder' => 'demo',
                ],
            );
        }
    }

    /**
     * Resolve a seeded demo image id by its stable key.
     */
    public static function id(string $key): ?int
    {
        return Media::query()->where('path', 'demo/'.$key.'.png')->value('id');
    }

    /**
     * @param  array<int, string>  $palette
     */
    private function gradient(int $width, int $height, array $palette, string $label): string
    {
        $image = imagecreatetruecolor($width, $height);
        $from = $this->hex($palette[0]);
        $mid = $this->hex($palette[1]);
        $accent = $this->hex($palette[2] ?? $palette[0]);

        for ($y = 0; $y < $height; $y++) {
            $ratio = $y / max(1, $height - 1);
            $r = (int) round($from[0] + ($mid[0] - $from[0]) * $ratio);
            $g = (int) round($from[1] + ($mid[1] - $from[1]) * $ratio);
            $b = (int) round($from[2] + ($mid[2] - $from[2]) * $ratio);
            imageline($image, 0, $y, $width, $y, imagecolorallocate($image, $r, $g, $b));
        }

        // Soft accent glow in the upper right.
        for ($i = 0; $i < 90; $i++) {
            $radius = (int) ($width * 0.5 - $i * 4);
            if ($radius <= 0) {
                break;
            }
            $alpha = min(127, 118 - $i);
            $color = imagecolorallocatealpha($image, $accent[0], $accent[1], $accent[2], $alpha);
            imagefilledellipse($image, (int) ($width * 0.78), (int) ($height * 0.16), $radius, $radius, $color);
        }

        // Technical grid.
        $grid = imagecolorallocatealpha($image, 248, 250, 252, 122);
        for ($x = 0; $x < $width; $x += 80) {
            imageline($image, $x, 0, $x, $height, $grid);
        }
        for ($y = 0; $y < $height; $y += 80) {
            imageline($image, 0, $y, $width, $y, $grid);
        }

        $white = imagecolorallocate($image, 248, 250, 252);
        $accentColor = imagecolorallocate($image, $accent[0], $accent[1], $accent[2]);
        imagefilledrectangle($image, 60, 60, 96, 62, $accentColor);
        imagestring($image, 5, 60, 80, 'MEDIA IT SOFT', imagecolorallocatealpha($image, 248, 250, 252, 40));
        $this->wrappedText($image, $label, 60, (int) ($height * 0.62), $width - 120, $white);

        ob_start();
        imagepng($image, null, 6);
        $data = (string) ob_get_clean();
        imagedestroy($image);

        return $data;
    }

    /** @return array{0: int, 1: int, 2: int} */
    private function hex(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        ];
    }

    private function wrappedText(\GdImage $image, string $text, int $x, int $y, int $maxWidth, int $color): void
    {
        $words = explode(' ', $text);
        $line = '';
        $lineHeight = 34;

        foreach ($words as $word) {
            $candidate = trim($line.' '.$word);

            if (strlen($candidate) * 9 > $maxWidth && $line !== '') {
                imagestring($image, 5, $x, $y, strtoupper($line), $color);
                $y += $lineHeight;
                $line = $word;
            } else {
                $line = $candidate;
            }
        }

        if ($line !== '') {
            imagestring($image, 5, $x, $y, strtoupper($line), $color);
        }
    }
}
