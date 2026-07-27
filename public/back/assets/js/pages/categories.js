$(document).ready(function () {
    'use strict';

    function buildAdminUrl(path) {
        return BASE_URL + '/admin' + (adminRoutePrefix ? '/' + adminRoutePrefix : '') + path;
    }

    function escapeHtml(value) {
        return $('<div>').text(value == null ? '' : String(value)).html();
    }

    function categoryLinks(slug) {
        return $('[data-category]').filter(function () {
            return String($(this).attr('data-category')) === String(slug);
        });
    }

    function notify(message, type) {
        type = type || 'success';

        if (typeof toastr !== 'undefined' && typeof toastr[type] === 'function') {
            toastr[type](message);
            return;
        }

        var feedback = $('#create-category-feedback');
        feedback
            .removeClass('is-success is-error')
            .addClass(type === 'error' ? 'is-error' : 'is-success')
            .text(message);
    }

    function validationMessage(xhr) {
        if (xhr.responseJSON && xhr.responseJSON.errors) {
            var errors = xhr.responseJSON.errors;
            var firstKey = Object.keys(errors)[0];

            if (firstKey && errors[firstKey] && errors[firstKey][0]) {
                return errors[firstKey][0];
            }
        }

        if (xhr.responseJSON && xhr.responseJSON.message) {
            return xhr.responseJSON.message;
        }

        return 'ثبت اطلاعات انجام نشد. دوباره تلاش کنید.';
    }

    function iconMarkup(data) {
        if (data.menu_icon_url) {
            return '<img src="' + escapeHtml(data.menu_icon_url) + '" alt="">';
        }

        return '<i class="feather icon-grid"></i>';
    }

    function categoryContent(data) {
        return '' +
            '<div class="category-list-main">' +
                '<span class="category-list-icon" data-category-icon="' + escapeHtml(data.slug) + '">' +
                    iconMarkup(data) +
                '</span>' +
                '<span class="category-title">' + escapeHtml(data.title) + '</span>' +
            '</div>' +
            '<div class="category-actions dd-nodrag">' +
                '<a data-category="' + escapeHtml(data.slug) + '" class="edit-category dd-nodrag" href="javascript:void(0)">' +
                    '<i class="fa fa-pencil text-info"></i> ویرایش' +
                '</a>' +
                '<a data-category="' + escapeHtml(data.slug) + '" class="delete-category dd-nodrag" href="javascript:void(0)" data-toggle="modal" data-target="#modal-delete">' +
                    '<i class="fa fa-trash text-danger"></i> حذف' +
                '</a>' +
            '</div>';
    }

    function setFilePreview(input, previewSelector) {
        var file = input.files && input.files[0] ? input.files[0] : null;
        var wrapper = $(input).closest('.custom-file');
        var label = wrapper.find('.custom-file-label');
        var preview = $(previewSelector);

        if (!file) {
            label.text('انتخاب آیکن');
            preview.removeClass('has-image').find('img').attr('src', '');
            return;
        }

        label.text(file.name);

        if (!file.type.match(/^image\//)) {
            return;
        }

        var reader = new FileReader();
        reader.onload = function (event) {
            preview.addClass('has-image').find('img').attr('src', event.target.result);
        };
        reader.readAsDataURL(file);
    }

    $('.dd').nestable({
        maxDepth: typeof maxDepth !== 'undefined' ? maxDepth : 10,
        forceFallback: true,
        callback: function () {
            if (JSON.stringify($('.dd').nestable('serialize')) !== JSON.stringify(categories)) {
                saveChanges();
            }
        }
    });

    var categories = $('.dd').nestable('serialize');

    $(document).on('change', '#create-menu-icon', function () {
        setFilePreview(this, '#create-menu-icon-preview');
    });

    $(document).on('change', '#menu_icon', function () {
        setFilePreview(this, '#edit-menu-icon-preview');
    });

    $(document).on('change', '.custom-file-input', function () {
        var fileName = this.files && this.files[0] ? this.files[0].name : 'انتخاب فایل';
        $(this).siblings('.custom-file-label').text(fileName);
    });

    $('#create-category').submit(function (event) {
        event.preventDefault();

        var form = $(this);
        var submitButton = form.find('button[type="submit"]');
        var formData = new FormData(this);

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            success: function (data) {
                $('.dd-empty').remove();
                $('.dd').nestable('add', {
                    id: data.id,
                    content: categoryContent(data)
                });

                form[0].reset();
                form.find('.custom-file-label').text('انتخاب آیکن');
                $('#create-menu-icon-preview')
                    .removeClass('has-image')
                    .find('img')
                    .attr('src', '');

                categories = $('.dd').nestable('serialize');
                notify('دسته‌بندی و آیکن آن با موفقیت ثبت شد.', 'success');
            },
            error: function (xhr) {
                notify(validationMessage(xhr), 'error');
            },
            beforeSend: function (xhr) {
                submitButton.prop('disabled', true);
                block('#main-block');
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            },
            complete: function () {
                submitButton.prop('disabled', false);
                unblock('#main-block');
            },
            cache: false,
            contentType: false,
            processData: false
        });
    });

    $(document).on('click', '.delete-category', function () {
        $('#delete-form').attr('action', deleteRouteBase + '/' + $(this).data('category'));
    });

    $(document).on('click', '.edit-category', function () {
        var category = $(this).data('category');

        $.ajax({
            url: buildAdminUrl('/categories/' + category + '/edit'),
            type: 'GET',
            success: function (data) {
                $('#edit-form')
                    .attr('action', buildAdminUrl('/categories/' + category))
                    .data('category', category);

                $('#modal-edit .modal-body').html(data);
                $('#modal-edit').modal('show');

                $('.tags').tagsInput({
                    defaultText: 'افزودن',
                    width: '100%',
                    autocomplete_url: buildAdminUrl('/get-tags')
                });

                if (typeof CKEDITOR !== 'undefined') {
                    if (CKEDITOR.instances['category-description']) {
                        CKEDITOR.instances['category-description'].destroy(true);
                    }
                    CKEDITOR.replace('category-description');
                }

                $('#filter_type').trigger('change');
            },
            error: function (xhr) {
                notify(validationMessage(xhr), 'error');
            },
            beforeSend: function () {
                block('#main-block');
            },
            complete: function () {
                unblock('#main-block');
            }
        });
    });

    $('#modal-edit').on('shown.bs.modal', function () {
        $('#edit-title').focus();
    });

    $('#edit-form').submit(function (event) {
        event.preventDefault();

        var form = $(this);
        var submitButton = form.find('button[type="submit"]');
        var oldSlug = String(form.data('category'));
        var formData = new FormData(this);

        if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances['category-description']) {
            formData.set('description', CKEDITOR.instances['category-description'].getData());
        }

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            success: function (data) {
                var links = categoryLinks(oldSlug);
                var handle = links.first().closest('.dd-handle');

                handle.find('.category-title').text(data.title);
                handle.find('.category-status-badge').remove();

                if (!data.published) {
                    handle.find('.category-list-main').append('<span class="category-status-badge">منتشر نشده</span>');
                }

                var icon = handle.find('.category-list-icon');
                icon.html(iconMarkup(data));
                icon.attr('data-category-icon', data.slug);

                links
                    .attr('data-category', data.slug)
                    .data('category', data.slug);

                $('#modal-edit').modal('hide');
                notify('تغییرات دسته‌بندی و آیکن با موفقیت ذخیره شد.', 'success');
            },
            error: function (xhr) {
                notify(validationMessage(xhr), 'error');
            },
            beforeSend: function (xhr) {
                submitButton.prop('disabled', true);
                block('#modal-edit .modal-content');
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            },
            complete: function () {
                submitButton.prop('disabled', false);
                unblock('#modal-edit .modal-content');
            },
            cache: false,
            contentType: false,
            processData: false
        });
    });

    function saveChanges() {
        var serialized = $('.dd').nestable('serialize');

        if (!serialized.length) {
            return;
        }

        $.ajax({
            url: buildAdminUrl('/categories/sort'),
            type: 'POST',
            data: {
                categories: serialized,
                type: $('input[name="type"]').first().val()
            },
            success: function () {
                categories = $('.dd').nestable('serialize');
                notify('ترتیب و ساختار دسته‌بندی‌ها ذخیره شد.', 'success');
            },
            error: function (xhr) {
                notify(validationMessage(xhr), 'error');
            },
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
                $('#save-changes').show();
            },
            complete: function () {
                $('#save-changes').hide();
            }
        });
    }

    window.onbeforeunload = function () {
        if (!$('#save-changes').is(':hidden')) {
            return 'آیا مطمئن هستید؟';
        }
    };

    $(document).on('click', '#generate-category-slug', function (event) {
        event.preventDefault();

        var title = $('#edit-title').val() || $('input[name="meta_title"]').val();

        $.ajax({
            url: buildAdminUrl('/category/slug'),
            type: 'POST',
            data: { title: title },
            success: function (data) {
                $('#slug').val(data.slug);
            },
            error: function (xhr) {
                notify(validationMessage(xhr), 'error');
            },
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
                $('#slug-spinner').show();
            },
            complete: function () {
                $('#slug-spinner').hide();
            }
        });
    });

    $(document).on('change', '#filter_type', function () {
        $('#filter_id').prop('disabled', $(this).val() !== 'filterId');
    });
});
