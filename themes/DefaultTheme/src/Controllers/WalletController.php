<?php

namespace Themes\DefaultTheme\src\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Gateway;
use App\Models\Inventory\Customer;
use App\Models\Inventory\CustomerLedger;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Shetabit\Multipay\Invoice;
use Shetabit\Payment\Facade\Payment;

class WalletController extends Controller
{
    public function index()
    {
        $wallet = Customer::query()
            ->withBalance()
            ->where('site_customer_id', auth()->id())
            ->firstOrFail();

        $histories = $wallet->ledgers()
            ->effectiveForBalance()
            ->latest()
            ->paginate(20);

        return view('front::user.wallet.index', compact('wallet', 'histories'));
    }

    public function show(CustomerLedger $wallet)
    {
        $customerId = Customer::query()
            ->where('site_customer_id', auth()->id())
            ->value('id');

        abort_unless($customerId && (int) $wallet->customer_id === (int) $customerId, 404);

        return view('front::user.wallet.show')->with('history', $wallet);
    }

    public function create()
    {
        $gateways = Gateway::active()->get();
        $customer = Customer::query()
            ->withBalance()
            ->where('site_customer_id', auth()->id())
            ->firstOrFail();

        // Inventory balance > 0 means debt. Site wallet shows the opposite sign:
        // positive = usable credit, negative = debt.
        $walletBalance = (int) $customer->wallet_balance;
        $suggestedAmount = max((int) $customer->debt, 0);

        return view('front::user.wallet.create', compact(
            'gateways',
            'customer',
            'walletBalance',
            'suggestedAmount'
        ));
    }

    public function store(Request $request)
    {
        $gateways = Gateway::active()->pluck('key')->toArray();

        $request->validate([
            'amount' => 'required|integer|min:1000|max:100000000000',
            'gateway' => 'required|in:' . implode(',', $gateways),
        ]);

        $gateway = (string) $request->gateway;
        $amount = (int) $request->amount;

        // Make sure the logged-in Site user has a canonical Inventory customer.
        Customer::query()
            ->where('site_customer_id', auth()->id())
            ->firstOrFail();

        try {
            $gatewayConfigs = get_gateway_configs($gateway);

            $invoice = (new Invoice)->amount($amount);
            $callbackToken = $invoice->getUuid();

            return Payment::via($gateway)
                ->config($gatewayConfigs)
                ->callbackUrl(route('front.wallet.verify', [
                    'gateway' => $gateway,
                    'tx' => $callbackToken,
                ]))
                ->purchase(
                    $invoice,
                    function ($driver, $transactionId) use ($gateway, $amount, $callbackToken) {
                        DB::table('transactions')->insert([
                            'status' => false,
                            'amount' => $amount,
                            'factorNumber' => auth()->id(),
                            'mobile' => auth()->user()->username,
                            'message' => trans('front::messages.controller.created-for-gateway') . $gateway,
                            'transID' => (string) $transactionId,
                            'token' => 'wallet:' . $callbackToken,
                            'user_id' => auth()->id(),
                            'transactionable_type' => User::class,
                            'transactionable_id' => auth()->id(),
                            'gateway_id' => Gateway::where('key', $gateway)->value('id'),
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]);

                        session()->put('transactionId', (string) $transactionId);
                        session()->put('amount', $amount);
                    }
                )
                ->pay()
                ->render();
        } catch (Exception $e) {
            return redirect()
                ->route('front.wallet.index')
                ->with('transaction-error', $e->getMessage());
        }
    }

    public function verify($gateway, Request $request)
    {
        // ARIYA_CALLBACK_CORRELATION_V1
        $gatewayId = Gateway::query()->where('key', (string) $gateway)->value('id');
        $requestToken = (string) ($request->query('tx') ?: $request->input('ResNum', ''));
        $transaction = null;

        if ($gatewayId && $requestToken !== '') {
            $transaction = Transaction::query()
                ->where('token', 'wallet:' . $requestToken)
                ->where('gateway_id', $gatewayId)
                ->first();
        }

        // Compatibility only for payments that were started before this patch.
        if (! $transaction) {
            $legacyTransactionId = (string) session()->get('transactionId', '');
            if ($legacyTransactionId !== '') {
                $transaction = Transaction::query()
                    ->where('transID', $legacyTransactionId)
                    ->where('gateway_id', $gatewayId)
                    ->first();
            }
        }

        if (! $transaction) {
            return redirect()->route('front.wallet.index')
                ->with('transaction-error', 'اطلاعات بازگشت از درگاه با تراکنش حساب مشتری مطابقت ندارد.');
        }

        $storedToken = (string) $transaction->token;
        $isHardened = str_starts_with($storedToken, 'wallet:');
        if ($isHardened) {
            $requestToken = substr($storedToken, strlen('wallet:'));
        }

        if ((string) $transaction->transactionable_type !== \App\Models\User::class) {
            return redirect()->route('front.wallet.index')
                ->with('transaction-error', 'نوع تراکنش بازگشتی معتبر نیست.');
        }

        if ((bool) $transaction->status) {
            session()->forget(['transactionId', 'amount']);
            return redirect()->route('front.wallet.index')->with('message', 'ok');
        }

        $transactionId = (string) $transaction->getAttribute('transId');
        $amount = (int) $transaction->amount;
        $gatewayConfigs = get_gateway_configs($gateway);

        try {
            $receipt = Payment::via($gateway)->config($gatewayConfigs);

            if ($amount > 0) {
                $receipt = $receipt->amount($amount);
            }

            $receipt = $receipt->transactionId($transactionId)->verify();

            if ($isHardened && (string) $gateway === 'sep') {
                $verifiedResNum = (string) $request->input('ResNum', '');
                if ($verifiedResNum === '' || ! hash_equals($requestToken, $verifiedResNum)) {
                    throw new \RuntimeException('شناسه پرداخت حساب تأییدشده توسط درگاه با تراکنش سایت مطابقت ندارد.');
                }
            }

            $customer = Customer::query()
                ->where('site_customer_id', (int) $transaction->user_id)
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
                    'note' => 'پرداخت آنلاین حساب مشتری از سایت - تراکنش #' . $transaction->id,
                ]
            );

            $transaction->update([
                'status' => 1,
                'traceNumber' => $receipt->getReferenceId(),
                'message' => $transaction->message . '<br>' . trans('front::messages.controller.successful-gateway') . $gateway,
                'updated_at' => Carbon::now(),
            ]);

            session()->forget(['transactionId', 'amount']);

            return redirect()->route('front.wallet.index')->with('message', 'ok');
        } catch (\Exception $exception) {
            DB::table('transactions')->where('transID', $transactionId)->update([
                'message' => $transaction->message . '<br>' . $exception->getMessage(),
                'updated_at' => Carbon::now(),
            ]);

            return redirect()
                ->route('front.wallet.index')
                ->with('transaction-error', $exception->getMessage());
        }
    }
}
