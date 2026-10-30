<header class="mobile-site-header" aria-label="سربرگ موبایل">
    <div class="mobile-site-header__inner">
        <button
            type="button"
            class="ariya-mobile-menu-launcher"
            aria-label="باز کردن منوی اصلی"
            aria-haspopup="true"
        >
            <i class="mdi mdi-menu" aria-hidden="true"></i>
        </button>
        <a
            href="{{ route('front.index') }}"
            class="mobile-site-header__logo"
            aria-label="{{ option('info_site_title', 'فروشگاه آریا') }}"
        >
            <img
                src="{{ media_url(option('info_logo'), theme_asset('img/ariya-mobile-logo.png')) }}"
                alt="{{ option('info_site_title', 'فروشگاه آریا') }}"
                loading="eager"
                decoding="async"
                fetchpriority="high"
            >
        </a>
    </div>
</header>

<div class="ariya-mobile-popup-overlay" hidden></div>

<div
    class="ariya-mobile-popup"
    dir="rtl"
    hidden
    aria-hidden="true"
>
    <div
        class="ariya-mobile-popup__panel"
        role="dialog"
        aria-modal="true"
        aria-label="فهرست صفحات"
    >
        <div class="ariya-mobile-popup__header">
            <strong class="ariya-mobile-popup__title">فهرست صفحات</strong>

            <button
                type="button"
                class="ariya-mobile-popup__close"
                aria-label="بستن"
            >
                <i class="mdi mdi-close" aria-hidden="true"></i>
            </button>
        </div>

        <nav class="ariya-mobile-popup__nav" aria-label="فهرست صفحات">
            <ul class="ariya-mobile-popup__list"></ul>
        </nav>
    </div>
</div>

{{-- ARIYA_TASK02_3F18_POPUP_MENU_V1 --}}
@once
<style>
    .ariya-mobile-menu-launcher,
    .ariya-mobile-popup,
    .ariya-mobile-popup-overlay {
        display: none !important;
    }

    @media (max-width: 991.98px) {
        .mobile-site-header__inner {
            position: relative;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-height: 64px;
            padding-right: 56px !important;
            padding-left: 56px !important;
            gap: 0 !important;
        }

        .mobile-site-header__inner .mobile-site-header__logo {
            display: flex !important;
            align-items: center;
            justify-content: center;
            margin: 0 auto !important;
            text-align: center;
            min-width: 0;
            max-width: 100%;
        }

        .mobile-site-header__inner .mobile-site-header__logo img,
        .mobile-site-header__inner .mobile-site-header__logo svg {
            display: block;
            margin: 0 auto;
            max-height: 42px;
            width: auto;
            max-width: 100%;
        }

        .mobile-site-header__inner .ariya-mobile-menu-launcher {
            display: inline-flex !important;
            position: absolute;
            top: 50%;
            right: 0;
            transform: translateY(-50%);
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            min-height: 42px;
            border: 1px solid #d8e5f4;
            border-radius: 14px;
            background: #f4f8ff;
            color: #146aa3;
            padding: 0;
            margin: 0;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(20, 106, 163, 0.10);
            z-index: 5;
        }

        .mobile-site-header__inner .ariya-mobile-menu-launcher i {
            font-size: 24px;
            line-height: 1;
        }

        .mobile-site-header__inner .ariya-mobile-menu-launcher:focus-visible,
        .ariya-mobile-popup__close:focus-visible {
            outline: 2px solid #146aa3;
            outline-offset: 3px;
        }

        /* Hide original side menu trigger visually. */
        .ariya-header-navigation .btn-menu {
            display: none !important;
        }

        .ariya-mobile-popup-overlay {
            display: block !important;
            position: fixed;
            inset: 0;
            background: rgba(17, 25, 54, 0.42);
            opacity: 0;
            visibility: hidden;
            transition: opacity .25s ease, visibility .25s ease;
            z-index: 1198;
        }

        .ariya-mobile-popup-overlay.is-open {
            opacity: 1;
            visibility: visible;
        }

        .ariya-mobile-popup {
            display: block !important;
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 1199;
        }

        .ariya-mobile-popup__panel {
            position: absolute;
            top: 0;
            right: 0;
            width: min(320px, 88vw);
            max-width: 100%;
            height: 100%;
            background: #fff;
            box-shadow: -18px 0 38px rgba(14, 30, 60, 0.16);
            transform: translateX(100%);
            transition: transform .28s ease;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            direction: rtl;
            text-align: right;
            pointer-events: auto;
        }

        .ariya-mobile-popup.is-open .ariya-mobile-popup__panel {
            transform: translateX(0);
        }

        .ariya-mobile-popup__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 18px 18px 14px;
            border-bottom: 1px solid #edf2f7;
            background: linear-gradient(135deg, #f8fbff, #eef6ff);
        }

        .ariya-mobile-popup__title {
            font-size: 16px;
            font-weight: 700;
            color: #111936;
        }

        .ariya-mobile-popup__close {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border: 1px solid #d8e5f4;
            border-radius: 12px;
            background: #fff;
            color: #146aa3;
            padding: 0;
            cursor: pointer;
        }

        .ariya-mobile-popup__close i {
            font-size: 22px;
            line-height: 1;
        }

        .ariya-mobile-popup__nav {
            flex: 1 1 auto;
            overflow-y: auto;
            padding: 14px 14px 24px;
            background: #fff;
        }

        .ariya-mobile-popup__list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .ariya-mobile-popup__list li {
            margin: 0;
            padding: 0;
        }

        .ariya-mobile-popup__list a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            width: 100%;
            min-height: 46px;
            padding: 12px 14px;
            border: 1px solid #e6eef8;
            border-radius: 14px;
            background: #f9fbff;
            color: #17203f;
            text-decoration: none !important;
            font-size: 14px;
            font-weight: 600;
            transition: background .2s ease, border-color .2s ease, color .2s ease, transform .2s ease;
        }

        .ariya-mobile-popup__list a::before {
            content: "\F0142";
            font-family: "Material Design Icons";
            font-size: 18px;
            color: #146aa3;
            flex: 0 0 auto;
        }

        .ariya-mobile-popup__list a:hover,
        .ariya-mobile-popup__list a:focus {
            background: #eef6ff;
            border-color: #cfe1f4;
            color: #0d4f84;
            transform: translateX(-1px);
            text-decoration: none !important;
        }

        body.ariya-mobile-popup-open {
            overflow: hidden;
        }
    }
