jQuery('#checkout-form').validate({
    rules: {
        name: {
            required: true
        },
        mobile: {
            required: true,
            regex: '(09)[0-9]{9}'
        },
        // postal_code: {
        //     required: true,
        //     digits: true,
        //     maxlength: 10,
        //     minlength: 10
        // },
        province_id: {
            required: true
        },
        city_id: {
            required: true
        },
        address: {
            maxlength: 300
        },
        description: {
            maxlength: 1000
        },
        carrier_id: {
            required: true
        }
    }
});

$('#discount-create-form').validate({
    rules: {
        code: {
            required: true
        }
    }
});

$.validator.addMethod(
    'regex',
    function (value, element, regexp) {
        var re = new RegExp(regexp);
        return this.optional(element) || re.test(value);
    },
    'لطفا یک مقدار معتبر وارد کنید'
);

$(document).on('change', '#city, input[name="carrier_id"]', function () {
    getFinalPrice();
});

function getFinalPrice() {
    var carrier_id = $('input[name="carrier_id"]:checked').val();
    var city_id = $('#city').val();
    var action = $('#checkout-form').data('price-action');

    $.ajax({
        url: action,
        type: 'GET',
        data: {
            city_id: city_id,
            carrier_id: carrier_id
        },
        success: function (data) {
            $('#checkout-carrier-container').replaceWith(
                data.carriers_container
            );

            $('#checkout-sidebar').replaceWith(data.checkout_sidebar);
            $('[data-toggle="tooltip"]').tooltip();

            if ($('.container .sticky-sidebar').length) {
                $('.container .sticky-sidebar').theiaStickySidebar();
            }

            check_wallet();
        }
    });
}

$(document).ready(function () {
    $('input[name="carrier_id"]').trigger('change');

    if ($('input[name="reserve"]:checked').val() == undefined) {
        $('input[name="reserve"][value="no-reserve"]').prop('checked', true);
    }
});

$('#discount-create-form').submit(function (e) {
    e.preventDefault();

    var formData = new FormData(this);
    var form = $(this);

    if ($(this).valid()) {
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            success: function (data) {
                toastr.success('', 'کد تخفیف با موفقیت ثبت شد', {
                    positionClass: 'toast-bottom-left',
                    containerId: 'toast-bottom-left'
                });

                setTimeout(function () {
                    location.reload();
                }, 1000);
            },

            beforeSend: function (xhr) {
                block(form);
            },
            complete: function () {
                unblock(form);
            },

            cache: false,
            contentType: false,
            processData: false
        });
    }
});

function walletMoney(value) {
    value = Math.max(
        0,
        parseInt(value || 0, 10)
    );

    return value
        .toString()
        .replace(/\B(?=(\d{3})+(?!\d))/g, ',') +
        ' تومان';
}

function check_wallet() {
    var finalPrice =
        parseInt(
            $('#final-price').data('value') || 0,
            10
        );

    var walletBalance =
        parseInt(
            $('#wallet-balance').data('value') || 0,
            10
        );

    var useWallet =
        $('#use-wallet').length &&
        $('#use-wallet').is(':checked');

    var walletUsed = 0;
    var gatewayPayable = finalPrice;

    if (useWallet && walletBalance > 0) {
        walletUsed = Math.min(
            walletBalance,
            finalPrice
        );

        gatewayPayable = Math.max(
            0,
            finalPrice - walletUsed
        );
    }

    $('#wallet-used-amount')
        .text(walletMoney(walletUsed));

    $('#gateway-payable-amount')
        .text(walletMoney(gatewayPayable));

    if (!useWallet) {
        $('#wallet-use-summary').hide();
        $('#wallet-full-payment-message').hide();

        return;
    }

    $('#wallet-use-summary').show();

    if (
        finalPrice > 0 &&
        gatewayPayable === 0
    ) {
        $('#wallet-full-payment-message').show();

        $('#wallet-use-summary')
            .removeClass('alert-info')
            .addClass('alert-success');
    } else {
        $('#wallet-full-payment-message').hide();

        $('#wallet-use-summary')
            .removeClass('alert-success')
            .addClass('alert-info');
    }
}

$(document).on(
    'change',
    '#use-wallet, input[name="gateway"]',
    function () {
        check_wallet();
    }
);

check_wallet();
$(document).on('change', 'input[name="reserve"]', function () {
    var reserve = $('input[name="reserve"]:checked').val();

    $.ajax({
        url: $('#order-reserve-container').data('action'),
        type: 'GET',
        data: {
            reserve
        },
        success: function (data) {
            window.location.reload();
        },
        beforeSend: function (xhr) {
            block('#checkout-form');

            xhr.setRequestHeader(
                'X-CSRF-TOKEN',
                $('meta[name="csrf-token"]').attr('content')
            );
        },
        complete: function () {
            unblock('#checkout-form');
        }
    });
});
$("input[name='gateway']").click(function () {
    var selectedRadio = $("input[name='gateway']:checked").attr('id');
    if (selectedRadio == 'toman') {
        $('#discount-create-form').hide();
    } else {
        $('#discount-create-form').show();
    }
});
