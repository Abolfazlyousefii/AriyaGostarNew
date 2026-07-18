<?php

namespace App\Services\Inventory;

use App\Models\Order;
use Illuminate\Support\Str;

class OrderEventBuilder
{
    public function build(Order $order, string $eventId): array
    {
        $order->loadMissing(['items.get_price.product', 'user', 'province', 'city']);
        return [
            'event_id' => $eventId,
            'event_type' => 'order.created',
            'occurred_at' => now()->toIso8601String(),
            'source' => 'ariya-site',
            'order' => [
                'id' => (string) $order->id,
                'number' => (string) $order->id,
                'status' => $order->status,
                'payment_status' => $order->isPaid() ? 'paid' : 'unpaid',
                'currency' => 'IRR',
                'total' => (int) $order->price,
                'shipping_cost' => (int) $order->shipping_cost,
                'discount_total' => (int) ($order->discount_amount ?? 0),
                'customer' => [
                    'id' => $order->user_id ? (string) $order->user_id : null,
                    'name' => $order->name,
                    'mobile' => $order->mobile,
                    'postal_code' => $order->postal_code,
                    'province' => optional($order->province)->name,
                    'city' => optional($order->city)->name,
                    'address' => $order->address,
                ],
                'items' => $order->items->map(function ($item) {
                    $price = $item->get_price;
                    return [
                        'order_item_id' => (string) $item->id,
                        'product_id' => (string) $item->product_id,
                        'price_id' => (string) $item->price_id,
                        'external_variant_id' => optional($price)->external_variant_id,
                        'variant_code' => optional($price)->variant_code ?? optional($price)->external_stock_code,
                        'sku' => optional($price)->external_stock_code,
                        'title' => $item->title,
                        'quantity' => (int) $item->quantity,
                        'unit_price' => (int) $item->price,
                        'regular_price' => (int) $item->real_price,
                        'discount' => (int) ($item->discount ?? 0),
                    ];
                })->values()->all(),
            ],
        ];
    }
}
