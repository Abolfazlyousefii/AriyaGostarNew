<?php

namespace Themes\DefaultTheme\src\Controllers;

use App\Events\OrderCreated;
use App\Events\OrderPaid;
use App\Http\Controllers\Controller;
use App\Jobs\CancelOrder;
use App\Models\Carrier;
use App\Models\Cart;
use App\Models\City;
use App\Models\Gateway;
use App\Models\Inventory\Customer;
use App\Models\Inventory\CustomerLedger;
use App\Models\Order;
use App\Models\Province;
use App\Models\Transaction;
use App\Services\Inventory\InventoryOrderRevisionService;
use App\Services\Inventory\InventoryStockHoldClient;
use App\Models\User;
use App\Notifications\Order\OrderPaid as NotificationsOrderPaid;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Shetabit\Multipay\Invoice;
use Shetabit\Payment\Facade\Payment;
use Themes\DefaultTheme\src\Requests\StoreOrderRequest;
use Themes\DefaultTheme\src\Requests\UpdateOrderRequest;

class OrderController extends Controller
{
    public function index()
    {
        $orders = auth()->user()->orders()->latest()->paginate(10);

        return view('front::user.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        if ($order->user_id != auth()->user()->id) {
            abort(404);
        }

        $order->loadMissing(['items.product', 'items.get_price.product', 'items.get_price.get_attributes', 'transactions']);

        $gateways = Gateway::active()->get();
        $customer = $this->getCustomer();
        $balance  = $customer->wallet_balance; // مثبت=اعتبار، منفی=بدهی

        // Inventory invoice is the source of truth for paid/synced orders.
        // It includes current items plus the complete add/remove revision history.
        $inventoryRevision = app(InventoryOrderRevisionService::class)->forOrder($order);
        // Render the same modern invoice partial immediately after checkout.
        // Inventory replaces this site-only snapshot once the actual invoice exists.
        $orderInvoiceDisplay = $inventoryRevision
            ?? app(\App\Services\Inventory\SiteOrderInvoicePreviewService::class)->forOrder($order);

        return view('front::user.orders.show', compact(
            'order',
            'gateways',
            'customer',
            'balance',
            'inventoryRevision',
            'orderInvoiceDisplay'
        ));
    }

