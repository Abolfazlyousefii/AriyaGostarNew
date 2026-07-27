'use strict';
// Class definition

var datatable;

var product_datatable = (function () {
    // Private functions

    var options = {
        // datasource definition
        data: {
            type: 'remote',
            source: {
                read: {
                    url: $('#products_datatable').data('action'),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                            'content'
                        )
                    },
                    map: function (raw) {
                        // sample data mapping
                        var dataSet = raw;
                        if (typeof raw.data !== 'undefined') {
                            dataSet = raw.data;
                        }
                        return dataSet;
                    },
                    params: {
                        query: $('#filter-products-form').serializeJSON()
                    }
                }
            },
            pageSize: 10,
            serverPaging: true,
            serverFiltering: true,
            serverSorting: true
        },

        layout: {
            scroll: true
        },

        rows: {
            autoHide: false
        },

        // columns definition
        columns: [
            {
                field: 'id',
                title: '#',
                sortable: false,
                width: 28,
                selector: { class: '' },
                textAlign: 'center'
            },
            {
                field: 'productid',
                sortable: false,
                width: 55,
                title: 'شناسه',
                template: function (row) {
                    return row.id;
                }
            },
            {
                field: 'title',
                title: 'عنوان',
                width: 230,
                template: function (row) {
                    var code = row.product_code
                        ? '<small class="d-block text-muted ltr">' + row.product_code + '</small>'
                        : '';
                    return '<strong>' + row.title + '</strong>' + code;
                }
            },
            {
                field: 'price',
                title: 'قیمت',
                textAlign: 'center',
                width: 105,
                template: function (row) {
                    return row.price !== null
                        ? '<span class="ltr">' + Number(row.price).toLocaleString('fa-IR') + '</span>'
                        : '<span class="text-muted">—</span>';
                }
            },
            {
                field: 'stock_count',
                title: 'موجودی',
                textAlign: 'center',
                width: 75,
                template: function (row) {
                    return '<strong>' + row.stock_count + '</strong>';
                }
            },
            {
                field: 'category',
                title: 'دسته‌بندی‌ها',
                width: 150,
                template: function (row) {
                    var values = row.categories && row.categories.length
                        ? row.categories
                        : (row.category ? [row.category] : []);
                    if (!values.length) return '<span class="text-muted">بدون دسته</span>';
                    return values.map(function (item) {
                        return '<span class="apl-badge apl-category-badge ml-25">' + item + '</span>';
                    }).join('');
                }
            },
            {
                field: 'availability',
                title: 'وضعیت',
                textAlign: 'center',
                width: 90,
                template: function (row) {
                    return row.addableToCart
                        ? '<span class="apl-badge apl-badge-success">موجود</span>'
                        : '<span class="apl-badge apl-badge-danger">ناموجود</span>';
                }
            },
            {
                field: 'published',
                title: 'وضعیت تأیید',
                textAlign: 'center',
                width: 95,
                template: function (row) {
                    return row.published
                        ? '<span class="apl-badge apl-badge-success"><i class="feather icon-check-circle ml-25"></i> تأیید</span>'
                        : '<span class="apl-badge apl-badge-info">پیش‌نویس</span>';
                }
            },
            {
                field: 'created_at',
                sortable: 'desc',
                title: 'تاریخ ثبت',
                width: 95,
                template: function (row) {
                    return '<span class="ltr">' + row.created_at + '</span>';
                }
            },
            {
                field: 'related_count',
                title: 'محصولات مشابه',
                textAlign: 'center',
                sortable: false,
                width: 90,
                template: function (row) {
                    return '<span class="apl-badge apl-badge-success">' + row.related_count + '</span>';
                }
            },
            {
                field: 'actions',
                title: 'عملیات',
                textAlign: 'center',
                sortable: false,
                width: 145,
                overflow: 'visible',
                autoHide: false,
                template: function (row) {
                    return (
                        '<a title="ویرایش" href="' + row.links.edit + '" class="apl-action-btn apl-edit"><i class="feather icon-edit-2"></i></a>' +
                        '<a title="کپی محصول" href="' + row.links.copy + '" class="apl-action-btn apl-copy"><i class="feather icon-copy"></i></a>' +
                        '<a title="مشاهده" target="_blank" href="' + row.links.front + '" class="apl-action-btn apl-copy"><i class="feather icon-external-link"></i></a>' +
                        '<button title="حذف" data-toggle="modal" data-target="#delete-modal" data-action="' + row.links.destroy + '" class="apl-action-btn apl-delete btn-delete"><i class="feather icon-trash-2"></i></button>'
                    );
                }
            }
        ]
    };

    var initDatatable = function () {
        // enable extension
        options.extensions = {
            // boolean or object (extension options)
            checkbox: true
        };

        datatable = $('#products_datatable').KTDatatable(options);

        $('#filter-products-form .datatable-filter').on('change', function () {
            formDataToUrl('filter-products-form');
            datatable.setDataSourceQuery(
                $('#filter-products-form').serializeJSON()
            );
            datatable.reload();
        });

        datatable.on('datatable-on-click-checkbox', function (e) {
            var ids = datatable.checkbox().getSelectedId();
            var count = ids.length;

            $('.selected_product_ids').empty();

            ids.forEach(function (id) {
                $('.selected_product_ids').append(
                    `<option value="${id}" selected>${id}</option>`
                );
            });
            $('#datatable-selected-rows').html(count);

            if (count > 0) {
                $('.datatable-actions').collapse('show');
            } else {
                $('.datatable-actions').collapse('hide');
            }
        });

        datatable.on('datatable-on-reloaded', function (e) {
            $('.datatable-actions').collapse('hide');
        });
    };

    return {
        // public functions
        init: function () {
            initDatatable();
        }
    };
})();

jQuery(document).ready(function () {
    product_datatable.init();
});

$(document).on('click', '.btn-delete', function () {
    $('#product-delete-form').attr('action', $(this).data('action'));
});

$('#product-delete-form').on('submit', function (e) {
    e.preventDefault();

    $('#delete-modal').modal('hide');

    var formData = new FormData(this);

    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: formData,
        success: function (data) {
            toastr.success('محصول با موفقیت حذف شد.');
            datatable.reload();
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
});

$('#product-multiple-delete-form, #product-multiple-unavailable-form').on(
    'submit',
    function (e) {
        e.preventDefault();

        $('#multiple-delete-modal').modal('hide');

        var formData = new FormData(this);
        var ids = datatable.checkbox().getSelectedId();

        ids.forEach(function (id) {
            formData.append('ids[]', id);
        });

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            success: function (data) {
                toastr.success('با موفقیت انجام شد.');
                datatable.reload();
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
);

$('#products-export-form').on('submit', function (e) {
    e.preventDefault();

    let formData = datatable.getDataSourceParam();
    let queryString = $.param(formData);

    let formData2 = new FormData(this);
    let queryString2 = new URLSearchParams(formData2).toString();

    let url = `${$(this).attr('action')}?${queryString}&${queryString2}`;

    window.open(url);
});

$('select[name="export_type"]').on('change', function () {
    $('.export-options').hide();
    $(`.export-options[data-export-type="${$(this).val()}"]`).show();
});
