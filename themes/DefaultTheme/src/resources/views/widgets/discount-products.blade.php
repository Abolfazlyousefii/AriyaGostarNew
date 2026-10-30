@php
    $variables = get_widget($widget);
    $products = $variables['products'];
    $timer = $variables['timer'];

    // ARIYA_SALES_CONTROL_DISCOUNT_SHOWCASE_V1
    $salesCampaign = app(
        \App\Services\Sales\SalesCampaignService::class
    );

    $controlledProducts = $salesCampaign
        ->showcaseProducts('discount');

    if ($controlledProducts !== null) {
        $products = $controlledProducts;
    }
@endphp

@php
    // ARIYA_DISCOUNT_BOX_ACTIVE_ONLY_V2
    // Only currently active, sellable discounts are eligible for this showcase.
    // Expired Scheduled Sales Control discounts and expired legacy Price discounts
    // must never keep ordinary products inside the active-discount carousel.

    $ariyaCardColumns = [
        'products.id', 'products.title', 'products.type',
        'products.category_id', 'products.slug',
        'products.image', 'products.special', 'products.image_alt',
    ];

    $ariyaLegacyActive = \App\Models\Product::detectLang()
        ->published()
        ->available()
        ->whereHas('prices', function ($price) {
            $price->where('stock', '>', 0)
                ->where('discount', '>', 0)
                ->where('regular_price', '>', 0)
                ->whereNotNull('discount_price')
                ->whereColumn('discount_price', '<', 'regular_price')
                ->where(function ($date) {
                    $date->whereNull('discount_expire_at')
                        ->orWhere('discount_expire_at', '>', now());
                });
        })
        ->with(['lowestPrice', 'category:id,title,slug,type'])
        ->select($ariyaCardColumns)
        ->latest('products.id')
        ->get();

    $ariyaStockService = app(\App\Services\Sales\SalesControlService::class);
    $ariyaLiveControlIds = \App\Models\SalesControl::query()
        ->where('enabled', true)
        ->where('discount_enabled', true)
        ->get()
        ->filter(function ($control) use ($ariyaStockService) {
            if (!$control->discountIsActive()) {
                return false;
            }
            $remaining = $ariyaStockService->remaining($control);
            return $remaining === null || $remaining > 0;
        })
        ->pluck('product_id')
        ->unique()
        ->values()
        ->all();

    $ariyaControlActive = collect();
    if ($ariyaLiveControlIds) {
        $ariyaControlActive = \App\Models\Product::detectLang()
            ->published()
            ->available()
            ->whereIn('products.id', $ariyaLiveControlIds)
            ->with(['lowestPrice', 'category:id,title,slug,type'])
            ->select($ariyaCardColumns)
            ->get();
    }

    // Active Sales Control promotions first, then regular Price promotions.
    // Never show ordinary cards alongside genuinely discounted cards.
    $ariyaOnlyDiscounted = $ariyaControlActive
        ->concat($ariyaLegacyActive)
        ->unique('id')
        ->values();

    if ($ariyaOnlyDiscounted->isNotEmpty()) {
        $ariyaDiscountMode = 'active';
        $products = $ariyaOnlyDiscounted;

    } else {
        $ariyaDiscountMode = 'fallback';
        $timer = null;
        // No current discount anywhere among published and available products:
        // display the previous widget products, otherwise use real ordinary stock.
        $products = collect($variables['products'] ?? []);
        if ($products->isEmpty()) {
            $products = \App\Models\Product::detectLang()
                ->published()
                ->available()
                ->where(function ($query) {
                    $query->where(function ($q) {
                        $q->whereNotNull('products.image')
                            ->where('products.image', '<>', '');
                    })->orWhereHas('gallery', function ($q) {
                        $q->whereNotNull('image')
                            ->where('image', '<>', '');
                    });
                })
                ->with(['lowestPrice', 'category:id,title,slug,type'])
                ->select($ariyaCardColumns)
                ->latest('products.id')
                ->limit(12)
                ->get();
        }
    }
    // ARIYA_BUNDLE_DISCOUNT_TIMER_MAX_V1
    // Timed SalesControl and legacy Price discounts belonging ONLY to the
    // eligible product cards displayed by this widget. An untimed discount
    // can stay visible, but cannot define a countdown deadline.
    // Public, checkout-inactive bundle previews do not count as discounts.
    $timer = null;
    if ($ariyaDiscountMode === 'active') {
        $ariyaEndCandidates = [];
        $ariyaControlledIds = $ariyaControlActive->pluck('id')->unique()->all();
        if ($ariyaControlledIds) {
            $ariyaControlledMax = \App\Models\SalesControl::query()
                ->whereIn('product_id', $ariyaControlledIds)
                ->where('enabled', true)
                ->where('discount_enabled', true)
                ->where(function ($query) {
                    $query->whereNull('discount_starts_at')
                        ->orWhere('discount_starts_at', '<=', now());
                })
                ->where('discount_ends_at', '>', now())
                ->max('discount_ends_at');
            if ($ariyaControlledMax) {
                $ariyaEndCandidates[] = \Carbon\Carbon::parse($ariyaControlledMax)->getTimestamp() * 1000;
            }
        }
        $ariyaLegacyIds = $ariyaLegacyActive->pluck('id')->unique()->all();
        if ($ariyaLegacyIds) {
            $ariyaLegacyMax = \App\Models\Price::query()
                ->whereIn('product_id', $ariyaLegacyIds)
                ->where('stock', '>', 0)
                ->where('discount', '>', 0)
                ->where('regular_price', '>', 0)
                ->whereNotNull('discount_price')
                ->whereColumn('discount_price', '<', 'regular_price')
                ->where('discount_expire_at', '>', now())
                ->max('discount_expire_at');
            if ($ariyaLegacyMax) {
                $ariyaEndCandidates[] = \Carbon\Carbon::parse($ariyaLegacyMax)->getTimestamp() * 1000;
            }
        }
        $timer = $ariyaEndCandidates ? max($ariyaEndCandidates) : null;
    }
    @endphp
