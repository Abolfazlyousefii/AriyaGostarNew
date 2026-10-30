<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class SupportedImage implements ValidationRule
{
    private const WEB_MIMES = [
        'image/jpeg', 'image/png', 'image/webp', 'image/gif',
        'image/bmp', 'image/x-ms-bmp', 'image/avif',
    ];

    private const CONVERTIBLE_MIMES = [
        'image/heic', 'image/heif', 'image/tiff', 'image/x-tiff',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile || !$value->isValid()) {
            $fail('فایل انتخاب‌شده تصویر معتبر نیست.');
            return;
        }

        $mime = (string) $value->getMimeType();

        if (in_array($mime, self::WEB_MIMES, true) && @getimagesize($value->getRealPath()) !== false) {
            return;
        }

        if (in_array($mime, self::CONVERTIBLE_MIMES, true)) {
            if (!extension_loaded('imagick')) {
                $fail('فرمت HEIC/HEIF/TIFF به Imagick فعال روی سرور نیاز دارد.');
                return;
            }

            try {
                $image = new \Imagick;
                $image->pingImage($value->getRealPath());
                $image->clear();
                return;
            } catch (\Throwable) {
                $fail('رمزگشایی یا تبدیل این تصویر روی سرور امکان‌پذیر نیست.');
            }
            return;
        }

        $fail('فرمت تصویر مجاز نیست. فرمت‌های JPG، PNG، WebP، GIF، BMP و AVIF پذیرفته می‌شوند.');
    }
}