    public function store(StoreOrderRequest $request)
    {
        $user = auth()->user();

        $cart = $user->cart;

        if (!$cart || !$cart->products->count() || !check_cart_quantity()) {
            return redirect()->route('front.cart');
        }

        // An automatic kit must never use legacy Site-only stock decrement; it has no finished SKU warehouse stock.
        if (!config('services.inventory_stock.enabled', false) && $cart->products->contains(fn ($product) =>
            (bool) ($product->is_bundle ?? false) && ($product->bundle_stock_mode ?? 'physical') === 'automatic')) {
            return redirect()->route('front.checkout')->withErrors([
                'stock' => 'خرید بسته خودکار موقتاً غیرفعال است؛ اتصال رزرو موجودی انبار باید فعال شود.',
            ]);
        }

        // Legacy availability check is retained when the new atomic stock-hold flow is disabled.
        // With the hold flow enabled, Inventory validates/locks ALL physical lines atomically.
        if (!config('services.inventory_stock.enabled', false)) {
            $inventoryError = $this->inventoryStockError($cart->products
                ->filter(fn ($product) => $product->isPhysical())
                ->map(fn ($product) => [
                    'product_id' => $product->id,
                    'variant_id' => $product->pivot->price_id,
                    'quantity' => $product->pivot->quantity,
                    'title' => $product->title,
                ])->all());
            if ($inventoryError !== null) {
                return redirect()->route('front.checkout')->withErrors(['stock' => $inventoryError]);
            }
        }

        if ($request->reserve != 'reserve' && !$cart->carrier_id && $cart->hasPhysicalProduct()) {
            return redirect()->route('front.checkout')->withErrors(['carrier_id' => 'نحوه ارسال انتخاب نشده است']);
        }

        if (!check_cart_discount()['status']) {
            return redirect()->route('front.checkout');
        }

        $gateway  = Gateway::where('key', $request->gateway)->first();
        $data     = $request->validated();

        $data['shipping_cost']      = $cart->shippingCostAmount();
        $data['price']              = $cart->finalPrice();
        $data['status']             = 'unpaid';
        $data['discount_amount']    = $cart->totalDiscount();
        $data['discount_id']        = $cart->discount_id;
        $data['weight']             = $cart->weight();
        $data['user_id']            = $user->id;

        if (option('reserve_orders_enabled')) {
            $data['reserve'] = $cart->reserved();
        }

        if ($gateway) {
            $data['gateway_id']         = $gateway->id;
        }

        if ($cart->hasPhysicalProduct() && !$cart->reserved()) {
            $carrier_result = Cart::canUseCarrier($request->carrier_id, $cart->weight(), $cart->city_id);

            if (!$carrier_result['status']) {
                return redirect()->back()->withInput()->withErrors([
                    'carrier_id' => $carrier_result['message'],
                ]);
            }
        }

        $order = Order::create($data);

        //add cart products to order
        foreach ($cart->products as $product) {

            $price = $product->prices()->find($product->pivot->price_id);

            if ($price) {
                $order->items()->create([
                    'product_id'      => $product->id,
                    'title'           => $product->title,
                    'price'           => $price->salePrice(),
                    'real_price'      => $price->regularPrice(),
                    'quantity'        => $product->pivot->quantity,
                    'discount'        => $price->discount(),
                    'price_id'        => $product->pivot->price_id,
                ]);
            }
        }

        // Keep ordinary Site order items/payment. Reserve physical stock before any payment starts.
        if (config('services.inventory_stock.enabled', false) && $cart->products->contains(fn ($product) => $product->isPhysical())) {
            try {
                $reservation = app(InventoryStockHoldClient::class)->reserve(
                    $order, (int) option('orders_cancel_time', 60)
                );
                if (empty($reservation['hold_id']) || ($reservation['status'] ?? '') !== 'held') {
                    throw new \RuntimeException('Inventory did not confirm a stock reservation.');
                }
                $order->update([
                    'inventory_stock_hold_id' => (int) $reservation['hold_id'],
                    'inventory_stock_hold_status' => 'held',
                ]);
            } catch (\Throwable $exception) {
                // Cart remains intact and no gateway or Site stock mutation has started.
                $order->update(['status' => 'canceled']);
                \Illuminate\Support\Facades\Log::warning('Inventory stock hold failed; checkout blocked.', [
                    'site_order_id' => $order->id, 'error' => $exception->getMessage(),
                ]);
                // Keep the existing checkout error display; give customers an
                // actionable Persian reason without exposing SQL, hosts, or keys.
                $message = 'ارتباط با انبار موقتاً برقرار نیست. سبد خرید شما محفوظ است؛ لطفاً دوباره تلاش کنید.';
                if ($exception instanceof \Illuminate\Validation\ValidationException) {
                    $details = strtolower(implode(' ', collect($exception->errors())->flatten()->all()));
                    if (str_contains($details, 'insufficient') || str_contains($details, 'موجودی')) {
                        $message = 'موجودی یکی از کالاها یا قطعات بسته کافی نیست. لطفاً تعداد را کاهش دهید.';
                    } elseif (str_contains($details, 'unavailable') || str_contains($details, 'inactive')
                        || str_contains($details, 'unknown') || str_contains($details, 'component')) {
                        $message = 'یکی از کالاها یا قطعات بسته فعلاً قابل سفارش نیست. لطفاً سبد را بررسی کنید.';
                    } else {
                        $message = 'امکان رزرو این سبد وجود ندارد. لطفاً اقلام سبد را بررسی کنید.';
                    }
                }
                return redirect()->route('front.checkout')->withErrors(['stock' => $message]);
            }
        }

        if ($cart->send_reserved_orders) {
            $orders_id = $user->orders()->reserved()->paid()->pluck('id')->toArray();

            $order->reservedOrders()->sync($orders_id);

            $user->orders()->reserved()->paid()->update([
                'reserve'       => false,
                'main_order_id' => $order->id
            ]);
        }

        // For SEP, keep the cart until the gateway page is successfully rendered.
        // This prevents losing the cart when token/payment initialization fails.
        if (! in_array((string) $request->gateway, ['sep', 'local'], true)) {
            $cart->delete();

            // cancel order after $minutes
            $minutes = option('orders_cancel_time', 60);
            CancelOrder::dispatch($order)->delay(now()->addMinutes($minutes));
        }

        event(new OrderCreated($order));

        return $this->pay($order, $request, in_array((string) $request->gateway, ['sep', 'local'], true) ? $cart : null);
    }

