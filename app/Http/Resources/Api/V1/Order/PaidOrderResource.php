<?php

namespace App\Http\Resources\Api\V1\Order;

use Illuminate\Http\Resources\Json\JsonResource;

class PaidOrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'stock_hold_id' => $this->inventory_stock_hold_id,
            'status_text' => $this->statusText(),
            'customer' => [
                'id' => $this->user_id,
                'name' => $this->name,
                'mobile' => $this->mobile,
                'username' => optional($this->user)->username,
            ],
            'delivery' => [
                'province' => optional($this->province)->name,
                'city' => optional($this->city)->name,
                'postal_code' => $this->postal_code,
                'address' => $this->address,
                'shipping_status' => $this->shipping_status,
                'tracking_code' => $this->tracking_code,
            ],
            'shipping_cost' => $this->shipping_cost,
            'discount_amount' => $this->discount_amount,
            'price' => $this->price,
            'created_at' => $this->created_at,
            'items' => $this->items->map(function ($item) {
                return [
                    'product_code' => $item->product?->product_code,
                    'title' => optional($item->product)->title ?: $item->title,
                    'variant_code' => optional($item->get_price)->variant_code,
                    'product_id' => $item->product_id,
                    'price_id' => $item->price_id,
                    'is_physical' => (bool) (!$item->product || $item->product->isPhysical()),
                    'quantity' => $item->quantity,
                    'unit_price' => $item->price,
                    'real_unit_price' => $item->real_price,
                    'discount' => $item->discount,
                    'line_discount_amount' => $item->discountAmount(),
                ];
            })->values(),
        ];
    }
}
