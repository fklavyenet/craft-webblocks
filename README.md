# WebBlocks

A portable website building toolkit for Craft CMS 5. Provides a full set of Bootstrap 5 page-builder blocks that can be dropped into any Craft site.

## Requirements

- Craft CMS 5.x
- PHP 8.2+
- Bootstrap 5.3 (loaded via CDN — no local build step required)

## Installation

```bash
composer require fklavyenet/craft-webblocks
ddev craft plugin/install webblocks
ddev craft webblocks/seed --interactive=0
```

## Console Commands

| Command                                             | Description                                          |
|-----------------------------------------------------|------------------------------------------------------|
| `ddev craft plugin/install webblocks --interactive=0` | Install plugin (runs InstallService automatically) |
| `ddev craft webblocks/seed --interactive=0`         | Seed demo content                                    |
| `ddev craft webblocks/wipe/all --interactive=0`     | Wipe all plugin data and uninstall plugin            |

## Usage

Include the entry point template from any page template:

```twig
{% include "wb/index" with { entry: entry } %}
```

The `wb/index` template dispatches each block to its component template via `item.type.handle`.

## Components

All components are registered as entry types under the `wbBlocks` matrix field. Each has a corresponding Twig template under `wb/components/`.

### Content Blocks

| Handle           | Description                                                                          |
|------------------|--------------------------------------------------------------------------------------|
| `wbHero`         | Full-width hero section with background image, overlay, heading, text, and CTA button |
| `wbTextBlock`    | Rich text block with optional title, alignment, background colour, and border        |
| `wbCard`         | Bootstrap card with image, title, body text, and button                              |
| `wbCallToAction` | Prominent CTA section with heading, text, and button                                 |
| `wbAlert`        | Bootstrap alert (info / success / warning / danger) with optional dismiss button     |
| `wbButton`       | Standalone button with configurable label, URL, style, size, and target              |
| `wbHeading`      | Standalone heading (h1–h6) with alignment                                            |

### Layout

| Handle      | Description                                                                                             |
|-------------|---------------------------------------------------------------------------------------------------------|
| `wbColumns` | Multi-column layout (2, 3, or 4 columns). Each column contains its own `wbBlocks` matrix — fully nested |
| `wbSpacing` | Configurable vertical spacer (Bootstrap spacing utilities)                                              |

### Media

| Handle               | Description                                                                                                      |
|----------------------|------------------------------------------------------------------------------------------------------------------|
| `wbGallery`          | Masonry image gallery with lightbox. Supports configurable gap                                                   |
| `wbCarousel`         | Bootstrap carousel with autoplay, controls, indicators, and frosted-glass captions                               |
| `wbFullscreenImage`  | Fullscreen slider with per-slide background image, overlay, title, subtitle, and caption alignment. Vanilla JS   |
| `wbVideoEmbed`       | Embedded video (YouTube / Vimeo) with configurable aspect ratio                                                  |

### Data

| Handle          | Description                                                                                    |
|-----------------|------------------------------------------------------------------------------------------------|
| `wbTable`       | Data table with configurable columns and rows. Supports caption, striped, bordered, and hover  |
| `wbAccordion`   | Bootstrap accordion with any number of items                                                   |
| `wbTabs`        | Bootstrap tabs with configurable alignment                                                     |
| `wbProgressBar` | Bootstrap progress bars with label, value, colour, and striped option                          |
| `wbListGroup`   | Bootstrap list group with optional flush style                                                 |

### Forms & Contact

| Handle             | Description                                                                                                                          |
|--------------------|--------------------------------------------------------------------------------------------------------------------------------------|
| `wbForm`           | Configurable contact form. Supports text, email, textarea, select, radio, and checkbox fields. Built-in submission storage and optional visitor confirmation email |
| `wbContactDetails` | Contact details block: phone, email, and address                                                                                     |

### Navigation

