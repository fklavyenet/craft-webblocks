<?php

namespace fklavyenet\webblocks\console;

use Craft;
use craft\console\Controller;
use craft\elements\Asset;
use craft\elements\Entry;
use craft\helpers\Assets as AssetsHelper;
use yii\console\ExitCode;

/**
 * Seeds the database with a test wbPage entry containing all wb* components.
 * Usage: ./craft webblocks/seed
 */
class SeedController extends Controller
{
    /**
     * Language sites to propagate content into (in addition to the primary EN site).
     * Each key is the Craft site handle; value is the suffix used in translation JSON filenames.
     */
    private const EXTRA_SITES = [
        'tr' => 'tr',
        'de' => 'de',
    ];
    /**
     * Seeds a test wbPage with all wb* block types and sample gallery images.
     */
    public function actionIndex(): int
    {
        $this->stdout("=== WebBlocks Seed Command ===\n\n");

        $seedPath = $this->getSeedPath();

        // 1. Load page config
        $pageConfig = $this->loadJson($seedPath . '/page.json');
        if (!$pageConfig) {
            $this->stderr("Error: Could not load page.json\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        // 2. Check if test page already exists
        $existingEntry = Entry::find()
            ->section($pageConfig['section'])
            ->slug($pageConfig['slug'])
            ->status(null)
            ->one();

        if ($existingEntry) {
            $this->stdout("Test page '{$pageConfig['slug']}' already exists (ID: {$existingEntry->id}). Deleting...\n");
            Craft::$app->getElements()->deleteElement($existingEntry, true);
        }

        // 3. Download and index sample images
        $this->stdout("Downloading sample images...\n");
        $assetIds = $this->seedImages($seedPath);
        $this->stdout("  Indexed " . count($assetIds) . " images\n\n");

        // 4. Build wbBlocks matrix data
        $this->stdout("Building blocks...\n");
        $blocksData = $this->buildBlocksData($seedPath, $pageConfig['blocks'], $assetIds);

        // 5. Resolve section + entry type
        $section = Craft::$app->getEntries()->getSectionByHandle($pageConfig['section']);
        if (!$section) {
            $this->stderr("Error: Section '{$pageConfig['section']}' not found. Is the plugin installed?\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $entryType = null;
        foreach ($section->getEntryTypes() as $et) {
            if ($et->handle === $pageConfig['entryType']) {
                $entryType = $et;
                break;
            }
        }
        if (!$entryType) {
            $this->stderr("Error: Entry type '{$pageConfig['entryType']}' not found in section '{$pageConfig['section']}'.\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        // 6. Create the page entry
        $this->stdout("Creating test page...\n");

        $entry = new Entry();
        $entry->sectionId = $section->id;
        $entry->typeId = $entryType->id;
        $entry->title = $pageConfig['title'];
        $entry->slug = $pageConfig['slug'];
        $entry->enabled = true;

        // Set wbBlocks
        $entry->setFieldValue('wbBlocks', $blocksData);

        if (!Craft::$app->getElements()->saveElement($entry)) {
            $this->stderr("Error saving entry:\n");
            foreach ($entry->getErrors() as $attr => $errors) {
                foreach ($errors as $error) {
                    $this->stderr("  [$attr] $error\n");
                }
            }

            // Check nested block errors
            $wbBlocks = $entry->getFieldValue('wbBlocks');
            if ($wbBlocks) {
                foreach ($wbBlocks->all() as $block) {
                    if ($block->hasErrors()) {
                        $this->stderr("  Block '{$block->getType()->handle}' errors:\n");
                        foreach ($block->getErrors() as $attr => $errors) {
                            foreach ($errors as $error) {
                                $this->stderr("    [$attr] $error\n");
                            }
                        }
                    }
                }
            }

            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout("\n=== Test page seeded! ===\n");
        $this->stdout("Entry ID: {$entry->id}\n");
        $this->stdout("URL: {$entry->getUrl()}\n");
        $this->stdout("CP: /admin/entries/{$pageConfig['section']}/{$entry->id}\n\n");

        // 7. Seed the wbSiteConfig global set (navbar + footer)
        $this->stdout("Seeding wbSiteConfig global set...\n");
        $this->seedGlobalSet($seedPath, $assetIds);

        // 7b. Seed the wbCookieSettings global set
        $this->stdout("\nSeeding wbCookieSettings global set...\n");
        $this->seedCookieSettings();

        // 8. Seed blog posts
        $this->stdout("\nSeeding blog posts...\n");
        $this->seedBlogPosts($seedPath, $assetIds);

        // 8b. Seed blog comments
        $this->stdout("\nSeeding blog comments...\n");
        $this->seedBlogComments();

        // 9. Seed about, services, contact pages
        $this->stdout("\nSeeding static pages...\n");
        $this->seedStaticPages($seedPath, $assetIds);

        // 10. Set wbShowInNav / wbShowInFooter on single-section pages (Home, Blog index)
        $this->stdout("\nSetting nav/footer flags on single sections...\n");
        $this->seedSinglePageNavFlags();

        $this->stdout("\n=== Seed complete! ===\n");

        return ExitCode::OK;
    }

    /**
     * Seeds the wbSiteConfig global set with a navbar and footer entry.
     */
    private function seedGlobalSet(string $seedPath, array $assetIds): void
    {
        $primarySiteId = Craft::$app->getSites()->getPrimarySite()->id;
        $globalSet = Craft::$app->getGlobals()->getSetByHandle('wbSiteConfig', $primarySiteId);
        if (!$globalSet) {
            $this->stderr("  Warning: Global set 'wbSiteConfig' not found. Skipping.\n");
            return;
        }

        // --- Color mode ---
        $globalSet->setFieldValue('wbColorMode', 'auto');

        // --- Navbar ---
        $navbarData = $this->loadJson($seedPath . '/components/wbNavbar.json');
        if ($navbarData) {
            $this->stdout("  - Building wbSiteNavbar...\n");
            $counter = 1;
            $navbarBlock = $this->buildNavbarBlock($navbarData, $counter);
            $globalSet->setFieldValue('wbSiteNavbar', ['new:1' => $navbarBlock]);
        }

        // --- Footer ---
        $footerData = $this->loadJson($seedPath . '/components/wbFooter.json');
        if ($footerData) {
            $this->stdout("  - Building wbSiteFooter...\n");
            $counter = 1;
            $footerBlock = $this->buildFooterBlock($footerData, $counter);
            $globalSet->setFieldValue('wbSiteFooter', ['new:1' => $footerBlock]);
        }

        if (!Craft::$app->getElements()->saveElement($globalSet)) {
            $this->stderr("  Error saving wbSiteConfig global set:\n");
            foreach ($globalSet->getErrors() as $attr => $errors) {
                foreach ($errors as $error) {
                    $this->stderr("    [$attr] $error\n");
                }
            }
        } else {
            $this->stdout("  wbSiteConfig saved (ID: {$globalSet->id})\n");
        }
    }

    /**
     * Seeds the wbCookieSettings global set with default EN banner content.
     */
    private function seedCookieSettings(): void
    {
        $primarySiteId = Craft::$app->getSites()->getPrimarySite()->id;
        $globalSet = Craft::$app->getGlobals()->getSetByHandle('wbCookieSettings', $primarySiteId);
        if (!$globalSet) {
            $this->stderr("  Warning: Global set 'wbCookieSettings' not found. Skipping.\n");
            return;
        }

        $primarySiteBaseUrl = rtrim(Craft::$app->getSites()->getPrimarySite()->getBaseUrl(), '/');

        // Build per-site base URLs — extra sites may have relative baseUrls (e.g. /tr/)
        $siteBaseUrl = function(string $handle) use ($primarySiteBaseUrl): string {
            $site = Craft::$app->getSites()->getSiteByHandle($handle);
            if (!$site) {
                return $primarySiteBaseUrl;
            }
            $url = $site->getBaseUrl();
            // If relative, prefix with primary site origin
            if (str_starts_with($url, '/')) {
                preg_match('#^(https?://[^/]+)#', $primarySiteBaseUrl, $m);
                $origin = $m[1] ?? $primarySiteBaseUrl;
                $url = $origin . '/' . ltrim($url, '/');
            }
            return rtrim($url, '/');
        };

        $globalSet->setFieldValue('wbCookieBannerEnabled', true);
        $globalSet->setFieldValue('wbCookieBannerTitle', 'We use cookies');
        $globalSet->setFieldValue('wbCookieBannerText', 'We use cookies to improve your experience, analyse traffic and enable personalised content. You can choose which categories to allow.');
        $globalSet->setFieldValue('wbCookiePrivacyUrl', $primarySiteBaseUrl . '/legal');
        $globalSet->setFieldValue('wbCookieLabelAnalytics', 'Analytics');
        $globalSet->setFieldValue('wbCookieLabelMarketing', 'Marketing');
        $globalSet->setFieldValue('wbCookieLabelPreferences', 'Preferences');

        if (!Craft::$app->getElements()->saveElement($globalSet)) {
            $this->stderr("  Error saving wbCookieSettings global set:\n");
            foreach ($globalSet->getErrors() as $attr => $errors) {
                foreach ($errors as $error) {
                    $this->stderr("    [$attr] $error\n");
                }
            }
        } else {
            $this->stdout("  wbCookieSettings saved (ID: {$globalSet->id})\n");
        }

        // Propagate translated content to extra sites
        $translations = [
            'tr' => [
                'wbCookieBannerTitle'       => 'Çerezleri kullanıyoruz',
                'wbCookieBannerText'        => 'Deneyiminizi geliştirmek, trafiği analiz etmek ve kişiselleştirilmiş içerik sunmak için çerezler kullanıyoruz. Hangi kategorilere izin vereceğinizi seçebilirsiniz.',
                'wbCookiePrivacyUrl'        => $siteBaseUrl('tr') . '/legal',
                'wbCookieLabelAnalytics'    => 'Analitik',
                'wbCookieLabelMarketing'    => 'Pazarlama',
                'wbCookieLabelPreferences'  => 'Tercihler',
            ],
            'de' => [
                'wbCookieBannerTitle'       => 'Wir verwenden Cookies',
                'wbCookieBannerText'        => 'Wir verwenden Cookies, um Ihre Erfahrung zu verbessern, den Datenverkehr zu analysieren und personalisierte Inhalte bereitzustellen. Sie können wählen, welche Kategorien Sie zulassen möchten.',
                'wbCookiePrivacyUrl'        => $siteBaseUrl('de') . '/legal',
                'wbCookieLabelAnalytics'    => 'Analyse',
                'wbCookieLabelMarketing'    => 'Marketing',
                'wbCookieLabelPreferences'  => 'Einstellungen',
            ],
        ];

        foreach ($translations as $siteHandle => $fields) {
            $site = Craft::$app->getSites()->getSiteByHandle($siteHandle);
            if (!$site) {
                continue;
            }
            $localSet = Craft::$app->getGlobals()->getSetByHandle('wbCookieSettings', $site->id);
            if (!$localSet) {
                continue;
            }
            foreach ($fields as $fieldHandle => $value) {
                $localSet->setFieldValue($fieldHandle, $value);
            }
            if (!Craft::$app->getElements()->saveElement($localSet)) {
                $this->stderr("  Error saving wbCookieSettings for site '$siteHandle'.\n");
                foreach ($localSet->getErrors() as $attr => $errors) {
                    foreach ($errors as $error) {
                        $this->stderr("    [$attr] $error\n");
                    }
                }
            } else {
                $this->stdout("  wbCookieSettings propagated to '$siteHandle'\n");
            }
        }
    }

    /**
     * Seeds 5 blog posts in the wbBlog section.
     */
    private function seedBlogPosts(string $seedPath, array $assetIds): void
    {
        $postsConfig = $this->loadJson($seedPath . '/blogs.json');
        if (!$postsConfig) {
            $this->stderr("  Warning: blogs.json not found. Skipping blog posts.\n");
            return;
        }

        $section = Craft::$app->getEntries()->getSectionByHandle('wbBlog');
        if (!$section) {
            $this->stderr("  Warning: Section 'wbBlog' not found. Skipping blog posts.\n");
            return;
        }

        $entryType = null;
        foreach ($section->getEntryTypes() as $et) {
            if ($et->handle === 'wbPost') {
                $entryType = $et;
                break;
            }
        }
        if (!$entryType) {
            $this->stderr("  Warning: Entry type 'wbPost' not found. Skipping blog posts.\n");
            return;
        }

        // Load translation configs for extra sites
        $extraSiteConfigs = $this->getExtraSiteConfigs('blogs');

        foreach ($postsConfig as $postDef) {
            $slug = $postDef['slug'];

            // Skip if already exists
            $existing = Entry::find()->section('wbBlog')->slug($slug)->status(null)->one();
            if ($existing) {
                $this->stdout("  - '{$postDef['title']}' already exists (ID: {$existing->id}), skipping.\n");
                continue;
            }

            $entry = new Entry();
            $entry->sectionId = $section->id;
            $entry->typeId = $entryType->id;
            $entry->title = $postDef['title'];
            $entry->slug = $slug;
            $entry->enabled = true;

            // Featured image
            $imageIndex = $postDef['imageIndex'] ?? 0;
            if (!empty($assetIds[$imageIndex])) {
                $entry->setFieldValue('wbFeaturedImage', [$assetIds[$imageIndex]]);
            }

            // Excerpt
            if (!empty($postDef['excerpt'])) {
                $entry->setFieldValue('wbExcerpt', $postDef['excerpt']);
            }

            // SEO
            if (!empty($postDef['wbSeoTitle'])) {
                $entry->setFieldValue('wbSeoTitle', $postDef['wbSeoTitle']);
            }
            if (!empty($postDef['wbSeoDescription'])) {
                $entry->setFieldValue('wbSeoDescription', $postDef['wbSeoDescription']);
            }

            // wbBlocks
            if (!empty($postDef['blocks'])) {
                $blocksData = $this->buildInlineBlocksData($postDef['blocks'], $assetIds);
                $entry->setFieldValue('wbBlocks', $blocksData);
            }

            if (Craft::$app->getElements()->saveElement($entry)) {
                $this->stdout("  - '{$entry->title}' saved (ID: {$entry->id})\n");

                // Propagate to extra sites
                foreach ($extraSiteConfigs as $siteHandle => $siteConfig) {
                    $translatedDef = $this->findTranslatedDef($siteConfig['defs'], $slug);
                    if ($translatedDef) {
                        $this->propagateEntryToSite($entry, $siteConfig['siteId'], $translatedDef, $assetIds);
                        $this->stdout("    - Propagated to '$siteHandle'\n");
                    }
                }
            } else {
                $this->stderr("  ! Failed to save '{$postDef['title']}':\n");
                foreach ($entry->getErrors() as $attr => $errors) {
                    foreach ($errors as $error) {
                        $this->stderr("    [$attr] $error\n");
                    }
                }
            }
        }
    }

    /**
     * Seeds sample comments (disabled/pending) for the first two blog posts.
     */
    private function seedBlogComments(): void
    {
        $section = Craft::$app->getEntries()->getSectionByHandle('wbComments');
        if (!$section) {
            $this->stderr("  Warning: Section 'wbComments' not found. Skipping comments.\n");
            return;
        }

        $entryType = null;
        foreach ($section->getEntryTypes() as $et) {
            if ($et->handle === 'wbComment') {
                $entryType = $et;
                break;
            }
        }
        if (!$entryType) {
            $this->stderr("  Warning: Entry type 'wbComment' not found. Skipping comments.\n");
            return;
        }

        // Get the first two published blog posts to attach comments to
        $posts = Entry::find()->section('wbBlog')->status('live')->limit(2)->orderBy('postDate asc')->all();
        if (empty($posts)) {
            $this->stderr("  Warning: No live blog posts found. Skipping comments.\n");
            return;
        }

        $siteId = Craft::$app->getSites()->getPrimarySite()->id;

        $samples = [
            [
                'authorName' => 'Emma Johnson',
                'email'      => 'emma.j@example.com',
                'body'       => 'Great article! Really enjoyed reading this. The tips were very practical and easy to follow.',
            ],
            [
                'authorName' => 'Marcus Bauer',
                'email'      => 'marcus.b@example.com',
                'body'       => 'Thanks for sharing this. I had a few questions about the third point — could you elaborate a bit more?',
            ],
            [
                'authorName' => 'Sophie Martin',
                'email'      => 'sophie.m@example.com',
                'body'       => 'Loved the style of this post. Looking forward to more content like this!',
            ],
        ];

        $count = 0;

        foreach ($posts as $postIndex => $post) {
            // Attach one or two sample comments per post
            $commentsForPost = $postIndex === 0 ? [$samples[0], $samples[1]] : [$samples[2]];

            foreach ($commentsForPost as $sample) {
                // Skip if a comment with the same title already exists
                $titleCheck = $sample['authorName'] . ' — ' . date('Y-m-d');
                $existing = Entry::find()
                    ->section('wbComments')
                    ->title($titleCheck)
                    ->status(null)
                    ->one();

                if ($existing) {
                    $this->stdout("  - Comment by '{$sample['authorName']}' for '{$post->title}' already exists, skipping.\n");
                    continue;
                }

                $comment = new Entry();
                $comment->sectionId = $section->id;
                $comment->typeId    = $entryType->id;
                $comment->siteId    = $siteId;
                $comment->title     = $sample['authorName'] . ' — ' . date('Y-m-d');
                $comment->enabled   = false; // pending moderation

                $comment->setFieldValue('wbCommentAuthorName', $sample['authorName']);
                $comment->setFieldValue('wbEmail', $sample['email']);
                $comment->setFieldValue('wbCommentBody', $sample['body']);
                $comment->setFieldValue('wbCommentPost', [$post->id]);

                if (Craft::$app->getElements()->saveElement($comment)) {
                    $this->stdout("  - Comment by '{$sample['authorName']}' for '{$post->title}' saved (ID: {$comment->id}, pending)\n");
                    $count++;
                } else {
                    $this->stderr("  ! Failed to save comment:\n");
                    foreach ($comment->getErrors() as $attr => $errors) {
                        foreach ($errors as $error) {
                            $this->stderr("    [$attr] $error\n");
                        }
                    }
                }
            }
        }

        $this->stdout("  $count comment(s) seeded (all pending moderation).\n");
    }

    /**
     * Builds wbBlocks data from an inline array of block definitions.
     * Each block has 'type', 'fields', and optionally 'title' (for accordion) and 'items'.
     */
    private function buildInlineBlocksData(array $blockDefs, array $assetIds = []): array
    {
        $blocks = [];
        $counter = 1;

        foreach ($blockDefs as $blockDef) {
            $type = $blockDef['type'];

            if ($type === 'wbAccordion') {
                $items = [];
                $itemCounter = 1;
                foreach ($blockDef['items'] ?? [] as $item) {
                    $items["new:$itemCounter"] = [
                        'type' => 'wbAccordionItem',
                        'fields' => [
                            'wbTitle' => $item['wbTitle'] ?? '',
                            'wbText' => $item['wbText'] ?? '',
                        ],
                    ];
                    $itemCounter++;
                }
                $block = [
                    'type' => 'wbAccordion',
                    'fields' => ['wbAccordionItems' => $items],
                ];
                if (!empty($blockDef['title'])) {
                    $block['title'] = $blockDef['title'];
                }
            } elseif ($type === 'wbCarousel') {
                $block = $this->buildCarouselBlock($blockDef, $assetIds, $counter);
            } elseif ($type === 'wbGallery') {
                $block = $this->buildGalleryBlock($blockDef, $assetIds, $counter);
            } elseif ($type === 'wbColumns') {
                $block = $this->buildColumnsBlock($blockDef, $assetIds, $counter);
            } elseif ($type === 'wbTabs') {
                $block = $this->buildTabsBlock($blockDef, $counter);
            } elseif ($type === 'wbProgressBar') {
                $block = $this->buildProgressBarBlock($blockDef, $counter);
            } elseif ($type === 'wbListGroup') {
                $block = $this->buildListGroupBlock($blockDef, $counter);
            } elseif ($type === 'wbNavbar') {
                $block = $this->buildNavbarBlock($blockDef, $counter);
            } elseif ($type === 'wbFullscreenImage') {
                $block = $this->buildFullscreenImageBlock($blockDef, $assetIds, $counter);
            } elseif ($type === 'wbLeftRight') {
                $block = $this->buildInlineLeftRightBlock($blockDef, $assetIds);
            } elseif ($type === 'wbForm') {
                $block = $this->buildInlineFormBlock($blockDef);
            } else {
                $fields = $blockDef['fields'] ?? [];
                // Cast lightswitch fields to bool
                foreach (['wbAlertDismissible', 'wbNavbarSearch', 'wbBorder'] as $boolField) {
                    if (isset($fields[$boolField])) {
                        $fields[$boolField] = (bool)$fields[$boolField];
                    }
                }
                // Resolve imageIndex → wbBackgroundImage (hero) or wbImage (others)
                if (isset($fields['imageIndex']) && !empty($assetIds[$fields['imageIndex']])) {
                    $assetId = $assetIds[$fields['imageIndex']];
                    if ($type === 'wbHero') {
                        $fields['wbBackgroundImage'] = [$assetId];
                    } else {
                        $fields['wbImage'] = [$assetId];
                    }
                    unset($fields['imageIndex']);
                }
                $block = ['type' => $type, 'fields' => $fields];
            }

            $blocks["new:$counter"] = $block;
            $counter++;
        }

        return $blocks;
    }

    /**
     * Builds a wbLeftRight block with nested wbLeftRightItems.
     */
    private function buildInlineLeftRightBlock(array $blockDef, array $assetIds): array
    {
        $items = [];
        $itemCounter = 1;

        foreach ($blockDef['items'] ?? [] as $item) {
            $fields = [
                'wbTitle' => $item['wbTitle'] ?? '',
                'wbText'  => $item['wbText'] ?? '',
            ];

            if (!empty($item['wbButtonLabel'])) {
                $fields['wbButtonLabel'] = $item['wbButtonLabel'];
            }
            if (!empty($item['wbButtonUrl'])) {
                $fields['wbButtonUrl'] = $item['wbButtonUrl'];
            }

            $imageIndex = $item['imageIndex'] ?? null;
            if ($imageIndex !== null && !empty($assetIds[$imageIndex])) {
                $fields['wbImage'] = [$assetIds[$imageIndex]];
            }

            $items["new:$itemCounter"] = [
                'type' => 'wbLeftRightItem',
                'fields' => $fields,
            ];
            $itemCounter++;
        }

        return [
            'type' => 'wbLeftRight',
            'fields' => ['wbLeftRightItems' => $items],
        ];
    }

    /**
     * Builds a wbForm block with nested wbFormFields (and nested wbFormOptions).
     */
    private function buildInlineFormBlock(array $blockDef): array
    {
        $formFields = [];
        $fieldCounter = 1;

        foreach ($blockDef['fields'] ?? [] as $fieldDef) {
            $fieldType = $fieldDef['type'] ?? 'text';
            $required  = $fieldDef['required'] ?? false;

            $fieldFields = [
                'wbFormFieldLabel'       => $fieldDef['label'] ?? '',
                'wbFormFieldType'        => $fieldType,
                'wbFormFieldRequired'    => (bool) $required,
                'wbFormFieldPlaceholder' => $fieldDef['placeholder'] ?? '',
            ];

            // Build nested options for select / radio fields
            if (in_array($fieldType, ['select', 'radio'], true) && !empty($fieldDef['options'])) {
                $options = [];
                $optCounter = 1;
                foreach ($fieldDef['options'] as $opt) {
                    $options["new:$optCounter"] = [
                        'type' => 'wbFormOption',
                        'fields' => [
                            'wbFormOptionLabel' => $opt['label'] ?? '',
                            'wbFormOptionValue' => $opt['value'] ?? '',
                        ],
                    ];
                    $optCounter++;
                }
                $fieldFields['wbFormOptions'] = $options;
            }

            $formFields["new:$fieldCounter"] = [
                'type' => 'wbFormField',
                'fields' => $fieldFields,
            ];
            $fieldCounter++;
        }

        return [
            'type' => 'wbForm',
            'fields' => [
                'wbTitle'            => $blockDef['wbTitle'] ?? '',
                'wbFormRecipient'    => $blockDef['recipient'] ?? '',
                'wbFormSubject'      => $blockDef['subject'] ?? '',
                'wbFormSubmitLabel'  => $blockDef['submitLabel'] ?? 'Send',
                'wbFormSuccessMsg'   => $blockDef['successMsg'] ?? '',
                'wbBorder'           => (bool) ($blockDef['wbBorder'] ?? false),
                'wbBorderColor'      => $blockDef['wbBorderColor'] ?? '',
                'wbRounded'          => $blockDef['wbRounded'] ?? '',
                'wbPadding'          => $blockDef['wbPadding'] ?? '',
                'wbFormFields'       => $formFields,
            ],
        ];
    }

    /**
     * Builds a footer block with nested wbFooterItems (wbFooterLinks matrix).
     */
    private function buildFooterBlock(array $componentData, int &$counter): array
    {
        $items = [];
        $itemCounter = 1;

        foreach ($componentData['items'] ?? [] as $item) {
            $items["new:$itemCounter"] = [
                'type' => 'wbFooterItem',
                'fields' => [
                    'wbFooterLinkLabel' => $item['wbFooterLinkLabel'] ?? '',
                    'wbFooterLinkUrl' => $item['wbFooterLinkUrl'] ?? '',
                ],
            ];
            $itemCounter++;
        }

        $fields = $componentData['fields'] ?? [];
        $fields['wbFooterLinks'] = $items;

        return [
            'type' => 'wbFooter',
            'fields' => $fields,
        ];
    }

    /**
     * Returns the path to the seed resources directory.
     */
    /**
     * Seeds the About, Services (index + 3 children), and Contact pages from pages.json.
     */
    private function seedStaticPages(string $seedPath, array $assetIds): void
    {
        $config = $this->loadJson($seedPath . '/pages.json');
        if (!$config) {
            $this->stderr("  Warning: pages.json not found. Skipping static pages.\n");
            return;
        }

        // Load translation configs for extra sites (pages.tr.json, pages.de.json)
        $extraSiteConfigs = $this->getExtraSiteConfigs('pages');

        // --- About index + sub-pages ---
        $about = $config['about'] ?? null;
        if ($about) {
            $this->stdout("  About:\n");
            $aboutIndex = $this->seedStructureEntry($about, $assetIds, null, $extraSiteConfigs, 'about');

            foreach ($config['aboutSubPages'] ?? [] as $subKey => $subDef) {
                $this->stdout("  About sub-page:\n");
                $this->seedStructureEntry($subDef, $assetIds, $aboutIndex, $extraSiteConfigs, 'aboutSubPages.' . $subKey);
            }
        }

        // --- Services index + children ---
        $servicesIndex = $config['servicesIndex'] ?? null;
        if ($servicesIndex) {
            $this->stdout("  Services index:\n");
            $indexEntry = $this->seedStructureEntry($servicesIndex, $assetIds, null, $extraSiteConfigs, 'servicesIndex');

            foreach ($config['services'] ?? [] as $serviceKey => $serviceDef) {
                $this->stdout("  Service page:\n");
                $this->seedStructureEntry($serviceDef, $assetIds, $indexEntry, $extraSiteConfigs, 'services.' . $serviceKey);
            }
        }

        // --- Generic standalone pages (wbPage section) ---
        foreach ($config['pages'] ?? [] as $pageKey => $pageDef) {
            $this->stdout("  Page:\n");
            $this->seedStructureEntry($pageDef, $assetIds, null, $extraSiteConfigs, 'pages.' . $pageKey);
        }

        // --- Contact (single section — entry already exists) ---
        $contactConfig = $config['contact'] ?? null;
        if ($contactConfig) {
            $this->stdout("  Contact:\n");
            $section = Craft::$app->getEntries()->getSectionByHandle($contactConfig['section']);
            if (!$section) {
                $this->stderr("    Warning: Section 'wbContact' not found.\n");
            } else {
                // Single sections always have one entry per site
                $entry = Entry::find()->section($contactConfig['section'])->status(null)->one();
                if (!$entry) {
                    $this->stderr("    Warning: No entry found in wbContact single section.\n");
                } else {
                    if (isset($contactConfig['wbShowInNav'])) {
                        $entry->setFieldValue('wbShowInNav', (bool) $contactConfig['wbShowInNav']);
                    }
                    if (isset($contactConfig['wbNavOrder'])) {
                        $entry->setFieldValue('wbNavOrder', (int) $contactConfig['wbNavOrder']);
                    }
                    if (isset($contactConfig['wbShowInFooter'])) {
                        $entry->setFieldValue('wbShowInFooter', (bool) $contactConfig['wbShowInFooter']);
                    }
                    if (isset($contactConfig['wbFooterOrder'])) {
                        $entry->setFieldValue('wbFooterOrder', (int) $contactConfig['wbFooterOrder']);
                    }
                    if (!empty($contactConfig['wbSeoTitle'])) {
                        $entry->setFieldValue('wbSeoTitle', $contactConfig['wbSeoTitle']);
                    }
                    if (!empty($contactConfig['wbSeoDescription'])) {
                        $entry->setFieldValue('wbSeoDescription', $contactConfig['wbSeoDescription']);
                    }
                    if (!empty($contactConfig['blocks'])) {
                        $entry->setFieldValue('wbBlocks', $this->buildInlineBlocksData($contactConfig['blocks'], $assetIds));
                    }
                    if (Craft::$app->getElements()->saveElement($entry)) {
                        $this->stdout("    - Contact page saved (ID: {$entry->id})\n");

                        // Propagate contact to extra sites
                        foreach ($extraSiteConfigs as $siteHandle => $siteConfig) {
                            $translatedDef = $this->findTranslatedDef($siteConfig['defs'], $entry->slug, 'contact');
                            if ($translatedDef) {
                                $this->propagateEntryToSite($entry, $siteConfig['siteId'], $translatedDef, $assetIds);
                                $this->stdout("      - Propagated to '$siteHandle'\n");
                            }
                        }
                    } else {
                        $this->stderr("    ! Failed to save contact page:\n");
                        foreach ($entry->getErrors() as $attr => $errors) {
                            foreach ($errors as $error) {
                                $this->stderr("      [$attr] $error\n");
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Creates or updates a structure-section entry.
     * Returns the saved Entry on success, or null on failure.
     *
     * @param array       $def              Entry definition from pages.json
     * @param array       $assetIds         Indexed asset IDs
     * @param Entry|null  $parent           Parent entry for structure sections
     * @param array       $extraSiteConfigs From getExtraSiteConfigs() — [siteHandle => [siteId, defs]]
     * @param string|null $defKey           Dotted key into translation JSON (e.g. 'about', 'services.1')
     */
    private function seedStructureEntry(array $def, array $assetIds, ?Entry $parent = null, array $extraSiteConfigs = [], ?string $defKey = null): ?Entry
    {
        $sectionHandle = $def['section'];
        $entryTypeHandle = $def['entryType'];
        $slug = $def['slug'];

        $section = Craft::$app->getEntries()->getSectionByHandle($sectionHandle);
        if (!$section) {
            $this->stderr("    Warning: Section '$sectionHandle' not found.\n");
            return null;
        }

        $entryType = null;
        foreach ($section->getEntryTypes() as $et) {
            if ($et->handle === $entryTypeHandle) {
                $entryType = $et;
                break;
            }
        }
        if (!$entryType) {
            $this->stderr("    Warning: Entry type '$entryTypeHandle' not found in '$sectionHandle'.\n");
            return null;
        }

        // Skip if already exists
        $existing = Entry::find()->section($sectionHandle)->slug($slug)->status(null)->one();
        if ($existing) {
            $this->stdout("    - '{$def['title']}' already exists (ID: {$existing->id}), skipping.\n");
            return $existing;
        }

        $entry = new Entry();
        $entry->sectionId = $section->id;
        $entry->typeId = $entryType->id;
        $entry->title = $def['title'];
        $entry->slug = $slug;
        $entry->enabled = true;

        if ($parent !== null) {
            $entry->setParentId($parent->id);
        }

        if (!empty($def['excerpt'])) {
            $entry->setFieldValue('wbExcerpt', $def['excerpt']);
        }

        if (!empty($def['wbSeoTitle'])) {
            $entry->setFieldValue('wbSeoTitle', $def['wbSeoTitle']);
        }

        if (!empty($def['wbSeoDescription'])) {
            $entry->setFieldValue('wbSeoDescription', $def['wbSeoDescription']);
        }

        $imageIndex = $def['imageIndex'] ?? null;
        if ($imageIndex !== null && !empty($assetIds[$imageIndex])) {
            $entry->setFieldValue('wbFeaturedImage', [$assetIds[$imageIndex]]);
        }

        if (isset($def['wbShowInNav'])) {
            $entry->setFieldValue('wbShowInNav', (bool) $def['wbShowInNav']);
        }

        if (isset($def['wbNavOrder'])) {
            $entry->setFieldValue('wbNavOrder', (int) $def['wbNavOrder']);
        }

        if (isset($def['wbShowInFooter'])) {
            $entry->setFieldValue('wbShowInFooter', (bool) $def['wbShowInFooter']);
        }

        if (isset($def['wbFooterOrder'])) {
            $entry->setFieldValue('wbFooterOrder', (int) $def['wbFooterOrder']);
        }

        if (!empty($def['blocks'])) {
            $entry->setFieldValue('wbBlocks', $this->buildInlineBlocksData($def['blocks'], $assetIds));
        }

        if (Craft::$app->getElements()->saveElement($entry)) {
            $this->stdout("    - '{$entry->title}' saved (ID: {$entry->id})\n");

            // Propagate to extra sites
            if ($defKey !== null) {
                foreach ($extraSiteConfigs as $siteHandle => $siteConfig) {
                    $translatedDef = $this->resolveNestedDef($siteConfig['defs'], $defKey);
                    if ($translatedDef) {
                        $this->propagateEntryToSite($entry, $siteConfig['siteId'], $translatedDef, $assetIds);
                        $this->stdout("      - Propagated to '$siteHandle'\n");
                    }
                }
            }

            return $entry;
        }

        $this->stderr("    ! Failed to save '{$def['title']}':\n");
        foreach ($entry->getErrors() as $attr => $errors) {
            foreach ($errors as $error) {
                $this->stderr("      [$attr] $error\n");
            }
        }
        // Dump nested block errors
        $wbBlocks = $entry->getFieldValue('wbBlocks');
        if ($wbBlocks) {
            foreach ($wbBlocks->all() as $block) {
                if ($block->hasErrors()) {
                    $this->stderr("      Block '{$block->getType()->handle}' errors:\n");
                    foreach ($block->getErrors() as $attr => $errors) {
                        foreach ($errors as $error) {
                            $this->stderr("        [$attr] $error\n");
                        }
                    }
                }
                // Check nested matrix fields on the block
                foreach ($block->getFieldLayout()->getCustomFields() as $field) {
                    if ($field instanceof \craft\fields\Matrix) {
                        $nestedQuery = $block->getFieldValue($field->handle);
                        if ($nestedQuery) {
                            foreach ($nestedQuery->all() as $nestedBlock) {
                                if ($nestedBlock->hasErrors()) {
                                    $this->stderr("        Nested '{$nestedBlock->getType()->handle}' errors:\n");
                                    foreach ($nestedBlock->getErrors() as $attr => $errors) {
                                        foreach ($errors as $error) {
                                            $this->stderr("          [$attr] $error\n");
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return null;
    }

    /**
     * Resolve a translation def from a pages-style JSON object using a dotted key path.
     * Supports:
     *   'about'          → $defs['about']
     *   'aboutSubPages.0'→ $defs['aboutSubPages'][0]
     *   'services.2'     → $defs['services'][2]
     *   'pages.0'        → $defs['pages'][0]
     *   'contact'        → $defs['contact']
     */
    private function resolveNestedDef(?array $defs, string $key): ?array
    {
        if (!$defs) {
            return null;
        }

        $parts = explode('.', $key, 2);
        $topKey = $parts[0];

        if (!isset($defs[$topKey])) {
            return null;
        }

        if (count($parts) === 1) {
            return is_array($defs[$topKey]) ? $defs[$topKey] : null;
        }

        $index = (int) $parts[1];
        return $defs[$topKey][$index] ?? null;
    }

    /**
     * Sets wbShowInNav, wbShowInFooter, and optionally wbBlocks on single-section
     * entries that aren't handled by seedStaticPages (i.e. Home and Blog index).
     */
    private function seedSinglePageNavFlags(): void
    {
        $seedPath = $this->getSeedPath();
        $pagesConfig = $this->loadJson($seedPath . '/pages.json');
        $homeConfig = $pagesConfig['home'] ?? [];

        $singles = [
            'home' => array_merge([
                'section'          => 'wbHome',
                'wbShowInNav'      => true,
                'wbNavOrder'       => 10,
                'wbShowInFooter'   => true,
                'wbFooterOrder'    => 10,
                'wbSeoTitle'       => 'Ember & Rye — Modern American Steakhouse, Washington DC',
                'wbSeoDescription' => 'Experience fire-driven cooking at its finest. Ember & Rye serves premium dry-aged beef and seasonal American cuisine at 1847 K Street NW, Washington DC.',
            ], $homeConfig),
            'blogIndex' => [
                'section'          => 'wbBlogIndex',
                'wbShowInNav'      => true,
                'wbNavOrder'       => 40,
                'wbShowInFooter'   => false,
                'wbFooterOrder'    => null,
                'wbPerPage'        => '6',
                'wbSeoTitle'       => 'Journal — Ember & Rye',
                'wbSeoDescription' => 'Stories from the kitchen, the farm, and the bar at Ember & Rye.',
            ],
        ];

        // Load asset IDs for image resolution
        $assetIds = $this->getIndexedAssetIds($seedPath);

        // Load translation configs for extra sites
        $extraSiteConfigs = $this->getExtraSiteConfigs('pages');

        foreach ($singles as $defKey => $def) {
            $sectionHandle = $def['section'];
            $entry = Entry::find()->section($sectionHandle)->status(null)->one();
            if (!$entry) {
                $this->stderr("  Warning: No entry found in '$sectionHandle' section. Skipping.\n");
                continue;
            }

            if (isset($def['wbShowInNav'])) {
                $entry->setFieldValue('wbShowInNav', (bool) $def['wbShowInNav']);
            }
            if (isset($def['wbNavOrder'])) {
                $entry->setFieldValue('wbNavOrder', (int) $def['wbNavOrder']);
            }
            if (isset($def['wbShowInFooter'])) {
                $entry->setFieldValue('wbShowInFooter', (bool) $def['wbShowInFooter']);
            }
            if (isset($def['wbFooterOrder'])) {
                $entry->setFieldValue('wbFooterOrder', (int) $def['wbFooterOrder']);
            }
            if (!empty($def['wbSeoTitle'])) {
                $entry->setFieldValue('wbSeoTitle', $def['wbSeoTitle']);
            }
            if (!empty($def['wbSeoDescription'])) {
                $entry->setFieldValue('wbSeoDescription', $def['wbSeoDescription']);
            }
            if (!empty($def['wbPerPage'])) {
                $entry->setFieldValue('wbPerPage', $def['wbPerPage']);
            }
            if (!empty($def['blocks'])) {
                $entry->setFieldValue('wbBlocks', $this->buildInlineBlocksData($def['blocks'], $assetIds));
            }

            if (Craft::$app->getElements()->saveElement($entry)) {
                $this->stdout("  - '$sectionHandle' nav flags set (ID: {$entry->id})\n");

                // Propagate to extra sites
                foreach ($extraSiteConfigs as $siteHandle => $siteConfig) {
                    $translatedDef = $this->resolveNestedDef($siteConfig['defs'], $defKey);
                    if ($translatedDef) {
                        $this->propagateEntryToSite($entry, $siteConfig['siteId'], $translatedDef, $assetIds);
                        $this->stdout("    - Propagated to '$siteHandle'\n");
                    }
                }
            } else {
                $this->stderr("  ! Failed to save '$sectionHandle' nav flags:\n");
                foreach ($entry->getErrors() as $attr => $errors) {
                    foreach ($errors as $error) {
                        $this->stderr("    [$attr] $error\n");
                    }
                }
            }
        }
    }

    // =========================================================================
    // Multi-site propagation helpers
    // =========================================================================

    /**
     * Returns an array of [siteHandle => [siteId, translationDefs]] for extra sites.
     * translationDefs is the decoded JSON from pages.{lang}.json or blogs.{lang}.json,
     * or null if the file doesn't exist yet.
     *
     * @param string $jsonFile  Base filename without extension, e.g. 'pages' or 'blogs'
     * @return array<string, array{siteId: int, defs: array|null}>
     */
    private function getExtraSiteConfigs(string $jsonFile): array
    {
        $seedPath = $this->getSeedPath();
        $result   = [];

        foreach (self::EXTRA_SITES as $siteHandle => $langSuffix) {
            $site = Craft::$app->getSites()->getSiteByHandle($siteHandle);
            if (!$site) {
                continue;
            }
            $translationFile = $seedPath . '/' . $jsonFile . '.' . $langSuffix . '.json';
            $defs = file_exists($translationFile) ? $this->loadJson($translationFile) : null;
            $result[$siteHandle] = ['siteId' => $site->id, 'defs' => $defs];
        }

        return $result;
    }

    /**
     * Given a saved EN entry, propagate it to an extra site with translated content.
     *
     * $translatedDef mirrors the EN entry def shape from pages.json / blogs.json.
     * Only fields present in $translatedDef are overwritten; everything else
     * inherits from the primary site propagation Craft already does.
     */
    private function propagateEntryToSite(Entry $entry, int $siteId, array $translatedDef, array $assetIds): void
    {
        // Load the entry for the target site
        $localEntry = Entry::find()
            ->id($entry->id)
            ->siteId($siteId)
            ->status(null)
            ->one();

        if (!$localEntry) {
            $this->stderr("      ! Could not load entry {$entry->id} for siteId $siteId\n");
            return;
        }

        // Title
        if (!empty($translatedDef['title'])) {
            $localEntry->title = $translatedDef['title'];
        }

        // Slug — only override if explicitly provided in translation file
        if (!empty($translatedDef['slug'])) {
            $localEntry->slug = $translatedDef['slug'];
        }

        // SEO
        if (!empty($translatedDef['wbSeoTitle'])) {
            $localEntry->setFieldValue('wbSeoTitle', $translatedDef['wbSeoTitle']);
        }
        if (!empty($translatedDef['wbSeoDescription'])) {
            $localEntry->setFieldValue('wbSeoDescription', $translatedDef['wbSeoDescription']);
        }

        // Excerpt
        if (!empty($translatedDef['excerpt'])) {
            $localEntry->setFieldValue('wbExcerpt', $translatedDef['excerpt']);
        }

        // wbBlocks
        if (!empty($translatedDef['blocks'])) {
            $blocksData = $this->buildInlineBlocksData($translatedDef['blocks'], $assetIds);
            $localEntry->setFieldValue('wbBlocks', $blocksData);
        }

        if (!Craft::$app->getElements()->saveElement($localEntry)) {
            $this->stderr("      ! Failed to propagate entry to siteId $siteId:\n");
            foreach ($localEntry->getErrors() as $attr => $errors) {
                foreach ($errors as $error) {
                    $this->stderr("        [$attr] $error\n");
                }
            }
        }
    }

    /**
     * Find a translated definition by slug from a flat array (blogs) or named-key object (pages).
     * Returns null if no translation found.
     *
     * @param array       $defs         Decoded translation JSON
     * @param string      $slug         Entry slug to match
     * @param string|null $topLevelKey  Top-level key for named objects (e.g. 'about', 'contact'); null for flat arrays
     */
    private function findTranslatedDef(?array $defs, string $slug, ?string $topLevelKey = null): ?array
    {
        if (!$defs) {
            return null;
        }

        if ($topLevelKey !== null) {
            // Named key (pages.json shape) — defs[$topLevelKey] is the entry def
            return $defs[$topLevelKey] ?? null;
        }

        // Flat array (blogs.json shape) — find by slug
        foreach ($defs as $def) {
            if (($def['slug'] ?? '') === $slug) {
                return $def;
            }
        }

        return null;
    }

    private function getSeedPath(): string
    {
        return dirname(__DIR__) . '/resources/seed';
    }

    /**
     * Loads and decodes a JSON file.
     */
    private function loadJson(string $path): ?array
    {
        if (!file_exists($path)) {
            $this->stderr("  Warning: File not found: $path\n");
            return null;
        }

        $json = json_decode(file_get_contents($path), true);
        if ($json === null) {
            $this->stderr("  Warning: Invalid JSON in: $path\n");
            return null;
        }

        return $json;
    }

    /**
     * Downloads sample images and indexes them as Craft assets.
     * Returns an array of asset IDs.
     */
    private function seedImages(string $seedPath): array
    {
        $imagesConfig = $this->loadJson($seedPath . '/images.json');
        if (!$imagesConfig) {
            return [];
        }

        $volumeHandle = $imagesConfig['volume'] ?? 'wbImages';
        $volume = Craft::$app->getVolumes()->getVolumeByHandle($volumeHandle);
        if (!$volume) {
            $this->stderr("  Warning: Volume '$volumeHandle' not found. Skipping images.\n");
            return [];
        }

        $folder = Craft::$app->getAssets()->getRootFolderByVolumeId($volume->id);
        if (!$folder) {
            $this->stderr("  Warning: Root folder for volume '$volumeHandle' not found.\n");
            return [];
        }

        $assetIds = [];

        foreach ($imagesConfig['images'] ?? [] as $imageDef) {
            $filename = $imageDef['filename'];
            $title = $imageDef['title'] ?? pathinfo($filename, PATHINFO_FILENAME);

            // Check if asset already exists
            $existing = Asset::find()
                ->volumeId($volume->id)
                ->folderId($folder->id)
                ->filename($filename)
                ->one();

            if ($existing) {
                $this->stdout("  - $filename (already indexed, ID: {$existing->id})\n");
                $assetIds[] = $existing->id;
                continue;
            }

            // Download the image to a temp file
            $tempPath = Craft::$app->getPath()->getTempPath() . DIRECTORY_SEPARATOR . $filename;

            $imageData = @file_get_contents($imageDef['url']);
            if ($imageData === false) {
                $this->stderr("  ! Failed to download: {$imageDef['url']}\n");
                continue;
            }

            file_put_contents($tempPath, $imageData);

            // Create the asset — copy temp file since Craft moves it
            $copyPath = $tempPath . '.copy';
            copy($tempPath, $copyPath);

            $asset = new Asset();
            $asset->tempFilePath = $copyPath;
            $asset->filename = $filename;
            $asset->title = $title;
            $asset->folderId = $folder->id;
            $asset->newFolderId = $folder->id;
            $asset->volumeId = $volume->id;
            $asset->setScenario(Asset::SCENARIO_CREATE);
            $asset->avoidFilenameConflicts = true;

            if (Craft::$app->getElements()->saveElement($asset)) {
                $this->stdout("  - $filename (ID: {$asset->id})\n");
                $assetIds[] = $asset->id;
            } else {
                $this->stderr("  ! Failed to save asset '$filename': " . implode(', ', $asset->getFirstErrors()) . "\n");
            }

            // Clean up temp file
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }

        return $assetIds;
    }

    /**
     * Returns an ordered array of asset IDs for already-indexed images (no download).
     * Used when images were already seeded and we just need IDs for block building.
     */
    private function getIndexedAssetIds(string $seedPath): array
    {
        $imagesConfig = $this->loadJson($seedPath . '/images.json');
        if (!$imagesConfig) {
            return [];
        }

        $volumeHandle = $imagesConfig['volume'] ?? 'wbImages';
        $volume = Craft::$app->getVolumes()->getVolumeByHandle($volumeHandle);
        if (!$volume) {
            return [];
        }

        $folder = Craft::$app->getAssets()->getRootFolderByVolumeId($volume->id);
        if (!$folder) {
            return [];
        }

        $assetIds = [];

        foreach ($imagesConfig['images'] ?? [] as $imageDef) {
            $filename = $imageDef['filename'];
            $existing = Asset::find()
                ->volumeId($volume->id)
                ->folderId($folder->id)
                ->filename($filename)
                ->one();
            if ($existing) {
                $assetIds[] = $existing->id;
            }
        }

        return $assetIds;
    }

    /**
     * Builds the wbBlocks matrix field data from seed component JSON files.
     */
    private function buildBlocksData(string $seedPath, array $blockNames, array $assetIds): array
    {
        $blocks = [];
        $counter = 1;

        foreach ($blockNames as $blockName) {
            $componentFile = $seedPath . '/components/' . $blockName . '.json';
            $componentData = $this->loadJson($componentFile);
            if (!$componentData) {
                continue;
            }

            $entryTypeHandle = $componentData['type'];
            $this->stdout("  - $entryTypeHandle\n");

            // Special handling for blocks with nested items
            if ($entryTypeHandle === 'wbAccordion') {
                $block = $this->buildAccordionBlock($componentData, $counter);
            } elseif ($entryTypeHandle === 'wbCarousel') {
                $block = $this->buildCarouselBlock($componentData, $assetIds, $counter);
            } elseif ($entryTypeHandle === 'wbGallery') {
                $block = $this->buildGalleryBlock($componentData, $assetIds, $counter);
            } elseif ($entryTypeHandle === 'wbFullscreenImage') {
                $block = $this->buildFullscreenImageBlock($componentData, $assetIds, $counter);
            } elseif ($entryTypeHandle === 'wbNavbar') {
                $block = $this->buildNavbarBlock($componentData, $counter);
            } elseif ($entryTypeHandle === 'wbTabs') {
                $block = $this->buildTabsBlock($componentData, $counter);
            } elseif ($entryTypeHandle === 'wbProgressBar') {
                $block = $this->buildProgressBarBlock($componentData, $counter);
            } elseif ($entryTypeHandle === 'wbListGroup') {
                $block = $this->buildListGroupBlock($componentData, $counter);
            } elseif ($entryTypeHandle === 'wbButtonGroup') {
                $block = $this->buildButtonGroupBlock($componentData, $counter);
            } elseif ($entryTypeHandle === 'wbOffcanvas') {
                $block = $this->buildOffcanvasBlock($componentData, $assetIds, $counter);
            } else {
                $block = $this->buildSimpleBlock($componentData, $counter);
            }

            if ($block) {
                $blocks["new:$counter"] = $block;
                $counter++;
            }
        }

        return $blocks;
    }

    /**
     * Builds a simple block (no nested matrix fields).
     */
    private function buildSimpleBlock(array $componentData, int &$counter): array
    {
        return [
            'type' => $componentData['type'],
            'fields' => $componentData['fields'] ?? [],
        ];
    }

    /**
     * Builds an accordion block with nested wbAccordionItems.
     */
    private function buildAccordionBlock(array $componentData, int &$counter): array
    {
        $items = [];
        $itemCounter = 1;

        foreach ($componentData['items'] ?? [] as $item) {
            $items["new:$itemCounter"] = [
                'type' => 'wbAccordionItem',
                'fields' => [
                    'wbTitle' => $item['wbTitle'] ?? '',
                    'wbText' => $item['wbText'] ?? '',
                ],
            ];
            $itemCounter++;
        }

        $fields = [];

        // Set the title if accordion has one
        if (!empty($componentData['title'])) {
            // wbAccordion has hasTitleField=true, so title is set on the entry level
        }

        $fields['wbAccordionItems'] = $items;

        $block = [
            'type' => 'wbAccordion',
            'fields' => $fields,
        ];

        // Set the title on the block
        if (!empty($componentData['title'])) {
            $block['title'] = $componentData['title'];
        }

        return $block;
    }

    /**
     * Builds a carousel block with nested wbCarouselItems, one slide per asset.
     */
    private function buildCarouselBlock(array $componentData, array $assetIds, int &$counter): array
    {
        $items = [];
        $itemCounter = 1;

        foreach ($componentData['items'] ?? [] as $index => $item) {
            // Respect explicit imageIndex if provided; otherwise fall back to loop position
            $imgIndex = $item['imageIndex'] ?? $index;
            $assetId = $assetIds[$imgIndex] ?? null;

            $fields = [
                'wbCaption' => $item['wbCaption'] ?? '',
                'wbCaptionText' => $item['wbCaptionText'] ?? '',
            ];

            if ($assetId) {
                $fields['wbImage'] = [$assetId];
            }

            $items["new:$itemCounter"] = [
                'type' => 'wbCarouselItem',
                'fields' => $fields,
            ];
            $itemCounter++;
        }

        $fields = $componentData['fields'] ?? [];
        $fields['wbCarouselItems'] = $items;

        return [
            'type' => 'wbCarousel',
            'fields' => $fields,
        ];
    }

    /**
     * Builds a wbGallery block with nested wbGalleryItems.
     */
    private function buildGalleryBlock(array $componentData, array $assetIds, int &$counter): array
    {
        $items = [];
        $itemCounter = 1;

        foreach ($componentData['items'] ?? [] as $index => $item) {
            $imgIndex = $item['imageIndex'] ?? $index;
            $assetId = $assetIds[$imgIndex] ?? null;

            $fields = [
                'wbCaption' => $item['wbCaption'] ?? '',
            ];

            if ($assetId) {
                $fields['wbImage'] = [$assetId];
            }

            $items["new:$itemCounter"] = [
                'type' => 'wbGalleryItem',
                'fields' => $fields,
            ];
            $itemCounter++;
        }

        $fields = $componentData['fields'] ?? [];
        $fields['wbGalleryItems'] = $items;

        return [
            'type' => 'wbGallery',
            'fields' => $fields,
        ];
    }

    /**
     * Builds a wbColumns block with nested wbColumn entries, each containing wbBlocks.
     */
    private function buildColumnsBlock(array $componentData, array $assetIds, int &$counter): array
    {
        $columns = [];
        $colCounter = 1;

        foreach ($componentData['columns'] ?? [] as $columnDef) {
            $nestedBlocks = $this->buildInlineBlocksData($columnDef['blocks'] ?? [], $assetIds);
            $columns["new:$colCounter"] = [
                'type'   => 'wbColumn',
                'fields' => ['wbBlocks' => $nestedBlocks],
            ];
            $colCounter++;
        }

        $fields = $componentData['fields'] ?? [];
        $fields['wbColumnItems'] = $columns;

        return [
            'type'   => 'wbColumns',
            'fields' => $fields,
        ];
    }

    /**
     * Builds a wbTabs block with nested wbTabItems.
     */
    private function buildTabsBlock(array $componentData, int &$counter): array
    {
        $items = [];
        $itemCounter = 1;

        foreach ($componentData['fields']['wbTabItems'] ?? [] as $item) {
            $items["new:$itemCounter"] = [
                'type' => 'wbTabItem',
                'fields' => [
                    'wbTabTitle' => $item['wbTabTitle'] ?? '',
                    'wbText'     => $item['wbText'] ?? '',
                ],
            ];
            $itemCounter++;
        }

        $fields = array_diff_key($componentData['fields'] ?? [], ['wbTabItems' => null]);
        $fields['wbTabItems'] = $items;

        return [
            'type'   => 'wbTabs',
            'fields' => $fields,
        ];
    }

    /**
     * Builds a wbProgressBar block with nested wbProgressBars.
     */
    private function buildProgressBarBlock(array $componentData, int &$counter): array
    {
        $items = [];
        $itemCounter = 1;

        foreach ($componentData['fields']['wbProgressBars'] ?? [] as $item) {
            $items["new:$itemCounter"] = [
                'type' => 'wbProgressBarItem',
                'fields' => [
                    'wbProgressLabel'   => $item['wbProgressLabel'] ?? '',
                    'wbProgressValue'   => (int) ($item['wbProgressValue'] ?? 0),
                    'wbProgressColor'   => $item['wbProgressColor'] ?? 'bg-primary',
                    'wbProgressStriped' => (bool) ($item['wbProgressStriped'] ?? false),
                ],
            ];
            $itemCounter++;
        }

        $fields = array_diff_key($componentData['fields'] ?? [], ['wbProgressBars' => null]);
        $fields['wbProgressBars'] = $items;

        return [
            'type'   => 'wbProgressBar',
            'fields' => $fields,
        ];
    }

    /**
     * Builds a wbListGroup block with nested wbListGroupItems.
     */
    private function buildListGroupBlock(array $componentData, int &$counter): array
    {
        $items = [];
        $itemCounter = 1;

        foreach ($componentData['items'] ?? [] as $item) {
            $items["new:$itemCounter"] = [
                'type' => 'wbListGroupItem',
                'fields' => [
                    'wbTitle'        => $item['wbTitle'] ?? '',
                    'wbButtonUrl'    => $item['wbButtonUrl'] ?? '',
                    'wbButtonTarget' => $item['wbButtonTarget'] ?? '',
                ],
            ];
            $itemCounter++;
        }

        $fields = $componentData['fields'] ?? [];
        $fields['wbListGroupItems'] = $items;

        return [
            'type'   => 'wbListGroup',
            'fields' => $fields,
        ];
    }

    /**
     * Builds a wbFullscreenImage block with nested wbFsSlide entries.
     *
     * Slide definition keys:
     *   imageIndex      – index into $assetIds for wbBackgroundImage
     *   portraitIndex   – (optional) index into $assetIds for wbPortraitImage
     *   wbTitle         – headline text
     *   wbText          – subtitle / body text
     *   wbAlignment     – 'start' | 'center' | 'end'  (default: 'center')
     *   wbVerticalAlign – 'top' | 'center' | 'bottom'  (default: 'center')
     *   wbOverlayOpacity – '0' | '20' | '40' | '60' | '70'  (default: '40')
     */
    private function buildFullscreenImageBlock(array $componentData, array $assetIds, int &$counter): array
    {
        $slides = [];
        $slideCounter = 1;

        foreach ($componentData['slides'] ?? [] as $slide) {
            $fields = [
                'wbTitle'           => $slide['wbTitle'] ?? '',
                'wbText'            => $slide['wbText'] ?? '',
                'wbAlignment'       => $slide['wbAlignment'] ?? 'center',
                'wbVerticalAlign'   => $slide['wbVerticalAlign'] ?? 'center',
                'wbOverlayOpacity'  => $slide['wbOverlayOpacity'] ?? '40',
            ];

            // Background image
            $imgIndex = $slide['imageIndex'] ?? null;
            if ($imgIndex !== null && !empty($assetIds[$imgIndex])) {
                $fields['wbBackgroundImage'] = [$assetIds[$imgIndex]];
            }

            // Optional portrait image
            $portIndex = $slide['portraitIndex'] ?? null;
            if ($portIndex !== null && !empty($assetIds[$portIndex])) {
                $fields['wbPortraitImage'] = [$assetIds[$portIndex]];
            }

            $slides["new:$slideCounter"] = [
                'type'   => 'wbFsSlide',
                'fields' => $fields,
            ];
            $slideCounter++;
        }

        $blockFields = $componentData['fields'] ?? [];
        $blockFields['wbFsSlides'] = $slides;

        return [
            'type'   => 'wbFullscreenImage',
            'fields' => $blockFields,
        ];
    }

    /**
     * Builds a navbar block with nested wbNavItems.
     */
    private function buildNavbarBlock(array $componentData, int &$counter): array
    {
        $items = [];
        $itemCounter = 1;

        foreach ($componentData['items'] ?? [] as $item) {
            $items["new:$itemCounter"] = [
                'type' => 'wbNavItem',
                'fields' => [
                    'wbNavItemLabel' => $item['wbNavItemLabel'] ?? '',
                    'wbNavItemUrl' => $item['wbNavItemUrl'] ?? '',
                    'wbNavItemTarget' => $item['wbNavItemTarget'] ?? '',
                ],
            ];
            $itemCounter++;
        }

        $fields = $componentData['fields'] ?? [];

        // Lightswitch fields must be passed as bool, not truthy string
        if (isset($fields['wbNavbarSearch'])) {
            $fields['wbNavbarSearch'] = (bool) $fields['wbNavbarSearch'];
        }

        $fields['wbNavbarItems'] = $items;

        return [
            'type' => 'wbNavbar',
            'fields' => $fields,
        ];
    }

    /**
     * Builds a wbButtonGroup block with nested wbButtonGroupItems.
     */
    private function buildButtonGroupBlock(array $componentData, int &$counter): array
    {
        $items = [];
        $itemCounter = 1;

        foreach ($componentData['items'] ?? [] as $item) {
            $items["new:$itemCounter"] = [
                'type' => 'wbButtonGroupItem',
                'fields' => [
                    'wbButtonLabel'  => $item['wbButtonLabel'] ?? '',
                    'wbButtonUrl'    => $item['wbButtonUrl'] ?? '',
                    'wbButtonTarget' => $item['wbButtonTarget'] ?? '',
                ],
            ];
            $itemCounter++;
        }

        $fields = $componentData['fields'] ?? [];
        $fields['wbButtonGroupItems'] = $items;

        return [
            'type'   => 'wbButtonGroup',
            'fields' => $fields,
        ];
    }

    /**
     * Builds a wbOffcanvas block with nested wbBlocks content.
     */
    private function buildOffcanvasBlock(array $componentData, array $assetIds, int &$counter): array
    {
        $nestedBlocks = $this->buildInlineBlocksData($componentData['blocks'] ?? [], $assetIds);

        $fields = $componentData['fields'] ?? [];

        // Cast lightswitch
        if (isset($fields['wbOffcanvasBackdrop'])) {
            $fields['wbOffcanvasBackdrop'] = (bool) $fields['wbOffcanvasBackdrop'];
        }

        $fields['wbBlocks'] = $nestedBlocks;

        return [
            'type'   => 'wbOffcanvas',
            'fields' => $fields,
        ];
    }
}
