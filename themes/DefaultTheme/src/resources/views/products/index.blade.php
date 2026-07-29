@extends('front::layouts.master', ['title' => trans('front::messages.products.products')])

@section('content')
    <main class="main-content dt-sl mt-4 mb-3 ariya-products-page">
        <div class="container main-container">
            <div class="title-breadcrumb-special dt-sl mb-3">
                <div class="breadcrumb dt-sl">
                    <nav>
                        <a href="{{ route('front.index') }}">{{ trans('front::messages.products.home') }}</a>
                        <span>{{ trans('front::messages.products.products') }}</span>
                    </nav>
                </div>
            </div>

            <div class="ariya-catalog-mobile-toolbar">
                <button type="button" class="ariya-catalog-mobile-filter-button" data-catalog-filter-open>
                    <i class="mdi mdi-filter-variant"></i>
                    <span>فیلتر محصولات</span>
                    @php
                        $mobileFilterCount = count((array) request('categories', []))
                            + count((array) request('brands', []))
                            + (request()->boolean('in_stock') ? 1 : 0)
                            + (request()->boolean('discounted') ? 1 : 0)
                            + (request()->filled('price_level') ? 1 : 0)
                            + (request()->filled('min_price') || request()->filled('max_price') ? 1 : 0);
                    @endphp
                    @if($mobileFilterCount)
                        <b>{{ $mobileFilterCount }}</b>
                    @endif
                </button>

                <label class="ariya-catalog-mobile-sort">
                    <i class="mdi mdi-sort-variant"></i>
                    <select data-catalog-sort-select>
                        <option value="latest" {{ request('sort', 'latest') === 'latest' ? 'selected' : '' }}>جدیدترین</option>
                        <option value="most_viewed" {{ request('sort') === 'most_viewed' ? 'selected' : '' }}>پربازدیدترین</option>
                        <option value="best_selling" {{ request('sort') === 'best_selling' ? 'selected' : '' }}>پرفروش‌ترین</option>
                        <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>ارزان‌ترین</option>
                        <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>گران‌ترین</option>
                    </select>
                </label>
            </div>

            <div class="ariya-catalog-layout">
                @include('front::products.partials.index-filters')

                <section class="ariya-catalog-results" id="ariya-catalog-results">
                    <div class="ariya-catalog-results__head">
                        <div>
                            <h1>همه محصولات</h1>
                            <p>{{ number_format($products->total()) }} کالا پیدا شد</p>
                        </div>

                        <label class="ariya-catalog-sort">
                            <span>مرتب‌سازی:</span>
                            <select data-catalog-sort-select>
                                <option value="latest" {{ request('sort', 'latest') === 'latest' ? 'selected' : '' }}>جدیدترین</option>
                                <option value="most_viewed" {{ request('sort') === 'most_viewed' ? 'selected' : '' }}>پربازدیدترین</option>
                                <option value="best_selling" {{ request('sort') === 'best_selling' ? 'selected' : '' }}>پرفروش‌ترین</option>
                                <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>ارزان‌ترین</option>
                                <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>گران‌ترین</option>
                            </select>
                        </label>
                    </div>

                    @if($products->count())
                        <div class="dt-sl dt-sn px-0 search-amazing-tab ariya-catalog-products-box">
                            <div class="row mb-3 mx-0 ariya-products-grid">
                                @foreach($products as $product)
                                    <div class="col-xl-3 col-lg-4 col-md-4 col-sm-6 col-6 px-10 mb-2 category-product-div">
                                        @include('front::products.partials.product-card', ['product' => $product])
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
                            <h2>محصولی با این فیلترها پیدا نشد</h2>
                            <p>فیلترها را تغییر دهید یا همه فیلترها را پاک کنید.</p>
                            <a href="{{ route('front.products.index') }}">نمایش همه محصولات</a>
                        </div>
                    @endif
                </section>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    <script src="{{ theme_asset('js/pages/products/index.js') }}?v=20260728-1"></script>
@endpush
