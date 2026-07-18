<!-- Product Info -->
<div class="col-lg-8 col-md-12 product-info-block aex-product-info-block">
    <div class="product-info aex-product-info dt-sl">
        @php
            $prev_attribute = null;
            $groups = null;
            $attributes_id = [];
            $group_loop = 0;
            $hasVariantGroups = false;
            $variantOptionCount = 0;
            $favorite_product = auth()->check()
                ? auth()->user()->favorites()->where('product_id', $product->id)->first()
                : null;

            $productPrices = $product->getPrices;
            $productPrices->loadMissing('get_attributes');
            $variantInventory = [];

            foreach ($productPrices as $variantPrice) {
                foreach ($variantPrice->get_attributes as $priceAttribute) {
                    if (!isset($variantInventory[$priceAttribute->id])) {
                        $variantInventory[$priceAttribute->id] = [
                            'stock' => 0,
                            'price' => null,
                        ];
                    }

                    $variantInventory[$priceAttribute->id]['stock'] += max(0, (int) $variantPrice->stock);
                    $salePrice = $variantPrice->salePrice();

                    if (
                        $variantInventory[$priceAttribute->id]['price'] === null ||
                        $salePrice < $variantInventory[$priceAttribute->id]['price']
                    ) {
                        $variantInventory[$priceAttribute->id]['price'] = $salePrice;
                    }
                }
            }
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
                                class="aex-action-button add-favorites {{ $favorite_product ? 'favorites' : '' }}"
                                aria-label="{{ $favorite_product ? 'حذف از علاقه‌مندی‌ها' : 'افزودن به علاقه‌مندی‌ها' }}"
                            >
                                <i class="mdi {{ $favorite_product ? 'mdi-heart' : 'mdi-heart-outline' }}"></i>
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

                @if ($productPrices->count())
                    <div class="aex-variants-modal-content d-none" aria-hidden="true">
                        @foreach ($attributeGroups as $attributeGroup)
                            @php
                                $groupAttributes = $product->get_attributes(
                                    $attributeGroup,
                                    $prev_attribute,
                                    $groups,
                                    $attributes_id
                                );
                            @endphp

                            @if ($groupAttributes)
                                @php
                                    $hasVariantGroups = true;
                                    $variantOptionCount += $groupAttributes->count();
                                    $checked = false;
                                    $group_checked = false;
                                    $prev_selected_attr = $attributes_id;
                                @endphp

                                <div
                                    class="product-variant aex-product-variant aex-modal-variant-group {{ $attributeGroup->type == 'color' ? 'product-variant-color aex-color-variant' : '' }}"
                                    data-variant-group="{{ $attributeGroup->id }}"
                                >
                                    <div class="aex-modal-group-heading">
                                        <div>
                                            <strong>{{ $attributeGroup->name }}</strong>
                                            <span>مدل موردنظر را انتخاب کنید</span>
                                        </div>
                                        <span class="aex-modal-group-selected" id="attributeGroup-{{ $attributeGroup->id }}"></span>
                                    </div>

                                    <ul class="product-variants aex-product-variants aex-modal-variant-grid">
                                        @foreach ($groupAttributes as $attribute)
                                            @php
                                                if ($group_loop != 0 && count($prev_selected_attr)) {
                                                    $has_stock = $product->hasAttributeStock($attribute, $prev_selected_attr);
                                                } else {
                                                    $has_stock = $product->hasAttributeStock($attribute);
                                                }

                                                if ($selected_price && $selected_price->get_attributes()->find($attribute->id)) {
                                                    $checked = true;
                                                    $prev_attribute = $attribute;
                                                    $attributes_id[] = $attribute->id;
                                                    $group_checked = true;
                                                } else {
                                                    $checked = false;
                                                }

                                                if ($loop->last && $checked == false && $group_checked == false) {
                                                    $checked = true;
                                                    $prev_attribute = $attribute;
                                                    $attributes_id[] = $attribute->id;
                                                }

                                                $attributeStock = $variantInventory[$attribute->id]['stock'] ?? 0;
                                            @endphp

                                            <li
                                                class="ui-variant product-attribute aex-variant-item aex-modal-variant-item {{ $has_stock ? '' : 'unavailable' }}"
                                                data-variant-search="{{ mb_strtolower($attribute->name) }}"
                                                title="{{ $has_stock ? $attribute->name : 'ناموجود' }}"
                                            >
                                                <label class="ui-variant aex-variant-label aex-modal-variant-label mb-0 {{ $attributeGroup->type == 'color' ? 'ui-variant--color' : '' }}">
                                                    <input
                                                        data-product="{{ $product->slug }}"
                                                        type="radio"
                                                        value="{{ $attribute->id }}"
                                                        name="attributes_group[{{ $loop->parent->iteration }}][]"
                                                        class="variant-selector"
                                                        {{ $checked ? 'checked' : '' }}
                                                        {{ $has_stock ? '' : 'disabled' }}
                                                    >

                                                    <span class="aex-modal-variant-card">
                                                        <span class="aex-modal-variant-name">
                                                            @if ($attributeGroup->type == 'color')
                                                                <span
                                                                    data-color-id="{{ $attribute->id }}"
                                                                    data-group-id="attributeGroup-{{ $attributeGroup->id }}"
                                                                    data-name="{{ $attribute->name }}"
                                                                    class="ui-variant-shape aex-color-shape"
                                                                    style="background-color: {{ $attribute->value }}"
                                                                    {{ $checked ? 'checked' : '' }}
                                                                ></span>
                                                            @endif
                                                            {{ $attribute->name }}
                                                        </span>

                                                        <span class="aex-modal-variant-stock {{ $attributeStock > 0 ? 'is-available' : 'is-empty' }}">
                                                            @if ($attributeStock > 0)
                                                                موجودی: {{ number_format($attributeStock) }} {{ $product->getUnit() }}
                                                            @else
                                                                ناموجود
                                                            @endif
                                                        </span>
                                                    </span>
                                                </label>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>

                                @php
                                    $groups[] = $attributeGroup;
                                    $group_loop++;
                                @endphp
                            @endif
                        @endforeach

                        @php
                            $selected_price = $product->getPriceWithAttributes($attributes_id) ?: $selected_price;
                        @endphp
                    </div>
                @endif

                @if ($hasVariantGroups)
                    <div class="aex-variant-picker-summary">
                        <div class="aex-variant-picker-icon" aria-hidden="true">
                            <i class="mdi mdi-format-list-bulleted"></i>
                        </div>

                        <div class="aex-variant-picker-copy">
                            <span>مدل انتخاب‌شده</span>
                            <strong>{{ $selected_price ? ($selected_price->getAttributesValue() ?: 'انتخاب مدل') : 'انتخاب مدل' }}</strong>
                        </div>

                        <button
                            type="button"
                            class="aex-open-variants-modal"
                            data-toggle="modal"
                            data-target="#aex-variants-modal-{{ $product->id }}"
                        >
                            مشاهده مدل‌ها
                            <span>{{ number_format($variantOptionCount) }}</span>
                        </button>
                    </div>
                @endif

                @if ($selected_price && $selected_price->stock > 0)
                    <div class="aex-stock-status" role="status">
                        موجودی مدل انتخابی
                        <strong>({{ number_format($selected_price->stock) }})</strong>
                        {{ $product->getUnit() }} می‌باشد
                    </div>
                @else
                    <div class="aex-stock-status aex-stock-status--empty" role="status">
                        این مدل در حال حاضر موجود نیست
                    </div>
                @endif
            </section>

            <aside class="col-lg-5 col-md-5 aex-buy-column">
                <div class="aex-buy-card">
                    @if ($product->labels->count())
                        <div class="aex-labels">
                            @foreach ($product->labels as $label)
                                <span>{{ $label->title }}</span>
                            @endforeach
                        </div>
                    @endif

                    @if ($selected_price)
                        <div class="aex-selected-variant">
                            <span>مدل انتخابی</span>
                            <strong>{{ $selected_price->getAttributesName() ?: $product->title }}</strong>
                        </div>
                    @endif

                    @if ($product->isPhysical() && $product->addableToCart() && $selected_price)
                        @auth
                            <div class="aex-buy-row aex-quantity-row">
                                <span class="aex-buy-row-label">تعداد:</span>
                                <div class="number-input aex-number-input">
                                    <button type="button" onclick="this.parentNode.querySelector('input[type=number]').stepDown()" aria-label="کاهش تعداد"></button>
                                    <input
                                        id="cart-quantity"
                                        class="quantity"
                                        min="{{ cart_min($selected_price) }}"
                                        max="{{ cart_max($selected_price) }}"
                                        value="{{ cart_min($selected_price) }}"
                                        type="number"
                                        required
                                        aria-label="تعداد سفارش"
                                    >
                                    <button type="button" onclick="this.parentNode.querySelector('input[type=number]').stepUp()" class="plus" aria-label="افزایش تعداد"></button>
                                </div>
                            </div>

                            <div class="aex-price-area">
                                <span class="aex-price-label">قیمت</span>
                                <div class="aex-price-values">
                                    @if ($selected_price->hasDiscount())
                                        <div class="aex-price-discount-row">
                                            <del>{{ number_format($selected_price->regularPrice()) }}</del>
                                            <span class="aex-discount-badge">{{ $selected_price->discount() }}٪</span>
                                        </div>
                                    @endif

                                    <div class="aex-final-price">
                                        <span class="price">{{ number_format($selected_price->salePrice()) }}</span>
                                        <span class="currency">{{ trans('front::messages.currency.suffix') }}</span>
                                    </div>
                                </div>
                            </div>

                            <button
                                data-price_id="{{ $selected_price->id }}"
                                data-image="{{ $selected_price->image ? asset($selected_price->image->image) : '' }}"
                                data-action="{{ route('front.cart.store', ['product' => $product]) }}"
                                data-product="{{ $product->slug }}"
                                type="button"
                                class="aex-add-to-cart add-to-cart"
                            >
                                {{ trans('front::messages.products.add-to-cart') }}
                            </button>
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
                </div>
            </aside>
        </div>

        @if ($hasVariantGroups)
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
                                <h5 class="modal-title" id="aex-variants-modal-title-{{ $product->id }}">انتخاب مدل محصول</h5>
                                <p>مدل موردنظر را انتخاب کنید؛ موجودی هر مدل در همین لیست نمایش داده شده است.</p>
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

                            <div class="aex-modal-groups-target"></div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
