<?php

namespace App\Support;

/** Pure presentation normalization; keeps the bundle itself as the only order line. */
final class BundleContentsPresenter
{
    /** @return array<int, array{name: string, variant: string, quantity: int}> */
    public static function rows(array|string|null $snapshot, array|string|null $currentContents): array
    {
        // An as-sold snapshot takes priority, even when it is empty.
        $raw = $snapshot !== null ? $snapshot : $currentContents;
        if (is_string($raw)) {
            $raw = json_decode($raw, true);
        }
        if (!is_array($raw)) return [];

        $rows = [];
        foreach ($raw as $part) {
            if (!is_array($part)) continue;
            $quantity = (int) ($part['quantity_per_bundle'] ?? $part['quantity'] ?? 0);
            if ($quantity < 1) continue;
            $name = trim((string) ($part['product_name'] ?? $part['name'] ?? ''));
            $rows[] = [
                'name' => $name !== '' ? $name : 'کالا',
                'variant' => trim((string) ($part['variant_name'] ?? $part['variant'] ?? '')),
                'quantity' => $quantity,
            ];
        }
        return $rows;
    }
}
