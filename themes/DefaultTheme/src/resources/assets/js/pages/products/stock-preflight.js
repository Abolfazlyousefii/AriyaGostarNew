(function ($) {
    'use strict';
    var $notice = $('#stock-preflight-feedback');
    if (!$notice.length) return;
    var timer, request, revision = 0;

    function check() {
        var items = [];
        $('[data-model-card].is-selected').each(function () {
            var quantity = Number($(this).find('.aex-model-qty-input').val());
            if (quantity > 0) items.push({price_id: Number($(this).attr('data-price-id')), quantity: quantity});
        });
        if (!items.length) {
            var $button = $('.add-to-cart[data-price_id]').first();
            if ($button.length) items.push({price_id: Number($button.data('price_id')), quantity: Number($('#cart-quantity').val() || 1)});
        }
        if (!items.length) { $notice.addClass('d-none'); return; }
        var current = ++revision;
        if (request) request.abort();
        request = $.ajax({
            url: $notice.data('action'), type: 'GET', data: {items: items}, cache: false,
            success: function (data) {
                if (current !== revision) return;
                var valid = data.status === 'success';
                $notice.removeClass('d-none alert-danger alert-success alert-warning')
                    .addClass(valid ? 'alert-success' : 'alert-danger')
                    .text(valid ? 'موجودی این انتخاب اکنون در انبار بررسی شد. کالا پس از ثبت نهایی رزرو می‌شود.' : (data.errors || []).join('\n'));
            },
            error: function (_, status) {
                if (status === 'abort' || current !== revision) return;
                $notice.removeClass('d-none alert-danger alert-success').addClass('alert-warning')
                    .text('بررسی موجودی موقتاً ممکن نیست. پیش از افزودن کالا دوباره موجودی کنترل می‌شود.');
            }
        });
    }
    $(document).on('input change', '#cart-quantity, .aex-model-qty-input', function () {
        clearTimeout(timer); timer = setTimeout(check, 450);
    });
    $(document).on('click', '.aex-model-card-main, .aex-model-qty-plus, .aex-model-qty-minus', function () {
        clearTimeout(timer); timer = setTimeout(check, 450);
    });
    check();
})(jQuery);
