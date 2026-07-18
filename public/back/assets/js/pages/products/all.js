CKEDITOR.config.height = 400;
CKEDITOR.replace('description');

$('.tags').tagsInput({
    defaultText: 'افزودن',
    width: '100%',
    autocomplete_url: $('.tags').data('action')
});

$('.labels').tagsInput({
    defaultText: 'افزودن',
    width: '100%',
    height: '110px',
    autocomplete_url: $('.labels').data('action')
});

$('.product-category').select2ToTree({
    rtl: true,
    width: '100%'
});

$('select.select2').select2({
    rtl: true,
    width: '100%'
});

$('.product-categories').select2ToTree({
    rtl: true,
    width: '100%'
});

// validate form with jquery validation plugin
jQuery('#product-create-form, #product-edit-form').validate({
    rules: {
        title: {
            required: true
        },
        weight: {
            required: true,
            digits: true
        }
    }
});

//------------ specification group js codes

var groupsCount = groupCount;

$('#add-product-specification-group').click(function () {
    var template = $('#specification-group').clone();

    var group = $('#specifications-area').append(template.html());

    var count = ++groupCount;
    groupsCount++;

    var input = group.find('input[name="specification_group"]');

    input.attr('name', 'specification_group[' + count + '][name]');
    input.data('group_name', count);

    groupSortable();

    setTimeout(() => {
        group.find('.specification-group').removeClass('.animated fadeIn');
    }, 700);
});

function groupSortable() {
    $('#specifications-area').sortable({
        opacity: 0.75,
        start: function (e, ui) {
            ui.placeholder.css({
                height: ui.item.outerHeight(),
                'margin-bottom': ui.item.css('margin-bottom')
            });
        },
        helper: function (e, tr) {
            var $originals = tr.children();
            var $helper = tr.clone();
            $helper.children().each(function (index) {
                $(this).width($originals.eq(index).width());
            });
            return $helper;
        }
    });
}

groupSortable();

$(document).on('click', '.remove-group', function () {
    var group = $(this).closest('.specification-group');

    group.addClass('animated fadeOut');

    setTimeout(() => {
        group.remove();
    }, 500);

    groupsCount--;
});

//------------ specifications js codes

$(document).on('click', '.add-specifaction', function () {
    var template = $('#specification-single').clone();

    var specification = $(this)
        .closest('.specification-group')
        .find('.all-specifications')
        .append(template.html());

    var count = ++specificationCount;
    var group_name = $(specification)
        .closest('.specification-group')
        .find('.group-input')
        .data('group_name');

    specification
        .find('input[name="special_specification"]')
        .attr(
            'name',
            'specification_group[' +
                group_name +
                '][specifications][' +
                count +
                '][special]'
        );
    specification
        .find('input[name="specification_name"]')
        .attr(
            'name',
            'specification_group[' +
                group_name +
                '][specifications][' +
                count +
                '][name]'
        );
    specification
        .find('textarea[name="specification_value"]')
        .attr(
            'name',
            'specification_group[' +
                group_name +
                '][specifications][' +
                count +
                '][value]'
        );

    specificationSortable();

    setTimeout(() => {
        specification
            .find('.single-specificition')
            .removeClass('.animated fadeIn');
    }, 700);
});

$(document).on('click', '.remove-specification', function () {
    var specification = $(this).closest('.single-specificition');

    specification.addClass('animated fadeOut');

    setTimeout(() => {
        specification.remove();
    }, 500);
});

function specificationSortable() {
    $('.all-specifications').sortable({
        opacity: 0.75,
        start: function (e, ui) {
            ui.placeholder.css({
                height: ui.item.outerHeight(),
                'margin-bottom': ui.item.css('margin-bottom')
            });
        },
        helper: function (e, tr) {
            var $originals = tr.children();
            var $helper = tr.clone();
            $helper.children().each(function (index) {
                $(this).width($originals.eq(index).width());
            });
            return $helper;
        }
    });
}

specificationSortable();

//------------ files js codes

