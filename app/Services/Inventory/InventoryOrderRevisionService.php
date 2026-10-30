<?php

namespace App\Services\Inventory;

use App\Models\Inventory\InventoryInboundEvent;
use App\Models\Order;
use App\Models\Price;
use Illuminate\Support\Facades\DB;
use Throwable;

class InventoryOrderRevisionService
{
    /**
     * Return the authoritative Inventory invoice snapshot for a Site order.
     *
     * For paid/synced orders, the Inventory `invoices` + `invoice_items` tables are
     * the source of truth. Site order_items stay immutable and are only a fallback
     * for legacy orders that do not have an Inventory invoice.
     */
    public function forOrder(Order $order): ?array
    {
        $event = $this->latestInboundEvent('invoice.items.updated', (int) $order->id);
        $eventPayload = $this->eventPayload($event) ?? [];

        // Always prefer the live Inventory database. This keeps the customer-facing
        // order page aligned with the exact invoice shown to finance/warehouse even
        // if a webhook is delayed or an older event had a smaller payload.
        $snapshot = $this->loadFromInventoryDatabase((int) $order->id);
        if ($snapshot === null) {
            return null;
        }

        // Event metadata is useful as a fallback only; live DB values win.
        $payload = array_replace($eventPayload, $snapshot);

        $changes = collect($payload['changes'] ?? [])
            ->map(fn ($row) => $this->normalizeChange((array) $row))
            ->values();

        // Keep the complete revision history, not only the latest revision.
        // This is important for the customer-facing order page: an item that was
        // added in revision #1 must still be shown as "added" after revision #2,
        // and a removed item must remain visible (faded/locked) instead of vanishing.
        $changeHistory = collect($payload['change_history'] ?? $payload['changes'] ?? [])
            ->map(fn ($row) => $this->normalizeChange((array) $row))
            ->values();

        $currentItems = collect($payload['current_items'] ?? [])
            ->map(fn ($row) => $this->normalizeCurrentItem((array) $row))
            ->values();

        $removedItems = collect($payload['removed_items'] ?? [])
            ->map(fn ($row) => $this->normalizeChange((array) $row))
            ->values();

        // Derive the effective removed rows from the whole history. A removed row
        // is displayed only when that variant is not present in the current invoice.
        // This prevents old remove/re-add cycles from producing duplicate ghost rows.
        $currentVariantIds = $currentItems->pluck('product_variant_id')
            ->filter(fn ($id) => (int) $id > 0)
            ->map(fn ($id) => (int) $id)
            ->unique();

        $historyRemoved = $changeHistory
            ->where('change_type', 'removed')
            ->groupBy(fn ($row) => (int) ($row['product_variant_id'] ?? 0))
            ->map(fn ($rows) => collect($rows)->last())
            ->filter(fn ($row, $variantId) => (int) $variantId > 0 && ! $currentVariantIds->contains((int) $variantId))
            ->values();

        if ($historyRemoved->isNotEmpty()) {
            $removedItems = $historyRemoved;
        } elseif ($removedItems->isEmpty()) {
            $removedItems = $changes->where('change_type', 'removed')->values();
        }

        $variantIds = $changeHistory->pluck('product_variant_id')
            ->merge($changes->pluck('product_variant_id'))
            ->merge($currentItems->pluck('product_variant_id'))
            ->merge($removedItems->pluck('product_variant_id'))
            ->filter(fn ($id) => (int) $id > 0)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $priceMap = $this->sitePriceMap($variantIds->all());
        $enrich = function (array $row) use ($priceMap): array {
            $variantId = (int) ($row['product_variant_id'] ?? 0);
            $price = $priceMap[$variantId] ?? null;
            $row['site_price'] = $price;
            $row['site_product'] = $price?->product;
            $row['site_price_id'] = $price?->id;
            $row['site_product_id'] = $price?->product_id;

            return $row;
        };

        $changes = $changes->map($enrich);
        $changeHistory = $changeHistory->map($enrich);
        $currentItems = $currentItems->map($enrich);
        $removedItems = $removedItems->map($enrich);

        $changesByItem = [];
        $changesByVariant = [];
        foreach ($changeHistory as $change) {
            $itemId = (int) ($change['invoice_item_id'] ?? 0);
            $variantId = (int) ($change['product_variant_id'] ?? 0);
            if ($itemId > 0) {
                $changesByItem[$itemId] = $change;
            }
            if ($variantId > 0) {
                $changesByVariant[$variantId] = $change;
            }
        }

        $currentItems = $currentItems->map(function (array $item) use ($changeHistory, $changesByItem, $changesByVariant): array {
            $itemId = (int) ($item['invoice_item_id'] ?? 0);
            $variantId = (int) ($item['product_variant_id'] ?? 0);

            $itemHistory = $changeHistory->filter(function (array $change) use ($itemId, $variantId) {
                $changeItemId = (int) ($change['invoice_item_id'] ?? 0);
                $changeVariantId = (int) ($change['product_variant_id'] ?? 0);

                return ($itemId > 0 && $changeItemId === $itemId)
                       || ($changeItemId <= 0 && $variantId > 0 && $changeVariantId === $variantId);
            })->values();

            $change = $itemHistory->last()
                      ?? ($changesByItem[$itemId] ?? $changesByVariant[$variantId] ?? null);

            // Preserve "added" as the visual origin of the row across later edits.
            // A subsequent quantity/price edit should not make a finance-added row
            // look like an original customer item.
            $wasAdded = $itemHistory->contains(fn (array $row) => ($row['change_type'] ?? null) === 'added');
            $effectiveType = $wasAdded ? 'added' : ($change['change_type'] ?? null);

            $item['latest_change'] = $change;
            $item['change_type'] = $effectiveType;
            $item['was_added_by_revision'] = $wasAdded;

            return $item;
        });

        $addedItems = $currentItems->where('change_type', 'added')->values();
        $modifiedItems = $currentItems->where('change_type', 'multiple_changes')->values();

        return array_replace($payload, [
            'source' => 'inventory_database',
            'changes' => $changes,
            'change_history' => $changeHistory,
            'changes_by_item' => $changesByItem,
            'changes_by_variant' => $changesByVariant,
            'current_items' => $currentItems,
            'added_items' => $addedItems,
            'removed_items' => $removedItems,
            'modified_items' => $modifiedItems,
            'source_event_id' => $event?->event_id,
            'received_at' => $event?->processed_at ?? $event?->created_at,
        ]);
    }

