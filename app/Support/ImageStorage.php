<?php

namespace App\Support;

use App\Rules\SupportedImage;
use App\Services\Media\ImageStorageService;
use Illuminate\Http\UploadedFile;

final class ImageStorage
{

    public static function store(UploadedFile $file, string $directory): string
    {
        return app(ImageStorageService::class)->store($file, $directory);
    }

    public static function delete(?string $path): bool
    {
        return app(ImageStorageService::class)->delete($path);
    }

    public static function replace(UploadedFile $file, string $directory, ?string $oldPath, callable $persist): string
    {
        return app(ImageStorageService::class)->replace($file, $directory, $oldPath, $persist);
    }

    public static function move(string $fromKey, string $toKey): string
    {
        return app(ImageStorageService::class)->move($fromKey, $toKey);
    }

    public static function exists(?string $path): bool
    {
        return app(ImageStorageService::class)->exists($path);
    }

    public static function publicPath(string $path): string
    {
        return app(ImageStorageService::class)->toPublicPath($path);
    }

    public static function diskPath(?string $path): ?string
    {
        return app(ImageStorageService::class)->toStorageKey($path);
    }

    public static function url(?string $path, ?string $fallback = null): ?string
    {
        return app(ImageStorageService::class)->url($path, $fallback);
    }

    public static function validationRules(bool $required = false, ?int $maxKilobytes = null): array
    {
        $rules = [
            $required ? 'required' : 'nullable',
            new SupportedImage,
        ];

        if ($maxKilobytes !== null) {
            $rules[] = 'max:' . $maxKilobytes;
        }

        return $rules;
    }
}
