<?php

namespace App\Services\Inventory;

use App\Models\Order;
use Carbon\CarbonImmutable;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

/**
 * MySQL-only Inventory reservation transport.
 * All Inventory writes share ONE `inv` transaction, including component allocation,
 * warehouse stock, summaries, hold lines and a durable sync-outbox marker.
 * No HTTP and no cross-connection Laravel transaction illusion.
 */
final class DirectInventoryStockHold
{
    /** Shared boundary lock stays held through every stock mutation in this transaction. */
    private function countState(ConnectionInterface $db): ?object
    {
        if (!$db->getSchemaBuilder()->hasTable('inventory_count_freezes')) return null;
        return $db->table('inventory_count_freezes')->where('id', 1)->sharedLock()->first();
    }

    private function assertCountOpen(ConnectionInterface $db, ?object $state, array $productIds): void
    {
        if (!$state || !$state->enabled || !$productIds) return;
        $productIds = array_values(array_unique(array_map('intval', $productIds)));
        $snapshot = array_map('intval', json_decode($state->product_ids ?? '[]', true));
        $selected = array_map('intval', json_decode($state->category_ids ?? '[]', true));
        $categories = $selected;
        $frontier = $selected;
        while ($frontier) {
            $children = $db->table('categories')->whereIn('parent_id', $frontier)->pluck('id')->map(fn ($id) => (int) $id)->all();
            $frontier = array_values(array_diff($children, $categories));
            $categories = array_values(array_unique(array_merge($categories, $frontier)));
        }
        $blocked = (bool) $state->all_products || array_intersect($snapshot, $productIds)
            || $db->table('products')->whereIn('id', $productIds)->whereIn('category_id', $categories)->exists();
        // Newly defined or moved bundles cannot bypass a frozen component category.
        if (!$blocked && $db->getSchemaBuilder()->hasTable('product_bundle_components')) {
            $blocked = $db->table('product_bundle_components as r')
                ->join('product_variants as b', 'b.id', '=', 'r.bundle_variant_id')
                ->join('product_variants as part', 'part.id', '=', 'r.component_variant_id')
                ->join('products as p', 'p.id', '=', 'part.product_id')
                ->whereIn('b.product_id', $productIds)
                ->where(fn ($q) => $q->whereIn('p.id', $snapshot)->orWhereIn('p.category_id', $categories))->exists();
        }
        if ($blocked) {
            $label = $state->all_products ? 'تمام کالاها' : $db->table('categories')->whereIn('id', $selected)->orderBy('name')->pluck('name')->join('، ');
            $this->invalid('دستهٔ «'.$label.'» برای انبارگردانی بسته است؛ خرید و تغییر رزرو تا بازکردن صریح دسته امکان‌پذیر نیست.');
        }
    }

    private function db(): ConnectionInterface
    {
        $db = DB::connection('inv');
        if ($db->getDriverName() !== 'mysql' || DB::connection()->getDatabaseName() === $db->getDatabaseName()) {
            throw new RuntimeException('Direct holds require a separate Inventory MySQL schema; verify DB_INV/DATABASE_URL.');
        }
        foreach (['host', 'port', 'unix_socket'] as $field) {
            if ((string) config('database.connections.mysql.'.$field) !==
                (string) config('database.connections.inv.'.$field)) {
                throw new RuntimeException('Site and Inventory must be on the same MySQL server.');
            }
        }
        // Laravel's DATABASE_URL may override a connection's database name.
        $configured = (string) config('database.connections.inv.database');
        if ($configured === '' || $db->getDatabaseName() !== $configured) {
            throw new RuntimeException('DB_INV does not match the effective Inventory connection database.');
        }
        return $db;
    }

