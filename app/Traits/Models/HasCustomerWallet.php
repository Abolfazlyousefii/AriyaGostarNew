<?php

namespace App\Traits\Models;

use App\Models\Wallet;
use App\Models\WalletHistory;
use Illuminate\Support\Facades\DB;
use RuntimeException;

trait HasCustomerWallet
{
    /**
     * کیف پول واقعی کاربر در خود Site.
     */
    public function wallet(): Wallet
    {
        return Wallet::firstOrCreate(
            [
                'user_id' => $this->getKey(),
            ],
            [
                'balance'   => 0,
                'is_active' => true,
            ]
        );
    }

    /**
     * افزایش اعتبار کیف پول.
     */
    public function deposit($amount, array $meta = [], bool $confirmed = true)
    {
        $amount = (int) $amount;

        if ($amount <= 0) {
            throw new RuntimeException('مبلغ واریز کیف پول باید بیشتر از صفر باشد.');
        }

        return DB::transaction(function () use ($amount, $meta, $confirmed) {
            $wallet = $this->wallet();

            $history = WalletHistory::create([
                'wallet_id'   => $wallet->id,
                'order_id'    => $meta['order_id'] ?? null,
                'shipping_cost' => (bool) ($meta['shipping_cost'] ?? false),
                'type'        => 'deposit',
                'source'      => $meta['source'] ?? 'user',
                'status'      => $confirmed ? 'success' : 'fail',
                'amount'      => $amount,
                'description' => $meta['description'] ?? null,
            ]);

            $wallet->refereshBalance();

            return $history;
        });
    }

    /**
     * برداشت از اعتبار کیف پول.
     */
    public function withdraw($amount, array $meta = [], bool $confirmed = true)
    {
        $amount = (int) $amount;

        if ($amount <= 0) {
            throw new RuntimeException('مبلغ برداشت کیف پول باید بیشتر از صفر باشد.');
        }

        return DB::transaction(function () use ($amount, $meta, $confirmed) {
            $wallet = $this->wallet();

            if ($confirmed && $wallet->balance() < $amount) {
                throw new RuntimeException('موجودی کیف پول کافی نیست.');
            }

            $history = WalletHistory::create([
                'wallet_id'   => $wallet->id,
                'order_id'    => $meta['order_id'] ?? null,
                'shipping_cost' => (bool) ($meta['shipping_cost'] ?? false),
                'type'        => 'withdraw',
                'source'      => $meta['source'] ?? 'user',
                'status'      => $confirmed ? 'success' : 'fail',
                'amount'      => $amount,
                'description' => $meta['description'] ?? null,
            ]);

            $wallet->refereshBalance();

            return $history;
        });
    }

    /**
     * موجودی قابل استفاده کیف پول.
     */
    public function balance(): int
    {
        return (int) $this->wallet()->balance();
    }
}