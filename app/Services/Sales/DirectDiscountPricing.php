<?php
namespace App\Services\Sales;

final class DirectDiscountPricing
{
    // Legacy percent discount uses raw stored units (rials).
    public static function discountedRaw(int $regularRaw, int $percent): int
    {
        if ($regularRaw < 0 || $percent < 0 || $percent > 99) {
            throw new \InvalidArgumentException('Invalid regular price or discount percent.');
        }
        return (int) round($regularRaw * (100 - $percent) / 100, 0, PHP_ROUND_HALF_UP);
    }

    // UI inputs/outputs are TOMAN. Fixed-final remains exact in TOMAN.
    public static function finalToman(int $regularToman, string $mode, int $value): int
    {
        if ($regularToman <= 0 || $value < 0) {
            throw new \InvalidArgumentException('Invalid price or discount value.');
        }
        if ($mode === 'percent') {
            if ($value > 99) throw new \InvalidArgumentException('Percent out of range.');
            return (int) round($regularToman * (100 - $value) / 100, 0, PHP_ROUND_HALF_UP);
        }
        if ($mode === 'fixed_amount') {
            if ($value >= $regularToman && $value !== 0) {
                throw new \InvalidArgumentException('Fixed discount cannot equal or exceed regular price.');
            }
            return $regularToman - $value;
        }
        if ($mode === 'fixed_final') {
            if ($value < 1 || $value >= $regularToman) {
                throw new \InvalidArgumentException('Final price must be positive and below regular price.');
            }
            return $value;
        }
        throw new \InvalidArgumentException('Unknown direct discount mode.');
    }

    // Existing sales/order contracts store a whole-number percent as a compatibility flag.
    // The exact line discount is the difference between regular and final unit prices.
    public static function effectivePercent(int $regularToman, int $finalToman): int
    {
        if ($regularToman <= 0 || $finalToman < 0 || $finalToman > $regularToman) {
            throw new \InvalidArgumentException('Invalid effective prices.');
        }
        if ($finalToman === $regularToman) return 0;
        return min(100, max(1, (int) round(100 * ($regularToman - $finalToman) / $regularToman)));
    }
}