</style>

<script>
(function () {
    'use strict';

    function normalizeText(value) {
        return (value || '').replace(/\s+/g, ' ').trim();
    }

    function collectTopLinks() {
        var selectors = [
            '.ariya-site-header .ariya-desktop-menu-list > li > a',
            '.ariya-main-menu .list > li > a',
            '.side-menu a'
        ];

        var links = [];
        var seen = Object.create(null);

        selectors.forEach(function (selector) {
            document.querySelectorAll(selector).forEach(function (anchor) {
                var href = (anchor.getAttribute('href') || '').trim();
                var text = normalizeText(anchor.textContent);

                if (!href || href === '#' || href === 'javascript:void(0)' || !text) {
                    return;
                }

                if (
                    anchor.closest('.mobile-bottom-nav') ||
                    anchor.classList.contains('btn-menu')
                ) {
                    return;
                }

                var key = text + '|' + href;

                if (seen[key]) {
                    return;
                }

                seen[key] = true;
                links.push({
                    href: href,
                    text: text
                });
            });
        });

        return links;
    }

    function initMobilePopupMenu() {
        var launcher = document.querySelector('.ariya-mobile-menu-launcher');
        var overlay = document.querySelector('.ariya-mobile-popup-overlay');
        var popup = document.querySelector('.ariya-mobile-popup');
        var panel = popup ? popup.querySelector('.ariya-mobile-popup__panel') : null;
        var closeButton = popup ? popup.querySelector('.ariya-mobile-popup__close') : null;
        var list = popup ? popup.querySelector('.ariya-mobile-popup__list') : null;

        if (!launcher || !overlay || !popup || !panel || !closeButton || !list) {
            return;
        }

        if (launcher.dataset.popupReady === '1') {
            return;
        }

        launcher.dataset.popupReady = '1';

        function buildList() {
            /*
             * Exactly five top-level pages.
             * Never copy nested product categories into this popup.
             */

            var contactHref = '';

            document.querySelectorAll('.side-menu a').forEach(
                function (anchor) {
                    if (
                        normalizeText(anchor.textContent) === 'تماس با ما' &&
                        !contactHref
                    ) {
                        var href = (anchor.getAttribute('href') || '').trim();

                        if (
                            href &&
                            href !== '#' &&
                            !/^javascript:/i.test(href)
                        ) {
                            contactHref = href;
                        }
                    }
                }
            );

            var items = [
                {
                    text: 'فروشگاه',
                    href: '{{ route('front.products.index') }}'
                },
                {
                    text: 'دسته‌بندی محصولات',
                    href: '{{ route('front.products.index') }}',
                    categorySheet: true
                },
                {
                    text: 'خرید عمده',
                    href: '{{ route('front.pages.show', ['page' => 'wholesale-guide']) }}'
                },
                {
                    text: 'راهنما و وبلاگ',
                    href: '{{ route('front.posts.index') }}'
                },
                {
                    text: 'تماس با ما',
                    href: contactHref || '{{ url('/contact') }}'
                }
            ];

            list.innerHTML = '';
            list.setAttribute('dir', 'rtl');

            items.forEach(function (item) {
                var li = document.createElement('li');
                var a = document.createElement('a');

                a.href = item.href;
                a.textContent = item.text;

                if (item.categorySheet) {
                    a.addEventListener('click', function (event) {
                        var categoryTrigger = document.querySelector(
                            '[data-mobile-categories-open]'
                        );

                        if (!categoryTrigger) {
                            return;
                        }

                        event.preventDefault();

                        closePopup();

                        window.setTimeout(function () {
                            categoryTrigger.click();
                        }, 320);
                    });
                }

                li.appendChild(a);
                list.appendChild(li);
            });
        }

        function openPopup() {
            buildList();

            overlay.hidden = false;
            popup.hidden = false;
            popup.setAttribute('aria-hidden', 'false');
            launcher.setAttribute('aria-expanded', 'true');
            document.body.classList.add('ariya-mobile-popup-open');

            requestAnimationFrame(function () {
                overlay.classList.add('is-open');
                popup.classList.add('is-open');
            });
        }

        function closePopup() {
            overlay.classList.remove('is-open');
            popup.classList.remove('is-open');
            popup.setAttribute('aria-hidden', 'true');
            launcher.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('ariya-mobile-popup-open');

            window.setTimeout(function () {
                overlay.hidden = true;
                popup.hidden = true;
            }, 280);
        }

        launcher.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            openPopup();
        });

        closeButton.addEventListener('click', function (event) {
            event.preventDefault();
            closePopup();
        });

        overlay.addEventListener('click', closePopup);

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !popup.hidden) {
                closePopup();
            }
        });

        popup.addEventListener('click', function (event) {
            if (!panel.contains(event.target)) {
                closePopup();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMobilePopupMenu, { once: true });
    } else {
        initMobilePopupMenu();
    }
})();
</script>
@endonce

