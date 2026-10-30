<?php

namespace App\Services;

/**
 * Read-only result filter. Input rows are already checked by the catalog and
 * enriched with the application's authoritative Price::salePrice() value.
 */
final class QuickWholesaleVariantFilters
{
    public static function apply(
        array $items,
        string $productId = 'all',
        string $sort = 'default',
        string $brandId = 'all'
    ): array
    {
        if ($productId !== 'all') {
            $items = array_filter($items, static function (array $item) use ($productId): bool {
                return ctype_digit($productId)
                    && (int) $item['product_id'] === (int) $productId;
            });
        }

        if ($brandId !== 'all') {
            $items = array_filter($items, static function (array $item) use ($brandId): bool {
                if ($brandId === 'none') {
                    return ($item['brand_id'] ?? null) === null;
                }

                return ctype_digit($brandId)
                    && (int) ($item['brand_id'] ?? 0) === (int) $brandId;
            });
        }

        $items = array_values($items);
        if (!in_array($sort, ['price_asc', 'price_desc', 'stock_desc'], true)) {
            return $items;
        }

        usort($items, static function (array $left, array $right) use ($sort): int {
            if ($sort === 'stock_desc') {
                $leftStock = min((int) $left['site_stock'], (int) $left['inventory_stock']);
                $rightStock = min((int) $right['site_stock'], (int) $right['inventory_stock']);
                $comp = $rightStock <=> $leftStock;
            } else {
                $comp = (float) $left['sale_toman'] <=> (float) $right['sale_toman'];
                if ($sort === 'price_desc') {
                    $comp = -$comp;
                }
            }

            // Explicit tie-breaker makes repeated requests deterministic.
            return $comp ?: ((int) $left['price_id'] <=> (int) $right['price_id']);
        });

        return $items;
    }
}
