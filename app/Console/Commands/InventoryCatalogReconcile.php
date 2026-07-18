<?php

namespace App\Console\Commands;

use App\Models\Price;
use App\Services\Inventory\CatalogEventProcessor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class InventoryCatalogReconcile extends Command
{
    protected $signature = 'inventory:catalog:reconcile {--dry-run} {--cursor=}';
    protected $description = 'Reconcile site catalog stock and prices from Inventory snapshots.';

    public function handle(): int
    {
        if (!config('inventory.enabled') || !config('inventory.base_url')) { $this->warn('Inventory integration is disabled or base URL is missing.'); return self::SUCCESS; }
        $cursor = $this->option('cursor'); $changed = 0; $missing = 0;
        do {
            $response = Http::timeout(config('inventory.timeout', 15))->get(rtrim(config('inventory.base_url'), '/') . '/api/integrations/ariya-site/v1/catalog/snapshot', ['cursor'=>$cursor]);
            if (!$response->successful()) { $this->error('Snapshot failed: HTTP '.$response->status()); return self::FAILURE; }
            $data = $response->json();
            foreach (($data['data'] ?? $data['items'] ?? []) as $row) {
                $variant = app(CatalogEventProcessor::class)->findVariant($row);
                if (!$variant) { $missing++; $this->warn('Missing mapping: '.json_encode($row)); continue; }
                $updates = [];
                if (array_key_exists('stock', $row) && (int)$variant->stock !== (int)$row['stock']) $updates['stock'] = (int)$row['stock'];
                if (array_key_exists('sell_price', $row) && (int)$variant->price !== (int)$row['sell_price']) $updates['price'] = (int)$row['sell_price'];
                if (($row['disabled'] ?? false) && !$variant->inventory_disabled) { $updates['inventory_disabled'] = true; $updates['stock'] = 0; }
                if ($updates) { $changed++; $this->line(($this->option('dry-run')?'Would update ':'Updated ').'price#'.$variant->id.' '.json_encode($updates)); if (!$this->option('dry-run')) $variant->update($updates + ['inventory_updated_at'=>now(), 'inventory_version'=>$row['version'] ?? $variant->inventory_version]); }
            }
            $cursor = $data['next_cursor'] ?? null;
        } while ($cursor);
        $this->info("Reconciliation complete. changed={$changed}, missing_mapping={$missing}");
        return self::SUCCESS;
    }
}
