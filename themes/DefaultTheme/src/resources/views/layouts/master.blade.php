<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">

    <!-- viewport meta -->

    <title>
        @isset($title)
            {{ $title }} |
        @endisset

        {{ option('info_site_title', 'لاراول شاپ') }}
    </title>

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    @stack('meta')

    <!-- Favicon Icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{ option('info_icon', theme_asset('images/favicon-32x32.png')) }}">

    <!-- Font Icon -->
    <link rel="stylesheet" href="{{ theme_asset('css/vendor/materialdesignicons.min.css') }}">

    @stack('befor-styles')

    <!-- theme color file -->
    <link rel="stylesheet" href="{{ theme_asset('css/colors/' . option('dt_theme_color', 'default') . '.css') }}?v={{ time() }}">

    @if ($current_local['direction'] == 'ltr')
        <link rel="stylesheet" href="{{ theme_asset('css/vendor/bootstrap.ltr.min.css') }}">
    @else
        <link rel="stylesheet" href="{{ theme_asset('css/vendor/bootstrap.min.css') }}">
    @endif

    @if (config('app.debug'))
        <!-- inject:css -->
        <link rel="stylesheet" href="{{ theme_asset('css/vendor/bootstrap-rtl.min.css') }}">
        <!-- Plugins -->
        <link rel="stylesheet" href="{{ theme_asset('css/vendor/owl.carousel.min.css') }}">
        <link rel="stylesheet" href="{{ theme_asset('css/vendor/jquery.horizontalmenu.css') }}">
        <link rel="stylesheet" href="{{ theme_asset('js/plugins/toastr/toastr.css') }}">

        <!-- Main CSS File -->
        <link rel="stylesheet" href="{{ theme_asset('css/main.css') }}?v=23">
        <link rel="stylesheet" href="{{ theme_asset('css/styles.css') }}?v=32">
        <link rel="stylesheet" href="{{ theme_asset('css/custom.css') }}?v=2">
        <!-- endinject -->

    @else
        <!-- All Css Files -->
        <link rel="stylesheet" href="{{ mix('css/all.css', config('front.mainfest_path')) }}">
    @endif

    @if ($current_local['direction'] == 'ltr')
        <link rel="stylesheet" href="{{ theme_asset('css/ltr.css') }}?v=2">
    @endif

    <link rel="stylesheet" href="{{ theme_asset('css/ariya-storefront.css') }}?v=20260807-3">
    <link rel="stylesheet" href="{{ theme_asset('css/ariya-mobile-fixes.css') }}?v=20260727-1">

    @stack('styles')

    {{-- ARIYA_TOP_HEADER_NEWS_1920_80_V2: keep published 1920x80 news banners visible. --}}
    <style>
        /* Header news image/GIF: preserve all text; never crop it on desktop. */
        .ariya-top-header-gif {
            display: block !important;
            width: 100%;
            overflow: hidden;
            background: #fff;
            line-height: 0;
        }
        @media (min-width: 992px) {
            .ariya-top-header-gif {
                max-width: 1920px;
                height: auto !important;
                max-height: 80px;
                aspect-ratio: 24 / 1;
                margin-inline: auto;
            }
            .ariya-top-header-gif a,
            .ariya-top-header-gif img {
                display: block;
                width: 100%;
                height: 100%;
            }
            .ariya-top-header-gif img {
                object-fit: contain;
                object-position: center center;
            }
        }
        @media (max-width: 991.98px) {
            /* Respect existing mobile header sizing; no oversized desktop strip. */
            .ariya-top-header-gif {
                height: clamp(38px, 12vw, 52px) !important;
            }
        }
    </style>

    <link rel="stylesheet" href="{{ theme_asset('css/ariya-brand.css') }}?v=3">
    <link rel="stylesheet" href="{{ theme_asset('css/ariya-responsive-runtime.css') }}?v=20260807-3">

    {!! option('info_header_codes') !!}

    {{-- ARIYA-DESKTOP-HIDE-MOBILE-UI --}}
    <style>
        @media (min-width: 992px) {
            .mobile-site-header,
            .mobile-bottom-nav,
            #mobile-category-sheet,
            .mobile-category-sheet,
            #mobile-search-panel,
            .mobile-search-panel,
            #mobile-cart-panel,
            .mobile-cart-panel {
                display: none !important;
                visibility: hidden !important;
                opacity: 0 !important;
                pointer-events: none !important;
            }

            body.has-mobile-bottom-navigation {
                padding-bottom: 0 !important;
            }
        }
    </style>

    {{-- ARIYA-MOBILE-SEARCH-TOP-INLINE-V2 --}}
    <style id="ariya-mobile-search-top-inline-v2">
