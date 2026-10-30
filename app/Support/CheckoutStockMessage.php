<?php

namespace App\Support;

final class CheckoutStockMessage
{
    /**
     * Extract Inventory variant identifiers from validation messages without
     * exposing the internal identifiers to the customer.
     */
    public static function variantIds(iterable $messages): array
    {
        $ids = [];

        foreach ($messages as $message) {
            if (!is_scalar($message)) {
                continue;
            }

            preg_match_all('/\bvariant\s+(\d+)\b/i', (string) $message, $matches);

            foreach ($matches[1] ?? [] as $id) {
                $ids[(int) $id] = true;
            }
        }

        return array_keys($ids);
    }

    public static function customerMessages(array $labels): array
    {
        $labels = array_values(array_unique(array_filter(array_map(
            static fn ($label) => trim((string) $label),
            $labels
        ))));

        if ($labels === []) {
            return ['موجودی یکی از کالاها یا قطعات بسته کافی نیست. لطفاً تعداد را کاهش دهید.'];
        }

        return array_map(
            static fn (string $label) => 'موجودی کالای «' . $label . '» کافی نیست. لطفاً تعداد آن را کاهش دهید یا از سبد خرید حذف کنید.',
            $labels
        );
    }
}
