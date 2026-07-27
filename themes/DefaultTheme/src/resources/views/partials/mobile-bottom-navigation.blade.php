@php
    $isHomeActive = request()->routeIs(['front.index', '*.front.index']);
    $isSearchActive = request()->routeIs(['front.products.search', '*.front.products.search']);
    $isProductsActive = !$isSearchActive && request()->routeIs([
        'front.products.index', '*.front.products.index',
        'front.products.show', '*.front.products.show',
        'front.products.category', '*.front.products.category',
        'front.products.category-products', '*.front.products.category-products',
        'front.products.category-specials', '*.front.products.category-specials',
        'front.products.specials', '*.front.products.specials',
        'front.products.discount', '*.front.products.discount',
        'front.brands.*', '*.front.brands.*',
    ]);
    $isCartActive = request()->routeIs([
        'front.cart', '*.front.cart',
        'front.checkout', '*.front.checkout',
    ]);
    $isAccountActive = auth()->check()
        ? request()->routeIs(['front.user.*', '*.front.user.*', 'front.orders.*', '*.front.orders.*'])
        : request()->routeIs(['login', 'register']);

    /* دسته‌بندی‌های اصلی؛ نتیجه برای ۱۰ دقیقه کش می‌شود. */
    $mobileProductCategories = cache()->remember(
        'mobile-nav-product-categories-' . app()->getLocale(),
        now()->addMinutes(10),
        function () {
            return \App\Models\Category::query()
                ->published()
                ->where('type', 'productcat')
                ->where(function ($query) {
                    $query->whereNull('category_id')
                        ->orWhere('category_id', 0);
                })
                ->orderBy('ordering')
                ->orderBy('title')
                ->get(['id', 'title', 'slug', 'image']);
        }
    );
@endphp

<nav class="mobile-bottom-nav" aria-label="ناوبری اصلی موبایل">
    <div class="mobile-bottom-nav__inner">
        <a
            href="{{ route('front.index') }}"
            class="mobile-bottom-nav__item{{ $isHomeActive ? ' is-active' : '' }}"
            @if($isHomeActive) aria-current="page" @endif
        >
            <i class="mdi mdi-home-outline mobile-bottom-nav__icon" aria-hidden="true"></i>
            <span class="mobile-bottom-nav__label">خانه</span>
        </a>

        <button
            type="button"
            class="mobile-bottom-nav__item mobile-bottom-nav__button{{ $isProductsActive ? ' is-active' : '' }}"
            data-mobile-categories-open
            aria-label="نمایش دسته‌بندی‌های محصولات"
            aria-controls="mobile-category-sheet"
            aria-expanded="false"
            @if($isProductsActive) aria-current="page" @endif
        >
            <i class="mdi mdi-view-grid-outline mobile-bottom-nav__icon" aria-hidden="true"></i>
            <span class="mobile-bottom-nav__label">دسته‌بندی</span>
        </button>

        <button
            type="button"
            class="mobile-bottom-nav__item mobile-bottom-nav__button{{ $isSearchActive ? ' is-active' : '' }}"
            data-mobile-search-open
            aria-label="بازکردن جستجوی محصولات"
            aria-controls="mobile-search-panel"
            aria-expanded="false"
            @if($isSearchActive) aria-current="page" @endif
        >
            <i class="mdi mdi-magnify mobile-bottom-nav__icon" aria-hidden="true"></i>
            <span class="mobile-bottom-nav__label">جستجو</span>
        </button>

        <button
            type="button"
            class="mobile-bottom-nav__item mobile-bottom-nav__button mobile-bottom-nav__cart{{ $isCartActive ? ' is-active' : '' }}"
            data-mobile-cart-open
            aria-label="نمایش خلاصه سبد خرید"
            aria-controls="mobile-cart-panel"
            aria-expanded="false"
            @if($isCartActive) aria-current="page" @endif
        >
            <span class="mobile-bottom-nav__icon-wrap">
                <i class="mdi mdi-cart-outline mobile-bottom-nav__icon" aria-hidden="true"></i>
                <span class="mobile-bottom-nav__badge" data-mobile-cart-count hidden>0</span>
            </span>
            <span class="mobile-bottom-nav__label">سبد خرید</span>
        </button>

        @auth
            <a
                href="{{ route('front.user.profile') }}"
                class="mobile-bottom-nav__item{{ $isAccountActive ? ' is-active' : '' }}"
                @if($isAccountActive) aria-current="page" @endif
            >
                <i class="mdi mdi-account-circle-outline mobile-bottom-nav__icon" aria-hidden="true"></i>
                <span class="mobile-bottom-nav__label">حساب</span>
            </a>
        @else
            <a
                href="{{ route('login') }}"
                class="mobile-bottom-nav__item{{ $isAccountActive ? ' is-active' : '' }}"
                @if($isAccountActive) aria-current="page" @endif
            >
                <i class="mdi mdi-account-circle-outline mobile-bottom-nav__icon" aria-hidden="true"></i>
                <span class="mobile-bottom-nav__label">ورود</span>
            </a>
        @endauth
    </div>
