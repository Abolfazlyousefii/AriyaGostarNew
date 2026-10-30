{{-- ARIYA_DESKTOP_SORT_TABS_V1 | Desktop only. Existing mobile select remains intact. --}}
<style id="ariya-desktop-sort-tabs-v1">
@media (min-width: 992px) {
    .ariya-products-page .ariya-catalog-results__head {
        flex-wrap: wrap;
        row-gap: 12px;
    }
    .ariya-products-page .ariya-catalog-sort-tabs {
        display: flex;
        flex: 1 1 450px;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 9px;
        min-width: 0;
        margin: 0;
        direction: rtl;
        font-size: 11.5px;
    }
    .ariya-products-page .ariya-catalog-sort-tabs__label {
        color: #687386;
        font-weight: 600;
        white-space: nowrap;
    }
    .ariya-products-page .ariya-catalog-sort-tabs__options {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
        min-width: 0;
    }
    .ariya-products-page .ariya-catalog-sort-tabs__option {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 34px;
        padding: 6px 11px;
        border: 1px solid #e6ebf3;
        border-radius: 9px;
        background: #f8fafd;
        color: #344054;
        font: inherit;
        font-weight: 500;
        white-space: nowrap;
        text-decoration: none;
        transition: background-color .16s ease, border-color .16s ease, color .16s ease;
    }
    .ariya-products-page .ariya-catalog-sort-tabs__option:hover,
    .ariya-products-page .ariya-catalog-sort-tabs__option:focus-visible {
        color: #1268b5;
        background: #eef6ff;
        border-color: #86b9ed;
        text-decoration: none;
    }
    .ariya-products-page .ariya-catalog-sort-tabs__option.is-active {
        color: #fff;
        background: #087ac7;
        border-color: #087ac7;
        font-weight: 700;
    }
    .ariya-products-page .ariya-catalog-sort-tabs__option.is-active:hover,
    .ariya-products-page .ariya-catalog-sort-tabs__option.is-active:focus-visible {
        color: #fff;
        background: #0668ad;
        border-color: #0668ad;
    }
}
@media (max-width: 991.98px) {
    .ariya-products-page .ariya-catalog-sort-tabs { display: none !important; }
}
</style>