    public function edit(Order $order)
    {
        if ($order->user_id != auth()->user()->id || !$order->isPaid() || !$order->reserved()) {
            abort(404);
        }

        $gateways  = Gateway::active()->orderBy('ordering')->get();
        $provinces = Province::detectLang()->active()->orderBy('ordering')->get();
        $carriers  = Carrier::detectLang()->active()->latest()->get();
        $customer = $this->getCustomer();
        $balance  = $customer->wallet_balance; // مثبت=اعتبار، منفی=بدهی
        return view('front::user.orders.edit', compact(
            'order',
            'gateways',
            'provinces',
            'carriers',
            'wallet',
        ));
    }

    public function update(Order $order, UpdateOrderRequest $request)
    {
        if ($order->user_id != auth()->user()->id || !$order->isPaid() || !$order->reserved()) {
            abort(404);
        }

        if (!$order->carrier_id)
            return redirect()->route('front.orders.edit', ['order', $order])->withErrors(['carrier_id' => 'نحوه ارسال انتخاب نشده است']);

        $gateway = Gateway::where('key', $request->gateway)->first();
        $data    = $request->validated();

        $data['shipping_cost'] = $order->shippingCostAmount();

        if ($gateway) {
            $data['gateway_id'] = $gateway->id;
        }

        $order->update($data);

        $carrier_result = Cart::canUseCarrier($request->carrier_id, $order->weight(), $order->city_id);

        if (!$carrier_result['status']) {
            return redirect()->back()->withInput()->withErrors([
                'carrier_id' => $carrier_result['message'],
            ]);
        }

        return $this->payShippingCost($order, $request);
    }

    public function payShippingCost(Order $order, Request $request)
    {
        $gateways = Gateway::active()->pluck('key')->toArray();

        $request->validate([
            'gateway' => 'required|in:wallet,' . implode(',', $gateways),
        ]);

        $customer = Customer::query()
            ->where('site_customer_id', (int) $order->user_id)
            ->firstOrFail();

        $shippingAmount = (int) $order->shippingCostAmount();
        $requiredAmount = $this->requiredPaymentAmount($customer, $shippingAmount);

        if ($requiredAmount <= 0) {
            $this->consumeShippingCostFromCustomerAccount($customer, $order, $shippingAmount);
            return $this->shippingCostPaid($order);
        }

        $gateway = (string) $request->gateway;

        if ($gateway === 'wallet') {
            $fallbackGateway = Gateway::active()->orderBy('ordering')->first();

            if (! $fallbackGateway) {
                return redirect()->route('front.orders.show', ['order' => $order])
                    ->with('error', trans('front::messages.controller.active-port'))
                    ->with('order_id', $order->id);
            }

            $gateway = (string) $fallbackGateway->key;
        }

        try {
            $gatewayConfigs = get_gateway_configs($gateway);

            return Payment::via($gateway)
                ->config($gatewayConfigs)
                ->callbackUrl(route('front.orders.shipping-cost.verify', ['gateway' => $gateway]))
                ->purchase(
                    (new Invoice)->amount($requiredAmount),
                    function ($driver, $transactionId) use ($order, $gateway, $requiredAmount) {
                        DB::table('transactions')->insert([
                            'status' => false,
                            'amount' => $requiredAmount,
                            'factorNumber' => $order->id,
                            'mobile' => auth()->user()->username,
                            'message' => '[SHIPPING] ' . trans('front::messages.controller.port-transaction') . $gateway,
                            'transID' => (string) $transactionId,
                            'token' => (string) $transactionId,
                            'user_id' => auth()->id(),
                            'transactionable_type' => Order::class,
                            'transactionable_id' => $order->id,
                            'gateway_id' => Gateway::where('key', $gateway)->value('id'),
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]);

                        session()->put('transactionId', (string) $transactionId);
                        session()->put('amount', $requiredAmount);
                    }
                )
                ->pay()
                ->render();
        } catch (Exception $e) {
            return redirect()->route('front.orders.show', ['order' => $order])
                ->with('error', $e->getMessage())
                ->with('order_id', $order->id);
        }
    }

