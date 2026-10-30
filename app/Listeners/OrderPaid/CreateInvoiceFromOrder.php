<?php

namespace App\Listeners\OrderPaid;

use App\Events\OrderPaid;
use App\Models\Inventory\Customer;
use App\Models\Inventory\Invoice;
use App\Models\Inventory\InvoiceItem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class CreateInvoiceFromOrder implements ShouldQueue
{
    /** Inventory user used as the audit actor for Site-originated stock movements. */
    private const INVENTORY_SYNC_USER_ID = 108;
    private const INVENTORY_SITE_SELLER_USERNAME = 'ariya_site';

    public $tries = 3;

    private bool $deferredMode = false;

    /** Create an ERP receivable without claiming that cash was received. */
    public function syncDeferredOrder(\App\Models\Order $order): void
    {
        $this->deferredMode = true;
        try {
            $this->handle(new OrderPaid($order));
        } finally {
            $this->deferredMode = false;
        }
    }

    public function handle(OrderPaid $event): void
    {
        $order = $event->order;
        // Held orders are imported by Inventory's paid-order service, which consumes their
        // stock hold once. The old direct writer must never deduct that stock again.
        // ARIYA_ORDER89_EXPIRED_HOLD_ONLY_V2
        // Leave all paid held orders to the existing paid hold consumer.
        // Only the operator-approved *unpaid* order 89 may be queued via deferred mode.
        if ($order->inventory_stock_hold_id) {
            if (! ($this->deferredMode && (int) $order->id === 89)) return;
            if ((int) $order->inventory_stock_hold_id !== 31 || (string) $order->status !== 'unpaid') {
                throw new RuntimeException('Unexpected Site order/hold state for unpaid ERP queue.');
            }
        }
        $order->load(['user', 'items']);

        try {
            /** @var \App\Models\Inventory\Invoice $invoice */
            $invoice = DB::connection('inv')->transaction(function () use ($order) {
                $siteUserId = (int) ($order->user_id ?? 0);
                $mobile = trim((string) ($order->mobile ?? ''));
                $manual = \App\Services\Inventory\ManualBankTransferPaymentMethod::confirmationForOrderId((int) $order->id);
                // ARIYA_ERP_DISCOUNT_RIAL_SYNC_V1
                // Reconcile EVERY site order in Toman before writing ANY ERP records.
                // Manual payment and deferred credit modes still behave independently.
                $pendingCollection = $manual !== null || $this->deferredMode;
                $manualAmounts = \App\Services\Inventory\ManualOrderPaymentAmounts::forOrder($order);

                if ($siteUserId <= 0) {
                    throw new RuntimeException('Order user_id is empty; inventory customer cannot be resolved.');
                }

                /*
                 * Canonical Site -> Inventory identity:
                 * Site users.id <-> Inventory customers.site_customer_id
                 *
                 * Mobile is only a fallback for the first mapping and a conflict check.
                 * It is NOT the primary identity key anymore.
                 */
                $customer = Customer::query()
                    ->where('site_customer_id', $siteUserId)
                    ->lockForUpdate()
                    ->first();

                if (! $customer) {
                    if ($mobile === '') {
                        throw new RuntimeException(
                            'Customer mapping does not exist and order mobile is empty. site_user_id=' . $siteUserId
                        );
                    }

                    $customerByMobile = Customer::query()
                        ->where('mobile', $mobile)
                        ->lockForUpdate()
                        ->first();

                    if ($customerByMobile) {
                        $mappedSiteUserId = (int) ($customerByMobile->site_customer_id ?? 0);

                        if ($mappedSiteUserId > 0 && $mappedSiteUserId !== $siteUserId) {
                            throw new RuntimeException(
                                'Inventory customer mapping conflict. '
                                . 'site_user_id=' . $siteUserId
                                . ', customer_id=' . $customerByMobile->id
                                . ', mapped_site_user_id=' . $mappedSiteUserId
                            );
                        }

                        if ($mappedSiteUserId === 0) {
                            $customerByMobile->forceFill([
                                'site_customer_id' => $siteUserId,
                            ])->save();
                        }

                        $customer = $customerByMobile->fresh();
                    } else {
                        $customer = Customer::create([
                            'site_customer_id' => $siteUserId,
                            'mobile' => $mobile,
                            'name' => $this->resolveCustomerName($order),
                            'address' => $order->address ?? '',
                        ]);
                    }
                }

                $invoice = Invoice::query()
                    ->where('external_order_id', $order->id)
                    ->lockForUpdate()
                    ->first();

                if ($this->deferredMode && $invoice) {
                    if ((string) $invoice->status !== Invoice::STATUS_PENDING_COLLECTION
                        || (int) $invoice->customer_id !== (int) $customer->id
                        || DB::connection('inv')->table('invoice_payments')
                            ->where('invoice_id', (int) $invoice->id)->exists()) {
                        throw new RuntimeException('ERP invoice is not a safely resumable unpaid credit invoice.');
                    }
                }

                $createdNow = false;

                if (! $invoice) {
                    $invoice = $this->createInvoice($order, $customer, $manualAmounts, $pendingCollection);
                    $this->createInvoiceItems($invoice, $order);
                    $invoice->recalculateSnapshotTotals();
                    $invoice->refresh();

                    // Validate the exact Rial amount before ANY stock consumption.
                    if ($manualAmounts && (int) $invoice->total !== $manualAmounts['total_rial']) {
                        throw new RuntimeException('ERP invoice total does not match the site order (Rial); stock was not consumed.');
                    }

                    // Only newly-created invoices consume initial stock.
                    // Existing invoices are never auto-repaired to avoid accidental double deduction.
                    // ARIYA_ORDER89_UNPAID_QUEUE_NO_STOCK_V2
                    // SKIP ERP STOCK AT INVOICE CREATION (order 89 only).
                    // Invoices are queued for warehouse collection; the warehouse
                    // MUST reconcile all original stock (not just shortages) by
                    // official ERP collection/revision procedures before dispatch.
                    if ($this->deferredMode && (int) $order->id === 89) {
                        $queueHold = DB::connection('inv')->table('site_stock_holds')
                            ->where('id', 31)->where('site_order_id', 89)
                            ->lockForUpdate()->first(['status', 'consumed_at', 'released_at']);
                        if (! $queueHold || (string) $queueHold->status !== 'expired'
                            || $queueHold->consumed_at !== null || $queueHold->released_at === null
                            || (string) $order->status !== 'unpaid'
                            || (int) $order->price !== 27656000) {
                            throw new RuntimeException('Refusing deferred stock queue: order or expired hold mismatch.');
                        }
                        Log::warning('Unpaid order 89 queued to ERP WITHOUT automatic stock deduction; warehouse reconciliation required for ALL lines.', [
                            'order_id' => 89, 'invoice_id' => (int) $invoice->id,
                        ]);
                    } else {
                        $this->consumeInitialInventoryStock($invoice);
                    }
                    $createdNow = true;
                } else {
                    Log::info('Inventory invoice already exists; stock is not auto-repaired, financial sync will be ensured.', [
                        'order_id' => $order->id,
                        'invoice_id' => $invoice->id,
                    ]);
                }

                // A retry must not quietly accept an old/mismatched manual invoice.
                if ($manualAmounts && (int) $invoice->total !== $manualAmounts['total_rial']) {
                    throw new RuntimeException('Existing ERP invoice amount differs from site order; refusing to mutate payment or ledger.');
                }
                $this->syncInvoiceDebit($invoice);
                if (! $this->deferredMode) {
                    $this->syncSitePayment($invoice, $customer, $order, $manual);
                }

                Log::info('Inventory invoice sync transaction completed.', [
                    'order_id' => $order->id,
                    'invoice_id' => $invoice->id,
                    'created_now' => $createdNow,
                ]);

                return $invoice->fresh();
            });

            $order->forceFill([
                'inventory_document_type' => 'invoice',
                'inventory_document_id' => $invoice->id,
                'inventory_document_uuid' => $invoice->uuid,
                'inventory_sync_status' => 'synced',
                'inventory_synced_at' => now(),
                'inventory_last_error' => null,
            ])->save();

            Log::info('Paid order synced to Inventory successfully.', [
                'order_id' => $order->id,
                'invoice_id' => $invoice->id,
                'invoice_uuid' => $invoice->uuid,
            ]);
        } catch (Throwable $e) {
            try {
                $order->forceFill([
                    'inventory_sync_status' => 'failed',
                    'inventory_last_error' => Str::limit($e->getMessage(), 1000, ''),
                ])->save();
            } catch (Throwable $orderUpdateError) {
                Log::error('Could not persist inventory sync failure on site order.', [
                    'order_id' => $order->id,
                    'error' => $orderUpdateError->getMessage(),
                ]);
            }

            Log::error('Paid order -> Inventory sync failed.', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function createInvoice($order, Customer $customer, ?array $manualAmounts = null, bool $pendingCollection = false): Invoice
    {
        $sellerId = DB::connection('inv')
            ->table('users')
            ->where('username', self::INVENTORY_SITE_SELLER_USERNAME)
            ->where('is_seller', true)
            ->value('id');

        if (! $sellerId) {
            Log::notice('Site-originated ERP invoice uses nullable seller_id: dedicated seller not configured.', [
                'site_order_id' => (int) $order->id,
            ]);
        }
        $invoice = Invoice::create([
            'uuid' => $this->generateInvoiceNumber(),
            'external_order_id' => $order->id,
            'customer_id' => $customer->id,
            'seller_id' => $sellerId ? (int) $sellerId : null,
            'document_date' => $order->created_at,
            'customer_name' => $this->resolveCustomerName($order),
            'customer_mobile' => $order->mobile ?? '',
            'customer_address' => $order->address ?? '',
            'province_id' => $order->province_id,
            'city_id' => $order->city_id,
            'shipping_id' => $order->carrier_id,
            'shipping_method_id' => null,
            // Site order monetary values are in toman; Inventory invoices are in rials.
            'shipping_price' => $manualAmounts['shipping_rial'] ?? (int) ($order->shipping_cost ?? 0) * 10,
            'discount_amount' => $manualAmounts['total_discount_rial'] ?? (int) ($order->discount_amount ?? 0) * 10,
            'subtotal' => 0,
            'total' => 0,
            'status' => $pendingCollection ? Invoice::STATUS_PENDING_COLLECTION : Invoice::STATUS_COLLECTING,
            'status_changed_at' => now(),
        ]);
        if ($manualAmounts !== null) {
            // Invoice::recalculateSnapshotTotals() distinguishes line vs invoice discount.
            // forceFill avoids silently discarded attributes when fillable differs.
            $invoice->forceFill([
                'invoice_discount_amount' => $manualAmounts['document_discount_rial'],
                'product_discount_amount' => $manualAmounts['line_discount_rial'],
            ])->save();
        }
        return $invoice;
    }

    private function createInvoiceItems(Invoice $invoice, $order): void
    {
        foreach ($order->items as $orderItem) {
            $lineDiscount = 0;

            if (
                isset($orderItem->real_price, $orderItem->price)
                && $orderItem->real_price > $orderItem->price
            ) {
                $lineDiscount = ($orderItem->real_price - $orderItem->price)
                                * $orderItem->quantity;
            }

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $orderItem->product_id,
                'variant_id' => $orderItem->price_id,
                'quantity' => $orderItem->quantity,
                'price' => (int) ($orderItem->real_price ?? $orderItem->price)* 10,
                'line_discount_amount' => (int) $lineDiscount * 10,
                'sort_order' => (int) ($orderItem->sort_order ?? 0),
            ]);
        }
    }

    /**
     * Consume initial stock for a newly-created Site invoice.
     *
     * Mirrors the important Inventory stock invariants:
     * - central warehouse stock is reduced
     * - ProductVariant.stock is synced from central warehouse
     * - Product.stock is synced from all central variants
     * - an auditable StockMovement is created
     *
     * This runs inside the same inv transaction as invoice creation, so any
     * failure rolls back invoice/items/stock/ledger/payment together.
     */
    private function consumeInitialInventoryStock(Invoice $invoice): void
    {
        $inv = DB::connection('inv');

        $centralWarehouseId = (int) $inv->table('warehouses')
            ->where('type', 'central')
            ->orderBy('id')
            ->value('id');

        if ($centralWarehouseId <= 0) {
            throw new RuntimeException('Central warehouse could not be resolved in Inventory.');
        }

        $movementUserId = self::INVENTORY_SYNC_USER_ID;

        $movementUserExists = $inv->table('users')->where('id', $movementUserId)->exists();
        if (! $movementUserExists) {
            throw new RuntimeException(
                'Configured Inventory sync user does not exist. user_id=' . $movementUserId
            );
        }

        $items = $inv->table('invoice_items')
            ->where('invoice_id', (int) $invoice->id)
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        if ($items->isEmpty()) {
            throw new RuntimeException('Inventory invoice has no items; stock cannot be consumed.');
        }

        foreach ($items as $item) {
            $productId = (int) $item->product_id;
            $variantId = (int) $item->variant_id;
            $quantity = (int) $item->quantity;

            if ($productId <= 0 || $variantId <= 0 || $quantity <= 0) {
                throw new RuntimeException(
                    "Invalid invoice item for stock consumption. invoice_item_id={$item->id}"
                );
            }

            $variant = $inv->table('product_variants')
                ->where('id', $variantId)
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->first();

            if (! $variant) {
                throw new RuntimeException(
                    "Inventory variant does not belong to product. product_id={$productId}, variant_id={$variantId}"
                );
            }

            $movementReference = sprintf(
                'SITE-ORDER-%d-INVITEM-%d',
                (int) $invoice->external_order_id,
                (int) $item->id
            );

            // Defensive idempotency layer inside the transaction.
            $movementAlreadyExists = $inv->table('stock_movements')
                ->where('reference', $movementReference)
                ->where('reference_type', 'App\\Models\\Invoice')
                ->where('reference_id', (int) $invoice->id)
                ->where('type', 'out')
                ->exists();

            if ($movementAlreadyExists) {
                continue;
            }

            $stock = $inv->table('warehouse_stocks')
                ->where('warehouse_id', $centralWarehouseId)
                ->where('product_id', $productId)
                ->where('product_variant_id', $variantId)
                ->lockForUpdate()
                ->first();

            if (! $stock) {
                throw new RuntimeException(
                    "Central warehouse stock row is missing. product_id={$productId}, variant_id={$variantId}"
                );
            }

            $before = (int) $stock->quantity;

            if ($before < $quantity) {
                throw new RuntimeException(
                    'Insufficient Inventory stock for paid Site order. '
                    . "product_id={$productId}, variant_id={$variantId}, "
                    . "requested={$quantity}, available={$before}"
                );
            }

            $after = $before - $quantity;
            $now = now();

            $inv->table('warehouse_stocks')
                ->where('id', (int) $stock->id)
                ->update([
                    'quantity' => $after,
                    'updated_at' => $now,
                ]);

            // Mirror WarehouseStockService::syncVariantStockFromCentral().
            $variantCentralQty = (int) $inv->table('warehouse_stocks')
                ->where('warehouse_id', $centralWarehouseId)
                ->where('product_variant_id', $variantId)
                ->sum('quantity');

            $inv->table('product_variants')
                ->where('id', $variantId)
                ->update([
                    'stock' => max(0, $variantCentralQty),
                    'updated_at' => $now,
                ]);

            // Mirror WarehouseStockService::syncProductStockFromCentral().
            $productCentralQty = (int) $inv->table('warehouse_stocks')
                ->where('warehouse_id', $centralWarehouseId)
                ->where('product_id', $productId)
                ->whereNotNull('product_variant_id')
                ->sum('quantity');

            $inv->table('products')
                ->where('id', $productId)
                ->update([
                    'stock' => max(0, $productCentralQty),
                    'updated_at' => $now,
                ]);

            $inv->table('stock_movements')->insert([
                'product_id' => $productId,
                'product_variant_id' => $variantId,
                'warehouse_id' => $centralWarehouseId,
                'user_id' => $movementUserId,
                'type' => 'out',
                'reason' => 'sale',
                'transaction_type' => 'site_order_sync',
                'quantity' => $quantity,
                'stock_before' => $before,
                'stock_after' => $after,
                'note' => 'کسر موجودی بابت سفارش پرداخت‌شده سایت #' . $invoice->external_order_id,
                'reference' => $movementReference,
                'reference_type' => 'App\\Models\\Invoice',
                'reference_id' => (int) $invoice->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function syncInvoiceDebit(Invoice $invoice): void
    {
        $now = now();

        DB::connection('inv')->table('customer_ledgers')->updateOrInsert(
            [
                'customer_id' => (int) $invoice->customer_id,
                'reference_type' => 'App\\Models\\Invoice',
                'reference_id' => (int) $invoice->id,
                'type' => 'debit',
            ],
            [
                'amount' => (int) $invoice->total,
                'note' => 'ثبت/بروزرسانی بدهکاری بابت حواله فروش ' . $invoice->uuid,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
    }

    private function syncSitePayment(Invoice $invoice, Customer $customer, $order, ?object $manual = null): void
    {
        $inv = DB::connection('inv');
        $identifierPrefix = 'SITE-ORDER-' . $order->id;
        $paymentNote = 'پرداخت سفارش سایت #' . $order->id;
        $method = $manual ? 'bank_transfer' : 'online';
        $bankReference = $manual ? trim((string) $manual->receipt_reference) : '';
        if ($manual && $bankReference === '') {
            throw new RuntimeException('Manual bank transfer reference is missing.');
        }
        // Preserve stable SITE-ORDER prefix for existing idempotency query.
        $paymentIdentifier = $manual ? $identifierPrefix . '-BANK-' . $bankReference : $identifierPrefix;
        $paymentDescription = $manual
            ? ('کارت‌به‌کارت سفارش سایت #' . $order->id . ' | شماره پیگیری: ' . $bankReference)
            : $paymentNote;

        $payment = $inv->table('invoice_payments')
            ->where('invoice_id', (int) $invoice->id)
            ->where(function ($query) use ($identifierPrefix, $paymentNote) {
                $query->where('payment_identifier', $identifierPrefix)
                    ->orWhere('payment_identifier', 'like', $identifierPrefix . '-%')
                    ->orWhere('note', $paymentNote);
            })
            ->orderBy('id')
            ->first();

        if ($payment && $manual && (
            (string) $payment->method !== 'bank_transfer'
            || (string) $payment->payment_identifier !== $paymentIdentifier
            || (int) $payment->amount !== (int) $invoice->total
        )) {
            throw new RuntimeException('Existing invoice payment conflicts with manual bank receipt.');
        }

        if (! $payment) {
            $now = now();

            $paymentId = $inv->table('invoice_payments')->insertGetId([
                'invoice_id' => (int) $invoice->id,
                'customer_id' => (int) $customer->id,
                'created_by' => null,
                'method' => $method,
                'amount' => (int) $invoice->total,
                'paid_at' => $manual
                    ? substr((string) $manual->confirmed_at, 0, 10)
                    : (optional($order->updated_at)->toDateString() ?: now()->toDateString()),
                'bank_name' => null,
                'payment_identifier' => $paymentIdentifier,
                'receipt_image' => null,
                'note' => $paymentDescription,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $payment = $inv->table('invoice_payments')
                ->where('id', $paymentId)
                ->first();
        }

        if (! $payment) {
            throw new RuntimeException('Inventory payment could not be created for site order #' . $order->id);
        }

        $now = now();
        $paymentCustomerId = (int) ($payment->customer_id ?: $customer->id);

        $inv->table('customer_ledgers')->updateOrInsert(
            [
                'customer_id' => $paymentCustomerId,
                'reference_type' => 'App\\Models\\InvoicePayment',
                'reference_id' => (int) $payment->id,
                'type' => 'credit',
            ],
            [
                'amount' => (int) $payment->amount,
                'note' => 'ثبت پرداخت برای فاکتور ' . $invoice->uuid . ' (' . $method . ')',
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
    }

    private function resolveCustomerName($order): string
    {
        $name = trim(
            (string) ($order->user->first_name ?? '')
            . ' '
            . (string) ($order->user->last_name ?? '')
        );

        return $name !== '' ? $name : (string) ($order->name ?? 'مشتری ناشناس');
    }

    protected function generateInvoiceNumber(): string
    {
        $lastInvoice = Invoice::query()
            ->whereRaw('LENGTH(uuid) = ? AND uuid REGEXP ?', [5, '^[0-9]{' . 5 . '}$'])
            ->orderByRaw('CAST(uuid AS UNSIGNED) DESC')
            ->lockForUpdate()
            ->first();

        $nextNumber = $lastInvoice ? (int) $lastInvoice->uuid + 1 : 1;

        return str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);
    }
}