@media (max-width: 991.98px) {

    /* ================================================
       ARIYA MOBILE SEARCH - TOP SHEET
       ================================================ */

    .mobile-search-panel {
        z-index: 1250 !important;
    }

    .mobile-search-panel__overlay {
        background: rgba(15, 23, 42, .44) !important;

        opacity: 0;

        -webkit-backdrop-filter: blur(1.5px);
        backdrop-filter: blur(1.5px);

        transition:
            opacity 220ms ease !important;
    }

    .mobile-search-panel.is-open
    .mobile-search-panel__overlay {
        opacity: 1 !important;
    }


    /* ================================================
       SEARCH SHEET
       OLD:
       bottom: 0;
       translateY(105%);

       NEW:
       top: 0;
       translateY(-105%);
       ================================================ */

    .mobile-search-panel
    .mobile-search-panel__sheet {

        position: absolute !important;

        top: 0 !important;
        right: 0 !important;
        bottom: auto !important;
        left: 0 !important;

        display: flex !important;
        flex-direction: column;

        width: 100% !important;
        max-width: 640px !important;

        max-height: 88vh;
        max-height: 88dvh;

        margin: 0 auto !important;

        padding:
            calc(14px + env(safe-area-inset-top, 0px))
            14px
            14px !important;

        overflow: hidden !important;

        background: #fff !important;

        border: 0 !important;

        border-radius:
            0
            0
            20px
            20px !important;

        box-shadow:
            0 18px 50px rgba(15, 23, 42, .16),
            0 2px 10px rgba(15, 23, 42, .05) !important;

        transform:
            translate3d(0, -105%, 0) !important;

        transition:
            transform
            260ms
            cubic-bezier(.22, .8, .3, 1) !important;

        will-change: transform;
    }

    .mobile-search-panel.is-open
    .mobile-search-panel__sheet {

        transform:
            translate3d(0, 0, 0) !important;
    }


    /* ================================================
       HEADER
       ================================================ */

    .mobile-search-panel__header {

        flex: 0 0 auto;

        display: flex !important;

        align-items: center;
        justify-content: space-between;

        gap: 12px;

        min-height: 40px;

        margin: 0 0 12px !important;

        padding: 0 2px;
    }

    .mobile-search-panel__title {

        margin: 0 !important;

        color: #172033 !important;

        font-size: 15px !important;
        font-weight: 600 !important;

        line-height: 1.8 !important;
    }

    .mobile-search-panel__close {

        display: inline-flex !important;

        align-items: center;
        justify-content: center;

        flex: 0 0 38px;

        width: 38px !important;
        height: 38px !important;

        padding: 0 !important;

        color: #667085 !important;

        font-size: 22px !important;
        line-height: 1 !important;

        background: #f7f8fa !important;

        border:
            1px solid
            #e8ebef !important;

        border-radius: 50% !important;

        box-shadow: none !important;

        transition:
            transform 150ms ease,
            background-color 150ms ease;
    }

    .mobile-search-panel__close:active {
        transform: scale(.94);
    }


    /* ================================================
       SEARCH FIELD
       ================================================ */

    .mobile-search-panel__form {

        flex: 0 0 auto;

        width: 100%;

        margin: 0 !important;
    }

    .mobile-search-panel__field {

        position: relative;

        display: flex !important;

        align-items: center;

        gap: 9px;

        width: 100%;

        min-height: 52px !important;

        padding:
            5px
            6px
            5px
            13px !important;

        background: #f7f8fa !important;

        border:
            1px solid
            #e3e7ec !important;

        border-radius: 13px !important;

        box-shadow: none !important;

        transition:
            border-color 160ms ease,
            background-color 160ms ease,
            box-shadow 160ms ease;
    }

    .mobile-search-panel__field:focus-within {

        background: #fff !important;

        border-color:
            var(--primary-color) !important;

        box-shadow:
            0 0 0 3px
            rgba(0, 126, 184, .08) !important;
    }

    .mobile-search-panel__field-icon {

        flex: 0 0 auto;

        color: #778396 !important;

        font-size: 21px !important;

        line-height: 1 !important;
    }

    .mobile-search-panel__input {

        flex: 1 1 auto;

        min-width: 0;

        height: 40px !important;

        padding: 0 !important;

        color: #1f2937 !important;

        font-family: inherit !important;

        /*
         * prevents iOS zoom on focus
         */
        font-size: 16px !important;

        font-weight: 400 !important;

        line-height: 1.7 !important;

        background: transparent !important;

        border: 0 !important;

        outline: 0 !important;

        box-shadow: none !important;

        appearance: none;
    }

    .mobile-search-panel__input::placeholder {

        color: #98a1af !important;

        opacity: 1 !important;
    }

    .mobile-search-panel__submit {

        flex: 0 0 auto;

        min-width: 70px !important;

        height: 40px !important;

        padding:
            0
            13px !important;

        color: #fff !important;

        font-family: inherit !important;

        font-size: 12px !important;

        font-weight: 600 !important;

        background:
            var(--primary-color) !important;

        border: 0 !important;

        border-radius: 9px !important;

        box-shadow: none !important;

        appearance: none;

        transition:
            transform 150ms ease,
            opacity 150ms ease;
    }

    .mobile-search-panel__submit:active {

        opacity: .9;

        transform: scale(.97);
    }


    /* ================================================
       LIVE SEARCH RESULTS
       ================================================ */

    .mobile-live-search {

        flex: 0 1 auto;

        width: 100%;

        max-height: 56vh !important;
        max-height: 56dvh !important;

        margin-top: 12px !important;

        overflow-x: hidden !important;
        overflow-y: auto !important;

        overscroll-behavior: contain;

        -webkit-overflow-scrolling: touch;

        background: #fff !important;

        border:
            1px solid
            #e9edf2 !important;

        border-radius: 14px !important;

        box-shadow:
            0 7px 22px
            rgba(15, 23, 42, .055);
    }

    .mobile-live-search[hidden] {
        display: none !important;
    }

    .mobile-live-search__list {

        display: flex !important;

        flex-direction: column;

        width: 100%;
    }

    .mobile-live-search__item {

        display: grid !important;

        grid-template-columns:
            54px
            minmax(0, 1fr)
            auto !important;

        align-items: center;

        gap: 10px;

        min-height: 72px;

        padding:
            9px
            10px !important;

        color: #263446 !important;

        text-decoration: none !important;

        background: #fff !important;

        border-bottom:
            1px solid
            #f0f2f5 !important;

        transition:
            background-color 140ms ease;
    }

    .mobile-live-search__item:last-child {
        border-bottom: 0 !important;
    }

    .mobile-live-search__item:active {
        background: #f8fafc !important;
    }


    /* product image */

    .mobile-live-search__image {

        display: flex !important;

        align-items: center;
        justify-content: center;

        width: 54px !important;
        height: 54px !important;

        padding: 4px;

        overflow: hidden;

        background: #f7f8fa;

        border:
            1px solid
            #edf0f3;

        border-radius: 11px;
    }

    .mobile-live-search__image img {

        display: block !important;

        width: 100% !important;
        height: 100% !important;

        object-fit: contain !important;
    }


    /* product text */

    .mobile-live-search__content {

        display: block;

        min-width: 0;

        overflow: hidden;

        color: #263446;

        font-size: 12px;

        line-height: 1.85;
    }

    .mobile-live-search__content strong,
    .mobile-live-search__content b {

        display: block;

        max-width: 100%;

        overflow: hidden;

        color: #202b3c;

        font-size: 12.5px;

        font-weight: 600;

        white-space: nowrap;

        text-overflow: ellipsis;
    }

    .mobile-live-search__content small {

        display: block;

        max-width: 100%;

        margin-top: 2px;

        overflow: hidden;

        color: #8993a2;

        font-size: 10.5px;

        white-space: nowrap;

        text-overflow: ellipsis;
    }


    /* product price */

    .mobile-live-search__price {

        align-self: center;

        color: #14739e !important;

        font-size: 11px !important;

        font-weight: 600 !important;

        white-space: nowrap;
    }


    /* no result */

    .mobile-live-search__empty {

        display: flex;

        align-items: center;
        justify-content: center;

        min-height: 92px;

        padding: 16px;

        color: #7c8796;

        font-size: 12px;

        line-height: 1.9;

        text-align: center;
    }
}


