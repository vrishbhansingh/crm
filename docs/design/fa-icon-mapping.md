# Font Awesome 4.7 → 6.5 icon mapping (V1 reference, not yet applied)

Per the V1 brief: **identify before replacing**. This is a reference only —
no `.blade.php` file has had an icon class touched in this phase. A later
phase does the actual mechanical swap, file by file, using this table.

Source: every `class="fa fa-*"` occurrence across `resources/views/**`
(110 distinct icon names, ~350 total usages), counted via a direct grep —
not a hypothetical universal FA mapping. FA 6.5 is already loaded on 9
files (`auth/*`, `layouts/platform.blade.php`); FA 4.7 is loaded on the
other ~52. The end state is one Font Awesome version everywhere.

## How FA4.7 → FA6 naming works

FA6 splits the old single `fa fa-name` style into three prefixes:
`fa-solid` (filled, the old default), `fa-regular` (outline — replaces
the old `-o` suffix convention), and `fa-brands` (logos). Most solid
icons kept their name and only need the prefix changed; the outline
(`-o`) ones need both a prefix *and* a name change; a smaller set was
renamed outright.

## Group 1 — direct: same name, prefix only (`fa fa-X` → `fa-solid fa-X`)

Majority of usage. No visual/behavior risk beyond the prefix swap.

`fa-plus`(54) `fa-trash`(29) `fa-arrow-left`(14) `fa-paper-plane`(12)
`fa-spinner`(11) `fa-check`(11) `fa-bullseye`(10) `fa-user`(7) `fa-eye`(7)
`fa-envelope`(7) `fa-inbox`(6) `fa-briefcase`(6) `fa-users`(5)
`fa-phone`(5) `fa-lock`(5) `fa-list-ul`(5) `fa-history`(5) `fa-download`(5)
`fa-cog`(5) `fa-upload`(4) `fa-truck`(4) `fa-trophy`(4) `fa-shield`(4)
`fa-home`(4) `fa-print`(3) `fa-power-off`(3) `fa-paperclip`(3)
`fa-list-alt`(3) `fa-chevron-right`(3) `fa-bars`(3) `fa-tasks`(2)
`fa-star`(2) `fa-shopping-cart`(2) `fa-search`(2) `fa-plug`(2)
`fa-percent`(2) `fa-list-ol`(2) `fa-heart`(2) `fa-filter`(2) `fa-cubes`(2)
`fa-credit-card`(2) `fa-comments`(2) `fa-calendar`(2) `fa-building`(2)
`fa-ban`(2) `fa-angle-down`(2) `fa-tags`(1) `fa-table`(1) `fa-server`(1)
`fa-repeat`(1) `fa-key`(1) `fa-globe`(1) `fa-flag`(1) `fa-copy`(1)
`fa-clone`(1) `fa-chevron-left`(1) `fa-camera`(1) `fa-calculator`(1)
`fa-bolt`(1) `fa-arrow-up`(1) `fa-arrow-right`(1) `fa-angle-left`(1)
`fa-file-excel`(1)

## Group 2 — outline (`-o` suffix) → `fa-regular`, name also changes

| FA4.7 | Count | FA6.5 |
|---|---|---|
| `fa-file-text-o` | 11 | `fa-regular fa-file-lines` |
| `fa-envelope-o` | 5 | `fa-regular fa-envelope` |
| `fa-trash-o` | 5 | `fa-regular fa-trash-can` |
| `fa-check-square-o` | 5 | `fa-regular fa-square-check` |
| `fa-building-o` | 7 | `fa-regular fa-building` |
| `fa-clock-o` | 5 | `fa-regular fa-clock` |
| `fa-calendar-o` | 2 | `fa-regular fa-calendar` |
| `fa-bell-o` | 2 | `fa-regular fa-bell` |
| `fa-bell-slash-o` | 2 | `fa-regular fa-bell-slash` |
| `fa-check-circle-o` | 2 | `fa-regular fa-circle-check` |
| `fa-comments-o` | 1 | `fa-regular fa-comments` |
| `fa-circle-o` | 1 | `fa-regular fa-circle` |
| `fa-file-pdf-o` | 2 | `fa-regular fa-file-pdf` |
| `fa-folder-open-o` | 1 | `fa-regular fa-folder-open` |
| `fa-sticky-note-o` | 1 | `fa-regular fa-note-sticky` |
| `fa-user-o` | 1 | `fa-regular fa-user` |
| `fa-file-o` | 1 | `fa-regular fa-file` |
| `fa-handshake-o` | 2 | `fa-regular fa-handshake` |
| `fa-moon-o` | 1 | `fa-regular fa-moon` (theme toggle icon — see `include/header.blade.php`) |
| `fa-share-square-o` | 2 | `fa-regular fa-share-from-square` |

