<?php

namespace fklavyenet\webblocks\services;

use Craft;
use craft\base\Component;
use craft\helpers\DateTimeHelper;
use craft\helpers\Db;
use fklavyenet\webblocks\services\DeprecatedFieldService;
use Throwable;

/**
 * ComponentMigrator — applies component migration step files to bring
 * installed WebBlocks components up to the version declared in their JSON.
 *
 * Migration files live in:
 *   src/componentMigrations/{type}/{handle}/{from}_to_{to}.php
 *
 * Each file returns a PHP array following the DSL documented in
 * src/componentMigrations/_example/1_to_2.php.
 *
 * Typical usage:
 *   // Dry-run (no DB changes)
 *   $report = (new ComponentMigrator())->migrateAll(dryRun: true);
 *
 *   // Apply all pending migrations
 *   $report = (new ComponentMigrator())->migrateAll(dryRun: false);
 *
 *   // Migrate a single component
 *   $report = (new ComponentMigrator())->migrateComponent('wbHero', 'entrytypes');
 *
 * Report shape (returned by every public method):
 * [
 *   'applied'              => [ ['handle', 'type', 'from', 'to', 'steps'] ],
 *   'skipped'              => [ ['handle', 'type', 'reason'] ],
 *   'warnings'             => [ string ],
 *   'manualReviewRequired' => [ ['handle', 'type', 'action', 'message'] ],
 *   'errors'               => [ ['handle', 'type', 'message'] ],
 *   'dryRun'               => bool,
 * ]
 */
class ComponentMigrator extends Component
{
    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Migrate all components that need a version bump.
     *
     * Uses ComponentDiffService to identify candidates, then applies
     * each component's migration chain in order.
     */
    public function migrateAll(bool $dryRun = false): array
    {
        $diff   = (new ComponentDiffService())->diff();
        $report = $this->emptyReport($dryRun);

        foreach ($diff['components'] as $row) {
            if ($row['status'] === ComponentDiffService::STATUS_VERSION_BUMP) {
                $result = $this->migrateComponent(
                    $row['handle'],
                    $row['type'],
                    $dryRun
                );
                $report = $this->mergeReports($report, $result);
            } elseif ($row['status'] === ComponentDiffService::STATUS_NEW) {
                // New component: just record it in the state table
                if (!$dryRun) {
                    (new ComponentStateService())->recordAll();
                }
                $report['skipped'][] = [
                    'handle' => $row['handle'],
                    'type'   => $row['type'],
                    'reason' => 'New component — recorded in state table.',
                ];
            }
        }

        $this->logReport($report);
        return $report;
    }

