@extends('back.layouts.master')
@include('back.partials.plugins', ['plugins' => ['persian-datepicker']])
@push('styles')@include('back.bulk-discounts.partials.ui-v41-style')@endpush
@section('content')
<div class="app-content content" dir="rtl"><div class="content-overlay"></div><div class="header-navbar-shadow"></div><div class="content-wrapper"><div class="content-body bd4-page">
<div class="bd4-heading"><div><h3>ویرایش کمپین: {{ $campaign->title }}</h3><a href="{{ route('admin.bulk-discounts.index') }}">← بازگشت به تخفیف‌های گروهی</a></div></div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<section class="bd4-card"><div class="bd4-section-head"><span class="bd4-step">۱</span><div><h4>تنظیمات کمپین</h4><p>ویرایش مبلغ، زمان شمسی و گرد کردن قیمت همه محصولات کمپین</p></div></div>
<form method="POST" action="{{ route('admin.bulk-discounts.update',$campaign) }}" onsubmit="return confirm('این تغییرات روی همه محصولات کمپین اثر می‌گذارند. تأیید می‌کنید؟')">@csrf
<div class="row">
<div class="col-lg-5 form-group"><label>عنوان کمپین</label><input class="form-control" required name="title" value="{{ old('title',$campaign->title) }}"></div>
<div class="col-lg-3 form-group"><label>نوع تخفیف</label><select class="form-control" name="discount_type"><option value="percent" {{ old('discount_type',$campaign->discount_type)==='percent'?'selected':'' }}>درصدی</option><option value="fixed_price" {{ old('discount_type',$campaign->discount_type)==='fixed_price'?'selected':'' }}>قیمت فروش نهایی ثابت</option></select></div>
<div class="col-lg-4 form-group"><label>درصد یا قیمت نهایی</label><input type="number" min="1" required name="discount_value" class="form-control" value="{{ old('discount_value',$campaign->discount_value) }}"></div>
<div class="col-lg-6 form-group"><label>گرد کردن قیمت</label><select class="form-control" name="round_step">@foreach([0=>'بدون گرد کردن اضافی',100=>'۱۰۰ تومان',500=>'۵۰۰ تومان',1000=>'۱٬۰۰۰ تومان',5000=>'۵٬۰۰۰ تومان',10000=>'۱۰٬۰۰۰ تومان'] as $step=>$label)<option value="{{ $step }}" {{ (int)old('round_step',$campaign->round_step)===(int)$step?'selected':'' }}>{{ $label }}</option>@endforeach</select></div>
<div class="col-lg-6 form-group"><label>جهت گرد کردن</label><select class="form-control" name="round_mode"><option value="nearest" {{ old('round_mode',$campaign->round_mode)==='nearest'?'selected':'' }}>نزدیک‌ترین</option><option value="down" {{ old('round_mode',$campaign->round_mode)==='down'?'selected':'' }}>رو به پایین</option><option value="up" {{ old('round_mode',$campaign->round_mode)==='up'?'selected':'' }}>رو به بالا</option></select></div>
<div class="col-lg-6 form-group"><label>شروع شمسی (خالی = فوری)</label><input class="form-control bd4-jalali" dir="ltr" inputmode="none" autocomplete="off" name="starts_at" value="{{ old('starts_at', $campaign->starts_at ? \Morilog\Jalali\Jalalian::fromCarbon($campaign->starts_at)->format('Y/m/d H:i') : '') }}" placeholder="۱۴۰۵/۰۷/۲۸ ۱۲:۳۰"></div>
<div class="col-lg-6 form-group"><label>پایان شمسی (خالی = نامحدود)</label><input class="form-control bd4-jalali" dir="ltr" inputmode="none" autocomplete="off" name="ends_at" value="{{ old('ends_at', $campaign->ends_at ? \Morilog\Jalali\Jalalian::fromCarbon($campaign->ends_at)->format('Y/m/d H:i') : '') }}" placeholder="۱۴۰۵/۰۸/۱۰ ۲۳:۵۹"></div>
</div><button type="submit" class="btn btn-primary">ذخیره تغییرات کمپین</button></form>
<div class="bd4-button-row">
<form method="POST" action="{{ route('admin.bulk-discounts.toggle',$campaign) }}">@csrf<button class="btn {{ $campaign->enabled?'btn-warning':'btn-success' }}">{{ $campaign->enabled?'غیرفعال کردن کمپین':'فعال کردن کمپین' }}</button></form>
<form method="POST" action="{{ route('admin.bulk-discounts.destroy',$campaign) }}" onsubmit="return confirm('کل کمپین و انتساب‌های آن حذف شوند؟')">@csrf @method('DELETE')<button class="btn btn-outline-danger">حذف کل کمپین</button></form>
</div></section>
<section class="bd4-card"><h4>افزودن محصول</h4><p class="text-muted">یک یا چند محصول را از فهرست جست‌وجو انتخاب کن؛ محصول نباید در کمپین دیگری باشد.</p>
<form method="POST" action="{{ route('admin.bulk-discounts.add-products',$campaign) }}" data-bd4-add-form>@csrf
<input type="search" class="form-control mb-2" data-bd4-add-search placeholder="نام یا شناسه محصول را جست‌وجو کن">
<div class="bd4-exclude-list" data-bd4-add-list>@foreach($products as $product)<label class="bd4-exclude-row" data-bd4-add-row><input type="checkbox" name="product_ids[]" value="{{ $product->id }}"><span>{{ $product->title }}</span><small>#{{ $product->id }}</small></label>@endforeach</div>
<div class="bd4-button-row"><span data-bd4-add-count>۰ محصول انتخاب شده</span><button class="btn btn-outline-primary">افزودن محصولات</button></div></form></section>
<section class="bd4-card"><h4>محصولات کمپین ({{ number_format($items->total()) }})</h4><p class="text-muted">زمان مستقل با هر دو تاریخ خالی، برای همان محصول نامحدود است؛ در غیر این صورت از تاریخ کمپین پیروی می‌کند.</p>
<div class="table-responsive"><table class="table table-striped"><thead><tr><th>محصول</th><th>زمان‌بندی شمسی مستقل</th><th>حذف</th></tr></thead><tbody>
@forelse($items as $item)
<tr><td>{{ $item->product->title ?? 'محصول حذف‌شده' }} (#{{ $item->product_id }})</td><td>
<form method="POST" action="{{ route('admin.bulk-discounts.schedule',[$campaign,$item]) }}">@csrf
<div class="bd4-inline-date"><select class="form-control" name="timer_override"><option value="0" {{ !$item->timer_override?'selected':'' }}>پیروی از کمپین</option><option value="1" {{ $item->timer_override?'selected':'' }}>زمان مستقل</option></select>
<input class="form-control bd4-jalali" dir="ltr" autocomplete="off" inputmode="none" name="starts_at" aria-label="شروع شمسی محصول" placeholder="تاریخ شروع شمسی" value="{{ $item->starts_at ? \Morilog\Jalali\Jalalian::fromCarbon($item->starts_at)->format('Y/m/d H:i') : '' }}">
<input class="form-control bd4-jalali" dir="ltr" autocomplete="off" inputmode="none" name="ends_at" aria-label="پایان شمسی محصول" placeholder="تاریخ پایان شمسی" value="{{ $item->ends_at ? \Morilog\Jalali\Jalalian::fromCarbon($item->ends_at)->format('Y/m/d H:i') : '' }}">
<button class="btn btn-sm btn-primary">ذخیره</button></div></form></td><td><form method="POST" action="{{ route('admin.bulk-discounts.remove-product',$campaign) }}" onsubmit="return confirm('فقط تخفیف گروهی این محصول حذف شود؟')">@csrf<input type="hidden" name="product_id" value="{{ $item->product_id }}"><button class="btn btn-sm btn-outline-danger">حذف</button></form></td></tr>
@empty<tr><td colspan="3">محصولی در این کمپین وجود ندارد.</td></tr>@endforelse
</tbody></table></div>{{ $items->links() }}</section>
</div></div></div>
@endsection
@push('scripts')@include('back.bulk-discounts.partials.ui-v41-script')@endpush