    private function latestInboundEvent(string $eventType, int $orderId): ?InventoryInboundEvent
    {
        try {
            $event = InventoryInboundEvent::query()
                ->where('event_type', $eventType)
                ->where('status', 'processed')
                ->where('payload->payload->external_order_id', $orderId)
                ->latest('id')
                ->first();

            if ($event) {
                return $event;
            }
        } catch (Throwable) {
            // Older DB engines / payload column definitions may not support JSON paths.
        }

        try {
            return InventoryInboundEvent::query()
                ->where('event_type', $eventType)
                ->where('status', 'processed')
                ->latest('id')
                ->limit(100)
                ->get()
                ->first(function (InventoryInboundEvent $event) use ($orderId) {
                    $payload = $this->eventPayload($event);

                    return (int) ($payload['external_order_id'] ?? 0) === $orderId;
                });
        } catch (Throwable) {
            return null;
        }
    }

    private function eventPayload(?InventoryInboundEvent $event): ?array
    {
        if (! $event) {
            return null;
        }

        $body = is_array($event->payload) ? $event->payload : [];
        $payload = $body['payload'] ?? $body['data'] ?? $body;

        return is_array($payload) ? $payload : null;
    }

    private function loadFromInventoryDatabase(int $orderId): ?array
    {
        try {
            $connection = DB::connection('inv');
            $schema = $connection->getSchemaBuilder();

            $invoice = $connection->table('invoices')->where('external_order_id', $orderId)->first();
            if (! $invoice) {
                return null;
            }

            $revision = null;
            $revisionCount = 0;
            $changes = [];
            $changeHistory = [];
            $removedItems = [];

            if ($schema->hasTable('invoice_collection_revisions')) {
                $revisionCount = (int) $connection->table('invoice_collection_revisions')
                    ->where('invoice_id', $invoice->id)
                    ->count();

                $revision = $connection->table('invoice_collection_revisions')
                    ->where('invoice_id', $invoice->id)
                    ->orderByDesc('revision_number')
                    ->orderByDesc('id')
                    ->first();
            }

            if ($revision && $schema->hasTable('invoice_collection_revision_items')) {
                $changes = $connection->table('invoice_collection_revision_items')
                    ->where('invoice_collection_revision_id', $revision->id)
                    ->orderBy('id')
                    ->get()
                    ->map(fn ($row) => [
                        'invoice_item_id' => $row->invoice_item_id !== null ? (int) $row->invoice_item_id : null,
                        'product_id' => $row->product_id !== null ? (int) $row->product_id : null,
                        'product_variant_id' => $row->product_variant_id !== null ? (int) $row->product_variant_id : null,
                        'change_type' => (string) $row->change_type,
                        'product_name' => $row->product_name_snapshot,
                        'variant_name' => $row->variant_name_snapshot,
                        'sku' => $row->sku_snapshot,
                        'old_quantity' => $row->old_quantity !== null ? (int) $row->old_quantity : null,
                        'new_quantity' => $row->new_quantity !== null ? (int) $row->new_quantity : null,
                        'old_price' => $row->old_price !== null ? (int) $row->old_price : null,
                        'new_price' => $row->new_price !== null ? (int) $row->new_price : null,
                        'old_discount' => $row->old_discount !== null ? (int) $row->old_discount : null,
                        'new_discount' => $row->new_discount !== null ? (int) $row->new_discount : null,
                        'old_line_total' => $row->old_line_total !== null ? (int) $row->old_line_total : null,
                        'new_line_total' => $row->new_line_total !== null ? (int) $row->new_line_total : null,
                    ])
                    ->values()
                    ->all();

                $removedItems = collect($changes)
                    ->where('change_type', 'removed')
                    ->values()
                    ->all();
            }

            if ($schema->hasTable('invoice_collection_revisions') && $schema->hasTable('invoice_collection_revision_items')) {
                $changeHistory = $connection->table('invoice_collection_revision_items as ri')
                    ->join('invoice_collection_revisions as r', 'r.id', '=', 'ri.invoice_collection_revision_id')
                    ->where('r.invoice_id', $invoice->id)
                    ->orderBy('r.revision_number')
                    ->orderBy('ri.id')
                    ->get([
                        'ri.*',
                        'r.revision_number',
                        'r.reason_type as revision_reason_type',
                        'r.reason_note as revision_reason_note',
                        'r.changed_by as revision_changed_by',
                        'r.created_at as revision_changed_at',
                    ])
                    ->map(fn ($row) => [
                        'invoice_item_id' => $row->invoice_item_id !== null ? (int) $row->invoice_item_id : null,
                        'product_id' => $row->product_id !== null ? (int) $row->product_id : null,
                        'product_variant_id' => $row->product_variant_id !== null ? (int) $row->product_variant_id : null,
                        'change_type' => (string) $row->change_type,
                        'product_name' => $row->product_name_snapshot,
                        'variant_name' => $row->variant_name_snapshot,
                        'sku' => $row->sku_snapshot,
                        'old_quantity' => $row->old_quantity !== null ? (int) $row->old_quantity : null,
                        'new_quantity' => $row->new_quantity !== null ? (int) $row->new_quantity : null,
                        'old_price' => $row->old_price !== null ? (int) $row->old_price : null,
                        'new_price' => $row->new_price !== null ? (int) $row->new_price : null,
                        'old_discount' => $row->old_discount !== null ? (int) $row->old_discount : null,
                        'new_discount' => $row->new_discount !== null ? (int) $row->new_discount : null,
                        'old_line_total' => $row->old_line_total !== null ? (int) $row->old_line_total : null,
                        'new_line_total' => $row->new_line_total !== null ? (int) $row->new_line_total : null,
                        'revision_number' => (int) $row->revision_number,
                        'revision_reason_type' => $row->revision_reason_type,
                        'revision_reason_note' => $row->revision_reason_note,
                        'revision_changed_by' => $row->revision_changed_by !== null ? (int) $row->revision_changed_by : null,
                        'revision_changed_at' => $row->revision_changed_at,
                    ])
                    ->values()
                    ->all();
            }

            $currentRows = $connection->table('invoice_items as ii')
                ->leftJoin('products as p', 'p.id', '=', 'ii.product_id')
                ->leftJoin('product_variants as v', 'v.id', '=', 'ii.variant_id')
                ->where('ii.invoice_id', $invoice->id)
                ->orderByRaw('COALESCE(ii.sort_order, 0)')
                ->orderBy('ii.id')
                ->get([
                    'ii.id as invoice_item_id',
                    'ii.product_id',
                    'ii.variant_id as product_variant_id',
                    'ii.quantity',
                    'ii.price',
                    'ii.line_discount_amount as discount',
                    'ii.line_total',
                    'ii.bundle_components_snapshot',
                    'p.name as product_name',
                    'v.variant_name',
                    'v.variety_name',
                    'v.variant_code',
                    'v.variety_code',
                ]);

            $invoiceItemIds = $currentRows->pluck('invoice_item_id')->map(fn ($id) => (int) $id)->all();
            $returnedByItem = $this->returnedQuantities($connection, $schema, $invoiceItemIds);

            $currentItems = $currentRows->map(function ($row) use ($returnedByItem) {
                $itemId = (int) $row->invoice_item_id;
                $quantity = (int) $row->quantity;
                $returned = min(max((int) ($returnedByItem[$itemId] ?? 0), 0), max($quantity, 0));

                return [
                    'invoice_item_id' => $itemId,
                    'product_id' => (int) $row->product_id,
                    'product_variant_id' => (int) $row->product_variant_id,
                    'product_name' => $row->product_name,
                    'variant_name' => $row->variant_name ?: $row->variety_name,
                    'sku' => $row->variant_code ?: $row->variety_code,
                    'quantity' => $quantity,
                    'returned_quantity' => $returned,
                    'remaining_quantity' => max($quantity - $returned, 0),
                    'price' => (int) $row->price,
                    'gross_line_total' => max($quantity * (int) $row->price, 0),
                    'discount' => (int) ($row->discount ?? 0),
                    'line_total' => (int) $row->line_total,
                    'bundle_components_snapshot' => $row->bundle_components_snapshot ? json_decode((string) $row->bundle_components_snapshot, true) : null,
                ];
            })->values()->all();

            $payments = [];
            if ($schema->hasTable('invoice_payments')) {
                $payments = $connection->table('invoice_payments')
                    ->where('invoice_id', $invoice->id)
                    ->orderBy('paid_at')
                    ->orderBy('id')
                    ->get()
                    ->map(fn ($row) => [
                        'id' => (int) $row->id,
                        'method' => (string) ($row->method ?? ''),
                        'method_label' => $this->paymentMethodLabel((string) ($row->method ?? '')),
                        'amount' => (int) ($row->amount ?? 0),
                        'paid_at' => $row->paid_at ?? $row->created_at ?? null,
                        'identifier' => $row->payment_identifier ?? null,
                        'bank_name' => $row->bank_name ?? null,
                        'note' => $row->note ?? null,
                    ])
                    ->values()
                    ->all();
            }

            $paidAmount = (int) collect($payments)->sum('amount');
            $total = (int) ($invoice->total ?? 0);
            $remainingAmount = max($total - $paidAmount, 0);
            $overpaidAmount = max($paidAmount - $total, 0);

            $provinceName = null;
            $cityName = null;
            if ($schema->hasTable('provinces') && ! empty($invoice->province_id)) {
                $provinceName = $connection->table('provinces')->where('id', $invoice->province_id)->value('name');
            }
            if ($schema->hasTable('cities') && ! empty($invoice->city_id)) {
                $cityName = $connection->table('cities')->where('id', $invoice->city_id)->value('name');
            }

            $shippingMethodName = null;
            $dispatchShippingMethodName = null;
            if ($schema->hasTable('shipping_methods')) {
                if (! empty($invoice->shipping_id)) {
                    $shippingMethodName = $connection->table('shipping_methods')->where('id', $invoice->shipping_id)->value('name');
                }
                if (! empty($invoice->shipping_method_id)) {
                    $dispatchShippingMethodName = $connection->table('shipping_methods')->where('id', $invoice->shipping_method_id)->value('name');
                }
            }

            $changedByName = null;
            if ($revision && ! empty($revision->changed_by) && $schema->hasTable('users')) {
                $changedByName = $connection->table('users')->where('id', $revision->changed_by)->value('name');
            }

            $status = (string) ($invoice->status ?? '');
            $paymentStatus = $paidAmount <= 0
                ? 'unpaid'
                : ($overpaidAmount > 0 ? 'overpaid' : ($remainingAmount > 0 ? 'partial' : 'paid'));

            $oldTotal = $revision ? (int) $revision->old_total : $total;
            $currentTotal = $total;

            return [
                'invoice_id' => (int) $invoice->id,
                'invoice_uuid' => (string) $invoice->uuid,
                'external_order_id' => (int) $orderId,
                'currency_label' => 'ریال',
                'status' => $status,
                'status_label' => $this->invoiceStatusLabel($status),
                'finance_approved' => in_array($status, ['pending_collection', 'warehouse_received', 'collecting', 'ready_to_ship', 'shipped'], true),
                'payment_status' => $paymentStatus,
                'payment_status_label' => $this->paymentStatusLabel($paymentStatus),
                'customer_name' => $invoice->customer_name ?? null,
                'customer_mobile' => $invoice->customer_mobile ?? null,
                'customer_address' => $invoice->customer_address ?? null,
                'province_name' => $provinceName,
                'city_name' => $cityName,
                'document_date' => $invoice->document_date ?? $invoice->created_at ?? null,
                'created_at' => $invoice->created_at ?? null,
                'updated_at' => $invoice->updated_at ?? null,
                'status_changed_at' => $invoice->status_changed_at ?? null,
                'warehouse_received_at' => $invoice->warehouse_received_at ?? null,
                'collection_started_at' => $invoice->collection_started_at ?? null,
                'collected_at' => $invoice->collected_at ?? null,
                'items_updated_at' => $invoice->items_updated_at ?? null,
                'shipped_at' => $invoice->shipped_at ?? null,
                'shipping_note' => $invoice->shipping_note ?? null,
                'collection_note' => $invoice->collection_note ?? null,
                'shipping_method_name' => $dispatchShippingMethodName ?: $shippingMethodName,
                'order_shipping_method_name' => $shippingMethodName,
                'dispatch_shipping_method_name' => $dispatchShippingMethodName,
                'subtotal' => (int) ($invoice->subtotal ?? collect($currentItems)->sum('gross_line_total')),
                'product_discount_amount' => (int) ($invoice->product_discount_amount ?? collect($currentItems)->sum('discount')),
                'invoice_discount_amount' => (int) ($invoice->invoice_discount_amount ?? 0),
                'discount_amount' => (int) ($invoice->discount_amount ?? 0),
                'shipping_price' => (int) ($invoice->shipping_price ?? 0),
                'shipping_cost' => (int) ($invoice->shipping_cost ?? 0),
                'total' => $currentTotal,
                'old_total' => $oldTotal,
                'new_total' => $currentTotal,
                'difference' => $currentTotal - $oldTotal,
                'paid_amount' => $paidAmount,
                'remaining_amount' => $remainingAmount,
                'overpaid_amount' => $overpaidAmount,
                'items_count' => count($currentItems),
                'total_quantity' => (int) collect($currentItems)->sum('quantity'),
                'returned_quantity' => (int) collect($currentItems)->sum('returned_quantity'),
                'remaining_quantity' => (int) collect($currentItems)->sum('remaining_quantity'),
                'payments' => $payments,
                'revision_id' => $revision ? (int) $revision->id : null,
                'revision_count' => $revisionCount,
                'revision' => $revision ? [
                    'id' => (int) $revision->id,
                    'number' => (int) $revision->revision_number,
                    'reason_type' => $revision->reason_type,
                    'reason_label' => $this->revisionReasonLabel((string) ($revision->reason_type ?? '')),
                    'reason_note' => $revision->reason_note,
                    'changed_by' => $revision->changed_by !== null ? (int) $revision->changed_by : null,
                    'changed_by_name' => $changedByName,
                    'changed_at' => $revision->created_at,
                    'source' => $revision->reason_type === 'invoice_correction' ? 'invoice_edit' : 'warehouse_collection',
                    'source_label' => $revision->reason_type === 'invoice_correction' ? 'ویرایش فاکتور' : 'انبار / جمع‌آوری',
                ] : [],
                'changes' => $changes,
                'change_history' => $changeHistory,
                'removed_items' => $removedItems,
                'current_items' => $currentItems,
            ];
        } catch (Throwable) {
            return null;
        }
    }