| Handle        | Description                                                              |
|---------------|--------------------------------------------------------------------------|
| `wbNavbar`    | Bootstrap navbar with brand, logo, colour scheme, nav items, and search  |
| `wbBreadcrumb`| Bootstrap breadcrumb                                                     |
| `wbModal`     | Bootstrap modal trigger and content                                      |
| `wbLeftRight` | Two-column image + text layout with switchable sides                     |

### UI Components

| Handle          | Description                                                                       |
|-----------------|-----------------------------------------------------------------------------------|
| `wbBadge`       | Bootstrap badge with colour variant and optional pill style                       |
| `wbPagination`  | Bootstrap pagination with configurable page count, size, and alignment            |
| `wbToast`       | Bootstrap toast notification with title, body, and colour variant                 |
| `wbSpinner`     | Bootstrap spinner (border or grow) with colour and size options                   |
| `wbPopover`     | Bootstrap popover trigger button with configurable placement and content          |
| `wbButtonGroup` | Group of buttons with shared style, size, and optional vertical orientation       |
| `wbOffcanvas`   | Off-canvas panel with configurable placement, backdrop, and nested block content  |

## Appearance Fields

Most components include an **Appearance** tab in the Craft control panel with the following fields:

| Field              | Options                                                          |
|--------------------|------------------------------------------------------------------|
| `wbBorder`         | Toggle border on/off                                             |
| `wbBorderColor`    | Bootstrap colour utility (primary / secondary / etc.)            |
| `wbRounded`        | Border-radius utility class                                      |
| `wbPadding`        | Padding utility class                                            |
| `wbTitlePosition`  | Title placement: **Outside** (above the box) or **Inside** (inside the box) |

## Image Transforms

| Handle           | Usage                                       |
|------------------|---------------------------------------------|
| `wbHero`         | Hero background images                      |
| `wbCarousel`     | Carousel slide images                       |
| `wbFullscreen`   | Fullscreen slider slide images (1920 px)    |
| `wbGalleryThumb` | Gallery thumbnails (800 px wide)            |
| `wbGalleryFull`  | Gallery lightbox images (1920 px wide)      |
| `wbFeaturedImage`| Blog post and reference featured images     |
| `wbCard`         | Card component images                       |
| `wbLeftRight`    | Left/right layout images                    |
| `wbOgImage`      | Open Graph / social share images            |

## Asset Volumes

Three volumes are created on install:

- **wbImages** — photos and graphics
- **wbVideos** — video files
- **wbDocuments** — PDFs and other documents

## Architecture

- `wbBlocks` is the top-level matrix field. Add it to any section's field layout to enable page-builder functionality.
- Nested columns: `wbColumns` → `wbColumnItems` matrix → `wbColumn` entry type → `wbBlocks` matrix (recursive).
- Templates live in `src/wbTemplates/` and are registered under the `wb` Twig namespace.
- Component definitions (fields, entry types, matrix fields) live in `src/wbComponents/` as JSON files and are auto-discovered on install.

## File Structure

```
src/
├── WebBlocks.php                   Plugin bootstrap; registers wb/ template root
├── services/
│   ├── InstallService.php          Orchestrates install order and field layout building
│   └── FieldInstallService.php     Creates Craft fields from JSON definitions
├── console/
│   ├── WipeController.php          Wipe command (Craft 5 API)
│   └── SeedController.php          Seeds demo content
├── wbComponents/
│   ├── fields/                     Plain field definitions (JSON)
│   ├── entrytypes/                 Entry type + field layout definitions (JSON)
│   ├── matrixfields/               Matrix field + entry type assignments (JSON)
│   ├── imagetransforms/            Image transform definitions (JSON)
│   ├── sections/                   Section definitions (JSON)
│   ├── volumes/                    Asset volume definitions (JSON)
│   ├── filesystems/                Filesystem definitions (JSON)
│   └── globalsets/                 Global set definitions (JSON)
├── wbTemplates/
│   ├── index.twig                  Entry point; dispatches to wb/components/{handle}
│   ├── layout.twig                 Bootstrap CDN, WB Masonry JS, WB Lightbox JS
│   ├── components/                 One Twig file per component handle
│   ├── sections/                   Section-level page templates
│   └── partials/                   Shared partial templates
└── resources/
    └── seed/
        ├── pages.json              Page definitions with inline block data
        ├── blogs.json              Blog post seed data
        ├── images.json             Sample image manifest
        └── components/             Named block files for the demo test page
```