/* ================================================
   SMALL MOBILE
   ================================================ */

@media (max-width: 575.98px) {

    .mobile-search-panel
    .mobile-search-panel__sheet {

        max-width: none !important;

        padding-right: 12px !important;
        padding-left: 12px !important;

        border-radius:
            0
            0
            18px
            18px !important;
    }
}


/* ================================================
   VERY SMALL MOBILE
   ================================================ */

@media (max-width: 359.98px) {

    .mobile-search-panel__submit {

        min-width: 62px !important;

        padding-right: 9px !important;
        padding-left: 9px !important;
    }

    .mobile-live-search__item {

        grid-template-columns:
            48px
            minmax(0, 1fr)
            auto !important;

        gap: 8px;
    }

    .mobile-live-search__image {

        width: 48px !important;
        height: 48px !important;
    }
}


/* ================================================
   SHORT VIEWPORT / OPEN KEYBOARD
   ================================================ */

@media
(max-width: 991.98px)
and (max-height: 620px) {

    .mobile-search-panel
    .mobile-search-panel__sheet {

        max-height: 94vh !important;
        max-height: 94dvh !important;
    }

    .mobile-live-search {

        max-height: 44vh !important;
        max-height: 44dvh !important;
    }
}


/* ================================================
   ACCESSIBILITY
   ================================================ */

