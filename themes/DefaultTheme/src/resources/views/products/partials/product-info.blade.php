<!-- Product Info -->
<div class="col-lg-8 col-md-12 product-info-block aex-product-info-block">
    <div class="product-info aex-product-info dt-sl">
        @php
            $favoriteProduct = auth()->check()
                ? auth()->user()->favorites()->where('product_id', $product->id)->first()
                : null;

            // Use all price rows so variable products can also show out-of-stock models.
            // A product is variable only when it truly has multiple prices or attribute-linked prices;
            // cart limits on a simple product must never open the model-selection modal.
            $productPrices = $product->prices()
                ->with(['get_attributes', 'image'])
                ->orderBy('id')
                ->get();

            $availablePrices = $productPrices->filter(function ($priceItem) {
                return (int) $priceItem->stock > 0;
            });

            $defaultDisplayPrice = $selected_price
                ?: $availablePrices->sortBy(function ($priceItem) {
                    return $priceItem->salePrice();
                })->first()
                ?: $productPrices->first();

            // Detect a real variable product from its price rows instead of relying on
            // an optional Product model helper that may not exist in older installations.
            $isVariableProduct = $productPrices->count() > 1
                || $productPrices->contains(function ($priceItem) {
                    return $priceItem->relationLoaded('get_attributes')
                        ? $priceItem->get_attributes->isNotEmpty()
                        : $priceItem->get_attributes()->exists();
                });
            $orderableVariantCount = $productPrices->filter(function ($priceItem) {
                return (int) $priceItem->stock > 0 && (int) cart_max($priceItem) >= max(1, (int) cart_min($priceItem));
            })->count();
        @endphp

        <div class="row no-gutters aex-product-info-row">
            <section class="col-lg-7 col-md-7 aex-product-center">
                <header class="aex-product-header">
                    <h1>{{ $product->title }}</h1>

                    <div class="aex-product-subtitle">
                        @if ($product->category)
                            <a href="{{ route('front.products.category', ['category' => $product->category]) }}">
                                {{ $product->category->title }}
                            </a>
                        @endif

                        @if ($product->brand)
                            <span class="aex-subtitle-separator">،</span>
                            <a href="{{ route('front.brands.show', ['brand' => $product->brand]) }}">
                                {{ $product->brand->name }}
                            </a>
                        @endif
                    </div>

                    <div class="aex-product-actions">
                        @if (option('show_product_share_links', 1) == 1)
                            <button
                                type="button"
                                class="aex-action-button"
                                data-toggle="modal"
                                data-target="#shareproduct"
                                aria-label="اشتراک‌گذاری محصول"
                            >
                                <i class="mdi mdi-share-variant"></i>
                            </button>
                        @endif

                        @if (auth()->check())
                            <button
                                id="add-to-favorites"
                                type="button"
                                data-action="{{ route('front.favorites.store') }}"
                                data-product="{{ $product->id }}"
                                class="aex-action-button add-favorites {{ $favoriteProduct ? 'favorites' : '' }}"
                                aria-label="{{ $favoriteProduct ? 'حذف از علاقه‌مندی‌ها' : 'افزودن به علاقه‌مندی‌ها' }}"
                            >
                                <i class="mdi {{ $favoriteProduct ? 'mdi-heart' : 'mdi-heart-outline' }}"></i>
                            </button>
                        @else
                            <a
                                href="{{ route('login', ['redirect' => route('front.products.show', ['product' => $product])]) }}"
                                class="aex-action-button"
                                aria-label="ورود برای افزودن به علاقه‌مندی‌ها"
                            >
                                <i class="mdi mdi-heart-outline"></i>
                            </a>
                        @endif
                    </div>
                </header>

                @if ($isVariableProduct)
                    <div class="aex-variant-picker-summary">
                        <div class="aex-variant-picker-icon" aria-hidden="true">
                            <i class="mdi mdi-format-list-checks"></i>
                        </div>

                        <div class="aex-variant-picker-copy">
                            <span>انتخاب مدل‌های مورد نیاز</span>
                            <strong id="aex-selected-models-summary">هنوز مدلی انتخاب نشده است</strong>
                        </div>

                        <button
                            type="button"
                            class="aex-open-variants-modal"
                            data-toggle="modal"
                            data-target="#aex-variants-modal-{{ $product->id }}"
                        >
                            مشاهده مدل‌ها
                            <span>{{ number_format($orderableVariantCount) }}</span>
                        </button>
                    </div>

                    <div class="aex-selection-help">
                        می‌توانید چند مدل را هم‌زمان انتخاب کرده و برای هرکدام تعداد جداگانه تعیین کنید.
                    </div>
                @elseif ($defaultDisplayPrice && $defaultDisplayPrice->stock > 0)
                    <div class="aex-stock-status" role="status">
                        موجودی محصول
                        <strong>({{ number_format($defaultDisplayPrice->stock) }})</strong>
                        {{ $product->getUnit() }} می‌باشد
                    </div>
                @endif
            </section>

            <aside class="col-lg-5 col-md-5 aex-buy-column">
                <div
                    class="aex-buy-card aex-buy-card--price-only"
                    data-default-price="{{ auth()->check() && $defaultDisplayPrice ? $defaultDisplayPrice->salePrice() : 0 }}"
                >
                    @auth
                        @if ($defaultDisplayPrice && $product->isPhysical() && $product->addableToCart())
                            <div class="aex-price-area aex-price-area--single">
                                <span class="aex-price-label">قیمت</span>

                                <div class="aex-price-values">
                                    <div class="aex-final-price">
                                        <span id="aex-main-price-value" class="price">
                                            {{ number_format($defaultDisplayPrice->salePrice()) }}
                                        </span>
                                        <span class="currency">{{ trans('front::messages.currency.suffix') }}</span>
                                    </div>
                                </div>
                            </div>

                            @if ($isVariableProduct)
                                <button
                                    type="button"
                                    class="aex-add-to-cart aex-add-selected-to-cart"
                                    data-action="{{ route('front.cart.store', ['product' => $product]) }}"
                                    data-product="{{ $product->slug }}"
                                    disabled
                                >
                                    {{ trans('front::messages.products.add-to-cart') }}
                                </button>

                                <div id="aex-buy-card-hint" class="aex-buy-card-hint">
                                    ابتدا مدل‌های موردنظر را از لیست انتخاب کنید.
                                </div>
                            @else
                                <input
                                    id="cart-quantity"
                                    class="quantity"
                                    type="hidden"
                                    value="{{ cart_min($defaultDisplayPrice) }}"
                                >

                                <button
                                    data-price_id="{{ $defaultDisplayPrice->id }}"
                                    data-image="{{ $defaultDisplayPrice->image ? asset($defaultDisplayPrice->image->image) : '' }}"
                                    data-action="{{ route('front.cart.store', ['product' => $product]) }}"
                                    data-product="{{ $product->slug }}"
                                    type="button"
                                    class="aex-add-to-cart add-to-cart"
                                >
                                    {{ trans('front::messages.products.add-to-cart') }}
                                </button>
                            @endif
                        @else
                            <div class="aex-unavailable-box">
                                <strong>{{ trans('front::messages.products.unavailable') }}</strong>
                                <p>{{ trans('front::messages.products.text-unavailable') }}</p>

                                <button
                                    id="stock_notify_btn"
                                    data-user="{{ auth()->check() ? auth()->user()->id : '' }}"
                                    data-product="{{ $product->id }}"
                                    type="button"
                                    class="aex-stock-notify-button"
                                >
                                    {{ trans('front::messages.products.let-me-know') }}
                                </button>
                            </div>
                        @endif
                    @else
                        <div class="aex-login-price-note">
                            برای مشاهده قیمت همکاری و ثبت سفارش وارد حساب کاربری شوید.
                        </div>

                        <a
                            href="{{ route('login', ['redirect' => route('front.products.show', ['product' => $product])]) }}"
                            class="aex-login-price-button"
                        >
                            مشاهده قیمت پس از ورود
                        </a>
                    @endauth
                </div>
            </aside>
        </div>

        @if ($isVariableProduct)
            <div
                class="modal fade aex-variants-modal"
                id="aex-variants-modal-{{ $product->id }}"
                tabindex="-1"
                role="dialog"
                aria-labelledby="aex-variants-modal-title-{{ $product->id }}"
                aria-hidden="true"
            >
                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header aex-variants-modal-header">
                            <div>
                                <h5 class="modal-title" id="aex-variants-modal-title-{{ $product->id }}">
                                    انتخاب مدل‌ها و تعداد سفارش
                                </h5>
                                <p>روی هر مدل بزنید، تعداد آن را مشخص کنید و مدل‌های دیگر را نیز به انتخاب خود اضافه کنید.</p>
                            </div>

                            <button type="button" class="close" data-dismiss="modal" aria-label="بستن">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body aex-variants-modal-body">
                            <div class="aex-variant-search-wrap">
                                <i class="mdi mdi-magnify" aria-hidden="true"></i>
                                <input
                                    type="search"
                                    class="aex-variant-search"
                                    placeholder="جست‌وجوی مدل..."
                                    autocomplete="off"
                                    aria-label="جست‌وجوی مدل"
                                >
                            </div>

                            <div class="aex-variant-search-empty" hidden>
                                مدلی با این عبارت پیدا نشد.
                            </div>

                            <div class="aex-multi-model-grid">
                                @foreach ($productPrices as $variantPrice)
                                    @php
                                        $variantName = trim($variantPrice->getAttributesValue())
                                            ?: trim($variantPrice->getAttributesName())
                                            ?: $product->title;
                                        $variantStock = max(0, (int) $variantPrice->stock);
                                        $variantMin = max(1, (int) cart_min($variantPrice));
                                        $variantMax = max(0, (int) cart_max($variantPrice));
                                        $canOrderVariant = $variantStock > 0 && $variantMax >= $variantMin;
                                    @endphp

                                    <article
                                        class="aex-model-card {{ $canOrderVariant ? '' : 'is-unavailable' }}"
                                        data-model-card
                                        data-search="{{ mb_strtolower($variantName) }}"
                                        data-price-id="{{ $variantPrice->id }}"
                                        data-price="{{ auth()->check() ? $variantPrice->salePrice() : 0 }}"
                                        data-stock="{{ $variantStock }}"
                                        data-min="{{ $variantMin }}"
                                        data-max="{{ $variantMax }}"
                                        data-name="{{ $variantName }}"
                                    >
                                        <button
                                            type="button"
                                            class="aex-model-card-main"
                                            {{ $canOrderVariant ? '' : 'disabled' }}
                                            aria-pressed="false"
                                        >
                                            <span class="aex-model-check" aria-hidden="true">
                                                <i class="mdi mdi-check"></i>
                                            </span>

                                            <span class="aex-model-card-copy">
                                                <strong>{{ $variantName }}</strong>

                                                <span class="aex-model-stock {{ $canOrderVariant ? 'is-available' : 'is-empty' }}">
                                                    @if ($canOrderVariant)
                                                        موجودی: {{ number_format($variantStock) }} {{ $product->getUnit() }}
                                                    @else
                                                        ناموجود
                                                    @endif
                                                </span>
                                            </span>

                                            @auth
                                                <span class="aex-model-price">
                                                    {{ number_format($variantPrice->salePrice()) }}
                                                    <small>{{ trans('front::messages.currency.suffix') }}</small>
                                                </span>
                                            @endauth
                                        </button>

                                        @if ($canOrderVariant)
                                            <div class="aex-model-quantity" hidden>
                                                <span>تعداد این مدل</span>

                                                <div class="aex-model-stepper">
                                                    <button type="button" class="aex-model-qty-minus" aria-label="کاهش تعداد">
                                                        <i class="mdi mdi-minus"></i>
                                                    </button>

                                                    <input
                                                        type="number"
                                                        class="aex-model-qty-input"
                                                        min="{{ $variantMin }}"
                                                        max="{{ $variantMax }}"
                                                        value="{{ $variantMin }}"
                                                        inputmode="numeric"
                                                        aria-label="تعداد {{ $variantName }}"
                                                    >

                                                    <button type="button" class="aex-model-qty-plus" aria-label="افزایش تعداد">
                                                        <i class="mdi mdi-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    </article>
                                @endforeach
                            </div>
                        </div>

                        <div class="modal-footer aex-variants-modal-footer">
                            <div class="aex-modal-selection-summary">
                                <span id="aex-modal-selected-count">۰ مدل انتخاب شده</span>
                                @auth
                                    <strong>
                                        جمع:
                                        <span id="aex-modal-total-price">۰</span>
                                        {{ trans('front::messages.currency.suffix') }}
                                    </strong>
                                @endauth
                            </div>

                            <button type="button" class="aex-apply-model-selection" data-dismiss="modal" disabled>
                                ثبت انتخاب‌ها
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
