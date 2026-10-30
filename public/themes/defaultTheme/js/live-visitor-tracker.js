(function () {
    'use strict';

    if (window.__ARIYA_LIVE_VISITOR_STARTED__) {
        return;
    }

    window.__ARIYA_LIVE_VISITOR_STARTED__ = true;

    var config = window.ARIYA_LIVE_ANALYTICS || {};
    var endpoint = config.endpoint;

    if (!endpoint || typeof window.fetch !== 'function') {
        return;
    }

    var storageKey = 'ariya_live_visitor_token';
    var heartbeatTimer = null;
    var lastPath = window.location.href;

    function createToken() {
        if (window.crypto && typeof window.crypto.randomUUID === 'function') {
            return window.crypto.randomUUID();
        }

        return 'lv-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2) + Math.random().toString(36).slice(2);
    }

    function getToken() {
        try {
            var token = window.localStorage.getItem(storageKey);

            if (!token) {
                token = createToken();
                window.localStorage.setItem(storageKey, token);
            }

            return token;
        } catch (error) {
            return createToken();
        }
    }

    var visitorToken = getToken();

    function textFrom(selectors) {
        for (var i = 0; i < selectors.length; i += 1) {
            var element = document.querySelector(selectors[i]);

            if (element && element.textContent && element.textContent.trim()) {
                return element.textContent.trim().replace(/\s+/g, ' ').slice(0, 255);
            }
        }

        return null;
    }

    function attributeFrom(selectors, attribute) {
        for (var i = 0; i < selectors.length; i += 1) {
            var element = document.querySelector(selectors[i]);

            if (element) {
                var value = element.getAttribute(attribute);

                if (value) {
                    return value;
                }
            }
        }

        return null;
    }

    function queryValue(name) {
        try {
            return new URL(window.location.href).searchParams.get(name);
        } catch (error) {
            return null;
        }
    }

    function detectEventType() {
        var path = window.location.pathname.toLowerCase();
        var searchTerm = queryValue('q') || queryValue('query') || queryValue('search');

        if (path.indexOf('/checkout') !== -1 || path.indexOf('/order/create') !== -1) {
            return 'checkout_start';
        }

        if (path.indexOf('/cart') !== -1) {
            return 'cart_update';
        }

        if (searchTerm || path.indexOf('/search') !== -1) {
            return 'search';
        }

        if (document.querySelector('meta[name="product_id"]') || path.indexOf('/products/') !== -1 || path.indexOf('/product/') !== -1) {
            return 'product_view';
        }

        return 'page_view';
    }

    function detectDeviceLabel() {
        var ua = navigator.userAgent || '';
        var patterns = [
            [/iPhone/i, 'iPhone'],
            [/iPad/i, 'iPad'],
            [/SM-[A-Z0-9-]+/i, null],
            [/Redmi[^;)]*/i, null],
            [/Pixel[^;)]*/i, null],
            [/Windows/i, 'Windows PC'],
            [/Macintosh/i, 'Mac'],
            [/Linux/i, 'Linux PC']
        ];

        for (var i = 0; i < patterns.length; i += 1) {
            var match = ua.match(patterns[i][0]);

            if (match) {
                return patterns[i][1] || match[0];
            }
        }

        return 'دستگاه ناشناس';
    }

    function buildPayload(eventType) {
        var productId = attributeFrom(['meta[name="product_id"]'], 'content')
            || attributeFrom(['[data-product-id]'], 'data-product-id')
            || attributeFrom(['[data-product]'], 'data-product');

        if (productId && !/^\d+$/.test(String(productId))) {
            productId = null;
        }

        var categoryId = attributeFrom(['meta[name="category_id"]'], 'content')
            || attributeFrom(['[data-category-id]'], 'data-category-id');

        if (categoryId && !/^\d+$/.test(String(categoryId))) {
            categoryId = null;
        }

        var connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;

        return {
            visitor_token: visitorToken,
            event_type: eventType,
            url: window.location.href,
            path: window.location.pathname + window.location.search,
            title: document.title || null,
            referrer: document.referrer || null,
            product_id: productId ? Number(productId) : null,
            product_title: eventType === 'product_view'
                ? textFrom(['.product-info h1', '.product-title h1', 'main h1', 'h1'])
                : null,
            category_id: categoryId ? Number(categoryId) : null,
            category_title: textFrom(['.breadcrumb-item.active', '.category-title', 'main h1']),
            search_term: queryValue('q') || queryValue('query') || queryValue('search') || null,
            timezone: (window.Intl && Intl.DateTimeFormat().resolvedOptions().timeZone) || null,
            device_label: detectDeviceLabel(),
            screen: window.screen ? window.screen.width + 'x' + window.screen.height : null,
            viewport: window.innerWidth + 'x' + window.innerHeight,
            language: navigator.language || null,
            connection: connection && connection.effectiveType ? connection.effectiveType : null
        };
    }

    function send(eventType, useKeepalive) {
        var csrf = config.csrf || attributeFrom(['meta[name="csrf-token"]'], 'content');
        var payload = buildPayload(eventType);

        return window.fetch(endpoint, {
            method: 'POST',
            credentials: 'same-origin',
            keepalive: Boolean(useKeepalive),
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf || '',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        }).then(function (response) {
            if (!response.ok) {
                return null;
            }

            return response.json();
        }).then(function (result) {
            if (result && result.visitor_token && result.visitor_token !== visitorToken) {
                visitorToken = result.visitor_token;

                try {
                    window.localStorage.setItem(storageKey, visitorToken);
                } catch (error) {
                    // localStorage may be blocked; tracking can continue for this page.
                }
            }

            return result;
        }).catch(function () {
            return null;
        });
    }

    function heartbeat() {
        if (document.visibilityState === 'visible') {
            send('heartbeat', false);
        }
    }

    function sendPageChange() {
        if (lastPath === window.location.href) {
            return;
        }

        lastPath = window.location.href;
        window.setTimeout(function () {
            send(detectEventType(), false);
        }, 100);
    }

    function watchAjaxCart() {
        if (!window.jQuery) {
            return;
        }

        window.jQuery(document).ajaxSuccess(function (_event, _xhr, settings) {
            var url = settings && settings.url ? String(settings.url).toLowerCase() : '';

            if (url.indexOf('cart') !== -1 || url.indexOf('basket') !== -1) {
                window.setTimeout(function () {
                    send('cart_update', false);
                }, 700);
            }
        });
    }

    function start() {
        send(detectEventType(), false);
        heartbeatTimer = window.setInterval(heartbeat, 15000);
        watchAjaxCart();

        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') {
                heartbeat();
            }
        });

        document.addEventListener('submit', function (event) {
            var form = event.target;
            var action = form && form.action ? String(form.action).toLowerCase() : '';

            if (action.indexOf('cart') !== -1 || action.indexOf('checkout') !== -1 || action.indexOf('order') !== -1) {
                window.setTimeout(function () {
                    send(action.indexOf('checkout') !== -1 || action.indexOf('order') !== -1 ? 'checkout_start' : 'cart_update', false);
                }, 600);
            }
        }, true);

        document.addEventListener('click', function (event) {
            var eventTarget = event.target;
            var target = eventTarget && typeof eventTarget.closest === 'function'
                ? eventTarget.closest('.add-to-cart, [data-action*="cart"], a[href*="/cart"]')
                : null;

            if (target) {
                window.setTimeout(function () {
                    send('cart_update', false);
                }, 800);
            }
        }, true);

        window.addEventListener('popstate', sendPageChange);
        window.addEventListener('hashchange', sendPageChange);
        window.addEventListener('pagehide', function () {
            send('heartbeat', true);

            if (heartbeatTimer) {
                window.clearInterval(heartbeatTimer);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start, {once: true});
    } else {
        start();
    }
}());