@media (prefers-reduced-motion: reduce) {

    .mobile-search-panel__overlay,
    .mobile-search-panel__sheet,
    .mobile-search-panel__close,
    .mobile-search-panel__field,
    .mobile-search-panel__submit {

        transition: none !important;
    }
}

    </style>


    {{-- ARIYA_CUSTOM_SCROLLBAR_V2 --}}
    <style id="ARIYA_CUSTOM_SCROLLBAR_V2">
        /*
         * Ariya Janebi custom scrollbar
         * Desktop + internal scroll areas
         */

        * {
            scrollbar-width: thin;
            scrollbar-color: #1189d1 #f1f6fa;
        }

        ::-webkit-scrollbar {
            width: 11px;
            height: 11px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f6fa;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(
                180deg,
                #27a8ef 0%,
                #128bd2 45%,
                #0868b2 100%
            );

            border: 3px solid #f1f6fa;
            border-radius: 999px;
            min-height: 55px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(
                180deg,
                #148ed5 0%,
                #0874c2 50%,
                #05599f 100%
            );
        }

        ::-webkit-scrollbar-thumb:active {
            background: #05599f;
        }

        ::-webkit-scrollbar-corner {
            background: #f1f6fa;
        }

        @media (max-width: 767px) {
            ::-webkit-scrollbar {
                width: 7px;
                height: 7px;
            }

            ::-webkit-scrollbar-thumb {
                border-width: 2px;
            }
        }
    </style>
