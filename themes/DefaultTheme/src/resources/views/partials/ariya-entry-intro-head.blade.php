{{-- ARIYA_ENTRY_INTRO_V1_HEAD --}}
<style id="ariya-entry-intro-styles-v1">
    #ariya-entry-intro-v1 {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 2147483000;
        box-sizing: border-box;
        place-items: center;
        overflow: hidden;
        isolation: isolate;
        direction: rtl;
        background: radial-gradient(ellipse 70% 52% at 50% 42%, #f1f8ff 0%, #fff 76%);
        color: #152744;
        opacity: 0;
        transition: opacity 340ms ease-in-out;
        -webkit-font-smoothing: antialiased;
    }
    html.ariya-entry-intro-active #ariya-entry-intro-v1 {
        display: grid;
        opacity: 1;
        pointer-events: auto;
    }
    html.ariya-entry-intro-active #ariya-entry-intro-v1.ariya-entry-intro-leaving {
        opacity: 0;
        pointer-events: none;
    }
    #ariya-entry-intro-v1::before,
    #ariya-entry-intro-v1::after {
        content: '';
        position: absolute;
        inset: auto;
        width: clamp(260px, 57vw, 520px);
        height: clamp(260px, 57vw, 520px);
        border-radius: 50%;
        border: 1px solid rgba(34, 122, 218, .12);
        z-index: -1;
        pointer-events: none;
    }
    #ariya-entry-intro-v1::before {
        animation: ariya-entry-ring 1350ms ease-out both;
    }
    #ariya-entry-intro-v1::after {
        width: clamp(190px, 39vw, 360px);
        height: clamp(190px, 39vw, 360px);
        border-color: rgba(34, 122, 218, .18);
        animation: ariya-entry-ring 1200ms 80ms ease-out both;
    }
    #ariya-entry-intro-v1 .ariya-entry-panel {
        padding: 28px 18px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 16px;
        text-align: center;
        max-width: min(90vw, 460px);
    }
    #ariya-entry-intro-v1 .ariya-entry-logo {
        display: block;
        width: clamp(180px, 44vw, 250px);
        height: auto;
        max-height: 90px;
        object-fit: contain;
        animation: ariya-entry-logo 790ms cubic-bezier(.18, .82, .27, 1) both;
    }
    #ariya-entry-intro-v1 .ariya-entry-title {
        margin: 0;
        font-family: inherit;
        font-size: clamp(14px, 2.7vw, 18px);
        line-height: 1.95;
        font-weight: 700;
        letter-spacing: 0;
        color: #25476c;
        animation: ariya-entry-copy 700ms 210ms ease-out both;
    }
    #ariya-entry-intro-v1 .ariya-entry-progress {
        position: relative;
        overflow: hidden;
        width: 114px;
        height: 3px;
        border-radius: 10px;
        background: #dbe8f6;
        animation: ariya-entry-copy 500ms 280ms ease-out both;
    }
    #ariya-entry-intro-v1 .ariya-entry-progress::after {
        content: '';
        display: block;
        width: 100%;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #3fa9ff, #1876d2);
        transform-origin: right center;
        animation: ariya-entry-fill 1180ms ease-out both;
    }
    #ariya-entry-intro-v1 .ariya-entry-skip {
        display: inline-block;
        margin-top: 2px;
        padding: 7px 14px;
        border: 0;
        border-radius: 50px;
        background: transparent;
        color: #516984;
        font-family: inherit;
        font-size: 12px;
        line-height: 1.8;
        cursor: pointer;
        animation: ariya-entry-copy 550ms 380ms ease-out both;
    }
    #ariya-entry-intro-v1 .ariya-entry-skip:hover,
    #ariya-entry-intro-v1 .ariya-entry-skip:focus-visible {
        color: #1167bc;
        background: rgba(39, 128, 220, .08);
        outline: 2px solid rgba(39, 128, 220, .34);
        outline-offset: 2px;
    }
    @keyframes ariya-entry-logo {
        from { opacity: 0; transform: translate3d(0, 19px, 0) scale(.94); filter: blur(5px); }
        to   { opacity: 1; transform: translate3d(0, 0, 0) scale(1); filter: blur(0); }
    }
    @keyframes ariya-entry-copy {
        from { opacity: 0; transform: translateY(9px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes ariya-entry-ring {
        from { opacity: 0; transform: scale(.82); }
        to   { opacity: 1; transform: scale(1); }
    }
    @keyframes ariya-entry-fill {
        from { transform: scaleX(0); }
        to   { transform: scaleX(1); }
    }
    @media (prefers-reduced-motion: reduce) {
        html.ariya-entry-intro-active #ariya-entry-intro-v1 { display: none !important; }
        #ariya-entry-intro-v1, #ariya-entry-intro-v1 * {
            animation: none !important;
            transition: none !important;
        }
    }
</style>
<script id="ariya-entry-intro-bootstrap-v1">
(function () {
    'use strict';
    try {
        if (window.self !== window.top) return;
        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        if (window.sessionStorage.getItem('ariya_entry_intro_seen_v1') === '1') return;
        var nav = window.performance && window.performance.getEntriesByType ?
            window.performance.getEntriesByType('navigation')[0] : null;
        if (nav && nav.type === 'back_forward') return;
        window.sessionStorage.setItem('ariya_entry_intro_seen_v1', '1');
        document.documentElement.classList.add('ariya-entry-intro-active');
    } catch (ignored) { /* If storage/security policies block this, never delay the page. */ }
})();
</script>