function addProductFile() {
    var template = $('#files-template').clone();

    var file = $('#product-files-area')
        .append(template.html())
        .find('.single-file:last');
    var count = ++filesCount;

    file.find('input[name="title"]').attr(
        'name',
        'download_files[' + count + '][title]'
    );
    file.find('select[name="status"]').attr(
        'name',
        'download_files[' + count + '][status]'
    );
    file.find('input[name="file"]').attr(
        'name',
        'download_files[' + count + '][file]'
    );
    file.find('input[name="file"]').attr(
        'id',
        'download_files[' + count + '][id]'
    );
    file.find('label[for="file"]').attr(
        'for',
        'download_files[' + count + '][id]'
    );
    file.find('input[name="price"]').attr(
        'name',
        'download_files[' + count + '][price]'
    );
    file.find('input[name="discount"]').attr(
        'name',
        'download_files[' + count + '][discount]'
    );

    filesSortable();

    setTimeout(() => {
        file.removeClass('animated fadeIn');
    }, 700);
}

$(document).on('click', '#add-product-file', function () {
    addProductFile();
});

$(document).on('click', '.remove-file', function () {
    var file = $(this).closest('.single-file');

    file.addClass('animated fadeOut');

    setTimeout(() => {
        file.remove();
    }, 500);
});

if (filesCount == 0) {
    addProductFile();
}

function filesSortable() {
    $('#product-files-area').sortable({
        opacity: 0.75,
        start: function (e, ui) {
            ui.placeholder.css({
                height: ui.item.outerHeight(),
                'margin-bottom': ui.item.css('margin-bottom')
            });
        },
        helper: function (e, tr) {
            var $originals = tr.children();
            var $helper = tr.clone();
            $helper.children().each(function (index) {
                $(this).width($originals.eq(index).width());
            });
            return $helper;
        }
    });
}

filesSortable();

$('#product-type').on('change', function () {
    if ($(this).val() == 'physical') {
        $('.physical-item').show();
        $('.download-item').hide();
    } else {
        $('.physical-item').hide();
        $('.download-item').show();
    }
});

$('#product-type').trigger('change');

//------------ spectype js codes

$('#specifications_type').autocomplete({
    source: availableTypes
});

$('#specifications_type').change(function () {
    var value = $(this).val();

    if (availableTypes.includes(value) && !specifications_type_first_change) {
        addSpecTypeData();
    } else if (availableTypes.includes(value) && groupsCount != 0) {
        $('#specifications-modal').modal('show');
    } else if (availableTypes.includes(value) && groupsCount == 0) {
        addSpecTypeData();
    }

    specifications_type_first_change = true;

    $('#spec-div').show();
});

$('#add-spec-type-data').click(addSpecTypeData);

$('#specifications_type').on('keyup keypress', function (e) {
    var keyCode = e.keyCode || e.which;
    if (keyCode === 13) {
        e.preventDefault();
        return false;
    }
});

function addSpecTypeData() {
    $.ajax({
        url: BASE_URL + '/spectypes/spec-type-data',
        type: 'GET',
        data: {
            name: $('#specifications_type').val()
        },
        success: function (data) {
            groupCount = data.groupCount;
            specificationCount = data.specificationCount;
            groupsCount = data.groupCount;

            $('#specifications-area').html(data.view);
            specificationSortable();
            groupSortable();
        },
        beforeSend: function (xhr) {
            block('#specifications-card');
        },
        complete: function () {
            unblock('#specifications-card');
        }
    });
}

//------------ size type js codes

$('#size_type_id').on('change', function () {
    $('#sizes-area').html('');
    $('.add-value').hide();

    if (!$(this).val()) return;

    let select = $(this);

    $.ajax({
        url: select.find('option:selected').data('action'),
        type: 'GET',
        success: function (data) {
            $('#sizes-area').html(data);
            sizeSortable();
            $('.add-value').show();
        },
        beforeSend: function (xhr) {
            block('#sizes-card');
        },
        complete: function () {
            unblock('#sizes-card');
        }
    });
});

