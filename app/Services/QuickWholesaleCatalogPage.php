<?php

declare(strict_types=1);

namespace App\Services;

/** Read-only search and paging after model, stock, product and price validation. */
final class QuickWholesaleCatalogPage
{
    /** @return array{items:array,total:int,page:int,last_page:int} */
    public static function slice(
        array $items,
        string $search,
        int $page = 1,
        int $perPage = 12,
        string $selectedModel = ''
    ): array {
        $perPage = max(1, min(100, $perPage));
        $needle = QuickWholesalePhoneModelMatcher::normalize(trim($search));
        if ($needle !== '') {
            $items = array_values(array_filter($items, static function (array $item) use ($needle, $selectedModel): bool {
                foreach (['product_title', 'variant_title', 'brand_name'] as $field) {
                    $value = QuickWholesalePhoneModelMatcher::normalize((string) ($item[$field] ?? ''));
                    if ($value !== '' && str_contains($value, $needle)) return true;
                }
                // The selected phone model belongs to every entry of this verified catalog.
                $model = QuickWholesalePhoneModelMatcher::normalize($selectedModel);
                return $model !== '' && str_contains($model, $needle);
            }));
        }
        $count = count($items);
        $last = max(1, (int) ceil($count / $perPage));
        $page = max(1, min($last, $page));
        return [
            'items' => array_slice(array_values($items), ($page - 1) * $perPage, $perPage),
            'total' => $count,
            'page' => $page,
            'last_page' => $last,
        ];
    }
}
