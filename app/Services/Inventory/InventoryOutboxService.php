<?php

namespace App\Services\Inventory;

use App\Jobs\Inventory\SendInventoryOutboxEvent;
use App\Models\Inventory\InventoryOutboxEvent;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventoryOutboxService
{
    public function createOrderCreated(Order $order): ?InventoryOutboxEvent
    {
        if (!config('inventory.enabled') || !$order->isPaid()) return null;
        return DB::transaction(function () use ($order) {
            $existing = InventoryOutboxEvent::where('event_type', 'order.created')->where('aggregate_type', 'order')->where('aggregate_id', $order->id)->first();
            if ($existing) return $existing;
            $eventId = (string) Str::uuid();
            $payload = app(OrderEventBuilder::class)->build($order, $eventId);
            $event = InventoryOutboxEvent::create(['event_id'=>$eventId,'event_type'=>'order.created','aggregate_id'=>$order->id,'payload'=>$payload,'next_attempt_at'=>now()]);
            SendInventoryOutboxEvent::dispatch($event->id)->afterCommit()->onQueue('integrations');
            return $event;
        });
    }
}