<style id="ariya-mobile-category-no-white-bg-v1">
/* No white category-card halos in mobile; keep category images and their color. */
@media (max-width: 991.98px) {
    .ariya-home-categories,
    .ariya-home-categories .ariya-category-slider-wrap,
    .ariya-home-categories .ariya-category-slider .owl-stage-outer,
    .ariya-home-categories .ariya-category-slider .owl-item,
    .ariya-home-categories .ariya-category-slider .item,
    .ariya-home-categories .ariya-category-item,
    .ariya-home-categories .ariya-category-image,
    .ariya-home-categories .ariya-category-image img {
        background: transparent !important;
        background-color: transparent !important;
        box-shadow: none !important;
    }
    .ariya-home-categories .ariya-category-item,
    .ariya-home-categories .ariya-category-image,
    .ariya-home-categories .ariya-category-image img {
        border: 0 !important;
    }
}
</style>
    <!-- ARIYA_ENTRY_INTRO_V1_HEAD_INCLUDE -->
    @if(request()->routeIs('front.index'))
        @include('front::partials.ariya-entry-intro-head')
    @endif
    {{-- ARIYA_PRODUCT_HOVER_SWAP_V1 --}}
    @include('front::partials.ariya-hover-assets')
{{-- ARIYA-MEGAMENU-NO-HOVER-BG:START --}}
<style id="ariya-megamenu-no-hover-bg">
/* Only the desktop mega-menu: retain icon pictures and submenu switching. */
@media (min-width: 992px) {
    .axm-sidebar .axm-category:hover,
    .axm-sidebar .axm-category:focus,
    .axm-sidebar .axm-category.is-active {
        background-color: transparent !important;
        background-image: none !important;
    }

    .axm-sidebar .axm-category-icon,
    .axm-sidebar .axm-category:hover .axm-category-icon,
    .axm-sidebar .axm-category:focus .axm-category-icon,
    .axm-sidebar .axm-category.is-active .axm-category-icon {
        background: transparent !important;
        border-color: transparent !important;
        box-shadow: none !important;
    }
}
</style>
{{-- ARIYA-MEGAMENU-NO-HOVER-BG:END --}}
</head>

<body class="has-mobile-bottom-navigation">
    <!-- ARIYA_ENTRY_INTRO_V1_BODY_INCLUDE -->
    @if(request()->routeIs('front.index'))
        @include('front::partials.ariya-entry-intro-body')
    @endif

    <div class="wrapper @yield('wrapper-classes')">


    @php
            $topHeaderGif = \App\Models\Banner::where('group', 'top_header_gif')
                ->where('published', 1)
                ->orderBy('ordering')
                ->first();
        @endphp

{{--        @if($topHeaderGif)--}}
{{--            <div class="ariya-top-header-gif">--}}
{{--                @if($topHeaderGif->link)--}}
{{--                    <a href="{{ $topHeaderGif->link }}">--}}
{{--                        <img src="{{ asset($topHeaderGif->image) }}" alt="{{ $topHeaderGif->title ?: option('info_site_title', 'فروشگاه آریا') }}">--}}
{{--                    </a>--}}
{{--                @else--}}
{{--                    <img src="{{ asset($topHeaderGif->image) }}" alt="{{ $topHeaderGif->title ?: option('info_site_title', 'فروشگاه آریا') }}">--}}
{{--                @endif--}}
{{--            </div>--}}
{{--        @endif--}}

        @include('front::partials.mobile-header')

        <!-- Start header -->
        <header class="main-header dt-sl ariya-site-header">
            <div class="container main-container">
                <div class="ariya-header-primary">
                    <div class="ariya-header-logo">
                        <a href="{{ route('front.index') }}" aria-label="{{ option('info_site_title', 'فروشگاه آریا') }}">
                            <img
    src="{{ media_url(option('info_logo'), theme_asset('img/logo.png')) }}"
    alt="{{ option('info_site_title', 'فروشگاه آریا') }}"