$(document).on('click', '.remove-value', function () {
    if ($('.single-value').length == 1) return;

    let value = $(this).closest('.single-value');

    value.addClass('animated fadeOut');

    setTimeout(() => {
        value.remove();
    }, 500);
});

$(document).on('click', '.add-value', function () {
    let template = $('.single-value').first().clone();
    template.addClass('animated fadeIn');
    template.find('input').val('');

    ++sizesCount;

    template.find('input').each(function (i, item) {
        $(item).attr(
            'name',
            `sizes[${sizesCount}][${$(item).data('size-id')}]`
        );
    });

    let value = $('#sizes-area').append(template);

    sizeSortable();

    setTimeout(() => {
        value.find('.single-value').removeClass('animated fadeIn');
    }, 700);
});

function sizeSortable() {
    if ($('.all-sizes').children().length == 0) {
        $('.add-value').hide();
    } else {
        $('.add-value').show();
    }

    $('.all-sizes').sortable({
        opacity: 0.75,
        start: function (e, ui) {
            ui.placeholder.css({
                height: ui.item.outerHeight(),
                'margin-bottom': ui.item.css('margin-bottom')
            });
        },
        helper: function (e, tr) {
            var $originals = tr.children();
            var $helper = tr.clone();
            $helper.children().each(function (index) {
                $(this).width($originals.eq(index).width());
            });
            return $helper;
        }
    });
}

sizeSortable();

//------------ prices js codes

$('#add-product-prices').click(function () {
    addProductPrice();
});

$('#copy-product-prices').click(function () {
    addProductPrice(true);
});

$(document).on('click', '.remove-product-price', function () {
    var price = $(this).closest('.single-price');

    price.addClass('animated fadeOut');

    setTimeout(() => {
        price.remove();
        setColorsImage();
    }, 500);
});

if (priceCount == 0) {
    addProductPrice();
}

function addProductPrice(copy = false, initializePlugins = true) {
    var template = $('#prices-template').clone();

    let lastPrice = $('#product-prices-div').find('.single-price').last();

    var data = {
        price: '',
        discount: '',
        cart_max: '',
        cart_min: '',
        stock: '',
        external_stock_code: '',
        stock_sync_enabled: false
    };

    if (copy && lastPrice.length) {
        data.price = lastPrice.find('.price').val();
        data.discount = lastPrice.find('.discount').val();
        data.cart_max = lastPrice.find('.cart_max').val();
        data.cart_min = lastPrice.find('.cart_min').val();
        data.stock = lastPrice.find('.stock').val();
        data.external_stock_code = lastPrice.find('.external-stock-code').val();
        data.stock_sync_enabled = lastPrice.find('.stock-sync-enabled').is(':checked');

        var selectedIds = [];

        lastPrice
            .find('select.price-attribute-select')
            .each(function (index, el) {
                selectedIds.push($(el).val());
            });
    }

    $('#product-prices-div').append(template.html());
    var price = $('#product-prices-div').find('.single-price').last();

    var count = ++priceCount;
    let unit = price
        .closest('.product-prices-tab')
        .find('select[name="currency_id"] option:selected')
        .data('title');

    price
        .find('select[name="attribute"]')
        .attr('name', 'prices[' + count + '][attributes][]')
        .attr('data-number', count)
        .each(function (index, el) {
            $(el)
                .val(
                    typeof selectedIds != 'undefined' ? selectedIds[index] : ''
                )
                .change();
        });

    price
        .find('input[name="price"]')
        .attr('name', 'prices[' + count + '][price]')
        .data('unit', unit)
        .val(data.price)
        .trigger('keyup');
    price
        .find('input[name="price_image"]')
        .attr('name', 'prices[' + count + '][image]')
        .attr('data-number', count);
    price
        .find('input[name="discount"]')
        .attr('name', 'prices[' + count + '][discount]')
        .val(data.discount);
    price
        .find('input[name="discount_expire_at"]')
        .attr('name', 'prices[' + count + '][discount_expire_at]');
    price
        .find('input[name="cart_max"]')
        .attr('name', 'prices[' + count + '][cart_max]')
        .val(data.cart_max);
    price
        .find('input[name="cart_min"]')
        .attr('name', 'prices[' + count + '][cart_min]')
        .val(data.cart_min);
    price
        .find('input[name="stock"]')
        .attr('name', 'prices[' + count + '][stock]')
        .val(data.stock);
    price
        .find('input[name="external_stock_code"]')
        .attr('name', 'prices[' + count + '][external_stock_code]')
        .val(data.external_stock_code);

    var stockSyncId = 'stock-sync-enabled-' + count;

    price
        .find('.stock-sync-enabled-hidden')
        .attr('name', 'prices[' + count + '][stock_sync_enabled]');
    price
        .find('.stock-sync-enabled')
        .attr('name', 'prices[' + count + '][stock_sync_enabled]')
        .attr('id', stockSyncId)
        .prop('checked', data.stock_sync_enabled);
    price
        .find('.stock-sync-enabled-label')
        .attr('for', stockSyncId);
    price
        .find('input[name="discount_expire"]')
        .attr('name', 'prices[' + count + '][discount_expire]');

    if (initializePlugins) {
        setTimeout(() => {
            $('.persian-date-picker').customPersianDate();
            price.removeClass('animated fadeIn');

            price.find('select.select2').select2({
                rtl: true,
                width: '100%'
            });
        }, 100);
    } else {
        price.removeClass('animated fadeIn');
    }

    return price;
}

