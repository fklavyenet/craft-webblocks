# WebBlocks — Developer Guide

This file is for contributors and maintainers of the WebBlocks plugin itself. If you are a Craft developer installing WebBlocks into your project, see [README.md](README.md).

---

## Environment

| | |
|---|---|
| DDEV test site | `https://restaurant.ddev.site/` |
| Restaurant project root | `/Users/osm/Sites/fklavyenet-webblocks/restaurant` |
| Plugin source | `/Users/osm/Sites/fklavyenet-webblocks/webblocks/src/` |

The restaurant project symlinks the plugin source via `path` repository in its `composer.json`. Changes to plugin source files take effect immediately without pushing to Packagist.

---

## Key Commands

Run all commands from the **restaurant project root** (`/Users/osm/Sites/fklavyenet-webblocks/restaurant`).

```bash
# Wipe everything and uninstall plugin
ddev craft webblocks/wipe/all --interactive=0

# Reinstall plugin (runs InstallService automatically)
ddev craft plugin/install webblocks --interactive=0

# Seed demo content
ddev craft webblocks/seed --interactive=0

# Full wipe → reinstall → reseed in one line
ddev craft webblocks/wipe/all --interactive=0 && ddev craft plugin/install webblocks --interactive=0 && ddev craft webblocks/seed --interactive=0
```

**When do changes require reinstall?**

| Change type | Reinstall needed? |
|---|---|
| `wbTemplates/` Twig files | No — take effect immediately |
| `wbComponents/` JSON files (fields, entry types, matrix fields) | Yes — wipe + reinstall |
| PHP service/controller changes | Yes — plugin reload |

---

## Architecture

### Install pipeline (`InstallService.php`)

`InstallService` auto-discovers and installs everything from JSON files:

```
wbComponents/fields/          → Craft fields
wbComponents/entrytypes/      → Entry types (with field layouts)
wbComponents/matrixfields/    → Matrix fields (referencing entry types by handle)
wbComponents/imagetransforms/ → Image transforms
```

Order matters: fields → entry types → matrix fields (entry types must exist before matrix fields reference them).

### Block system

- `wbBlocks` is the top-level matrix field attached to all page-level entry types.
- Every block type is an entry type registered in `wbBlocks.json`.
- Nested blocks: parent entry type → own matrix field → child entry type → `wbBlocks` again (e.g. `wbColumns` → `wbColumnItems` → `wbColumn` → `wbBlocks`).

### Template routing

`wb/index.twig` receives `entry` and loops `entry.wbBlocks.all()`, dispatching each block to:

```twig
{% include "wb/components/#{item.type.handle}" %}
```

The `wb` Twig root maps to `src/wbTemplates/` (registered in `WebBlocks.php`).

### Seed system (`SeedController.php`)

Two dispatchers:

- **`buildInlineBlocksData()`** — handles blocks defined inline in `pages.json`. Every new block type needs an `elseif` branch here.
- **`buildBlocksData()`** — handles named component files in `seed/components/` (used by the `wb-demo` test page).

Both return Craft matrix block data arrays suitable for `Entry::setFieldValue('wbBlocks', $blocks)`.

---

## Adding a New Block

1. **Field JSON** — add any new fields to `src/wbComponents/fields/`.
2. **Entry type JSON** — create `src/wbComponents/entrytypes/wbMyBlock.json` with the field layout.
3. **Register in matrix** — add the new entry type handle to `src/wbComponents/matrixfields/wbBlocks.json`.
4. **Template** — create `src/wbTemplates/components/wbMyBlock.twig`.
5. **Seed** — add an `elseif` branch in `SeedController::buildInlineBlocksData()` and add demo data to `pages.json`.
6. **Wipe + reinstall + reseed** to test.

All handles must use the `wb` prefix.

---

## File Structure

