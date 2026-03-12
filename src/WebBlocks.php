<?php

namespace fklavyenet\webblocks;

use craft\base\Model;
use craft\base\Plugin as BasePlugin;
use craft\elements\Entry;
use craft\events\RegisterElementActionsEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use craft\web\View;
use fklavyenet\webblocks\assetbundles\WebBlocksAsset;
use fklavyenet\webblocks\elementactions\ApproveComment;
use fklavyenet\webblocks\elementactions\RejectComment;
use fklavyenet\webblocks\models\Settings;
use fklavyenet\webblocks\variables\WebBlocksVariable;
use yii\base\Event;

/**
 * WebBlocks — A portable website building toolkit for Craft CMS 5.
 *
 * Provides reusable content blocks, flexible page layouts, and built-in SEO
 * for creating modern business websites. On install, creates the full content
 * schema: filesystems, volumes, image transforms, fields, entry types,
 * sections, global sets, categories, and tags.
 */
class WebBlocks extends BasePlugin
{
    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;

    public static function config(): array
    {
        return [
            'name' => 'WebBlocks',
            'description' => 'A portable website building toolkit for Craft CMS — create modern business websites quickly with reusable content blocks, flexible page layouts, and built-in SEO.',
            'developer' => 'Fklavye',
            'developerUrl' => 'https://fklavye.net',
            'controllerMap' => [
                'wipe' => \fklavyenet\webblocks\console\WipeController::class,
                'seed' => \fklavyenet\webblocks\console\SeedController::class,
                'form'    => \fklavyenet\webblocks\controllers\FormController::class,
                'comment' => \fklavyenet\webblocks\controllers\CommentController::class,
            ],
        ];
    }

