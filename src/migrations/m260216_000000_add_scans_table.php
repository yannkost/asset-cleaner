<?php

declare(strict_types=1);

namespace yann\assetcleaner\migrations;

use craft\db\Migration;

/**
 * Adds the assetcleaner_scans table for existing installs
 */
class m260216_000000_add_scans_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        if ($this->db->tableExists('{{%assetcleaner_scans}}')) {
            return true;
        }

        $this->createTable('{{%assetcleaner_scans}}', [
            'id' => $this->primaryKey(),
            'volumeIds' => $this->text(),
            'status' => $this->string()->notNull()->defaultValue('pending'),
            'progress' => $this->integer()->notNull()->defaultValue(0),
            'totalAssets' => $this->integer()->notNull()->defaultValue(0),
            'processedAssets' => $this->integer()->notNull()->defaultValue(0),
            'usedCount' => $this->integer()->notNull()->defaultValue(0),
            'unusedCount' => $this->integer()->notNull()->defaultValue(0),
            'results' => $this->longText(),
            'error' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->dropTableIfExists('{{%assetcleaner_scans}}');
        return true;
    }
}