    public function verifyShippingCost($gateway)
    {
        $transactionId = (string) session()->get('transactionId');
        $sessionAmount = (int) session()->get('amount');

        $transaction = Transaction::query()
            ->where('status', false)
            ->where('transID', $transactionId)
            ->firstOrFail();

        Gateway::assertLocalCallbackAllowed((string) $gateway, $transaction);

        $order = $transaction->transactionable;
        abort_unless($order instanceof Order, 404);

        $amount = (int) ($transaction->amount ?: $sessionAmount);
        $gatewayConfigs = get_gateway_configs($gateway);

        try {
            $receipt = Payment::via($gateway)->config($gatewayConfigs);

            if ($amount > 0) {
                $receipt = $receipt->amount($amount);
            }

            $receipt = $receipt->transactionId($transactionId)->verify();

            $customer = Customer::query()
                ->where('site_customer_id', (int) $order->user_id)
                ->firstOrFail();

            CustomerLedger::query()->updateOrCreate(
                [
                    'customer_id' => (int) $customer->id,
                    'reference_type' => Transaction::class,
                    'reference_id' => (int) $transaction->id,
                    'type' => 'credit',
                ],
                [
                    'amount' => $amount,
                    'note' => 'پرداخت آنلاین تسویه حساب و هزینه ارسال سفارش #' . $order->id,
                ]
            );

            $shippingAmount = (int) $order->shippingCostAmount();
            $this->consumeShippingCostFromCustomerAccount($customer->fresh(), $order, $shippingAmount);

            DB::table('transactions')->where('id', $transaction->id)->update([
                'status' => 1,
                'amount' => $amount,
                'factorNumber' => $order->id,
                'mobile' => $order->mobile,
                'traceNumber' => $receipt->getReferenceId(),
                'message' => $transaction->message . '<br>' . trans('front::messages.controller.successful-payment') . $gateway,
                'updated_at' => Carbon::now(),
            ]);

            session()->forget(['transactionId', 'amount']);

            return $this->shippingCostPaid($order);
        } catch (\Exception $exception) {
            DB::table('transactions')->where('id', $transaction->id)->update([
                'message' => $transaction->message . '<br>' . $exception->getMessage(),
                'updated_at' => Carbon::now(),
            ]);

            return redirect()->route('front.orders.show', ['order' => $order])
                ->with('error', $exception->getMessage());
        }
    }

