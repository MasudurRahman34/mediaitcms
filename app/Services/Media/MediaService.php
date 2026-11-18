<?php

namespace App\Services\Media;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class MediaService
{
    /** Width variants generated for uploaded images. */
    private const VARIANTS = [400, 800, 1600];

    public function upload(UploadedFile $file, string $folder = 'uploads', ?string $disk = null, ?int $userId = null): Media
    {
        $disk ??= config('filesystems.media_disk', 'public');

        $media = Media::storeUpload($file, $folder, $disk, $userId);

        if ($media->isImage() && $this->canProcess($media)) {
            $media->forceFill(['conversions' => $this->generateVariants($media)])->save();
        }

        return $media;
    }

    /**
     * Media may not be deleted while the CMS still references it.
     */
    public function isInUse(Media $media): bool
    {
        $references = [
            ['pages', 'og_media_id'],
            ['services', 'cover_media_id'],
            ['services', 'og_media_id'],
            ['solutions', 'cover_media_id'],
            ['solutions', 'og_media_id'],
            ['solution_features', 'media_id'],
            ['solution_screenshots', 'media_id'],
            ['technologies', 'logo_media_id'],
            ['team_members', 'photo_media_id'],
            ['articles', 'featured_media_id'],
            ['articles', 'og_media_id'],
            ['users', 'avatar_media_id'],
        ];

        foreach ($references as [$table, $column]) {
            if (DB::table($table)->where($column, $media->id)->exists()) {
                return true;
            }
        }

        if (DB::table('settings')->where('type', 'media')->where('value->value', $media->id)->exists()) {
            return true;
        }

        // Page builder content keeps media ids nested inside JSON payloads.
        return DB::table('page_sections')
            ->whereNotNull('content')
            ->pluck('content')
            ->contains(fn ($content) => $this->jsonReferencesMedia(
                json_decode((string) $content, true),
                $media->id,
            ));
    }

    private function jsonReferencesMedia(mixed $node, int $mediaId): bool
    {
        if (! is_array($node)) {
            return false;
        }

        foreach ($node as $key => $value) {
            $isMediaKey = is_string($key) && in_array($key, ['media_id', 'image', 'avatar', 'logo', 'media'], true);

            if ($isMediaKey && (int) $value === $mediaId) {
                return true;
            }

            if (is_array($value) && $this->jsonReferencesMedia($value, $mediaId)) {
                return true;
            }
        }

        return false;
    }

    public function delete(Media $media): bool
    {
        if ($this->isInUse($media)) {
            return false;
        }

        return (bool) $media->delete();
    }

    private function canProcess(Media $media): bool
    {
        return extension_loaded('gd')
            && in_array($media->mime, ['image/jpeg', 'image/png', 'image/webp'], true);
    }

    /**
     * Generate resized copies with GD (no external image dependency).
     *
     * @return array<string, string>
     */
    private function generateVariants(Media $media): array
    {
        $disk = Storage::disk($media->disk);
        $sourcePath = $disk->path($media->path);

        if (! is_file($sourcePath) || $media->width === null || $media->height === null) {
            return [];
        }

        $image = $this->createImage($sourcePath, $media->mime);

        if (! $image) {
            return [];
        }

        $conversions = [];
        $extension = pathinfo($media->path, PATHINFO_EXTENSION);
        $base = Str::beforeLast($media->path, '.'.$extension);
        $supportWebp = function_exists('imagewebp');

        foreach (self::VARIANTS as $width) {
            if ($media->width <= $width) {
                continue;
            }

            $height = (int) round($media->height * ($width / $media->width));
            $canvas = imagecreatetruecolor($width, $height);

            if ($media->mime === 'image/png' || $media->mime === 'image/webp') {
                imagealphablending($canvas, false);
                imagesavealpha($canvas, true);
            }

            imagecopyresampled($canvas, $image, 0, 0, 0, 0, $width, $height, $media->width, $media->height);

            $targetExtension = ($media->mime === 'image/webp' && $supportWebp) ? 'webp' : $extension;
            $targetPath = $base.'-'.$width.'.'.$targetExtension;
            $tmpPath = tempnam(sys_get_temp_dir(), 'media');

            $saved = $this->writeImage($canvas, $tmpPath, $media->mime);

            if ($saved) {
                $disk->put($targetPath, (string) file_get_contents($tmpPath));
                $conversions[(string) $width] = $targetPath;
            }

            @unlink($tmpPath);
            imagedestroy($canvas);
        }

        imagedestroy($image);

        return $conversions;
    }

    private function createImage(string $path, string $mime): \GdImage|false
    {
        return match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };
    }

    private function writeImage(\GdImage $image, string $path, string $mime): bool
    {
        return match ($mime) {
            'image/jpeg' => imagejpeg($image, $path, 82),
            'image/png' => imagepng($image, $path, 8),
            'image/webp' => function_exists('imagewebp') ? imagewebp($image, $path, 82) : false,
            default => throw new RuntimeException('Unsupported image type for conversion: '.$mime),
        };
    }
}
