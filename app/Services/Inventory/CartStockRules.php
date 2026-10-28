<?php
namespace App\Services\Inventory;

use Carbon\CarbonImmutable;

final class CartStockRules
{
    public static function label(string $title, ?string $variant = null): string
    {
        $title = trim($title);
        $variant = trim((string) $variant);
        return $variant !== '' && $variant !== $title ? $title.' / '.$variant : $title;
    }

    public static function evaluate(array $line, int $available, bool $sellable = true, bool $mapped = true): array
    {
        $label = self::label($line['title'], $line['variant'] ?? null);
        $available = max(0, $available);
        $result = ['status' => true, 'code' => 'ok', 'message' => 'ok', 'available' => $available, 'label' => $label];
        if (!($line['physical'] ?? true)) return $result;
        $quantity = (int) $line['quantity'];
        $minimum = max(1, (int) ($line['minimum'] ?? 1));
        $maximum = $line['maximum'] ?? null;
        $code = $message = null;
        if (!$mapped) {
            $code = 'mapping_missing';
            $message = 'اطلاعات مدل «'.$label.'» با انبار هماهنگ نیست. لطفاً با پشتیبانی تماس بگیرید.';
        } elseif (!$sellable) {
            $code = 'not_sellable';
            $message = 'کالای «'.$label.'» فعلاً برای فروش فعال نیست. انتخاب شما در سبد حفظ شده است.';
        } elseif ($quantity < $minimum) {
            $code = 'purchase_minimum';
            $message = 'حداقل تعداد خرید «'.$label.'» برابر '.$minimum.' است.';
        } elseif ($maximum !== null && $quantity > (int) $maximum) {
            $code = 'purchase_limit';
            $message = 'حداکثر تعداد مجاز خرید «'.$label.'» برابر '.(int) $maximum.' است؛ لطفاً تعداد را اصلاح کنید.';
        } elseif ($quantity > $available) {
            $code = $available === 0 ? 'out_of_stock' : 'insufficient_stock';
            $message = $available === 0
                ? 'کالای «'.$label.'» اکنون موجود نیست. انتخاب شما در سبد حفظ شده است.'
                : 'از «'.$label.'» اکنون '.$available.' عدد قابل خرید است؛ تعداد انتخابی شما '.$quantity.' عدد است.';
        }
        return $code ? array_replace($result, ['status' => false, 'code' => $code, 'message' => $message]) : $result;
    }

    public static function isStale($timestamp, $now = null): bool
    {
        return !$timestamp || CarbonImmutable::parse($timestamp)->addHours(24)->lte($now ? CarbonImmutable::parse($now) : CarbonImmutable::now());
    }
}
