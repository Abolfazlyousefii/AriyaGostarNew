<div
    id="mobile-search-panel"
    class="mobile-search-panel"
    role="dialog"
    aria-modal="true"
    aria-hidden="true"
    aria-labelledby="mobile-search-panel-title"
>
    <button
        type="button"
        class="mobile-search-panel__overlay"
        data-mobile-search-close
        aria-label="بستن جستجو"
        tabindex="-1"
    ></button>

    <div class="mobile-search-panel__sheet" role="document">
        <div class="mobile-search-panel__header">
            <h2 id="mobile-search-panel-title" class="mobile-search-panel__title">جستجوی محصولات</h2>

            <button
                type="button"
                class="mobile-search-panel__close"
                data-mobile-search-close
                aria-label="بستن جستجو"
            >
                <i class="mdi mdi-close" aria-hidden="true"></i>
            </button>
        </div>

        <form
            method="GET"
            action="{{ route('front.products.search') }}"
            class="mobile-search-panel__form"
            role="search"
        >
            <div class="mobile-search-panel__field">
                <i class="mdi mdi-magnify mobile-search-panel__field-icon" aria-hidden="true"></i>

                <input
                    id="mobile-product-search-input"
                    class="mobile-search-panel__input"
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    autocomplete="off"
                    inputmode="search"
                    placeholder="{{ trans('front::messages.header.Search-for-product') }}"
                    aria-label="جستجوی محصول"
                >

                <button type="submit" class="mobile-search-panel__submit">جستجو</button>
            </div>
        </form>
    </div>
</div>
