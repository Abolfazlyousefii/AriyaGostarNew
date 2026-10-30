let autoCompleteUrl = $('#order-create-form').data('auto-complete-url');

$('#order-create-form input[name="username"]')
    .autocomplete({
        delay: 1000,
        minLength: 3,
        source: function (term, response) {
            $.ajax({
                url: `${autoCompleteUrl}?input=username`,
                type: 'GET',
                data: term,
                success: function (data) {
                    response(data);
                },
                error: function (data) {
                    //
                }
            });
        },
        select: function (event, ui) {
            setTimeout(function () {
                $(event.target).val(ui.item.username);
                $('#order-create-form input[name="first_name"]').val(
                    ui.item.first_name
                );
                $('#order-create-form input[name="last_name"]').val(
                    ui.item.last_name
                );
                $('#order-create-form select[name="city_id"]').data(
                    'id',
                    ui.item.address?.city_id
                );
                $('#order-create-form select[name="province_id"]')
                    .val(ui.item.address?.province_id)
                    .trigger('change');

                $('#order-create-form input[name="postal_code"]').val(
                    ui.item.address?.postal_code
                );
                $('#order-create-form textarea[name="address"]').val(
                    ui.item.address?.address
                );
            }, 100);
        }
    })
    .autocomplete('instance')._renderItem = function (ul, item) {
    return $('<li>')
        .attr('data-value', item.username)
        .append(
            `<li data-value="${item.username}">
                ${item.username}
                <small class="text-muted">
                    <p class="m-0">(${item.first_name} ${item.last_name})</p>
                </small>
            </li>`
        )
        .appendTo(ul);
};

let productsCount = 0;
const $category = $('#order-product-category');
const $productSearch = $('#add-product-to-order');
const disabledProductPlaceholder = 'ابتدا دسته‌بندی را انتخاب کنید';
const enabledProductPlaceholder = 'نام یا کد محصول را جستجو کنید';

function getDisplayPrice(price) {
    if (!price) {
        return null;
    }

    return price.sale_price ?? price.price ?? null;
}

function getInitialPriceId(searchTerm, prices) {
    const match = String(searchTerm || '').trim().match(/^(?:p-)?(\d+)$/i);

    if (match && prices.some(price => String(price.id) === match[1])) {
        return match[1];
    }

    return prices[0].id;
}

$productSearch
    .autocomplete({
        delay: 300,
        minLength: 0,
        source: function (request, response) {
            if (!$category.val()) {
                response([]);
                return;
            }

            $.ajax({
                url: $productSearch.data('action'),
                type: 'GET',
                data: {
                    term: request.term || '',
                    category_id: $category.val()
                },
                success: function (data) {
                    response(data && Array.isArray(data.data) ? data.data : []);
                },
                error: function () {
                    response([]);
                }
            });
        },
        select: function (event, ui) {
            const prices = Array.isArray(ui.item.prices) ? ui.item.prices : [];

            if (!prices.length) {
                toastr.error('برای این محصول قیمت قابل انتخابی وجود ندارد.');
                return false;
            }

            const initialPriceId = getInitialPriceId($productSearch.val(), prices);
            let template = ejs.render($('#product-template').html(), {
                product: ui.item,
                initialPriceId: initialPriceId
            });
            const $productRow = $(template);

            $productRow.find('.price-select option').each(function () {
                const priceIndex = Number($(this).data('price-index'));
                $(this).data('price', prices[priceIndex]);
            });

            $('#order-products-list').append($productRow);
            productsCount++;

            $productSearch.val('');
            return false;
        }
    })
    .autocomplete('instance')._renderItem = function (ul, item) {
    const title = item && item.title ? String(item.title) : 'محصول بدون عنوان';
    const $content = $('<div>').addClass('d-flex align-items-center py-50');

    if (item && item.image) {
        $('<img>')
            .attr('src', item.image)
            .attr('alt', 'تصویر محصول')
            .css({ width: '50px', height: '50px', objectFit: 'cover' })
            .appendTo($content);
    }

    const $details = $('<div>').addClass('ml-2');
    $('<div>').text(title).appendTo($details);

    if (item && item.product_code) {
        $('<p>')
            .addClass('m-0 text-muted small')
            .text(`کد محصول: ${item.product_code}`)
            .appendTo($details);
    }

    const displayPrice = getDisplayPrice(item);
    if (displayPrice !== null && displayPrice !== '') {
        $('<p>')
            .addClass('m-0 text-muted small')
            .text(`${number_format(displayPrice)} تومان`)
            .appendTo($details);
    }

    $content.append($details);

    return $('<li>').append($content).appendTo(ul);
};

$category.on('change', function () {
    $productSearch.val('');

    if (!$(this).val()) {
        $productSearch
            .prop('disabled', true)
            .attr('placeholder', disabledProductPlaceholder)
            .autocomplete('close');
        return;
    }

    $productSearch
        .prop('disabled', false)
        .attr('placeholder', enabledProductPlaceholder)
        .autocomplete('search', '');
});

$productSearch.on('focus', function () {
    if ($category.val()) {
        $(this).autocomplete('search', $(this).val());
    }
});

$(document).on(
    'click',
    '.order-single-product .delete-product-btn',
    function () {
        $(this).closest('.order-single-product').remove();
    }
);
$(document).on('change', '.order-single-product .price-select', function () {
    let price = $(this).find('option:selected').data('price');
    const $productRow = $(this).closest('.order-single-product');

    if (!price) {
        return;
    }

    $productRow.find('.selected-price').val(price.id);

    const $quantity = $productRow.find('.product-quantity');
    if (price.stock !== undefined && price.stock !== null && price.stock !== '') {
        $quantity.attr('max', price.stock);
    } else {
        $quantity.removeAttr('max');
    }

    const salePrice = getDisplayPrice(price) ?? 0;
    const regularPrice = price.regular_price ?? salePrice;
    $productRow.find('.sale-price').text(number_format(salePrice));
    $productRow.find('.regular-price').text(number_format(regularPrice));
    $productRow.find('.product-price').val(regularPrice);
    $productRow.find('.product-discount').val(regularPrice - salePrice);

    if (price.discount) {
        $productRow.find('.regular-price-container').removeClass('d-none');
    } else {
        $productRow.find('.regular-price-container').addClass('d-none');
    }
});

$('#order-create-form').submit(function (e) {
    e.preventDefault();

    if (!$(this).data('disabled')) {
        var formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            success: function (data) {
                $('#order-create-form').data('disabled', true);
                window.location.href = $('#order-create-form').data('redirect');
            },
            beforeSend: function (xhr) {
                block('#main-card');
                xhr.setRequestHeader(
                    'X-CSRF-TOKEN',
                    $('meta[name="csrf-token"]').attr('content')
                );
            },
            complete: function () {
                unblock('#main-card');
            },
            cache: false,
            contentType: false,
            processData: false
        });
    }
});

$('#province').trigger('change');
