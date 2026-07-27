@php
    $cart = isset($render_cart) ? $render_cart : $cart;
    $hasCartProducts = $cart && $cart->products()->count();
@endphp

<li class="nav-item ariya-cart-nav-item" id="cart-list-item">
    <button type="button"
            class="nav-link ariya-cart-trigger"
            data-ariya-cart-open
            aria-controls="ariya-cart-drawer"
            aria-expanded="false">
        <span class="label-dropdown">{{ trans('front::messages.header.cart') }}</span>
        <i class="mdi mdi-cart-outline"></i>
        @if($hasCartProducts)
            <span class="count">{{ $cart->quantity }}</span>
        @endif
    </button>

    <div class="ariya-cart-overlay" data-ariya-cart-close hidden></div>

    <aside class="ariya-cart-drawer"
           id="ariya-cart-drawer"
           role="dialog"
           aria-modal="true"
           aria-label="{{ trans('front::messages.header.cart') }}"
           aria-hidden="true">
        <header class="ariya-cart-drawer-header">
            <div>
                <strong>{{ trans('front::messages.header.cart') }}</strong>
                @if($hasCartProducts)
                    <small>{{ $cart->quantity }} کالا</small>
                @endif
            </div>
            <button type="button" class="ariya-cart-close" data-ariya-cart-close aria-label="بستن سبد خرید">
                <i class="mdi mdi-close"></i>
            </button>
        </header>

        <div class="ariya-cart-drawer-body">
            @if($hasCartProducts)
                @foreach ($cart->products as $product)
                    @php
                        $cartProductPrice = $product->prices()->find($product->pivot->price_id);
                    @endphp
                    <article class="ariya-cart-drawer-item">
                        <a href="{{ route('front.products.show', ['product' => $product]) }}" class="ariya-cart-item-image">
                            <img src="{{ $product->image ? asset($product->image) : asset('/empty.jpg') }}" alt="{{ $product->title }}">
                        </a>
                        <div class="ariya-cart-item-content">
                            <a href="{{ route('front.products.show', ['product' => $product]) }}" class="ariya-cart-item-title">
                                {{ $product->title }}
                            </a>
                            <div class="ariya-cart-item-meta">
                                <span>{{ $product->pivot->quantity }} عدد</span>
                                @if($cartProductPrice)
                                    <strong>{{ number_format($cartProductPrice->salePrice() * $product->pivot->quantity) }} {{ trans('front::messages.currency.suffix') }}</strong>
                                @endif
                            </div>
                        </div>
                        <button type="button"
                                class="ariya-cart-remove"
                                data-ariya-cart-remove
                                data-action="{{ route('front.cart.destroy', ['id' => $product->pivot->id]) }}"
                                aria-label="حذف {{ $product->title }} از سبد خرید">
                            <i class="mdi mdi-delete-outline"></i>
                        </button>
                    </article>
                @endforeach
            @else
                <div class="ariya-cart-empty">
                    <i class="mdi mdi-cart-outline"></i>
                    <strong>{{ trans('front::messages.header.shopping-cart-empty') }}</strong>
                    <a href="{{ route('front.products.index') }}">مشاهده محصولات</a>
                </div>
            @endif
        </div>

        @if($hasCartProducts)
            <footer class="ariya-cart-drawer-footer">
                <div class="ariya-cart-total-row">
                    <span>{{ trans('front::messages.header.total') }}</span>
                    <strong>{{ number_format($cart->discountPrice()) }} {{ trans('front::messages.currency.suffix') }}</strong>
                </div>
                <div class="ariya-cart-actions">
                    <a href="{{ route('front.cart') }}" class="btn ariya-cart-view-btn">{{ trans('front::messages.header.view-cart') }}</a>
                    @auth
                        <a href="{{ route('front.checkout') }}" class="btn ariya-cart-checkout-btn">{{ trans('front::messages.header.payment') }}</a>
                    @else
                        <a href="{{ route('login') }}" class="btn ariya-cart-checkout-btn">ورود و ادامه خرید</a>
                    @endauth
                </div>
            </footer>
        @endif
    </aside>
</li>
