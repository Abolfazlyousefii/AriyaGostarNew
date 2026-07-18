@if ($product->isPhysical() && isset($variant_prices) && $variant_prices->count() > 1)
    @php
        $activeVariantId = optional($selected_price)->id;
    @endphp

    <section class="variant-order-panel mt-3" data-product="{{ $product->slug }}" data-action="{{ route('front.cart.store-variants', ['product' => $product]) }}">
        <div class="variant-order-heading">
            <div>
                <h3>انتخاب و سفارش مدل‌ها</h3>
                <p>قیمت و موجودی هر مدل را ببینید؛ برای سفارش چند مدل، تعداد هرکدام را وارد کنید.</p>
            </div>
            <span class="variant-order-count">{{ $variant_prices->count() }} مدل</span>
        </div>

        <div class="variant-order-search-wrap">
            <i class="mdi mdi-magnify"></i>
            <input type="search" class="variant-order-search" placeholder="جستجو میان مدل‌ها..." autocomplete="off">
        </div>

        <div class="variant-order-list" role="listbox" aria-label="مدل‌های قابل سفارش">
            @foreach ($variant_prices as $variantPrice)
                @php
                    $orderedAttributes = $variantPrice->get_attributes
                        ->sortBy(function ($attribute) {
                            return optional($attribute->group)->ordering ?? 999;
                        });

                    $variantTitle = $orderedAttributes
                        ->map(function ($attribute) {
                            $groupName = optional($attribute->group)->name;
                            return $groupName ? $groupName . ': ' . $attribute->name : $attribute->name;
                        })
                        ->implode('، ');

                    $variantTitle = $variantTitle ?: 'مدل اصلی';
                    $attributeIds = $orderedAttributes->pluck('id')->values();
                    $isAvailable = (int) $variantPrice->stock > 0;
                    $minOrder = $isAvailable ? cart_min($variantPrice) : 1;
                    $maxOrder = $isAvailable ? cart_max($variantPrice) : 0;
                @endphp

                <div class="variant-order-row {{ $activeVariantId == $variantPrice->id ? 'is-active' : '' }} {{ !$isAvailable ? 'is-unavailable' : '' }}"
                     role="option"
                     tabindex="{{ $isAvailable ? '0' : '-1' }}"
                     aria-selected="{{ $activeVariantId == $variantPrice->id ? 'true' : 'false' }}"
                     data-price-id="{{ $variantPrice->id }}"
                     data-title="{{ $variantTitle }}"
                     data-sale-price="{{ $variantPrice->salePrice() }}"
                     data-regular-price="{{ $variantPrice->regularPrice() }}"
                     data-discount="{{ $variantPrice->discount() }}"
                     data-stock="{{ (int) $variantPrice->stock }}"
                     data-min-order="{{ $minOrder }}"
                     data-max-order="{{ $maxOrder }}"
                     data-attributes='@json($attributeIds)'
                     data-search="{{ mb_strtolower($variantTitle) }}">
                    <div class="variant-order-selector" aria-hidden="true">
                        <span></span>
                    </div>

                    <div class="variant-order-main">
                        <strong>{{ $variantTitle }}</strong>
                        <small>
                            @if ($isAvailable)
                                موجودی: {{ number_format($variantPrice->stock) }} {{ $product->getUnit() }}
                            @else
                                ناموجود
                            @endif
                        </small>
                    </div>

                    <div class="variant-order-price">
                        @if ($variantPrice->hasDiscount())
                            <del>{{ number_format($variantPrice->regularPrice()) }}</del>
                        @endif
                        <strong>{{ number_format($variantPrice->salePrice()) }}</strong>
                        <span>{{ trans('front::messages.currency.suffix') }}</span>
                    </div>

                    <div class="variant-order-quantity" data-ignore-row-click="true">
                        <button type="button" class="variant-quantity-minus" aria-label="کاهش تعداد" {{ !$isAvailable ? 'disabled' : '' }}>−</button>
                        <input type="number"
                               class="variant-quantity-input"
                               value="0"
                               min="0"
                               max="{{ $maxOrder }}"
                               inputmode="numeric"
                               aria-label="تعداد سفارش {{ $variantTitle }}"
                               {{ !$isAvailable ? 'disabled' : '' }}>
                        <button type="button" class="variant-quantity-plus" aria-label="افزایش تعداد" {{ !$isAvailable ? 'disabled' : '' }}>+</button>
                    </div>
                </div>
            @endforeach

            <div class="variant-order-empty d-none">مدلی با این عبارت پیدا نشد.</div>
        </div>

        <div class="variant-order-footer">
            <div class="variant-order-total">
                <span>مدل‌های انتخاب‌شده</span>
                <strong><span class="variant-selected-count">۰</span> مدل، <span class="variant-selected-quantity">۰</span> عدد</strong>
            </div>
            <button type="button" class="btn-primary-cm variant-add-selected" disabled>
                افزودن مدل‌های انتخاب‌شده به سبد
            </button>
        </div>
    </section>
@endif
