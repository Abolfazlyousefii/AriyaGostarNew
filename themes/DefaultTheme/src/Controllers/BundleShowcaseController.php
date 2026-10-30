<?php

namespace Themes\DefaultTheme\src\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BundleOffer;
use App\Models\BundleOfferVariant;
use App\Services\Bundles\PreviewRandomComposer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

/** Read-only public teaser, intentionally isolated from cart and payment. */
class BundleShowcaseController extends Controller
{
    private function publicOffer(int $id): BundleOffer
    {
        abort_unless(config('ariya_bundle_showcase.enabled', false), 404);
        $published = array_map('intval', (array) config('ariya_bundle_showcase.offer_ids', []));
        abort_unless(in_array($id, $published, true), 404);

        return BundleOffer::query()->withCount('variants')->findOrFail($id);
    }

    public function show(BundleOffer $bundleOffer)
    {
        $offer = $this->publicOffer((int) $bundleOffer->id);
        $featuredVariant = $offer->variants()->with('price.product')->first();
        $coverProduct = $featuredVariant?->price?->product;

        $requiredPriceIds = $offer->variants()->where('required', true)->pluck('price_id')->map(fn ($id) => (int) $id)->all();

        return response()->view('front::bundles.showcase', compact('offer', 'coverProduct', 'requiredPriceIds'))
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function variants(BundleOffer $bundleOffer, Request $request)
    {
        $offer = $this->publicOffer((int) $bundleOffer->id);
        $validated = $request->validate([
            'after_id' => ['nullable', 'integer', 'min:0'],
            'q' => ['nullable', 'string', 'max:120'],
            'in_stock' => ['nullable', 'in:0,1'],
        ]);
        $q = trim($validated['q'] ?? '');
        $cursor = (int) ($validated['after_id'] ?? 0);
        $stockOnly = $request->query('in_stock') === '1';
        $items = [];
        $scanned = 0;
        $exhausted = false;
        $pageSize = 24;
        $scanMax = 480;

        while (count($items) < $pageSize && $scanned < $scanMax) {
            $batch = BundleOfferVariant::query()
                ->where('bundle_offer_id', $offer->id)
                ->where('id', '>', $cursor)
                ->whereHas('price.product')
                ->when($q !== '', function ($query) use ($q) {
                    $query->where(function ($nested) use ($q) {
                        $nested->whereHas('price.product', fn ($p) => $p->where('title', 'like', '%' . $q . '%'));
                        if (ctype_digit($q)) {
                            $nested->orWhere('price_id', (int) $q);
                        }
                    });
                })
                ->with('price.product')
                ->orderBy('id')
                ->limit(48)
                ->get();
            if ($batch->isEmpty()) {
                $exhausted = true;
                break;
            }

            $stocks = $this->centralStock($batch);
            if ($stockOnly && $stocks === null) {
                return response()->json(['message' => 'دریافت موجودی انبار ممکن نیست؛ فیلتر موجودی را خاموش کنید.'], 503);
            }

            foreach ($batch as $variant) {
                $cursor = (int) $variant->id;
                $scanned++;
                $price = $variant->price;
                $product = $price?->product;
                if (!$price || !$product) continue;
                $stock = $stocks === null ? null : ($stocks[(int) $variant->price_id] ?? 0);
                if ($stockOnly && $stock <= 0) continue;
                $items[] = [
                    'id' => (int) $variant->id,
                    'price_id' => (int) $variant->price_id,
                    'product_title' => $product->title,
                    'label' => trim($price->getAttributesName()),
                    'stock' => $stock,
                    'minimum' => max(1, (int) $variant->minimum_if_selected),
                    'required' => (bool) $variant->required,
                ];
                if (count($items) >= $pageSize) break;
            }
            if (count($items) >= $pageSize) break;
            if ($batch->count() < 48) {
                $exhausted = true;
                break;
            }
        }

        return response()->json([
            'items' => $items,
            'next_cursor' => $exhausted ? null : $cursor,
            'read_only' => true,
        ])->header('Cache-Control', 'private, no-store');
    }

    /** Stock-checked random preview; GET only, cannot create an order or reserve stock. */
    public function suggestion(BundleOffer $bundleOffer)
    {
        $offer = $this->publicOffer((int) $bundleOffer->id);
        $variants = $offer->variants()->with('price.product')->orderBy('id')->get();
        if ($variants->isEmpty()) {
            return response()->json(['message' => 'برای این پک مدلی تعریف نشده است.'], 422);
        }
        $stocks = [];
        foreach ($variants->chunk(150) as $chunk) {
            $batch = $this->centralStock($chunk);
            if ($batch === null) {
                return response()->json(['message' => 'ارتباط با انبار برقرار نیست؛ فعلاً چینش خودکار ممکن نیست.'], 503);
            }
            $stocks += $batch;
        }
        $candidates = [];
        foreach ($variants as $variant) {
            if (!$variant->price || !$variant->price->product) continue;
            $candidates[] = [
                'price_id' => (int) $variant->price_id,
                'stock' => (int) ($stocks[(int) $variant->price_id] ?? 0),
                'minimum' => max(1, (int) $variant->minimum_if_selected),
                'required' => (bool) $variant->required,
            ];
        }
        $allocation = PreviewRandomComposer::compose($candidates, (int) $offer->required_quantity);
        if ($allocation === null) {
            return response()->json(['message' => 'با موجودی فعلی و حداقل‌های مدل‌ها، چینش خودکار معتبر امکان‌پذیر نیست؛ مدل‌ها را دستی انتخاب کنید.'], 422);
        }
        $selected = [];
        foreach ($variants as $variant) {
            $id = (int) $variant->price_id;
            if (!isset($allocation[$id])) continue;
            $selected[] = [
                'price_id' => $id,
                'qty' => $allocation[$id],
                'product_title' => (string) $variant->price->product->title,
                'label' => trim((string) $variant->price->getAttributesName()),
                'stock' => $stocks[$id],
                'minimum' => max(1, (int) $variant->minimum_if_selected),
                'required' => (bool) $variant->required,
            ];
        }
        return response()->json(['items' => $selected, 'target' => (int) $offer->required_quantity, 'read_only' => true])
            ->header('Cache-Control', 'private, no-store');
    }

    /** Stock read-only; null means unavailable, never silently zero. */
    private function centralStock($variants): ?array
    {
        try {
            $inv = DB::connection('inv');
            $warehouseId = $inv->table('warehouses')->where('type', 'central')->orderBy('id')->value('id');
            if (!$warehouseId) return null;
            $result = [];
            $stockRows = $inv->table('warehouse_stocks')
                ->where('warehouse_id', $warehouseId)
                ->whereIn('product_variant_id', $variants->pluck('price_id')->all())
                ->select(['product_id', 'product_variant_id'])
                ->selectRaw('SUM(quantity) AS total_stock')
                ->groupBy('product_id', 'product_variant_id')
                ->get()
                ->keyBy(fn ($row) => (int) $row->product_id . ':' . (int) $row->product_variant_id);
            foreach ($variants as $variant) {
                $key = (int) $variant->product_id . ':' . (int) $variant->price_id;
                $result[(int) $variant->price_id] = max(0, (int) ($stockRows->get($key)->total_stock ?? 0));
            }
            return $result;
        } catch (Throwable $e) {
            report($e);
            return null;
        }
    }
}
