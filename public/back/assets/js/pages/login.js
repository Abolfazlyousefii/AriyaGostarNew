$(document).ready(function() {

    $('#login-form').submit(function(e) {
        e.preventDefault();


        var formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            success: function(data) {
                if ($.isEmptyObject(data.error)) {
                    window.location.href = window.redirect_url || window.BASE_URL || '/admin';
                }
            },
            beforeSend: function(xhr) {
                block('#main-card');
                xhr.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
            },
            complete: function() {
                unblock('#main-card');
            },

            cache: false,
            contentType: false,
            processData: false
        });
    });
});
