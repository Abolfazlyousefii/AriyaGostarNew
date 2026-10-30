<?php

namespace Tests\Unit\Media;

use App\Services\Media\WebpImageOptimizer;
use PHPUnit\Framework\TestCase;

class WebpImageOptimizerTest extends TestCase
{
    public function test_it_creates_a_smaller_webp_without_changing_dimensions(): void
    {
        $source = $this->pngFixture(320, 240, false);

        $result = (new WebpImageOptimizer())->optimize($source, 82);

        $this->assertNotNull($result);
        $this->assertSame('image/webp', $result->mimeType);
        $this->assertSame(320, $result->width);
        $this->assertSame(240, $result->height);
        $this->assertLessThan(strlen($source), strlen($result->contents));
    }

    public function test_it_preserves_transparency(): void
    {
        $source = $this->pngFixture(100, 100, true);

        $result = (new WebpImageOptimizer())->optimize($source, 82);

        $this->assertNotNull($result);
        $image = new \Imagick();
        $image->readImageBlob($result->contents);
        $pixel = $image->getImagePixelColor(0, 0)->getColor(true);
        $this->assertLessThan(0.05, $pixel['a']);
    }

    public function test_it_rejects_invalid_image_data(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new WebpImageOptimizer())->optimize('not-an-image', 82);
    }

    private function pngFixture(int $width, int $height, bool $transparent): string
    {
        $image = new \Imagick();
        $image->newImage($width, $height, $transparent ? new \ImagickPixel('transparent') : new \ImagickPixel('#e11d48'));
        if ($transparent) {
            $image->setImageAlphaChannel(\Imagick::ALPHACHANNEL_ACTIVATE);
            $draw = new \ImagickDraw();
            $draw->setFillColor('#2563eb');
            $draw->rectangle(20, 20, $width - 20, $height - 20);
            $image->drawImage($draw);
        }
        $image->setImageFormat('png');
        if (!$transparent) {
            // Keep the fixture deliberately unoptimized so the size assertion is deterministic.
            $image->setOption('png:compression-level', '0');
        }

        return $image->getImagesBlob();
    }
}
