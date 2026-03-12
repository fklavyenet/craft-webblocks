<?php

namespace fklavyenet\webblocks\services;

use Craft;
use craft\base\Component;
use craft\base\FieldInterface;
use craft\elements\Category;
use craft\elements\Entry;
use craft\elements\GlobalSet;
use craft\fieldlayoutelements\CustomField;
use craft\fieldlayoutelements\entries\EntryTitleField;
use craft\fields\Matrix;
use craft\helpers\FileHelper;
use craft\models\CategoryGroup;
use craft\models\CategoryGroup_SiteSettings;
use craft\models\EntryType;
use craft\models\FieldLayout;
use craft\models\FieldLayoutTab;
use craft\models\ImageTransform;
use craft\models\Section;
use craft\models\Section_SiteSettings;
use craft\models\Site;
use craft\models\SiteGroup;
use craft\models\TagGroup;
use craft\models\Volume;
use craft\fs\Local;

/**
 * Creates the full WebBlocks content schema on a fresh Craft 5 site.
 *
 * All schema definitions are read from JSON files in src/wbComponents/.
 * To add or modify schema elements, edit the JSON files — no PHP changes needed.
 *
 * Installation order:
 *   1. Filesystems (local storage directories)
 *   2. Volumes (asset sources)
 *   3. Image transforms (responsive image sizes)
 *   4. Simple fields (from JSON definitions)
 *   5. Entry types (for Matrix blocks and sections)
 *   6. Matrix fields (require entry types)
 *   7. Update entry type layouts (add Matrix fields created in step 6)
 *   8. Sections (singles, channels, structures)
 *   9. Global sets (site settings, theme, footer, etc.)
 *  10. Category groups
 *  11. Tag groups
 */
class InstallService extends Component
{
    private FieldInstallService $fieldService;

    /** @var array<string, FieldInterface> handle => saved field */
    private array $fieldCache = [];

    /** @var array<string, EntryType> handle => saved entry type */
    private array $entryTypeCache = [];

    public function init(): void
    {
        parent::init();
        $this->fieldService = new FieldInstallService();
    }

    // =========================================================================
    // Public API
    // =========================================================================

    public function install(): void
    {
        Craft::info('WebBlocks: Starting schema installation...', __METHOD__);

        $this->installFilesystems();
        $this->installVolumes();
        $this->installTransforms();
        $this->installSimpleFields();
        $this->installEntryTypes();
        $this->cacheExistingEntryTypes();
        $this->installMatrixFields();
        $this->entryTypeCache = [];
        $this->cacheExistingEntryTypes();
        $this->updateEntryTypeMatrixFields();
        $this->installSites();
        $this->installSections();
        $this->installGlobalSets();
        $this->installCategories();
        $this->installTags();

        Craft::info('WebBlocks: Schema installation complete.', __METHOD__);
    }

    public function uninstall(): void
    {
        Craft::info('WebBlocks: Plugin uninstalled.', __METHOD__);
    }

    // =========================================================================
    // 1. Filesystems
    // =========================================================================

    private function installFilesystems(): void
    {
        foreach ($this->loadComponents('filesystems') as $def) {
            $handle = $def['handle'];

            if (Craft::$app->getProjectConfig()->get("fs.$handle")) {
                continue;
            }

            Craft::$app->getProjectConfig()->set("fs.$handle", [
                'name' => $def['name'],
                'type' => Local::class,
                'hasUrls' => true,
                'url' => $def['url'],
                'settings' => ['path' => $def['path']],
            ]);

            $realPath = Craft::getAlias($def['path']);
            if ($realPath && !is_dir($realPath)) {
                FileHelper::createDirectory($realPath);
            }
        }
    }

    // =========================================================================
    // 2. Volumes
    // =========================================================================

    private function installVolumes(): void
    {
        foreach ($this->loadComponents('volumes') as $def) {
            $handle = $def['handle'];

            if (Craft::$app->getVolumes()->getVolumeByHandle($handle)) {
                continue;
            }

            $volume = new Volume();
            $volume->name = $def['name'];
            $volume->handle = $handle;

            $volume->setFsHandle($def['fs']);
            $volume->subpath = $def['subpath'] ?? '';

            if (!empty($def['transformFs'])) {
                $volume->setTransformFsHandle($def['transformFs']);
                $volume->transformSubpath = $def['transformSubpath'] ?? null;
            }

            $volume->titleTranslationMethod = 'site';

            $volume->setFieldLayout(new FieldLayout(['type' => \craft\elements\Asset::class]));

            if (!Craft::$app->getVolumes()->saveVolume($volume)) {
                Craft::error("Failed to save volume '$handle': " . implode(', ', $volume->getFirstErrors()), __METHOD__);
            }
        }
    }

