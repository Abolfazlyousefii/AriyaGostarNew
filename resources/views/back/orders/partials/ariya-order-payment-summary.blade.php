{{-- Additional read-only transaction facts for the authorized admin order detail page. --}}
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">جزئیات پرداخت و تراکنش‌های سفارش #{{ $order->id }}</h4>
        </div>
        <div class="card-body">
            <div class="row mb-2">
                <div class="col-md-4 mb-1">
                    <strong>وضعیت پرداخت سفارش:</strong>
                    @if ($order->status === 'paid')
                        <span class="badge badge-success">پرداخت شده</span>
                    @elseif ($order->status === 'canceled')
                        <span class="badge badge-danger">لغوشده</span>
                    @else
                        <span class="badge badge-warning">پرداخت نشده</span>
                    @endif
                </div>
                <div class="col-md-4 mb-1">
                    <strong>مبلغ کل سفارش:</strong> {{ number_format((float) $order->price) }} تومان
                </div>
                <div class="col-md-4 mb-1">
                    <strong>روش ثبت سفارش:</strong> {{ $order->in_person ? 'حضوری' : 'آنلاین' }}
                </div>
            </div>
            <div class="alert alert-info">
                اطلاعات زیر فقط برای بررسی پرداخت است. تغییر وضعیت ارسال از قسمت «عملیات» بالای همین صفحه انجام می‌شود.
                وضعیت پرداخت نباید بدون تأیید واقعی تراکنش و بررسی آثار آن بر سفارش تغییر کند.
            </div>
            @if ($order->transactions->isEmpty())
                <div class="alert alert-secondary mb-0">تراکنش بانکی ثبت‌شده‌ای برای این سفارش پیدا نشد.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>شناسه تراکنش</th>
                                <th>تاریخ</th>
                                <th>مبلغ (تومان)</th>
                                <th>وضعیت تراکنش</th>
                                <th>شماره تراکنش</th>
                                <th>شماره پیگیری</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->transactions->sortByDesc('id') as $transaction)
                                <tr>
                                    <td>{{ $transaction->id }}</td>
                                    <td>{{ jdate($transaction->created_at) }}</td>
                                    <td>{{ number_format((float) $transaction->amount) }}</td>
                                    <td>
                                        @if ($transaction->status)
                                            <span class="badge badge-success">موفق</span>
                                        @else
                                            <span class="badge badge-danger">ناموفق / تأییدنشده</span>
                                        @endif
                                    </td>
                                    <td>{{ $transaction->transId ?: '—' }}</td>
                                    <td>{{ $transaction->traceNumber ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
