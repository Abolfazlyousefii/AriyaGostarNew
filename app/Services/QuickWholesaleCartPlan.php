<?php

declare(strict_types=1);

namespace App\Services;

final class QuickWholesaleCartPlan
{
    public static function build(array $requestedItems, array $catalogItems, array $currentQuantities): array
    {
        $catalog = [];
        foreach ($catalogItems as $item) {
            $catalog[(int) $item['price_id']] = $item;
        }

        $seen = [];
        $lines = [];
        $requestedByProduct = [];

        foreach ($requestedItems as $requested) {
            $priceId = (int) ($requested['price_id'] ?? 0);
            $quantity = (int) ($requested['quantity'] ?? 0);

            if (isset($seen[$priceId])) {
                throw new QuickWholesaleSelectionException(
                    'یک تنوع به‌صورت تکراری ارسال شده است.',
                    $priceId ?: null
                );
            }
            $seen[$priceId] = true;

            if (!isset($catalog[$priceId])) {
                throw new QuickWholesaleSelectionException(
                    'این کالا دیگر برای مدل انتخاب‌شده قابل سفارش نیست.',
                    $priceId ?: null
                );
            }

            $item = $catalog[$priceId];
            $label = trim((string) ($item['variant_title'] ?: $item['product_title']));
            $minimum = max(1, (int) $item['min_order']);
            $maximum = max(0, (int) $item['max_order']);
            $current = max(0, (int) ($currentQuantities[$priceId] ?? 0));
            $final = $current + $quantity;

            if ($quantity < 1 || $final < $minimum) {
                throw new QuickWholesaleSelectionException(
                    "حداقل تعداد سفارش «{$label}» {$minimum} عدد است.",
                    $priceId
                );
            }

            if ($maximum < $minimum || $final > $maximum) {
                throw new QuickWholesaleSelectionException(
                    "حداکثر تعداد قابل سفارش «{$label}» {$maximum} عدد است.",
                    $priceId
                );
            }

            $productId = (int) $item['product_id'];
            $lines[] = [
                'price_id' => $priceId,
                'product_id' => $productId,
                'requested_quantity' => $quantity,
                'final_quantity' => $final,
                'label' => $label,
            ];
            $requestedByProduct[$productId] = ($requestedByProduct[$productId] ?? 0) + $quantity;
        }

        return [
            'lines' => $lines,
            'requested_by_product' => $requestedByProduct,
        ];
    }
}
