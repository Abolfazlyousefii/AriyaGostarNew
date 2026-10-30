{{-- ARIYA_TASK01_STAGE05C_VARIANT_LIST_V1 --}}
@push('styles')
<style>
/* Stage 5: read-only guard discovery, no prices or cart operations. */
.ariya-qw-catalog { margin-top: 28px; }
.ariya-qw-catalog h2 { font-size: 20px; font-weight: 800; color: #0f172a; margin: 0 0 8px; }
.ariya-qw-catalog__intro { color: #64748b; font-size: 13px; line-height: 2; margin-bottom: 16px; }
.ariya-qw-catalog__list { display: grid; gap: 11px; }
.ariya-qw-catalog__item { display: flex; justify-content: space-between; gap: 14px; align-items: center; padding: 15px; border: 1px solid #e2e8f0; border-radius: 13px; background: #fbfdff; }
.ariya-qw-catalog__details { flex: 1; min-width: 0; }
.ariya-qw-catalog__details strong { display: block; color: #0f172a; font-size: 15px; line-height: 1.8; overflow-wrap: anywhere; }
.ariya-qw-catalog__details small { display: block; color: #64748b; line-height: 1.9; }
.ariya-qw-catalog__stock { flex-shrink: 0; color: #166534; background: #dcfce7; border-radius: 8px; font-size: 12px; padding: 7px 9px; white-space: nowrap; }
.ariya-qw-catalog__notice { margin-top: 15px; color: #475569; background: #f1f5f9; padding: 13px; border-radius: 10px; line-height: 2; }
/* Stage 6 display-only: thumbnail and authoritative server price. */
.ariya-qw-catalog__image { width: 96px; height: 96px; object-fit: contain; flex: none; border: 1px solid #e2e8f0; border-radius: 11px; background: #fff; }
.ariya-qw-catalog__meta { flex: none; display: flex; flex-direction: column; gap: 9px; align-items: flex-end; }
.ariya-qw-catalog__price { display: flex; align-items: baseline; flex-wrap: wrap; justify-content: flex-end; gap: 5px; color: #0f172a; direction: rtl; }
.ariya-qw-catalog__price strong { font-size: 18px; font-weight: 800; }
.ariya-qw-catalog__price del { color: #94a3b8; font-size: 12px; }
.ariya-qw-catalog__price span { font-size: 12px; color: #64748b; }
/* Stage 7: GET-only filtering by verified product group, deterministic read-only sorting. */
.ariya-qw-filters { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 12px; padding: 14px; margin: 14px 0; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; }
.ariya-qw-filters__field { display: flex; flex-direction: column; gap: 6px; flex: 1 1 190px; min-width: 0; }
.ariya-qw-filters__field label { color: #334155; font-size: 13px; font-weight: 700; }
.ariya-qw-filters__field select { width: 100%; padding: 9px; min-height: 42px; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; font: inherit; color: #0f172a; }
.ariya-qw-filters__apply { min-height: 42px; padding: 9px 20px; border: 0; border-radius: 8px; color: #fff; background: #0f766e; font: inherit; font-weight: 700; cursor: pointer; }
.ariya-qw-filters__clear { display: inline-flex; align-items: center; min-height: 42px; padding: 9px 8px; color: #475569; font-size: 13px; text-decoration: underline; }
/* Stage 8B: visual-only quantity controls, scoped to Quick Wholesale. */
.ariya-qw-qty { display: flex; flex-direction: column; align-items: flex-end; gap: 6px; margin-top: 4px; }
.ariya-qw-qty__caption { font-size: 12px; color: #475569; }
.ariya-qw-qty__controls { display: flex; align-items: center; gap: 5px; }
.ariya-qw-qty__button { display: inline-flex; align-items: center; justify-content: center; min-width: 44px; min-height: 44px; border: 1px solid #cbd5e1; border-radius: 9px; background: #f1f5f9; color: #0f172a; font: inherit; font-size: 20px; font-weight: 800; cursor: pointer; }
.ariya-qw-qty__button:disabled { opacity: .42; cursor: not-allowed; }
.ariya-qw-qty__input { box-sizing: border-box; width: 64px; min-height: 44px; padding: 6px 2px; border: 1px solid #cbd5e1; border-radius: 9px; background: white; text-align: center; font: inherit; font-size: 16px; direction: ltr; font-variant-numeric: tabular-nums; }
.ariya-qw-qty__input::-webkit-inner-spin-button, .ariya-qw-qty__input::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
.ariya-qw-qty__input { -moz-appearance: textfield; appearance: textfield; }
.ariya-qw-qty-summary { margin: 14px 0 0; padding: 12px 14px; border: 1px solid #dbeafe; border-radius: 10px; background: #eff6ff; color: #1e3a8a; line-height: 2; font-size: 13px; }
.ariya-qw-qty-summary span { display: block; color: #475569; font-weight: normal; }
.ariya-qw-submit-wrap { position: sticky; bottom: 10px; z-index: 12; margin-top: 14px; padding: 12px; border: 1px solid #bfdbfe; border-radius: 14px; background: rgba(255,255,255,.96); box-shadow: 0 12px 35px rgba(15,23,42,.12); backdrop-filter: blur(8px); }
.ariya-qw-submit { width: 100%; min-height: 50px; border: 0; border-radius: 11px; background: #166534; color: #fff; font: inherit; font-weight: 800; cursor: pointer; }
.ariya-qw-submit:disabled { opacity: .55; cursor: not-allowed; }
.ariya-qw-feedback { display: none; margin-bottom: 9px; padding: 10px 12px; border-radius: 9px; line-height: 1.8; font-size: 13px; }
.ariya-qw-feedback.is-visible { display: block; }
.ariya-qw-feedback.is-error { color: #991b1b; background: #fef2f2; }
.ariya-qw-feedback.is-success { color: #166534; background: #f0fdf4; }
.ariya-qw-catalog__item.is-invalid { border-color: #ef4444; box-shadow: 0 0 0 2px rgba(239,68,68,.12); }
@media (max-width: 600px) {
    .ariya-qw-filters { flex-direction: column; align-items: stretch; }
    .ariya-qw-filters__field { flex: auto; }
    .ariya-qw-filters__apply { width: 100%; }
    .ariya-qw-catalog__item { flex-direction: column; align-items: stretch; }
    .ariya-qw-catalog__stock { align-self: flex-start; }
    .ariya-qw-catalog__image { align-self: flex-start; }
    .ariya-qw-catalog__meta { align-items: flex-start; width: 100%; }
    .ariya-qw-qty { align-items: flex-start; width: 100%; }
    .ariya-qw-catalog__price { justify-content: flex-start; }
}
</style>
@endpush

<section class="ariya-qw-catalog" aria-label="فهرست گاردهای سازگار" data-qw-stage08b="1">
    <h2>گاردهای سازگار با <span dir="ltr">{{ $selectedLabel }}</span></h2>
    @auth
        {{-- ARIYA_TASK01_STAGE07B_FILTER_UI_V1 --}}
        <form class="ariya-qw-filters" method="GET" action="{{ route('front.quick-wholesale.index') }}" aria-label="فیلتر و مرتب‌سازی گاردها">
            <input type="hidden" name="model" value="{{ $selectedLabel }}">
            <div class="ariya-qw-filters__field">
                <label for="ariya-qw-filter-type">نوع گارد</label>
                <select id="ariya-qw-filter-type" name="type">
                    <option value="all" {{ $catalogFilterType === 'all' ? 'selected' : '' }}>همه گاردها</option>
                    @foreach($catalogFilterOptions as $id => $title)
                        <option value="{{ $id }}" {{ (string) $catalogFilterType === (string) $id ? 'selected' : '' }}>{{ $title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ariya-qw-filters__field">
                <label for="ariya-qw-filter-brand">برند</label>
                <select id="ariya-qw-filter-brand" name="brand">
                    <option value="all" {{ $catalogBrand === 'all' ? 'selected' : '' }}>همه برندها</option>
                    @foreach($catalogBrandOptions as $id => $title)
                        <option value="{{ $id }}" {{ (string) $catalogBrand === (string) $id ? 'selected' : '' }}>{{ $title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ariya-qw-filters__field">
                <label for="ariya-qw-filter-sort">مرتب‌سازی</label>
                <select id="ariya-qw-filter-sort" name="sort">
                    <option value="default" {{ $catalogSort === 'default' ? 'selected' : '' }}>چیدمان پیش‌فرض</option>
                    <option value="price_asc" {{ $catalogSort === 'price_asc' ? 'selected' : '' }}>ارزان‌ترین قیمت</option>
                    <option value="price_desc" {{ $catalogSort === 'price_desc' ? 'selected' : '' }}>گران‌ترین قیمت</option>
                    <option value="stock_desc" {{ $catalogSort === 'stock_desc' ? 'selected' : '' }}>بیشترین موجودی</option>
                </select>
            </div>
            <button type="submit" class="ariya-qw-filters__apply">اعمال فیلتر</button>
            @if($catalogFilterType !== 'all' || $catalogBrand !== 'all' || $catalogSort !== 'default')
                <a class="ariya-qw-filters__clear" href="{{ route('front.quick-wholesale.index', ['model' => $selectedLabel]) }}">حذف فیلترها</a>
            @endif
        </form>
        <p class="ariya-qw-catalog__intro" data-qw-catalog-state="authenticated">
            {{ number_format(count($items)) }} تنوع از مجموع {{ number_format($catalogTotal) }} تنوع سازگار نمایش داده می‌شود.
            موجودی‌ها لحظه‌ای هستند و پیش از ثبت خرید دوباره بررسی خواهند شد.
        </p>
        @if(count($items) > 0)
            <div class="ariya-qw-catalog__list" data-qw-variant-list>
                @foreach($items as $item)
                    <article class="ariya-qw-catalog__item"
                             data-price-id="{{ $item['price_id'] }}"
                             data-product-id="{{ $item['product_id'] }}"
                             data-unit-price="{{ (int) round($item['sale_toman']) }}">
                        {{-- ARIYA_TASK01_STAGE06C_CARD_V1 --}}
                        <img
                            class="ariya-qw-catalog__image"
                            src="{{ $item['image_url'] }}"
                            alt="{{ $item['variant_title'] ?: $item['product_title'] }}"
                            loading="lazy"
                            decoding="async"
                            width="96"
                            height="96"
                        >
                        <div class="ariya-qw-catalog__details">
                            <strong>{{ $item['variant_title'] ?: $item['product_title'] }}</strong>
                            <small>محصول: {{ $item['product_title'] }}</small>
                            <small>شناسه تنوع: {{ $item['price_id'] }}</small>
                            <small>حداقل خرید: {{ number_format($item['min_order']) }} | حداکثر فعلی: {{ number_format($item['max_order']) }}</small>
                        </div>
                        <div class="ariya-qw-catalog__meta" data-qw-price-visible="1">
                            <div class="ariya-qw-catalog__price">
                                @if($item['has_price_discount'])
                                    <del>{{ number_format($item['regular_toman']) }}</del>
                                @endif
                                <strong>{{ number_format($item['sale_toman']) }}</strong>
                                <span>{{ trans('front::messages.currency.suffix') }}</span>
                            </div>
                            <span class="ariya-qw-catalog__stock">موجودی قابل بررسی: {{ number_format(min($item['site_stock'], $item['inventory_stock'])) }}</span>
                            {{-- ARIYA_TASK01_STAGE08B_QTY_V1: display-only, no cart submit. --}}
                            <div class="ariya-qw-qty" data-qw-quantity
                                 data-min-order="{{ (int) $item['min_order'] }}"
                                 data-max-order="{{ (int) $item['max_order'] }}">
                                <span class="ariya-qw-qty__caption">تعداد انتخابی</span>
                                <div class="ariya-qw-qty__controls" dir="ltr">
                                    <button type="button" class="ariya-qw-qty__button" data-qw-qty-minus
                                            aria-label="کاهش تعداد {{ $item['variant_title'] ?: $item['product_title'] }}" disabled>−</button>
                                    <input type="number" class="ariya-qw-qty__input" data-qw-qty-input
                                           min="0" max="{{ (int) $item['max_order'] }}" step="1" value="0"
                                           inputmode="numeric"
                                           aria-label="تعداد {{ $item['variant_title'] ?: $item['product_title'] }}">
                                    <button type="button" class="ariya-qw-qty__button" data-qw-qty-plus
                                            aria-label="افزایش تعداد {{ $item['variant_title'] ?: $item['product_title'] }}">+</button>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            {{-- Display-only selection summary; no cart or order request. --}}
            <p class="ariya-qw-qty-summary" data-qw-qty-summary role="status" aria-live="polite">
                <strong data-qw-selected-count>۰</strong> تنوع،
                <strong data-qw-selected-units>۰</strong> عدد انتخاب شده است.
                <span>مبلغ تخمینی: <strong data-qw-selected-total>۰</strong> {{ trans('front::messages.currency.suffix') }}</span>
            </p>
            <div class="ariya-qw-submit-wrap">
                <div class="ariya-qw-feedback" data-qw-feedback role="alert" aria-live="assertive"></div>
                <button type="button" class="ariya-qw-submit" data-qw-submit
                        data-action="{{ route('front.quick-wholesale.store') }}" disabled>
                    افزودن گروهی به سبد خرید
                </button>
            </div>
        @else
            <p class="ariya-qw-catalog__notice">{{ $catalogTotal > 0 ? 'هیچ گاردی با فیلتر انتخاب‌شده پیدا نشد.' : 'در حال حاضر گارد قابل ارائه‌ای برای این مدل یافت نشد.' }}</p>
        @endif
        <p class="ariya-qw-catalog__notice">قیمت و موجودی هنگام افزودن گروهی دوباره در سرور بررسی می‌شوند.</p>
    @else
        <p class="ariya-qw-catalog__notice" data-qw-catalog-state="login-required">
            برای مشاهده فهرست تنوع‌ها و موجودی همکاری، ابتدا وارد حساب کاربری خود شوید.
        </p>
    @endauth
</section>
@auth
<script>
(function () {
    'use strict';

    // Quantity state and one atomic multi-product cart request.
    function validBounds(minimum, maximum) {
        var min = Number(minimum);
        var max = Number(maximum);
        if (!Number.isSafeInteger(min) || !Number.isSafeInteger(max) || min < 1 || max < min) {
            return null;
        }
        return { min: min, max: max };
    }

    function normalizedQuantity(raw, bounds) {
        if (!bounds) return 0;
        if (typeof raw === 'string' && raw.trim() === '') return 0;
        var qty = Number(raw);
        if (!Number.isSafeInteger(qty) || qty <= 0) return 0;
        return Math.max(bounds.min, Math.min(bounds.max, qty));
    }

    function steppedQuantity(raw, direction, bounds) {
        if (!bounds) return 0;
        var current = normalizedQuantity(raw, bounds);
        if (direction === 'plus') {
            return current === 0 ? bounds.min : Math.min(current + 1, bounds.max);
        }
        if (direction === 'minus') {
            return current <= bounds.min ? 0 : current - 1;
        }
        return current;
    }

    // Node-only test export; in a browser the actual DOM controller runs below.
    if (typeof module !== 'undefined' && module.exports) {
        module.exports = {
            validBounds: validBounds,
            normalizedQuantity: normalizedQuantity,
            steppedQuantity: steppedQuantity
        };
        return;
    }

    var catalog = document.querySelector('.ariya-qw-catalog[data-qw-stage08b="1"]');
    if (!catalog) return;
    var controls = Array.prototype.slice.call(catalog.querySelectorAll('[data-qw-quantity]'));
    var countOutput = catalog.querySelector('[data-qw-selected-count]');
    var unitsOutput = catalog.querySelector('[data-qw-selected-units]');
    var totalOutput = catalog.querySelector('[data-qw-selected-total]');
    var submit = catalog.querySelector('[data-qw-submit]');
    var feedback = catalog.querySelector('[data-qw-feedback]');
    var formatter = new Intl.NumberFormat('fa-IR');

    function boundsFor(control) {
        return validBounds(control.getAttribute('data-min-order'), control.getAttribute('data-max-order'));
    }

    function updateSummary() {
        var selectedTypes = 0;
        var units = 0;
        var total = 0;
        controls.forEach(function (control) {
            var field = control.querySelector('[data-qw-qty-input]');
            if (!field) return;
            var quantity = normalizedQuantity(field.value, boundsFor(control));
            if (quantity > 0) {
                selectedTypes += 1;
                units += quantity;
                var item = control.closest('[data-price-id]');
                var unitPrice = item ? Number(item.getAttribute('data-unit-price')) : 0;
                if (Number.isFinite(unitPrice) && unitPrice > 0) total += unitPrice * quantity;
            }
        });
        if (countOutput) countOutput.textContent = formatter.format(selectedTypes);
        if (unitsOutput) unitsOutput.textContent = formatter.format(units);
        if (totalOutput) totalOutput.textContent = formatter.format(total);
        if (submit) submit.disabled = selectedTypes === 0;
    }

    function apply(control, raw) {
        var bounds = boundsFor(control);
        var field = control.querySelector('[data-qw-qty-input]');
        var plus = control.querySelector('[data-qw-qty-plus]');
        var minus = control.querySelector('[data-qw-qty-minus]');
        if (!field || !plus || !minus) return;
        var quantity = normalizedQuantity(raw, bounds);
        field.value = String(quantity);
        field.disabled = !bounds;
        plus.disabled = !bounds || quantity >= bounds.max;
        minus.disabled = !bounds || quantity === 0;
        updateSummary();
    }

    catalog.addEventListener('click', function (event) {
        if (!(event.target instanceof Element)) return;
        var button = event.target.closest('[data-qw-qty-plus], [data-qw-qty-minus]');
        if (!button || !catalog.contains(button) || button.disabled) return;
        var control = button.closest('[data-qw-quantity]');
        if (!control) return;
        var input = control.querySelector('[data-qw-qty-input]');
        if (!input) return;
        var direction = button.hasAttribute('data-qw-qty-plus') ? 'plus' : 'minus';
        apply(control, steppedQuantity(input.value, direction, boundsFor(control)));
    });

    catalog.addEventListener('change', function (event) {
        if (!(event.target instanceof Element) || !event.target.matches('[data-qw-qty-input]')) return;
        var control = event.target.closest('[data-qw-quantity]');
        if (control) apply(control, event.target.value);
    });

    // Do not interrupt keyboard typing. Validate and clamp on leaving the field.
    catalog.addEventListener('blur', function (event) {
        if (!(event.target instanceof Element) || !event.target.matches('[data-qw-qty-input]')) return;
        var control = event.target.closest('[data-qw-quantity]');
        if (control) apply(control, event.target.value);
    }, true);

    controls.forEach(function (control) { apply(control, 0); });

    function showFeedback(message, type) {
        if (!feedback) return;
        feedback.textContent = message || '';
        feedback.className = 'ariya-qw-feedback is-visible ' + (type === 'success' ? 'is-success' : 'is-error');
    }

    function selectedItems() {
        return controls.map(function (control) {
            var item = control.closest('[data-price-id]');
            var field = control.querySelector('[data-qw-qty-input]');
            var quantity = field ? normalizedQuantity(field.value, boundsFor(control)) : 0;
            if (!item || quantity < 1) return null;
            return { price_id: Number(item.getAttribute('data-price-id')), quantity: quantity };
        }).filter(Boolean);
    }

    if (submit) submit.addEventListener('click', function () {
        var items = selectedItems();
        if (!items.length || submit.disabled) return;

        catalog.querySelectorAll('.is-invalid').forEach(function (item) { item.classList.remove('is-invalid'); });
        submit.disabled = true;
        submit.setAttribute('aria-busy', 'true');
        showFeedback('در حال بررسی قیمت و موجودی…', 'success');

        fetch(submit.getAttribute('data-action'), {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).content || ''
            },
            body: JSON.stringify({ model: @json($selectedLabel), items: items })
        }).then(function (response) {
            return response.json().catch(function () { return {}; }).then(function (data) {
                if (!response.ok || data.status !== 'success') {
                    var error = new Error(data.message || 'افزودن کالاها به سبد انجام نشد.');
                    error.priceId = data.failed_price_id;
                    throw error;
                }
                return data;
            });
        }).then(function (data) {
            if (data.cart) {
                var currentCart = document.getElementById('cart-list-item');
                if (currentCart) currentCart.outerHTML = data.cart;
                document.dispatchEvent(new CustomEvent('ariya:cart-updated'));
            }
            controls.forEach(function (control) { apply(control, 0); });
            showFeedback(data.message || 'کالاها به سبد خرید اضافه شدند.', 'success');
        }).catch(function (error) {
            if (error.priceId) {
                var failed = catalog.querySelector('[data-price-id="' + String(error.priceId) + '"]');
                if (failed) {
                    failed.classList.add('is-invalid');
                    failed.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
            showFeedback(error.message || 'خطایی رخ داد؛ انتخاب‌های شما حفظ شدند.', 'error');
        }).finally(function () {
            submit.removeAttribute('aria-busy');
            updateSummary();
        });
    });
})();

</script>
@endauth