    public function reserve(Order $order, array $lines, int $ttlMinutes): array
    {
        if ($order->id < 1 || $ttlMinutes < 1 || $ttlMinutes > 1440) {
            throw ValidationException::withMessages(['hold' => 'Invalid order ID or hold duration.']);
        }
        $commercial = $this->canonical($lines);
        $fingerprint = hash('sha256', json_encode($commercial, JSON_THROW_ON_ERROR));
        $db = $this->db();
        // Expired stock is reclaimed when the next checkout occurs; no cron is required.
        $this->expireOverdue(30);

        try {
            return $db->transaction(function () use ($db, $order, $commercial, $fingerprint, $ttlMinutes): array {
            $freeze = $this->countState($db);
            $this->assertCountOpen($db, $freeze, $db->table('product_variants')->whereIn('id', array_keys($commercial))->pluck('product_id')->all());
            $existing = $db->table('site_stock_holds')->where('site_order_id', $order->id)
                ->lockForUpdate()->first();
            if ($existing) return $this->same($existing, $fingerprint);

            // Lock the bundle products before reading recipes; admin edits lock these rows too.
            $allVariants = $db->table('product_variants as v')
                ->join('products as p', 'p.id', '=', 'v.product_id')
                ->whereIn('v.id', array_keys($commercial))
                ->select('v.id', 'v.product_id', 'v.is_active', 'v.sales_enabled',
                    'p.is_sellable', 'p.is_bundle', 'p.bundle_stock_mode')->get()->keyBy('id');
            if ($allVariants->count() !== count($commercial)) $this->invalid('Unknown Inventory variant.');
            $bundleIds = [];
            foreach ($commercial as $id => $units) {
                $variant = $allVariants->get($id);
                if (!$variant->is_active || !$variant->sales_enabled || !$variant->is_sellable) {
                    $this->invalid('Variant is unavailable for sale.');
                }
                if ($variant->is_bundle && $variant->bundle_stock_mode === 'automatic') {
                    $bundleIds[$id] = (int) $variant->product_id;
                }
            }
            if ($bundleIds) {
                $db->table('products')->whereIn('id', array_values($bundleIds))
                    ->orderBy('id')->lockForUpdate()->get();
            }

            $physical = [];
            $requirements = [];
            foreach ($commercial as $id => $units) {
                if (!isset($bundleIds[$id])) {
                    $physical[$id] = ($physical[$id] ?? 0) + $units;
                    continue;
                }
                $recipe = $db->table('product_bundle_components')
                    ->where('bundle_variant_id', $id)
                    ->orderBy('component_variant_id')
                    ->pluck('quantity', 'component_variant_id');
                if ($recipe->isEmpty()) $this->invalid('Automatic bundle has no components.');
                foreach ($recipe as $componentId => $perBundle) {
                    $partId = (int) $componentId;
                    $perBundle = (int) $perBundle;
                    if ($partId < 1 || $perBundle < 1 || $units > intdiv(PHP_INT_MAX, $perBundle)) {
                        $this->invalid('Invalid component quantity.');
                    }
                    $need = $units * $perBundle;
                    if (($physical[$partId] ?? 0) > PHP_INT_MAX - $need) {
                        $this->invalid('Component quantity overflow.');
                    }
                    $physical[$partId] = ($physical[$partId] ?? 0) + $need;
                    $requirements[$id][$partId] = $need;
                }
            }
            ksort($physical, SORT_NUMERIC);

            $components = $db->table('product_variants as v')
                ->join('products as p', 'p.id', '=', 'v.product_id')
                ->whereIn('v.id', array_keys($physical))
                ->select('v.id', 'v.product_id', 'v.is_active', 'v.sales_enabled',
                    'p.is_sellable', 'p.is_bundle', 'p.bundle_stock_mode')->get()->keyBy('id');
            if ($components->count() !== count($physical)) $this->invalid('Unknown bundle component.');
            foreach ($components as $v) {
                if (!$v->is_active || !$v->sales_enabled || !$v->is_sellable ||
                    ($v->is_bundle && $v->bundle_stock_mode === 'automatic')) {
                    $this->invalid('Bundle component unavailable for sale.');
                }
            }

            $this->assertCountOpen($db, $freeze, $components->pluck('product_id')->all());
            $allocated = [];
            ksort($requirements, SORT_NUMERIC);
            foreach ($requirements as $bundleVariantId => $parts) {
                ksort($parts, SORT_NUMERIC);
                $rows = $db->table('bundle_component_allocations')
                    ->where('bundle_variant_id', $bundleVariantId)
                    ->orderBy('component_variant_id')->lockForUpdate()->get()->keyBy('component_variant_id');
                foreach ($parts as $componentId => $need) {
                    $record = $rows->get($componentId);
                    if (!$record || $need > (int) $record->remaining) {
                        $this->invalid("Insufficient protected bundle allocation for variant {$componentId}.");
                    }
                    $db->table('bundle_component_allocations')->where('id', $record->id)
                        ->update(['remaining' => (int) $record->remaining - $need, 'updated_at' => now()]);
                    $allocated[$componentId] = ($allocated[$componentId] ?? 0) + $need;
                }
            }

            $normal = $physical;
            foreach ($allocated as $partId => $units) {
                $normal[$partId] -= $units;
                if ($normal[$partId] < 0) throw new RuntimeException('Allocated requirements exceed the physical plan.');
                if ($normal[$partId] === 0) unset($normal[$partId]);
            }

            $centralId = $this->centralWarehouse($db);
            $rows = $db->table('warehouse_stocks')->where('warehouse_id', $centralId)
                ->whereIn('product_variant_id', array_keys($normal))
                ->orderBy('product_variant_id')->lockForUpdate()->get()->keyBy('product_variant_id');
            $stockErrors = [];
            foreach ($normal as $partId => $need) {
                $available = (int) ($rows->get($partId)?->quantity ?? 0);
                if ($available < $need) {
                    $stockErrors[] = "Insufficient ordinary stock for variant {$partId}; requested {$need}; available {$available}.";
                }
            }
            if ($stockErrors !== []) $this->invalid($stockErrors);

            $now = now();
            $holdId = (int) $db->table('site_stock_holds')->insertGetId([
                'site_order_id' => $order->id,
                'fingerprint' => $fingerprint,
                'status' => 'held',
                'expires_at' => $now->copy()->addMinutes($ttlMinutes),
                'bundle_allocated_lines' => $requirements ? json_encode($requirements, JSON_THROW_ON_ERROR) : null,
                'created_at' => $now, 'updated_at' => $now,
            ]);
            $holdLines = [];
            foreach ($physical as $partId => $qty) {
                $holdLines[] = [
                    'hold_id' => $holdId,
                    'variant_id' => $partId,
                    'product_id' => (int) $components[$partId]->product_id,
                    'quantity' => $qty,
                ];
            }
            $db->table('site_stock_hold_lines')->insert($holdLines);

            $touched = [];
            foreach ($normal as $partId => $units) {
                $record = $rows[$partId];
                $db->table('warehouse_stocks')->where('id', $record->id)
                    ->update(['quantity' => (int) $record->quantity - $units, 'updated_at' => $now]);
                $touched[$partId] = (int) $components[$partId]->product_id;
            }
            $this->refreshSummaries($db, $centralId, $touched);
            // The automatic bundle's sellable stock also changes, even though its
            // reserved components were already removed from ordinary stock.
            foreach ($bundleIds as $bundleVariantId => $productId) $touched[$bundleVariantId] = $productId;
            $this->enqueueSync($db, $touched);
            $this->refreshSiteStock($db, $touched);

            return $this->result((object) [
                'id' => $holdId, 'site_order_id' => $order->id,
                'status' => 'held', 'expires_at' => $now->copy()->addMinutes($ttlMinutes),
            ]);
            }, 3);
        } catch (QueryException $e) {
            // Unique site_order_id index resolves concurrent identical retries.
            $existing = $db->table('site_stock_holds')->where('site_order_id', $order->id)->first();
            if ($existing) return $this->same($existing, $fingerprint);
            throw $e;
        }
    }

