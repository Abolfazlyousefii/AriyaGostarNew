<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CategoryImageUrlTest extends TestCase
{
    public function test_category_images_support_storage_and_legacy_public_paths(): void
    {
        Storage::fake('public');
        config(['filesystems.default' => 'public']);
        Storage::disk('public')->put('categories/new.webp', 'image');

        $this->assertStringEndsWith(
            '/storage/categories/new.webp',
            category_image_url('categories/new.webp')
        );
        $this->assertStringEndsWith(
            '/uploads/categories/legacy.webp',
            category_image_url('/uploads/categories/legacy.webp')
        );
        $this->assertSame(
            'https://images.example.test/category.webp',
            category_image_url('https://images.example.test/category.webp')
        );
    }
}
