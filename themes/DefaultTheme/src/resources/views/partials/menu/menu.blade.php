<style>
/* ARIYA_TASK02_WHOLESALE_BLUE_FIX_V1 */

/* Prevent inherited menu hover underline */
.ariya-site-header .ariya-wholesale-menu-item,
.ariya-site-header .ariya-wholesale-menu-item:hover {
    border-bottom: 0 !important;
    background: transparent !important;
    box-shadow: none !important;
}

/* Frosted light-blue button */
.ariya-site-header .ariya-desktop-menu-list
> li.ariya-wholesale-menu-item
> a.ariya-wholesale-menu-link {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    min-height: 42px !important;
    padding: 0 17px !important;
    border: 1px solid rgba(60, 125, 205, .32) !important;
    border-radius: 12px !important;

    background: rgba(237, 247, 255, .94) !important;
    background-image: linear-gradient(
        135deg,
        rgba(255, 255, 255, .98),
        rgba(216, 235, 255, .94)
    ) !important;

    color: #14549c !important;
    font-weight: 700 !important;
    text-decoration: none !important;
    white-space: nowrap !important;

    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, .95),
        0 4px 12px rgba(35, 91, 160, .08) !important;

    transition:
        background .2s ease,
        color .2s ease,
        border-color .2s ease,
        box-shadow .2s ease !important;
}

/* Clearly different, readable hover */
.ariya-site-header .ariya-desktop-menu-list
> li.ariya-wholesale-menu-item
> a.ariya-wholesale-menu-link:hover,
.ariya-site-header .ariya-desktop-menu-list
> li.ariya-wholesale-menu-item
> a.ariya-wholesale-menu-link:focus,
.ariya-site-header .ariya-desktop-menu-list
> li.ariya-wholesale-menu-item
> a.ariya-wholesale-menu-link:active {
    background: #c9e1ff !important;
    background-image: linear-gradient(
        135deg,
        #d9ebff,
        #b9d9ff
    ) !important;

    color: #083d82 !important;
    border-color: #7dacdf !important;
    text-decoration: none !important;

    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, .65),
        0 5px 15px rgba(35, 91, 160, .14) !important;
}

/* Remove decorative hover lines */
.ariya-site-header .ariya-wholesale-menu-item::before,
.ariya-site-header .ariya-wholesale-menu-item::after,
.ariya-site-header .ariya-wholesale-menu-link::before,
.ariya-site-header .ariya-wholesale-menu-link::after {
    content: none !important;
    display: none !important;
}

/* Blue guide page button hover */
.ariya-wholesale-guide a[style*="background:#1763bb"]:hover,
.ariya-wholesale-guide a[style*="background:#1763bb"]:focus {
    background: #104d9d !important;
    color: #fff !important;
    text-decoration: none !important;
}

.ariya-wholesale-guide a[style*="border:1px solid #1763bb"]:hover,
.ariya-wholesale-guide a[style*="border:1px solid #1763bb"]:focus {
    background: #e0eeff !important;
    color: #104d9d !important;
    text-decoration: none !important;
}
</style>
<nav class="main-menu dt-sl ariya-main-menu">
    <ul class="list hidden-md ariya-desktop-menu-list">
        {{-- ARIYA_TASK02_3_A_01 --}}
        @php
            $hasIndependentShopMenu = $menus->contains(function ($item) {
                return trim((string) ($item->title ?? '')) === 'فروشگاه'
                    && ($item->type ?? '') !== 'static';
            });
        @endphp

        @foreach($menus as $menu)
            @include('front::partials.menu.child-menu')

            {{-- ARIYA_TASK02_3_A_01_SHOP_AFTER_CATEGORIES --}}
            @if ($loop->first && ! $hasIndependentShopMenu)
                <li class="list-item ariya-shop-menu-item">
                    <a class="nav-link" href="{{ route('front.products.index') }}">
                        فروشگاه
                    </a>
                </li>
            @endif
        @endforeach

        @if ($menus->isEmpty() && ! $hasIndependentShopMenu)
            <li class="list-item ariya-shop-menu-item">
                <a class="nav-link" href="{{ route('front.products.index') }}">
                    فروشگاه
                </a>
            </li>
        @endif

        {{-- ARIYA_TASK02_3_A_02 --}}
        {{-- Activated only after the wholesale page passes verification. --}}
        @if(is_file(storage_path('app/ariya-wholesale-guide.ready')))
            <li class="list-item ariya-wholesale-menu-item"
                style="margin-inline-start:auto">
                <a
                    class="nav-link ariya-wholesale-menu-link"
                    href="{{ route('front.pages.show', ['page' => 'wholesale-guide']) }}"
                    style="white-space:nowrap"
                >
                    خرید عمده از آریا
                </a>
            </li>
        @endif
    </ul>

    @include('front::partials.mobile-menu.menu')
</nav>
