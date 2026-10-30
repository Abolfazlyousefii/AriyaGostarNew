{{-- ARIYA_PRODUCT_HOVER_SWAP_V1: shared primary/secondary product images --}}
@php
    $ariyaHoverPrimaryUrl = $product->imageUrl('/no-image-product.png');
    $ariyaHoverPrimaryPath = trim((string) $product->primaryImagePath());

    // Gallery order is the same as Product::primaryImagePath(): ordering, then id.
    // image is the leading photo; the first DIFFERENT gallery photo is the hover photo.
    $ariyaHoverSecondPath = null;
    if ($product->relationLoaded('gallery')) {
        $ariyaHoverGallery = $product->gallery->sortBy(function ($photo) {
            return sprintf('%020d-%020d', $photo->ordering ?? PHP_INT_MAX, $photo->id ?? PHP_INT_MAX);
        });
        foreach ($ariyaHoverGallery as $ariyaGalleryPhoto) {
            $candidate = trim((string) ($ariyaGalleryPhoto->image ?? ''));
            if ($candidate !== '' && ltrim(str_replace('\\', '/', $candidate), '/') !== ltrim(str_replace('\\', '/', $ariyaHoverPrimaryPath), '/')) {
                $ariyaHoverSecondPath = $candidate;
                break;
            }
        }
    } else {
        // One scalar gallery query per card, not a full gallery collection.
        $ariyaSecondQuery = $product->gallery()
            ->whereNotNull('image')
            ->where('image', '<>', '')
            ->orderByRaw('CASE WHEN ordering IS NULL THEN 1 ELSE 0 END')
            ->orderBy('ordering')
            ->orderBy('id');
        if ($ariyaHoverPrimaryPath !== '') {
            $ariyaSecondQuery->where('image', '<>', $ariyaHoverPrimaryPath);
        }
        $ariyaHoverSecondPath = $ariyaSecondQuery->value('image');
    }

    $ariyaHoverSecondUrl = $ariyaHoverSecondPath !== null
        ? media_url($ariyaHoverSecondPath)
        : '';
    $ariyaHoverIsEager = !empty($ariyaHoverPrimaryEager);
@endphp
<img
    class="ariya-hover-swap__primary{{ $ariyaHoverIsEager ? ' ariya-product-card__image' : '' }}"
    data-src="{{ $ariyaHoverPrimaryUrl }}"
    src="{{ $ariyaHoverIsEager ? $ariyaHoverPrimaryUrl : theme_asset('images/600-600.png') }}"
    alt="{{ $product->title }}"
    loading="lazy"
    decoding="async"
>
@if ($ariyaHoverSecondUrl !== '')
    <img
        class="ariya-hover-swap__secondary"
        src="{{ $ariyaHoverSecondUrl }}"
        alt=""
        aria-hidden="true"
        loading="lazy"
        decoding="async"
    >
@endif
