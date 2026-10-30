@extends('back.layouts.master')
@section('title', 'فروش سبدی | کنترل ویترین فروش')
@section('content')
<div class="app-content content"><div class="content-wrapper"><div class="content-body">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h3>کنترل ویترین فروش / مدیریت پک‌های فروش سبدی</h3>
        <a class="btn btn-outline-secondary" href="{{ route('admin.sales-control.index') }}">بازگشت به کنترل ویترین</a>
    </div>
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if($errors->any()) <div class="alert alert-danger"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div> @endif
    <div class="card"><div class="card-body">
        <h4>{{ $editing ? 'ویرایش پک: '.$editing->title : 'ساخت پک جدید' }}</h4>
        <form id="bundle-editor-form" method="POST" action="{{ $editing ? route('admin.sales-control.bundles.update', $editing) : route('admin.sales-control.bundles.store') }}">
            @csrf @if($editing) @method('PUT') @endif
            <div class="row">
                <div class="col-md-7 form-group"><label>عنوان پک</label><input class="form-control" required name="title" maxlength="180" value="{{ old('title', $editing->title ?? '') }}" placeholder="پک عمده گارد سیلیکونی"></div>
                <div class="col-md-2 form-group"><label>تعداد کل اجباری</label><input class="form-control" required type="number" min="1" name="required_quantity" value="{{ old('required_quantity', $editing->required_quantity ?? 300) }}"></div>
                <div class="col-md-3 form-group"><label>قیمت ثابت کل (تومان)</label><input class="form-control" required type="number" min="1" name="fixed_price" value="{{ old('fixed_price', $editing->fixed_price ?? '') }}"></div>
                <div class="col-md-4 form-group"><label>قیمت عادی هر عدد (تومان)</label>
                    <input class="form-control" id="ariya-regular-unit" type="number" min="1" step="1" name="regular_unit_price" placeholder="مثلاً 270000" value="{{ old('regular_unit_price', $editing->regular_unit_price ?? '') }}">
                </div>
                <div class="col-md-4 form-group"><label>قیمت سبدی هر عدد (تومان)</label>
                    <input class="form-control" id="ariya-bundle-unit" type="number" min="1" step="1" name="bundle_unit_price" placeholder="مثلاً 145000" value="{{ old('bundle_unit_price', $editing->bundle_unit_price ?? '') }}">
                </div>
                <div class="col-md-12 form-group">
                    <div class="alert alert-info mb-0" id="ariya-pricing-result" role="status" aria-live="polite">دو قیمت واحد را وارد کنید تا مبلغ قبل، مبلغ بعد و مقدار تخفیف محاسبه شود. قیمت پک ثبت‌شده تا زمان ذخیره تغییر نمی‌کند.</div>
                    <small class="text-muted">با تکمیل هر دو قیمت، قیمت ثابت کل به‌طور خودکار محاسبه می‌شود. اگر این دو فیلد خالی باشند، قیمت ثابت قبلی بدون تغییر حفظ می‌شود.</small>
                </div>
                <div class="col-md-4 form-group"><label>شروع (اختیاری؛ ساعت سایت)</label><input class="form-control" type="datetime-local" name="starts_at" value="{{ old('starts_at', $editing && $editing->starts_at ? $editing->starts_at->format('Y-m-d\TH:i') : '') }}"></div>
                <div class="col-md-4 form-group"><label>پایان فروش (اجباری)</label><input class="form-control" required type="datetime-local" name="ends_at" value="{{ old('ends_at', $editing && $editing->ends_at ? $editing->ends_at->format('Y-m-d\TH:i') : '') }}"></div>
                <div class="col-md-4 form-group"><label class="d-block">وضعیت</label><div class="alert alert-warning mb-0">تا تکمیل تست خرید و انبار، انتشار عمومی غیرفعال است. همه پک‌ها به‌صورت پیش‌نویس ذخیره می‌شوند.</div></div>
                <div class="col-12 form-group"><label>توضیحات پک</label><textarea name="description" rows="2" class="form-control">{{ old('description', $editing->description ?? '') }}</textarea></div>
            </div>
            <hr><h5>مدل‌ها و حداقل تعداد</h5>
            <p class="text-muted">حداقل اختصاصی هر مدل <strong>اختیاری</strong> است: کادر خالی یعنی حداقل ویژه‌ای برای آن مدل تعیین نشده است. اگر مشتری آن مدل را انتخاب کند، تعدادش طبیعتاً حداقل ۱ عدد خواهد بود؛ اما بدون تیک «اجباری»، می‌تواند اصلاً آن مدل را انتخاب نکند.</p>
            <p class="text-info mb-1">موجودی‌ها از انبار مرکزی و در زمان جست‌وجو یا بازکردن فرم خوانده می‌شوند؛ این اعداد رزرو موجودی نیستند.</p>
            <div class="row"><div class="col-md-8"><input id="bundle-model-search" type="search" class="form-control" placeholder="جست‌وجوی محصول یا کد محصول (حداقل ۲ حرف)"></div><div class="col-md-4"><button id="bundle-search-btn" class="btn btn-outline-primary btn-block" type="button">جست‌وجوی مدل</button></div></div>
            <div class="d-flex align-items-center flex-wrap mt-2 mb-2" style="gap:12px">
                <label class="mb-0" for="bundle-only-in-stock" style="cursor:pointer">
                    <input id="bundle-only-in-stock" type="checkbox" class="mr-1">
                    <strong>فقط مدل‌های دارای موجودی (بیشتر از صفر) را نمایش بده</strong>
                </label>
                <span class="small text-muted">این فیلتر روی جست‌وجو، «انتخاب همه» و نمایش جدول اثر دارد؛ مدل‌های از قبل انتخاب‌شده را حذف نمی‌کند.</span>
            </div>
            <div id="bundle-search-results" class="list-group mt-1 mb-1" style="max-height:300px;overflow-y:auto"></div>
            <button id="bundle-search-more" class="btn btn-sm btn-outline-secondary mb-2" type="button" hidden style="display:none">نمایش نتایج موجودِ بیشتر</button>
            <div id="bundle-selection-feedback" class="small text-info mb-2" role="status" aria-live="polite"></div>
            <p class="small text-muted">برای محصول‌های دارای صدها یا هزاران مدل، انتخاب همه به‌صورت مرحله‌ای انجام می‌شود. محدودیت عددی برای تعداد مدل‌های پک در پنل قرار داده نشده است.</p>
            <div id="bundle-selected-count" class="small font-weight-bold mb-1" aria-live="polite"></div>
            <button id="bundle-remove-out-of-stock" type="button" class="btn btn-sm btn-outline-danger mb-2" hidden style="display:none">حذف مدل‌های ناموجود یا نامشخص از انتخاب (با تأیید)</button>
            <button id="bundle-clear-all-minimums" type="button" class="btn btn-sm btn-outline-secondary mb-2">خالی‌کردن حداقل همه مدل‌ها</button>
            <input id="bundle-variants-json" type="hidden" name="variants_json" value="{{ old('variants_json', '') }}">
            <div class="table-responsive" style="max-height:650px;overflow-y:auto"><table class="table table-bordered"><thead><tr><th>مدل انتخابی</th><th style="width:140px">موجودی انبار مرکزی</th><th style="width:140px">حداقل اختصاصی (اختیاری)</th><th style="width:110px">اجباری؟</th><th style="width:90px">حذف</th></tr></thead><tbody id="bundle-selected-rows">
                @if($editing)
                    @foreach($editing->variants as $variant)
                        <tr data-variant-id="{{ $variant->price_id }}">
                            <td><span class="bundle-model-label">{{ $variant->price?->product?->title }} | مدل {{ $variant->price_id }} | {{ $variant->price?->getAttributesName() }}</span></td>
                            <td class="bundle-stock-cell" data-stock="{{ array_key_exists((int) $variant->price_id, $selectedStock) && $selectedStock[(int) $variant->price_id] !== null ? $selectedStock[(int) $variant->price_id] : '' }}">
                                @if(array_key_exists((int) $variant->price_id, $selectedStock) && $selectedStock[(int) $variant->price_id] !== null)
                                    <span class="{{ $selectedStock[(int) $variant->price_id] > 0 ? 'text-success' : 'text-danger' }}">{{ number_format($selectedStock[(int) $variant->price_id]) }} عدد</span>
                                @else
                                    <span class="text-muted">نامشخص</span>
                                @endif
                            </td>
                            <td><input type="number" class="form-control bundle-minimum" min="1" placeholder="اختیاری" value="{{ (int) $variant->minimum_if_selected > 1 ? $variant->minimum_if_selected : '' }}"></td>
                            <td><input type="checkbox" class="bundle-required" value="1" {{ $variant->required ? 'checked' : '' }}></td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger bundle-remove">حذف</button></td>
                        </tr>
                    @endforeach
                @endif
            </tbody></table></div>
            <button type="submit" class="btn btn-primary">{{ $editing ? 'ذخیره تغییرات پک' : 'ساخت پک جدید' }}</button>
            @if($editing) <a href="{{ route('admin.sales-control.bundles.index') }}" class="btn btn-light">لغو ویرایش</a> @endif
        </form>
    </div></div>
    <div class="card"><div class="card-body"><h4>پک‌های تعریف‌شده</h4><div class="table-responsive"><table class="table"><thead><tr><th>نام</th><th>تعداد</th><th>قیمت تومان</th><th>مدل‌ها</th><th>پایان</th><th>وضعیت</th><th>عملیات</th></tr></thead><tbody>
        @forelse($offers as $offer)
            <tr><td>{{ $offer->title }}</td><td>{{ $offer->required_quantity }}</td><td>{{ number_format($offer->fixed_price) }}</td><td>{{ $offer->variants_count }}</td><td>{{ $offer->ends_at?->format('Y/m/d H:i') }}</td><td>{{ $offer->isLive() ? 'در حال فروش' : ($offer->is_active ? 'خارج از بازه' : 'غیرفعال') }}</td>
            <td><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.sales-control.bundles.edit', $offer) }}">ویرایش</a>
            @if($offer->is_active)<form method="POST" action="{{ route('admin.sales-control.bundles.destroy', $offer) }}" class="d-inline">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">غیرفعال</button></form>@endif</td></tr>
        @empty <tr><td colspan="7">هنوز پکی تعریف نشده است.</td></tr> @endforelse
    </tbody></table></div></div></div>
