<div class="row single-price">

    <div class="col-12">
        <div class="row">
            @foreach ($attributeGroups as $attributeGroup)
                <div class="col-md-3 col-12">
                    <div class="form-group">
                        <label>{{ $attributeGroup->name }}</label>
                        <select class="form-control price-attribute-select select2" data-group-id="{{ $attributeGroup->id }}" data-type="{{ $attributeGroup->type }}" data-number="{{ $loop->parent->iteration }}" name="prices[{{ $loop->parent->iteration }}][attributes][]">
                            <option value="">انتخاب کنید</option>
                            @foreach ($attributeGroup->get_attributes->sortBy('name')->sortBy('ordering') as $attribute)
                                <option value="{{ $attribute->id }}" {{ $price->get_attributes->find($attribute->id) ? 'selected' : '' }}>{{ $attribute->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endforeach

            <input type="hidden" data-number="{{ $loop->iteration }}" value="{{ $price->image->image ?? '' }}" class="price-image" name="prices[{{ $loop->iteration }}][image]">
        </div>
    </div>

    <div class="col-md-3 col-12">
        <div class="form-group">
            <label>قیمت</label>
            <input type="number" data-unit="تومان" class="form-control amount-input price" name="prices[{{ $loop->iteration }}][price]" value="{{ $price->price() }}" required>
        </div>
    </div>

    <div class="col-md-3 col-12">
        <div class="form-group">
            <label>تخفیف</label>
            <input type="number" class="form-control discount" name="prices[{{ $loop->iteration }}][discount]" value="{{ $price->discount }}" min="0" max="100" placeholder="%">
        </div>
    </div>
    <div class="col-md-3 col-12">
        <div class="form-group">
            <label>زمان انقضای تخفیف</label>
            <input type="text" class="form-control discount_expire_at persian-date-picker" data-timestamps="true" name="prices[{{ $loop->iteration }}][discount_expire_at]" value="{{ $price->discount_expire_at ? jdate($price->discount_expire_at)->getTimestamp() : '' }}">
        </div>
    </div>

    <div class="col-md-3 col-12">
        <div class="form-group">
            <label>بیشترین تعداد مجاز در هر سفارش</label>
            <input type="number" class="form-control cart_max" name="prices[{{ $loop->iteration }}][cart_max]" value="{{ $price->cart_max }}" min="1">
        </div>
    </div>
    <div class="col-md-3 col-12">
        <div class="form-group">
            <label>کمترین تعداد مجاز در هر سفارش</label>
            <input type="number" class="form-control cart_min" name="prices[{{ $loop->iteration }}][cart_min]" value="{{ $price->cart_min }}" min="1">
        </div>
    </div>
    <div class="col-md-3 col-12">
        <div class="form-group">
            <label>موجودی انبار</label>
            <input type="number" class="form-control stock" name="prices[{{ $loop->iteration }}][stock]" value="{{ $price->stock }}" min="0" required>
        </div>
    </div>
    <div class="col-md-4 col-12">
        <div class="form-group">
            <label>کد متغیر در نرم‌افزار انبار</label>
            <input type="text" class="form-control external-stock-code" name="prices[{{ $loop->iteration }}][external_stock_code]" value="{{ $price->external_stock_code }}" maxlength="191" placeholder="مثلاً GD-IP13-001" dir="ltr">
            <small class="text-muted">این کد بعداً برای دریافت موجودی همین مدل از API استفاده می‌شود.</small>
        </div>
    </div>
    <div class="col-md-4 col-12">
        <div class="form-group mt-md-2 pt-md-1">
            <input type="hidden" name="prices[{{ $loop->iteration }}][stock_sync_enabled]" value="0">
            <div class="custom-control custom-switch custom-switch-success mr-1 mb-1">
                <input type="checkbox" class="custom-control-input stock-sync-enabled" id="stock-sync-enabled-{{ $loop->iteration }}" name="prices[{{ $loop->iteration }}][stock_sync_enabled]" value="1" {{ $price->stock_sync_enabled ? 'checked' : '' }}>
                <label class="custom-control-label" for="stock-sync-enabled-{{ $loop->iteration }}">موجودی این مدل از API انبار خوانده شود</label>
            </div>
            <small class="text-muted">تا زمان اتصال API، موجودی دستی بالا به‌عنوان مقدار فعلی حفظ می‌شود.</small>
        </div>
    </div>
    <div class="col-md-4 col-12">
        <div class="form-group">
            <label>وضعیت آخرین همگام‌سازی</label>
            <div class="form-control bg-light h-auto warehouse-sync-status">
                @if ($price->stock_sync_error)
                    <span class="text-danger">{{ $price->stock_sync_error }}</span>
                @elseif ($price->stock_synced_at)
                    <span class="text-success">موفق: {{ jdate($price->stock_synced_at)->format('Y/m/d H:i') }}</span>
                @else
                    <span class="text-muted">هنوز همگام‌سازی نشده است.</span>
                @endif
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
