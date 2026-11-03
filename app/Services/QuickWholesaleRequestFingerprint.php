<?php

declare(strict_types=1);

namespace App\Services;

/** Canonicalizes a purchase request so re-ordered lines are the same request. */
final class QuickWholesaleRequestFingerprint
{
    public static function decision(?string $storedHash, string $incomingHash): string
    {
        if ($storedHash === null) return 'new';
        return hash_equals($storedHash, $incomingHash) ? 'replay' : 'conflict';
    }

    public static function hash(string $model, array $items): string
    {
        $lines = [];
        foreach ($items as $item) {
            $lines[] = [(int) $item['price_id'], (int) $item['quantity']];
        }
        usort($lines, static fn (array $a, array $b): int => $a[0] <=> $b[0]);

        return hash('sha256', json_encode([
            'model' => $model,
            'items' => $lines,
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
    }
}
