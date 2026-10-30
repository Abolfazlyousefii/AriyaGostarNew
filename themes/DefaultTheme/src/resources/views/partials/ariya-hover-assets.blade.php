{{-- ARIYA_PRODUCT_HOVER_SWAP_V1: no CSS build required --}}
<style id="ariya-product-hover-swap-v1">
    /* Disable the old diagonal white sweep on product cards. */
    .product-card .product-thumb::before,
    .product-card:hover .product-thumb::before {
        display: none !important;
        content: none !important;
        animation: none !important;
        box-shadow: none !important;
    }

    .product-card .ariya-hover-swap-thumb {
        position: relative;
        overflow: hidden;
        isolation: isolate;
    }

    .product-card .ariya-hover-swap-thumb > .ariya-hover-swap__primary {
        transition: opacity 240ms ease !important;
    }

    .product-card .ariya-hover-swap-thumb > .ariya-hover-swap__secondary {
        display: block !important;
        position: absolute !important;
        top: 0 !important;
        left: 10% !important;
        right: auto !important;
        bottom: 0 !important;
        margin: auto !important;
        width: 80% !important;
        max-width: 80% !important;
        height: 100% !important;
        object-fit: contain !important;
        opacity: 0 !important;
        pointer-events: none !important;
        transition: opacity 240ms ease !important;
        z-index: 1;
    }

    @media (hover: hover) and (pointer: fine) {
        .product-card .ariya-hover-swap-thumb.ariya-hover-swap--ready:hover > .ariya-hover-swap__primary {
            opacity: 0 !important;
        }
        .product-card .ariya-hover-swap-thumb.ariya-hover-swap--ready:hover > .ariya-hover-swap__secondary {
            opacity: 1 !important;
        }
    }
    @media (prefers-reduced-motion: reduce) {
        .product-card .ariya-hover-swap-thumb > img {
            transition: none !important;
        }
    }
</style>
<script id="ariya-product-hover-swap-script-v1">
(function () {
    function mark(img) {
        if (!img || !img.classList || !img.classList.contains('ariya-hover-swap__secondary')) return;
        var container = img.parentElement;
        if (container) container.classList.toggle('ariya-hover-swap--ready', img.complete && img.naturalWidth > 0);
    }
    // Capture image load/error, including images injected into carousels later.
    document.addEventListener('load', function (evt) { mark(evt.target); }, true);
    document.addEventListener('error', function (evt) { mark(evt.target); }, true);
    function init() {
        document.querySelectorAll('.ariya-hover-swap__secondary').forEach(mark);
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, {once: true});
    else init();
})();
</script>
