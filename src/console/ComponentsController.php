<?php

namespace fklavyenet\webblocks\console;

use Craft;
use craft\console\Controller;
use fklavyenet\webblocks\services\ComponentDiffService;
use fklavyenet\webblocks\services\ComponentMigrator;
use fklavyenet\webblocks\services\DeprecatedFieldService;
use yii\console\ExitCode;

/**
 * WebBlocks component versioning console commands.
 *
 * Usage:
 *   ddev craft webblocks/components/diff                 — full diff report
 *   ddev craft webblocks/components/check                — exit 0 if all OK, exit 1 if action needed
 *   ddev craft webblocks/components/dry-run              — show what a migration run would do
 *   ddev craft webblocks/components/migrate              — apply all pending migrations
 *   ddev craft webblocks/components/cleanup-deprecated   — list deprecated fields (--force to purge)
 */
class ComponentsController extends Controller
{
    /**
     * Show a full diff of all component JSON files vs the installed DB state.
     *
     * Prints a colour-coded table with one row per component that needs
     * attention, followed by a summary. Components that are "ok" are omitted
     * unless --verbose is passed.
     *
     * Exit codes:
     *   0 — all components up to date
     *   1 — one or more components need attention
     */
    public function actionDiff(): int
    {
        $this->stdout("=== WebBlocks Component Diff ===\n\n");

        $report = (new ComponentDiffService())->diff();
        $this->printReport($report, verbose: true);

        return $report['summary']['needsAction'] ? ExitCode::UNSPECIFIED_ERROR : ExitCode::OK;
    }

    /**
     * Quick health check — exits 0 if everything is up to date, 1 otherwise.
     * Suitable for CI or pre-deploy checks.
     */
    public function actionCheck(): int
    {
        $report = (new ComponentDiffService())->diff();
        $s      = $report['summary'];

        if (!$s['needsAction']) {
            $this->stdout("✓ All {$s['total']} components are up to date.\n");
            return ExitCode::OK;
        }

        $this->stderr(
            "✗ Components need attention: " .
            "{$s['version_bump']} version bump(s), " .
            "{$s['checksum_drift']} drift(s), " .
            "{$s['new']} new, " .
            "{$s['orphan']} orphan(s).\n" .
            "Run: ddev craft webblocks/components/diff\n"
        );
        return ExitCode::UNSPECIFIED_ERROR;
    }

    /**
     * Dry-run migration report — shows what would be applied without making
     * any changes to the database or Craft schema.
     */
    public function actionDryRun(): int
    {
        $this->stdout("=== WebBlocks Dry-Run Migration Report ===\n\n");

        $report = (new ComponentDiffService())->migrateAll(dryRun: true);
        $this->printReport($report, verbose: false);

        $planned = $report['plannedActions'] ?? [];
        if (empty($planned)) {
            $this->stdout("No planned actions — nothing to migrate.\n");
            return ExitCode::OK;
        }

        $this->stdout("\nPlanned actions (" . count($planned) . "):\n");
        foreach ($planned as $i => $action) {
            $n       = $i + 1;
            $warning = !empty($action['warning']) ? ' [WARNING]' : '';
            $from    = $action['from'] !== null ? "v{$action['from']}" : '—';
            $to      = $action['to']   !== null ? "v{$action['to']}"   : '—';
            $this->stdout(sprintf(
                "  %d. [%s] %s/%s  %s → %s%s\n     %s\n",
                $n,
                strtoupper($action['action']),
                $action['type'],
                $action['handle'],
                $from,
                $to,
                $warning,
                $action['message']
            ));
        }

        $this->stdout("\n[dry-run] No changes were applied.\n");

        return $report['summary']['needsAction'] ? ExitCode::UNSPECIFIED_ERROR : ExitCode::OK;
    }

