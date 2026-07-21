<?php

namespace App\Console\Commands;

use App\Services\WarehouseInventoryService;
use Illuminate\Console\Command;

class SyncWarehouseInventory extends Command
{
    protected $signature = 'warehouse:sync-stock {--code=* : Sync only the provided stock codes}';

    protected $description = 'Sync product variant stock from the configured warehouse provider';

    public function handle(WarehouseInventoryService $service): int
    {
        $codes = array_values(array_filter($this->option('code') ?: []));
        $result = $service->sync($codes ?: null);

        if ($result['status'] === 'disabled') {
            $this->warn($result['message']);
            return self::SUCCESS;
        }

        $this->info($result['message']);

        return $result['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
