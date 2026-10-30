(function ($) {
    'use strict';

    function formatNumber(value) {
        var number = Number(value || 0);

        try {
            return number.toLocaleString('fa-IR');
        } catch (error) {
            return String(number).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }
    }

    function selectGalleryItem(index) {
        var $gallery = $('#product-gallery');
        var $thumb = $gallery.find('.aex-gallery-thumb[data-index="' + index + '"]');

        if (!$thumb.length) {
            return;
        }

        var image = $thumb.data('image');

        $gallery.find('.aex-gallery-thumb')
            .removeClass('is-active')
            .attr('aria-pressed', 'false');
        $thumb.addClass('is-active').attr('aria-pressed', 'true');

        $gallery.find('.aex-gallery-dot').removeClass('is-active');
        $gallery.find('.aex-gallery-dot[data-index="' + index + '"]').addClass('is-active');

        $('#aex-gallery-main-image').attr('src', image);
        $('#aex-gallery-main-link').attr('href', image);
    }

    function clampQuantity($input) {
        var min = Number($input.attr('min') || 1);
        var max = Number($input.attr('max') || min);
        var value = Number($input.val() || min);

        if (!Number.isFinite(value)) {
            value = min;
        }

        value = Math.max(min, Math.min(max, Math.floor(value)));
        $input.val(value);

        return value;
    }

    function getSelectedModelItems() {
        var items = [];

        $('[data-model-card].is-selected').each(function () {
            var $card = $(this);
            var quantity = clampQuantity($card.find('.aex-model-qty-input'));

            items.push({
                priceId: String($card.data('price-id')),
                price: Number($card.data('price') || 0),
                stock: Number($card.data('stock') || 0),
                name: String($card.data('name') || ''),
                quantity: quantity,
                $card: $card
            });
        });

        return items;
    }

    function updateSelectionState() {
        var items = getSelectedModelItems();
        var count = items.length;
        var totalQuantity = 0;
        var totalPrice = 0;
        var names = [];

        items.forEach(function (item) {
            totalQuantity += item.quantity;
            totalPrice += item.price * item.quantity;
            names.push(item.name);
        });

        var summary = 'هنوز مدلی انتخاب نشده است';

        if (count === 1) {
            summary = names[0] + '، تعداد ' + formatNumber(totalQuantity);
        } else if (count > 1) {
            summary = formatNumber(count) + ' مدل، مجموع ' + formatNumber(totalQuantity) + ' عدد';
        }

        $('#aex-selected-models-summary').text(summary);
        $('#aex-modal-selected-count').text(formatNumber(count) + ' مدل انتخاب شده');
        $('#aex-modal-total-price').text(formatNumber(totalPrice));

        var $buyCard = $('.aex-buy-card--price-only');
        var defaultPrice = Number($buyCard.data('default-price') || 0);
        $('#aex-main-price-value').text(formatNumber(count > 0 ? totalPrice : defaultPrice));

        $('.aex-add-selected-to-cart').prop('disabled', count === 0);
        $('.aex-apply-model-selection').prop('disabled', count === 0);

        $('#aex-buy-card-hint').text(
            count > 0
                ? formatNumber(count) + ' مدل برای افزودن به سبد آماده است.'
                : 'ابتدا مدل‌های موردنظر را از لیست انتخاب کنید.'
        );
    }

    function setModelSelected($card, selected) {
        if ($card.hasClass('is-unavailable')) {
            return;
        }

        var $main = $card.find('.aex-model-card-main');
        var $quantity = $card.find('.aex-model-quantity');
        var $input = $card.find('.aex-model-qty-input');

        $card.toggleClass('is-selected', selected);
        $main.attr('aria-pressed', selected ? 'true' : 'false');
        $quantity.prop('hidden', !selected);

        if (selected) {
            clampQuantity($input);
            window.setTimeout(function () {
                $input.trigger('focus').select();
            }, 60);
        }

        updateSelectionState();
    }

    function clearModelSelections() {
        $('[data-model-card].is-selected').each(function () {
            var $card = $(this);
            $card.removeClass('is-selected');
            $card.find('.aex-model-card-main').attr('aria-pressed', 'false');
            $card.find('.aex-model-quantity').prop('hidden', true);
            $card.find('.aex-model-qty-input').val($card.data('min') || 1);
        });

        updateSelectionState();
    }

    function filterModelCards($modal, value) {
        var query = $.trim(String(value || '')).toLowerCase();
        var visibleCount = 0;

        $modal.find('[data-model-card]').each(function () {
            var $card = $(this);
            var searchable = String($card.data('search') || '').toLowerCase();
            var visible = query === '' || searchable.indexOf(query) !== -1;

            $card.toggle(visible);

            if (visible) {
                visibleCount++;
            }
        });

        $modal.find('.aex-variant-search-empty').prop('hidden', visibleCount > 0);
    }

    function showCartMessage(type, title, text) {
        if (typeof Swal !== 'undefined' && Swal.fire) {
            Swal.fire({
                type: type,
                title: title,
                text: text,
                confirmButtonText: 'باشه',
                footer: '<h5><a href="/cart">مشاهده سبد خرید</a></h5>'
            });
            return;
        }

        alert(text);
    }

    function addSelectedItemsToCart($button) {
        var items = getSelectedModelItems();

        if (!items.length) {
            showCartMessage('error', 'مدلی انتخاب نشده', 'ابتدا مدل‌های موردنظر را انتخاب کنید.');
            return;
        }

        var action = $button.data('action');
        var originalText = $button.text();
        var lastCartHtml = null;
        var failedItems = [];
        var index = 0;

        $button
            .prop('disabled', true)
            .addClass('is-loading')
            .text('در حال افزودن به سبد...');

        function finish() {
            if (lastCartHtml) {
                $('#cart-list-item').replaceWith(lastCartHtml);
            }

            $button
                .removeClass('is-loading')
                .text(originalText);

            if (failedItems.length) {
                $button.prop('disabled', false);
                showCartMessage(
                    'error',
                    'بخشی از سفارش اضافه نشد',
                    'مدل‌های ناموفق: ' + failedItems.join('، ')
                );
                return;
            }

            clearModelSelections();
            showCartMessage(
                'success',
                'با موفقیت اضافه شد',
                'تمام مدل‌های انتخاب‌شده با تعداد تعیین‌شده به سبد خرید اضافه شدند.'
            );
        }

        function sendNext() {
            if (index >= items.length) {
                finish();
                return;
            }

            var item = items[index++];

            $.ajax({
                type: 'POST',
                url: action,
                data: {
                    quantity: item.quantity,
                    price_id: item.priceId
                },
                beforeSend: function (xhr) {
                    xhr.setRequestHeader(
                        'X-CSRF-TOKEN',
                        $('meta[name="csrf-token"]').attr('content')
                    );
                },
                success: function (data) {
                    if (data && data.status === 'success') {
                        lastCartHtml = data.cart;
                    } else {
                        failedItems.push(item.name + (data && data.message ? ' (' + data.message + ')' : ''));
                    }
                },
                error: function (xhr) {
                    var message = '';

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = ' (' + xhr.responseJSON.message + ')';
                    }

                    failedItems.push(item.name + message);
                },
                complete: sendNext
            });
        }

        sendNext();
    }

    $(document).on('click', '.aex-gallery-thumb, .aex-gallery-dot', function () {
        selectGalleryItem($(this).data('index'));
    });

    $(document).on('keydown', '.aex-gallery-thumb', function (event) {
        var $items = $('#product-gallery .aex-gallery-thumb');
        var currentIndex = $items.index(this);
        var targetIndex = currentIndex;

        if (event.key === 'ArrowDown' || event.key === 'ArrowLeft') {
            targetIndex = Math.min(currentIndex + 1, $items.length - 1);
        }

        if (event.key === 'ArrowUp' || event.key === 'ArrowRight') {
            targetIndex = Math.max(currentIndex - 1, 0);
        }

        if (targetIndex !== currentIndex) {
            event.preventDefault();
            $items.eq(targetIndex).focus().trigger('click');
        }
    });

    $(document).on('shown.bs.modal', '.aex-variants-modal', function () {
        $(this).find('.aex-variant-search').trigger('focus');
    });

    $(document).on('hidden.bs.modal', '.aex-variants-modal', function () {
        var $modal = $(this);
        $modal.find('.aex-variant-search').val('');
        filterModelCards($modal, '');
    });

    $(document).on('input', '.aex-variant-search', function () {
        filterModelCards($(this).closest('.aex-variants-modal'), $(this).val());
    });

    $(document).on('click', '.aex-model-card-main', function () {
        var $card = $(this).closest('[data-model-card]');
        setModelSelected($card, !$card.hasClass('is-selected'));
    });

    $(document).on('click', '.aex-model-qty-minus, .aex-model-qty-plus', function (event) {
        event.preventDefault();
        event.stopPropagation();

        var $input = $(this).siblings('.aex-model-qty-input');
        var value = clampQuantity($input);
        var isPlus = $(this).hasClass('aex-model-qty-plus');

        $input.val(value + (isPlus ? 1 : -1));
        clampQuantity($input);
        updateSelectionState();
    });

    $(document).on('input change', '.aex-model-qty-input', function (event) {
        event.stopPropagation();
        clampQuantity($(this));
        updateSelectionState();
    });

    $(document).on('click', '.aex-add-selected-to-cart', function () {
        addSelectedItemsToCart($(this));
    });

    $(function () {
        updateSelectionState();
    });
})(jQuery);
