<?php

namespace fklavyenet\webblocks\services;

use Craft;
use craft\base\Component;
use fklavyenet\webblocks\services\ComponentMigrator;

/**
 * ComponentDiffService — compares the current wbComponents/ JSON definitions
 * against the installed state in webblocks_component_versions.
 *
 * Produces a structured diff report used by:
 *   - The admin health screen (Faz 4)
 *   - The ComponentMigrator dry-run / apply flow (Faz 3)
 *   - Console commands
 *
 * Status codes for each component:
 *
 *   ok             — version and checksum match; nothing to do
 *   version_bump   — JSON version > DB installedVersion; migration required
 *   checksum_drift — version matches but checksum differs; JSON changed without
 *                    bumping the version number (developer error / warning)
 *   new            — JSON file exists but no DB row; component was added after
 *                    the last install (needs to be recorded)
 *   orphan         — DB row exists but no matching JSON file; component was
 *                    removed from the plugin source (needs manual review)
 */
class ComponentDiffService extends Component
{
    // -------------------------------------------------------------------------
    // Status constants
    // -------------------------------------------------------------------------

    public const STATUS_OK             = 'ok';
    public const STATUS_VERSION_BUMP   = 'version_bump';
    public const STATUS_CHECKSUM_DRIFT = 'checksum_drift';
    public const STATUS_NEW            = 'new';
    public const STATUS_ORPHAN         = 'orphan';

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Run a full diff of all components and return a structured report.
     *
     * Return shape:
     * ```
     * [
     *   'summary' => [
     *     'ok'             => int,
     *     'version_bump'   => int,
     *     'checksum_drift' => int,
     *     'new'            => int,
     *     'orphan'         => int,
     *     'total'          => int,
     *     'needsAction'    => bool,
     *   ],
     *   'components' => [
     *     [
     *       'handle'           => string,
     *       'type'             => string,
     *       'status'           => string,   // one of the STATUS_* constants
     *       'jsonVersion'      => int|null,
     *       'dbVersion'        => int|null,
     *       'jsonChecksum'     => string|null,
     *       'dbChecksum'       => string|null,
     *       'lastAppliedAt'    => string|null,
     *       'message'          => string,
     *     ],
     *     ...
     *   ],
     * ]
     * ```
     */
    public function diff(): array
    {
        $jsonComponents = $this->loadJsonComponents();
        $dbStates       = $this->loadDbStates();

        $rows = [];

        // ── Step 1: compare each JSON component against the DB ──────────────
        foreach ($jsonComponents as $key => [$type, $handle, $version, $checksum]) {
            $db = $dbStates[$key] ?? null;

            if ($db === null) {
                $rows[] = $this->makeRow($handle, $type, self::STATUS_NEW, [
                    'jsonVersion'  => $version,
                    'dbVersion'    => null,
                    'jsonChecksum' => $checksum,
                    'dbChecksum'   => null,
                    'message'      => 'Component found in JSON but not yet recorded in the database.',
                ]);
                continue;
            }

            $dbVersion  = (int) $db['installedVersion'];
            $dbChecksum = $db['checksum'];

            if ($version > $dbVersion) {
                $rows[] = $this->makeRow($handle, $type, self::STATUS_VERSION_BUMP, [
                    'jsonVersion'   => $version,
                    'dbVersion'     => $dbVersion,
                    'jsonChecksum'  => $checksum,
                    'dbChecksum'    => $dbChecksum,
                    'lastAppliedAt' => $db['lastAppliedAt'] ?? null,
                    'message'       => "Version bumped from $dbVersion → $version. Migration required.",
                ]);
                continue;
            }

            if ($checksum !== $dbChecksum) {
                $rows[] = $this->makeRow($handle, $type, self::STATUS_CHECKSUM_DRIFT, [
                    'jsonVersion'   => $version,
                    'dbVersion'     => $dbVersion,
                    'jsonChecksum'  => $checksum,
                    'dbChecksum'    => $dbChecksum,
                    'lastAppliedAt' => $db['lastAppliedAt'] ?? null,
                    'message'       => 'JSON content changed but version was not bumped. Review this component.',
                ]);
                continue;
            }

            // All good
            $rows[] = $this->makeRow($handle, $type, self::STATUS_OK, [
                'jsonVersion'   => $version,
                'dbVersion'     => $dbVersion,
                'jsonChecksum'  => $checksum,
                'dbChecksum'    => $dbChecksum,
                'lastAppliedAt' => $db['lastAppliedAt'] ?? null,
                'message'       => 'Up to date.',
            ]);
        }

        // ── Step 2: find orphans (DB rows with no matching JSON file) ────────
        $jsonKeys = array_keys($jsonComponents);
        foreach ($dbStates as $key => $db) {
            if (!in_array($key, $jsonKeys, true)) {
                [$type, $handle] = explode('/', $key, 2);
                $rows[] = $this->makeRow($handle, $type, self::STATUS_ORPHAN, [
                    'jsonVersion'   => null,
                    'dbVersion'     => (int) $db['installedVersion'],
                    'jsonChecksum'  => null,
                    'dbChecksum'    => $db['checksum'],
                    'lastAppliedAt' => $db['lastAppliedAt'] ?? null,
                    'message'       => 'DB record exists but no matching JSON file found. Component may have been removed.',
                ]);
            }
        }

        // ── Step 3: sort for readability (action-needed first) ───────────────
        $order = [
            self::STATUS_VERSION_BUMP   => 0,
            self::STATUS_CHECKSUM_DRIFT => 1,
            self::STATUS_NEW            => 2,
            self::STATUS_ORPHAN         => 3,
            self::STATUS_OK             => 4,
        ];
        usort($rows, fn($a, $b) => ($order[$a['status']] ?? 9) <=> ($order[$b['status']] ?? 9));

        // ── Step 4: build summary ────────────────────────────────────────────
        $counts = array_count_values(array_column($rows, 'status'));
        $summary = [
            'ok'             => $counts[self::STATUS_OK]             ?? 0,
            'version_bump'   => $counts[self::STATUS_VERSION_BUMP]   ?? 0,
            'checksum_drift' => $counts[self::STATUS_CHECKSUM_DRIFT] ?? 0,
            'new'            => $counts[self::STATUS_NEW]            ?? 0,
            'orphan'         => $counts[self::STATUS_ORPHAN]         ?? 0,
            'total'          => count($rows),
        ];
        $summary['needsAction'] = (
            $summary['version_bump']   > 0 ||
            $summary['checksum_drift'] > 0 ||
            $summary['new']            > 0 ||
            $summary['orphan']         > 0
        );

        return [
            'summary'    => $summary,
            'components' => $rows,
        ];
    }