    // =========================================================================
    // 3. Image Transforms
    // =========================================================================

    private function installTransforms(): void
    {
        foreach ($this->loadComponents('imagetransforms') as $def) {
            $handle = $def['handle'];

            if (Craft::$app->getImageTransforms()->getTransformByHandle($handle)) {
                continue;
            }

            $transform = new ImageTransform();
            $transform->name = $def['name'];
            $transform->handle = $handle;
            $transform->width = $def['width'] ?? null;
            $transform->height = $def['height'] ?? null;
            $transform->mode = $def['mode'] ?? 'crop';
            $transform->format = null;
            $transform->quality = null;
            $transform->interlace = 'none';

            if (!Craft::$app->getImageTransforms()->saveTransform($transform)) {
                Craft::error("Failed to save transform '$handle'", __METHOD__);
            }
        }
    }

    // =========================================================================
    // 4. Simple (non-Matrix) Fields
    // =========================================================================

    private function installSimpleFields(): void
    {
        $wbComponentsPath = $this->getWbComponentsPath();
        if (!$wbComponentsPath) {
            Craft::warning('WebBlocks: No fields directory found', __METHOD__);
            return;
        }

        $fieldsPath = $wbComponentsPath . 'fields' . DIRECTORY_SEPARATOR;
        if (!is_dir($fieldsPath)) {
            Craft::warning('WebBlocks: No fields directory found', __METHOD__);
            return;
        }

        $files = glob($fieldsPath . '*.json');
        foreach ($files as $file) {
            $template = json_decode(file_get_contents($file), true);
            if (!$template) {
                continue;
            }

            $field = $this->fieldService->installFieldFromTemplate($template);
            if ($field) {
                $this->fieldCache[$field->handle] = $field;
            }
        }

        $this->cacheExistingFields();
    }

    // =========================================================================
    // 5. Entry Types
    // =========================================================================

    private function installEntryTypes(): void
    {
        Craft::info('Starting entry types installation...', __METHOD__);
        
        foreach ($this->loadComponents('entrytypes') as $def) {
            $handle = $def['handle'];

            Craft::info("Processing entry type: $handle", __METHOD__);
            
            // Check if already exists
            foreach (Craft::$app->getEntries()->getAllEntryTypes() as $et) {
                if ($et->handle === $handle) {
                    $this->entryTypeCache[$handle] = $et;
                    continue 2;
                }
            }

            $entryType = new EntryType();
            $entryType->name = $def['name'];
            $entryType->handle = $handle;
            $entryType->showSlugField = $def['showSlugField'] ?? true;
            $entryType->showStatusField = $def['showStatusField'] ?? true;

            if (!empty($def['tabs'])) {
                // Entry type with tabs — use tabs from JSON
                $entryType->hasTitleField = $def['hasTitleField'] ?? true;
                $entryType->titleTranslationMethod = $def['titleTranslationMethod'] ?? 'site';
                if (!($def['hasTitleField'] ?? true) && !empty($def['titleFormat'])) {
                    $entryType->titleFormat = $def['titleFormat'];
                }

                $layout = new FieldLayout(['type' => Entry::class]);
                $tabs = [];

                foreach ($def['tabs'] ?? [] as $tabIndex => $tabDef) {
                    $tab = new FieldLayoutTab(['name' => $tabDef['name']]);
                    $tab->setLayout($layout);

                    $elements = [];

                    // Add TitleField to the first tab when hasTitleField is true
                    if ($tabIndex === 0 && ($def['hasTitleField'] ?? true)) {
                        $elements[] = new EntryTitleField();
                    }

                    foreach ($tabDef['fields'] ?? [] as $fieldDef) {
                        // Support both plain handle strings and {handle, label} objects
                        if (is_array($fieldDef)) {
                            $fieldHandle  = $fieldDef['handle'];
                            $labelOverride = $fieldDef['label'] ?? null;
                        } else {
                            $fieldHandle  = $fieldDef;
                            $labelOverride = null;
                        }
                        $field = $this->getField($fieldHandle);
                        if ($field) {
                            $element = new CustomField($field);
                            if ($labelOverride !== null) {
                                $element->label = $labelOverride;
                            }
                            $elements[] = $element;
                        }
                    }
                    if (!empty($elements)) {
                        $tab->setElements($elements);
                    }
                    $tabs[] = $tab;
                }

                $layout->setTabs($tabs);
            } elseif (!empty($def['fields'])) {
                // Matrix block entry type — no title field, single Content tab
                $entryType->hasTitleField = false;
                $entryType->titleFormat = $def['titleFormat'] ?? '{type.name}';

                $layout = new FieldLayout(['type' => Entry::class]);
                $tab = new FieldLayoutTab(['name' => 'Content']);
                $tab->setLayout($layout);

                $elements = [];
                foreach ($def['fields'] ?? [] as $fieldHandle) {
                    $field = $this->getField($fieldHandle);
                    if ($field) {
                        $elements[] = new CustomField($field);
                    }
                }
                if (!empty($elements)) {
                    $tab->setElements($elements);
                }
                $layout->setTabs([$tab]);
            }

            $entryType->setFieldLayout($layout);

            if (!Craft::$app->getEntries()->saveEntryType($entryType)) {
                Craft::error("Failed to save entry type '$handle': " . implode(', ', $entryType->getFirstErrors()), __METHOD__);
                continue;
            }

            Craft::info("Created entry type: $handle", __METHOD__);
            $this->entryTypeCache[$handle] = $entryType;
        }
    }

