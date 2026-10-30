<?php

namespace App\Http\Controllers\Back;

use App\Events\OrderPaid;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Throwable;

/** Explicitly verified bank transfer payment, kept separate from gateway transactions. */
class ManualOrderPaymentController extends Controller
{
    public function confirm(Request $request, Order $order)
    {
        $this->authorize('orders.update');

        $validated = $request->validate([
            'receipt_reference' => ['required', 'string', 'min:3', 'max:150'],
            'payment_note' => ['nullable', 'string', 'max:500'],
            'amount_confirm' => ['required', 'integer', 'min:1'],
            'funds_received' => ['accepted'],
        ]);

        if (! Schema::hasTable('manual_order_payment_confirmations')) {
            abort(503, 'جدول ثبت تأیید دستی پرداخت هنوز نصب نشده است.');
        }

        $orderId = (int) $order->id;
        $actorId = (int) auth()->id();

        DB::transaction(function () use ($orderId, $actorId, $validated) {
            $current = Order::query()->whereKey($orderId)->lockForUpdate()->firstOrFail();

            if ($current->status !== 'unpaid') {
                throw ValidationException::withMessages([
                    'funds_received' => 'این سفارش دیگر پرداخت‌نشده نیست. عملیات متوقف شد.',
                ]);
            }

            if ($current->in_person || $current->reserved() || $current->main_order_id) {
                throw ValidationException::withMessages([
                    'funds_received' => 'نوع سفارش نیاز به بررسی دستی جداگانه دارد.',
                ]);
            }

            if ((int) $current->price <= 0 || (int) $validated['amount_confirm'] !== (int) $current->price) {
                throw ValidationException::withMessages([
                    'amount_confirm' => 'مبلغ تأییدشده باید دقیقاً برابر مبلغ سفارش باشد.',
                ]);
            }

            if (DB::table('manual_order_payment_confirmations')->where('order_id', $orderId)->exists()) {
                throw ValidationException::withMessages([
                    'funds_received' => 'تأیید دستی این سفارش قبلاً ثبت شده است.',
                ]);
            }

            if ($current->transactions()->where('status', 1)->exists()) {
                throw ValidationException::withMessages([
                    'funds_received' => 'برای این سفارش تراکنش موفق ثبت شده است؛ ابتدا مغایرت پرداخت را بررسی کنید.',
                ]);
            }

            if (! empty($current->inventory_document_id) || $current->inventory_sync_status === 'synced') {
                throw ValidationException::withMessages([
                    'funds_received' => 'سفارش از قبل سند انبارداری دارد؛ عملیات تکراری مجاز نیست.',
                ]);
            }

            $items = $current->items()->get(['product_id', 'price_id', 'quantity']);
            if ($items->isEmpty() || $items->contains(fn ($i) => (int) $i->quantity <= 0)) {
                throw ValidationException::withMessages([
                    'funds_received' => 'اقلام سفارش ناقص هستند.',
                ]);
            }

            // Do not mark the Site order paid if its existing line items do not
            // reconcile with the confirmed amount, including all discounts.
            try {
                $current->load('items');
                \App\Services\Inventory\ManualOrderPaymentAmounts::forOrder($current);
            } catch (\RuntimeException $e) {
                throw ValidationException::withMessages([
                    'amount_confirm' => 'مبلغ اقلام، تخفیف یا هزینه ارسال با مبلغ سفارش سازگار نیست؛ تأیید متوقف شد.',
                ]);
            }

            // Site already uses the inv connection in the regular OrderPaid listener.
            // Check its stock/mapping before recording irreversible manual confirmation.
            try {
                $inv = DB::connection('inv');
                $previousInvoice = $inv->table('invoices')->where('external_order_id', $orderId)->exists();
                if ($previousInvoice) {
                    throw new \RuntimeException('این سفارش قبلاً در انبارداری فاکتور دارد.');
                }
                $centralId = (int) $inv->table('warehouses')
                    ->where('type', 'central')->orderBy('id')->value('id');
                if ($centralId <= 0) {
                    throw new \RuntimeException('انبار مرکزی مشخص نیست.');
                }
                if (! $inv->table('users')->where('id', 108)->exists()
                    || ! $inv->table('users')->where('username', 'ariya_site')->where('is_seller', true)->exists()) {
                    throw new \RuntimeException('حساب سیستمی ارسال سفارش در انبارداری آماده نیست.');
                }
                $grouped = $items->groupBy(fn ($i) => (int) $i->product_id . ':' . (int) $i->price_id);
                foreach ($grouped as $group) {
                    $item = $group->first();
                    $productId = (int) $item->product_id;
                    $variantId = (int) $item->price_id;
                    $quantity = (int) $group->sum('quantity');
                    if ($variantId <= 0 || ! $inv->table('product_variants')
                        ->where('id', $variantId)->where('product_id', $productId)->exists()) {
                        throw new \RuntimeException("تنوع انبارداری محصول {$productId} معتبر نیست.");
                    }
                    $stock = $inv->table('warehouse_stocks')->where('warehouse_id', $centralId)
                        ->where('product_id', $productId)->where('product_variant_id', $variantId)
                        ->value('quantity');
                    if ($stock === null || (int) $stock < $quantity) {
                        throw new \RuntimeException("موجودی انبار مرکزی برای محصول {$productId} کافی نیست.");
                    }
                }
            } catch (Throwable $e) {
                throw ValidationException::withMessages([
                    'funds_received' => 'ارسال ایمن به انبارداری ممکن نیست: ' . $e->getMessage(),
                ]);
            }

            DB::table('manual_order_payment_confirmations')->insert([
                'order_id' => $orderId,
                'confirmed_by' => $actorId,
                'amount_toman' => (int) $current->price,
                'receipt_reference' => trim($validated['receipt_reference']),
                'payment_note' => trim((string) ($validated['payment_note'] ?? '')),
                'confirmed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $current->status = 'paid';
            $current->inventory_sync_status = 'pending';
            $current->inventory_last_error = null;
            $current->save();

            Log::notice('Manual payment confirmed by site administrator.', [
                'order_id' => $orderId,
                'admin_user_id' => $actorId,
                'amount_toman' => (int) $current->price,
                'receipt_reference' => trim($validated['receipt_reference']),
            ]);

            DB::afterCommit(function () use ($orderId) {
                try {
                    $fresh = Order::query()->find($orderId);
                    if ($fresh && $fresh->isPaid()) {
                        event(new OrderPaid($fresh));
                    }
                } catch (Throwable $e) {
                    Log::error('Manual-paid order event dispatch failed.', [
                        'order_id' => $orderId, 'error' => $e->getMessage(),
                    ]);
                    Order::query()->whereKey($orderId)->update([
                        'inventory_sync_status' => 'failed',
                        'inventory_last_error' => 'Manual payment event dispatch: ' . $e->getMessage(),
                    ]);
                }
            });
        }, 3);

        return redirect()->route('admin.orders.show', ['order' => $orderId])
            ->with('success', 'پرداخت دستی تأیید شد؛ ارسال به صف انبارداری درخواست شد. وضعیت همگام‌سازی را بررسی کنید.');
    }
}
