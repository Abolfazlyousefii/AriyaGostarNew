@push('styles')
<style>
.ariya-product-card{
    height:100%;
    display:flex;
    flex-direction:column;
}
.ariya-product-card .product-card-body{
    flex:1;
    display:flex;
    flex-direction:column;
}
.ariya-card-actions{
    margin-top:auto;
    padding-top:12px;
}
.ariya-card-action-button{
    width:100%;
    min-height:42px;
    border:0;
    border-radius:10px;
    background:#0879c9;
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:7px;
    cursor:pointer;
    font-family:inherit;
    font-size:13px;
    font-weight:700;
    transition:.2s ease;
    padding:8px 12px;
}
.ariya-card-action-button:hover{
    background:#066caf;
    color:#fff;
    transform:translateY(-1px);
}
.ariya-card-action-button i{
    font-size:19px;
}
.ariya-card-action-button.is-disabled{
    background:#e9ecef;
    color:#9099a4;
    cursor:not-allowed;
    transform:none;
}
#ariya-quick-variants-modal .modal-dialog{
    max-width:900px;
}
#ariya-quick-variants-modal .modal-content{
    border:0;
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 18px 55px rgba(0,0,0,.18);
}
#ariya-quick-variants-modal .modal-header{
    border-bottom:1px solid #edf0f4;
    padding:16px 20px;
}
#ariya-quick-variants-modal .modal-title{
    font-weight:800;
    font-size:17px;
}
#ariya-quick-variants-modal .modal-body{
    padding:0;
}
.ariya-quick-loading{
    min-height:260px;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    gap:12px;
    color:#6f7883;
}
.ariya-quick-loading i{
    font-size:34px;
}
.ariya-quick-variants{
    padding:18px;
}
.ariya-quick-variants__top{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:15px;
    margin-bottom:15px;
}
.ariya-quick-variants__top h4{
    margin:0 0 6px;
    font-size:17px;
    font-weight:800;
}
.ariya-quick-variants__top p{
    margin:0;
    color:#7b8490;
    font-size:12px;
}
.ariya-quick-variants__count{
    white-space:nowrap;
    background:#edf6ff;
    color:#0879c9;
    border-radius:20px;
    padding:6px 11px;
    font-size:12px;
    font-weight:700;
}
.ariya-quick-variants__search{
    position:relative;
    margin-bottom:14px;
}
.ariya-quick-variants__search i{
    position:absolute;
    right:13px;
    top:50%;
    transform:translateY(-50%);
    color:#8c95a1;
    font-size:20px;
}
.ariya-quick-variants__search input{
    width:100%;
    height:44px;
    border:1px solid #e2e7ed;
    border-radius:11px;
    padding:0 42px 0 12px;
    font-family:inherit;
    outline:none;
}
.ariya-quick-variants__search input:focus{
    border-color:#0879c9;
}
.ariya-quick-variants__list{
    max-height:420px;
    overflow:auto;
    padding-left:4px;
}
.ariya-quick-variant-row{
    display:grid;
    grid-template-columns:minmax(0,1fr) 160px 138px;
    align-items:center;
    gap:14px;
    padding:13px 14px;
    border:1px solid #e7ebef;
    border-radius:12px;
    margin-bottom:9px;
    background:#fff;
}
.ariya-quick-variant-row.is-unavailable{
    opacity:.55;
    background:#f7f8f9;
}
.ariya-quick-variant-row__info{
    min-width:0;
}
.ariya-quick-variant-row__info strong{
    display:block;
    color:#252a30;
    font-size:13px;
    line-height:1.9;
}
.ariya-quick-variant-row__info small{
    display:block;
    color:#7d8791;
    margin-top:3px;
}
.ariya-quick-variant-row__price{
    text-align:center;
}
.ariya-quick-variant-row__price del{
    display:block;
    color:#a6adb5;
    font-size:11px;
}
.ariya-quick-variant-row__price strong{
    font-size:14px;
    color:#20252a;
}
.ariya-quick-variant-row__price span{
    font-size:10px;
    margin-right:3px;
}
.ariya-quick-variant-row__quantity{
    display:flex;
    align-items:center;
    justify-content:center;
    direction:ltr;
}
.ariya-quick-variant-row__quantity button{
    width:34px;
    height:34px;
    border:1px solid #dce2e8;
    background:#f6f8fa;
    font-size:19px;
    line-height:1;
    cursor:pointer;
}
.ariya-quick-variant-row__quantity button:first-child{
    border-radius:8px 0 0 8px;
}
.ariya-quick-variant-row__quantity button:last-child{
    border-radius:0 8px 8px 0;
}
.ariya-quick-variant-row__quantity input{
    width:54px;
    height:34px;
    border-top:1px solid #dce2e8;
    border-bottom:1px solid #dce2e8;
    border-left:0;
    border-right:0;
    text-align:center;
    outline:none;
}
.ariya-quick-variants__empty{
    padding:30px 15px;
    text-align:center;
    color:#7b8490;
}
.ariya-quick-variants__footer{
    border-top:1px solid #edf0f4;
    margin-top:15px;
    padding-top:15px;
}
.ariya-quick-variants__summary{
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-bottom:13px;
    font-size:12px;
}
.ariya-quick-variants__summary strong{
    font-size:13px;
}
.ariya-quick-variants__summary b{
    color:#0879c9;
}
.ariya-quick-variants__footer-actions{
    display:flex;
    gap:10px;
}
.ariya-quick-product-link,
.ariya-quick-add-selected{
    min-height:44px;
    border-radius:10px;
    padding:9px 14px;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:6px;
    font-family:inherit;
    font-size:12px;
    font-weight:700;
}
.ariya-quick-product-link{
    border:1px solid #0879c9;
    color:#0879c9;
    background:#fff;
    flex:0 0 180px;
}
.ariya-quick-add-selected{
    border:0;
    color:#fff;
    background:#0879c9;
    flex:1;
    cursor:pointer;
}
.ariya-quick-add-selected:disabled{
    background:#c9d0d7;
    cursor:not-allowed;
}
@media(max-width:767px){
    #ariya-quick-variants-modal .modal-dialog{
        margin:10px;
    }
    .ariya-quick-variants{
        padding:13px;
    }
    .ariya-quick-variant-row{
        grid-template-columns:1fr auto;
    }
    .ariya-quick-variant-row__price{
        text-align:left;
    }
    .ariya-quick-variant-row__quantity{
        grid-column:1 / -1;
        justify-content:flex-start;
    }
    .ariya-quick-variants__footer-actions{
        flex-direction:column-reverse;
    }
    .ariya-quick-product-link{
        flex:auto;
    }
}
</style>
@endpush

