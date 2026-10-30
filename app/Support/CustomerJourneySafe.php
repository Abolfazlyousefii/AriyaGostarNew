<?php

namespace App\Support;

final class CustomerJourneySafe
{
    public static function path($raw): string
    {
        $path = (string) (parse_url((string) $raw, PHP_URL_PATH) ?: '/');
        $path = preg_replace('/[\x00-\x1f\x7f]/', '', $path);
        $parts = explode('/', $path);
        $parts = array_map(function ($part) {
            if (preg_match('/^[a-f0-9]{24,}$/i', $part) || preg_match('/^[A-Za-z0-9_\-]{48,}$/', $part)) {
                return '{id}';
            }
            // Avoid keeping emails, phone numbers and sensitive query-like segments.
            if (preg_match('/@|^\+?\d{10,}$/', $part)) {
                return '{redacted}';
            }
            return self::safeCut($part, 80);
        }, $parts);
        $safe = self::safeCut(implode('/', $parts), 200);
        return '/' . ltrim($safe, '/');
    }

    private static function safeCut(string $value, int $chars): string
    {
        if (preg_match('//u', $value)) {
            $parts = preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY);
            return implode('', array_slice($parts ?: [], 0, $chars));
        }
        return substr($value, 0, $chars);
    }

    public static function route($name): ?string
    {
        if (!is_string($name) || !preg_match('/^[a-zA-Z0-9._-]{1,120}$/D', $name)) {
            return null;
        }
        return $name;
    }

    public static function event($value): ?string
    {
        $allowed = [
            'page_ready', 'product_click', 'variant_popup_open', 'cart_click',
            'checkout_click', 'payment_click', 'form_submit', 'js_error', 'promise_error',
            'client_navigation',
        ];
        return in_array($value, $allowed, true) ? $value : null;
    }

    public static function errorClass($value): ?string
    {
        if (!is_string($value)) return null;
        return preg_match('/^[a-zA-Z][a-zA-Z0-9_]{0,79}$/D', $value) ? $value : null;
    }

    public static function sourceFile($value): ?string
    {
        if (!is_string($value)) return null;
        $path = parse_url($value, PHP_URL_PATH);
        if (!is_string($path)) return null;
        $file = basename($path);
        return preg_match('/^[a-zA-Z0-9_.-]{1,100}$/D', $file) ? $file : null;
    }
}
