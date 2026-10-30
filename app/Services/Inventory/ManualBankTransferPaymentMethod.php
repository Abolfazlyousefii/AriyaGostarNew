<?php

namespace App\Services\Inventory;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Audit-backed manual payment reference; null means normal online payment. */
final class ManualBankTransferPaymentMethod
{
    public static function confirmationForOrderId(int $orderId): ?object
    {
        if ($orderId <= 0 || ! Schema::hasTable('manual_order_payment_confirmations')) {
            return null;
        }

        return DB::table('manual_order_payment_confirmations')
            ->where('order_id', $orderId)
            ->first(['order_id', 'confirmed_by', 'receipt_reference', 'payment_note', 'confirmed_at']);
    }

    public static function forOrderId(int $orderId): string
    {
        return self::confirmationForOrderId($orderId) !== null ? 'bank_transfer' : 'online';
    }
}
