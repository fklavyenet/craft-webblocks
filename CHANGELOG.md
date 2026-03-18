# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/) and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## Unreleased

## 1.10.4 - 2026-03-18

### Fixed
- **`wb-blocks.css` — form field spacing added** — `.wb-field { margin-bottom: 1rem }` and `.wb-radio { margin-bottom: 0.5rem }` added for proper spacing between form fields.
- **Seed data — `text-muted` → `wb-text-muted`** — Contact page seed data (`pages.json`, `pages.de.json`, `pages.tr.json`) updated from Bootstrap `text-muted` class to WebBlocks UI Kit `wb-text-muted`.
- **`SeedController.php` — Bootstrap CSS variables removed** — Custom CSS output no longer uses `--bs-primary`, `--bs-link-color`, or `.btn-primary` styles. Replaced with `--wb-primary` and `--wb-primary-hover`.
- **`wbHeading.twig` — text alignment fixed** — Alignment values now mapped from `start/center/end` to `left/center/right` before outputting `wb-text-*` classes (WebBlocks UI Kit uses `wb-text-left` not `wb-text-start`).
- **`wbTextBlock.twig` — unused `text-block` class removed.**
- **Fallback templates (9 files) — Bootstrap classes replaced** — All 9 files in `fallback-templates/wb/sections/` updated: `card`, `card-header`, `card-body`, `card-text`, `bg-warning`, `text-dark`, `text-muted`, `bg-light`, `p-3`, `rounded`, `fw-bold` replaced with WebBlocks UI Kit equivalents (`wb-alert`, `wb-text-muted`, `wb-bg-light`, `wb-p-3`, `wb-rounded`).

## 1.10.3 - 2026-03-18

### Fixed
- **`cp/help.twig` — Colour Themes section rewritten** — The Bootswatch/Bootstrap theme table (18 themes: Cerulean, Cosmo, Darkly, etc.) replaced with three updated sections: **Accent Colours** (8 palettes: ocean, forest, sunset, royal, mint, amber, rose, slate-fire), **Style Presets** (5 packages: modern, minimal, rounded, bold, editorial), and **Colour Mode** (light/dark/auto explanation with `WBColorMode.applyMode()` reference). All new strings translated in EN/TR/DE/ES.
- **`wb-blocks.js` — `data-bs-theme` replaced with `data-mode`** — `applyMode()` now sets `data-mode` (not `data-bs-theme`) on `<html>` for consistency with WebBlocks UI Kit colour mode system. The `data-wb-mode` attribute was also removed as it served no purpose.
- **`fallback-templates/wb/_layout.twig` — Bootstrap CDN replaced** — The legacy fallback layout template (not used in production) had a Bootstrap CDN `<link>`. Replaced with WebBlocks UI Kit CDN.
- **Translation files — 21 old Bootswatch keys removed, 17 new keys added** — All four locale files (`en`, `tr`, `de`, `es`) updated with new keys for Accent Colours, Style Presets, and Colour Mode sections. Old Bootswatch theme strings (`Cerulean`, `Cosmo`, `Darkly`, etc.) removed. "Bootstrap 5.3 CDN" reference replaced with "WebBlocks UI Kit CSS + JS". All 4 files remain in sync with 147 keys each.

## 1.10.2 - 2026-03-18

### Fixed
- **`wbNavbar.twig` — desktop nav links now visible** — `wb-navbar-links` was incorrectly placed inside `wb-navbar-drawer`, which is hidden on desktop via `display:none !important`. Rewritten with `wb-navbar-links` rendered directly inside `.wb-container-full` for desktop, with a separate copy inside the drawer for mobile. Macro pattern used to avoid duplicating the link-rendering logic.
- **`wbNavbar.twig` — search, language switcher, and theme toggle always visible** — All `wb-navbar-end` controls now sit outside the drawer so they are accessible on desktop without opening the hamburger menu.
- **`layout.twig` — custom CSS HTML entity encoding** — `{{ _customCss }}` was Twig auto-escaped, turning apostrophes and quotes into `&#039;` and `&quot;` inside injected `<style>` blocks. Added `|raw` filter.
- **`wbCard.twig`, `blog-index.twig`, `references-index.twig`, `service.twig` — card title spacing** — `.wb-card-title` has `margin:0` in WebBlocks UI Kit, causing the title to touch the card body. Added `wb-mb-2` utility class.
- **18 component templates — stray `mb-3`/`mb-4` Bootstrap classes** — Leftover Bootstrap margin utilities from the v1.10.0 migration replaced with `wb-mb-3`/`wb-mb-4` equivalents (`wbAccordion`, `wbAlert`, `wbBadge`, `wbButton`, `wbButtonGroup`, `wbCallToAction`, `wbColumns`, `wbContactDetails`, `wbGallery`, `wbHeading`, `wbHero`, `wbPagination`, `wbPopover`, `wbProgressBar`, `wbSpinner`, `wbTabs`, `wbTextBlock`).