    /**
     * Migrate a single component from its current DB version to the JSON version.
     *
     * @param string $handle     Component handle (e.g. 'wbHero')
     * @param string $type       Component type (e.g. 'entrytypes')
     * @param bool   $dryRun     If true, resolve and validate the chain but make no changes
     */
    public function migrateComponent(string $handle, string $type, bool $dryRun = false): array
    {
        $report = $this->emptyReport($dryRun);

        // ── Load current DB state ────────────────────────────────────────────
        $state = (new ComponentStateService())->getState($handle, $type);
        if (!$state) {
            $report['skipped'][] = [
                'handle' => $handle,
                'type'   => $type,
                'reason' => 'No DB state found. Run install first.',
            ];
            return $report;
        }

        // ── Load target version from JSON ────────────────────────────────────
        $jsonVersion = $this->readJsonVersion($handle, $type);
        if ($jsonVersion === null) {
            $report['skipped'][] = [
                'handle' => $handle,
                'type'   => $type,
                'reason' => 'JSON file not found or missing version.',
            ];
            return $report;
        }

        $fromVersion = (int) $state['installedVersion'];
        $toVersion   = $jsonVersion;

        if ($fromVersion >= $toVersion) {
            $report['skipped'][] = [
                'handle' => $handle,
                'type'   => $type,
                'reason' => "Already at v$fromVersion — nothing to migrate.",
            ];
            return $report;
        }

        // ── Resolve migration chain ──────────────────────────────────────────
        $chain = $this->resolveMigrationPath($handle, $type, $fromVersion, $toVersion);

        if (empty($chain)) {
            $report['warnings'][] = "$type/$handle: version bump v$fromVersion → v$toVersion but no migration files found in src/componentMigrations/$type/$handle/. State will be updated without applying steps.";

            if (!$dryRun) {
                $this->updateComponentState($handle, $type, $toVersion);
            }

            $report['applied'][] = [
                'handle' => $handle,
                'type'   => $type,
                'from'   => $fromVersion,
                'to'     => $toVersion,
                'steps'  => [],
                'note'   => 'No migration files — state version updated only.',
            ];
            return $report;
        }

        // ── Apply each step in the chain ─────────────────────────────────────
        $appliedSteps = [];
        foreach ($chain as $step) {
            $stepResult = $this->applyMigrationStep($step, $handle, $type, $dryRun);

            $appliedSteps[] = [
                'from'    => $step['from'],
                'to'      => $step['to'],
                'actions' => count($step['actions'] ?? []),
                'dryRun'  => $dryRun,
            ];

            // Collect manual review items and warnings from this step
            foreach ($stepResult['manualReviewRequired'] as $item) {
                $report['manualReviewRequired'][] = $item;
            }
            foreach ($stepResult['warnings'] as $w) {
                $report['warnings'][] = $w;
            }
            foreach ($stepResult['errors'] as $e) {
                $report['errors'][] = $e;
                // Abort chain on error
                Craft::error("ComponentMigrator: aborting chain for $type/$handle at step {$step['from']}→{$step['to']}: {$e['message']}", __METHOD__);
                return $report;
            }

            // After each step, update the intermediate version in the DB
            if (!$dryRun) {
                $this->updateComponentState($handle, $type, (int) $step['to']);
            }
        }

        $report['applied'][] = [
            'handle' => $handle,
            'type'   => $type,
            'from'   => $fromVersion,
            'to'     => $toVersion,
            'steps'  => $appliedSteps,
        ];

        return $report;
    }

    /**
     * Resolve the ordered list of migration step files for a component.
     *
     * Looks for files named {from}_to_{to}.php in the component's migration
     * directory and chains them in order from $fromVersion to $toVersion.
     *
     * Returns an array of loaded step definitions (PHP arrays), or [] if
     * no migration directory or files exist.
     *
     * @return array<int, array>  Ordered list of step definition arrays
     */
    public function resolveMigrationPath(string $handle, string $type, int $fromVersion, int $toVersion): array
    {
        $dir = $this->getMigrationDir($type, $handle);
        if (!$dir || !is_dir($dir)) {
            return [];
        }

        $chain   = [];
        $current = $fromVersion;

        while ($current < $toVersion) {
            $next = $current + 1;
            $file = $dir . "{$current}_to_{$next}.php";

            if (!file_exists($file)) {
                // Gap in the chain — warn but stop here
                Craft::warning(
                    "ComponentMigrator: missing migration file $file for $type/$handle.",
                    __METHOD__
                );
                break;
            }

            $step = require $file;
            if (!is_array($step) || empty($step['from']) || empty($step['to'])) {
                Craft::warning(
                    "ComponentMigrator: invalid migration file $file — must return array with 'from' and 'to' keys.",
                    __METHOD__
                );
                break;
            }

            $chain[] = $step;
            $current = $next;
        }

        return $chain;
    }

