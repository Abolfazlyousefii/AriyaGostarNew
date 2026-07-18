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

    function mountVariantGroups($modal) {
        var $productInfo = $modal.closest('.product-info-block');
        var $source = $productInfo.find('.aex-variants-modal-content');
        var $target = $modal.find('.aex-modal-groups-target');

        if (!$source.length || $source.data('mounted')) {
            return;
        }

        $source
            .removeClass('d-none')
            .removeAttr('aria-hidden')
            .data('mounted', true)
            .appendTo($target);
    }

    function filterVariants($modal, value) {
        var query = $.trim(String(value || '')).toLowerCase();
        var visibleCount = 0;

        $modal.find('.aex-modal-variant-group').each(function () {
            var $group = $(this);
            var groupVisibleCount = 0;

            $group.find('.aex-modal-variant-item').each(function () {
                var $item = $(this);
                var searchable = String($item.data('variant-search') || '').toLowerCase();
                var isVisible = query === '' || searchable.indexOf(query) !== -1;

                $item.toggle(isVisible);

                if (isVisible) {
                    groupVisibleCount++;
                    visibleCount++;
                }
            });

            $group.toggle(groupVisibleCount > 0);
        });

        $modal.find('.aex-variant-search-empty').prop('hidden', visibleCount > 0);
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

    $(document).on('show.bs.modal', '.aex-variants-modal', function () {
        var $modal = $(this);
        mountVariantGroups($modal);
        filterVariants($modal, $modal.find('.aex-variant-search').val());
    });

    $(document).on('shown.bs.modal', '.aex-variants-modal', function () {
        $(this).find('.aex-variant-search').trigger('focus');
    });

    $(document).on('input', '.aex-variant-search', function () {
        filterVariants($(this).closest('.aex-variants-modal'), $(this).val());
    });

    $(document).on('change', '.aex-variants-modal .variant-selector', function () {
        var $modal = $(this).closest('.aex-variants-modal');

        $modal.removeClass('fade').modal('hide');
    });

    $(document).on('hidden.bs.modal', '.aex-variants-modal', function () {
        var $modal = $(this);
        $modal.find('.aex-variant-search').val('');
        filterVariants($modal, '');
    });
})(jQuery);