</div></div></div>
@endsection
@push('scripts')
<script>
(function () {
    'use strict';
    const regularUnit = document.getElementById('ariya-regular-unit');
    const bundleUnit = document.getElementById('ariya-bundle-unit');
    const requiredQuantityInput = document.querySelector('#bundle-editor-form [name="required_quantity"]');
    const fixedTotalInput = document.querySelector('#bundle-editor-form [name="fixed_price"]');
    const pricingResult = document.getElementById('ariya-pricing-result');
    const priceFmt = new Intl.NumberFormat('fa-IR');
    function updatePricing() {
        const count = Number(requiredQuantityInput.value);
        const original = Number(regularUnit.value);
        const discounted = Number(bundleUnit.value);
        if (!regularUnit.value && !bundleUnit.value) {
            pricingResult.textContent = 'قیمت‌گذاری واحد ثبت نشده؛ مبلغ ثابت فعلی پک محفوظ است.';
            return;
        }
        if (!Number.isSafeInteger(count) || count < 1 || !Number.isSafeInteger(original) || !Number.isSafeInteger(discounted)
            || original <= 0 || discounted <= 0 || discounted >= original || !regularUnit.value || !bundleUnit.value) {
            pricingResult.textContent = 'تعداد و هر دو قیمت واحد را صحیح وارد کنید؛ قیمت سبدی باید کمتر از قیمت عادی باشد.';
            return;
        }
        const before = count * original, after = count * discounted;
        if (!Number.isSafeInteger(before) || !Number.isSafeInteger(after) || before > 1000000000000) {
            pricingResult.textContent = 'مبلغ کل از محدوده قابل ثبت بیشتر است.';
            return;
        }
        fixedTotalInput.value = String(after);
        const saving = before - after;
        pricingResult.textContent = 'قیمت قبل: ' + priceFmt.format(before) + ' تومان | قیمت سبدی: '
            + priceFmt.format(after) + ' تومان | تخفیف: ' + priceFmt.format(saving)
            + ' تومان (' + priceFmt.format(Math.round(saving / before * 100)) + '٪)';
    }
    [regularUnit, bundleUnit, requiredQuantityInput].forEach(input => input.addEventListener('input', updatePricing));
    updatePricing();
    const form = document.getElementById('bundle-editor-form');
    const serialized = document.getElementById('bundle-variants-json');
    const search = document.getElementById('bundle-model-search');
    const results = document.getElementById('bundle-search-results');
    const selected = document.getElementById('bundle-selected-rows');
    const feedback = document.getElementById('bundle-selection-feedback');
    const searchButton = document.getElementById('bundle-search-btn');
    const counter = document.getElementById('bundle-selected-count');
    const stockOnly = document.getElementById('bundle-only-in-stock');
    const searchMore = document.getElementById('bundle-search-more');
    const removeUnavailable = document.getElementById('bundle-remove-out-of-stock');
    const clearAllMinimums = document.getElementById('bundle-clear-all-minimums');
    let lastSearchItems = [];
    let nextSearchCursor = null;
    let lastSearchTerm = '';
    let searchVersion = 0; // Ignore responses from earlier searches.
    const searchUrl = @json(route('admin.sales-control.bundles.search'));
    const numeral = new Intl.NumberFormat('fa-IR');
    const selectedIds = new Set(Array.from(selected.querySelectorAll('tr[data-variant-id]'))
        .map(row => Number(row.dataset.variantId)));

    function stockText(stock) {
        if (stock === null || stock === undefined || stock === '') return 'موجودی نامشخص';
        return 'موجودی: ' + numeral.format(stock) + ' عدد';
    }
    function stockClass(stock) {
        return stock === null || stock === undefined || stock === ''
            ? 'text-muted' : (Number(stock) > 0 ? 'text-success' : 'text-danger');
    }
    function hasStock(stock) {
        return stock !== null && stock !== undefined && stock !== ''
            && Number.isFinite(Number(stock)) && Number(stock) > 0;
    }
    function updateCount() {
        let shown = 0;
        let hidden = 0;
        selected.querySelectorAll('tr[data-variant-id]').forEach(row => {
            const cell = row.querySelector('.bundle-stock-cell');
            const available = cell && hasStock(cell.dataset.stock);
            row.hidden = Boolean(stockOnly.checked && !available);
            row.style.display = row.hidden ? 'none' : '';
            if (row.hidden) hidden++; else shown++;
        });
        const total = selectedIds.size;
        counter.textContent = 'تعداد کل مدل‌های انتخاب‌شده: ' + numeral.format(total)
            + (stockOnly.checked ? ' | نمایش: ' + numeral.format(shown)
            + ' مدل دارای موجودی | پنهان‌شده: ' + numeral.format(hidden)
            + ' مدل (همچنان انتخاب‌شده‌اند)' : '');
        removeUnavailable.hidden = !stockOnly.checked || hidden === 0;
        removeUnavailable.style.display = removeUnavailable.hidden ? 'none' : '';
    }
    removeUnavailable.addEventListener('click', () => {
        const unavailableRows = Array.from(selected.querySelectorAll('tr[data-variant-id]'))
            .filter(row => !hasStock(row.querySelector('.bundle-stock-cell')?.dataset.stock));
        if (!unavailableRows.length) return;
        if (!window.confirm('آیا ' + numeral.format(unavailableRows.length)
            + ' مدل با موجودی صفر یا نامشخص از پک حذف شوند؟ این کار موجودی انبار را تغییر نمی‌دهد.')) return;
        unavailableRows.forEach(row => {
            selectedIds.delete(Number(row.dataset.variantId));
            row.remove();
        });
        updateCount();
        feedback.textContent = numeral.format(unavailableRows.length) + ' مدل از انتخاب‌های فرم حذف شد.';
    });
    // A blank minimum means "no special per-model minimum". The server keeps
    // its natural quantity floor of one for a model only if a buyer selects it.
    function addVariant(item, minimumValue = null, isRequired = false, parent = selected) {
        const id = Number(item.id);
        if (!Number.isSafeInteger(id) || id < 1 || selectedIds.has(id)) return false;
        const row = document.createElement('tr');
        row.dataset.variantId = String(id);
        const label = document.createElement('td');
        const labelSpan = document.createElement('span');
        labelSpan.className = 'bundle-model-label';
        labelSpan.textContent = String(item.label || ('مدل ' + id));
        label.appendChild(labelSpan);
        const stockCell = document.createElement('td');
        stockCell.className = 'bundle-stock-cell';
        stockCell.dataset.stock = item.stock === null || item.stock === undefined ? '' : String(item.stock);
        const stock = document.createElement('span');
        stock.className = stockClass(item.stock);
        stock.textContent = stockText(item.stock);
        stockCell.appendChild(stock);
        const minCell = document.createElement('td');
        const minimum = document.createElement('input');
        minimum.type = 'number'; minimum.min = '1';
        minimum.value = Number(minimumValue) > 1 ? String(Number(minimumValue)) : '';
        minimum.placeholder = 'اختیاری';
        minimum.className = 'form-control bundle-minimum';
        minCell.appendChild(minimum);
        const requiredCell = document.createElement('td');
        const required = document.createElement('input');
        required.type = 'checkbox'; required.className = 'bundle-required';
        required.checked = Boolean(isRequired);
        requiredCell.appendChild(required);
        const delCell = document.createElement('td');
        const del = document.createElement('button');
        del.type = 'button'; del.className = 'btn btn-sm btn-outline-danger bundle-remove';
        del.textContent = 'حذف'; delCell.appendChild(del);
        row.append(label, stockCell, minCell, requiredCell, delCell);
        parent.appendChild(row);
        selectedIds.add(id);
        return true;
    }
    clearAllMinimums.addEventListener('click', () => {
        const fields = Array.from(selected.querySelectorAll('tr[data-variant-id] .bundle-minimum'));
        const filled = fields.filter(field => field.value.trim() !== '');
        if (!filled.length) {
            feedback.textContent = 'هیچ حداقل اختصاصی‌ای برای پاک‌کردن ثبت نشده است.';
            return;
        }
        if (!window.confirm('حداقل اختصاصی ' + numeral.format(filled.length)
            + ' مدل پاک شود؟ مدل‌ها و تیک‌های اجباری باقی می‌مانند.')) return;
        filled.forEach(field => { field.value = ''; });
        feedback.textContent = 'حداقل اختصاصی ' + numeral.format(filled.length)
            + ' مدل خالی شد؛ مدل‌ها از پک حذف نشده‌اند.';
    });
    selected.addEventListener('click', event => {
        const button = event.target.closest('.bundle-remove');
        if (!button) return;
        const row = button.closest('tr');
        selectedIds.delete(Number(row.dataset.variantId));
        row.remove();
        updateCount();
    });
    async function fetchModels(url) {
        const response = await fetch(url, {
            credentials: 'same-origin',
            headers: {'Accept': 'application/json'}
        });
        if (!response.ok) {
            const body = await response.json().catch(() => null);
            throw new Error(body && body.message ? body.message : 'دریافت مدل‌ها ناموفق بود.');
        }
        return response.json();
    }
    async function selectProduct(productId, button) {
        button.disabled = true;
        let cursor = 0;
        let added = 0;
        let loaded = 0;
        const requireStock = stockOnly.checked;
        let unknown = 0;
        try {
            do {
                const url = searchUrl + '?product_id=' + encodeURIComponent(productId)
                    + '&after_id=' + encodeURIComponent(cursor);
                const page = await fetchModels(url);
                if (!page || !Array.isArray(page.items)) throw new Error('پاسخ سرور معتبر نیست.');
                const fragment = document.createDocumentFragment();
                page.items.forEach(item => {
                    if (requireStock && !hasStock(item.stock)) {
                        if (item.stock === null || item.stock === undefined) unknown++;
                        return;
                    }
                    if (addVariant(item, 1, false, fragment)) added++;
                });
                selected.appendChild(fragment);
                loaded += page.items.length;
                updateCount();
                feedback.textContent = 'بارگذاری مدل‌ها: ' + numeral.format(loaded)
                    + ' بررسی شد؛ ' + numeral.format(added) + ' مدل جدید اضافه شد…';
                if (page.next_cursor === null || page.next_cursor === undefined) break;
                if (!Number.isSafeInteger(Number(page.next_cursor)) || Number(page.next_cursor) <= cursor) {
                    throw new Error('پیشروی بارگذاری مدل‌ها معتبر نیست.');
                }
                cursor = Number(page.next_cursor);
            } while (true);
            feedback.textContent = added
                ? numeral.format(added) + ' مدل ' + (requireStock ? 'دارای موجودی ' : '')
                    + 'اضافه شد. حداقل تعداد هر مدل را در جدول تنظیم کن.'
                : (requireStock ? 'مدل جدید دارای موجودی پیدا نشد.' : 'همه مدل‌های این محصول قبلاً انتخاب شده‌اند.');
            if (unknown) feedback.textContent += ' توجه: موجودی ' + numeral.format(unknown)
                + ' مدل مشخص نبود و انتخاب نشد.';
        } catch (error) {
            feedback.textContent = (error.message || 'دریافت مدل‌ها ناموفق بود.')
                + (added ? ' ' + numeral.format(added) + ' مدل تا این مرحله اضافه شده است؛ برای ادامه دوباره انتخاب همه را بزن.' : '');
        } finally {
            button.disabled = false;
        }
    }
    function showResults(items) {
        results.textContent = '';
        const visibleItems = stockOnly.checked ? items.filter(item => hasStock(item.stock)) : items;
        if (!visibleItems.length) {
            results.textContent = stockOnly.checked
                ? 'در نتایج دریافت‌شده، مدل دارای موجودی پیدا نشد.' : 'مدلی پیدا نشد.';
            return;
        }
        const grouped = new Map();
        visibleItems.forEach(item => {
            if (!grouped.has(item.product_id)) grouped.set(item.product_id, []);
            grouped.get(item.product_id).push(item);
        });
        grouped.forEach((models, productId) => {
            const header = document.createElement('div');
            header.className = 'list-group-item bg-light d-flex justify-content-between align-items-center flex-wrap';
            const name = document.createElement('strong');
            name.textContent = models[0].product_title;
            const allButton = document.createElement('button');
            allButton.type = 'button';
            allButton.className = 'btn btn-sm btn-outline-success';
            allButton.textContent = 'انتخاب همه مدل‌های این محصول';
            allButton.addEventListener('click', () => selectProduct(productId, allButton));
            header.append(name, allButton);
            results.appendChild(header);
            models.forEach(item => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center flex-wrap';
                const label = document.createElement('span');
                label.textContent = item.label;
                const stock = document.createElement('small');
                stock.className = stockClass(item.stock);
                stock.textContent = stockText(item.stock);
                button.append(label, stock);
                button.addEventListener('click', () => {
                    const added = addVariant(item);
                    updateCount();
                    feedback.textContent = added ? 'مدل به جدول اضافه شد.' : 'این مدل قبلاً انتخاب شده است.';
                });
                results.appendChild(button);
            });
        });
    }
    function refreshMoreButton() {
        searchMore.hidden = !stockOnly.checked || nextSearchCursor === null;
        searchMore.style.display = searchMore.hidden ? 'none' : '';
    }
    async function performSearch(append = false) {
        const term = search.value.trim();
        if (term.length < 2) {
            results.textContent = 'حداقل ۲ حرف وارد کنید.';
            nextSearchCursor = null;
            refreshMoreButton();
            return;
        }
        const currentVersion = ++searchVersion;
        searchButton.disabled = true;
        searchMore.disabled = true;
        if (!append) {
            feedback.textContent = '';
            results.textContent = 'در حال جست‌وجو…';
            lastSearchItems = [];
            nextSearchCursor = null;
            lastSearchTerm = term;
        }
        try {
            let url = searchUrl + '?q=' + encodeURIComponent(term);
            if (stockOnly.checked) url += '&in_stock=1';
            if (append && nextSearchCursor !== null) {
                url += '&after_id=' + encodeURIComponent(nextSearchCursor);
            }
            const payload = await fetchModels(url);
            if (currentVersion !== searchVersion) return; // Ignore stale search results.
            const items = Array.isArray(payload) ? payload : payload.items;
            if (!Array.isArray(items)) throw new Error('پاسخ جست‌وجو معتبر نیست.');
            lastSearchItems = append ? lastSearchItems.concat(items) : items;
            nextSearchCursor = !Array.isArray(payload) && payload.next_cursor !== undefined
                ? payload.next_cursor : null;
            showResults(lastSearchItems);
            refreshMoreButton();
        } catch (error) {
            if (currentVersion !== searchVersion) return;
            if (!append) results.textContent = error.message || 'جست‌وجو ناموفق بود.';
            else feedback.textContent = error.message || 'ادامه جست‌وجو ناموفق بود.';
        } finally {
            if (currentVersion === searchVersion) {
                searchButton.disabled = false;
                searchMore.disabled = false;
            }
        }
    }
    searchButton.addEventListener('click', () => performSearch(false));
    searchMore.addEventListener('click', () => {
        if (nextSearchCursor !== null && search.value.trim() === lastSearchTerm) performSearch(true);
        else performSearch(false);
    });
    stockOnly.addEventListener('change', () => {
        updateCount();
        nextSearchCursor = null;
        refreshMoreButton();
        if (search.value.trim().length >= 2) performSearch(false);
        else { results.textContent = ''; lastSearchItems = []; }
    });
    search.addEventListener('keydown', event => {
        if (event.key === 'Enter') { event.preventDefault(); searchButton.click(); }
    });

    // Restore all selected models and entered minimums after a validation error.
    // The display text/stock values are NEVER trusted by the server for saving.
    if (serialized.value) {
        try {
            const oldRows = JSON.parse(serialized.value);
            if (Array.isArray(oldRows)) {
                selected.textContent = '';
                selectedIds.clear();
                const fragment = document.createDocumentFragment();
                oldRows.forEach(row => addVariant({id: row.price_id, label: row.label, stock: row.stock},
                    row.minimum_if_selected, row.required, fragment));
                selected.appendChild(fragment);
                feedback.textContent = 'مدل‌ها و حداقل تعداد قبلی پس از خطای اعتبارسنجی بازیابی شدند.';
            }
        } catch (error) { feedback.textContent = 'بازیابی فهرست قبلی ناموفق بود.'; }
    }
    updateCount();
    form.addEventListener('submit', event => {
        const rows = Array.from(selected.querySelectorAll('tr[data-variant-id]'));
        if (!rows.length) {
            event.preventDefault();
            feedback.textContent = 'حداقل یک مدل به پک اضافه کن.';
            return;
        }
        // All selected models travel in ONE form field; no PHP max_input_vars truncation.
        serialized.value = JSON.stringify(rows.map(row => {
            const rawStock = row.querySelector('.bundle-stock-cell').dataset.stock;
            return {
                price_id: Number(row.dataset.variantId),
                // Keep backend's min:1 schema unchanged; blank is normalized to
                // 1, which does NOT force the variant to be selected by buyers.
                minimum_if_selected: row.querySelector('.bundle-minimum').value.trim() === ''
                    ? 1 : Number(row.querySelector('.bundle-minimum').value),
                required: row.querySelector('.bundle-required').checked ? 1 : 0,
                label: row.querySelector('.bundle-model-label').textContent,
                stock: rawStock === '' ? null : Number(rawStock)
            };
        }));
    });
})();
</script>
@endpush
