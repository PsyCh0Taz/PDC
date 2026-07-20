(function() {
    'use strict';

    var storageKey = 'pdc.theme';
    var root = document.documentElement;

    function currentTheme() {
        return root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
    }

    function updateControls(theme) {
        var dark = theme === 'dark';
        document.querySelectorAll('[data-pdc-theme-toggle]').forEach(function(button) {
            var icon = button.querySelector('i');
            var label = button.querySelector('.pdc-theme-toggle-label');
            button.setAttribute('aria-label', dark ? 'Activer le thème clair' : 'Activer le thème sombre');
            button.setAttribute('aria-pressed', dark ? 'true' : 'false');
            if (label) {
                label.textContent = dark ? 'Thème clair' : 'Thème sombre';
            }
            if (icon) {
                icon.classList.toggle('fa-moon', !dark);
                icon.classList.toggle('fa-sun', dark);
            }
        });
    }

    function applyTheme(theme, persist) {
        root.setAttribute('data-theme', theme);
        updateControls(theme);

        if (persist) {
            try { localStorage.setItem(storageKey, theme); } catch (e) {}
        }

        window.dispatchEvent(new CustomEvent('pdc:themechange', { detail: { theme: theme } }));
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateControls(currentTheme());
        document.querySelectorAll('[data-pdc-theme-toggle]').forEach(function(button) {
            button.addEventListener('click', function() {
                applyTheme(currentTheme() === 'dark' ? 'light' : 'dark', true);
            });
        });
    });
}());
