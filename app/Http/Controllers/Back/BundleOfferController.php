<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\BundleOffer;
use App\Models\Price;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use JsonException;
use Throwable;

class BundleOfferController extends Controller
{
    public function index(?BundleOffer $bundleOffer = null)
    {
        $this->authorize('products');
        $offers = BundleOffer::query()->withCount('variants')->latest()->get();
        $editing = $bundleOffer?->exists ? $bundleOffer->load('variants.price.product') : null;
        $selectedStock = $editing
            ? $this->centralStockByPrices($editing->variants->pluck('price')->filter())
            : [];
        return view('back.sales-control.bundles', compact('offers', 'editing', 'selectedStock'));
    }

    public function edit(BundleOffer $bundleOffer)
    {
        return $this->index($bundleOffer);
    }

    public function search(Request $request)
    {
        $this->authorize('products');

        // Stock-only searches scan bounded batches and can continue by cursor.
        // A 35-row raw preview would incorrectly hide in-stock models after zeros.
        if (!$request->query->has('product_id') && $request->boolean('in_stock')) {
            return $this->searchStockOnly($request);
        }

        // Cursor-pagination deliberately has NO total-model limit. Fetch in small
        // pages to avoid timeouts and oversized central-warehouse stock queries.
        $bulkProduct = $request->query->has('product_id');
        if ($bulkProduct) {
            $validated = $request->validate([
                'product_id' => 'required|integer|min:1',
                'after_id' => 'nullable|integer|min:0',
            ]);
            $product = Product::query()->findOrFail((int) $validated['product_id']);
            if (!$product->isPhysical()) {
                return response()->json(['message' => 'تنها مدل‌های محصولات فیزیکی قابل انتخاب هستند.'], 422);
            }
            $pageSize = 100;
            $page = Price::query()->with('product')
                ->where('product_id', $product->id)
                ->where('id', '>', (int) ($validated['after_id'] ?? 0))
                ->orderBy('id')->limit($pageSize + 1)->get();
            $hasMore = $page->count() > $pageSize;
            $prices = $page->take($pageSize)->values();
        } else {
            $term = trim((string) $request->query('q', ''));
            if (mb_strlen($term) < 2) return response()->json([]);
            // Search is a preview. Select-all loads ALL models, including beyond
            // these first results, through cursor pagination above.
            $prices = Price::query()->with('product')
                ->whereHas('product', function ($q) use ($term) {
                    $q->where('title', 'like', '%' . $term . '%');
                    if (ctype_digit($term)) $q->orWhere('id', (int) $term);
                })
                ->orderBy('id')->limit(35)->get();
        }

        $prices = $prices->filter(fn ($p) => $p->product && $p->product->isPhysical())->values();
        $stocks = $this->centralStockByPrices($prices);

        $items = $prices->map(fn ($p) => [
            'id' => (int) $p->id,
            'product_id' => (int) $p->product_id,
            'product_title' => $p->product->title,
            'label' => $p->product->title . ' | مدل ' . $p->id . ' | ' . $p->getAttributesName(),
            // null means warehouse unavailable; 0 means no central stock row/quantity.
            'stock' => $stocks[(int) $p->id] ?? null,
        ])->values();

        if ($bulkProduct) {
            return response()->json([
                'items' => $items,
                'next_cursor' => $hasMore && $prices->isNotEmpty()
                    ? (int) $prices->last()->id : null,
            ]);
        }
        return response()->json($items);
    }