$('select[name="currency_id"]').on('change', function () {
    var unit = $(this).find(':selected').data('title');

    $('.single-price .amount-input').data('unit', unit).trigger('keyup');
});

$(document).on(
    'keyup',
    '.single-price .price, .single-price .discount',
    function () {
        let unit = $(this)
            .closest('.product-prices-tab')
            .find('select[name="currency_id"] option:selected')
            .data('amount');

        let roundingAmount = $(this)
            .closest('.product-prices-tab')
            .find('select[name="rounding_amount"] option:selected')
            .data('value');

        let roundingType = $(this)
            .closest('.product-prices-tab')
            .find('select[name="rounding_type"] option:selected')
            .data('value');

        let discount = $(this).closest('.single-price').find('.discount').val();

        let price = $(this).closest('.single-price').find('.price').val();

        price = price ? parseFloat(price) : 0;
        unit = parseFloat(unit);
        discount = discount ? parseFloat(discount) : 0;
        roundingAmount =
            roundingAmount != 'no' ? parseFloat(roundingAmount) : 0;

        let finalPrice = (price - price * (discount / 100)) * unit;

        finalPrice = toRoundInt(finalPrice, roundingType, roundingAmount);

        finalPrice = +finalPrice.toFixed(2);

        let finalPriceText = number_format(finalPrice) + ' تومان';

        $(this)
            .closest('.single-price')
            .find('.final-price')
            .val(finalPriceText);
    }
);

$(document).on('change', '.prices-option-div select', function () {
    $('.single-price .price').trigger('keyup');
});

$('.prices-option-div select').trigger('change');

//------------ bulk variable product prices

var bulkPriceGroups = [];

try {
    bulkPriceGroups = JSON.parse($('#bulk-price-groups-data').text() || '[]');
} catch (error) {
    bulkPriceGroups = [];
}

function bulkPriceNotify(message, type = 'error') {
    if (typeof toastr !== 'undefined' && toastr[type]) {
        toastr[type](message, type === 'error' ? 'خطا' : 'موفق');
        return;
    }

    alert(message);
}

function getBulkPriceGroup(groupId) {
    return bulkPriceGroups.find(function (group) {
        return String(group.id) === String(groupId);
    });
}

function getSelectedBulkModels() {
    var selectedIds = $('#bulk-price-models').val() || [];
    var models = [];

    selectedIds.forEach(function (id) {
        var option = $('#bulk-price-models option[value="' + id + '"]');

        models.push({
            id: String(id),
            name: option.text()
        });
    });

    return models;
}

function updateBulkModelCounter() {
    var count = ($('#bulk-price-models').val() || []).length;
    $('#bulk-price-model-count').text(count + ' مدل انتخاب شده');
}

