/**
 * Palette Preview — client demo tool
 * DS Theme Customizations
 *
 * Loads in <head> (not deferred) so the palette applies before first paint —
 * no flash of the default red. Order of precedence:
 *   1. ?palette=ocean / ?palette=default in the URL (also saves the choice)
 *   2. localStorage from a previous visit
 *
 * The floating toggle is only built for logged-in users; the client just
 * sees whichever palette the shared URL carries.
 */

(function () {
    'use strict';

    var STORAGE_KEY = 'yycd_palette_preview';
    // 'dusty-gold' is parked — its CSS block still exists, so add it back
    // here (and to LABELS) to resume playing with it.
    var PALETTES = ['default', 'midnight-teal', 'midnight-blue', 'harbor'];
    var LABELS = {
        'default': 'Red (current)',
        'midnight-teal': 'Midnight + Teal',
        'midnight-blue': 'Midnight + Azure',
        'harbor': 'Harbor (navy + coral)'
    };

    function apply(palette) {
        if (palette && palette !== 'default') {
            document.documentElement.setAttribute('data-palette', palette);
        } else {
            document.documentElement.removeAttribute('data-palette');
        }
    }

    function current() {
        return document.documentElement.getAttribute('data-palette') || 'default';
    }

    // --- Resolve palette immediately (pre-paint) ---
    var fromUrl = new URLSearchParams(window.location.search).get('palette');
    if (fromUrl && PALETTES.indexOf(fromUrl) !== -1) {
        apply(fromUrl);
        try { localStorage.setItem(STORAGE_KEY, fromUrl); } catch (e) {}
    } else {
        try {
            var saved = localStorage.getItem(STORAGE_KEY);
            if (saved && PALETTES.indexOf(saved) !== -1) {
                apply(saved);
            }
        } catch (e) {}
    }

    // --- Toggle pill for logged-in users ---
    document.addEventListener('DOMContentLoaded', function () {
        if (!document.body.classList.contains('logged-in')) {
            return;
        }

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'ds-palette-toggle';
        btn.setAttribute('aria-label', 'Switch color palette preview');

        var swatch = document.createElement('span');
        swatch.className = 'ds-palette-toggle__swatch';
        swatch.setAttribute('aria-hidden', 'true');

        var label = document.createElement('span');

        function refresh() {
            label.textContent = 'Palette: ' + LABELS[current()];
        }

        btn.appendChild(swatch);
        btn.appendChild(label);
        refresh();

        btn.addEventListener('click', function () {
            var next = PALETTES[(PALETTES.indexOf(current()) + 1) % PALETTES.length];
            apply(next);
            try { localStorage.setItem(STORAGE_KEY, next); } catch (e) {}
            refresh();
        });

        document.body.appendChild(btn);
    });
})();
