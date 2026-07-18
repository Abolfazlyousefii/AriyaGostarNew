<?php

namespace App\Services\Inventory;

use App\Models\Inventory\InventoryInboundEvent;
use App\Models\Price;
use Illuminate\Support\Facades\DB;

class CatalogEventProcessor
{
    public const TYPES = ['catalog.variant.updated','catalog.variant.stock_changed','catalog.variant.price_changed','catalog.variant.disabled'];

    public function process(InventoryInboundEvent $event): InventoryInboundEvent
    {
        return DB::transaction(function () use ($event) {
            $payload = $event->payload['payload'] ?? $event->payload['data'] ?? $event->payload;
            $variant = $this->findVariant($payload);
            if (!$variant) {
                $event->update(['status'=>'requires_mapping','error'=>'Variant mapping not found']);
                return $event;
            }
            $version = $payload['version'] ?? null;
            $updatedAt = isset($payload['updated_at']) ? \Carbon\Carbon::parse($payload['updated_at']) : null;
            if (($version && $variant->inventory_version && $version <= $variant->inventory_version) || ($updatedAt && $variant->inventory_updated_at && $updatedAt->lte($variant->inventory_updated_at))) {
                $event->update(['status'=>'ignored_old','processed_at'=>now()]);
                return $event;
            }
            $updates = ['inventory_version'=>$version, 'inventory_updated_at'=>$updatedAt ?? now()];
            if (array_key_exists('stock', $payload)) $updates['stock'] = (int) $payload['stock'];
            if (array_key_exists('sell_price', $payload)) {
                $updates['price'] = (int) $payload['sell_price'];
                $updates['regular_price'] = (int) $payload['sell_price'];
                $updates['discount_price'] = (int) $payload['sell_price'];
            }
            if ($event->event_type === 'catalog.variant.disabled' || ($payload['disabled'] ?? false)) {
                $updates['inventory_disabled'] = true; $updates['stock'] = 0;
            }
            $variant->update($updates);
            $event->update(['status'=>'processed','processed_at'=>now(),'error'=>null]);
            \Log::info('Inventory catalog event applied', ['event_id'=>$event->event_id,'price_id'=>$variant->id]);
            return $event;
        });
    }

    public function findVariant(array $payload): ?Price
    {
        if (!empty($payload['external_variant_id']) && ($p = Price::where('external_variant_id', $payload['external_variant_id'])->first())) return $p;
        if (!empty($payload['variant_code']) && ($p = Price::where('variant_code', $payload['variant_code'])->first())) return $p;
        if (!empty($payload['sku']) && ($p = Price::where('external_stock_code', $payload['sku'])->first())) return $p;
        return null;
    }
}
