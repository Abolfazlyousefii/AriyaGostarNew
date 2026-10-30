{{-- ARIYA_PUBLIC_CARD_VARIANT_POPUP_V1: public card only; click opens secured model popup. --}}
@php
    $ariyaCardLink = route('front.products.show', ['product' => $product]);
    $ariyaCardIsBundle = (bool) ($product->is_bundle ?? false);
    // Bundles with true selectable variants use the same existing model-picker endpoint.
    // Fixed-composition bundles get a read-only component-list popup instead.
    $ariyaCardHasVariants = method_exists($product, 'usesVariantInventoryCodes')
        && $product->usesVariantInventoryCodes();
    $ariyaCardPopupEligible = $ariyaCardHasVariants || $ariyaCardIsBundle;
    $ariyaCardCta = $ariyaCardPopupEligible ? 'انتخاب مدل و تعداد' : 'مشاهده محصول';
    $ariyaCardModalId = 'ariya-bundle-popup-' . $product->id . '-' . uniqid();
    $ariyaCardGuestModalId = 'ariya-login-popup-' . $product->id . '-' . uniqid();
    $ariyaBundleRows = ($ariyaCardIsBundle && !$ariyaCardHasVariants && auth()->check())
        ? \App\Support\BundleContentsPresenter::rows(null, $product->bundle_contents ?? null)
        : [];
@endphp
{{-- ARIYA_MEMBER_CARD_PRICES_V1: gate all numeric prices server-side. --}}
@auth
    @php
        // Use the same effective display-pricing API as the product detail page.
        // These calls must never run for guests, including search engine visitors.
        $ariyaCardSale = $product->getLowestPrice(true);
        $ariyaCardRegular = $product->getLowestDiscount(true);
        $ariyaCardHasPrice = is_numeric($ariyaCardSale) && (float) $ariyaCardSale > 0;
        $ariyaCardSale = $ariyaCardHasPrice ? (float) $ariyaCardSale : null;
        $ariyaCardRegular = is_numeric($ariyaCardRegular) ? (float) $ariyaCardRegular : null;
        $ariyaCardIsDiscounted = $ariyaCardHasPrice && $ariyaCardRegular !== null
            && $ariyaCardRegular > $ariyaCardSale;
        $ariyaCardPercent = $ariyaCardIsDiscounted
            ? max(1, min(100, (int) round(100 * ($ariyaCardRegular - $ariyaCardSale) / $ariyaCardRegular)))
            : null;
    @endphp
