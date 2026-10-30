<?php

namespace Tests\Unit\Media;

use App\Services\Media\OptimizedImagePath;
use App\Services\Media\StoredImageOptimizer;
use App\Services\Media\WebpImageOptimizer;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use PHPUnit\Framework\TestCase;

class StoredImageOptimizerTest extends TestCase
{
    private string $directory;
    private Filesystem $filesystem;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = sys_get_temp_dir() . '/ariya-image-test-' . bin2hex(random_bytes(6));
        mkdir($this->directory, 0700, true);
        $this->filesystem = new Filesystem(new LocalFilesystemAdapter($this->directory));
    }

    protected function tearDown(): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($this->directory);
        parent::tearDown();
    }

    public function test_dry_run_leaves_storage_unchanged(): void
    {
        $source = $this->largePng();
        $this->filesystem->write('products/test.png', $source);

        $result = $this->optimizer()->optimize($this->filesystem, 'products/test.png', false);

        $this->assertSame('dry-run', $result->status);
        $this->assertTrue($this->filesystem->fileExists('products/test.png'));
        $this->assertFalse($this->filesystem->fileExists($result->targetKey));
        $this->assertLessThan($result->sourceBytes, $result->targetBytes);
    }

    public function test_upload_is_idempotent_and_never_deletes_the_original(): void
    {
        $source = $this->largePng();
        $this->filesystem->write('products/test.png', $source);

        $first = $this->optimizer()->optimize($this->filesystem, 'products/test.png', true);
        $second = $this->optimizer()->optimize($this->filesystem, 'products/test.png', true);

        $this->assertSame('uploaded', $first->status);
        $this->assertSame('already-exists', $second->status);
        $this->assertSame($first->targetKey, $second->targetKey);
        $this->assertTrue($this->filesystem->fileExists('products/test.png'));
        $this->assertTrue($this->filesystem->fileExists($first->targetKey));
    }

    public function test_it_skips_unsupported_formats(): void
    {
        $this->filesystem->write('products/animation.gif', 'gif-data');

        $result = $this->optimizer()->optimize($this->filesystem, 'products/animation.gif', true);

        $this->assertSame('unsupported', $result->status);
        $this->assertSame('', $result->targetKey);
    }

    private function optimizer(): StoredImageOptimizer
    {
        return new StoredImageOptimizer(new WebpImageOptimizer(), new OptimizedImagePath(), 82, 5.0);
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
