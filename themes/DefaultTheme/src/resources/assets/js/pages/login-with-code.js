jQuery('#login-with-code-form').validate({
    rules: {
        mobile: {
            required: true
        }
    }
});

$(document).ready(function () {
    $('#login-with-code-form').submit(function (e) {
        e.preventDefault();

        var form = $(this);

        if (!form.valid()) {
            return;
        }

        var formData = new FormData(this);

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            dataType: 'json',
            timeout: 20000,

            beforeSend: function (xhr) {
                block('.form-ui');

                xhr.setRequestHeader(
                    'X-CSRF-TOKEN',
                    $('meta[name="csrf-token"]').attr('content')
                );
            },

            success: function (data) {
                if (data && data.redirect) {
                    window.location.href = data.redirect;
                    return;
                }

                toastr.error(
                    'پاسخ انتقال از سرور دریافت نشد.'
                );
            },

            error: function (xhr, status) {
                if (status === 'timeout') {
                    toastr.error(
                        'زمان ارسال کد به پایان رسید. دوباره تلاش کنید.'
                    );
                    return;
                }

                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    var key = Object.keys(errors)[0];

                    if (key && errors[key] && errors[key][0]) {
                        toastr.error(errors[key][0]);
                        return;
                    }
                }

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    toastr.error(xhr.responseJSON.message);
                    return;
                }

                toastr.error(
                    'خطایی در ارسال درخواست رخ داد.'
                );
            },

            complete: function () {
                unblock('.form-ui');
            },

            cache: false,
            contentType: false,
            processData: false
        });
    });
});
