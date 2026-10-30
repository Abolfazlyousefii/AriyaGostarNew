<?php

namespace App\Listeners\Wallet;

use App\Events\WalletAmountDecreased;
use App\Notifications\Wallet\WalletAmountDecreasedSms;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class SendWalletDecreasedSms
{
    /**
     * Handle the event.
     *
     * SMS is intentionally non-blocking: a provider failure must never break
     * a successful wallet payment or stop the following OrderPaid workflow.
     */
    public function handle(WalletAmountDecreased $event): void
    {
        $amount = $event->wallet
            ->histories()
            ->where('type', 'withdraw')
            ->latest()
            ->first();

        if (! $amount) {
            Log::warning('Wallet decreased SMS skipped because no withdraw history was found.', [
                'wallet_id' => $event->wallet->id ?? null,
                'user_id' => $event->wallet->user_id ?? null,
            ]);

            return;
        }

        try {
            Notification::send(
                $event->wallet->user,
                new WalletAmountDecreasedSms($event->wallet, $amount->amount)
            );
        } catch (Throwable $e) {
            Log::warning('Wallet decreased SMS failed; wallet operation remains successful.', [
                'wallet_id' => $event->wallet->id ?? null,
                'user_id' => $event->wallet->user_id ?? null,
                'wallet_history_id' => $amount->id ?? null,
                'amount' => (int) ($amount->amount ?? 0),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
