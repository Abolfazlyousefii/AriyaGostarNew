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