    public function pay(Order $order, Request $request, ?Cart $cart = null)
    {
        $gateways = Gateway::active()->pluck('key')->toArray();

        $request->validate([
            'gateway' => 'required|in:wallet,' . implode(',', $gateways),
        ]);

        // Never debit an already-paid order again, including one whose Inventory sync failed.
        if ($order->status !== 'unpaid') {
            return redirect()->route('front.orders.show', ['order' => $order])
                ->with('transaction-error', 'این سفارش قبلاً پرداخت یا بسته شده است.');}
        if ($order->inventory_stock_hold_id) {
            try {
                $status = app(InventoryStockHoldClient::class)->status($order);
                if (!in_array($status['status'] ?? '', ['held', 'payment_confirmed'], true)
                    || (isset($status['expires_at']) && ($status['status'] ?? '') === 'held'
                        && \Carbon\Carbon::parse($status['expires_at'])->isPast())) {
                    return redirect()->route('front.orders.show', ['order' => $order])
                        ->with('transaction-error', 'رزرو موجودی این سفارش منقضی شده است.');
                }
            } catch (\Throwable $exception) {
                return redirect()->route('front.orders.show', ['order' => $order])
                    ->with('transaction-error', 'امکان بررسی رزرو انبار در این لحظه وجود ندارد.');
            }
        }
        if (!$order->inventory_stock_hold_id) {
        $inventoryError = $this->inventoryStockError($order->items()
        ->with('product')->get()
        ->filter(fn ($item) => !$item->product || $item->product->isPhysical())
        ->map(fn ($item) => [
            'product_id' => $item->product_id,
            'variant_id' => $item->price_id,
            'quantity' => $item->quantity,
            'title' => $item->title,
        ])->all());if ($inventoryError !== null) {
            return redirect()->route('front.orders.show', ['order' => $order])
                ->with('transaction-error', $inventoryError);}
        }

        $customer = $this->getCustomer();
        $requiredAmount = $this->requiredPaymentAmount($customer, (int) $order->price);

        // Existing customer credit is consumed first. Existing debt is added to
        // the amount that must be settled before this order can be paid.
        if ($requiredAmount <= 0) {
            $this->consumeOrderAmountFromCustomerAccount($customer, $order);
            return $this->orderPaid($order);
        }

        $gateway = (string) $request->gateway;

        if ($gateway === 'wallet') {
            $fallbackGateway = Gateway::active()->orderBy('ordering')->first();

            if (! $fallbackGateway) {
                return redirect()->route('front.orders.show', ['order' => $order])
                    ->with('transaction-error', trans('front::messages.controller.active-port'))
                    ->with('order_id', $order->id);
            }

            $gateway = (string) $fallbackGateway->key;
        }

        return $this->startOrderGatewayPayment($order, $gateway, $requiredAmount, $cart);
    }

