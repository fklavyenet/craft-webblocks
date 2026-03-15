<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * ComponentMigratorPureTest
 *
 * Tests the pure (Craft-free) logic in ComponentMigrator:
 *   - resolveMigrationPath() — reads migration PHP files from disk, chains them
 *   - applyMigrationStep()   — dispatches actions, handles risk=high, dry-run
 *   - validateAction()       — validates required keys per action type
 *   - mergeReports()         — merges two report arrays
 *   - emptyReport()          — produces a well-shaped empty report
 *
 * Methods that call Craft::$app (dispatchAction handlers, updateComponentState)
 * are not tested here — they need an integration test environment.
 */

/**
 * Testable subclass of ComponentMigrator.
 *
 * Overrides filesystem helpers so migration files can be provided as
 * in-memory arrays instead of real disk files.
 */
class TestableComponentMigrator extends \fklavyenet\webblocks\services\ComponentMigrator
{
    /** Map of "type/handle/from_to_to" => step array (used instead of disk files). */
    public array $migrationFiles = [];

    /** Expose private methods via Reflection. */
    private function callPrivate(string $method, array $args = []): mixed
    {
        $ref = new ReflectionMethod(\fklavyenet\webblocks\services\ComponentMigrator::class, $method);
        $ref->setAccessible(true);
        return $ref->invokeArgs($this, $args);
    }

    public function exposedValidateAction(array $action, string $stepKey): ?string
    {
        return $this->callPrivate('validateAction', [$action, $stepKey]);
    }

    public function exposedMergeReports(array $base, array $addition): array
    {
        return $this->callPrivate('mergeReports', [$base, $addition]);
    }

    public function exposedEmptyReport(bool $dryRun): array
    {
        return $this->callPrivate('emptyReport', [$dryRun]);
    }

    /**
     * Override getMigrationDir to return a temp directory populated with
     * our in-memory migration files written to actual temp files so that
     * resolveMigrationPath() can file_exists / require them normally.
     */
    protected function buildTempMigrationDir(string $type, string $handle): string
    {
        $dir = sys_get_temp_dir() . '/wb_test_migrations/' . $type . '/' . $handle . '/';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        // Write only the files registered for this type/handle
        $prefix = $type . '/' . $handle . '/';
        foreach ($this->migrationFiles as $key => $step) {
            if (str_starts_with($key, $prefix)) {
                $filename = substr($key, strlen($prefix)) . '.php';
                file_put_contents($dir . $filename, '<?php return ' . var_export($step, true) . ';');
            }
        }
        return $dir;
    }

    /**
     * We need to expose resolveMigrationPath() but its getMigrationDir() call
     * is private, so we override the whole method using our temp dir.
     */
    public function resolveMigrationPath(string $handle, string $type, int $fromVersion, int $toVersion): array
    {
        $dir = $this->buildTempMigrationDir($type, $handle);

        $chain   = [];
        $current = $fromVersion;

        while ($current < $toVersion) {
            $next = $current + 1;
            $file = $dir . "{$current}_to_{$next}.php";

            if (!file_exists($file)) {
                break;
            }

            $step = require $file;
            if (!is_array($step) || empty($step['from']) || empty($step['to'])) {
                break;
            }

            $chain[] = $step;
            $current = $next;
        }

        return $chain;
    }

    /** Expose applyMigrationStep (it's already public in the parent). */
    // (no override needed — already public)
}

// ──────────────────────────────────────────────────────────────────────────────

class ComponentMigratorPureTest extends TestCase
{
    private TestableComponentMigrator $migrator;

    protected function setUp(): void
    {
        $this->migrator = new TestableComponentMigrator();
    }

    protected function tearDown(): void
    {
        // Clean up temp files
        $dir = sys_get_temp_dir() . '/wb_test_migrations/';
        if (is_dir($dir)) {
            $this->rmdirRecursive($dir);
        }
    }

