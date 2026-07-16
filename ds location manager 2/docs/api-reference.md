# DS Location Manager — REST API Reference

**Living document.** When you change `rest.php` or `includes/app-settings.php`, update this file in the same commit. All routes are under namespace `ds/v1` (base: `{site}/wp-json/ds/v1`). All endpoints are public GET, no auth.

## Endpoints

### `GET /locations`
Defined: `rest.php` → `ds_get_locations_app()` (~line 113). Returns all published `ds_location` posts.

Per-location payload:

| Key | Source | Notes |
|---|---|---|
| `id` | post ID | |
| `title` | post title | **The city** (e.g. "Jupiter, FL"). See data-model note below. |
| `name` | `_ds_location_name` | Studio/program name (e.g. "In Motion Ballroom") |
| `city` | `_ds_location_city` | Duplicates title's meaning; pending: auto-synced from title |
| `address` | `_ds_location_address` | Multi-line text |
| `phone` | `_ds_location_phone` | |
| `email` | `_ds_location_email` | |
| `contact` | `_ds_location_contact_name` | Contact person |
| `description` | `_ds_location_description` | Short tagline, plain text |
| `yycd_description` | `_ds_location_yycd_description` | About-the-program body. Currently plain text; becoming TinyMCE-managed — see pending changes. |
| `logo_id` / `logo_url` | `_ds_location_logo` | Full-size attachment URL currently |
| `latitude` / `longitude` | meta | Strings |

### `GET /posts_by_location`
Defined: `rest.php` (~line 179). All location taxonomy terms, each with `term_id`, `name`, `slug`, `post_count`, and a lightweight `posts` array (`id`, `title`, `url`).

### `GET /locations/{id}/posts`
Defined: `rest.php` → `ds_get_posts_for_location()` (~line 238). Paginated posts for one location (`page`, `per_page` params). Per-post: `id`, `title`, `url`, `excerpt`, `content` (rendered HTML), `date` (ISO 8601), `featured_image` (large), `sticky`.

### `GET /posts/{id}`
Defined: `rest.php` (~line 324). Single post, same shape as above.

### `GET /splash`
Defined: `includes/app-settings.php` (~line 311). App splash content from the App Settings admin page (`ds_app_splash_*` options): `logo_url`, `image_url`, welcome text. **Design contract: the logo asset must be a light/white version** — the app renders it on the ink `#211E26` splash background without tinting.

## Data-model note (read before touching location fields)

A location is a `ds_location` post PLUS a synced `ds_post_location` taxonomy term (see `ensure_term_for_location()` in the main plugin file). The post **title is the city**; `_ds_location_name` is the studio/program. `_ds_location_city` historically duplicated the title and could drift — the fix in progress makes title the source of truth and syncs the meta on save. Don't add a new field without wiring it through: meta box, settings-page save handler, `get_location_display_data()`, and the REST payload (a unification of these paths is planned — see plans/).

## Pending API changes (agreed, not yet implemented)

| Change | Status |
|---|---|
| `text_phone` — new meta field; API sends only when set; consumers fall back to `phone` | planned |
| `website` — meta exists (`_ds_location_website`), add to `/locations` payload | planned |
| `page_url` — `get_permalink()` of the location page, add to `/locations` payload | planned |
| `yycd_description_html` — rendered rich text (field moving to TinyMCE); existing `yycd_description` stays as stripped plain text | planned |
| `flyer_url` — new optional attachment field (PDF/image) for a downloadable flyer | planned |
| `logo_url` → sized variant (`medium`) instead of full attachment | planned |
| `featured_image` for locations | undecided — only needed if the app's Location Home gets a photo hero |

When a row above ships, move its documentation up into the payload table and delete the row.
