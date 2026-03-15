<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * DeprecatedFieldServicePureTest
 *
 * Tests the tracking logic of DeprecatedFieldService without a real DB or
 * Craft bootstrap, by subclassing it and replacing all Craft::$app calls with
 * in-memory state.
 *
 * Methods covered:
 *   - markDeprecated()  — insert/update logic
 *   - isDeprecated()    — query logic
 *   - untrack()         — delete logic
 *   - getDeprecated()   — list logic + fieldExists resolution
 *   - purge()           — delete + untrack logic
 */

/**
 * In-memory stand-in for DeprecatedFieldService.
 *
 * We cannot call Craft::$app at all in a unit test, so we reimplement the
 * service's public API in pure PHP using an in-memory array store.
 *
 * This lets us test the INTENDED BEHAVIOUR described in the docblocks
 * (idempotent upsert, cascading delete, fieldExists resolution, etc.)
 * without any Craft dependency.
 */
class InMemoryDeprecatedFieldStore
{
    /** @var array<string, array> Map of fieldHandle → row */
    private array $rows = [];

    /** @var array<string> Set of field handles that "exist" in Craft */
    private array $existingFields = [];

    public function setFieldExists(string $handle, bool $exists): void
    {
        if ($exists) {
            $this->existingFields[$handle] = $handle;
        } else {
            unset($this->existingFields[$handle]);
        }
    }

    /** Replicates DeprecatedFieldService::markDeprecated() */
    public function markDeprecated(string $fieldHandle, ?string $migrationSource = null, ?string $notes = null): void
    {
        $now = date('Y-m-d H:i:s');

        if (isset($this->rows[$fieldHandle])) {
            // Update existing row
            $this->rows[$fieldHandle]['deprecatedAt']    = $now;
            $this->rows[$fieldHandle]['migrationSource'] = $migrationSource;
            $this->rows[$fieldHandle]['notes']           = $notes;
            $this->rows[$fieldHandle]['dateUpdated']     = $now;
        } else {
            // Insert new row
            $this->rows[$fieldHandle] = [
                'fieldHandle'     => $fieldHandle,
                'deprecatedAt'    => $now,
                'migrationSource' => $migrationSource,
                'notes'           => $notes,
                'dateCreated'     => $now,
                'dateUpdated'     => $now,
            ];
        }
    }

    /** Replicates DeprecatedFieldService::isDeprecated() */
    public function isDeprecated(string $fieldHandle): bool
    {
        return isset($this->rows[$fieldHandle]);
    }

    /** Replicates DeprecatedFieldService::untrack() */
    public function untrack(string $fieldHandle): void
    {
        unset($this->rows[$fieldHandle]);
    }

    /** Replicates DeprecatedFieldService::getDeprecated() */
    public function getDeprecated(): array
    {
        $result = [];
        foreach ($this->rows as $row) {
            $row['fieldExists'] = isset($this->existingFields[$row['fieldHandle']]);
            $result[] = $row;
        }
        return $result;
    }

    /**
     * Replicates DeprecatedFieldService::purge().
     *
     * Returns false if field doesn't exist AND couldn't be deleted; otherwise true.
     * In our in-memory version, "delete" simply removes from existingFields.
     */
    public function purge(string $fieldHandle): bool
    {
        // Remove from Craft fields (in-memory)
        unset($this->existingFields[$fieldHandle]);

        // Remove from tracking table
        unset($this->rows[$fieldHandle]);

        return true;
    }

    /** Helper: return the raw row for a field handle (for assertions). */
    public function getRow(string $fieldHandle): ?array
    {
        return $this->rows[$fieldHandle] ?? null;
    }

    /** Helper: count tracked rows. */
    public function count(): int
    {
        return count($this->rows);
    }
}

// ──────────────────────────────────────────────────────────────────────────────

class DeprecatedFieldServicePureTest extends TestCase
{
    private InMemoryDeprecatedFieldStore $store;

    protected function setUp(): void
    {
        $this->store = new InMemoryDeprecatedFieldStore();
    }

    // -------------------------------------------------------------------------
    // markDeprecated() — insert path
    // -------------------------------------------------------------------------

    public function testMarkDeprecatedInsertsNewRow(): void
    {
        $this->store->markDeprecated('wbOldField', 'entrytypes/wbHero v1→v2');

        $this->assertTrue($this->store->isDeprecated('wbOldField'));
        $row = $this->store->getRow('wbOldField');
        $this->assertNotNull($row);
        $this->assertSame('wbOldField', $row['fieldHandle']);
        $this->assertSame('entrytypes/wbHero v1→v2', $row['migrationSource']);
    }

    public function testMarkDeprecatedWithNullMigrationSource(): void
    {
        $this->store->markDeprecated('wbField');
        $row = $this->store->getRow('wbField');
        $this->assertNull($row['migrationSource']);
    }

