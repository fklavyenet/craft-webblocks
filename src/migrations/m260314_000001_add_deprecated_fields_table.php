<?php

namespace fklavyenet\webblocks\migrations;

use craft\db\Migration;

/**
 * Adds the webblocks_deprecated_fields table for existing WebBlocks installs.
 *
 * This table tracks Craft fields that were deprecated by a component migration
 * step. Deprecated fields are removed from all field layouts but their data is
 * kept in the Craft fields table until an explicit cleanup command is run.
 *
 * Fresh installs get this table from Install::safeUp() directly.
 * Existing installs that already ran Install.php pick it up here.
 */
class m260314_000001_add_deprecated_fields_table extends Migration
{
    public function safeUp(): bool
    {
        if ($this->db->tableExists('{{%webblocks_deprecated_fields}}')) {
            return true;
        }

        $this->createTable('{{%webblocks_deprecated_fields}}', [
            'id'              => $this->primaryKey(),
            'fieldHandle'     => $this->string(255)->notNull(),
            'deprecatedAt'    => $this->dateTime()->notNull(),
            'migrationSource' => $this->string(512)->null(),
            'notes'           => $this->text()->null(),
            'dateCreated'     => $this->dateTime()->notNull(),
            'dateUpdated'     => $this->dateTime()->notNull(),
            'uid'             => $this->uid(),
        ]);

        $this->createIndex(
            'idx_webblocks_deprecated_fields_handle',
            '{{%webblocks_deprecated_fields}}',
            ['fieldHandle'],
            true
        );

        return true;
    }

    public function safeDown(): bool
    {
        $this->dropTableIfExists('{{%webblocks_deprecated_fields}}');
        return true;
    }
}