    /**
     * Preview all searchable in-stock models in bounded batches. Never treat
     * a warehouse connection failure as "zero available". The continuation
     * cursor advances over RAW rows, including zero-stock ones, so nothing is
     * accidentally skipped when a batch contains no available models.
     */
    private function searchStockOnly(Request $request)
    {
        $validated = $request->validate([
            'q' => 'required|string|min:2|max:180',
            'after_id' => 'nullable|integer|min:0',
        ]);
        $term = trim($validated['q']);
        if (mb_strlen($term) < 2) {
            return response()->json(['items' => [], 'next_cursor' => null]);
        }

        $cursor = (int) ($validated['after_id'] ?? 0);
        $items = [];
        $scanned = 0;
        $exhausted = false;
        $maxPerRequest = 1200;
        $batchSize = 100;
        $previewSize = 35;

        while (count($items) < $previewSize && $scanned < $maxPerRequest) {
            $batch = Price::query()->with('product')
                ->whereHas('product', function ($query) use ($term) {
                    $query->where('title', 'like', '%' . $term . '%');
                    if (ctype_digit($term)) $query->orWhere('id', (int) $term);
                })
                ->where('id', '>', $cursor)
                ->orderBy('id')
                ->limit($batchSize)
                ->get();

            if ($batch->isEmpty()) {
                $exhausted = true;
                break;
            }
            $cursor = (int) $batch->last()->id;
            $scanned += $batch->count();
            $physical = $batch->filter(fn ($price) => $price->product && $price->product->isPhysical())->values();
            $stocks = $this->centralStockByPrices($physical);
            // Unknown means inventory failed: a stock-only filter must not lie.
            if ($physical->contains(fn ($price) => ($stocks[(int) $price->id] ?? null) === null)) {
                return response()->json([
                    'message' => 'دریافت موجودی انبار مرکزی ممکن نیست. فیلتر مدل‌های موجود را بعداً امتحان کنید.'
                ], 503);
            }
            foreach ($physical as $price) {
                $available = (int) ($stocks[(int) $price->id] ?? 0);
                if ($available <= 0) continue;
                $items[] = [
                    'id' => (int) $price->id,
                    'product_id' => (int) $price->product_id,
                    'product_title' => $price->product->title,
                    'label' => $price->product->title . ' | مدل ' . $price->id . ' | ' . $price->getAttributesName(),
                    'stock' => $available,
                ];
                if (count($items) >= $previewSize) {
                    // Continue immediately after the LAST EMITTED row, not the
                    // end of the scanned batch: otherwise available models
                    // later in the same 100-row batch would be skipped.
                    $cursor = (int) $price->id;
                    break;
                }
            }
            if (count($items) >= $previewSize) break;
            if ($batch->count() < $batchSize) {
                $exhausted = true;
                break;
            }
        }

        return response()->json([
            'items' => $items,
            'next_cursor' => $exhausted ? null : $cursor,
        ]);
    }

    /**
     * Read-only stock lookup from the same central warehouse + site price-id mapping
     * used by the checkout/inventory sync code. No inventory writes or reservations.
     */
    private function centralStockByPrices(Collection $prices): array
    {
        if ($prices->isEmpty()) return [];

        $result = $prices->mapWithKeys(fn ($price) => [(int) $price->id => null])->all();
        try {
            $inv = DB::connection('inv');
            $warehouseId = $inv->table('warehouses')
                ->where('type', 'central')->orderBy('id')->value('id');
            if (!$warehouseId) return $result;

            // Multiple small queries keep edit pages viable with thousands of models.
            foreach ($prices->chunk(150) as $priceChunk) {
                $rows = $inv->table('warehouse_stocks')
                    ->where('warehouse_id', $warehouseId)
                    ->whereIn('product_variant_id', $priceChunk->pluck('id')->all())
                    ->select(['product_id', 'product_variant_id'])
                    ->selectRaw('SUM(quantity) as stock_quantity')
                    ->groupBy('product_id', 'product_variant_id')
                    ->get()
                    ->keyBy(fn ($row) => (int) $row->product_id . ':' . (int) $row->product_variant_id);

                foreach ($priceChunk as $price) {
                    $key = (int) $price->product_id . ':' . (int) $price->id;
                    $result[(int) $price->id] = max(0, (int) ($rows->get($key)->stock_quantity ?? 0));
                }
            }
        } catch (Throwable $exception) {
            report($exception);
            // Preserve null/unknown rather than misleadingly reporting zero stock.
        }
        return $result;
    }


