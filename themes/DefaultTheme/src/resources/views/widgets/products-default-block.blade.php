@php
    $variables = get_widget($widget);
    $products = $variables['products'];

    // ARIYA_SALES_CONTROL_WIDGET_V1
    $salesCampaign = app(
        \App\Services\Sales\SalesCampaignService::class
    );

    if ($widget->option('products_type') === 'discount') {
        $controlledProducts = $salesCampaign
            ->showcaseProducts('discount');

        if ($controlledProducts !== null) {
            $products = $controlledProducts;
        }
    }

    $isNewArrivalsWidget =
        (int) $widget->id === 4
        || trim((string) $widget->option('title'))
            === 'ورودی های جدید';

    if ($isNewArrivalsWidget) {
        $controlledProducts = $salesCampaign
            ->showcaseProducts('new_arrivals');

        if ($controlledProducts !== null) {
            $products = $controlledProducts;
        }
    }
@endphp
@once
    @push('styles')
        <!-- ARIYA_TASK02_3D15_CURATED_CARDS_V1 -->
        <style>
            /*
             * Stage 15: New Arrivals and Colleague Picks.
             * Visual layout only. No purchasing logic.
             */

            .ariya-home-main
            .ariya-stage15-curated-products
            .product-carousel.owl-carousel .owl-stage {
                display: flex;
                align-items: stretch;
            }

            .ariya-home-main
            .ariya-stage15-curated-products
            .product-carousel.owl-carousel .owl-item {
                display: flex;
                min-width: 0;
                align-self: stretch;
            }

            .ariya-home-main
            .ariya-stage15-curated-products
            .product-carousel.owl-carousel .owl-item > .item {
                display: flex;
                flex-direction: column;
                width: 100%;
                min-width: 0;
                box-sizing: border-box;
                padding: 2px 4px 8px;
            }

            .ariya-home-main
            .ariya-stage15-curated-products
            .product-carousel .item > .product-card {
                display: flex;
                flex: 1 1 auto;
                flex-direction: column;
                width: 100%;
                min-width: 0;
                height: auto;
                box-sizing: border-box;
                margin-top: 0 !important;
                margin-bottom: 12px !important;
            }

            .ariya-home-main
            .ariya-stage15-curated-products
            .product-carousel .product-card .product-thumb {
                flex: 0 0 auto;
                aspect-ratio: 1 / 1;
                max-height: 230px;
                align-items: center;
                justify-content: center;
            }

            .ariya-home-main
            .ariya-stage15-curated-products
            .product-carousel .product-card-body {
                display: flex;
                flex: 1 1 auto;
                flex-direction: column;
                min-width: 0;
                padding-inline: 10px;
            }

            .ariya-home-main
            .ariya-stage15-curated-products
            .product-carousel .product-title {
                min-height: 3.6em;
                max-height: 3.6em;
                overflow: hidden;
            }

            .ariya-home-main
            .ariya-stage15-curated-products
            .product-carousel .price-index-h {
                margin-top: auto;
                min-width: 0;
            }

            .ariya-home-main
            .ariya-stage15-curated-products
            .product-carousel .ariya-card-actions {
                padding-top: 10px;
            }

            @media (max-width: 575.98px) {
                .ariya-home-main
                .ariya-stage15-curated-products
                .product-carousel.owl-carousel .owl-item > .item {
                    padding: 2px 2px 6px;
                }

                .ariya-home-main
                .ariya-stage15-curated-products
                .product-carousel .product-card-body {
                    padding-inline: 8px;
                }
            }
        </style>
    @endpush
@endonce


<!-- Start products -->
@if ($products->count())
    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <section class="slider-section dt-sl mb-3 {{ in_array(trim((string) $widget->option('title')), ['ورودی های جدید', 'انتخاب همکاران'], true) ? 'ariya-stage15-curated-products' : '' }} {{ $widget->option('products_type') === 'discount' ? 'ariya-discount-products-widget' : '' }}">
                <div class="row mb-3">
                    <div class="col-12 px-0 px-sm-3">
                        <div class="section-title text-sm-title title-wide-custom title-wide no-after-title-wide">
                            <h2>{{ $widget->option('title') }}</h2>
                            @if ($widget->option('link'))
                                <a href="{{ $widget->option('link') }}">{{ $widget->option('link_title', trans('front::messages.user.see-all')) }} ></a>
                            @endif
                        </div>
                    </div>

                    <div class="col-12 px-res-0">
                        <div class="product-carousel carousel-md owl-carousel owl-theme">
                            @foreach ($products as $product)
                                @include('front::partials.product-block')
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endif
<!-- End products -->
