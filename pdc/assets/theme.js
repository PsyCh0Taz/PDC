(function () {
    'use strict';

    var storageKey = 'pdc.theme';
    var root = document.documentElement;
    var media = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;

    function storedTheme() {
        try {
            var value = localStorage.getItem(storageKey);
            return value === 'light' || value === 'dark' ? value : null;
        } catch (error) {
            return null;
        }
    }

    function currentTheme() {
        return root.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
    }

    function updateButtons(theme) {
        document.querySelectorAll('[data-pdc-theme-toggle]').forEach(function (button) {
            var dark = theme === 'dark';
            var icon = button.querySelector('i');
            var label = button.querySelector('.pdc-theme-toggle-label');
            button.setAttribute('aria-pressed', dark ? 'true' : 'false');
            button.setAttribute('aria-label', dark ? 'Activer le thème clair' : 'Activer le thème sombre');
            button.title = dark ? 'Activer le thème clair' : 'Activer le thème sombre';
            if (icon) icon.className = dark ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
            if (label) label.textContent = dark ? 'Thème clair' : 'Thème sombre';
        });
    }

    function applyTheme(theme, persist) {
        root.setAttribute('data-bs-theme', theme);
        if (persist) {
            try { localStorage.setItem(storageKey, theme); } catch (error) {}
        }
        updateButtons(theme);
        document.dispatchEvent(new CustomEvent('pdc:themechange', { detail: { theme: theme } }));
    }

    document.addEventListener('DOMContentLoaded', function () {
        updateButtons(currentTheme());
        document.querySelectorAll('[data-pdc-theme-toggle]').forEach(function (button) {
            button.addEventListener('click', function () {
                applyTheme(currentTheme() === 'dark' ? 'light' : 'dark', true);
            });
        });
    });

    if (media && media.addEventListener) {
        media.addEventListener('change', function (event) {
            if (!storedTheme()) applyTheme(event.matches ? 'dark' : 'light', false);
        });
    }
}());