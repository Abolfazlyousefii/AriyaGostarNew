@extends('back.layouts.master')

@section('content')

    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-9 col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <div class="breadcrumb-wrapper col-12">
                                <ol class="breadcrumb no-border">
                                    <li class="breadcrumb-item">مدیریت
                                    </li>
                                    <li class="breadcrumb-item">تنظیمات
                                    </li>
                                    <li class="breadcrumb-item active">تنظیمات دیگر
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <!-- users edit start -->
                <section class="users-edit">
                    <div class="card">
                        <div id="main-card" class="card-content">
                            <div class="card-body">
                                <div class="tab-content">
                                    <form id="others-form" action="{{ route('admin.settings.others') }}" method="POST">
                                        <div class="card border-primary mb-2" style="border-width: 1px !important;">
                                            <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                                                <div>
                                                    <h4 class="card-title mb-25">صفحه «به‌زودی» سایت</h4>
                                                    <p class="mb-0 text-muted">با فعال‌سازی این حالت، همه صفحات عمومی سایت برای کاربران با صفحه معرفی موقت جایگزین می‌شوند. پنل مدیریت همچنان در دسترس خواهد بود.</p>
                                                </div>
                                                @if(option('site_coming_soon_enabled', 'off') === 'on')
                                                    <span class="badge badge-pill badge-warning mt-50 mt-md-0">در حال نمایش</span>
                                                @else
                                                    <span class="badge badge-pill badge-success mt-50 mt-md-0">سایت در دسترس است</span>
                                                @endif
                                            </div>

                                            <div class="card-body pt-1">
                                                <div class="alert alert-info mb-2" role="alert">
                                                    مسیر ورود و تمام صفحات پنل مدیریت از این محدودیت مستثنا هستند؛ بنابراین هر زمان بخواهید می‌توانید این حالت را خاموش کنید.
                                                </div>

                                                <div class="row">
                                                    <div class="col-lg-3 col-md-6 col-12">
                                                        <div class="form-group">
                                                            <label for="site-coming-soon-enabled">وضعیت صفحه</label>
                                                            <select id="site-coming-soon-enabled" name="site_coming_soon_enabled" class="form-control">
                                                                <option value="off" {{ option('site_coming_soon_enabled', 'off') === 'off' ? 'selected' : '' }}>غیرفعال؛ نمایش کامل سایت</option>
                                                                <option value="on" {{ option('site_coming_soon_enabled', 'off') === 'on' ? 'selected' : '' }}>فعال؛ نمایش صفحه به‌زودی</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-9 col-md-6 col-12">
                                                        <div class="form-group">
                                                            <label for="site-coming-soon-title">عنوان اصلی</label>
                                                            <input id="site-coming-soon-title" type="text" name="site_coming_soon_title" class="form-control" maxlength="120" value="{{ option('site_coming_soon_title', 'آریا گستر؛ به‌زودی با تجربه‌ای تازه') }}">
                                                        </div>
                                                    </div>

                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="site-coming-soon-description">توضیحات صفحه</label>
                                                            <textarea id="site-coming-soon-description" name="site_coming_soon_description" rows="3" maxlength="500" class="form-control">{{ option('site_coming_soon_description', 'در حال آماده‌سازی نسخه جدید وب‌سایت آریا گستر هستیم تا خرید عمده لوازم جانبی را سریع‌تر، ساده‌تر و حرفه‌ای‌تر کنیم. خیلی زود با محصولات و امکانات تازه در کنار شما خواهیم بود.') }}</textarea>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-8 col-md-7 col-12">
                                                        <div class="form-group">
                                                            <label for="site-coming-soon-note">متن پایانی</label>
                                                            <input id="site-coming-soon-note" type="text" name="site_coming_soon_note" class="form-control" maxlength="180" value="{{ option('site_coming_soon_note', 'از همراهی و شکیبایی شما سپاسگزاریم.') }}">
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-4 col-md-5 col-12">
                                                        <div class="form-group">
                                                            <label for="site-coming-soon-phone">شماره تماس</label>
                                                            <input id="site-coming-soon-phone" type="text" name="site_coming_soon_phone" class="form-control ltr text-right" maxlength="30" value="{{ option('site_coming_soon_phone', '90005202') }}" placeholder="90005202">
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-3 col-md-6 col-12">
                                                        <div class="form-group">
                                                            <label for="site-coming-soon-instagram">آیدی اینستاگرام</label>
                                                            <input id="site-coming-soon-instagram" type="text" name="site_coming_soon_instagram" class="form-control ltr text-right" maxlength="100" value="{{ option('site_coming_soon_instagram', 'ariyajanebi.ir') }}" placeholder="ariyajanebi.ir">
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-3 col-md-6 col-12">
                                                        <div class="form-group">
                                                            <label for="site-coming-soon-whatsapp">شماره واتساپ</label>
                                                            <input id="site-coming-soon-whatsapp" type="text" name="site_coming_soon_whatsapp" class="form-control ltr text-right" maxlength="30" value="{{ option('site_coming_soon_whatsapp', '09055019120') }}" placeholder="09055019120">
                                                            <small class="text-muted">لینک مستقیم شروع گفت‌وگو به‌صورت خودکار ساخته می‌شود.</small>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-3 col-md-6 col-12">
                                                        <div class="form-group">
                                                            <label for="site-coming-soon-bale">آیدی یا لینک بله</label>
                                                            <input id="site-coming-soon-bale" type="text" name="site_coming_soon_bale" class="form-control ltr text-right" maxlength="150" value="{{ option('site_coming_soon_bale', '09055019120') }}" placeholder="09055019120 یا لینک کامل">
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-3 col-md-6 col-12">
                                                        <div class="form-group">
                                                            <label for="site-coming-soon-rubika">آیدی یا لینک روبیکا</label>
                                                            <input id="site-coming-soon-rubika" type="text" name="site_coming_soon_rubika" class="form-control ltr text-right" maxlength="150" value="{{ option('site_coming_soon_rubika', '09055019120') }}" placeholder="09055019120 یا لینک کامل">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="d-flex flex-wrap align-items-center justify-content-between">
                                                    <small class="text-muted">بعد از ذخیره، وضعیت جدید بدون نیاز به تغییر کد اعمال می‌شود.</small>
                                                    <div class="mt-1 mt-md-0">
                                                        @if(option('site_coming_soon_enabled', 'off') === 'on')
                                                            <a href="{{ url('/') }}" target="_blank" rel="noopener" class="btn btn-outline-primary mr-50">پیش‌نمایش صفحه</a>
                                                        @endif
                                                        <button type="submit" class="btn btn-primary glow">ذخیره وضعیت سایت</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <h4 class="mt-2">تنظیمات قیمت ها</h4>
                                        <div class="row">
                                            <div class="col-md-3 col-12">
                                                <div class="form-group">
                                                    <label>انتخاب ارز پیش فرض</label>
                                                    <select name="default_currency_id" class="form-control">
                                                        <option value="">تومان (پیش فرض)</option>
                                                        @foreach ($currencies as $currency)
                                                            <option value="{{ $currency->id }}" {{ option('default_currency_id') == $currency->id ? 'selected' : '' }}>{{ $currency->title }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-12">
                                                <div class="form-group">
                                                    <label>گرد کردن</label>
                                                    <select name="default_rounding_amount" class="form-control">
                                                        <option value="no" {{ option('default_rounding_amount', 'no') == 'no' ? 'selected' : '' }}>خیر</option>
                                                        <option value="100" {{ option('default_rounding_amount') == 100 ? 'selected' : '' }}>100 تومان</option>
                                                        <option value="1000" {{ option('default_rounding_amount') == 1000 ? 'selected' : '' }}>1000 تومان</option>
                                                        <option value="10000" {{ option('default_rounding_amount') == 10000 ? 'selected' : '' }}>10000 تومان</option>
                                                        <option value="100000" {{ option('default_rounding_amount') == 100000 ? 'selected' : '' }}>100000 تومان</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>نحوه گرد کردن</label>
                                                    <select name="default_rounding_type" class="form-control">
                                                        <option value="close" {{ option('default_rounding_type', 'close') == 'close' ? 'selected' : '' }}>نزدیک</option>
                                                        <option value="up" {{ option('default_rounding_type') == 'up' ? 'selected' : '' }}>رو به بالا</option>
                                                        <option value="down" {{ option('default_rounding_type') == 'down' ? 'selected' : '' }}>رو به پایین</option>
                                                    </select>
                                                </div>
                                            </div>

                                        </div>

                                        <h4 class="mt-2">تنظیمات فاکتور سفارشات</h4>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <fieldset class="form-group">
                                                    <label for="">لوگو</label>
                                                    <div class="custom-file">
                                                        <input type="file" accept="image/*" name="factor_logo" class="custom-file-input">
                                                        <label class="custom-file-label" for="">{{ option('factor_logo') }}</label>
                                                    </div>
                                                </fieldset>
                                            </div>
                                            <div class="col-md-3">
                                                <label>عنوان فاکتور</label>
                                                <div class="input-group mb-75">
                                                    <input type="text" name="factor_title" class="form-control" value="{{ option('factor_title', option('info_site_title')) }}">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label>فروشنده</label>
                                                <div class="input-group mb-75">
                                                    <input type="text" name="factor_seller_name" class="form-control" value="{{ option('factor_seller_name') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label>شناسه ملی</label>
                                                <div class="input-group mb-75">
                                                    <input type="text" name="factor_national_code" class="form-control" value="{{ option('factor_national_code') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label>شناسه ثبت</label>
                                                <div class="input-group mb-75">
                                                    <input type="text" name="factor_registeration_id" class="form-control" value="{{ option('factor_registeration_id') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label>شماره اقتصادی</label>
                                                <div class="input-group mb-75">
                                                    <input type="text" name="factor_economical_number" class="form-control" value="{{ option('factor_economical_number') }}">
                                                </div>
                                            </div>

                                        </div>

                                        <h4 class="mt-2">تنظیمات فاکتور سفارش حضوری</h4>
                                        <div class="row">

                                            <div class="col-md-3">
                                                <label>عنوان فاکتور</label>
                                                <div class="input-group mb-75">
                                                    <input type="text" name="in_person_factor_title" class="form-control" value="{{ option('in_person_factor_title') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <label>درصد مالیات</label>
                                                <div class="input-group mb-75">
                                                    <input type="text" name="in_person_factor_tax" class="form-control" value="{{ option('in_person_factor_tax') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <label>محاسبه مالیات بصورت پیشفرض</label>
                                                <div class="input-group mb-75">
                                                    <select name="in_person_tax" class="form-control">
                                                        <option value="0" {{ option('in_person_tax', 0) == 0 ? 'selected' : '' }}>خیر</option>
                                                        <option value="1" {{ option('in_person_tax', 0) == 1 ? 'selected' : '' }}>بله</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-5">
                                                <label>متن فوتر</label>
                                                <div class="input-group mb-75">
                                                    <textarea name="in_person_factor_footer" class="form-control" rows="3">{{ option('in_person_factor_footer') }}</textarea>
                                                </div>
                                            </div>

                                        </div>

                                        <h4 class="mt-2">تنظیمات مربوط به کاربران</h4>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label>اعتبار هدیه ثبت نام کاربر</label>
                                                <div class="input-group mb-75">
                                                    <input type="number" name="user_register_gift_credit" class="form-control" min="0" value="{{ option('user_register_gift_credit', 0) }}">
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <label>فعال کردن امکان معرفی افراد</label>
                                                <div class="input-group mb-75">
                                                    <select name="user_refrral_enable" class="form-control">
                                                        <option value="0" {{ option('user_refrral_enable', 0) == 0 ? 'selected' : '' }}>خیر</option>
                                                        <option value="1" {{ option('user_refrral_enable', 1) == 1 ? 'selected' : '' }}>بله</option>
                                                    </select>
                                                </div>


                                            </div>

                                            <div class="col-md-3">
                                                <label> مقدار تخفیف معرفی کننده به درصد</label>
                                                <div class="input-group mb-75">
                                                    <input type="number" name="owner_refrral_amount" class="form-control" min="0" value="{{ option('owner_refrral_amount', 0) }}">
                                                </div>

                                            </div>
                                            <div class="col-md-3">

                                                <label> مقدار تخفیف معرفی شونده به درصد</label>
                                                <div class="input-group mb-75">
                                                    <input type="number" name="user_refrral_amount" class="form-control" min="0" value="{{ option('user_refrral_amount', 0) }}">
                                                </div>

                                            </div>
                                        </div>

                                        <h4 class="mt-2">تنظیمات pusher</h4>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label>PUSHER_APP_ID</label>
                                                <div class="input-group mb-75">
                                                    <input type="text" name="PUSHER_APP_ID" class="form-control ltr" value="{{ config('broadcasting.connections.pusher.app_id') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label>PUSHER_APP_KEY</label>
                                                <div class="input-group mb-75">
                                                    <input type="text" name="PUSHER_APP_KEY" class="form-control ltr" value="{{ config('broadcasting.connections.pusher.key') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label>PUSHER_APP_SECRET</label>
                                                <div class="input-group mb-75">
                                                    <input type="text" name="PUSHER_APP_SECRET" class="form-control ltr" value="{{ config('broadcasting.connections.pusher.secret') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label>PUSHER_APP_CLUSTER</label>
                                                <div class="input-group mb-75">
                                                    <input type="text" name="PUSHER_APP_CLUSTER" class="form-control ltr" value="{{ config('broadcasting.connections.pusher.options.cluster') }}">
                                                </div>
                                            </div>

                                        </div>

                                        <h4 class="mt-2">مخفی کردن سایت از موتور های جستجو</h4>
                                        <span class="text-danger small">
                                            <i class="feather icon-alert-octagon"></i>
                                            غیرفعال کردن این گزینه موجب عدم نمایش سایت در موتور های جست و جو میشود.
                                        </span>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label> در صفحه اصلی سایت</label>

                                                <select name="allow_indexing_main_page" class="form-control">
                                                    <option value="on"  {{ option('allow_indexing_main_page') == "on" ? 'selected' : '' }}>فعال</option>
                                                    <option value="off" {{ option('allow_indexing_main_page') == "off" ? 'selected' : '' }} >غیر فعال</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label> در لیست محصولات سایت</label>
                                                <select name="allow_indexing_product_page" class="form-control">
                                                    <option value="on"  {{ option('allow_indexing_product_page') == "on" ? 'selected' : '' }}>فعال</option>
                                                    <option value="off" {{ option('allow_indexing_product_page') == "off" ? 'selected' : '' }}>غیر فعال</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label> در لیست بلاگ های سایت</label>
                                                <select name="allow_indexing_blog_page" class="form-control">
                                                    <option value="on"  {{ option('allow_indexing_blog_page') == "on" ? 'selected' : '' }}>فعال</option>
                                                    <option value="off" {{ option('allow_indexing_blog_page') == "off" ? 'selected' : '' }}>غیر فعال</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label> در صفحات دسته بندی ها و زیر دسته ها</label>
                                                <select name="allow_indexing_categories" class="form-control">
                                                    <option value="on"  {{ option('allow_indexing_categories') == "on" ? 'selected' : '' }}>فعال</option>
                                                    <option value="off" {{ option('allow_indexing_categories') == "off" ? 'selected' : '' }}>غیر فعال</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label> در صفحه برند ها</label>
                                                <select name="allow_indexing_brands" class="form-control">
                                                    <option value="on"  {{ option('allow_indexing_brands') == "on" ? 'selected' : '' }}>فعال</option>
                                                    <option value="off" {{ option('allow_indexing_brands') == "off" ? 'selected' : '' }}>غیر فعال</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label> در صفحه درباره ما</label>
                                                <select name="allow_indexing_contact" class="form-control">
                                                    <option value="on"  {{ option('allow_indexing_contact') == "on" ? 'selected' : '' }}>فعال</option>
                                                    <option value="off" {{ option('allow_indexing_contact') == "off" ? 'selected' : '' }}>غیر فعال</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-12 d-flex flex-sm-row flex-column justify-content-end mt-1">
                                                <button type="submit" class="btn btn-primary glow">ذخیره تغییرات</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- users edit ends -->

            </div>
        </div>
    </div>

@endsection

@include('back.partials.plugins', ['plugins' => ['jquery.validate']])

@push('scripts')
    <script src="{{ asset('back/assets/js/pages/settings/others.js') }}"></script>
@endpush