>
                        </a>
                    </div>

                    <div class="search-area ariya-header-search">
                        <form id="search-form" action="{{ route('front.products.search') }}" class="search">
                            <input type="text" name="q" value="{{ request('q') }}" id="search-input" autocomplete="off" placeholder="{{ trans('front::messages.header.Search-for-product') }}">
                            <button type="submit" aria-label="جستجو"><img src="{{ theme_asset('img/theme/search.png') }}" alt=""></button>
                            <button id="close-search-result" class="close-search-result" type="button" aria-label="بستن نتایج"><i class="mdi mdi-close"></i></button>
                            <div class="search-result p-0" id="search-result"><ul></ul></div>
                        </form>
                    </div>

                    <ul class="nav ariya-header-actions">
                        @include('front::partials.user-menu')
                        @include('front::partials.cart')
                    </ul>
                </div>
            </div>

            <div class="bottom-header dt-sl mb-sm-bottom-header ariya-header-navigation">
                <div class="container main-container">
                    @include('front::partials.menu.menu')
                </div>
            </div>
        </header>
        <!-- End header -->


        @yield('content')

        @include('front::partials.footer')
    </div>

    @include('front::partials.mobile-bottom-navigation')
    @include('front::partials.mobile-search')
    @include('front::partials.mobile-cart')

    <script>
        var BASE_URL = "{{ route('front.index') }}";
        var IS_RTL = {{ $current_local['direction'] == 'rtl' ? 1 : 0 }};
    </script>

    @if (config('app.debug'))
        <!-- Core JS Files -->
        <script src="{{ theme_asset('js/vendor/jquery-3.4.1.min.js') }}"></script>
        <script src="{{ theme_asset('js/vendor/popper.min.js') }}"></script>
        <script src="{{ theme_asset('js/vendor/bootstrap.min.js') }}"></script>
        <!-- Plugins -->
        <script src="{{ theme_asset('js/vendor/owl.carousel.min.js') }}"></script>
        <script src="{{ theme_asset('js/vendor/jquery.horizontalmenu.js') }}"></script>
        <script src="{{ theme_asset('js/vendor/theia-sticky-sidebar.min.js') }}"></script>
        <script src="{{ theme_asset('js/vendor/jquery.lazyloadxt.min.js') }}"></script>

        <script src="{{ theme_asset("js/plugins/jquery.blockUI.js") }}"></script>
        <script src="{{ theme_asset("js/plugins/sweetalert2.all.min.js") }}"></script>
        <script src="{{ theme_asset("js/plugins/toastr/toastr.min.js") }}"></script>

        <!-- Main JS File -->
        <script src="{{ theme_asset('js/main.js') }}?v=5"></script>
        <script src="{{ theme_asset('js/custom.js') }}"></script>
        <script src="{{ theme_asset("js/scripts.js") }}?v=20261026-guest-price-1"></script>
    @else
        <!-- All JS Files -->
        <script src="{{ mix('js/all.js', config('front.mainfest_path')) }}"></script>
    @endif

    <script src="{{ theme_asset('js/ariya-storefront.js') }}?v=stage07-20261022-152830-1770416"></script>
    <script src="{{ theme_asset('js/ariya-responsive-runtime.js') }}?v=20260807-3"></script>

    <script>
        window.ARIYA_LIVE_ANALYTICS = {
            endpoint: @json(route('live-analytics.collect')),
            csrf: @json(csrf_token())
        };
    </script>
    <script src="{{ theme_asset('js/live-visitor-tracker.js') }}?v=20260728-1" defer></script>

    @include('front::partials.customer-journey')

    @stack('scripts')

    @if ($current_local['direction'] == 'ltr')
        <script src="{{ theme_asset('js/ltr.js') }}"></script>
    @endif

{{--    @toastr_render--}}

    {!! option('info_scripts') !!}
    <!-- endinject -->
    <script src="{{ theme_asset('js/ariya-live-search.js') }}?v=20261026-guest-price-1"></script>
<script type="text/javascript">
  !function(){var i="5dJNBY",d=document,g=d.createElement("script"),s="https://www.goftino.com/widget/"+i,l=localStorage.getItem("goftino_"+i);g.type="text/javascript",g.async=!0,g.src=l?s+"?o="+l:s;d.getElementsByTagName("head")[0].appendChild(g);}();
