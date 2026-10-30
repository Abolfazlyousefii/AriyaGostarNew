@extends('front::layouts.master')

@push('meta')
@if(option('allow_indexing_main_page') == "off")
    <meta name="robots" content="noindex, nofollow">
    <meta name="googlebot" content="noindex, nofollow">
    <meta name="bingbot" content="noindex, nofollow">
@else
    <meta name="robots" content="index, follow"/>
    <meta name="googlebot" content="index, follow">
    <meta name="bingbot" content="index, follow">
@endif
    <meta name="description" content="{{ option('info_short_description') }}">
    <meta name="keywords" content="{{ option('info_tags') }}">
    <link rel="canonical" href="{{ url('/') }}" />
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "WebSite",
            "url": "{{ route('front.index') }}",
            "name": "{{ option('site_title') }}",
            "logo": "{{ option('info_logo') ? asset(option('info_logo')) : asset(config('front.asset_path') . 'img/logo.png') }}",
            "potentialAction": {
                "@type": "SearchAction",
                "target": "{{ route('front.products.search') }}/?q={search_term_string}",
                "query-input": "required name=search_term_string"
            }
        }
    </script>
@endpush

{{-- ARIYA_HOME_LAYOUT_EMERGENCY_V1: avoid unbounded bundle-chip lists stretching homepage cards. Display-only. --}}
@push('styles')
<style>
    .ariya-home-main .product-card .bundle-components {
        display: none !important;
    }
</style>
@endpush
@push('styles')
<!-- ARIYA_RECOVER_SAFE_HERO_V3 -->
@endpush
@push('styles')
<!-- ARIYA_TASK02_3D10_CARD_LAYOUT_V1 -->
<style>
    /* Task 10: homepage product cards only. */

    .ariya-home-main .product-carousel.owl-carousel .owl-stage {
        display: flex;
        align-items: stretch;
    }

    .ariya-home-main .product-carousel.owl-carousel .owl-item {
        display: flex;
        align-self: stretch;
        min-width: 0;
    }

    .ariya-home-main .product-carousel.owl-carousel .owl-item > .item {
        display: flex;
        flex-direction: column;
        width: 100%;
        min-width: 0;
    }

    .ariya-home-main .product-carousel .item > .product-card {
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
        width: 100%;
        min-width: 0;
        height: auto;
        box-sizing: border-box;
        margin-top: 0 !important;
        margin-bottom: 12px !important;
    }

    .ariya-home-main .product-carousel .product-card .product-head {
        flex: 0 0 auto;
        box-sizing: border-box;
    }

    .ariya-home-main .product-carousel .product-card .product-thumb {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        width: 100%;
        min-height: 0;
        aspect-ratio: 1 / 1;
        max-height: 230px;
        box-sizing: border-box;
        overflow: hidden;
    }

    .ariya-home-main .product-carousel .product-thumb > img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        object-position: center;
    }

    .ariya-home-main .product-carousel .product-card-body {
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
        min-width: 0;
    }

    .ariya-home-main .product-carousel .product-title {
        flex: 0 0 auto;
        height: 3.6em !important;
        min-height: 3.6em;
        max-height: 3.6em;
        line-height: 1.8;
        overflow: hidden;
    }

    .ariya-home-main .product-carousel .product-title > a {
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
        overflow-wrap: anywhere;
    }

    .ariya-home-main .product-carousel .rating-stars {
        flex: 0 0 auto;
        min-width: 0;
        min-height: 26px;
    }

    .ariya-home-main .product-carousel .price-index-h {
        margin-top: auto;
        width: 100%;
        min-width: 0;
        box-sizing: border-box;
    }

    .ariya-home-main .product-carousel .product-prices-div {
        min-width: 0;
        max-width: 100%;
    }

    .ariya-home-main .product-carousel .product-price,
    .ariya-home-main .product-carousel .product-price-del {
        max-width: 100%;
        overflow-wrap: anywhere;
    }

    .ariya-home-main .product-carousel .ariya-card-action-button,
    .ariya-home-main .product-carousel .cart a {
        box-sizing: border-box;
        max-width: 100%;
        min-height: 38px;
    }

    @media (max-width: 575.98px) {
        .ariya-home-main .product-carousel .product-thumb {
            max-height: 180px;
        }

        .ariya-home-main .product-carousel .product-title {
            font-size: 12px;
        }
    }