    public function confirm(Order $order): array
    {
        return $this->transition($order, 'confirm');
    }

    public function release(Order $order): array
    {
        return $this->transition($order, 'release');
    }

    public function status(Order $order): array
    {
        $this->expireOverdue(15);
        $hold = $this->db()->table('site_stock_holds')->where('site_order_id', $order->id)->first();
        if (!$hold) throw new RuntimeException('Inventory stock hold not found.');
        return $this->result($hold);
    }

    private function transition(Order $order, string $action): array
    {
        $holdId = (int) $order->inventory_stock_hold_id;
        if ($holdId < 1) throw new RuntimeException('Order has no stock reservation.');
        $db = $this->db();
        return $db->transaction(function () use ($db, $order, $holdId, $action) {
            $freeze = $this->countState($db);
            $hold = $db->table('site_stock_holds')->where('id', $holdId)
                ->where('site_order_id', $order->id)->lockForUpdate()->first();
            if (!$hold) throw new RuntimeException('Inventory stock reservation does not belong to this order.');
            if ($action === 'confirm') {
                if (in_array($hold->status, ['payment_confirmed', 'consumed'], true)) return $this->result($hold);
                if ($hold->status !== 'held') $this->invalid('Stock hold has already been released.');
                $db->table('site_stock_holds')->where('id', $holdId)->update([
                    'status' => 'payment_confirmed', 'confirmed_at' => now(), 'updated_at' => now(),
                ]);
                return $this->result((object) array_merge((array) $hold, ['status' => 'payment_confirmed']));
            }
            if (in_array($hold->status, ['released', 'expired'], true)) return $this->result($hold);
            if ($hold->status !== 'held') $this->invalid('Paid or consumed stock cannot be released.');
            $requirements = $hold->bundle_allocated_lines
                ? json_decode((string) $hold->bundle_allocated_lines, true, 512, JSON_THROW_ON_ERROR) : [];
            $productIds = $db->table('site_stock_hold_lines')->where('hold_id', $holdId)->pluck('product_id')->all();
            $productIds = array_merge($productIds, $db->table('product_variants')->whereIn('id', array_keys($requirements))->pluck('product_id')->all());
            $legacyIds = array_keys(json_decode((string) ($hold->bundle_shared_allocations ?? '[]'), true, 512, JSON_THROW_ON_ERROR));
            $this->assertCountOpen($db, $freeze, array_merge($productIds, $legacyIds));
            $allocated = [];
            $touched = [];
            ksort($requirements, SORT_NUMERIC);
            foreach ($requirements as $bundleVariantId => $parts) {
                $bundleVariantId = (int) $bundleVariantId;
                $bundleProductId = $db->table('product_variants')->where('id', $bundleVariantId)->value('product_id');
                if (!$bundleProductId) throw new RuntimeException('Bundle product is missing.');
                $db->table('products')->where('id', $bundleProductId)->lockForUpdate()->first();
                $touched[$bundleVariantId] = (int) $bundleProductId;
                ksort($parts, SORT_NUMERIC);
                $rows = $db->table('bundle_component_allocations')
                    ->where('bundle_variant_id', $bundleVariantId)
                    ->orderBy('component_variant_id')->lockForUpdate()->get()->keyBy('component_variant_id');
                foreach ($parts as $partId => $units) {
                    $row = $rows->get((int) $partId);
                    $units = (int) $units;
                    if (!$row || $units < 1 || (int) $row->remaining > 1000000 - $units) {
                        $this->invalid('Cannot restore protected bundle allocation.');
                    }
                    $db->table('bundle_component_allocations')->where('id', $row->id)->update([
                        'remaining' => (int) $row->remaining + $units, 'updated_at' => now(),
                    ]);
                    $allocated[(int) $partId] = ($allocated[(int) $partId] ?? 0) + $units;
                }
            }
            // Compatibility for holds created before protected allocations were introduced.
            $legacy = json_decode((string) ($hold->bundle_shared_allocations ?? '[]'), true, 512, JSON_THROW_ON_ERROR);
            foreach ($legacy as $productId => $count) {
                $product = $db->table('products')->where('id', (int) $productId)->lockForUpdate()->first();
                if (!$product) throw new RuntimeException('Legacy bundle no longer exists.');
                $db->table('products')->where('id', (int) $productId)->update([
                    'bundle_shared_remaining' => (int) $product->bundle_shared_remaining + (int) $count,
                    'updated_at' => now(),
                ]);
                $touched[0-(int)$productId] = (int) $productId;
            }
            $centralId = $this->centralWarehouse($db);
            $stockTouched = [];
            $lines = $db->table('site_stock_hold_lines')->where('hold_id', $holdId)
                ->orderBy('variant_id')->get();
            foreach ($lines as $line) {
                $quantity = (int) $line->quantity - ($allocated[(int) $line->variant_id] ?? 0);
                if ($quantity < 0) throw new RuntimeException('Protected allocation exceeds held units.');
                if ($quantity === 0) continue;
                $row = $db->table('warehouse_stocks')
                    ->where('warehouse_id', $centralId)
                    ->where('product_variant_id', $line->variant_id)
                    ->lockForUpdate()->first();
                if (!$row || $row->product_id != $line->product_id) {
                    throw new RuntimeException('Warehouse stock row disappeared; cannot safely release.');
                }
                $db->table('warehouse_stocks')->where('id', $row->id)->update([
                    'quantity' => (int) $row->quantity + $quantity, 'updated_at' => now(),
                ]);
                $stockTouched[(int) $line->variant_id] = (int) $line->product_id;
                $touched[(int) $line->variant_id] = (int) $line->product_id;
            }
            // Never overwrite an automatic bundle's virtual availability with 0
            // just because its component allocation (not physical stock) changed.
            $this->refreshSummaries($db, $centralId, $stockTouched);
            $this->enqueueSync($db, $touched);
            $this->refreshSiteStock($db, $touched);
            $db->table('site_stock_holds')->where('id', $holdId)->update([
                'status' => $action === 'expire' ? 'expired' : 'released', 'released_at' => now(), 'updated_at' => now(),
            ]);
            return $this->result((object) array_merge((array) $hold, ['status' => $action === 'expire' ? 'expired' : 'released']));
        }, 3);
    }

