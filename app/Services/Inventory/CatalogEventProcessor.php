<?php

namespace App\Services\Inventory;

use App\Models\Inventory\InventoryInboundEvent;
use App\Models\Price;
use Illuminate\Support\Facades\DB;

class CatalogEventProcessor
{
    public const TYPES = [
        'catalog.variant.updated',
        'catalog.variant.stock_changed',
        'catalog.variant.price_changed',
        'catalog.variant.disabled',

        // تغییر اقلام توسط انبار؛ فقط دریافت و ثبت می‌شود.
        'invoice.items.updated',

        // تایید نهایی مالی؛ در این مرحله Wallet sync می‌شود.
        'invoice.collection.completed',
    ];

    public function __construct(
        private readonly InventoryOverpaymentWalletService $overpaymentWalletService
    ) {
    }

    public function process(InventoryInboundEvent $event): InventoryInboundEvent
    {
        return DB::transaction(function () use ($event) {
            $payload = $event->payload['payload']
                       ?? $event->payload['data']
                          ?? $event->payload;

            /*
             * انبار اقلام فاکتور را تغییر داده است.
             *
             * هنوز مالی تغییرات را تایید نکرده، بنابراین نباید
             * اعتبار قابل استفاده‌ای به Wallet مشتری اضافه شود.
             */
            if ($event->event_type === 'invoice.items.updated') {
                $event->update([
                    'status' => 'processed',
                    'processed_at' => now(),
                    'error' => null,
                ]);

                return $event;
            }

            /*
             * مالی تغییرات انبار را تایید کرده است.
             *
             * حالا مبلغ نهایی قطعی شده و می‌توان overpayment را
             * به Wallet سایت منتقل کرد.
             */
            if ($event->event_type === 'invoice.collection.completed') {
                $this->processInvoiceOverpayment($payload);

                $event->update([
                    'status' => 'processed',
                    'processed_at' => now(),
                    'error' => null,
                ]);

                return $event;
            }

            /*
             * Catalog events
             */
            $variant = $this->findVariant($payload);

            if (!$variant) {
                $event->update([
                    'status' => 'requires_mapping',
                    'error' => 'Variant mapping not found',
                ]);

                return $event;
            }

            $updates = [
                'inventory_updated_at' => now(),
                'inventory_version' => $payload['version'] ?? null,
            ];

            if (array_key_exists('stock', $payload)) {
                $updates['stock'] = (int) $payload['stock'];
            }

            if (array_key_exists('sell_price', $payload)) {
                $updates['price'] = (int) $payload['sell_price'];
                $updates['regular_price'] = (int) $payload['sell_price'];
                // ARIYA_DIRECT_DISCOUNT_SYNC_V1: keep direct discount after warehouse repricing.
                // ARIYA_DIRECT_DISCOUNT_SYNC_THREE_MODES_V1: preserve the manager-selected mode.
                $rawBase = max(0, (int) $payload['sell_price']);
                $selectedMode = (string) ($variant->direct_discount_mode ?? '');
                if (in_array($selectedMode, ['fixed_amount','fixed_final'], true)) {
                    $regularToman = (int) round($variant->toRoundPrice($rawBase));
                    $requestedToman = max(0, (int) $variant->direct_discount_value);
                    // If ERP base drops below a fixed final/discount, clamp to base (never markup/free).
                    $finalToman = $selectedMode === 'fixed_final'
                        ? min($regularToman, max(1, $requestedToman))
                        : max(1, $regularToman - $requestedToman);
                    $updates['discount_price'] = $finalToman * 10;
                    $updates['discount'] = $regularToman > 0
                        ? \App\Services\Sales\DirectDiscountPricing::effectivePercent($regularToman, $finalToman)
                        : 0;
                } else {
                    $updates['discount_price'] = \App\Services\Sales\DirectDiscountPricing::discountedRaw(
                        $rawBase, max(0, min(99, (int) $variant->discount))
                    );
                }
            }

            if (
                $event->event_type === 'catalog.variant.disabled'
                || ($payload['disabled'] ?? false)
            ) {
                $updates['inventory_disabled'] = true;
                $updates['stock'] = 0;
            }

            $variant->update($updates);

            $event->update([
                'status' => 'processed',
                'processed_at' => now(),
                'error' => null,
            ]);

            return $event;
        });
    }

    private function processInvoiceOverpayment(array $payload): void
    {
        $orderId = (int) ($payload['external_order_id'] ?? 0);

        if ($orderId <= 0) {
            throw new \RuntimeException(
                'Inventory invoice event does not contain a valid external_order_id.'
            );
        }

        $this->overpaymentWalletService->syncForOrder($orderId);
    }

    public function findVariant(array $payload): ?Price
    {
        if (
            !empty($payload['external_variant_id'])
            && ($variant = Price::where(
                'external_variant_id',
                $payload['external_variant_id']
            )->first())
        ) {
            return $variant;
        }

        if (
            !empty($payload['variant_code'])
            && ($variant = Price::where(
                'variant_code',
                $payload['variant_code']
            )->first())
        ) {
            return $variant;
        }

        if (
            !empty($payload['sku'])
            && ($variant = Price::where(
                'external_stock_code',
                $payload['sku']
            )->first())
        ) {
            return $variant;
        }

        return null;
    }
}