## Group 3 — renamed solid icons (name changed, not just prefix)

| FA4.7 | Count | FA6.5 |
|---|---|---|
| `fa-sign-out` | 5 | `fa-solid fa-right-from-bracket` |
| `fa-refresh` | 5 | `fa-solid fa-arrows-rotate` |
| `fa-random` | 5 | `fa-solid fa-shuffle` |
| `fa-inr` | 2 | `fa-solid fa-indian-rupee-sign` |
| `fa-exchange` | 3 | `fa-solid fa-right-left` |
| `fa-bar-chart` | 3 | `fa-solid fa-chart-bar` |
| `fa-line-chart` | 1 | `fa-solid fa-chart-line` |
| `fa-share-alt` | 1 | `fa-solid fa-share-nodes` |
| `fa-columns` | 1 | `fa-solid fa-table-columns` |
| `fa-exclamation-triangle` | 1 | `fa-solid fa-triangle-exclamation` — **note**: this is the same target class two already-broken usages below resolve to; after migration all three converge correctly. |
| `fa-exclamation-circle` | 1 | `fa-solid fa-circle-exclamation` |

## Group 4 — brand icons (need `fa-brands`)

| FA4.7 | Count | FA6.5 |
|---|---|---|
| `fa-whatsapp` | 5 | `fa-brands fa-whatsapp` |
| `fa-facebook-official` | 1 | `fa-brands fa-facebook` (`-official` variant was removed; plain `facebook` is the closest FA6 equivalent — **manual review**, glyph differs slightly) |

## Group 5 — manual review before touching

- **`fa-user-group`, `fa-triangle-exclamation`, `fa-shield-halved`** — these three are already FA6-native icon *names* being used with the old FA4.7 `fa fa-` prefix. FA 4.7 has no icon by these names, so **these are almost certainly rendering as a blank/missing glyph right now, in production, independent of anything in this redesign.** Pre-existing bug, found in passing — worth a quick look outside this phase's scope. Once the file(s) using them move to FA6, drop the extra prefix change (`fa-solid`/`fa-regular` as appropriate) and they'll resolve correctly for the first time.
- **`fa-magic`** — removed from recent FA6 Free icon sets in favor of `fa-wand-magic-sparkles`. Verify against the exact FA6.5 kit this app vendors before swapping; if the alias isn't present the icon will silently disappear.
- **`fa-save`, `fa-times`, `fa-info-circle`, `fa-home`, `fa-check-circle`, `fa-plus-circle`, `fa-cog`** — FA6 keeps these as deprecated aliases (`fa-save`→`fa-floppy-disk`, `fa-times`→`fa-xmark`, `fa-info-circle`→`fa-circle-info`, `fa-home`→`fa-house`, `fa-check-circle`→`fa-circle-check`, `fa-plus-circle`→`fa-circle-plus`, `fa-cog`→`fa-gear`). The alias renders correctly today, so these are **not urgent**, but a later cleanup should move to the canonical name since aliases are the first thing dropped in a future major version.
- **`fa-pencil`** — FA6 Free dropped the bare `fa-pencil` alias in some kit configurations (the closest solid icon is `fa-pen`; `fa-pencil` itself was reintroduced in later 6.x point releases as a distinct icon). Confirm which exact 6.5.x behavior applies before the 22 occurrences of this one are swapped — the highest-count item in this review group.
- **`fa-arrow-`** — one grep match was truncated before the full class name (regex artifact, not a real class). Needs a direct look at its source line before mapping; not included in any count above.

## Feather / Themify icons (separate cleanup, not FA)

Feather icons (`vendors/feather/feather.css`) load on 21 files and
Themify icons (`vendors/ti-icons/`) on 13, layered on top of FA on some
pages (e.g. `admin/lead/lead.blade.php` loads all three at once). Once
every page is on one FA version, a follow-up pass should map any
Feather/Themify glyph actually in use to its closest FA6 equivalent and
drop those two libraries — out of scope for this reference, which only
covers FA usage as the V1 brief asked.
