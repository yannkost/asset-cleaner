<?php

declare(strict_types=1);

namespace yann\assetcleaner\migrations;

use craft\db\Migration;

/**
 * Adds database-backed scan storage tables.
 */
class m260402_150000_add_db_scan_storage extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->createScansTable();
        $this->createScanAssetsTable();
        $this->createScanUsedAssetsTable();
        $this->createScanResultsTable();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->dropTableIfExists('{{%assetcleaner_scan_results}}');
        $this->dropTableIfExists('{{%assetcleaner_scan_usedassets}}');
        $this->dropTableIfExists('{{%assetcleaner_scan_assets}}');
        $this->dropTableIfExists('{{%assetcleaner_scans}}');

        return true;
    }

    private function createScansTable(): void
    {
        $this->dropTableIfExists('{{%assetcleaner_scans}}');

        $this->createTable('{{%assetcleaner_scans}}', [
            'id' => $this->primaryKey(),
            'scanId' => $this->string()->notNull(),
            'status' => $this->string()->notNull(),
            'stage' => $this->string()->notNull(),
            'progress' => $this->integer()->notNull()->defaultValue(0),
            'volumeIds' => $this->text(),
            'includeDrafts' => $this->boolean()->notNull()->defaultValue(false),
            'assetChunkSize' => $this->integer()->notNull()->defaultValue(100),
            'entryBatchSize' => $this->integer()->notNull()->defaultValue(200),
            'totalAssets' => $this->integer()->notNull()->defaultValue(0),
            'processedAssets' => $this->integer()->notNull()->defaultValue(0),
            'usedCount' => $this->integer()->notNull()->defaultValue(0),
            'unusedCount' => $this->integer()->notNull()->defaultValue(0),
            'error' => $this->text(),
            'createdAt' => $this->integer()->notNull(),
            'updatedAt' => $this->integer()->notNull(),
            'completedAt' => $this->integer(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(
            null,
            '{{%assetcleaner_scans}}',
            'scanId',
            true
        );
        $this->createIndex(
            null,
            '{{%assetcleaner_scans}}',
            'status',
            false
        );
        $this->createIndex(
            null,
            '{{%assetcleaner_scans}}',
            'updatedAt',
            false
        );
    }

    private function createScanAssetsTable(): void
    {
        $this->dropTableIfExists('{{%assetcleaner_scan_assets}}');

        $this->createTable('{{%assetcleaner_scan_assets}}', [
            'id' => $this->primaryKey(),
            'scanId' => $this->string()->notNull(),
            'assetId' => $this->integer()->notNull(),
            'filename' => $this->string()->notNull(),
            'volumeId' => $this->integer(),
            'volumeHandle' => $this->string(),
            'folderPath' => $this->text(),
            'pathCandidates' => $this->text(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(
            null,
            '{{%assetcleaner_scan_assets}}',
            ['scanId', 'assetId'],
            true
        );
        $this->createIndex(
            null,
            '{{%assetcleaner_scan_assets}}',
            'scanId',
            false
        );
        $this->createIndex(
            null,
            '{{%assetcleaner_scan_assets}}',
            'assetId',
            false
        );
    }

    private function createScanUsedAssetsTable(): void
    {
        $this->dropTableIfExists('{{%assetcleaner_scan_usedassets}}');

        $this->createTable('{{%assetcleaner_scan_usedassets}}', [
            'id' => $this->primaryKey(),
            'scanId' => $this->string()->notNull(),
            'assetId' => $this->integer()->notNull(),
            'source' => $this->string()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(
            null,
            '{{%assetcleaner_scan_usedassets}}',
            ['scanId', 'assetId', 'source'],
            true
        );
        $this->createIndex(
            null,
            '{{%assetcleaner_scan_usedassets}}',
            ['scanId', 'assetId'],
            false
        );
        $this->createIndex(
            null,
            '{{%assetcleaner_scan_usedassets}}',
            'scanId',
            false
        );
    }

    private function createScanResultsTable(): void
    {
        $this->dropTableIfExists('{{%assetcleaner_scan_results}}');

        $this->createTable('{{%assetcleaner_scan_results}}', [
            'id' => $this->primaryKey(),
            'scanId' => $this->string()->notNull(),
            'assetId' => $this->integer()->notNull(),
            'title' => $this->text()->notNull(),
            'filename' => $this->string()->notNull(),
            'url' => $this->text(),
            'cpUrl' => $this->text(),
            'volume' => $this->string(),
            'volumeId' => $this->integer(),
            'size' => $this->bigInteger()->notNull()->defaultValue(0),
            'path' => $this->text(),
            'kind' => $this->string(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(
            null,
            '{{%assetcleaner_scan_results}}',
            ['scanId', 'assetId'],
            true
        );
        $this->createIndex(
            null,
            '{{%assetcleaner_scan_results}}',
            'scanId',
            false
        );
        $this->createIndex(
            null,
            '{{%assetcleaner_scan_results}}',
            'volumeId',
            false
        );
    }
}
