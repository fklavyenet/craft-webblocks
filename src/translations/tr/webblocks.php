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

    // =========================================================================
    // Comment moderation (CP)
    // =========================================================================
    'Approval Status'                                   => 'Onay Durumu',
    'Approved'                                          => 'Onaylandı',
    'Pending'                                           => 'Beklemede',
    'Are you sure you want to reject this comment?'     => 'Bu yorumu reddetmek istediğinizden emin misiniz?',
];