function setBulkPriceControlsState() {
    var hasGroup = Boolean($('#bulk-price-group').val());
    var hasModels = ($('#bulk-price-models').val() || []).length > 0;

    $('#bulk-price-models')
        .prop('disabled', !hasGroup)
        .trigger('change.select2');
    $('#bulk-price-select-all, #bulk-price-clear-models').prop(
        'disabled',
        !hasGroup
    );
    $('#bulk-add-price-exception, #bulk-generate-prices').prop(
        'disabled',
        !hasModels
    );
}

function refreshBulkExceptionSelect($select) {
    var currentValue = $select.val();
    var models = getSelectedBulkModels();

    if ($select.hasClass('select2-hidden-accessible')) {
        $select.select2('destroy');
    }

    $select.empty().append('<option value="">انتخاب مدل</option>');

    models.forEach(function (model) {
        $('<option>')
            .val(model.id)
            .text(model.name)
            .appendTo($select);
    });

    if (models.some(function (model) { return model.id === String(currentValue); })) {
        $select.val(currentValue);
    }

    $select.select2({
        rtl: true,
        width: '100%'
    });
}

function refreshAllBulkExceptionSelects() {
    $('#bulk-price-exceptions .bulk-exception-model').each(function () {
        refreshBulkExceptionSelect($(this));
    });
}

function addBulkPriceException() {
    if (!getSelectedBulkModels().length) {
        bulkPriceNotify('ابتدا حداقل یک مدل را انتخاب کنید.');
        return;
    }

    var row = $(
        '<div class="row align-items-end border rounded p-1 mb-1 bulk-price-exception-row">' +
            '<div class="col-lg-3 col-md-6 col-12">' +
                '<div class="form-group mb-md-0">' +
                    '<label>مدل استثنا</label>' +
                    '<select class="form-control bulk-exception-model"></select>' +
                '</div>' +
            '</div>' +
            '<div class="col-lg-2 col-md-3 col-12">' +
                '<div class="form-group mb-md-0">' +
                    '<label>قیمت متفاوت</label>' +
                    '<input type="number" min="0" class="form-control amount-input bulk-exception-price" data-unit="تومان" placeholder="قیمت مشترک">' +
                '</div>' +
            '</div>' +
            '<div class="col-lg-2 col-md-3 col-12">' +
                '<div class="form-group mb-md-0">' +
                    '<label>موجودی متفاوت</label>' +
                    '<input type="number" min="0" class="form-control bulk-exception-stock" placeholder="موجودی مشترک">' +
                '</div>' +
            '</div>' +
            '<div class="col-lg-3 col-md-8 col-12">' +
                '<div class="form-group mb-md-0">' +
                    '<label>کد متغیر متفاوت</label>' +
                    '<input type="text" class="form-control bulk-exception-code" dir="ltr" placeholder="اختیاری">' +
                '</div>' +
            '</div>' +
            '<div class="col-lg-2 col-md-4 col-12">' +
                '<button type="button" class="btn btn-flat-danger btn-block remove-bulk-price-exception">حذف</button>' +
            '</div>' +
        '</div>'
    );

    $('#bulk-price-exceptions').append(row);
    refreshBulkExceptionSelect(row.find('.bulk-exception-model'));
}

function getBulkPriceExceptions() {
    var exceptions = {};
    var isValid = true;

    $('#bulk-price-exceptions .bulk-price-exception-row').each(function () {
        var row = $(this);
        var modelId = row.find('.bulk-exception-model').val();
        var price = String(row.find('.bulk-exception-price').val() || '').trim();
        var stock = String(row.find('.bulk-exception-stock').val() || '').trim();
        var code = String(row.find('.bulk-exception-code').val() || '').trim();

        if (!modelId) {
            bulkPriceNotify('برای تمام ردیف‌های استثنا یک مدل انتخاب کنید.');
            isValid = false;
            return false;
        }

        if (Object.prototype.hasOwnProperty.call(exceptions, modelId)) {
            bulkPriceNotify('هر مدل فقط یک‌بار می‌تواند در استثناها ثبت شود.');
            isValid = false;
            return false;
        }

        if (price === '' && stock === '' && code === '') {
            bulkPriceNotify('برای مدل استثنا حداقل قیمت، موجودی یا کد متغیر متفاوت وارد کنید.');
            isValid = false;
            return false;
        }

        exceptions[String(modelId)] = {
            price: price,
            stock: stock,
            code: code
        };
    });

    return isValid ? exceptions : null;
}