```
src/
├── WebBlocks.php                        Plugin bootstrap; registers 'wb' template root, asset bundle
├── assetbundles/
│   └── WebBlocksAsset.php               Loads wb-blocks.css + wb-blocks.js on front-end requests
├── controllers/
│   ├── CommentController.php            CP actions: approve/reject a wbComment entry
│   └── FormController.php               Front-end form submission + email dispatch
├── elementactions/
│   ├── ApproveComment.php               Sets selected wbComment entries to enabled=true
│   ├── RejectComment.php                Sets selected wbComment entries to enabled=false
│   ├── MarkSubmissionRead.php           Sets selected wbSubmission entries to status=read
│   ├── MarkSubmissionUnread.php         Sets selected wbSubmission entries to status=unread
│   └── ArchiveSubmission.php            Sets selected wbSubmission entries to status=archived
├── services/
│   ├── InstallService.php               Orchestrates install order, field layout building
│   └── FieldInstallService.php          Creates Craft fields from JSON definitions
├── console/
│   ├── InstallController.php            Console: install plugin data
│   ├── WipeController.php               Console: wipe all plugin data (Craft 5 API)
│   └── SeedController.php               Console: seed demo content (Ember & Rye steakhouse)
├── resources/
│   ├── css/wb-blocks.css                Fullscreen slider styles, masonry, lightbox overrides
│   ├── js/wb-blocks.js                  Masonry, lightbox, fullscreen slider — all vanilla JS
│   └── seed/
│       ├── pages.json                   Static page definitions with inline block data
│       ├── images.json                  23 sample images (8 food + 10 gallery + 5 fullscreen)
│       └── components/                  Named block files for wb-demo test page
├── translations/
│   ├── en/webblocks.php                 English translations (error pages, blog, search, CP)
│   ├── tr/webblocks.php                 Turkish translations
│   └── de/webblocks.php                 German translations
├── wbComponents/
│   ├── entrytypes/                      One JSON per entry type
│   ├── fields/                          One JSON per custom field
│   ├── matrixfields/                    One JSON per matrix field
│   └── imagetransforms/                 One JSON per image transform
└── wbTemplates/                         Twig templates (root alias: 'wb')
    ├── index.twig                       Entry point — dispatches to wb/components/{handle}
    ├── layout.twig                      Bootstrap CDN + asset bundle
    ├── components/                      One .twig per block type
    └── partials/                        Shared partials (cookie banner, SEO meta, etc.)
```

---

## Design Decisions

| Decision | Detail |
|---|---|
| Zero external JS | Bootstrap CDN only. Masonry, lightbox, fullscreen slider all vanilla JS in `wb-blocks.js` |
| Asset bundle | `WebBlocksAsset.php` loads `wb-blocks.css` + `wb-blocks.js` on every front-end page request |
| WB Lightbox | Reads `data-fancybox` (group) + `data-caption` attributes — drop-in compatible with Fancybox markup |
| Masonry | CSS grid `grid-row: span N`; `data-wb-masonry` on the container; JS sets `grid-auto-rows: 4px` |
| Hero/Carousel scrim | Hero: `rgba(0,0,0,0.25)` base + gradient pseudo-element. Carousel: gradient + frosted-glass caption (`backdrop-filter: blur`) |
| Pagination | `pageUrl` macro reads `craft.app.config.general.pageTrigger`; generates `/blog/p2` path-based URLs. Disabled buttons render as `<span>` not `<a>` |
| Global sets in console | `getGlobals()->getSetByHandle()` requires explicit `$primarySiteId` in console context |
| wbBlocks hard-delete on wipe | `WipeController::hardDeleteSoftDeleted()` purges `elements WHERE dateDeleted IS NOT NULL` — prevents ~500 row bloat per cycle |
| Metadata row ordering trick | `EVENT_DEFINE_METADATA` fires before `getMetadata()` calls `array_merge([ID,Status], $event->metadata, [Created at, Updated at, Notes])`. PHP `array_merge` with string keys: first occurrence wins **position**, last wins **value**. Insert `'Created at' => false` and `'Updated at' => false` into `$event->metadata` before your own key — Craft's real values override them, but your key stays anchored after "Updated at". |
| Comment inline toggle | `_registerCommentApprovalToggle()` uses `EVENT_DEFINE_METADATA` + `Cp::lightswitchHtml()` + `registerJsWithVars` + `Craft.sendActionRequest`. No page save required — AJAX POST to `webblocks/comment/approve` or `reject`, then `window.location.href` reload. |
| CP translations | All CP-facing strings use `\Craft::t('webblocks', ...)` and are defined in `src/translations/{en,tr,de}/webblocks.php`. |

---

## Versioning & Release

- Follows [Semantic Versioning](https://semver.org/): `MAJOR.MINOR.PATCH`
- Before tagging: update `CHANGELOG.md`, bump `version` in `composer.json`, commit, then tag
- Tag format: `v1.2.3`

```bash
git tag -a v1.2.3 -m "Release v1.2.3"
git push origin v1.2.3
```

---

## Core Rules

- All new handles use the `wb` prefix (fields, entry types, matrix fields, sections, globals)
- Bootstrap 5.3 CDN is the only external dependency — zero external JS libraries
- LSP errors in PHP files are pre-existing Craft CMS type stub issues — ignore them