</style>
@endpush

@push('styles')
<!-- ARIYA_TASK02_3D11_HOVER_FADE_V1 -->
<style>
    /*
     * Task 11
     * Remove shine and fade to the second product image.
     * Scope: homepage product carousel cards only.
     */

    .ariya-home-main .product-carousel
    .product-card .product-thumb::before {
        content: none !important;
        display: none !important;
        opacity: 0 !important;
        animation: none !important;
        box-shadow: none !important;
    }

    .ariya-home-main .product-carousel
    .product-thumb.ariya-hover-swap-thumb {
        position: relative;
        isolation: isolate;
    }

    .ariya-home-main .product-carousel
    .product-thumb.ariya-hover-swap-thumb
    .ariya-hover-swap__primary {
        position: relative !important;
        z-index: 0;
        opacity: 1 !important;
        transform: none !important;
        transition: none !important;
        object-fit: contain !important;
    }

    .ariya-home-main .product-carousel
    .product-thumb.ariya-hover-swap-thumb
    .ariya-hover-swap__secondary {
        position: absolute !important;
        inset: 0 !important;
        z-index: 1;
        display: block;
        width: 100% !important;
        height: 100% !important;
        max-width: 100% !important;
        max-height: 100% !important;
        object-fit: contain !important;
        object-position: center !important;
        opacity: 0 !important;
        transform: none !important;
        pointer-events: none !important;
        transition: opacity 350ms ease !important;
    }

    .ariya-home-main .product-carousel
    .product-thumb.ariya-hover-swap-thumb:hover
    .ariya-hover-swap__secondary,
    .ariya-home-main .product-carousel
    .product-thumb.ariya-hover-swap-thumb:focus-visible
    .ariya-hover-swap__secondary {
        opacity: 1 !important;
    }

    /* Touch devices should not retain a simulated hover. */
    @media (hover: none) {
        .ariya-home-main .product-carousel
        .product-thumb.ariya-hover-swap-thumb
        .ariya-hover-swap__secondary {
            opacity: 0 !important;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .ariya-home-main .product-carousel
        .product-thumb.ariya-hover-swap-thumb
        .ariya-hover-swap__secondary {
            transition: none !important;
        }
    }
</style>
@endpush

{{-- ARIYA_HERO_1529_329_V1: desktop hero 1529 x 329 CSS pixels, never stretch when viewport widens. --}}
@push('styles')
<style>
/* Scope to the homepage's full-screen desktop carousel. Mobile remains unchanged. */
@media (min-width: 992px) {
    .ariya-home-main .ariya-home-slider-fullbleed > .ariya-full-slider-wrap {
        width: 100%;
        max-width: 1529px;
        margin-inline: auto;
    }

    .ariya-home-main .ariya-home-slider-fullbleed .ariya-full-slider-wrap #main-slider {
        display: block;
        width: 100%;
        height: auto !important;
        aspect-ratio: 1529 / 329;
        overflow: hidden;
        background: #fff;
    }

    .ariya-home-main .ariya-home-slider-fullbleed .ariya-full-slider-wrap #main-slider .carousel-inner,
    .ariya-home-main .ariya-home-slider-fullbleed .ariya-full-slider-wrap #main-slider .carousel-item,
    .ariya-home-main .ariya-home-slider-fullbleed .ariya-full-slider-wrap #main-slider .main-slider-slide {
        width: 100%;
        height: 100%;
    }

    .ariya-home-main .ariya-home-slider-fullbleed .ariya-full-slider-wrap #main-slider .main-slider-slide img {
        display: block;
        width: 100% !important;
        height: 100% !important;
        max-width: 100% !important;
        max-height: 100% !important;
        object-fit: contain;
        object-position: center center;
    }
}
</style>
@endpush
{{-- ARIYA_MOBILE_HERO_523_240_V1: maximum mobile hero 523 x 240 CSS px, proportional on narrower screens. --}}
@push('styles')
<style>
/* Homepage-only mobile carousel; do not change the desktop 1529x329 banner. */
@media (max-width: 991.98px) {
    .ariya-home-main .ariya-home-slider-fullbleed #main-slider-res {
        width: 100% !important;
        max-width: 523px !important;
        height: auto !important;
        aspect-ratio: 523 / 240;
        margin-left: auto !important;
        margin-right: auto !important;
        overflow: hidden;
        background: #fff;
    }

    .ariya-home-main .ariya-home-slider-fullbleed #main-slider-res .carousel-inner,
    .ariya-home-main .ariya-home-slider-fullbleed #main-slider-res .carousel-item,
    .ariya-home-main .ariya-home-slider-fullbleed #main-slider-res .main-slider-slide {
        width: 100% !important;
        height: 100% !important;
    }

    .ariya-home-main .ariya-home-slider-fullbleed #main-slider-res .main-slider-slide img {
        display: block;
        width: 100% !important;
        height: 100% !important;
        max-width: 100% !important;
        max-height: 100% !important;
        object-fit: cover !important;
        object-position: center center;
        border-radius: 0;
    }
}
</style>
@endpush

