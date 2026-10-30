<?php

namespace App\Services\Media;

final readonly class OptimizedImage
{
    public function __construct(
        public string $contents,
        public string $mimeType,
        public int $width,
        public int $height,
    ) {
    }
}