## 1.10.1 - 2026-03-18

### Fixed
- **`layout.twig` — `data-preset` → `data-accent`** — The colour-theme attribute on `<html>` was incorrectly set to `data-preset` (which controls the style preset: modern/minimal/rounded/bold/editorial). Colour themes (ocean, forest, sunset, etc.) must use `data-accent`. The two attributes are independent: `data-accent` sets the brand colour, `data-preset` sets the component shape/style.
- **`layout.twig` — stray `data-wb-theme` on `<body>` removed** — A leftover attribute from the Bootstrap migration era served no purpose and has been removed.
- **`wbOffcanvas.twig` — drawer class and close-button corrected** — The outer wrapper had an incorrect `wb-offcanvas` class (does not exist in WebBlocks UI Kit) and a redundant `mb-4` utility. Replaced with a plain `wb-block` wrapper. The close button used `wb-drawer-close` (non-existent); replaced with the correct `wb-btn-close` + `data-wb-dismiss="drawer"`.
- **`wbToast.twig` — static HTML replaced with `WBToast.show()` JS API** — WebBlocks UI Kit does not support static HTML toast markup; toasts must be triggered via `WBToast.show(message, { type, duration })`. Template rewritten to emit an inline `<script>` that calls the API on DOMContentLoaded. A `<noscript>` fallback paragraph is included for accessibility.

### Changed
- **`wb-blocks.css`** — Added an explanatory comment to the `.wb-navbar` block noting that the navbar is sticky by default in WebBlocks UI Kit and `wb-navbar--static` is required to opt out.

## 1.10.0 - 2026-03-18

### Changed
- **Bootstrap → WebBlocks UI Kit migration** — All 38 front-end files (`wbTemplates/`, `wb-blocks.css`) migrated from Bootstrap 5.3 CDN to the `fklavyenet/webblocks-ui` UI Kit. The CDN links in `layout.twig` now point to `cdn.jsdelivr.net/gh/fklavyenet/webblocks-ui@master/dist/webblocks-ui.{css,js}`. Bootstrap is no longer a dependency.
- **`layout.twig`** — Bootstrap CDN `<link>`/`<script>` replaced with WebBlocks UI Kit CDN. Bootswatch theme support removed. `data-bs-theme` / `data-wb-mode` attributes replaced with `data-mode`. `data-bs-theme` body attribute replaced with `data-preset` on `<html>` for the color theme system.
- **All component templates** — Bootstrap utility classes (`container`, `row`, `col-*`, `btn`, `alert`, `badge`, `card`, `accordion`, `modal`, `offcanvas`, `nav-*`, `navbar`, `breadcrumb`, `list-group`, `pagination`, `progress`, `spinner`, `toast`, `popover`, `d-*`, `flex-*`, `gap-*`, `p-*`, `m-*`, `text-*`, `bg-*`, `border-*`, `rounded-*`) replaced with WebBlocks UI Kit equivalents (`wb-container`, `wb-row`, `wb-col-*`, `wb-btn`, `wb-alert`, `wb-badge`, `wb-card`, etc.).
- **`wbCarousel.twig`** — Rewritten as a wb-native slider using the same `data-wb-fs-slider` / `wb-fs-*` pattern as `wbFullscreenImage`. Bootstrap carousel JS dependency removed entirely.
- **`wbVideoEmbed.twig`** — `ratio ratio-16x9` replaced with `style="aspect-ratio:16/9;overflow:hidden;"`.
- **`wbSpacing.twig`** — `py-N` Bootstrap spacing utilities replaced with an inline padding size map (`1`→`0.25rem` … `5`→`3rem`).
- **`wbTable.twig`** — `table table-*` classes replaced with `wb-table wb-table-*` equivalents.
- **`partials/footer.twig`** — `container`, `row`, `col-*`, `text-*`, `bg-*` classes replaced with `wb-container` and CSS custom properties.
- **`partials/cookie-banner.twig`** — `.btn` classes replaced with `.wb-btn`.
- **`partials/comment-form.twig`** — All Bootstrap form, alert, and visibility classes converted; JS `d-none` toggle updated.
- **`partials/comment-list.twig`** — Flex, spacing, and colour utility classes converted.
- **`sections/search.twig`** — `input-group`, `list-group`, pagination, and container classes converted.
- **`sections/blog-index.twig`, `references-index.twig`, `service.twig`, `post.twig`, `reference.twig`** — Card grids, pagination, featured image, lead, and border-top utilities converted.
- **`wb-blocks.css`** — All `var(--bs-*)` CSS variables replaced with `var(--wb-*)` equivalents. Dark mode selectors updated from `html[data-bs-theme="dark"]` / `html[data-wb-mode="*"]` to `html[data-mode="*"]`. `.btn` reference in cookie actions updated to `.wb-btn`.

