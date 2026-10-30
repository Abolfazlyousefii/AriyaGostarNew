<?php

namespace Tests\Unit\Media;

use App\Services\Media\OptimizedImagePath;
use PHPUnit\Framework\TestCase;

class OptimizedImagePathTest extends TestCase
{
    public function test_it_builds_a_deterministic_new_key_without_overwriting_the_source(): void
    {
        $path = new OptimizedImagePath();

        $first = $path->for('products/photo.png', 'source-contents');
        $second = $path->for('products/photo.png', 'source-contents');

        $this->assertSame($first, $second);
        $this->assertSame('optimized/webp/products/photo-59e522545628.webp', $first);
        $this->assertNotSame('products/photo.png', $first);
    }

    public function test_different_source_contents_produce_different_keys(): void
    {
        $path = new OptimizedImagePath();

        $this->assertNotSame(
            $path->for('products/photo.png', 'version-one'),
            $path->for('products/photo.png', 'version-two')
        );
    }

    public function test_it_rejects_unsafe_source_keys(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new OptimizedImagePath())->for('../secret.png', 'source-contents');
    }
}