    public function store(Request $request)
    {
        $this->authorize('products');
        $data = $this->validated($request);
        DB::transaction(function () use ($data) {
            $offer = BundleOffer::create($data['offer']);
            $this->insertVariants($offer, $data['variants']);
        }, 3);
        return redirect()->route('admin.sales-control.bundles.index')->with('success', 'پک فروش سبدی ساخته شد.');
    }

    public function update(BundleOffer $bundleOffer, Request $request)
    {
        $this->authorize('products');
        $data = $this->validated($request);
        DB::transaction(function () use ($bundleOffer, $data) {
            BundleOffer::whereKey($bundleOffer->id)->lockForUpdate()->firstOrFail()->update($data['offer']);
            $bundleOffer->variants()->delete();
            $this->insertVariants($bundleOffer, $data['variants']);
        }, 3);
        return redirect()->route('admin.sales-control.bundles.index')->with('success', 'تنظیمات پک بروزرسانی شد.');
    }

    public function destroy(BundleOffer $bundleOffer)
    {
        $this->authorize('products');
        // Preserve cart rows and old snapshots: deactivate rather than deleting a referenced offer.
        $bundleOffer->update(['is_active' => false]);
        return redirect()->route('admin.sales-control.bundles.index')->with('success', 'پک غیرفعال شد.');
    }