    // =========================================================================
    // 6. Matrix Fields
    // =========================================================================

    private function installMatrixFields(): void
    {
        $this->cacheExistingFields();

        foreach ($this->loadComponents('matrixfields') as $def) {
            $handle = $def['handle'];

            if ($this->getField($handle)) {
                continue;
            }

            $field = new Matrix();
            $field->name = $def['name'];
            $field->handle = $handle;

            $entryTypes = [];
            foreach ($def['entryTypes'] ?? [] as $etHandle) {
                $et = $this->resolveEntryType($etHandle);
                if ($et) {
                    $entryTypes[] = $et->uid;
                } else {
                    Craft::warning("Entry type '$etHandle' not found for Matrix field '$handle'", __METHOD__);
                }
            }

            $field->setEntryTypes($entryTypes);

            if (!empty($def['maxEntries'])) {
                $field->maxEntries = (int) $def['maxEntries'];
            }

            if (!Craft::$app->getFields()->saveField($field)) {
                Craft::error("Failed to save Matrix field '$handle': " . implode(', ', $field->getFirstErrors()), __METHOD__);
                continue;
            }

            $this->fieldCache[$handle] = $field;
        }
    }

    // =========================================================================
    // 7. Update Entry Type Layouts (add Matrix fields)
    // =========================================================================

    /**
     * After Matrix fields are created, update entry type field layouts to
     * include the Matrix fields defined in each entry type's "matrixFields" key.
     */
    private function updateEntryTypeMatrixFields(): void
    {
        foreach ($this->loadComponents('entrytypes') as $def) {
            $matrixFieldHandles = $def['matrixFields'] ?? [];
            if (empty($matrixFieldHandles)) {
                continue;
            }

            $handle = $def['handle'];
            $et = $this->resolveEntryType($handle);
            if (!$et) {
                continue;
            }

            $layout = $et->getFieldLayout();
            $tabs = $layout->getTabs();

            if (empty($tabs)) {
                $newTab = new FieldLayoutTab(['name' => 'Content']);
                $newTab->setLayout($layout);
                $tabs = [$newTab];
            }

            // Add Matrix fields to the first tab (Content)
            $existingElements = $tabs[0]->getElements();
            $modified = false;

            foreach ($matrixFieldHandles as $fieldHandle) {
                $field = $this->getField($fieldHandle);
                if (!$field) {
                    continue;
                }

                // Check if already in layout
                $alreadyPresent = false;
                foreach ($existingElements as $el) {
                    if ($el instanceof CustomField && $el->getField()->handle === $fieldHandle) {
                        $alreadyPresent = true;
                        break;
                    }
                }

                if (!$alreadyPresent) {
                    $existingElements[] = new CustomField($field);
                    $modified = true;
                }
            }

            if ($modified) {
                $tabs[0]->setElements($existingElements);
                $layout->setTabs($tabs);
                $et->setFieldLayout($layout);

                if (!Craft::$app->getEntries()->saveEntryType($et)) {
                    $errors = $et->getErrors();
                    $layoutErrors = $et->getFieldLayout()->getErrors();
                    Craft::error("Failed to update entry type '$handle' layout: " . json_encode(array_merge($errors, $layoutErrors)), __METHOD__);
                }
            }
        }
    }

