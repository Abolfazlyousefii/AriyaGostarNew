<?php

namespace App\Services\Inventory;

use RuntimeException;

/** Strict, integer-only Toman -> Rial projection for confirmed manual transfers. */
final class ManualOrderPaymentAmounts
{
    public static function forOrder(object $order): array
    {
        $gross = 0;
        $lineDiscount = 0;
        $count = 0;
        foreach ($order->items as $item) {
            $quantity = self::positiveInteger($item->quantity, 'quantity');
            $sale = self::positiveInteger($item->price, 'site sale price');
            $regular = self::positiveInteger($item->real_price ?? $item->price, 'regular price');
            if ($regular < $sale) {
                throw new RuntimeException('Manual bank transfer blocked: order has an item with regular price below sale price.');
            }
            $gross = self::safeAdd($gross, self::safeMultiply($regular, $quantity));
            $lineDiscount = self::safeAdd($lineDiscount, self::safeMultiply($regular - $sale, $quantity));
            $count++;
        }
        if ($count === 0) {
            throw new RuntimeException('Manual bank transfer blocked: order has no items.');
        }

        $storedDiscount = self::nonnegativeInteger($order->discount_amount ?? 0, 'order discount');
        $shipping = self::nonnegativeInteger($order->shipping_cost ?? 0, 'shipping');
        $storedTotal = self::positiveInteger($order->price, 'order total');
        if ($storedDiscount < $lineDiscount || $storedDiscount > $gross) {
            throw new RuntimeException('Manual bank transfer blocked: inconsistent product/invoice discounts.');
        }
        $documentDiscount = $storedDiscount - $lineDiscount;
        $calculatedTotal = self::safeAdd($gross - $storedDiscount, $shipping);
        if ($calculatedTotal !== $storedTotal) {
            throw new RuntimeException('Manual bank transfer blocked: item-based total does not match stored order price.');
        }

        return [
            'total_rial' => self::safeMultiply($storedTotal, 10),
            'shipping_rial' => self::safeMultiply($shipping, 10),
            'total_discount_rial' => self::safeMultiply($storedDiscount, 10),
            'line_discount_rial' => self::safeMultiply($lineDiscount, 10),
            'document_discount_rial' => self::safeMultiply($documentDiscount, 10),
        ];
    }

    private static function positiveInteger(mixed $value, string $label): int
    {
        $number = self::nonnegativeInteger($value, $label);
        if ($number <= 0) {
            throw new RuntimeException('Manual bank transfer blocked: invalid ' . $label . '.');
        }
        return $number;
    }

    private static function nonnegativeInteger(mixed $value, string $label): int
    {
        if (!is_numeric($value) || !preg_match('/^\d+$/D', (string) $value)) {
            throw new RuntimeException('Manual bank transfer blocked: fractional or invalid ' . $label . '.');
        }
        $number = filter_var((string) $value, FILTER_VALIDATE_INT);
        if ($number === false || $number < 0) {
            throw new RuntimeException('Manual bank transfer blocked: out-of-range ' . $label . '.');
        }
        return $number;
    }

    private static function safeMultiply(int $a, int $b): int
    {
        if ($a !== 0 && $b > intdiv(PHP_INT_MAX, $a)) {
            throw new RuntimeException('Manual bank transfer blocked: money amount overflow.');
        }
        return $a * $b;
    }

    private static function safeAdd(int $a, int $b): int
    {
        if ($b > PHP_INT_MAX - $a) {
            throw new RuntimeException('Manual bank transfer blocked: money amount overflow.');
        }
        return $a + $b;
    }
}