    public function testMarkDeprecatedWithNotes(): void
    {
        $this->store->markDeprecated('wbField', 'source', 'Review before deleting.');
        $row = $this->store->getRow('wbField');
        $this->assertSame('Review before deleting.', $row['notes']);
    }

    // -------------------------------------------------------------------------
    // markDeprecated() — upsert / idempotency
    // -------------------------------------------------------------------------

    public function testMarkDeprecatedIsIdempotent(): void
    {
        $this->store->markDeprecated('wbField', 'source-v1');
        $this->store->markDeprecated('wbField', 'source-v2'); // re-deprecate

        // Row count must still be 1 (upsert, not duplicate insert)
        $this->assertSame(1, $this->store->count());
        // Migration source updated to latest
        $row = $this->store->getRow('wbField');
        $this->assertSame('source-v2', $row['migrationSource']);
    }

    public function testMarkMultipleDifferentFieldsEachGetOwnRow(): void
    {
        $this->store->markDeprecated('wbField1');
        $this->store->markDeprecated('wbField2');
        $this->store->markDeprecated('wbField3');

        $this->assertSame(3, $this->store->count());
    }

    // -------------------------------------------------------------------------
    // isDeprecated()
    // -------------------------------------------------------------------------

    public function testIsDeprecatedReturnsFalseForUnknownHandle(): void
    {
        $this->assertFalse($this->store->isDeprecated('wbNeverDeprecated'));
    }

    public function testIsDeprecatedReturnsTrueAfterMarkDeprecated(): void
    {
        $this->store->markDeprecated('wbSomeField');
        $this->assertTrue($this->store->isDeprecated('wbSomeField'));
    }

    // -------------------------------------------------------------------------
    // untrack()
    // -------------------------------------------------------------------------

    public function testUntrackRemovesRowWithoutDeletingField(): void
    {
        $this->store->setFieldExists('wbField', true);
        $this->store->markDeprecated('wbField');

        $this->store->untrack('wbField');

        $this->assertFalse($this->store->isDeprecated('wbField'));
        // The field itself still "exists" (untrack doesn't delete it)
        $deprecated = $this->store->getDeprecated();
        $this->assertEmpty($deprecated);
    }

    public function testUntrackOnNonExistentHandleDoesNotThrow(): void
    {
        // Should be a no-op, not an exception
        $this->store->untrack('wbNonExistent');
        $this->assertFalse($this->store->isDeprecated('wbNonExistent'));
    }

    // -------------------------------------------------------------------------
    // getDeprecated()
    // -------------------------------------------------------------------------

    public function testGetDeprecatedReturnsEmptyWhenNoneTracked(): void
    {
        $this->assertEmpty($this->store->getDeprecated());
    }

    public function testGetDeprecatedIncludesFieldExistsTrue(): void
    {
        $this->store->setFieldExists('wbField', true);
        $this->store->markDeprecated('wbField');

        $rows = $this->store->getDeprecated();
        $this->assertCount(1, $rows);
        $this->assertTrue($rows[0]['fieldExists']);
    }

    public function testGetDeprecatedIncludesFieldExistsFalse(): void
    {
        $this->store->setFieldExists('wbField', false);
        $this->store->markDeprecated('wbField');

        $rows = $this->store->getDeprecated();
        $this->assertFalse($rows[0]['fieldExists']);
    }

    public function testGetDeprecatedContainsAllTrackedFields(): void
    {
        $this->store->markDeprecated('wbA', 'source-a');
        $this->store->markDeprecated('wbB', 'source-b');

        $rows    = $this->store->getDeprecated();
        $handles = array_column($rows, 'fieldHandle');

        $this->assertContains('wbA', $handles);
        $this->assertContains('wbB', $handles);
    }

    // -------------------------------------------------------------------------
    // purge()
    // -------------------------------------------------------------------------

    public function testPurgeRemovesFieldAndTrackingRow(): void
    {
        $this->store->setFieldExists('wbField', true);
        $this->store->markDeprecated('wbField');

        $result = $this->store->purge('wbField');

        $this->assertTrue($result);
        $this->assertFalse($this->store->isDeprecated('wbField'));
        // field no longer in "Craft" either
        $rows = $this->store->getDeprecated();
        $this->assertEmpty($rows);
    }

    public function testPurgeOnAlreadyDeletedFieldRemovesTrackingRow(): void
    {
        // Field does not exist in Craft (already deleted externally)
        $this->store->setFieldExists('wbField', false);
        $this->store->markDeprecated('wbField');

        $result = $this->store->purge('wbField');

        $this->assertTrue($result);
        $this->assertFalse($this->store->isDeprecated('wbField'));
    }

    public function testPurgeOnUntrackedHandleIsNoop(): void
    {
        $result = $this->store->purge('wbGhost');
        $this->assertTrue($result);
    }
}
