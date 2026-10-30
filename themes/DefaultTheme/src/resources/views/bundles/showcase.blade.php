@extends('front::layouts.master', ['title' => $offer->title])

@push('meta')
<meta name="robots" content="noindex,nofollow">
<meta name="description" content="پیش‌نمایش پک ترکیبی {{ $offer->title }} در فروشگاه آریا">
@endpush

@push('styles')
<style>
.ariya-bshow{--bink:#1a2841;--bmuted:#66768e;--bline:#e4eaf3;color:var(--bink);padding:22px 0 55px;font-family:inherit}
.ariya-bshow *{box-sizing:border-box}.ariya-bshow .bshow-shell{max-width:1160px;margin:0 auto;padding:0 15px}
.ariya-bshow a{text-decoration:none}.ariya-bshow .bshow-breadcrumb{font-size:12px;color:#6b788e;margin-bottom:16px}.ariya-bshow .bshow-breadcrumb a{color:#4478ca}
.ariya-bshow .bshow-hero{display:grid;grid-template-columns:minmax(0,1fr) minmax(255px,360px);gap:22px;align-items:stretch}
.ariya-bshow .bshow-summary{padding:28px 29px;border-radius:24px;background:linear-gradient(135deg,#1e2e69 0%,#3a3ca9 60%,#6e49cf 100%);color:#fff;position:relative;overflow:hidden;min-height:350px}
.ariya-bshow .bshow-summary:before{content:"";position:absolute;width:420px;height:420px;left:-210px;top:-215px;border-radius:50%;background:rgba(255,255,255,.055);pointer-events:none}
.ariya-bshow .bshow-tag{display:inline-flex;align-items:center;background:rgba(255,255,255,.16);padding:7px 13px;border:1px solid rgba(255,255,255,.23);border-radius:99px;font-size:12px}
.ariya-bshow .bshow-summary h1{font-size:27px;line-height:1.65;margin:17px 0 11px;font-weight:850;color:white}
.ariya-bshow .bshow-description{font-size:13px;line-height:2;color:#e5eaff;max-width:620px;white-space:pre-line}
.ariya-bshow .bshow-kpis{display:flex;flex-wrap:wrap;gap:11px;margin-top:25px}.ariya-bshow .bshow-kpis span{background:rgba(255,255,255,.13);border-radius:13px;padding:12px 17px;font-size:13px}
.ariya-bshow .bshow-kpis strong{font-size:19px;margin-left:5px}
.ariya-bshow .bshow-photo{background:radial-gradient(circle at 50% 50%,#fff 30%,#e7edfc 100%);border-radius:24px;min-height:345px;display:flex;position:relative;align-items:center;justify-content:center;padding:40px;border:1px solid #dbe4f6}
.ariya-bshow .bshow-photo img{max-height:245px;max-width:95%;object-fit:contain;filter:drop-shadow(0 18px 19px rgba(38,51,98,.19))}
.ariya-bshow .bshow-photo small{position:absolute;bottom:17px;right:20px;color:#5e6b87}
.ariya-bshow .bshow-panel{background:white;border:1px solid var(--bline);border-radius:22px;padding:22px;margin-top:22px;box-shadow:0 5px 26px rgba(19,37,79,.045)}
.ariya-bshow .bshow-purchase{display:grid;grid-template-columns:minmax(0,1fr) 240px;gap:18px;align-items:center}
.ariya-bshow .bshow-price small{display:block;font-size:13px;color:var(--bmuted);margin-bottom:5px}.ariya-bshow .bshow-price strong{font-size:31px;font-weight:850;letter-spacing:-.6px;color:#e74e47}.ariya-bshow .bshow-price strong em{font-style:normal;font-size:15px;margin-right:7px}
.ariya-bshow .bshow-disabled{background:#f0f3f9;color:#4d5d75;border:1px solid #dfe6f2;border-radius:14px;padding:13px;text-align:center;font-size:13px}.ariya-bshow .bshow-disabled button{display:block;margin-top:7px;width:100%;padding:12px;border-radius:10px;border:0;background:#a7afbd;color:white;cursor:not-allowed;font-weight:800}
.ariya-bshow .bshow-title-row{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:15px}.ariya-bshow h2{font-size:20px;font-weight:800;margin:0}
.ariya-bshow .bshow-hint{font-size:12px;line-height:1.9;color:#738197}
.ariya-bshow .bshow-controls{display:flex;flex-wrap:wrap;align-items:center;gap:12px;margin:15px 0 17px}.ariya-bshow .bshow-search{min-width:230px;flex:1;max-width:520px;padding:12px 15px;border:1px solid #d5def0;border-radius:12px;outline:0;font-size:13px}.ariya-bshow .bshow-search:focus{border-color:#706ae6;box-shadow:0 0 0 3px rgba(112,106,230,.12)}
.ariya-bshow .bshow-check{display:flex;align-items:center;gap:7px;font-size:13px;color:#536179;cursor:pointer}.ariya-bshow .bshow-check input{width:16px;height:16px}
.ariya-bshow .bshow-meter{background:#f3f5fa;border-radius:14px;padding:17px;margin-bottom:19px}.ariya-bshow .bshow-meter-top{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:10px;font-size:13px}.ariya-bshow #bshow-count{font-size:21px;font-weight:800;color:#4443bd}.ariya-bshow .bshow-progress{height:10px;border-radius:20px;background:#dce3f1;overflow:hidden}.ariya-bshow #bshow-fill{height:100%;background:linear-gradient(90deg,#23b894,#6955e5);width:0;transition:width .18s}
.ariya-bshow #bshow-status{font-size:12px;margin:10px 0 0;color:#68778e;line-height:1.9}
.ariya-bshow .bshow-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:11px}
.ariya-bshow .bshow-model{border:1px solid #e1e8f3;border-radius:15px;padding:14px;display:flex;justify-content:space-between;align-items:center;gap:14px;min-height:94px;background:#fff}
.ariya-bshow .bshow-model-main{flex:1;min-width:0}.ariya-bshow .bshow-model-main h3{font-size:13px;line-height:1.8;font-weight:750;margin:0 0 3px}.ariya-bshow .bshow-model-attrs{font-size:12px;color:#717d8e;line-height:1.6}.ariya-bshow .bshow-model-badges{display:flex;gap:7px;flex-wrap:wrap;margin-top:8px;font-size:11px}
.ariya-bshow .bshow-model-badges span{background:#f2f5fc;border-radius:8px;padding:4px 7px;color:#5f6e89}.ariya-bshow .bshow-model-badges .has{background:#e6f7ee;color:#187844}.ariya-bshow .bshow-model-badges .none{background:#fff0ef;color:#d44f47}.ariya-bshow .bshow-model-badges .unknown{background:#f3f4f6;color:#646e80}
.ariya-bshow .bshow-qty{display:flex;align-items:center;gap:4px;flex-shrink:0}.ariya-bshow .bshow-qty button{border:1px solid #d8e1ef;background:#f5f7fd;cursor:pointer;font-weight:800;color:#515dd4;width:30px;height:35px;border-radius:8px}.ariya-bshow .bshow-qty button:disabled{opacity:.35;cursor:not-allowed}.ariya-bshow .bshow-qty input{width:58px;padding:7px 3px;text-align:center;direction:ltr;font-size:13px;border:1px solid #dce3ee;border-radius:8px}
.ariya-bshow .bshow-footer{display:flex;align-items:center;justify-content:center;padding-top:18px}.ariya-bshow #bshow-more{background:#f0edff;color:#5045ca;border:1px solid #ddd6ff;padding:12px 26px;border-radius:11px;cursor:pointer;font-size:13px;font-weight:700}
.ariya-bshow #bshow-more[hidden]{display:none}.ariya-bshow #bshow-alert{margin:9px 0;color:#6a7789;font-size:13px}
@media(max-width:850px){.ariya-bshow .bshow-hero{grid-template-columns:1fr}.ariya-bshow .bshow-photo{min-height:250px;order:-1}.ariya-bshow .bshow-summary{min-height:auto}.ariya-bshow .bshow-purchase{grid-template-columns:1fr}}
@media(max-width:630px){.ariya-bshow .bshow-summary{padding:23px 19px}.ariya-bshow .bshow-summary h1{font-size:21px}.ariya-bshow .bshow-grid{grid-template-columns:1fr}.ariya-bshow .bshow-panel{padding:17px 13px}.ariya-bshow .bshow-model{padding:10px;gap:7px}.ariya-bshow .bshow-qty input{width:43px}.ariya-bshow .bshow-qty button{width:27px}.ariya-bshow .bshow-price strong{font-size:26px}}

.ariya-bshow .bshow-timer{margin-top:17px;display:flex;align-items:center;gap:11px;flex-wrap:wrap;font-size:14px}
.ariya-bshow .bshow-clock{font-size:21px;font-weight:800;direction:ltr;font-variant-numeric:tabular-nums;background:rgba(255,255,255,.15);padding:7px 13px;border-radius:11px;color:white}
.ariya-bshow .bshow-costs{display:flex;align-items:center;gap:14px;flex-wrap:wrap}
.ariya-bshow .bshow-costs del{color:#8791a4;font-size:18px}.ariya-bshow .bshow-saving{display:inline-block;color:#1b875c;background:#e9f9ef;padding:7px 11px;border-radius:10px;font-weight:750}
.ariya-bshow .bshow-autofill{border:0;border-radius:12px;background:linear-gradient(110deg,#4f46d9,#147fcb);color:#fff;padding:12px 18px;font-weight:800;cursor:pointer}
.ariya-bshow .bshow-autofill:disabled{opacity:.55;cursor:wait}.ariya-bshow .bshow-picked{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin:12px 0 19px}
.ariya-bshow .bshow-picked-label{font-weight:800;font-size:15px;margin:13px 0}.ariya-bshow .bshow-picked:empty{display:none}
@media(max-width:630px){.ariya-bshow .bshow-picked{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
<main class="ariya-bshow main-content" dir="rtl">
    <div class="bshow-shell">
        <nav class="bshow-breadcrumb"><a href="{{ route('front.index') }}">خانه</a> / <a href="{{ route('front.products.discount') }}">تخفیف‌ها</a> / پک فروش سبدی</nav>
        <div class="bshow-hero">
            <section class="bshow-summary">
                <span class="bshow-tag">✦ پک ترکیبی ویژه آریا گستر</span>
                <h1>{{ $offer->title }}</h1>
                <p class="bshow-description">{{ $offer->description ?: 'مدل‌های دلخواه خود را از میان گزینه‌های این پک انتخاب و ترکیب پیشنهادی خود را بررسی کنید.' }}</p>
                <div class="bshow-kpis">
                    <span><strong>{{ number_format($offer->required_quantity) }}</strong> عدد در هر پک</span>
                    <span><strong>{{ number_format($offer->variants_count) }}</strong> مدل مجاز</span>
                    <span>قیمت ثابت برای کل پک</span>
                </div>
                <div class="bshow-timer" aria-label="زمان باقی‌مانده پیشنهاد">
                    <span>زمان باقی‌مانده پیشنهاد:</span>
                    <time class="bshow-clock" id="bshow-clock" data-expiry="{{ $offer->ends_at?->toIso8601String() ?? '' }}" aria-live="off">در حال محاسبه...</time>
                </div>
            </section>
            <div class="bshow-photo">
                <img src="{{ $coverProduct ? $coverProduct->imageUrl('/no-image-product.png') : theme_asset('images/600-600.png') }}" alt="{{ $offer->title }}">
                <small>محصولات منتخب آریا گستر</small>
            </div>
        </div>
        <section class="bshow-panel bshow-purchase" aria-label="قیمت پک">
            <div class="bshow-price">
                <small>قیمت کل پک {{ number_format($offer->required_quantity) }} عددی</small>
                @if($offer->regular_unit_price && $offer->bundle_unit_price && $offer->regular_unit_price > $offer->bundle_unit_price)
                    <div class="bshow-costs"><del>{{ number_format($offer->required_quantity * $offer->regular_unit_price) }} تومان</del>
                        <span class="bshow-saving">{{ number_format($offer->required_quantity * $offer->regular_unit_price - $offer->fixed_price) }} تومان تخفیف</span></div>
                    <small>قیمت عادی هر عدد: {{ number_format($offer->regular_unit_price) }} — قیمت سبدی هر عدد: {{ number_format($offer->bundle_unit_price) }} تومان</small>
                @endif
                <strong>{{ number_format($offer->fixed_price) }} <em>تومان</em></strong>
            </div>
            <div class="bshow-disabled"><span>پیش‌نمایش پک — خرید هنوز فعال نشده است</span><button type="button" disabled>افزودن پک به سبد خرید (به‌زودی)</button></div>
        </section>
        <section class="bshow-panel" aria-label="مدل‌های پک">
            <div class="bshow-title-row"><h2>مدل‌های پک را انتخاب کنید</h2><span class="bshow-hint">در این مرحله انتخاب‌ها فقط نمایشی هستند و سفارشی ثبت نمی‌شود.</span></div>
            <div class="bshow-meter">
                <div class="bshow-meter-top"><strong>تعداد انتخاب‌شده</strong><strong id="bshow-count">۰ / {{ number_format($offer->required_quantity) }}</strong></div>
                <div class="bshow-progress"><div id="bshow-fill"></div></div>
                <p id="bshow-status" role="status" aria-live="polite">برای تشکیل پک {{ number_format($offer->required_quantity) }} عدد انتخاب کنید.</p>
            </div>
            <div class="bshow-controls">
                <button type="button" id="bshow-autofill" class="bshow-autofill">چینش تصادفی و تکمیل {{ number_format($offer->required_quantity) }} عدد</button>
                <input class="bshow-search" id="bshow-search" placeholder="جست‌وجوی مدل یا کد مدل..." aria-label="جست‌وجوی مدل‌ها" maxlength="120">
                <label class="bshow-check"><input id="bshow-stock-only" type="checkbox"> فقط مدل‌های موجود</label>
            </div>
            <div class="bshow-picked-label">مدل‌های انتخاب‌شده — قابل کم‌وزیادکردن و حذف</div>
            <div id="bshow-picked" class="bshow-picked"></div>
            <div id="bshow-alert" role="status" aria-live="polite">در حال بارگذاری مدل‌ها...</div>
            <div id="bshow-list" class="bshow-grid" aria-live="polite"></div>
            <div class="bshow-footer"><button id="bshow-more" type="button" hidden>نمایش مدل‌های بیشتر</button></div>
        </section>
    </div>
</main>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';
    const rateLimitMessage = 'تعداد درخواست‌ها بیش از حد مجاز است. لطفاً کمی صبر کنید و دوباره تلاش کنید.';
    const root = document.querySelector('.ariya-bshow');
    if (!root) return;
    const url = @json(route('front.bundles.variants', ['bundleOffer' => $offer->id]));
    const target = {{ (int) $offer->required_quantity }};
    const requiredIds = @json($requiredPriceIds);
    const suggestionUrl = @json(route('front.bundles.suggestion', ['bundleOffer' => $offer->id]));
    const randomButton = root.querySelector('#bshow-autofill');
    const picked = root.querySelector('#bshow-picked');
    const clock = root.querySelector('#bshow-clock');
    function refreshClock() {
        if (!clock) return;
        const time = Date.parse(clock.dataset.expiry || '');
        if (!Number.isFinite(time)) { clock.textContent = 'زمان پایان تعیین نشده'; return; }
        const diff = Math.max(0, time - Date.now());
        if (diff === 0) { clock.textContent = 'مهلت این پیشنهاد پایان یافته'; return; }
        const days = Math.floor(diff / 86400000);
        const hours = Math.floor(diff % 86400000 / 3600000);
        const minutes = Math.floor(diff % 3600000 / 60000);
        const seconds = Math.floor(diff % 60000 / 1000);
        const two = x => String(x).padStart(2, '0');
        clock.textContent = days + ' روز ' + two(hours) + ':' + two(minutes) + ':' + two(seconds);
    }
    refreshClock();
    setInterval(refreshClock, 1000);

    const list = root.querySelector('#bshow-list');
    const alertBox = root.querySelector('#bshow-alert');
    const more = root.querySelector('#bshow-more');
    const search = root.querySelector('#bshow-search');
    const stockOnly = root.querySelector('#bshow-stock-only');
    const count = root.querySelector('#bshow-count');
    const fill = root.querySelector('#bshow-fill');
    const status = root.querySelector('#bshow-status');
    const selected = new Map();
    const fmt = value => Number(value).toLocaleString('fa-IR');
    let cursor = null;
    let busy = false;
    let generation = 0;
    let controller = null;
    let debounce = null;

    function recalc() {
        let total = 0, hasError = false;
        selected.forEach(row => {
            const qty = Number(row.qty);
            total += qty;
            if (qty < 0 || !Number.isInteger(qty) || (qty > 0 && qty < row.min) || (row.required && qty === 0) || (row.stock !== null && qty > row.stock)) hasError = true;
        });
        count.textContent = fmt(total) + ' / ' + fmt(target);
        fill.style.width = Math.min(100, Math.max(0, total / Math.max(1, target) * 100)) + '%';
        if (requiredIds.some(id => !selected.has(Number(id)) || selected.get(Number(id)).qty <= 0)) hasError = true;
        if (total === target && !hasError) {
            status.textContent = 'ترکیب شما کامل است. امکان خرید پس از فعال‌سازی فروش فراهم می‌شود.';
            status.style.color = '#158056';
        } else if (total > target) {
            status.textContent = fmt(total - target) + ' عدد بیشتر از ظرفیت پک انتخاب شده است.';
            status.style.color = '#c54b4b';
        } else {
            status.textContent = fmt(Math.max(0, target - total)) + ' عدد دیگر لازم است.' + (hasError ? ' حداقل‌های اجباری و موجودی را بررسی کنید.' : '');
            status.style.color = hasError ? '#c54b4b' : '#68778e';
        }
    }

    function element(tag, className, value) {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (value !== undefined && value !== null) node.textContent = String(value);
        return node;
    }

    function refreshPicked() {
        const fragment = document.createDocumentFragment();
        selected.forEach((row, id) => {
            if (row.qty > 0 && row.product_title) fragment.append(renderModel(Object.assign({price_id: id}, row)));
        });
        picked.replaceChildren(fragment);
    }
    function syncVisibleInputs(id, qty) {
        root.querySelectorAll('[data-bshow-price-id]').forEach(card => {
            if (Number(card.dataset.bshowPriceId) === Number(id)) {
                const input = card.querySelector('input[type="number"]');
                if (input && input !== document.activeElement) input.value = String(qty);
            }
        });
    }
    function renderModel(item) {
        const card = element('article', 'bshow-model');
        card.dataset.bshowPriceId = String(item.price_id);
        const body = element('div', 'bshow-model-main');
        body.append(element('h3', null, item.product_title));
        body.append(element('div', 'bshow-model-attrs', item.label || ('مدل شماره ' + item.price_id)));
        const badges = element('div', 'bshow-model-badges');
        let stockClass = 'unknown', stockText = 'موجودی نامشخص';
        if (item.stock !== null) {
            stockClass = item.stock > 0 ? 'has' : 'none';
            stockText = 'موجودی: ' + fmt(item.stock) + ' عدد';
        }
        badges.append(element('span', stockClass, stockText));
        if (item.minimum > 1) badges.append(element('span', '', 'حداقل: ' + fmt(item.minimum)));
        if (item.required) badges.append(element('span', '', 'الزامی'));
        body.append(badges);
        const qtyBox = element('div', 'bshow-qty');
        const dec = element('button', '', '−');
        dec.type = 'button'; dec.setAttribute('aria-label','کم کردن تعداد');
        const input = element('input', '');
        input.type = 'number'; input.min = '0'; input.step = '1';
        input.inputMode = 'numeric'; input.setAttribute('aria-label','تعداد انتخابی');
        const max = item.stock === null ? target : Math.min(target, Math.max(0, Number(item.stock)));
        input.max = String(max);
        const inc = element('button', '', '+');
        inc.type = 'button'; inc.setAttribute('aria-label','افزودن تعداد');
        const old = selected.get(item.price_id);
        input.value = String(old ? old.qty : 0);
        function setQty(value) {
            const qty = Math.max(0, Math.min(max, Number.isFinite(value) ? Math.trunc(value) : 0));
            input.value = String(qty);
            const previousQty = selected.get(item.price_id)?.qty || 0;
            selected.set(item.price_id, {
                qty, min: item.minimum, stock: item.stock, required: item.required,
                product_title: item.product_title, label: item.label, minimum: item.minimum
            });
            syncVisibleInputs(item.price_id, qty);
            if ((previousQty === 0 && qty > 0) || (previousQty > 0 && qty === 0)) refreshPicked();
            recalc();
        }
        input.addEventListener('input', () => setQty(Number(input.value)));
        dec.addEventListener('click', () => setQty(Number(input.value || 0) - 1));
        inc.addEventListener('click', () => setQty(Number(input.value || 0) + 1));
        inc.disabled = max === 0;
        qtyBox.append(dec, input, inc);
        card.append(body, qtyBox);
        return card;
    }

    async function load(reset = false) {
        if (reset) {
            generation++;
            if (controller) controller.abort();
            cursor = null;
            list.replaceChildren();
            more.hidden = true;
        } else if (busy || cursor === null) return;
        const run = generation;
        busy = true;
        controller = new AbortController();
        alertBox.textContent = 'در حال دریافت مدل‌ها...';
        more.disabled = true;
        const params = new URLSearchParams();
        const q = search.value.trim();
        if (q) params.set('q', q);
        if (stockOnly.checked) params.set('in_stock','1');
        if (cursor !== null) params.set('after_id', String(cursor));
        try {
            const response = await fetch(url + '?' + params.toString(), {signal: controller.signal, headers:{'Accept':'application/json'}, credentials:'same-origin'});
            const data = await response.json();
            if (!response.ok) throw new Error(response.status === 429 || /too many requests|please slow down/i.test(data.message || '') ? rateLimitMessage : (data.message || 'خطا در دریافت مدل‌ها'));
            if (run !== generation) return;
            const fragment = document.createDocumentFragment();
            (data.items || []).forEach(item => fragment.append(renderModel(item)));
            list.append(fragment);
            cursor = data.next_cursor;
            more.hidden = cursor === null;
            alertBox.textContent = list.children.length ? 'مدل‌های نمایشی: ' + fmt(list.children.length) + ' — انتخاب‌ها با تغییر فیلتر حفظ می‌شوند.' : (cursor !== null ? 'مدلی در این بخش پیدا نشد؛ مدل‌های بعدی را نمایش بدهید.' : 'مدلی با این مشخصات یافت نشد.');
        } catch (e) {
            if (e.name !== 'AbortError' && run === generation) alertBox.textContent = e.message || 'خطا در دریافت مدل‌ها. دوباره تلاش کنید.';
        } finally {
            if (run === generation) { busy = false; more.disabled = false; }
        }
    }
    randomButton.addEventListener('click', async () => {
        if (randomButton.disabled) return;
        randomButton.disabled = true;
        alertBox.textContent = 'در حال بررسی موجودی و چینش تصادفی مدل‌ها...';
        try {
            const response = await fetch(suggestionUrl, {headers:{'Accept':'application/json'}, credentials:'same-origin'});
            const data = await response.json();
            if (!response.ok) throw new Error(response.status === 429 || /too many requests|please slow down/i.test(data.message || '') ? rateLimitMessage : (data.message || 'چینش تصادفی در دسترس نیست.'));
            selected.clear();
            (data.items || []).forEach(item => selected.set(Number(item.price_id), {
                qty: Number(item.qty), min: Number(item.minimum), minimum: Number(item.minimum),
                required: Boolean(item.required), stock: item.stock,
                product_title: item.product_title, label: item.label
            }));
            refreshPicked();
            root.querySelectorAll('#bshow-list [data-bshow-price-id]').forEach(card => {
                const input = card.querySelector('input[type="number"]');
                if (input) input.value = String(selected.get(Number(card.dataset.bshowPriceId))?.qty || 0);
            });
            recalc();
            alertBox.textContent = 'چینش تصادفی آماده شد؛ می‌توانید مدل‌ها را حذف یا تعدادشان را تغییر دهید.';
        } catch (e) {
            alertBox.textContent = e.message || 'خطا در چینش خودکار.';
        } finally { randomButton.disabled = false; }
    });
    more.addEventListener('click', () => load(false));
    search.addEventListener('input', () => {clearTimeout(debounce); debounce = setTimeout(() => load(true), 330);});
    stockOnly.addEventListener('change', () => load(true));
    recalc();
    load(true);
})();
</script>
@endpush
