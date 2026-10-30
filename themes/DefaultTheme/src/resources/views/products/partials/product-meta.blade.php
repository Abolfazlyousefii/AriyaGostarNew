<meta property="og:locale" content="fa_IR" />
<meta property="og:title" content="{{ $product->meta_title ?: $product->title }}" />
<meta property="og:type" content="product" />
<meta property="og:url" content="{{ route('front.products.show', ['product' => $product]) }}" />
<meta property="og:description" content="{{ $product->meta_description ?: $product->short_description }}">
<meta name="keywords" content="{{ $product->getTags }}">
<meta name="product_id" content="{{ $product->id }}">
<link rel="canonical" href="{{ rtrim(config('app.url'), '/') . route('front.products.show', ['product' => $product], false) }}" />

<meta name="product_name" content="{{ $product->title }}">

@auth
<meta name="product_price" content="{{ $product->getLowestPrice(true, true) }}">
<meta name="product_old_price" content="{{ $product->getLowestDiscount(true, true) }}">
<meta name="price" content="{{ $product->getLowestPrice(true, true) }}">
@endauth

@php
    $primaryProductImage = $product->imageUrl('/no-image-product.png');
@endphp

<meta property="og:image" content="{{ $primaryProductImage }}">
<meta property="og:image:width" content="600" />
<meta property="og:image:height" content="600" />

@auth
    @if ($product->addableToCart())
        <meta property="product:availability" content="in stock">
        <meta property="product:price:amount" content="{{ $product->getLowestPrice(true, true) }}">
        <meta property="product:price:currency" content="IRR">
        <meta name="availability" content="https://schema.org/InStock">
    @else
        <meta property="product:availability" content="out of stock">
        <meta name="availability" content="https://schema.org/OutOfStock">
    @endif
@endauth

<script type="application/ld+json">
{
    "@context": "https://schema.org/",
    "@type": "Product",
    "name": "{{ $product->meta_title ?: $product->title }}",
    @if($product->title_en)
    "alternateName": "{{ $product->title_en }}",
    @endif

    @php
        // Emit one ordered image per gallery item, without duplicating the cover.
        $structuredImages = $product->gallery()
            ->orderByRaw('CASE WHEN ordering IS NULL THEN 1 ELSE 0 END')
            ->orderBy('ordering')
            ->orderBy('id')
            ->pluck('image')
            ->filter()
            ->values();

        if ($structuredImages->isEmpty() && $product->image && \App\Support\ImageStorage::exists($product->image)) {
            $structuredImages->push($product->image);
        }
    @endphp
    "image": {!! json_encode($structuredImages->map(fn ($img) => media_url($img))->all(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!},

    @if ($product->brand)
    "brand": {
        "@type": "Brand",
        "name": "{{ $product->brand->name }}"
    },
    @endif

    "description": {!! json_encode($product->meta_description ?: $product->short_description) !!}

    @auth
    ,
    "offers": {
        "@type": "Offer",
        "url": "{{ route('front.products.show', ['product' => $product]) }}",
        "priceCurrency": "IRR",
        "price": "{{ $product->getLowestPrice(true, true) }}",
        @if ($product->addableToCart())
        "availability": "https://schema.org/InStock"
        @else
        "availability": "https://schema.org/OutOfStock"
        @endif
    }
    @endauth
}
</script>
