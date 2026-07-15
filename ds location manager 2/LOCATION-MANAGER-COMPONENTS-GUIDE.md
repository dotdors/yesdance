# Location Manager Plugin Components
## Implementation Guide

This document explains how to integrate the Location Grid and News Carousel components into the DS Location Manager plugin.

---

## File Structure

Add these files to your plugin:

```
DS Location Manager/
├── includes/
│   ├── class-location-grid-shortcode.php        ← Location Grid shortcode
│   └── class-location-news-carousel.php         ← News Carousel shortcode
├── templates/
│   ├── location-grid.php                        ← Location Grid template
│   └── location-news-carousel.php               ← News Carousel template
└── assets/
    ├── location-grid.css                        ← Location Grid styles
    ├── location-news-carousel.css               ← News Carousel styles
    └── location-news-carousel.js                ← News Carousel navigation
```

---

## Plugin Integration

### Main Plugin File

In your main plugin file (e.g., `ds-location-manager.php`), add:

```php
// Require component files
require_once plugin_dir_path(__FILE__) . 'includes/class-location-grid-shortcode.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-location-news-carousel.php';
```

That's it! The shortcodes will auto-register.

---

## Shortcode Usage

### Location Grid (Find a Program)

**Basic Usage:**
```
[ds_location_grid]
```

**With Custom Parameters:**
```
[ds_location_grid 
  limit="3" 
  heading="Find a Program" 
  heading_word_split="2"
  subtitle="We're proud to serve communities across the country"
  show_link="true"
  link_text="See more Locations"
  link_url="/locations/"
]
```

**Parameters:**
- `limit` (int, default: 3) - Number of locations to show
- `order` (string, default: 'menu_order') - Order direction (ASC/DESC)
- `orderby` (string, default: 'menu_order') - What to order by
- `show_link` (bool, default: true) - Show "See more" link
- `link_text` (string) - Text for "See more" link
- `link_url` (string) - URL for link (auto-generates if empty)
- `heading` (string) - Section heading
- `heading_word_split` (int, default: 2) - Which word gets accent color (0 = none)
- `subtitle` (string) - Section subtitle

### News Carousel (Latest News)

**Basic Usage:**
```
[ds_location_news_carousel]
```

**With Custom Parameters:**
```
[ds_location_news_carousel 
  limit="10"
  taxonomy="ds_location"
  heading="Latest News"
  heading_word_split="2"
  subtitle="Stay updated with what's happening at our locations"
]
```

**Parameters:**
- `limit` (int, default: 10) - Number of posts to show
- `taxonomy` (string, default: 'ds_location') - Taxonomy to filter by
- `order` (string, default: 'DESC') - Order direction
- `orderby` (string, default: 'date') - What to order by
- `heading` (string) - Section heading
- `heading_word_split` (int, default: 2) - Which word gets accent color
- `subtitle` (string) - Section subtitle

---

## Two-Color Heading Component

Both components use a shared two-color heading pattern:

**HTML Structure:**
```html
<h2 class="ds-heading-split">
  <span class="ds-heading-split__primary">Latest</span>
  <span class="ds-heading-split__accent">News</span>
</h2>
```

**Usage:**
- Set `heading_word_split` parameter to which word gets accent color
- Example: "Find a Program" with `heading_word_split="2"` makes "Program" red
- Set to `0` to disable two-color effect

---

## Theme Integration

### Required CSS Variables

The components use these CSS custom properties from your theme:

**Colors:**
- `--color-primary` - Primary text color
- `--color-secondary` - Secondary/white color
- `--color-accent` - Accent/red color
- `--color-text` - Body text color
- `--color-text-light` - Light text color
- `--color-background` - Background color
- `--color-surface` - Surface/alternate background
- `--color-border` - Border/yellow color

**Typography:**
- `--font-primary` - Primary font stack
- `--font-size-base` through `--font-size-4xl` - Font sizes
- Inherits `font-family` from parent

### Example Theme Setup

In your theme's `_variables.less`:

```css
:root {
  --color-primary: #000000;
  --color-secondary: #ffffff;
  --color-accent: #e23b2a;
  --color-text: #5a65a1;
  --color-text-light: #666666;
  --color-background: #ffffff;
  --color-surface: #fbf5e4;
  --color-border: #eda318;
  /* ... typography variables ... */
}
```

### Overriding Styles

If you need to customize styles, add to your theme's CSS:

```css
/* Override location card border color */
.ds-location-card {
  border-color: #custom-color;
}

/* Override news badge color */
.ds-news-card__badge {
  background: #custom-color;
}
```

---

## Location Meta Fields Required

**Location Name:** `ds_location_name`
- Display name for the location
- Example: "Atlanta Dance Co"
- Used in card title

**City:** `ds_location_city`
- Location city
- Example: "Atlanta"

**State:** `ds_location_state`
- State abbreviation or full name
- Example: "GA" or "Georgia"
- Used in card subtitle

**Featured Image:**
- Standard WordPress featured image
- Used as card background image
- Recommended size: 800x600px minimum

---

## Browser Support

Both components use modern CSS with fallbacks:

**CSS Features:**
- CSS Custom Properties (full support)
- CSS Grid (95%+ support)
- Flexbox (full support)
- Scroll-snap (95%+ support)
- Smooth scrolling (progressive enhancement)

**Accessibility:**
- Respects `prefers-reduced-motion`
- Keyboard navigation support
- ARIA labels on navigation buttons
- Focus-visible styles
- Semantic HTML

---

## Customization Examples

### Homepage Implementation

```html
<!-- Hero section -->
<section class="hero">...</section>

<!-- Find a Program -->
<?php echo do_shortcode('[ds_location_grid limit="3"]'); ?>

<!-- Don't See a Location CTA -->
<section class="cta-section">
  <h2>Don't See a Location Near You?</h2>
  <p>We're growing! ...</p>
  <a href="/bring-yycd/" class="button">Learn More</a>
</section>

<!-- Latest News -->
<?php echo do_shortcode('[ds_location_news_carousel limit="10"]'); ?>
```

### Using Categories Instead of Locations

For sites without location taxonomy:

```
[ds_location_news_carousel taxonomy="category" limit="8"]
```

### Custom Theme Colors

In your theme-customizations plugin:

```css
/* Use different accent colors */
.ds-heading-split__accent {
  color: #custom-accent;
}

.ds-location-card {
  border-color: #custom-border;
}

.ds-news-card__badge {
  background: #custom-badge;
}
```

---

## Troubleshooting

**Cards not showing:**
- Verify locations have featured images set
- Check that `ds_location` post type has published posts
- Ensure CSS/JS files are loading (check browser console)

**Two-color heading not working:**
- Verify `heading_word_split` parameter is set correctly
- Check that CSS custom properties are defined
- Try setting to `0` and back to test

**Carousel not scrolling:**
- Check JavaScript console for errors
- Verify `location-news-carousel.js` is loaded
- Test with different browsers

**Styles not matching theme:**
- Ensure theme CSS variables are defined
- Check for CSS conflicts with browser inspector
- Verify variable names match exactly

---

## Future Enhancements

Potential improvements for future versions:

- [ ] Gutenberg blocks (visual editor integration)
- [ ] Location filtering/search
- [ ] Sticky/featured post highlighting
- [ ] Lazy loading for images
- [ ] Infinite scroll for carousel
- [ ] Admin settings page for defaults
- [ ] Widget versions of components

---

**Last Updated:** January 2025  
**Plugin:** DS Location Manager V2  
**Project:** YYCD (Yes, You Can Dance)