    public function verify($gateway)
    {
        $transactionId = (string) session()->get('transactionId');
        $sessionAmount = (int) session()->get('amount');

        $transaction = Transaction::query()
            ->where('status', false)
            ->where('transID', $transactionId)
            ->firstOrFail();

        Gateway::assertLocalCallbackAllowed((string) $gateway, $transaction);

        $order = $transaction->transactionable;
        abort_unless($order instanceof Order, 404);

        $amount = (int) ($transaction->amount ?: $sessionAmount);
        $gatewayConfigs = get_gateway_configs($gateway);

        try {
            $receipt = Payment::via($gateway)->config($gatewayConfigs);

            if ($amount > 0) {
                $receipt = $receipt->amount($amount);
            }

            $receipt = $receipt->transactionId($transactionId)->verify();

            $customer = Customer::query()
                ->where('site_customer_id', (int) $order->user_id)
                ->firstOrFail();

            // The gateway payment settles the account first. This can cover an
            // old debt, the current order, or both. It is recorded only after
            // the bank confirms payment, so failed transactions never change
            // the customer's Inventory balance.
            CustomerLedger::query()->updateOrCreate(
                [
                    'customer_id' => (int) $customer->id,
                    'reference_type' => Transaction::class,
                    'reference_id' => (int) $transaction->id,
                    'type' => 'credit',
                ],
                [
                    'amount' => $amount,
                    'note' => 'پرداخت آنلاین تسویه حساب و سفارش #' . $order->id,
                ]
            );

            $this->consumeOrderAmountFromCustomerAccount($customer->fresh(), $order);

            DB::table('transactions')->where('id', $transaction->id)->update([
                'status' => 1,
                'amount' => $amount,
                'factorNumber' => $order->id,
                'mobile' => $order->mobile,
                'traceNumber' => $receipt->getReferenceId(),
                'message' => $transaction->message . '<br>' . trans('front::messages.controller.successful-payment') . $gateway,
                'updated_at' => Carbon::now(),
            ]);

            session()->forget(['transactionId', 'amount']);

            return $this->orderPaid($order);
        } catch (\Exception $exception) {
            DB::table('transactions')->where('id', $transaction->id)->update([
                'message' => $transaction->message . '<br>' . $exception->getMessage(),
                'updated_at' => Carbon::now(),
            ]);

            // A deliberate cancel on the local simulator is a definitive unpaid
            // outcome. Release only this test order's Inventory hold; never touch
            // a real payment gateway or a paid order.
            if ($gateway === 'local' && request()->boolean('cancel') && $order->status === 'unpaid') {
                try {
                    if ($order->inventory_stock_hold_id) {
                        $release = app(InventoryStockHoldClient::class)->release($order);
                        if (($release['status'] ?? '') !== 'released') {
                            throw new \RuntimeException('Inventory did not confirm local test hold release.');
                        }
                    }
                    $order->update([
                        'status' => 'canceled',
                        'inventory_stock_hold_status' => $order->inventory_stock_hold_id ? 'released' : null,
                    ]);
                } catch (\Throwable $releaseException) {
                    \Illuminate\Support\Facades\Log::error('Local test payment canceled but Inventory hold release failed.', [
                        'site_order_id' => $order->id,
                        'error' => $releaseException->getMessage(),
                    ]);
                }
            }

            return redirect()->route('front.orders.show', ['order' => $order])
                ->with('transaction-error', $exception->getMessage());
        }
    }

    public function getPrices(Order $order, Request $request)
    {
        if ($request->city_id) {
            $request->validate([
                'city_id' => 'required|exists:cities,id',
            ]);
        }

        if ($request->carrier_id) {
            $request->validate([
                'carrier_id' => 'required|exists:carriers,id',
            ]);
        }

        $carriers = Carrier::detectLang()->active()->latest()->get();

        if ($request->city_id) {
            $province_id = City::find($request->city_id)->province_id;
        } else {
            $province_id = null;
        }

        $order->update([
            'province_id' => $province_id,
            'city_id'     => $request->city_id,
            'carrier_id'  => $request->carrier_id,
        ]);

        return [
            'checkout_sidebar'   => view('front::partials.order-sidebar', ['order' => $order])->render(),

            'carriers_container' => view('front::partials.carriers-container', [
                'cart'     => $order,
                'carriers' => $carriers
            ])->render(),
        ];
    }

    /** Check the same central-warehouse availability used by the paid-order invoice listener. */private function inventoryStockError(array $lines): ?string {if (!$lines) {         return null;     }     $inv = DB::connection('inv');$warehouseId = $inv->table('warehouses')->where('type', 'central')->orderBy('id')->value('id');if (!$warehouseId) {
        return 'انبار مرکزی در دسترس نیست.';     }
    foreach (collect($lines)->groupBy(fn ($line) => $line['product_id'] . ':' . $line['variant_id']) as $group) {
        $line = $group->first();
        $quantity = $group->sum('quantity');
        $available = $inv->table('warehouse_stocks')
            ->where('warehouse_id', $warehouseId)
            ->where('product_id', $line['product_id'])
            ->where('product_variant_id', $line['variant_id'])
            ->value('quantity');         if ($available === null || (int) $available < $quantity) {
                return 'موجودی انبار مرکزی برای محصول «' . $line['title'] . '» کافی نیست.';}     }     return null;}

