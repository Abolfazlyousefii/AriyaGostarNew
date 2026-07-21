<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Warehouse inventory integration
    |--------------------------------------------------------------------------
    |
    | Integration is intentionally disabled until a real provider is written.
    | The provider must implement WarehouseInventoryProvider and return an
    | associative array in the form: ['ARY-V-0000000001' => 25].
    |
    */
    'enabled' => env('WAREHOUSE_INVENTORY_ENABLED', false),

    'provider' => env(
        'WAREHOUSE_INVENTORY_PROVIDER',
        App\Services\WarehouseInventory\NullWarehouseInventoryProvider::class
    ),

    'chunk_size' => (int) env('WAREHOUSE_INVENTORY_CHUNK_SIZE', 100),

    'product_prefix' => env('WAREHOUSE_PRODUCT_CODE_PREFIX', 'ARY-P-'),
    'variant_prefix' => env('WAREHOUSE_VARIANT_CODE_PREFIX', 'ARY-V-'),
];
