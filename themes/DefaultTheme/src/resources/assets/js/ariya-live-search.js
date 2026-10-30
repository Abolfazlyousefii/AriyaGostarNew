(function ($) {
    'use strict';

    var selector = 'header.main-header .search-area form.search input';
    var $input = $(selector);

    if (!$input.length) {
        return;
    }

    // Remove the legacy keyup handler from scripts.js so only one live request runs.
    $input.off('keyup');

    var timer = null;
    var xhr = null;
    var requestSeq = 0;

    function closeResults() {
        $('header.main-header .search-area form.search .search-result').removeClass('open');
        $('header.main-header .search-area form.search .close-search-result').removeClass('show');
        $('header.main-header .search-area form.search .search-result ul').empty();
    }

    function escapeHtml(value) {
        return $('<div>').text(value == null ? '' : String(value)).html();
    }

    $input.on('input', function () {
        var q = $.trim($(this).val());
        clearTimeout(timer);

        if (xhr && xhr.readyState !== 4) {
            xhr.abort();
        }

        if (!q) {
            closeResults();
            return;
        }

        timer = setTimeout(function () {
            var seq = ++requestSeq;

            xhr = $.ajax({
                url: '/search',
                type: 'POST',
                data: { q: q },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (data) {
                    if (seq !== requestSeq) {
                        return;
                    }

                    var $box = $('header.main-header .search-area form.search .search-result');
                    var $list = $box.find('ul');
                    var $close = $('header.main-header .search-area form.search .close-search-result');

                    $list.empty();

                    if (!Array.isArray(data) || !data.length) {
                        $box.removeClass('open');
                        $close.removeClass('show');
                        return;
                    }

                    data.forEach(function (item) {
                        var stockBadge = item.in_stock
                            ? '<span class="badge badge-success mr-2">موجود</span>'
                            : '<span class="badge badge-light mr-2">ناموجود</span>';

                        // Guest responses omit price completely. Check the
                        // server-provided authorization flag before rendering.
                        var priceMarkup = item.can_view_price === true &&
                            item.price !== null && typeof item.price !== 'undefined'
                            ? '<span class="ml-3">' + escapeHtml(item.price) + '</span>'
                            : '<span class="ml-3 text-muted">برای مشاهده قیمت وارد شوید</span>';

                        $list.append(
                            '<li class="d-flex p-2 px-3">' +
                                '<div><img src="' + escapeHtml(item.image) + '" alt="' + escapeHtml(item.title) + '"></div>' +
                                '<div class="search-result-text">' +
                                    '<div class="search-result-body">' +
                                        '<a class="mr-3" href="' + escapeHtml(item.link) + '">' + escapeHtml(item.title) + '</a>' +
                                        '<span class="text-muted mr-3">' + escapeHtml(item.category) + '</span>' +
                                        stockBadge +
                                    '</div>' +
                                '</div>' +
                                '<div class="box-search-price">' +
                                    priceMarkup +
                                    '<a href="' + escapeHtml(item.link) + '" class="d-flex align-items-center">' +
                                        '<i class="mdi mdi-eye text-center search-icon"></i>' +
                                    '</a>' +
                                '</div>' +
                            '</li>'
                        );
                    });

                    $box.addClass('open');
                    $close.addClass('show');
                },
                error: function (jqXHR, textStatus) {
                    if (textStatus !== 'abort') {
                        closeResults();
                    }
                }
            });
        }, 250);
    });
})(jQuery);