    // =========================================================================
    // 8. Additional Sites (TR + DE)
    // =========================================================================

    /**
     * Additional language sites to create alongside the primary EN site.
     * Each entry: [handle, name, language, baseUrl, uriPrefix]
     * uriPrefix is prepended to section uriFormats for this site (e.g. 'tr/').
     */
    private const EXTRA_SITES = [
        ['handle' => 'tr', 'name' => 'Restaurant (TR)', 'language' => 'tr',    'baseUrl' => '@web/tr/',  'uriPrefix' => 'tr/'],
        ['handle' => 'de', 'name' => 'Restaurant (DE)', 'language' => 'de-DE', 'baseUrl' => '@web/de/',  'uriPrefix' => 'de/'],
    ];

    private function installSites(): void
    {
        $sitesService = Craft::$app->getSites();
        $primarySite  = $sitesService->getPrimarySite();

        // Ensure the primary site group exists; use it for extra sites too
        $groups = $sitesService->getAllGroups();
        $group  = $groups[0] ?? null;

        if (!$group) {
            $group = new SiteGroup();
            $group->setName('Default');
            if (!$sitesService->saveGroup($group)) {
                Craft::error('WebBlocks: Failed to save default site group', __METHOD__);
                return;
            }
        }

        foreach (self::EXTRA_SITES as $def) {
            $handle = $def['handle'];

            if ($sitesService->getSiteByHandle($handle)) {
                Craft::info("WebBlocks: Site '$handle' already exists, skipping.", __METHOD__);
                continue;
            }

            $site = new Site();
            $site->groupId = $primarySite->groupId ?? $group->id;
            $site->handle  = $handle;
            $site->primary = false;
            $site->hasUrls = true;
            $site->setName($def['name']);
            $site->setLanguage($def['language']);
            $site->setBaseUrl($def['baseUrl']);

            if (!$sitesService->saveSite($site)) {
                Craft::error("WebBlocks: Failed to save site '$handle': " . implode(', ', $site->getFirstErrors()), __METHOD__);
            } else {
                Craft::info("WebBlocks: Created site '$handle'", __METHOD__);
            }
        }
    }

    // =========================================================================
    // 9. Sections
    // =========================================================================

    private function installSections(): void
    {
        $sitesService = Craft::$app->getSites();
        $primarySite  = $sitesService->getPrimarySite();

        foreach ($this->loadComponents('sections') as $def) {
            $handle = $def['handle'];

            $section = Craft::$app->getEntries()->getSectionByHandle($handle);
            $isNew = false;

            if (!$section) {
                $isNew = true;
                $section = new Section();
                $section->name = $def['name'];
                $section->handle = $handle;
                $section->type = $def['type'];

                if (!empty($def['maxLevels'])) {
                    $section->maxLevels = (int) $def['maxLevels'];
                }

                if (isset($def['enableVersioning'])) {
                    $section->enableVersioning = (bool) $def['enableVersioning'];
                }

                $allSiteSettings = [];

                // Primary site
                $primarySettings = new Section_SiteSettings();
                $primarySettings->siteId = $primarySite->id;
                $primarySettings->hasUrls = !empty($def['uriFormat']);
                $primarySettings->uriFormat = $def['uriFormat'] ?: null;
                $primarySettings->template = $def['template'] ?: null;
                $primarySettings->enabledByDefault = true;
                $allSiteSettings[$primarySite->id] = $primarySettings;

                // Extra sites (TR, DE)
                foreach (self::EXTRA_SITES as $extraDef) {
                    $extraSite = $sitesService->getSiteByHandle($extraDef['handle']);
                    if (!$extraSite) {
                        continue;
                    }
                    $extraSettings = new Section_SiteSettings();
                    $extraSettings->siteId = $extraSite->id;
                    $extraSettings->hasUrls = !empty($def['uriFormat']);
                    $extraSettings->uriFormat = $def['uriFormat'] ?: null;
                    $extraSettings->template = $def['template'] ?: null;
                    $extraSettings->enabledByDefault = true;
                    $allSiteSettings[$extraSite->id] = $extraSettings;
                }

                $section->setSiteSettings($allSiteSettings);
            }

            // Always set entry types (both new and existing sections)
            $entryTypes = [];
            foreach ($def['entryTypes'] ?? [] as $etHandle) {
                $et = $this->resolveEntryType($etHandle);
                if ($et) {
                    $entryTypes[] = $et;
                } else {
                    Craft::warning("Entry type '$etHandle' not found for section '$handle'", __METHOD__);
                }
            }

            if (!empty($entryTypes)) {
                $section->setEntryTypes($entryTypes);
            }

            if (!Craft::$app->getEntries()->saveSection($section)) {
                Craft::error("Failed to save section '$handle': " . implode(', ', $section->getFirstErrors()), __METHOD__);
            } else {
                Craft::info(($isNew ? 'Created' : 'Updated') . " section '$handle' with " . count($entryTypes) . " entry types", __METHOD__);
            }
        }
    }

