# YYCD Design Tokens — v5 "Ink · Bone · Red"

## Colors
| Token | Hex | Use |
|---|---|---|
| ink | `#211E26` | text, dark surfaces (splash bg, app bar text) |
| ink-soft | `#5C5861` | secondary text, dates, captions |
| paper | `#FCFBF9` | screen background |
| bone | `#F1EFE9` | alt surfaces: quick-action circles, about card, thumbs |
| line | `#E3E0D8` | hairline borders, dividers |
| accent | `#E8402A` | THE action color: pills, primary buttons, links, active states |
| accent-ink | `#C33420` | accent pressed/hover |
| gold | `#F0A63B` | motif ONLY: eyebrows on dark, pinned ring, small flourishes. Never buttons or large surfaces. |

## Typography
- **Display** (titles, card headings): Fraunces — iOS: "New York" via `.fontDesign(.serif)` is an approved stand-in, or bundle Fraunces; Android: bundle Fraunces variable TTF. Weights: 900 for titles, italic 600 for accent words.
- **Body/UI**: DM Sans — iOS: SF Pro (system) fine; Android: Roboto fine, or bundle DM Sans.
- Eyebrow style: 11–12sp, weight 700, letter-spacing ~0.16em, UPPERCASE, color accent (on light) or gold (on dark).
- Dates use oldstyle numerals where the font supports it.

## Shape & elevation
- Cards: corner radius 22–24, soft layered shadow (approx: y=14 blur=30 @ 30% ink, plus y=2 blur=4 @ 6%).
- Buttons: full pill (Capsule). Primary = accent fill, white text.
- Circular icon buttons (quick actions): 52dp circle, bone fill, line border; "hot" variant = accent fill.
- The ARCH shape (rounded-top arch mask) is reserved for the splash photo — the brand's one special shape.

## Photo treatment (apply to ALL content photos)
- saturation ×0.78, contrast ×1.08, brightness ×1.05
- plus an ink gradient scrim from transparent (top ~60%) to `#211E26` at ~50% opacity (bottom) — anchors badges/labels and unifies mixed event photography.

## Motion
- Subtle only. Press states: slight lift/scale. Respect Reduce Motion. Optional flourish: variable-weight text bloom on the splash wordmark (weight 150→900, ~0.9s ease-out) matching the website hero.
