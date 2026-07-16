@php
    $isHomeActive = request()->routeIs(['front.index', '*.front.index']);
    $isSearchActive = request()->routeIs(['front.products.search', '*.front.products.search']);
    $isProductsActive = !$isSearchActive && request()->routeIs([
        'front.products.index', '*.front.products.index',
        'front.products.show', '*.front.products.show',
        'front.products.category', '*.front.products.category',
        'front.products.category-products', '*.front.products.category-products',
        'front.products.category-specials', '*.front.products.category-specials',
        'front.products.specials', '*.front.products.specials',
        'front.products.discount', '*.front.products.discount',
        'front.brands.*', '*.front.brands.*',
    ]);
    $isCartActive = request()->routeIs([
        'front.cart', '*.front.cart',
        'front.checkout', '*.front.checkout',
    ]);
    $isAccountActive = auth()->check()
        ? request()->routeIs(['front.user.*', '*.front.user.*', 'front.orders.*', '*.front.orders.*'])
        : request()->routeIs(['login', 'register']);
@endphp

<nav class="mobile-bottom-nav" aria-label="ناوبری اصلی موبایل">
    <div class="mobile-bottom-nav__inner">
        <a
            href="{{ route('front.index') }}"
            class="mobile-bottom-nav__item{{ $isHomeActive ? ' is-active' : '' }}"
            @if($isHomeActive) aria-current="page" @endif
        >
            <i class="mdi mdi-home-outline mobile-bottom-nav__icon" aria-hidden="true"></i>
            <span class="mobile-bottom-nav__label">خانه</span>
        </a>

        <a
            href="{{ route('front.products.index') }}"
            class="mobile-bottom-nav__item{{ $isProductsActive ? ' is-active' : '' }}"
            @if($isProductsActive) aria-current="page" @endif
        >
            <i class="mdi mdi-view-grid-outline mobile-bottom-nav__icon" aria-hidden="true"></i>
            <span class="mobile-bottom-nav__label">محصولات</span>
        </a>

        <button
            type="button"
            class="mobile-bottom-nav__item mobile-bottom-nav__button{{ $isSearchActive ? ' is-active' : '' }}"
            data-mobile-search-open
            aria-label="بازکردن جستجوی محصولات"
            aria-controls="mobile-search-panel"
            aria-expanded="false"
            @if($isSearchActive) aria-current="page" @endif
        >
            <i class="mdi mdi-magnify mobile-bottom-nav__icon" aria-hidden="true"></i>
            <span class="mobile-bottom-nav__label">جستجو</span>
        </button>

        <button
            type="button"
            class="mobile-bottom-nav__item mobile-bottom-nav__button mobile-bottom-nav__cart{{ $isCartActive ? ' is-active' : '' }}"
            data-mobile-cart-open
            aria-label="نمایش خلاصه سبد خرید"
            aria-controls="mobile-cart-panel"
            aria-expanded="false"
            @if($isCartActive) aria-current="page" @endif
        >
            <span class="mobile-bottom-nav__icon-wrap">
                <i class="mdi mdi-cart-outline mobile-bottom-nav__icon" aria-hidden="true"></i>
                <span class="mobile-bottom-nav__badge" data-mobile-cart-count hidden>0</span>
            </span>
            <span class="mobile-bottom-nav__label">سبد خرید</span>
        </button>

        @auth
            <a
                href="{{ route('front.user.profile') }}"
                class="mobile-bottom-nav__item{{ $isAccountActive ? ' is-active' : '' }}"
                @if($isAccountActive) aria-current="page" @endif
            >
                <i class="mdi mdi-account-circle-outline mobile-bottom-nav__icon" aria-hidden="true"></i>
                <span class="mobile-bottom-nav__label">حساب</span>
            </a>
        @else
            <a
                href="{{ route('login') }}"
                class="mobile-bottom-nav__item{{ $isAccountActive ? ' is-active' : '' }}"
                @if($isAccountActive) aria-current="page" @endif
            >
                <i class="mdi mdi-account-circle-outline mobile-bottom-nav__icon" aria-hidden="true"></i>
                <span class="mobile-bottom-nav__label">ورود</span>
            </a>
        @endauth
    </div>
</nav>
