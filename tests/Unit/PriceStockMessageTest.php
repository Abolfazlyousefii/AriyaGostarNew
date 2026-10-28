<?php
namespace Tests\Unit;

use App\Models\Price;
use PHPUnit\Framework\TestCase;

class PriceStockMessageTest extends TestCase
{
    public function test_legacy_price_check_distinguishes_purchase_cap(): void
    {
        $price = new Price();
        $price->setRawAttributes(['stock' => 34, 'cart_min' => null, 'cart_max' => 2, 'variant_name' => 'مدل C']);
        $price->setRelation('product', new class {
            public $title = 'کابل';
            public function isDownload() { return false; }
        });
        $result = $price->hasStock(3);
        $this->assertSame('purchase_limit', $result['code'] ?? null);
        $this->assertStringContainsString('حداکثر', $result['message']);
    }
}