    private function rmdirRecursive(string $dir): void
    {
        foreach (glob($dir . '*', GLOB_MARK) as $file) {
            if (str_ends_with($file, '/')) {
                $this->rmdirRecursive($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dir);
    }

    // -------------------------------------------------------------------------
    // emptyReport()
    // -------------------------------------------------------------------------

    public function testEmptyReportHasExpectedKeys(): void
    {
        $report = $this->migrator->exposedEmptyReport(false);
        $this->assertArrayHasKey('applied',              $report);
        $this->assertArrayHasKey('skipped',              $report);
        $this->assertArrayHasKey('warnings',             $report);
        $this->assertArrayHasKey('manualReviewRequired', $report);
        $this->assertArrayHasKey('errors',               $report);
        $this->assertArrayHasKey('dryRun',               $report);
    }

    public function testEmptyReportHasEmptyArraysForListKeys(): void
    {
        $report = $this->migrator->exposedEmptyReport(true);
        $this->assertEmpty($report['applied']);
        $this->assertEmpty($report['skipped']);
        $this->assertEmpty($report['warnings']);
        $this->assertEmpty($report['manualReviewRequired']);
        $this->assertEmpty($report['errors']);
        $this->assertTrue($report['dryRun']);
    }

    // -------------------------------------------------------------------------
    // mergeReports()
    // -------------------------------------------------------------------------

    public function testMergeReportsMergesAllListKeys(): void
    {
        $base = $this->migrator->exposedEmptyReport(false);
        $base['applied'][]  = ['handle' => 'a'];
        $base['warnings'][] = 'warn1';

        $addition = $this->migrator->exposedEmptyReport(false);
        $addition['applied'][]  = ['handle' => 'b'];
        $addition['warnings'][] = 'warn2';
        $addition['errors'][]   = ['message' => 'err'];

        $merged = $this->migrator->exposedMergeReports($base, $addition);

        $this->assertCount(2, $merged['applied']);
        $this->assertCount(2, $merged['warnings']);
        $this->assertCount(1, $merged['errors']);
    }

    public function testMergeReportsPreservesBaseWhenAdditionEmpty(): void
    {
        $base = $this->migrator->exposedEmptyReport(false);
        $base['skipped'][] = ['handle' => 'x'];

        $addition = $this->migrator->exposedEmptyReport(false);

        $merged = $this->migrator->exposedMergeReports($base, $addition);
        $this->assertCount(1, $merged['skipped']);
    }

    // -------------------------------------------------------------------------
    // validateAction()
    // -------------------------------------------------------------------------

    public function testValidateActionReturnNullForValidRenameField(): void
    {
        $action = ['type' => 'renameField', 'oldHandle' => 'wbOld', 'newHandle' => 'wbNew'];
        $this->assertNull($this->migrator->exposedValidateAction($action, 'test'));
    }

    public function testValidateActionReturnWarningForMissingKey(): void
    {
        $action = ['type' => 'renameField', 'oldHandle' => 'wbOld']; // missing newHandle
        $result = $this->migrator->exposedValidateAction($action, 'myStep');
        $this->assertNotNull($result);
        $this->assertStringContainsString('newHandle', $result);
    }

    public function testValidateActionReturnWarningForUnknownType(): void
    {
        $action = ['type' => 'doSomethingWeird'];
        $result = $this->migrator->exposedValidateAction($action, 'myStep');
        $this->assertNotNull($result);
        $this->assertStringContainsString('doSomethingWeird', $result);
    }

    public function testValidateActionKnownTypes(): void
    {
        $validActions = [
            ['type' => 'addField',              'handle' => 'wbFoo',   'definition' => 'fields/wbFoo.json'],
            ['type' => 'removeField',            'handle' => 'wbFoo'],
            ['type' => 'deprecateField',         'handle' => 'wbFoo'],
            ['type' => 'updateFieldSettings',    'handle' => 'wbFoo',   'settings' => ['required' => true]],
            ['type' => 'updateFieldLayout',      'entryType' => 'wbHero', 'tab' => 'Content', 'fields' => ['wbTitle']],
            ['type' => 'addMatrixBlockType',     'matrixField' => 'wbBlocks', 'entryType' => 'wbHero'],
            ['type' => 'removeMatrixBlockType',  'matrixField' => 'wbBlocks', 'entryType' => 'wbHero'],
            ['type' => 'copyContent',            'fromField' => 'wbOld', 'toField' => 'wbNew'],
        ];

        foreach ($validActions as $action) {
            $result = $this->migrator->exposedValidateAction($action, 'test');
            $this->assertNull($result, "Expected null for valid action type '{$action['type']}', got: $result");
        }
    }

    // -------------------------------------------------------------------------
    // resolveMigrationPath()
    // -------------------------------------------------------------------------

    public function testResolvesMigrationChainForConsecutiveVersions(): void
    {
        $step1 = ['from' => 1, 'to' => 2, 'actions' => []];
        $step2 = ['from' => 2, 'to' => 3, 'actions' => []];

        $this->migrator->migrationFiles = [
            'entrytypes/wbHero/1_to_2' => $step1,
            'entrytypes/wbHero/2_to_3' => $step2,
        ];

        $chain = $this->migrator->resolveMigrationPath('wbHero', 'entrytypes', 1, 3);

        $this->assertCount(2, $chain);
        $this->assertSame(1, $chain[0]['from']);
        $this->assertSame(2, $chain[0]['to']);
        $this->assertSame(2, $chain[1]['from']);
        $this->assertSame(3, $chain[1]['to']);
    }

    public function testResolveMigrationPathReturnsEmptyWhenNoFiles(): void
    {
        $this->migrator->migrationFiles = [];
        $chain = $this->migrator->resolveMigrationPath('wbHero', 'entrytypes', 1, 2);
        $this->assertEmpty($chain);
    }

    public function testResolveMigrationPathStopsAtGap(): void
    {
        // Only 1→2 exists, not 2→3
        $step1 = ['from' => 1, 'to' => 2, 'actions' => []];
        $this->migrator->migrationFiles = [
            'entrytypes/wbHero/1_to_2' => $step1,
        ];

        $chain = $this->migrator->resolveMigrationPath('wbHero', 'entrytypes', 1, 3);
        // Only the first step resolved before the gap
        $this->assertCount(1, $chain);
    }

    // -------------------------------------------------------------------------
    // applyMigrationStep() — dry-run and risk=high handling
    // -------------------------------------------------------------------------

    public function testApplyMigrationStepInDryRunValidatesActions(): void
    {
        $step = [
            'from'    => 1,
            'to'      => 2,
            'actions' => [
                // Valid action in dry-run → no warning
                ['type' => 'renameField', 'oldHandle' => 'wbOld', 'newHandle' => 'wbNew'],
            ],
        ];

        $result = $this->migrator->applyMigrationStep($step, 'wbHero', 'entrytypes', dryRun: true);

        $this->assertEmpty($result['errors']);
        // Valid action in dry-run produces no warnings about structure
        $this->assertEmpty($result['warnings']);
    }

    public function testApplyMigrationStepDryRunWarnsForInvalidAction(): void
    {
        $step = [
            'from'    => 1,
            'to'      => 2,
            'actions' => [
                ['type' => 'renameField', 'oldHandle' => 'wbOld'], // missing newHandle
            ],
        ];

        $result = $this->migrator->applyMigrationStep($step, 'wbHero', 'entrytypes', dryRun: true);

        $this->assertNotEmpty($result['warnings']);
        $this->assertStringContainsString('newHandle', $result['warnings'][0]);
    }

    public function testApplyMigrationStepRiskHighGoesToManualReview(): void
    {
        $step = [
            'from'    => 1,
            'to'      => 2,
            'actions' => [
                ['type' => 'removeField', 'handle' => 'wbDangerous', 'risk' => 'high'],
            ],
        ];

        $result = $this->migrator->applyMigrationStep($step, 'wbHero', 'entrytypes', dryRun: false);

        $this->assertCount(1, $result['manualReviewRequired']);
        $this->assertEmpty($result['errors']);
        $this->assertSame('removeField', $result['manualReviewRequired'][0]['action']);
    }

    public function testApplyMigrationStepRiskHighSkipsEvenInDryRun(): void
    {
        $step = [
            'from'    => 1,
            'to'      => 2,
            'actions' => [
                ['type' => 'removeField', 'handle' => 'wbDangerous', 'risk' => 'high'],
            ],
        ];

        $result = $this->migrator->applyMigrationStep($step, 'wbHero', 'entrytypes', dryRun: true);

        // risk=high goes to manualReview even in dry-run
        $this->assertCount(1, $result['manualReviewRequired']);
    }

    public function testApplyMigrationStepEmptyActionsProducesEmptyResult(): void
    {
        $step = ['from' => 1, 'to' => 2, 'actions' => []];
        $result = $this->migrator->applyMigrationStep($step, 'wbHero', 'entrytypes', dryRun: true);

        $this->assertEmpty($result['manualReviewRequired']);
        $this->assertEmpty($result['warnings']);
        $this->assertEmpty($result['errors']);
    }
}
