@php
    $catalogTitle = $catalogTitle ?? 'محصولات';

    $filterAction = $filterAction ?? url()->current();
    $filterResetUrl = $filterResetUrl ?? url()->current();

    $showCategoryFilter = $showCategoryFilter ?? true;
    $showBrandFilter = $showBrandFilter ?? true;
    $showDiscountFilter = $showDiscountFilter ?? true;
    $showStockFilter = $showStockFilter ?? true;

    $mobileExtraCount = collect((array) request('filters', []))
        ->sum(function ($values) {
            return is_array($values) ? count($values) : 0;
        });

    $mobileFilterCount =
        ($showCategoryFilter
            ? count((array) request('categories', []))
            : 0)
        + ($showBrandFilter
            ? count((array) request('brands', []))
            : 0)
        + ($showStockFilter && request()->boolean('in_stock') ? 1 : 0)
        + ($showDiscountFilter && request()->boolean('discounted') ? 1 : 0)
        + (request()->filled('price_level') ? 1 : 0)
        + (request()->filled('min_price') || request()->filled('max_price') ? 1 : 0)
        + $mobileExtraCount;
@endphp

<div class="ariya-catalog-mobile-toolbar">
    <button
        type="button"
        class="ariya-catalog-mobile-filter-button"
        data-catalog-filter-open
    >
        <i class="mdi mdi-filter-variant"></i>
        <span>فیلتر محصولات</span>

        @if($mobileFilterCount)
            <b>{{ $mobileFilterCount }}</b>
        @endif
    </button>

    <label class="ariya-catalog-mobile-sort">
        <i class="mdi mdi-sort-variant"></i>

        <select data-catalog-sort-select>
            <option
                value="latest"
                {{ request('sort', 'latest') === 'latest' ? 'selected' : '' }}
            >
                جدیدترین
            </option>

            <option
                value="most_viewed"
                {{ request('sort') === 'most_viewed' ? 'selected' : '' }}
            >
                پربازدیدترین
            </option>

            <option
                value="best_selling"
                {{ request('sort') === 'best_selling' ? 'selected' : '' }}
            >
                پرفروش‌ترین
            </option>

            <option
                value="price_asc"
                {{ request('sort') === 'price_asc' ? 'selected' : '' }}
            >
                ارزان‌ترین
            </option>

            <option
                value="price_desc"
                {{ request('sort') === 'price_desc' ? 'selected' : '' }}
            >
                گران‌ترین
            </option>
        </select>
    </label>
</div>

<div class="ariya-catalog-layout">

    @include(
        'front::products.partials.index-filters',
        [
            'filterAction' => $filterAction,
            'filterResetUrl' => $filterResetUrl,
            'showCategoryFilter' => $showCategoryFilter,
            'showBrandFilter' => $showBrandFilter,
            'showDiscountFilter' => $showDiscountFilter,
            'showStockFilter' => $showStockFilter,
        ]
    )

    <section
        class="ariya-catalog-results"
        id="ariya-catalog-results"
    >
        <div class="ariya-catalog-results__head">
            <div>
                <h1>{{ $catalogTitle }}</h1>

                <p>
                    {{ number_format($products->total()) }}
                    کالا پیدا شد
                </p>
            </div>

            {{-- ARIYA_DESKTOP_SORT_TABS_V1 --}}
                        @include('front::products.partials.ariya-desktop-sort', ['ariyaAllowPriceSorting' => true])
        </div>

        @if($products->count())
            <div class="dt-sl dt-sn px-0 search-amazing-tab ariya-catalog-products-box">
                <div class="row mb-3 mx-0 ariya-products-grid">

                    @foreach($products as $product)
                        <div class="col-xl-3 col-lg-4 col-md-4 col-sm-6 col-6 px-10 mb-2 category-product-div">

                            @include(
                                'front::products.partials.product-card',
                                ['product' => $product]
                            )

                        </div>
                    @endforeach

                </div>

                <div class="ariya-catalog-pagination">
                    {{ $products->links('front::components.paginate') }}
                </div>
            </div>
        @else
            <div class="ariya-catalog-empty">
                <i class="mdi mdi-package-variant-closed"></i>

                <h2>
                    محصولی با این فیلترها پیدا نشد
                </h2>

                <p>
                    فیلترها را تغییر دهید یا همه فیلترها را پاک کنید.
                </p>

                <a href="{{ $filterResetUrl }}">
                    پاک کردن فیلترها
                </a>
            </div>
        @endif
    </section>
</div>

@push('scripts')
    <script
        src="{{ theme_asset('js/pages/products/index.js') }}?v=20261001-1"
    ></script>
@endpush