<!-- ARIYA_TASK02_3F18_FIVE_PAGES_V3 -->
<style>
@media (max-width: 991.98px) {

    .mobile-site-header__inner .ariya-mobile-menu-launcher {
        right: 14px !important;
    }

    .ariya-mobile-popup__nav {
        direction: rtl !important;
        text-align: right !important;
    }

    .ariya-mobile-popup__list {
        display: flex !important;
        flex-direction: column !important;
        gap: 10px !important;
        direction: rtl !important;
        list-style: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .ariya-mobile-popup__list > li {
        display: block !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
    }

    .ariya-mobile-popup__list > li > a {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-start !important;
        width: 100% !important;
        min-height: 48px !important;
        padding: 12px 14px !important;
        background: #f9fbff !important;
        border: 1px solid #e6eef8 !important;
        border-radius: 14px !important;
        color: #17203f !important;
        font-size: 14px !important;
        font-weight: 700 !important;
        line-height: 1.7 !important;
        direction: rtl !important;
        text-align: right !important;
        text-decoration: none !important;
        box-sizing: border-box !important;
    }

    /* ARIYA_TASK18_RTL_TEXT_V4 */
    .ariya-mobile-popup__list > li > a::before {
        order: 2 !important;
        margin-right: auto !important;
        margin-left: 0 !important;
    }

    .ariya-mobile-popup__list > li > a:hover,
    .ariya-mobile-popup__list > li > a:focus-visible {
        background: #eef6ff !important;
        border-color: #cfe1f4 !important;
        color: #0d4f84 !important;
    }
}
</style>