<!-- Start products -->
@if ($products->count())
    <div class="ariya-discount-products-widget ariya-mobile-discount-section" data-ariya-discount-mode="{{ $ariyaDiscountMode }}" data-ariya-discount-count="{{ $products->count() }}">
        @if ($ariyaDiscountMode === 'fallback')
            <style>
                /*
                 * Task 14: ordinary fallback products must not
                 * display misleading discount decorations.
                 */
                .ariya-discount-products-widget[data-ariya-discount-mode="fallback"] .product-card .discount,
                .ariya-discount-products-widget[data-ariya-discount-mode="fallback"] .product-card .product-price-del {
                    display: none !important;
                }
            </style>
        @endif
            {{-- ARIYA_TASK02_3D14_VALID_PROMOS_V1 --}}
            @if (
                $ariyaDiscountMode === 'active' &&
                is_numeric($timer) &&
                (int) $timer > (time() * 1000)
            )
                <div class="discount-timer ariya-discount-max-timer" aria-label="بیشترین زمان باقی‌مانده پیشنهادهای ویژه">
                    <span class="ariya-discount-max-label">بیشترین زمان باقی‌مانده پیشنهادها:</span>
                    <span data-timer="{{ $timer }}" class="timer" role="timer">در حال محاسبه...</span>
                </div>
            @endif

        <div class="row">
            <div class="col-xl-12 col-lg-12">
                <section class="slider-section dt-sl mb-3">
                    <div class="row mb-3">
                        <div class="col-12 px-0 px-sm-3">
                            <div class="section-title text-sm-title title-wide-custom title-wide no-after-title-wide">
                                <h2>{{ $ariyaDiscountMode === 'active' ? $widget->option('title') : 'محصولات منتخب' }}</h2>
                                @if ($widget->option('link'))
                                    <a href="{{ $widget->option('link') }}">{{ $widget->option('link_title', trans('front::messages.user.see-all')) }} ></a>
                                @endif
                            </div>
                        </div>

                        <div class="col-12 px-res-0">
                            <div class="product-carousel carousel-md owl-carousel owl-theme">
                                @foreach ($products as $product)
                                    @include('front::partials.product-block')
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
@endif
<!-- End products -->

@push('styles')
<style>
.ariya-discount-products-widget > .ariya-discount-max-timer {
    display:flex;align-items:center;justify-content:center;flex-wrap:wrap;
    gap:9px;direction:rtl;line-height:1.8;padding:8px 12px;
}
.ariya-discount-max-timer .ariya-discount-max-label {font-size:14px;font-weight:600}
.ariya-discount-max-timer .timer {direction:ltr;display:inline-block;font-variant-numeric:tabular-nums;white-space:nowrap}
</style>
@endpush
@push('scripts')
<script>
$(function () {
    document.querySelectorAll('.ariya-discount-products-widget .ariya-discount-max-timer .timer[data-timer]').forEach(function (element) {
        if (element.dataset.ariyaTimerRunning) return;
        const expiry = Number(element.dataset.timer);
        if (!Number.isFinite(expiry) || expiry <= 0) return;
        element.dataset.ariyaTimerRunning = '1';
        let interval = null;
        function refresh() {
            const remaining = Math.max(0, expiry - Date.now());
            const days = Math.floor(remaining / 86400000);
            const hours = Math.floor((remaining % 86400000) / 3600000);
            const minutes = Math.floor((remaining % 3600000) / 60000);
            const seconds = Math.floor((remaining % 60000) / 1000);
            const two = n => String(n).padStart(2, '0');
            element.textContent = days + ':' + two(hours) + ':' + two(minutes) + ':' + two(seconds);
            if (remaining === 0) {
                element.textContent = 'زمان تخفیف‌ها به پایان رسید';
                if (interval !== null) clearInterval(interval);
            }
        }
        refresh();
        if (expiry > Date.now()) interval = setInterval(refresh, 1000);
    });
});
</script>
@endpush
