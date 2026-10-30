<?php

namespace App\Services\Media;

use Imagick;
use ImagickException;
use InvalidArgumentException;

final class WebpImageOptimizer
{
    public function optimize(string $sourceContents, int $quality = 82): OptimizedImage
    {
        if ($quality < 1 || $quality > 100) {
            throw new InvalidArgumentException('WebP quality must be between 1 and 100.');
        }

        try {
            $image = new Imagick();
            $image->readImageBlob($sourceContents);
            $image->setIteratorIndex(0);
            if (method_exists($image, 'autoOrientImage')) {
                $image->autoOrientImage();
            } else {
                $image->autoOrient();
            }
            $image->setImageFormat('webp');
            $image->setImageCompressionQuality($quality);
            $image->setOption('webp:method', '6');
            $image->setOption('webp:alpha-quality', '90');
            $image->setImagePage(0, 0, 0, 0);

            $contents = $image->getImageBlob();
            $width = $image->getImageWidth();
            $height = $image->getImageHeight();
            $image->clear();
        } catch (ImagickException $exception) {
            throw new InvalidArgumentException('Invalid or unsupported image data.', 0, $exception);
        }

        if ($contents === '') {
            throw new InvalidArgumentException('Image encoder returned empty output.');
        }

        return new OptimizedImage($contents, 'image/webp', $width, $height);
    }
}
