<?php
/**
 * WebBlocks plugin for Craft CMS
 *
 * German translations (webblocks category).
 * Used by wbTemplates sections, error pages, and form validation.
 */

return [
    // =========================================================================
    // Error pages
    // =========================================================================
    'Back to homepage'           => 'Zur Startseite',
    'Bad Request'                => 'Ungültige Anfrage',
    'Internal Server Error'      => 'Interner Serverfehler',
    'Page Not Found'             => 'Seite nicht gefunden',
    'Service Unavailable'        => 'Dienst nicht verfügbar',
    'Unauthorized'               => 'Nicht autorisiert',
    'An error occurred while processing your request.' => 'Bei der Verarbeitung Ihrer Anfrage ist ein Fehler aufgetreten.',
    'Our site is temporarily unavailable. Please try again later.' => 'Unsere Website ist vorübergehend nicht verfügbar. Bitte versuchen Sie es später erneut.',
    'The request could not be understood by the server due to malformed syntax.' => 'Die Anfrage konnte vom Server aufgrund fehlerhafter Syntax nicht verstanden werden.',
    'The requested URL was not found on this server.' => 'Die angeforderte URL wurde auf diesem Server nicht gefunden.',
    "You don't have the proper credentials to access this page." => 'Sie haben nicht die erforderlichen Anmeldedaten, um auf diese Seite zuzugreifen.',

    // =========================================================================
    // Blog / References listing pages
    // =========================================================================
    'Blog'               => 'Blog',
    'References'         => 'Referenzen',
    'No posts yet.'      => 'Noch keine Beiträge vorhanden.',
    'No references yet.' => 'Noch keine Referenzen vorhanden.',
    'Blog post pages'    => 'Blog-Beitragsseiten',
    'Reference pages'    => 'Referenzseiten',
    'Previous'           => 'Zurück',
    'Next'               => 'Weiter',

    // =========================================================================
    // Search
    // =========================================================================
    'Search'                               => 'Suche',
    'Search: {query}'                      => 'Suche: {query}',
    'Search all content…'                  => 'Alle Inhalte durchsuchen…',
    'Enter a search term above to find content.' => 'Geben Sie oben einen Suchbegriff ein, um Inhalte zu finden.',
    'No results found for "{query}".'      => 'Keine Ergebnisse für „{query}" gefunden.',
    '1 result for "{query}"'               => '1 Ergebnis für „{query}"',
    '{total} results for "{query}"'        => '{total} Ergebnisse für „{query}"',
    'Search result pages'                  => 'Suchergebnisseiten',

    // =========================================================================
    // CP navigation
    // =========================================================================
    'Help'                                              => 'Hilfe',
    'Component Health'                                  => 'Komponentenstatus',

    // =========================================================================
    // Plugin settings (CP)
    // =========================================================================
    'Default Admin Email'    => 'Standard-Admin-E-Mail',
    "Fallback recipient for wbForm admin notification emails when a form's own Recipient field is empty." => "Ersatzempfänger für wbForm-Admin-Benachrichtigungs-E-Mails, wenn das Empfängerfeld des Formulars leer ist.",
    'Comment Notification Email' => 'Kommentar-Benachrichtigungs-E-Mail',
    'When set, an email is sent to this address every time a new comment is submitted and is awaiting moderation.' => 'Wenn festgelegt, wird bei jedem neuen Kommentar, der auf Moderation wartet, eine E-Mail an diese Adresse gesendet.',
    'SEO'                    => 'SEO',
    'Page Title Format'      => 'Seitentitelformat',
    "Format for the <title> tag when wbSeoTitle is empty. Use {title} and {siteName} as placeholders. Example: {title} — {siteName}" => "Format für das <title>-Tag, wenn wbSeoTitle leer ist. Verwenden Sie {title} und {siteName} als Platzhalter. Beispiel: {title} — {siteName}",
    'Seed Content'           => 'Seed-Inhalt',
    'Seed Languages'         => 'Seed-Sprachen',
    'Languages to seed when running the webblocks/seed command. English is always seeded. Changes take effect on the next wipe + seed cycle.' => 'Sprachen, die beim Ausführen des webblocks/seed-Befehls geseedet werden. Englisch wird immer geseedet. Änderungen werden im nächsten Wipe + Seed-Zyklus wirksam.',
    'Analytics'              => 'Analytics',
    'GA4 Measurement ID'     => 'GA4 Mess-ID',
    'Google Analytics 4 Measurement ID (e.g. G-XXXXXXXXXX). Leave blank to disable.' => 'Google Analytics 4 Mess-ID (z. B. G-XXXXXXXXXX). Leer lassen zum Deaktivieren.',
    'Matomo URL'             => 'Matomo-URL',
    'Your Matomo instance URL (e.g. https://analytics.example.com/). Both URL and Site ID are required.' => 'Ihre Matomo-Instanz-URL (z. B. https://analytics.example.com/). Sowohl URL als auch Website-ID sind erforderlich.',
    'Matomo Site ID'         => 'Matomo-Website-ID',
    'Your Matomo Site ID (e.g. 1). Both URL and Site ID are required.' => 'Ihre Matomo-Website-ID (z. B. 1). Sowohl URL als auch Website-ID sind erforderlich.',

    // =========================================================================
    // Help page — headings & table headers
    // =========================================================================
    'Quick Start'                => 'Schnellstart',
    'Content Blocks (%field% matrix)' => 'Inhaltsblöcke (%field%-Matrix)',
    'Block'                      => 'Block',
    'What it renders'            => 'Was es darstellt',
    'Global Sets'                => 'Globale Sets',
    'Field'                      => 'Feld',
    'Purpose'                    => 'Zweck',
    'Sections & Entry Types'     => 'Bereiche & Eintragstypen',
    'Section'                    => 'Bereich',
    'Type'                       => 'Typ',
    'Forms & Submissions'        => 'Formulare & Einsendungen',
    'Blog Comments'              => 'Blog-Kommentare',
    'Colour Themes'              => 'Farbthemen',
    'Theme'                      => 'Thema',
    'Character'                  => 'Charakter',
    'Multi-language'             => 'Mehrsprachig',
    'Wipe & Reseed'              => 'Zurücksetzen & Neu befüllen',
    'Requirements'               => 'Anforderungen',
    'Necessary'                  => 'Notwendig',

    // =========================================================================
    // Help page — body strings
    // =========================================================================
    'WebBlocks is a portable website-building toolkit for Craft CMS 5. Install it, run the seeder, and get a fully working multi-language business site — complete with pages, blog, forms, SEO, cookie consent, and a colour theme system — in minutes.' => 'WebBlocks ist ein portables Website-Baukastensystem für Craft CMS 5. Installieren Sie es, führen Sie den Seeder aus und erhalten Sie in Minuten eine voll funktionsfähige mehrsprachige Unternehmenswebsite — komplett mit Seiten, Blog, Formularen, SEO, Cookie-Einwilligung und einem Farbthema-System.',
    'Install the plugin via the Plugin Store or Composer.' => 'Installieren Sie das Plugin über den Plugin Store oder Composer.',
    'Run the seed command from your project root:' => 'Führen Sie den Seed-Befehl aus Ihrem Projektstammverzeichnis aus:',
    'Visit %globals% to set the site name, navbar, footer links, and colour theme.' => 'Besuchen Sie %globals%, um den Sitenamen, die Navigationsleiste, Footer-Links und das Farbthema festzulegen.',
    'Edit, duplicate, or delete the seeded entries in %entries% as needed.' => 'Bearbeiten, duplizieren oder löschen Sie die vorausgefüllten Einträge in %entries% nach Bedarf.',
    'Every page uses a %field% matrix field. Add, remove, and reorder blocks freely.' => 'Jede Seite verwendet ein %field% Matrixfeld. Fügen Sie Blöcke frei hinzu, entfernen und ordnen Sie sie neu an.',
    'Full-width hero banner with optional overlay, headline, text, and CTA button.' => 'Volldbreites Hero-Banner mit optionalem Overlay, Überschrift, Text und CTA-Schaltfläche.',
    'Auto-playing fullscreen slider with dot pagination and caption alignment controls.' => 'Automatisch abspielender Vollbild-Slider mit Punkt-Paginierung und Untertitel-Ausrichtungssteuerung.',
    'Bootstrap carousel with optional captions and frosted-glass overlay.' => 'Bootstrap-Karussell mit optionalen Untertiteln und Milchglas-Overlay.',
    'Masonry photo gallery with a built-in lightbox.' => 'Masonry-Fotogalerie mit integriertem Lightbox.',
    'Bootstrap card grid — title, image, text, optional link button.' => 'Bootstrap-Kartenraster — Titel, Bild, Text, optionale Link-Schaltfläche.',
    'Two-column image + text layout with swappable sides.' => 'Zweispaltiges Bild + Text-Layout mit austauschbaren Seiten.',
    '2/3/4-column layout; each column holds its own full %field% stack (recursive nesting).' => '2/3/4-Spalten-Layout; jede Spalte enthält ihren eigenen vollständigen %field%-Stack (rekursive Verschachtelung).',
    'Rich-text prose with optional heading.' => 'Rich-Text-Prosa mit optionaler Überschrift.',
    'Standalone heading (h1–h6) with alignment and colour variant.' => 'Eigenständige Überschrift (h1–h6) mit Ausrichtung und Farbvariante.',
    'Centred CTA section with heading, text, and button.' => 'Zentrierter CTA-Bereich mit Überschrift, Text und Schaltfläche.',
    'Contact/enquiry form — stores submissions, sends admin email, optional visitor confirmation email.' => 'Kontakt-/Anffrageformular — speichert Einsendungen, sendet Admin-E-Mail, optionale Besucher-Bestätigungs-E-Mail.',
    'Bootstrap accordion / FAQ block.' => 'Bootstrap-Akkordeon / FAQ-Block.',
    'Bootstrap tabbed content panels.' => 'Bootstrap-Tabbed-Inhaltspanele.',
    'Data table with configurable column headers.' => 'Datentabelle mit konfigurierbaren Spaltenüberschriften.',
    'Bootstrap list group — plain, linked, or with badges.' => 'Bootstrap-Listengruppe — einfach, verlinkt oder mit Abzeichen.',
    'Responsive YouTube / Vimeo embed.' => 'Responsives YouTube / Vimeo-Einbettung.',
    'Address, phone, email, and map embed.' => 'Adresse, Telefon, E-Mail und Karteneinbettung.',
    'Bootstrap alert box — dismissible, with theme-aware default colour.' => 'Bootstrap-Warnmeldungsfeld — schließbar, mit themenbewusster Standardfarbe.',
    'Inline Bootstrap badge — pill option, theme-aware default colour.' => 'Inline-Bootstrap-Abzeichen — Pillen-Option, themenbewusste Standardfarbe.',
    'Horizontal or vertical Bootstrap button group.' => 'Horizontale oder vertikale Bootstrap-Schaltflächengruppe.',
    'Bootstrap modal dialog triggered by a button.' => 'Bootstrap-Modal-Dialog, ausgelöst durch eine Schaltfläche.',
    'Bootstrap offcanvas panel (drawer) with nested blocks.' => 'Bootstrap-Offcanvas-Panel (Schublade) mit verschachtelten Blöcken.',
    'Bootstrap popover tooltip on a button.' => 'Bootstrap-Popover-Tooltip auf einer Schaltfläche.',
    'Bootstrap progress bar with label and colour variant.' => 'Bootstrap-Fortschrittsbalken mit Beschriftung und Farbvariante.',
    'Bootstrap loading spinner.' => 'Bootstrap-Ladedrehkreuz.',
    'Bootstrap toast notification (always visible, no JS trigger required).' => 'Bootstrap-Toast-Benachrichtigung (immer sichtbar, kein JS-Auslöser erforderlich).',
    'Bootstrap pagination demo block.' => 'Bootstrap-Paginierung-Demo-Block.',
    "Sticky top navigation bar — auto-populated from entries marked 'Show in nav'." => "Haftende obere Navigationsleiste — automatisch aus als 'In Nav anzeigen' markierten Einträgen befüllt.",
    'Vertical whitespace spacer.' => 'Vertikaler Leerraum-Abstandshalter.',
    'Master configuration for the site: navbar block, footer block, colour mode (light/dark/auto), and colour theme.' => 'Hauptkonfiguration der Website: Navigationsleistenblock, Footer-Block, Farbmodus (hell/dunkel/automatisch) und Farbthema.',
    'Bootstrap colour mode — %light%, %dark%, or %auto% (follows OS preference).' => 'Bootstrap-Farbmodus — %light%, %dark% oder %auto% (folgt der Betriebssystempräferenz).',
    'Colour theme — selects a Bootswatch theme. WebBlocks loads the matching CSS from CDN automatically.' => 'Farbthema — wählt ein Bootswatch-Thema aus. WebBlocks lädt das passende CSS automatisch vom CDN.',
    'A single wbNavbar block used site-wide.' => 'Ein einziger wbNavbar-Block, der site-weit verwendet wird.',
    'A single footer block used site-wide.' => 'Ein einziger Footer-Block, der site-weit verwendet wird.',
    'Controls the cookie consent banner. Enable the banner, set the title, body text, and privacy URL. Four categories: %necessary% (always on), Analytics, Marketing, Preferences. Consent is stored in %storage% as %key%; the %obj% object is published for third-party scripts.' => 'Steuert das Cookie-Einwilligungsbanner. Aktivieren Sie das Banner, legen Sie Titel, Textinhalt und Datenschutz-URL fest. Vier Kategorien: %necessary% (immer aktiv), Analyse, Marketing, Einstellungen. Die Einwilligung wird in %storage% als %key% gespeichert; das %obj%-Objekt wird für Drittanbieter-Skripte veröffentlicht.',
    'Homepage — full wbBlocks stack.' => 'Startseite — vollständiger wbBlocks-Stack.',
    'General-purpose pages with wbBlocks.' => 'Allgemeine Seiten mit wbBlocks.',
    'About / team pages.' => 'Über uns / Team-Seiten.',
    'Services / products pages.' => 'Dienstleistungen / Produkte-Seiten.',
    'Blog listing page with pagination.' => 'Blog-Listenseite mit Paginierung.',
    'Blog posts — featured image, categories, tags, comments.' => 'Blog-Beiträge — Hauptbild, Kategorien, Tags, Kommentare.',
    'Blog comments (pending by default). Approve / Reject via CP element actions or the inline toggle on the edit page.' => 'Blog-Kommentare (standardmäßig ausstehend). Genehmigen / Ablehnen über CP-Elementaktionen oder den Inline-Schalter auf der Bearbeitungsseite.',
    'Contact page with a wbForm block.' => 'Kontaktseite mit einem wbForm-Block.',
    'Stores every form submission. Manage status (Unread / Read / Archived) via CP element actions.' => 'Speichert jede Formulareinsendung. Status verwalten (Ungelesen / Gelesen / Archiviert) über CP-Elementaktionen.',
    'Site search — uses craft.entries.search().' => 'Website-Suche — verwendet craft.entries.search().',
    'Client logos / references for a references block.' => 'Kundenlogos / Referenzen für einen Referenzen-Block.',
    'Add a wbForm block to any page. Fields are built in the CP — text, email, phone, textarea, select, checkbox, radio.' => 'Fügen Sie einer beliebigen Seite einen wbForm-Block hinzu. Felder werden im CP erstellt — Text, E-Mail, Telefon, Textbereich, Auswahl, Kontrollkästchen, Radio.',
    'Each submission is stored in %path% with the submitted data, originating form reference, and visitor email.' => 'Jede Einsendung wird in %path% mit den übermittelten Daten, dem Ursprungsformular-Verweis und der Besucher-E-Mail gespeichert.',
    'Admin notification email is sent immediately (Craft mail queue). Only fields with %toggle% toggled on are included.' => 'Admin-Benachrichtigungs-E-Mail wird sofort gesendet (Craft-Mail-Warteschlange). Nur Felder mit aktiviertem %toggle% werden einbezogen.',
    'Include in admin email' => 'In Admin-E-Mail aufnehmen',
    'Visitor confirmation email is optional — enable it per form and set a subject and body template. Use %placeholder% placeholders in the body.' => 'Besucher-Bestätigungs-E-Mail ist optional — aktivieren Sie sie pro Formular und legen Sie ein Betreff- und Textvorlage fest. Verwenden Sie %placeholder%-Platzhalter im Text.',
    'Honeypot spam protection is built in.' => 'Honeypot-Spam-Schutz ist integriert.',
    'To enable Google reCAPTCHA, add your site and secret keys in %path%.' => 'Um Google reCAPTCHA zu aktivieren, fügen Sie Ihre Website- und Geheimschlüssel in %path% hinzu.',
    'Comments are submitted via the front end and stored in %path% with %status% (pending).' => 'Kommentare werden über das Frontend eingereicht und in %path% mit %status% (ausstehend) gespeichert.',
    'Approve or reject in bulk from the index using the element action menu, or use the %toggle% lightswitch on the individual comment edit page.' => 'Genehmigen oder ablehnen Sie in großen Mengen aus dem Index über das Element-Aktionsmenü, oder verwenden Sie den %toggle%-Lichtschalter auf der individuellen Kommentar-Bearbeitungsseite.',
    'Only approved comments appear on the front end.' => 'Nur genehmigte Kommentare erscheinen im Frontend.',
    'Every entry type that powers a page has %seoTitle% and %seoDesc% fields. Leave them blank to fall back to the entry title and site name. The %head% partial outputs the correct %title%, %desc%, and Open Graph tags automatically.' => 'Jeder Eintragstyp, der eine Seite betreibt, hat %seoTitle%- und %seoDesc%-Felder. Lassen Sie sie leer, um auf den Eintragstitel und den Sitenamen zurückzugreifen. Die %head%-Partial gibt automatisch die korrekten %title%-, %desc%- und Open-Graph-Tags aus.',
    'Set the brand colour in %path% using the %data-accent% attribute on %html%. WebBlocks ships 8 accent palettes — every component (buttons, cards, navbar, badges, alerts, forms) inherits the colour automatically. No rebuild required.' => 'Legen Sie die Markenfarbe in %path% mit dem %data-accent%-Attribut auf %html% fest. WebBlocks wird mit 8 Akzentpaletten geliefert — jede Komponente (Schaltflächen, Karten, Navigationsleiste, Abzeichen, Warnmeldungen, Formulare) erbt die Farbe automatisch. Kein Neuaufbau erforderlich.',
    'Accent'                      => 'Akzent',
    'Professional, trustworthy, calming.' => 'Professionell, vertrauenswürdig, beruhigend.',
    'Rich green. Natural, organic, fresh.' => 'Reiches Grün. Natürlich, organisch, frisch.',
    'Warm orange-amber. Energetic, friendly, inviting.' => 'Warmes Orange-Bernstein. Energetisch, freundlich, einladend.',
    'Deep purple-blue. Elegant, premium, authoritative.' => 'Tiefes Purpur-Blau. Elegant, premium, authoritativ.',
    'Soft mint-green. Modern, clean, approachable.' => 'Sanftes Mint-Grün. Modern, sauber, zugänglich.',
    'Golden amber. Warm, confident, bold.' => 'Goldenes Bernstein. Warm, selbstbewusst, mutig.',
    'Dusty rose. Sophisticated, stylish, refined.' => 'Staubiges Rosa. Raffiniert, stilvoll, verfeinert.',
    'Slate-grey with fire-orange accents. Contemporary, edgy, distinctive.' => 'Schiefergrau mit feuer-orangefarbenen Akzenten. Zeitgenössisch, kantig, unverwechselbar.',
    'Set the component style package in %path% using the %data-preset% attribute on %html%. Combine with any accent colour for complete visual control.' => 'Legen Sie das Komponenten-Stilpaket in %path% mit dem %data-preset%-Attribut auf %html% fest. Kombinieren Sie es mit einer beliebigen Akzentfarbe für vollständige visuelle Kontrolle.',
    'Preset'                      => 'Stilpaket',
    'Clean lines, subtle shadows, contemporary feel.' => 'Klare Linien, subtile Schatten, zeitgenössisches Gefühl.',
    'Reduced styling, maximum whitespace, stripped-back aesthetic.' => 'Reduziertes Styling, maximaler Weißraum, zurückhaltende Ästhetik.',
    'Fully rounded corners on all components. Soft and approachable.' => 'Vollständig abgerundete Ecken an allen Komponenten. Weich und zugänglich.',
    'Heavy borders, strong contrast, impactful presence.' => 'Schwere Rahmen, starker Kontrast, präsenter Auftritt.',
    'Serif headings, generous spacing, magazine-style layouts.' => 'Serif-Überschriften, großzügige Abstände, Zeitschrift-Layouts.',
    'WebBlocks supports light, dark, and auto (system preference) colour modes. The mode toggle in the navbar calls %applyMode% which sets %data-mode% on %html% and stores the preference in localStorage.' => 'WebBlocks unterstützt Hell-, Dunkel- und Auto-Modi (Systemeinstellung). Der Modus-Umschalter in der Navigationsleiste ruft %applyMode% auf, das %data-mode% auf %html% setzt und die Präferenz in localStorage speichert.',
    'To apply custom styles on top of any preset, use %cssUrl% (external stylesheet) or %css% (inline rules injected into %head%).' => 'Um benutzerdefinierte Stile über ein beliebiges Stilpaket anzuwenden, verwenden Sie %cssUrl% (externes Stylesheet) oder %css% (in %head% eingefügte Inline-Regeln).',
    "WebBlocks seeds content in English, Turkish, and German when those sites exist in Craft. Add more sites in %path% and translate entries normally via the site switcher on each entry's edit page." => "WebBlocks füllt Inhalte auf Englisch, Türkisch und Deutsch vor, wenn diese Sites in Craft vorhanden sind. Fügen Sie weitere Sites in %path% hinzu und übersetzen Sie Einträge normal über den Site-Wechsler auf der Bearbeitungsseite jedes Eintrags.",
    'To reset all demo content and reinstall the full schema:' => 'Um alle Demo-Inhalte zurückzusetzen und das vollständige Schema neu zu installieren:',
    'This is destructive — all existing WebBlocks content will be permanently deleted.' => 'Dies ist destruktiv — alle vorhandenen WebBlocks-Inhalte werden dauerhaft gelöscht.',
    'CKEditor plugin (installed automatically as a dependency)' => 'CKEditor-Plugin (wird automatisch als Abhängigkeit installiert)',
    'WebBlocks UI Kit CSS + JS (loaded from CDN — no npm required)' => 'WebBlocks UI Kit CSS + JS (von CDN geladen — kein npm erforderlich)',

    // =========================================================================
    // Help page — Plugin Settings section
    // =========================================================================
    'Plugin Settings'            => 'Plugin-Einstellungen',
    'Setting'                    => 'Einstellung',
    'Configure WebBlocks at %path%.' => 'Konfigurieren Sie WebBlocks unter %path%.',
    "Fallback recipient for wbForm admin notifications when a form's own Recipient field is empty." => "Ersatzempfänger für wbForm-Admin-Benachrichtigungen, wenn das Empfängerfeld des Formulars leer ist.",
    'Receives an email whenever a new blog comment is submitted and awaits moderation.' => 'Erhält eine E-Mail, wenn ein neuer Blog-Kommentar eingereicht wird und auf Moderation wartet.',
    'Controls the %title% tag format when wbSeoTitle is blank. Supports {title} and {siteName} placeholders.' => 'Steuert das %title%-Tag-Format, wenn wbSeoTitle leer ist. Unterstützt {title} und {siteName} als Platzhalter.',
    'Which languages to seed on the next wipe + seed cycle (English is always included).' => 'Welche Sprachen beim nächsten Wipe + Seed-Zyklus befüllt werden (Englisch ist immer enthalten).',
    'Google Analytics 4 ID (e.g. G-XXXXXXXXXX). Leave blank to disable.' => 'Google Analytics 4 ID (z. B. G-XXXXXXXXXX). Leer lassen zum Deaktivieren.',
    'Matomo URL / Site ID'       => 'Matomo-URL / Website-ID',
    'Matomo instance URL and site ID. Both required to activate tracking.' => 'Matomo-Instanz-URL und Website-ID. Beide erforderlich, um das Tracking zu aktivieren.',

    // =========================================================================
    // Help page — Component Versioning section
    // =========================================================================
    'Component Versioning'       => 'Komponentenversionierung',
    'WebBlocks tracks the installed version of every component (field, entry type, matrix field) in the %table% database table. Use the console commands below to audit and migrate components when the plugin is updated.' => 'WebBlocks verfolgt die installierte Version jeder Komponente (Feld, Eintragstyp, Matrixfeld) in der %table%-Datenbanktabelle. Verwenden Sie die folgenden Konsolenbefehle, um Komponenten zu prüfen und zu migrieren, wenn das Plugin aktualisiert wird.',
    'Command'                    => 'Befehl',
    'What it does'               => 'Was es macht',
    'Full diff report — JSON version vs installed DB state.' => 'Vollständiger Diff-Bericht — JSON-Version vs. installierter DB-Zustand.',
    'Exit 0 if everything is up to date, exit 1 if action is needed (CI-friendly).' => 'Beendet mit 0, wenn alles aktuell ist, mit 1, wenn Aktion erforderlich ist (CI-freundlich).',
    'Preview what a migration run would do without making any changes.' => 'Vorschau dessen, was ein Migrationslauf tun würde, ohne Änderungen vorzunehmen.',
    'Apply all pending component migrations.' => 'Alle ausstehenden Komponentenmigrationen anwenden.',
    'List deprecated fields. Add --force to permanently delete them.' => 'Veraltete Felder auflisten. --force hinzufügen, um sie dauerhaft zu löschen.',
    'The %link% in the CP sidebar shows a live health summary — pending migrations, deprecated fields, and component version mismatches.' => 'Der %link% in der CP-Seitenleiste zeigt eine Live-Gesundheitszusammenfassung — ausstehende Migrationen, veraltete Felder und Komponentenversionskonflikte.',

    // =========================================================================
    // Comment moderation (CP)
    // =========================================================================
    'Approval Status'                                   => 'Genehmigungsstatus',
    'Approved'                                          => 'Genehmigt',
    'Pending'                                           => 'Ausstehend',
    'Are you sure you want to reject this comment?'     => 'Möchten Sie diesen Kommentar wirklich ablehnen?',
];
