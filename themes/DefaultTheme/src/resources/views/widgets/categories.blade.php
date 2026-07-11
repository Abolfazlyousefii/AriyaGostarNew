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

    <section class="ariya-home-categories">
        <div class="ariya-category-slider-wrap">
            <div class="owl-carousel ariya-categories-carousel">

                @foreach ($categories as $category)
                    <div class="item">
                        <a href="{{ $category->link }}" class="ariya-category-item">

                            <span class="ariya-category-image">
                                @if($category->image)
                                    <img src="{{ asset($category->image) }}" alt="{{ $category->title }}">
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