@push('scripts')
<div
    class="modal fade"
    id="ariya-quick-variants-modal"
    tabindex="-1"
    role="dialog"
    aria-hidden="true"
    data-cart-url="{{ route('front.cart') }}"
>
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">انتخاب مدل</h5>

                <button
                    type="button"
                    class="close"
                    data-dismiss="modal"
                    aria-label="بستن"
                >
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body" data-quick-modal-content>
                <div class="ariya-quick-loading">
                    <i class="mdi mdi-loading mdi-spin"></i>
                    <span>در حال دریافت مدل‌ها...</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function ($) {
    'use strict';

    if (!$) {
        return;
    }

    var $modal = $('#ariya-quick-variants-modal');

    function csrfToken() {
        return $('meta[name="csrf-token"]').attr('content');
    }

    function cartUrl() {
        return $modal.data('cart-url') || '/cart';
    }

    function errorMessage(message) {
        Swal.fire({
            type: 'error',
            title: 'خطا',
            text: message || 'خطایی رخ داده است.',
            confirmButtonText: 'باشه'
        });
    }

    function successMessage(title, text) {
        Swal.fire({
            type: 'success',
            title: title,
            text: text,
            confirmButtonText: 'باشه',
            footer: '<h5><a href="' + cartUrl() + '">مشاهده سبد خرید</a></h5>'
        });
    }

    function refreshCart(data) {
        if (data && data.cart && $('#cart-list-item').length) {
            $('#cart-list-item').replaceWith(data.cart);
        }
    }

    $(document).on('click', '.ariya-card-add-to-cart', function () {
        var $button = $(this);

        if ($button.prop('disabled')) {
            return;
        }

        $button.prop('disabled', true);

        $.ajax({
            type: 'POST',
            url: $button.data('action'),
            data: {
                quantity: Number($button.data('quantity')) || 1,
                price_id: $button.data('price-id')
            },
            headers: {
                'X-CSRF-TOKEN': csrfToken()
            },
            success: function (data) {
                if (data.status !== 'success') {
                    errorMessage(data.message);
                    return;
                }

                refreshCart(data);

                successMessage(
                    'به سبد خرید اضافه شد',
                    'محصول با موفقیت به سبد خرید شما اضافه شد.'
                );
            },
            error: function (xhr) {
                var message =
                    xhr.responseJSON && xhr.responseJSON.message
                        ? xhr.responseJSON.message
                        : 'امکان افزودن محصول به سبد خرید وجود ندارد.';

                errorMessage(message);
            },
            complete: function () {
                $button.prop('disabled', false);
            }
        });
    });

    $(document).on('click', '.ariya-card-open-variants', function () {
        var $button = $(this);
        var title = $button.data('title') || 'انتخاب مدل';

        $modal.find('.modal-title').text(title);

        $modal.find('[data-quick-modal-content]').html(
            '<div class="ariya-quick-loading">' +
                '<i class="mdi mdi-loading mdi-spin"></i>' +
                '<span>در حال دریافت مدل‌ها...</span>' +
            '</div>'
        );

        $modal.modal('show');

        $.ajax({
            type: 'GET',
            url: $button.data('action'),
            success: function (html) {
                if (String(html).indexOf('ariya-quick-variants') === -1) {
                    $modal.modal('hide');
                    errorMessage('اطلاعات مدل‌های محصول دریافت نشد.');
                    return;
                }

                $modal.find('[data-quick-modal-content]').html(html);
            },
            error: function () {
                $modal.modal('hide');
                errorMessage('امکان دریافت مدل‌های محصول وجود ندارد.');
            }
        });
    });

    function updateSummary($container) {
        var models = 0;
        var quantity = 0;

        $container
            .find('.ariya-quick-variant-row:not(.is-unavailable)')
            .each(function () {
                var value =
                    Number($(this).find('[data-quick-quantity]').val()) || 0;

                if (value > 0) {
                    models++;
                    quantity += value;
                }
            });

        $container.find('[data-quick-model-count]').text(models);
        $container.find('[data-quick-total-quantity]').text(quantity);
        $container
            .find('[data-quick-add-selected]')
            .prop('disabled', models === 0);
    }

    function normalizeQuantity($input) {
        var $row = $input.closest('.ariya-quick-variant-row');
        var min = Number($row.data('min')) || 1;
        var max = Number($row.data('max')) || 0;
        var value = Number($input.val()) || 0;

        value = Math.max(0, value);

        if (value > 0 && value < min) {
            value = min;
        }

        if (max > 0 && value > max) {
            value = max;
        }

        $input.val(value);
        updateSummary($row.closest('.ariya-quick-variants'));
    }

    $(document).on('click', '[data-quick-plus]', function () {
        var $row = $(this).closest('.ariya-quick-variant-row');
        var $input = $row.find('[data-quick-quantity]');
        var current = Number($input.val()) || 0;
        var min = Number($row.data('min')) || 1;
        var max = Number($row.data('max')) || 0;
        var next = current === 0 ? min : current + 1;

        if (max > 0) {
            next = Math.min(next, max);
        }

        $input.val(next);
        updateSummary($row.closest('.ariya-quick-variants'));
    });

    $(document).on('click', '[data-quick-minus]', function () {
        var $row = $(this).closest('.ariya-quick-variant-row');
        var $input = $row.find('[data-quick-quantity]');
        var current = Number($input.val()) || 0;
        var min = Number($row.data('min')) || 1;
        var next = current <= min ? 0 : current - 1;

        $input.val(next);
        updateSummary($row.closest('.ariya-quick-variants'));
    });

    $(document).on('change input', '[data-quick-quantity]', function () {
        normalizeQuantity($(this));
    });

    $(document).on('input', '[data-quick-variant-search]', function () {
        var query = String($(this).val() || '').trim().toLowerCase();
        var $container = $(this).closest('.ariya-quick-variants');
        var visible = 0;

        $container.find('.ariya-quick-variant-row').each(function () {
            var haystack = String($(this).data('search') || '').toLowerCase();
            var matches = !query || haystack.indexOf(query) !== -1;

            $(this).toggle(matches);

            if (matches) {
                visible++;
            }
        });

        $container
            .find('[data-quick-empty]')
            .toggleClass('d-none', visible > 0);
    });

    $(document).on('click', '[data-quick-add-selected]', function () {
        var $button = $(this);
        var $container = $button.closest('.ariya-quick-variants');
        var items = [];

        $container
            .find('.ariya-quick-variant-row:not(.is-unavailable)')
            .each(function () {
                var quantity =
                    Number($(this).find('[data-quick-quantity]').val()) || 0;

                if (quantity <= 0) {
                    return;
                }

                items.push({
                    price_id: Number($(this).data('price-id')),
                    quantity: quantity
                });
            });

        if (!items.length) {
            return;
        }

        $button.prop('disabled', true);

        $.ajax({
            type: 'POST',
            url: $container.data('store-action'),
            data: {
                items: items
            },
            headers: {
                'X-CSRF-TOKEN': csrfToken()
            },
            success: function (data) {
                if (data.status !== 'success') {
                    errorMessage(data.message);
                    return;
                }

                refreshCart(data);
                $modal.modal('hide');

                successMessage(
                    'مدل‌ها اضافه شدند',
                    'مدل‌های انتخاب‌شده با تعداد موردنظر به سبد خرید اضافه شدند.'
                );
            },
            error: function (xhr) {
                var message =
                    xhr.responseJSON && xhr.responseJSON.message
                        ? xhr.responseJSON.message
                        : 'امکان افزودن مدل‌ها به سبد خرید وجود ندارد.';

                errorMessage(message);
            },
            complete: function () {
                $button.prop('disabled', false);
            }
        });
    });
})(window.jQuery);
</script>
@endpush