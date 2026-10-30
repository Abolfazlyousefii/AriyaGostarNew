<?php

namespace App\Support;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Diversifies homepage product widgets without changing product, price or ERP data.
 * The original widget query remains authoritative for published/discount/category/order rules.
 */
final class AriyaHomeProductMixer
{
    private static array $shownProductIds = [];
    private static ?array $categoryRows = null;

    public static function forWidget($widget, Builder $query): Collection
    {
        $limit = self::requestedCount((int) $widget->option('number', 10));

        // Respect explicit sold-out widget configuration; otherwise prefer buyable items.
        if ($widget->option('inventory_status', 'all') === 'all') {
            $query = clone $query;
            $query->available();
        }

        $poolLimit = min(240, max(90, $limit * 16));
        $pool = (clone $query)->limit($poolLimit)->get()->keyBy('id');

        if ($pool->isEmpty()) {
            return collect();
        }

        $categories = self::categoryRows();
        $groupCache = [];
        $groupForProduct = static function ($product) use ($categories, &$groupCache): string {
            $categoryId = (int) ($product->category_id ?? 0);
            if (!array_key_exists($categoryId, $groupCache)) {
                $groupCache[$categoryId] = self::categoryGroup($categoryId, $categories);
            }
            return $groupCache[$categoryId];
        };

        $poolGroups = [];
        foreach ($pool as $product) {
            $poolGroups[$groupForProduct($product)] = true;
        }

        // A large batch of fresh phone covers must not hide older cables/chargers.
        // The same original query is cloned, so selected categories, sale status,
        // publication, discounts and sort order are still enforced by the DB.
        if (count($poolGroups) < 4 && count($pool) >= $limit) {
            $groupCategoryIds = self::groupCategoryIds($categories);
            foreach (['cable', 'charger', 'audio', 'powerbank', 'glass', 'other-electronic', 'cover'] as $group) {
                if (isset($poolGroups[$group]) || empty($groupCategoryIds[$group])) {
                    continue;
                }
                $extra = (clone $query)
                    ->whereIn('products.category_id', $groupCategoryIds[$group])
                    ->limit(min(8, max(3, $limit)))
                    ->get();
                foreach ($extra as $product) {
                    $pool->put($product->id, $product);
                }
            }
        }

        $ids = $pool->keys()->map(static fn ($id) => (int) $id)->all();
        $groups = [];
        foreach ($pool as $product) {
            $groups[(int) $product->id] = $groupForProduct($product);
        }

        $selected = self::mixIds($ids, $groups, $limit, self::$shownProductIds);
        foreach ($selected as $id) {
            self::$shownProductIds[$id] = true;
        }

        return collect($selected)->map(static fn (int $id) => $pool->get($id));
    }

    public static function requestedCount(int $number): int
    {
        return min(60, max(1, $number));
    }

    /** Pure, deterministic balancing; preserves ranking within each product family. */
    public static function mixIds(array $ids, array $groups, int $limit, array $previouslyShown = []): array
    {
        $limit = self::requestedCount($limit);
        $unique = [];
        foreach ($ids as $id) {
            $id = (int) $id;
            if ($id > 0 && !isset($unique[$id])) {
                $unique[$id] = $id;
            }
        }
        $ids = array_values($unique);
        $unseen = array_values(array_filter($ids, static fn (int $id): bool => !isset($previouslyShown[$id])));
        // Keep earlier widgets distinct when enough inventory is available.
        $eligible = count($unseen) >= $limit ? $unseen : array_merge(
            $unseen,
            array_values(array_filter($ids, static fn (int $id): bool => isset($previouslyShown[$id])))
        );

        $buckets = [];
        foreach ($eligible as $id) {
            $group = (string) ($groups[$id] ?? 'unknown');
            $buckets[$group][] = $id;
        }
        $result = [];
        while (count($result) < $limit) {
            $advanced = false;
            foreach ($buckets as &$bucket) {
                if (!$bucket) {
                    continue;
                }
                $result[] = array_shift($bucket);
                $advanced = true;
                if (count($result) === $limit) {
                    break;
                }
            }
            unset($bucket);
            if (!$advanced) {
                break;
            }
        }
        return $result;
    }

    /** Maps the real store taxonomy, not product names, to recognizable families. */
    public static function categoryGroup(int $categoryId, array $categories): string
    {
        $visited = [];
        $chain = [];
        $id = $categoryId;
        for ($depth = 0; $id > 0 && $depth < 12 && !isset($visited[$id]); $depth++) {
            $visited[$id] = true;
            $node = $categories[$id] ?? null;
            if (!$node) {
                break;
            }
            $chain[] = ['id' => $id, 'title' => (string) ($node['title'] ?? '')];
            $id = (int) ($node['parent'] ?? 0);
        }

        foreach ($chain as $node) {
            $title = function_exists('mb_strtolower')
                ? mb_strtolower($node['title'], 'UTF-8')
                : strtolower($node['title']);
            if (preg_match('/گارد|قاب|کاور|کیس|phone\s*case|phone\s*cover/u', $title)) return 'cover';
            if (preg_match('/کابل|cable|charging\s*cord/u', $title)) return 'cable';
            if (preg_match('/سرشارژ|شارژر|آداپتور|adapter|charger/u', $title)) return 'charger';
            if (preg_match('/هندزفری|هدفون|ایرپاد|هدست|اسپیکر|بلندگو|earbud|headphone|speaker/u', $title)) return 'audio';
            if (preg_match('/پاوربانک|پاور بانک|power\s*bank/u', $title)) return 'powerbank';
            if (preg_match('/گلس|محافظ صفحه|screen\s*protector/u', $title)) return 'glass';
            if (preg_match('/هولدر|هلدر|نگهدارنده|پایه|holder|stand|تبدیل|مبدل|هاب|hub|الکترونیک|برقی|دیجیتال/u', $title)) return 'other-electronic';
        }

        if (count($chain) >= 2) {
            // Group related subcategories under the child of the top-level category.
            return 'category:' . $chain[count($chain) - 2]['id'];
        }
        return 'category:' . ($chain[0]['id'] ?? 0);
    }

    private static function categoryRows(): array
    {
        if (self::$categoryRows !== null) {
            return self::$categoryRows;
        }
        $map = [];
        foreach (Category::query()->where('type', 'productcat')->get(['id', 'category_id', 'title']) as $category) {
            $map[(int) $category->id] = [
                'parent' => (int) ($category->category_id ?? 0),
                'title' => (string) $category->title,
            ];
        }
        return self::$categoryRows = $map;
    }

    private static function groupCategoryIds(array $categoryRows): array
    {
        $result = [];
        foreach ($categoryRows as $id => $_) {
            $group = self::categoryGroup((int) $id, $categoryRows);
            $result[$group][] = (int) $id;
        }
        return $result;
    }
}
