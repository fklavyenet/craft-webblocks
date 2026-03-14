<?php

namespace fklavyenet\webblocks\migrations;

use craft\db\Migration;

/**
 * WebBlocks install migration.
 *
 * Creates:
 *   - webblocks_component_versions — tracks installed version/checksum of
 *     every component JSON file (Component Versioning & Migration system)
 *   - webblocks_deprecated_fields  — tracks Craft fields that have been
 *     deprecated by a migration step (data kept; cleanup deferred)
 */
class Install extends Migration
{
    public function safeUp(): bool
    {
        // ── Component versions table ─────────────────────────────────────────
        if (!$this->db->tableExists('{{%webblocks_component_versions}}')) {
            $this->createTable('{{%webblocks_component_versions}}', [
                'id'               => $this->primaryKey(),
                'componentHandle'  => $this->string(255)->notNull(),
                'componentType'    => $this->string(64)->notNull(),
                'installedVersion' => $this->integer()->notNull()->defaultValue(0),
                'checksum'         => $this->string(32)->notNull()->defaultValue(''),
                'lastAppliedAt'    => $this->dateTime()->null(),
                'dateCreated'      => $this->dateTime()->notNull(),
                'dateUpdated'      => $this->dateTime()->notNull(),
                'uid'              => $this->uid(),
            ]);

            $this->createIndex(
                'idx_webblocks_component_versions_handle_type',
                '{{%webblocks_component_versions}}',
                ['componentHandle', 'componentType'],
                true  // unique
            );
        }

        // ── Deprecated fields table ──────────────────────────────────────────
        if (!$this->db->tableExists('{{%webblocks_deprecated_fields}}')) {
            $this->createTable('{{%webblocks_deprecated_fields}}', [
                'id'              => $this->primaryKey(),
                'fieldHandle'     => $this->string(255)->notNull(),
                'deprecatedAt'    => $this->dateTime()->notNull(),
                'migrationSource' => $this->string(512)->null(),  // e.g. "entrytypes/wbHero v1→v2"
                'notes'           => $this->text()->null(),
                'dateCreated'     => $this->dateTime()->notNull(),
                'dateUpdated'     => $this->dateTime()->notNull(),
                'uid'             => $this->uid(),
            ]);

            $this->createIndex(
                'idx_webblocks_deprecated_fields_handle',
                '{{%webblocks_deprecated_fields}}',
                ['fieldHandle'],
                true  // unique — one deprecation record per field handle
            );
        }

        return true;
    }

    public function safeDown(): bool
    {
        $this->dropTableIfExists('{{%webblocks_component_versions}}');
        $this->dropTableIfExists('{{%webblocks_deprecated_fields}}');
        return true;
    }
}
