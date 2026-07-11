
(function ($) {
    'use strict';

    var $panel = $('#mobile-search-panel');
    var $openButtons = $('[data-mobile-search-open]');
    var $input = $('#mobile-search-input');
    var lastFocus = null;

    function setMobileCartBadge(count) {
        var value = parseInt(count, 10) || 0;
        $('[data-mobile-cart-count]').text(value).toggleClass('is-empty', value <= 0);
    }

    function openMobileSearch() {
        if (!$panel.length) {
            return;
        }
        lastFocus = document.activeElement;
        $panel.addClass('is-open').attr('aria-hidden', 'false');
        $openButtons.attr('aria-expanded', 'true');
        $('body').addClass('mobile-search-is-open');
        setTimeout(function () {
            $input.trigger('focus');
        }, 80);
    }

    function closeMobileSearch() {
        if (!$panel.length || !$panel.hasClass('is-open')) {
            return;
        }
        $panel.removeClass('is-open').attr('aria-hidden', 'true');
        $openButtons.attr('aria-expanded', 'false');
        $('body').removeClass('mobile-search-is-open');
        if (lastFocus && typeof lastFocus.focus === 'function') {
            lastFocus.focus();
        }
    }

    $(document)
        .off('click.mobileSearchOpen', '[data-mobile-search-open]')
        .on('click.mobileSearchOpen', '[data-mobile-search-open]', function (event) {
            event.preventDefault();
            openMobileSearch();
        })
        .off('click.mobileSearchClose', '[data-mobile-search-close]')
        .on('click.mobileSearchClose', '[data-mobile-search-close]', function (event) {
            event.preventDefault();
            closeMobileSearch();
        })
        .off('keyup.mobileSearch')
        .on('keyup.mobileSearch', function (event) {
            if (event.key === 'Escape' || event.keyCode === 27) {
                closeMobileSearch();
            }
        })
        .off('ajaxSuccess.mobileCartBadge')
        .on('ajaxSuccess.mobileCartBadge', function (event, xhr, settings, data) {
            if (data && typeof data.cart_count !== 'undefined') {
                setMobileCartBadge(data.cart_count);
            }
        });
})(jQuery);
