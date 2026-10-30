@php
    // Find a real cover among the bundle's products, not just the first model:
    // many variants have no dedicated thumbnail.
    $ariyaBundleCoverVariant = $bundlePreview->variants()
        ->whereHas('price.product', function ($query) {
            $query->whereNotNull('image')->where('image', '<>', '');
        })
        ->with('price.product')
        ->first();
    if (!$ariyaBundleCoverVariant) {
        $ariyaBundleCoverVariant = $bundlePreview->variants()
            ->whereHas('price.product.gallery')
            ->with('price.product')
            ->first();
    }
    $ariyaBundleCover = $ariyaBundleCoverVariant?->price?->product;
    $ariyaBundlePhoto = $ariyaBundleCover
        ? $ariyaBundleCover->imageUrl(theme_asset('images/600-600.png'))
        : theme_asset('images/600-600.png');
    $ariyaBundleUrl = route('front.bundles.show', ['bundleOffer' => $bundlePreview->id]);
@endphp
<div class="item">
    <div class="product-card ariya-product-card ariya-bundle-product-card" dir="rtl">
        <div class="product-head">
            <div class="row"><div class="btn-group" role="group">
                <div class="fild_products"><span>پک ویژه سبدی</span></div>
            </div></div>
        </div>

        <a class="product-thumb" href="{{ $ariyaBundleUrl }}" aria-label="مشاهده {{ $bundlePreview->title }}">
            <img data-src="{{ $ariyaBundlePhoto }}" src="{{ $ariyaBundlePhoto }}"
                 alt="{{ $bundlePreview->title }}" loading="lazy">
        </a>

        <div class="product-card-body">
            <h5 class="product-title">
                <a href="{{ $ariyaBundleUrl }}" title="{{ $bundlePreview->title }}">{{ $bundlePreview->title }}</a>
            </h5>
            <div class="rating-stars">
                <a class="product-meta" href="{{ $ariyaBundleUrl }}">
                    {{ number_format($bundlePreview->required_quantity) }} عدد • {{ number_format($bundlePreview->variants_count) }} مدل
                </a>
            </div>
            <div class="price-index-h">
                <div class="product-prices-div" style="display:flex;flex-direction:column;align-items:center;gap:3px">
                    @if($bundlePreview->regular_unit_price && $bundlePreview->bundle_unit_price && $bundlePreview->regular_unit_price > $bundlePreview->bundle_unit_price)
                        <del style="color:#8792a3;font-size:12px">{{ number_format($bundlePreview->regular_unit_price * $bundlePreview->required_quantity) }} تومان</del>
                        <span class="product-meta" style="color:#23845b">{{ number_format(($bundlePreview->regular_unit_price * $bundlePreview->required_quantity) - $bundlePreview->fixed_price) }} تومان تخفیف</span>
                    @endif
                    <span class="product-price">{{ number_format($bundlePreview->fixed_price) }} تومان</span>
                </div>
            </div>
            <div class="ariya-card-actions">
                <a class="ariya-card-action-button" href="{{ $ariyaBundleUrl }}">
                    <i class="mdi mdi-format-list-bulleted" aria-hidden="true"></i>
                    <span>مشاهده مدل‌ها</span>
                </a>
            </div>
        </div>
    </div>
</div>
@once
@push('styles')
<style>
/* The bundle card intentionally shares the SAME product-card layout as stock cards. */
.ariya-bundle-product-card{height:100%;display:flex;flex-direction:column}
.ariya-bundle-product-card .product-card-body{flex:1;display:flex;flex-direction:column}
.ariya-bundle-product-card .ariya-card-actions{margin-top:auto;padding-top:12px}
.ariya-bundle-product-card .ariya-card-action-button{
    display:flex;align-items:center;justify-content:center;gap:7px;
    width:100%;min-height:42px;border:0;border-radius:10px;
    background:#0879c9;color:#fff;padding:8px 12px;font-size:13px;font-weight:700
}
.ariya-bundle-product-card .ariya-card-action-button:hover{background:#066caf;color:#fff}
.ariya-bundle-product-card .ariya-card-action-button i{font-size:19px}
.ariya-bundle-product-card .product-title a{overflow-wrap:anywhere}

/* ARIYA_TASK02_3D13_BUNDLE_CARD_FIX_V1 */

/* Prevent a 100%-height card plus margins from clipping its footer. */
.ariya-bundle-product-card,
.ariya-home-main .product-carousel .item > .ariya-bundle-product-card {
    height: auto !important;
    min-height: 0 !important;
    margin: 0 !important;
    box-sizing: border-box !important;
    display: flex !important;
    flex-direction: column !important;
}

.ariya-bundle-product-card .product-head {
    flex: 0 0 auto;
}

.ariya-bundle-product-card .product-thumb {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    width: 100%;
    height: auto;
    max-height: 220px;
    aspect-ratio: 1 / 1;
    box-sizing: border-box;
    overflow: hidden;
}

.ariya-bundle-product-card .product-thumb > img {
    display: block;
    width: auto !important;
    height: auto !important;
    max-width: 88% !important;
    max-height: 88% !important;
    object-fit: contain !important;
    object-position: center;
}

.ariya-bundle-product-card .product-card-body {
    display: flex;
    flex: 1 1 auto;
    flex-direction: column;
    min-width: 0;
    height: auto !important;
    box-sizing: border-box;
    padding-bottom: 16px !important;
}

.ariya-home-main .product-carousel
.ariya-bundle-product-card .product-title {
    height: auto !important;
    max-height: none !important;
    overflow: visible !important;
}

.ariya-bundle-product-card .product-title a,
.ariya-bundle-product-card .product-price {
    overflow-wrap: anywhere;
}

.ariya-bundle-product-card .ariya-card-actions {
    position: relative !important;
    bottom: auto !important;
    flex: 0 0 auto;
    margin-top: auto !important;
    padding-top: 12px !important;
    padding-bottom: 4px !important;
}

.ariya-bundle-product-card .ariya-card-action-button {
    display: flex !important;
    align-items: center;
    justify-content: center;
    gap: 7px;
    width: 100%;
    max-width: 100%;
    min-height: 42px;
    height: auto !important;
    box-sizing: border-box;
    white-space: normal;
    text-align: center;
}

</style>
@endpush
@endonce