</nav>

<div id="mobile-category-sheet" class="mobile-category-sheet" aria-hidden="true">
    <button
        type="button"
        class="mobile-category-sheet__backdrop"
        data-mobile-categories-close
        tabindex="-1"
        aria-label="بستن دسته‌بندی‌ها"
    ></button>

    <section
        class="mobile-category-sheet__panel"
        role="dialog"
        aria-modal="true"
        aria-labelledby="mobile-category-sheet-title"
    >
        <span class="mobile-category-sheet__handle" aria-hidden="true"></span>

        <header class="mobile-category-sheet__header">
            <div>
                <strong id="mobile-category-sheet-title">دسته‌بندی محصولات</strong>
                <span>دسته موردنظر را انتخاب کنید</span>
            </div>

            <button
                type="button"
                class="mobile-category-sheet__close"
                data-mobile-categories-close
                aria-label="بستن"
            >
                <i class="mdi mdi-close" aria-hidden="true"></i>
            </button>
        </header>

        <div class="mobile-category-sheet__body">
            <a href="{{ route('front.products.index') }}" class="mobile-category-sheet__all">
                <span class="mobile-category-sheet__all-icon">
                    <i class="mdi mdi-view-grid-outline" aria-hidden="true"></i>
                </span>
                <span>
                    <strong>همه محصولات</strong>
                    <small>مشاهده کامل فروشگاه</small>
                </span>
                <i class="mdi mdi-chevron-left" aria-hidden="true"></i>
            </a>

            @if($mobileProductCategories->isNotEmpty())
                <div class="mobile-category-sheet__grid">
                    @foreach($mobileProductCategories as $category)
                        <a
                            href="{{ $category->link }}"
                            class="mobile-category-sheet__item"
                            title="{{ $category->title }}"
                        >
                            <span class="mobile-category-sheet__image">
                                @if($category->image)
                                    <img
                                        src="{{ asset($category->image) }}"
                                        alt="{{ $category->title }}"
                                        loading="lazy"
                                    >
                                @else
                                    <i class="mdi mdi-shape-outline" aria-hidden="true"></i>
                                @endif
                            </span>

                            <span class="mobile-category-sheet__title">{{ $category->title }}</span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="mobile-category-sheet__empty">
                    <i class="mdi mdi-folder-open-outline" aria-hidden="true"></i>
                    <span>هنوز دسته‌بندی فعالی ثبت نشده است.</span>
                </div>
            @endif
        </div>
    </section>
</div>

