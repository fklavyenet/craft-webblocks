<?php

namespace fklavyenet\webblocks\console;

use Craft;
use craft\console\Controller;
use yii\console\ExitCode;

class WipeController extends Controller
{
    /**
     * Wipe ALL plugin data: sections, entries, entry types, fields, volumes, filesystems, etc.
     * Usage: ddev craft webblocks/wipe
     */
    public function actionIndex(): int
    {
        $this->stdout("=== WebBlocks Wipe Command ===\n\n");

        // Order matters: sections first (deletes entries via cascade),
        // then entry types, matrix fields, fields, volumes, filesystems last
        $this->wipeSections();
        $this->wipeGlobalSets();
        $this->wipeCategoryGroups();
        $this->wipeTagGroups();
        $this->wipeEntryTypes();
        $this->wipeMatrixFields();
        $this->wipeFields();
        $this->wipeImageTransforms();
        $this->wipeVolumes();
        $this->wipeFilesystems();
        $this->hardDeleteSoftDeleted();
        $this->purgeProjectConfigEntries();

        $this->stdout("\n=== Wipe complete! ===\n");

        return ExitCode::OK;
    }

    /**
     * Wipe, uninstall, reinstall and seed in one step.
     * Usage: ddev craft webblocks/wipe/all
     */
    public function actionAll(): int
    {
        $this->stdout("=== WebBlocks: Wipe → Reinstall → Seed ===\n\n");

        // 1. Wipe
        if ($this->actionIndex() !== ExitCode::OK) {
            $this->stderr("Wipe failed — aborting.\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        // 2. Uninstall
        $this->stdout("\nUninstalling plugin...\n");
        $plugin = Craft::$app->getPlugins()->getPlugin('webblocks');
        if ($plugin && !Craft::$app->getPlugins()->uninstallPlugin('webblocks')) {
            $this->stderr("Plugin uninstall failed — aborting.\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $this->stdout("  Plugin uninstalled.\n");

        // 3. Install
        $this->stdout("\nInstalling plugin...\n");

        // Flush in-memory field/service caches BEFORE install so FieldInstallService
        // does not see stale pre-wipe objects and skips recreating fields.
        Craft::$app->getFields()->refreshFields();
        Craft::$app->set('entries', ['class' => \craft\services\Entries::class]);
        Craft::$app->set('globals', ['class' => \craft\services\Globals::class]);

        if (!Craft::$app->getPlugins()->installPlugin('webblocks')) {
            $this->stderr("Plugin install failed — aborting.\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $this->stdout("  Plugin installed.\n");

        // Flush again after install so seed sees the freshly created field layouts,
        // global sets, etc. — not objects cached during the install process itself.
        Craft::$app->getFields()->refreshFields();
        Craft::$app->set('entries', ['class' => \craft\services\Entries::class]);
        Craft::$app->set('globals', ['class' => \craft\services\Globals::class]);

        // 4. Seed
        $this->stdout("\nSeeding demo content...\n");
        $seed = new \fklavyenet\webblocks\console\SeedController('seed', Craft::$app);
        $result = $seed->actionIndex();
        if ($result !== ExitCode::OK) {
            $this->stderr("Seed failed.\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout("\n=== All done! ===\n");
        return ExitCode::OK;
    }

    private function wipeSections(): void
    {
        $this->stdout("Removing sections (and their entries)...\n");

        try {
            $sections = Craft::$app->getEntries()->getAllSections();
            $count = 0;
            foreach ($sections as $section) {
                if (Craft::$app->getEntries()->deleteSection($section)) {
                    $this->stdout("  - Deleted section: {$section->handle}\n");
                    $count++;
                } else {
                    $this->stdout("  ! Failed to delete section: {$section->handle}\n");
                }
            }
            $this->stdout("  Removed $count sections\n");
        } catch (\Exception $e) {
            $this->stdout("  Error: " . $e->getMessage() . "\n");
        }
    }

    private function wipeEntryTypes(): void
    {
        $this->stdout("Removing entry types...\n");
        $entryTypes = Craft::$app->getEntries()->getAllEntryTypes();
        $count = 0;

        foreach ($entryTypes as $et) {
            try {
                if (Craft::$app->getEntries()->deleteEntryType($et)) {
                    $count++;
                }
            } catch (\Exception $e) {
                $this->stdout("  ! Failed to delete entry type '{$et->handle}': " . $e->getMessage() . "\n");
            }
        }

        $this->stdout("  Removed $count entry types\n");
    }

    private function wipeMatrixFields(): void
    {
        $this->stdout("Removing matrix fields...\n");
        $fields = Craft::$app->getFields()->getAllFields();
        $count = 0;

        foreach ($fields as $field) {
            if ($field instanceof \craft\fields\Matrix) {
                if (Craft::$app->getFields()->deleteField($field)) {
                    $count++;
                }
            }
        }

        $this->stdout("  Removed $count matrix fields\n");
    }

    private function wipeFields(): void
    {
        $this->stdout("Removing fields...\n");
        $fields = Craft::$app->getFields()->getAllFields();
        $count = 0;

        foreach ($fields as $field) {
            try {
                if (Craft::$app->getFields()->deleteField($field)) {
                    $count++;
                }
            } catch (\Exception $e) {
                $this->stdout("  ! Failed to delete field '{$field->handle}': " . $e->getMessage() . "\n");
            }
        }

        $this->stdout("  Removed $count fields\n");
    }

    private function wipeGlobalSets(): void
    {
        $this->stdout("Removing global sets...\n");

        try {
            $db = Craft::$app->getDb();

            // Delete via Craft API first (handles project config, field layout cleanup, etc.)
            $primarySiteId = Craft::$app->getSites()->getPrimarySite()->id;
            $sets = \craft\elements\GlobalSet::find()->siteId($primarySiteId)->all();
            $count = 0;
            foreach ($sets as $set) {
                if (Craft::$app->getGlobals()->deleteSet($set)) {
                    $count++;
                }
            }

            // Hard-delete any remaining GlobalSet elements (soft-deleted or missed by API)
            $purged = $db->createCommand(
                "DELETE FROM {{%elements}} WHERE [[type]] = 'craft\\\\elements\\\\GlobalSet' AND [[dateDeleted]] IS NOT NULL"
            )->execute();
            if ($purged > 0) {
                $this->stdout("  - Purged $purged soft-deleted GlobalSet element rows\n");
            }

            $this->stdout("  Removed $count global sets\n");
        } catch (\Exception $e) {
            $this->stdout("  Error: " . $e->getMessage() . "\n");
        }
    }

    private function wipeCategoryGroups(): void
    {
        $this->stdout("Removing category groups...\n");

        try {
            $groups = Craft::$app->getCategories()->getAllGroups();
            $count = 0;
            foreach ($groups as $group) {
                if (Craft::$app->getCategories()->deleteGroup($group)) {
                    $count++;
                }
            }
            $this->stdout("  Removed $count category groups\n");
        } catch (\Exception $e) {
            $this->stdout("  Error: " . $e->getMessage() . "\n");
        }
    }

    private function wipeTagGroups(): void
    {
        $this->stdout("Removing tag groups...\n");

        try {
            $groups = Craft::$app->getTags()->getAllTagGroups();
            $count = 0;
            foreach ($groups as $group) {
                if (Craft::$app->getTags()->deleteTagGroup($group)) {
                    $count++;
                }
            }
            $this->stdout("  Removed $count tag groups\n");
        } catch (\Exception $e) {
            $this->stdout("  Error: " . $e->getMessage() . "\n");
        }
    }

    private function wipeImageTransforms(): void
    {
        $this->stdout("Removing image transforms...\n");

        try {
            $transforms = Craft::$app->getImageTransforms()->getAllTransforms();
            $count = 0;
            foreach ($transforms as $transform) {
                Craft::$app->getImageTransforms()->deleteTransform($transform);
                $count++;
            }
            $this->stdout("  Removed $count image transforms\n");
        } catch (\Exception $e) {
            $this->stdout("  Error: " . $e->getMessage() . "\n");
        }
    }

    private function wipeVolumes(): void
    {
        $this->stdout("Removing volumes...\n");

        try {
            $volumes = Craft::$app->getVolumes()->getAllVolumes();
            $count = 0;
            foreach ($volumes as $volume) {
                if (Craft::$app->getVolumes()->deleteVolume($volume)) {
                    $this->stdout("  - Deleted volume: {$volume->handle}\n");
                    $count++;
                } else {
                    $this->stdout("  ! Failed to delete volume: {$volume->handle}\n");
                }
            }
            $this->stdout("  Removed $count volumes\n");
        } catch (\Exception $e) {
            $this->stdout("  Error: " . $e->getMessage() . "\n");
        }
    }

    /**
     * Hard-delete all soft-deleted records left behind by Craft's soft-delete pattern.
     * This prevents ghost entry types/sections from accumulating across wipe+reinstall cycles.
     */
    private function hardDeleteSoftDeleted(): void
    {
        $this->stdout("Purging soft-deleted records...\n");

        try {
            $db = Craft::$app->getDb();
            $tables = ['entrytypes', 'fieldlayouts', 'fields', 'volumes', 'sections', 'categorygroups', 'taggroups', 'globalsets', 'imagetransforms'];
            $totalPurged = 0;

            foreach ($tables as $table) {
                $fullTable = "{{%$table}}";
                try {
                    $count = $db->createCommand("DELETE FROM $fullTable WHERE [[dateDeleted]] IS NOT NULL")->execute();
                    if ($count > 0) {
                        $this->stdout("  - Purged $count soft-deleted rows from $table\n");
                        $totalPurged += $count;
                    }
                } catch (\Exception $e) {
                    // Table may not have dateDeleted column, skip silently
                }
            }

            // Hard-delete all soft-deleted elements (entries, assets, matrix blocks, etc.)
            // These accumulate across wipe+reinstall cycles because Craft soft-deletes
            // entries when sections are deleted rather than hard-deleting them.
            $count = $db->createCommand(
                "DELETE FROM {{%elements}} WHERE [[dateDeleted]] IS NOT NULL"
            )->execute();
            if ($count > 0) {
                $this->stdout("  - Purged $count soft-deleted rows from elements\n");
                $totalPurged += $count;
            }

            $this->stdout("  Purged $totalPurged total soft-deleted records\n");
        } catch (\Exception $e) {
            $this->stdout("  Error: " . $e->getMessage() . "\n");
        }
    }

    /**
     * Delete any wb-prefixed project config YAML entries left behind after
     * field/section/entrytype deletion. Craft sometimes keeps YAML files on
     * disk even after deleteField() / deleteSection() because of project-config
     * write timing — these stale files cause installFieldFromTemplate() to
     * find "existing" fields and skip fresh creation from JSON.
     */
    private function purgeProjectConfigEntries(): void
    {
        $this->stdout("Purging wb-prefixed project config entries...\n");

        $projectConfigPath = Craft::$app->getPath()->getProjectConfigPath();
        if (!$projectConfigPath || !is_dir($projectConfigPath)) {
            return;
        }

        $dirs = ['fields', 'entrytypes', 'sections', 'globalSets', 'imageTransforms'];
        $purged = 0;

        foreach ($dirs as $dir) {
            $path = $projectConfigPath . DIRECTORY_SEPARATOR . $dir;
            if (!is_dir($path)) {
                continue;
            }
            foreach (glob($path . DIRECTORY_SEPARATOR . 'wb*--*.yaml') ?: [] as $file) {
                if (@unlink($file)) {
                    $purged++;
                }
            }
        }

        if ($purged > 0) {
            $this->stdout("  Purged $purged wb-prefixed project config YAML file(s)\n");
        } else {
            $this->stdout("  Nothing to purge\n");
        }
    }

    private function wipeFilesystems(): void
    {
        $this->stdout("Removing filesystems...\n");

        try {
            $projectConfig = Craft::$app->getProjectConfig();
            $fsConfigs = $projectConfig->get('fs') ?? [];
            $count = 0;
            foreach (array_keys($fsConfigs) as $handle) {
                $projectConfig->remove("fs.$handle");
                $this->stdout("  - Deleted filesystem: $handle\n");
                $count++;
            }
            $this->stdout("  Removed $count filesystems\n");
        } catch (\Exception $e) {
            $this->stdout("  Error: " . $e->getMessage() . "\n");
        }
    }
}
