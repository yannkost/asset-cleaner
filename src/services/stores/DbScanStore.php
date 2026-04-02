<?php

declare(strict_types=1);

namespace yann\assetcleaner\services\stores;

use Craft;
use craft\helpers\Json;
use yann\assetcleaner\helpers\Logger;
use yii\db\Connection;
use yii\db\Query;

/**
 * Database-backed scan store implementation.
 *
 * This store is intended for environments where web requests and queue workers
 * cannot rely on a shared filesystem, such as containerized or cloud-style
 * deployments.
 */
class DbScanStore implements ScanStoreInterface
{
    private const TABLE_SCANS = '{{%assetcleaner_scans}}';
    private const TABLE_SCAN_ASSETS = '{{%assetcleaner_scan_assets}}';
    private const TABLE_SCAN_USED_ASSETS = '{{%assetcleaner_scan_usedassets}}';
    private const TABLE_SCAN_RESULTS = '{{%assetcleaner_scan_results}}';

    /**
     * @inheritdoc
     */
    public function clearRetainedScans(): void
    {
        $db = $this->db();
        $transaction = $db->beginTransaction();

        try {
            if ($this->tableExists(self::TABLE_SCAN_RESULTS)) {
                $db->createCommand()->delete(self::TABLE_SCAN_RESULTS)->execute();
            }
            if ($this->tableExists(self::TABLE_SCAN_USED_ASSETS)) {
                $db->createCommand()->delete(self::TABLE_SCAN_USED_ASSETS)->execute();
            }
            if ($this->tableExists(self::TABLE_SCAN_ASSETS)) {
                $db->createCommand()->delete(self::TABLE_SCAN_ASSETS)->execute();
            }
            if ($this->tableExists(self::TABLE_SCANS)) {
                $db->createCommand()->delete(self::TABLE_SCANS)->execute();
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Logger::exception('Failed clearing retained database-backed scan rows.', $e);
            throw $e;
        }
    }

    /**
     * @inheritdoc
     */
    public function initializeScan(string $scanId, array $volumeIds, int $assetChunkSize, int $entryBatchSize): void
    {
        $now = time();

        $payload = [
            'scanId' => $scanId,
            'status' => 'pending',
            'stage' => 'setup',
            'progress' => 0,
            'volumeIds' => Json::encode(array_values(array_unique(array_map('intval', $volumeIds)))),
            'assetChunkSize' => max(1, $assetChunkSize),
            'entryBatchSize' => max(1, $entryBatchSize),
            'totalAssets' => 0,
            'processedAssets' => 0,
            'usedCount' => 0,
            'unusedCount' => 0,
            'error' => null,
            'createdAt' => $now,
            'updatedAt' => $now,
            'completedAt' => null,
            'uid' => $this->generateUid(),
        ];

        $this->db()->createCommand()->insert(self::TABLE_SCANS, $payload)->execute();
    }

    /**
     * @inheritdoc
     */
    public function scanExists(string $scanId): bool
    {
        if (!$this->tableExists(self::TABLE_SCANS)) {
            return false;
        }

        return (new Query())
            ->from(self::TABLE_SCANS)
            ->where(['scanId' => $scanId])
            ->exists($this->db());
    }

    /**
     * @inheritdoc
     */
    public function getMeta(string $scanId): ?array
    {
        $row = $this->fetchScanRow($scanId);

        return $row ? $this->normalizeScanRow($row) : null;
    }

    /**
     * @inheritdoc
     */
    public function getProgress(string $scanId): ?array
    {
        $meta = $this->getMeta($scanId);
        if ($meta === null) {
            return null;
        }

        return [
            'status' => $meta['status'],
            'stage' => $meta['stage'],
            'progress' => $meta['progress'],
            'totalAssets' => $meta['totalAssets'],
            'processedAssets' => $meta['processedAssets'],
            'usedCount' => $meta['usedCount'],
            'unusedCount' => $meta['unusedCount'],
            'error' => $meta['error'],
            'updatedAt' => $meta['updatedAt'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function updateMeta(string $scanId, array $updates): void
    {
        $this->mergeIntoScanRow($scanId, $updates);
    }

    /**
     * @inheritdoc
     */
    public function updateProgress(string $scanId, array $updates): void
    {
        $this->mergeIntoScanRow($scanId, $updates);
    }

    /**
     * @inheritdoc
     */
    public function failScan(string $scanId, string $message): void
    {
        if (!$this->scanExists($scanId)) {
            return;
        }

        $this->mergeIntoScanRow($scanId, [
            'status' => 'failed',
            'error' => $message,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getResults(string $scanId): ?array
    {
        if (!$this->tableExists(self::TABLE_SCAN_RESULTS)) {
            return null;
        }

        $rows = (new Query())
            ->from(self::TABLE_SCAN_RESULTS)
            ->where(['scanId' => $scanId])
            ->orderBy(['volumeId' => SORT_ASC, 'assetId' => SORT_ASC])
            ->all($this->db());

        if (empty($rows)) {
            return $this->scanExists($scanId) ? [] : null;
        }

        return array_map([$this, 'normalizeResultRow'], $rows);
    }

    /**
     * @inheritdoc
     */
    public function hasResults(string $scanId): bool
    {
        if ($this->tableExists(self::TABLE_SCAN_RESULTS) && (new Query())
            ->from(self::TABLE_SCAN_RESULTS)
            ->where(['scanId' => $scanId])
            ->exists($this->db())) {
            return true;
        }

        $scan = $this->fetchScanRow($scanId);
        if ($scan === null) {
            return false;
        }

        return ($scan['status'] ?? null) === 'complete'
            && !empty($scan['completedAt']);
    }

    /**
     * @inheritdoc
     */
    public function getLastScan(): ?array
    {
        $row = (new Query())
            ->from(self::TABLE_SCANS)
            ->where(['status' => 'complete'])
            ->andWhere(['not', ['completedAt' => null]])
            ->orderBy(['completedAt' => SORT_DESC, 'updatedAt' => SORT_DESC])
            ->one($this->db());

        if (!$row) {
            return null;
        }

        $scanId = (string)$row['scanId'];
        if (!$this->hasResults($scanId)) {
            return null;
        }

        return [
            'scanId' => $scanId,
            'completedAt' => (int)$row['completedAt'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function setLastScan(string $scanId, ?int $completedAt = null): void
    {
        if (!$this->scanExists($scanId)) {
            return;
        }

        $this->mergeIntoScanRow($scanId, [
            'completedAt' => $completedAt ?? time(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function resetAssetSnapshot(string $scanId): void
    {
        if (!$this->tableExists(self::TABLE_SCAN_ASSETS)) {
            return;
        }

        $this->db()->createCommand()
            ->delete(self::TABLE_SCAN_ASSETS, ['scanId' => $scanId])
            ->execute();
    }

    /**
     * @inheritdoc
     */
    public function storeAssetSnapshotChunk(string $scanId, int $chunkIndex, array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        $columns = ['scanId', 'assetId', 'filename', 'volumeId', 'volumeHandle', 'folderPath', 'pathCandidates', 'uid'];
        $batchRows = [];

        foreach ($rows as $row) {
            $batchRows[] = [
                $scanId,
                (int)($row['id'] ?? 0),
                (string)($row['filename'] ?? ''),
                isset($row['volumeId']) ? (int)$row['volumeId'] : null,
                $row['volumeHandle'] !== null ? (string)$row['volumeHandle'] : null,
                $row['folderPath'] !== null ? (string)$row['folderPath'] : null,
                Json::encode(array_values($row['pathCandidates'] ?? [])),
                $this->generateUid(),
            ];
        }

        $this->db()->createCommand()
            ->batchInsert(self::TABLE_SCAN_ASSETS, $columns, $batchRows)
            ->execute();
    }

    /**
     * @inheritdoc
     */
    public function iterateAssetSnapshot(string $scanId): iterable
    {
        $query = (new Query())
            ->from(self::TABLE_SCAN_ASSETS)
            ->where(['scanId' => $scanId])
            ->orderBy(['assetId' => SORT_ASC]);

        foreach ($query->batch(500, $this->db()) as $batch) {
            foreach ($batch as $row) {
                yield $this->normalizeAssetSnapshotRow($row);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function replaceUsedIds(string $scanId, string $source, array $assetIds): void
    {
        $db = $this->db();
        $transaction = $db->beginTransaction();

        try {
            $db->createCommand()
                ->delete(self::TABLE_SCAN_USED_ASSETS, [
                    'scanId' => $scanId,
                    'source' => $source,
                ])
                ->execute();

            $assetIds = array_values(array_unique(array_filter(array_map('intval', $assetIds))));
            if (!empty($assetIds)) {
                $rows = [];
                foreach ($assetIds as $assetId) {
                    $rows[] = [
                        $scanId,
                        $assetId,
                        $source,
                        $this->generateUid(),
                    ];
                }

                $db->createCommand()
                    ->batchInsert(
                        self::TABLE_SCAN_USED_ASSETS,
                        ['scanId', 'assetId', 'source', 'uid'],
                        $rows
                    )
                    ->execute();
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Logger::exception('Failed replacing used asset IDs in database-backed scan storage.', $e, [
                'scanId' => $scanId,
                'source' => $source,
            ]);
            throw $e;
        }
    }

    /**
     * @inheritdoc
     */
    public function getMergedUsedIds(string $scanId): array
    {
        if (!$this->tableExists(self::TABLE_SCAN_USED_ASSETS)) {
            return [];
        }

        $ids = (new Query())
            ->select(['assetId'])
            ->distinct()
            ->from(self::TABLE_SCAN_USED_ASSETS)
            ->where(['scanId' => $scanId])
            ->column($this->db());

        $ids = array_map('intval', $ids);
        sort($ids, SORT_NUMERIC);

        return $ids;
    }

    /**
     * @inheritdoc
     */
    public function resetResults(string $scanId): void
    {
        if (!$this->tableExists(self::TABLE_SCAN_RESULTS)) {
            return;
        }

        $this->db()->createCommand()
            ->delete(self::TABLE_SCAN_RESULTS, ['scanId' => $scanId])
            ->execute();
    }

    /**
     * @inheritdoc
     */
    public function appendResultRows(string $scanId, array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        $columns = [
            'scanId',
            'assetId',
            'title',
            'filename',
            'url',
            'cpUrl',
            'volume',
            'volumeId',
            'size',
            'path',
            'kind',
            'uid',
        ];

        $batchRows = [];
        foreach ($rows as $row) {
            $batchRows[] = [
                $scanId,
                (int)($row['id'] ?? $row['assetId'] ?? 0),
                (string)($row['title'] ?? ''),
                (string)($row['filename'] ?? ''),
                isset($row['url']) ? (string)$row['url'] : null,
                isset($row['cpUrl']) ? (string)$row['cpUrl'] : null,
                isset($row['volume']) ? (string)$row['volume'] : null,
                isset($row['volumeId']) ? (int)$row['volumeId'] : null,
                isset($row['size']) ? (int)$row['size'] : 0,
                isset($row['path']) ? (string)$row['path'] : null,
                isset($row['kind']) ? (string)$row['kind'] : null,
                $this->generateUid(),
            ];
        }

        $this->db()->createCommand()
            ->batchInsert(self::TABLE_SCAN_RESULTS, $columns, $batchRows)
            ->execute();
    }

    /**
     * @return Connection
     */
    private function db(): Connection
    {
        return Craft::$app->getDb();
    }

    /**
     * @param string $table
     * @return bool
     */
    private function tableExists(string $table): bool
    {
        return $this->db()->getTableSchema($table, true) !== null;
    }

    /**
     * @param string $scanId
     * @return array|null
     */
    private function fetchScanRow(string $scanId): ?array
    {
        if (!$this->tableExists(self::TABLE_SCANS)) {
            return null;
        }

        $row = (new Query())
            ->from(self::TABLE_SCANS)
            ->where(['scanId' => $scanId])
            ->one($this->db());

        return is_array($row) ? $row : null;
    }

    /**
     * @param string $scanId
     * @param array $updates
     * @return void
     */
    private function mergeIntoScanRow(string $scanId, array $updates): void
    {
        $row = $this->fetchScanRow($scanId);
        if ($row === null) {
            return;
        }

        $current = $this->normalizeScanRow($row);
        $merged = array_merge($current, $updates);
        $payload = $this->serializeScanPayload($merged);

        if (!isset($payload['updatedAt'])) {
            $payload['updatedAt'] = time();
        }

        $this->db()->createCommand()
            ->update(self::TABLE_SCANS, $payload, ['scanId' => $scanId])
            ->execute();
    }

    /**
     * @param array $row
     * @return array
     */
    private function normalizeScanRow(array $row): array
    {
        $assetChunkSize = max(1, (int)($row['assetChunkSize'] ?? 100));
        $totalAssets = (int)($row['totalAssets'] ?? 0);

        return [
            'scanId' => (string)$row['scanId'],
            'createdAt' => (int)($row['createdAt'] ?? 0),
            'updatedAt' => (int)($row['updatedAt'] ?? 0),
            'completedAt' => isset($row['completedAt']) ? (int)$row['completedAt'] : null,
            'volumeIds' => $this->decodeIntArray($row['volumeIds'] ?? null),
            'assetChunkSize' => $assetChunkSize,
            'entryBatchSize' => max(1, (int)($row['entryBatchSize'] ?? 200)),
            'status' => (string)($row['status'] ?? 'pending'),
            'stage' => (string)($row['stage'] ?? 'setup'),
            'progress' => (int)($row['progress'] ?? 0),
            'totalAssets' => $totalAssets,
            'totalChunks' => $totalAssets > 0 ? (int)ceil($totalAssets / $assetChunkSize) : 0,
            'processedAssets' => (int)($row['processedAssets'] ?? 0),
            'usedCount' => (int)($row['usedCount'] ?? 0),
            'unusedCount' => (int)($row['unusedCount'] ?? 0),
            'error' => $row['error'] !== null ? (string)$row['error'] : null,
        ];
    }

    /**
     * @param array $meta
     * @return array
     */
    private function serializeScanPayload(array $meta): array
    {
        return [
            'status' => (string)($meta['status'] ?? 'pending'),
            'stage' => (string)($meta['stage'] ?? 'setup'),
            'progress' => (int)($meta['progress'] ?? 0),
            'volumeIds' => Json::encode(array_values(array_unique(array_map('intval', $meta['volumeIds'] ?? [])))),
            'assetChunkSize' => max(1, (int)($meta['assetChunkSize'] ?? 100)),
            'entryBatchSize' => max(1, (int)($meta['entryBatchSize'] ?? 200)),
            'totalAssets' => (int)($meta['totalAssets'] ?? 0),
            'processedAssets' => (int)($meta['processedAssets'] ?? 0),
            'usedCount' => (int)($meta['usedCount'] ?? 0),
            'unusedCount' => (int)($meta['unusedCount'] ?? 0),
            'error' => isset($meta['error']) ? (string)$meta['error'] : null,
            'updatedAt' => (int)($meta['updatedAt'] ?? time()),
            'completedAt' => isset($meta['completedAt']) ? (int)$meta['completedAt'] : null,
        ];
    }

    /**
     * @param array $row
     * @return array
     */
    private function normalizeAssetSnapshotRow(array $row): array
    {
        return [
            'id' => (int)$row['assetId'],
            'filename' => (string)$row['filename'],
            'volumeId' => isset($row['volumeId']) ? (int)$row['volumeId'] : null,
            'volumeHandle' => $row['volumeHandle'] !== null ? (string)$row['volumeHandle'] : null,
            'folderPath' => $row['folderPath'] !== null ? (string)$row['folderPath'] : '',
            'pathCandidates' => $this->decodeStringArray($row['pathCandidates'] ?? null),
        ];
    }

    /**
     * @param array $row
     * @return array
     */
    private function normalizeResultRow(array $row): array
    {
        return [
            'id' => (int)$row['assetId'],
            'title' => (string)$row['title'],
            'filename' => (string)$row['filename'],
            'url' => $row['url'] !== null ? (string)$row['url'] : null,
            'cpUrl' => $row['cpUrl'] !== null ? (string)$row['cpUrl'] : null,
            'volume' => $row['volume'] !== null ? (string)$row['volume'] : '',
            'volumeId' => isset($row['volumeId']) ? (int)$row['volumeId'] : null,
            'size' => (int)($row['size'] ?? 0),
            'path' => $row['path'] !== null ? (string)$row['path'] : '',
            'kind' => $row['kind'] !== null ? (string)$row['kind'] : '',
        ];
    }

    /**
     * @param mixed $value
     * @return array<int>
     */
    private function decodeIntArray(mixed $value): array
    {
        $decoded = $this->decodeJsonArray($value);

        return array_values(array_map('intval', $decoded));
    }

    /**
     * @param mixed $value
     * @return array<string>
     */
    private function decodeStringArray(mixed $value): array
    {
        $decoded = $this->decodeJsonArray($value);

        return array_values(array_map('strval', $decoded));
    }

    /**
     * @param mixed $value
     * @return array
     */
    private function decodeJsonArray(mixed $value): array
    {
        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        try {
            $decoded = Json::decode($value);

            return is_array($decoded) ? $decoded : [];
        } catch (\Throwable $e) {
            Logger::warning('Failed decoding JSON payload from database-backed scan storage.', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * @return string
     */
    private function generateUid(): string
    {
        return Craft::$app->getSecurity()->generateRandomString(36);
    }
}
