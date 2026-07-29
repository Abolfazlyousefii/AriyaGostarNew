@extends('back.layouts.master')

@push('styles')
<style>
    .lv-wrap { padding: 0 1rem 2rem; direction: rtl; }
    .lv-hero { display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:18px; }
    .lv-title { margin:0; color:#172b4d; font-size:24px; font-weight:800; }
    .lv-subtitle { margin:6px 0 0; color:#8392a5; font-size:13px; }
    .lv-live { display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:999px; background:#e9fff5; color:#14936b; font-size:12px; font-weight:700; }
    .lv-dot { width:9px; height:9px; border-radius:50%; background:#20c997; box-shadow:0 0 0 0 rgba(32,201,151,.5); animation:lvPulse 1.8s infinite; }
    @keyframes lvPulse { 70% { box-shadow:0 0 0 9px rgba(32,201,151,0); } 100% { box-shadow:0 0 0 0 rgba(32,201,151,0); } }
    .lv-stats { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:14px; margin-bottom:18px; }
    .lv-stat { background:#fff; border:1px solid #edf0f7; border-radius:16px; padding:18px; box-shadow:0 8px 28px rgba(31,45,61,.05); }
    .lv-stat-label { color:#8995aa; font-size:12px; }
    .lv-stat-value { margin-top:8px; color:#172b4d; font-size:27px; font-weight:900; line-height:1; }
    .lv-panel { background:#fff; border:1px solid #edf0f7; border-radius:18px; box-shadow:0 8px 30px rgba(31,45,61,.05); overflow:hidden; }
    .lv-panel-head { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:18px 20px; border-bottom:1px solid #eef1f6; }
    .lv-panel-head h3 { margin:0; color:#172b4d; font-size:17px; font-weight:800; }
    .lv-refresh { border:1px solid #dce3ed; background:#fff; border-radius:10px; padding:8px 13px; color:#5e6e82; cursor:pointer; }
    .lv-refresh:disabled { opacity:.55; cursor:wait; }
    .lv-error { display:none; margin:16px 20px 0; padding:12px 14px; border-radius:10px; background:#fff2f2; color:#c0392b; }
    .lv-table-wrap { overflow-x:auto; }
    .lv-table { width:100%; border-collapse:collapse; min-width:1050px; }
    .lv-table th { padding:13px 14px; background:#fafbfe; color:#7b8798; font-size:12px; font-weight:700; text-align:right; white-space:nowrap; }
    .lv-table td { padding:15px 14px; border-top:1px solid #f0f2f6; color:#344563; font-size:13px; vertical-align:middle; }
    .lv-user { display:flex; align-items:center; gap:10px; min-width:180px; }
    .lv-avatar { width:38px; height:38px; display:inline-flex; align-items:center; justify-content:center; flex:0 0 38px; border-radius:12px; color:#fff; background:linear-gradient(135deg,#7367f0,#32c5ff); font-weight:800; }
    .lv-name { color:#172b4d; font-weight:800; }
    .lv-phone { margin-top:3px; color:#8492a6; font-size:11px; direction:ltr; text-align:right; }
    .lv-badge { display:inline-flex; align-items:center; justify-content:center; padding:5px 9px; border-radius:999px; font-size:11px; font-weight:700; white-space:nowrap; }
    .lv-badge.user { background:#e8fff5; color:#138a65; }
    .lv-badge.guest { background:#f1f3f8; color:#67758a; }
    .lv-action { max-width:260px; color:#172b4d; font-weight:700; }
    .lv-path { display:block; max-width:270px; margin-top:4px; color:#99a5b5; font-size:11px; direction:ltr; text-align:right; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .lv-device { display:flex; align-items:center; gap:7px; white-space:nowrap; }
    .lv-device i { color:#7367f0; font-size:18px; }
    .lv-empty { padding:55px 20px; text-align:center; color:#94a0b2; }
    .lv-empty i { display:block; margin-bottom:12px; font-size:38px; color:#c7ced9; }
    @media (max-width: 991.98px) { .lv-stats { grid-template-columns:repeat(2,minmax(0,1fr)); } .lv-stat:first-child { grid-column:span 2; } }
    @media (max-width: 575.98px) { .lv-wrap { padding:0 .25rem 1.5rem; } .lv-hero { align-items:flex-start; flex-direction:column; } .lv-stats { gap:10px; } .lv-stat { padding:14px; border-radius:13px; } .lv-stat-value { font-size:23px; } }
</style>
@endpush

@section('content')
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper">
        <div class="content-header row"></div>
        <div class="content-body lv-wrap">
            <div class="lv-hero">
                <div>
                    <h1 class="lv-title">بازدیدکنندگان زنده سایت</h1>
                    <p class="lv-subtitle">نمایش فعالیت کاربران در پنج دقیقه اخیر؛ بدون ذخیره رمز، اطلاعات فرم یا محتوای پرداخت.</p>
                </div>
                <span class="lv-live"><span class="lv-dot"></span> به‌روزرسانی خودکار</span>
            </div>

            <div class="lv-stats">
                <div class="lv-stat"><div class="lv-stat-label">آنلاین</div><div class="lv-stat-value" data-stat="online">0</div></div>
                <div class="lv-stat"><div class="lv-stat-label">ثبت‌نام‌شده</div><div class="lv-stat-value" data-stat="registered">0</div></div>
                <div class="lv-stat"><div class="lv-stat-label">مهمان</div><div class="lv-stat-value" data-stat="guests">0</div></div>
                <div class="lv-stat"><div class="lv-stat-label">موبایل / تبلت</div><div class="lv-stat-value" data-stat="mobile">0</div></div>
                <div class="lv-stat"><div class="lv-stat-label">سیستم</div><div class="lv-stat-value" data-stat="desktop">0</div></div>
            </div>

            <div class="lv-panel">
                <div class="lv-panel-head">
                    <h3>فعالیت لحظه‌ای مشتری‌ها</h3>
                    <button id="lv-refresh" class="lv-refresh" type="button"><i class="feather icon-refresh-cw"></i> بروزرسانی</button>
                </div>
                <div id="lv-error" class="lv-error"></div>
                <div class="lv-table-wrap">
                    <table class="lv-table">
                        <thead>
                        <tr>
                            <th>کاربر</th>
                            <th>نوع</th>
                            <th>فعالیت فعلی</th>
                            <th>دستگاه</th>
                            <th>موقعیت تقریبی</th>
                            <th>بازدید صفحه</th>
                            <th>آخرین فعالیت</th>
                        </tr>
                        </thead>
                        <tbody id="lv-body"></tbody>
                    </table>
                    <div id="lv-empty" class="lv-empty" style="display:none">
                        <i class="feather icon-users"></i>
                        در پنج دقیقه اخیر کاربر فعالی ثبت نشده است.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    var state = {!! json_encode($initialPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!};
    var body = document.getElementById('lv-body');
    var empty = document.getElementById('lv-empty');
    var errorBox = document.getElementById('lv-error');
    var refreshButton = document.getElementById('lv-refresh');
    var endpoint = window.location.pathname + '?json=1';

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function deviceIcon(type) {
        if (type === 'mobile') return 'icon-smartphone';
        if (type === 'tablet') return 'icon-tablet';
        return 'icon-monitor';
    }

    function timeAgo(iso) {
        if (!iso) return 'همین حالا';
        var seconds = Math.max(0, Math.floor((Date.now() - new Date(iso).getTime()) / 1000));
        if (seconds < 15) return 'همین حالا';
        if (seconds < 60) return seconds + ' ثانیه پیش';
        var minutes = Math.floor(seconds / 60);
        return minutes + ' دقیقه پیش';
    }

    function render(payload) {
        payload = payload || {};
        var stats = payload.stats || {};
        document.querySelectorAll('[data-stat]').forEach(function (element) {
            element.textContent = Number(stats[element.getAttribute('data-stat')] || 0).toLocaleString('fa-IR');
        });

        if (payload.error) {
            errorBox.textContent = payload.error;
            errorBox.style.display = 'block';
        } else {
            errorBox.style.display = 'none';
        }

        var visitors = Array.isArray(payload.visitors) ? payload.visitors : [];
        body.innerHTML = '';
        empty.style.display = visitors.length ? 'none' : 'block';

        visitors.forEach(function (visitor) {
            var initials = visitor.registered && visitor.name ? visitor.name.trim().charAt(0) : 'م';
            var device = [visitor.device_name, visitor.platform, visitor.browser].filter(Boolean).join(' / ') || 'نامشخص';
            var badgeClass = visitor.registered ? 'user' : 'guest';
            var badgeText = visitor.registered ? 'عضو سایت' : 'مهمان';
            var phone = visitor.phone ? '<div class="lv-phone">' + escapeHtml(visitor.phone) + '</div>' : '';
            var product = visitor.product_title ? '<div class="lv-path">محصول: ' + escapeHtml(visitor.product_title) + '</div>' : '';

            body.insertAdjacentHTML('beforeend',
                '<tr>' +
                    '<td><div class="lv-user"><span class="lv-avatar">' + escapeHtml(initials) + '</span><div><div class="lv-name">' + escapeHtml(visitor.name || 'کاربر مهمان') + '</div>' + phone + '</div></div></td>' +
                    '<td><span class="lv-badge ' + badgeClass + '">' + badgeText + '</span></td>' +
                    '<td><div class="lv-action">' + escapeHtml(visitor.current_action || 'مشاهده سایت') + '</div>' + product + '<span class="lv-path">' + escapeHtml(visitor.current_path || '/') + '</span></td>' +
                    '<td><div class="lv-device"><i class="feather ' + deviceIcon(visitor.device_type) + '"></i><span>' + escapeHtml(device) + '</span></div></td>' +
                    '<td>' + escapeHtml(visitor.location || 'نامشخص') + '</td>' +
                    '<td>' + Number(visitor.page_views || 0).toLocaleString('fa-IR') + '</td>' +
                    '<td data-time="' + escapeHtml(visitor.last_activity_at || '') + '">' + escapeHtml(timeAgo(visitor.last_activity_at)) + '</td>' +
                '</tr>'
            );
        });
    }

    function refresh() {
        refreshButton.disabled = true;
        fetch(endpoint, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
            cache: 'no-store'
        })
        .then(function (response) {
            if (!response.ok) throw new Error('HTTP ' + response.status);
            return response.json();
        })
        .then(function (payload) {
            state = payload;
            render(payload);
        })
        .catch(function () {
            errorBox.textContent = 'دریافت اطلاعات زنده انجام نشد. چند ثانیه دیگر دوباره تلاش می‌شود.';
            errorBox.style.display = 'block';
        })
        .finally(function () {
            refreshButton.disabled = false;
        });
    }

    refreshButton.addEventListener('click', refresh);
    render(state);
    window.setInterval(refresh, 10000);
    window.setInterval(function () {
        document.querySelectorAll('[data-time]').forEach(function (element) {
            element.textContent = timeAgo(element.getAttribute('data-time'));
        });
    }, 5000);
})();
</script>
@endpush
