@extends('back.layouts.master')

@include('back.partials.plugins', ['plugins' => ['persian-datepicker']])
@push('styles')
@include('back.bulk-discounts.partials.ui-v41-style')
@endpush

@section('content')
<div class="app-content content" dir="rtl">
    <div class="content-overlay"></div><div class="header-navbar-shadow"></div>
    <div class="content-wrapper"><div class="content-body bd4-page">
        <div class="bd4-heading">
            <div><h3>مدیریت تخفیف‌های گروهی</h3><p>به‌صورت امن روی همه محصولات یا شاخه‌های دلخواه تخفیف اعمال کن؛ بدون تغییر قیمت اصلی یا تخفیف دستی محصول.</p></div>
            <div class="bd4-summary">{{ number_format($products->count()) }} <span>محصول سایت</span></div>
        </div>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger"><strong>درخواست انجام نشد:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form method="POST" action="{{ route('admin.bulk-discounts.submit') }}" id="ariya-bulk-discount-form" autocomplete="off">
            @csrf
            <section class="bd4-card">
                <div class="bd4-section-head"><span class="bd4-step">۱</span><div><h4>تنظیمات تخفیف</h4><p>عنوان، نوع تخفیف و شیوه گرد کردن قیمت نهایی</p></div></div>
                <div class="row">
                    <div class="col-lg-5 form-group"><label>عنوان کمپین</label><input class="form-control" required maxlength="150" name="title" value="{{ $form['title'] ?? '' }}" placeholder="مثلاً جشنواره ویژه گاردها"></div>
                    <div class="col-lg-3 form-group"><label>نوع تخفیف</label><select class="form-control" name="discount_type" required><option value="percent" {{ ($form['discount_type'] ?? 'percent') === 'percent' ? 'selected' : '' }}>درصدی (%)</option><option value="fixed_price" {{ ($form['discount_type'] ?? '') === 'fixed_price' ? 'selected' : '' }}>قیمت فروش نهایی ثابت</option></select></div>
                    <div class="col-lg-4 form-group"><label>درصد یا قیمت نهایی (تومان)</label><input class="form-control" type="number" min="1" step="1" required name="discount_value" value="{{ $form['discount_value'] ?? '' }}" placeholder="مثلاً ۱۵"></div>
                    <div class="col-lg-6 form-group"><label>میزان گرد کردن قیمت نهایی</label><select class="form-control" name="round_step" required>@foreach([0=>'بدون گرد کردن اضافی',100=>'۱۰۰ تومان',500=>'۵۰۰ تومان',1000=>'۱٬۰۰۰ تومان',5000=>'۵٬۰۰۰ تومان',10000=>'۱۰٬۰۰۰ تومان'] as $step=>$label)<option value="{{ $step }}" {{ (int)($form['round_step'] ?? 0)===(int)$step ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></div>
                    <div class="col-lg-6 form-group"><label>جهت گرد کردن</label><select class="form-control" name="round_mode" required><option value="nearest" {{ ($form['round_mode'] ?? 'nearest')==='nearest' ? 'selected':'' }}>نزدیک‌ترین</option><option value="down" {{ ($form['round_mode'] ?? '')==='down' ? 'selected':'' }}>رو به پایین</option><option value="up" {{ ($form['round_mode'] ?? '')==='up' ? 'selected':'' }}>رو به بالا</option></select></div>
                </div>
                <p class="bd4-hint">گرد کردن بعد از محاسبه تخفیف انجام می‌شود. قیمت از قیمت عادی بیشتر نخواهد شد و تخفیف دستی ارزان‌تر حفظ می‌شود.</p>
            </section>

            <section class="bd4-card">
                <div class="bd4-section-head"><span class="bd4-step">۲</span><div><h4>زمان‌بندی شمسی</h4><p>تاریخ‌ها به شمسی نمایش داده می‌شوند؛ ذخیره‌سازی استاندارد میلادی است.</p></div></div>
                <div class="row">
                    <div class="col-lg-6 form-group"><label>شروع (خالی = بلافاصله)</label><input class="form-control bd4-jalali" dir="ltr" autocomplete="off" inputmode="none" name="starts_at" value="{{ $form['starts_at'] ?? '' }}" placeholder="۱۴۰۵/۰۷/۲۸ ۱۲:۳۰"><small class="text-muted">روز / ماه / سال شمسی + ساعت</small></div>
                    <div class="col-lg-6 form-group"><label>پایان (خالی = نامحدود)</label><input class="form-control bd4-jalali" dir="ltr" autocomplete="off" inputmode="none" name="ends_at" value="{{ $form['ends_at'] ?? '' }}" placeholder="۱۴۰۵/۰۸/۱۰ ۲۳:۵۹"><small class="text-muted">می‌توانی این فیلد را خالی بگذاری.</small></div>
                </div>
            </section>

            <section class="bd4-card">
                <div class="bd4-section-head"><span class="bd4-step">۳</span><div><h4>انتخاب محصولات و دسته‌بندی‌ها</h4><p>بدون نیاز به Ctrl؛ انتخاب سریع، جست‌وجو و مستثناکردن محصولات</p></div></div>
                <div class="bd4-scope-switch">
                    <label><input type="radio" name="scope" value="all" {{ ($form['scope'] ?? 'all') === 'all' ? 'checked' : '' }}> <span>تمام محصولات سایت</span></label>
                    <label><input type="radio" name="scope" value="categories" {{ ($form['scope'] ?? '') === 'categories' ? 'checked' : '' }}> <span>دسته‌بندی و زیردسته‌ها</span></label>
                    <label><input type="radio" name="scope" value="products" {{ ($form['scope'] ?? '') === 'products' ? 'checked' : '' }}> <span>محصولات مشخص</span></label>
                </div>
                <div class="bd4-all-note" data-for-scope="all">تمام {{ number_format($products->count()) }} محصول فعلی شامل این کمپین می‌شوند؛ می‌توانی تعدادی را در بخش استثناها حذف کنی.</div>
                <div class="bd4-browser" data-for-scope="categories">
                    <div class="bd4-browser-head"><strong>درخت دسته‌بندی‌ها</strong><span>انتخاب والد، زیرمجموعه‌ها را هم شامل می‌شود.</span></div>
                    <div class="bd4-toolbar"><input type="search" class="form-control" placeholder="جست‌وجوی دسته‌بندی..." data-bd4-search="category"><button type="button" class="btn btn-outline-primary btn-sm" data-bd4-all-categories>انتخاب همه</button><button type="button" class="btn btn-outline-secondary btn-sm" data-bd4-clear-categories>لغو همه</button><button type="button" class="btn btn-light btn-sm" data-bd4-expand="category">بازکردن همه</button><button type="button" class="btn btn-light btn-sm" data-bd4-collapse="category">بستن همه</button></div>
                    <div class="bd4-tree" data-bd4-tree="category">@foreach($rootCategories as $root) @include('back.bulk-discounts.category-node', ['categoryNode'=>$root, 'depth'=>0, 'productsMode'=>false]) @endforeach</div>
                    <div class="bd4-selected-note"><span data-bd4-count="category">۰ دسته انتخاب شده</span></div>
                </div>
                <div class="bd4-browser" data-for-scope="products">
                    <div class="bd4-browser-head"><strong>انتخاب محصولات از داخل دسته‌ها</strong><span>می‌توانی یک شاخه کامل را انتخاب کنی و سپس بعضی محصولات را برداری.</span></div>
                    <div class="bd4-toolbar"><input type="search" class="form-control" placeholder="جست‌وجو بر اساس نام یا شماره محصول..." data-bd4-search="product"><button type="button" class="btn btn-outline-primary btn-sm" data-bd4-select-visible>انتخاب نتایج جست‌وجو</button><button type="button" class="btn btn-outline-secondary btn-sm" data-bd4-clear-products>لغو همه</button><button type="button" class="btn btn-light btn-sm" data-bd4-expand="product">بازکردن همه</button><button type="button" class="btn btn-light btn-sm" data-bd4-collapse="product">بستن همه</button></div>
                    <div class="bd4-tree" data-bd4-tree="product">
                        @foreach($rootCategories as $root) @include('back.bulk-discounts.category-node', ['categoryNode'=>$root, 'depth'=>0, 'productsMode'=>true]) @endforeach
                        @if($productsByCategory->get(null, collect())->isNotEmpty())
                            <details class="bd4-node" data-bd4-category open><summary class="bd4-node-head">محصولات بدون دسته اصلی</summary><div class="bd4-node-children">
                                @foreach($productsByCategory->get(null, collect()) as $p)<label class="bd4-product-row" data-bd4-product-row><input type="checkbox" data-bd4-product-choice name="product_ids[]" value="{{ $p->id }}" {{ in_array($p->id, (array)($form['product_ids'] ?? [])) ? 'checked' : '' }}><span>{{ $p->title }}</span><small>#{{ $p->id }}</small></label>@endforeach
                            </div></details>
                        @endif
                    </div>
                    <div class="bd4-selected-note"><span data-bd4-count="product">۰ محصول انتخاب شده</span></div>
                </div>
                <div class="bd4-exclusion">
                    <div><strong>محصولات مستثنا از تخفیف</strong><p>اگر روی همه محصولات یا یک دسته تخفیف می‌دهی، اینجا محصولات استثنا را جست‌وجو و تیک بزن.</p></div>
                    <input class="form-control" type="search" placeholder="جست‌وجوی محصول برای استثنا..." data-bd4-search="exclude">
                    <div class="bd4-exclude-list" data-bd4-tree="exclude">
                        @foreach($products as $product)
                        <label class="bd4-exclude-row" data-bd4-exclude-row><input type="checkbox" name="exclude_product_ids[]" value="{{ $product->id }}" {{ in_array($product->id, (array)($form['exclude_product_ids'] ?? [])) ? 'checked' : '' }}><span>{{ $product->title }}</span><small>#{{ $product->id }}</small></label>
                        @endforeach
                    </div>
                    <div class="bd4-selected-note"><span data-bd4-count="exclude">۰ محصول مستثنا</span><button type="button" class="btn btn-link btn-sm" data-bd4-clear-excludes>حذف همه استثناها</button></div>
                </div>
                <label class="bd4-replace"><input type="checkbox" name="replace_existing" value="1" {{ !empty($form['replace_existing']) ? 'checked' : '' }}> جایگزینی تخفیف گروهی قبلی محصولات تکراری <small>(تعداد تداخل در پیش‌نمایش نشان داده می‌شود)</small></label>
            </section>

            <section class="bd4-card bd4-action-card">
                <p>تخفیف‌های مستقیم صفحه ویرایش محصول تغییر نمی‌کنند. محصولات جدید پس از ساخت کمپین به‌طور خودکار اضافه نمی‌شوند.</p>
                <button name="step" value="preview" type="submit" class="btn btn-primary">۱. پیش‌نمایش قیمت‌ها و تعداد محصولات</button>
                @if($preview)
                    <div class="bd4-preview">
                        <h5>پیش‌نمایش محاسبات</h5>
                        <p>{{ number_format($preview['products']) }} محصول، {{ number_format($preview['prices']) }} ردیف قیمت، {{ number_format($preview['conflicts']) }} تداخل با کمپین‌های دیگر</p>
                        <div class="table-responsive"><table class="table table-striped table-sm"><thead><tr><th>محصول نمونه</th><th>کد تنوع</th><th>قیمت اصلی (تومان)</th><th>قیمت پیشنهادی (تومان)</th></tr></thead><tbody>
                        @foreach($preview['samples'] as $s)<tr><td>{{ $s['product'] }}</td><td>#{{ $s['variant_id'] }}</td><td>{{ number_format($s['before']) }}</td><td><strong>{{ number_format($s['after']) }}</strong></td></tr>@endforeach
                        </tbody></table></div>
                        <button name="step" value="apply" type="submit" class="btn btn-success" onclick="return confirm('تأیید نهایی: تخفیف واقعی روی محصولات انتخاب‌شده اعمال شود؟')">۲. تأیید و اعمال واقعی تخفیف</button>
                    </div>
                @endif
            </section>
        </form>
        <section class="bd4-card"><div class="bd4-section-head"><span class="bd4-step">۴</span><div><h4>کمپین‌های موجود</h4><p>مدیریت، غیرفعال کردن، زمان‌بندی هر محصول و حذف تخفیف‌ها</p></div></div>
            <div class="table-responsive"><table class="table table-striped"><thead><tr><th>عنوان</th><th>نوع تخفیف</th><th>مقدار</th><th>تعداد محصولات</th><th>وضعیت</th><th>مدیریت</th></tr></thead><tbody>
            @forelse($campaigns as $campaign)<tr><td>{{ $campaign->title }}</td><td>{{ $campaign->discount_type === 'percent' ? 'درصدی' : 'قیمت ثابت' }}</td><td>{{ number_format($campaign->discount_value) }} {{ $campaign->discount_type === 'percent' ? '٪' : 'تومان' }}</td><td>{{ number_format($campaign->items_count) }}</td><td><span class="badge badge-{{ $campaign->enabled?'success':'secondary' }}">{{ $campaign->enabled?'فعال':'غیرفعال' }}</span></td><td><a class="btn btn-outline-primary btn-sm" href="{{ route('admin.bulk-discounts.edit',$campaign) }}">مدیریت کمپین</a></td></tr>
            @empty<tr><td colspan="6">هنوز کمپینی ایجاد نشده است.</td></tr>@endforelse
            </tbody></table></div>{{ $campaigns->links() }}
        </section>
    </div></div>
</div>
@endsection
@push('scripts')@include('back.bulk-discounts.partials.ui-v41-script')@endpush
