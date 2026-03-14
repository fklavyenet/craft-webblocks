<?php

namespace fklavyenet\webblocks\services;

use Craft;
use craft\base\Component;
use craft\helpers\DateTimeHelper;
use craft\helpers\Db;

/**
 * DeprecatedFieldService — tracks Craft fields that have been deprecated by a
 * WebBlocks component migration step.
 *
 * A deprecated field is one that has been removed from all field layouts by the
 * `deprecateField` migration action, but whose data is still present in the
 * Craft `fields` table (and therefore in entry content tables). This is
 * intentional: data is never deleted automatically.
 *
 * Lifecycle:
 *   1. `deprecateField` migration action runs →
 *        field removed from all layouts, markDeprecated() called
 *   2. Admin reviews the list via `webblocks/components/cleanup-deprecated --dry-run`
 *        or the CP health screen
 *   3. Admin runs `webblocks/components/cleanup-deprecated --force` to hard-delete
 *        the field (and its content) from Craft
 *
 * Table: webblocks_deprecated_fields
 *   fieldHandle, deprecatedAt, migrationSource, notes
 */
class DeprecatedFieldService extends Component
{
    private const TABLE = '{{%webblocks_deprecated_fields}}';

    // =========================================================================
    // Public API
    // =========================================================================

    /**
     * Record a field as deprecated.
     *
     * Safe to call multiple times for the same handle — subsequent calls update
     * the existing row (re-deprecation after a wipe+reinstall cycle).
     *
     * @param string      $fieldHandle      Craft field handle
     * @param string|null $migrationSource  Human-readable source, e.g. "entrytypes/wbHero v1→v2"
     * @param string|null $notes            Optional freeform notes
     */
    public function markDeprecated(
        string  $fieldHandle,
        ?string $migrationSource = null,
        ?string $notes = null
    ): void {
        $now = Db::prepareDateForDb(DateTimeHelper::now());
        $db  = Craft::$app->getDb();

        $existing = $db->createCommand(
            'SELECT id FROM ' . self::TABLE . ' WHERE fieldHandle = :h'
        )->bindValue(':h', $fieldHandle)->queryScalar();

        if ($existing) {
            $db->createCommand()->update(self::TABLE, [
                'deprecatedAt'    => $now,
                'migrationSource' => $migrationSource,
                'notes'           => $notes,
                'dateUpdated'     => $now,
            ], ['fieldHandle' => $fieldHandle])->execute();
        } else {
            $db->createCommand()->insert(self::TABLE, [
                'fieldHandle'     => $fieldHandle,
                'deprecatedAt'    => $now,
                'migrationSource' => $migrationSource,
                'notes'           => $notes,
                'dateCreated'     => $now,
                'dateUpdated'     => $now,
                'uid'             => \craft\helpers\StringHelper::UUID(),
            ])->execute();
        }

        Craft::info("DeprecatedFieldService: marked '$fieldHandle' as deprecated (source: $migrationSource).", __METHOD__);
    }

    /**
     * Return all currently-tracked deprecated fields.
     *
     * Each row includes:
     *   fieldHandle, deprecatedAt, migrationSource, notes,
     *   fieldExists (bool — whether the Craft field is still in the DB)
     *
     * @return array<int, array>
     */
    public function getDeprecated(): array
    {
        $rows = Craft::$app->getDb()->createCommand(
            'SELECT * FROM ' . self::TABLE . ' ORDER BY deprecatedAt ASC'
        )->queryAll();

        foreach ($rows as &$row) {
            $field = Craft::$app->getFields()->getFieldByHandle($row['fieldHandle']);
            $row['fieldExists'] = ($field !== null);
        }
        unset($row);

        return $rows;
    }

    /**
     * Hard-delete a deprecated field from Craft and remove it from this table.
     *
     * Returns true on success, false if the field no longer exists in Craft
     * (already deleted — removes the tracking row anyway).
     */
    public function purge(string $fieldHandle): bool
    {
        $field = Craft::$app->getFields()->getFieldByHandle($fieldHandle);

        $deleted = true;
        if ($field) {
            $deleted = Craft::$app->getFields()->deleteField($field);
            if (!$deleted) {
                Craft::error("DeprecatedFieldService: failed to delete field '$fieldHandle'.", __METHOD__);
                return false;
            }
        }

        // Remove from tracking table regardless
        Craft::$app->getDb()->createCommand()->delete(
            self::TABLE,
            ['fieldHandle' => $fieldHandle]
        )->execute();

        Craft::info("DeprecatedFieldService: purged '$fieldHandle'.", __METHOD__);
        return true;
    }

    /**
     * Remove a field from the deprecated tracking table without deleting the
     * Craft field. Useful when a field was re-added to layouts and should no
     * longer be considered deprecated.
     */
    public function untrack(string $fieldHandle): void
    {
        Craft::$app->getDb()->createCommand()->delete(
            self::TABLE,
            ['fieldHandle' => $fieldHandle]
        )->execute();

        Craft::info("DeprecatedFieldService: untracked '$fieldHandle' (field preserved).", __METHOD__);
    }

    /**
     * Return true if a field handle is currently tracked as deprecated.
     */
    public function isDeprecated(string $fieldHandle): bool
    {
        return (bool) Craft::$app->getDb()->createCommand(
            'SELECT COUNT(*) FROM ' . self::TABLE . ' WHERE fieldHandle = :h'
        )->bindValue(':h', $fieldHandle)->queryScalar();
    }
}
