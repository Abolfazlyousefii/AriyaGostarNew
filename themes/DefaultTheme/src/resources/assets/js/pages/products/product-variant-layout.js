(function ($) {
    'use strict';

    function formatNumber(value) {
        var number = Number(value || 0);

        try {
            return new Intl.NumberFormat('fa-IR').format(number);
        } catch (error) {
            return String(number).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }
    }

    function updateBulkSummary() {
        var selectedModels = 0;

        $('.aria-variant-quantity').each(function () {
            if (Number($(this).val() || 0) > 0) {
                selectedModels++;
            }
        });

        $('#aria-bulk-selected-count').text(formatNumber(selectedModels) + ' مدل');
        $('.aria-add-selected-variants').prop('disabled', selectedModels === 0);
    }

    function updateSelectedVariant(input) {
        var $input = $(input);
        var title = $input.data('title');
        var stock = Number($input.data('stock') || 0);
        var unit = $input.data('unit') || 'عدد';
        var salePrice = Number($input.data('sale-price') || 0);
        var regularPrice = Number($input.data('regular-price') || 0);
        var discount = Number($input.data('discount') || 0);
        var cartMin = Number($input.data('cart-min') || 1);
        var cartMax = Number($input.data('cart-max') || stock || 1);
        var image = $input.data('image') || '';
        var $stockSummary = $('#aria-variant-stock-summary');

        $('.aria-variant-row').removeClass('is-active');
        $input.closest('.aria-variant-row').addClass('is-active');

        $('#aria-current-model, #aria-purchase-model-title').text(title);
        $('#aria-current-stock').text(stock > 0 ? formatNumber(stock) + ' ' + unit : 'ناموجود');

        $stockSummary
            .toggleClass('is-available', stock > 0)
            .toggleClass('is-unavailable', stock <= 0);

        $stockSummary
            .find('.aria-stock-icon i')
            .toggleClass('mdi-package-variant-closed-check', stock > 0)
            .toggleClass('mdi-package-variant-closed-remove', stock <= 0);

        $('#aria-sale-price').text(formatNumber(salePrice));
        $('#aria-regular-price').text(formatNumber(regularPrice));
        $('#aria-discount-badge').text(discount + '%');
        $('#aria-regular-price-row').toggleClass('d-none', discount <= 0 || regularPrice <= salePrice);

        $('#cart-quantity')
            .attr('min', cartMin)
            .attr('max', cartMax)
            .val(cartMin);

        $('.aria-single-add-to-cart')
            .data('price_id', $input.val())
            .attr('data-price_id', $input.val())
            .data('image', image)
            .attr('data-image', image)
            .prop('disabled', stock <= 0);
    }

    $(document).on('change', '.aria-variant-radio', function () {
        updateSelectedVariant(this);
    });

    $(document).on('input', '#aria-variant-search', function () {
        var term = String($(this).val() || '').trim().toLocaleLowerCase('fa');

        $('.aria-variant-row').each(function () {
            var searchValue = String($(this).data('search') || '').toLocaleLowerCase('fa');
            $(this).toggle(searchValue.indexOf(term) !== -1);
        });
    });

    $(document).on('click', '.aria-variant-qty-plus, .aria-variant-qty-minus', function () {
        var $input = $(this).siblings('.aria-variant-quantity');
        var current = Number($input.val() || 0);
        var max = Number($input.attr('max') || 0);
        var next = $(this).hasClass('aria-variant-qty-plus') ? current + 1 : current - 1;

        next = Math.max(0, Math.min(max, next));
        $input.val(next).trigger('change');
    });

    $(document).on('input change', '.aria-variant-quantity', function () {
        var value = Number($(this).val() || 0);
        var max = Number($(this).attr('max') || 0);

        value = Math.max(0, Math.min(max, value));
        $(this).val(value);
        updateBulkSummary();
    });

    $(document).on('click', '#aria-cart-step-down', function () {
        var input = document.getElementById('cart-quantity');

        if (input) {
            input.stepDown();
        }
    });

    $(document).on('click', '#aria-cart-step-up', function () {
        var input = document.getElementById('cart-quantity');

        if (input) {
            input.stepUp();
        }
    });

    function addVariantToCart(action, priceId, quantity) {
        return $.ajax({
            type: 'POST',
            url: action,
            data: {
                quantity: quantity,
                price_id: priceId
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    }

    $(document).on('click', '.aria-add-selected-variants', function () {
        var button = this;
        var $button = $(button);
        var action = $button.data('action');
        var requests = [];
        var invalidRows = [];

        $('.aria-variant-row').each(function () {
            var $row = $(this);
            var $quantity = $row.find('.aria-variant-quantity');
            var quantity = Number($quantity.val() || 0);

            if (quantity <= 0) {
                return;
            }

            var minOrder = Number($quantity.data('min-order') || 1);

            if (quantity < minOrder) {
                invalidRows.push($row.find('.aria-variant-name').text() + ' (حداقل ' + minOrder + ')');
                return;
            }

            requests.push({
                priceId: $row.data('price-id'),
                quantity: quantity,
                input: $quantity
            });
        });

        if (invalidRows.length) {
            Swal.fire({
                type: 'error',
                title: 'تعداد نامعتبر',
                text: 'حداقل سفارش این مدل‌ها رعایت نشده است: ' + invalidRows.join('، '),
                confirmButtonText: 'باشه'
            });
            return;
        }

        if (!requests.length) {
            return;
        }

        block(button);

        var chain = $.Deferred().resolve();
        var lastCart = null;
        var errors = [];

        requests.forEach(function (item) {
            chain = chain.then(function () {
                return addVariantToCart(action, item.priceId, item.quantity)
                    .then(function (response) {
                        if (response.status === 'success') {
                            lastCart = response.cart;
                            item.input.val(0);
                        } else {
                            errors.push(response.message || 'خطا در افزودن یک مدل');
                        }
                    })
                    .fail(function (xhr) {
                        var message = xhr.responseJSON && xhr.responseJSON.message
                            ? xhr.responseJSON.message
                            : 'ارتباط با سرور ناموفق بود.';
                        errors.push(message);
                    });
            });
        });

        chain.always(function () {
            unblock(button);
            updateBulkSummary();

            if (lastCart) {
                $('#cart-list-item').replaceWith(lastCart);
            }

            if (errors.length) {
                Swal.fire({
                    type: 'warning',
                    title: 'بخشی از مدل‌ها اضافه نشد',
                    text: errors.join('، '),
                    confirmButtonText: 'باشه'
                });
                return;
            }

            Swal.fire({
                type: 'success',
                title: 'مدل‌ها به سبد اضافه شدند',
                text: 'تمام مدل‌های انتخاب‌شده با موفقیت به سبد خرید اضافه شدند.',
                confirmButtonText: 'باشه',
                footer: '<h5><a href="/cart">مشاهده سبد خرید</a></h5>'
            });
        });
    });

    $(function () {
        var selected = $('.aria-variant-radio:checked').first();

        if (selected.length) {
            updateSelectedVariant(selected[0]);
        }

        updateBulkSummary();
    });
})(jQuery);