    /**
     * Insert large packs in bounded statements within the surrounding transaction.
     * The BundleOfferVariant model has no custom hooks; these are ordinary pivot rows.
     */
    private function insertVariants(BundleOffer $offer, array $variants): void
    {
        $now = now();
        foreach (array_chunk($variants, 200) as $chunk) {
            $rows = [];
            foreach ($chunk as $variant) {
                $rows[] = [
                    'bundle_offer_id' => $offer->id,
                    'price_id' => $variant['price_id'],
                    'product_id' => $variant['product_id'],
                    'minimum_if_selected' => $variant['minimum_if_selected'],
                    'required' => $variant['required'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            DB::table('bundle_offer_variants')->insert($rows);
        }
    }

    private function validated(Request $request): array
    {
        // A normal variants[0][...] form exceeds PHP max_input_vars at roughly
        // 250-500 models and silently loses fields. One JSON field has no model-count
        // cap and avoids that truncation; all IDs/minimums are revalidated server-side.
        $raw = $request->input('variants_json');
        if (!is_string($raw) || $raw === '') {
            throw ValidationException::withMessages(['variants_json' => 'ابتدا مدل‌های پک را انتخاب کنید.']);
        }
        try {
            $variantsInput = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw ValidationException::withMessages(['variants_json' => 'فهرست مدل‌ها معتبر نیست.']);
        }
        if (!is_array($variantsInput) || !array_is_list($variantsInput)) {
            throw ValidationException::withMessages(['variants_json' => 'فهرست مدل‌ها باید آرایه باشد.']);
        }
        $input = $request->all();
        $input['variants'] = $variantsInput;
        $data = Validator::make($input, [
            'title' => 'required|string|max:180',
            'description' => 'nullable|string|max:10000',
            'required_quantity' => 'required|integer|min:1|max:1000000',
            'fixed_price' => 'required|integer|min:1|max:1000000000000',
            'regular_unit_price' => 'nullable|integer|min:1|max:1000000000000',
            'bundle_unit_price' => 'nullable|integer|min:1|max:1000000000000',
            'starts_at' => 'nullable|date',
            'ends_at' => 'required|date',
            'variants' => 'required|array|min:1',
            'variants.*.price_id' => 'required|integer',
            'variants.*.minimum_if_selected' => 'required|integer|min:1|max:1000000',
            'variants.*.required' => 'nullable|boolean',
        ])->validate();
        // Both unit prices must be explicitly supplied for a discounted pack. Never
        // silently overwrite an existing 145-million-Toman offer with an example.
        $regularUnit = isset($data['regular_unit_price']) ? (int) $data['regular_unit_price'] : null;
        $bundleUnit = isset($data['bundle_unit_price']) ? (int) $data['bundle_unit_price'] : null;
        if (($regularUnit === null) !== ($bundleUnit === null)) {
            throw ValidationException::withMessages(['bundle_unit_price' => 'قیمت قبل و قیمت سبدی هر عدد را هر دو وارد کنید.']);
        }
        if ($regularUnit !== null) {
            if ($bundleUnit >= $regularUnit) {
                throw ValidationException::withMessages(['bundle_unit_price' => 'قیمت سبدی هر عدد باید کمتر از قیمت عادی باشد.']);
            }
            if ((int) $data['required_quantity'] > intdiv(1000000000000, $bundleUnit)
                || (int) $data['required_quantity'] > intdiv(1000000000000, $regularUnit)) {
                throw ValidationException::withMessages(['bundle_unit_price' => 'مبلغ کل خارج از محدوده مجاز است.']);
            }
            // The browser's fixed_price is advisory only when unit pricing is entered.
            $data['fixed_price'] = $bundleUnit * (int) $data['required_quantity'];
        }
        if ((int)$data['fixed_price'] < (int)$data['required_quantity']) {
            throw ValidationException::withMessages(['fixed_price' => 'قیمت باید حداقل یک تومان به ازای هر واحد باشد.']);
        }
        if (strtotime($data['ends_at']) <= strtotime($data['starts_at'] ?? 'now')) {
            throw ValidationException::withMessages(['ends_at' => 'پایان باید بعد از شروع فروش باشد.']);
        }
        // Resolve authoritative price/product information in chunks. Never trust
        // display labels, stocks or product IDs supplied by the browser.
        $prices = collect();
        $variantIds = array_values(array_unique(array_map('intval', array_column($data['variants'], 'price_id'))));
        foreach (array_chunk($variantIds, 200) as $ids) {
            foreach (Price::with('product')->whereIn('id', $ids)->get() as $price) {
                $prices->put((int) $price->id, $price);
            }
        }
        $variants = [];
        $minimumMandatory = 0;
        $seenPriceIds = [];
        foreach ($data['variants'] as $row) {
            $priceId = (int) $row['price_id'];
            if (isset($seenPriceIds[$priceId])) {
                throw ValidationException::withMessages(['variants' => 'مدل تکراری در پک وجود دارد.']);
            }
            $seenPriceIds[$priceId] = true;
            $price = $prices->get((int)$row['price_id']);
            if (!$price || !$price->product || !$price->product->isPhysical()) {
                throw ValidationException::withMessages(['variants' => 'یکی از مدل‌ها محصول فیزیکی معتبر ندارد.']);
            }
            $minimum = (int)$row['minimum_if_selected'];
            if ($minimum > (int)$data['required_quantity']) {
                throw ValidationException::withMessages(['variants' => 'حداقل مدل نمی‌تواند بیشتر از تعداد کل پک باشد.']);
            }
            $required = (bool)($row['required'] ?? false);
            if ($required) $minimumMandatory += $minimum;
            $variants[] = [
                'price_id' => (int)$price->id,
                'product_id' => (int)$price->product_id,
                'minimum_if_selected' => $minimum,
                'required' => $required,
            ];
        }
        if ($minimumMandatory > (int)$data['required_quantity']) {
            throw ValidationException::withMessages(['variants' => 'جمع حداقل مدل‌های اجباری از تعداد کل پک بیشتر است.']);
        }
        return [
            'offer' => [
                'title' => $data['title'], 'description' => $data['description'] ?? null,
                'required_quantity' => (int)$data['required_quantity'], 'fixed_price' => (int)$data['fixed_price'],
                'regular_unit_price' => $regularUnit, 'bundle_unit_price' => $bundleUnit,
                'starts_at' => $data['starts_at'] ?? null, 'ends_at' => $data['ends_at'],
                'is_active' => false // Locked until the customer checkout/inventory integration is validated,
            ],
            'variants' => $variants,
        ];
    }
}