## Frontend Dependencies

Bootstrap 5.3.3 is loaded via CDN (MIT licence). No build step is required. All other JS (masonry layout, lightbox, fullscreen slider) is vanilla JavaScript bundled in `wb-blocks.js` via the WebBlocksAsset bundle — zero external slider or UI library dependencies.

## Blog Comment Moderation

WebBlocks includes a built-in comment system for blog posts:

- Comments submitted on the front end are saved to the `wbComments` section with `enabled = false` (pending) by default.
- Honeypot field silently blocks spam bots — no CAPTCHA required.
- In the Craft CP, navigate to **Entries → Comments** to review pending submissions.
- Select one or more comments and use the **Approve** or **Reject** actions from the action menu to publish or hide them.
- Approved comments (`enabled = true`) are automatically displayed on the relevant blog post page.

## Form Submission Storage

Every `wbForm` submission is automatically stored in the Craft CP:

- Submissions are saved as entries in the `wbSubmissions` section. Each entry stores the visitor's email address, a field/value data table, and a relation to the originating form entry.
- Navigate to **Entries → Submissions** in the CP to review all received submissions.
- Bulk-update status with the **Mark as Read**, **Mark as Unread**, and **Archive** element actions.
- Default status is **Unread**; transitions to Read or Archived via the action menu.

### Visitor Confirmation Email

Each `wbForm` entry has a **Confirmation Email** tab in the CP:

- `wbFormConfirmationEnabled` — toggle the confirmation email on or off per form.
- `wbFormConfirmationSubject` — subject line sent to the visitor.
- `wbFormConfirmationBody` — plain-text body. Use `{FieldLabel}` placeholders (e.g. `{Email address}`) to include the submitted value for any field.

### Per-field Email Filters

Each field in a `wbForm` has two toggles in the CP:

- **Include in admin email** (`wbFormFieldInAdminEmail`) — on by default. Controls whether this field appears in the notification sent to the form recipient.
- **Include in confirmation email** (`wbFormFieldInConfirmEmail`) — off by default. Controls whether this field appears in the visitor confirmation email.

## Global Sets

| Handle              | Description                                                                                                           |
|---------------------|-----------------------------------------------------------------------------------------------------------------------|
| `wbSiteConfig`      | Site-wide configuration: site name, logo, contact details, social links, and footer text                              |
| `wbCookieSettings`  | Cookie consent banner configuration: enable/disable toggle, title, body text, privacy policy URL, and category labels |

### Cookie Consent Banner

The `wbCookieSettings` global set powers a built-in GDPR-friendly cookie consent banner:

- **Four categories:** Necessary (always on), Analytics, Marketing, Preferences
- **Vanilla JS** — no external libraries. Consent is stored in `localStorage` as `wb_cookie_consent`
- **`window.wbCookieConsent`** is published for third-party scripts to read consent state
- **`window.wbOpenCookieBanner()`** re-opens the banner programmatically from any script
- **Footer trigger:** A "Cookie Settings" link is automatically rendered in the footer when `wbCookieBannerEnabled` is on. Any element with `data-wb-cookie-trigger` attribute also re-opens the banner
- **Design:** Bottom-right card (380 px max-width); full-width bar on mobile
- **Toggle** the banner on/off with `wbCookieBannerEnabled` (lightswitch field)
- **Seed content** provided in EN, TR, and DE

## Prefix Convention

All plugin-owned handles use the `wb` prefix (fields, entry types, matrix fields, sections, volumes, transforms) to avoid conflicts with site-level content.
