<?php

namespace App\Console\Commands;

use App\Models\Price;
use App\Services\WarehouseInventoryService;
use Illuminate\Console\Command;

class SyncWarehouseInventory extends Command
{
    protected $signature = 'warehouse:sync-stock {--price=* : Sync only the given price IDs}';

    protected $description = 'Synchronize product-variant stock from the configured warehouse inventory provider';

    public function handle(WarehouseInventoryService $service): int
    {
        if (!config('warehouse-inventory.enabled', false)) {
            $this->warn('Warehouse inventory sync is disabled. Set WAREHOUSE_INVENTORY_ENABLED=true after connecting a provider.');
            return self::SUCCESS;
        }

        $query = Price::query()
            ->where('stock_sync_enabled', true)
            ->whereNotNull('external_stock_code')
            ->where('external_stock_code', '!=', '');

        $priceIds = array_values(array_filter($this->option('price')));

        if ($priceIds) {
            $query->whereIn('id', $priceIds);
        }

        $result = $service->sync($query->get());

        $this->info(sprintf(
            'Updated: %d | Unresolved: %d | Failed: %d',
            $result['updated'],
            $result['unresolved'],
            $result['failed']
        ));

        return $result['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
