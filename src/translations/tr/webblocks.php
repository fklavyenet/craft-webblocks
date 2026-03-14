<?php
/**
 * WebBlocks plugin for Craft CMS
 *
 * Turkish translations (webblocks category).
 * Used by wbTemplates sections, error pages, and form validation.
 */

return [
    // =========================================================================
    // Error pages
    // =========================================================================
    'Back to homepage'           => 'Ana sayfaya dön',
    'Bad Request'                => 'Geçersiz İstek',
    'Internal Server Error'      => 'Dahili Sunucu Hatası',
    'Page Not Found'             => 'Sayfa Bulunamadı',
    'Service Unavailable'        => 'Hizmet Kullanılamıyor',
    'Unauthorized'               => 'Yetkisiz Erişim',
    'An error occurred while processing your request.' => 'İsteğiniz işlenirken bir hata oluştu.',
    'Our site is temporarily unavailable. Please try again later.' => 'Sitemiz geçici olarak kullanılamıyor. Lütfen daha sonra tekrar deneyin.',
    'The request could not be understood by the server due to malformed syntax.' => 'İstek, hatalı sözdizimi nedeniyle sunucu tarafından anlaşılamadı.',
    'The requested URL was not found on this server.' => 'İstenen URL bu sunucuda bulunamadı.',
    "You don't have the proper credentials to access this page." => 'Bu sayfaya erişmek için gerekli kimlik bilgilerine sahip değilsiniz.',

    // =========================================================================
    // Blog / References listing pages
    // =========================================================================
    'Blog'               => 'Blog',
    'References'         => 'Referanslar',
    'No posts yet.'      => 'Henüz yazı yok.',
    'No references yet.' => 'Henüz referans yok.',
    'Blog post pages'    => 'Blog yazısı sayfaları',
    'Reference pages'    => 'Referans sayfaları',
    'Previous'           => 'Önceki',
    'Next'               => 'Sonraki',

    // =========================================================================
    // Search
    // =========================================================================
    'Search'                               => 'Ara',
    'Search: {query}'                      => 'Arama: {query}',
    'Search all content…'                  => 'Tüm içeriklerde ara…',
    'Enter a search term above to find content.' => 'İçerik bulmak için yukarıya bir arama terimi girin.',
    'No results found for "{query}".'      => '"{query}" için sonuç bulunamadı.',
    '1 result for "{query}"'               => '"{query}" için 1 sonuç',
    '{total} results for "{query}"'        => '"{query}" için {total} sonuç',
    'Search result pages'                  => 'Arama sonucu sayfaları',

    // =========================================================================
    // CP navigation
    // =========================================================================
    'Help'                                              => 'Yardım',
    'Component Health'                                  => 'Bileşen Sağlığı',

    // =========================================================================
    // Plugin settings (CP)
    // =========================================================================
    'Default Admin Email'    => 'Varsayılan Yönetici E-postası',
    "Fallback recipient for wbForm admin notification emails when a form's own Recipient field is empty." => "Formun kendi Alıcı alanı boş olduğunda wbForm yönetici bildirimi e-postalarının gönderileceği yedek adres.",
    'Comment Notification Email' => 'Yorum Bildirim E-postası',
    'When set, an email is sent to this address every time a new comment is submitted and is awaiting moderation.' => 'Ayarlandığında, her yeni yorum gönderildiğinde ve moderasyon beklendiğinde bu adrese bir e-posta gönderilir.',
    'SEO'                    => 'SEO',
    'Page Title Format'      => 'Sayfa Başlığı Formatı',
    "Format for the <title> tag when wbSeoTitle is empty. Use {title} and {siteName} as placeholders. Example: {title} — {siteName}" => "wbSeoTitle boş olduğunda <title> etiketi için format. {title} ve {siteName} yer tutucularını kullanın. Örnek: {title} — {siteName}",
    'Seed Content'           => 'Seed İçeriği',
    'Seed Languages'         => 'Seed Dilleri',
    'Languages to seed when running the webblocks/seed command. English is always seeded. Changes take effect on the next wipe + seed cycle.' => 'webblocks/seed komutu çalıştırıldığında seed edilecek diller. İngilizce her zaman seed edilir. Değişiklikler bir sonraki wipe + seed döngüsünde geçerli olur.',
    'Analytics'              => 'Analitik',
    'GA4 Measurement ID'     => 'GA4 Ölçüm Kimliği',
    'Google Analytics 4 Measurement ID (e.g. G-XXXXXXXXXX). Leave blank to disable.' => 'Google Analytics 4 Ölçüm Kimliği (örn. G-XXXXXXXXXX). Devre dışı bırakmak için boş bırakın.',
    'Matomo URL'             => 'Matomo URL',
    'Your Matomo instance URL (e.g. https://analytics.example.com/). Both URL and Site ID are required.' => 'Matomo örnek URL\'niz (örn. https://analytics.example.com/). Hem URL hem de Site Kimliği gereklidir.',
    'Matomo Site ID'         => 'Matomo Site Kimliği',
    'Your Matomo Site ID (e.g. 1). Both URL and Site ID are required.' => 'Matomo Site Kimliğiniz (örn. 1). Hem URL hem de Site Kimliği gereklidir.',

    // =========================================================================
    // Help page — headings & table headers
    // =========================================================================
    'Quick Start'                => 'Hızlı Başlangıç',
    'Content Blocks (%field% matrix)' => 'İçerik Blokları (%field% matrisi)',
    'Block'                      => 'Blok',
    'What it renders'            => 'Ne görüntüler',
    'Global Sets'                => 'Global Setler',
    'Field'                      => 'Alan',
    'Purpose'                    => 'Amaç',
    'Sections & Entry Types'     => 'Bölümler ve Giriş Türleri',
    'Section'                    => 'Bölüm',
    'Type'                       => 'Tür',
    'Forms & Submissions'        => 'Formlar ve Gönderimler',
    'Blog Comments'              => 'Blog Yorumları',
    'Colour Themes'              => 'Renk Temaları',
    'Theme'                      => 'Tema',
    'Character'                  => 'Karakter',
    'Multi-language'             => 'Çok Dilli',
    'Wipe & Reseed'              => 'Sıfırla ve Yeniden Tohumla',
    'Requirements'               => 'Gereksinimler',
    'Necessary'                  => 'Zorunlu',

    // =========================================================================
    // Help page — body strings
    // =========================================================================
    'WebBlocks is a portable website-building toolkit for Craft CMS 5. Install it, run the seeder, and get a fully working multi-language business site — complete with pages, blog, forms, SEO, cookie consent, and a colour theme system — in minutes.' => 'WebBlocks, Craft CMS 5 için taşınabilir bir web sitesi oluşturma araç setidir. Yükleyin, tohumlayıcıyı çalıştırın ve dakikalar içinde sayfalar, blog, formlar, SEO, çerez onayı ve renk teması sistemi dahil tam işlevsel çok dilli bir iş sitesi elde edin.',
    'Install the plugin via the Plugin Store or Composer.' => 'Eklentiyi Plugin Store veya Composer aracılığıyla yükleyin.',
    'Run the seed command from your project root:' => 'Tohum komutunu proje kök dizininden çalıştırın:',
    'Visit %globals% to set the site name, navbar, footer links, and colour theme.' => 'Site adını, gezinti çubuğunu, alt bilgi bağlantılarını ve renk temasını ayarlamak için %globals% sayfasını ziyaret edin.',
    'Edit, duplicate, or delete the seeded entries in %entries% as needed.' => 'Tohumlanmış girişleri %entries% bölümünde gerektiği gibi düzenleyin, çoğaltın veya silin.',
    'Every page uses a %field% matrix field. Add, remove, and reorder blocks freely.' => 'Her sayfa bir %field% matris alanı kullanır. Blokları özgürce ekleyin, kaldırın ve yeniden sıralayın.',
    'Full-width hero banner with optional overlay, headline, text, and CTA button.' => 'İsteğe bağlı kaplama, başlık, metin ve CTA düğmesiyle tam genişlikte hero banner.',
    'Auto-playing fullscreen slider with dot pagination and caption alignment controls.' => 'Nokta sayfalama ve altyazı hizalama kontrolleriyle otomatik oynatılan tam ekran slider.',
    'Bootstrap carousel with optional captions and frosted-glass overlay.' => 'İsteğe bağlı altyazılar ve buzlu cam kaplamayla Bootstrap carousel.',
    'Masonry photo gallery with a built-in lightbox.' => 'Yerleşik lightbox ile masonry fotoğraf galerisi.',
    'Bootstrap card grid — title, image, text, optional link button.' => 'Bootstrap kart ızgarası — başlık, resim, metin, isteğe bağlı bağlantı düğmesi.',
    'Two-column image + text layout with swappable sides.' => 'Tarafları değiştirilebilen iki sütunlu resim + metin düzeni.',
    '2/3/4-column layout; each column holds its own full %field% stack (recursive nesting).' => '2/3/4 sütunlu düzen; her sütun kendi tam %field% yığınını tutar (özyinelemeli iç içe geçme).',
    'Rich-text prose with optional heading.' => 'İsteğe bağlı başlıkla zengin metin.',
    'Standalone heading (h1–h6) with alignment and colour variant.' => 'Hizalama ve renk varyantlı bağımsız başlık (h1–h6).',
    'Centred CTA section with heading, text, and button.' => 'Başlık, metin ve düğmeyle ortalanmış CTA bölümü.',
    'Contact/enquiry form — stores submissions, sends admin email, optional visitor confirmation email.' => 'İletişim/sorgu formu — gönderimleri saklar, yönetici e-postası gönderir, isteğe bağlı ziyaretçi onay e-postası.',
    'Bootstrap accordion / FAQ block.' => 'Bootstrap akordeon / SSS bloğu.',
    'Bootstrap tabbed content panels.' => 'Bootstrap sekmeli içerik panelleri.',
    'Data table with configurable column headers.' => 'Yapılandırılabilir sütun başlıklarıyla veri tablosu.',
    'Bootstrap list group — plain, linked, or with badges.' => 'Bootstrap liste grubu — düz, bağlantılı veya rozetli.',
    'Responsive YouTube / Vimeo embed.' => 'Duyarlı YouTube / Vimeo yerleştirmesi.',
    'Address, phone, email, and map embed.' => 'Adres, telefon, e-posta ve harita yerleştirmesi.',
    'Bootstrap alert box — dismissible, with theme-aware default colour.' => 'Bootstrap uyarı kutusu — kapatılabilir, tema farkında varsayılan renk.',
    'Inline Bootstrap badge — pill option, theme-aware default colour.' => 'Satır içi Bootstrap rozeti — hap seçeneği, tema farkında varsayılan renk.',
    'Horizontal or vertical Bootstrap button group.' => 'Yatay veya dikey Bootstrap düğme grubu.',
    'Bootstrap modal dialog triggered by a button.' => 'Bir düğmeyle tetiklenen Bootstrap modal diyaloğu.',
    'Bootstrap offcanvas panel (drawer) with nested blocks.' => 'İç içe bloklarla Bootstrap offcanvas paneli (çekmece).',
    'Bootstrap popover tooltip on a button.' => 'Bir düğme üzerinde Bootstrap popover araç ipucu.',
    'Bootstrap progress bar with label and colour variant.' => 'Etiket ve renk varyantlı Bootstrap ilerleme çubuğu.',
    'Bootstrap loading spinner.' => 'Bootstrap yükleme döndürücüsü.',
    'Bootstrap toast notification (always visible, no JS trigger required).' => 'Bootstrap bildirim mesajı (her zaman görünür, JS tetikleyici gerekmez).',
    'Bootstrap pagination demo block.' => 'Bootstrap sayfalama demo bloğu.',
    "Sticky top navigation bar — auto-populated from entries marked 'Show in nav'." => "'Navigasyonda göster' olarak işaretlenen girişlerden otomatik doldurulan yapışkan üst gezinti çubuğu.",
    'Vertical whitespace spacer.' => 'Dikey boşluk ayırıcı.',
    'Master configuration for the site: navbar block, footer block, colour mode (light/dark/auto), and colour theme.' => 'Site için ana yapılandırma: gezinti çubuğu bloğu, alt bilgi bloğu, renk modu (açık/koyu/otomatik) ve renk teması.',
    'Bootstrap colour mode — %light%, %dark%, or %auto% (follows OS preference).' => 'Bootstrap renk modu — %light%, %dark% veya %auto% (işletim sistemi tercihini takip eder).',
    'Colour theme — Default / Dark / Warm / Cool / Forest / Ocean / Custom. Sets %attr% on %tag%; all theming is handled by CSS.' => 'Renk teması — Varsayılan / Koyu / Sıcak / Serin / Orman / Okyanus / Özel. %tag% üzerinde %attr% ayarlar; tüm tema CSS tarafından yönetilir.',
    'A single wbNavbar block used site-wide.' => 'Site genelinde kullanılan tek bir wbNavbar bloğu.',
    'A single footer block used site-wide.' => 'Site genelinde kullanılan tek bir alt bilgi bloğu.',
    'Controls the cookie consent banner. Enable the banner, set the title, body text, and privacy URL. Four categories: %necessary% (always on), Analytics, Marketing, Preferences. Consent is stored in %storage% as %key%; the %obj% object is published for third-party scripts.' => 'Çerez onay banner\'ını kontrol eder. Banner\'ı etkinleştirin, başlığı, gövde metnini ve gizlilik URL\'sini ayarlayın. Dört kategori: %necessary% (her zaman açık), Analitik, Pazarlama, Tercihler. Onay %storage%\'da %key% olarak saklanır; %obj% nesnesi üçüncü taraf betikler için yayımlanır.',
    'Homepage — full wbBlocks stack.' => 'Ana sayfa — tam wbBlocks yığını.',
    'General-purpose pages with wbBlocks.' => 'wbBlocks ile genel amaçlı sayfalar.',
    'About / team pages.' => 'Hakkımızda / ekip sayfaları.',
    'Services / products pages.' => 'Hizmetler / ürünler sayfaları.',
    'Blog listing page with pagination.' => 'Sayfalama ile blog listeleme sayfası.',
    'Blog posts — featured image, categories, tags, comments.' => 'Blog yazıları — öne çıkan resim, kategoriler, etiketler, yorumlar.',
    'Blog comments (pending by default). Approve / Reject via CP element actions or the inline toggle on the edit page.' => 'Blog yorumları (varsayılan olarak beklemede). CP element aksiyonları veya düzenleme sayfasındaki satır içi geçiş ile Onayla / Reddet.',
    'Contact page with a wbForm block.' => 'wbForm bloğuyla iletişim sayfası.',
    'Stores every form submission. Manage status (Unread / Read / Archived) via CP element actions.' => 'Her form gönderimini saklar. CP element aksiyonları aracılığıyla durumu yönetin (Okunmamış / Okunmuş / Arşivlenmiş).',
    'Site search — uses craft.entries.search().' => 'Site araması — craft.entries.search() kullanır.',
    'Client logos / references for a references block.' => 'Referanslar bloğu için müşteri logoları / referanslar.',
    'Add a wbForm block to any page. Fields are built in the CP — text, email, phone, textarea, select, checkbox, radio.' => 'Herhangi bir sayfaya wbForm bloğu ekleyin. Alanlar CP\'de oluşturulur — metin, e-posta, telefon, metin alanı, seçim, onay kutusu, radyo.',
    'Each submission is stored in %path% with the submitted data, originating form reference, and visitor email.' => 'Her gönderim, gönderilen veriler, kaynak form referansı ve ziyaretçi e-postasıyla birlikte %path% bölümünde saklanır.',
    'Admin notification email is sent immediately (Craft mail queue). Only fields with %toggle% toggled on are included.' => 'Yönetici bildirim e-postası hemen gönderilir (Craft posta kuyruğu). Yalnızca %toggle% açık olan alanlar dahil edilir.',
    'Include in admin email' => 'Yönetici e-postasına dahil et',
    'Visitor confirmation email is optional — enable it per form and set a subject and body template. Use %placeholder% placeholders in the body.' => 'Ziyaretçi onay e-postası isteğe bağlıdır — form başına etkinleştirin ve bir konu ile gövde şablonu ayarlayın. Gövdede %placeholder% yer tutucularını kullanın.',
    'Honeypot spam protection is built in.' => 'Honeypot spam koruması yerleşik olarak mevcuttur.',
    'To enable Google reCAPTCHA, add your site and secret keys in %path%.' => 'Google reCAPTCHA\'yı etkinleştirmek için site ve gizli anahtarlarınızı %path% bölümüne ekleyin.',
    'Comments are submitted via the front end and stored in %path% with %status% (pending).' => 'Yorumlar ön uç aracılığıyla gönderilir ve %status% (beklemede) ile birlikte %path% bölümünde saklanır.',
    'Approve or reject in bulk from the index using the element action menu, or use the %toggle% lightswitch on the individual comment edit page.' => 'Element aksiyon menüsünü kullanarak dizinden toplu olarak onaylayın veya reddedin ya da bireysel yorum düzenleme sayfasındaki %toggle% ışık anahtarını kullanın.',
    'Only approved comments appear on the front end.' => 'Yalnızca onaylanan yorumlar ön uçta görünür.',
    'Every entry type that powers a page has %seoTitle% and %seoDesc% fields. Leave them blank to fall back to the entry title and site name. The %head% partial outputs the correct %title%, %desc%, and Open Graph tags automatically.' => 'Bir sayfayı destekleyen her giriş türünün %seoTitle% ve %seoDesc% alanları vardır. Giriş başlığı ve site adına geri dönmek için bunları boş bırakın. %head% kısmi doğru %title%, %desc% ve Open Graph etiketlerini otomatik olarak çıkarır.',
    'Select a theme in %path%. WebBlocks automatically loads the corresponding %bootswatch% CSS from CDN — every Bootstrap component (buttons, cards, navbar, badges, alerts, forms, typography) inherits the theme instantly. No rebuild required.' => '%path% bölümünde bir tema seçin. WebBlocks, ilgili %bootswatch% CSS\'ini CDN\'den otomatik olarak yükler — her Bootstrap bileşeni (düğmeler, kartlar, gezinti çubuğu, rozetler, uyarılar, formlar, tipografi) temayı anında devralır. Yeniden oluşturma gerekmez.',
    'Bootstrap built-in styles — no overrides applied.' => 'Bootstrap yerleşik stilleri — hiçbir geçersiz kılma uygulanmaz.',
    'Clean, flat design with a green primary accent. Light and professional.' => 'Yeşil birincil vurguyla temiz, düz tasarım. Hafif ve profesyonel.',
    'Cool sky-blue primary. Light, airy, corporate feel.' => 'Serin gök mavisi birincil renk. Hafif, ferah, kurumsal his.',
    'Fresh mint-green accents. Light and friendly.' => 'Taze nane yeşili vurgular. Hafif ve dost canlısı.',
    'Refined serif typography with a premium, editorial look.' => 'Premium, editoryal görünümlü rafine serif tipografi.',
    'Full dark background with light text. Modern dark-mode theme.' => 'Açık metinli tam koyu arka plan. Modern karanlık mod teması.',
    'Muted dark-grey palette. Sophisticated and low-contrast.' => 'Sönük koyu gri palet. Sofistike ve düşük kontrastlı.',
    'Vibrant gradients and glassmorphism. Bold and modern.' => 'Canlı degradeler ve glassmorphism. Cesur ve modern.',
    'To apply custom styles on top of any theme, use %cssUrl% (external stylesheet) or %css% (inline rules injected into %head%).' => 'Herhangi bir temanın üzerine özel stiller uygulamak için %cssUrl% (harici stil sayfası) veya %css% (%head% içine eklenen satır içi kurallar) kullanın.',
    "WebBlocks seeds content in English, Turkish, and German when those sites exist in Craft. Add more sites in %path% and translate entries normally via the site switcher on each entry's edit page." => "WebBlocks, bu siteler Craft'ta mevcut olduğunda İngilizce, Türkçe ve Almanca içerik tohumlar. %path% bölümüne daha fazla site ekleyin ve girişleri her giriş düzenleme sayfasındaki site değiştirici aracılığıyla normal şekilde çevirin.",
    'To reset all demo content and reinstall the full schema:' => 'Tüm demo içeriği sıfırlamak ve tam şemayı yeniden yüklemek için:',
    'This is destructive — all existing WebBlocks content will be permanently deleted.' => 'Bu işlem geri alınamaz — mevcut tüm WebBlocks içeriği kalıcı olarak silinecektir.',
    'CKEditor plugin (installed automatically as a dependency)' => 'CKEditor eklentisi (bağımlılık olarak otomatik yüklenir)',
    'Bootstrap 5.3 CDN (loaded from the default layout template — no npm required)' => 'Bootstrap 5.3 CDN (varsayılan düzen şablonundan yüklenir — npm gerekmez)',

    // =========================================================================
    // Comment moderation (CP)
    // =========================================================================
    'Approval Status'                                   => 'Onay Durumu',
    'Approved'                                          => 'Onaylandı',
    'Pending'                                           => 'Beklemede',
    'Are you sure you want to reject this comment?'     => 'Bu yorumu reddetmek istediğinizden emin misiniz?',
];
