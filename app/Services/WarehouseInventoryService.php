<?php

namespace App\Services;

use App\Contracts\WarehouseInventoryProvider;
use App\Events\ProductPricesChanged;
use App\Models\Price;
use App\Models\Product;
use App\Services\WarehouseInventory\NullWarehouseInventoryProvider;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Throwable;

class WarehouseInventoryService
{
    private WarehouseInventoryProvider $provider;

    public function __construct(?WarehouseInventoryProvider $provider = null)
    {
        $this->provider = $provider ?: $this->resolveProvider();
    }

    /**
     * Synchronize API-managed variant stocks without zeroing unresolved items.
     *
     * @param Collection<int, Price>|null $prices
     * @return array{updated:int, unresolved:int, failed:int}
     */
    public function sync(?Collection $prices = null): array
    {
        $result = [
            'updated' => 0,
            'unresolved' => 0,
            'failed' => 0,
        ];
        $changedProductIds = collect();

        $prices = $prices ?: Price::query()
            ->where('stock_sync_enabled', true)
            ->whereNotNull('external_stock_code')
            ->where('external_stock_code', '!=', '')
            ->get();

        if ($prices->isEmpty()) {
            return $result;
        }

        $chunkSize = max(1, (int) config('warehouse-inventory.chunk_size', 100));

        foreach ($prices->chunk($chunkSize) as $chunk) {
            $codes = $chunk
                ->pluck('external_stock_code')
                ->filter()
                ->map(fn ($code) => trim((string) $code))
                ->unique()
                ->values()
                ->all();

            try {
                $stocks = $this->normalizeStocks($this->provider->fetchStocks($codes));
            } catch (Throwable $exception) {
                foreach ($chunk as $price) {
                    $price->forceFill([
                        'stock_sync_error' => mb_substr($exception->getMessage(), 0, 2000),
                    ])->saveQuietly();
                    $result['failed']++;
                }

                report($exception);
                continue;
            }

            foreach ($chunk as $price) {
                $code = trim((string) $price->external_stock_code);

                if (!array_key_exists($code, $stocks)) {
                    $price->forceFill([
                        'stock_sync_error' => 'کد متغیر در پاسخ سرویس انبار پیدا نشد.',
                    ])->saveQuietly();
                    $result['unresolved']++;
                    continue;
                }

                $price->forceFill([
                    'stock' => max(0, (int) $stocks[$code]),
                    'stock_synced_at' => now(),
                    'stock_sync_error' => null,
                ])->saveQuietly();

                $changedProductIds->push($price->product_id);
                $result['updated']++;
            }
        }

        if ($changedProductIds->isNotEmpty()) {
            Product::whereIn('id', $changedProductIds->unique())->get()->each(function (Product $product) {
                event(new ProductPricesChanged($product));
            });

            Product::clearCache();
        }

        return $result;
    }

    private function resolveProvider(): WarehouseInventoryProvider
    {
        $providerClass = config(
            'warehouse-inventory.provider',
            NullWarehouseInventoryProvider::class
        );

        $provider = app($providerClass);

        if (!$provider instanceof WarehouseInventoryProvider) {
            throw new InvalidArgumentException(
                sprintf('%s must implement %s.', $providerClass, WarehouseInventoryProvider::class)
            );
        }

        return $provider;
    }

    /**
     * @param array<mixed> $stocks
     * @return array<string, int>
     */
    private function normalizeStocks(array $stocks): array
    {
        $normalized = [];

        foreach ($stocks as $code => $stock) {
            if (!is_scalar($code) || !is_numeric($stock)) {
                continue;
            }

            $normalized[trim((string) $code)] = max(0, (int) $stock);
        }

        return $normalized;
    }
}
