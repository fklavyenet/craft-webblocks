# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/) and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

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
