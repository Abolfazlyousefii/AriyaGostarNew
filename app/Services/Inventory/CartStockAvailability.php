<?php
namespace App\Services\Inventory;

use App\Models\Cart;
use App\Models\Price;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/** Read-only preflight. The existing atomic hold remains the final authority. */
final class CartStockAvailability
{
    public function priceLine(Price $price, int $quantity): array
    {
        return ['price_id' => (int) $price->id, 'product_id' => (int) $price->product_id,
            'title' => (string) $price->product->title,
            'variant' => (string) ($price->variant_name ?: $price->get_attributes->pluck('name')->implode(' / ')),
            'physical' => $price->product->isPhysical(), 'quantity' => $quantity,
            'minimum' => $price->cart_min, 'maximum' => $price->cart_max];
    }

    public function forPrice(Price $price, int $quantity): array
    {
        $result = $this->check([$this->priceLine($price, $quantity)]);
        $result['message'] = $result['errors'][0] ?? 'ok';
        return $result;
    }

    public function forCart(Cart $cart, array $quantities = []): array
    {
        $lines = [];
        $errors = [];
        $stale = 0;
        foreach ($cart->products as $product) {
            $quantity = (int) ($quantities[$product->pivot->id] ?? $product->pivot->quantity);
            if ($quantity === 0) continue;
            $price = $product->prices()->find($product->pivot->price_id);
            if (!$price) { $errors[] = 'مدل انتخاب‌شده برای «'.$product->title.'» دیگر قابل سفارش نیست. لطفاً این قلم را بررسی کنید.'; continue; }
            $price->setRelation('product', $product);
            $lines[] = $this->priceLine($price, $quantity);
            if ($product->isPhysical() && CartStockRules::isStale($product->pivot->updated_at ?: ($product->pivot->created_at ?: $cart->created_at))) $stale++;
        }
        $result = $this->check($lines);
        $result['errors'] = array_values(array_unique(array_merge($errors, $result['errors'])));
        $result['status'] = $result['errors'] === [];
        $result['stale_count'] = $stale;
        return $result;
    }

    public function check(array $lines): array
    {
        $physical = array_filter($lines, fn ($line) => $line['physical']);
        if (!$physical) return ['status' => true, 'errors' => [], 'lines' => [], 'stale_count' => 0];
        try {
            $db = DB::connection('inv');
            // Match DirectInventoryStockHold::centralWarehouse, including its exact warehouse selection.
            $warehouse = $db->table('warehouses')->where('type', 'central')->where('name', 'انبار مرکزی')->value('id');
            if (!$warehouse) throw new \RuntimeException('Central warehouse missing');
            $ids = array_values(array_unique(array_column($physical, 'price_id')));
            $variants = $db->table('product_variants as v')->join('products as p', 'p.id', '=', 'v.product_id')
                ->whereIn('v.id', $ids)->get(['v.id', 'v.product_id', 'v.is_active', 'v.sales_enabled', 'p.is_sellable', 'p.is_bundle', 'p.bundle_stock_mode'])->keyBy('id');
            $stocks = $db->table('warehouse_stocks')->where('warehouse_id', $warehouse)->whereIn('product_variant_id', $ids)->get()->keyBy('product_variant_id');
            $bundleIds = $variants->filter(fn ($v) => $v->is_bundle && $v->bundle_stock_mode === 'automatic')->keys();
            $recipes = $db->table('product_bundle_components')->whereIn('bundle_variant_id', $bundleIds)->get()->groupBy('bundle_variant_id');
            $allocations = $db->table('bundle_component_allocations')->whereIn('bundle_variant_id', $bundleIds)->get()->groupBy('bundle_variant_id');
            $partIds = $recipes->flatten(1)->pluck('component_variant_id')->unique();
            $parts = $db->table('product_variants as v')->join('products as p', 'p.id', '=', 'v.product_id')
                ->whereIn('v.id', $partIds)->get(['v.id', 'v.is_active', 'v.sales_enabled', 'p.is_sellable', 'p.is_bundle', 'p.bundle_stock_mode'])->keyBy('id');
            $results = [];
            $grouped = collect($physical)->groupBy('price_id');
            foreach ($grouped as $id => $group) {
                $line = $group->first();
                $line['quantity'] = $group->sum('quantity');
                $v = $variants->get($id);
                $mapped = $v && (int) $v->product_id === (int) $line['product_id'];
                $sellable = $mapped && $v->is_active && $v->sales_enabled && $v->is_sellable;
                $available = (int) ($stocks->get($id)?->quantity ?? 0);
                if ($mapped && $v->is_bundle && $v->bundle_stock_mode === 'automatic') {
                    $recipe = $recipes->get($id, collect());
                    $allocated = $allocations->get($id, collect())->keyBy('component_variant_id');
                    $available = $recipe->isEmpty() ? 0 : PHP_INT_MAX;
                    foreach ($recipe as $item) {
                        $part = $parts->get($item->component_variant_id);
                        $sellable = $sellable && $part && $part->is_active && $part->sales_enabled && $part->is_sellable
                            && !($part->is_bundle && $part->bundle_stock_mode === 'automatic');
                        $remaining = max(0, (int) ($allocated->get($item->component_variant_id)?->remaining ?? 0));
                        $available = min($available, (int) $item->quantity > 0 ? intdiv($remaining, (int) $item->quantity) : 0);
                    }
                }
                $results[$id] = CartStockRules::evaluate($line, $available, (bool) $sellable, (bool) $mapped);
            }
            $errors = array_column(array_filter($results, fn ($r) => !$r['status']), 'message');
            return ['status' => $errors === [], 'errors' => array_values($errors), 'lines' => $results, 'stale_count' => 0];
        } catch (\Throwable $e) {
            Log::warning('Cart inventory preflight unavailable', ['exception' => get_class($e)]);
            return ['status' => false, 'errors' => ['بررسی موجودی انبار موقتاً ممکن نیست. سبد شما محفوظ است؛ لطفاً کمی بعد دوباره تلاش کنید.'], 'lines' => [], 'stale_count' => 0];
        }
    }

    public function reservationErrors(ValidationException $exception, Cart $cart): array
    {
        $review = $this->forCart($cart);
        if (!$review['status']) return $review['errors'];
        $details = implode(' ', collect($exception->errors())->flatten()->all());
        preg_match_all('/\bvariant\s+(\d+)\b/i', $details, $matches);
        if ($matches[1] ?? []) {
            $items = DB::connection('inv')->table('product_variants as v')->join('products as p', 'p.id', '=', 'v.product_id')
                ->whereIn('v.id', $matches[1])->get(['p.name', 'v.variant_name']);
            if ($items->isNotEmpty()) return $items->map(fn ($v) => 'موجودی قابل رزرو «'.CartStockRules::label($v->name, $v->variant_name).'» هنگام ثبت تغییر کرد. لطفاً سبد را دوباره بررسی کنید.')->all();
        }
        return ['امکان رزرو یکی از اقلام سبد یا قطعات بسته تغییر کرده است. انتخاب‌های شما حفظ شده‌اند؛ لطفاً سبد را دوباره بررسی کنید.'];
    }
}
