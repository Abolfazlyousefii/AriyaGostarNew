<?php
namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Listeners\OrderPaid\CreateInvoiceFromOrder;
use App\Models\Order;
use App\Services\Inventory\ManualOrderPaymentAmounts;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Throwable;

class CreditOrderController extends Controller
{
    public function approve(Order $order)
    {
        $this->authorize('orders.update');
        abort_unless(auth()->user() && auth()->user()->level === 'creator', 403);
        $id = (int) $order->id;

        try {
            DB::transaction(function () use ($id) {
                $current = Order::query()->whereKey($id)->lockForUpdate()->firstOrFail();
                if ($current->status !== 'unpaid' || $current->in_person
                    || $current->reserved() || $current->main_order_id
                    || $current->inventory_document_id
                    || in_array($current->inventory_sync_status, ['synced', 'credit_processing'], true)) {
                    throw ValidationException::withMessages([
                        'credit_order' => 'وضعیت سفارش برای ارسال اعتباری مجاز نیست یا ارسال قبلاً انجام شده است.',
                    ]);
                }
                if ($current->transactions()->where('status', 1)->exists()) {
                    throw ValidationException::withMessages([
                        'credit_order' => 'این سفارش تراکنش موفق دارد؛ ابتدا مغایرت مالی را بررسی کنید.',
                    ]);
                }
                if (Schema::hasTable('manual_order_payment_confirmations')
                    && DB::table('manual_order_payment_confirmations')->where('order_id', $id)->exists()) {
                    throw ValidationException::withMessages([
                        'credit_order' => 'پرداخت دستی قبلاً ثبت شده و این مسیر اعتباری مجاز نیست.',
                    ]);
                }
                $current->load('items');
                ManualOrderPaymentAmounts::forOrder($current);
                $current->forceFill([
                    'inventory_sync_status' => 'credit_processing',
                    'inventory_last_error' => null,
                ])->save();
            }, 3);

            Log::notice('Credit order submitted to ERP without a payment receipt.', [
                'order_id' => $id, 'admin_id' => auth()->id(),
            ]);
            app(CreateInvoiceFromOrder::class)->syncDeferredOrder(Order::findOrFail($id));

            $fresh = Order::query()->findOrFail($id);
            if ($fresh->inventory_sync_status !== 'synced' || ! $fresh->inventory_document_id) {
                $fresh->forceFill(['inventory_sync_status' => 'credit_failed'])->save();
                throw new \RuntimeException('ERP did not confirm invoice synchronization.');
            }

            $inv = DB::connection('inv');
            $invoice = $inv->table('invoices')->where('external_order_id', $id)
                ->first(['id', 'status']);
            $hasDebit = $invoice && $inv->table('customer_ledgers')
                ->where('reference_type', 'App\\Models\\Invoice')
                ->where('reference_id', $invoice->id)
                ->where('type', 'debit')->exists();
            $hasPayment = $invoice && $inv->table('invoice_payments')
                ->where('invoice_id', $invoice->id)->exists();

            if (! $invoice || (string) $invoice->status !== 'pending_collection'
                || ! $hasDebit || $hasPayment) {
                throw new \RuntimeException('ERP invoice requires manual reconciliation before retry.');
            }

            return redirect()->route('admin.orders.show', ['order' => $id])
                ->with('success', 'فاکتور به صف جمع‌آوری ERP ارسال شد؛ مشتری بدهکار و در انتظار تسویه است.');
        } catch (Throwable $e) {
            Log::error('Credit order ERP processing failed.', [
                'order_id' => $id, 'error' => $e->getMessage(),
            ]);

            // A normal exception must not leave the order permanently locked in
            // credit_processing. Preserve any ERP invoice/document reference.
            try {
                DB::transaction(function () use ($id, $e) {
                    $current = Order::query()->whereKey($id)->lockForUpdate()->first();
                    if ($current && $current->status === 'unpaid'
                        && $current->inventory_sync_status === 'credit_processing') {
                        $current->forceFill([
                            'inventory_sync_status' => 'credit_failed',
                            'inventory_last_error' => mb_substr($e->getMessage(), 0, 900),
                        ])->save();
                    }
                }, 3);
            } catch (Throwable $recoveryError) {
                Log::error('Could not release failed credit order for safe retry.', [
                    'order_id' => $id, 'error' => $recoveryError->getMessage(),
                ]);
            }

            return redirect()->route('admin.orders.show', ['order' => $id])
                ->withErrors(['credit_order' => 'ارسال اعتباری تکمیل نشد. وضعیت ERP را بررسی کنید.']);
        }
    }
}