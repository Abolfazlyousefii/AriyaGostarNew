<?php
namespace Tests\Unit;

use App\Services\Inventory\CartStockRules;
use PHPUnit\Framework\TestCase;

class CartStockRulesTest extends TestCase
{
    private function line(array $changes = []): array
    {
        return array_replace(['title' => 'کابل', 'variant' => 'مدل C', 'quantity' => 3, 'minimum' => null, 'maximum' => null, 'physical' => true], $changes);
    }

    public function test_purchase_cap_is_not_reported_as_stock_shortage(): void
    {
        $result = CartStockRules::evaluate($this->line(['maximum' => 2]), 34);
        $this->assertSame('purchase_limit', $result['code']);
        $this->assertStringContainsString('حداکثر', $result['message']);
        $this->assertStringContainsString('مدل C', $result['message']);
    }

    public function test_low_stock_does_not_bypass_minimum_purchase(): void
    {
        $this->assertSame('purchase_minimum', CartStockRules::evaluate($this->line(['quantity' => 1, 'minimum' => 3]), 1)['code']);
    }

    public function test_shortage_names_the_variant_and_available_quantity(): void
    {
        $result = CartStockRules::evaluate($this->line(['quantity' => 5]), 3);
        $this->assertSame('insufficient_stock', $result['code']);
        $this->assertStringContainsString('3', $result['message']);
        $this->assertStringContainsString('کابل / مدل C', $result['message']);
    }

    public function test_live_inventory_can_be_used_when_site_snapshot_is_old(): void
    {
        $this->assertTrue(CartStockRules::evaluate($this->line(['site_stock' => 0]), 8)['status']);
    }

    public function test_disabled_item_has_a_distinct_error(): void
    {
        $this->assertSame('not_sellable', CartStockRules::evaluate($this->line(), 34, false)['code']);
    }

    public function test_unmapped_variant_does_not_claim_zero_stock(): void
    {
        $this->assertSame('mapping_missing', CartStockRules::evaluate($this->line(), 0, true, false)['code']);
    }

    public function test_downloads_do_not_require_inventory(): void
    {
        $this->assertTrue(CartStockRules::evaluate($this->line(['physical' => false]), 0, false, false)['status']);
    }

    public function test_repeated_title_is_not_repeated_in_customer_label(): void
    {
        $this->assertSame('کابل', CartStockRules::label('کابل', 'کابل'));
    }

    public function test_twenty_four_hour_boundary_and_unknown_age_require_review(): void
    {
        $this->assertTrue(CartStockRules::isStale('2026-10-27 12:00:00', '2026-10-28 12:00:00'));
        $this->assertFalse(CartStockRules::isStale('2026-10-27 12:00:01', '2026-10-28 12:00:00'));
        $this->assertTrue(CartStockRules::isStale(null, '2026-10-28 12:00:00'));
    }
}
