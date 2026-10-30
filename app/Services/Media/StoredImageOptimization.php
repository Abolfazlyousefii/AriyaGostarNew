<?php

namespace App\Services\Media;

final readonly class StoredImageOptimization
{
    public function __construct(
        public string $status,
        public string $sourceKey,
        public string $targetKey,
        public int $sourceBytes,
        public int $targetBytes,
        public float $savingsPercent,
        public int $width = 0,
        public int $height = 0,
    ) {
    }
}
