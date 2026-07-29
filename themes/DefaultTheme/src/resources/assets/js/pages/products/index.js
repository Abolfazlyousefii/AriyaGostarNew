(function () {
    'use strict';

    var body = document.body;
    var filter = document.getElementById('ariya-catalog-filter');
    var form = document.getElementById('ariya-products-filter-form');
    var hiddenSort = form ? form.querySelector('[data-catalog-sort-hidden]') : null;
    var openButtons = document.querySelectorAll('[data-catalog-filter-open]');
    var closeButtons = document.querySelectorAll('[data-catalog-filter-close]');
    var sortSelects = document.querySelectorAll('[data-catalog-sort-select]');
    var customPriceInputs = document.querySelectorAll('[data-custom-price-input]');

    function openFilter() {
        if (!filter) {
            return;
        }

        body.classList.add('ariya-catalog-filter-open');
        filter.setAttribute('aria-hidden', 'false');
    }

    function closeFilter() {
        body.classList.remove('ariya-catalog-filter-open');

        if (filter) {
            filter.setAttribute('aria-hidden', 'true');
        }
    }

    openButtons.forEach(function (button) {
        button.addEventListener('click', openFilter);
    });

    closeButtons.forEach(function (button) {
        button.addEventListener('click', closeFilter);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeFilter();
        }
    });

    sortSelects.forEach(function (select) {
        select.addEventListener('change', function () {
            if (!form || !hiddenSort) {
                return;
            }

            hiddenSort.value = select.value;

            sortSelects.forEach(function (otherSelect) {
                otherSelect.value = select.value;
            });

            form.submit();
        });
    });

    customPriceInputs.forEach(function (input) {
        input.addEventListener('input', function () {
            var selectedLevel = form ? form.querySelector('input[name="price_level"]:checked') : null;

            if (selectedLevel) {
                selectedLevel.checked = false;
            }
        });
    });

    if (form) {
        form.addEventListener('submit', function () {
            if (hiddenSort && !hiddenSort.value) {
                hiddenSort.value = 'latest';
            }
        });
    }

    if (filter) {
        filter.setAttribute('aria-hidden', window.innerWidth < 992 ? 'true' : 'false');
    }

    window.addEventListener('resize', function () {
        if (window.innerWidth >= 992) {
            closeFilter();
            if (filter) {
                filter.setAttribute('aria-hidden', 'false');
            }
        }
    });
})();
