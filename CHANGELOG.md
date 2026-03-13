# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/) and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.6.1] - 2026-03-13

### Added
- Inline approval toggle on the `wbComment` entry edit page. An **Approved** lightswitch row is injected into the metadata panel (below "Updated at") via `Element::EVENT_DEFINE_METADATA`. Toggling it fires `Craft.sendActionRequest` to `webblocks/comment/approve` or `webblocks/comment/reject` and reloads the page — no full save required.
- `CommentController::actionApprove()` and `actionApprove::actionReject()` — CP-only POST actions that set `enabled = true` / `false` on the target comment entry and flash a notice. Used by both the inline toggle and (if re-enabled in future) any toolbar menu item.

### Changed
- `WebBlocks::_registerCommentApprovalToggle()` replaces the earlier `_registerCommentSidebarButton()`. The metadata row approach (`EVENT_DEFINE_METADATA`) is cleaner than prepending raw HTML to the sidebar, and the toggle is correctly positioned by anchoring `Created at` and `Updated at` placeholder keys in `$event->metadata` before adding `Approved` — exploiting PHP `array_merge` string-key ordering (first occurrence wins position; last occurrence wins value).

## [1.6.0] - 2026-03-13

### Added
- Form submission storage: every contact form submission is saved as a `wbSubmission` entry in the new `wbSubmissions` channel section. Entries store visitor email, a field/value data table, and a relation to the originating form entry.
- Visitor confirmation email: `wbForm` now has a **Confirmation Email** tab with `wbFormConfirmationEnabled` (lightswitch), `wbFormConfirmationSubject`, and `wbFormConfirmationBody` fields. The body supports `{FieldLabel}` placeholders that are replaced with the submitted value at send time.
- Per-field email filters: each `wbFormField` has two new toggles — `wbFormFieldInAdminEmail` (default on) and `wbFormFieldInConfirmEmail` (default off) — so editors control independently which fields appear in the admin notification and in the visitor confirmation.
- `MarkSubmissionRead`, `MarkSubmissionUnread`, and `ArchiveSubmission` element actions on the `wbSubmissions` CP index, extending `craft\base\ElementAction` directly (same pattern as comment actions).
- `wbSubmissionStatus` dropdown field (unread / read / archived) on the `wbSubmission` entry type.
- Seed data for all three form blocks (EN, TR, DE) updated with `confirmationEnabled`, `confirmationSubject`, `confirmationBody`, and per-field `inAdminEmail` / `inConfirmEmail` values.

### Changed
- `FormController::actionSubmit()` refactored: collects admin vs. confirmation field lists separately, saves a `wbSubmission` entry via new `_saveSubmission()` private method, and dispatches the confirmation email via `_buildConfirmationBody()` + `_replacePlaceholders()`.

## [1.5.0] - 2026-03-13

### Added
- Blog comment moderation via the Craft control panel. Comments are stored as entries in the `wbComments` section with `enabled = false` (pending) by default.
- `ApproveComment` and `RejectComment` element actions — appear in the CP element index action menu for the `wbComments` section. Selecting one or more comments and choosing **Approve** sets `enabled = true`; **Reject** sets `enabled = false`.
- `wbComment` entry type: `titleFormat` set to `{wbCommentAuthorName}` so each comment shows the author's name instead of "Untitled Entry" in the CP list.
- `src/elementactions/ApproveComment.php` and `src/elementactions/RejectComment.php` — both extend `craft\base\ElementAction` directly (not `SetStatus`) to avoid Craft's `defineRules()` validation dropping the actions before they reach the menu.
- 3 seed comments (all pending) seeded automatically as part of `webblocks/seed`.

### Fixed
- Element actions extending `SetStatus` were silently discarded by Craft's `createAction()` pipeline because `SetStatus::defineRules()` marks `$status` as required, and validation runs before `init()` sets the value. Both actions now extend `ElementAction` directly and hardcode their enable/disable logic in `performAction()`.

## [1.4.2] - 2026-03-12

