<?php

namespace Tests\Unit;

use App\Models\Price;
use App\Models\Product;
use Tests\TestCase;

class PriceStockGuardTest extends TestCase
{
    public function test_zero_stock_physical_product_cannot_be_ordered(): void
    {
        $price = $this->physicalPrice(stock: 0);

        $result = $price->hasStock(1);

        $this->assertFalse($result['status']);
    }

    public function test_quantity_above_available_stock_cannot_be_ordered(): void
    {
        $price = $this->physicalPrice(stock: 2);

        $result = $price->hasStock(3);

        $this->assertFalse($result['status']);
    }

    public function test_quantity_within_available_stock_can_be_ordered(): void
    {
        $price = $this->physicalPrice(stock: 2);

        $result = $price->hasStock(2);

        $this->assertTrue($result['status']);
    }

    private function physicalPrice(int $stock): Price
    {
        $product = new Product([
            'type' => 'physical',
            'title' => 'Test product',
        ]);

        $price = new Price([
            'stock' => $stock,
            'cart_min' => null,
            'cart_max' => null,
        ]);
        $price->setRelation('product', $product);

        return $price;
    }
}
