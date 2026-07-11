<div class="mobile-search-panel" id="mobile-search-panel" aria-hidden="true">
    <div class="mobile-search-panel__overlay" data-mobile-search-close></div>
    <div class="mobile-search-panel__dialog" role="dialog" aria-modal="true" aria-labelledby="mobile-search-title">
        <div class="mobile-search-panel__header">
            <h2 id="mobile-search-title">جستجوی محصول</h2>
            <button type="button" class="mobile-search-panel__close" data-mobile-search-close aria-label="بستن جستجو">
                <i class="mdi mdi-close" aria-hidden="true"></i>
            </button>
        </div>
        <form class="mobile-search-panel__form" action="{{ route('front.products.search') }}" method="GET">
            <label class="sr-only" for="mobile-search-input">عبارت جستجو</label>
            <input id="mobile-search-input" type="search" name="q" value="{{ request('q') }}" autocomplete="off" placeholder="{{ trans('front::messages.header.Search-for-product') }}">
            <button type="submit" aria-label="ارسال جستجو">
                <i class="mdi mdi-magnify" aria-hidden="true"></i>
                <span>جستجو</span>
            </button>
        </form>
    </div>
</div>
