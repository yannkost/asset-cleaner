<?php

declare(strict_types=1);

namespace yann\assetcleaner\migrations;

use craft\db\Migration;

/**
 * Adds the includeRevisions column to database-backed scan storage.
 */
class m260402_181000_add_include_revisions_to_scan_storage extends Migration
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

        if (!isset($schema->columns['includeRevisions'])) {
            $this->addColumn(
                $table,
                'includeRevisions',
                $this->boolean()->notNull()->defaultValue(false)->after('includeDrafts')
            );
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

        if (isset($schema->columns['includeRevisions'])) {
            $this->dropColumn($table, 'includeRevisions');
        }

        return true;
    }
}
