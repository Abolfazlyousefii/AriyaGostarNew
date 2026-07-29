@php
    $selectedCategories = collect((array) request('categories', []))->map(fn ($id) => (string) $id);
    $selectedBrands = collect((array) request('brands', []))->map(fn ($id) => (string) $id);
    $activeFilterCount = $selectedCategories->count()
        + $selectedBrands->count()
        + (request()->boolean('in_stock') ? 1 : 0)
        + (request()->boolean('discounted') ? 1 : 0)
        + (request()->filled('price_level') ? 1 : 0)
        + (request()->filled('min_price') || request()->filled('max_price') ? 1 : 0)
        + (trim((string) request('q')) !== '' ? 1 : 0);
@endphp

<div class="ariya-catalog-filter-backdrop" data-catalog-filter-close></div>

<aside class="ariya-catalog-filter" id="ariya-catalog-filter" aria-label="فیلتر محصولات">
    <div class="ariya-catalog-filter__mobile-head">
        <div>
            <strong>فیلتر محصولات</strong>
            @if($activeFilterCount)
                <span>{{ $activeFilterCount }} فیلتر فعال</span>
            @endif
        </div>
        <button type="button" class="ariya-catalog-filter__close" data-catalog-filter-close aria-label="بستن فیلتر">
            <i class="mdi mdi-close"></i>
        </button>
    </div>

    <form id="ariya-products-filter-form" action="{{ route('front.products.index') }}" method="GET">
        <input type="hidden" name="sort" value="{{ request('sort', 'latest') }}" data-catalog-sort-hidden>

        <div class="ariya-catalog-filter__section ariya-catalog-filter__search">
            <label for="catalog-search">جستجو در محصولات</label>
            <div class="ariya-catalog-search-field">
                <i class="mdi mdi-magnify"></i>
                <input
                    id="catalog-search"
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="نام، مدل یا کد کالا..."
                    autocomplete="off"
                >
            </div>
        </div>

        @if($categories->count())
            <div class="ariya-catalog-filter__section">
                <button class="ariya-catalog-filter__section-title" type="button" data-toggle="collapse" data-target="#catalog-category-filter" aria-expanded="true">
                    <span>دسته‌بندی</span>
                    <i class="mdi mdi-chevron-down"></i>
                </button>
                <div class="collapse show" id="catalog-category-filter">
                    <div class="ariya-catalog-check-list ariya-catalog-check-list--categories">
                        @foreach($categories as $category)
                            <label class="ariya-catalog-check">
                                <input
                                    type="checkbox"
                                    name="categories[]"
                                    value="{{ $category->id }}"
                                    {{ $selectedCategories->contains((string) $category->id) ? 'checked' : '' }}
                                >
                                <span class="ariya-catalog-check__box"><i class="mdi mdi-check"></i></span>
                                <span class="ariya-catalog-check__label">{{ $category->full_title }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if($brands->count())
            <div class="ariya-catalog-filter__section">
                <button class="ariya-catalog-filter__section-title collapsed" type="button" data-toggle="collapse" data-target="#catalog-brand-filter" aria-expanded="false">
                    <span>برند</span>
                    <i class="mdi mdi-chevron-down"></i>
                </button>
                <div class="collapse {{ $selectedBrands->isNotEmpty() ? 'show' : '' }}" id="catalog-brand-filter">
                    <div class="ariya-catalog-check-list ariya-catalog-check-list--compact">
                        @foreach($brands as $brand)
                            <label class="ariya-catalog-check">
                                <input
                                    type="checkbox"
                                    name="brands[]"
                                    value="{{ $brand->id }}"
                                    {{ $selectedBrands->contains((string) $brand->id) ? 'checked' : '' }}
                                >
                                <span class="ariya-catalog-check__box"><i class="mdi mdi-check"></i></span>
                                <span class="ariya-catalog-check__label">{{ $brand->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if($catalogMaxPrice > $catalogMinPrice)
            <div class="ariya-catalog-filter__section">
                <button class="ariya-catalog-filter__section-title" type="button" data-toggle="collapse" data-target="#catalog-price-filter" aria-expanded="true">
                    <span>سطح و بازه قیمت</span>
                    <i class="mdi mdi-chevron-down"></i>
                </button>
                <div class="collapse show" id="catalog-price-filter">
                    <div class="ariya-price-levels">
                        @foreach([
                            'economy' => 'اقتصادی',
                            'midrange' => 'میان‌رده',
                            'premium' => 'رده‌بالا',
                        ] as $levelKey => $levelTitle)
                            @php
                                $levelMin = $priceLevels[$levelKey][0];
                                $levelMax = $priceLevels[$levelKey][1];
                            @endphp
                            <label class="ariya-price-level">
                                <input
                                    type="radio"
                                    name="price_level"
                                    value="{{ $levelKey }}"
                                    {{ request('price_level') === $levelKey ? 'checked' : '' }}
                                >
                                <span>
                                    <strong>{{ $levelTitle }}</strong>
                                    <small>{{ number_format($levelMin) }} تا {{ number_format($levelMax) }}</small>
                                </span>
                            </label>
                        @endforeach
                    </div>

                    <div class="ariya-price-range-fields">
                        <label>
                            <span>از قیمت</span>
                            <input
                                type="number"
                                name="min_price"
                                min="0"
                                step="1000"
                                value="{{ request('min_price') }}"
                                placeholder="{{ number_format($catalogMinPrice, 0, '.', '') }}"
                                data-custom-price-input
                            >
                        </label>
                        <label>
                            <span>تا قیمت</span>
                            <input
                                type="number"
                                name="max_price"
                                min="0"
                                step="1000"
                                value="{{ request('max_price') }}"
                                placeholder="{{ number_format($catalogMaxPrice, 0, '.', '') }}"
                                data-custom-price-input
                            >
                        </label>
                    </div>
                    <p class="ariya-catalog-filter__hint">مبالغ بر اساس واحد قیمت فعلی فروشگاه هستند.</p>
                </div>
            </div>
        @endif

        <div class="ariya-catalog-filter__section ariya-catalog-switches">
            <label class="ariya-catalog-switch">
                <span>
                    <strong>فقط کالاهای موجود</strong>
                    <small>محصولات ناموجود نمایش داده نشوند</small>
                </span>
                <input type="checkbox" name="in_stock" value="1" {{ request()->boolean('in_stock') ? 'checked' : '' }}>
                <i></i>
            </label>

            <label class="ariya-catalog-switch">
                <span>
                    <strong>فقط تخفیف‌دارها</strong>
                    <small>محصولات دارای تخفیف فعال</small>
                </span>
                <input type="checkbox" name="discounted" value="1" {{ request()->boolean('discounted') ? 'checked' : '' }}>
                <i></i>
            </label>
        </div>

        <div class="ariya-catalog-filter__actions">
            <button type="submit" class="ariya-catalog-filter__apply">
                <i class="mdi mdi-filter-variant"></i>
                اعمال فیلترها
            </button>
            <a href="{{ route('front.products.index') }}" class="ariya-catalog-filter__reset">پاک کردن همه</a>
        </div>
    </form>
</aside>