function normalizeBulkModelName(value) {
    return String(value || '')
        .trim()
        .toLowerCase()
        .replace(/\s+/g, ' ');
}

function parseBulkExternalStockCodes(models) {
    var raw = String($('#bulk-external-stock-codes').val() || '').trim();
    var result = {};

    if (!raw) {
        return result;
    }

    var lines = raw
        .split(/\r?\n/)
        .map(function (line) { return line.trim(); })
        .filter(Boolean);

    var sequentialCodes = [];

    lines.forEach(function (line) {
        var parts = line.split(/\s*(?:\||=|\t)\s*/, 2);

        if (parts.length === 2 && parts[0] && parts[1]) {
            result[normalizeBulkModelName(parts[0])] = parts[1].trim();
        } else {
            sequentialCodes.push(line);
        }
    });

    if (sequentialCodes.length) {
        models.forEach(function (model, index) {
            if (sequentialCodes[index]) {
                result[String(model.id)] = sequentialCodes[index];
            }
        });
    }

    return result;
}

function findExactBulkPriceRow(groupId, attributeId) {
    var matchedRow = null;

    $('#product-prices-div .single-price').each(function () {
        var row = $(this);
        var selects = row.find('select.price-attribute-select');
        var target = selects.filter('[data-group-id="' + groupId + '"]');

        if (!target.length || String(target.val() || '') !== String(attributeId)) {
            return;
        }

        var hasAnotherAttribute = false;

        selects.not(target).each(function () {
            if ($(this).val()) {
                hasAnotherAttribute = true;
                return false;
            }
        });

        if (!hasAnotherAttribute) {
            matchedRow = row;
            return false;
        }
    });

    return matchedRow;
}

function removeCompletelyEmptyPriceRows() {
    $('#product-prices-div .single-price').each(function () {
        var row = $(this);
        var hasAttribute = false;

        row.find('select.price-attribute-select').each(function () {
            if ($(this).val()) {
                hasAttribute = true;
                return false;
            }
        });

        if (
            !hasAttribute &&
            !row.find('.price').val() &&
            !row.find('.stock').val() &&
            !row.find('.discount').val()
        ) {
            row.remove();
        }
    });
}

function fillBulkPriceRow(row, groupId, attributeId, values) {
    var target = row.find(
        'select.price-attribute-select[data-group-id="' + groupId + '"]'
    );

    if (!target.length) {
        return false;
    }

    target.val(String(attributeId)).trigger('change');
    row.find('.price').val(values.price);
    row.find('.discount').val(values.discount);
    row.find('.stock').val(values.stock);
    row.find('.cart_min').val(values.cartMin);
    row.find('.cart_max').val(values.cartMax);
    row.find('.external-stock-code').val(values.externalStockCode || '');
    row.find('.stock-sync-enabled').prop(
        'checked',
        Boolean(values.stockSyncEnabled && values.externalStockCode)
    );
    row.find('.price').trigger('keyup');

    return true;
}

$('#bulk-price-group').on('change', function () {
    var group = getBulkPriceGroup($(this).val());
    var modelsSelect = $('#bulk-price-models');

    modelsSelect.val(null).empty();
    $('#bulk-price-exceptions').empty();

    if (group) {
        group.attributes.forEach(function (attribute) {
            $('<option>')
                .val(attribute.id)
                .text(attribute.name)
                .appendTo(modelsSelect);
        });
    }

    modelsSelect.trigger('change');
    updateBulkModelCounter();
    setBulkPriceControlsState();
});