    /**
     * Apply all pending component migrations.
     *
     * Runs ComponentMigrator::migrateAll(dryRun: false) and prints a
     * structured report of what was applied, skipped, and any errors.
     *
     * Exit codes:
     *   0 — all pending migrations applied successfully (or nothing to do)
     *   1 — one or more errors occurred
     */
    public function actionMigrate(): int
    {
        $this->stdout("=== WebBlocks Component Migrate ===\n\n");

        $report = (new ComponentMigrator())->migrateAll(dryRun: false);

        $applied  = $report['applied']              ?? [];
        $skipped  = $report['skipped']              ?? [];
        $warnings = $report['warnings']             ?? [];
        $reviews  = $report['manualReviewRequired'] ?? [];
        $errors   = $report['errors']               ?? [];

        // Applied
        if (!empty($applied)) {
            $this->stdout("Applied (" . count($applied) . "):\n");
            foreach ($applied as $item) {
                $note = isset($item['note']) ? "  ({$item['note']})" : '';
                $this->stdout(sprintf(
                    "  ✓ %s/%s  v%s → v%s%s\n",
                    $item['type'],
                    $item['handle'],
                    $item['from'],
                    $item['to'],
                    $note
                ));
            }
            $this->stdout("\n");
        }

        // Skipped
        if (!empty($skipped)) {
            $this->stdout("Skipped (" . count($skipped) . "):\n");
            foreach ($skipped as $item) {
                $this->stdout(sprintf(
                    "  - %s/%s: %s\n",
                    $item['type'] ?? '?',
                    $item['handle'] ?? '?',
                    $item['reason'] ?? '?'
                ));
            }
            $this->stdout("\n");
        }

        // Warnings
        if (!empty($warnings)) {
            $this->stdout("Warnings (" . count($warnings) . "):\n");
            foreach ($warnings as $w) {
                $this->stdout("  [WARN] $w\n");
            }
            $this->stdout("\n");
        }

        // Manual review required
        if (!empty($reviews)) {
            $this->stdout("Manual review required (" . count($reviews) . "):\n");
            foreach ($reviews as $item) {
                $this->stdout(sprintf(
                    "  [REVIEW] %s/%s — %s: %s\n",
                    $item['type']    ?? '?',
                    $item['handle']  ?? '?',
                    $item['action']  ?? '?',
                    $item['message'] ?? ''
                ));
            }
            $this->stdout("\n");
        }

        // Errors
        if (!empty($errors)) {
            $this->stderr("Errors (" . count($errors) . "):\n");
            foreach ($errors as $item) {
                $this->stderr(sprintf(
                    "  [ERROR] %s/%s — %s: %s\n",
                    $item['type']    ?? '?',
                    $item['handle']  ?? '?',
                    $item['action']  ?? '?',
                    $item['message'] ?? ''
                ));
            }
            $this->stdout("\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        if (empty($applied)) {
            $this->stdout("Nothing to migrate — all components up to date.\n");
        } else {
            $this->stdout("Migration complete. Applied " . count($applied) . " component(s).\n");
        }

        return ExitCode::OK;
    }

    /**
     * List or purge deprecated fields tracked by WebBlocks migrations.
     *
     * By default (no flags) this command lists all pending deprecated fields
     * with their handle, deprecatedAt timestamp, migration source, and whether
     * the Craft field still exists. No changes are made.
     *
     * Options:
     *   --force   Permanently delete each deprecated field from Craft (and its
     *             content data). Prompts for confirmation unless --interactive=0.
     *
     * Exit codes:
     *   0 — no deprecated fields pending (or all purged successfully)
     *   1 — deprecated fields remain (dry-run mode) or one or more purge failures
     *
     * Examples:
     *   ddev craft webblocks/components/cleanup-deprecated
     *   ddev craft webblocks/components/cleanup-deprecated --force --interactive=0
     */
    public bool $force = false;

    public function options($actionID): array
    {
        $options = parent::options($actionID);
        if ($actionID === 'cleanup-deprecated') {
            $options[] = 'force';
        }
        return $options;
    }

    public function actionCleanupDeprecated(): int
    {
        $service = new DeprecatedFieldService();
        $fields  = $service->getDeprecated();

        if (empty($fields)) {
            $this->stdout("No deprecated fields tracked.\n");
            return ExitCode::OK;
        }

        $this->stdout("Deprecated fields (" . count($fields) . "):\n\n");
        $this->stdout(sprintf(
            "  %-30s %-26s %-12s  %s\n",
            'Handle', 'Deprecated At', 'Field Exists', 'Migration Source'
        ));
        $this->stdout("  " . str_repeat('-', 90) . "\n");

        foreach ($fields as $row) {
            $exists = $row['fieldExists'] ? 'yes' : 'no (already gone)';
            $this->stdout(sprintf(
                "  %-30s %-26s %-12s  %s\n",
                $row['fieldHandle'],
                $row['deprecatedAt'] ?? '—',
                $exists,
                $row['migrationSource'] ?? '—'
            ));
        }
        $this->stdout("\n");

        if (!$this->force) {
            $this->stdout("Run with --force to permanently delete these fields and their content.\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        // --force: confirm then purge
        if ($this->interactive) {
            $confirm = $this->confirm(
                'This will PERMANENTLY DELETE ' . count($fields) . ' field(s) and all their content. Proceed?'
            );
            if (!$confirm) {
                $this->stdout("Aborted.\n");
                return ExitCode::OK;
            }
        }

        $errors = 0;
        foreach ($fields as $row) {
            $handle = $row['fieldHandle'];
            $ok     = $service->purge($handle);
            if ($ok) {
                $this->stdout("  ✓ Purged '$handle'\n");
            } else {
                $this->stderr("  ✗ Failed to purge '$handle'\n");
                $errors++;
            }
        }

        $this->stdout("\n");

        if ($errors > 0) {
            $this->stderr("Cleanup complete with $errors error(s).\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout("Cleanup complete. All deprecated fields purged.\n");
        return ExitCode::OK;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function printReport(array $report, bool $verbose): void
    {
        $s = $report['summary'];

        // Print components that need attention (and all if verbose)
        $printed = 0;
        foreach ($report['components'] as $row) {
            if (!$verbose && $row['status'] === ComponentDiffService::STATUS_OK) {
                continue;
            }

            $badge = $this->statusBadge($row['status']);
            $jv    = $row['jsonVersion'] !== null ? "v{$row['jsonVersion']}" : '—';
            $dv    = $row['dbVersion']   !== null ? "v{$row['dbVersion']}"   : '—';

            $this->stdout(sprintf(
                "  %-20s %-15s  JSON:%-5s  DB:%-5s  %s\n  %s%s\n\n",
                $row['type'],
                $row['handle'],
                $jv,
                $dv,
                $badge,
                '  ',
                $row['message']
            ));
            $printed++;
        }

        if ($printed === 0) {
            $this->stdout("  (no issues found)\n\n");
        }

        // Summary
        $this->stdout("Summary:\n");
        $this->stdout("  ok:             {$s['ok']}\n");
        $this->stdout("  version_bump:   {$s['version_bump']}\n");
        $this->stdout("  checksum_drift: {$s['checksum_drift']}\n");
        $this->stdout("  new:            {$s['new']}\n");
        $this->stdout("  orphan:         {$s['orphan']}\n");
        $this->stdout("  total:          {$s['total']}\n");
        $this->stdout($s['needsAction']
            ? "\n  Action required.\n"
            : "\n  All components up to date.\n"
        );
        $this->stdout("\n");
    }

    private function statusBadge(string $status): string
    {
        return match ($status) {
            ComponentDiffService::STATUS_OK             => '[OK]',
            ComponentDiffService::STATUS_VERSION_BUMP   => '[VERSION BUMP]',
            ComponentDiffService::STATUS_CHECKSUM_DRIFT => '[CHECKSUM DRIFT]',
            ComponentDiffService::STATUS_NEW            => '[NEW]',
            ComponentDiffService::STATUS_ORPHAN         => '[ORPHAN]',
            default                                     => "[$status]",
        };
    }
}
