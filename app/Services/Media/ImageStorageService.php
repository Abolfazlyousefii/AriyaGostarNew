<?php

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;
use RuntimeException;
use Throwable;

class ImageStorageService
{
    private const MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        'image/bmp' => 'bmp',
        'image/x-ms-bmp' => 'bmp',
        'image/avif' => 'avif',
    ];
    private const CONVERTIBLE_MIMES = ['image/heic', 'image/heif', 'image/tiff', 'image/x-tiff'];
    private array $existsCache = [];

    public function replace(UploadedFile $file, string $directory, ?string $oldPath, callable $persist): string
    {
        $newPath = $this->store($file, $directory);

        try {
            $persist($newPath);
        } catch (Throwable $exception) {
            $this->delete($newPath);
            Log::error('Image model update failed; the new file was removed.', [
                'new_path' => $newPath,
                'old_path' => $oldPath,
                'exception' => $exception,
            ]);
            throw $exception;
        }

        $this->delete($oldPath);

        return $newPath;
    }

    public function store(UploadedFile $file, string $directory, ?string $oldPath = null): string
    {
        $directory = $this->sanitizeDirectory($directory);
        $mime = (string)$file->getMimeType();
        $convert = in_array($mime, self::CONVERTIBLE_MIMES, true);
        $extension = $convert ? 'webp' : (self::MIME_EXTENSIONS[$mime] ?? null);

        if ($extension === null || (!$convert && @getimagesize($file->getRealPath()) === false)) {
            throw new RuntimeException("Unsupported image MIME type: {$mime}");
        }

        $storageKey = trim($directory . '/' . Str::uuid() . '.' . $extension, '/');

        try {
            if ($convert) {
                if (!extension_loaded('imagick')) {
                    throw new RuntimeException('Imagick is required to convert this image.');
                }

                $encoded = (new ImageManager(new Driver))->read($file->getRealPath())->toWebp(85);
                $stored = Storage::disk($this->disk())->put($storageKey, (string)$encoded);
            } else {
                $stored = Storage::disk($this->disk())->putFileAs(
                    dirname($storageKey) === '.' ? '' : dirname($storageKey),
                    $file,
                    basename($storageKey)
                );
            }

            if ($stored === false || !Storage::disk($this->disk())->exists($storageKey)) {
                throw new RuntimeException("Image storage failed for key [{$storageKey}].");
            }

            $this->existsCache[$storageKey] = true;
        } catch (Throwable $exception) {
            Storage::disk($this->disk())->delete($storageKey);
            Log::error('Image storage failed.', ['key' => $storageKey, 'exception' => $exception]);
            throw new RuntimeException('ذخیره‌سازی تصویر با خطا مواجه شد.', 0, $exception);
        }

        if ($oldPath !== null) {
            $this->delete($oldPath);
        }

        return $this->toPublicPath($storageKey);
    }

    private function sanitizeDirectory(string $directory): string
    {
        $directory = trim(str_replace('\\', '/', $directory), '/');

        if (preg_match('#(^|/)\.\.(/|$)#', $directory)) {
            throw new RuntimeException('Invalid image directory.');
        }

        return $directory;
    }

    public function disk(): string
    {
        return (string)config('filesystems.default', 'local');
    }

    public function toStorageKey(?string $publicPath): ?string
    {
        if ($publicPath === null || trim($publicPath) === '') {
            return null;
        }

        if ($this->isExternal($publicPath) && !$this->isCurrentApplicationUploadUrl($publicPath)) {
            return null;
        }

        $path = rawurldecode((string)(parse_url($publicPath, PHP_URL_PATH) ?: $publicPath));
        $path = str_replace('\\', '/', trim($path));

        if ($path === '' || str_contains($path, "\0") || preg_match('#(^|/)\.\.(/|$)#', $path)) {
            return null;
        }

        return $path;
    }

    public function exists(?string $publicPath): bool
    {
        $key = $this->toStorageKey($publicPath);

        if ($key === null) {
            return false;
        }
        return $this->existsCache[$key] ??= Storage::disk($this->disk())->exists($key);
    }

    private function isExternal(string $path): bool
    {
        return filter_var($path, FILTER_VALIDATE_URL) !== false;
    }

    private function isCurrentApplicationUploadUrl(string $path): bool
    {
        $appHost = parse_url((string)config('app.url'), PHP_URL_HOST);
        $pathHost = parse_url($path, PHP_URL_HOST);

        return $appHost && $pathHost && strcasecmp($appHost, $pathHost) === 0
            && str_starts_with((string)parse_url($path, PHP_URL_PATH), '/uploads/');
    }

    public function delete(?string $publicPath): bool
    {
        $key = $this->toStorageKey($publicPath);

        if ($key === null || !Storage::disk($this->disk())->exists($key)) {
            return true;
        }

        $deleted = Storage::disk($this->disk())->delete($key);
        $this->existsCache[$key] = !$deleted;

        return $deleted;
    }

    public function toPublicPath(string $storageKey): string
    {
        $key = $this->toStorageKey($storageKey);

        if ($key === null) {
            throw new RuntimeException('Invalid image storage key.');
        }

        return $key;
    }

    public function move(string $fromKey, string $toKey): string
    {
        $from = $this->toStorageKey($fromKey);
        $to = $this->toStorageKey($toKey);

        if ($from === null || $to === null || !Storage::disk($this->disk())->exists($from)) {
            throw new RuntimeException('Invalid or missing source image.');
        }

        if (!Storage::disk($this->disk())->move($from, $to) || !Storage::disk($this->disk())->exists($to)) {
            throw new RuntimeException('انتقال تصویر با خطا مواجه شد.');
        }

        $this->existsCache[$from] = false;
        $this->existsCache[$to] = true;

        return $this->toPublicPath($to);
    }

    public function url(?string $publicPath, ?string $fallback = '/empty.jpg'): string
    {
        if ($publicPath && $this->isExternal($publicPath) && !$this->isCurrentApplicationUploadUrl($publicPath)) {
            return $publicPath;
        }

        $normalized = $this->normalizePublicPath($publicPath);

        if ($normalized !== null) {
            try {
                if ($this->exists($normalized)) {
                    return Storage::disk($this->disk())->url($normalized);
                }
            } catch (Throwable $exception) {
                // ARIYA_MEDIA_URL_S3_FALLBACK_V2
                // Only public image URL lookup falls back on S3 read errors.
                // Store, delete, move, and exists() retain strict behavior.
                static $ariyaMediaWarningLogged = false;
                if (!$ariyaMediaWarningLogged) {
                    $ariyaMediaWarningLogged = true;
                    try {
                        Log::warning('Image URL lookup failed; serving fallback.', [
                            'exception_type' => get_class($exception),
                        ]);
                    } catch (Throwable $loggingException) {
                        // Logging problems must not prevent page rendering.
                    }
                }
            }
        }

        return $fallback === null ? '' : asset($fallback);
    }

    public function normalizePublicPath(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        if ($this->isExternal($path) && !$this->isCurrentApplicationUploadUrl($path)) {
            return $path;
        }

        $key = $this->toStorageKey($path);

        return $key === null ? null : $this->toPublicPath($key);
    }
}
