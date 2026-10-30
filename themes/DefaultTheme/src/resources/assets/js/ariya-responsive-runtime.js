/*
 * Ariya responsive runtime compatibility layer
 *
 * Production may serve an older compiled js/all.js while local debug loads
 * source custom.js. This is the current mobile navigation/cart/search runtime.
 * The source code already contains its own duplicate-initialization guard.
 */

(function () {
    'use strict';

    function initAriaMobileNavigation() {
        var root = document.documentElement;

        if (root.getAttribute('data-ariya-mobile-navigation-ready') === '1') {
            return;
        }

        root.setAttribute('data-ariya-mobile-navigation-ready', '1');

        var searchPanel = document.getElementById('mobile-search-panel');
        var searchInput = document.getElementById('mobile-product-search-input');
        var cartPanel = document.getElementById('mobile-cart-panel');
        var cartContent = document.querySelector('[data-mobile-cart-content]');
        var lastSearchFocusedElement = null;
        var lastCartFocusedElement = null;

        function getSearchTriggers() {
            return document.querySelectorAll('[data-mobile-search-open]');
        }

        function getCartTriggers() {
            return document.querySelectorAll('[data-mobile-cart-open]');
        }

        function setTriggersExpanded(triggers, expanded) {
            triggers.forEach(function (trigger) {
                trigger.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            });
        }

        function closeMobileSearch(restoreFocus) {
            if (!searchPanel || !searchPanel.classList.contains('is-open')) {
                return;
            }

            searchPanel.classList.remove('is-open');
            searchPanel.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('mobile-search-open');
            setTriggersExpanded(getSearchTriggers(), false);

            if (restoreFocus !== false && lastSearchFocusedElement && typeof lastSearchFocusedElement.focus === 'function') {
                window.setTimeout(function () {
                    lastSearchFocusedElement.focus();
                }, 30);
            }
        }

        function closeMobileCart(restoreFocus) {
            if (!cartPanel || !cartPanel.classList.contains('is-open')) {
                return;
            }

            cartPanel.classList.remove('is-open');
            cartPanel.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('mobile-cart-open');
            setTriggersExpanded(getCartTriggers(), false);

            if (restoreFocus !== false && lastCartFocusedElement && typeof lastCartFocusedElement.focus === 'function') {
                window.setTimeout(function () {
                    lastCartFocusedElement.focus();
                }, 30);
            }
        }

        function openMobileSearch(trigger) {
            if (!searchPanel || window.innerWidth >= 992) {
                return;
            }

            closeMobileCart(false);
            lastSearchFocusedElement = trigger || document.activeElement;
            searchPanel.classList.add('is-open');
            searchPanel.setAttribute('aria-hidden', 'false');
            document.body.classList.add('mobile-search-open');
            setTriggersExpanded(getSearchTriggers(), true);

            window.setTimeout(function () {
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select();
                }
            }, 230);
        }

        function createEmptyCartMessage(message) {
            var paragraph = document.createElement('p');
            paragraph.className = 'mobile-cart-panel__empty';
            paragraph.textContent = message || 'سبد خرید شما خالی است.';
            return paragraph;
        }

        function syncMobileCartPanel() {
            if (!cartContent) {
                return;
            }

            var sourceMenu = document.querySelector('#cart-list-item .dropdown-menu.cart');
            cartContent.innerHTML = '';

            if (!sourceMenu) {
                cartContent.appendChild(createEmptyCartMessage());
                return;
            }

            var sourceList = sourceMenu.querySelector('.dropdown-list-icons');

            if (sourceList && sourceList.children.length) {
                var clonedList = sourceList.cloneNode(true);
                clonedList.className = 'mobile-cart-panel__products';

                clonedList.querySelectorAll('.dropdown-item').forEach(function (item) {
                    item.classList.add('mobile-cart-panel__product');
                });

                clonedList.querySelectorAll('.dropdown-item-icon').forEach(function (icon) {
                    icon.classList.add('mobile-cart-panel__product-image');
                });

                clonedList.querySelectorAll('.cart-title').forEach(function (title) {
                    title.classList.add('mobile-cart-panel__product-info');
                });

                cartContent.appendChild(clonedList);

                var sourceTotal = sourceMenu.querySelector('.dropdown-footer .dt-sl');
                if (sourceTotal) {
                    var clonedTotal = sourceTotal.cloneNode(true);
                    clonedTotal.classList.add('mobile-cart-panel__total');
                    cartContent.appendChild(clonedTotal);
                }

                return;
            }

            var sourceEmpty = sourceMenu.querySelector('p');
            cartContent.appendChild(createEmptyCartMessage(sourceEmpty ? sourceEmpty.textContent.trim() : ''));
        }

        function openMobileCart(trigger) {
            if (!cartPanel || window.innerWidth >= 992) {
                return;
            }

            closeMobileSearch(false);
            syncMobileCartPanel();
            lastCartFocusedElement = trigger || document.activeElement;
            cartPanel.classList.add('is-open');
            cartPanel.setAttribute('aria-hidden', 'false');
            document.body.classList.add('mobile-cart-open');
            setTriggersExpanded(getCartTriggers(), true);

            window.setTimeout(function () {
                var closeButton = cartPanel.querySelector('[data-mobile-cart-close]:not([tabindex="-1"])');
                if (closeButton) {
                    closeButton.focus();
                }
            }, 230);
        }

        function syncMobileCartBadge() {
            var sourceCount = document.querySelector('#cart-list-item .count');
            var badge = document.querySelector('[data-mobile-cart-count]');

            if (!badge) {
                return;
            }

            var rawCount = sourceCount ? sourceCount.textContent : '0';
            var normalizedCount = parseInt(String(rawCount).replace(/[^0-9]/g, ''), 10) || 0;

            badge.textContent = normalizedCount > 99 ? '99+' : String(normalizedCount);
            badge.hidden = normalizedCount < 1;
            badge.setAttribute('aria-label', normalizedCount + ' کالا در سبد خرید');
        }

        function syncMobileCart() {
            syncMobileCartBadge();
            syncMobileCartPanel();
        }

        function trapPanelFocus(panel, event) {
            if (!panel || !panel.classList.contains('is-open')) {
                return false;
            }

            var focusableElements = panel.querySelectorAll(
                'button:not([disabled]):not([tabindex="-1"]), input:not([disabled]), a[href], [tabindex]:not([tabindex="-1"])'
            );

            if (!focusableElements.length) {
                return false;
            }

            var firstFocusable = focusableElements[0];
            var lastFocusable = focusableElements[focusableElements.length - 1];

            if (event.shiftKey && document.activeElement === firstFocusable) {
                event.preventDefault();
                lastFocusable.focus();
            } else if (!event.shiftKey && document.activeElement === lastFocusable) {
                event.preventDefault();
                firstFocusable.focus();
            }

            return true;
        }

        document.addEventListener('click', function (event) {
            var target = event.target;

            if (!(target instanceof Element)) {
                return;
            }

            var searchOpenTrigger = target.closest('[data-mobile-search-open]');
            var searchCloseTrigger = target.closest('[data-mobile-search-close]');
            var cartOpenTrigger = target.closest('[data-mobile-cart-open]');
            var cartCloseTrigger = target.closest('[data-mobile-cart-close]');

            if (searchOpenTrigger) {
                event.preventDefault();
                openMobileSearch(searchOpenTrigger);
                return;
            }

            if (cartOpenTrigger) {
                event.preventDefault();
                openMobileCart(cartOpenTrigger);
                return;
            }

            if (searchCloseTrigger) {
                event.preventDefault();
                closeMobileSearch(true);
                return;
            }

            if (cartCloseTrigger) {
                event.preventDefault();
                closeMobileCart(true);
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                if (cartPanel && cartPanel.classList.contains('is-open')) {
                    closeMobileCart(true);
                } else {
                    closeMobileSearch(true);
                }
                return;
            }

            if (event.key !== 'Tab') {
                return;
            }

            if (trapPanelFocus(cartPanel, event)) {
                return;
            }

            trapPanelFocus(searchPanel, event);
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth >= 992) {
                closeMobileSearch(false);
                closeMobileCart(false);
            }
        });

        syncMobileCart();

        if (window.jQuery) {
            window.jQuery(document)
                .off('ajaxComplete.ariyaMobileCart')
                .on('ajaxComplete.ariyaMobileCart', function () {
                    window.setTimeout(syncMobileCart, 30);
                });
        }

        document.addEventListener('ariya:cart-updated', syncMobileCart);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAriaMobileNavigation, { once: true });
    } else {
        initAriaMobileNavigation();
    }
})();

