<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OptimizeImagesCommandTest extends TestCase
{
    public function test_dry_run_writes_a_report_but_does_not_upload_variants(): void
    {
        Storage::fake('arvan');
        Storage::fake('image_reports');
        Storage::disk('arvan')->put('products/test.png', $this->largePng());

        $exitCode = Artisan::call('images:optimize', [
            'keys' => ['products/test.png'],
            '--disk' => 'arvan',
            '--report' => 'pilot.csv',
        ]);

        $this->assertSame(0, $exitCode);
        Storage::disk('arvan')->assertExists('products/test.png');
        $this->assertCount(1, Storage::disk('arvan')->allFiles());
        Storage::disk('image_reports')->assertExists('pilot.csv');
        $this->assertStringContainsString('dry-run', Artisan::output());
    }

    public function test_upload_creates_only_a_new_variant(): void
    {
        Storage::fake('arvan');
        Storage::fake('image_reports');
        Storage::disk('arvan')->put('products/test.png', $this->largePng());

        $exitCode = Artisan::call('images:optimize', [
            'keys' => ['products/test.png'],
            '--disk' => 'arvan',
            '--upload' => true,
            '--report' => 'pilot.csv',
        ]);

        $this->assertSame(0, $exitCode);
        Storage::disk('arvan')->assertExists('products/test.png');
        $this->assertCount(2, Storage::disk('arvan')->allFiles());
        $this->assertStringContainsString('uploaded', Artisan::output());
    }

    private function largePng(): string
    {
        $image = new \Imagick();
        $image->newImage(320, 240, new \ImagickPixel('#e11d48'));
        $image->setImageFormat('png');
        $image->setOption('png:compression-level', '0');

        return $image->getImagesBlob();
    }
}
