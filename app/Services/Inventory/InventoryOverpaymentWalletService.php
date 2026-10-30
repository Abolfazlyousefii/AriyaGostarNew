<?php

namespace App\Services\Inventory;

use App\Models\Inventory\Customer;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoryOverpaymentWalletService
{
    /**
     * Inventory customer_ledgers are the single source of truth for customer balance.
     * Invoice webhooks no longer copy overpayment into the legacy Site wallets table.
     * This method only validates the Site <-> Inventory mapping and returns the live
     * account state; the actual financial effect has already been recorded in Inventory.
     */
    public function syncForOrder(int $orderId): array
    {
        $order = Order::query()->findOrFail($orderId);

        $invoice = DB::connection('inv')
            ->table('invoices')
            ->where('external_order_id', $orderId)
            ->first();

        if (! $invoice) {
            throw new RuntimeException(
                "Inventory invoice for order #{$orderId} was not found."
            );
        }

        $customer = Customer::query()
            ->withBalance()
            ->find((int) $invoice->customer_id);

        if (! $customer) {
            throw new RuntimeException(
                "Inventory customer for invoice #{$invoice->id} was not found."
            );
        }

        if ((int) $customer->site_customer_id !== (int) $order->user_id) {
            throw new RuntimeException(
                "Site user mapping does not match Inventory customer for order #{$orderId}."
            );
        }

        $paidTotal = (int) DB::connection('inv')
            ->table('invoice_payments')
            ->where('invoice_id', $invoice->id)
            ->sum('amount');

        return [
            'order_id' => (int) $order->id,
            'user_id' => (int) $order->user_id,
            'invoice_id' => (int) $invoice->id,
            'invoice_uuid' => $invoice->uuid ?? null,
            'paid_total' => $paidTotal,
            'invoice_total' => (int) $invoice->total,
            'customer_id' => (int) $customer->id,
            'account_balance' => (int) $customer->balance,
            'wallet_balance' => (int) $customer->wallet_balance,
            'debt' => (int) $customer->debt,
            'usable_credit' => (int) $customer->usable_credit,
            'source' => 'inventory_customer_ledgers',
        ];
    }
}
