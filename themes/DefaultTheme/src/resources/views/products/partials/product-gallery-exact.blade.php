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

{{-- ARIYA_PRODUCT_GALLERY_FANCYBOX_ARROWS_V2 --}}
@push('scripts')
<script>
// ARIYA_PRODUCT_GALLERY_FANCYBOX_ARROWS_V2
// Run only for the dedicated product gallery's large-image link.
// fancyBox v3 already exists on product pages; no other library is added.
(function () {
    'use strict';

    document.addEventListener('click', function (event) {
        if (event.defaultPrevented || event.button !== 0 ||
                event.ctrlKey || event.metaKey || event.shiftKey || event.altKey ||
                !event.target || typeof event.target.closest !== 'function') {
            return;
        }

        var trigger = event.target.closest('#aex-gallery-main-link');
        if (!trigger) {
            return;
        }

        var gallery = trigger.closest('#product-gallery');
        if (!gallery) {
            return;
        }

        var $ = window.jQuery;
        if (!$ || !$.fancybox || typeof $.fancybox.open !== 'function') {
            // Preserve the existing single-image behavior when fancyBox is unavailable.
            return;
        }

        // Read the current DOM on every click: selected colors or product variants
        // can change the set of images after page load.
        var thumbs = gallery.querySelectorAll('.aex-gallery-thumb[data-image]');
        var items = [];
        var indexes = Object.create(null);
        var selectedImage = null;
        var active = gallery.querySelector('.aex-gallery-thumb.is-active[data-image]');
        if (active) {
            selectedImage = active.getAttribute('data-image');
        }
        if (!selectedImage) {
            selectedImage = trigger.getAttribute('href');
        }

        for (var i = 0; i < thumbs.length; i++) {
            var src = (thumbs[i].getAttribute('data-image') || '').trim();
            if (!src) {
                continue;
            }
            // Avoid duplicate images (e.g., featured image also present in gallery).
            if (Object.prototype.hasOwnProperty.call(indexes, src)) {
                continue;
            }
            indexes[src] = items.length;
            items.push({ src: src, type: 'image' });
        }

        if (items.length < 2) {
            // Native Fancybox behavior remains unchanged for single-image products.
            return;
        }

        var startIndex = Object.prototype.hasOwnProperty.call(indexes, selectedImage)
            ? indexes[selectedImage]
            : 0;

        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();

        // The real multi-image gallery enables fancyBox's existing next/prev
        // arrow controls, keyboard keys and mobile swipes.
        $.fancybox.open(items, {
            arrows: true,
            loop: true,
            keyboard: true,
            touch: true,
            infobar: true,
            hash: false,
            clickContent: false
        }, startIndex);
    }, true);
})();
</script>
@endpush