    // =========================================================================
    // 9. Global Sets
    // =========================================================================

    private function installGlobalSets(): void
    {
        foreach ($this->loadComponents('globalsets') as $def) {
            $handle = $def['handle'];

            // Check if already exists (use primary site to avoid console-context current-site issues)
            $primarySiteId = Craft::$app->getSites()->getPrimarySite()->id;
            if (Craft::$app->getGlobals()->getSetByHandle($handle, $primarySiteId)) {
                continue;
            }

            $globalSet = new GlobalSet();
            $globalSet->name = $def['name'];
            $globalSet->handle = $handle;

            $layout = new FieldLayout(['type' => GlobalSet::class]);
            $tab = new FieldLayoutTab(['name' => 'Content']);
            $tab->setLayout($layout);

            $elements = [];
            foreach ($def['fields'] ?? [] as $fieldHandle) {
                $field = $this->getField($fieldHandle);
                if ($field) {
                    $elements[] = new CustomField($field);
                }
            }
            if (!empty($elements)) {
                $tab->setElements($elements);
            }
            $layout->setTabs([$tab]);
            $globalSet->setFieldLayout($layout);

            if (!Craft::$app->getGlobals()->saveSet($globalSet)) {
                Craft::error("Failed to save global set '$handle': " . implode(', ', $globalSet->getFirstErrors()), __METHOD__);
            }
        }
    }

    // =========================================================================
    // 10. Categories
    // =========================================================================

    private function installCategories(): void
    {
        $sitesService = Craft::$app->getSites();
        $primarySite  = $sitesService->getPrimarySite();

        foreach ($this->loadComponents('categorygroups') as $def) {
            $handle = $def['handle'];

            if (Craft::$app->getCategories()->getGroupByHandle($handle)) {
                continue;
            }

            $group = new CategoryGroup();
            $group->name = $def['name'];
            $group->handle = $handle;
            $group->maxLevels = $def['maxLevels'] ?? null;

            $allSiteSettings = [];

            // Primary site
            $primarySettings = new CategoryGroup_SiteSettings();
            $primarySettings->siteId = $primarySite->id;
            $primarySettings->uriFormat = $def['uriFormat'] ?? 'category/{slug}';
            $primarySettings->template = $def['template'] ?? '';
            $primarySettings->hasUrls = true;
            $allSiteSettings[$primarySite->id] = $primarySettings;

            // Extra sites (TR, DE)
            foreach (self::EXTRA_SITES as $extraDef) {
                $extraSite = $sitesService->getSiteByHandle($extraDef['handle']);
                if (!$extraSite) {
                    continue;
                }
                $extraSettings = new CategoryGroup_SiteSettings();
                $extraSettings->siteId = $extraSite->id;
                $extraSettings->uriFormat = $def['uriFormat'] ?? 'category/{slug}';
                $extraSettings->template = $def['template'] ?? '';
                $extraSettings->hasUrls = true;
                $allSiteSettings[$extraSite->id] = $extraSettings;
            }

            $group->setSiteSettings($allSiteSettings);

            $layout = new FieldLayout(['type' => Category::class]);
            $tab = new FieldLayoutTab(['name' => 'Content']);
            $tab->setLayout($layout);

            $elements = [];
            foreach ($def['fields'] ?? [] as $fieldHandle) {
                $field = $this->getField($fieldHandle);
                if ($field) {
                    $elements[] = new CustomField($field);
                }
            }
            if (!empty($elements)) {
                $tab->setElements($elements);
            }
            $layout->setTabs([$tab]);
            $group->setFieldLayout($layout);

            if (!Craft::$app->getCategories()->saveGroup($group)) {
                Craft::error("Failed to save category group '$handle': " . implode(', ', $group->getFirstErrors()), __METHOD__);
            }
        }
    }

