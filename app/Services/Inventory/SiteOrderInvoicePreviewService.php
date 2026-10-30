<?php

namespace App\Services\Inventory;

use App\Models\Order;

/**
 * Site-side preview for orders that do not yet have an Inventory invoice.
 * All monetary values are converted from the site's toman to Inventory's rial
 * so that the same invoice partial can render both sources consistently.
 * This preview must never be treated as an actual Inventory invoice.
 */
class SiteOrderInvoicePreviewService
{
    public function forOrder(Order $order): array
    {
        $items = [];
        $gross = 0;
        $productDiscount = 0;
        $returned = 0;
        $remaining = 0;
        $quantity = 0;

        foreach ($order->items as $item) {
            $units = max(0, (int) $item->quantity);
            $basePrice = max(0, (int) $item->originalUnitPrice());
            $finalPrice = max(0, (int) $item->finalUnitPrice());
            $lineGross = $basePrice * $units;
            $lineFinal = $finalPrice * $units;
            $lineDiscount = max(0, $lineGross - $lineFinal);
            $returnedUnits = min($units, max(0, (int) $item->returnedQuantity()));
            $notAvailable = $item->isUnavailableForShipping();
            $availableUnits = $notAvailable ? 0 : max(0, $units - $returnedUnits);

            $gross += $lineGross;
            $productDiscount += $lineDiscount;
            $returned += $returnedUnits;
            $remaining += $availableUnits;
            $quantity += $units;

            $price = $item->get_price;
            $itemNotes = is_array($item->notes) ? $item->notes : [];
            $attributes = $price && method_exists($price, 'relationLoaded') && $price->relationLoaded('get_attributes')
                ? $price->getRelation('get_attributes') : null;
            $variantName = $attributes ? $attributes->pluck('name')->filter()->implode('، ') : null;
            $items[] = [
                'invoice_item_id' => null,
                'product_name' => (string) ($item->title ?? $item->product?->title ?? '—'),
                'variant_name' => $variantName ?: null,
                'sku' => $price?->variant_code ?? null,
                'product_variant_id' => $price?->external_variant_id ?? null,
                'quantity' => $units,
                'returned_quantity' => $returnedUnits,
                'remaining_quantity' => $availableUnits,
                'price' => $basePrice * 10,
                'discount' => $lineDiscount * 10,
                'line_total' => $lineFinal * 10,
                'bundle_components_snapshot' => $itemNotes['bundle_components_snapshot'] ?? null,
                'site_product' => $item->product,
                'site_price' => $price,
                'change_type' => $notAvailable ? 'unavailable' : null,
                'site_fulfillment_message' => $item->fulfillmentMessage(),
            ];
        }

        // The site order stores the actual checkout amount and discount at purchase time.
        // Do not reprice from the current product catalogue here.
        $total = max(0, (int) $order->price);
        $discount = max(0, (int) $order->totalDiscount());
        $invoiceDiscount = max(0, $discount - $productDiscount);
        $paid = $order->isPaid();
        $canceled = (string) $order->status === 'canceled';
        $shipped = $paid && (string) $order->shipping_status === 'sent';
        $paymentStatus = $canceled ? 'canceled' : ($paid ? 'paid' : 'unpaid');
        $status = $canceled ? 'not_shipped' : ($shipped ? 'shipped' : ($paid ? 'site_pending_inventory' : 'site_unpaid'));
        $statusLabel = $canceled ? 'لغو شده در سایت' : ($shipped ? 'ارسال شده در سایت' : ($paid ? 'پرداخت شده · در انتظار اطلاعات انبار' : 'در انتظار پرداخت'));

        $payments = [];
        foreach ($order->transactions ?? [] as $transaction) {
            $payments[] = [
                'id' => (int) $transaction->id,
                'method_label' => (bool) $transaction->status ? 'پرداخت آنلاین سایت' : 'پرداخت ناموفق',
                'amount' => max(0, (int) $transaction->amount) * 10,
                'paid_at' => $transaction->created_at,
                'identifier' => null,
                'is_successful' => (bool) $transaction->status,
            ];
        }

        return [
            'source' => 'site_order',
            'invoice_id' => null,
            'invoice_uuid' => null,
            'external_order_id' => (int) $order->id,
            'status' => $status,
            'status_label' => $statusLabel,
            'payment_status' => $paymentStatus,
            'payment_status_label' => $canceled ? 'سفارش لغو شده' : ($paid ? 'پرداخت شده در سایت' : 'پرداخت نشده'),
            'document_date' => null,
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
            'customer_name' => $order->name ?? null,
            'customer_mobile' => $order->mobile ?? null,
            'customer_address' => $order->address ?? null,
            'shipping_method_name' => $order->carrier?->title ?? null,
            'subtotal' => $gross * 10,
            'product_discount_amount' => $productDiscount * 10,
            'invoice_discount_amount' => $invoiceDiscount * 10,
            'discount_amount' => $discount * 10,
            'shipping_price' => max(0, (int) $order->shipping_cost) * 10,
            'total' => $total * 10,
            'paid_amount' => ($paid ? $total : 0) * 10,
            'remaining_amount' => ($paid || $canceled ? 0 : $total) * 10,
            'overpaid_amount' => 0,
            'items_count' => count($items),
            'total_quantity' => $quantity,
            'returned_quantity' => $returned,
            'remaining_quantity' => $remaining,
            'current_items' => $items,
            'removed_items' => [],
            'payments' => $payments,
            'revision' => [],
        ];
    }
}
