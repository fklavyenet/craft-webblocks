<?php

namespace fklavyenet\webblocks\migrations;

use craft\db\Migration;

/**
 * WebBlocks install migration.
 *
 * Creates the webblocks_component_versions table, which tracks the installed
 * version and checksum of every component JSON file. Used by the Component
 * Versioning & Migration system to detect schema drift and apply updates.
 */
class Install extends Migration
{
    public function safeUp(): bool
    {
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

        return true;
    }

    public function safeDown(): bool
    {
        $this->dropTableIfExists('{{%webblocks_component_versions}}');
        return true;
    }
}