    public function init(): void
    {
        parent::init();

        $this->_registerTemplateVariable();
        $this->_registerSiteTemplateRoots();
        $this->_registerCpTemplateRoots();
        $this->_registerSiteUrlRules();
        $this->_registerAssetBundle();
        $this->_registerCommentActions();
    }

    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }

    protected function settingsHtml(): ?string
    {
        return \Craft::$app->getView()->renderTemplate('webblocks-cp/_settings', [
            'settings' => $this->getSettings(),
        ]);
    }

    protected function afterInstall(): void
    {
        // CKEditor is a Composer dependency (always in vendor/).
        // Ensure it is also *activated* in Craft before creating schema.
        $pluginsService = \Craft::$app->getPlugins();
        if (!$pluginsService->isPluginInstalled('ckeditor')) {
            $pluginsService->installPlugin('ckeditor');
        }

        (new services\InstallService())->install();
        $this->_installSiteTranslations();
    }

    protected function beforeUninstall(): void
    {
        (new services\InstallService())->uninstall();
        $this->_uninstallSiteTranslations();
    }

    // =========================================================================
    // Site translation management
    // =========================================================================

    /**
     * Copies WebBlocks site translations to the project's translations/ directory.
     *
     * The `site` translation category in Craft reads ONLY from the project-level
     * translations/ folder (@translations). Plugin-provided site.php files are
     * not picked up automatically. We copy our translations there on install
     * and merge with any existing project translations.
     */
    private function _installSiteTranslations(): void
    {
        $sourceDir = $this->getBasePath() . DIRECTORY_SEPARATOR . 'translations';
        $targetDir = \Craft::getAlias('@translations');

        if (!is_dir($sourceDir)) {
            return;
        }

        foreach (glob($sourceDir . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) as $langDir) {
            $lang = basename($langDir);
            $sourceFile = $langDir . DIRECTORY_SEPARATOR . 'site.php';

            if (!file_exists($sourceFile)) {
                continue;
            }

            $targetLangDir = $targetDir . DIRECTORY_SEPARATOR . $lang;
            $targetFile = $targetLangDir . DIRECTORY_SEPARATOR . 'site.php';

            // Load our translations
            $ourTranslations = require $sourceFile;

            // Merge with existing project translations (if any)
            $existingTranslations = [];
            if (file_exists($targetFile)) {
                $existingTranslations = require $targetFile;
            }

            // Our keys go first, project overrides take priority
            $merged = array_merge($ourTranslations, $existingTranslations);

            // Ensure target directory exists
            if (!is_dir($targetLangDir)) {
                mkdir($targetLangDir, 0775, true);
            }

            // Write the merged file
            $this->_writeSiteTranslationFile($targetFile, $merged, $lang);
        }
    }

    /**
     * Removes WebBlocks site translations from the project's translations/ directory.
     *
     * Only removes keys that belong to WebBlocks. If the project had its own
     * translations, those are preserved.
     */
    private function _uninstallSiteTranslations(): void
    {
        $sourceDir = $this->getBasePath() . DIRECTORY_SEPARATOR . 'translations';
        $targetDir = \Craft::getAlias('@translations');

        if (!is_dir($sourceDir)) {
            return;
        }

        foreach (glob($sourceDir . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) as $langDir) {
            $lang = basename($langDir);
            $sourceFile = $langDir . DIRECTORY_SEPARATOR . 'site.php';
            $targetFile = $targetDir . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR . 'site.php';

            if (!file_exists($sourceFile) || !file_exists($targetFile)) {
                continue;
            }

            $ourKeys = array_keys(require $sourceFile);
            $currentTranslations = require $targetFile;

            // Remove only our keys
            foreach ($ourKeys as $key) {
                unset($currentTranslations[$key]);
            }

            if (empty($currentTranslations)) {
                // No translations left — remove the file
                unlink($targetFile);

                // Remove the language directory if empty
                $langDir = dirname($targetFile);
                if (is_dir($langDir) && count(glob($langDir . DIRECTORY_SEPARATOR . '*')) === 0) {
                    rmdir($langDir);
                }
            } else {
                // Write back remaining translations
                $this->_writeSiteTranslationFile($targetFile, $currentTranslations, $lang);
            }
        }
    }

    /**
     * Writes a PHP translation array file.
     */
    private function _writeSiteTranslationFile(string $path, array $translations, string $lang): void
    {
        $content = "<?php\n";
        $content .= "/**\n";
        $content .= " * Site translations ($lang) — includes WebBlocks admin labels.\n";
        $content .= " * Auto-generated by the WebBlocks plugin. Manual edits are preserved.\n";
        $content .= " */\n\n";
        $content .= "return [\n";

        foreach ($translations as $key => $value) {
            $escapedKey = str_replace("'", "\\'", $key);
            $escapedValue = str_replace("'", "\\'", $value);
            $content .= "    '$escapedKey' => '$escapedValue',\n";
        }

        $content .= "];\n";

        file_put_contents($path, $content);
    }

    // =========================================================================
    // Event registrations
    // =========================================================================

    private function _registerAssetBundle(): void
    {
        // Only register on site (front-end) requests, not console
        if (\Craft::$app->getRequest()->getIsSiteRequest()) {
            Event::on(
                View::class,
                View::EVENT_BEFORE_RENDER_PAGE_TEMPLATE,
                function () {
                    \Craft::$app->getView()->registerAssetBundle(WebBlocksAsset::class);
                }
            );
        }
    }

    private function _registerTemplateVariable(): void
    {
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('webblocks', WebBlocksVariable::class);
            }
        );
    }

    private function _registerSiteTemplateRoots(): void
    {
        $wbTemplatesPath = $this->getBasePath() . DIRECTORY_SEPARATOR . 'wbTemplates';

        Event::on(
            View::class,
            View::EVENT_REGISTER_SITE_TEMPLATE_ROOTS,
            function (RegisterTemplateRootsEvent $event) use ($wbTemplatesPath) {
                $event->roots['wb'] = $wbTemplatesPath;
            }
        );
    }

    private function _registerCpTemplateRoots(): void
    {
        $cpPath = $this->getBasePath() . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'cp';

        Event::on(
            View::class,
            View::EVENT_REGISTER_CP_TEMPLATE_ROOTS,
            function (RegisterTemplateRootsEvent $event) use ($cpPath) {
                $event->roots['webblocks-cp'] = $cpPath;
            }
        );
    }

    private function _registerSiteUrlRules(): void
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['sitemap.xml'] = ['template' => 'wb/sitemap'];
            }
        );
    }

    private function _registerCommentActions(): void
    {
        Event::on(
            Entry::class,
            Entry::EVENT_REGISTER_ACTIONS,
            function (RegisterElementActionsEvent $event) {
                $section = \Craft::$app->getEntries()->getSectionByHandle('wbComments');
                if (!$section) {
                    return;
                }

                $source = $event->source ?? '';
                if (
                    $source !== 'section:' . $section->uid &&
                    $source !== 'section:' . $section->id
                ) {
                    return;
                }

                array_unshift($event->actions, RejectComment::class);
                array_unshift($event->actions, ApproveComment::class);
            }
        );
    }

}
