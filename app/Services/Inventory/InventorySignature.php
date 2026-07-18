<?php

namespace App\Services\Inventory;

class InventorySignature
{
    public static function sign(string $timestamp, string $rawJsonBody, ?string $secret = null): string
    {
        return hash_hmac('sha256', $timestamp . '.' . $rawJsonBody, $secret ?? (string) config('inventory.shared_secret'));
    }

    public static function valid(string $timestamp, string $rawJsonBody, string $signature): bool
    {
        if (!config('inventory.shared_secret')) return false;
        if (abs(now()->timestamp - strtotime($timestamp)) > (int) config('inventory.timestamp_tolerance', 300)) return false;
        return hash_equals(self::sign($timestamp, $rawJsonBody), $signature);
    }
}
