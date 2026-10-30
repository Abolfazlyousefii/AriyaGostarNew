<?php

namespace App\Services\Bundles;

/** Pure random stock-bound preview allocator. NEVER reserves, sells or writes stock. */
final class PreviewRandomComposer
{
    /**
     * @param array<int, array{price_id:int, stock:int, minimum:int, required:bool}> $variants
     * @return array<int,int>|null price_id => qty, or null if no valid composition found
     */
    public static function compose(array $variants, int $target): ?array
    {
        if ($target <= 0 || !$variants) return null;
        $eligible = [];
        $mandatory = [];
        $mandatoryTotal = 0;
        $maximumTotal = 0;
        foreach ($variants as $row) {
            $id = (int) $row['price_id'];
            $stock = max(0, min($target, (int) $row['stock']));
            $minimum = max(1, (int) $row['minimum']);
            $required = (bool) $row['required'];
            if ($required && $stock < $minimum) return null;
            if ($stock < $minimum) continue;
            $eligible[$id] = ['min' => $minimum, 'stock' => $stock, 'required' => $required];
            $maximumTotal += $stock;
            if ($required) {
                $mandatory[$id] = $minimum;
                $mandatoryTotal += $minimum;
            }
        }
        if ($mandatoryTotal > $target || $maximumTotal < $target) return null;
        if ($mandatoryTotal === $target) return $mandatory;
        $ids = array_keys($eligible);
        for ($attempt = 0; $attempt < 12; $attempt++) {
            $chosen = $mandatory;
            $remaining = $target - $mandatoryTotal;
            shuffle($ids);
            // Seed multiple distinct models instead of exhausting just one item.
            $seedCount = min(count($ids), max(4, min(22, (int) ceil($target / 8))));
            $seeded = 0;
            foreach ($ids as $id) {
                if ($seeded >= $seedCount || $remaining <= 0) break;
                if (isset($chosen[$id])) continue;
                $minimum = $eligible[$id]['min'];
                if ($remaining < $minimum) continue;
                $chosen[$id] = $minimum;
                $remaining -= $minimum;
                $seeded++;
            }
            for ($pass = 0; $pass < 1500 && $remaining > 0; $pass++) {
                $options = [];
                foreach ($ids as $id) {
                    $current = $chosen[$id] ?? 0;
                    $free = $eligible[$id]['stock'] - $current;
                    if ($free <= 0) continue;
                    $need = $current ? 1 : $eligible[$id]['min'];
                    if ($need <= $remaining && $need <= $free) $options[] = $id;
                }
                if (!$options) break;
                $id = $options[random_int(0, count($options) - 1)];
                $current = $chosen[$id] ?? 0;
                $minAdd = $current ? 1 : $eligible[$id]['min'];
                $maxAdd = min($remaining, $eligible[$id]['stock'] - $current);
                // Bounded chunks: more variety, sublinear work for large bundles.
                $suggestedChunk = max($minAdd, min($maxAdd, max(1, (int) ceil($remaining / 10))));
                $add = random_int($minAdd, $suggestedChunk);
                $chosen[$id] = $current + $add;
                $remaining -= $add;
            }
            if ($remaining === 0) return array_filter($chosen, fn ($q) => $q > 0);
        }
        return null;
    }
}
