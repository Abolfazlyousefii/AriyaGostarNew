@php
    $variables      = get_widget($widget);
    $main_sliders   = $variables['main_sliders'];
    $mobile_sliders = $variables['mobile_sliders'];
    $responsive_sliders = $mobile_sliders->count() ? $mobile_sliders : $main_sliders;
    $index_slider_banners = $variables['index_slider_banners'];
@endphp

<!-- Start Main-Slider -->
<div class="row index-main-slider mb-3 {{ $widget->option('banner_position', 'left') == 'right' ? 'flex-row-reverse' : '' }}">

    @if ($index_slider_banners->count())
        <aside class="sidebar col-lg-4 hidden-md order-2 hidden-md">
            <!-- Start banner -->
            <div class="sidebar-inner dt-sl">
                <div class="sidebar-banner">
                    <div class="row">
                        @foreach ($index_slider_banners as $banner)
                            <div class="col-12 mb-1">
                                <div class="widget-banner">
                                    <a href="{{ $banner->link }}">
                                        <img src="{{ asset($banner->image) }}" alt="{{ $banner->title }}">
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <!-- End banner -->
        </aside>
    @endif

    <div class="col-lg-8 col-md-12 order-1">
        <!-- Start main-slider -->
        @if ($main_sliders->count())
            <section id="main-slider" class="main-slider main-slider-cs mt-1 carousel slide carousel-fade card hidden-sm" data-ride="carousel">
                <ol class="carousel-indicators">
                    @foreach ($main_sliders as $slider)
                        <li data-target="#main-slider" data-slide-to="{{ $loop->index }}" class="{{ $loop->first ? 'active' : '' }}"></li>
                    @endforeach
                </ol>
                <div class="carousel-inner">
                    @foreach ($main_sliders as $slider)
                        <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                            <a class="main-slider-slide" href="{{ $slider->link }}">
                                <img
                                    src="{{ asset($slider->image) }}"
                                    alt="{{ $slider->title }}"
                                    class="img-fluid"
                                    draggable="false"
                                >
                            </a>
                        </div>
                    @endforeach

                </div>
                <a class="carousel-control-prev" href="#main-slider" role="button" data-slide="prev">
                    <i class="mdi mdi-chevron-right"></i>
                </a>
                <a class="carousel-control-next" href="#main-slider" data-slide="next">
                    <i class="mdi mdi-chevron-left"></i>
                </a>
            </section>
        @endif

        @if ($responsive_sliders->count())
            <section
                id="main-slider-res"
                class="main-slider ariya-mobile-main-slider carousel slide carousel-fade card d-none show-sm"
                data-ride="carousel"
                data-interval="5000"
                data-pause="hover"
            >
                <ol class="carousel-indicators">
                    @foreach ($responsive_sliders as $slider)
                        <li data-target="#main-slider-res" data-slide-to="{{ $loop->index }}" class="{{ $loop->first ? 'active' : '' }}"></li>
                    @endforeach
                </ol>

                <div class="carousel-inner">
                    @foreach ($responsive_sliders as $slider)
                        <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                            <a class="main-slider-slide" href="{{ $slider->link }}">
                                <img
                                    src="{{ asset($slider->image) }}"
                                    alt="{{ $slider->title }}"
                                    class="img-fluid"
                                    loading="eager"
                                    decoding="async"
                                    fetchpriority="{{ $loop->first ? 'high' : 'auto' }}"
                                    draggable="false"
                                >
                            </a>
                        </div>
                    @endforeach
                </div>

                <a class="carousel-control-prev" href="#main-slider-res" role="button" data-slide="prev">
                    <i class="mdi mdi-chevron-right"></i>
                </a>
                <a class="carousel-control-next" href="#main-slider-res" data-slide="next">
                    <i class="mdi mdi-chevron-left"></i>
                </a>
            </section>
        @endif
        <!-- End main-slider -->
    </div>
</div>

@once
    <style>
        /* جلوگیری از دیده‌شدن اسلاید قبلی زیر اسلاید جدید در موبایل */
        @media (max-width: 991.98px) {
            #main-slider-res {
                position: relative;
                overflow: hidden;
                isolation: isolate;
                background: #fff;
                transform: translateZ(0);
                -webkit-transform: translateZ(0);
            }

            #main-slider-res .carousel-inner {
                position: relative;
                display: grid;
                width: 100%;
                overflow: hidden;
                background: #fff;
                isolation: isolate;
                transform: translateZ(0);
                -webkit-transform: translateZ(0);
            }

            #main-slider-res .carousel-item {
                grid-area: 1 / 1;
                position: relative;
                display: block !important;
                float: none !important;
                width: 100%;
                margin: 0 !important;
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
                z-index: 0;
                transform: none !important;
                backface-visibility: hidden;
                -webkit-backface-visibility: hidden;
                transition: opacity .42s ease, visibility 0s linear .42s !important;
                will-change: opacity;
            }

            #main-slider-res .carousel-item.active,
            #main-slider-res .carousel-item-next.carousel-item-left,
            #main-slider-res .carousel-item-prev.carousel-item-right {
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
                z-index: 2;
                transition: opacity .42s ease, visibility 0s linear 0s !important;
            }

            #main-slider-res .active.carousel-item-left,
            #main-slider-res .active.carousel-item-right {
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
                z-index: 1;
            }

            #main-slider-res .main-slider-slide,
            #main-slider-res .main-slider-slide img {
                display: block;
                width: 100%;
            }

            #main-slider-res .main-slider-slide img {
                height: auto;
                object-fit: cover;
                transform: translateZ(0);
                -webkit-transform: translateZ(0);
                backface-visibility: hidden;
                -webkit-backface-visibility: hidden;
                user-select: none;
                -webkit-user-drag: none;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            #main-slider-res .carousel-item {
                transition: none !important;
            }
        }
    </style>
@endonce
