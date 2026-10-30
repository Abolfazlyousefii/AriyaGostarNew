<div
    class="ariya-quick-variants"
    data-store-action="{{ route('front.cart.store-variants', ['product' => $product]) }}"
>
    <div class="ariya-quick-variants__top">
        <div>
            <h4>{{ $product->title }}</h4>
            <p>مدل موردنظر را انتخاب کنید و تعداد هر مدل را مشخص کنید.</p>
        </div>

        <span class="ariya-quick-variants__count">
            {{ number_format($variantPrices->count()) }} مدل
        </span>
    </div>

    <div class="ariya-quick-variants__search">
        <i class="mdi mdi-magnify"></i>
        <input
            type="search"
            placeholder="جستجو در مدل‌ها..."
            autocomplete="off"
            data-quick-variant-search
        >
    </div>

    <div class="ariya-quick-variants__list">
        @foreach($variantPrices as $variantPrice)
            @php
                $orderedAttributes = $variantPrice->get_attributes
                    ->sortBy(function ($attribute) {
                        return optional($attribute->group)->ordering ?? 999;
                    });

                $variantTitle = $orderedAttributes
                    ->map(function ($attribute) {
                        $groupName = optional($attribute->group)->name;

                        return $groupName
                            ? $groupName . ': ' . $attribute->name
                            : $attribute->name;
                    })
                    ->implode('، ');

                $variantTitle = $variantTitle ?: 'مدل ' . number_format($loop->iteration);

                $available = (int) $variantPrice->stock > 0;
                $minimum = $available ? max(1, (int) cart_min($variantPrice)) : 1;
                $maximum = $available ? max(0, (int) cart_max($variantPrice)) : 0;
            @endphp

            <div
                class="ariya-quick-variant-row {{ !$available ? 'is-unavailable' : '' }}"
                data-price-id="{{ $variantPrice->id }}"
                data-min="{{ $minimum }}"
                data-max="{{ $maximum }}"
                data-search="{{ mb_strtolower($variantTitle) }}"
            >
                <div class="ariya-quick-variant-row__info">
                    <strong>{{ $variantTitle }}</strong>

                    <small>
                        @if($available)
                            موجودی:
                            {{ number_format($variantPrice->stock) }}
                            {{ $product->getUnit() }}
                        @else
                            ناموجود
                        @endif
                    </small>
                </div>

                <div class="ariya-quick-variant-row__price">
                    @if($variantPrice->hasDiscount())
                        <del>{{ number_format($variantPrice->regularPrice()) }}</del>
                    @endif

                    <strong>{{ number_format($variantPrice->salePrice()) }}</strong>
                    <span>{{ trans('front::messages.currency.suffix') }}</span>
                </div>

                <div class="ariya-quick-variant-row__quantity">
                    <button
                        type="button"
                        data-quick-minus
                        {{ !$available ? 'disabled' : '' }}
                    >−</button>

                    <input
                        type="number"
                        value="0"
                        min="0"
                        max="{{ $maximum }}"
                        inputmode="numeric"
                        data-quick-quantity
                        {{ !$available ? 'disabled' : '' }}
                    >

                    <button
                        type="button"
                        data-quick-plus
                        {{ !$available ? 'disabled' : '' }}
                    >+</button>
                </div>
            </div>
        @endforeach

        <div class="ariya-quick-variants__empty d-none" data-quick-empty>
            مدلی با این عبارت پیدا نشد.
        </div>
    </div>

    <div class="ariya-quick-variants__footer">
        <div class="ariya-quick-variants__summary">
            <span>انتخاب شما</span>

            <strong>
                <b data-quick-model-count>۰</b>
                مدل،
                <b data-quick-total-quantity>۰</b>
                عدد
            </strong>
        </div>

        <div class="ariya-quick-variants__footer-actions">
            <a
                href="{{ route('front.products.show', ['product' => $product]) }}"
                class="ariya-quick-product-link"
            >
                <i class="mdi mdi-open-in-new"></i>
                مشاهده صفحه محصول
            </a>

            <button
                type="button"
                class="ariya-quick-add-selected"
                data-quick-add-selected
                disabled
            >
                <i class="mdi mdi-cart-plus"></i>
                افزودن مدل‌های انتخاب‌شده
            </button>
        </div>
    </div>
</div>