    /** @return array<int,int> */
    private function returnedQuantities($connection, $schema, array $invoiceItemIds): array
    {
        if ($invoiceItemIds === []) {
            return [];
        }

        $new = [];
        if ($schema->hasTable('sales_return_documents') && $schema->hasTable('sales_return_document_items')) {
            $new = $connection->table('sales_return_document_items as i')
                ->join('sales_return_documents as d', 'd.id', '=', 'i.document_id')
                ->whereIn('d.status', ['applied', 'pending_warehouse'])
                ->whereIn('i.invoice_item_id', $invoiceItemIds)
                ->groupBy('i.invoice_item_id')
                ->pluck(DB::raw('sum(i.return_quantity)'), 'i.invoice_item_id')
                ->map(fn ($value) => (int) $value)
                ->all();
        }

        $legacy = [];
        if ($schema->hasTable('warehouse_transfers') && $schema->hasTable('warehouse_transfer_items')) {
            $legacy = $connection->table('warehouse_transfer_items as wi')
                ->join('warehouse_transfers as wt', 'wt.id', '=', 'wi.warehouse_transfer_id')
                ->where('wt.voucher_type', 'customer_return')
                ->whereNotNull('wi.invoice_item_id')
                ->whereIn('wi.invoice_item_id', $invoiceItemIds)
                ->groupBy('wi.invoice_item_id')
                ->pluck(DB::raw('sum(wi.quantity)'), 'wi.invoice_item_id')
                ->map(fn ($value) => (int) $value)
                ->all();
        }

        $out = [];
        foreach ($invoiceItemIds as $id) {
            $out[(int) $id] = (int) ($new[$id] ?? 0) + (int) ($legacy[$id] ?? 0);
        }

        return $out;
    }

