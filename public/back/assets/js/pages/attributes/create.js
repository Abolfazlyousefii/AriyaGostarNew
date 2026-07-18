(function ($) {
    'use strict';

    var $form = $('#attribute-create-form');
    var $groupSelect = $form.find('select[name="attribute_group_id"]');
    var $bulkMode = $('#attribute-mode-bulk');
    var $singleMode = $('#attribute-mode-single');
    var $bulkFields = $('#bulk-fields');
    var $singleFields = $('#single-fields');
    var $colorSelectDiv = $('#color-select-div');
    var $colorModeHint = $('#color-mode-hint');
    var $submitButton = $('#attribute-submit-button');

    function selectedGroupType() {
        return $groupSelect.find('option:selected').data('type') || '';
    }

    function refreshFormMode() {
        var isColor = selectedGroupType() === 'color';

        if (isColor) {
            $bulkMode.prop('checked', false).prop('disabled', true);
            $singleMode.prop('checked', true);
            $colorModeHint.show();
        } else {
            $bulkMode.prop('disabled', false);
            $colorModeHint.hide();
        }

        var isBulk = $form.find('input[name="mode"]:checked').val() === 'bulk';

        $bulkFields.toggle(isBulk);
        $singleFields.toggle(!isBulk);
        $colorSelectDiv.toggle(!isBulk && isColor);

        $submitButton.text(isBulk ? 'ثبت ویژگی‌ها' : 'ایجاد ویژگی');
    }

    function firstValidationMessage(xhr) {
        if (!xhr.responseJSON || !xhr.responseJSON.errors) {
            return 'خطایی در ثبت اطلاعات رخ داد. دوباره تلاش کنید.';
        }

        var errors = xhr.responseJSON.errors;
        var firstKey = Object.keys(errors)[0];

        if (!firstKey || !errors[firstKey] || !errors[firstKey][0]) {
            return 'اطلاعات واردشده معتبر نیست.';
        }

        return errors[firstKey][0];
    }

    $form.validate({
        ignore: ':hidden:not(select)',
        rules: {
            attribute_group_id: {
                required: true
            },
            name: {
                required: function () {
                    return $form.find('input[name="mode"]:checked').val() === 'single';
                }
            },
            names: {
                required: function () {
                    return $form.find('input[name="mode"]:checked').val() === 'bulk';
                }
            }
        }
    });

    $groupSelect.on('change', refreshFormMode);
    $form.find('input[name="mode"]').on('change', refreshFormMode);

    refreshFormMode();

    $form.on('submit', function (event) {
        event.preventDefault();

        if (!$form.valid() || $form.data('disabled')) {
            return;
        }

        $form.data('disabled', true);
        $submitButton.prop('disabled', true);

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: new FormData(this),
            beforeSend: function (xhr) {
                block('#main-card');
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            },
            success: function () {
                window.location.href = BASE_URL + '/attributeGroups';
            },
            error: function (xhr) {
                var message = firstValidationMessage(xhr);

                if (typeof toastr !== 'undefined') {
                    toastr.error(message);
                } else {
                    alert(message);
                }
            },
            complete: function () {
                unblock('#main-card');
                $form.data('disabled', false);
                $submitButton.prop('disabled', false);
            },
            cache: false,
            contentType: false,
            processData: false
        });
    });
})(jQuery);
