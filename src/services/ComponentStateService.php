<?php

namespace fklavyenet\webblocks\services;

use Craft;
use craft\base\Component;
use craft\helpers\DateTimeHelper;
use craft\helpers\Db;

/**
 * ComponentStateService — tracks installed versions of every WebBlocks
 * component JSON file in the webblocks_component_versions table.
 *
 * The table is populated once during plugin install and updated during
 * component migrations (Faz 3+).
 *
 * Component types recognised:
 *   filesystems, volumes, imagetransforms, fields, entrytypes,
 *   matrixfields, sections, globalsets, categorygroups, taggroups
 */
class ComponentStateService extends Component
{
    private const TABLE = '{{%webblocks_component_versions}}';

    // =========================================================================
    // Public API
    // =========================================================================

    /**
     * Record all component JSON files found under src/wbComponents/.
     *
     * Called at the end of InstallService::install(). Uses INSERT … ON DUPLICATE
     * KEY UPDATE so it is safe to call multiple times (idempotent).
     */
    public function recordAll(): void
    {
        $dir = $this->getWbComponentsPath();
        if (!$dir) {
            Craft::warning('WebBlocks: wbComponents directory not found — skipping state recording.', __METHOD__);
            return;
        }

        $types = [
            'filesystems',
            'volumes',
            'imagetransforms',
            'fields',
            'entrytypes',
            'matrixfields',
            'sections',
            'globalsets',
            'categorygroups',
            'taggroups',
        ];

        $now = Db::prepareDateForDb(DateTimeHelper::now());
        $recorded = 0;

        foreach ($types as $type) {
            $typePath = $dir . $type . DIRECTORY_SEPARATOR;
            if (!is_dir($typePath)) {
                continue;
            }

            foreach (glob($typePath . '*.json') as $file) {
                $raw = file_get_contents($file);
                $data = json_decode($raw, true);
                if (!$data || empty($data['handle'])) {
                    continue;
                }

                $handle   = $data['handle'];
                $version  = (int) ($data['version'] ?? 1);
                $checksum = md5($raw);

                $existing = Craft::$app->getDb()->createCommand('
                    SELECT id FROM ' . self::TABLE . '
                    WHERE componentHandle = :handle AND componentType = :type
                ')->bindValues([':handle' => $handle, ':type' => $type])->queryScalar();

                if ($existing) {
                    // Already recorded — do not overwrite (install is idempotent)
                    continue;
                }

                Craft::$app->getDb()->createCommand()->insert(self::TABLE, [
                    'componentHandle'  => $handle,
                    'componentType'    => $type,
                    'installedVersion' => $version,
                    'checksum'         => $checksum,
                    'lastAppliedAt'    => $now,
                    'dateCreated'      => $now,
                    'dateUpdated'      => $now,
                    'uid'              => \craft\helpers\StringHelper::UUID(),
                ])->execute();

                $recorded++;
            }
        }

        Craft::info("WebBlocks: ComponentStateService recorded $recorded components.", __METHOD__);
    }

    /**
     * Return the stored state row for a given component, or null if not found.
     *
     * @return array{id:int, componentHandle:string, componentType:string, installedVersion:int, checksum:string, lastAppliedAt:string|null}|null
     */
    public function getState(string $handle, string $type): ?array
    {
        $row = Craft::$app->getDb()->createCommand('
            SELECT * FROM ' . self::TABLE . '
            WHERE componentHandle = :handle AND componentType = :type
        ')->bindValues([':handle' => $handle, ':type' => $type])->queryOne();

        return $row ?: null;
    }

    /**
     * Update the stored version and checksum after a migration has been applied.
     */
    public function updateState(string $handle, string $type, int $version, string $checksum): void
    {
        $now = Db::prepareDateForDb(DateTimeHelper::now());

        Craft::$app->getDb()->createCommand()->update(
            self::TABLE,
            [
                'installedVersion' => $version,
                'checksum'         => $checksum,
                'lastAppliedAt'    => $now,
                'dateUpdated'      => $now,
            ],
            ['componentHandle' => $handle, 'componentType' => $type]
        )->execute();
    }

    /**
     * Return all stored state rows as an array keyed by "type/handle".
     *
     * @return array<string, array>
     */
    public function getAllStates(): array
    {
        $rows = Craft::$app->getDb()->createCommand('SELECT * FROM ' . self::TABLE)->queryAll();
        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row['componentType'] . '/' . $row['componentHandle']] = $row;
        }
        return $indexed;
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    private function getWbComponentsPath(): ?string
    {
        $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'wbComponents' . DIRECTORY_SEPARATOR;
        return is_dir($path) ? $path : null;
    }
}
