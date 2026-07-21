(function () {
    'use strict';

    function initMegaMenu(root) {
        var trigger = root.querySelector('.ariya-mega-trigger');
        var shell = root.querySelector('.ariya-mega-shell');
        var sidebarItems = Array.prototype.slice.call(root.querySelectorAll('.ariya-mega-sidebar-item'));
        var panels = Array.prototype.slice.call(root.querySelectorAll('.ariya-mega-panel'));
        var closeTimer = null;

        if (!trigger || !shell || !sidebarItems.length) {
            return;
        }

        function setOpen(isOpen) {
            root.classList.toggle('is-open', isOpen);
            trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            shell.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        }

        function cancelClose() {
            if (closeTimer) {
                window.clearTimeout(closeTimer);
                closeTimer = null;
            }
        }

        function openMenu() {
            cancelClose();
            setOpen(true);
        }

        function scheduleClose() {
            cancelClose();
            closeTimer = window.setTimeout(function () {
                setOpen(false);
            }, 130);
        }

        function activatePanel(targetId) {
            sidebarItems.forEach(function (item) {
                var active = item.getAttribute('data-mega-target') === targetId;
                item.classList.toggle('is-active', active);
                item.setAttribute('aria-current', active ? 'true' : 'false');
            });

            panels.forEach(function (panel) {
                var active = panel.getAttribute('data-mega-panel') === targetId;
                panel.classList.toggle('is-active', active);
                panel.setAttribute('aria-hidden', active ? 'false' : 'true');
            });
        }

        root.addEventListener('mouseenter', openMenu);
        root.addEventListener('mouseleave', scheduleClose);
        root.addEventListener('focusin', openMenu);
        root.addEventListener('focusout', function (event) {
            if (!root.contains(event.relatedTarget)) {
                scheduleClose();
            }
        });

        sidebarItems.forEach(function (item) {
            var activate = function () {
                activatePanel(item.getAttribute('data-mega-target'));
            };

            item.addEventListener('mouseenter', activate);
            item.addEventListener('focus', activate);
        });

        trigger.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                openMenu();
                sidebarItems[0].focus();
            }
        });

        root.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                setOpen(false);
                trigger.focus();
            }
        });

        document.addEventListener('click', function (event) {
            if (!root.contains(event.target)) {
                setOpen(false);
            }
        });
    }

    function boot() {
        document.querySelectorAll('.ariya-mega-root').forEach(initMegaMenu);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
