@php
    // ARIYA_GALLERY_PRIMARY_V1: Do not prepend the standalone image field.
    // Gallery ordering is authoritative; keep an image-only legacy fallback.
    $aexGalleryItems = $product->gallery()
        ->orderByRaw('CASE WHEN ordering IS NULL THEN 1 ELSE 0 END')
        ->orderBy('ordering')
        ->orderBy('id')
        ->get();

    if ($aexGalleryItems->isEmpty() && $product->image && \App\Support\ImageStorage::exists($product->image)) {
        $aexGalleryItems = collect([(object) ['image' => $product->image]]);
    }
@endphp

@if ($aexGalleryItems->count())
	<div id="product-gallery" class="aex-product-gallery" data-gallery-count="{{ $aexGalleryItems->count() }}">
		<div class="aex-gallery-shell">
			<div class="aex-gallery-thumbs" aria-label="تصاویر محصول">
				@foreach ($aexGalleryItems as $item)
					<button type="button" class="aex-gallery-thumb {{ $loop->first ? 'is-active' : '' }}" data-image="{{ media_url($item->image, '/no-image-product.png') }}" data-index="{{ $loop->index }}" aria-label="نمایش تصویر {{ $loop->iteration }} از {{ $product->title }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}">
						<img src="{{ media_url($item->image, '/no-image-product.png') }}" alt="{{ $product->title }} - تصویر {{ $loop->iteration }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}">
					</button>
				@endforeach
			</div>

			<div class="aex-gallery-main">
				<a id="aex-gallery-main-link" class="aex-gallery-main-link" href="{{ media_url($aexGalleryItems->first()->image, '/no-image-product.png') }}" data-fancybox="product-gallery" aria-label="مشاهده تصویر بزرگ {{ $product->title }}">
					<img id="aex-gallery-main-image" src="{{ media_url($aexGalleryItems->first()->image, '/no-image-product.png') }}" alt="{{ $product->title }}">
				</a>

				@if ($aexGalleryItems->count() > 1)
					<div class="aex-gallery-dots" aria-label="انتخاب تصویر محصول">
						@foreach ($aexGalleryItems as $item)
							<button type="button" class="aex-gallery-dot {{ $loop->first ? 'is-active' : '' }}" data-index="{{ $loop->index }}" aria-label="تصویر {{ $loop->iteration }}"></button>
						@endforeach
					</div>
				@endif
			</div>
		</div>
	</div>
@else
	<div class="aex-product-gallery aex-product-gallery--empty">
		<img src="{{ $product->imageUrl('/no-image-product.png') }}" alt="{{ $product->title }}">
	</div>
@endif
