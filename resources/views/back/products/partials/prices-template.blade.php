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

        <div class="col-12">
            <div class="alert alert-light-primary border-primary mb-2">
                <label class="font-weight-bold">کد خودکار نرم‌افزار انبار</label>
                <input type="text" class="form-control ltr" value="بعد از ذخیره محصول به صورت خودکار ساخته می‌شود" readonly>
                <small class="text-muted">هیچ کدی وارد نکنید؛ سایت بعد از ذخیره این ردیف، کد یکتا تولید می‌کند.</small>
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
                <label>قیمت خرید</label>
                <input type="number" data-unit="تومان" class="form-control amount-input purchase-price" name="purchase_price" min="0" value="0">
            </div>
        </div>
        <div class="col-md-3 col-12">
            <div class="form-group">
                <label>بارکد تنوع</label>
                <input type="text" class="form-control ltr" name="barcode" placeholder="اختیاری">
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
                <label>موجودی دریافتی از API انبار</label>
                <input type="number" class="form-control stock bg-light" name="stock" value="0" min="0" readonly required>
                <small class="text-muted">موجودی اولیه صفر است و فقط API آن را تغییر می‌دهد.</small>
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
</script>

<script>
    if (!window.__ariyaInventoryCodeCopyBound) {
        window.__ariyaInventoryCodeCopyBound = true;

        document.addEventListener('click', function (event) {
            var button = event.target.closest('.copy-generated-inventory-code');
            if (!button) return;

            var code = button.getAttribute('data-code') || '';
            var done = function () {
                var oldHtml = button.innerHTML;
                button.textContent = 'کپی شد';
                setTimeout(function () { button.innerHTML = oldHtml; }, 1200);
            };

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(code).then(done);
                return;
            }

            var temp = document.createElement('textarea');
            temp.value = code;
            temp.style.position = 'fixed';
            temp.style.opacity = '0';
            document.body.appendChild(temp);
            temp.select();
            document.execCommand('copy');
            temp.remove();
            done();
        });
    }
</script>