/* ARIYA_MOBILE_CATALOG_FILTER_HOTFIX_20260807 */
(function () {
    'use strict';

    var root = document.documentElement;

    if (root.getAttribute('data-ariya-mobile-catalog-hotfix') === '1') {
        return;
    }

    root.setAttribute('data-ariya-mobile-catalog-hotfix', '1');

    function isMobileCatalog() {
        return window.innerWidth < 992;
    }

    function getFilter() {
        return document.getElementById('ariya-catalog-filter');
    }

    function setFilterState(open) {
        var filter = getFilter();

        if (!filter) {
            return;
        }

        if (open && !isMobileCatalog()) {
            return;
        }

        document.body.classList.toggle('ariya-catalog-filter-open', !!open);
        filter.setAttribute('aria-hidden', open ? 'false' : 'true');
    }

    document.addEventListener('click', function (event) {
        if (!(event.target instanceof Element)) {
            return;
        }

        var openButton = event.target.closest('[data-catalog-filter-open]');
        var closeButton = event.target.closest('[data-catalog-filter-close]');

        if (openButton) {
            event.preventDefault();
            setFilterState(true);
            return;
        }

        if (closeButton) {
            event.preventDefault();
            setFilterState(false);
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && document.body.classList.contains('ariya-catalog-filter-open')) {
            setFilterState(false);
        }
    });

    window.addEventListener('resize', function () {
        if (!isMobileCatalog()) {
            setFilterState(false);
        }
    });

    /* Always start closed on a real mobile page load. */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            setFilterState(false);
        }, { once: true });
    } else {
        setFilterState(false);
    }
})();

