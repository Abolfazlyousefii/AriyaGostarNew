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
        : request()->routeIs(['login', 'register', 'membership.*']);

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
            <i class="mdi mdi-view-grid mobile-bottom-nav__icon" aria-hidden="true"></i>
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
                <span class="mobile-bottom-nav__label">ورود / عضویت</span>
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
                    <i class="mdi mdi-view-grid" aria-hidden="true"></i>
                </span>
                <span>
                    <strong>همه محصولات</strong>
                    <small>مشاهده کامل فروشگاه</small>
                </span>
                <i class="mdi mdi-chevron-left" aria-hidden="true"></i>
            </a>

            @if($mobileProductCategories->isNotEmpty())
                <div
                    class="mobile-category-accordion"
                    data-ariya-category-linear-v3="1"
                >
                    @foreach($mobileProductCategories as $category)

                        @php($children = $category->getCategories())

                        <div class="mobile-category-accordion__item">

                            <div class="mobile-category-accordion__row">

                                {{-- ARIYA_TASK02_3F19_PARENT_CATEGORY_LINK_V1 --}}
<a
                                    class="mobile-category-accordion__link" href="{{ route('front.products.category-products', ['category' => $category]) }}"
                                {{-- ARIYA_TASK19_PRODUCT_CATEGORY_ROUTE_V2 --}}
                                    title="{{ $category->title }}"
                                >
                                    <span class="mobile-category-accordion__title">
                                        {{ $category->title }}
                                    </span>
                                </a>

                                @if($children->isNotEmpty())

                                    <button
                                        type="button"
                                        class="mobile-category-accordion__toggle"
                                        data-mobile-category-toggle
                                        aria-expanded="false"
                                        aria-label="نمایش زیر دسته‌های {{ $category->title }}"
                                    >
                                        <i
                                            class="mdi mdi-chevron-down"
                                            aria-hidden="true"
                                        ></i>
                                    </button>

                                @endif

                            </div>

                            @if($children->isNotEmpty())

                                <ul
                                    class="mobile-category-accordion__children"
                                    hidden
                                >
                                    @foreach($children as $childCategory)

                                        @include(
                                            'front::partials.mobile-menu.child-category',
                                            [
                                                'category' => $childCategory
                                            ]
                                        )

                                    @endforeach
                                </ul>

                            @endif

                        </div>

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
    
        /* =========================================================
           ARIYA-MOBILE-CATEGORY-RIGHT-DRAWER-V2
           Final mobile category drawer:
           right -> left / full height
           ========================================================= */

        @media (max-width: 991.98px) {

            .mobile-category-sheet {
                position: fixed !important;
                inset: 0 !important;
                z-index: 1240 !important;
            }

            .mobile-category-sheet__backdrop {
                position: absolute !important;
                inset: 0 !important;

                background:
                    rgba(15, 23, 42, .46) !important;

                opacity: 0;

                -webkit-backdrop-filter:
                    blur(1.5px);

                backdrop-filter:
                    blur(1.5px);

                transition:
                    opacity 220ms ease !important;
            }

            .mobile-category-sheet.is-open
            .mobile-category-sheet__backdrop {
                opacity: 1 !important;
            }


            /* -----------------------------------------
               RIGHT DRAWER
               ----------------------------------------- */

            .mobile-category-sheet
            .mobile-category-sheet__panel {

                position: absolute !important;

                top: 0 !important;
                right: 0 !important;
                bottom: 0 !important;
                left: auto !important;

                display: flex !important;
                flex-direction: column;

                width: min(88vw, 380px) !important;

                height: 100vh !important;
                height: 100dvh !important;

                max-width: 380px !important;
                max-height: none !important;

                margin: 0 !important;

                padding:
                    env(safe-area-inset-top, 0px)
                    0
                    env(safe-area-inset-bottom, 0px)
                    0 !important;

                overflow: hidden !important;

                background: #fff !important;

                border: 0 !important;

                border-radius:
                    20px
                    0
                    0
                    20px !important;

                box-shadow:
                    -16px 0 46px
                    rgba(15, 23, 42, .16) !important;

                transform:
                    translate3d(104%, 0, 0) !important;

                transition:
                    transform
                    280ms
                    cubic-bezier(.22, .8, .24, 1) !important;

                will-change: transform;
            }

            .mobile-category-sheet.is-open
            .mobile-category-sheet__panel {

                transform:
                    translate3d(0, 0, 0) !important;
            }


            /* -----------------------------------------
               REMOVE BOTTOM-SHEET HANDLE
               ----------------------------------------- */

            .mobile-category-sheet__handle {
                display: none !important;
            }


            /* -----------------------------------------
               HEADER
               ----------------------------------------- */

            .mobile-category-sheet__header {

                flex: 0 0 auto;

                min-height: 76px;

                padding:
                    18px
                    18px
                    14px !important;

                background: #fff;

                border-bottom:
                    1px solid #edf1f5 !important;
            }

            .mobile-category-sheet__title {

                color: #172033 !important;

                font-size: 15px !important;
                font-weight: 700 !important;

                line-height: 1.8 !important;
            }

            .mobile-category-sheet__subtitle {

                margin-top: 2px;

                color: #98a1af !important;

                font-size: 11px !important;
                font-weight: 400 !important;
            }

            .mobile-category-sheet__close {

                display: inline-flex !important;

                align-items: center;
                justify-content: center;

                width: 40px !important;
                height: 40px !important;

                flex: 0 0 40px;

                padding: 0 !important;

                color: #697586 !important;

                font-size: 23px !important;

                background:
                    #f8fafc !important;

                border:
                    1px solid
                    #e8edf2 !important;

                border-radius: 12px !important;

                box-shadow: none !important;
            }


            /* -----------------------------------------
               SCROLLABLE BODY
               ----------------------------------------- */

            .mobile-category-sheet__body {

                flex: 1 1 auto;

                min-height: 0;

                overflow-x: hidden !important;
                overflow-y: auto !important;

                overscroll-behavior: contain;

                -webkit-overflow-scrolling: touch;

                background: #fff;
            }


            /* -----------------------------------------
               ALL PRODUCTS CARD
               ----------------------------------------- */

            .mobile-category-sheet__all {

                margin:
                    14px
                    16px
                    10px !important;

                border-radius:
                    15px !important;
            }


            /* -----------------------------------------
               CATEGORY GRID
               ----------------------------------------- */

            .mobile-category-sheet__grid {

                padding:
                    6px
                    14px
                    22px !important;

                gap:
                    18px
                    10px !important;
            }

            .mobile-category-sheet__item {

                min-width: 0;

                text-align: center;
            }

            .mobile-category-sheet__image {

                width: 72px !important;
                height: 72px !important;

                margin:
                    0
                    auto
                    7px !important;

                border-radius:
                    50% !important;
            }

            .mobile-category-sheet__image img {

                width: 100% !important;
                height: 100% !important;

                object-fit:
                    contain !important;
            }

            .mobile-category-sheet__item-title {

                color: #344054 !important;

                font-size: 11.5px !important;
                font-weight: 500 !important;

                line-height: 1.7 !important;
            }
        }


        @media (max-width: 359.98px) {

            .mobile-category-sheet
            .mobile-category-sheet__panel {

                width:
                    min(91vw, 360px) !important;
            }

            .mobile-category-sheet__header {

                padding-right:
                    14px !important;

                padding-left:
                    14px !important;
            }

            .mobile-category-sheet__grid {

                padding-right:
                    10px !important;

                padding-left:
                    10px !important;

                gap:
                    16px
                    6px !important;
            }

            .mobile-category-sheet__image {

                width: 66px !important;
                height: 66px !important;
            }
        }


        @media (prefers-reduced-motion: reduce) {

            .mobile-category-sheet__backdrop,
            .mobile-category-sheet__panel {

                transition:
                    none !important;
            }
        }


        /* ARIYA-MOBILE-CATEGORY-LINEAR-FINAL */

        @media (max-width: 991.98px) {

            .mobile-category-sheet__body {
                padding:
                    8px
                    16px
                    24px !important;
            }


            /* -----------------------------------------
               ALL PRODUCTS
               ----------------------------------------- */

            .mobile-category-sheet__all {
                min-height:
                    52px !important;

                margin:
                    0 !important;

                padding:
                    0
                    4px !important;

                background:
                    transparent !important;

                border:
                    0 !important;

                border-bottom:
                    1px solid
                    #edf0f3 !important;

                border-radius:
                    0 !important;

                box-shadow:
                    none !important;
            }

            .mobile-category-sheet__all-icon {
                display:
                    none !important;
            }


            /* -----------------------------------------
               ROOT ACCORDION
               ----------------------------------------- */

            .mobile-category-accordion {
                display:
                    block !important;

                width:
                    100% !important;
            }

            .mobile-category-accordion__item {
                display:
                    block;

                width:
                    100%;

                border-bottom:
                    1px solid
                    #edf0f3;
            }

            .mobile-category-accordion__item:last-child {
                border-bottom:
                    0;
            }


            /* -----------------------------------------
               PARENT ROW
               گارد                         فلش
               ----------------------------------------- */

            .mobile-category-accordion__row {
                display:
                    flex;

                align-items:
                    center;

                width:
                    100%;

                min-height:
                    54px;
            }

            .mobile-category-accordion__link {
                display:
                    flex;

                align-items:
                    center;

                flex:
                    1 1 auto;

                min-width:
                    0;

                min-height:
                    54px;

                padding:
                    0
                    4px;

                color:
                    #253247 !important;

                font-size:
                    13.5px;

                font-weight:
                    600;

                line-height:
                    1.8;

                text-decoration:
                    none !important;

                background:
                    transparent !important;
            }

            .mobile-category-accordion__title {
                display:
                    block;

                overflow:
                    hidden;

                white-space:
                    nowrap;

                text-overflow:
                    ellipsis;
            }


            /* -----------------------------------------
               TOGGLE
               ----------------------------------------- */

            .mobile-category-accordion__toggle {
                display:
                    inline-flex;

                align-items:
                    center;

                justify-content:
                    center;

                flex:
                    0 0 44px;

                width:
                    44px;

                height:
                    44px;

                padding:
                    0;

                color:
                    #667085;

                background:
                    transparent;

                border:
                    0;

                border-radius:
                    10px;

                cursor:
                    pointer;

                -webkit-tap-highlight-color:
                    transparent;
            }

            .mobile-category-accordion__toggle i {
                font-size:
                    21px;

                line-height:
                    1;

                transition:
                    transform
                    180ms
                    ease;
            }

            .mobile-category-accordion__toggle[
                aria-expanded="true"
            ] i {
                transform:
                    rotate(180deg);
            }


            /* -----------------------------------------
               CHILDREN
               ----------------------------------------- */

            .mobile-category-accordion__children {
                display:
                    block;

                width:
                    100%;

                margin:
                    0 !important;

                padding:
                    2px
                    12px
                    10px
                    0 !important;

                list-style:
                    none !important;

                background:
                    #fafbfc;

                border-radius:
                    0;
            }

            .mobile-category-accordion__children[hidden] {
                display:
                    none !important;
            }

            .mobile-category-accordion__children li {
                display:
                    block;

                width:
                    100%;

                margin:
                    0 !important;

                padding:
                    0 !important;

                list-style:
                    none !important;
            }

            .mobile-category-accordion__children li > a {
                display:
                    flex;

                align-items:
                    center;

                width:
                    100%;

                min-height:
                    41px;

                padding:
                    7px
                    14px !important;

                color:
                    #536174 !important;

                font-size:
                    12.5px;

                font-weight:
                    400;

                line-height:
                    1.8;

                text-decoration:
                    none !important;

                background:
                    transparent !important;

                border-bottom:
                    1px solid
                    #f0f2f5;
            }

            .mobile-category-accordion__children
            li:last-child > a {
                border-bottom:
                    0;
            }


            /* -----------------------------------------
               DEEPER LEVELS
               ----------------------------------------- */

            .mobile-category-accordion__children ul {
                display:
                    block;

                width:
                    100%;

                margin:
                    0 !important;

                padding:
                    0
                    15px
                    3px
                    0 !important;

                list-style:
                    none !important;

                border-right:
                    1px solid
                    #e4e9ef;
            }

            .mobile-category-accordion__children
            ul li > a {
                min-height:
                    37px;

                color:
                    #697586 !important;

                font-size:
                    11.5px;
            }


            /* -----------------------------------------
               OPEN CATEGORY
               ----------------------------------------- */

            .mobile-category-accordion__item.is-open
            > .mobile-category-accordion__row
            > .mobile-category-accordion__link {

                color:
                    var(--primary-color) !important;
            }

            .mobile-category-accordion__item.is-open
            > .mobile-category-accordion__row {

                background:
                    #fff;
            }
        }


        @media (max-width: 359.98px) {

            .mobile-category-sheet__body {
                padding-right:
                    12px !important;

                padding-left:
                    12px !important;
            }

            .mobile-category-accordion__link {
                font-size:
                    13px;
            }
        }

        /* =========================================================
           ARIYA-MOBILE-CATEGORY-POLISH-V4
           Parent row click + polished all-products card
           ========================================================= */

        @media (max-width: 991.98px) {

            /*
             * Parent title is now a real button,
             * but visually remains a clean text row.
             */
            .mobile-category-accordion__link {
                appearance: none !important;
                -webkit-appearance: none !important;

                width: 100% !important;

                margin: 0 !important;

                border: 0 !important;
                outline: 0;

                background: transparent !important;

                font-family: inherit !important;

                text-align: right !important;

                cursor: pointer;

                -webkit-tap-highlight-color: transparent;
            }

            .mobile-category-accordion__link:active {
                background: #f8fafc !important;
            }

            .mobile-category-accordion__row {
                transition:
                    background-color 150ms ease;
            }

            .mobile-category-accordion__item.is-open
            > .mobile-category-accordion__row {

                background:
                    #f9fbfd !important;
            }


            /*
             * -----------------------------------------
             * ALL PRODUCTS CARD
             * -----------------------------------------
             */

            .mobile-category-sheet__all {
                display: grid !important;

                grid-template-columns:
                    42px
                    minmax(0, 1fr)
                    22px !important;

                align-items: center !important;

                gap: 10px !important;

                width: 100% !important;

                min-height: 66px !important;

                margin:
                    8px
                    0
                    12px !important;

                padding:
                    10px
                    11px !important;

                color:
                    #243247 !important;

                text-decoration:
                    none !important;

                background:
                    linear-gradient(
                        135deg,
                        #f8fbff 0%,
                        #f3f8fc 100%
                    ) !important;

                border:
                    1px solid
                    #e1eaf2 !important;

                border-radius:
                    14px !important;

                box-shadow:
                    0 3px 12px
                    rgba(35, 65, 90, .045) !important;

                overflow:
                    hidden;

                transition:
                    border-color 160ms ease,
                    background-color 160ms ease,
                    transform 160ms ease;
            }

            .mobile-category-sheet__all:active {
                transform:
                    scale(.985);

                border-color:
                    #cfdde8 !important;
            }


            /*
             * Blue icon block
             */

            .mobile-category-sheet__all-icon {
                display:
                    inline-flex !important;

                align-items:
                    center !important;

                justify-content:
                    center !important;

                width:
                    42px !important;

                height:
                    42px !important;

                margin:
                    0 !important;

                color:
                    #fff !important;

                background:
                    var(--primary-color) !important;

                border:
                    0 !important;

                border-radius:
                    12px !important;

                box-shadow:
                    0 4px 10px
                    rgba(0, 126, 184, .16);
            }

            .mobile-category-sheet__all-icon i {
                color:
                    #fff !important;

                font-size:
                    23px !important;

                line-height:
                    1 !important;
            }


            /*
             * Main text
             */

            .mobile-category-sheet__all
            > span:nth-child(2) {

                display:
                    flex !important;

                flex-direction:
                    column !important;

                align-items:
                    flex-start !important;

                justify-content:
                    center !important;

                min-width:
                    0 !important;

                gap:
                    1px !important;

                margin:
                    0 !important;
            }

            .mobile-category-sheet__all strong {
                display:
                    block !important;

                width:
                    100%;

                margin:
                    0 !important;

                overflow:
                    hidden;

                color:
                    #243247 !important;

                font-size:
                    13px !important;

                font-weight:
                    700 !important;

                line-height:
                    1.7 !important;

                white-space:
                    nowrap;

                text-overflow:
                    ellipsis;
            }

            .mobile-category-sheet__all small {
                display:
                    block !important;

                width:
                    100%;

                margin:
                    1px
                    0
                    0 !important;

                overflow:
                    hidden;

                color:
                    #8995a5 !important;

                font-size:
                    10.5px !important;

                font-weight:
                    400 !important;

                line-height:
                    1.6 !important;

                white-space:
                    nowrap;

                text-overflow:
                    ellipsis;
            }


            /*
             * Left arrow
             */

            .mobile-category-sheet__all
            > i {

                display:
                    inline-flex !important;

                align-items:
                    center;

                justify-content:
                    center;

                width:
                    22px;

                height:
                    22px;

                margin:
                    0 !important;

                color:
                    #718096 !important;

                font-size:
                    18px !important;

                line-height:
                    1 !important;
            }
        }


        @media (max-width: 359.98px) {

            .mobile-category-sheet__all {

                grid-template-columns:
                    38px
                    minmax(0, 1fr)
                    20px !important;

                gap:
                    8px !important;

                min-height:
                    62px !important;

                padding:
                    9px !important;
            }

            .mobile-category-sheet__all-icon {

                width:
                    38px !important;

                height:
                    38px !important;

                border-radius:
                    11px !important;
            }

            .mobile-category-sheet__all strong {
                font-size:
                    12.5px !important;
            }

            .mobile-category-sheet__all small {
                font-size:
                    10px !important;
            }
        }

        /* =========================================================
           ARIYA-MOBILE-CART-LEFT-DRAWER-V1
           Cart opens from LEFT -> RIGHT
           ========================================================= */

        @media (max-width: 991.98px) {

            /*
             * Whole cart layer
             */
            .mobile-cart-panel {
                position: fixed !important;
                inset: 0 !important;

                z-index: 1245 !important;
            }


            /*
             * Overlay
             */
            .mobile-cart-panel__overlay {
                position: absolute !important;
                inset: 0 !important;

                width: 100% !important;
                height: 100% !important;

                background:
                    rgba(15, 23, 42, .46) !important;

                opacity: 0;

                -webkit-backdrop-filter:
                    blur(1.5px);

                backdrop-filter:
                    blur(1.5px);

                transition:
                    opacity 220ms ease !important;
            }

            .mobile-cart-panel.is-open
            .mobile-cart-panel__overlay {
                opacity: 1 !important;
            }


            /*
             * =========================================
             * LEFT DRAWER
             * =========================================
             *
             * closed:
             *  خارج صفحه در سمت چپ
             *
             * open:
             *  حرکت از چپ به راست
             */
            .mobile-cart-panel
            .mobile-cart-panel__sheet {

                position: absolute !important;

                top: 0 !important;
                right: auto !important;
                bottom: 0 !important;
                left: 0 !important;

                display: flex !important;
                flex-direction: column !important;

                width: min(88vw, 380px) !important;

                height: 100vh !important;
                height: 100dvh !important;

                max-width: 380px !important;
                max-height: none !important;

                margin: 0 !important;

                padding: 0 !important;

                overflow: hidden !important;

                background: #fff !important;

                border: 0 !important;

                /*
                 * Drawer touches left edge.
                 * Rounded corners are on its RIGHT side.
                 */
                border-radius:
                    0
                    20px
                    20px
                    0 !important;

                box-shadow:
                    16px 0 46px
                    rgba(15, 23, 42, .16) !important;

                transform: translate3d(-104%, 0, 0) !important;

                transition:
                    transform
                    280ms
                    cubic-bezier(.22, .8, .24, 1) !important;

                will-change: transform;
            }

            .mobile-cart-panel.is-open
            .mobile-cart-panel__sheet {

                transform: translate3d(0, 0, 0) !important;
            }


            /*
             * =========================================
             * HEADER
             * =========================================
             */
            .mobile-cart-panel__header {

                display: flex !important;

                flex: 0 0 auto !important;

                align-items: center !important;
                justify-content: space-between !important;

                gap: 12px !important;

                min-height: 72px !important;

                padding:
                    calc(10px + env(safe-area-inset-top, 0px))
                    16px
                    10px !important;

                background: #fff !important;

                border-bottom:
                    1px solid #edf1f5 !important;
            }

            .mobile-cart-panel__title {

                margin: 0 !important;

                color: #172033 !important;

                font-size: 15px !important;

                font-weight: 700 !important;

                line-height: 1.8 !important;
            }


            /*
             * Close button
             */
            .mobile-cart-panel__close {

                display: inline-flex !important;

                align-items: center !important;
                justify-content: center !important;

                flex: 0 0 40px !important;

                width: 40px !important;
                height: 40px !important;

                padding: 0 !important;

                color: #697586 !important;

                font-size: 23px !important;

                background:
                    #f8fafc !important;

                border:
                    1px solid
                    #e8edf2 !important;

                border-radius:
                    12px !important;

                box-shadow:
                    none !important;

                transition:
                    transform 150ms ease,
                    background-color 150ms ease;
            }

            .mobile-cart-panel__close:active {
                transform: scale(.94);
            }


            /*
             * =========================================
             * CONTENT
             * =========================================
             */
            .mobile-cart-panel__content {

                flex:
                    1 1 auto !important;

                min-height:
                    0 !important;

                padding:
                    10px
                    14px !important;

                overflow-x:
                    hidden !important;

                overflow-y:
                    auto !important;

                overscroll-behavior:
                    contain;

                -webkit-overflow-scrolling:
                    touch;

                background:
                    #fff;
            }


            /*
             * Empty cart
             */
            .mobile-cart-panel__empty {

                display:
                    flex !important;

                align-items:
                    center !important;

                justify-content:
                    center !important;

                min-height:
                    180px !important;

                margin:
                    0 !important;

                padding:
                    24px
                    12px;

                color:
                    #8a95a5 !important;

                font-size:
                    12.5px !important;

                line-height:
                    1.9 !important;

                text-align:
                    center;
            }


            /*
             * =========================================
             * PRODUCTS
             * =========================================
             */
            .mobile-cart-panel__products {

                display:
                    flex !important;

                flex-direction:
                    column !important;

                width:
                    100%;

                margin:
                    0 !important;

                padding:
                    0 !important;
            }

            .mobile-cart-panel__product.dropdown-item {

                display:
                    grid !important;

                grid-template-columns:
                    62px
                    minmax(0, 1fr) !important;

                align-items:
                    center !important;

                gap:
                    11px !important;

                width:
                    100% !important;

                min-height:
                    84px !important;

                margin:
                    0 !important;

                padding:
                    10px
                    0 !important;

                white-space:
                    normal !important;

                background:
                    #fff !important;

                border:
                    0 !important;

                border-bottom:
                    1px solid
                    #f0f2f5 !important;

                border-radius:
                    0 !important;
            }

            .mobile-cart-panel__product:last-child {
                border-bottom:
                    0 !important;
            }

            .mobile-cart-panel__product-image.dropdown-item-icon {

                display:
                    flex !important;

                align-items:
                    center !important;

                justify-content:
                    center !important;

                width:
                    62px !important;

                height:
                    62px !important;

                margin:
                    0 !important;

                padding:
                    5px !important;

                overflow:
                    hidden !important;

                background:
                    #f7f8fa !important;

                border:
                    1px solid
                    #edf0f3 !important;

                border-radius:
                    11px !important;
            }

            .mobile-cart-panel__product-image img {

                display:
                    block !important;

                width:
                    100% !important;

                height:
                    100% !important;

                object-fit:
                    contain !important;
            }

            .mobile-cart-panel__product-info.cart-title {

                min-width:
                    0 !important;

                margin:
                    0 !important;

                color:
                    #273548 !important;

                font-size:
                    11.5px !important;

                font-weight:
                    500 !important;

                line-height:
                    1.85 !important;
            }


            /*
             * =========================================
             * FOOTER
             * =========================================
             */
            .mobile-cart-panel__footer {

                flex:
                    0 0 auto !important;

                padding:
                    12px
                    14px
                    calc(
                        12px
                        + env(safe-area-inset-bottom, 0px)
                    ) !important;

                background:
                    #fff !important;

                border-top:
                    1px solid
                    #edf1f5 !important;
            }

            .mobile-cart-panel__view-cart {

                display:
                    flex !important;

                align-items:
                    center !important;

                justify-content:
                    center !important;

                width:
                    100% !important;

                min-height:
                    46px !important;

                padding:
                    9px
                    14px !important;

                color:
                    #fff !important;

                font-size:
                    12.5px !important;

                font-weight:
                    600 !important;

                line-height:
                    1.6 !important;

                text-decoration:
                    none !important;

                background:
                    var(--primary-color) !important;

                border:
                    0 !important;

                border-radius:
                    11px !important;

                box-shadow:
                    none !important;
            }

            .mobile-cart-panel__view-cart:active {

                transform:
                    scale(.985);
            }
        }


        /*
         * Very small phones
         */
        @media (max-width: 359.98px) {

            .mobile-cart-panel
            .mobile-cart-panel__sheet {

                width:
                    min(91vw, 360px) !important;
            }

            .mobile-cart-panel__header {

                padding-right:
                    13px !important;

                padding-left:
                    13px !important;
            }

            .mobile-cart-panel__content {

                padding-right:
                    12px !important;

                padding-left:
                    12px !important;
            }

            .mobile-cart-panel__footer {

                padding-right:
                    12px !important;

                padding-left:
                    12px !important;
            }
        }


        /*
         * Accessibility
         */
        @media (prefers-reduced-motion: reduce) {

            .mobile-cart-panel__overlay,
            .mobile-cart-panel__sheet,
            .mobile-cart-panel__close {

                transition:
                    none !important;
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

            function bindMobileSearchPanel() {
                var panel = document.getElementById('mobile-search-panel');

                if (!panel || panel.dataset.bound === '1') {
                    return;
                }

                panel.dataset.bound = '1';

                var openers = document.querySelectorAll('[data-mobile-search-open]');
                var closers = panel.querySelectorAll('[data-mobile-search-close]');
                var input = document.getElementById('mobile-product-search-input');
                var lastFocusedElement = null;

                function openPanel(event) {
                    if (event) {
                        event.preventDefault();
                    }

                    if (window.matchMedia('(min-width: 992px)').matches) {
                        return;
                    }

                    var categorySheet = document.getElementById('mobile-category-sheet');
                    if (categorySheet && categorySheet.classList.contains('is-open')) {
                        categorySheet.classList.remove('is-open');
                        categorySheet.setAttribute('aria-hidden', 'true');
                        document.body.classList.remove('mobile-categories-open');
                    }

                    lastFocusedElement = document.activeElement;
                    panel.classList.add('is-open');
                    panel.setAttribute('aria-hidden', 'false');
                    document.body.classList.add('mobile-search-open');

                    openers.forEach(function (opener) {
                        opener.setAttribute('aria-expanded', 'true');
                    });

                    window.setTimeout(function () {
                        if (input) {
                            input.focus({ preventScroll: true });
                            input.select();
                        }
                    }, 230);
                }

                function closePanel(event) {
                    if (event) {
                        event.preventDefault();
                    }

                    panel.classList.remove('is-open');
                    panel.setAttribute('aria-hidden', 'true');
                    document.body.classList.remove('mobile-search-open');

                    openers.forEach(function (opener) {
                        opener.setAttribute('aria-expanded', 'false');
                    });

                    if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
                        window.setTimeout(function () {
                            lastFocusedElement.focus({ preventScroll: true });
                        }, 30);
                    }
                }

                openers.forEach(function (opener) {
                    opener.addEventListener('click', openPanel);
                });

                closers.forEach(function (closer) {
                    closer.addEventListener('click', closePanel);
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape' && panel.classList.contains('is-open')) {
                        closePanel(event);
                    }
                });

                window.addEventListener('resize', function () {
                    if (window.matchMedia('(min-width: 992px)').matches && panel.classList.contains('is-open')) {
                        closePanel();
                    }
                });
            }

            function bindMobileNavigationPanels() {
                bindMobileCategorySheet();
                bindMobileSearchPanel();
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bindMobileNavigationPanels, { once: true });
            } else {
                bindMobileNavigationPanels();
            }
        })();
    </script>

    <script data-ariya-category-linear-js="1">
        (function () {

            function initAriyaCategoryAccordion() {

                var sheet =
                    document.getElementById(
                        'mobile-category-sheet'
                    );

                if (
                    !sheet ||
                    sheet.dataset.linearAccordionBound === '1'
                ) {
                    return;
                }

                sheet.dataset.linearAccordionBound = '1';

                sheet.addEventListener(
                    'click',
                    function (event) {

                        var target = event.target;

                        if (!(target instanceof Element)) {
                            return;
                        }

                        var toggle =
                            target.closest(
                                '[data-mobile-category-toggle]'
                            );

                        if (
                            !toggle ||
                            !sheet.contains(toggle)
                        ) {
                            return;
                        }

                        event.preventDefault();
                        event.stopPropagation();

                        var item =
                            toggle.closest(
                                '.mobile-category-accordion__item'
                            );

                        if (!item) {
                            return;
                        }

                        var children =
                            item.querySelector(
                                ':scope > .mobile-category-accordion__children'
                            );

                        if (!children) {
                            return;
                        }

                        var willOpen =
                            toggle.getAttribute(
                                'aria-expanded'
                            ) !== 'true';


                        /*
                         * Close other top-level categories.
                         */

                        if (willOpen) {

                            sheet
                                .querySelectorAll(
                                    '.mobile-category-accordion__item.is-open'
                                )
                                .forEach(
                                    function (otherItem) {

                                        if (
                                            otherItem === item
                                        ) {
                                            return;
                                        }

                                        var otherToggle =
                                            otherItem.querySelector(
                                                ':scope > .mobile-category-accordion__row [data-mobile-category-toggle]'
                                            );

                                        var otherChildren =
                                            otherItem.querySelector(
                                                ':scope > .mobile-category-accordion__children'
                                            );

                                        otherItem.classList.remove(
                                            'is-open'
                                        );

                                        if (otherToggle) {
                                            otherToggle.setAttribute(
                                                'aria-expanded',
                                                'false'
                                            );
                                        }

                                        if (otherChildren) {
                                            otherChildren.hidden =
                                                true;
                                        }
                                    }
                                );
                        }


                        /*
                         * Toggle selected category.
                         */

                        item.classList.toggle(
                            'is-open',
                            willOpen
                        );

                        toggle.setAttribute(
                            'aria-expanded',
                            willOpen
                                ? 'true'
                                : 'false'
                        );

                        children.hidden =
                            !willOpen;
                    }
                );
            }


            if (
                document.readyState ===
                'loading'
            ) {

                document.addEventListener(
                    'DOMContentLoaded',
                    initAriyaCategoryAccordion,
                    {
                        once: true
                    }
                );

            } else {

                initAriyaCategoryAccordion();

            }

        })();
    </script>

    <script data-ariya-category-polish-v4="1">
        (function () {

            function bindAriyaCategoryRowClick() {

                var sheet =
                    document.getElementById(
                        'mobile-category-sheet'
                    );

                if (
                    !sheet ||
                    sheet.dataset.rowToggleBound === '1'
                ) {
                    return;
                }

                sheet.dataset.rowToggleBound = '1';

                sheet.addEventListener(
                    'click',
                    function (event) {

                        var target =
                            event.target;

                        if (
                            !(target instanceof Element)
                        ) {
                            return;
                        }

                        var label =
                            target.closest(
                                '[data-mobile-category-label-toggle]'
                            );

                        if (
                            !label ||
                            !sheet.contains(label)
                        ) {
                            return;
                        }

                        event.preventDefault();
                        event.stopPropagation();

                        var item =
                            label.closest(
                                '.mobile-category-accordion__item'
                            );

                        if (!item) {
                            return;
                        }

                        var arrow =
                            item.querySelector(
                                ':scope > .mobile-category-accordion__row [data-mobile-category-toggle]'
                            );

                        if (arrow) {
                            arrow.click();
                        }
                    }
                );
            }


            if (
                document.readyState ===
                'loading'
            ) {

                document.addEventListener(
                    'DOMContentLoaded',
                    bindAriyaCategoryRowClick,
                    {
                        once: true
                    }
                );

            } else {

                bindAriyaCategoryRowClick();

            }

        })();
    </script>
@endonce