    private function normalizeChange(array $row): array
    {
        foreach (['invoice_item_id', 'product_id', 'product_variant_id', 'old_quantity', 'new_quantity', 'old_price', 'new_price', 'old_discount', 'new_discount', 'old_line_total', 'new_line_total'] as $field) {
            if (array_key_exists($field, $row) && $row[$field] !== null && $row[$field] !== '') {
                $row[$field] = (int) $row[$field];
            }
        }
        $row['change_type'] = (string) ($row['change_type'] ?? '');

        return $row;
    }

    private function normalizeCurrentItem(array $row): array
    {
        foreach (['invoice_item_id', 'product_id', 'product_variant_id', 'quantity', 'returned_quantity', 'remaining_quantity', 'price', 'gross_line_total', 'discount', 'line_total'] as $field) {
            if (array_key_exists($field, $row) && $row[$field] !== null && $row[$field] !== '') {
                $row[$field] = (int) $row[$field];
            }
        }

        return $row;
    }

    /** @return array<int, Price> */
    private function sitePriceMap(array $variantIds): array
    {
        if ($variantIds === []) {
            return [];
        }

        $ids = collect($variantIds)->map(fn ($id) => (int) $id)->filter()->unique()->values();
        $stringIds = $ids->map(fn ($id) => (string) $id)->all();

        $prices = Price::query()
            ->with('product')
            ->where(function ($query) use ($ids, $stringIds) {
                $query->whereIn('id', $ids->all())
                    ->orWhereIn('external_variant_id', $stringIds);
            })
            ->get();

        $map = [];
        foreach ($prices as $price) {
            $externalId = (int) ($price->external_variant_id ?? 0);
            if ($externalId > 0) {
                $map[$externalId] = $price;
            }
            if (! isset($map[(int) $price->id])) {
                $map[(int) $price->id] = $price;
            }
        }

        return $map;
    }

