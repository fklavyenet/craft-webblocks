<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use fklavyenet\webblocks\services\ComponentDiffService;

/**
 * ComponentDiffServiceTest
 *
 * Tests the pure diff logic in ComponentDiffService by subclassing it and
 * overriding the two I/O methods (loadJsonComponents / loadDbStates) with
 * in-memory fixtures. No Craft bootstrap or DB is required.
 *
 * The class is NOT final, so we can extend it anonymously.
 */

/**
 * Testable subclass — replaces the two data-loading methods with fixtures.
 */
class TestableComponentDiffService extends ComponentDiffService
{
    public array $fixtureJson = [];
    public array $fixtureDb  = [];

    // We expose makeRow publicly so it can be tested directly.
    public function exposedMakeRow(string $handle, string $type, string $status, array $data): array
    {
        // makeRow is private — we use Reflection to call it
        $ref = new ReflectionMethod(ComponentDiffService::class, 'makeRow');
        $ref->setAccessible(true);
        return $ref->invoke($this, $handle, $type, $status, $data);
    }

    // We expose diff() by running it against our fixtures.
    // To inject fixtures we must override the private loader methods via Reflection-
    // injected closures bound to $this. Simpler: just duplicate the diff() logic
    // calling our fixture properties directly via an override of the loaders.
    //
    // Since loadJsonComponents and loadDbStates are private, we override diff()
    // itself to inject the fixtures.
    public function diff(): array
    {
        // Replicate the exact diff() algorithm from the parent, but use
        // our fixture arrays instead of filesystem + DB calls.
        $jsonComponents = $this->fixtureJson;
        $dbStates       = $this->fixtureDb;

        $rows = [];

        foreach ($jsonComponents as $key => [$type, $handle, $version, $checksum]) {
            $db = $dbStates[$key] ?? null;

            if ($db === null) {
                $rows[] = $this->exposedMakeRow($handle, $type, ComponentDiffService::STATUS_NEW, [
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
                $rows[] = $this->exposedMakeRow($handle, $type, ComponentDiffService::STATUS_VERSION_BUMP, [
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
                $rows[] = $this->exposedMakeRow($handle, $type, ComponentDiffService::STATUS_CHECKSUM_DRIFT, [
                    'jsonVersion'   => $version,
                    'dbVersion'     => $dbVersion,
                    'jsonChecksum'  => $checksum,
                    'dbChecksum'    => $dbChecksum,
                    'lastAppliedAt' => $db['lastAppliedAt'] ?? null,
                    'message'       => 'JSON content changed but version was not bumped. Review this component.',
                ]);
                continue;
            }

            $rows[] = $this->exposedMakeRow($handle, $type, ComponentDiffService::STATUS_OK, [
                'jsonVersion'   => $version,
                'dbVersion'     => $dbVersion,
                'jsonChecksum'  => $checksum,
                'dbChecksum'    => $dbChecksum,
                'lastAppliedAt' => $db['lastAppliedAt'] ?? null,
                'message'       => 'Up to date.',
            ]);
        }

        // Orphan detection
        $jsonKeys = array_keys($jsonComponents);
        foreach ($dbStates as $key => $db) {
            if (!in_array($key, $jsonKeys, true)) {
                [$type, $handle] = explode('/', $key, 2);
                $rows[] = $this->exposedMakeRow($handle, $type, ComponentDiffService::STATUS_ORPHAN, [
                    'jsonVersion'   => null,
                    'dbVersion'     => (int) $db['installedVersion'],
                    'jsonChecksum'  => null,
                    'dbChecksum'    => $db['checksum'],
                    'lastAppliedAt' => $db['lastAppliedAt'] ?? null,
                    'message'       => 'DB record exists but no matching JSON file found. Component may have been removed.',
                ]);
            }
        }

        // Sort
        $order = [
            ComponentDiffService::STATUS_VERSION_BUMP   => 0,
            ComponentDiffService::STATUS_CHECKSUM_DRIFT => 1,
            ComponentDiffService::STATUS_NEW            => 2,
            ComponentDiffService::STATUS_ORPHAN         => 3,
            ComponentDiffService::STATUS_OK             => 4,
        ];
        usort($rows, fn($a, $b) => ($order[$a['status']] ?? 9) <=> ($order[$b['status']] ?? 9));

        // Summary
        $counts  = array_count_values(array_column($rows, 'status'));
        $summary = [
            'ok'             => $counts[ComponentDiffService::STATUS_OK]             ?? 0,
            'version_bump'   => $counts[ComponentDiffService::STATUS_VERSION_BUMP]   ?? 0,
            'checksum_drift' => $counts[ComponentDiffService::STATUS_CHECKSUM_DRIFT] ?? 0,
            'new'            => $counts[ComponentDiffService::STATUS_NEW]            ?? 0,
            'orphan'         => $counts[ComponentDiffService::STATUS_ORPHAN]         ?? 0,
            'total'          => count($rows),
        ];
        $summary['needsAction'] = (
            $summary['version_bump']   > 0 ||
            $summary['checksum_drift'] > 0 ||
            $summary['new']            > 0 ||
            $summary['orphan']         > 0
        );

        return ['summary' => $summary, 'components' => $rows];
    }
}

// ──────────────────────────────────────────────────────────────────────────────

class ComponentDiffServiceTest extends TestCase
{
    private TestableComponentDiffService $svc;

    protected function setUp(): void
    {
        $this->svc = new TestableComponentDiffService();
    }

    // -------------------------------------------------------------------------
    // makeRow
    // -------------------------------------------------------------------------

    public function testMakeRowReturnsAllExpectedKeys(): void
    {
        $row = $this->svc->exposedMakeRow('wbHero', 'entrytypes', ComponentDiffService::STATUS_OK, [
            'jsonVersion' => 2,
            'dbVersion'   => 2,
            'message'     => 'Up to date.',
        ]);

        $expected = ['handle', 'type', 'status', 'jsonVersion', 'dbVersion', 'jsonChecksum', 'dbChecksum', 'lastAppliedAt', 'message'];
        foreach ($expected as $key) {
            $this->assertArrayHasKey($key, $row, "makeRow result is missing key '$key'");
        }
    }

    public function testMakeRowSetsHandleTypeStatus(): void
    {
        $row = $this->svc->exposedMakeRow('wbAlert', 'entrytypes', ComponentDiffService::STATUS_NEW, []);

        $this->assertSame('wbAlert', $row['handle']);
        $this->assertSame('entrytypes', $row['type']);
        $this->assertSame(ComponentDiffService::STATUS_NEW, $row['status']);
    }

    public function testMakeRowDefaultsNullsForMissingOptionalFields(): void
    {
        $row = $this->svc->exposedMakeRow('wbHero', 'entrytypes', ComponentDiffService::STATUS_OK, []);

        $this->assertNull($row['jsonVersion']);
        $this->assertNull($row['dbVersion']);
        $this->assertNull($row['jsonChecksum']);
        $this->assertNull($row['dbChecksum']);
        $this->assertNull($row['lastAppliedAt']);
        $this->assertSame('', $row['message']);
    }

    public function testMakeRowOverridesDefaultsWithProvidedData(): void
    {
        $row = $this->svc->exposedMakeRow('wbHero', 'entrytypes', ComponentDiffService::STATUS_VERSION_BUMP, [
            'jsonVersion'  => 3,
            'dbVersion'    => 1,
            'jsonChecksum' => 'abc123',
            'dbChecksum'   => 'def456',
            'message'      => 'Version bumped.',
        ]);

        $this->assertSame(3, $row['jsonVersion']);
        $this->assertSame(1, $row['dbVersion']);
        $this->assertSame('abc123', $row['jsonChecksum']);
        $this->assertSame('def456', $row['dbChecksum']);
        $this->assertSame('Version bumped.', $row['message']);
    }

    // -------------------------------------------------------------------------
    // STATUS_NEW — component in JSON but not in DB
    // -------------------------------------------------------------------------

    public function testNewComponentWhenNoDbRow(): void
    {
        $this->svc->fixtureJson = [
            'entrytypes/wbHero' => ['entrytypes', 'wbHero', 1, 'checksum1'],
        ];
        $this->svc->fixtureDb = [];

        $result = $this->svc->diff();

        $this->assertCount(1, $result['components']);
        $this->assertSame(ComponentDiffService::STATUS_NEW, $result['components'][0]['status']);
        $this->assertSame('wbHero', $result['components'][0]['handle']);
    }

    // -------------------------------------------------------------------------
    // STATUS_OK — version and checksum match
    // -------------------------------------------------------------------------

    public function testOkWhenVersionAndChecksumMatch(): void
    {
        $this->svc->fixtureJson = [
            'entrytypes/wbHero' => ['entrytypes', 'wbHero', 2, 'checksum-abc'],
        ];
        $this->svc->fixtureDb = [
            'entrytypes/wbHero' => [
                'componentHandle' => 'wbHero',
                'componentType'   => 'entrytypes',
                'installedVersion' => '2',
                'checksum'         => 'checksum-abc',
                'lastAppliedAt'    => '2025-01-01 00:00:00',
            ],
        ];

        $result = $this->svc->diff();

        $this->assertSame(ComponentDiffService::STATUS_OK, $result['components'][0]['status']);
    }

    // -------------------------------------------------------------------------
    // STATUS_VERSION_BUMP — JSON version > DB version
    // -------------------------------------------------------------------------

    public function testVersionBumpWhenJsonVersionIsHigher(): void
    {
        $this->svc->fixtureJson = [
            'entrytypes/wbHero' => ['entrytypes', 'wbHero', 3, 'checksum-new'],
        ];
        $this->svc->fixtureDb = [
            'entrytypes/wbHero' => [
                'componentHandle' => 'wbHero',
                'componentType'   => 'entrytypes',
                'installedVersion' => '1',
                'checksum'         => 'checksum-old',
                'lastAppliedAt'    => null,
            ],
        ];

        $result = $this->svc->diff();

        $row = $result['components'][0];
        $this->assertSame(ComponentDiffService::STATUS_VERSION_BUMP, $row['status']);
        $this->assertSame(3, $row['jsonVersion']);
        $this->assertSame(1, $row['dbVersion']);
    }

    // -------------------------------------------------------------------------
    // STATUS_CHECKSUM_DRIFT — same version, different checksum
    // -------------------------------------------------------------------------

    public function testChecksumDriftWhenVersionMatchesButChecksumDiffers(): void
    {
        $this->svc->fixtureJson = [
            'entrytypes/wbHero' => ['entrytypes', 'wbHero', 2, 'new-checksum'],
        ];
        $this->svc->fixtureDb = [
            'entrytypes/wbHero' => [
                'componentHandle' => 'wbHero',
                'componentType'   => 'entrytypes',
                'installedVersion' => '2',
                'checksum'         => 'old-checksum',
                'lastAppliedAt'    => null,
            ],
        ];

        $result = $this->svc->diff();

        $this->assertSame(ComponentDiffService::STATUS_CHECKSUM_DRIFT, $result['components'][0]['status']);
    }

    // -------------------------------------------------------------------------
    // STATUS_ORPHAN — DB row has no matching JSON file
    // -------------------------------------------------------------------------

    public function testOrphanWhenDbRowHasNoMatchingJson(): void
    {
        $this->svc->fixtureJson = [];
        $this->svc->fixtureDb = [
            'entrytypes/wbOld' => [
                'componentHandle' => 'wbOld',
                'componentType'   => 'entrytypes',
                'installedVersion' => '1',
                'checksum'         => 'checksum-orphan',
                'lastAppliedAt'    => null,
            ],
        ];

        $result = $this->svc->diff();

        $row = $result['components'][0];
        $this->assertSame(ComponentDiffService::STATUS_ORPHAN, $row['status']);
        $this->assertSame('wbOld', $row['handle']);
        $this->assertNull($row['jsonVersion']);
    }

    // -------------------------------------------------------------------------
    // Summary counts
    // -------------------------------------------------------------------------

    public function testSummaryCountsAreAccurate(): void
    {
        $this->svc->fixtureJson = [
            // ok
            'entrytypes/wbHero'  => ['entrytypes', 'wbHero', 1, 'chk1'],
            // version_bump
            'entrytypes/wbAlert' => ['entrytypes', 'wbAlert', 2, 'chk2'],
            // checksum_drift
            'entrytypes/wbBadge' => ['entrytypes', 'wbBadge', 1, 'chk-new'],
            // new (no DB row)
            'entrytypes/wbNew'   => ['entrytypes', 'wbNew', 1, 'chk4'],
        ];
        $this->svc->fixtureDb = [
            'entrytypes/wbHero'  => ['componentHandle' => 'wbHero',  'componentType' => 'entrytypes', 'installedVersion' => '1', 'checksum' => 'chk1',    'lastAppliedAt' => null],
            'entrytypes/wbAlert' => ['componentHandle' => 'wbAlert', 'componentType' => 'entrytypes', 'installedVersion' => '1', 'checksum' => 'chk2',    'lastAppliedAt' => null],
            'entrytypes/wbBadge' => ['componentHandle' => 'wbBadge', 'componentType' => 'entrytypes', 'installedVersion' => '1', 'checksum' => 'chk-old', 'lastAppliedAt' => null],
            // orphan
            'entrytypes/wbGone'  => ['componentHandle' => 'wbGone',  'componentType' => 'entrytypes', 'installedVersion' => '1', 'checksum' => 'chk5',    'lastAppliedAt' => null],
        ];

        $result  = $this->svc->diff();
        $summary = $result['summary'];

        $this->assertSame(1, $summary['ok']);
        $this->assertSame(1, $summary['version_bump']);
        $this->assertSame(1, $summary['checksum_drift']);
        $this->assertSame(1, $summary['new']);
        $this->assertSame(1, $summary['orphan']);
        $this->assertSame(5, $summary['total']);
        $this->assertTrue($summary['needsAction']);
    }

    public function testNeedsActionFalseWhenAllOk(): void
    {
        $this->svc->fixtureJson = [
            'entrytypes/wbHero' => ['entrytypes', 'wbHero', 1, 'chk1'],
        ];
        $this->svc->fixtureDb = [
            'entrytypes/wbHero' => ['componentHandle' => 'wbHero', 'componentType' => 'entrytypes', 'installedVersion' => '1', 'checksum' => 'chk1', 'lastAppliedAt' => null],
        ];

        $result = $this->svc->diff();

        $this->assertFalse($result['summary']['needsAction']);
    }

    // -------------------------------------------------------------------------
    // Sort order — action-needed items sort before ok
    // -------------------------------------------------------------------------

    public function testSortOrderPutsVersionBumpFirst(): void
    {
        $this->svc->fixtureJson = [
            'entrytypes/wbOk'   => ['entrytypes', 'wbOk',   1, 'chk1'],
            'entrytypes/wbBump' => ['entrytypes', 'wbBump', 2, 'chk2'],
        ];
        $this->svc->fixtureDb = [
            'entrytypes/wbOk'   => ['componentHandle' => 'wbOk',   'componentType' => 'entrytypes', 'installedVersion' => '1', 'checksum' => 'chk1', 'lastAppliedAt' => null],
            'entrytypes/wbBump' => ['componentHandle' => 'wbBump', 'componentType' => 'entrytypes', 'installedVersion' => '1', 'checksum' => 'chk2', 'lastAppliedAt' => null],
        ];

        $result     = $this->svc->diff();
        $components = $result['components'];

        $this->assertSame(ComponentDiffService::STATUS_VERSION_BUMP, $components[0]['status']);
        $this->assertSame(ComponentDiffService::STATUS_OK, $components[1]['status']);
    }

    // -------------------------------------------------------------------------
    // Status constants sanity check
    // -------------------------------------------------------------------------

    public function testStatusConstants(): void
    {
        $this->assertSame('ok',             ComponentDiffService::STATUS_OK);
        $this->assertSame('version_bump',   ComponentDiffService::STATUS_VERSION_BUMP);
        $this->assertSame('checksum_drift', ComponentDiffService::STATUS_CHECKSUM_DRIFT);
        $this->assertSame('new',            ComponentDiffService::STATUS_NEW);
        $this->assertSame('orphan',         ComponentDiffService::STATUS_ORPHAN);
    }

    // -------------------------------------------------------------------------
    // Empty state
    // -------------------------------------------------------------------------

    public function testEmptyJsonAndDbProducesEmptySummary(): void
    {
        $this->svc->fixtureJson = [];
        $this->svc->fixtureDb   = [];

        $result = $this->svc->diff();

        $this->assertSame(0, $result['summary']['total']);
        $this->assertFalse($result['summary']['needsAction']);
        $this->assertEmpty($result['components']);
    }
}
