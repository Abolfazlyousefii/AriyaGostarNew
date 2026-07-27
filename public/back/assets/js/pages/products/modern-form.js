(function ($) {
    'use strict';

    $(document).on('click', '.apf-copy-code', function () {
        var code = String($(this).data('code') || '');
        if (!code) return;

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(code).then(function () {
                if (window.toastr) toastr.success('کد کپی شد.');
            });
            return;
        }

        var input = $('<input>').val(code).appendTo('body').select();
        document.execCommand('copy');
        input.remove();
        if (window.toastr) toastr.success('کد کپی شد.');
    });

    $(document).on('change', '.custom-file-input', function () {
        var fileName = this.files && this.files.length ? this.files[0].name : 'انتخاب فایل';
        $(this).next('.custom-file-label').text(fileName);
    });

    function syncSidebarHeight() {
        if (window.innerWidth < 992) return;
        $('.apf-sticky-sidebar').css('max-height', 'calc(100vh - 115px)');
        $('.apf-sticky-sidebar').css('overflow-y', 'auto');
        $('.apf-sticky-sidebar').css('padding-left', '4px');
    }

    syncSidebarHeight();
    $(window).on('resize', syncSidebarHeight);
})(jQuery);

(function ($) {
    'use strict';

    var fields = ['currency_id', 'rounding_amount', 'rounding_type'];
    var $priceTab = $('.product-prices-tab').first();

    if (!$priceTab.length) return;

    var $proxyWrap = $('<div class="prices-option-div apf-price-proxies d-none"></div>');
    $priceTab.prepend($proxyWrap);

    fields.forEach(function (fieldName) {
        var $visible = $('.apf-side-card select[name="' + fieldName + '"]').first();
        if (!$visible.length) return;

        var $proxy = $visible.clone(false)
            .removeAttr('id')
            .removeClass('select2-hidden-accessible')
            .attr('tabindex', '-1')
            .val($visible.val());

        $proxyWrap.append($proxy);
        $visible.removeAttr('name').attr('data-proxy-name', fieldName);

        $visible.on('change', function () {
            $proxy.val($(this).val()).trigger('change');

            if (fieldName === 'currency_id') {
                var unitTitle = $(this).find(':selected').data('title') || 'تومان';
                $('.single-price .amount-input').data('unit', unitTitle).trigger('keyup');
            } else {
                $('.single-price .price').trigger('keyup');
            }
        });
    });

    $('.single-price .price').trigger('keyup');
})(jQuery);

(function ($) {
    'use strict';

    function detectRowIndex($row, fallback) {
        var name = String($row.find('.price').attr('name') || '');
        var match = name.match(/prices\[(\d+)\]/);
        return match ? match[1] : fallback;
    }

    function normalizeExtendedPriceFields($row, fallbackIndex) {
        if (!$row || !$row.length) return;
        var index = detectRowIndex($row, fallbackIndex);
        $row.find('input[name="purchase_price"]').attr('name', 'prices[' + index + '][purchase_price]');
        $row.find('input[name="barcode"]').attr('name', 'prices[' + index + '][barcode]');
    }

    $('#product-prices-div .single-price').each(function (i) {
        normalizeExtendedPriceFields($(this), i + 1);
    });

    if (typeof window.addProductPrice === 'function') {
        var originalAddProductPrice = window.addProductPrice;

        window.addProductPrice = function (copy) {
            var $previous = $('#product-prices-div .single-price').last();
            var previousPurchasePrice = copy ? $previous.find('.purchase-price').val() : '';
            var previousBarcode = copy ? $previous.find('input[name$="[barcode]"]').val() : '';

            var result = originalAddProductPrice.apply(this, arguments);
            var $row = $('#product-prices-div .single-price').last();
            normalizeExtendedPriceFields($row, window.priceCount || $('#product-prices-div .single-price').length);

            if (copy) {
                $row.find('.purchase-price').val(previousPurchasePrice || '');
                $row.find('input[name$="[barcode]"]').val(previousBarcode || '');
            }

            return result;
        };
    }
})(jQuery);
