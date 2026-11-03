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
.ariya-qw-filters__field input { width: 100%; padding: 9px; min-height: 42px; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; font: inherit; color: #0f172a; }
.ariya-qw-pager { display:flex; justify-content:center; flex-wrap:wrap; align-items:center; gap:8px; margin:18px 0; }
.ariya-qw-pager a, .ariya-qw-pager span { min-width:40px; min-height:40px; padding:8px 12px; border:1px solid #cbd5e1; border-radius:9px; text-align:center; font-size:13px; }
.ariya-qw-pager .is-current { background:#1d4ed8; color:#fff; border-color:#1d4ed8; }
.ariya-qw-pager a:focus-visible { outline:3px solid #93c5fd; }
/* Immediate feedback while AJAX pagination is loading. */
.ariya-qw-pager a.is-loading { display:inline-flex; align-items:center; justify-content:center; gap:7px; background:#eff6ff; color:#1d4ed8; border-color:#93c5fd; pointer-events:none; }
.ariya-qw-pager a.is-loading::after, .ariya-qw-pager__loader::before { content:""; display:inline-block; box-sizing:border-box; width:16px; height:16px; flex:none; border:2px solid #bfdbfe; border-top-color:#1d4ed8; border-radius:50%; animation:ariya-qw-page-spin .65s linear infinite; }
.ariya-qw-pager__loader { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:8px 12px; color:#1e40af; background:#eff6ff; border:1px solid #bfdbfe; border-radius:9px; font-size:12px; }
[data-qw-ajax-zone].is-loading .ariya-qw-catalog__list { opacity:.55; transition:opacity .15s ease; }
@keyframes ariya-qw-page-spin { to { transform:rotate(360deg); } }
@media (prefers-reduced-motion:reduce) { .ariya-qw-pager a.is-loading::after, .ariya-qw-pager__loader::before { animation:none; } }
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

<section class="ariya-qw-catalog" aria-label="فهرست گاردهای سازگار" data-qw-stage08b="1"
         data-qw-user="{{ auth()->id() ?? 0 }}" data-qw-model="{{ $selected }}">
    <h2>گاردهای سازگار با <span dir="ltr">{{ $selectedLabel }}</span></h2>
    @auth
        {{-- ARIYA_TASK01_STAGE07B_FILTER_UI_V1 --}}
        <form class="ariya-qw-filters" method="GET" action="{{ route('front.quick-wholesale.index') }}" aria-label="فیلتر و مرتب‌سازی گاردها">
            <input type="hidden" name="model" value="{{ $selected }}">
            <div class="ariya-qw-filters__field">
                <label for="ariya-qw-filter-search">جست‌وجو در نام کالا و تنوع</label>
                <input id="ariya-qw-filter-search" type="search" name="q" maxlength="80"
                       placeholder="نام گارد، مدل یا برند" value="{{ $catalogSearch }}">
            </div>
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
            @if($catalogFilterType !== 'all' || $catalogBrand !== 'all' || $catalogSort !== 'default' || $catalogSearch !== '')
                <a class="ariya-qw-filters__clear" href="{{ route('front.quick-wholesale.index', ['model' => $selected]) }}">حذف فیلترها</a>
            @endif
        </form>
        {{-- AJAX swaps only this section; selection summary and submit remain mounted. --}}
        <div data-qw-ajax-zone aria-live="polite">
        <p class="ariya-qw-catalog__intro" data-qw-catalog-state="authenticated">
            {{ number_format($catalogFilteredTotal) }} تنوع مطابق جست‌وجو و فیلتر از مجموع {{ number_format($catalogTotal) }} تنوع سازگار.
            صفحه {{ number_format($catalogPage) }} از {{ number_format($catalogLastPage) }}؛ در این صفحه {{ number_format(count($items)) }} تنوع.
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
        @else
            <p class="ariya-qw-catalog__notice">{{ $catalogTotal > 0 ? 'هیچ گاردی با فیلتر انتخاب‌شده پیدا نشد.' : 'در حال حاضر گارد قابل ارائه‌ای برای این مدل یافت نشد.' }}</p>
        @endif
        @if($catalogLastPage > 1)
            <nav class="ariya-qw-pager" aria-label="صفحه‌بندی گاردها">
                @if($catalogPage > 1)
                    <a href="{{ route('front.quick-wholesale.index', array_merge(request()->except('page'), ['model' => $selected, 'page' => $catalogPage - 1])) }}">قبلی</a>
                @endif
                @for($number = max(1, $catalogPage - 2); $number <= min($catalogLastPage, $catalogPage + 2); $number++)
                    @if($number === $catalogPage)
                        <span class="is-current" aria-current="page">{{ number_format($number) }}</span>
                    @else
                        <a href="{{ route('front.quick-wholesale.index', array_merge(request()->except('page'), ['model' => $selected, 'page' => $number])) }}">{{ number_format($number) }}</a>
                    @endif
                @endfor
                @if($catalogPage < $catalogLastPage)
                    <a href="{{ route('front.quick-wholesale.index', array_merge(request()->except('page'), ['model' => $selected, 'page' => $catalogPage + 1])) }}">بعدی</a>
                @endif
            </nav>
        @endif
        </div>
            {{-- Selection summary remains available even when filters yield zero rows. --}}
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

    // Reuse an idempotency key for a retry; a changed selection gets a new key.
    function submissionSignature(model, items) {
        var sorted = items.slice().sort(function (a, b) { return a.price_id - b.price_id; });
        return JSON.stringify({ model: model, items: sorted });
    }

    function createRequestId(cryptoApi) {
        if (!cryptoApi || typeof cryptoApi.getRandomValues !== 'function') return null;
        var bytes = new Uint8Array(16);
        cryptoApi.getRandomValues(bytes);
        bytes[6] = (bytes[6] & 15) | 64;
        bytes[8] = (bytes[8] & 63) | 128;
        var hex = Array.prototype.map.call(bytes, function (n) {
            return n.toString(16).padStart(2, '0');
        }).join('');
        return [hex.slice(0, 8), hex.slice(8, 12), hex.slice(12, 16),
            hex.slice(16, 20), hex.slice(20)].join('-');
    }

    function nextPendingRequest(previous, signature, makeId) {
        if (previous && previous.signature === signature
            && /^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i.test(previous.id)) {
            return previous;
        }
        return { signature: signature, id: makeId() };
    }

    // Node-only test export; in a browser the actual DOM controller runs below.
    if (typeof module !== 'undefined' && module.exports) {
        module.exports = {
            validBounds: validBounds, normalizedQuantity: normalizedQuantity,
            steppedQuantity: steppedQuantity,
            submissionSignature: submissionSignature, createRequestId: createRequestId,
            nextPendingRequest: nextPendingRequest
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
    var storageKey = 'ariya-qw-selected:v1:' + catalog.getAttribute('data-qw-user')
        + ':' + catalog.getAttribute('data-qw-model');
    var selections = Object.create(null);
    var submitting = false;
    var pendingKey = storageKey + ':pending-v1';
    var pendingRequest = null;
    try {
        pendingRequest = JSON.parse(sessionStorage.getItem(pendingKey) || 'null');
    } catch (_) { pendingRequest = null; }
    function savePending() {
        try {
            if (pendingRequest) sessionStorage.setItem(pendingKey, JSON.stringify(pendingRequest));
            else sessionStorage.removeItem(pendingKey);
        } catch (_) {}
    }

    try {
        var restored = JSON.parse(sessionStorage.getItem(storageKey) || '{}');
        if (restored && typeof restored === 'object' && !Array.isArray(restored)) {
            Object.keys(restored).slice(0, 100).forEach(function (key) {
                var row = restored[key];
                if (!/^\d+$/.test(key) || !row || !Number.isSafeInteger(row.quantity)
                    || row.quantity < 1 || row.quantity > 100000
                    || !Number.isSafeInteger(row.price) || row.price < 0) return;
                selections[key] = {
                    quantity: row.quantity, price: row.price,
                    label: String(row.label || '').slice(0, 160)
                };
            });
        }
    } catch (_) { /* Browser storage unavailable: controls still work in this page. */ }

    function persist() {
        try { sessionStorage.setItem(storageKey, JSON.stringify(selections)); } catch (_) {}
    }

    function boundsFor(control) {
        return validBounds(control.getAttribute('data-min-order'), control.getAttribute('data-max-order'));
    }

    function updateSummary() {
        var entries = Object.keys(selections).map(function (id) { return selections[id]; });
        var units = 0;
        var total = 0;
        entries.forEach(function (row) {
            units += row.quantity;
            total += row.price * row.quantity;
        });
        if (countOutput) countOutput.textContent = formatter.format(entries.length);
        if (unitsOutput) unitsOutput.textContent = formatter.format(units);
        if (totalOutput) totalOutput.textContent = formatter.format(total);
        if (submit) submit.disabled = submitting || entries.length === 0 || entries.length > 100;
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
        var item = control.closest('[data-price-id]');
        if (item) {
            var id = item.getAttribute('data-price-id');
            var price = Number(item.getAttribute('data-unit-price'));
            if (quantity > 0 && Number.isSafeInteger(price) && price >= 0) {
                selections[id] = {
                    quantity: quantity, price: price,
                    label: (item.querySelector('.ariya-qw-catalog__details strong') || {}).textContent || ''
                };
            } else {
                delete selections[id];
            }
            persist();
        }
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

    function restoreVisibleQuantities() {
        controls = Array.prototype.slice.call(catalog.querySelectorAll('[data-qw-quantity]'));
        controls.forEach(function (control) {
            var item = control.closest('[data-price-id]');
            var saved = item && selections[item.getAttribute('data-price-id')];
            apply(control, saved ? saved.quantity : 0);
        });
        updateSummary();
    }
    restoreVisibleQuantities();

    // Progressive enhancement: pagination changes only the server-rendered result region.
    // The old links still navigate normally when JavaScript is disabled.
    var ajaxRequest = null;
    var ajaxSequence = 0;
    var ajaxStartPath = window.location.pathname;

    function clearAjaxBusy(zone) {
        if (!zone) return;
        zone.classList.remove('is-loading');
        zone.removeAttribute('aria-busy');
        Array.prototype.forEach.call(zone.querySelectorAll('.ariya-qw-pager a.is-loading'), function (link) {
            link.classList.remove('is-loading');
            link.removeAttribute('aria-label');
        });
        Array.prototype.forEach.call(zone.querySelectorAll('[data-qw-page-loader]'), function (loader) {
            loader.remove();
        });
    }

    function showAjaxBusy(zone, triggerLink) {
        clearAjaxBusy(zone);
        zone.classList.add('is-loading');
        zone.setAttribute('aria-busy', 'true');
        var pager = zone.querySelector('.ariya-qw-pager');
        if (!pager) return;
        if (triggerLink) {
            triggerLink.classList.add('is-loading');
            triggerLink.setAttribute('aria-label', '\u062f\u0631 \u062d\u0627\u0644 \u0628\u0627\u0631\u06af\u0630\u0627\u0631\u06cc \u0635\u0641\u062d\u0647');
        }
    }

    function ajaxNavigate(href, pushHistory, triggerLink) {
        var destination = new URL(href, window.location.href);
        if (destination.origin !== window.location.origin || destination.pathname !== ajaxStartPath) {
            if (pushHistory) window.location.assign(destination.href);
            return;
        }
        var zone = catalog.querySelector('[data-qw-ajax-zone]');
        if (!zone || !window.fetch || !window.DOMParser || !window.AbortController) {
            if (pushHistory) window.location.assign(destination.href);
            else window.location.reload();
            return;
        }
        var sequence = ++ajaxSequence;
        if (ajaxRequest) ajaxRequest.abort();
        ajaxRequest = new AbortController();
        showAjaxBusy(zone, triggerLink);

        fetch(destination.href, {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
            signal: ajaxRequest.signal
        }).then(function (response) {
            if (!response.ok || !String(response.headers.get('content-type') || '').includes('text/html')) {
                throw new Error('Unexpected pagination response');
            }
            return response.text();
        }).then(function (html) {
            if (sequence !== ajaxSequence) return;
            var doc = new DOMParser().parseFromString(html, 'text/html');
            var nextCatalog = doc.querySelector('.ariya-qw-catalog[data-qw-stage08b="1"]');
            var nextZone = nextCatalog && nextCatalog.querySelector('[data-qw-ajax-zone]');
            if (!nextZone || nextCatalog.getAttribute('data-qw-model') !== catalog.getAttribute('data-qw-model')) {
                throw new Error('Pagination markup mismatch');
            }
            zone.replaceWith(document.importNode(nextZone, true));
            restoreVisibleQuantities();
            if (pushHistory) {
                window.history.pushState({ ariyaQwAjax: true }, '', destination.href);
            }
            var newZone = catalog.querySelector('[data-qw-ajax-zone]');
            if (newZone) newZone.scrollIntoView({ block: 'start', behavior: 'auto' });
        }).catch(function (error) {
            if (error.name === 'AbortError' || sequence !== ajaxSequence) return;
            // Fail closed to normal server navigation rather than showing stale results.
            if (pushHistory) window.location.assign(destination.href);
            else window.location.reload();
        }).finally(function () {
            if (sequence === ajaxSequence) {
                ajaxRequest = null;
                var visibleZone = catalog.querySelector('[data-qw-ajax-zone]');
                clearAjaxBusy(visibleZone);
            }
        });
    }

    catalog.addEventListener('click', function (event) {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey
            || event.shiftKey || event.altKey || !(event.target instanceof Element)) return;
        var link = event.target.closest('.ariya-qw-pager a[href]');
        if (!link || !catalog.contains(link) || link.hasAttribute('download')
            || (link.target && link.target !== '_self')) return;
        event.preventDefault();
        ajaxNavigate(link.href, true, link);
    });

    window.addEventListener('popstate', function () {
        if (window.location.pathname === ajaxStartPath) {
            ajaxNavigate(window.location.href, false);
        }
    });

    function showFeedback(message, type) {
        if (!feedback) return;
        feedback.textContent = message || '';
        feedback.className = 'ariya-qw-feedback is-visible ' + (type === 'success' ? 'is-success' : 'is-error');
    }

    function selectedItems() {
        return Object.keys(selections).map(function (id) {
            return { price_id: Number(id), quantity: selections[id].quantity };
        }).filter(function (row) {
            return Number.isSafeInteger(row.price_id) && row.price_id > 0;
        });
    }

    if (submit) submit.addEventListener('click', function () {
        var items = selectedItems();
        if (!items.length || submit.disabled || submitting) return;
        var signature = submissionSignature(@json($selected), items);
        pendingRequest = nextPendingRequest(pendingRequest, signature, function () {
            return createRequestId(window.crypto);
        });
        if (!pendingRequest.id) {
            showFeedback('مرورگر شما شناسه امن درخواست را پشتیبانی نمی‌کند. لطفاً مرورگر را به‌روزرسانی کنید.', 'error');
            return;
        }
        savePending();
        submitting = true;

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
            body: JSON.stringify({ request_id: pendingRequest.id, model: @json($selected), items: items })
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
            pendingRequest = null;
            savePending();
            selections = Object.create(null);
            persist();
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
            submitting = false;
            submit.removeAttribute('aria-busy');
            updateSummary();
        });
    });
})();

</script>
@endauth

