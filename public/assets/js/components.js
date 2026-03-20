/**
 * DarkCore CMS — Shared Component Scripts
 * Template-independent. Loaded by every template via __PATH_ASSETS_JS__.
 * Includes: DHToast notification system, theme toggle.
 */
(function (global) {
    'use strict';

    /* ============================================================
       TOAST SYSTEM
       Usage (JS): DHToast.show('Message', 'success'|'error'|'warning'|'info')
       Auto-processes .dh-toast-trigger elements on DOMContentLoaded.
       ============================================================ */

    var CONTAINER_ID = 'dh-toast-container';
    var ANIM_OUT     = 350;

    var icons = {
        success: '<svg viewBox="0 0 20 20" fill="none" width="18" height="18"><circle cx="10" cy="10" r="9" stroke="#4caf50" stroke-width="1.5"/><path d="M6 10.5l2.5 2.5 5-5" stroke="#4caf50" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        error:   '<svg viewBox="0 0 20 20" fill="none" width="18" height="18"><circle cx="10" cy="10" r="9" stroke="#ef5350" stroke-width="1.5"/><path d="M7 7l6 6M13 7l-6 6" stroke="#ef5350" stroke-width="1.8" stroke-linecap="round"/></svg>',
        warning: '<svg viewBox="0 0 20 20" fill="none" width="18" height="18"><path d="M10 2L18.66 17H1.34L10 2z" stroke="#ffa726" stroke-width="1.5" stroke-linejoin="round"/><path d="M10 8v4M10 13.5v1" stroke="#ffa726" stroke-width="1.8" stroke-linecap="round"/></svg>',
        info:    '<svg viewBox="0 0 20 20" fill="none" width="18" height="18"><circle cx="10" cy="10" r="9" stroke="#42a5f5" stroke-width="1.5"/><path d="M10 9v5M10 6.5v1" stroke="#42a5f5" stroke-width="1.8" stroke-linecap="round"/></svg>'
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
        type = type || 'info';
        if (duration === undefined) {
            duration = (type === 'error' || type === 'warning') ? 7000 : 4500;
        }

        var container = getContainer();
        var toast     = document.createElement('div');
        toast.className = 'dh-toast dh-toast--' + type;

        toast.innerHTML =
            '<span class="dh-toast__icon">' + (icons[type] || icons.info) + '</span>' +
            '<span class="dh-toast__msg">'  + message + '</span>' +
            '<button class="dh-toast__close" aria-label="Close">&times;</button>';

        container.appendChild(toast);
        /* trigger reflow to allow CSS transition */
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

    /** Process hidden .dh-toast-trigger spans emitted by PHP message() */
    function processAutoToasts() {
        var triggers = document.querySelectorAll('.dh-toast-trigger');
        triggers.forEach(function (el) {
            var type    = el.getAttribute('data-type')    || 'info';
            var message = el.getAttribute('data-message') || '';
            if (message) show(message, type);
            if (el.parentNode) el.parentNode.removeChild(el);
        });
    }

    global.DHToast = { show: show };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            processAutoToasts();
        });
    } else {
        processAutoToasts();
    }


}(window));

/* ============================================================
   RANKINGS — filter by class
   ============================================================ */
function rankingsFilterByClass() {
    var delay = 500;
    var classList = Array.prototype.slice.call(arguments);
    if (!document.querySelector('.rankings-table')) return;
    var table = document.querySelector('.rankings-table');
    table.style.opacity = '0';
    setTimeout(function () {
        table.querySelectorAll('tr').forEach(function (row) {
            if (!row.hasAttribute('data-class-id')) return;
            var id = parseInt(row.getAttribute('data-class-id'));
            row.style.display = classList.indexOf(id) === -1 ? 'none' : '';
        });
        table.style.opacity = '1';
    }, delay);
}

function rankingsFilterRemove() {
    var delay = 500;
    var table = document.querySelector('.rankings-table');
    if (!table) return;
    table.style.opacity = '0';
    setTimeout(function () {
        table.querySelectorAll('tr').forEach(function (row) { row.style.display = ''; });
        table.style.opacity = '1';
    }, delay);
}

/* ============================================================
   THEME TOGGLE (dark / light mode)
   ============================================================ */
(function () {
    function initThemeToggle() {
        var root   = document.documentElement;
        var toggle = document.getElementById('theme-toggle');
        if (!toggle) return;

        function syncLabel() {
            var dark = root.classList.contains('dark-mode');
            toggle.setAttribute('aria-label', dark ? 'Switch to light mode' : 'Switch to dark mode');
            toggle.setAttribute('title',      dark ? 'Light mode'           : 'Dark mode');
        }
        syncLabel();

        toggle.addEventListener('click', function () {
            root.classList.toggle('dark-mode');
            try { localStorage.setItem('site-theme', root.classList.contains('dark-mode') ? 'dark' : 'light'); } catch (e) {}
            syncLabel();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initThemeToggle);
    } else {
        initThemeToggle();
    }
}());

/* ============================================================
   RANKINGS CLASS FILTER — highlight selected
   ============================================================ */
document.addEventListener('DOMContentLoaded', function () {
    var filters = document.querySelectorAll('a.rankings-class-filter-selection');
    if (!filters.length) return;
    filters.forEach(function (el) {
        el.addEventListener('click', function () {
            filters.forEach(function (f) { f.classList.add('rankings-class-filter-grayscale'); });
            el.classList.remove('rankings-class-filter-grayscale');
        });
    });
});