@once
    <style>
        .mobile-category-sheet {
            position: fixed;
            inset: 0;
            z-index: 10060;
            visibility: hidden;
            pointer-events: none;
        }

        .mobile-category-sheet__backdrop {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            padding: 0;
            border: 0;
            background: rgba(15, 23, 42, .46);
            opacity: 0;
            transition: opacity .24s ease;
            -webkit-tap-highlight-color: transparent;
        }

        .mobile-category-sheet__panel {
            position: absolute;
            right: 0;
            bottom: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            max-height: min(78vh, 680px);
            padding: 8px 0 calc(18px + env(safe-area-inset-bottom));
            overflow: hidden;
            border-radius: 24px 24px 0 0;
            background: #fff;
            box-shadow: 0 -18px 50px rgba(15, 23, 42, .18);
            transform: translate3d(0, 104%, 0);
            transition: transform .3s cubic-bezier(.22, .8, .24, 1);
            will-change: transform;
        }

        .mobile-category-sheet.is-open {
            visibility: visible;
            pointer-events: auto;
        }

        .mobile-category-sheet.is-open .mobile-category-sheet__backdrop {
            opacity: 1;
        }

        .mobile-category-sheet.is-open .mobile-category-sheet__panel {
            transform: translate3d(0, 0, 0);
        }

        .mobile-category-sheet__handle {
            flex: 0 0 auto;
            width: 42px;
            height: 4px;
            margin: 0 auto 8px;
            border-radius: 999px;
            background: #dbe2ea;
        }

        .mobile-category-sheet__header {
            display: flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 8px 18px 14px;
            border-bottom: 1px solid #edf1f5;
        }

        .mobile-category-sheet__header > div {
            display: flex;
            min-width: 0;
            flex-direction: column;
            gap: 4px;
        }

        .mobile-category-sheet__header strong {
            color: #172033;
            font-size: 16px;
            font-weight: 800;
            line-height: 1.7;
        }

        .mobile-category-sheet__header span {
            color: #8a96a8;
            font-size: 11px;
            line-height: 1.7;
        }

        .mobile-category-sheet__close {
            display: inline-flex;
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
            align-items: center;
            justify-content: center;
            padding: 0;
            border: 1px solid #e6ebf1;
            border-radius: 12px;
            color: #526175;
            background: #fff;
            font-size: 21px;
            cursor: pointer;
        }

        .mobile-category-sheet__body {
            flex: 1 1 auto;
            padding: 14px 16px 6px;
            overflow-x: hidden;
            overflow-y: auto;
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
        }

        .mobile-category-sheet__all {
            display: grid;
            grid-template-columns: 42px minmax(0, 1fr) 22px;
            align-items: center;
            gap: 11px;
            min-height: 64px;
            margin-bottom: 14px;
            padding: 9px 11px;
            border: 1px solid #e6ebf4;
            border-radius: 16px;
            color: #1d2a3d;
            background: #f8faff;
            text-decoration: none !important;
        }

        .mobile-category-sheet__all-icon {
            display: inline-flex;
            width: 42px;
            height: 42px;
            align-items: center;
            justify-content: center;
            border-radius: 13px;
            color: #fff;
            background: linear-gradient(135deg, #36bffa, #2f6fed);
            font-size: 22px;
        }

        .mobile-category-sheet__all > span:nth-child(2) {
            display: flex;
            min-width: 0;
            flex-direction: column;
            gap: 2px;
        }

        .mobile-category-sheet__all strong {
            color: #172033;
            font-size: 13px;
            font-weight: 800;
        }

        .mobile-category-sheet__all small {
            color: #8a96a8;
            font-size: 10px;
        }

        .mobile-category-sheet__grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px 9px;
        }

        .mobile-category-sheet__item {
            display: flex;
            min-width: 0;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding: 2px;
            color: #273449;
            text-align: center;
            text-decoration: none !important;
            -webkit-tap-highlight-color: transparent;
        }

        .mobile-category-sheet__image {
            display: flex;
            width: 72px;
            height: 72px;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 1px solid #edf1f5;
            border-radius: 22px;
            color: #4c7ef3;
            background: #f7f9fc;
            font-size: 29px;
        }

        .mobile-category-sheet__image img {
            display: block;
            width: 100%;
            height: 100%;
            padding: 7px;
            object-fit: contain;
        }

        .mobile-category-sheet__title {
            display: -webkit-box;
            width: 100%;
            min-height: 34px;
            overflow: hidden;
            color: #2e3a4e;
            font-size: 11px;
            font-weight: 600;
            line-height: 1.55;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
        }

        .mobile-category-sheet__empty {
            display: flex;
            min-height: 150px;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            color: #9aa5b5;
            text-align: center;
        }

        .mobile-category-sheet__empty i {
            font-size: 38px;
        }

        body.mobile-categories-open {
            overflow: hidden !important;
            touch-action: none;
        }

        @media (min-width: 992px) {
            .mobile-category-sheet {
                display: none !important;
            }
        }

        @media (max-width: 374px) {
            .mobile-category-sheet__grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .mobile-category-sheet__image {
                width: 76px;
                height: 76px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .mobile-category-sheet__backdrop,
            .mobile-category-sheet__panel {
                transition: none !important;
            }
        }
    </style>

    <script>
        (function () {
            function bindMobileCategorySheet() {
                var sheet = document.getElementById('mobile-category-sheet');

                if (!sheet || sheet.dataset.bound === '1') {
                    return;
                }

                sheet.dataset.bound = '1';

                var openers = document.querySelectorAll('[data-mobile-categories-open]');
                var closers = sheet.querySelectorAll('[data-mobile-categories-close]');
                var closeButton = sheet.querySelector('.mobile-category-sheet__close');
                var lastFocusedElement = null;

                function openSheet() {
                    if (window.matchMedia('(min-width: 992px)').matches) {
                        return;
                    }

                    lastFocusedElement = document.activeElement;
                    sheet.classList.add('is-open');
                    sheet.setAttribute('aria-hidden', 'false');
                    document.body.classList.add('mobile-categories-open');

                    openers.forEach(function (opener) {
                        opener.setAttribute('aria-expanded', 'true');
                    });

                    window.setTimeout(function () {
                        if (closeButton) {
                            closeButton.focus({ preventScroll: true });
                        }
                    }, 40);
                }

                function closeSheet() {
                    sheet.classList.remove('is-open');
                    sheet.setAttribute('aria-hidden', 'true');
                    document.body.classList.remove('mobile-categories-open');

                    openers.forEach(function (opener) {
                        opener.setAttribute('aria-expanded', 'false');
                    });

                    if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
                        lastFocusedElement.focus({ preventScroll: true });
                    }
                }

                openers.forEach(function (opener) {
                    opener.addEventListener('click', openSheet);
                });

                closers.forEach(function (closer) {
                    closer.addEventListener('click', closeSheet);
                });

                sheet.querySelectorAll('a').forEach(function (link) {
                    link.addEventListener('click', function () {
                        document.body.classList.remove('mobile-categories-open');
                    });
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape' && sheet.classList.contains('is-open')) {
                        event.preventDefault();
                        closeSheet();
                    }
                });

                window.addEventListener('resize', function () {
                    if (window.matchMedia('(min-width: 992px)').matches && sheet.classList.contains('is-open')) {
                        closeSheet();
                    }
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bindMobileCategorySheet, { once: true });
            } else {
                bindMobileCategorySheet();
            }
        })();
    </script>
@endonce
