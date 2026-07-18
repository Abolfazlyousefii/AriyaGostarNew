<script id="prices-template" type="text/x-custom-template">
    <div class="row animated fadeIn single-price">

        <div class="col-12">
            <div class="row">
                @foreach ($attributeGroups as $attributeGroup)
                    <div class="col-md-3 col-12">
                        <div class="form-group">
                            <label>{{ $attributeGroup->name }}</label>
                            <select class="form-control price-attribute-select select2" data-group-id="{{ $attributeGroup->id }}" data-type="{{ $attributeGroup->type }}" name="attribute">
                                <option value="">انتخاب کنید</option>
                                @foreach ($attributeGroup->get_attributes()->orderBy('ordering')->orderBy('name')->get() as $attribute)
                                    <option value="{{ $attribute->id }}">{{ $attribute->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endforeach

                <input type="hidden" class="price-image" name="price_image">
            </div>
        </div>

        <div class="col-md-3 col-12">
            <div class="form-group">
                <label>قیمت</label>
                <input type="number" data-unit="تومان" class="form-control amount-input price" name="price" required>
            </div>
        </div>

        <div class="col-md-3 col-12">
            <div class="form-group">
                <label>تخفیف</label>
                <input type="number" class="form-control discount" name="discount" min="0" max="100" placeholder="%">
            </div>
        </div>
        <div class="col-md-3 col-12">
            <div class="form-group">
                <label>زمان انقضای تخفیف</label>
                <input type="text" class="form-control discount_expire_at persian-date-picker" data-timestamps="true" name="discount_expire_at">
            </div>
        </div>

        <div class="col-md-3 col-12">
            <div class="form-group">
                <label>بیشترین تعداد مجاز در هر سفارش</label>
                <input type="number" class="form-control cart_max" name="cart_max" min="1">
            </div>
        </div>
        <div class="col-md-3 col-12">
            <div class="form-group">
                <label>کمترین تعداد مجاز در هر سفارش</label>
                <input type="number" class="form-control cart_min" name="cart_min" min="1">
            </div>
        </div>
        <div class="col-md-3 col-12">
            <div class="form-group">
                <label>موجودی انبار</label>
                <input type="number" class="form-control stock" name="stock" min="0" required>
            </div>
        </div>
        <div class="col-md-4 col-12">
            <div class="form-group">
                <label>کد متغیر در نرم‌افزار انبار</label>
                <input type="text" class="form-control external-stock-code" name="external_stock_code" maxlength="191" placeholder="مثلاً GD-IP13-001" dir="ltr">
                <small class="text-muted">این کد بعداً برای دریافت موجودی همین مدل از API استفاده می‌شود.</small>
            </div>
        </div>
        <div class="col-md-4 col-12">
            <div class="form-group mt-md-2 pt-md-1">
                <input type="hidden" class="stock-sync-enabled-hidden" name="stock_sync_enabled_hidden" value="0">
                <div class="custom-control custom-switch custom-switch-success mr-1 mb-1">
                    <input type="checkbox" class="custom-control-input stock-sync-enabled" name="stock_sync_enabled" value="1">
                    <label class="custom-control-label stock-sync-enabled-label">موجودی این مدل از API انبار خوانده شود</label>
                </div>
                <small class="text-muted">تا زمان اتصال API، موجودی دستی بالا به‌عنوان مقدار فعلی حفظ می‌شود.</small>
            </div>
        </div>
        <div class="col-md-4 col-12">
            <div class="form-group">
                <label>وضعیت آخرین همگام‌سازی</label>
                <div class="form-control bg-light h-auto warehouse-sync-status">
                    <span class="text-muted">هنوز همگام‌سازی نشده است.</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-12">
            <div class="form-group">
                <label>قیمت نهایی</label>
                <input type="text" class="form-control final-price" disabled>
            </div>
        </div>

        <div class="col-md-12">
            <button type="button" class="btn btn-flat-danger waves-effect waves-light remove-product-price custom-padding">حذف</i></button>
        </div>

        <div class="col-md-12"><hr></div>
    </div>
</script>
