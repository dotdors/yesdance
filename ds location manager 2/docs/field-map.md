# YYCD App — Screen-to-API Field Map

Companion to `mockups/yycd-mobile-ui-mock-v2.html` and `api-reference.md`.
Plain reference, no prose. One section per screen: API call(s), then field → where
it goes, then what's hardcoded.

Conventions:
- `L.` = a location object from `GET /ds/v1/locations`
- `P.` = a post object (bundle, `/locations/{id}/posts`, or `/posts/{id}` — same shape; bundle omits `content`)
- Empty string `""` means "not set — hide the element" unless noted.

---

## 1. Splash

API: none required at launch. Splash content comes from **App Settings**
endpoint (WP-managed splash text/imagery) — logo asset ships in the app bundle.

| Field | Position |
|---|---|
| app settings: tagline | Centered under hero image |
| app settings: hero image | Arched photo, center |

Static: "Select a Location" button label. Logo (bundled asset, replaces old text title).

---

## 2. Locations list

API: `GET /ds/v1/locations` (paginated; fetch all pages up front — list is small).

| Field | Position |
|---|---|
| `L.city` | Row, small uppercase label (top line) |
| `L.title` / `L.location_name` | Row, main serif line (below city) |
| `L.logo_url` | Row, 44px thumb (left). Fallback: generic glyph |
| `L.id` | Row tap → Location Home |

Static: "Find a program" eyebrow, "Choose your location" heading, subheading.
Search bar: **removed** (v2 sketch).

---

## 3. Location Home

API: `GET /ds/v1/locations/{id}` + `GET /ds/v1/locations/{id}/posts?per_page=N`
(or the app's cached bundle from `/posts_by_location`).

| Field | Position |
|---|---|
| `L.location_name` + `L.city` | App bar title, format `{name} – {city}` |
| contact fields (see Contact Sheet) | Single contact icon button, app bar top-right → opens Contact Sheet |
| `L.yycd_description` | About card teaser (truncate ~2 lines) → About screen |
| `P.featured_image` | News card image (16:8). `null` → solid ink block, no img |
| `P.title` | News card headline |
| `P.date` | News card date line (format locally) |
| `P.sticky` | `true` → pin to top + "★ Pinned" treatment |
| `P.id` | Card tap → Article |

Static: "About our program" eyebrow, "Read more →", "Latest News", "All posts →".
Location badge on news cards: **removed** (v2 sketch — redundant, list is already scoped).
Call/Text/Email/Directions quick-row: **removed** — collapsed into app-bar contact button.

---

## 4. Article

API: `GET /ds/v1/posts/{id}` (or reuse list payload if `content` already fetched).

| Field | Position |
|---|---|
| `P.featured_image` | Hero (16:10). `null` → no hero, content starts at top |
| `L.city` (current location) | Eyebrow above headline |
| `P.title` | Headline |
| `P.date` | Under headline (format locally) |
| `P.content` | Body — rendered HTML, render natively |

Static: back chevron, share action (top-right).

---

## 5. About Program

API: `GET /ds/v1/locations/{id}` (already fetched for Location Home).

| Field | Position |
|---|---|
| `L.city` (or region label if we add one) | Eyebrow |
| `L.yycd_description` | Body. Plain text today; becomes HTML (`yycd_description_html`) when TinyMCE change lands — see api-reference pending table |
| `L.flyer_url` | "⤓ Download Flyer" ghost button. `""` → hide button |
| `L.website` | "↗ Visit us on the web" ghost button, opens external browser. `""` → hide button |

Static: "About Our YYCD Program" heading, both button labels,
"Contact Us" pill — **pinned to bottom of screen** (v2 sketch), opens Contact Sheet.

---

## 6. Contact Sheet (modal)

API: none — uses `L.` already in memory.

| Field | Position |
|---|---|
| `L.location_name` | Sheet heading: "Contact {name}" |
| `L.phone` | Call tile (primary/red). `""` → hide tile |
| `L.text_phone` | Text tile. `""` → hide tile. **Never fall back to `phone`** (see api-reference) |
| `L.email` | Email tile, sublabel shows `L.contact_name` if set, else the address |
| `L.latitude` + `L.longitude` | Directions tile → Apple/Google Maps intent. Both empty → hide tile |
| `L.address` | "Find us" block at bottom. `""` → hide block |

Static: sheet grab handle, "Come dance with us — we'd love to hear from you." subline,
tile labels (Call / Text / Email / Directions), "Find us" eyebrow, "Apple / Google Maps" sublabel.

---

## Notes for dev

- Bundle endpoint (`/posts_by_location`) post shape now includes
  `excerpt`, `date`, `featured_image`, `sticky` (added 2026-07). Update the
  Kotlin data class — previously only `id`/`title`/`url` deserialized.
- Same reminder for `LocationDetail`: WP already sends `yycd_description`,
  `description`, `address`, `contact_name`, `city`, `logo_url`, `website`,
  `flyer_url`, `text_phone`, `page_url` — model must deserialize all of them.
- All "hide when empty" rules assume the API's stable-key convention: keys are
  always present, emptiness is `""` (or `null` for `featured_image`).