    private function invoiceStatusLabel(string $status): string
    {
        return [
                   'pending_warehouse_approval' => 'در انتظار تأیید انبار',
                   'collecting' => 'در حال جمع‌آوری',
                   'checking_discrepancy' => 'در حال بررسی مغایرت',
                   'final_check' => 'در حال چک نهایی',
                   'packing' => 'در حال بسته‌بندی',
                   'shipped' => 'ارسال شده',
                   'not_shipped' => 'لغو شده',
                   'pending_collection' => 'در صف جمع‌آوری',
                   'warehouse_received' => 'دریافت‌شده توسط انبار',
                   'pending_finance_reapproval' => 'در انتظار تأیید مجدد مالی',
                   'ready_to_ship' => 'آماده ارسال',
                   'returned_to_sales_after_collection' => 'ارجاع‌شده به اپراتور پس از اصلاح انبار',
               ][$status] ?? ($status !== '' ? $status : 'نامشخص');
    }

    private function paymentStatusLabel(string $status): string
    {
        return [
                   'unpaid' => 'پرداخت‌نشده',
                   'partial' => 'پرداخت ناقص',
                   'paid' => 'تسویه‌شده',
                   'overpaid' => 'پرداخت اضافه',
               ][$status] ?? 'نامشخص';
    }

    private function paymentMethodLabel(string $method): string
    {
        return [
                   'online' => 'پرداخت آنلاین سایت',
                   'wallet' => 'کیف پول',
                   'cash' => 'نقدی',
                   'cheque' => 'چک',
                   'card' => 'کارت',
                   'bank_transfer' => 'حواله بانکی',
               ][$method] ?? ($method !== '' ? $method : 'نامشخص');
    }

    private function revisionReasonLabel(string $reason): string
    {
        return [
                   'physical_shortage' => 'کسری فیزیکی',
                   'customer_cancelled' => 'انصراف مشتری',
                   'wrong_item' => 'کالای اشتباه',
                   'warehouse_correction' => 'اصلاح انبار',
                   'replacement' => 'جایگزینی کالا',
                   'invoice_correction' => 'اصلاح فاکتور',
                   'warehouse_queue_items' => 'اصلاح اقلام در صف انبار',
                   'other' => 'سایر',
               ][$reason] ?? ($reason !== '' ? $reason : 'بدون اصلاح');
    }
}
