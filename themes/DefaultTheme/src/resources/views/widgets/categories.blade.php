@php
    $variables  = get_widget($widget);
    $categories = $variables['categories'];
@endphp

@if ($categories->count())
    <style>
        .ariya-home-categories {
            margin: 22px 0 24px;
            padding: 0;
            background: transparent;
        }

        .ariya-home-categories .ariya-category-slider-wrap {
            position: relative;
            padding: 0;
            background: transparent;
        }

        .ariya-home-categories .owl-stage {
            display: flex;
            align-items: flex-start;
        }

        .ariya-home-categories .owl-stage-outer,
        .ariya-home-categories .owl-stage,
        .ariya-home-categories .owl-item,
        .ariya-home-categories .item {
            background: transparent;
        }

        .ariya-home-categories .ariya-category-item {
            min-height: 170px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            text-decoration: none;
            background: transparent;
            border: 0;
            box-shadow: none;
            padding: 0 10px;
        }

        .ariya-home-categories .ariya-category-image {
            width: 125px;
            height: 125px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            background: transparent;
            border: 0;
            box-shadow: none;
            overflow: visible;
        }

        .ariya-home-categories .ariya-category-image img {
            max-width: 125px;
            max-height: 125px;
            width: auto;
            height: auto;
            object-fit: contain;
            display: block;
            background: transparent;
            border: 0;
            box-shadow: none;
        }

        .ariya-home-categories .ariya-category-title {
            display: block;
            width: 100%;
            text-align: center;
            color: #000;
            font-size: 15px;
            font-weight: 500;
            line-height: 1.8;
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ariya-home-categories .owl-nav {
            display: none;
        }

        @media (max-width: 991px) {
            .ariya-home-categories {
                margin: 0 0 18px;
                padding: 12px 0 6px;
                background: #ffffff;
            }

            .ariya-home-categories .ariya-category-slider-wrap,
            .ariya-home-categories .owl-stage-outer,
            .ariya-home-categories .owl-stage,
            .ariya-home-categories .owl-item,
            .ariya-home-categories .item {
                background: #ffffff;
            }

            .ariya-home-categories .ariya-category-item {
                min-height: 135px;
            }

            .ariya-home-categories .ariya-category-image {
                width: 105px;
                height: 105px;
            }

            .ariya-home-categories .ariya-category-image img {
                max-width: 105px;
                max-height: 105px;
            }

            .ariya-home-categories .ariya-category-title {
                font-size: 13.5px;
            }
        }

        @media (max-width: 575px) {
            .ariya-home-categories .ariya-category-item {
                min-height: 110px;
            }

            .ariya-home-categories .ariya-category-image {
                width: 82px;
                height: 82px;
            }

            .ariya-home-categories .ariya-category-image img {
                max-width: 82px;
                max-height: 82px;
            }

            .ariya-home-categories .ariya-category-title {
                font-size: 12.5px;
            }
        }
    </style>


    <!-- ARIYA_TASK02_3C08_HOVER_V1 -->
    <style>
        /* Task 08: calm category hover, no jump or crop. */

        .ariya-home-categories .ariya-category-item,
        .ariya-home-categories .ariya-category-item:hover,
        .ariya-home-categories .ariya-category-item:focus {
            transform: none !important;
            transition: none !important;
        }

        .ariya-home-categories .ariya-category-image,
        .ariya-home-categories .ariya-category-item:hover .ariya-category-image {
            overflow: visible !important;
            transform: none !important;
        }

        .ariya-home-categories .ariya-category-image img {
            transform: none !important;
            filter: saturate(.65) !important;
            transition: filter 350ms ease !important;
        }

        .ariya-home-categories .ariya-category-item:hover .ariya-category-image img,
        .ariya-home-categories .ariya-category-item:focus-visible .ariya-category-image img {
            transform: none !important;
            filter: saturate(1) !important;
        }

        /* Remove shine overlays only from homepage category items. */
        .ariya-home-categories .ariya-category-item::before,
        .ariya-home-categories .ariya-category-item::after,
        .ariya-home-categories .ariya-category-image::before,
        .ariya-home-categories .ariya-category-image::after {
            content: none !important;
            display: none !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .ariya-home-categories .ariya-category-image img {
                transition: none !important;
            }
        }
    </style>
    <!-- ARIYA_TASK02_3C09_UNIFORM_V1 -->
<style>
    /* Task 09: consistent circular category thumbnails */

    .ariya-home-categories .ariya-category-image {
        box-sizing: border-box !important;
        width: min(125px, 100%) !important;
        height: auto !important;
        aspect-ratio: 1 / 1 !important;
        flex: 0 0 auto;
        margin: 0 auto 10px !important;
        overflow: visible !important;
    }

    .ariya-home-categories .ariya-category-image img {
        display: block !important;
        width: 100% !important;
        height: auto !important;
        aspect-ratio: 1 / 1 !important;
        max-width: 100% !important;
        max-height: none !important;
        object-fit: contain !important;
        object-position: center !important;
    }

    .ariya-home-categories .ariya-category-title {
        display: block !important;
        width: 100% !important;
        margin: 2px 0 0 !important;
        line-height: 1.75 !important;
        text-align: center !important;
    }

    @media (max-width: 991px) {
        .ariya-home-categories .ariya-category-image {
            width: min(105px, 100%) !important;
        }
    }

    @media (max-width: 575px) {
        .ariya-home-categories .ariya-category-image {
            width: min(82px, 100%) !important;
        }
    }
</style>
<section class="ariya-home-categories">
        <div class="ariya-category-slider-wrap">
            <div class="owl-carousel ariya-categories-carousel">

                @foreach ($categories as $category)
                    <div class="item">
                        <a href="{{ $category->link }}" class="ariya-category-item">

                            <span class="ariya-category-image">
                                @php
                                    // New category media lives on the configured storage disk;
                                    // legacy /uploads/category files remain public local assets.
                                    $categoryImageUrl = category_image_url($category->image ?? null);
                                    if ($categoryImageUrl !== '' && $category->updated_at) {
                                        // The updated-at token defeats cached stale image URLs after edits.
                                        $categoryImageUrl .= (str_contains($categoryImageUrl, '?') ? '&' : '?')
                                            . 'v=' . $category->updated_at->timestamp;
                                    }
                                @endphp
                                @if($categoryImageUrl !== '')
                                    <img src="{{ $categoryImageUrl }}" alt="{{ $category->title }}" loading="lazy" decoding="async">
                                @endif
                            </span>

                            <span class="ariya-category-title">
                                {{ $category->title }}
                            </span>

                        </a>
                    </div>
                @endforeach

            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            $(document).ready(function () {
                $('.ariya-categories-carousel').owlCarousel({
                    rtl: IS_RTL ? true : false,
                    margin: 28,
                    nav: false,
                    dots: false,
                    responsiveClass: true,
                    responsive: {
                        0: {
                            items: 3,
                            slideBy: 1
                        },
                        576: {
                            items: 4,
                            slideBy: 2
                        },
                        768: {
                            items: 5,
                            slideBy: 2
                        },
                        992: {
                            items: 6,
                            slideBy: 3
                        },
                        1200: {
                            items: 7,
                            slideBy: 3
                        },
                        1400: {
                            items: 8,
                            slideBy: 4
                        }
                    }
                });
            });
        </script>
    @endpush
@endif
