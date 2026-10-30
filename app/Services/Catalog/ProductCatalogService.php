<?php

namespace App\Services\Catalog;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Price;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ProductCatalogService
{
    public function build(Builder $query, Request $request, ?Category $categoryContext = null): array
    {
        $now = now();

        $effectivePriceSql = 'CASE WHEN prices.discount > 0 AND (prices.discount_expire_at IS NULL OR prices.discount_expire_at > ?) THEN prices.discount_price ELSE prices.regular_price END';

        $priceScopeQuery = clone $query;
        $priceScopeQuery->select('products.id')->reorder();

        $priceStats = Price::query()
            ->where('stock', '>', 0)
            ->whereIn('product_id', $priceScopeQuery)
            ->selectRaw(
                "MIN({$effectivePriceSql}) AS min_price, MAX({$effectivePriceSql}) AS max_price",
                [$now, $now]
            )
            ->first();

        $catalogMinPrice = max(0, (int) ($priceStats->min_price ?? 0));
        $catalogMaxPrice = max($catalogMinPrice, (int) ($priceStats->max_price ?? 0));

        $priceRange = max(0, $catalogMaxPrice - $catalogMinPrice);

        $firstPriceLevelEnd = (int) floor(
            $catalogMinPrice + ($priceRange / 3)
        );

        $secondPriceLevelEnd = (int) floor(
            $catalogMinPrice + (($priceRange * 2) / 3)
        );

        $query->with([
            'category',
            'brand',
            'labels',
            'lowestPrice',
        ]);

        if (
            $categoryContext &&
            $categoryContext->getFilter() &&
            $request->has('filters')
        ) {
            $query->frontFilter($categoryContext);

            // frontFilter فیلترهای تخصصی دسته را اعمال می‌کند،
            // سپس ترتیب را پاک می‌کنیم تا sort مشترک پایین اعمال شود.
            $query->reorder();
        }

        $search = trim((string) $request->input(
            'q',
            $request->input('s', '')
        ));

        if ($search !== '') {
            $query->search($search);
        }

        $selectedCategoryIds = collect(
            (array) $request->input('categories', [])
        )
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($selectedCategoryIds->isNotEmpty()) {
            $categoryIds = collect();

            Category::whereIn('id', $selectedCategoryIds)
                ->get()
                ->each(function (Category $category) use ($categoryIds) {
                    $categoryIds->push(...$category->allChildCategories());
                });

            $categoryIds = $categoryIds
                ->merge($selectedCategoryIds)
                ->unique()
                ->values()
                ->all();

            $query->where(function ($categoryQuery) use ($categoryIds) {
                $categoryQuery
                    ->whereIn('products.category_id', $categoryIds)
                    ->orWhereHas('categories', function ($relationQuery) use ($categoryIds) {
                        $relationQuery->whereIn('categories.id', $categoryIds);
                    });
            });
        }

        $selectedBrandIds = collect(
            (array) $request->input('brands', [])
        )
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($selectedBrandIds->isNotEmpty()) {
            $query->whereIn(
                'products.brand_id',
                $selectedBrandIds->all()
            );
        }

        if ($request->boolean('in_stock')) {
            $query->available();
        }

        if ($request->boolean('discounted')) {
            $query->discount();
        }

        $minPrice = $request->filled('min_price')
            ? max(0, (int) $request->input('min_price'))
            : null;

        $maxPrice = $request->filled('max_price')
            ? max(0, (int) $request->input('max_price'))
            : null;

        if (
            $minPrice === null &&
            $maxPrice === null &&
            $priceRange > 0
        ) {
            switch ($request->input('price_level')) {
                case 'economy':
                    $minPrice = $catalogMinPrice;
                    $maxPrice = $firstPriceLevelEnd;
                    break;

                case 'midrange':
                    $minPrice = $firstPriceLevelEnd + 1;
                    $maxPrice = $secondPriceLevelEnd;
                    break;

                case 'premium':
                    $minPrice = $secondPriceLevelEnd + 1;
                    $maxPrice = $catalogMaxPrice;
                    break;
            }
        }

        if ($minPrice !== null) {
            $query->whereHas(
                'prices',
                function ($priceQuery) use (
                    $effectivePriceSql,
                    $now,
                    $minPrice
                ) {
                    $priceQuery
                        ->where('stock', '>', 0)
                        ->whereRaw(
                            "({$effectivePriceSql}) >= ?",
                            [$now, $minPrice]
                        );
                }
            );
        }

        if ($maxPrice !== null) {
            $query->whereHas(
                'prices',
                function ($priceQuery) use (
                    $effectivePriceSql,
                    $now,
                    $maxPrice
                ) {
                    $priceQuery
                        ->where('stock', '>', 0)
                        ->whereRaw(
                            "({$effectivePriceSql}) <= ?",
                            [$now, $maxPrice]
                        );
                }
            );
        }

        $query->orderByStock();

        switch ($request->input('sort', 'latest')) {
            case 'most_viewed':
                $query->orderByDesc('products.view');
                break;

            case 'best_selling':
                $query->orderBySale('desc');
                break;

            case 'price_asc':
                $query->orderByPrice('asc');
                break;

            case 'price_desc':
                $query->orderByPrice('desc');
                break;

            default:
                $query->latest('products.created_at');
                break;
        }

        $products = $query
            ->paginate(20)
            ->withQueryString();

        $categories = Category::detectLang()
            ->published()
            ->where('type', 'productcat')
            ->orderBy('ordering')
            ->get();

        $brands = Brand::detectLang()
            ->whereHas('products', function ($productQuery) {
                $productQuery
                    ->detectLang()
                    ->published();
            })
            ->orderBy('name')
            ->get();

        $priceLevels = [
            'economy' => [
                $catalogMinPrice,
                $firstPriceLevelEnd,
            ],

            'midrange' => [
                $firstPriceLevelEnd + 1,
                $secondPriceLevelEnd,
            ],

            'premium' => [
                $secondPriceLevelEnd + 1,
                $catalogMaxPrice,
            ],
        ];

        return compact(
            'products',
            'categories',
            'brands',
            'catalogMinPrice',
            'catalogMaxPrice',
            'priceLevels'
        );
    }
}