$('#bulk-price-models').on('change', function () {
    updateBulkModelCounter();
    setBulkPriceControlsState();
    refreshAllBulkExceptionSelects();
});

$('#bulk-price-select-all').on('click', function () {
    var ids = $('#bulk-price-models option')
        .map(function () {
            return $(this).val();
        })
        .get();

    $('#bulk-price-models').val(ids).trigger('change');
});

$('#bulk-price-clear-models').on('click', function () {
    $('#bulk-price-models').val(null).trigger('change');
    $('#bulk-price-exceptions').empty();
});

$('#bulk-add-price-exception').on('click', function () {
    addBulkPriceException();
});

$(document).on('click', '.remove-bulk-price-exception', function () {
    var row = $(this).closest('.bulk-price-exception-row');

    if (row.find('.bulk-exception-model').hasClass('select2-hidden-accessible')) {
        row.find('.bulk-exception-model').select2('destroy');
    }

    row.remove();
});

$('#bulk-generate-prices').on('click', function () {
    var groupId = $('#bulk-price-group').val();
    var modelIds = $('#bulk-price-models').val() || [];
    var defaultPrice = String($('#bulk-default-price').val() || '').trim();
    var defaultStock = String($('#bulk-default-stock').val() || '').trim();
    var defaultDiscount = String($('#bulk-default-discount').val() || '').trim();
    var defaultCartMin = String($('#bulk-default-cart-min').val() || '').trim();
    var defaultCartMax = String($('#bulk-default-cart-max').val() || '').trim();

    if (!groupId) {
        bulkPriceNotify('گروه مدل را انتخاب کنید.');
        return;
    }

    if (!modelIds.length) {
        bulkPriceNotify('حداقل یک مدل را انتخاب کنید.');
        return;
    }

    if (defaultPrice === '') {
        bulkPriceNotify('قیمت مشترک را وارد کنید.');
        return;
    }

    if (defaultStock === '') {
        bulkPriceNotify('موجودی مشترک را وارد کنید.');
        return;
    }

    if (
        defaultCartMin !== '' &&
        defaultCartMax !== '' &&
        Number(defaultCartMin) > Number(defaultCartMax)
    ) {
        bulkPriceNotify('حداقل سفارش نمی‌تواند بیشتر از حداکثر سفارش باشد.');
        return;
    }

    var exceptions = getBulkPriceExceptions();
    var selectedModels = getSelectedBulkModels();
    var externalStockCodes = parseBulkExternalStockCodes(selectedModels);
    var enableStockSync = $('#bulk-stock-sync-enabled').is(':checked');

    if (exceptions === null) {
        return;
    }

    removeCompletelyEmptyPriceRows();

    var createdCount = 0;
    var updatedCount = 0;
    var newRows = [];

    modelIds.forEach(function (modelId) {
        var row = findExactBulkPriceRow(groupId, modelId);
        var exception = exceptions[String(modelId)] || {};
        var selectedModel = selectedModels.find(function (model) {
            return String(model.id) === String(modelId);
        });
        var mappedCode = externalStockCodes[String(modelId)] ||
            externalStockCodes[normalizeBulkModelName(selectedModel ? selectedModel.name : '')] ||
            '';
        var externalStockCode = exception.code !== undefined && exception.code !== ''
            ? exception.code
            : mappedCode;
        var values = {
            price: exception.price !== undefined && exception.price !== ''
                ? exception.price
                : defaultPrice,
            stock: exception.stock !== undefined && exception.stock !== ''
                ? exception.stock
                : defaultStock,
            discount: defaultDiscount,
            cartMin: defaultCartMin,
            cartMax: defaultCartMax,
            externalStockCode: externalStockCode,
            stockSyncEnabled: enableStockSync
        };

        if (row && row.length) {
            updatedCount++;
        } else {
            row = addProductPrice(false, false);
            newRows.push(row);
            createdCount++;
        }

        fillBulkPriceRow(row, groupId, modelId, values);
    });

    newRows.forEach(function (row) {
        row.find('select.select2').select2({
            rtl: true,
            width: '100%'
        });
    });

    if (typeof setColorsImage === 'function') {
        setColorsImage();
    }

    bulkPriceNotify(
        createdCount +
            ' مدل ساخته شد و ' +
            updatedCount +
            ' مدل قبلی به‌روزرسانی شد. برای ذخیره نهایی، محصول را ثبت کنید.',
        'success'
    );
});

