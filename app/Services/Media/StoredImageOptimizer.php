<?php

namespace App\Services\Media;

use League\Flysystem\FilesystemOperator;

final class StoredImageOptimizer
{
    public function __construct(
        private readonly WebpImageOptimizer $encoder,
        private readonly OptimizedImagePath $paths,
        private readonly int $quality = 82,
        private readonly float $minimumSavingsPercent = 5.0,
    ) {
    }

    public function optimize(
        FilesystemOperator $filesystem,
        string $sourceKey,
        bool $upload = false,
    ): StoredImageOptimization {
        $extension = strtolower(pathinfo($sourceKey, PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
            return new StoredImageOptimization('unsupported', $sourceKey, '', 0, 0, 0.0);
        }

        if (!$filesystem->fileExists($sourceKey)) {
            return new StoredImageOptimization('missing', $sourceKey, '', 0, 0, 0.0);
        }

        $source = $filesystem->read($sourceKey);
        $sourceBytes = strlen($source);
        $optimized = $this->encoder->optimize($source, $this->quality);
        $targetBytes = strlen($optimized->contents);
        $targetKey = $this->paths->for($sourceKey, $source);
        $savings = $sourceBytes === 0 ? 0.0 : (($sourceBytes - $targetBytes) / $sourceBytes) * 100;

        if ($targetBytes >= $sourceBytes || $savings < $this->minimumSavingsPercent) {
            return new StoredImageOptimization(
                'not-smaller', $sourceKey, $targetKey, $sourceBytes, $targetBytes,
                round($savings, 2), $optimized->width, $optimized->height
            );
        }

        if ($filesystem->fileExists($targetKey)) {
            return new StoredImageOptimization(
                'already-exists', $sourceKey, $targetKey, $sourceBytes,
                (int)$filesystem->fileSize($targetKey), round($savings, 2),
                $optimized->width, $optimized->height
            );
        }

        if ($upload) {
            $filesystem->write($targetKey, $optimized->contents, [
                'visibility' => 'public',
                'ContentType' => 'image/webp',
                'CacheControl' => 'public, max-age=31536000, immutable',
            ]);
        }

        return new StoredImageOptimization(
            $upload ? 'uploaded' : 'dry-run',
            $sourceKey,
            $targetKey,
            $sourceBytes,
            $targetBytes,
            round($savings, 2),
            $optimized->width,
            $optimized->height,
        );
    }
}
