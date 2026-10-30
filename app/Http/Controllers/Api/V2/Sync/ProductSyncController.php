<?php

namespace App\Http\Controllers\Api\V2\Sync;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\Price;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;

class ProductSyncController extends Controller
{
    private const PRODUCT_VERIFY_FIELDS = [
        'title',
        'product_code',
        'published',
        'type',
        'price_type',
        'slug',
        'lang',
        'inventory_to_site_synced',
    ];

    private const PRICE_VERIFY_FIELDS = [
        'external_variant_id',
        'variant_code',
        'variant_name',
        'product_id',
        'price',
        'regular_price',
        'discount',
        'discount_price',
        'stock',
        'discount_expire_at',
    ];

    private const EMPTY_VARIANT_NAMES = [
        '—',
        '–',
        '-',
        'ــ',
        'بدون تنوع',
        'بدون مدل',
    ];

    public function __invoke(Request $request): JsonResponse
    {
        set_time_limit(300);
        ini_set('max_execution_time', '3000');

        $validator = Validator::make($request->all(), $this->validationRules());

        if ($validator->fails()) {
            return response()->json([
                'status' => 'validation_error',
                'message' => 'اطلاعات ارسالی معتبر نیست.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $products = $validator->validated()['products'];

        [$existingProductIds, $existingPrices] = $this->loadExistingRecords($products);

        $snapshot = $this->buildSnapshot(
            $products,
            $existingProductIds,
            $existingPrices
        );

        try {
            DB::transaction(function () use ($snapshot): void {
                $this->saveSnapshot(
                    $snapshot['productRows'],
                    $snapshot['priceRows'],
                    $snapshot['productVariantIds'],
                    $snapshot['variantNames']
                );
            });
        } catch (Throwable $exception) {
            Log::error('Product synchronization failed.', [
                'message' => $exception->getMessage(),
                'product_count' => count($snapshot['productRows']),
                'price_count' => count($snapshot['priceRows']),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'خطایی هنگام ذخیره اطلاعات رخ داد.',
                'error' => app()->hasDebugModeEnabled() ? $exception->getMessage() : null,
            ], 500);
        }

        return $this->buildSuccessResponse(
            $snapshot['productRows'],
            $snapshot['priceRows'],
            $snapshot['results']
        );
    }

    private function validationRules(): array
    {
        return [
            // این endpoint یک snapshot کامل از کاتالوگ انبار دریافت می‌کند؛
            // بنابراین آرایه خالی نیز معتبر است و به معنی حذف کاتالوگ قبلی است.
            'products' => ['required', 'array'],
            'products.*.id' => ['required', 'integer', 'distinct'],
            'products.*.name' => ['required', 'string'],
            'products.*.code' => ['nullable', 'string', 'max:191'],
            'products.*.is_sellable' => ['nullable', 'boolean'],
            'products.*.variants' => ['required', 'array'],

            'products.*.variants.*.id' => ['required', 'integer', 'distinct'],
            'products.*.variants.*.variant_code' => ['nullable', 'string', 'max:191'],
            'products.*.variants.*.variant_name' => ['nullable', 'string', 'max:191'],
            'products.*.variants.*.sell_price' => ['nullable', 'numeric'],
            'products.*.variants.*.regular_price' => ['nullable', 'numeric'],
            'products.*.variants.*.discount_price' => ['nullable', 'numeric'],
            'products.*.variants.*.stock' => ['nullable', 'integer'],
            'products.*.variants.*.reserved' => ['nullable', 'integer'],
            'products.*.variants.*.discount_expire_at' => ['nullable', 'date'],
            'products.*.variants.*.is_active' => ['nullable', 'boolean'],
            'products.*.variants.*.sales_enabled' => ['nullable', 'boolean'],
        ];
    }

    private function loadExistingRecords(array $products): array
    {
        $productIds = array_map(
            static fn(array $product): int => (int)$product['id'],
            $products
        );

        $variantIds = [];

        foreach ($products as $product) {
            foreach ($product['variants'] as $variant) {
                $variantIds[] = (int)$variant['id'];
            }
        }

        $variantIds = array_values(array_unique($variantIds));

        $existingProductIds = Product::query()
            ->whereIn('id', $productIds)
            ->pluck('id')
            ->mapWithKeys(static fn($id): array => [(int)$id => true])
            ->all();

        $existingPrices = Price::withTrashed()
            ->whereIn('id', $variantIds)
            ->get([
                'id',
                'product_id',
                'price',
                'regular_price',
                'discount',
                'discount_price',
                'stock',
                'discount_expire_at',
            ])
            ->keyBy('id');

        return [$existingProductIds, $existingPrices];
    }

    private function buildSnapshot(
        array $products,
        array $existingProductIds,
              $existingPrices
    ): array {
        $productRows = [];
        $priceRows = [];
        $variantNames = [];
        $productVariantIds = [];
        $results = [];

        foreach ($products as $productData) {
            $productId = (int)$productData['id'];
            $productName = trim($productData['name']);

            $productRows[$productId] = $this->makeProductRow($productData);
            $productVariantIds[$productId] = [];
            $variantResults = [];

            foreach ($productData['variants'] as $variantData) {
                $variantId = (int)$variantData['id'];
                $oldPrice = $existingPrices->get($variantId);
                $priceRow = $this->makePriceRow($productId, $variantData);

                if ($priceRow === null) {
                    $variantResults[] = [
                        'id' => $variantId,
                        'status' => 'ignored',
                        'changes' => [],
                        'reason' => 'missing_price',
                    ];

                    continue;
                }

                $priceRows[$variantId] = $priceRow;
                $variantNames[$variantId] = $priceRow['variant_name'];
                $productVariantIds[$productId][] = $variantId;

                $changes = $this->detectPriceChanges($oldPrice, $priceRow);

                $variantResults[] = [
                    'id' => $variantId,
                    'status' => $oldPrice
                        ? (empty($changes) ? 'unchanged' : 'updated')
                        : 'created',
                    'changes' => $changes,
                ];
            }

            $results[] = [
                'id' => $productId,
                'title' => $productName,
                'status' => isset($existingProductIds[$productId]) ? 'updated' : 'created',
                'variants' => $variantResults,
            ];
        }

        return [
            'productRows' => $productRows,
            'priceRows' => $priceRows,
            'variantNames' => $variantNames,
            'productVariantIds' => $productVariantIds,
            'results' => $results,
        ];
    }

    private function makeProductRow(array $productData): array
    {
        $productId = (int)$productData['id'];
        $productName = trim($productData['name']);
        $productCode = isset($productData['code']) && trim((string)$productData['code']) !== ''
            ? trim((string)$productData['code'])
            : Product::generateInventoryProductCode($productId);

        $slug = Str::slug($productName);

        if ($slug === '') {
            $slug = 'product-' . $productId;
        }

        $slug .= '-' . $productId;

        return [
            'id' => $productId,
            'title' => $productName,
            'product_code' => $productCode,
            'published' => (bool)($productData['is_sellable'] ?? true),
            'type' => 'physical',
            'price_type' => 'multiple-price',
            'slug' => $slug,
            'lang' => app()->getLocale(),
            'inventory_to_site_synced' => true,
            'site_to_inventory_verified' => false,
        ];
    }

    private function makePriceRow(int $productId, array $variantData): ?array
    {
        $variantId = (int)$variantData['id'];
        $sellPrice = (int)($variantData['sell_price'] ?? 0);
        $regularPrice = (int)($variantData['regular_price'] ?? $sellPrice);
        $discountPrice = (int)($variantData['discount_price'] ?? 0);
        $stock = (int)($variantData['stock'] ?? 0);
        $isActive = (bool)($variantData['is_active'] ?? true);
        $salesEnabled = (bool)($variantData['sales_enabled'] ?? true);

        if (!$isActive || !$salesEnabled) {
            $stock = 0;
        }

        $finalPrice = $this->calculateFinalPrice(
            $sellPrice,
            $regularPrice,
            $discountPrice
        );

        if ($finalPrice <= 0) {
            return null;
        }

        $variantCode = isset($variantData['variant_code']) && trim((string)$variantData['variant_code']) !== ''
            ? trim((string)$variantData['variant_code'])
            : Price::generateInventoryVariantCode($variantId);

        return [
            'id' => $variantId,
            'external_variant_id' => (string)$variantId,
            'variant_code' => $variantCode,
            'variant_name' => $this->resolveVariantName($variantData),
            'product_id' => $productId,
            'price' => $finalPrice,
            'regular_price' => $regularPrice,
            'discount' => $this->calculateDiscount($sellPrice, $regularPrice, $discountPrice),
            'discount_price' => $finalPrice,
            'stock' => $stock,
            'discount_expire_at' => $this->parseDate($variantData['discount_expire_at'] ?? null),
        ];
    }

    private function saveSnapshot(
        array $productRows,
        array $priceRows,
        array $productVariantIds,
        array $variantNames
    ): void {
        // این endpoint به صورت batch/incremental صدا زده می‌شود.
        // بنابراین فقط داده‌های دریافتی را ذخیره یا به‌روزرسانی می‌کنیم
        // و هیچ محصول یا واریانت غایبی را حذف نمی‌کنیم.
        foreach ($productRows as $productId => $productRow) {
            $product = $this->saveProduct($productRow);

            foreach ($productVariantIds[$productId] as $variantId) {
                $this->savePrice($product, $priceRows[$variantId]);
            }
        }

        $this->syncVariantNameAttributes($variantNames);
    }

    private function saveProduct(array $data): Product
    {
        $product = Product::query()->find($data['id']) ?? new Product();

        $product->forceFill($data);
        $product->save();

        return $product;
    }

    private function savePrice(Product $product, array $data): void
    {
        $price = Price::withTrashed()->find($data['id']) ?? new Price();

        $price->forceFill($data);
        $price->product()->associate($product);
        $price->save();

        if ($price->trashed()) {
            $price->restore();
        }
    }

    private function buildSuccessResponse(
        array $productRows,
        array $priceRows,
        array $results
    ): JsonResponse {
        $expectedProductIds = array_map(
            static fn($id): int => (int)$id,
            array_keys($productRows)
        );

        sort($expectedProductIds);

        $syncedProductIds = Product::query()
            ->whereIn('id', $expectedProductIds)
            ->where('inventory_to_site_synced', true)
            ->pluck('id')
            ->map(static fn($id): int => (int)$id)
            ->sort()
            ->values()
            ->all();

        $verifiedProductIds = $this->verifyStoredProducts($productRows, $priceRows);

        $this->updateVerificationFlags($expectedProductIds, $verifiedProductIds);

        $unverifiedProductIds = array_values(
            array_diff($expectedProductIds, $verifiedProductIds)
        );

        $syncComplete = $syncedProductIds === $expectedProductIds;
        $verifyComplete = $verifiedProductIds === $expectedProductIds;

        return response()->json([
            // معیار موفقیت اصلی، ذخیره‌شدن اطلاعات است.
            // verify فقط یک کنترل ثانویه است و ناقص‌بودن آن نباید
            // ذخیره موفق را به خطا تبدیل کند.
            'status' => $syncComplete ? 'success' : 'partial_success',
            'message' => $syncComplete
                ? ($verifyComplete
                    ? 'همگام‌سازی و تأیید محصولات با موفقیت انجام شد.'
                    : 'محصولات با موفقیت ذخیره شدند اما تأیید ثانویه بعضی محصولات کامل نشد.')
                : 'اطلاعات دریافت شد اما ذخیره بعضی محصولات کامل نشد.',
            'data' => [
                'product_count' => count($productRows),
                'variant_count' => count($priceRows),
                'synced_product_ids' => $syncedProductIds,
                'verified_product_ids' => $verifiedProductIds,
                'unverified_product_ids' => $unverifiedProductIds,
                'products' => $results,
            ],
        ]);
    }

    private function updateVerificationFlags(
        array $expectedProductIds,
        array $verifiedProductIds
    ): void {
        if ($verifiedProductIds !== []) {
            Product::query()
                ->whereIn('id', $verifiedProductIds)
                ->update(['site_to_inventory_verified' => true]);
        }

        $unverifiedProductIds = array_values(
            array_diff($expectedProductIds, $verifiedProductIds)
        );

        if ($unverifiedProductIds !== []) {
            Product::query()
                ->whereIn('id', $unverifiedProductIds)
                ->update(['site_to_inventory_verified' => false]);
        }
    }

    private function calculateDiscount(int $sellPrice, int $regularPrice, int $discountPrice): int
    {
        if (
            $regularPrice > 0
            && $discountPrice > 0
            && $discountPrice < $regularPrice
        ) {
            return (int)round((($regularPrice - $discountPrice) / $regularPrice) * 100);
        }

        if (
            $regularPrice > 0
            && $sellPrice >= 0
            && $sellPrice < $regularPrice
        ) {
            return (int)round((($regularPrice - $sellPrice) / $regularPrice) * 100);
        }

        return 0;
    }

    private function calculateFinalPrice(int $sellPrice, int $regularPrice, int $discountPrice): int
    {
        if ($discountPrice > 0 && $discountPrice < $regularPrice) {
            return $discountPrice;
        }

        return $sellPrice;
    }

    private function parseDate(mixed $date): ?string
    {
        if (blank($date)) {
            return null;
        }

        return Carbon::parse($date)->toDateTimeString();
    }

    private function resolveVariantName(array $variantData): ?string
    {
        $variantName = $this->normalizeVariantName($variantData['variant_name'] ?? null);
        $varietyName = $this->normalizeVariantName($variantData['variety_name'] ?? null);

        if (in_array($variantName, self::EMPTY_VARIANT_NAMES, true)) {
            $variantName = null;
        }

        if (in_array($varietyName, self::EMPTY_VARIANT_NAMES, true)) {
            $varietyName = null;
        }

        if ($variantName !== null && $varietyName !== null && $variantName !== $varietyName) {
            return mb_substr($variantName . ' - ' . $varietyName, 0, 255);
        }

        return $variantName ?? $varietyName;
    }

    private function normalizeVariantName(mixed $name): ?string
    {
        if (!is_string($name)) {
            return null;
        }

        $name = trim($name);

        return $name === '' ? null : $name;
    }

    private function detectPriceChanges(?Price $oldPrice, array $newPrice): array
    {
        if (!$oldPrice) {
            return [
                'product_id' => $newPrice['product_id'],
                'price' => $newPrice['price'],
                'regular_price' => $newPrice['regular_price'],
                'discount' => $newPrice['discount'],
                'discount_price' => $newPrice['discount_price'],
                'stock' => $newPrice['stock'],
            ];
        }

        $changes = [];

        foreach ([
            'product_id',
            'price',
            'regular_price',
            'discount',
            'discount_price',
            'stock',
        ] as $field) {
            if ((string)$oldPrice->{$field} !== (string)$newPrice[$field]) {
                $changes[$field] = $newPrice[$field];
            }
        }

        $oldExpireAt = $oldPrice->discount_expire_at
            ? Carbon::parse($oldPrice->discount_expire_at)->toDateTimeString()
            : null;

        if ($oldExpireAt !== $newPrice['discount_expire_at']) {
            $changes['discount_expire_at'] = $newPrice['discount_expire_at'];
        }

        return $changes;
    }

    private function syncVariantNameAttributes(array $variantNames): void
    {
        if (empty($variantNames)) {
            return;
        }

        $attributeGroup = AttributeGroup::withTrashed()
            ->where('name', 'مدل')
            ->where('lang', app()->getLocale())
            ->first();

        if (!$attributeGroup) {
            $attributeGroup = AttributeGroup::create([
                'name' => 'مدل',
                'type' => 'select',
                'lang' => app()->getLocale(),
                'ordering' => (int)AttributeGroup::max('ordering') + 1,
            ]);
        } elseif ($attributeGroup->trashed()) {
            $attributeGroup->restore();
        }

        $names = array_values(array_unique(array_filter($variantNames)));

        $attributesByName = Attribute::query()
            ->where('attribute_group_id', $attributeGroup->id)
            ->whereIn('name', $names)
            ->get()
            ->keyBy('name');

        foreach ($names as $name) {
            if (!$attributesByName->has($name)) {
                $attributesByName->put(
                    $name,
                    $attributeGroup->get_attributes()->create([
                        'name' => $name,
                        'value' => null,
                    ])
                );
            }
        }

        $groupAttributeIds = Attribute::query()
            ->where('attribute_group_id', $attributeGroup->id)
            ->pluck('id');

        foreach ($variantNames as $priceId => $name) {
            $price = Price::query()->find($priceId);

            if (!$price) {
                continue;
            }

            $price->get_attributes()->detach($groupAttributeIds);

            if ($name !== null) {
                $price->get_attributes()->attach($attributesByName->get($name)->id, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function verifyStoredProducts(array $productRows, array $priceRows): array
    {
        if ($productRows === []) {
            return [];
        }

        $storedProducts = Product::query()
            ->with('prices')
            ->whereIn('id', array_keys($productRows))
            ->get(array_merge(['id'], self::PRODUCT_VERIFY_FIELDS))
            ->keyBy('id');

        $verifiedProductIds = [];

        foreach ($productRows as $productId => $expectedProduct) {
            $storedProduct = $storedProducts->get($productId);

            if (!$storedProduct) {
                continue;
            }

            if (!$this->modelMatches($storedProduct, $expectedProduct, self::PRODUCT_VERIFY_FIELDS)) {
                continue;
            }

            $expectedProductPrices = array_filter(
                $priceRows,
                static fn(array $price): bool => (int)$price['product_id'] === (int)$productId
            );

            $storedPrices = $storedProduct->prices->keyBy('id');

            if (!$this->sameIds($storedPrices->keys()->all(), array_keys($expectedProductPrices))) {
                continue;
            }

            $pricesMatch = true;

            foreach ($expectedProductPrices as $priceId => $expectedPrice) {
                $storedPrice = $storedPrices->get($priceId);

                if (!$storedPrice || !$this->modelMatches(
                        $storedPrice,
                        $expectedPrice,
                        self::PRICE_VERIFY_FIELDS
                    )) {
                    $pricesMatch = false;
                    break;
                }
            }

            if ($pricesMatch) {
                $verifiedProductIds[] = (int)$productId;
            }
        }

        sort($verifiedProductIds);

        return $verifiedProductIds;
    }

    private function modelMatches(object $model, array $expected, array $fields): bool
    {
        foreach ($fields as $field) {
            if (!$this->valuesAreEquivalent($model->{$field}, $expected[$field])) {
                return false;
            }
        }

        return true;
    }

    private function sameIds(array $actualIds, array $expectedIds): bool
    {
        $actualIds = array_map('intval', $actualIds);
        $expectedIds = array_map('intval', $expectedIds);

        sort($actualIds);
        sort($expectedIds);

        return $actualIds === $expectedIds;
    }

    private function valuesAreEquivalent(mixed $actual, mixed $expected): bool
    {
        if ($actual === null || $expected === null) {
            return $actual === null && $expected === null;
        }

        if (is_bool($actual) || is_bool($expected)) {
            return (bool)$actual === (bool)$expected;
        }

        if (
            is_int($actual)
            || is_float($actual)
            || is_int($expected)
            || is_float($expected)
        ) {
            return (string)$actual === (string)$expected;
        }

        return trim((string)$actual) === trim((string)$expected);
    }
}