updateBulkModelCounter();
setBulkPriceControlsState();

//------------ generate slug

$('#generate-product-slug').click(function (e) {
    e.preventDefault();

    var title = $('input[name="meta_title"]').val();

    $.ajax({
        url: BASE_URL + '/product/slug',
        type: 'POST',
        data: {
            title: title
        },
        success: function (data) {
            $('#slug').val(data.slug);
        },
        beforeSend: function (xhr) {
            xhr.setRequestHeader(
                'X-CSRF-TOKEN',
                $('meta[name="csrf-token"]').attr('content')
            );
            $('#slug-spinner').show();
        },
        complete: function () {
            $('#slug-spinner').hide();
        }
    });
});

//------------ dropzone sortable

$('.dropzone-area').sortable({
    items: '.dz-preview',
    opacity: 0.75,
    start: function (e, ui) {
        ui.placeholder.css({
            height: ui.item.outerHeight(),
            'margin-bottom': ui.item.css('margin-bottom')
        });
    },
    helper: function (e, tr) {
        var $originals = tr.children();
        var $helper = tr.clone();
        $helper.children().each(function (index) {
            $(this).width($originals.eq(index).width());
        });
        return $helper;
    }
});

//------------ spectype js codes

$('#brand').autocomplete({
    source: BASE_URL + '/brands/ajax/get',
    delay: 1000
});

//------------ special product js codes

$('input[name="special"]').on('change', function () {
    if ($(this).is(':checked')) {
        $('#special-end-date-container').show();
    } else {
        $('#special-end-date-container').hide();
    }
});

$('input[name="special"]').trigger('change');

//------------ prices color

function setColorsImage() {
    let colorSelects = $('select[data-type="color"]');
    let colors = [];

    colorSelects.each(function (index, el) {
        let option = $(el).find('option:selected');

        if (option.val()) {
            colors.push({name: option.text(), id: option.val()});
        }
    });

    colors = colors.filter(
        (item, index, self) =>
            index ===
            self.findIndex((t) => t.name === item.name && t.id === item.id)
    );

    let dropzoneSelects = $('div#product-images select.color-select');

    if (colors.length) {
        dropzoneSelects.each(function (index, el) {
            $(el).removeClass('d-none');

            let selectedColor = $(el).data('color-id');

            if (selectedColor) {
                $('#product-edit-form').append(
                    `<input type="hidden" name="color_selects[${$(el).data(
                        'image'
                    )}]" value="${$(el).data('color-id')}" />`
                );
            }

            $(el).empty();

            $(el).append('<option value="">انتخاب رنگ</option>');

            colors.forEach(function (el2) {
                let selected = selectedColor == el2.id ? 'selected' : '';

                $(el).append(
                    `<option value="${el2.id}" ${selected}>${el2.name}</option>`
                );
            });
        });
    } else {
        dropzoneSelects.empty().addClass('d-none');
    }
}

setTimeout(() => {
    setColorsImage();
}, 600);

$(document).on(
    'change',
    'div#product-images select.color-select',
    function (e) {
        let select = $(this);

        $('#product-edit-form').append(
            `<input type="hidden" name="color_selects[${select.data(
                'image'
            )}]" value="${select.val()}" />`
        );
        select.data('color-id', select.val());
    }
);

$(document).on('change', 'select[data-type="color"]', function () {
    $(`input[name="prices[${$(this).data('number')}][image]"]`).val('');
    setColorsImage();
});

setTimeout(() => {
    $('select[data-type="color"]').each(function (index, el) {
        let imageInputEl = $(
            `input[name="prices[${$(el).data('number')}][image]"]`
        );

        $(`div[data-name="${imageInputEl.val()}"]`)
            .find('select')
            .val($(el).find('option:selected').text());
    });
}, 500);
