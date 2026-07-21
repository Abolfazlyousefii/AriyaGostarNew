<?php

namespace App\Services;

use App\Contracts\WarehouseInventoryProvider;
use App\Models\Price;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class WarehouseInventoryService
{
    public function sync(?array $onlyCodes = null): array
    {
        if (!config('warehouse-inventory.enabled')) {
            return [
                'status' => 'disabled',
                'updated' => 0,
                'failed' => 0,
                'message' => 'اتصال موجودی انبار غیرفعال است.',
            ];
        }

        $providerClass = config('warehouse-inventory.provider');
        $provider = app($providerClass);

        if (!$provider instanceof WarehouseInventoryProvider) {
            throw new RuntimeException('Warehouse inventory provider must implement WarehouseInventoryProvider.');
        }

        $query = Price::query()
            ->where('stock_sync_enabled', true)
            ->whereNotNull('stock_code')
            ->where('stock_code', '!=', '');

        if (!empty($onlyCodes)) {
            $query->whereIn('stock_code', $onlyCodes);
        }

        $updated = 0;
        $failed = 0;
        $chunkSize = max(1, (int) config('warehouse-inventory.chunk_size', 100));

        $query->orderBy('id')->chunkById($chunkSize, function ($prices) use ($provider, &$updated, &$failed) {
            $codes = $prices->pluck('stock_code')->filter()->values()->all();

            try {
                $stocks = $provider->fetchStocks($codes);

                if (!is_array($stocks)) {
                    throw new RuntimeException('Warehouse provider response must be an array.');
                }

                DB::transaction(function () use ($prices, $stocks, &$updated, &$failed) {
                    foreach ($prices as $price) {
                        if (!array_key_exists($price->stock_code, $stocks)) {
                            $price->forceFill([
                                'stock_sync_error' => 'کد کالا در پاسخ انبار پیدا نشد.',
                            ])->saveQuietly();
                            $failed++;
                            continue;
                        }

                        $stock = filter_var($stocks[$price->stock_code], FILTER_VALIDATE_INT);

                        if ($stock === false || $stock < 0) {
                            $price->forceFill([
                                'stock_sync_error' => 'موجودی نامعتبر از سرویس انبار دریافت شد.',
                            ])->saveQuietly();
                            $failed++;
                            continue;
                        }

                        $price->forceFill([
                            'stock' => $stock,
                            'stock_synced_at' => now(),
                            'stock_sync_error' => null,
                        ])->saveQuietly();

                        $updated++;
                    }
                });
            } catch (Throwable $exception) {
                foreach ($prices as $price) {
                    $price->forceFill([
                        'stock_sync_error' => mb_substr($exception->getMessage(), 0, 1000),
                    ])->saveQuietly();
                    $failed++;
                }
            }
        });

        return [
            'status' => 'completed',
            'updated' => $updated,
            'failed' => $failed,
            'message' => "{$updated} موجودی به‌روزرسانی شد و {$failed} مورد خطا داشت.",
        ];
    }
}
