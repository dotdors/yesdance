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
| `city` | post title (mirrored to `_ds_location_city`) | Kept in sync automatically — see data-model note below |
| `address` | `_ds_location_address` | Multi-line text |
| `phone` | `_ds_location_phone` | |
| `email` | `_ds_location_email` | |
| `contact` | `_ds_location_contact_name` | Contact person |
| `description` | `_ds_location_description` | Short tagline, plain text |
| `yycd_description` | `_ds_location_yycd_description` | About-the-program body. Currently plain text; becoming TinyMCE-managed — see pending changes. |
| `logo_id` / `logo_url` | `_ds_location_logo` | `logo_url` is the `medium` size, not the full attachment |
| `latitude` / `longitude` | meta | Strings |
| `website` | `_ds_location_website` | External site for this location, if any |
| `page_url` | `get_permalink()` | Link to this location's page on the YYCD site |
| `flyer_url` | `_ds_location_flyer` | PDF or image attachment URL; empty string when not set |
| `text_phone` | `_ds_location_text_phone` | **Present whenever this location accepts texts at all** (mirrors `phone` when the same number does both, or holds the distinct number when it doesn't). **Absent entirely** when texting isn't available (e.g. a landline). Consumers should show a Text action iff this key is present — no fallback logic needed. |

### `GET /posts_by_location`
Defined: `rest.php` (~line 179). All location taxonomy terms, each with `term_id`, `name`, `slug`, `post_count`, and a lightweight `posts` array (`id`, `title`, `url`).

### `GET /locations/{id}/posts`
Defined: `rest.php` → `ds_get_posts_for_location()` (~line 238). Paginated posts for one location (`page`, `per_page` params). Per-post: `id`, `title`, `url`, `excerpt`, `content` (rendered HTML), `date` (ISO 8601), `featured_image` (large), `sticky`.

### `GET /posts/{id}`
Defined: `rest.php` (~line 324). Single post, same shape as above.

### `GET /splash`
Defined: `includes/app-settings.php` (~line 311). App splash content from the App Settings admin page (`ds_app_splash_*` options): `logo_url`, `image_url`, welcome text. **Design contract: the logo asset must be a light/white version** — the app renders it on the ink `#211E26` splash background without tinting.

## Data-model note (read before touching location fields)

A location is a `ds_location` post PLUS a synced `ds_post_location` taxonomy term (see `ensure_term_for_location()` in the main plugin file). The post **title is the city** (e.g. "Jupiter, FL") and is the single source of truth — `_ds_location_city` is a synced mirror kept in sync by `DS_Location_Data::save()` / `sync_city_from_title()` on every save path, so it can no longer drift the way it used to. `_ds_location_name` is the separate studio/program name (e.g. "In Motion Ballroom").

**Field access is unified** (`includes/class-location-fields.php` + `includes/class-location-data.php`): `DS_Location_Fields::all()` is the single registry of meta-backed fields (key, meta key, sanitizer), and `DS_Location_Data` is the single get/save path used by the meta box, the settings-page save handler, the "+ Add New Location" creation flow, `get_location_display_data()`, and this REST endpoint. **Adding a new field now means one new row in the registry** — not touching four separate places. City (post title) and the featured image (post thumbnail) are handled as special cases in `DS_Location_Data` rather than registry rows, since neither is a plain meta field.

**Creating a location:** the only sanctioned path is "+ Add New Location" on the Settings page (admin-only) → `DS_Location_Data::create()` → lands as a draft, ready to fill in on the Settings page. There's no other supported way to create one (the standard "Add New" flow is still hidden from location managers).

## Pending API changes (agreed, not yet implemented)

| Change | Status |
|---|---|
| `yycd_description_html` — rendered rich text (field moving to TinyMCE); existing `yycd_description` stays as stripped plain text | planned — its own session, bundled with the post_content migration |
| `featured_image` for locations | undecided — only needed if the app's Location Home gets a photo hero |

Shipped 2026-07: `text_phone`, `website`, `page_url`, `flyer_url`, sized `logo_url`. See payload table above.

When a row above ships, move its documentation up into the payload table and delete the row.
