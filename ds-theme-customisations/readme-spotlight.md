# Cursor Spotlight Effects (Custom WP Plugin)

## Overview
This plugin extension adds interactive cursor effects to the site using **CSS variables** and a small JavaScript helper.  
Currently implemented:
- **Spotlight mode** (brightens area under the cursor using `backdrop-filter: brightness`).
- **Invert mode** (inverts colors inside the circle using `backdrop-filter: invert`).
- Mobile devices are excluded for performance and UX reasons.

All effects are desktop-only (min-width: 768px) and optimized using `requestAnimationFrame`.

---

## File Overview
- **functions.php** → Enqueues the CSS and JS into WordPress.  
- **assets/spotlight.css** → Contains styles and effect definitions.  
- **assets/spotlight.js** → Handles mouse tracking, circle position, and mode toggling.  
- **assets/README.md** → This documentation file.

---

## Usage

### Default
- On desktop, spotlight mode is enabled automatically.

### Keyboard Shortcuts
- `1` → Enable **Spotlight** mode  
- `2` → Enable **Invert** mode  
- `0` → Disable effects  

### JavaScript API
From the browser console or your own scripts:
```js
window.toggleCursorEffect("spotlight"); // Spotlight mode
window.toggleCursorEffect("invert");    // Invert mode
window.toggleCursorEffect(null);        // Disable

### Configuration

You can adjust settings in spotlight.css:

:root {
  --circle-size: 150px; /* radius of the spotlight circle */
  --circle-x: 50%;
  --circle-y: 50%;
}


Change --circle-size to control spotlight radius.

Spotlight brightness is set via:

backdrop-filter: brightness(1.5);

### Future Enhancements (Ideas)

Add UI toggle button (floating icon in corner of screen to switch between modes).

Add sparkle/particle mode inside circle.

Add RGB overlapping spotlight experimental effect (simulate stage lighting).

Add per-page enable/disable toggle in WordPress admin.

Add CSS variables for brightness/invert strength for finer control.

### Notes

Requires modern browsers (supporting backdrop-filter and clip-path).

No support for IE11 or older.