### Removed
- Bootstrap 5.3 CDN dependency — no Bootstrap CSS or JS is loaded on any page.
- Bootswatch theme system — `wbColorTheme` dropdown values that previously mapped to Bootswatch CDN URLs are now mapped to `data-preset` on `<html>`.

## 1.9.2 - 2026-03-16

### Added
- **Navbar background opacity** — Added CSS styling to `.wb-navbar` with `background: rgba(255, 255, 255, 0.95)` in light mode (with `backdrop-filter: blur(4px)`) and `rgba(33, 37, 41, 0.95)` in dark mode for improved visibility and modern frosted-glass effect.

### Fixed
- **Carousel and gallery block titles not displaying in CP** — Seed data for carousel and gallery blocks in `pages.json` and language variants (`pages.de.json`, `pages.tr.json`) were missing the `"title"` root-level key, causing Craft's `titleFormat` auto-generation to fail. Added `"title"` and corresponding `"wbTitle"` field values to all carousel and gallery blocks across all language files.
- **SeedController builders not passing through root-level title** — Updated `buildCarouselBlock()` and `buildGalleryBlock()` methods to explicitly pass the `"title"` key from seed data to the entry creation array, ensuring nested entry titles are properly set even when `hasTitleField: false`.

### Changed
- `src/resources/seed/components/wbCarousel.json` — Added root `"title"` key
- `src/resources/seed/components/wbGallery.json` — Added root `"title"` key
- `src/resources/seed/pages.json` — Added `"title"` and `"wbTitle"` to carousel block and both gallery blocks
- `src/resources/seed/pages.de.json` — Added `"title"` and `"wbTitle"` to carousel block and both gallery blocks
- `src/resources/seed/pages.tr.json` — Added `"title"` and `"wbTitle"` to carousel block and both gallery blocks
- `src/console/SeedController.php` — `buildCarouselBlock()` and `buildGalleryBlock()` now pass through root-level `"title"` from seed data

## 1.9.1 - 2026-03-15

### Added
- **CP Help page full i18n** — `help.twig` rewritten with `|t('webblocks')` on every visible string (headings, table cells, body paragraphs, list items, warning notices). 95 new translation keys added to all locale files; `'Component Health'` CP nav label added to all locales.
- **Project title header on Help page** — static `<h1>Fklavyenet WebBlocks</h1>` + `<p>CraftCMS Plugin</p>` rendered at the top of the Help page for clear plugin branding.
- **Spanish (ES) translations** — `src/translations/es/webblocks.php` with 147 keys covering all categories: error pages, blog/references, search, CP navigation, plugin settings, help page, and comment moderation. In full sync with EN/TR/DE.

### Changed
- All four locale files (`en`, `tr`, `de`, `es`) now contain 147 translation keys each.

## 1.9.0 - 2026-03-14

