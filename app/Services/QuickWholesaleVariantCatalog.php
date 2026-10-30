<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;

/** Read-only variant discovery; does not authorize cart operations. */
final class QuickWholesaleVariantCatalog
{
    public function findForModel(string $selected): array
    {
        // ARIYA_TASK01_STAGE05_VARIANT_SEARCH_V2
        if (QuickWholesalePhoneModelMatcher::selectedModelKey($selected) === null) {
            return [];
        }

        $inv = DB::connection('inv');
        $site = DB::connection();

        $modelIds = $inv->table('model_lists')
            ->where('brand', 'Apple (iPhone)')
            ->get(['id', 'model_name'])
            ->filter(static fn ($model): bool =>
                QuickWholesalePhoneModelMatcher::inventoryModelMatches(
                    $selected, (string) $model->model_name
                )
            )->pluck('id')->all();

        if (!$modelIds) {
            return [];
        }

        $variants = $inv->table('product_variants')
            ->whereIn('model_list_id', $modelIds)
            ->where('is_active', 1)
            ->where('sales_enabled', 1)
            ->where('stock', '>', 0)
            ->get(['id', 'product_id', 'model_list_id', 'variant_code', 'stock'])
            ->keyBy('id');

        if ($variants->isEmpty()) {
            return [];
        }

        // Strict external_variant_id mapping only; no fuzzy name/code fallback.
        $prices = $site->table('prices as pr')
            ->join('products as p', 'p.id', '=', 'pr.product_id')
            ->join('categories as c', 'c.id', '=', 'p.category_id')
            ->leftJoin('brands as b', 'b.id', '=', 'p.brand_id')
            ->whereIn('pr.external_variant_id', $variants->keys()->all())
            ->whereNull('pr.deleted_at')
            ->where('pr.stock', '>', 0)
            ->where('p.published', 1)
            ->where('p.type', 'physical')
            ->where('c.type', 'productcat')
            ->get([
                'pr.id as price_id', 'pr.product_id', 'pr.external_variant_id',
                'pr.variant_code', 'pr.variant_name', 'pr.stock as site_stock',
                'pr.cart_min', 'pr.cart_max', 'p.title as product_title',
                'p.brand_id', 'b.name as brand_name',
            ]);

        $results = [];
        foreach ($prices as $price) {
            $variant = $variants->get((int) $price->external_variant_id);
            if (!$variant || (int) $variant->product_id !== (int) $price->product_id) {
                continue;
            }

            $siteCode = trim((string) $price->variant_code);
            $inventoryCode = trim((string) $variant->variant_code);
            if ($siteCode !== '' && $inventoryCode !== '' && $siteCode !== $inventoryCode) {
                continue;
            }

            $title = (string) $price->product_title;
            if (!str_contains($title, 'گارد') && !str_contains($title, 'قاب')) {
                continue;
            }

            $minimum = max(1, (int) ($price->cart_min ?? 1));
            $maximum = min((int) $price->site_stock, (int) $variant->stock);
            if ($price->cart_max !== null) {
                $maximum = min($maximum, (int) $price->cart_max);
            }
            if ($maximum < $minimum) {
                continue;
            }

            $results[(int) $price->price_id] = [
                'price_id' => (int) $price->price_id,
                'product_id' => (int) $price->product_id,
                'model_list_id' => (int) $variant->model_list_id,
                'inventory_variant_id' => (int) $variant->id,
                'product_title' => $title,
                'brand_id' => $price->brand_id === null ? null : (int) $price->brand_id,
                'brand_name' => trim((string) ($price->brand_name ?? '')),
                'variant_title' => (string) $price->variant_name,
                'site_stock' => (int) $price->site_stock,
                'inventory_stock' => (int) $variant->stock,
                'min_order' => $minimum,
                'max_order' => $maximum,
            ];
        }

        ksort($results, SORT_NUMERIC);
        return array_values($results);
    }
}
