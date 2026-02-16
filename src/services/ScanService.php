<?php

declare(strict_types=1);

namespace yann\assetcleaner\services;

use Craft;
use craft\base\Component;
use craft\db\Query;
use craft\helpers\Db;
use craft\helpers\DateTimeHelper;

/**
 * Scan Service — CRUD for scan records in the assetcleaner_scans table
 */
class ScanService extends Component
{
    private const TABLE = '{{%assetcleaner_scans}}';

    /**
     * Create a new pending scan record
     *
     * @param array $volumeIds
     * @return int The scan ID
     */
    public function createScan(array $volumeIds): int
    {
        $now = Db::prepareDateForDb(DateTimeHelper::currentUTCDateTime());

        Craft::$app->getDb()->createCommand()->insert(self::TABLE, [
            'volumeIds' => json_encode($volumeIds),
            'status' => 'pending',
            'progress' => 0,
            'totalAssets' => 0,
            'processedAssets' => 0,
            'usedCount' => 0,
            'unusedCount' => 0,
            'dateCreated' => $now,
            'dateUpdated' => $now,
            'uid' => \craft\helpers\StringHelper::UUID(),
        ])->execute();

        return (int)Craft::$app->getDb()->getLastInsertID(self::TABLE);
    }

    /**
     * Update a scan record with the given attributes
     *
     * @param int $scanId
     * @param array $attributes
     */
    public function updateScan(int $scanId, array $attributes): void
    {
        $attributes['dateUpdated'] = Db::prepareDateForDb(DateTimeHelper::currentUTCDateTime());

        Craft::$app->getDb()->createCommand()->update(
            self::TABLE,
            $attributes,
            ['id' => $scanId]
        )->execute();
    }

    /**
     * Fetch a scan record by ID
     *
     * @param int $scanId
     * @return array|null
     */
    public function getScan(int $scanId): ?array
    {
        $row = (new Query())
            ->from(self::TABLE)
            ->where(['id' => $scanId])
            ->one();

        return $row ?: null;
    }

    /**
     * Cancel any active (pending/running) scans by marking them as failed
     */
    public function cancelActiveScan(): void
    {
        $now = Db::prepareDateForDb(DateTimeHelper::currentUTCDateTime());

        Craft::$app->getDb()->createCommand()->update(
            self::TABLE,
            [
                'status' => 'failed',
                'error' => 'Cancelled — a new scan was started.',
                'dateUpdated' => $now,
            ],
            ['status' => ['pending', 'running']]
        )->execute();
    }

    /**
     * Get the most recent completed scan
     *
     * @return array|null
     */
    public function getLatestCompletedScan(): ?array
    {
        $row = (new Query())
            ->from(self::TABLE)
            ->where(['status' => 'complete'])
            ->orderBy(['dateCreated' => SORT_DESC])
            ->one();

        return $row ?: null;
    }

    /**
     * Prune scan records older than the given number of days
     *
     * @param int $days
     */
    public function cleanupOldScans(int $days = 7): void
    {
        $cutoff = (new \DateTime())->modify("-{$days} days")->format('Y-m-d H:i:s');

        Craft::$app->getDb()->createCommand()->delete(
            self::TABLE,
            ['<', 'dateCreated', $cutoff]
        )->execute();
    }
}