### Added
- **Faz 5: Deprecated field lifecycle** — `deprecateField` migration action now records the deprecated field in a new `webblocks_deprecated_fields` tracking table. Deprecated fields are removed from all entry type layouts but their data is preserved in the database until an explicit cleanup command is run.
- `DeprecatedFieldService` — new service with `markDeprecated()`, `getDeprecated()` (includes `fieldExists` bool), `purge()` (hard-deletes Craft field + removes tracking row), `untrack()`, and `isDeprecated()`.
- `m260314_000001_add_deprecated_fields_table` — Craft content migration that creates `webblocks_deprecated_fields` (columns: `fieldHandle`, `deprecatedAt`, `migrationSource`, `notes`, `dateCreated`, `dateUpdated`, `uid`; unique index on `fieldHandle`) for existing installs. Fresh installs get the table via the updated `Install::safeUp()`.
- `webblocks/components/cleanup-deprecated` console command — lists all pending deprecated fields with handle, deprecation timestamp, field-exists status, and migration source. Pass `--force` to permanently delete each field and its content data from Craft (prompts for confirmation unless `--interactive=0`). Exits 0 when no deprecated fields remain; exits 1 otherwise.
- **CP health screen — Deprecated Fields section** — the `webblocks/health` page now shows a "Deprecated Fields" table below the issues list whenever tracked deprecated fields exist, with a reminder of the cleanup command.

### Changed
- `ComponentMigrator::dispatchAction()` — builds a `$migrationSource` string (`"type/handle vX→vY"`) and passes it to `actionDeprecateField()`.
- `ComponentMigrator::actionDeprecateField()` — now accepts `$migrationSource`, calls `DeprecatedFieldService::markDeprecated()`, and wraps `getField()` calls in `try/catch` to guard against `craft\errors\FieldNotFoundException` on orphaned layout element references.
- `ComponentHealthController::actionIndex()` — now fetches deprecated fields from `DeprecatedFieldService` and passes them to the health template.
- `Install::safeDown()` — updated to also drop `webblocks_deprecated_fields` on uninstall.

## 1.8.0 - 2026-03-14

### Added
- **Plugin Settings page** — full CP settings page (`webblocks/settings`) with four sections: Email, SEO, Seed Content, and Analytics. Replaces the previous empty settings stub.
- `adminEmail` setting — fallback recipient for wbForm admin notification emails when a form's own Recipient field is empty.
- `commentNotificationEmail` setting — when set, an email is sent to this address every time a new comment is submitted and awaiting moderation.
- `seoTitleFormat` setting — configurable `<title>` tag format using `{title}` and `{siteName}` placeholders; centralised rendering in `layout.twig` replaces per-template `{% block title %}` overrides.
- `seedLanguages` setting — array of language codes (`en`, `tr`, `de`) to seed on `webblocks/seed`; also exposed as a `--languages` CLI option on `SeedController`.
- `ga4MeasurementId` setting — Google Analytics 4 Measurement ID; when set, `gtag.js` is automatically injected before `</head>` in `layout.twig`.
- `matomoUrl` + `matomoSiteId` settings — Matomo analytics; when both are set, the Matomo tracking snippet is injected before `</head>`.
- `commentNotificationEmail`: `CommentController` sends an HTML notification email on every new comment submission.
- **Plugin removal safety** — `layout.twig` guards `craft.webblocks.settings` access with `isPluginEnabled('webblocks')`; all settings-dependent features degrade gracefully to null/defaults if the plugin is disabled. `beforeUninstall()` logs a warning; user content (entries, fields, sections) is never deleted automatically on uninstall.

### Changed
- `SeedController`: replaced hardcoded `EXTRA_SITES` constant with `ALL_EXTRA_SITES` + `_getActiveSeedLanguages()` helper — seed languages are now filtered by the `seedLanguages` plugin setting at runtime.
- `layout.twig`: `<title>` tag centralised; per-template `{% block title %}` overrides removed from all section templates (`page`, `post`, `blog-index`, `service`, `reference`, `references-index`).
- CP translation strings for all new settings fields added to `src/translations/{en,tr,de}/webblocks.php`.

## 1.7.0 - 2026-03-13

