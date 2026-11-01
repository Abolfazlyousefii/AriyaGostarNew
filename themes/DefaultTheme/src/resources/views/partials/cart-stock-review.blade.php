@php
    $stockMessages = $stockReview['errors'] ?? [];
    if (request()->routeIs('front.cart')) $stockMessages = array_unique(array_merge($stockMessages, $errors->all()));
@endphp
@if ($stockMessages)
    <div class="alert alert-danger" role="alert" id="cart-stock-review-errors">
        <strong>این اقلام نیاز به بررسی شما دارند:</strong>
        @foreach ($stockMessages as $stockMessage)
            <p class="mb-1">{{ $stockMessage }}</p>
        @endforeach
        <small>انتخاب‌های شما حفظ شده است. برای خرید سایر اقلام، کالای متوقف‌شده را خودتان از سبد حذف کنید؛ یا تا بازگشایی فروش آن منتظر بمانید. در خطاهای تعداد، تعداد را اصلاح کنید.</small>
    </div>
@endif
@if (($stockReview['stale_count'] ?? 0) > 0)
    <div class="alert alert-warning" role="status" id="cart-age-notice">
        <strong>بررسی دوباره سبد پس از ۲۴ ساعت</strong>
        <p class="mb-1">{{ $stockReview['stale_count'] }} قلم از سبد شما بیش از ۲۴ ساعت پیش انتخاب شده است. موجودی زنده اکنون دوباره بررسی شد؛ لطفاً تعداد و قیمت‌ها را مرور کنید.</p>
        @if (request()->routeIs('front.cart'))
            <p class="mb-1">پس از بررسی، «بروزرسانی سبد خرید» را بزنید.</p>
        @else
            <a href="{{ route('front.cart') }}" class="d-inline-block mb-1">بازبینی سبد خرید</a><br>
        @endif
        <small>سبد شما حفظ می‌شود؛ موجودی تا زمان ثبت نهایی برای شما رزرو نشده است.</small>
    </div>
@endif
