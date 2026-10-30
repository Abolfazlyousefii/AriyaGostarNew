<?php

declare(strict_types=1);

namespace App\Services;

/**
 * TASK01_STAGE03D
 *
 * Normalizes phone-model names and checks model compatibility.
 * Does not query prices, inventory or carts.
 * Does not authorize a variant for checkout.
 */
final class QuickWholesalePhoneModelMatcher
{
    public static function normalize(string $text): string
    {
        $text = strtolower(trim($text));

        $text = strtr($text, [
            '۰' => '0', '۱' => '1', '۲' => '2',
            '۳' => '3', '۴' => '4', '۵' => '5',
            '۶' => '6', '۷' => '7', '۸' => '8',
            '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2',
            '٣' => '3', '٤' => '4', '٥' => '5',
            '٦' => '6', '٧' => '7', '٨' => '8',
            '٩' => '9',
            'ي' => 'ی', 'ك' => 'ک',
        ]);

        $text = preg_replace(
            '/[\x{200C}\x{200E}\x{200F}\x{FEFF}]/u',
            ' ',
            $text
        );

        $text = preg_replace(
            '/(?:آ|ا)ی\s*فون/u',
            'iphone',
            $text
        );

        $text = preg_replace(
            '/پرو\s*مکس/u',
            'pro max',
            $text
        );

        $text = str_replace(
            ['پرو', 'مینی', 'پلاس'],
            ['pro', 'mini', 'plus'],
            $text
        );

        $text = preg_replace('/\s*\/\s*/u', '/', $text);

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    public static function selectedModelKey(string $query): ?string
    {
        $query = self::normalize($query);

        if (!preg_match(
            '/^iphone\s*(\d{1,2})(?:\s*(pro\s*max|pro|mini|plus))?$/u',
            $query,
            $matches
        )) {
            return null;
        }

        $suffix = isset($matches[2]) && $matches[2] !== ''
            ? preg_replace('/\s+/u', '-', $matches[2])
            : 'base';

        return 'iphone:' . (int) $matches[1] . ':' . $suffix;
    }

    public static function inventoryModelMatches(
        string $query,
        string $modelName
    ): bool {
        $selected = self::selectedModelKey($query);

        if ($selected === null) {
            return false;
        }

        $exact = self::selectedModelKey($modelName);

        if ($exact !== null) {
            return $selected === $exact;
        }

        if (!str_ends_with($selected, ':base')) {
            return false;
        }

        $inventoryName = self::normalize($modelName);

        if (!preg_match(
            '/^iphone\s*(\d{1,2}(?:\/\d{1,2})+)$/u',
            $inventoryName,
            $matches
        )) {
            return false;
        }

        $requestedNumber = explode(':', $selected)[1];

        $compatibleNumbers = array_map(
            static fn (string $number): string =>
                (string) (int) $number,
            explode('/', $matches[1])
        );

        return in_array(
            $requestedNumber,
            $compatibleNumbers,
            true
        );
    }
}
