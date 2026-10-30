<?php

namespace Tests\Unit;

use App\Models\Price;
use Tests\TestCase;

class ProductEditMemoryTest extends TestCase
{
    public function test_inventory_api_code_returns_an_existing_variant_code(): void
    {
        $price = new Price(['variant_code' => 'VAR-CUSTOM']);

        $this->assertSame('VAR-CUSTOM', $price->inventoryApiCode());
    }

    public function test_inventory_api_code_is_deterministic_when_variant_code_is_empty(): void
    {
        $price = new Price();
        $price->id = 42;

        $this->assertSame(Price::generateInventoryVariantCode(42), $price->inventoryApiCode());
        $this->assertSame($price->inventoryApiCode(), $price->inventoryApiCode());
    }

    public function test_inventory_api_code_does_not_mutate_the_price(): void
    {
        $price = new Price();
        $price->id = 42;
        $price->syncOriginal();

        $price->inventoryApiCode();

        $this->assertFalse($price->isDirty());
        $this->assertNull($price->variant_code);
    }

    public function test_price_partial_does_not_lazy_load_product_or_sort_collections(): void
    {
        $blade = file_get_contents(
            dirname(__DIR__, 2) . '/resources/views/back/products/partials/prices-include.blade.php'
        );

        $this->assertStringNotContainsString('$price->product', $blade);
        $this->assertStringNotContainsString('sortBy(', $blade);
        $this->assertStringNotContainsString('get_attributes as $attribute', $blade);
        $this->assertStringContainsString('data-options-loaded="0"', $blade);
    }

    public function test_attribute_dataset_is_emitted_once_and_templates_are_lightweight(): void
    {
        $root = dirname(__DIR__, 2);
        $form = file_get_contents($root . '/resources/views/back/products/partials/modern-form.blade.php');
        $template = file_get_contents($root . '/resources/views/back/products/partials/prices-template.blade.php');
        $bulk = file_get_contents($root . '/resources/views/back/products/partials/bulk-prices-builder.blade.php');
        $javascript = file_get_contents($root . '/public/back/assets/js/pages/products/all.js');

        $this->assertSame(1, substr_count($form, 'id="price-attribute-groups-data"'));
        $this->assertStringNotContainsString('get_attributes as $attribute', $template);
        $this->assertStringNotContainsString('bulk-price-groups-data', $bulk);
        $this->assertStringContainsString('hydratePriceAttributeSelect', $javascript);
        $this->assertStringContainsString('select2:opening', $javascript);
    }
}
