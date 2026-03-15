/**
 * DH Toast Notification System — AdminCP
 * Usage (JS): DHToast.show('Message text', 'success'|'error'|'warning'|'info')
 * Auto-converts .alert divs inside .acp-main to toasts.
 */
(function (global) {
    'use strict';

    var CONTAINER_ID = 'dh-toast-container';
    var DURATION     = 4500;
    var ANIM_OUT     = 400;

    var icons = {
        success : '<svg viewBox="0 0 20 20" fill="none" width="18" height="18"><circle cx="10" cy="10" r="9" stroke="#4caf50" stroke-width="1.5"/><path d="M6 10.5l2.5 2.5 5-5" stroke="#4caf50" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        error   : '<svg viewBox="0 0 20 20" fill="none" width="18" height="18"><circle cx="10" cy="10" r="9" stroke="#ef5350" stroke-width="1.5"/><path d="M7 7l6 6M13 7l-6 6" stroke="#ef5350" stroke-width="1.8" stroke-linecap="round"/></svg>',
        warning : '<svg viewBox="0 0 20 20" fill="none" width="18" height="18"><path d="M10 2L18.66 17H1.34L10 2z" stroke="#ffa726" stroke-width="1.5" stroke-linejoin="round"/><path d="M10 8v4M10 13.5v1" stroke="#ffa726" stroke-width="1.8" stroke-linecap="round"/></svg>',
        info    : '<svg viewBox="0 0 20 20" fill="none" width="18" height="18"><circle cx="10" cy="10" r="9" stroke="#42a5f5" stroke-width="1.5"/><path d="M10 9v5M10 6.5v1" stroke="#42a5f5" stroke-width="1.8" stroke-linecap="round"/></svg>'
    };

    function getContainer() {
        var c = document.getElementById(CONTAINER_ID);
        if (!c) {
            c = document.createElement('div');
            c.id = CONTAINER_ID;
            document.body.appendChild(c);
        }
        return c;
    }

    function show(message, type, duration) {
        type     = type     || 'info';
        if (duration === undefined) {
            duration = (type === 'warning' || type === 'error') ? 7000 : DURATION;
        }

        var container = getContainer();
        var toast     = document.createElement('div');
        toast.className = 'dh-toast dh-toast--' + type;

        toast.innerHTML =
            '<span class="dh-toast__icon">' + (icons[type] || icons.info) + '</span>' +
            '<span class="dh-toast__msg">'  + message + '</span>' +
            '<button class="dh-toast__close" aria-label="Close">&times;</button>';

        container.appendChild(toast);
        void toast.offsetWidth;
        toast.classList.add('dh-toast--in');

        var dismissed = false;
        function dismiss() {
            if (dismissed) return;
            dismissed = true;
            toast.classList.remove('dh-toast--in');
            toast.classList.add('dh-toast--out');
            setTimeout(function () {
                if (toast.parentNode) toast.parentNode.removeChild(toast);
            }, ANIM_OUT);
        }

        toast.querySelector('.dh-toast__close').addEventListener('click', dismiss);
        if (duration > 0) setTimeout(dismiss, duration);
    }

    function processAutoToasts() {
        var triggers = document.querySelectorAll('.dh-toast-trigger');
        triggers.forEach(function (el) {
            var type    = el.getAttribute('data-type')    || 'info';
            var message = el.getAttribute('data-message') || '';
            if (message) show(message, type);
            el.parentNode && el.parentNode.removeChild(el);
        });
    }

    function absorbLegacyAlerts() {
        var map = {
            'alert-success' : 'success',
            'alert-danger'  : 'error',
            'alert-warning' : 'warning',
            'alert-info'    : 'info'
        };
        // Absorb alerts inside the main content area, not navigation or modals
        var alerts = document.querySelectorAll('.acp-main .alert');
        alerts.forEach(function (el) {
            var type = 'info';
            Object.keys(map).forEach(function (cls) {
                if (el.classList.contains(cls)) type = map[cls];
            });
            var msg = el.innerText || el.textContent || '';
            msg = msg.trim();
            if (msg) show(msg, type);
            el.style.display = 'none';
        });
    }

    global.DHToast = { show: show };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            processAutoToasts();
            absorbLegacyAlerts();
        });
    } else {
        processAutoToasts();
        absorbLegacyAlerts();
    }

})(window);

