(function ($) {
    'use strict';

    function getCartElements() {
        return {
            trigger: document.querySelector('[data-ariya-cart-open]'),
            drawer: document.querySelector('.ariya-cart-drawer'),
            overlay: document.querySelector('.ariya-cart-overlay')
        };
    }

    function openCart() {
        var elements = getCartElements();
        if (!elements.drawer || !elements.overlay) return;

        elements.overlay.hidden = false;
        window.requestAnimationFrame(function () {
            elements.drawer.classList.add('is-open');
            elements.overlay.classList.add('is-open');
        });
        elements.drawer.setAttribute('aria-hidden', 'false');
        if (elements.trigger) elements.trigger.setAttribute('aria-expanded', 'true');
        document.body.classList.add('ariya-cart-open');
    }

    function closeCart() {
        var elements = getCartElements();
        if (!elements.drawer || !elements.overlay) return;

        elements.drawer.classList.remove('is-open');
        elements.overlay.classList.remove('is-open');
        elements.drawer.setAttribute('aria-hidden', 'true');
        if (elements.trigger) elements.trigger.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('ariya-cart-open');

        window.setTimeout(function () {
            if (!elements.overlay.classList.contains('is-open')) {
                elements.overlay.hidden = true;
            }
        }, 280);
    }

    document.addEventListener('click', function (event) {
        var openButton = event.target.closest('[data-ariya-cart-open]');
        if (openButton) {
            event.preventDefault();
            openCart();
            return;
        }

        if (event.target.closest('[data-ariya-cart-close]')) {
            event.preventDefault();
            closeCart();
            return;
        }

        var removeButton = event.target.closest('[data-ariya-cart-remove]');
        if (removeButton) {
            event.preventDefault();
            var action = removeButton.getAttribute('data-action');
            if (!action || removeButton.disabled) return;

            removeButton.disabled = true;
            fetch(action, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: '_method=DELETE'
            })
                .then(function (response) {
                    if (!response.ok) throw new Error('Cart remove failed');
                    return response.json();
                })
                .then(function (data) {
                    if (data.cart) {
                        var current = document.getElementById('cart-list-item');
                        if (current) current.outerHTML = data.cart;
                        openCart();
                    }
                })
                .catch(function () {
                    removeButton.disabled = false;
                    if (window.toastr) toastr.error('حذف کالا از سبد خرید انجام نشد.');
                });
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeCart();
    });

    function initDragCarousel(element) {
        if (!element || element.dataset.dragReady === '1') return;
        element.dataset.dragReady = '1';

        var startX = 0;
        var startY = 0;
        var currentX = 0;
        var currentY = 0;
        var pointerId = null;
        var dragging = false;
        var moved = false;

        function changeSlide(direction) {
            if (window.jQuery && $.fn && typeof $.fn.carousel === 'function') {
                $(element).carousel('pause');
                $(element).carousel(direction);
                return;
            }

            var control = element.querySelector(
                direction === 'next'
                    ? '[data-slide="next"], .carousel-control-next'
                    : '[data-slide="prev"], .carousel-control-prev'
            );

            if (control) control.click();
        }

        function begin(clientX, clientY, id) {
            startX = currentX = clientX;
            startY = currentY = clientY;
            pointerId = id;
            dragging = true;
            moved = false;
            element.classList.add('is-dragging');
        }

        function update(clientX, clientY, event) {
            if (!dragging) return;

            currentX = clientX;
            currentY = clientY;

            var deltaX = currentX - startX;
            var deltaY = currentY - startY;

            if (Math.abs(deltaX) > 8 && Math.abs(deltaX) > Math.abs(deltaY)) {
                moved = true;
                if (event && event.cancelable) event.preventDefault();
            }
        }

        function finish(clientX, clientY) {
            if (!dragging) return;

            currentX = typeof clientX === 'number' ? clientX : currentX;
            currentY = typeof clientY === 'number' ? clientY : currentY;

            var deltaX = currentX - startX;
            var deltaY = currentY - startY;

            dragging = false;
            pointerId = null;
            element.classList.remove('is-dragging');

            if (Math.abs(deltaX) >= 42 && Math.abs(deltaX) > Math.abs(deltaY)) {
                changeSlide(deltaX < 0 ? 'next' : 'prev');
            }
        }

        element.addEventListener('dragstart', function (event) {
            event.preventDefault();
        });

        if (window.PointerEvent) {
            element.addEventListener('pointerdown', function (event) {
                if (event.pointerType === 'mouse' && event.button !== 0) return;
                if (event.target.closest('.carousel-control-prev, .carousel-control-next, .carousel-indicators')) return;

                begin(event.clientX, event.clientY, event.pointerId);

                if (element.setPointerCapture) {
                    try {
                        element.setPointerCapture(event.pointerId);
                    } catch (error) {}
                }
            });

            element.addEventListener('pointermove', function (event) {
                if (!dragging || (pointerId !== null && event.pointerId !== pointerId)) return;
                update(event.clientX, event.clientY, event);
            }, { passive: false });

            element.addEventListener('pointerup', function (event) {
                if (pointerId !== null && event.pointerId !== pointerId) return;
                finish(event.clientX, event.clientY);
            });

            element.addEventListener('pointercancel', function (event) {
                if (pointerId !== null && event.pointerId !== pointerId) return;
                finish(event.clientX, event.clientY);
            });
        } else {
            element.addEventListener('mousedown', function (event) {
                if (event.button !== 0) return;
                begin(event.clientX, event.clientY, 'mouse');
            });

            window.addEventListener('mousemove', function (event) {
                update(event.clientX, event.clientY, event);
            });

            window.addEventListener('mouseup', function (event) {
                finish(event.clientX, event.clientY);
            });

            element.addEventListener('touchstart', function (event) {
                if (!event.touches.length) return;
                begin(event.touches[0].clientX, event.touches[0].clientY, 'touch');
            }, { passive: true });

            element.addEventListener('touchmove', function (event) {
                if (!event.touches.length) return;
                update(event.touches[0].clientX, event.touches[0].clientY, event);
            }, { passive: false });

            element.addEventListener('touchend', function (event) {
                var touch = event.changedTouches && event.changedTouches[0];
                if (touch) finish(touch.clientX, touch.clientY);
            }, { passive: true });
        }

        element.addEventListener('click', function (event) {
            if (!moved) return;

            event.preventDefault();
            event.stopPropagation();
            moved = false;
        }, true);
    }

    function initializeCarousels() {
        document.querySelectorAll('.ariya-draggable-carousel').forEach(initDragCarousel);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeCarousels);
    } else {
        initializeCarousels();
    }
})(jQuery);
