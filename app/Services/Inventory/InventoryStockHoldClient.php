<?php

namespace App\Services\Inventory;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Switchable transport. API remains available during staged rollout/rollback;
 * database transport uses the existing `inv` Laravel connection and no HTTP.
 */
class InventoryStockHoldClient
{
    public function reserve(Order $order, int $ttlMinutes): array
    {
        $lines = $order->items()->with('product')->get()
            ->filter(fn ($item) => !$item->product || $item->product->isPhysical())
            ->map(fn ($item) => [
                'variant_id' => (int) $item->price_id,
                'quantity' => (int) $item->quantity,
            ])->values()->all();
        if ($lines === []) return [];
        if ($this->direct()) {
            return app(DirectInventoryStockHold::class)->reserve($order, $lines,
                max(1, min(1440, $ttlMinutes)));
        }
        return $this->request('POST', '/api/external/stock-holds', [
            'site_order_id' => (int) $order->id,
            'expires_in_minutes' => max(1, min(1440, $ttlMinutes)),
            'lines' => $lines,
        ]);
    }

    public function confirm(Order $order): array
    {
        if ($this->direct()) return app(DirectInventoryStockHold::class)->confirm($order);
        return $this->request('POST', '/api/external/stock-holds/'.$order->id.'/confirm', [
            'hold_id' => (int) $order->inventory_stock_hold_id,
        ]);
    }

    public function release(Order $order): array
    {
        if ($this->direct()) return app(DirectInventoryStockHold::class)->release($order);
        return $this->request('POST', '/api/external/stock-holds/'.$order->id.'/release', [
            'hold_id' => (int) $order->inventory_stock_hold_id,
        ]);
    }

    public function status(Order $order): array
    {
        if ($this->direct()) return app(DirectInventoryStockHold::class)->status($order);
        return $this->request('GET', '/api/external/stock-holds/'.$order->id);
    }

    private function direct(): bool
    {
        return config('services.inventory_stock.transport', 'api') === 'database';
    }

    private function request(string $method, string $uri, array $payload = []): array
    {
        $base = rtrim((string) config('services.inventory_stock.base_url'), '/');
        $token = (string) config('services.inventory_stock.token');
        if ($base === '' || $token === '' || !str_starts_with($base, 'https://')) {
            throw new RuntimeException('Inventory stock API requires a configured HTTPS URL and service token.');
        }
        $http = Http::baseUrl($base)->acceptJson()->withToken($token)
            ->connectTimeout(3)->timeout(15);
        $response = $method === 'GET' ? $http->get($uri) : $http->post($uri, $payload);
        $response->throw();
        $data = $response->json();
        if (!is_array($data)) throw new RuntimeException('Invalid Inventory stock response.');
        return $data;
    }
}
