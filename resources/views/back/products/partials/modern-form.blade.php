@php
    $formProduct = $formProduct ?? null;
    $isEdit = $isEdit ?? false;
    $isPhysical = !$formProduct || $formProduct->isPhysical();
    $selectedCategoryId = $formProduct ? $formProduct->category_id : null;
    $selectedCategories = $formProduct ? $formProduct->categories()->pluck('categories.id')->toArray() : [];
    $productCode = $formProduct ? data_get($formProduct, 'product_code') : null;
@endphp

<div class="apf-modern" dir="rtl">
    <div class="apf-page-head">
        <div>
            <h2>{{ $isEdit ? 'ویرایش محصول' : 'ایجاد محصول جدید' }}</h2>
            <p>{{ $isEdit ? 'تمام اطلاعات محصول، قیمت‌ها، تنوع‌ها و تنظیمات انتشار را از همین صفحه مدیریت کنید.' : 'اطلاعات محصول را تکمیل کنید؛ تنظیمات پیشرفته و تنوع‌ها نیز در همین صفحه در دسترس هستند.' }}</p>
        </div>
        @if ($isEdit && $formProduct)
            <a target="_blank" href="{{ $formProduct->link() }}" class="btn btn-outline-primary apf-view-product">
                <i class="feather icon-external-link"></i>
                مشاهده محصول
            </a>
        @endif
    </div>

    <div class="row apf-layout-row">
        <aside class="col-xl-4 col-lg-4 col-12 apf-sidebar-column">
            <div class="apf-sticky-sidebar">
                <section class="apf-card apf-side-card">
                    <div class="apf-card-title">
                        <span></span>
                        <h4>انتشار</h4>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block apf-primary-submit">
                        <i class="feather icon-check"></i>
                        {{ $isEdit ? 'ثبت تغییرات' : 'ثبت محصول' }}
                    </button>

                    <div class="form-group mt-2">
                        <label>وضعیت</label>
                        <select name="published" class="form-control">
                            <option value="1" {{ !$formProduct || $formProduct->published ? 'selected' : '' }}>منتشر شود</option>
                            <option value="0" {{ $formProduct && !$formProduct->published ? 'selected' : '' }}>پیش‌نویس</option>
                        </select>
                    </div>

                    @if ($isEdit)
                        <div class="form-group">
                            <label>تاریخ ایجاد</label>
                            <input autocomplete="off" type="text" data-timestamps="true" class="form-control persian-date-picker ltr" name="created_at" value="{{ jdate($formProduct->created_at)->getTimestamp() }}">
                        </div>
                    @endif

                    <div class="form-group">
                        <label>زمان انتشار</label>
                        @if ($isEdit)
                            <input id="publish_date" autocomplete="off" type="text" data-timestamps="true" class="form-control persian-date-picker ltr" name="publish_date" value="{{ $formProduct->publish_date ? jdate($formProduct->publish_date)->getTimestamp() : jdate($formProduct->created_at)->getTimestamp() }}">
                        @else
                            <input autocomplete="off" type="text" class="form-control" id="publish_date_picker" placeholder="انتخاب تاریخ و ساعت">
                            <input type="hidden" name="publish_date" id="publish_date">
                        @endif
                    </div>

                    <div class="apf-switch-row">
                        <div>
                            <strong>محصول ویژه</strong>
                            <small>نمایش در بخش محصولات ویژه</small>
                        </div>
                        <label class="apf-switch">
                            <input type="checkbox" name="special" {{ $formProduct && $formProduct->special ? 'checked' : '' }}>
                            <span></span>
                        </label>
                    </div>

                    <div id="special-end-date-container" class="form-group mt-1" style="display:none;">
                        <label>تاریخ پایان ویژه بودن</label>
                        <input type="text" name="special_end_date" class="form-control persian-date-picker" data-timestamps="true" value="{{ $formProduct && $formProduct->special_end_date ? jdate($formProduct->special_end_date)->getTimestamp() : '' }}">
                    </div>
                </section>

                <section class="apf-card apf-side-card physical-item">
                    <div class="apf-card-title">
                        <span></span>
                        <h4>قیمت‌گذاری</h4>
                    </div>
                    <div class="form-group">
                        <label>واحد پول</label>
                        <select name="currency_id" class="form-control">
                            <option data-amount="1" data-title="تومان" value="">تومان (پیش‌فرض)</option>
                            @foreach ($currencies as $currency)
                                <option data-amount="{{ $currency->amount }}" data-title="{{ $currency->title }}" value="{{ $currency->id }}" {{ $formProduct ? ($formProduct->currency && $formProduct->currency->id == $currency->id ? 'selected' : '') : (option('default_currency_id') == $currency->id ? 'selected' : '') }}>{{ $currency->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>گرد کردن قیمت</label>
                        <select name="rounding_amount" class="form-control">
                            <option data-value="{{ option('default_rounding_amount', 'no') }}" value="default" {{ $formProduct && $formProduct->rounding_amount == 'default' ? 'selected' : '' }}>پیش‌فرض</option>
                            <option data-value="no" value="no" {{ $formProduct && $formProduct->rounding_amount == 'no' ? 'selected' : '' }}>بدون گرد کردن</option>
                            <option data-value="100" value="100" {{ $formProduct && $formProduct->rounding_amount == '100' ? 'selected' : '' }}>۱۰۰ تومان</option>
                            <option data-value="1000" value="1000" {{ $formProduct && $formProduct->rounding_amount == '1000' ? 'selected' : '' }}>۱,۰۰۰ تومان</option>
                            <option data-value="10000" value="10000" {{ $formProduct && $formProduct->rounding_amount == '10000' ? 'selected' : '' }}>۱۰,۰۰۰ تومان</option>
                            <option data-value="100000" value="100000" {{ $formProduct && $formProduct->rounding_amount == '100000' ? 'selected' : '' }}>۱۰۰,۰۰۰ تومان</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label>نحوه گرد کردن</label>
                        <select name="rounding_type" class="form-control">
                            <option data-value="{{ option('default_rounding_type', 'close') }}" value="default" {{ $formProduct && $formProduct->rounding_type == 'default' ? 'selected' : '' }}>پیش‌فرض</option>
                            <option data-value="close" value="close" {{ $formProduct && $formProduct->rounding_type == 'close' ? 'selected' : '' }}>نزدیک‌ترین مقدار</option>
                            <option data-value="up" value="up" {{ $formProduct && $formProduct->rounding_type == 'up' ? 'selected' : '' }}>رو به بالا</option>
                            <option data-value="down" value="down" {{ $formProduct && $formProduct->rounding_type == 'down' ? 'selected' : '' }}>رو به پایین</option>
                        </select>
                    </div>
                </section>

                <section class="apf-card apf-side-card">
                    <div class="apf-card-title">
                        <span></span>
                        <h4>تنظیمات نمایش</h4>
                    </div>
                    <div class="apf-switch-row">
                        <div>
                            <strong>نوع محصول</strong>
                            <small>فیزیکی یا دانلودی</small>
                        </div>
                    </div>
                    <select name="type" id="product-type" class="form-control mt-1">
                        <option value="physical" {{ $isPhysical ? 'selected' : '' }}>محصول فیزیکی</option>
                        <option value="download" {{ !$isPhysical ? 'selected' : '' }}>محصول دانلودی</option>
                    </select>
                </section>

                <section class="apf-card apf-side-card">
                    <div class="apf-card-title">
                        <span></span>
                        <h4>تنظیمات دیگر</h4>
                    </div>
                    @if ($productCode)
                        <div class="apf-code-box">
                            <small>کد خودکار انبار</small>
                            <strong class="ltr">{{ $productCode }}</strong>
                            <button type="button" class="btn btn-sm btn-outline-primary apf-copy-code" data-code="{{ $productCode }}">کپی</button>
                        </div>
                    @else
                        <p class="text-muted mb-1">کد انبار پس از اولین ذخیره محصول به‌صورت خودکار ساخته می‌شود.</p>
                    @endif

                    <div class="form-group mt-1">
                        <label>هشدار موجودی</label>
                        <input type="number" class="form-control" name="stock_alert" min="0" value="{{ $formProduct ? data_get($formProduct, 'stock_alert', 0) : 0 }}" placeholder="مثلاً 5">
                        <small class="text-muted">وقتی موجودی به این عدد برسد در پنل قابل پیگیری است.</small>
                    </div>
                    <div class="apf-switch-row mt-1">
                        <div>
                            <strong>قابل شارژ</strong>
                            <small>امکان شارژ یا افزایش اعتبار این محصول</small>
                        </div>
                        <label class="apf-switch">
                            <input type="checkbox" name="is_rechargeable" value="1" {{ $formProduct && data_get($formProduct, 'is_rechargeable') ? 'checked' : '' }}>
                            <span></span>
                        </label>
                    </div>

                    @if ($isEdit && option('stock-amount-increased_sms') == 'on')
                        <div class="apf-switch-row mt-1">
                            <div>
                                <strong>اعلان موجود شدن</strong>
                                <small>ارسال پیامک به کاربران منتظر</small>
                            </div>
                            <label class="apf-switch">
                                <input type="hidden" name="stock_increase_sms" value="false">
                                <input type="checkbox" name="stock_increase_sms" value="true">
                                <span></span>
                            </label>
                        </div>
                    @endif
                </section>
            </div>
        </aside>

        <main class="col-xl-8 col-lg-8 col-12 apf-main-column">
            <section class="apf-card" id="apf-product-info">
                <div class="apf-card-title">
                    <span></span>
                    <h4>اطلاعات محصول</h4>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>نام محصول <b class="text-danger">*</b></label>
                            <input type="text" class="form-control" name="title" value="{{ old('title', $formProduct ? $formProduct->title : '') }}" placeholder="نام محصول">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>عنوان انگلیسی</label>
                            <input type="text" class="form-control ltr" name="title_en" value="{{ old('title_en', $formProduct ? $formProduct->title_en : '') }}" placeholder="English title">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>SKU / کد انبار خودکار</label>
                            <input type="text" class="form-control ltr bg-light" value="{{ $productCode ?: 'بعد از ثبت محصول ساخته می‌شود' }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>بارکد محصول</label>
                            <input type="text" class="form-control ltr" name="barcode" value="{{ $formProduct ? data_get($formProduct, 'barcode') : '' }}" placeholder="اختیاری">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>دسته‌بندی اصلی <b class="text-danger">*</b></label>
                            <select class="form-control product-category" name="category_id">
                                <option value="">انتخاب کنید</option>
                                @foreach ($categories as $category)
                                    <option class="l{{ $category->parents()->count() + 1 }} {{ $category->categories()->count() ? 'non-leaf' : '' }}" data-pup="{{ $category->category_id }}" value="{{ $category->id }}" {{ $selectedCategoryId == $category->id ? 'selected' : '' }}>{{ $category->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>دسته‌بندی‌های تکمیلی</label>
                            <select class="form-control product-categories" name="categories[]" multiple>
                                @foreach ($categories as $category)
                                    <option class="l{{ $category->parents()->count() + 1 }} {{ $category->categories()->count() ? 'non-leaf' : '' }}" data-pup="{{ $category->category_id }}" value="{{ $category->id }}" {{ in_array($category->id, $selectedCategories) ? 'selected' : '' }}>{{ $category->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 physical-item">
                        <div class="form-group">
                            <label>برند</label>
                            <input id="brand" type="text" class="form-control" name="brand" value="{{ $formProduct && $formProduct->brand ? $formProduct->brand->name : '' }}" placeholder="انتخاب یا ایجاد برند">
                        </div>
                    </div>
                    <div class="col-md-4 physical-item">
                        <div class="form-group">
                            <label>واحد <b class="text-danger">*</b></label>
                            <input type="text" class="form-control" name="unit" value="{{ $formProduct ? $formProduct->unit : 'تعداد' }}" placeholder="مثلاً عدد، بسته یا کارتن">
                        </div>
                    </div>
                    <div class="col-md-4 physical-item">
                        <div class="form-group">
                            <label>وزن (گرم) <b class="text-danger">*</b></label>
                            <input type="number" class="form-control" name="weight" value="{{ $formProduct ? $formProduct->weight : 0 }}" min="0">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <fieldset class="form-group">
                            <label>برچسب‌ها</label>
                            <input type="text" name="labels" class="form-control labels" data-action="{{ route('admin.get-labels') }}" value="{{ $formProduct ? $formProduct->getLabels() : '' }}">
                        </fieldset>
                    </div>
                    <div class="col-12">
                        <div class="form-group mb-0">
                            <label>توضیحات کوتاه</label>
                            <textarea class="form-control" name="short_description" rows="3" placeholder="خلاصه‌ای کوتاه برای کارت و ابتدای صفحه محصول">{{ $formProduct ? $formProduct->short_description : '' }}</textarea>
                        </div>
                    </div>
                </div>
            </section>

            <section class="apf-card">
                <div class="apf-card-title">
                    <span></span>
                    <h4>توضیحات</h4>
                </div>
                <textarea id="description" class="form-control" name="description">{{ $formProduct ? $formProduct->description : '' }}</textarea>
            </section>

            <section class="apf-card">
                <div class="apf-card-title">
                    <span></span>
                    <h4>عکس‌ها</h4>
                </div>
                <div class="row">
                    <div class="col-12">
                        <label>گالری محصول <small class="text-muted">بهترین اندازه: {{ config('front.imageSizes.productGalleryImage') }} پیکسل</small></label>
                        <div class="dropzone dropzone-area mb-2" id="product-images">
                            <div class="dz-message">
                                <i class="feather icon-image"></i>
                                تصاویر را بکشید و اینجا رها کنید یا برای انتخاب کلیک کنید
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <fieldset class="form-group">
                            <label>تصویر شاخص</label>
                            @if ($isEdit && $formProduct->image)
                                <div class="apf-current-image mb-1"><img src="{{ asset($formProduct->image) }}" alt="{{ $formProduct->title }}"></div>
                            @endif
                            <div class="custom-file">
                                <input id="image" type="file" accept="image/*" name="image" class="custom-file-input">
                                <label class="custom-file-label" for="image">انتخاب تصویر</label>
                            </div>
                        </fieldset>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>متن جایگزین تصویر</label>
                            <input type="text" class="form-control" name="image_alt" value="{{ $formProduct ? $formProduct->image_alt : '' }}">
                        </div>
                    </div>
                </div>
            </section>

            <section class="apf-card">
                <div class="apf-card-title">
                    <span></span>
                    <h4>ویدیو محصول</h4>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>کاور ویدیو</label>
                            @if ($formProduct && data_get($formProduct, 'video_cover'))
                                <div class="apf-current-image mb-1"><img src="{{ asset(data_get($formProduct, 'video_cover')) }}" alt="کاور ویدیو"></div>
                            @endif
                            <div class="custom-file">
                                <input id="video_cover" type="file" accept="image/*" name="video_cover" class="custom-file-input">
                                <label class="custom-file-label" for="video_cover">انتخاب تصویر کاور</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>لینک ویدیو</label>
                            <input type="text" class="form-control ltr" name="video_url" value="{{ $formProduct ? data_get($formProduct, 'video_url') : '' }}" placeholder="لینک آپارات، یوتیوب یا فایل ویدیو">
                        </div>
                    </div>
                </div>
            </section>

            <section class="apf-card physical-item" id="product-prices-section">
                <div class="apf-card-title apf-title-with-help">
                    <span></span>
                    <div>
                        <h4>تنوع‌ها و قیمت‌ها</h4>
                        <p>قیمت، تخفیف، موجودی، محدودیت سفارش، مدل‌ها و کدهای خودکار انبار را مدیریت کنید.</p>
                    </div>
                </div>

                <div class="product-prices-tab">
                    @if (view()->exists('back.products.partials.bulk-prices-builder'))
                        @include('back.products.partials.bulk-prices-builder')
                    @endif

                    <div id="product-prices-div" class="product-prices-div">
                        @if ($formProduct && $formProduct->isPhysical())
                            @foreach ($formProduct->prices as $price)
                                @include('back.products.partials.prices-include', ['price' => $price])
                            @endforeach
                        @endif
                    </div>

                    <div class="apf-inline-actions">
                        <button id="add-product-prices" type="button" class="btn btn-primary"><i class="feather icon-plus"></i> افزودن قیمت</button>
                        <button id="copy-product-prices" type="button" class="btn btn-outline-primary"><i class="feather icon-copy"></i> کپی آخرین قیمت</button>
                    </div>
                </div>
            </section>

            <section class="apf-card download-item" id="product-download-files">
                <div class="apf-card-title">
                    <span></span>
                    <h4>فایل‌های محصول دانلودی</h4>
                </div>
                <div id="product-files-area">
                    @if ($formProduct && $formProduct->isDownload())
                        @foreach ($formProduct->prices()->orderBy('ordering')->get() as $price)
                            @include('back.products.partials.files-include', ['price' => $price])
                        @endforeach
                    @endif
                </div>
                <div class="apf-inline-actions">
                    <button id="add-product-file" type="button" class="btn btn-primary"><i class="feather icon-plus"></i> افزودن فایل</button>
                </div>
            </section>

            <section class="apf-card" id="specifications-card">
                <div class="apf-card-title apf-title-with-help">
                    <span></span>
                    <div>
                        <h4>مشخصات محصول</h4>
                        <p>مشخصات قابل مقایسه محصول را گروه‌بندی و ثبت کنید.</p>
                    </div>
                </div>
                <div class="row align-items-end">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>نوع مشخصات</label>
                            <input id="specifications_type" class="form-control" name="spec_type" placeholder="مثلاً گوشی موبایل" value="{{ $formProduct && $formProduct->specType ? $formProduct->specType->name : '' }}">
                        </div>
                    </div>
                </div>

                <div id="specifications-area">
                    @if ($isEdit && $formProduct)
                        @foreach ($formProduct->specificationGroups->unique() as $group)
                            <div class="row mt-2 specification-group">
                                <div class="col-12">
                                    <div class="row group-row">
                                        <div class="col-md-2"><span>نام گروه</span></div>
                                        <div class="col-md-10 form-group">
                                            <input type="text" class="form-control group-input" data-group_name="{{ $loop->index }}" name="specification_group[{{ $loop->index }}][name]" placeholder="مثال: مشخصات کلی" value="{{ $group->name }}" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="all-specifications col-12">
                                    @foreach ($formProduct->specifications()->where('specification_group_id', $group->id)->get() as $specification)
                                        <div class="single-specificition">
                                            <div class="row align-items-end">
                                                <div class="col-md-1">
                                                    <label class="apf-mini-check"><input name="specification_group[{{ $loop->parent->index }}][specifications][{{ $loop->index }}][special]" type="checkbox" {{ $specification->pivot->special ? 'checked' : '' }}> ویژه</label>
                                                </div>
                                                <div class="col-md-4 form-group">
                                                    <label>عنوان</label>
                                                    <input type="text" class="form-control spec-label" name="specification_group[{{ $loop->parent->index }}][specifications][{{ $loop->index }}][name]" value="{{ $specification->name }}" required>
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label>مقدار</label>
                                                    <textarea class="form-control spec-label" rows="1" name="specification_group[{{ $loop->parent->index }}][specifications][{{ $loop->index }}][value]" required>{{ $specification->pivot->value }}</textarea>
                                                </div>
                                                <div class="col-md-1"><button type="button" class="btn btn-flat-danger remove-specification custom-padding"><i class="feather icon-minus"></i></button></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="col-12 apf-inline-actions">
                                    <button type="button" class="btn btn-sm btn-success add-specifaction">افزودن مشخصات</button>
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-group">حذف گروه</button>
                                </div>
                            </div>
                        @endforeach
                    @elseif (!empty($autoFillData))
                        @foreach ($autoFillData['specificationGroups'] as $group)
                            <div class="row mt-2 specification-group">
                                <div class="col-12">
                                    <div class="row group-row">
                                        <div class="col-md-2"><span>نام گروه</span></div>
                                        <div class="col-md-10 form-group">
                                            <input type="text" class="form-control group-input" data-group_name="{{ $loop->index }}" name="specification_group[{{ $loop->index }}][name]" value="{{ $group['name'] }}" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="all-specifications col-12">
                                    @foreach ($group['specifications'] as $specification)
                                        <div class="single-specificition">
                                            <div class="row align-items-end">
                                                <div class="col-md-1"><label class="apf-mini-check"><input name="specification_group[{{ $loop->parent->index }}][specifications][{{ $loop->index }}][special]" type="checkbox" {{ $specification['special'] ? 'checked' : '' }}> ویژه</label></div>
                                                <div class="col-md-4 form-group"><label>عنوان</label><input type="text" class="form-control spec-label" name="specification_group[{{ $loop->parent->index }}][specifications][{{ $loop->index }}][name]" value="{{ $specification['name'] }}" required></div>
                                                <div class="col-md-6 form-group"><label>مقدار</label><textarea class="form-control spec-label" rows="1" name="specification_group[{{ $loop->parent->index }}][specifications][{{ $loop->index }}][value]" required>{!! $specification['value'] !!}</textarea></div>
                                                <div class="col-md-1"><button type="button" class="btn btn-flat-danger remove-specification custom-padding"><i class="feather icon-minus"></i></button></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="col-12 apf-inline-actions"><button type="button" class="btn btn-sm btn-success add-specifaction">افزودن مشخصات</button><button type="button" class="btn btn-sm btn-outline-danger remove-group">حذف گروه</button></div>
                            </div>
                        @endforeach
                    @endif
                </div>
                <div class="apf-inline-actions">
                    <button id="add-product-specification-group" type="button" class="btn btn-primary"><i class="feather icon-plus"></i> افزودن گروه مشخصات</button>
                </div>
            </section>

            <section class="apf-card physical-item apf-nested-section">
                <div class="apf-card-title">
                    <span></span>
                    <h4>سایز و راهنمای اندازه</h4>
                </div>
                @include('back.products.partials.sizes-tab', ['product' => $formProduct])
            </section>

            @if ($isEdit)
                <section class="apf-card apf-nested-section">
                    <div class="apf-card-title">
                        <span></span>
                        <h4>اطلاعات ترب</h4>
                    </div>
                    @include('back.products.partials.torob-tab', ['product' => $formProduct])
                </section>
            @endif

            <section class="apf-card">
                <div class="apf-card-title">
                    <span></span>
                    <h4>اطلاعات سئو</h4>
                </div>
                <div class="row">
                    <div class="col-md-6"><div class="form-group"><label>عنوان سئو</label><input type="text" class="form-control" name="meta_title" value="{{ $formProduct ? $formProduct->meta_title : '' }}"></div></div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>آدرس صفحه</label>
                            <input id="slug" type="text" class="form-control ltr" name="slug" value="{{ $formProduct ? $formProduct->slug : '' }}">
                            <small><a id="generate-product-slug" href="#">ایجاد خودکار</a> <span id="slug-spinner" class="spinner-grow spinner-grow-sm text-success" style="display:none;"></span></small>
                        </div>
                    </div>
                    <div class="col-md-6"><div class="form-group"><label>توضیحات سئو</label><textarea class="form-control" name="meta_description" rows="4">{{ $formProduct ? $formProduct->meta_description : '' }}</textarea></div></div>
                    <div class="col-md-6"><fieldset class="form-group"><label>کلمات کلیدی</label><input type="text" name="tags" class="form-control tags" data-action="{{ route('admin.get-tags') }}" value="{{ $formProduct ? $formProduct->getTags : option('product_default_tags') }}"></fieldset></div>
                </div>
            </section>

            <div class="apf-bottom-submit">
                <button type="submit" class="btn btn-primary btn-lg"><i class="feather icon-check"></i> {{ $isEdit ? 'ثبت تغییرات محصول' : 'ثبت محصول' }}</button>
            </div>
        </main>
    </div>
</div>
