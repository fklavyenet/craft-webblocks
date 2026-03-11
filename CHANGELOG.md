# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/) and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

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
