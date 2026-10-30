@extends('back.layouts.master')

@section('title', 'کنترل فروش و ویترین')

@push('styles')
<style>
.sales-control-shell,
.sales-control-shell .content-wrapper,
.sales-control-page,
.sales-control-page .card,
.sales-control-page .card-content,
.sales-control-page .card-body {
    min-width: 0;
    max-width: 100%;
}

.sales-control-page .table-responsive {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.sales-control-page .form-control {
    max-width: 100%;
}

.sales-control-page .sales-duration {
    margin-bottom: .4rem;
}

@media (max-width: 767.98px) {
    .sales-control-page .card-body {
        padding: 1rem;
    }

    .sales-control-page .btn-block {
        margin-top: .75rem;
    }
}
</style>
@endpush

@section('content')
<div class="app-content content sales-control-shell">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-8 col-12 mb-2">
            <h3 class="content-header-title mb-0">کنترل فروش و ویترین</h3>
            <div class="row breadcrumbs-top">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">داشبورد</a>
                        </li>
                        <li class="breadcrumb-item active">کنترل فروش و ویترین</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body sales-control-page">
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="card">
        <div class="card-header">
            <h4 class="card-title">انتخاب محصول</h4>
        </div>

        <div class="card-content">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.sales-control.index') }}">
                    <div class="row align-items-end">
                        <div class="col-md-9">
                            <label>نام، کد محصول یا ID</label>
                            <input
                                type="text"
                                class="form-control"
                                name="q"
                                value="{{ $search }}"
                                placeholder="مثلاً گارد سیلیکون آیفون 16 Pro Max"
                            >
                        </div>

                        <div class="col-md-3">
                            <button class="btn btn-primary btn-block" type="submit">
                                <i class="feather icon-search"></i>
                                جستجو
                            </button>
                        </div>
                    </div>
                </form>

                @if($search !== '')
                    <div class="table-responsive mt-2">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>محصول</th>
                                <th>کد</th>
                                <th>وضعیت</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($searchProducts as $product)
                                <tr>
                                    <td>{{ $product->id }}</td>
                                    <td>{{ $product->title }}</td>
                                    <td>{{ $product->product_code ?: '—' }}</td>
                                    <td>
                                        {{ $product->published ? 'منتشر شده' : 'منتشر نشده' }}
                                    </td>
                                    <td>
                                        <a
                                            class="btn btn-sm btn-outline-primary"
                                            href="{{ route('admin.sales-control.index', ['product_id' => $product->id]) }}"
                                        >
                                            مدیریت
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        محصولی پیدا نشد.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </section>

    @if($selectedProduct)
        @php
            $control = $selectedControl;
        @endphp

        <section class="card border-primary">
            <div class="card-header">
                <h4 class="card-title">
                    {{ $selectedProduct->title }}
                    <small class="text-muted">#{{ $selectedProduct->id }}</small>
                </h4>
            </div>

            <div class="card-content">
                <div class="card-body">

                    <div class="row mb-2">
                        <div class="col-md-3">
                            <div class="alert alert-info mb-1">
                                <strong>موجودی انبار مرکزی</strong><br>
                                @if($selectedCentralStock === null)
                                    عدم دسترسی
                                @else
                                    {{ number_format($selectedCentralStock) }} عدد
                                @endif
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="alert alert-secondary mb-1">
                                <strong>موجودی ثبت‌شده سایت</strong><br>
                                {{ number_format($selectedSiteStock) }} عدد
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="alert alert-warning mb-1">
                                <strong>فروش/رزرو از شروع شمارنده</strong><br>
                                {{ number_format($selectedCommitted) }} عدد
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="alert alert-success mb-1">
                                <strong>سهم باقی‌مانده</strong><br>
                                @if($selectedRemaining === null)
                                    بدون محدودیت
                                @else
                                    {{ number_format($selectedRemaining) }} عدد
                                @endif
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.sales-control.save') }}">
                        @csrf

                        <input type="hidden" name="product_id" value="{{ $selectedProduct->id }}">

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="d-block">وضعیت کنترل فروش</label>

                                    <div class="custom-control custom-switch">
                                        <input
                                            type="checkbox"
                                            class="custom-control-input"
                                            id="enabled"
                                            name="enabled"
                                            value="1"
                                            {{ old('enabled', $control?->enabled ?? true) ? 'checked' : '' }}
                                        >
                                        <label class="custom-control-label" for="enabled">
                                            فعال
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>سقف فروش سایت</label>
                                    <input
                                        type="number"
                                        min="0"
                                        class="form-control"
                                        name="sale_limit"
                                        value="{{ old('sale_limit', $control?->sale_limit) }}"
                                        placeholder="مثلاً 25"
                                    >
                                    <small class="text-muted">
                                        موجودی انبار تغییر نمی‌کند. خالی = بدون سقف فروش.
                                    </small>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="d-block">شروع شمارنده جدید</label>
                                    <div class="custom-control custom-checkbox mt-1">
                                        <input
                                            type="checkbox"
                                            class="custom-control-input"
                                            id="reset_counter"
                                            name="reset_counter"
                                            value="1"
                                        >
                                        <label class="custom-control-label" for="reset_counter">
                                            شمارنده فروش از الان صفر شود
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <h5 class="mb-2">تخفیف زمان‌دار</h5>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="d-block">فعال‌سازی تخفیف</label>

                                    <div class="custom-control custom-switch">
                                        <input
                                            type="checkbox"
                                            class="custom-control-input"
                                            id="discount_enabled"
                                            name="discount_enabled"
                                            value="1"
                                            {{ old('discount_enabled', $control?->discount_enabled ?? false) ? 'checked' : '' }}
                                        >
                                        <label class="custom-control-label" for="discount_enabled">
                                            تخفیف فعال باشد
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>نوع تخفیف</label>
                                    <select class="form-control" name="discount_type">
                                        <option value="">انتخاب کنید</option>
                                        <option
                                            value="percent"
                                            {{ old('discount_type', $control?->discount_type) === 'percent' ? 'selected' : '' }}
                                        >
                                            درصدی
                                        </option>
                                        <option
                                            value="amount"
                                            {{ old('discount_type', $control?->discount_type) === 'amount' ? 'selected' : '' }}
                                        >
                                            مبلغ ثابت
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>مقدار تخفیف</label>
                                    <input
                                        type="number"
                                        min="1"
                                        class="form-control"
                                        name="discount_value"
                                        value="{{ old('discount_value', $control?->discount_value) }}"
                                        placeholder="درصد یا مبلغ تومان"
                                    >
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>شروع تخفیف</label>
                                    <input
                                        type="datetime-local"
                                        class="form-control"
                                        id="discount_starts_at"
                                        name="discount_starts_at"
                                        value="{{ old(
                                            'discount_starts_at',
                                            $control?->discount_starts_at?->format('Y-m-d\TH:i')
                                        ) }}"
                                    >
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>پایان تخفیف</label>
                                    <input
                                        type="datetime-local"
                                        class="form-control"
                                        id="discount_ends_at"
                                        name="discount_ends_at"
                                        value="{{ old(
                                            'discount_ends_at',
                                            $control?->discount_ends_at?->format('Y-m-d\TH:i')
                                        ) }}"
                                    >
                                </div>
                            </div>

                            <div class="col-md-8">
                                <label>مدت سریع</label>
                                <div class="btn-group d-flex flex-wrap" role="group">
                                    <button type="button" class="btn btn-outline-secondary sales-duration" data-hours="1">
                                        ۱ ساعت
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary sales-duration" data-hours="6">
                                        ۶ ساعت
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary sales-duration" data-hours="24">
                                        ۱ روز
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary sales-duration" data-hours="72">
                                        ۳ روز
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary sales-duration" data-hours="168">
                                        ۱ هفته
                                    </button>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <h5 class="mb-2">کنترل ویترین</h5>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card border-danger">
                                    <div class="card-body">
                                        <div class="custom-control custom-switch mb-2">
                                            <input
                                                type="checkbox"
                                                class="custom-control-input"
                                                id="show_in_discount_box"
                                                name="show_in_discount_box"
                                                value="1"
                                                {{ old('show_in_discount_box', $control?->show_in_discount_box ?? false) ? 'checked' : '' }}
                                            >
                                            <label class="custom-control-label" for="show_in_discount_box">
                                                نمایش در باکس تخفیفات
                                            </label>
                                        </div>

                                        <div class="form-group">
                                            <label>سقف فروش این ویترین</label>
                                            <input
                                                type="number"
                                                min="0"
                                                class="form-control"
                                                name="discount_box_limit"
                                                value="{{ old('discount_box_limit', $control?->discount_box_limit) }}"
                                                placeholder="خالی = بدون سقف اختصاصی"
                                            >
                                        </div>

                                        <div class="form-group mb-0">
                                            <label>ترتیب نمایش</label>
                                            <input
                                                type="number"
                                                min="0"
                                                class="form-control"
                                                name="discount_box_order"
                                                value="{{ old('discount_box_order', $control?->discount_box_order ?? 100) }}"
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card border-info">
                                    <div class="card-body">
                                        <div class="custom-control custom-switch mb-2">
                                            <input
                                                type="checkbox"
                                                class="custom-control-input"
                                                id="show_in_new_arrivals"
                                                name="show_in_new_arrivals"
                                                value="1"
                                                {{ old('show_in_new_arrivals', $control?->show_in_new_arrivals ?? false) ? 'checked' : '' }}
                                            >
                                            <label class="custom-control-label" for="show_in_new_arrivals">
                                                نمایش در ورودی‌های جدید
                                            </label>
                                        </div>

                                        <div class="form-group">
                                            <label>سقف فروش این ویترین</label>
                                            <input
                                                type="number"
                                                min="0"
                                                class="form-control"
                                                name="new_arrivals_limit"
                                                value="{{ old('new_arrivals_limit', $control?->new_arrivals_limit) }}"
                                                placeholder="خالی = بدون سقف اختصاصی"
                                            >
                                        </div>

                                        <div class="form-group mb-0">
                                            <label>ترتیب نمایش</label>
                                            <input
                                                type="number"
                                                min="0"
                                                class="form-control"
                                                name="new_arrivals_order"
                                                value="{{ old('new_arrivals_order', $control?->new_arrivals_order ?? 100) }}"
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>یادداشت داخلی</label>
                            <textarea
                                class="form-control"
                                rows="3"
                                name="notes"
                                placeholder="مثلاً کمپین فروش قاب‌های سری آیفون 16"
                            >{{ old('notes', $control?->notes) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="feather icon-save"></i>
                            ذخیره تنظیمات
                        </button>
                    </form>

                    @if($control)
                        <div class="d-flex mt-2">
                            <form
                                method="POST"
                                action="{{ route('admin.sales-control.reset', $control) }}"
                                class="mr-1"
                            >
                                @csrf
                                <button type="submit" class="btn btn-outline-warning">
                                    ریست شمارنده فروش
                                </button>
                            </form>

                            <form
                                method="POST"
                                action="{{ route('admin.sales-control.destroy', $control) }}"
                                onsubmit="return confirm('کنترل فروش این محصول حذف شود؟')"
                            >
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="btn btn-outline-danger">
                                    حذف کنترل
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif

    <section class="card">
        <div class="card-header">
            <h4 class="card-title">محصولات تحت کنترل</h4>
        </div>

        <div class="card-content">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                        <tr>
                            <th>محصول</th>
                            <th>انبار</th>
                            <th>سقف فروش</th>
                            <th>مصرف شده</th>
                            <th>باقی‌مانده</th>
                            <th>تخفیف</th>
                            <th>ویترین</th>
                            <th>وضعیت</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($controls as $control)
                            <tr>
                                <td>
                                    @if($control->product)
                                        <strong>{{ $control->product->title }}</strong>
                                        <small class="d-block text-muted">
                                            #{{ $control->product_id }}
                                        </small>
                                    @else
                                        محصول حذف شده
                                    @endif
                                </td>

                                <td>
                                    @if($control->central_stock === null)
                                        —
                                    @else
                                        {{ number_format($control->central_stock) }}
                                    @endif
                                </td>

                                <td>
                                    {{ $control->sale_limit === null ? 'نامحدود' : number_format($control->sale_limit) }}
                                </td>

                                <td>{{ number_format($control->committed_quantity) }}</td>

                                <td>
                                    {{ $control->remaining_quantity === null
                                        ? 'نامحدود'
                                        : number_format($control->remaining_quantity)
                                    }}
                                </td>

                                <td>
                                    @if($control->discount_enabled)
                                        {{ $control->discount_type === 'percent' ? '%' : 'تومان' }}
                                        {{ number_format($control->discount_value) }}
                                    @else
                                        —
                                    @endif
                                </td>

                                <td>
                                    @if($control->show_in_discount_box)
                                        <span class="badge badge-danger">تخفیفات</span>
                                    @endif

                                    @if($control->show_in_new_arrivals)
                                        <span class="badge badge-info">ورودی جدید</span>
                                    @endif

                                    @if(!$control->show_in_discount_box && !$control->show_in_new_arrivals)
                                        —
                                    @endif
                                </td>

                                <td>
                                    <span class="badge badge-primary">
                                        {{ $control->sales_control_status }}
                                    </span>
                                </td>

                                <td>
                                    @if($control->product)
                                        <a
                                            class="btn btn-sm btn-outline-primary"
                                            href="{{ route('admin.sales-control.index', ['product_id' => $control->product_id]) }}"
                                        >
                                            ویرایش
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-3">
                                    هنوز هیچ محصولی تنظیم نشده است.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-2">
                    {{ $controls->links() }}
                </div>
            </div>
        </div>
    </section>
    </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    function localDateTimeValue(date) {
        var pad = function (value) {
            return String(value).padStart(2, '0');
        };

        return date.getFullYear()
            + '-' + pad(date.getMonth() + 1)
            + '-' + pad(date.getDate())
            + 'T' + pad(date.getHours())
            + ':' + pad(date.getMinutes());
    }

    document.querySelectorAll('.sales-duration').forEach(function (button) {
        button.addEventListener('click', function () {
            var startInput = document.getElementById('discount_starts_at');
            var endInput = document.getElementById('discount_ends_at');

            var start = startInput.value
                ? new Date(startInput.value)
                : new Date();

            startInput.value = localDateTimeValue(start);

            var end = new Date(start.getTime());
            end.setHours(end.getHours() + Number(button.dataset.hours || 0));

            endInput.value = localDateTimeValue(end);
        });
    });
})();
</script>
@endpush
{{-- ARIYA_SALES_CONTROL_JALALI_PICKER_V1 --}}
@push('scripts')
<style>
/* ARIYA_SALES_CONTROL_JALALI_PICKER_V1 */
.ariya-jalali-native {display:none!important}
.ariya-jalali-visible {cursor:pointer!important;direction:rtl;text-align:right;background-color:#fff!important}
.ariya-jalali-popover[hidden]{display:none!important}
.ariya-jalali-popover{position:fixed;z-index:1000000;box-sizing:border-box;direction:rtl;text-align:right;background:#fff;color:#243447;border:1px solid #dce5f0;border-radius:13px;box-shadow:0 16px 50px #1d294233;padding:12px;font-family:Tahoma,sans-serif;font-size:13px;max-height:calc(100vh - 16px);overflow-y:auto}
.ariya-jalali-head{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:10px}
.ariya-jalali-head button,.ariya-jalali-actions button{border:1px solid #e0e5ef;background:#f9fbff;color:#344054;border-radius:7px;padding:7px;cursor:pointer;font-size:12px}
.ariya-jalali-month{font-size:15px}
.ariya-jalali-grid{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:3px;text-align:center}
.ariya-jalali-weekday{font-weight:700;padding:6px 0;color:#7a8497}
.ariya-jalali-weekday.friday,.ariya-jalali-day.friday{color:#bb4358}
.ariya-jalali-day{padding:8px 0;border:1px solid transparent;background:#fff;border-radius:7px;cursor:pointer;font-family:inherit;font-size:12px}
.ariya-jalali-day:hover,.ariya-jalali-day.today{border-color:#655df0;background:#f2f0ff}
.ariya-jalali-day.selected{background:#655df0;color:#fff!important}
.ariya-jalali-time{display:flex;align-items:center;justify-content:center;gap:8px;margin-top:12px;padding:9px;background:#f7f9fc;border-radius:9px}
.ariya-jalali-time-select{font-family:inherit;border:1px solid #dce5f0;border-radius:5px;padding:5px;color:#344054;background:#fff}
.ariya-jalali-actions{display:flex;align-items:center;justify-content:space-between;gap:6px;margin-top:10px}
.ariya-jalali-actions .ariya-jalali-confirm{background:#655df0;border-color:#655df0;color:#fff}
@media(max-width:480px){.ariya-jalali-popover{font-size:12px;padding:8px}.ariya-jalali-day{padding:7px 0}}
</style>
<script>
/* ARIYA_SALES_CONTROL_JALALI_PICKER_V1 */
(function () {
    'use strict';

    var ids = ['discount_starts_at', 'discount_ends_at'];
    var fields = ids.map(function (id) { return document.getElementById(id); });
    if (fields.some(function (item) { return !item || item.type !== 'datetime-local'; })) return;

    var fmt;
    try {
        fmt = new Intl.DateTimeFormat('en-US-u-ca-persian', {
            calendar: 'persian', numberingSystem: 'latn',
            year: 'numeric', month: 'numeric', day: 'numeric'
        });
        if (fmt.resolvedOptions().calendar !== 'persian') return;
    } catch (e) { return; }

    var months = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
    var weekdays = ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'];
    var fDigits = '۰۱۲۳۴۵۶۷۸۹';
    var toFa = function (v) { return String(v).replace(/[0-9]/g, function (n) { return fDigits[Number(n)]; }); };
    var pad = function (v) { return String(v).padStart(2, '0'); };
    var current = null, year, month;
    var display = {};

    function pParts(date) {
        var out = {};
        fmt.formatToParts(date).forEach(function (part) {
            if (part.type === 'year' || part.type === 'month' || part.type === 'day') {
                out[part.type] = parseInt(part.value, 10);
            }
        });
        return out;
    }

    function monthStart(py, pm) {
        var d = new Date(py + 621, 2, 21, 12, 0, 0, 0);
        d.setDate(d.getDate() + (pm - 1) * 30 - 16);
        for (var i = 0; i < 45; i++) {
            var p = pParts(d);
            if (p.year === py && p.month === pm && p.day === 1) return d;
            d.setDate(d.getDate() + 1);
        }
        throw new Error('Persian calendar conversion failed');
    }

    function localDate(date) {
        return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate())
            + 'T' + pad(date.getHours()) + ':' + pad(date.getMinutes());
    }

    function fieldDate(input) {
        var value = input.value;
        if (value && /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/.test(value)) {
            var date = new Date(value);
            if (!isNaN(date.getTime())) return date;
        }
        return new Date();
    }

    function refresh() {
        fields.forEach(function (input) {
            if (!display[input.id]) return;
            if (!input.value) { display[input.id].value = ''; return; }
            var d = fieldDate(input), p = pParts(d);
            display[input.id].value = toFa(p.year + '/' + pad(p.month) + '/' + pad(p.day)
                + '  ' + pad(d.getHours()) + ':' + pad(d.getMinutes()));
        });
    }

    var popup = document.createElement('div');
    popup.className = 'ariya-jalali-popover';
    popup.dir = 'rtl';
    popup.setAttribute('role', 'dialog');
    popup.setAttribute('aria-label', 'انتخاب تاریخ و ساعت شمسی');
    popup.hidden = true;
    document.body.appendChild(popup);

    function element(tag, name, label, parent) {
        var el = document.createElement(tag);
        if (name) el.className = name;
        if (label !== null && label !== undefined) el.textContent = label;
        parent.appendChild(el);
        return el;
    }

    function chooseDate(day) {
        var first = monthStart(year, month), original = fieldDate(current);
        first.setDate(first.getDate() + day - 1);
        var chosen = new Date(first.getFullYear(), first.getMonth(), first.getDate(),
            original.getHours(), original.getMinutes(), 0, 0);
        current.value = localDate(chosen);
        current.dispatchEvent(new Event('change', { bubbles: true }));
        refresh();
        render();
    }

    function setTime(kind, value) {
        if (!current) return;
        var d = fieldDate(current), n = Number(value);
        if (kind === 'hour') d.setHours(n);
        else d.setMinutes(n);
        current.value = localDate(d);
        current.dispatchEvent(new Event('change', { bubbles: true }));
        refresh();
    }

    function render() {
        if (!current) return;
        popup.textContent = '';
        var head = element('div', 'ariya-jalali-head', null, popup);
        var prev = element('button', 'ariya-jalali-nav', 'ماه قبل', head);
        prev.type = 'button';
        element('strong', 'ariya-jalali-month', months[month - 1] + ' ' + toFa(year), head);
        var next = element('button', 'ariya-jalali-nav', 'ماه بعد', head);
        next.type = 'button';
        prev.addEventListener('click', function () {
            month--;
            if (month < 1) { month = 12; year--; }
            render();
        });
        next.addEventListener('click', function () {
            month++;
            if (month > 12) { month = 1; year++; }
            render();
        });

        var grid = element('div', 'ariya-jalali-grid', null, popup);
        weekdays.forEach(function (day, i) {
            var w = element('span', 'ariya-jalali-weekday' + (i === 6 ? ' friday' : ''), day, grid);
            w.setAttribute('aria-hidden', 'true');
        });
        var first = monthStart(year, month);
        var nextYear = month === 12 ? year + 1 : year;
        var nextMonth = month === 12 ? 1 : month + 1;
        var end = monthStart(nextYear, nextMonth);
        var days = Math.round((end.getTime() - first.getTime()) / 86400000);
        var lead = (first.getDay() + 1) % 7;
        for (var blank = 0; blank < lead; blank++) element('span', 'ariya-jalali-blank', '', grid);
        var picked = current.value ? pParts(fieldDate(current)) : null;
        var today = pParts(new Date());
        for (var d = 1; d <= days; d++) {
            (function (day) {
                var cls = 'ariya-jalali-day';
                if (picked && picked.year === year && picked.month === month && picked.day === day) cls += ' selected';
                if (today.year === year && today.month === month && today.day === day) cls += ' today';
                if ((lead + day - 1) % 7 === 6) cls += ' friday';
                var b = element('button', cls, toFa(day), grid);
                b.type = 'button';
                b.setAttribute('aria-label', 'روز ' + toFa(day) + ' ' + months[month - 1]);
                b.addEventListener('click', function () { chooseDate(day); });
            })(d);
        }

        var dValue = fieldDate(current);
        var time = element('div', 'ariya-jalali-time', null, popup);
        element('span', null, 'ساعت:', time);
        var hour = element('select', 'ariya-jalali-time-select', null, time);
        hour.setAttribute('aria-label', 'ساعت');
        for (var h = 0; h <= 23; h++) {
            var opt = element('option', null, toFa(pad(h)), hour);
            opt.value = String(h);
        }
        hour.value = String(dValue.getHours());
        element('span', null, ':', time);
        var minute = element('select', 'ariya-jalali-time-select', null, time);
        minute.setAttribute('aria-label', 'دقیقه');
        for (var m = 0; m <= 59; m++) {
            var option = element('option', null, toFa(pad(m)), minute);
            option.value = String(m);
        }
        minute.value = String(dValue.getMinutes());
        hour.addEventListener('change', function () { setTime('hour', hour.value); });
        minute.addEventListener('change', function () { setTime('minute', minute.value); });
        var foot = element('div', 'ariya-jalali-actions', null, popup);
        var todayBtn = element('button', 'ariya-jalali-action', 'امروز', foot);
        todayBtn.type = 'button';
        todayBtn.addEventListener('click', function () {
            var now = new Date(), p = pParts(now);
            year = p.year; month = p.month;
            current.value = localDate(now);
            current.dispatchEvent(new Event('change', { bubbles: true }));
            refresh(); render();
        });
        var clear = element('button', 'ariya-jalali-action', 'پاک کردن', foot);
        clear.type = 'button';
        clear.addEventListener('click', function () {
            current.value = '';
            current.dispatchEvent(new Event('change', { bubbles: true }));
            refresh(); close();
        });
        var done = element('button', 'ariya-jalali-confirm', 'تأیید', foot);
        done.type = 'button';
        done.addEventListener('click', close);
    }

    function position() {
        if (!current || popup.hidden) return;
        var rect = display[current.id].getBoundingClientRect();
        var width = Math.min(340, window.innerWidth - 16);
        var left = Math.max(8, Math.min(rect.right - width, window.innerWidth - width - 8));
        var height = popup.offsetHeight;
        var top = rect.bottom + 5;
        if (top + height > window.innerHeight - 8) top = Math.max(8, rect.top - height - 5);
        popup.style.width = width + 'px';
        popup.style.left = left + 'px';
        popup.style.top = top + 'px';
    }

    function open(input) {
        current = input;
        var p = pParts(fieldDate(input));
        year = p.year; month = p.month;
        popup.hidden = false;
        render(); position();
    }
    function close() { popup.hidden = true; current = null; }

    fields.forEach(function (native) {
        var input = document.createElement('input');
        input.type = 'text';
        input.className = 'form-control ariya-jalali-visible';
        input.readOnly = true;
        input.dir = 'rtl';
        input.placeholder = 'انتخاب تاریخ و ساعت شمسی';
        input.setAttribute('aria-label', native.id === 'discount_starts_at' ? 'شروع تخفیف به تاریخ شمسی' : 'پایان تخفیف به تاریخ شمسی');
        input.addEventListener('click', function () { open(native); });
        native.insertAdjacentElement('afterend', input);
        display[native.id] = input;
        native.classList.add('ariya-jalali-native');
        native.addEventListener('change', refresh);
    });
    refresh();
    document.querySelectorAll('.sales-duration').forEach(function (b) {
        b.addEventListener('click', refresh);
    });
    document.addEventListener('pointerdown', function (event) {
        if (!popup.hidden && !popup.contains(event.target)
            && !Object.values(display).includes(event.target)) close();
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !popup.hidden) close();
    });
    window.addEventListener('resize', position);
    window.addEventListener('scroll', position, true);
})();
</script>
@endpush
