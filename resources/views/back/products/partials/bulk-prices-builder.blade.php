@php
    $bulkPriceGroups = $attributeGroups->map(function ($group) {
        return [
            'id' => (string) $group->id,
            'name' => $group->name,
            'attributes' => $group->get_attributes()
                ->orderBy('ordering')
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(function ($attribute) {
                    return [
                        'id' => (string) $attribute->id,
                        'name' => $attribute->name,
                    ];
                })
                ->values(),
        ];
    })->values();
@endphp

<div id="bulk-prices-builder" class="card border border-primary mb-2">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
        <div>
            <h4 class="card-title mb-50">ساخت گروهی مدل‌ها و قیمت‌ها</h4>
            <p class="mb-0 text-muted">مدل‌ها را یکجا انتخاب کنید، قیمت مشترک بدهید و فقط مدل‌های متفاوت را در بخش استثناها تغییر دهید.</p>
        </div>
        <span class="badge badge-light-primary mt-50 mt-md-0">مناسب گارد و محصولات متغیر</span>
    </div>

    <div class="card-body">
        <div class="alert alert-info" role="alert">
            این ابزار ردیف‌های عادی قیمت محصول را برای شما می‌سازد. بعد از تولید مدل‌ها، برای ذخیره نهایی حتماً دکمه ایجاد یا ویرایش محصول را بزنید.
        </div>

        <div class="row">
            <div class="col-lg-4 col-md-6 col-12">
                <div class="form-group">
                    <label for="bulk-price-group">گروه مدل <span class="text-danger">*</span></label>
                    <select id="bulk-price-group" class="form-control">
                        <option value="">انتخاب کنید</option>
                        @foreach ($bulkPriceGroups as $group)
                            <option value="{{ $group['id'] }}">{{ $group['name'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-lg-8 col-md-6 col-12">
                <div class="form-group">
                    <label for="bulk-price-models">مدل‌ها <span class="text-danger">*</span></label>
                    <select id="bulk-price-models" class="form-control select2" multiple disabled></select>
                    <div class="mt-50">
                        <button id="bulk-price-select-all" type="button" class="btn btn-sm btn-outline-primary" disabled>انتخاب همه</button>
                        <button id="bulk-price-clear-models" type="button" class="btn btn-sm btn-outline-secondary" disabled>پاک کردن انتخاب‌ها</button>
                        <small id="bulk-price-model-count" class="text-muted mr-1">۰ مدل انتخاب شده</small>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-12">
                <div class="form-group">
                    <label for="bulk-default-price">قیمت مشترک <span class="text-danger">*</span></label>
                    <input id="bulk-default-price" type="number" min="0" class="form-control amount-input" data-unit="تومان" placeholder="مثلاً 250000">
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-12">
                <div class="form-group">
                    <label for="bulk-default-stock">موجودی مشترک <span class="text-danger">*</span></label>
                    <input id="bulk-default-stock" type="number" min="0" class="form-control" value="0">
                </div>
            </div>

            <div class="col-lg-2 col-md-4 col-12">
                <div class="form-group">
                    <label for="bulk-default-discount">تخفیف مشترک</label>
                    <input id="bulk-default-discount" type="number" min="0" max="100" class="form-control" placeholder="%">
                </div>
            </div>

            <div class="col-lg-2 col-md-4 col-12">
                <div class="form-group">
                    <label for="bulk-default-cart-min">حداقل سفارش</label>
                    <input id="bulk-default-cart-min" type="number" min="1" class="form-control">
                </div>
            </div>

            <div class="col-lg-2 col-md-4 col-12">
                <div class="form-group">
                    <label for="bulk-default-cart-max">حداکثر سفارش</label>
                    <input id="bulk-default-cart-max" type="number" min="1" class="form-control">
                </div>
            </div>

            <div class="col-lg-8 col-12">
                <div class="form-group">
                    <label for="bulk-external-stock-codes">کدهای متغیر نرم‌افزار انبار</label>
                    <textarea id="bulk-external-stock-codes" class="form-control" rows="5" dir="ltr" placeholder="iPhone 13 | GD-IP13-001&#10;iPhone 14 | GD-IP14-001"></textarea>
                    <small class="text-muted d-block mt-50">هر خط: نام مدل | کد متغیر. جداکننده‌های | ، = و Tab پشتیبانی می‌شوند. می‌توانید فقط کدها را هم به ترتیب مدل‌های انتخاب‌شده در هر خط وارد کنید.</small>
                </div>
            </div>

            <div class="col-lg-4 col-12">
                <div class="form-group mt-lg-2 pt-lg-1">
                    <div class="custom-control custom-switch custom-switch-success mr-1 mb-1">
                        <input type="checkbox" class="custom-control-input" id="bulk-stock-sync-enabled">
                        <label class="custom-control-label" for="bulk-stock-sync-enabled">فعال‌سازی دریافت موجودی از API برای مدل‌های دارای کد</label>
                    </div>
                    <small class="text-muted">تا زمان اتصال API، موجودی مشترک یا استثنا به‌عنوان موجودی فعلی ذخیره می‌شود.</small>
                </div>
            </div>
        </div>

        <hr>

        <div class="d-flex flex-wrap align-items-center justify-content-between mb-1">
            <div>
                <h5 class="mb-25">استثناهای قیمت و موجودی</h5>
                <p class="mb-0 text-muted">فقط مدل‌هایی را اضافه کنید که قیمت یا موجودی آن‌ها با مقدار مشترک فرق دارد.</p>
            </div>
            <button id="bulk-add-price-exception" type="button" class="btn btn-sm btn-outline-warning mt-50 mt-md-0" disabled>
                <i class="feather icon-plus"></i> افزودن استثنا
            </button>
        </div>

        <div id="bulk-price-exceptions"></div>

        <div class="row mt-2">
            <div class="col-12 text-center">
                <button id="bulk-generate-prices" type="button" class="btn btn-primary" disabled>
                    <i class="feather icon-zap"></i> تولید یا به‌روزرسانی قیمت مدل‌ها
                </button>
            </div>
        </div>
    </div>
</div>

<script id="bulk-price-groups-data" type="application/json">{!! json_encode($bulkPriceGroups, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
