<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Media extends Model
{
    use HasFactory;

    protected $table = 'media';

    protected $fillable = [
        'disk', 'path', 'filename', 'mime', 'size', 'width', 'height',
        'alt', 'title', 'folder', 'hash', 'conversions', 'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'conversions' => 'array',
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime, 'image/');
    }

    public function isPrivate(): bool
    {
        return $this->disk !== 'public';
    }

    public function url(): string
    {
        if ($this->isPrivate()) {
            return route('media.private', $this);
        }

        return Storage::disk($this->disk)->url($this->path);
    }

    /**
     * Fully-qualified URL, required by Open Graph tags, JSON-LD and emails.
     */
    public function absoluteUrl(): string
    {
        return $this->toAbsolute($this->url());
    }

    /** Path of a generated variant, falling back to the original. */
    public function variantUrl(string $variant): string
    {
        $path = $this->conversions[$variant] ?? null;

        if (! $path || ! Storage::disk($this->disk)->exists($path)) {
            return $this->url();
        }

        return Storage::disk($this->disk)->url($path);
    }

    public function absoluteVariantUrl(string $variant): string
    {
        return $this->toAbsolute($this->variantUrl($variant));
    }

    /**
     * Public disk URLs are relative (see config/filesystems.php), so meta tags
     * need them resolved against the current request or APP_URL.
     */
    private function toAbsolute(string $url): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return url($url);
    }

    /**
     * Build a srcset string from the generated width variants.
     */
    public function srcset(): string
    {
        $parts = [];

        foreach (['400' => 400, '800' => 800, '1600' => 1600] as $key => $width) {
            if (! empty($this->conversions[$key])) {
                $parts[] = $this->variantUrl($key).' '.$width.'w';
            }
        }

        return $parts ? implode(', ', $parts) : '';
    }

    public function altText(): string
    {
        return $this->alt ?: ($this->title ?: '');
    }

    public function humanSize(): string
    {
        $bytes = (int) $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $index = 0;

        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return round($bytes, $index === 0 ? 0 : 1).' '.$units[$index];
    }

    public static function storeUpload(
        UploadedFile $file,
        string $folder = 'uploads',
        string $disk = 'public',
        ?int $userId = null,
    ): self {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension());
        $filename = Str::uuid().'.'.$extension;
        $directory = trim($folder, '/').'/'.date('Y/m');
        $path = $file->storeAs($directory, $filename, $disk);

        $width = null;
        $height = null;

        if (str_starts_with((string) $file->getMimeType(), 'image/')) {
            $dimensions = @getimagesize($file->getRealPath());
            if ($dimensions) {
                [$width, $height] = $dimensions;
            }
        }

        return self::create([
            'disk' => $disk,
            'path' => $path,
            'filename' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
            'width' => $width,
            'height' => $height,
            'title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'folder' => trim($folder, '/'),
            'uploaded_by' => $userId,
        ]);
    }

    public function scopeImages(Builder $query): Builder
    {
        return $query->where('mime', 'like', 'image/%');
    }

    protected static function booted(): void
    {
        static::deleting(function (Media $media) {
            Storage::disk($media->disk)->delete($media->path);

            foreach ($media->conversions ?? [] as $path) {
                Storage::disk($media->disk)->delete($path);
            }
        });
    }
}
