<?php
namespace Tests\Unit;

use App\Models\Cart;
use PHPUnit\Framework\TestCase;

class StaleCartPriceTest extends TestCase
{
    public function test_removed_variant_keeps_cart_line_without_breaking_totals(): void
    {
        $cart = new Cart();
        $product = new class {
            public $pivot;
            public function prices() { return new class { public function find($id) { return null; } }; }
        };
        $product->pivot = (object) ['price_id' => 999, 'quantity' => 3];
        $cart->setRelation('products', collect([$product]));
        $cart->setRelation('discount', null);
        $this->assertSame(0, $cart->price);
        $this->assertSame(0, $cart->discountPrice());
        $this->assertSame(0, $cart->totalDiscount());
        $this->assertCount(1, $cart->products);
    }
}