@endauth
{{-- ARIYA_MEMBER_CARD_PRICE_VALUES_END --}}
@once
    @push('styles')
        <style id="ariya-member-card-prices-v1">
            .ariya-public-only-card.item {display:flex;width:100%;min-width:0;box-sizing:border-box}
            .ariya-public-only-card > .product-card.ariya-product-card {display:flex;flex-direction:column;width:100%;height:100%;min-width:0;box-sizing:border-box;margin:0!important;overflow:hidden}
            .ariya-public-only-card .product-thumb {display:flex!important;align-items:center;justify-content:center;width:100%;aspect-ratio:1 / 1;overflow:hidden;min-width:0}
            .ariya-public-only-card .product-thumb img {width:100%;height:100%;max-height:235px;object-fit:contain}
            .ariya-public-only-card .product-card-body {display:flex;flex-direction:column;flex:1 1 auto;min-width:0;padding:12px!important;gap:8px}
            .ariya-public-only-card .product-title {margin:0!important;min-height:3.5em;max-height:3.5em;overflow:hidden;line-height:1.75;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;overflow-wrap:anywhere;text-align:right}
            .ariya-public-only-card .product-meta {display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:100%;font-size:12px;color:#718096;text-align:right}
            .ariya-public-only-card .ariya-card-actions {display:block;margin-top:auto!important;padding-top:12px!important;width:100%}
            /* Member-only price area. Keep it immediately above the CTA. */
            .ariya-public-only-card > .product-card.ariya-product-card {position:relative}
            .ariya-public-only-card .ariya-card-discount-badge {position:absolute;z-index:3;top:10px;right:10px;direction:rtl;display:inline-flex;align-items:center;justify-content:center;background:#0c57a6;color:#fff;border-radius:10px;padding:5px 9px;font-size:12px;font-weight:800;line-height:1.4;box-shadow:0 2px 7px rgba(0,0,0,.12)}
            .ariya-public-only-card .ariya-member-price-block {display:flex;min-width:0;min-height:54px;flex-direction:column;align-items:flex-start;justify-content:flex-end;gap:3px;margin-top:auto;direction:rtl;box-sizing:border-box}
            .ariya-public-only-card .ariya-member-price-block .ariya-card-regular-price {display:block;color:#8e99a9;font-size:12px;text-decoration:line-through;line-height:1.5;white-space:normal}
            .ariya-public-only-card .ariya-member-price-block .ariya-card-sale-price {display:block;color:#095ca6;font-size:15px;font-weight:800;line-height:1.55;overflow-wrap:anywhere}
            .ariya-public-only-card .ariya-member-price-block .ariya-card-price-currency {font-size:11px;font-weight:500}
            .ariya-public-only-card .ariya-member-price-block + .ariya-card-actions {margin-top:0!important;padding-top:8px!important}
            .ariya-public-only-card .ariya-card-detail-link {display:flex;align-items:center;justify-content:center;gap:9px;min-height:44px;width:100%;padding:10px 12px;border-radius:10px;background:#0877c9;color:#fff!important;text-decoration:none!important;font-size:13px;font-weight:700;line-height:1.5;transition:background .15s ease,transform .15s ease}
            .ariya-public-only-card .ariya-card-detail-link:hover,.ariya-public-only-card .ariya-card-detail-link:focus-visible {background:#075b9b;color:#fff!important}
            .ariya-public-only-card .ariya-card-detail-link:focus-visible {outline:3px solid #93c5fd;outline-offset:2px}
            .ariya-public-only-card button.ariya-card-detail-link {border:0;cursor:pointer;font-family:inherit;appearance:none}
            .ariya-public-only-card button.ariya-card-detail-link:disabled {background:#94a3b8;opacity:.82;cursor:not-allowed}
            .ariya-bundle-popup {direction:rtl;text-align:right}
            .ariya-bundle-popup .modal-content{border:1px solid #dbeafe;border-radius:17px;overflow:hidden;box-shadow:0 18px 48px rgba(15,23,42,.2)}
            .ariya-bundle-popup .modal-header{display:flex;align-items:center;justify-content:space-between;background:#f0f7ff;border-bottom:1px solid #dbeafe;padding:17px 20px}
            .ariya-bundle-popup .modal-title{font-size:17px;font-weight:800;color:#14365c;line-height:1.7}
            .ariya-bundle-popup .ariya-popup-close{background:transparent;border:0;padding:4px 9px;font-size:25px;color:#64748b;cursor:pointer}
            .ariya-bundle-popup .modal-body{padding:16px 20px}
            .ariya-bundle-popup .ariya-bundle-model-list{display:flex;flex-direction:column;gap:8px;max-height:390px;overflow-y:auto;padding:3px 2px}
            .ariya-bundle-popup .ariya-bundle-model-item{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;background:#f8fbff;border:1px solid #e2e8f0;border-radius:11px;padding:11px 13px}
            .ariya-bundle-popup .ariya-bundle-model-name{font-size:13px;line-height:1.9;overflow-wrap:anywhere}
            .ariya-bundle-popup .ariya-bundle-model-name small{display:block;color:#64748b}
            .ariya-bundle-popup .ariya-bundle-model-qty{flex:none;white-space:nowrap;background:#e1efff;border-radius:8px;color:#075b9b;padding:5px 10px;font-weight:750}
            .ariya-bundle-popup .modal-footer{gap:9px;justify-content:flex-start;border-top:1px solid #e2e8f0;padding:15px 20px}
            .ariya-bundle-popup .ariya-popup-primary{display:inline-flex;justify-content:center;align-items:center;min-height:42px;padding:9px 16px;border-radius:9px;background:#0877c9;color:white!important;text-decoration:none;font-weight:700}
            @media(max-width:575px){.ariya-bundle-popup .modal-dialog{margin:12px}.ariya-bundle-popup .modal-body{padding:12px}.ariya-bundle-popup .modal-header{padding:12px 15px}}
            .ariya-public-only-card .ariya-bundle-card-scroll,.ariya-public-only-card .bundle-components,.ariya-public-only-card .product-prices-div,.ariya-public-only-card .price-index-h,.ariya-public-only-card .discount {display:none!important}
            @media(max-width:480px){.ariya-public-only-card .product-card-body{padding:10px!important}.ariya-public-only-card .ariya-card-detail-link{min-height:42px;font-size:12px}}
        </style>
    @endpush
@endonce
<div class="item ariya-public-only-card" data-ariya-simple-cart-v1="1" dir="rtl">
    <article class="product-card ariya-product-card">
        {{-- ARIYA_MEMBER_CARD_BADGE_BEGIN --}}
        @auth
            @if($ariyaCardIsDiscounted)
                <span class="ariya-card-discount-badge" aria-label="{{ $ariyaCardPercent }} درصد تخفیف">٪ {{ $ariyaCardPercent }}</span>
            @endif
        @endauth
        {{-- ARIYA_MEMBER_CARD_BADGE_END --}}
        <a class="product-thumb ariya-hover-swap-thumb" href="{{ $ariyaCardLink }}" aria-label="مشاهده {{ $product->title }}">
            @include('front::partials.ariya-hover-images', ['product' => $product, 'ariyaHoverPrimaryEager' => false])
        </a>
        <div class="product-card-body">
            <h3 class="product-title"><a href="{{ $ariyaCardLink }}" title="{{ $product->title }}">{{ $product->title }}</a></h3>
            @if($product->category)
                <a class="product-meta" href="{{ $product->category->link }}">{{ $product->category->title }}</a>
            @endif
            {{-- ARIYA_MEMBER_CARD_PRICE_OUTPUT_BEGIN --}}
            @auth
                @if($ariyaCardHasPrice)
                    <div class="ariya-member-price-block" aria-label="قیمت محصول">
                        @if($ariyaCardIsDiscounted)
                            <del class="ariya-card-regular-price">{{ number_format($ariyaCardRegular) }} <span class="ariya-card-price-currency">{{ trans('front::messages.currency.suffix') }}</span></del>
                        @endif
                        <strong class="ariya-card-sale-price">{{ number_format($ariyaCardSale) }} <span class="ariya-card-price-currency">{{ trans('front::messages.currency.suffix') }}</span></strong>
                    </div>
                @endif
            @endauth
            {{-- ARIYA_MEMBER_CARD_PRICE_OUTPUT_END --}}
            <div class="ariya-card-actions">
                {{-- ARIYA_SIMPLE_CARD_CART_V1: only authorized, purchasable non-variable, non-bundle products. --}}
                @if($ariyaCardPopupEligible && auth()->check() && $ariyaCardHasVariants)
                    {{-- Reuse the approved AJAX quick-variant modal; never render models/prices on the card. --}}
                    <button type="button" class="ariya-card-detail-link ariya-card-modal-cta ariya-card-action-button ariya-card-open-variants"
                        data-action="{{ route('front.products.card-variants', ['product' => $product]) }}"
                        data-title="{{ $product->title }}"
                        aria-label="{{ $ariyaCardCta }} - {{ $product->title }}">
                        <i class="mdi mdi-format-list-checks" aria-hidden="true"></i><span>{{ $ariyaCardCta }}</span>
                    </button>
                @elseif($ariyaCardIsBundle && auth()->check())
                    {{-- Fixed bundle: immutable component quantities, displayed only after opening popup. --}}
                    <button type="button" class="ariya-card-detail-link ariya-card-modal-cta" data-toggle="modal" data-target="#{{ $ariyaCardModalId }}"
                        aria-label="{{ $ariyaCardCta }} - {{ $product->title }}" aria-haspopup="dialog">
                        <i class="mdi mdi-format-list-checks" aria-hidden="true"></i><span>{{ $ariyaCardCta }}</span>
                    </button>
                @elseif($ariyaCardPopupEligible)
                    {{-- The existing AJAX variants route is authenticated: never expose protected prices/models to guests. --}}
                    <button type="button" class="ariya-card-detail-link ariya-card-modal-cta" data-toggle="modal" data-target="#{{ $ariyaCardGuestModalId }}"
                        aria-label="{{ $ariyaCardCta }} - {{ $product->title }}" aria-haspopup="dialog">
                        <i class="mdi mdi-format-list-checks" aria-hidden="true"></i><span>{{ $ariyaCardCta }}</span>
                    </button>
                @elseif(auth()->check())
                    {{-- Reuse the existing AJAX quick-add handler and protected cart.store endpoint. --}}
                    @php
                        $ariyaSingleCartPrice = $product->addableToCart() ? $product->getPrices()->first() : null;
                    @endphp
                    @if($ariyaSingleCartPrice)
                        <button type="button"
                            class="ariya-card-detail-link ariya-card-action-button ariya-card-add-to-cart"
                            data-action="{{ route('front.cart.store', ['product' => $product]) }}"
                            data-price-id="{{ $ariyaSingleCartPrice->id }}"
                            data-quantity="{{ max(1, (int) cart_min($ariyaSingleCartPrice)) }}"
                            aria-label="افزودن {{ $product->title }} به سبد خرید">
                            <i class="mdi mdi-cart-plus" aria-hidden="true"></i><span>افزودن به سبد خرید</span>
                        </button>
                    @else
                        <button type="button" class="ariya-card-detail-link" disabled aria-disabled="true">
                            <i class="mdi mdi-cart-off" aria-hidden="true"></i><span>ناموجود</span>
                        </button>
                    @endif
                @else
                    {{-- Guest access: do not reveal price IDs, add-to-cart requests or protected prices. --}}
                    <a class="ariya-card-detail-link" href="{{ $ariyaCardLink }}" aria-label="{{ $ariyaCardCta }} - {{ $product->title }}">
                        <i class="mdi mdi-eye-outline" aria-hidden="true"></i><span>{{ $ariyaCardCta }}</span>
                    </a>
                @endif
            </div>
        </div>
    </article>
@if($ariyaCardIsBundle && !$ariyaCardHasVariants && auth()->check())
    <div class="modal fade ariya-bundle-popup" id="{{ $ariyaCardModalId }}" tabindex="-1" role="dialog" aria-label="محتویات و تعداد {{ $product->title }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">مدل‌های موجود در پک «{{ $product->title }}»</h2>
                    <button class="ariya-popup-close" type="button" data-dismiss="modal" aria-label="بستن">×</button>
                </div>
                <div class="modal-body">
                    @if(count($ariyaBundleRows))
                        <p class="text-muted" style="font-size:13px">ترکیب و تعداد اقلام این پک ثابت است. سفارش کل پک از صفحه محصول انجام می‌شود.</p>
                        <div class="ariya-bundle-model-list" role="list">
                            @foreach($ariyaBundleRows as $ariyaBundleRow)
                                <div class="ariya-bundle-model-item" role="listitem">
                                    <div class="ariya-bundle-model-name">
                                        <strong>{{ $ariyaBundleRow['name'] }}</strong>
                                        @if($ariyaBundleRow['variant'] !== '')
                                            <small>{{ $ariyaBundleRow['variant'] }}</small>
                                        @endif
                                    </div>
                                    <span class="ariya-bundle-model-qty">× {{ number_format($ariyaBundleRow['quantity']) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">جزئیات این پک در صفحه محصول قابل مشاهده است.</p>
                    @endif
                </div>
                <div class="modal-footer">
                    <a class="ariya-popup-primary" href="{{ $ariyaCardLink }}">مشاهده محصول و ثبت سفارش پک</a>
                    <button type="button" class="btn btn-light" data-dismiss="modal">بستن</button>
                </div>
            </div>
        </div>
    </div>
@elseif($ariyaCardPopupEligible && !auth()->check())
    <div class="modal fade ariya-bundle-popup" id="{{ $ariyaCardGuestModalId }}" tabindex="-1" role="dialog" aria-label="ورود برای انتخاب مدل" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">انتخاب مدل و تعداد</h2>
                    <button class="ariya-popup-close" type="button" data-dismiss="modal" aria-label="بستن">×</button>
                </div>
                <div class="modal-body"><p>برای مشاهده مدل‌های قابل سفارش، قیمت همکاری و ثبت تعداد، وارد حساب کاربری شوید.</p></div>
                <div class="modal-footer"><a class="ariya-popup-primary" href="{{ route('login', ['redirect' => $ariyaCardLink]) }}">ورود / عضویت</a></div>
            </div>
        </div>
    </div>
@endif
</div>
@auth
    @once
        @include('front::products.partials.quick-order-assets')
    @endonce
@endauth
@once
    @push('scripts')
        <script id="ariya-bundle-popup-v1-script">
        (function(){
            // Carousel containers can clip Bootstrap modals; move dialogs to document.body.
            function movePopups(){
                document.querySelectorAll('.ariya-bundle-popup').forEach(function(modal){
                    if(modal.parentNode !== document.body) document.body.appendChild(modal);
                });
            }
            if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', movePopups);
            else movePopups();
        })();
        </script>
    @endpush
@endonce
