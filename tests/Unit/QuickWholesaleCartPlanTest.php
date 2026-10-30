<?php

namespace Tests\Unit;

use App\Services\QuickWholesaleCartPlan;
use App\Services\QuickWholesaleSelectionException;
use PHPUnit\Framework\TestCase;

class QuickWholesaleCartPlanTest extends TestCase
{
    public function test_it_builds_an_atomic_plan_across_multiple_products(): void
    {
        $plan = QuickWholesaleCartPlan::build(
            [
                ['price_id' => 11, 'quantity' => 3],
                ['price_id' => 22, 'quantity' => 2],
            ],
            [
                ['price_id' => 11, 'product_id' => 101, 'product_title' => 'گارد الف', 'variant_title' => 'آیفون ۱۳', 'min_order' => 2, 'max_order' => 8],
                ['price_id' => 22, 'product_id' => 202, 'product_title' => 'گارد ب', 'variant_title' => 'آیفون ۱۳', 'min_order' => 1, 'max_order' => 5],
            ],
            [11 => 1]
        );

        $this->assertSame(4, $plan['lines'][0]['final_quantity']);
        $this->assertSame(2, $plan['lines'][1]['final_quantity']);
        $this->assertSame([101 => 3, 202 => 2], $plan['requested_by_product']);
    }

    public function test_it_rejects_a_variant_that_is_not_in_the_verified_catalog(): void
    {
        $this->expectException(QuickWholesaleSelectionException::class);
        $this->expectExceptionMessage('دیگر برای مدل انتخاب‌شده قابل سفارش نیست');

        QuickWholesaleCartPlan::build(
            [['price_id' => 999, 'quantity' => 1]],
            [],
            []
        );
    }

    public function test_it_rejects_a_quantity_below_minimum_without_losing_other_selections(): void
    {
        try {
            QuickWholesaleCartPlan::build(
                [['price_id' => 11, 'quantity' => 1]],
                [['price_id' => 11, 'product_id' => 101, 'product_title' => 'گارد الف', 'variant_title' => 'آیفون ۱۳', 'min_order' => 3, 'max_order' => 8]],
                []
            );
            $this->fail('The invalid quantity should be rejected.');
        } catch (QuickWholesaleSelectionException $exception) {
            $this->assertSame(11, $exception->priceId());
            $this->assertStringContainsString('حداقل تعداد', $exception->getMessage());
        }
    }

    public function test_it_rejects_a_final_cart_quantity_above_current_inventory_limit(): void
    {
        $this->expectException(QuickWholesaleSelectionException::class);
        $this->expectExceptionMessage('حداکثر تعداد قابل سفارش');

        QuickWholesaleCartPlan::build(
            [['price_id' => 11, 'quantity' => 3]],
            [['price_id' => 11, 'product_id' => 101, 'product_title' => 'گارد الف', 'variant_title' => 'آیفون ۱۳', 'min_order' => 1, 'max_order' => 5]],
            [11 => 4]
        );
    }

    public function test_it_rejects_duplicate_price_ids_even_if_validation_is_bypassed(): void
    {
        $this->expectException(QuickWholesaleSelectionException::class);
        $this->expectExceptionMessage('تکراری');

        QuickWholesaleCartPlan::build(
            [
                ['price_id' => 11, 'quantity' => 1],
                ['price_id' => 11, 'quantity' => 1],
            ],
            [['price_id' => 11, 'product_id' => 101, 'product_title' => 'گارد الف', 'variant_title' => 'آیفون ۱۳', 'min_order' => 1, 'max_order' => 5]],
            []
        );
    }
}
