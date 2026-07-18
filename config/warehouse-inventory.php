<?php

use App\Services\WarehouseInventory\NullWarehouseInventoryProvider;

return [
    /*
    |--------------------------------------------------------------------------
    | Warehouse inventory integration
    |--------------------------------------------------------------------------
    |
    | Keep this disabled until an actual API adapter is implemented. The adapter
    | only needs to implement App\Contracts\WarehouseInventoryProvider and return
    | stock values indexed by each variant's external_stock_code.
    |
    */
    'enabled' => env('WAREHOUSE_INVENTORY_ENABLED', false),

    'provider' => env(
        'WAREHOUSE_INVENTORY_PROVIDER',
        NullWarehouseInventoryProvider::class
    ),

    'chunk_size' => env('WAREHOUSE_INVENTORY_CHUNK_SIZE', 100),

    'sync_every_minutes' => env('WAREHOUSE_INVENTORY_SYNC_EVERY_MINUTES', 10),
];