@section('content')
    <main class="main-content dt-sl mb-3 ariya-home-main">
        @foreach ($widgets as $widget)
            @if(in_array($widget->key, ['fullscreen-slider', 'main-slider']))
                <div class="ariya-home-slider-fullbleed">
                    @if($widget->key === 'fullscreen-slider')
                        @include('front::widgets.fullscreen-slider')
                    @else
                        @include('front::widgets.main-slider')
                    @endif
                </div>
            @else
                <div class="container main-container ariya-home-widget-container">
                    @switch($widget->key)
                        @case('products-default-block')
                            @include('front::widgets.products-default-block')
                        @break
                        @case('products-colorful-block')
                            @include('front::widgets.products-colorful-block')
                        @break
                        @case('middle-banners')
                            @include('front::widgets.middle-banners')
                        @break
                        @case('coworker-sliders')
                            @include('front::widgets.coworker-sliders')
                        @break
                        @case('sevices-sliders')
                            @include('front::widgets.sevices-sliders')
                        @break
                        @case('categories')
                            @include('front::widgets.categories')
                        @break
                        @case('posts')
                            @include('front::widgets.posts')
                        @break
                        @case('products-discount-block')
                            @include('front::widgets.discount-products')
                        @break
                        @case('custom-page')
                            @include('front::widgets.custom-page')
                        @break
                    @endswitch
                </div>
            @endif
        @endforeach
    </main>
@endsection

@push('scripts')
    <script>
        function showIndexModal(name) {
            let now = new Date().getTime();
            let prevShowDate = localStorage.getItem(name);
            if (!prevShowDate || (Math.abs(now - prevShowDate) / 3600000) >= 24) {
                setTimeout(() => {
                    $(name).modal('show');
                    localStorage.setItem(name, new Date().getTime());
                }, 5000);
            }
        }
    </script>

    @if (option('dt_index_popup_type') == 'image')
        <div id="image-popup-modal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
                    <div class="modal-body">
                        <a href="{{ option('dt_index_popup_link') }}">
                            <div class="d-none d-md-block"><img src="{{ asset(option('dt_index_popup_image')) }}" class="img-responsive w-100"></div>
                            <div class="d-block d-md-none"><img src="{{ asset(option('dt_index_popup_image_mobile')) }}" class="img-responsive w-100"></div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <script>showIndexModal('#image-popup-modal');</script>
    @elseif (option('dt_index_popup_type') == 'text')
        <div id="text-popup-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
                    <div class="modal-body pt-0"><p>{!! option('dt_index_popup_text') !!}</p></div>
                </div>
            </div>
        </div>
        <script>showIndexModal('#text-popup-modal');</script>
    @endif
@endpush