    /**
     * Apply a single migration step (one {from}_to_{to}.php file).
     *
     * Iterates over the step's 'actions' array and dispatches each action
     * to the appropriate handler. Returns a partial report for this step.
     */
    public function applyMigrationStep(array $step, string $handle, string $type, bool $dryRun = false): array
    {
        $result = [
            'manualReviewRequired' => [],
            'warnings'             => [],
            'errors'               => [],
        ];

        $actions         = $step['actions'] ?? [];
        $stepKey         = "$type/$handle v{$step['from']}→v{$step['to']}";
        $migrationSource = $stepKey;

        foreach ($actions as $action) {
            $actionType = $action['type'] ?? 'unknown';
            $risk       = $action['risk'] ?? null;

            // High-risk actions always go to manualReviewRequired
            if ($risk === 'high') {
                $result['manualReviewRequired'][] = [
                    'handle'  => $handle,
                    'type'    => $type,
                    'action'  => $actionType,
                    'message' => "[$stepKey] Action '$actionType' is marked risk=high. Apply manually after review.",
                ];
                continue;
            }

            if ($dryRun) {
                // In dry-run mode, just validate the action shape
                $validation = $this->validateAction($action, $stepKey);
                if ($validation) {
                    $result['warnings'][] = $validation;
                }
                continue;
            }

            // Apply the action
            try {
                $this->dispatchAction($action, $handle, $type, $result, $migrationSource);
            } catch (Throwable $e) {
                $result['errors'][] = [
                    'handle'  => $handle,
                    'type'    => $type,
                    'action'  => $actionType,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $result;
    }

    // -------------------------------------------------------------------------
    // Action dispatcher
    // -------------------------------------------------------------------------

    /**
     * Dispatch a single action definition to its handler.
     *
     * Each handler modifies Craft's schema via the appropriate service.
     * Handlers are intentionally kept simple — complex logic belongs in
     * dedicated helper methods below.
     */
    private function dispatchAction(array $action, string $handle, string $type, array &$result, string $migrationSource = ''): void
    {
        switch ($action['type']) {

            case 'renameField':
                $this->actionRenameField($action, $result);
                break;

            case 'addField':
                $this->actionAddField($action, $result);
                break;

            case 'removeField':
                // removeField with risk=high is already handled above (skipped)
                // Medium or no-risk: proceed
                $this->actionRemoveField($action, $result);
                break;

            case 'deprecateField':
                $this->actionDeprecateField($action, $result, $migrationSource);
                break;

            case 'updateFieldSettings':
                $this->actionUpdateFieldSettings($action, $result);
                break;

            case 'updateFieldLayout':
                $this->actionUpdateFieldLayout($action, $result);
                break;

            case 'addMatrixBlockType':
                $this->actionAddMatrixBlockType($action, $result);
                break;

            case 'removeMatrixBlockType':
                $this->actionRemoveMatrixBlockType($action, $result);
                break;

            case 'renameMatrixBlockType':
                $this->actionRenameMatrixBlockType($action, $result);
                break;

            case 'copyContent':
                $this->actionCopyContent($action, $result);
                break;

            case 'transformContent':
                $this->actionTransformContent($action, $result);
                break;

            default:
                $result['warnings'][] = "Unknown action type '{$action['type']}' — skipped.";
        }
    }

    // -------------------------------------------------------------------------
    // Action handlers
    // -------------------------------------------------------------------------

    private function actionRenameField(array $action, array &$result): void
    {
        $oldHandle = $action['oldHandle'] ?? null;
        $newHandle = $action['newHandle'] ?? null;

        if (!$oldHandle || !$newHandle) {
            $result['warnings'][] = "renameField: missing oldHandle or newHandle.";
            return;
        }

        $field = Craft::$app->getFields()->getFieldByHandle($oldHandle);
        if (!$field) {
            $result['warnings'][] = "renameField: field '$oldHandle' not found — skipping.";
            return;
        }

        $field->handle = $newHandle;
        if (!Craft::$app->getFields()->saveField($field)) {
            $errors = implode(', ', $field->getFirstErrors());
            throw new \RuntimeException("renameField: failed to rename '$oldHandle' → '$newHandle': $errors");
        }

        Craft::info("ComponentMigrator: renamed field '$oldHandle' → '$newHandle'.", __METHOD__);
    }

    private function actionAddField(array $action, array &$result): void
    {
        $handle     = $action['handle']     ?? null;
        $definition = $action['definition'] ?? null;

        if (!$handle || !$definition) {
            $result['warnings'][] = "addField: missing handle or definition path.";
            return;
        }

        // Skip if already exists
        if (Craft::$app->getFields()->getFieldByHandle($handle)) {
            $result['warnings'][] = "addField: field '$handle' already exists — skipping.";
            return;
        }

        $defPath = $this->getWbComponentsPath() . $definition;
        if (!file_exists($defPath)) {
            throw new \RuntimeException("addField: definition file not found: $defPath");
        }

        $def = json_decode(file_get_contents($defPath), true);
        if (!$def) {
            throw new \RuntimeException("addField: could not parse JSON definition: $defPath");
        }

        // Delegate to FieldInstallService which already knows how to build fields from JSON
        (new FieldInstallService())->installFieldFromDefinition($def);
        Craft::info("ComponentMigrator: added field '$handle'.", __METHOD__);
    }

    private function actionRemoveField(array $action, array &$result): void
    {
        $handle = $action['handle'] ?? null;
        if (!$handle) {
            $result['warnings'][] = "removeField: missing handle.";
            return;
        }

        $field = Craft::$app->getFields()->getFieldByHandle($handle);
        if (!$field) {
            $result['warnings'][] = "removeField: field '$handle' not found — skipping.";
            return;
        }

        if (!Craft::$app->getFields()->deleteField($field)) {
            throw new \RuntimeException("removeField: failed to delete field '$handle'.");
        }

        Craft::info("ComponentMigrator: removed field '$handle'.", __METHOD__);
    }

    private function actionDeprecateField(array $action, array &$result, string $migrationSource = ''): void
    {
        // Deprecation: remove the field from all field layouts but keep the field
        // and its data in the database. The field remains queryable/accessible.
        $handle = $action['handle'] ?? null;
        if (!$handle) {
            $result['warnings'][] = "deprecateField: missing handle.";
            return;
        }

        $field = Craft::$app->getFields()->getFieldByHandle($handle);
        if (!$field) {
            $result['warnings'][] = "deprecateField: field '$handle' not found — skipping.";
            return;
        }

        // Remove from all entry type field layouts
        foreach (Craft::$app->getEntries()->getAllEntryTypes() as $entryType) {
            $layout  = $entryType->getFieldLayout();
            $changed = false;
            foreach ($layout->getTabs() as $tab) {
                $elements = array_filter(
                    $tab->getElements(),
                    function ($el) use ($handle) {
                        if (!($el instanceof \craft\fieldlayoutelements\CustomField)) {
                            return true; // keep non-custom-field elements
                        }
                        try {
                            $f = $el->getField();
                        } catch (\Throwable $e) {
                            return true; // orphaned reference — leave it alone
                        }
                        return !($f && $f->handle === $handle);
                    }
                );
                if (count($elements) !== count($tab->getElements())) {
                    $tab->setElements(array_values($elements));
                    $changed = true;
                }
            }
            if ($changed) {
                Craft::$app->getEntries()->saveEntryType($entryType);
            }
        }

        // Record in deprecated fields tracking table
        (new DeprecatedFieldService())->markDeprecated($handle, $migrationSource ?: null);

        $result['warnings'][] = "deprecateField: '$handle' removed from layouts but data preserved. Clean up with: ddev craft webblocks/components/cleanup-deprecated";
        Craft::info("ComponentMigrator: deprecated field '$handle' (removed from layouts, data kept).", __METHOD__);
    }

    private function actionUpdateFieldSettings(array $action, array &$result): void
    {
        $handle   = $action['handle']   ?? null;
        $settings = $action['settings'] ?? null;

        if (!$handle || !$settings) {
            $result['warnings'][] = "updateFieldSettings: missing handle or settings.";
            return;
        }

        $field = Craft::$app->getFields()->getFieldByHandle($handle);
        if (!$field) {
            $result['warnings'][] = "updateFieldSettings: field '$handle' not found — skipping.";
            return;
        }

        foreach ($settings as $key => $value) {
            $field->$key = $value;
        }

        if (!Craft::$app->getFields()->saveField($field)) {
            $errors = implode(', ', $field->getFirstErrors());
            throw new \RuntimeException("updateFieldSettings: failed to update '$handle': $errors");
        }

        Craft::info("ComponentMigrator: updated settings on field '$handle'.", __METHOD__);
    }

    private function actionUpdateFieldLayout(array $action, array &$result): void
    {
        $entryTypeHandle = $action['entryType'] ?? null;
        $tabName         = $action['tab']       ?? null;
        $fieldHandles    = $action['fields']     ?? null;

        if (!$entryTypeHandle || !$tabName || !is_array($fieldHandles)) {
            $result['warnings'][] = "updateFieldLayout: missing entryType, tab, or fields.";
            return;
        }

        $entryType = Craft::$app->getEntries()->getEntryTypeByHandle($entryTypeHandle);
        if (!$entryType) {
            $result['warnings'][] = "updateFieldLayout: entry type '$entryTypeHandle' not found — skipping.";
            return;
        }

        $layout  = $entryType->getFieldLayout();
        $tabs    = $layout->getTabs();
        $updated = false;

        foreach ($tabs as $tab) {
            if ($tab->name !== $tabName) {
                continue;
            }

            // Build an ordered set of layout elements matching the new field order
            $existing = [];
            foreach ($tab->getElements() as $el) {
                if ($el instanceof \craft\fieldlayoutelements\CustomField) {
                    $f = $el->getField();
                    if ($f) {
                        $existing[$f->handle] = $el;
                    }
                }
            }

            $newElements = [];
            foreach ($fieldHandles as $fHandle) {
                if (isset($existing[$fHandle])) {
                    $newElements[] = $existing[$fHandle];
                }
            }
            // Append any existing elements not in the new list (preserve unknown)
            foreach ($existing as $fHandle => $el) {
                if (!in_array($fHandle, $fieldHandles, true)) {
                    $newElements[] = $el;
                }
            }

            $tab->setElements($newElements);
            $updated = true;
        }

        if ($updated) {
            Craft::$app->getEntries()->saveEntryType($entryType);
            Craft::info("ComponentMigrator: updated field layout for '$entryTypeHandle' tab '$tabName'.", __METHOD__);
        } else {
            $result['warnings'][] = "updateFieldLayout: tab '$tabName' not found on '$entryTypeHandle' — skipping.";
        }
    }

    private function actionAddMatrixBlockType(array $action, array &$result): void
    {
        // Adding an entry type to a matrix field's allowed types
        $matrixHandle    = $action['matrixField'] ?? null;
        $entryTypeHandle = $action['entryType']   ?? null;

        if (!$matrixHandle || !$entryTypeHandle) {
            $result['warnings'][] = "addMatrixBlockType: missing matrixField or entryType.";
            return;
        }

        $field = Craft::$app->getFields()->getFieldByHandle($matrixHandle);
        if (!$field || !($field instanceof \craft\fields\Matrix)) {
            $result['warnings'][] = "addMatrixBlockType: matrix field '$matrixHandle' not found — skipping.";
            return;
        }

        $entryType = Craft::$app->getEntries()->getEntryTypeByHandle($entryTypeHandle);
        if (!$entryType) {
            $result['warnings'][] = "addMatrixBlockType: entry type '$entryTypeHandle' not found — skipping.";
            return;
        }

        $types = $field->getEntryTypes();
        foreach ($types as $t) {
            if ($t->handle === $entryTypeHandle) {
                $result['warnings'][] = "addMatrixBlockType: '$entryTypeHandle' already in '$matrixHandle' — skipping.";
                return;
            }
        }

        $types[] = $entryType;
        $field->setEntryTypes($types);

        if (!Craft::$app->getFields()->saveField($field)) {
            $errors = implode(', ', $field->getFirstErrors());
            throw new \RuntimeException("addMatrixBlockType: failed to update '$matrixHandle': $errors");
        }

        Craft::info("ComponentMigrator: added '$entryTypeHandle' to matrix field '$matrixHandle'.", __METHOD__);
    }

    private function actionRemoveMatrixBlockType(array $action, array &$result): void
    {
        $matrixHandle    = $action['matrixField'] ?? null;
        $entryTypeHandle = $action['entryType']   ?? null;

        if (!$matrixHandle || !$entryTypeHandle) {
            $result['warnings'][] = "removeMatrixBlockType: missing matrixField or entryType.";
            return;
        }

        $field = Craft::$app->getFields()->getFieldByHandle($matrixHandle);
        if (!$field || !($field instanceof \craft\fields\Matrix)) {
            $result['warnings'][] = "removeMatrixBlockType: matrix field '$matrixHandle' not found — skipping.";
            return;
        }

        $types    = $field->getEntryTypes();
        $filtered = array_filter($types, fn($t) => $t->handle !== $entryTypeHandle);

        if (count($filtered) === count($types)) {
            $result['warnings'][] = "removeMatrixBlockType: '$entryTypeHandle' not found in '$matrixHandle' — skipping.";
            return;
        }

        $field->setEntryTypes(array_values($filtered));

        if (!Craft::$app->getFields()->saveField($field)) {
            $errors = implode(', ', $field->getFirstErrors());
            throw new \RuntimeException("removeMatrixBlockType: failed to update '$matrixHandle': $errors");
        }

        Craft::info("ComponentMigrator: removed '$entryTypeHandle' from matrix field '$matrixHandle'.", __METHOD__);
    }

    private function actionRenameMatrixBlockType(array $action, array &$result): void
    {
        $oldHandle = $action['oldHandle'] ?? null;
        $newHandle = $action['newHandle'] ?? null;

        if (!$oldHandle || !$newHandle) {
            $result['warnings'][] = "renameMatrixBlockType: missing oldHandle or newHandle.";
            return;
        }

        $entryType = Craft::$app->getEntries()->getEntryTypeByHandle($oldHandle);
        if (!$entryType) {
            $result['warnings'][] = "renameMatrixBlockType: entry type '$oldHandle' not found — skipping.";
            return;
        }

        $entryType->handle = $newHandle;
        if (!Craft::$app->getEntries()->saveEntryType($entryType)) {
            $errors = implode(', ', $entryType->getFirstErrors());
            throw new \RuntimeException("renameMatrixBlockType: failed to rename '$oldHandle' → '$newHandle': $errors");
        }

        Craft::info("ComponentMigrator: renamed entry type '$oldHandle' → '$newHandle'.", __METHOD__);
    }

    private function actionCopyContent(array $action, array &$result): void
    {
        $fromField       = $action['fromField']  ?? null;
        $toField         = $action['toField']    ?? null;
        $entryTypeHandle = $action['entryType']  ?? null;

        if (!$fromField || !$toField) {
            $result['warnings'][] = "copyContent: missing fromField or toField.";
            return;
        }

        $query = \craft\elements\Entry::find();
        if ($entryTypeHandle) {
            $entryType = Craft::$app->getEntries()->getEntryTypeByHandle($entryTypeHandle);
            if ($entryType) {
                $query->typeId($entryType->id);
            }
        }

        $count = 0;
        foreach ($query->all() as $entry) {
            $value = $entry->$fromField ?? null;
            if ($value !== null) {
                $entry->setFieldValue($toField, $value);
                Craft::$app->getElements()->saveElement($entry, false);
                $count++;
            }
        }

        Craft::info("ComponentMigrator: copyContent '$fromField' → '$toField' on $count entries.", __METHOD__);
    }

    private function actionTransformContent(array $action, array &$result): void
    {
        $fieldHandle     = $action['field']      ?? null;
        $entryTypeHandle = $action['entryType']  ?? null;
        $transform       = $action['transform']  ?? null;

        if (!$fieldHandle || !is_callable($transform)) {
            $result['warnings'][] = "transformContent: missing field or callable transform.";
            return;
        }

        $query = \craft\elements\Entry::find();
        if ($entryTypeHandle) {
            $entryType = Craft::$app->getEntries()->getEntryTypeByHandle($entryTypeHandle);
            if ($entryType) {
                $query->typeId($entryType->id);
            }
        }

        $count = 0;
        foreach ($query->all() as $entry) {
            $oldValue = $entry->$fieldHandle ?? null;
            $newValue = $transform($oldValue);
            if ($newValue !== $oldValue) {
                $entry->setFieldValue($fieldHandle, $newValue);
                Craft::$app->getElements()->saveElement($entry, false);
                $count++;
            }
        }

        Craft::info("ComponentMigrator: transformContent '$fieldHandle' on $count entries.", __METHOD__);
    }

    // -------------------------------------------------------------------------
    // State management
    // -------------------------------------------------------------------------

    public function updateComponentState(string $handle, string $type, int $version): void
    {
        $jsonPath = $this->getWbComponentsPath() . $type . DIRECTORY_SEPARATOR . $handle . '.json';
        $checksum = file_exists($jsonPath) ? md5(file_get_contents($jsonPath)) : '';

        (new ComponentStateService())->updateState($handle, $type, $version, $checksum);

        Craft::info("ComponentMigrator: updated state for $type/$handle to v$version.", __METHOD__);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function validateAction(array $action, string $stepKey): ?string
    {
        $required = [
            'renameField'          => ['oldHandle', 'newHandle'],
            'addField'             => ['handle', 'definition'],
            'removeField'          => ['handle'],
            'deprecateField'       => ['handle'],
            'updateFieldSettings'  => ['handle', 'settings'],
            'updateFieldLayout'    => ['entryType', 'tab', 'fields'],
            'addMatrixBlockType'   => ['matrixField', 'entryType'],
            'removeMatrixBlockType'=> ['matrixField', 'entryType'],
            'renameMatrixBlockType'=> ['matrixField', 'oldHandle', 'newHandle'],
            'copyContent'          => ['fromField', 'toField'],
            'transformContent'     => ['field', 'transform'],
        ];

        $type = $action['type'] ?? 'unknown';
        $keys = $required[$type] ?? null;
        if ($keys === null) {
            return "[$stepKey] Unknown action type '$type'.";
        }

        foreach ($keys as $key) {
            if (empty($action[$key])) {
                return "[$stepKey] Action '$type' is missing required key '$key'.";
            }
        }

        return null;
    }

    private function readJsonVersion(string $handle, string $type): ?int
    {
        $path = $this->getWbComponentsPath() . $type . DIRECTORY_SEPARATOR . $handle . '.json';
        if (!file_exists($path)) {
            return null;
        }
        $data = json_decode(file_get_contents($path), true);
        return isset($data['version']) ? (int) $data['version'] : null;
    }

    private function getMigrationDir(string $type, string $handle): ?string
    {
        $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'componentMigrations'
            . DIRECTORY_SEPARATOR . $type
            . DIRECTORY_SEPARATOR . $handle
            . DIRECTORY_SEPARATOR;
        return $path;
    }

    private function getWbComponentsPath(): string
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'wbComponents' . DIRECTORY_SEPARATOR;
    }

    private function emptyReport(bool $dryRun): array
    {
        return [
            'applied'              => [],
            'skipped'              => [],
            'warnings'             => [],
            'manualReviewRequired' => [],
            'errors'               => [],
            'dryRun'               => $dryRun,
        ];
    }

    private function mergeReports(array $base, array $addition): array
    {
        foreach (['applied', 'skipped', 'warnings', 'manualReviewRequired', 'errors'] as $key) {
            $base[$key] = array_merge($base[$key], $addition[$key] ?? []);
        }
        return $base;
    }

    private function logReport(array $report): void
    {
        $applied  = count($report['applied']);
        $skipped  = count($report['skipped']);
        $warnings = count($report['warnings']);
        $reviews  = count($report['manualReviewRequired']);
        $errors   = count($report['errors']);
        $mode     = $report['dryRun'] ? 'dry-run' : 'apply';

        Craft::info(
            "ComponentMigrator [$mode]: applied=$applied skipped=$skipped warnings=$warnings manualReview=$reviews errors=$errors",
            __METHOD__
        );
    }
}
