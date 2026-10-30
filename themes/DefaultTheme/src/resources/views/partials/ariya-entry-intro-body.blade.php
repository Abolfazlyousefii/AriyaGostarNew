{{-- ARIYA_ENTRY_INTRO_V1_BODY --}}
<div id="ariya-entry-intro-v1" role="region" aria-label="خوشامدگویی فروشگاه آریا جانبی">
    <div class="ariya-entry-panel">
        <img
            class="ariya-entry-logo"
            src="{{ media_url(option('info_logo'), theme_asset('img/ariya-mobile-logo.png')) }}"
            alt="آریا جانبی"
            width="250"
            height="78"
            decoding="async"
            fetchpriority="low"
        >
        <p class="ariya-entry-title">به فروشگاه آریا جانبی خوش آمدید</p>
        <div class="ariya-entry-progress" aria-hidden="true"></div>
        <button type="button" class="ariya-entry-skip" id="ariya-entry-intro-skip-v1">رد کردن</button>
    </div>
</div>
<script id="ariya-entry-intro-runtime-v1">
(function () {
    'use strict';
    var root = document.documentElement;
    var overlay = document.getElementById('ariya-entry-intro-v1');
    if (!overlay || !root.classList.contains('ariya-entry-intro-active')) return;
    var skip = document.getElementById('ariya-entry-intro-skip-v1');
    var ending = false;
    var hideTimer;
    var cleanTimer;
    function cleanup() {
        window.clearTimeout(hideTimer);
        window.clearTimeout(cleanTimer);
        root.classList.remove('ariya-entry-intro-active');
        if (overlay && overlay.parentNode) overlay.parentNode.removeChild(overlay);
        document.removeEventListener('keydown', onKeyDown);
        window.removeEventListener('pagehide', cleanup);
        document.removeEventListener('visibilitychange', onVisibilityChange);
    }
    function finish() {
        if (ending) return;
        ending = true;
        overlay.classList.add('ariya-entry-intro-leaving');
        if (skip && document.activeElement === skip) skip.blur();
        cleanTimer = window.setTimeout(cleanup, 360);
    }
    function onKeyDown(event) {
        if (event.key === 'Escape') finish();
    }
    function onVisibilityChange() {
        if (document.hidden) cleanup();
    }
    if (skip) skip.addEventListener('click', finish);
    document.addEventListener('keydown', onKeyDown);
    document.addEventListener('visibilitychange', onVisibilityChange);
    window.addEventListener('pagehide', cleanup);
    hideTimer = window.setTimeout(finish, 1160);
})();
</script>
