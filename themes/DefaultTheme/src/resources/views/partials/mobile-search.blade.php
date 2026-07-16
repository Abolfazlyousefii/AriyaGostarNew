<div
    class="mobile-search-panel"
    id="mobile-search-panel"
    aria-hidden="true"
>
    <button
        type="button"
        class="mobile-search-panel__overlay"
        data-mobile-search-close
        aria-label="بستن جستجو"
        tabindex="-1"
    ></button>

    <section
        class="mobile-search-panel__sheet"
        role="dialog"
        aria-modal="true"
        aria-labelledby="mobile-search-title"
    >
        <div class="mobile-search-panel__header">
            <h2 id="mobile-search-title" class="mobile-search-panel__title">جستجوی محصولات</h2>
            <button
                type="button"
                class="mobile-search-panel__close"
                data-mobile-search-close
                aria-label="بستن پنل جستجو"
            >
                <i class="mdi mdi-close" aria-hidden="true"></i>
            </button>
        </div>

        <form
            action="{{ route('front.products.search') }}"
            method="GET"
            class="mobile-search-panel__form"
            role="search"
        >
            <label class="sr-only" for="mobile-product-search-input">نام محصول را وارد کنید</label>
            <div class="mobile-search-panel__field">
                <i class="mdi mdi-magnify mobile-search-panel__field-icon" aria-hidden="true"></i>
                <input
                    type="search"
                    id="mobile-product-search-input"
                    name="q"
                    value="{{ request('q') }}"
                    class="mobile-search-panel__input"
                    placeholder="نام کالا یا برند را جستجو کنید..."
                    autocomplete="off"
                    enterkeyhint="search"
                    required
                >
                <button type="submit" class="mobile-search-panel__submit">جستجو</button>
            </div>
        </form>
    </section>
</div>
