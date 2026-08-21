# Design QA — Public Website / Design 1

## Evidence

- Source visual: `design-options/01-cinematic-arrival.png` (863 × 1822 concept canvas).
- Desktop implementation: `qa-home-desktop-top.png` at a requested 1440 × 1024 viewport (1425 CSS px content width after the browser scrollbar).
- Mobile implementation: `qa-home-mobile-top.png` at a requested 390 × 844 viewport (375 CSS px content width after browser chrome/scrollbar).
- State: public home page, unauthenticated, with generated fleet fallback photography because the current database has no published cars.
- Density normalization: the source is a tall presentation canvas, so comparison used shared composition, hierarchy, spacing rhythm, typography, colors, and component proportions rather than pixel-for-pixel raster scaling.

## Comparison

The source and both implementation screenshots were reviewed together. Focused regions included the navigation/header, cinematic hero, CTA pair, booking panel, floating WhatsApp control, fleet-card treatment, and the first mobile viewport.

The implementation preserves the selected direction: a dark navy/black premium surface, restrained gold accents, Cairo typography, a Mecca/Medina luxury fleet hero, strong Arabic headline hierarchy, gold primary actions, bordered secondary actions, and an RTL booking experience. Desktop and mobile have no horizontal overflow.

## Required surfaces

- Typography: Cairo is active (`Cairo, ui-sans-serif, system-ui, sans-serif`).
- Layout: responsive RTL header, hero, booking controls, fleet cards, content sections, and footer.
- Brand assets: transparent Fakhamah Mosafer logo and a clean hero image derived from the supplied assets.
- Content: Arabic public navigation, calls to action, service copy, and FAQs.
- Interaction: mobile navigation toggle and fleet navigation verified in-browser; booking/contact behavior and validation covered by feature tests.
- Runtime: no browser console warnings or errors in the checked home-page state.

## Findings and iteration history

1. **P2 — Hero collision:** the supplied hero contained baked-in branding and text that competed with live HTML content. Fixed by producing `public/assets/images/hero-clean.png` while preserving the vehicles and holy-city setting. Post-fix evidence shows one clear headline and CTA hierarchy.
2. **P2 — Logo background:** the original rectangular logo treatment looked pasted onto the header. Fixed with `public/assets/images/logo-clean.png`; corner alpha was verified as transparent.
3. **P2 — Language consistency:** seeded FAQs were English inside the Arabic public site. Fixed by translating the FAQ seeder and reseeding current data.
4. **P2 — Mobile obstruction:** the floating WhatsApp control sat on the RTL content edge. Moved to the physical left; final mobile measurement is 20 px from the left and 20 px from the bottom.
5. **P3 — Decorative motif:** the concept's compass ornament was intentionally omitted instead of recreating it as fake CSS/SVG artwork. This does not affect hierarchy or conversion flow.
6. **P3 — Fleet content:** generated BMW, GMC, and Staria images provide a coherent empty-database fallback. Dashboard-managed published cars replace these automatically.

## Verification

- `php artisan test --compact`: 28 passed, 110 assertions.
- `npm run build`: production Vite build passed.
- `git diff --check`: passed.
- Responsive measurements: desktop 1425/1425 and mobile 375/375 client/scroll widths.

## Final result

**Passed.** No unresolved P0, P1, or P2 design QA findings remain.