    // =========================================================================
    // 11. Tags
    // =========================================================================

    private function installTags(): void
    {
        foreach ($this->loadComponents('taggroups') as $def) {
            $handle = $def['handle'];

            // Check if already exists
            foreach (Craft::$app->getTags()->getAllTagGroups() as $g) {
                if ($g->handle === $handle) {
                    continue 2;
                }
            }

            $group = new TagGroup();
            $group->name = $def['name'];
            $group->handle = $handle;

            $layout = new FieldLayout(['type' => \craft\elements\Tag::class]);
            $tab = new FieldLayoutTab(['name' => 'Content']);
            $tab->setLayout($layout);

            $elements = [];
            foreach ($def['fields'] ?? [] as $fieldHandle) {
                $field = $this->getField($fieldHandle);
                if ($field) {
                    $elements[] = new CustomField($field);
                }
            }
            if (!empty($elements)) {
                $tab->setElements($elements);
            }
            $layout->setTabs([$tab]);
            $group->setFieldLayout($layout);

            if (!Craft::$app->getTags()->saveTagGroup($group)) {
                Craft::error("Failed to save tag group '$handle': " . implode(', ', $group->getFirstErrors()), __METHOD__);
            }
        }
    }

    // =========================================================================
    // Component loader
    // =========================================================================

    /**
     * Load all JSON definitions from a wbComponents subdirectory.
     *
     * @return array[] Array of decoded JSON definitions
     */
    private function loadComponents(string $type): array
    {
        $components = [];

        $dir = $this->getWbComponentsPath();
        if ($dir) {
            $path = $dir . $type . DIRECTORY_SEPARATOR;
            if (is_dir($path)) {
                foreach (glob($path . '*.json') as $file) {
                    $data = json_decode(file_get_contents($file), true);
                    if ($data) {
                        $components[] = $data;
                    }
                }
            }
        }

        return $components;
    }

    /**
     * Get the path to the wbComponents directory (wb-prefixed components).
     */
    private function getWbComponentsPath(): ?string
    {
        $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'wbComponents' . DIRECTORY_SEPARATOR;
        if (!is_dir($path)) {
            return null;
        }
        return $path;
    }

    // =========================================================================
    // Field & entry type helpers
    // =========================================================================

    private function cacheExistingFields(): void
    {
        foreach (Craft::$app->getFields()->getAllFields() as $field) {
            $this->fieldCache[$field->handle] = $field;
        }
    }

    private function cacheExistingEntryTypes(): void
    {
        foreach (Craft::$app->getEntries()->getAllEntryTypes() as $et) {
            $this->entryTypeCache[$et->handle] = $et;
        }
    }

    private function getField(string $handle): ?FieldInterface
    {
        if (isset($this->fieldCache[$handle])) {
            return $this->fieldCache[$handle];
        }
        $field = Craft::$app->getFields()->getFieldByHandle($handle);
        if ($field) {
            $this->fieldCache[$handle] = $field;
        }
        return $field;
    }

    /**
     * Resolve an entry type by handle, checking cache first then database.
     */
    private function resolveEntryType(string $handle): ?EntryType
    {
        if (isset($this->entryTypeCache[$handle])) {
            return $this->entryTypeCache[$handle];
        }

        foreach (Craft::$app->getEntries()->getAllEntryTypes() as $et) {
            if ($et->handle === $handle) {
                $this->entryTypeCache[$handle] = $et;
                return $et;
            }
        }

        return null;
    }
}
