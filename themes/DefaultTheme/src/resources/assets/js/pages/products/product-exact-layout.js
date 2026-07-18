(function ($) {
    'use strict';

    function selectGalleryItem(index) {
        var $gallery = $('#product-gallery');
        var $thumb = $gallery.find('.aex-gallery-thumb[data-index="' + index + '"]');

        if (!$thumb.length) {
            return;
        }

        var image = $thumb.data('image');

        $gallery.find('.aex-gallery-thumb')
            .removeClass('is-active')
            .attr('aria-pressed', 'false');
        $thumb.addClass('is-active').attr('aria-pressed', 'true');

        $gallery.find('.aex-gallery-dot').removeClass('is-active');
        $gallery.find('.aex-gallery-dot[data-index="' + index + '"]').addClass('is-active');

        $('#aex-gallery-main-image').attr('src', image);
        $('#aex-gallery-main-link').attr('href', image);
    }

    $(document).on('click', '.aex-gallery-thumb, .aex-gallery-dot', function () {
        selectGalleryItem($(this).data('index'));
    });

    $(document).on('keydown', '.aex-gallery-thumb', function (event) {
        var $items = $('#product-gallery .aex-gallery-thumb');
        var currentIndex = $items.index(this);
        var targetIndex = currentIndex;

        if (event.key === 'ArrowDown' || event.key === 'ArrowLeft') {
            targetIndex = Math.min(currentIndex + 1, $items.length - 1);
        }

        if (event.key === 'ArrowUp' || event.key === 'ArrowRight') {
            targetIndex = Math.max(currentIndex - 1, 0);
        }

        if (targetIndex !== currentIndex) {
            event.preventDefault();
            $items.eq(targetIndex).focus().trigger('click');
        }
    });
})(jQuery);
