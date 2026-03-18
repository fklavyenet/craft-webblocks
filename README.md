# WebBlocks

A portable website building toolkit for Craft CMS 5. Provides a full set of WebBlocks UI Kit page-builder blocks that can be dropped into any Craft site.

## Installation

### Requirements

- Craft CMS 5.x already installed and configured
- PHP 8.2+
- Composer

### Steps

**1. Require the package**

```bash
composer require fklavyenet/craft-webblocks
```

**2. Install the plugin**

```bash
# DDEV
ddev craft plugin/install webblocks --interactive=0

# Plain server (SSH)
php craft plugin/install webblocks --interactive=0
```

**3. (Optional) Seed demo content**

```bash
# DDEV
ddev craft webblocks/seed --interactive=0

# Plain server
php craft webblocks/seed --interactive=0
```

Seed content is a restaurant demo site in English, Turkish, and German. Omit this step for a blank install.

To seed specific languages only:

```bash
php craft webblocks/seed --languages=en,tr
```

## Console Commands

> Replace `php craft` with `ddev craft` if you are using DDEV.

| Command                                               | Description                                          |
|-------------------------------------------------------|------------------------------------------------------|
| `php craft plugin/install webblocks --interactive=0`  | Install plugin (runs InstallService automatically)   |
| `php craft webblocks/seed --interactive=0`            | Seed demo content                                    |
| `php craft webblocks/seed --languages=en,tr`          | Seed specific languages only                         |
| `php craft webblocks/wipe --interactive=0`            | Wipe all plugin data (preserves plugin install)      |
| `php craft webblocks/wipe/all --interactive=0`        | Wipe, uninstall, reinstall, and reseed from scratch  |
| `php craft webblocks/components/diff`                 | Diff component JSON versions vs installed DB state   |
| `php craft webblocks/components/check`                | Exit 0 if all OK, exit 1 if migrations pending (CI)  |
| `php craft webblocks/components/dry-run`              | Show what a migration run would do (no changes)      |
| `php craft webblocks/components/migrate`              | Apply all pending component migrations               |
| `php craft webblocks/components/cleanup-deprecated`   | List deprecated fields; add `--force` to purge them  |

## Plugin Settings

Navigate to **CP → WebBlocks → Settings** to configure:

| Setting | Description |
|---|---|
| **Default Admin Email** | Fallback recipient for wbForm admin notification emails |
| **Comment Notification Email** | Email address notified on every new pending comment |
| **Page Title Format** | `<title>` tag format; use `{title}` and `{siteName}` placeholders |
| **Seed Languages** | Languages to seed (EN always included; TR and DE optional) |
| **GA4 Measurement ID** | Google Analytics 4 ID (e.g. `G-XXXXXXXXXX`); leave blank to disable |
| **Matomo URL** | Matomo instance URL (e.g. `https://analytics.example.com/`) |
| **Matomo Site ID** | Matomo Site ID (e.g. `1`); both URL and Site ID required |

Analytics snippets are automatically injected into `<head>` when the relevant settings are filled in — no template edits required.

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
| `wbCard`         | UI Kit card with image, title, body text, and button                              |
| `wbCallToAction` | Prominent CTA section with heading, text, and button                                 |
| `wbAlert`        | Alert (info / success / warning / danger) with optional dismiss button     |
| `wbButton`       | Standalone button with configurable label, URL, style, size, and target              |
| `wbHeading`      | Standalone heading (h1–h6) with alignment                                            |

### Layout

| Handle      | Description                                                                                             |
|-------------|---------------------------------------------------------------------------------------------------------|
| `wbColumns` | Multi-column layout (2, 3, or 4 columns). Each column contains its own `wbBlocks` matrix — fully nested |
| `wbSpacing` | Configurable vertical spacer                                                                        |

### Media

| Handle               | Description                                                                                                      |
|----------------------|------------------------------------------------------------------------------------------------------------------|
| `wbGallery`          | Masonry image gallery with lightbox. Supports configurable gap                                                   |
| `wbCarousel`         | Fullscreen-style slider with autoplay, controls, indicators, and frosted-glass captions. Vanilla JS |
| `wbFullscreenImage`  | Fullscreen slider with per-slide background image, overlay, title, subtitle, and caption alignment. Vanilla JS   |
| `wbVideoEmbed`       | Embedded video (YouTube / Vimeo) with configurable aspect ratio                                                  |

### Data

| Handle          | Description                                                                                    |
|-----------------|------------------------------------------------------------------------------------------------|
| `wbTable`       | Data table with configurable columns and rows. Supports caption, striped, bordered, and hover  |
| `wbAccordion`   | Accordion with any number of items                                                   |
| `wbTabs`        | Tabs with configurable alignment                                                     |
| `wbProgressBar` | Progress bars with label, value, colour, and striped option                          |
| `wbListGroup`   | List group with optional flush style                                                 |

### Forms & Contact

