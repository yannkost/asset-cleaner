<?php

declare(strict_types=1);

namespace yann\assetcleaner\migrations;

use craft\db\Migration;

/**
 * Adds the includeDrafts column to database-backed scan storage.
 */
class m260402_170000_add_include_drafts_to_scan_storage extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $table = '{{%assetcleaner_scans}}';

        $schema = $this->db->getTableSchema($table, true);
        if ($schema === null) {
            return true;
        }
        if ($schema !== null && !isset($schema->columns['includeDrafts'])) {
            $this->addColumn($table, 'includeDrafts', $this->boolean()->notNull()->defaultValue(false)->after('volumeIds'));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $table = '{{%assetcleaner_scans}}';

        $schema = $this->db->getTableSchema($table, true);
        if ($schema === null) {
            return true;
        }
        if ($schema !== null && isset($schema->columns['includeDrafts'])) {
            $this->dropColumn($table, 'includeDrafts');
        }

        return true;
    }
}