### Added
- **CSS Color Theme system** — a `wbColorTheme` dropdown (Default / Dark / Warm / Cool / Forest / Ocean / Custom) on `wbSiteConfig`. The selected value is written as `data-wb-theme="…"` on the `<body>` element; all theme styling is handled via CSS `[data-wb-theme]` selectors in `wb-blocks.css` targeting stable helper classes (`.wb-footer`, `.wb-theme-btn`, `.wb-theme-badge`, `.wb-theme-alert`) — no per-field values stored.
- `WebBlocksCpAsset` — dedicated CP-only asset bundle (`wb-cp.js`) ready for future CP enhancements.
- `InstallService::installGlobalSets()` now updates the field layout of existing global sets on reinstall instead of skipping them — prevents new fields from being invisible in the CP after a wipe+reinstall cycle.

### Changed
- `wbSiteConfig` global set reduced to four fields: `wbColorMode`, `wbColorTheme`, `wbSiteNavbar`, `wbSiteFooter`. The four deprecated variant fields (`wbFooterBg`, `wbDefaultButtonVariant`, `wbDefaultBadgeVariant`, `wbDefaultAlertVariant`) have been removed in favour of the CSS theme approach.
- `footer.twig`, `wbButton.twig`, `wbCallToAction.twig`, `wbBadge.twig`, `wbAlert.twig` — all global set lookups removed; templates now emit stable helper CSS classes (`wb-footer`, `wb-theme-btn`, `wb-theme-badge`, `wb-theme-alert`) that the theme stylesheet targets.
- `wb-cp.js` gutted of auto-fill logic (no longer needed); kept as a placeholder for future CP scripting.

## 1.6.2 - 2026-03-13

### Fixed
- Comment approval lightswitch label ("Approved" / "Pending") did not translate when the CP language was changed to Turkish or German. `onLabel` and `offLabel` removed from `Cp::lightswitchHtml()` calls; the row label ("Approved") is now the sole visual indicator and is translated via `\Craft::t('webblocks', ...)`.
- Added CP translation strings (`Approved`, `Pending`, `Approval Status`, rejection confirm dialog) to `src/translations/{en,tr,de}/webblocks.php` so all comment moderation UI respects the active CP language.

## 1.6.1 - 2026-03-13

### Added
- Inline approval toggle on the `wbComment` entry edit page. An **Approved** lightswitch row is injected into the metadata panel (below "Updated at") via `Element::EVENT_DEFINE_METADATA`. Toggling it fires `Craft.sendActionRequest` to `webblocks/comment/approve` or `webblocks/comment/reject` and reloads the page — no full save required.
- `CommentController::actionApprove()` and `actionApprove::actionReject()` — CP-only POST actions that set `enabled = true` / `false` on the target comment entry and flash a notice. Used by both the inline toggle and (if re-enabled in future) any toolbar menu item.

### Changed
- `WebBlocks::_registerCommentApprovalToggle()` replaces the earlier `_registerCommentSidebarButton()`. The metadata row approach (`EVENT_DEFINE_METADATA`) is cleaner than prepending raw HTML to the sidebar, and the toggle is correctly positioned by anchoring `Created at` and `Updated at` placeholder keys in `$event->metadata` before adding `Approved` — exploiting PHP `array_merge` string-key ordering (first occurrence wins position; last occurrence wins value).

## 1.6.0 - 2026-03-13

### Added
- Form submission storage: every contact form submission is saved as a `wbSubmission` entry in the new `wbSubmissions` channel section. Entries store visitor email, a field/value data table, and a relation to the originating form entry.
- Visitor confirmation email: `wbForm` now has a **Confirmation Email** tab with `wbFormConfirmationEnabled` (lightswitch), `wbFormConfirmationSubject`, and `wbFormConfirmationBody` fields. The body supports `{FieldLabel}` placeholders that are replaced with the submitted value at send time.
- Per-field email filters: each `wbFormField` has two new toggles — `wbFormFieldInAdminEmail` (default on) and `wbFormFieldInConfirmEmail` (default off) — so editors control independently which fields appear in the admin notification and in the visitor confirmation.
- `MarkSubmissionRead`, `MarkSubmissionUnread`, and `ArchiveSubmission` element actions on the `wbSubmissions` CP index, extending `craft\base\ElementAction` directly (same pattern as comment actions).
- `wbSubmissionStatus` dropdown field (unread / read / archived) on the `wbSubmission` entry type.
- Seed data for all three form blocks (EN, TR, DE) updated with `confirmationEnabled`, `confirmationSubject`, `confirmationBody`, and per-field `inAdminEmail` / `inConfirmEmail` values.

