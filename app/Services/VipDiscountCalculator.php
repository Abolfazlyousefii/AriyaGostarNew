<?php
namespace App\Services;

/** Pure Toman arithmetic. Shipping is intentionally excluded from VIP eligibility. */
final class VipDiscountCalculator
{
    public static function amount($merchandiseSubtotal, $couponDiscount, $percent): int
    {
        $base = max(0, (int) round($merchandiseSubtotal) - max(0, (int) round($couponDiscount)));
        $rate = min(100.0, max(0.0, (float) $percent));
        return min($base, max(0, (int) round($base * $rate / 100)));
    }
}
