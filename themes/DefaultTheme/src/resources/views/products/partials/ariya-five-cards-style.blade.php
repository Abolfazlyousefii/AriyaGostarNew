{{-- ARIYA_FIVE_CARDS_GRID_V1 | Only product archives, not home sliders --}}
<style id="ariya-five-cards-grid-v1">
@media (min-width: 1280px) {
    /* Use five equal columns inside the existing catalog results area. */
    .ariya-products-page .ariya-catalog-products-box .ariya-products-grid {
        display: grid !important;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 12px 9px;
        align-items: stretch;
        padding: 12px 9px 8px;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }

    /* Override the Bootstrap col-xl-3 (25%) without touching other grids. */
    .ariya-products-page .ariya-catalog-products-box .ariya-products-grid > .category-product-div {
        display: flex;
        flex: none !important;
        width: auto !important;
        max-width: none !important;
        min-width: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .ariya-products-page .ariya-catalog-products-box .ariya-products-grid .ariya-product-card {
        display: flex;
        flex: 1 1 auto;
        flex-direction: column;
        min-width: 0;
        width: 100%;
        height: 100%;
        margin: 0 !important;
        padding: 8px 0 11px !important;
        overflow: hidden;
    }

    .ariya-products-page .ariya-catalog-products-box .ariya-products-grid .product-head {
        flex: 0 0 auto;
        min-height: 28px;
        padding: 0 8px 3px;
    }

    .ariya-products-page .ariya-catalog-products-box .ariya-products-grid .product-thumb {
        display: flex !important;
        flex: 0 0 172px;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 172px !important;
        min-height: 0;
        margin: 0;
        padding: 3px 0 !important;
        position: relative;
        overflow: hidden;
    }

    .ariya-products-page .ariya-catalog-products-box .ariya-products-grid .product-thumb > .ariya-hover-swap__primary,
    .ariya-products-page .ariya-catalog-products-box .ariya-products-grid .product-thumb > .ariya-product-card__image,
    .ariya-products-page .ariya-catalog-products-box .ariya-products-grid .product-thumb > img:not(.ariya-hover-swap__secondary) {
        display: block;
        width: auto;
        max-width: 86%;
        height: auto;
        max-height: 100%;
        margin: auto;
        object-fit: contain;
    }

    .ariya-products-page .ariya-catalog-products-box .ariya-products-grid .product-card-body {
        display: flex;
        flex: 1 1 auto;
        flex-direction: column;
        min-width: 0;
        padding: 0 9px;
    }

    .ariya-products-page .ariya-catalog-products-box .ariya-products-grid .product-title {
        height: 45px;
        min-height: 45px;
        margin: 7px 0 4px;
        font-size: 11.5px;
        line-height: 1.7;
        overflow: hidden;
    }

    .ariya-products-page .ariya-catalog-products-box .ariya-products-grid .product-title > a {
        font-size: inherit;
        line-height: 1.7;
        overflow-wrap: anywhere;
    }

    .ariya-products-page .ariya-catalog-products-box .ariya-products-grid .product-meta {
        display: block;
        min-width: 0;
        min-height: 20px;
        margin-bottom: 4px;
        font-size: 10px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ariya-products-page .ariya-catalog-products-box .ariya-products-grid .product-prices-div {
        min-width: 0;
        margin-top: auto;
        font-size: 11px;
    }

    .ariya-products-page .ariya-catalog-products-box .ariya-products-grid .ariya-card-actions {
        margin-top: 12px;
        min-width: 0;
    }

    .ariya-products-page .ariya-catalog-products-box .ariya-products-grid .ariya-card-action-button {
        width: 100%;
        max-width: 100%;
        min-height: 41px;
        padding: 6px 4px;
        font-size: 10.5px;
        line-height: 1.4;
        white-space: normal;
    }
}
@media (min-width: 1280px) and (max-width: 1439.98px) {
    .ariya-products-page .ariya-catalog-products-box .ariya-products-grid .product-thumb {
        flex-basis: 148px;
        height: 148px !important;
    }
    .ariya-products-page .ariya-catalog-products-box .ariya-products-grid .ariya-card-action-button {
        font-size: 10px;
    }
}
</style>