### Changed
- `FormController::actionSubmit()` refactored: collects admin vs. confirmation field lists separately, saves a `wbSubmission` entry via new `_saveSubmission()` private method, and dispatches the confirmation email via `_buildConfirmationBody()` + `_replacePlaceholders()`.

## 1.5.0 - 2026-03-13

### Added
- Blog comment moderation via the Craft control panel. Comments are stored as entries in the `wbComments` section with `enabled = false` (pending) by default.
- `ApproveComment` and `RejectComment` element actions — appear in the CP element index action menu for the `wbComments` section. Selecting one or more comments and choosing **Approve** sets `enabled = true`; **Reject** sets `enabled = false`.
- `wbComment` entry type: `titleFormat` set to `{wbCommentAuthorName}` so each comment shows the author's name instead of "Untitled Entry" in the CP list.
- `src/elementactions/ApproveComment.php` and `src/elementactions/RejectComment.php` — both extend `craft\base\ElementAction` directly (not `SetStatus`) to avoid Craft's `defineRules()` validation dropping the actions before they reach the menu.
- 3 seed comments (all pending) seeded automatically as part of `webblocks/seed`.

### Fixed
- Element actions extending `SetStatus` were silently discarded by Craft's `createAction()` pipeline because `SetStatus::defineRules()` marks `$status` as required, and validation runs before `init()` sets the value. Both actions now extend `ElementAction` directly and hardcode their enable/disable logic in `performAction()`.

## 1.4.2 - 2026-03-12

### Added
- "Cookie Settings" link in footer — visible whenever `wbCookieBannerEnabled` is on. Clicking it re-opens the consent banner so visitors can change their preferences at any time.
- Cookie banner redesigned as a bottom-right card (380 px max-width, rounded corners, box shadow). Categories are listed vertically with label + description on the left and toggle on the right. Fully responsive — becomes a full-width bottom bar on small screens.

### Fixed
- Cookie banner action buttons (Accept all / Save preferences) did not work when the banner was re-opened after consent was already stored. Listener registration moved before the early-return so buttons are always wired.

## 1.4.1 - 2026-03-12

### Fixed
- Cookie consent privacy policy URL was hardcoded to `restaurant.ddev.site`. It now resolves dynamically from each site's base URL, so the correct domain is used on any installation.

## 1.4.0 - 2026-03-12

### Added
- Legal Notice page with EN, TR, and DE seed content (`/impressum`). EN uses standard liability/copyright text; DE follows § 5 TMG Impressum format; TR provides a Turkish yasal bildirim. Page is hidden from the navbar and linked from the footer.
- "Legal Notice" footer link added to `wbFooter.json` seed component.
- Cookie consent banner via `wbCookieSettings` global set. Four consent categories: Necessary (always on), Analytics, Marketing, and Preferences. Vanilla JS consent manager stores user choice in `localStorage` as `wb_cookie_consent` and publishes `window.wbCookieConsent` for third-party scripts. Banner is included via `wb/partials/cookie-banner.twig` in `layout.twig` and can be toggled with the `wbCookieBannerEnabled` lightswitch. EN/TR/DE seed content included.
- `LICENSE.md` — Craft License with `fklavye.net` copyright holder.

## 1.3.0 - 2026-03-12

### Added
- Multi-language seed content: full TR (Turkish) and DE (German) translations for all 11 blog posts and all static pages (About, Philosophy, Sustainability, Services, Contact, Home, Blog index).
- `InstallService`: creates TR and DE Craft sites on install; all sections and category groups now include site settings for each extra site.
- `SeedController`: `propagateEntryToSite()`, `getExtraSiteConfigs()`, `findTranslatedDef()`, and `resolveNestedDef()` — every seeded entry is propagated to TR and DE with fully translated titles, SEO fields, excerpts, and block text.
- `pages.tr.json`, `pages.de.json`, `blogs.tr.json`, `blogs.de.json` seed translation files.