| Handle             | Description                                                                                                                          |
|--------------------|--------------------------------------------------------------------------------------------------------------------------------------|
| `wbForm`           | Configurable contact form. Supports text, email, textarea, select, radio, and checkbox fields. Built-in submission storage and optional visitor confirmation email |
| `wbContactDetails` | Contact details block: phone, email, and address                                                                                     |

### Navigation

| Handle        | Description                                                              |
|---------------|--------------------------------------------------------------------------|
| `wbNavbar`    | Navbar with brand, logo, colour scheme, nav items, and search  |
| `wbBreadcrumb`| Breadcrumb                                                     |
| `wbModal`     | Modal trigger and content                                      |
| `wbLeftRight` | Two-column image + text layout with switchable sides                     |

### UI Components

| Handle          | Description                                                                       |
|-----------------|-----------------------------------------------------------------------------------|
| `wbBadge`       | Badge with colour variant and optional pill style                       |
| `wbPagination`  | Pagination with configurable page count, size, and alignment            |
| `wbToast`       | Toast notification with title, body, and colour variant                 |
| `wbSpinner`     | Spinner (border or grow) with colour and size options                   |
| `wbPopover`     | Popover trigger button with configurable placement and content          |
| `wbButtonGroup` | Group of buttons with shared style, size, and optional vertical orientation       |
| `wbOffcanvas`   | Off-canvas panel with configurable placement, backdrop, and nested block content  |

## Appearance Fields

Most components include an **Appearance** tab in the Craft control panel with the following fields:

| Field              | Options                                                          |
|--------------------|------------------------------------------------------------------|
| `wbBorder`         | Toggle border on/off                                             |
| `wbBorderColor`    | Colour variant (primary / secondary / etc.)                      |
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

## Frontend Dependencies

The **WebBlocks UI Kit** (`fklavyenet/webblocks-ui`) is loaded via CDN:

```
https://cdn.jsdelivr.net/gh/fklavyenet/webblocks-ui@master/dist/webblocks-ui.css
https://cdn.jsdelivr.net/gh/fklavyenet/webblocks-ui@master/dist/webblocks-ui.js
```

No build step is required. Bootstrap is **not** used. All other JS (masonry layout, lightbox, fullscreen slider, carousel) is vanilla JavaScript bundled in `wb-blocks.js` via the WebBlocksAsset bundle — zero external slider or UI library dependencies.

## Blog Comment Moderation

WebBlocks includes a built-in comment system for blog posts:

- Comments submitted on the front end are saved to the `wbComments` section with `enabled = false` (pending) by default.
- Honeypot field silently blocks spam bots — no CAPTCHA required.
- In the Craft CP, navigate to **Entries → Comments** to review pending submissions.
- **Index view:** select one or more comments and use the **Approve** or **Reject** bulk actions from the action menu.
- **Edit view:** an **Approved** lightswitch is shown in the metadata panel (below "Updated at"). Toggle it on or off to approve or reject the comment instantly — no page save required.
- All CP-facing strings (approval status labels, confirm dialog) are fully translated in EN, TR, DE, and ES.
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

## Component Versioning & Migration System

WebBlocks tracks the installed version of every component (field, entry type, matrix field, etc.) in a `webblocks_component_versions` database table. Each JSON definition file carries a `"version"` integer. When a component's JSON version is ahead of the installed version, the diff system detects it and the migrator can apply a structured migration file.

### CP Health Screen

Navigate to **CP → WebBlocks → Component Health** to see:

- Summary pills showing OK / Version Bump / Checksum Drift / New / Orphan counts
- A table of components needing attention
- **Dry Run** button — shows what would be applied without making changes
- **Run Migrations** button — applies all pending migrations
- **Deprecated Fields** table — lists fields removed from layouts but not yet purged from the database

### Migration DSL

Migration files live at `src/componentMigrations/{type}/{handle}/{from}_to_{to}.php`. Each file returns a PHP array:

```php
return [
    'from'    => 1,
    'to'      => 2,
    'actions' => [
        ['type' => 'updateFieldSettings', 'handle' => 'wbTitle', 'settings' => ['instructions' => '...']],
        ['type' => 'deprecateField',      'handle' => 'wbOldField'],
        ['type' => 'addField',            'handle' => 'wbNewField', 'definition' => 'fields/wbNewField.json'],
    ],
];
```

Supported action types: `renameField`, `addField`, `removeField`, `deprecateField`, `updateFieldSettings`, `updateFieldLayout`, `addMatrixBlockType`, `removeMatrixBlockType`, `renameMatrixBlockType`, `copyContent`, `transformContent`.

### Deprecated Field Lifecycle

A `deprecateField` action removes a field from all entry type layouts and records it in `webblocks_deprecated_fields`. The field data is preserved. To permanently delete it:

```bash
php craft webblocks/components/cleanup-deprecated          # list pending
php craft webblocks/components/cleanup-deprecated --force  # purge all
```

## Contributing

See [DEVELOPERS.md](DEVELOPERS.md) for architecture notes, local environment setup, and instructions for adding new blocks.
