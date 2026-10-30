@extends('front::user.layouts.master')

@section('user-content')
    @php
        // The modern invoice component is the ONLY order-details layout.
        // Use the authoritative Inventory invoice when it exists, and otherwise
        // render the saved Site order in exactly the same component.
        $orderInvoiceDisplay = $inventoryRevision ?? $orderInvoiceDisplay ?? null;
        $hasInventoryInvoice = is_array($orderInvoiceDisplay)
            && (!empty($orderInvoiceDisplay['invoice_id']) || !empty($orderInvoiceDisplay['invoice_uuid']));

        if (!$hasInventoryInvoice) {
            $order->loadMissing([
                'items.product',
                'items.get_price.product',
                'items.get_price.get_attributes',
                'transactions',
            ]);
            $orderInvoiceDisplay = app(\App\Services\Inventory\SiteOrderInvoicePreviewService::class)
                ->forOrder($order);
        }

        $customerCredit = max(0, (int) ($balance ?? 0));
    @endphp

    <div class="col-xl-9 col-lg-8 col-md-8 col-sm-12">
        <div class="row">
            @if(session('message') == 'ok')
                <div class="col-12">
                    <div class="alert alert-success mb-3" role="status">
                        سفارش شماره {{ $order->id }} با موفقیت ثبت شد.
                    </div>
                </div>
            @endif

            @if(session('transaction-error'))
                <div class="col-12">
                    <div class="alert alert-danger mb-3" role="alert">
                        {{ session('transaction-error') }}
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="col-12">
                    <div class="alert alert-danger mb-3" role="alert">{{ session('error') }}</div>
                </div>
            @endif

            @if ($order->reserved() && $order->isPaid())
                <div class="col-12">
                    <div class="alert alert-info mb-3">
                        این سفارش رزرو شده است و می‌توانید همراه سفارش بعدی آن را ارسال کنید.
                        <a href="{{ route('front.orders.edit', ['order' => $order]) }}">درخواست ارسال و پرداخت هزینه ارسال</a>
                    </div>
                </div>
            @endif

            {{-- Unpaid orders still need the existing payment actions. Paid orders do not
                 get an older summary, shipping carousel, or legacy item table. --}}
            @if($order->status === 'unpaid')
                <div class="col-12 mb-3">
                    <div class="dt-sn p-3" style="background:#fff;border:1px solid #e7edf5;border-radius:16px;">
                        <h5 style="font-weight:800;margin-bottom:12px;">پرداخت سفارش</h5>
                        <form action="{{ route('front.orders.pay', ['order' => $order]) }}" method="GET">
                            <div class="row align-items-center">
                                <div class="col-sm-8 mb-2 mb-sm-0">
                                    <select class="form-control" name="gateway" required>
                                        @if($customerCredit >= (int) $order->price)
                                            <option value="wallet">{{ trans('front::messages.profile.pay-with-wallet') }}</option>
                                        @elseif($customerCredit > 0)
                                            <option value="wallet">{{ trans('front::messages.profile.charging-and-payment') }}</option>
                                        @endif
                                        @foreach($gateways as $gateway)
                                            <option value="{{ $gateway->key }}">{{ $gateway->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-4">
                                    <button class="btn btn-primary w-100" type="submit">
                                        {{ trans('front::messages.profile.order-payment') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <div class="col-12">
                <div class="section-title text-sm-title title-wide dt-sl mb-3 px-res-1">
                    <h2>جزئیات فاکتور و اقلام سفارش</h2>
                </div>
                @include('front::user.orders.partials.inventory-invoice', ['inventoryInvoice' => $orderInvoiceDisplay])
            </div>
        </div>
    </div>
@endsection
