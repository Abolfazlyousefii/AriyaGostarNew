<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Events\OrderPaid;
use App\Services\Inventory\InventoryOutboxService;

class CreateInventoryOrderOutboxEvent
{
    public $afterCommit = true;

    public function handle(OrderCreated|OrderPaid $event): void
    {
        app(InventoryOutboxService::class)->createOrderCreated($event->order);
    }
}
