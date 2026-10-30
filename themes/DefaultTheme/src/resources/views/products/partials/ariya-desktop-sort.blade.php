{{-- ARIYA_DESKTOP_SORT_TABS_V1 | Desktop sorting options; mobile select is unchanged. --}}
@php
    $ariyaCurrentSort = (string) request('sort', 'latest');
    $ariyaSortOptions = [
        'latest' => 'جدیدترین',
        'most_viewed' => 'پربازدیدترین',
        'best_selling' => 'پرفروش‌ترین',
    ];
    if ($ariyaAllowPriceSorting ?? true) {
        $ariyaSortOptions['price_asc'] = 'ارزان‌ترین';
        $ariyaSortOptions['price_desc'] = 'گران‌ترین';
    }
    // Keep current filters/search; new sorting starts from page one.
    $ariyaPreservedSortParams = request()->except(['sort', 'page']);
@endphp
<nav class="ariya-catalog-sort-tabs" aria-label="مرتب‌سازی محصولات">
    <span class="ariya-catalog-sort-tabs__label">مرتب‌سازی:</span>
    <div class="ariya-catalog-sort-tabs__options">
        @foreach($ariyaSortOptions as $ariyaSortKey => $ariyaSortTitle)
            @php
                $ariyaSortHref = url()->current() . '?' . http_build_query(
                    array_merge($ariyaPreservedSortParams, ['sort' => $ariyaSortKey]),
                    '', '&', PHP_QUERY_RFC3986
                );
            @endphp
            <a
                href="{{ $ariyaSortHref }}"
                class="ariya-catalog-sort-tabs__option{{ $ariyaCurrentSort === $ariyaSortKey ? ' is-active' : '' }}"
                @if($ariyaCurrentSort === $ariyaSortKey) aria-current="page" @endif
            >{{ $ariyaSortTitle }}</a>
        @endforeach
    </div>
</nav>