    private function payUsingWallet(Order $order)
    {
        $customer = $this->getCustomer();
        $requiredAmount = $this->requiredPaymentAmount($customer, (int) $order->price);

        if ($requiredAmount <= 0) {
            $this->consumeOrderAmountFromCustomerAccount($customer, $order);
            return $this->orderPaid($order);
        }

        $gateway = Gateway::active()->orderBy('ordering')->first();

        if (! $gateway) {
            return redirect()->route('front.orders.show', ['order' => $order])
                ->with('transaction-error', trans('front::messages.controller.active-port'))
                ->with('order_id', $order->id);
        }

        return $this->startOrderGatewayPayment(
            $order,
            (string) $gateway->key,
            $requiredAmount
        );
    }

    private function payShippingCostUsingWallet(Order $order)
    {
        $customer = Customer::query()
            ->where('site_customer_id', (int) $order->user_id)
            ->firstOrFail();

        $shippingAmount = (int) $order->shippingCostAmount();
        $requiredAmount = $this->requiredPaymentAmount($customer, $shippingAmount);

        if ($requiredAmount <= 0) {
            $this->consumeShippingCostFromCustomerAccount($customer, $order, $shippingAmount);
            return $this->shippingCostPaid($order);
        }

        return redirect()->route('front.orders.show', ['order' => $order])
            ->with('error', 'برای تسویه بدهی/هزینه ارسال، یکی از درگاه‌های بانکی را انتخاب کنید.');
    }

