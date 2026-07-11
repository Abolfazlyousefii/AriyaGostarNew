@php
    $mobileCartQuantity = isset($cart) && $cart ? $cart->quantity : 0;
@endphp
<nav class="mobile-bottom-nav" aria-label="ناوبری پایین موبایل">
    <a class="mobile-bottom-nav__item {{ request()->routeIs('front.index') ? 'is-active' : '' }}" href="{{ route('front.index') }}" {{ request()->routeIs('front.index') ? 'aria-current=page' : '' }}>
        <i class="mdi mdi-home-outline" aria-hidden="true"></i><span>خانه</span>
    </a>
    <a class="mobile-bottom-nav__item {{ request()->routeIs('front.products.*') && !request()->routeIs('front.products.search') ? 'is-active' : '' }}" href="{{ route('front.products.index') }}" {{ request()->routeIs('front.products.*') && !request()->routeIs('front.products.search') ? 'aria-current=page' : '' }}>
        <i class="mdi mdi-view-grid-outline" aria-hidden="true"></i><span>محصولات</span>
    </a>
    <button class="mobile-bottom-nav__item mobile-bottom-nav__button {{ request()->routeIs('front.products.search') ? 'is-active' : '' }}" type="button" data-mobile-search-open aria-controls="mobile-search-panel" aria-expanded="false">
        <i class="mdi mdi-magnify" aria-hidden="true"></i><span>جستجو</span>
    </button>
    <a class="mobile-bottom-nav__item {{ request()->routeIs('front.cart') ? 'is-active' : '' }}" href="{{ route('front.cart') }}" {{ request()->routeIs('front.cart') ? 'aria-current=page' : '' }}>
        <span class="mobile-bottom-nav__icon-wrap"><i class="mdi mdi-cart-outline" aria-hidden="true"></i><span class="mobile-cart-badge {{ $mobileCartQuantity ? '' : 'is-empty' }}" data-mobile-cart-count>{{ $mobileCartQuantity }}</span></span><span>سبد خرید</span>
    </a>
    @auth
        <a class="mobile-bottom-nav__item {{ request()->routeIs('front.user.profile') ? 'is-active' : '' }}" href="{{ route('front.user.profile') }}" {{ request()->routeIs('front.user.profile') ? 'aria-current=page' : '' }}>
            <i class="mdi mdi-account-outline" aria-hidden="true"></i><span>حساب</span>
        </a>
    @endauth
    @guest
        <a class="mobile-bottom-nav__item {{ request()->routeIs('login') ? 'is-active' : '' }}" href="{{ route('login') }}" {{ request()->routeIs('login') ? 'aria-current=page' : '' }}>
            <i class="mdi mdi-account-outline" aria-hidden="true"></i><span>حساب</span>
        </a>
    @endguest
</nav>