/* ARIYA-MOBILE-LIVE-SEARCH-SAME-AS-DESKTOP */
(function () {
    'use strict';

    function initAriyaMobileLiveSearch() {
        var panel = document.getElementById('mobile-search-panel');
        var input = document.getElementById('mobile-product-search-input');

        if (!panel || !input || input.getAttribute('data-ariya-live-search-ready') === '1') {
            return;
        }

        input.setAttribute('data-ariya-live-search-ready', '1');

        var form = input.closest('form');
        var sheet = panel.querySelector('.mobile-search-panel__sheet') || panel;
        var timer = null;
        var controller = null;
        var requestNumber = 0;

        var results = document.createElement('div');
        results.className = 'mobile-live-search';
        results.setAttribute('aria-live', 'polite');
        results.setAttribute('aria-label', 'نتایج جستجوی زنده');
        results.hidden = true;

        var list = document.createElement('div');
        list.className = 'mobile-live-search__list';
        results.appendChild(list);

        if (form && form.parentNode) {
            form.insertAdjacentElement('afterend', results);
        } else {
            sheet.appendChild(results);
        }

        function clearResults() {
            list.innerHTML = '';
            results.hidden = true;
            results.classList.remove('is-open', 'is-loading');
        }

        function escapeHtml(value) {
            var div = document.createElement('div');
            div.textContent = value == null ? '' : String(value);
            return div.innerHTML;
        }

        function renderItems(items) {
            list.innerHTML = '';

            if (!Array.isArray(items) || !items.length) {
                results.hidden = false;
                results.classList.add('is-open');
                list.innerHTML =
                    '<div class="mobile-live-search__empty">محصولی با این عبارت پیدا نشد.</div>';
                return;
            }

            items.forEach(function (item) {
                var row = document.createElement('a');
                row.className = 'mobile-live-search__item';
                row.href = item.link || '#';

                row.innerHTML =
                    '<span class="mobile-live-search__image">' +
                        '<img src="' + escapeHtml(item.image) + '" alt="' + escapeHtml(item.title) + '">' +
                    '</span>' +
                    '<span class="mobile-live-search__content">' +
                        '<strong>' + escapeHtml(item.title) + '</strong>' +
                        '<small>' + escapeHtml(item.category || '') + '</small>' +
                    '</span>' +
                    '<span class="mobile-live-search__price">' +
                        escapeHtml(item.price || '') +
                    '</span>';

                list.appendChild(row);
            });

            results.hidden = false;
            results.classList.add('is-open');
        }

        input.addEventListener('input', function () {
            var q = input.value.trim();

            window.clearTimeout(timer);

            if (controller) {
                controller.abort();
                controller = null;
            }

            if (!q || window.innerWidth >= 992) {
                requestNumber++;
                clearResults();
                return;
            }

            timer = window.setTimeout(function () {
                var currentRequest = ++requestNumber;
                controller = new AbortController();

                results.hidden = false;
                results.classList.add('is-open', 'is-loading');

                var csrf = document.querySelector('meta[name="csrf-token"]');
                var body = new URLSearchParams();
                body.set('q', q);

                fetch('/search', {
                    method: 'POST',
                    credentials: 'same-origin',
                    signal: controller.signal,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf ? csrf.getAttribute('content') : ''
                    },
                    body: body.toString()
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Search request failed: ' + response.status);
                        }

                        return response.json();
                    })
                    .then(function (data) {
                        if (currentRequest !== requestNumber) {
                            return;
                        }

                        results.classList.remove('is-loading');
                        renderItems(data);
                    })
                    .catch(function (error) {
                        if (error && error.name === 'AbortError') {
                            return;
                        }

                        if (currentRequest === requestNumber) {
                            clearResults();
                        }
                    });
            }, 300);
        });

        panel.addEventListener('click', function (event) {
            if (event.target.closest('[data-mobile-search-close]')) {
                clearResults();
            }
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth >= 992) {
                clearResults();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAriyaMobileLiveSearch, { once: true });
    } else {
        initAriyaMobileLiveSearch();
    }
})();
