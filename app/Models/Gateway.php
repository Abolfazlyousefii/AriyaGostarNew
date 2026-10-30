<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gateway extends Model
{
    protected $guarded = ['id'];

    public function configs()
    {
        return $this->hasMany(GatewayConfig::class, 'gateway_id');
    }

    public function scopeActive($query)
    {
        $supported_gateways = array_keys(config('general.supported_gateways'));

        // Never expose fake checkout payments on a non-local HTTP hostname
        // or unless the operator has explicitly enabled test mode in .env.
        if (! static::localTestPaymentAllowed()) {
            $supported_gateways = array_values(array_diff($supported_gateways, ['local']));
        }

        return $query->whereIn('key', $supported_gateways)
            ->where('is_active', true);
    }

    public function config($key)
    {
        $config = $this->configs()->where('gateway_configs.key', $key)->first();

        return $config ? $config->value : null;
    }

    public static function localTestPaymentAllowed(): bool
    {
        return (bool) config('general.local_payment_test_enabled', false)
            && in_array(request()->getHost(), ['siteariya.local', 'localhost', '127.0.0.1'], true);
    }

    /**
     * The payment callback must never use the fake driver to verify an
     * unrelated real-bank transaction, even if someone changes the URL.
     */
    public static function assertLocalCallbackAllowed(string $gateway, $transaction): void
    {
        if ($gateway !== 'local') {
            return;
        }

        abort_unless(
            static::localTestPaymentAllowed()
                && (int) $transaction->gateway_id > 0
                && (int) $transaction->gateway_id === (int) static::query()->where('key', 'local')->value('id'),
            404
        );
    }

}