    private function orderPaid(Order $order)
    {
        $order->update([
            'status' => 'paid',
        ]);

        // The payment is authoritative even if Inventory is temporarily unreachable.
        // The paid-order pull can consume the still-held stock; expiry checks Site status first.
        if ($order->inventory_stock_hold_id) {
            try {
                app(InventoryStockHoldClient::class)->confirm($order);
                $order->update(['inventory_stock_hold_status' => 'payment_confirmed']);
            } catch (\Throwable $exception) {
                $order->update(['inventory_stock_hold_status' => 'confirmation_pending']);
                try {
                    $remote = app(InventoryStockHoldClient::class)->status($order);
                    if (in_array($remote['status'] ?? '', ['released', 'expired'], true)) {
                        $order->update([
                            'inventory_stock_hold_status' => 'fulfillment_exception',
                            'inventory_sync_status' => 'failed',
                            'inventory_last_error' => 'Paid after the Inventory stock hold was released; manual recovery/refund required.',
                        ]);
                    }
                } catch (\Throwable $lookupException) {
                    // Leave for the Inventory paid-order importer and operations reconciliation.
                }
                \Illuminate\Support\Facades\Log::critical('Paid Site order needs Inventory stock confirmation.', [
                    'site_order_id' => $order->id, 'hold_id' => $order->inventory_stock_hold_id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        event(new OrderPaid($order));

        return redirect()->route('front.orders.show', ['order' => $order])->with('message', 'ok');
    }
    /**
     * Site wallet convention: positive = usable credit, negative = debt.
     * Inventory account convention is the opposite, therefore wallet_balance
     * is exposed by the Inventory Customer model as -balance.
     */
    private function requiredPaymentAmount(Customer $customer, int $purchaseAmount): int
    {
        $walletBalance = (int) $customer->wallet_balance;

        return max($purchaseAmount - $walletBalance, 0);
    }

    private function consumeOrderAmountFromCustomerAccount(Customer $customer, Order $order): void
    {
        $amount = (int) $order->price;

        if ($amount <= 0) {
            return;
        }

        CustomerLedger::query()->updateOrCreate(
            [
                'customer_id' => (int) $customer->id,
                'reference_type' => Order::class,
                'reference_id' => (int) $order->id,
                'type' => 'debit',
            ],
            [
                'amount' => $amount,
                'note' => 'مصرف اعتبار/پرداخت از حساب برای سفارش #' . $order->id,
            ]
        );
    }

    private function consumeShippingCostFromCustomerAccount(
        Customer $customer,
        Order $order,
        int $amount
    ): void {
        if ($amount <= 0) {
            return;
        }

        CustomerLedger::query()->updateOrCreate(
            [
                'customer_id' => (int) $customer->id,
                'reference_type' => 'site_order_shipping_cost',
                'reference_id' => (int) $order->id,
                'type' => 'debit',
            ],
            [
                'amount' => $amount,
                'note' => 'مصرف اعتبار/پرداخت هزینه ارسال سفارش #' . $order->id,
            ]
        );
    }

    private function startOrderGatewayPayment(
        Order $order,
        string $gateway,
        int $amount,
        ?Cart $cart = null
    ) {
        try {
            $gatewayConfigs = get_gateway_configs($gateway);

            $payment = Payment::via($gateway)
                ->config($gatewayConfigs)
                ->callbackUrl(route('front.orders.verify', ['gateway' => $gateway]))
                ->purchase(
                    (new Invoice)->amount($amount),
                    function ($driver, $transactionId) use ($order, $gateway, $amount) {
                        DB::table('transactions')->insert([
                            'status' => false,
                            'amount' => $amount,
                            'factorNumber' => $order->id,
                            'mobile' => auth()->user()->username,
                            'message' => trans('front::messages.controller.port-transaction') . $gateway,
                            'transID' => (string) $transactionId,
                            'token' => (string) $transactionId,
                            'user_id' => auth()->id(),
                            'transactionable_type' => Order::class,
                            'transactionable_id' => $order->id,
                            'gateway_id' => Gateway::where('key', $gateway)->value('id'),
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]);

                        session()->put('transactionId', (string) $transactionId);
                        session()->put('amount', $amount);
                    }
                );

            $response = $payment->pay()->render();

            if (in_array($gateway, ['sep', 'local'], true) && $cart) {
                $cart->delete();

                if (config('queue.default') !== 'sync') {
                    $minutes = option('orders_cancel_time', 60);
                    CancelOrder::dispatch($order)->delay(now()->addMinutes($minutes));
                }
            }

            return $response;
        } catch (\Shetabit\Multipay\Exceptions\PurchaseFailedException $e) {
            // SEP definitively rejected payment initialization before creating a
            // transaction. The customer still has their cart, so immediately
            // return the held stock instead of waiting for hold expiration.
            // Never release if a gateway transaction may have been initiated.
            if ($gateway === 'sep' && $cart !== null && $order->inventory_stock_hold_id
                && !$order->transactions()->exists()) {
                try {
                    $release = app(InventoryStockHoldClient::class)->release($order);
                    if (($release['status'] ?? '') !== 'released') {
                        throw new \RuntimeException('Inventory did not confirm release of rejected SEP checkout.');
                    }
                    $order->update([
                        'status' => 'canceled',
                        'inventory_stock_hold_status' => 'released',
                    ]);
                } catch (\Throwable $releaseException) {
                    // Keep the reservation/order for reconciliation when Inventory
                    // cannot confirm release; do not manipulate stock locally.
                    \Illuminate\Support\Facades\Log::error('Failed to release SEP stock hold after gateway initiation rejection.', [
                        'site_order_id' => $order->id,
                        'hold_id' => $order->inventory_stock_hold_id,
                        'error' => $releaseException->getMessage(),
                    ]);
                }
            }
            return redirect()->route('front.orders.show', ['order' => $order])
                ->with('transaction-error', $e->getMessage())
                ->with('order_id', $order->id);
        } catch (Exception $e) {
            return redirect()->route('front.orders.show', ['order' => $order])
                ->with('transaction-error', $e->getMessage())
                ->with('order_id', $order->id);
        }
    }

    private function getCustomer() {
        return Customer::where('site_customer_id', auth()->id())
            ->firstOrFail();
    }
    private function shippingCostPaid(Order $order)
    {
        $order->update([
            'reserve' => false,
            'price'   => $order->price + $order->shipping_cost
        ]);

        $admins = User::whereIn('level', ['admin', 'creator'])->get();
        Notification::send($admins, new NotificationsOrderPaid($order));

        return redirect()->route('front.orders.show', ['order' => $order])->with('message', 'ok');
    }
}
