# DS Location Manager — App & Design Docs

Documentation for the YYCD mobile app integration and the v5 design language. Lives inside the plugin because this plugin **is** the API the app consumes.

**For the mobile developer (and your Claude):** clone this repo (`dotdors/yesdance`) read-only for reference while working in the mobile repo (`ddorsner/yycd`). The files below are kept current with the code; when they conflict with older documents you've received, these win.

## Reading order

1. **`api-reference.md`** — every endpoint, every payload field, where it's defined in code, and the pending-changes table. This is the living source of truth for the WP↔app contract. Verify against `../rest.php` and `../includes/app-settings.php` if in doubt.
2. **`design-tokens.md`** — the v5 design language (colors, type, shape, photo treatment) with native iOS/Android mappings. Shared by the website and the app; don't fork it.
3. **`mockups/yycd-mobile-ui-mock-v2.html`** — open in a browser, tap the tabs. Six screens; this is the app design target. Design decisions already settled (compact list picker, Location Home structure with quick actions + About card, About Program screen, contact bottom sheet, pinned-post treatment, splash contract) are listed in `plans/2026-07-mobile-alignment.md` §"decisions".
4. **`plans/`** — dated, point-in-time planning documents. Effort estimates and gap analyses age quickly; treat anything here as a snapshot from its filename date, not current truth.

## Maintenance rules

- Changed `rest.php` or `app-settings.php`? **Update `api-reference.md` in the same commit.**
- Changed a design token on the website (`ds-theme-customisations/assets/_variables.less`)? Update `design-tokens.md`.
- New plans get a new dated file in `plans/`; don't rewrite old ones.
- This `docs/` directory doesn't need to be uploaded to the production server (harmless if it is; `index.php` guard included).

## Current work queue (WP side)

See the pending-changes table in `api-reference.md`. In-flight plugin work: unified field save path (single data class behind meta boxes / settings page / REST), settings-page location creation flow, City vs Studio label clarification with title→city sync, TinyMCE for the program description + flyer attachment field, `text_phone` field.
