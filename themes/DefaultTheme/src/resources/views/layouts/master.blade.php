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

    <link rel="stylesheet" href="{{ theme_asset('css/ariya-storefront.css') }}?v=2">
    <link rel="stylesheet" href="{{ theme_asset('css/ariya-mobile-fixes.css') }}?v=20260727-1">

    @stack('styles')

    {!! option('info_header_codes') !!}
</head>

<body class="has-mobile-bottom-navigation">
    <div class="wrapper @yield('wrapper-classes')">


    @php
            $topHeaderGif = \App\Models\Banner::where('group', 'top_header_gif')
                ->where('published', 1)
                ->orderBy('ordering')
                ->first();
        @endphp

        @if($topHeaderGif)
            <div class="ariya-top-header-gif">
                @if($topHeaderGif->link)
                    <a href="{{ $topHeaderGif->link }}">
                        <img src="{{ asset($topHeaderGif->image) }}" alt="{{ $topHeaderGif->title ?: option('info_site_title', 'فروشگاه آریا') }}">
                    </a>
                @else
                    <img src="{{ asset($topHeaderGif->image) }}" alt="{{ $topHeaderGif->title ?: option('info_site_title', 'فروشگاه آریا') }}">
                @endif
            </div>
        @endif

        @include('front::partials.mobile-header')

        <!-- Start header -->
        <header class="main-header dt-sl ariya-site-header">
            <div class="container main-container">
                <div class="ariya-header-primary">
                    <div class="ariya-header-logo">
                        <a href="{{ route('front.index') }}" aria-label="{{ option('info_site_title', 'فروشگاه آریا') }}">
                            <img src="{{ option('info_logo', theme_asset('img/logo.png')) }}"
                                 alt="{{ option('info_site_title', 'فروشگاه آریا') }}">
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
        <script src="{{ theme_asset("js/scripts.js") }}?v=15"></script>
    @else
        <!-- All JS Files -->
        <script src="{{ mix('js/all.js', config('front.mainfest_path')) }}"></script>
    @endif

    <script src="{{ theme_asset('js/ariya-storefront.js') }}?v=2"></script>

    @stack('scripts')

    @if ($current_local['direction'] == 'ltr')
        <script src="{{ theme_asset('js/ltr.js') }}"></script>
    @endif

{{--    @toastr_render--}}

    {!! option('info_scripts') !!}
    <!-- endinject -->
</body>

</html>
