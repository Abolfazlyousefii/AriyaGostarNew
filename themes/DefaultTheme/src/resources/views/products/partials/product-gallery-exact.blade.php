@php
    $aexGalleryItems = $product->gallery()->orderBy('ordering')->get();
@endphp

@if ($aexGalleryItems->count())
    <div id="product-gallery" class="aex-product-gallery" data-gallery-count="{{ $aexGalleryItems->count() }}">
        <div class="aex-gallery-shell">
            <div class="aex-gallery-thumbs" aria-label="تصاویر محصول">
                @foreach ($aexGalleryItems as $item)
                    <button
                        type="button"
                        class="aex-gallery-thumb {{ $loop->first ? 'is-active' : '' }}"
                        data-image="{{ asset($item->image) }}"
                        data-index="{{ $loop->index }}"
                        aria-label="نمایش تصویر {{ $loop->iteration }} از {{ $product->title }}"
                        aria-pressed="{{ $loop->first ? 'true' : 'false' }}"
                    >
                        <img
                            src="{{ asset($item->image) }}"
                            alt="{{ $product->title }} - تصویر {{ $loop->iteration }}"
                            loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                        >
                    </button>
                @endforeach
            </div>

            <div class="aex-gallery-main">
                <a
                    id="aex-gallery-main-link"
                    class="aex-gallery-main-link"
                    href="{{ asset($aexGalleryItems->first()->image) }}"
                    data-fancybox="product-gallery"
                    aria-label="مشاهده تصویر بزرگ {{ $product->title }}"
                >
                    <img
                        id="aex-gallery-main-image"
                        src="{{ asset($aexGalleryItems->first()->image) }}"
                        alt="{{ $product->title }}"
                    >
                </a>

                @if ($aexGalleryItems->count() > 1)
                    <div class="aex-gallery-dots" aria-label="انتخاب تصویر محصول">
                        @foreach ($aexGalleryItems as $item)
                            <button
                                type="button"
                                class="aex-gallery-dot {{ $loop->first ? 'is-active' : '' }}"
                                data-index="{{ $loop->index }}"
                                aria-label="تصویر {{ $loop->iteration }}"
                            ></button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@else
    <div class="aex-product-gallery aex-product-gallery--empty">
        <img src="{{ asset('/no-image-product.png') }}" alt="{{ $product->title }}">
    </div>
@endif
