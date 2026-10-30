{{-- ARIYA_PRINT_BUNDLE_VIEW_BRIDGE_V1: reuse the canonical storefront bundle view for admin invoice printing. --}}
@include('front::partials.bundle-contents', [
    'bundleProduct' => $bundleProduct ?? null,
    'bundleQuantity' => $bundleQuantity ?? 1,
    'bundleSnapshot' => $bundleSnapshot ?? null,
])
