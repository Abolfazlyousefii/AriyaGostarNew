@php
    $inventoryCode = $price->inventoryApiCode();
    $usesVariantCode = $price->product->usesVariantInventoryCodes();
@endphp

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

    <div class="col-12">
        <div class="alert alert-light-primary border-primary mb-2">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <label class="font-weight-bold">{{ $usesVariantCode ? 'کد خودکار این مدل برای نرم‌افزار انبار' : 'کد خودکار این محصول برای نرم‌افزار انبار' }}</label>
                    <div class="input-group ltr">
                        <input type="text" class="form-control inventory-code-readonly" value="{{ $inventoryCode }}" readonly>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-primary copy-generated-inventory-code" data-code="{{ $inventoryCode }}">
                                <i class="feather icon-copy"></i> کپی کد
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 mt-1 mt-lg-0">
                    <small class="text-muted">
                        این کد توسط سایت تولید شده و قابل تغییر نیست. همین کد را داخل نرم‌افزار انبار وارد کنید تا API موجودی این کالا را برگرداند.
                    </small>
                </div>
            </div>
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
            <label>قیمت خرید</label>
            <input type="number" data-unit="تومان" class="form-control amount-input purchase-price" name="prices[{{ $loop->iteration }}][purchase_price]" value="{{ data_get($price, 'purchase_price', 0) }}" min="0">
        </div>
    </div>
    <div class="col-md-3 col-12">
        <div class="form-group">
            <label>بارکد تنوع</label>
            <input type="text" class="form-control ltr" name="prices[{{ $loop->iteration }}][barcode]" value="{{ data_get($price, 'barcode') }}" placeholder="اختیاری">
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
            <label>موجودی دریافتی از API انبار</label>
            <input type="number" class="form-control stock bg-light" name="prices[{{ $loop->iteration }}][stock]" value="{{ $price->stock }}" min="0" readonly required>
            <small class="text-muted">موجودی از داخل پنل قابل تغییر نیست.</small>
        </div>
    </div>
    <div class="col-md-3 col-12">
        <div class="form-group">
            <label>وضعیت آخرین همگام‌سازی</label>
            <div class="form-control bg-light" style="height:auto;min-height:38px">
                @if($price->stock_sync_error)
                    <span class="text-danger">{{ $price->stock_sync_error }}</span>
                @elseif($price->stock_synced_at)
                    <span class="text-success">{{ jdate($price->stock_synced_at)->ago() }}</span>
                @else
                    <span class="text-muted">هنوز از API دریافت نشده</span>
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
        <button type="button" class="btn btn-flat-danger waves-effect waves-light remove-product-price custom-padding">حذف</button>
    </div>

    <div class="col-md-12"><hr></div>
</div>
