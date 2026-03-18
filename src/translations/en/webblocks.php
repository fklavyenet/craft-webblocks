<?php
/**
 * WebBlocks plugin for Craft CMS
 *
 * English translations (webblocks category).
 * Used by wbTemplates sections, error pages, and form validation.
 */

return [
    // =========================================================================
    // Error pages
    // =========================================================================
    'Back to homepage'           => 'Back to homepage',
    'Bad Request'                => 'Bad Request',
    'Internal Server Error'      => 'Internal Server Error',
    'Page Not Found'             => 'Page Not Found',
    'Service Unavailable'        => 'Service Unavailable',
    'Unauthorized'               => 'Unauthorized',
    'An error occurred while processing your request.' => 'An error occurred while processing your request.',
    'Our site is temporarily unavailable. Please try again later.' => 'Our site is temporarily unavailable. Please try again later.',
    'The request could not be understood by the server due to malformed syntax.' => 'The request could not be understood by the server due to malformed syntax.',
    'The requested URL was not found on this server.' => 'The requested URL was not found on this server.',
    "You don't have the proper credentials to access this page." => "You don't have the proper credentials to access this page.",

    // =========================================================================
    // Blog / References listing pages
    // =========================================================================
    'Blog'             => 'Blog',
    'References'       => 'References',
    'No posts yet.'    => 'No posts yet.',
    'No references yet.' => 'No references yet.',
    'Blog post pages'  => 'Blog post pages',
    'Reference pages'  => 'Reference pages',
    'Previous'         => 'Previous',
    'Next'             => 'Next',

    // =========================================================================
    // Search
    // =========================================================================
    'Search'                               => 'Search',
    'Search: {query}'                      => 'Search: {query}',
    'Search all content…'                  => 'Search all content…',
    'Enter a search term above to find content.' => 'Enter a search term above to find content.',
    'No results found for "{query}".'      => 'No results found for "{query}".',
    '1 result for "{query}"'               => '1 result for "{query}"',
    '{total} results for "{query}"'        => '{total} results for "{query}"',
    'Search result pages'                  => 'Search result pages',

    // =========================================================================
    // CP navigation
    // =========================================================================
    'Help'                                              => 'Help',
    'Component Health'                                  => 'Component Health',

    // =========================================================================
    // Plugin settings (CP)
    // =========================================================================
    'Default Admin Email'    => 'Default Admin Email',
    "Fallback recipient for wbForm admin notification emails when a form's own Recipient field is empty." => "Fallback recipient for wbForm admin notification emails when a form's own Recipient field is empty.",
    'Comment Notification Email' => 'Comment Notification Email',
    'When set, an email is sent to this address every time a new comment is submitted and is awaiting moderation.' => 'When set, an email is sent to this address every time a new comment is submitted and is awaiting moderation.',
    'SEO'                    => 'SEO',
    'Page Title Format'      => 'Page Title Format',
    "Format for the <title> tag when wbSeoTitle is empty. Use {title} and {siteName} as placeholders. Example: {title} — {siteName}" => "Format for the <title> tag when wbSeoTitle is empty. Use {title} and {siteName} as placeholders. Example: {title} — {siteName}",
    'Seed Content'           => 'Seed Content',
    'Seed Languages'         => 'Seed Languages',
    'Languages to seed when running the webblocks/seed command. English is always seeded. Changes take effect on the next wipe + seed cycle.' => 'Languages to seed when running the webblocks/seed command. English is always seeded. Changes take effect on the next wipe + seed cycle.',
    'Analytics'              => 'Analytics',
    'GA4 Measurement ID'     => 'GA4 Measurement ID',
    'Google Analytics 4 Measurement ID (e.g. G-XXXXXXXXXX). Leave blank to disable.' => 'Google Analytics 4 Measurement ID (e.g. G-XXXXXXXXXX). Leave blank to disable.',
    'Matomo URL'             => 'Matomo URL',
    'Your Matomo instance URL (e.g. https://analytics.example.com/). Both URL and Site ID are required.' => 'Your Matomo instance URL (e.g. https://analytics.example.com/). Both URL and Site ID are required.',
    'Matomo Site ID'         => 'Matomo Site ID',
    'Your Matomo Site ID (e.g. 1). Both URL and Site ID are required.' => 'Your Matomo Site ID (e.g. 1). Both URL and Site ID are required.',

    // =========================================================================
    // Help page — headings & table headers
    // =========================================================================
    'Quick Start'                => 'Quick Start',
    'Content Blocks (%field% matrix)' => 'Content Blocks (%field% matrix)',
    'Block'                      => 'Block',
    'What it renders'            => 'What it renders',
    'Global Sets'                => 'Global Sets',
    'Field'                      => 'Field',
    'Purpose'                    => 'Purpose',
    'Sections & Entry Types'     => 'Sections & Entry Types',
    'Section'                    => 'Section',
    'Type'                       => 'Type',
    'Forms & Submissions'        => 'Forms & Submissions',
    'Blog Comments'              => 'Blog Comments',
    'Colour Themes'              => 'Colour Themes',
    'Theme'                      => 'Theme',
    'Character'                  => 'Character',
    'Multi-language'             => 'Multi-language',
    'Wipe & Reseed'              => 'Wipe & Reseed',
    'Requirements'               => 'Requirements',
    'Necessary'                  => 'Necessary',

    // =========================================================================
    // Help page — body strings
    // =========================================================================
    'WebBlocks is a portable website-building toolkit for Craft CMS 5. Install it, run the seeder, and get a fully working multi-language business site — complete with pages, blog, forms, SEO, cookie consent, and a colour theme system — in minutes.' => 'WebBlocks is a portable website-building toolkit for Craft CMS 5. Install it, run the seeder, and get a fully working multi-language business site — complete with pages, blog, forms, SEO, cookie consent, and a colour theme system — in minutes.',
    'Install the plugin via the Plugin Store or Composer.' => 'Install the plugin via the Plugin Store or Composer.',
    'Run the seed command from your project root:' => 'Run the seed command from your project root:',
    'Visit %globals% to set the site name, navbar, footer links, and colour theme.' => 'Visit %globals% to set the site name, navbar, footer links, and colour theme.',
    'Edit, duplicate, or delete the seeded entries in %entries% as needed.' => 'Edit, duplicate, or delete the seeded entries in %entries% as needed.',
    'Every page uses a %field% matrix field. Add, remove, and reorder blocks freely.' => 'Every page uses a %field% matrix field. Add, remove, and reorder blocks freely.',
    'Full-width hero banner with optional overlay, headline, text, and CTA button.' => 'Full-width hero banner with optional overlay, headline, text, and CTA button.',
    'Auto-playing fullscreen slider with dot pagination and caption alignment controls.' => 'Auto-playing fullscreen slider with dot pagination and caption alignment controls.',
    'Bootstrap carousel with optional captions and frosted-glass overlay.' => 'Bootstrap carousel with optional captions and frosted-glass overlay.',
    'Masonry photo gallery with a built-in lightbox.' => 'Masonry photo gallery with a built-in lightbox.',
    'Bootstrap card grid — title, image, text, optional link button.' => 'Bootstrap card grid — title, image, text, optional link button.',
    'Two-column image + text layout with swappable sides.' => 'Two-column image + text layout with swappable sides.',
    '2/3/4-column layout; each column holds its own full %field% stack (recursive nesting).' => '2/3/4-column layout; each column holds its own full %field% stack (recursive nesting).',
    'Rich-text prose with optional heading.' => 'Rich-text prose with optional heading.',
    'Standalone heading (h1–h6) with alignment and colour variant.' => 'Standalone heading (h1–h6) with alignment and colour variant.',
    'Centred CTA section with heading, text, and button.' => 'Centred CTA section with heading, text, and button.',
    'Contact/enquiry form — stores submissions, sends admin email, optional visitor confirmation email.' => 'Contact/enquiry form — stores submissions, sends admin email, optional visitor confirmation email.',
    'Bootstrap accordion / FAQ block.' => 'Bootstrap accordion / FAQ block.',
    'Bootstrap tabbed content panels.' => 'Bootstrap tabbed content panels.',
    'Data table with configurable column headers.' => 'Data table with configurable column headers.',
    'Bootstrap list group — plain, linked, or with badges.' => 'Bootstrap list group — plain, linked, or with badges.',
    'Responsive YouTube / Vimeo embed.' => 'Responsive YouTube / Vimeo embed.',
    'Address, phone, email, and map embed.' => 'Address, phone, email, and map embed.',
    'Bootstrap alert box — dismissible, with theme-aware default colour.' => 'Bootstrap alert box — dismissible, with theme-aware default colour.',
    'Inline Bootstrap badge — pill option, theme-aware default colour.' => 'Inline Bootstrap badge — pill option, theme-aware default colour.',
    'Horizontal or vertical Bootstrap button group.' => 'Horizontal or vertical Bootstrap button group.',
    'Bootstrap modal dialog triggered by a button.' => 'Bootstrap modal dialog triggered by a button.',
    'Bootstrap offcanvas panel (drawer) with nested blocks.' => 'Bootstrap offcanvas panel (drawer) with nested blocks.',
    'Bootstrap popover tooltip on a button.' => 'Bootstrap popover tooltip on a button.',
    'Bootstrap progress bar with label and colour variant.' => 'Bootstrap progress bar with label and colour variant.',
    'Bootstrap loading spinner.' => 'Bootstrap loading spinner.',
    'Bootstrap toast notification (always visible, no JS trigger required).' => 'Bootstrap toast notification (always visible, no JS trigger required).',
    'Bootstrap pagination demo block.' => 'Bootstrap pagination demo block.',
    "Sticky top navigation bar — auto-populated from entries marked 'Show in nav'." => "Sticky top navigation bar — auto-populated from entries marked 'Show in nav'.",
    'Vertical whitespace spacer.' => 'Vertical whitespace spacer.',
    'Master configuration for the site: navbar block, footer block, colour mode (light/dark/auto), and colour theme.' => 'Master configuration for the site: navbar block, footer block, colour mode (light/dark/auto), and colour theme.',
    'Bootstrap colour mode — %light%, %dark%, or %auto% (follows OS preference).' => 'Bootstrap colour mode — %light%, %dark%, or %auto% (follows OS preference).',
    'Colour theme — selects a Bootswatch theme. WebBlocks loads the matching CSS from CDN automatically.' => 'Colour theme — selects a Bootswatch theme. WebBlocks loads the matching CSS from CDN automatically.',
    'A single wbNavbar block used site-wide.' => 'A single wbNavbar block used site-wide.',
    'A single footer block used site-wide.' => 'A single footer block used site-wide.',
    'Controls the cookie consent banner. Enable the banner, set the title, body text, and privacy URL. Four categories: %necessary% (always on), Analytics, Marketing, Preferences. Consent is stored in %storage% as %key%; the %obj% object is published for third-party scripts.' => 'Controls the cookie consent banner. Enable the banner, set the title, body text, and privacy URL. Four categories: %necessary% (always on), Analytics, Marketing, Preferences. Consent is stored in %storage% as %key%; the %obj% object is published for third-party scripts.',
    'Homepage — full wbBlocks stack.' => 'Homepage — full wbBlocks stack.',
    'General-purpose pages with wbBlocks.' => 'General-purpose pages with wbBlocks.',
    'About / team pages.' => 'About / team pages.',
    'Services / products pages.' => 'Services / products pages.',
    'Blog listing page with pagination.' => 'Blog listing page with pagination.',
    'Blog posts — featured image, categories, tags, comments.' => 'Blog posts — featured image, categories, tags, comments.',
    'Blog comments (pending by default). Approve / Reject via CP element actions or the inline toggle on the edit page.' => 'Blog comments (pending by default). Approve / Reject via CP element actions or the inline toggle on the edit page.',
    'Contact page with a wbForm block.' => 'Contact page with a wbForm block.',
    'Stores every form submission. Manage status (Unread / Read / Archived) via CP element actions.' => 'Stores every form submission. Manage status (Unread / Read / Archived) via CP element actions.',
    'Site search — uses craft.entries.search().' => 'Site search — uses craft.entries.search().',
    'Client logos / references for a references block.' => 'Client logos / references for a references block.',
    'Add a wbForm block to any page. Fields are built in the CP — text, email, phone, textarea, select, checkbox, radio.' => 'Add a wbForm block to any page. Fields are built in the CP — text, email, phone, textarea, select, checkbox, radio.',
    'Each submission is stored in %path% with the submitted data, originating form reference, and visitor email.' => 'Each submission is stored in %path% with the submitted data, originating form reference, and visitor email.',
    'Admin notification email is sent immediately (Craft mail queue). Only fields with %toggle% toggled on are included.' => 'Admin notification email is sent immediately (Craft mail queue). Only fields with %toggle% toggled on are included.',
    'Include in admin email' => 'Include in admin email',
    'Visitor confirmation email is optional — enable it per form and set a subject and body template. Use %placeholder% placeholders in the body.' => 'Visitor confirmation email is optional — enable it per form and set a subject and body template. Use %placeholder% placeholders in the body.',
    'Honeypot spam protection is built in.' => 'Honeypot spam protection is built in.',
    'To enable Google reCAPTCHA, add your site and secret keys in %path%.' => 'To enable Google reCAPTCHA, add your site and secret keys in %path%.',
    'Comments are submitted via the front end and stored in %path% with %status% (pending).' => 'Comments are submitted via the front end and stored in %path% with %status% (pending).',
    'Approve or reject in bulk from the index using the element action menu, or use the %toggle% lightswitch on the individual comment edit page.' => 'Approve or reject in bulk from the index using the element action menu, or use the %toggle% lightswitch on the individual comment edit page.',
    'Only approved comments appear on the front end.' => 'Only approved comments appear on the front end.',
    'Every entry type that powers a page has %seoTitle% and %seoDesc% fields. Leave them blank to fall back to the entry title and site name. The %head% partial outputs the correct %title%, %desc%, and Open Graph tags automatically.' => 'Every entry type that powers a page has %seoTitle% and %seoDesc% fields. Leave them blank to fall back to the entry title and site name. The %head% partial outputs the correct %title%, %desc%, and Open Graph tags automatically.',
    'Set the brand colour in %path% using the %data-accent% attribute on %html%. WebBlocks ships 8 accent palettes — every component (buttons, cards, navbar, badges, alerts, forms) inherits the colour automatically. No rebuild required.' => 'Set the brand colour in %path% using the %data-accent% attribute on %html%. WebBlocks ships 8 accent palettes — every component (buttons, cards, navbar, badges, alerts, forms) inherits the colour automatically. No rebuild required.',
    'Accent'                      => 'Accent',
    'Professional, trustworthy, calming.' => 'Professional, trustworthy, calming.',
    'Rich green. Natural, organic, fresh.' => 'Rich green. Natural, organic, fresh.',
    'Warm orange-amber. Energetic, friendly, inviting.' => 'Warm orange-amber. Energetic, friendly, inviting.',
    'Deep purple-blue. Elegant, premium, authoritative.' => 'Deep purple-blue. Elegant, premium, authoritative.',
    'Soft mint-green. Modern, clean, approachable.' => 'Soft mint-green. Modern, clean, approachable.',
    'Golden amber. Warm, confident, bold.' => 'Golden amber. Warm, confident, bold.',
    'Dusty rose. Sophisticated, stylish, refined.' => 'Dusty rose. Sophisticated, stylish, refined.',
    'Slate-grey with fire-orange accents. Contemporary, edgy, distinctive.' => 'Slate-grey with fire-orange accents. Contemporary, edgy, distinctive.',
    'Set the component style package in %path% using the %data-preset% attribute on %html%. Combine with any accent colour for complete visual control.' => 'Set the component style package in %path% using the %data-preset% attribute on %html%. Combine with any accent colour for complete visual control.',
    'Preset'                      => 'Preset',
    'Clean lines, subtle shadows, contemporary feel.' => 'Clean lines, subtle shadows, contemporary feel.',
    'Reduced styling, maximum whitespace, stripped-back aesthetic.' => 'Reduced styling, maximum whitespace, stripped-back aesthetic.',
    'Fully rounded corners on all components. Soft and approachable.' => 'Fully rounded corners on all components. Soft and approachable.',
    'Heavy borders, strong contrast, impactful presence.' => 'Heavy borders, strong contrast, impactful presence.',
    'Serif headings, generous spacing, magazine-style layouts.' => 'Serif headings, generous spacing, magazine-style layouts.',
    'WebBlocks supports light, dark, and auto (system preference) colour modes. The mode toggle in the navbar calls %applyMode% which sets %data-mode% on %html% and stores the preference in localStorage.' => 'WebBlocks supports light, dark, and auto (system preference) colour modes. The mode toggle in the navbar calls %applyMode% which sets %data-mode% on %html% and stores the preference in localStorage.',
    'To apply custom styles on top of any preset, use %cssUrl% (external stylesheet) or %css% (inline rules injected into %head%).' => 'To apply custom styles on top of any preset, use %cssUrl% (external stylesheet) or %css% (inline rules injected into %head%).',
    "WebBlocks seeds content in English, Turkish, and German when those sites exist in Craft. Add more sites in %path% and translate entries normally via the site switcher on each entry's edit page." => "WebBlocks seeds content in English, Turkish, and German when those sites exist in Craft. Add more sites in %path% and translate entries normally via the site switcher on each entry's edit page.",
    'To reset all demo content and reinstall the full schema:' => 'To reset all demo content and reinstall the full schema:',
    'This is destructive — all existing WebBlocks content will be permanently deleted.' => 'This is destructive — all existing WebBlocks content will be permanently deleted.',
    'CKEditor plugin (installed automatically as a dependency)' => 'CKEditor plugin (installed automatically as a dependency)',
    'WebBlocks UI Kit CSS + JS (loaded from CDN — no npm required)' => 'WebBlocks UI Kit CSS + JS (loaded from CDN — no npm required)',

    // =========================================================================
    // Help page — Plugin Settings section
    // =========================================================================
    'Plugin Settings'            => 'Plugin Settings',
    'Setting'                    => 'Setting',
    'Configure WebBlocks at %path%.' => 'Configure WebBlocks at %path%.',
    "Fallback recipient for wbForm admin notifications when a form's own Recipient field is empty." => "Fallback recipient for wbForm admin notifications when a form's own Recipient field is empty.",
    'Receives an email whenever a new blog comment is submitted and awaits moderation.' => 'Receives an email whenever a new blog comment is submitted and awaits moderation.',
    'Controls the %title% tag format when wbSeoTitle is blank. Supports {title} and {siteName} placeholders.' => 'Controls the %title% tag format when wbSeoTitle is blank. Supports {title} and {siteName} placeholders.',
    'Which languages to seed on the next wipe + seed cycle (English is always included).' => 'Which languages to seed on the next wipe + seed cycle (English is always included).',
    'Google Analytics 4 ID (e.g. G-XXXXXXXXXX). Leave blank to disable.' => 'Google Analytics 4 ID (e.g. G-XXXXXXXXXX). Leave blank to disable.',
    'Matomo URL / Site ID'       => 'Matomo URL / Site ID',
    'Matomo instance URL and site ID. Both required to activate tracking.' => 'Matomo instance URL and site ID. Both required to activate tracking.',

    // =========================================================================
    // Help page — Component Versioning section
    // =========================================================================
    'Component Versioning'       => 'Component Versioning',
    'WebBlocks tracks the installed version of every component (field, entry type, matrix field) in the %table% database table. Use the console commands below to audit and migrate components when the plugin is updated.' => 'WebBlocks tracks the installed version of every component (field, entry type, matrix field) in the %table% database table. Use the console commands below to audit and migrate components when the plugin is updated.',
    'Command'                    => 'Command',
    'What it does'               => 'What it does',
    'Full diff report — JSON version vs installed DB state.' => 'Full diff report — JSON version vs installed DB state.',
    'Exit 0 if everything is up to date, exit 1 if action is needed (CI-friendly).' => 'Exit 0 if everything is up to date, exit 1 if action is needed (CI-friendly).',
    'Preview what a migration run would do without making any changes.' => 'Preview what a migration run would do without making any changes.',
    'Apply all pending component migrations.' => 'Apply all pending component migrations.',
    'List deprecated fields. Add --force to permanently delete them.' => 'List deprecated fields. Add --force to permanently delete them.',
    'The %link% in the CP sidebar shows a live health summary — pending migrations, deprecated fields, and component version mismatches.' => 'The %link% in the CP sidebar shows a live health summary — pending migrations, deprecated fields, and component version mismatches.',

    // =========================================================================
    // Comment moderation (CP)
    // =========================================================================
    'Approval Status'                                   => 'Approval Status',
    'Approved'                                          => 'Approved',
    'Pending'                                           => 'Pending',
    'Are you sure you want to reject this comment?'     => 'Are you sure you want to reject this comment?',
];
