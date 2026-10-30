<?php

namespace Tests\Unit;

use App\Services\QuickWholesalePhoneModelMatcher;
use App\Services\QuickWholesaleVariantFilters;
use App\Services\Sales\BulkDirectDiscountPlan;
use App\Services\Sales\DirectDiscountPricing;
use PHPUnit\Framework\TestCase;

class RecoveryBusinessLogicTest extends TestCase
{
    public function test_quick_wholesale_normalizes_persian_digits_and_does_not_match_pro_as_base(): void
    {
        $this->assertSame('iphone:15:base', QuickWholesalePhoneModelMatcher::selectedModelKey('آیفون ۱۵'));
        $this->assertTrue(QuickWholesalePhoneModelMatcher::inventoryModelMatches('iphone 15', 'iPhone 14/15'));
        $this->assertFalse(QuickWholesalePhoneModelMatcher::inventoryModelMatches('iphone 15', 'iPhone 15 Pro'));
    }

    public function test_direct_discount_supports_all_three_approved_modes(): void
    {
        $this->assertSame(800_000, DirectDiscountPricing::finalToman(1_000_000, 'percent', 20));
        $this->assertSame(850_000, DirectDiscountPricing::finalToman(1_000_000, 'fixed_amount', 150_000));
        $this->assertSame(725_000, DirectDiscountPricing::finalToman(1_000_000, 'fixed_final', 725_000));
    }

    public function test_quick_wholesale_can_filter_by_brand_without_changing_sorting(): void
    {
        $items = [
            ['price_id' => 2, 'product_id' => 20, 'brand_id' => null, 'sale_toman' => 90, 'site_stock' => 5, 'inventory_stock' => 5],
            ['price_id' => 1, 'product_id' => 10, 'brand_id' => 7, 'sale_toman' => 120, 'site_stock' => 5, 'inventory_stock' => 5],
            ['price_id' => 3, 'product_id' => 30, 'brand_id' => 7, 'sale_toman' => 80, 'site_stock' => 5, 'inventory_stock' => 5],
        ];

        $branded = QuickWholesaleVariantFilters::apply($items, 'all', 'price_asc', '7');
        $unbranded = QuickWholesaleVariantFilters::apply($items, 'all', 'default', 'none');

        $this->assertSame([3, 1], array_column($branded, 'price_id'));
        $this->assertSame([2], array_column($unbranded, 'price_id'));
    }

    public function test_bulk_direct_discount_is_all_or_nothing_when_any_variant_is_invalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        BulkDirectDiscountPlan::build([
            11 => 1_000_000,
            12 => 100_000,
        ], 'fixed_amount', 100_000);
    }
}
