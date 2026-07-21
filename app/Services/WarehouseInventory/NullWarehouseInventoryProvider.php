<?php

namespace App\Services\WarehouseInventory;

use App\Contracts\WarehouseInventoryProvider;

class NullWarehouseInventoryProvider implements WarehouseInventoryProvider
{
    public function fetchStocks(array $stockCodes): array
    {
        return [];
    }
}
