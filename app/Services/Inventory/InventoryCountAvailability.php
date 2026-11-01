<?php

namespace App\Services\Inventory;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/** Read the ERP boundary on every check; never toggle it or change stock. */
final class InventoryCountAvailability
{
    public static function message(string $label): string
    {
        return 'فروش «'.$label.'» به دلیل انبارگردانی موقتاً متوقف است. پس از بازگشایی فروش در انبار، امکان خرید دوباره فراهم می‌شود.';
    }

    /** Includes ERP snapshots, current descendants and bundles containing counted products. */
    public function blockedProductIds(array $productIds): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $productIds), fn ($id) => $id > 0)));
        if (!$ids) return [];
        $db = DB::connection('inv');
        $state = $db->table('inventory_count_freezes')->where('id', 1)->first();
        if (!$state) throw new \RuntimeException('Inventory count boundary missing');
        if (!$state->enabled) return [];
        if ($state->all_products) return $ids;

        $snapshot = array_map('intval', json_decode($state->product_ids ?? '[]', true, 512, JSON_THROW_ON_ERROR));
        $categories = array_map('intval', json_decode($state->category_ids ?? '[]', true, 512, JSON_THROW_ON_ERROR));
        $frontier = $categories;
        while ($frontier) {
            $children = $db->table('categories')->whereIn('parent_id', $frontier)->pluck('id')->map(fn ($id) => (int) $id)->all();
            $frontier = array_values(array_diff($children, $categories));
            $categories = array_values(array_unique(array_merge($categories, $frontier)));
        }

        $blocked = $db->table('products')->whereIn('id', $ids)
            ->where(fn ($q) => $q->whereIn('id', $snapshot)->orWhereIn('category_id', $categories))
            ->pluck('id')->all();
        // Apply to physical as well as automatic bundles, exactly as the ERP guard does.
        $bundles = $db->table('product_bundle_components as r')
            ->join('product_variants as b', 'b.id', '=', 'r.bundle_variant_id')
            ->join('product_variants as part', 'part.id', '=', 'r.component_variant_id')
            ->join('products as p', 'p.id', '=', 'part.product_id')
            ->whereIn('b.product_id', $ids)
            ->where(fn ($q) => $q->whereIn('p.id', $snapshot)->orWhereIn('p.category_id', $categories))
            ->pluck('b.product_id')->all();
        return array_values(array_unique(array_map('intval', array_merge($blocked, $bundles))));
    }

    public function forProduct(int $productId, string $title): array
    {
        try {
            $blocked = in_array($productId, $this->blockedProductIds([$productId]), true);
            return ['blocked' => $blocked, 'message' => $blocked ? self::message($title) : null];
        } catch (\Throwable $exception) {
            Log::warning('Product inventory count lookup unavailable', ['exception' => get_class($exception)]);
            return ['blocked' => true, 'message' => 'بررسی وضعیت فروش در انبار موقتاً ممکن نیست. لطفاً کمی بعد دوباره تلاش کنید.'];
        }
    }
}