</script>
    <!-- ARIYA_GOFTINO_MOBILE_BOTTOM_NAV_V1 -->
    <script id="ariya-goftino-mobile-bottom-nav-v1">
    (function () {
        'use strict';
        var lastMargin = null;
        var changedOnMobile = false;
        var resizeTimer = null;

        function updateGoftinoPosition() {
            if (!window.Goftino || typeof window.Goftino.setWidget !== 'function') {
                return;
            }

            if (!window.matchMedia('(max-width: 991.98px)').matches) {
                if (changedOnMobile) {
                    window.Goftino.setWidget({ marginBottom: 20 });
                    changedOnMobile = false;
                    lastMargin = null;
                }
                return;
            }

            var nav = document.querySelector('.mobile-bottom-nav');
            var height = nav ? nav.getBoundingClientRect().height : 64;
            var margin = Math.max(88, Math.ceil(height) + 18);
            if (lastMargin === margin) {
                return;
            }
            window.Goftino.setWidget({ marginBottom: margin });
            lastMargin = margin;
            changedOnMobile = true;
        }

        window.addEventListener('goftino_ready', updateGoftinoPosition);
        window.addEventListener('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(updateGoftinoPosition, 120);
        });
        window.addEventListener('orientationchange', updateGoftinoPosition);
        updateGoftinoPosition();
    })();
    </script>
