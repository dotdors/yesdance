# YYCD Mobile App — v5 Design Alignment Plan

*Prepared 2026-07-15 · based on `ddorsner/yycd` (mobile) and `dotdors/yesdance` (WP) as of today, plus the approved v5 design language and the mobile UI mock (`yycd-mobile-ui-mock.html`).*

---

## 1. Current state

**Architecture:** Kotlin Multiplatform. A `shared` module (Ktor client, `YYCDRepository`, kotlinx-serialization models) serves a SwiftUI iOS app and an Android app. API base: `https://dandysite.com/yycd/wp-json/ds/v1`.

**iOS screens:** Splash (WP-managed via `/splash`) → LocationPicker → ArticleList (per-location posts, paginated) → ArticleDetail, with ContactView as a sheet (call / text / email / directions intents already implemented). UI is entirely default platform chrome — plain Lists, system blue, no brand.

**Android:** significantly behind iOS — view-based XML layouts, still on the `wordpressnewsletter` template package, no location picker flow parity.

## 2. Gap analysis — data

The key finding: **the WP API already returns nearly everything the redesigned app needs.** `/ds/v1/locations` includes `yycd_description` (the About Our YYCD Program content), `description` (tagline), `address`, `contact` (name), `city`, and `logo_url`. The app-side `LocationDetail` model only deserializes `id, name, phone, email, latitude, longitude` and silently drops the rest.

| Field | WP API | App model | App UI | Action |
|---|---|---|---|---|
| yycd_description (About) | ✅ | ❌ | ❌ | Add to model + Location Home UI |
| description (tagline) | ✅ | ❌ | ❌ | Add to model + UI |
| address | ✅ | ❌ | ❌ | Add to model + contact sheet |
| contact name | ✅ | ❌ | ❌ | Add to model + contact sheet |
| city | ✅ | ❌ | ❌ | Add to model + eyebrows/badges |
| logo_url | ✅ | ❌ | ❌ | Add to model + picker/header |
| featured image (hero photo) | ❌ | ❌ | ❌ | **Add to WP endpoint**, then model + picker cards |
| website | ❌ | ❌ | ❌ | **Add to WP endpoint**, then contact sheet |
| text number (new field) | ❌ (field doesn't exist yet) | ❌ | text intent exists, uses phone | **New meta field + endpoint + model**; UI hook already built |
| sticky posts | ✅ | ✅ | ❌ (not surfaced) | "Pinned" treatment per mock |

One code smell worth fixing while in there: `getLocationDetail()` fetches the **entire locations list** and filters client-side. Fine at 4 locations; add a `/locations/{id}` endpoint (or accept it for now and note it).

## 3. Design language mapping (web → native)

| Token | Web (v5) | iOS | Android (Compose) |
|---|---|---|---|
| Ink / Paper / Bone / Line | #211E26 / #FCFBF9 / #F1EFE9 / #E3E0D8 | Asset catalog colors (+ dark-mode slots for later) | Compose `ColorScheme` overrides |
| Accent / hover | #E8402A / #C33420 | `Color("Accent")` | `primary` |
| Gold (motif only) | #F0A63B | sparing: pinned ring, eyebrows | same |
| Display type | Fraunces 900 / italic 600 | **"New York" via `.fontDesign(.serif)`** (free, excellent match) or bundle Fraunces | bundle Fraunces variable TTF |
| Body type | DM Sans | SF Pro (system) | Roboto or bundle DM Sans |
| Cards | 24px radius + layered shadow | `RoundedRectangle(cornerRadius: 24)` + shadow | `Card(shape = RoundedCornerShape(24.dp))` |
| Buttons | red pills | `Capsule()` fill | `Button` w/ pill shape |
| Photo treatment | saturate .78, contrast 1.08 + ink scrim | `.saturation(0.78).contrast(1.08)` + gradient overlay | Coil + `ColorMatrix` or gradient overlay |
| Arch mask | hero/splash only | custom `Shape` (splash) | custom `Shape` |

## 4. Work plan & effort

Estimates assume your mobile dev's familiarity with the codebase; treat as calibration ranges, not quotes.

**Phase A — WordPress side (Nancy, ~2–4 hrs).** Add `featured_image` and `website` to the `/locations` payload (both already exist as meta/thumbnail — a few lines in `ds_get_locations_app()`). Add the new **Text Number** meta field (meta-boxes.php + display data + REST), defaulting UI hint "same as phone." This also feeds the website contact card, so it pays twice.

**Phase B — Shared module (~2–3 hrs).** Extend `LocationDetail` with the new optional fields (all `@SerialName` + defaults, so it's non-breaking). Optionally add `/locations/{id}` fetch. Bump nothing else — Ktor/serialization handle unknowns already.

**Phase C — iOS reskin (~2–4 days).** Create a `Theme.swift` (Color + Font extensions from the token table). Restyle the five existing views to the mock: splash (ink stage, arch shape, pill CTA), location picker → photo cards, ArticleList → **Location Home** (quick-action row: Call / Text / Email / Directions surfaced at top; About section — tagline lede + `yycd_description`; news cards with badges + pinned treatment), ArticleDetail (treated hero, serif title), Contact sheet → action-card grid. No architecture changes; this is view-layer work.

**Phase D — Android (~1–2 weeks).** Honest recommendation: **rebuild the Android UI in Jetpack Compose** against the same token values rather than reskinning the legacy XML/newsletter code. It's behind on flow parity anyway, so a reskin would be throwing effort at code that needs replacing. The shared module carries over untouched — that's the payoff of the KMP setup.

**Phase E — Polish (~1–3 days, can trail).** App icon + adaptive icon from the footprint mark; branded launch screen; empty states ("no news yet" in brand voice, not a blank list); pull-to-refresh; error states.

**Suggested order:** A → B → C, ship iOS, then D, then E alongside.

## 5. Suggestions beyond parity

- **Location Home replaces "article list" as the app's mental model.** The location IS the app for a user; news is one section of it, not the whole screen. The About content and quick actions matter more to a caregiver than the post feed.
- **Accessibility is a brand promise here, not a checkbox.** Support Dynamic Type (serif display scales fine), VoiceOver labels on the quick actions, respect Reduce Motion, and keep touch targets ≥44pt. The audience skews toward exactly the users these settings serve.
- **Remember the chosen location** (UserDefaults / DataStore) and skip straight to Location Home on relaunch, with a switcher in the app bar — matches the website's location-picker persistence.
- **Cache last-fetched content** (posts + location detail) so the app opens usefully offline; even a naive JSON cache beats a spinner.
- **Deep links** (`yycd://location/{id}`, universal links to post URLs) — cheap now, painful to retrofit.
- **Defer push notifications** — high infra cost, low value at four locations; revisit when locations post regularly.
- **Later:** dark mode falls out nearly free once tokens are centralized (ink/paper invert), but hold it until light mode ships — same call you made on the website.

## 6. Open questions for the client / next session

1. Confirm Text Number semantics: fallback to phone when empty, or hide the Text action?
2. Featured image for locations: reuse the location page hero image meta, or a separate app-specific image?
3. Android timeline pressure — does iOS-first shipping work for the client?