### Fixed
- Craft 5 multi-site `elements_owners` integrity constraint violation during seed: added `enableVersioning: false` to all section JSON definitions so Craft does not attempt to create nested-element revisions across multiple sites.
- Extra-site URI formats incorrectly included the language path prefix (e.g. `tr/{slug}`), causing all TR/DE content pages to return 404. URI formats now match the primary site; routing uses the site `baseUrl` to distinguish locales.

## 1.2.0 - 2026-03-11

### Added
- `wbBadge` block: Bootstrap badge with colour variant (`wbBadgeType`) and optional pill style (`wbBadgePill`).
- `wbPagination` block: Bootstrap pagination with configurable page count, size (sm/lg), and alignment (start/center/end).
- `wbToast` block: Bootstrap toast notification with title, body text, and colour variant. Rendered with `show` class so it is visible without a JS trigger.
- `wbSpinner` block: Bootstrap spinner with type (border/grow), colour variant, and size (sm/default).
- `wbPopover` block: Bootstrap popover trigger button with configurable label, header, body, placement, and button size. Initialised with an inline IIFE after Bootstrap CDN loads.
- `wbButtonGroup` block: Nested block — `wbButtonGroupItems` matrix containing `wbButtonGroupItem` entry types. Supports shared button variant/outline style, size, and horizontal/vertical orientation.
- `wbOffcanvas` block: Off-canvas panel with configurable placement, backdrop, trigger button label and size, and fully nested `wbBlocks` content.

## 1.1.0 - 2026-03-11

### Added
- `wbFullscreenImage` block: new fullscreen slider component with per-slide background image, overlay, title, subtitle, portrait image, horizontal/vertical caption alignment, autoplay, and configurable minimum height.
- Vanilla JS fade slider for `wbFullscreenImage`: loop, autoplay, prev/next buttons, dot pagination, keyboard (←/→) and touch/swipe navigation — zero external dependencies. Bootstrap 5.3 CDN remains the only external dependency.
- CSS chevron arrows for slider nav buttons (pure border-based, font-metric independent, always pixel-perfect centred in the circle).
- `wbFullscreen` image transform (used by `wbFullscreenImage` slides).
- `wbGalleryThumb` transform reused for optional portrait/corner image within slides.
- `WipeController::hardDeleteSoftDeleted()` now also purges soft-deleted rows from the `elements` table, preventing database bloat across repeated wipe+reinstall+seed cycles.

### Fixed
- Slider nav button arrows were visually off-centre due to font metrics of `‹`/`›` HTML entities. Replaced with CSS `border` chevrons that are always precisely centred regardless of font stack.
- Soft-deleted `elements` rows were not purged during wipe, causing the `elements` table to grow by ~500 rows per wipe+reinstall cycle. Hard-delete now runs as part of `webblocks/wipe/all`.

## 1.0.0 - 2026-03-11

### Added
- Initial release of WebBlocks — a portable website building toolkit for Craft CMS 5.
- 47 entry types, 86 custom fields, and 15 matrix fields covering all common content needs.
- 23 modular Bootstrap 5 frontend components: Hero, Gallery, Carousel, Accordion, Tabs, Table, Progress Bar, List Group, Modal, Form, Contact Details, Card, Button, Alert, Call to Action, Text Block, Heading, Columns, Video Embed, Spacing, Navbar, Breadcrumb, Left/Right.
- Pre-configured sections: Home, Blog, About, Contact, Services, References, Search.
- Console commands for automated Install, Wipe, and Seed workflows.
- Full multi-language support (DE, EN, TR).
- Built-in SEO fields (`wbSeoTitle`, `wbSeoDescription`) on all page-level entry types.
- Dynamic contact form handling with configurable recipient and email notifications.
- Nested column layout system: `wbColumns` → `wbColumnItems` → `wbColumn` → `wbBlocks` (recursive).
- Custom masonry gallery with lightbox (no third-party JS dependencies).
- Bootstrap carousel with frosted-glass captions.
- Asset volumes: wbImages, wbVideos, wbDocuments.
- Image transforms: wbHero, wbCarousel, wbGalleryThumb, wbGalleryFull, wbOgImage, wbCard, wbFeaturedImage, wbLeftRight.
- Global set `wbSiteConfig` for site-wide configuration.
- All handles prefixed with `wb` to avoid conflicts with site-level content.
