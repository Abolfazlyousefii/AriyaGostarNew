{{-- Separate manual transfer confirmation; never edit or synthesize gateway transactions. --}}
<div class="col-12 d-print-none">
  <div class="card">
    <div class="card-header"><h4 class="card-title">تأیید دستی پرداخت کارت‌به‌کارت</h4></div>
    <div class="card-body">
      @if($order->status === 'unpaid')
        @can('orders.update')
          <div class="alert alert-warning">
            فقط وقتی وجه واقعاً در حساب دریافت و با مبلغ سفارش تطبیق داده شده تأیید کنید.
            این عملیات سفارش را پرداخت‌شده می‌کند و فرایند صدور فاکتور و کسر موجودی انبارداری را فعال می‌کند.
            پرداخت درگاهیِ ناموفق را از اینجا موفق ثبت نکنید؛ ابتدا تراکنش را بررسی کنید.
          </div>
          @if($errors->any())
            <div class="alert alert-danger">
              @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
          @endif
          <form action="{{ route('admin.orders.manual-payment.confirm', ['order' => $order]) }}" method="POST" onsubmit="return window.confirm('دریافت وجه سفارش شماره {{ $order->id }} را بررسی کرده‌اید؟ این تأیید باعث شروع فرایند انبارداری می‌شود.');">
            @csrf
            <div class="row">
              <div class="col-lg-3 col-md-6 mb-1">
                <label>مبلغ سفارش (تومان)</label>
                <input class="form-control" value="{{ number_format((int) $order->price) }}" readonly>
                <input type="hidden" name="amount_confirm" value="{{ (int) $order->price }}">
              </div>
              <div class="col-lg-4 col-md-6 mb-1">
                <label>شماره پیگیری یا شناسه رسید بانکی</label>
                <input class="form-control" name="receipt_reference" required minlength="3" maxlength="150" value="{{ old('receipt_reference') }}" placeholder="کد پیگیری کارت‌به‌کارت">
              </div>
              <div class="col-lg-5 col-md-12 mb-1">
                <label>توضیحات (اختیاری)</label>
                <input class="form-control" name="payment_note" maxlength="500" value="{{ old('payment_note') }}" placeholder="ملاحظات واریزی">
              </div>
            </div>
            <div class="form-group">
              <label><input type="checkbox" name="funds_received" value="1" required> دریافت وجه در حساب را شخصاً تأیید می‌کنم و مسئولیت این اقدام را می‌پذیرم.</label>
            </div>
            <button class="btn btn-success" type="submit">تأیید دستی پرداخت و ارسال به صف انبار</button>
          </form>
        @else
          <div class="alert alert-secondary">برای تأیید دستی به مجوز ویرایش سفارش نیاز دارید.</div>
        @endcan
      @else
        <p class="mb-0">وضعیت پرداخت: <strong>{{ $order->statusText() }}</strong>
          @if($order->status === 'paid')
            | وضعیت همگام‌سازی انبارداری: <strong>{{ $order->inventory_sync_status ?? 'نامشخص' }}</strong>
            @if($order->inventory_document_id) | شماره فاکتور انبار: {{ $order->inventory_document_id }} @endif
            @if($order->inventory_sync_status === 'failed')
              <span class="text-danger">| خطای ارسال؛ نیازمند بررسی مدیر سیستم.</span>
            @endif
          @endif
        </p>
      @endif
      @if(session('success'))<div class="alert alert-success mt-2">{{ session('success') }}</div>@endif
    </div>
  </div>
</div>