<script id="ariya-goftino-popup-category-autoplay-v1">
(function () {
    'use strict';

    // ARIYA_GOFTINO_OVERLAY_CATEGORY_AUTOPLAY_V1
    var mobile = window.matchMedia('(max-width: 991.98px)');
    var lastChatState = null;
    var updatePending = false;
    var owlSnapshot = null;

    var blockingBodyClasses = [
        'mobile-categories-open',
        'mobile-cart-open',
        'mobile-search-open',
        'modal-open',
        'offcanvas-open',
        'fancybox-active',
        'swal2-shown',
        'mfp-zoom-out-cur'
    ];

    var blockingSelectors = [
        '.mobile-category-sheet.is-open',
        '.mobile-cart-panel.is-open',
        '.mobile-search-panel.is-open',
        '.modal.show',
        '.modal.in',
        '.offcanvas.show',
        '.offcanvas.showing',
        '.swal2-container.swal2-backdrop-show',
        '.swal2-container.swal2-shown',
        '.fancybox-container.fancybox-is-open',
        '.mfp-wrap',
        'dialog[open]',
        '[role="dialog"][aria-modal="true"]',
        '[role="alertdialog"][aria-modal="true"]'
    ];

    function visible(element) {
        if (!element || !element.isConnected) return false;
        if (element.closest('[id^="goftino"], [class*="goftino-"]')) return false;
        var style = window.getComputedStyle(element);
        if (style.display === 'none' || style.visibility === 'hidden') return false;
        return element.getClientRects().length > 0;
    }

    function hasActivePopup() {
        var body = document.body;
        if (!body) return false;

        for (var i = 0; i < blockingBodyClasses.length; i++) {
            if (body.classList.contains(blockingBodyClasses[i])) return true;
        }

        var nodes = document.querySelectorAll(blockingSelectors.join(','));
        for (var j = 0; j < nodes.length; j++) {
            if (visible(nodes[j])) return true;
        }
        return false;
    }

    function updateChatLauncher() {
        if (!window.Goftino || typeof window.Goftino.setWidget !== 'function') return;

        var hidden = hasActivePopup();
        var mobileMode = mobile.matches;
        var margin = null;
        var settings = { hasIcon: !hidden };

        if (mobileMode) {
            var nav = document.querySelector('.mobile-bottom-nav');
            var navHeight = nav ? nav.getBoundingClientRect().height : 64;
            margin = Math.max(88, Math.ceil(navHeight) + 18);
            settings.marginBottom = margin;
        }

        var key = (hidden ? 'hidden' : 'visible') + ':' + (mobileMode ? margin : 'desktop');
        if (lastChatState === key) return;
        window.Goftino.setWidget(settings);
        lastChatState = key;
    }

    function scheduleChatSync() {
        if (updatePending) return;
        updatePending = true;
        window.requestAnimationFrame(function () {
            updatePending = false;
            updateChatLauncher();
        });
    }

    function updateCategoryAutoplay() {
        if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.owlCarousel) return;
        var element = document.querySelector('.ariya-home-categories .ariya-category-slider.owl-carousel') ||
            document.querySelector('.ariya-category-slider.owl-carousel');
        if (!element || !element.classList.contains('owl-loaded')) return;

        var $carousel = window.jQuery(element);
        var owl = $carousel.data('owl.carousel');
        if (!owl || !owl.options || typeof owl.invalidate !== 'function' || typeof owl.refresh !== 'function') return;

        if (owlSnapshot && owlSnapshot.element !== element) owlSnapshot = null;
        if (mobile.matches) {
            if (owlSnapshot && owlSnapshot.active) return;
            if (!owlSnapshot) {
                owlSnapshot = {
                    element: element,
                    active: false,
                    original: {
                        autoplay: owl.options.autoplay,
                        autoplayTimeout: owl.options.autoplayTimeout,
                        autoplayHoverPause: owl.options.autoplayHoverPause,
                        loop: owl.options.loop,
                        responsive: owl.options.responsive
                    }
                };
            }

            var options = owl.options;
            var responsive = Object.assign({}, options.responsive || {});
            Object.keys(responsive).forEach(function (point) {
                if (Number(point) <= 991) {
                    responsive[point] = Object.assign({}, responsive[point], {
                        autoplay: true,
                        autoplayTimeout: 3000,
                        autoplayHoverPause: false,
                        loop: true
                    });
                }
            });
            options.responsive = responsive;
            options.autoplay = true;
            options.autoplayTimeout = 3000;
            options.autoplayHoverPause = false;
            options.loop = true;
            $carousel.trigger('stop.owl.autoplay');
            owl.invalidate('settings');
            owl.refresh();
            $carousel.trigger('play.owl.autoplay', [3000]);
            owlSnapshot.active = true;
        } else if (owlSnapshot && owlSnapshot.active) {
            var original = owlSnapshot.original;
            owl.options.autoplay = original.autoplay;
            owl.options.autoplayTimeout = original.autoplayTimeout;
            owl.options.autoplayHoverPause = original.autoplayHoverPause;
            owl.options.loop = original.loop;
            owl.options.responsive = original.responsive;
            $carousel.trigger('stop.owl.autoplay');
            owl.invalidate('settings');
            owl.refresh();
            if (original.autoplay) {
                $carousel.trigger('play.owl.autoplay', [original.autoplayTimeout || 5000]);
            }
            owlSnapshot.active = false;
        }
    }

    function onViewportChange() {
        scheduleChatSync();
        updateCategoryAutoplay();
    }

    function init() {
        if (!document.body) return;
        var observer = new MutationObserver(scheduleChatSync);
        observer.observe(document.body, {
            subtree: true,
            childList: true,
            attributes: true,
            attributeFilter: ['class', 'style', 'aria-hidden', 'aria-modal', 'open']
        });

        window.addEventListener('goftino_ready', function () {
            lastChatState = null;
            scheduleChatSync();
        });
        // ARIYA_GOFTINO_RESTORE_ON_CLOSE_V1
        // Goftino may hide its own launcher while the window is open.
        // Re-sync after closing even when the page DOM has not changed.
        window.addEventListener('goftino_closeWidget', function () {
            lastChatState = null;
            scheduleChatSync();
            window.setTimeout(function () {
                lastChatState = null;
                scheduleChatSync();
            }, 250);
        });
        window.addEventListener('resize', onViewportChange);
        window.addEventListener('orientationchange', onViewportChange);
        document.addEventListener('visibilitychange', scheduleChatSync);
        scheduleChatSync();
        updateCategoryAutoplay();
        window.addEventListener('load', updateCategoryAutoplay);

        // Owl Carousel can initialize after DOMContentLoaded: retry briefly.
        var attempts = 0;
        var waitForOwl = window.setInterval(function () {
            attempts++;
            updateCategoryAutoplay();
            if (owlSnapshot || attempts >= 15) window.clearInterval(waitForOwl);
        }, 600);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, { once: true });
    } else {
        init();
    }
})();
</script>
</body>

</html>
