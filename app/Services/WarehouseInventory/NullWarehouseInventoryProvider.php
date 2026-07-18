<?php

namespace App\Services\WarehouseInventory;

use App\Contracts\WarehouseInventoryProvider;

class NullWarehouseInventoryProvider implements WarehouseInventoryProvider
{
    public function fetchStocks(array $codes): array
    {
        return [];
    }
}
