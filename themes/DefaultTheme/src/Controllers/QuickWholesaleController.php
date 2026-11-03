<?php

namespace Themes\DefaultTheme\src\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Price;
use App\Services\QuickWholesaleCartPlan;
use App\Services\QuickWholesaleModelDirectory;
use App\Services\QuickWholesaleRequestFingerprint;
use App\Services\QuickWholesaleSelectionException;
use App\Services\QuickWholesaleVariantCatalog;
use App\Services\Sales\SalesControlService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuickWholesaleController extends Controller
{
    private const SESSION_KEY = 'ariya_quick_wholesale.selected_model';

    public function index(Request $request)
    {
        // ARIYA_TASK01_STAGE04_MODEL_SELECTOR_V2

        $options = $this->modelOptions();

        $saved = $request->session()->get(self::SESSION_KEY);

        $selected = is_string($saved) && isset($options[$saved])
            ? $saved
            : (QuickWholesaleModelDirectory::resolve('iPhone 13', $options) ?? (array_key_first($options) ?? ''));

        $error = null;

        if ($request->query->has('model')) {
            $input = $request->query('model');

            $key = is_string($input)
                ? QuickWholesaleModelDirectory::resolve($input, $options)
                : null;

            if ($key !== null && isset($options[$key])) {
                $selected = $key;
                $request->session()->put(self::SESSION_KEY, $key);
            } else {
                $error = 'مدل انتخاب‌شده معتبر نیست.';
            }
        }

        // ARIYA_TASK01_STAGE05C_READONLY_LIST_V1
        // Match variants from inventory; only authenticated users see stock.
        $catalogItems = auth()->check() && $selected !== ''
            ? app(QuickWholesaleVariantCatalog::class)->findForModelId(
                QuickWholesaleModelDirectory::idFromKey($selected) ?? 0
            )
            : [];
        // ARIYA_TASK01_STAGE06C_PRICE_IMAGE_V1
        // Authenticated-only display enrichment. Never authorizes checkout.
        // Existing campaign/discount and rounding rules remain in Price.
        if (auth()->check() && $catalogItems) {
            $priceModels = \App\Models\Price::query()
                ->with(['product', 'image'])
                ->whereIn('id', array_column($catalogItems, 'price_id'))
                ->get()
                ->keyBy('id');

            $displayItems = [];
            foreach ($catalogItems as $item) {
                $price = $priceModels->get((int) $item['price_id']);
                if (!$price || !$price->product
                    || (int) $price->product_id !== (int) $item['product_id']
                    || !$price->product->isPhysical()
                    || !(bool) $price->product->published
                    || (int) $price->stock < 1) {
                    continue;
                }

                $productImage = $price->product->imageUrl();
                $variantImagePath = $price->image
                    ? trim((string) $price->image->image)
                    : '';

                $item['image_url'] = $variantImagePath !== ''
                    ? media_url($variantImagePath, $productImage)
                    : $productImage;

                $item['regular_toman'] = (float) $price->regularPrice();
                $item['sale_toman'] = (float) $price->salePrice();
                $item['has_price_discount'] =
                    $item['sale_toman'] < $item['regular_toman'];

                $displayItems[] = $item;
            }

            $catalogItems = $displayItems;
        }
        // ARIYA_TASK01_STAGE07B_FILTER_V1
        // Only catalog candidates are filterable, and prices remain server-derived.
        $catalogFilterOptions = [];
        foreach ($catalogItems as $item) {
            $id = (int) $item['product_id'];
            if ($id > 0) {
                $catalogFilterOptions[(string) $id] = (string) $item['product_title'];
            }
        }
        ksort($catalogFilterOptions, SORT_NUMERIC);
        $catalogBrandOptions = [];
        foreach ($catalogItems as $item) {
            $brandKey = $item['brand_id'] === null ? 'none' : (string) $item['brand_id'];
            $catalogBrandOptions[$brandKey] = $item['brand_name'] !== ''
                ? $item['brand_name']
                : 'بدون برند';
        }
        ksort($catalogBrandOptions, SORT_NATURAL);
        $catalogFilterType = $request->query('type', 'all');
        if (!is_string($catalogFilterType) || (
            $catalogFilterType !== 'all'
            && !array_key_exists($catalogFilterType, $catalogFilterOptions)
        )) {
            $catalogFilterType = 'all';
        }
        $catalogSort = $request->query('sort', 'default');
        if (!is_string($catalogSort)
            || !in_array($catalogSort, ['default', 'price_asc', 'price_desc', 'stock_desc'], true)) {
            $catalogSort = 'default';
        }
        $catalogBrand = $request->query('brand', 'all');
        if (!is_string($catalogBrand) || (
            $catalogBrand !== 'all'
            && !array_key_exists($catalogBrand, $catalogBrandOptions)
        )) {
            $catalogBrand = 'all';
        }
        $catalogTotal = count($catalogItems);
        $catalogItems = \App\Services\QuickWholesaleVariantFilters::apply(
            $catalogItems, $catalogFilterType, $catalogSort, $catalogBrand
        );
        // Search and paginate only verified, server-priced candidates.
        $searchInput = $request->query('q', '');
        $catalogSearch = is_string($searchInput) ? mb_substr(trim($searchInput), 0, 80) : '';
        $pageInput = $request->query('page', 1);
        $catalogRequestedPage = is_scalar($pageInput) && ctype_digit((string) $pageInput)
            ? min(100000, max(1, (int) $pageInput)) : 1;
        $listing = \App\Services\QuickWholesaleCatalogPage::slice(
            $catalogItems, $catalogSearch, $catalogRequestedPage, 12, $options[$selected] ?? ''
        );
        $catalogItems = $listing['items'];
        return response()
            ->view('front::products.quick-wholesale', [
                'options' => $options,
                'selected' => $selected,
                'selectedLabel' => $options[$selected] ?? '',
                'error' => $error,
                'catalogItems' => $catalogItems,
                'catalogFilterOptions' => $catalogFilterOptions,
                'catalogFilterType' => $catalogFilterType,
                'catalogBrandOptions' => $catalogBrandOptions,
                'catalogBrand' => $catalogBrand,
                'catalogSort' => $catalogSort,
                'catalogTotal' => $catalogTotal,
                'catalogSearch' => $catalogSearch,
                'catalogFilteredTotal' => $listing['total'],
                'catalogPage' => $listing['page'],
                'catalogLastPage' => $listing['last_page'],
            ])
            ->header('Cache-Control', 'private, no-store');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_id' => ['required', 'uuid', 'max:36'],
            'model' => ['required', 'string', 'max:80'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.price_id' => ['required', 'integer', 'min:1', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ], [
            'request_id.required' => 'برای افزودن گروهی، لطفاً صفحه را به‌روزرسانی کنید.',
        ]);

        $requestHash = QuickWholesaleRequestFingerprint::hash(
            $validated['model'], $validated['items']
        );
        $cart = $request->user()->getCart();

        try {
            $result = DB::transaction(function () use ($validated, $requestHash, $cart): array {
                // Serialize all Quick Wholesale submissions to this cart, including different IDs.
                // This prevents duplicate inserts when concurrent requests see an empty cart.
                $lockedCart = DB::table('carts')
                    ->where('id', $cart->id)
                    ->where('user_id', $cart->user_id)
                    ->lockForUpdate()
                    ->first(['id']);
                if (!$lockedCart) {
                    throw new QuickWholesaleSelectionException('سبد خرید در دسترس نیست.');
                }

                $prior = DB::table('ariya_qw_cart_requests')
                    ->where('cart_id', $cart->id)
                    ->where('request_id', $validated['request_id'])
                    ->first(['payload_hash', 'summary_json']);

                if ($prior) {
                    if (QuickWholesaleRequestFingerprint::decision((string) $prior->payload_hash, $requestHash) === 'conflict') {
                        return ['conflict' => true];
                    }
                    $originalSummary = json_decode((string) $prior->summary_json, true, 512, JSON_THROW_ON_ERROR);
                    if (!is_array($originalSummary)) {
                        throw new \RuntimeException('Invalid Quick Wholesale request receipt');
                    }
                    return [
                        'replayed' => true,
                        'summary' => $originalSummary,
                        'model_key' => $validated['model'],
                    ];
                }

                // For a first submission, resolve the model and verify actual inventory.
                $options = $this->modelOptions();
                $modelKey = QuickWholesaleModelDirectory::resolve($validated['model'], $options);
                if ($modelKey === null || !isset($options[$modelKey])) {
                    throw new QuickWholesaleSelectionException('مدل گوشی انتخاب‌شده معتبر نیست.');
                }

                $catalogItems = app(QuickWholesaleVariantCatalog::class)->findForModelId(
                    QuickWholesaleModelDirectory::idFromKey($modelKey) ?? 0
                );
                $priceIds = array_map(
                    static fn (array $item): int => (int) $item['price_id'],
                    $validated['items']
                );

                $currentQuantities = DB::table('cart_product')
                    ->where('cart_id', $cart->id)
                    ->whereIn('price_id', $priceIds)
                    ->lockForUpdate()
                    ->pluck('quantity', 'price_id')
                    ->map(static fn ($quantity): int => (int) $quantity)
                    ->all();

                $plan = QuickWholesaleCartPlan::build(
                    $validated['items'], $catalogItems, $currentQuantities
                );

                $prices = Price::query()
                    ->with('product')
                    ->whereIn('id', $priceIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $requestedTotalToman = 0;
                foreach ($plan['lines'] as $line) {
                    $price = $prices->get($line['price_id']);
                    if (!$price || !$price->product
                        || (int) $price->product_id !== $line['product_id']
                        || !$price->product->isPhysical()
                        || !(bool) $price->product->published
                        || !$price->product->usesVariantInventoryCodes()) {
                        throw new QuickWholesaleSelectionException(
                            "«{$line['label']}» دیگر قابل سفارش نیست.",
                            $line['price_id']
                        );
                    }

                    $stock = app(\App\Services\Inventory\CartStockAvailability::class)->forPrice($price, (int) $line['final_quantity']);
                    if (!$stock['status']) {
                        throw new QuickWholesaleSelectionException(
                            "«{$line['label']}»: {$stock['message']}",
                            $line['price_id']
                        );
                    }

                    $requestedTotalToman += (int) round(
                        $price->salePrice() * $line['requested_quantity']
                    );
                }

                $productIds = array_keys($plan['requested_by_product']);
                $currentProductQuantities = DB::table('cart_product')
                    ->selectRaw('product_id, SUM(quantity) AS aggregate_quantity')
                    ->where('cart_id', $cart->id)
                    ->whereIn('product_id', $productIds)
                    ->groupBy('product_id')
                    ->lockForUpdate()
                    ->pluck('aggregate_quantity', 'product_id');

                foreach ($plan['requested_by_product'] as $productId => $requestedQuantity) {
                    $finalProductQuantity = (int) ($currentProductQuantities[$productId] ?? 0)
                        + (int) $requestedQuantity;
                    $salesStatus = app(SalesControlService::class)
                        ->validateQuantity((int) $productId, $finalProductQuantity);
                    if (!$salesStatus['status']) {
                        throw new QuickWholesaleSelectionException(
                            (string) $salesStatus['message']
                        );
                    }
                }

                foreach ($plan['lines'] as $line) {
                    $existing = array_key_exists($line['price_id'], $currentQuantities);
                    if ($existing) {
                        DB::table('cart_product')
                            ->where('cart_id', $cart->id)
                            ->where('product_id', $line['product_id'])
                            ->where('price_id', $line['price_id'])
                            ->update([
                                'quantity' => $line['final_quantity'],
                                'updated_at' => now(),
                            ]);
                        continue;
                    }

                    DB::table('cart_product')->insert([
                        'cart_id' => $cart->id,
                        'product_id' => $line['product_id'],
                        'price_id' => $line['price_id'],
                        'quantity' => $line['final_quantity'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $summary = [
                    'selected_count' => count($plan['lines']),
                    'selected_units' => array_sum(array_column($plan['lines'], 'requested_quantity')),
                    'requested_total_toman' => $requestedTotalToman,
                ];

                // The receipt is committed atomically with cart mutations.
                // If any validation or write fails, neither the cart nor receipt changes.
                DB::table('ariya_qw_cart_requests')->insert([
                    'cart_id' => $cart->id,
                    'request_id' => $validated['request_id'],
                    'payload_hash' => $requestHash,
                    'summary_json' => json_encode($summary, JSON_THROW_ON_ERROR),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return [
                    'replayed' => false,
                    'summary' => $summary,
                    'model_key' => $modelKey,
                ];
            }, 3);
        } catch (QuickWholesaleSelectionException $exception) {
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage(),
                'failed_price_id' => $exception->priceId(),
            ], 422);
        }

        if (!empty($result['conflict'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'شناسه درخواست قبلاً با انتخاب دیگری ثبت شده است. صفحه را به‌روزرسانی کنید.',
            ], 409);
        }

        $request->session()->put(self::SESSION_KEY, $result['model_key']);
        $cart->unsetRelation('products');
        $cart->load('products');

        return response()->json([
            'status' => 'success',
            'replayed' => $result['replayed'],
            'message' => $result['replayed']
                ? 'این درخواست قبلاً ثبت شده است؛ کالاها دوباره اضافه نشدند.'
                : 'کالاهای انتخاب‌شده با موفقیت به سبد خرید اضافه شدند.',
            'summary' => $result['summary'],
            'cart' => view('front::partials.cart')
                ->with('render_cart', $cart)
                ->render(),
        ]);
    }

    private function modelOptions(): array
    {
        return app(QuickWholesaleModelDirectory::class)->options();
    }
}