### Added
- "Cookie Settings" link in footer — visible whenever `wbCookieBannerEnabled` is on. Clicking it re-opens the consent banner so visitors can change their preferences at any time.
- Cookie banner redesigned as a bottom-right card (380 px max-width, rounded corners, box shadow). Categories are listed vertically with label + description on the left and toggle on the right. Fully responsive — becomes a full-width bottom bar on small screens.

### Fixed
- Cookie banner action buttons (Accept all / Save preferences) did not work when the banner was re-opened after consent was already stored. Listener registration moved before the early-return so buttons are always wired.

## [1.4.1] - 2026-03-12

### Fixed
- Cookie consent privacy policy URL was hardcoded to `restaurant.ddev.site`. It now resolves dynamically from each site's base URL, so the correct domain is used on any installation.

## [1.4.0] - 2026-03-12

### Added
- Legal Notice page with EN, TR, and DE seed content (`/impressum`). EN uses standard liability/copyright text; DE follows § 5 TMG Impressum format; TR provides a Turkish yasal bildirim. Page is hidden from the navbar and linked from the footer.
- "Legal Notice" footer link added to `wbFooter.json` seed component.
- Cookie consent banner via `wbCookieSettings` global set. Four consent categories: Necessary (always on), Analytics, Marketing, and Preferences. Vanilla JS consent manager stores user choice in `localStorage` as `wb_cookie_consent` and publishes `window.wbCookieConsent` for third-party scripts. Banner is included via `wb/partials/cookie-banner.twig` in `layout.twig` and can be toggled with the `wbCookieBannerEnabled` lightswitch. EN/TR/DE seed content included.
- `LICENSE.md` — Craft License with `fklavye.net` copyright holder.

## [1.3.0] - 2026-03-12

### Added
- Multi-language seed content: full TR (Turkish) and DE (German) translations for all 11 blog posts and all static pages (About, Philosophy, Sustainability, Services, Contact, Home, Blog index).
- `InstallService`: creates TR and DE Craft sites on install; all sections and category groups now include site settings for each extra site.
- `SeedController`: `propagateEntryToSite()`, `getExtraSiteConfigs()`, `findTranslatedDef()`, and `resolveNestedDef()` — every seeded entry is propagated to TR and DE with fully translated titles, SEO fields, excerpts, and block text.
- `pages.tr.json`, `pages.de.json`, `blogs.tr.json`, `blogs.de.json` seed translation files.

### Fixed
- Craft 5 multi-site `elements_owners` integrity constraint violation during seed: added `enableVersioning: false` to all section JSON definitions so Craft does not attempt to create nested-element revisions across multiple sites.
- Extra-site URI formats incorrectly included the language path prefix (e.g. `tr/{slug}`), causing all TR/DE content pages to return 404. URI formats now match the primary site; routing uses the site `baseUrl` to distinguish locales.

## [1.2.0] - 2026-03-11

### Added
- `wbBadge` block: Bootstrap badge with colour variant (`wbBadgeType`) and optional pill style (`wbBadgePill`).
- `wbPagination` block: Bootstrap pagination with configurable page count, size (sm/lg), and alignment (start/center/end).
- `wbToast` block: Bootstrap toast notification with title, body text, and colour variant. Rendered with `show` class so it is visible without a JS trigger.
- `wbSpinner` block: Bootstrap spinner with type (border/grow), colour variant, and size (sm/default).
- `wbPopover` block: Bootstrap popover trigger button with configurable label, header, body, placement, and button size. Initialised with an inline IIFE after Bootstrap CDN loads.
- `wbButtonGroup` block: Nested block — `wbButtonGroupItems` matrix containing `wbButtonGroupItem` entry types. Supports shared button variant/outline style, size, and horizontal/vertical orientation.
- `wbOffcanvas` block: Off-canvas panel with configurable placement, backdrop, trigger button label and size, and fully nested `wbBlocks` content.

## [1.1.0] - 2026-03-11

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

## [1.0.0] - 2026-03-11

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
