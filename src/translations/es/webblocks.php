<?php
/**
 * WebBlocks plugin for Craft CMS
 *
 * Spanish translations (webblocks category).
 * Used by wbTemplates sections, error pages, and form validation.
 */

return [
    // =========================================================================
    // Error pages
    // =========================================================================
    'Back to homepage'           => 'Volver a la página de inicio',
    'Bad Request'                => 'Solicitud incorrecta',
    'Internal Server Error'      => 'Error interno del servidor',
    'Page Not Found'             => 'Página no encontrada',
    'Service Unavailable'        => 'Servicio no disponible',
    'Unauthorized'               => 'No autorizado',
    'An error occurred while processing your request.' => 'Se produjo un error al procesar su solicitud.',
    'Our site is temporarily unavailable. Please try again later.' => 'Nuestro sitio no está disponible temporalmente. Por favor, inténtelo de nuevo más tarde.',
    'The request could not be understood by the server due to malformed syntax.' => 'El servidor no pudo entender la solicitud debido a una sintaxis incorrecta.',
    'The requested URL was not found on this server.' => 'La URL solicitada no se encontró en este servidor.',
    "You don't have the proper credentials to access this page." => 'No tiene las credenciales necesarias para acceder a esta página.',

    // =========================================================================
    // Blog / References listing pages
    // =========================================================================
    'Blog'               => 'Blog',
    'References'         => 'Referencias',
    'No posts yet.'      => 'Aún no hay entradas.',
    'No references yet.' => 'Aún no hay referencias.',
    'Blog post pages'    => 'Páginas de entradas del blog',
    'Reference pages'    => 'Páginas de referencias',
    'Previous'           => 'Anterior',
    'Next'               => 'Siguiente',

    // =========================================================================
    // Search
    // =========================================================================
    'Search'                               => 'Buscar',
    'Search: {query}'                      => 'Búsqueda: {query}',
    'Search all content…'                  => 'Buscar en todo el contenido…',
    'Enter a search term above to find content.' => 'Introduzca un término de búsqueda arriba para encontrar contenido.',
    'No results found for "{query}".'      => 'No se encontraron resultados para "{query}".',
    '1 result for "{query}"'               => '1 resultado para "{query}"',
    '{total} results for "{query}"'        => '{total} resultados para "{query}"',
    'Search result pages'                  => 'Páginas de resultados de búsqueda',

    // =========================================================================
    // CP navigation
    // =========================================================================
    'Help'                                              => 'Ayuda',
    'Component Health'                                  => 'Estado de componentes',

    // =========================================================================
    // Plugin settings (CP)
    // =========================================================================
    'Default Admin Email'    => 'Correo electrónico de administrador predeterminado',
    "Fallback recipient for wbForm admin notification emails when a form's own Recipient field is empty." => "Destinatario alternativo para los correos de notificación de administrador de wbForm cuando el campo Destinatario del formulario está vacío.",
    'Comment Notification Email' => 'Correo de notificación de comentarios',
    'When set, an email is sent to this address every time a new comment is submitted and is awaiting moderation.' => 'Cuando se configura, se envía un correo a esta dirección cada vez que se envía un nuevo comentario y está pendiente de moderación.',
    'SEO'                    => 'SEO',
    'Page Title Format'      => 'Formato del título de página',
    "Format for the <title> tag when wbSeoTitle is empty. Use {title} and {siteName} as placeholders. Example: {title} — {siteName}" => "Formato para la etiqueta <title> cuando wbSeoTitle está vacío. Use {title} y {siteName} como marcadores. Ejemplo: {title} — {siteName}",
    'Seed Content'           => 'Contenido inicial',
    'Seed Languages'         => 'Idiomas iniciales',
    'Languages to seed when running the webblocks/seed command. English is always seeded. Changes take effect on the next wipe + seed cycle.' => 'Idiomas a sembrar al ejecutar el comando webblocks/seed. El inglés siempre se siembra. Los cambios surten efecto en el siguiente ciclo de limpieza + siembra.',
    'Analytics'              => 'Analítica',
    'GA4 Measurement ID'     => 'ID de medición GA4',
    'Google Analytics 4 Measurement ID (e.g. G-XXXXXXXXXX). Leave blank to disable.' => 'ID de medición de Google Analytics 4 (p. ej. G-XXXXXXXXXX). Deje en blanco para desactivar.',
    'Matomo URL'             => 'URL de Matomo',
    'Your Matomo instance URL (e.g. https://analytics.example.com/). Both URL and Site ID are required.' => 'URL de su instancia de Matomo (p. ej. https://analytics.example.com/). Tanto la URL como el ID del sitio son obligatorios.',
    'Matomo Site ID'         => 'ID del sitio de Matomo',
    'Your Matomo Site ID (e.g. 1). Both URL and Site ID are required.' => 'ID de su sitio en Matomo (p. ej. 1). Tanto la URL como el ID del sitio son obligatorios.',

    // =========================================================================
    // Help page — headings & table headers
    // =========================================================================
    'Quick Start'                => 'Inicio rápido',
    'Content Blocks (%field% matrix)' => 'Bloques de contenido (matriz %field%)',
    'Block'                      => 'Bloque',
    'What it renders'            => 'Qué muestra',
    'Global Sets'                => 'Sets globales',
    'Field'                      => 'Campo',
    'Purpose'                    => 'Propósito',
    'Sections & Entry Types'     => 'Secciones y tipos de entrada',
    'Section'                    => 'Sección',
    'Type'                       => 'Tipo',
    'Forms & Submissions'        => 'Formularios y envíos',
    'Blog Comments'              => 'Comentarios del blog',
    'Colour Themes'              => 'Temas de color',
    'Theme'                      => 'Tema',
    'Character'                  => 'Carácter',
    'Multi-language'             => 'Multiidioma',
    'Wipe & Reseed'              => 'Limpiar y resembrar',
    'Requirements'               => 'Requisitos',
    'Necessary'                  => 'Necesario',

    // =========================================================================
    // Help page — body strings
    // =========================================================================
    'WebBlocks is a portable website-building toolkit for Craft CMS 5. Install it, run the seeder, and get a fully working multi-language business site — complete with pages, blog, forms, SEO, cookie consent, and a colour theme system — in minutes.' => 'WebBlocks es un kit de construcción de sitios web portátil para Craft CMS 5. Instálelo, ejecute el sembrador y obtenga un sitio web empresarial multiidioma completamente funcional —con páginas, blog, formularios, SEO, consentimiento de cookies y un sistema de temas de color— en minutos.',
    'Install the plugin via the Plugin Store or Composer.' => 'Instale el plugin a través del Plugin Store o Composer.',
    'Run the seed command from your project root:' => 'Ejecute el comando de siembra desde la raíz de su proyecto:',
    'Visit %globals% to set the site name, navbar, footer links, and colour theme.' => 'Visite %globals% para configurar el nombre del sitio, la barra de navegación, los enlaces del pie de página y el tema de color.',
    'Edit, duplicate, or delete the seeded entries in %entries% as needed.' => 'Edite, duplique o elimine las entradas sembradas en %entries% según sea necesario.',
    'Every page uses a %field% matrix field. Add, remove, and reorder blocks freely.' => 'Cada página usa un campo de matriz %field%. Añada, elimine y reordene bloques libremente.',
    'Full-width hero banner with optional overlay, headline, text, and CTA button.' => 'Banner hero a ancho completo con superposición opcional, titular, texto y botón CTA.',
    'Auto-playing fullscreen slider with dot pagination and caption alignment controls.' => 'Slider a pantalla completa con reproducción automática, paginación por puntos y controles de alineación de subtítulos.',
    'Bootstrap carousel with optional captions and frosted-glass overlay.' => 'Carrusel Bootstrap con subtítulos opcionales y superposición de vidrio esmerilado.',
    'Masonry photo gallery with a built-in lightbox.' => 'Galería de fotos en mosaico con lightbox integrado.',
    'Bootstrap card grid — title, image, text, optional link button.' => 'Cuadrícula de tarjetas Bootstrap — título, imagen, texto, botón de enlace opcional.',
    'Two-column image + text layout with swappable sides.' => 'Diseño de dos columnas imagen + texto con lados intercambiables.',
    '2/3/4-column layout; each column holds its own full %field% stack (recursive nesting).' => 'Diseño de 2/3/4 columnas; cada columna contiene su propia pila %field% completa (anidamiento recursivo).',
    'Rich-text prose with optional heading.' => 'Prosa de texto enriquecido con encabezado opcional.',
    'Standalone heading (h1–h6) with alignment and colour variant.' => 'Encabezado independiente (h1–h6) con alineación y variante de color.',
    'Centred CTA section with heading, text, and button.' => 'Sección CTA centrada con encabezado, texto y botón.',
    'Contact/enquiry form — stores submissions, sends admin email, optional visitor confirmation email.' => 'Formulario de contacto/consulta — almacena envíos, envía correo al administrador, correo de confirmación al visitante opcional.',
    'Bootstrap accordion / FAQ block.' => 'Bloque acordeón / FAQ de Bootstrap.',
    'Bootstrap tabbed content panels.' => 'Paneles de contenido con pestañas de Bootstrap.',
    'Data table with configurable column headers.' => 'Tabla de datos con encabezados de columna configurables.',
    'Bootstrap list group — plain, linked, or with badges.' => 'Grupo de lista Bootstrap — simple, enlazado o con insignias.',
    'Responsive YouTube / Vimeo embed.' => 'Incrustación responsiva de YouTube / Vimeo.',
    'Address, phone, email, and map embed.' => 'Dirección, teléfono, correo electrónico e incrustación de mapa.',
    'Bootstrap alert box — dismissible, with theme-aware default colour.' => 'Cuadro de alerta Bootstrap — descartable, con color predeterminado según el tema.',
    'Inline Bootstrap badge — pill option, theme-aware default colour.' => 'Insignia Bootstrap en línea — opción píldora, color predeterminado según el tema.',
    'Horizontal or vertical Bootstrap button group.' => 'Grupo de botones Bootstrap horizontal o vertical.',
    'Bootstrap modal dialog triggered by a button.' => 'Diálogo modal Bootstrap activado por un botón.',
    'Bootstrap offcanvas panel (drawer) with nested blocks.' => 'Panel offcanvas Bootstrap (cajón) con bloques anidados.',
    'Bootstrap popover tooltip on a button.' => 'Información emergente Bootstrap en un botón.',
    'Bootstrap progress bar with label and colour variant.' => 'Barra de progreso Bootstrap con etiqueta y variante de color.',
    'Bootstrap loading spinner.' => 'Indicador de carga Bootstrap.',
    'Bootstrap toast notification (always visible, no JS trigger required).' => 'Notificación toast Bootstrap (siempre visible, no requiere activación JS).',
    'Bootstrap pagination demo block.' => 'Bloque de demostración de paginación Bootstrap.',
    "Sticky top navigation bar — auto-populated from entries marked 'Show in nav'." => "Barra de navegación superior fija — poblada automáticamente desde entradas marcadas como 'Mostrar en nav'.",
    'Vertical whitespace spacer.' => 'Espaciador de espacio en blanco vertical.',
    'Master configuration for the site: navbar block, footer block, colour mode (light/dark/auto), and colour theme.' => 'Configuración principal del sitio: bloque de barra de navegación, bloque de pie de página, modo de color (claro/oscuro/automático) y tema de color.',
    'Bootstrap colour mode — %light%, %dark%, or %auto% (follows OS preference).' => 'Modo de color Bootstrap — %light%, %dark% o %auto% (sigue la preferencia del sistema operativo).',
    'Colour theme — Default / Dark / Warm / Cool / Forest / Ocean / Custom. Sets %attr% on %tag%; all theming is handled by CSS.' => 'Tema de color — Predeterminado / Oscuro / Cálido / Fresco / Bosque / Océano / Personalizado. Establece %attr% en %tag%; todo el tema es gestionado por CSS.',
    'A single wbNavbar block used site-wide.' => 'Un único bloque wbNavbar utilizado en todo el sitio.',
    'A single footer block used site-wide.' => 'Un único bloque de pie de página utilizado en todo el sitio.',
    'Controls the cookie consent banner. Enable the banner, set the title, body text, and privacy URL. Four categories: %necessary% (always on), Analytics, Marketing, Preferences. Consent is stored in %storage% as %key%; the %obj% object is published for third-party scripts.' => 'Controla el banner de consentimiento de cookies. Active el banner, configure el título, el texto del cuerpo y la URL de privacidad. Cuatro categorías: %necessary% (siempre activo), Analítica, Marketing, Preferencias. El consentimiento se almacena en %storage% como %key%; el objeto %obj% se publica para scripts de terceros.',
    'Homepage — full wbBlocks stack.' => 'Página de inicio — pila wbBlocks completa.',
    'General-purpose pages with wbBlocks.' => 'Páginas de uso general con wbBlocks.',
    'About / team pages.' => 'Páginas sobre nosotros / equipo.',
    'Services / products pages.' => 'Páginas de servicios / productos.',
    'Blog listing page with pagination.' => 'Página de listado del blog con paginación.',
    'Blog posts — featured image, categories, tags, comments.' => 'Entradas del blog — imagen destacada, categorías, etiquetas, comentarios.',
    'Blog comments (pending by default). Approve / Reject via CP element actions or the inline toggle on the edit page.' => 'Comentarios del blog (pendientes por defecto). Aprobar / Rechazar mediante acciones de elemento del CP o el interruptor en línea en la página de edición.',
    'Contact page with a wbForm block.' => 'Página de contacto con un bloque wbForm.',
    'Stores every form submission. Manage status (Unread / Read / Archived) via CP element actions.' => 'Almacena cada envío de formulario. Gestione el estado (No leído / Leído / Archivado) mediante acciones de elemento del CP.',
    'Site search — uses craft.entries.search().' => 'Búsqueda del sitio — usa craft.entries.search().',
    'Client logos / references for a references block.' => 'Logotipos de clientes / referencias para un bloque de referencias.',
    'Add a wbForm block to any page. Fields are built in the CP — text, email, phone, textarea, select, checkbox, radio.' => 'Añada un bloque wbForm a cualquier página. Los campos se crean en el CP — texto, correo electrónico, teléfono, área de texto, selección, casilla de verificación, radio.',
    'Each submission is stored in %path% with the submitted data, originating form reference, and visitor email.' => 'Cada envío se almacena en %path% con los datos enviados, la referencia del formulario de origen y el correo del visitante.',
    'Admin notification email is sent immediately (Craft mail queue). Only fields with %toggle% toggled on are included.' => 'El correo de notificación al administrador se envía inmediatamente (cola de correo de Craft). Solo se incluyen los campos con %toggle% activado.',
    'Include in admin email' => 'Incluir en correo de administrador',
    'Visitor confirmation email is optional — enable it per form and set a subject and body template. Use %placeholder% placeholders in the body.' => 'El correo de confirmación al visitante es opcional — actívelo por formulario y configure un asunto y una plantilla de cuerpo. Use marcadores %placeholder% en el cuerpo.',
    'Honeypot spam protection is built in.' => 'La protección antispam honeypot está integrada.',
    'To enable Google reCAPTCHA, add your site and secret keys in %path%.' => 'Para activar Google reCAPTCHA, añada las claves de su sitio y secretas en %path%.',
    'Comments are submitted via the front end and stored in %path% with %status% (pending).' => 'Los comentarios se envían desde el front-end y se almacenan en %path% con %status% (pendiente).',
    'Approve or reject in bulk from the index using the element action menu, or use the %toggle% lightswitch on the individual comment edit page.' => 'Apruebe o rechace en lote desde el índice usando el menú de acciones de elemento, o use el interruptor %toggle% en la página de edición del comentario individual.',
    'Only approved comments appear on the front end.' => 'Solo los comentarios aprobados aparecen en el front-end.',
    'Every entry type that powers a page has %seoTitle% and %seoDesc% fields. Leave them blank to fall back to the entry title and site name. The %head% partial outputs the correct %title%, %desc%, and Open Graph tags automatically.' => 'Cada tipo de entrada que impulsa una página tiene campos %seoTitle% y %seoDesc%. Déjelos en blanco para recurrir al título de la entrada y al nombre del sitio. El parcial %head% genera automáticamente las etiquetas %title%, %desc% y Open Graph correctas.',
    'Select a theme in %path%. WebBlocks automatically loads the corresponding %bootswatch% CSS from CDN — every Bootstrap component (buttons, cards, navbar, badges, alerts, forms, typography) inherits the theme instantly. No rebuild required.' => 'Seleccione un tema en %path%. WebBlocks carga automáticamente el CSS de %bootswatch% correspondiente desde CDN — cada componente Bootstrap (botones, tarjetas, barra de navegación, insignias, alertas, formularios, tipografía) hereda el tema al instante. No se requiere reconstrucción.',
    'Bootstrap built-in styles — no overrides applied.' => 'Estilos integrados de Bootstrap — sin personalizaciones aplicadas.',
    'Clean, flat design with a green primary accent. Light and professional.' => 'Diseño limpio y plano con acento primario verde. Ligero y profesional.',
    'Cool sky-blue primary. Light, airy, corporate feel.' => 'Azul cielo fresco como color primario. Sensación ligera, airosa y corporativa.',
    'Fresh mint-green accents. Light and friendly.' => 'Acentos verde menta frescos. Ligero y amigable.',
    'Refined serif typography with a premium, editorial look.' => 'Tipografía serif refinada con un aspecto editorial y premium.',
    'Full dark background with light text. Modern dark-mode theme.' => 'Fondo completamente oscuro con texto claro. Tema moderno de modo oscuro.',
    'Muted dark-grey palette. Sophisticated and low-contrast.' => 'Paleta gris oscuro apagada. Sofisticada y de bajo contraste.',
    'Vibrant gradients and glassmorphism. Bold and modern.' => 'Degradados vibrantes y glassmorphism. Audaz y moderno.',
    'To apply custom styles on top of any theme, use %cssUrl% (external stylesheet) or %css% (inline rules injected into %head%).' => 'Para aplicar estilos personalizados sobre cualquier tema, use %cssUrl% (hoja de estilos externa) o %css% (reglas en línea inyectadas en %head%).',
    "WebBlocks seeds content in English, Turkish, and German when those sites exist in Craft. Add more sites in %path% and translate entries normally via the site switcher on each entry's edit page." => "WebBlocks siembra contenido en inglés, turco y alemán cuando esos sitios existen en Craft. Añada más sitios en %path% y traduzca las entradas normalmente mediante el selector de sitio en la página de edición de cada entrada.",
    'To reset all demo content and reinstall the full schema:' => 'Para restablecer todo el contenido de demostración y reinstalar el esquema completo:',
    'This is destructive — all existing WebBlocks content will be permanently deleted.' => 'Esta acción es destructiva — todo el contenido existente de WebBlocks se eliminará permanentemente.',
    'CKEditor plugin (installed automatically as a dependency)' => 'Plugin CKEditor (se instala automáticamente como dependencia)',
    'Bootstrap 5.3 CDN (loaded from the default layout template — no npm required)' => 'Bootstrap 5.3 CDN (cargado desde la plantilla de diseño predeterminada — no se requiere npm)',

    // =========================================================================
    // Comment moderation (CP)
    // =========================================================================
    'Approval Status'                                   => 'Estado de aprobación',
    'Approved'                                          => 'Aprobado',
    'Pending'                                           => 'Pendiente',
    'Are you sure you want to reject this comment?'     => '¿Está seguro de que desea rechazar este comentario?',
];