    /**
     * Reclaim expired UNPAID holds during ordinary checkout/stock access.
     * Idle rows need no worker. Payment-confirmed/consumed holds are never touched.
     * Keep the batch bounded: a large historic backlog must not exhaust PHP memory.
     */
    public function expireOverdue(int $limit = 30): int
    {
        $db = $this->db();
        $schema = $this->siteSchema();
        $ids = $db->table('site_stock_holds')->where('status', 'held')
            ->where('expires_at', '<=', now())->orderBy('expires_at')
            ->limit(max(1, min(100, $limit)))->pluck('id');
        $count = 0;
        foreach ($ids as $id) {
            try {
                $released = $db->transaction(function () use ($db, $schema, $id): bool {
                    // Same lock order as Inventory activation: freeze boundary before hold/order rows.
                    $this->countState($db);
                    $hold = $db->table('site_stock_holds')->where('id', $id)->first();
                    if (!$hold || $hold->status !== 'held' ||
                        CarbonImmutable::parse($hold->expires_at)->isFuture()) return false;
                    // Lock the Site order BEFORE the hold. Keep both locks until
                    // restoration commits, to avoid racing a payment status update.
                    $siteOrder = $db->table($schema.'.orders')->where('id', $hold->site_order_id)
                        ->lockForUpdate()->first(['id', 'status']);
                    if (!$siteOrder || !in_array($siteOrder->status, ['unpaid', 'canceled'], true)) return false;
                    $locked = $db->table('site_stock_holds')->where('id', $id)->lockForUpdate()->first();
                    if (!$locked || $locked->status !== 'held' ||
                        CarbonImmutable::parse($locked->expires_at)->isFuture()) return false;
                    $order = new Order();
                    $order->id = (int) $siteOrder->id;
                    $order->inventory_stock_hold_id = (int) $locked->id;
                    // The nested transaction reuses this same MySQL connection;
                    // no state can become paid between the status check and release.
                    $this->transition($order, 'expire');
                    return true;
                }, 3);
                if ($released) $count++;
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Deferred expired Inventory hold recovery', [
                    'hold_id' => (int) $id, 'exception' => $e::class,
                ]);
            }
        }
        return $count;
    }

    private function siteSchema(): string
    {
        $schema = (string) DB::connection()->getDatabaseName();
        if (!preg_match('/^[A-Za-z0-9_]+$/D', $schema)) {
            throw new RuntimeException('Unsafe Site schema name.');
        }
        return $schema;
    }

    /**
     * Keep customer-visible stock in the existing Site prices table current
     * IN THE SAME MySQL transaction as the reservation. No HTTP or scheduler.
     * Ordinary variants use the same central warehouse as checkout reservations.
     * Virtual bundle availability uses the protected component allocation ledger.
     */
    private function refreshSiteStock(ConnectionInterface $db, array $touched): void
    {
        $schema = $this->siteSchema();
        $now = now();
        $centralId = $this->centralWarehouse($db);
        foreach ($touched as $variantId => $productId) {
            $variantId = (int) $variantId;
            if ($variantId < 1) continue;
            $variant = $db->table('product_variants as v')
                ->join('products as p', 'p.id', '=', 'v.product_id')
                ->where('v.id', $variantId)
                ->first(['v.id','v.is_active','v.sales_enabled','p.is_sellable','p.is_bundle','p.bundle_stock_mode']);
            if (!$variant) continue;
            $stock = 0;
            if ($variant->is_active && $variant->sales_enabled && $variant->is_sellable) {
                if ($variant->is_bundle && $variant->bundle_stock_mode === 'automatic') {
                    $recipe = $db->table('product_bundle_components')
                        ->where('bundle_variant_id', $variantId)->pluck('quantity', 'component_variant_id');
                    if ($recipe->isNotEmpty()) {
                        // Match Inventory AutomaticBundleAvailabilityService:
                        // any disabled or nested component makes the bundle unsellable.
                        $usable = $db->table('product_variants as v')
                            ->join('products as p', 'p.id', '=', 'v.product_id')
                            ->whereIn('v.id', $recipe->keys()->all())
                            ->where('v.is_active', 1)->where('v.sales_enabled', 1)
                            ->where('p.is_sellable', 1)->where('p.is_bundle', 0)
                            ->count();
                        if ($usable !== $recipe->count()) {
                            $db->table($schema.'.prices')->where('id', $variantId)
                                ->update(['stock' => 0, 'updated_at' => $now]);
                            continue;
                        }
                        $allocations = $db->table('bundle_component_allocations')
                            ->where('bundle_variant_id', $variantId)->pluck('remaining', 'component_variant_id');
                        $min = PHP_INT_MAX;
                        foreach ($recipe as $componentId => $perBundle) {
                            $perBundle = (int) $perBundle;
                            if ($perBundle <= 0) { $min = 0; break; }
                            $min = min($min, intdiv((int) ($allocations[$componentId] ?? 0), $perBundle));
                        }
                        $stock = $min === PHP_INT_MAX ? 0 : $min;
                    }
                } else {
                    $stock = (int) $db->table('warehouse_stocks')
                        ->where('warehouse_id', $centralId)
                        ->where('product_variant_id', $variantId)->sum('quantity');
                }
            }
            // Only update existing synchronized price rows. Creating products/prices
            // remains the job of the existing Inventory->Site sync service.
            $db->table($schema.'.prices')->where('id', $variantId)
                ->update(['stock' => max(0, $stock), 'updated_at' => $now]);
        }
    }

    private function centralWarehouse(ConnectionInterface $db): int
    {
        $id = $db->table('warehouses')->where('type', 'central')
            ->where('name', 'انبار مرکزی')->value('id');
        if (!$id) throw new RuntimeException('Central Inventory warehouse is not configured.');
        return (int) $id;
    }

    /** Keep Inventory warehouse, variant and product quantity projections consistent. */
    private function refreshSummaries(ConnectionInterface $db, int $warehouseId, array $touched): void
    {
        if (!$touched) return;
        foreach ($touched as $variantId => $productId) {
            if ($variantId <= 0) continue;
            $total = $db->table('warehouse_stocks')->where('warehouse_id', $warehouseId)
                ->where('product_variant_id', $variantId)->sum('quantity');
            $db->table('product_variants')->where('id', $variantId)->update([
                'stock' => max(0, (int) $total), 'updated_at' => now(),
            ]);
        }
        foreach (array_unique(array_values($touched)) as $productId) {
            $total = $db->table('warehouse_stocks')->where('warehouse_id', $warehouseId)
                ->where('product_id', $productId)->whereNotNull('product_variant_id')
                ->sum('quantity');
            $db->table('products')->where('id', $productId)->update([
                'stock' => max(0, (int) $total), 'updated_at' => now(),
            ]);
        }
    }

    /** Durable outbox, drained on a relevant Inventory request (no cron). */
    private function enqueueSync(ConnectionInterface $db, array $variantToProduct): void
    {
        $now = now();
        foreach ($variantToProduct as $variantId => $productId) {
            $db->table('site_stock_sync_outbox')->insert([
                'product_id' => $productId, 'variant_id' => max(0, (int) $variantId), 'origin' => 'site',
                'created_at' => $now, 'available_at' => $now,
            ]);
        }
    }

    private function canonical(array $lines): array
    {
        if (!$lines || count($lines) > 500) $this->invalid('Checkout must have 1 to 500 stock lines.');
        $total = [];
        foreach ($lines as $line) {
            $id = $line['variant_id'] ?? null;
            $qty = $line['quantity'] ?? null;
            if (!(is_int($id) || (is_string($id) && ctype_digit($id))) ||
                !(is_int($qty) || (is_string($qty) && ctype_digit($qty))) ||
                (int) $id < 1 || (int) $qty < 1 || (int) $qty > 100000) {
                $this->invalid('Invalid variant or order quantity.');
            }
            $id = (int) $id;
            if (($total[$id] ?? 0) > 100000 - (int) $qty) $this->invalid('Quantity exceeds checkout limit.');
            $total[$id] = ($total[$id] ?? 0) + (int) $qty;
        }
        ksort($total, SORT_NUMERIC);
        return $total;
    }

    private function same(object $existing, string $hash): array
    {
        if (!hash_equals((string) $existing->fingerprint, $hash) ||
            !in_array($existing->status, ['held', 'payment_confirmed', 'consumed'], true)) {
            $this->invalid('Existing order has a changed or released reservation.');
        }
        return $this->result($existing);
    }

    private function result(object $hold): array
    {
        return [
            'hold_id' => (int) $hold->id,
            'site_order_id' => (int) $hold->site_order_id,
            'status' => (string) $hold->status,
            'expires_at' => CarbonImmutable::parse($hold->expires_at)->toIso8601String(),
        ];
    }

    private function invalid(string|array $message): never
    {
        throw ValidationException::withMessages(['stock' => $message]);
    }
}
