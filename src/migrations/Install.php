<?php

declare(strict_types=1);

namespace yann\assetcleaner\migrations;

use craft\db\Migration;

/**
 * Install migration - creates the assetcleaner_scans table
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->createScansTable();
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

    private function createScansTable(): void
    {
        if ($this->db->tableExists('{{%assetcleaner_scans}}')) {
            return;
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
    }
}