    /**
     * Run the diff and simulate what a migration run would do, without
     * actually applying any changes.
     *
     * Returns the same report shape as diff(), with an additional top-level
     * key `dryRun => true` and a `plannedActions` array describing what would
     * be applied (populated by ComponentMigrator in Faz 3).
     */
    public function migrateAll(bool $dryRun = true): array
    {
        $report = $this->diff();
        $report['dryRun'] = $dryRun;

        // Build planned actions from version_bump and new components.
        // The actual execution logic lives in ComponentMigrator (Faz 3).
        // Here we only describe what would happen.
        $planned = [];
        foreach ($report['components'] as $row) {
            switch ($row['status']) {
                case self::STATUS_VERSION_BUMP:
                    $planned[] = [
                        'action'  => 'migrate',
                        'handle'  => $row['handle'],
                        'type'    => $row['type'],
                        'from'    => $row['dbVersion'],
                        'to'      => $row['jsonVersion'],
                        'message' => "Would run migration chain v{$row['dbVersion']} → v{$row['jsonVersion']}.",
                    ];
                    break;

                case self::STATUS_NEW:
                    $planned[] = [
                        'action'  => 'record',
                        'handle'  => $row['handle'],
                        'type'    => $row['type'],
                        'from'    => null,
                        'to'      => $row['jsonVersion'],
                        'message' => "Would record new component at v{$row['jsonVersion']}.",
                    ];
                    break;

                case self::STATUS_CHECKSUM_DRIFT:
                    $planned[] = [
                        'action'   => 'review',
                        'handle'   => $row['handle'],
                        'type'     => $row['type'],
                        'from'     => $row['dbVersion'],
                        'to'       => $row['jsonVersion'],
                        'message'  => 'Checksum drift detected — manual review recommended before migrating.',
                        'warning'  => true,
                    ];
                    break;

                case self::STATUS_ORPHAN:
                    $planned[] = [
                        'action'  => 'review',
                        'handle'  => $row['handle'],
                        'type'    => $row['type'],
                        'from'    => $row['dbVersion'],
                        'to'      => null,
                        'message' => 'Orphan DB record — JSON file missing. Manual review required.',
                        'warning' => true,
                    ];
                    break;
            }
        }

        $report['plannedActions'] = $planned;

        if ($dryRun) {
            Craft::info(
                'WebBlocks ComponentDiffService::migrateAll(dryRun=true) — ' .
                count($planned) . ' planned action(s). No changes applied.',
                __METHOD__
            );
        } else {
            // Delegate the actual apply to ComponentMigrator
            $migrationReport = (new ComponentMigrator())->migrateAll(dryRun: false);
            $report['migrationReport'] = $migrationReport;

            Craft::info(
                'WebBlocks ComponentDiffService::migrateAll(dryRun=false) — ' .
                count($migrationReport['applied']) . ' applied, ' .
                count($migrationReport['errors'])  . ' error(s).',
                __METHOD__
            );
        }

        return $report;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Load all JSON component definitions from wbComponents/.
     *
     * @return array<string, array{0:string, 1:string, 2:int, 3:string}>
     *         Keyed by "type/handle" => [type, handle, version, checksum]
     */
    private function loadJsonComponents(): array
    {
        $dir = $this->getWbComponentsPath();
        if (!$dir) {
            return [];
        }

        $types = [
            'filesystems', 'volumes', 'imagetransforms', 'fields',
            'entrytypes', 'matrixfields', 'sections', 'globalsets',
            'categorygroups', 'taggroups',
        ];

        $result = [];
        foreach ($types as $type) {
            $path = $dir . $type . DIRECTORY_SEPARATOR;
            if (!is_dir($path)) {
                continue;
            }
            foreach (glob($path . '*.json') as $file) {
                $raw  = file_get_contents($file);
                $data = json_decode($raw, true);
                if (!$data || empty($data['handle'])) {
                    continue;
                }
                $handle  = $data['handle'];
                $version = (int) ($data['version'] ?? 1);
                $key     = $type . '/' . $handle;
                $result[$key] = [$type, $handle, $version, md5($raw)];
            }
        }

        return $result;
    }

    /**
     * Load all rows from webblocks_component_versions, keyed by "type/handle".
     *
     * @return array<string, array>
     */
    private function loadDbStates(): array
    {
        if (!Craft::$app->getDb()->tableExists('{{%webblocks_component_versions}}')) {
            return [];
        }

        $rows = Craft::$app->getDb()
            ->createCommand('SELECT * FROM {{%webblocks_component_versions}}')
            ->queryAll();

        $indexed = [];
        foreach ($rows as $row) {
            $key = $row['componentType'] . '/' . $row['componentHandle'];
            $indexed[$key] = $row;
        }
        return $indexed;
    }

    /**
     * Build a normalised component row array.
     */
    private function makeRow(string $handle, string $type, string $status, array $data): array
    {
        return array_merge([
            'handle'        => $handle,
            'type'          => $type,
            'status'        => $status,
            'jsonVersion'   => null,
            'dbVersion'     => null,
            'jsonChecksum'  => null,
            'dbChecksum'    => null,
            'lastAppliedAt' => null,
            'message'       => '',
        ], $data);
    }

    private function getWbComponentsPath(): ?string
    {
        $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'wbComponents' . DIRECTORY_SEPARATOR;
        return is_dir($path) ? $path : null;
    }
}
