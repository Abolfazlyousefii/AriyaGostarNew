<?php
namespace App\Services\Sales;

/** Pure validation and price planning: returns no partial plan when any variant fails. */
final class BulkDirectDiscountPlan
{
    public static function build(array $regularByPriceId, string $mode, int $value): array
    {
        if (!$regularByPriceId) {
            throw new \InvalidArgumentException('No active price variants.');
        }
        if (count($regularByPriceId) > 5000) {
            throw new \InvalidArgumentException('Too many variants for a single bulk operation.');
        }
        $plan = [];
        foreach ($regularByPriceId as $id => $regularToman) {
            $id = (int) $id;
            $regularToman = (int) $regularToman;
            if ($id < 1 || $regularToman < 1) {
                throw new \InvalidArgumentException('Invalid variant ID or base price.');
            }
            $plan[$id] = DirectDiscountPricing::finalToman($regularToman, $mode, $value);
        }
        return $plan;
    }
}
