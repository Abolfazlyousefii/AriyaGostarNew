<?php

namespace App\Providers;

use App\Listeners\OrderPaid\CreateInvoiceFromOrder;
use App\Listeners\User\SyncUserToINV;
use App\Listeners\UserLogin\GetCart;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider {
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Login::class => [
            GetCart::class,
            SyncUserToINV::class,
        ],

        Registered::class => [
//            'App\Listeners\User\Registered',
//            'App\Listeners\User\AddGiftCredit',
//            'App\Listeners\User\CreateReferralDiscounts',
            SyncUserToINV::class,
        ],

        'App\Events\OrderCreated' => [
            'App\Listeners\OrderCreated\SaveAddress',
            'App\Listeners\OrderCreated\ChangePrices',
        ],

        'App\Events\OrderPaid' => [
            'App\Listeners\OrderPaid\CreateInvoiceFromOrder',
            'App\Listeners\OrderPaid',
        ],

        'App\Events\WalletAmountIncreased' => [
            'App\Listeners\Wallet\SendWalletIncreasedSms',
        ],

        'App\Events\WalletAmountDecreased' => [
            'App\Listeners\Wallet\SendWalletDecreasedSms',
        ],

        'App\Events\ProductPricesChanged' => [
            'App\Listeners\Product\CreatePricesChange',
        ],

        'App\Events\ContactCreated' => [],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot() {
        //
    }
}
