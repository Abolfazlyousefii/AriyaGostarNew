<div
    id="mobile-cart-panel"
    class="mobile-cart-panel"
    role="dialog"
    aria-modal="true"
    aria-hidden="true"
    aria-labelledby="mobile-cart-panel-title"
>
    <button
        type="button"
        class="mobile-cart-panel__overlay"
        data-mobile-cart-close
        aria-label="بستن سبد خرید"
        tabindex="-1"
    ></button>

    <div class="mobile-cart-panel__sheet" role="document">
        <div class="mobile-cart-panel__header">
            <h2 id="mobile-cart-panel-title" class="mobile-cart-panel__title">سبد خرید شما</h2>
            <button
                type="button"
                class="mobile-cart-panel__close"
                data-mobile-cart-close
                aria-label="بستن سبد خرید"
            >
                <i class="mdi mdi-close" aria-hidden="true"></i>
            </button>
        </div>

        <div class="mobile-cart-panel__content" data-mobile-cart-content aria-live="polite">
            <p class="mobile-cart-panel__empty">در حال دریافت سبد خرید...</p>
        </div>

        <div class="mobile-cart-panel__footer">
            <a href="{{ route('front.cart') }}" class="mobile-cart-panel__view-cart">
                مشاهده سبد خرید
            </a>
        </div>
    </div>
</div>
