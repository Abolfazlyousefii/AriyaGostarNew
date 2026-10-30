<?php

namespace App\Listeners\Wallet;

use App\Events\WalletAmountIncreased;
use App\Notifications\Wallet\WalletAmountIncreasedSms;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class SendWalletIncreasedSms
{
    /**
     * Handle the event.
     *
     * SMS is intentionally non-blocking: a provider failure must never break
     * a successful wallet credit or the business workflow that triggered it.
     */
    public function handle(WalletAmountIncreased $event): void
    {
        $amount = $event->wallet
            ->histories()
            ->where('type', 'deposit')
            ->latest()
            ->first();

        if (! $amount) {
            Log::warning('Wallet increased SMS skipped because no deposit history was found.', [
                'wallet_id' => $event->wallet->id ?? null,
                'user_id' => $event->wallet->user_id ?? null,
            ]);

            return;
        }

        try {
            Notification::send(
                $event->wallet->user,
                new WalletAmountIncreasedSms($event->wallet, $amount->amount)
            );
        } catch (Throwable $e) {
            Log::warning('Wallet increased SMS failed; wallet operation remains successful.', [
                'wallet_id' => $event->wallet->id ?? null,
                'user_id' => $event->wallet->user_id ?? null,
                'wallet_history_id' => $amount->id ?? null,
                'amount' => (int) ($amount->amount ?? 0),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
