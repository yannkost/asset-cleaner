<?php

declare(strict_types=1);

namespace yann\assetcleaner\services;

use Craft;
use craft\base\Component;
use craft\db\Query;
use craft\db\Table;
use craft\elements\Asset;
use craft\elements\Entry;
use craft\elements\GlobalSet;
use craft\helpers\FileHelper;
use craft\helpers\Json;
use yii\base\Exception;

/**
 * File-backed scan orchestration and staged scan helpers.
 *
 * This service stores scan state under @storage/asset-cleaner/scans/<scanId>
 * and provides the domain logic for:
 * - creating scan workspaces
 * - snapshotting assets into chunk files
 * - collecting used asset IDs from relations and content
 * - finalizing scan results into file-backed outputs
 */
class ScanService extends Component
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETE = 'complete';
    public const STATUS_FAILED = 'failed';

    public const STAGE_SETUP = 'setup';
    public const STAGE_RELATIONS = 'relations';
    public const STAGE_CONTENT = 'content';
    public const STAGE_FINALIZE = 'finalize';

    private const DEFAULT_ASSET_CHUNK_SIZE = 100;
    private const DEFAULT_ENTRY_BATCH_SIZE = 200;
    private const DEFAULT_RESULT_QUERY_CHUNK_SIZE = 250;
    private const LAST_SCAN_FILE = 'last-scan.json';

    /**
     * Create the scan workspace and write initial metadata/progress files.
     *
     * @param string $scanId
     * @param array $volumeIds
     * @param int $assetChunkSize
     * @return void
     * @throws Exception
     */
    public function initializeScan(string $scanId, array $volumeIds = [], int $assetChunkSize = self::DEFAULT_ASSET_CHUNK_SIZE): void
    {
        $scanId = trim($scanId);
        if ($scanId === '') {
            throw new Exception('Missing scan ID.');
        }

        $volumeIds = array_values(array_unique(array_map('intval', $volumeIds)));
        $assetChunkSize = max(1, $assetChunkSize);

        $this->ensureBaseDirectories();

        FileHelper::createDirectory($this->getScanPath($scanId));
        FileHelper::createDirectory($this->getAssetsDirectory($scanId));
        FileHelper::createDirectory($this->getUsedDirectory($scanId));
        FileHelper::createDirectory($this->getResultsDirectory($scanId));

        $now = time();

        $this->writeJsonFile($this->getMetaPath($scanId), [
            'scanId' => $scanId,
            'createdAt' => $now,
            'updatedAt' => $now,
            'completedAt' => null,
            'volumeIds' => $volumeIds,
            'assetChunkSize' => $assetChunkSize,
            'entryBatchSize' => self::DEFAULT_ENTRY_BATCH_SIZE,
            'status' => self::STATUS_PENDING,
            'stage' => self::STAGE_SETUP,
            'totalAssets' => 0,
            'totalChunks' => 0,
        ]);

        $this->writeJsonFile($this->getProgressPath($scanId), [
            'status' => self::STATUS_PENDING,
            'stage' => self::STAGE_SETUP,
            'progress' => 0,
            'totalAssets' => 0,
            'processedAssets' => 0,
            'usedCount' => 0,
            'unusedCount' => 0,
            'error' => null,
            'updatedAt' => $now,
        ]);

        $this->writeJsonFile($this->getStatePath($scanId), [
            'assetsSnapshotted' => false,
            'relationsProcessed' => false,
            'contentProcessed' => false,
            'finalized' => false,
            'usedRelationCount' => 0,
            'usedContentCount' => 0,
        ]);
    }

    /**
     * Read progress data for a scan.
     *
     * @param string $scanId
     * @return array|null
     */
    public function getProgress(string $scanId): ?array
    {
        $progress = $this->readJsonFile($this->getProgressPath($scanId));
        if (!is_array($progress)) {
            return null;
        }

        return $progress;
    }

    /**
     * Read scan metadata.
     *
     * @param string $scanId
     * @return array|null
     */
    public function getMeta(string $scanId): ?array
    {
        $meta = $this->readJsonFile($this->getMetaPath($scanId));
        return is_array($meta) ? $meta : null;
    }

    /**
     * Read final unused asset results for a scan.
     *
     * @param string $scanId
     * @return array|null
     */
    public function getResults(string $scanId): ?array
    {
        $path = $this->getUnusedResultsPath($scanId);
        if (!is_file($path)) {
            return null;
        }

        $results = [];
        foreach ($this->iterateNdjsonFile($path) as $row) {
            if (is_array($row)) {
                $results[] = $row;
            }
        }

        return $results;
    }

    /**
     * Whether the scan has a results file.
     *
     * @param string $scanId
     * @return bool
     */
    public function hasResults(string $scanId): bool
    {
        return is_file($this->getUnusedResultsPath($scanId));
    }

    /**
     * Read last completed scan metadata.
     *
     * @return array|null
     */
    public function getLastScan(): ?array
    {
        $payload = $this->readJsonFile($this->getLastScanFilePath());
        if (!is_array($payload)) {
            return null;
        }

        if (empty($payload['scanId']) || empty($payload['completedAt'])) {
            return null;
        }

        if (!$this->hasResults((string)$payload['scanId'])) {
            return null;
        }

        return $payload;
    }

    /**
     * Snapshot all assets in scope for the scan into chunk files.
     *
     * @param string $scanId
     * @return array{totalAssets:int,totalChunks:int}
     * @throws \Throwable
     */
    public function snapshotAssets(string $scanId): array
    {
        $meta = $this->requireMeta($scanId);
        $volumeIds = array_map('intval', $meta['volumeIds'] ?? []);
        $chunkSize = max(1, (int)($meta['assetChunkSize'] ?? self::DEFAULT_ASSET_CHUNK_SIZE));

        $this->clearDirectory($this->getAssetsDirectory($scanId));

        $query = Asset::find()
            ->status(null)
            ->orderBy(['elements.id' => SORT_ASC]);

        if (!empty($volumeIds)) {
            $query->volumeId($volumeIds);
        }

        $chunkIndex = 0;
        $chunkCount = 0;
        $currentChunk = [];
        $totalAssets = 0;

        $this->updateProgress($scanId, [
            'status' => self::STATUS_RUNNING,
            'stage' => self::STAGE_SETUP,
            'progress' => 1,
            'processedAssets' => 0,
            'totalAssets' => 0,
            'error' => null,
        ]);

        foreach ($query->each($chunkSize) as $asset) {
            /** @var Asset $asset */
            $currentChunk[] = $this->buildAssetSnapshotRecord($asset);
            $totalAssets++;

            if (count($currentChunk) >= $chunkSize) {
                $this->writeAssetChunk($scanId, $chunkIndex, $currentChunk);
                $chunkIndex++;
                $chunkCount++;
                $currentChunk = [];
            }
        }

        if (!empty($currentChunk)) {
            $this->writeAssetChunk($scanId, $chunkIndex, $currentChunk);
            $chunkCount++;
        }

        $this->touchMeta($scanId, [
            'status' => self::STATUS_RUNNING,
            'stage' => self::STAGE_SETUP,
            'totalAssets' => $totalAssets,
            'totalChunks' => $chunkCount,
        ]);

        $this->touchState($scanId, [
            'assetsSnapshotted' => true,
        ]);

        $progress = $totalAssets > 0 ? 10 : 100;
        $status = $totalAssets > 0 ? self::STATUS_RUNNING : self::STATUS_COMPLETE;

        $this->updateProgress($scanId, [
            'status' => $status,
            'stage' => $totalAssets > 0 ? self::STAGE_SETUP : self::STAGE_FINALIZE,
            'progress' => $progress,
            'totalAssets' => $totalAssets,
            'processedAssets' => 0,
            'usedCount' => 0,
            'unusedCount' => 0,
        ]);

        if ($totalAssets === 0) {
            $this->clearDirectory($this->getUsedDirectory($scanId));
            $this->clearDirectory($this->getResultsDirectory($scanId));
            $this->touchState($scanId, [
                'relationsProcessed' => true,
                'contentProcessed' => true,
                'finalized' => true,
                'usedRelationCount' => 0,
                'usedContentCount' => 0,
            ]);
            $this->touchMeta($scanId, [
                'status' => self::STATUS_COMPLETE,
                'stage' => self::STAGE_FINALIZE,
                'completedAt' => time(),
            ]);
            $this->writeEmptyResults($scanId);
            $this->setLastScan($scanId);
        }

        return [
            'totalAssets' => $totalAssets,
            'totalChunks' => $chunkCount,
        ];
    }

    /**
     * Collect used asset IDs from the relations table in one pass over the asset chunks.
     *
     * @param string $scanId
     * @return int Number of unique used asset IDs found via relations.
     * @throws \Throwable
     */
    public function collectRelationsUsage(string $scanId): int
    {
        $meta = $this->requireMeta($scanId);
        $totalChunks = max(1, (int)($meta['totalChunks'] ?? 1));
        $chunkFiles = $this->getAssetChunkFiles($scanId);

        $usedIds = [];

        $this->writeLines($this->getUsedRelationsPath($scanId), []);

        foreach ($chunkFiles as $chunkIndex => $chunkFile) {
            $assetIds = [];
            foreach ($this->iterateNdjsonFile($chunkFile) as $row) {
                if (is_array($row) && isset($row['id'])) {
                    $assetIds[] = (int)$row['id'];
                }
            }

            if (!empty($assetIds)) {
                $relationIds = (new Query())
                    ->select(['targetId'])
                    ->distinct()
                    ->from(Table::RELATIONS)
                    ->where(['targetId' => $assetIds])
                    ->column();

                foreach ($relationIds as $relationId) {
                    $usedIds[(int)$relationId] = true;
                }
            }

            $ratio = ($chunkIndex + 1) / $totalChunks;
            $this->updateProgress($scanId, [
                'status' => self::STATUS_RUNNING,
                'stage' => self::STAGE_RELATIONS,
                'progress' => $this->scaleProgress($ratio, 10, 25),
                'usedCount' => count($usedIds),
            ]);
        }

        $uniqueIds = array_map('intval', array_keys($usedIds));
        sort($uniqueIds, SORT_NUMERIC);
        $this->writeLines($this->getUsedRelationsPath($scanId), $uniqueIds);

        $this->touchState($scanId, [
            'relationsProcessed' => true,
            'usedRelationCount' => count($uniqueIds),
        ]);

        $this->touchMeta($scanId, [
            'status' => self::STATUS_RUNNING,
            'stage' => self::STAGE_RELATIONS,
        ]);

        $this->updateProgress($scanId, [
            'status' => self::STATUS_RUNNING,
            'stage' => self::STAGE_RELATIONS,
            'progress' => 25,
            'usedCount' => count($uniqueIds),
        ]);

        return count($uniqueIds);
    }

    /**
     * Scan relevant entry/global content and resolve asset references against
     * the asset snapshot lookup maps.
     *
     * @param string $scanId
     * @return int Number of unique used asset IDs found via content.
     * @throws \Throwable
     */
    public function collectContentUsage(string $scanId): int
    {
        $lookups = $this->buildAssetLookups($scanId);
        $scannedIds = $lookups['scannedIds'];
        $pathLookup = $lookups['pathLookup'];
        $filenameLookup = $lookups['filenameLookup'];

        $usedIds = [];

        $this->writeLines($this->getUsedContentPath($scanId), []);

        $htmlFields = $this->getHtmlFields();
        if (empty($htmlFields)) {
            $this->touchState($scanId, [
                'contentProcessed' => true,
                'usedContentCount' => 0,
            ]);
            $this->updateProgress($scanId, [
                'status' => self::STATUS_RUNNING,
                'stage' => self::STAGE_CONTENT,
                'progress' => 75,
            ]);
            return 0;
        }

        $fieldIds = array_values(array_filter(array_map(
            static fn($field) => (int)($field->id ?? 0),
            $htmlFields
        )));
        $relevantTypeIds = $this->getEntryTypeIdsWithFields($fieldIds);

        $entryBatchSize = self::DEFAULT_ENTRY_BATCH_SIZE;
        $entryQuery = Entry::find()
            ->status(null)
            ->orderBy(['elements.id' => SORT_ASC]);

        if (!empty($relevantTypeIds)) {
            $entryQuery->typeId($relevantTypeIds);
        } else {
            $entryQuery->id([-1]);
        }

        $totalEntries = (int)$entryQuery->count();
        $processedEntries = 0;

        $this->updateProgress($scanId, [
            'status' => self::STATUS_RUNNING,
            'stage' => self::STAGE_CONTENT,
            'progress' => 25,
        ]);

        if ($totalEntries > 0) {
            foreach ($entryQuery->each($entryBatchSize) as $entry) {
                /** @var Entry $entry */
                foreach ($htmlFields as $field) {
                    try {
                        $fieldValue = $entry->getFieldValue($field->handle);
                    } catch (\Throwable) {
                        continue;
                    }

                    $content = $this->normalizeFieldValueToString($fieldValue);
                    if ($content === '') {
                        continue;
                    }

                    foreach ($this->extractReferencedAssetIds($content, $scannedIds, $pathLookup, $filenameLookup) as $assetId) {
                        $usedIds[$assetId] = true;
                    }
                }

                $processedEntries++;
                if (($processedEntries % 25) === 0 || $processedEntries === $totalEntries) {
                    $ratio = $totalEntries > 0 ? ($processedEntries / $totalEntries) : 1.0;
                    $this->updateProgress($scanId, [
                        'status' => self::STATUS_RUNNING,
                        'stage' => self::STAGE_CONTENT,
                        'progress' => $this->scaleProgress($ratio, 25, 70),
                        'usedCount' => count($usedIds),
                    ]);
                }
            }
        }

        foreach (GlobalSet::find()->all() as $globalSet) {
            /** @var GlobalSet $globalSet */
            $fieldLayout = $globalSet->getFieldLayout();
            if (!$fieldLayout) {
                continue;
            }

            foreach ($fieldLayout->getCustomFields() as $field) {
                if (!$this->isHtmlField($field)) {
                    continue;
                }

                $content = $this->normalizeFieldValueToString($globalSet->getFieldValue($field->handle));
                if ($content === '') {
                    continue;
                }

                foreach ($this->extractReferencedAssetIds($content, $scannedIds, $pathLookup, $filenameLookup) as $assetId) {
                    $usedIds[$assetId] = true;
                }
            }
        }

        $uniqueIds = array_map('intval', array_keys($usedIds));
        sort($uniqueIds, SORT_NUMERIC);
        $this->writeLines($this->getUsedContentPath($scanId), $uniqueIds);

        $this->touchState($scanId, [
            'contentProcessed' => true,
            'usedContentCount' => count($uniqueIds),
        ]);

        $this->touchMeta($scanId, [
            'status' => self::STATUS_RUNNING,
            'stage' => self::STAGE_CONTENT,
        ]);

        $this->updateProgress($scanId, [
            'status' => self::STATUS_RUNNING,
            'stage' => self::STAGE_CONTENT,
            'progress' => 75,
            'usedCount' => count($uniqueIds),
        ]);

        return count($uniqueIds);
    }

    /**
     * Merge used IDs, classify the asset snapshot, and build final unused results.
     *
     * @param string $scanId
     * @return array{usedCount:int,unusedCount:int}
     * @throws \Throwable
     */
    public function finalizeScan(string $scanId): array
    {
        $meta = $this->requireMeta($scanId);
        $totalAssets = (int)($meta['totalAssets'] ?? 0);
        $usedIds = $this->readMergedUsedIds($scanId);
        $usedLookup = array_fill_keys($usedIds, true);

        $this->writeLines($this->getUsedFinalPath($scanId), $usedIds);
        $this->writeLines($this->getUnusedIdsPath($scanId), []);
        $this->clearResults($scanId);

        $unusedIds = [];
        $processedAssets = 0;

        foreach ($this->getAssetChunkFiles($scanId) as $chunkFile) {
            foreach ($this->iterateNdjsonFile($chunkFile) as $row) {
                if (!is_array($row) || !isset($row['id'])) {
                    continue;
                }

                $assetId = (int)$row['id'];
                if (!isset($usedLookup[$assetId])) {
                    $unusedIds[] = $assetId;
                }

                $processedAssets++;
                if (($processedAssets % 100) === 0 || $processedAssets === $totalAssets) {
                    $ratio = $totalAssets > 0 ? ($processedAssets / $totalAssets) : 1.0;
                    $this->updateProgress($scanId, [
                        'status' => self::STATUS_RUNNING,
                        'stage' => self::STAGE_FINALIZE,
                        'progress' => $this->scaleProgress($ratio, 75, 95),
                        'processedAssets' => $processedAssets,
                        'usedCount' => count($usedIds),
                        'unusedCount' => count($unusedIds),
                    ]);
                }
            }
        }

        sort($unusedIds, SORT_NUMERIC);
        $this->writeLines($this->getUnusedIdsPath($scanId), $unusedIds);
        $unusedCount = $this->writeUnusedResults($scanId, $unusedIds);

        $completedAt = time();

        $this->touchState($scanId, [
            'finalized' => true,
        ]);

        $this->touchMeta($scanId, [
            'status' => self::STATUS_COMPLETE,
            'stage' => self::STAGE_FINALIZE,
            'completedAt' => $completedAt,
        ]);

        $this->updateProgress($scanId, [
            'status' => self::STATUS_COMPLETE,
            'stage' => self::STAGE_FINALIZE,
            'progress' => 100,
            'processedAssets' => $totalAssets,
            'usedCount' => count($usedIds),
            'unusedCount' => $unusedCount,
        ]);

        $this->setLastScan($scanId, $completedAt);

        return [
            'usedCount' => count($usedIds),
            'unusedCount' => $unusedCount,
        ];
    }

    /**
     * Mark a scan as failed.
     *
     * @param string $scanId
     * @param \Throwable|string $error
     * @return void
     */
    public function failScan(string $scanId, \Throwable|string $error): void
    {
        $message = $error instanceof \Throwable ? $error->getMessage() : (string)$error;

        $this->touchMeta($scanId, [
            'status' => self::STATUS_FAILED,
        ]);

        $this->updateProgress($scanId, [
            'status' => self::STATUS_FAILED,
            'error' => $message,
        ]);
    }

    /**
     * Human-readable label for the current stage.
     *
     * @param string $stage
     * @return string
     */
    public function getStageLabel(string $stage): string
    {
        return match ($stage) {
            self::STAGE_SETUP => Craft::t('asset-cleaner', 'Preparing asset snapshot...'),
            self::STAGE_RELATIONS => Craft::t('asset-cleaner', 'Scanning relations...'),
            self::STAGE_CONTENT => Craft::t('asset-cleaner', 'Scanning content...'),
            self::STAGE_FINALIZE => Craft::t('asset-cleaner', 'Finalizing results...'),
            default => Craft::t('asset-cleaner', 'Scanning...'),
        };
    }

    /**
     * Delete scan workspaces older than the provided age.
     *
     * @param int $maxAgeSeconds
     * @return int
     */
    public function pruneOldScans(int $maxAgeSeconds = 604800): int
    {
        $deleted = 0;
        $root = $this->getScansRootPath();

        if (!is_dir($root)) {
            return 0;
        }

        $now = time();
        $entries = scandir($root);
        if ($entries === false) {
            return 0;
        }

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $path = $root . DIRECTORY_SEPARATOR . $entry;
            if (!is_dir($path)) {
                continue;
            }

            $meta = $this->readJsonFile($path . DIRECTORY_SEPARATOR . 'meta.json');
            $updatedAt = is_array($meta) ? (int)($meta['updatedAt'] ?? $meta['createdAt'] ?? 0) : 0;
            if ($updatedAt > 0 && ($now - $updatedAt) > $maxAgeSeconds) {
                FileHelper::removeDirectory($path);
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * @return string
     */
    public function getBaseStoragePath(): string
    {
        return Craft::getAlias('@storage') . DIRECTORY_SEPARATOR . 'asset-cleaner';
    }

    /**
     * @return string
     */
    public function getScansRootPath(): string
    {
        return $this->getBaseStoragePath() . DIRECTORY_SEPARATOR . 'scans';
    }

    /**
     * @param string $scanId
     * @return string
     */
    public function getScanPath(string $scanId): string
    {
        return $this->getScansRootPath() . DIRECTORY_SEPARATOR . $scanId;
    }

    /**
     * @param string $scanId
     * @return string
     */
    public function getMetaPath(string $scanId): string
    {
        return $this->getScanPath($scanId) . DIRECTORY_SEPARATOR . 'meta.json';
    }

    /**
     * @param string $scanId
     * @return string
     */
    public function getProgressPath(string $scanId): string
    {
        return $this->getScanPath($scanId) . DIRECTORY_SEPARATOR . 'progress.json';
    }

    /**
     * @param string $scanId
     * @return string
     */
    public function getStatePath(string $scanId): string
    {
        return $this->getScanPath($scanId) . DIRECTORY_SEPARATOR . 'state.json';
    }

    /**
     * @param string $scanId
     * @return string
     */
    public function getAssetsDirectory(string $scanId): string
    {
        return $this->getScanPath($scanId) . DIRECTORY_SEPARATOR . 'assets';
    }

    /**
     * @param string $scanId
     * @return string
     */
    public function getUsedDirectory(string $scanId): string
    {
        return $this->getScanPath($scanId) . DIRECTORY_SEPARATOR . 'used';
    }

    /**
     * @param string $scanId
     * @return string
     */
    public function getResultsDirectory(string $scanId): string
    {
        return $this->getScanPath($scanId) . DIRECTORY_SEPARATOR . 'results';
    }

    /**
     * @param string $scanId
     * @return string
     */
    public function getUsedRelationsPath(string $scanId): string
    {
        return $this->getUsedDirectory($scanId) . DIRECTORY_SEPARATOR . 'relations.txt';
    }

    /**
     * @param string $scanId
     * @return string
     */
    public function getUsedContentPath(string $scanId): string
    {
        return $this->getUsedDirectory($scanId) . DIRECTORY_SEPARATOR . 'content.txt';
    }

    /**
     * @param string $scanId
     * @return string
     */
    public function getUsedFinalPath(string $scanId): string
    {
        return $this->getUsedDirectory($scanId) . DIRECTORY_SEPARATOR . 'final.txt';
    }

    /**
     * @param string $scanId
     * @return string
     */
    public function getUnusedIdsPath(string $scanId): string
    {
        return $this->getResultsDirectory($scanId) . DIRECTORY_SEPARATOR . 'unused-ids.txt';
    }

    /**
     * @param string $scanId
     * @return string
     */
    public function getUnusedResultsPath(string $scanId): string
    {
        return $this->getResultsDirectory($scanId) . DIRECTORY_SEPARATOR . 'unused.ndjson';
    }

    /**
     * @return string
     */
    private function getLastScanFilePath(): string
    {
        return $this->getBaseStoragePath() . DIRECTORY_SEPARATOR . self::LAST_SCAN_FILE;
    }

    /**
     * @return void
     * @throws Exception
     */
    private function ensureBaseDirectories(): void
    {
        FileHelper::createDirectory($this->getBaseStoragePath());
        FileHelper::createDirectory($this->getScansRootPath());
    }

    /**
     * @param string $scanId
     * @return array
     * @throws Exception
     */
    private function requireMeta(string $scanId): array
    {
        $meta = $this->getMeta($scanId);
        if ($meta === null) {
            throw new Exception("Scan metadata not found for '{$scanId}'.");
        }

        return $meta;
    }

    /**
     * @param string $scanId
     * @param array $chunk
     * @return void
     * @throws Exception
     */
    private function writeAssetChunk(string $scanId, int $chunkIndex, array $chunk): void
    {
        $filename = sprintf('chunk-%06d.ndjson', $chunkIndex + 1);
        $path = $this->getAssetsDirectory($scanId) . DIRECTORY_SEPARATOR . $filename;

        $this->writeNdjsonFile($path, $chunk);
    }

    /**
     * @param string $scanId
     * @return array<int,string>
     */
    private function getAssetChunkFiles(string $scanId): array
    {
        $directory = $this->getAssetsDirectory($scanId);
        if (!is_dir($directory)) {
            return [];
        }

        $files = glob($directory . DIRECTORY_SEPARATOR . 'chunk-*.ndjson') ?: [];
        sort($files, SORT_NATURAL);

        return array_values($files);
    }

    /**
     * @param Asset $asset
     * @return array
     */
    private function buildAssetSnapshotRecord(Asset $asset): array
    {
        $volume = null;
        $volumeHandle = null;
        $volumePathPrefixes = [];

        try {
            $volume = $asset->getVolume();
            if ($volume) {
                $volumeHandle = $volume->handle;
                $volumePathPrefixes = $this->buildVolumePathPrefixes($volume, $volumeHandle);
            }
        } catch (\Throwable) {
            $volume = null;
            $volumeHandle = null;
            $volumePathPrefixes = [];
        }

        return [
            'id' => (int)$asset->id,
            'filename' => (string)$asset->filename,
            'volumeId' => (int)$asset->volumeId,
            'volumeHandle' => $volumeHandle,
            'folderPath' => (string)($asset->folderPath ?? ''),
            'pathCandidates' => array_values($this->buildAssetPathCandidates($asset, $volumeHandle, $volumePathPrefixes)),
        ];
    }

    /**
     * @param Asset $asset
     * @param string|null $volumeHandle
     * @return array<string,string>
     */
    private function buildAssetPathCandidates(Asset $asset, ?string $volumeHandle, array $volumePathPrefixes = []): array
    {
        $candidates = [];

        $filename = trim((string)$asset->filename);
        if ($filename === '') {
            return [];
        }

        $folderPath = trim((string)($asset->folderPath ?? ''), '/');
        $relativePath = $folderPath !== ''
            ? $folderPath . '/' . $filename
            : $filename;

        foreach ($this->normalizePathCandidates($relativePath) as $candidate) {
            $candidates[$candidate] = $candidate;
        }

        if ($volumeHandle) {
            $volumeRelative = trim($volumeHandle . '/' . $relativePath, '/');
            foreach ($this->normalizePathCandidates($volumeRelative) as $candidate) {
                $candidates[$candidate] = $candidate;
            }
        }

        foreach ($volumePathPrefixes as $prefix) {
            $prefixedPath = trim($prefix . '/' . $relativePath, '/');
            foreach ($this->normalizePathCandidates($prefixedPath) as $candidate) {
                $candidates[$candidate] = $candidate;
            }
        }

        return $candidates;
    }

    /**
     * @param mixed $volume
     * @param string|null $volumeHandle
     * @return array<int,string>
     */
    private function buildVolumePathPrefixes(mixed $volume, ?string $volumeHandle): array
    {
        $prefixes = [];

        if ($volumeHandle) {
            $prefixes[] = $volumeHandle;
        }

        try {
            $fs = $volume?->getFs();
            if ($fs) {
                if (method_exists($fs, 'getRootUrl')) {
                    $rootUrl = $fs->getRootUrl();
                    if (is_string($rootUrl) && $rootUrl !== '') {
                        $rootPath = parse_url($rootUrl, PHP_URL_PATH);
                        if (is_string($rootPath) && $rootPath !== '') {
                            $prefixes[] = trim($rootPath, '/');
                        }
                    }
                }

                if (method_exists($fs, 'getRootPath')) {
                    $rootPath = $fs->getRootPath();
                    if (is_string($rootPath) && $rootPath !== '') {
                        $prefixes[] = trim(basename($rootPath), '/');
                    }
                }
            }
        } catch (\Throwable) {
            // Ignore inaccessible FS metadata
        }

        return array_values(array_unique(array_filter($prefixes, static fn($prefix) => $prefix !== '')));
    }

    /**
     * @param string $scanId
     * @return array{
     *     scannedIds: array<int,bool>,
     *     pathLookup: array<string,array<int>>,
     *     filenameLookup: array<string,array<int>>
     * }
     */
    private function buildAssetLookups(string $scanId): array
    {
        $scannedIds = [];
        $pathLookup = [];
        $filenameLookup = [];

        foreach ($this->getAssetChunkFiles($scanId) as $chunkFile) {
            foreach ($this->iterateNdjsonFile($chunkFile) as $row) {
                if (!is_array($row) || !isset($row['id'])) {
                    continue;
                }

                $assetId = (int)$row['id'];
                $scannedIds[$assetId] = true;

                $filename = trim((string)($row['filename'] ?? ''));
                if ($filename !== '') {
                    $key = mb_strtolower($filename);
                    $filenameLookup[$key] ??= [];
                    $filenameLookup[$key][] = $assetId;
                }

                foreach ((array)($row['pathCandidates'] ?? []) as $candidate) {
                    $normalizedVariants = $this->normalizePathCandidates((string)$candidate);
                    foreach ($normalizedVariants as $variant) {
                        $pathLookup[$variant] ??= [];
                        $pathLookup[$variant][] = $assetId;
                    }
                }
            }
        }

        foreach ($pathLookup as $key => $ids) {
            $pathLookup[$key] = array_values(array_unique(array_map('intval', $ids)));
        }

        foreach ($filenameLookup as $key => $ids) {
            $filenameLookup[$key] = array_values(array_unique(array_map('intval', $ids)));
        }

        return [
            'scannedIds' => $scannedIds,
            'pathLookup' => $pathLookup,
            'filenameLookup' => $filenameLookup,
        ];
    }

    /**
     * @return array
     */
    private function getHtmlFields(): array
    {
        $fields = Craft::$app->getFields()->getAllFields();

        return array_values(array_filter($fields, fn($field) => $this->isHtmlField($field)));
    }

    /**
     * @param mixed $field
     * @return bool
     */
    private function isHtmlField(mixed $field): bool
    {
        if (!is_object($field)) {
            return false;
        }

        return in_array(get_class($field), [
            'craft\\redactor\\Field',
            'craft\\ckeditor\\Field',
        ], true);
    }

    /**
     * @param array $fieldIds
     * @return array
     */
    private function getEntryTypeIdsWithFields(array $fieldIds): array
    {
        if (empty($fieldIds)) {
            return [];
        }

        $relevantLayoutIds = [];
        foreach (Craft::$app->getFields()->getAllLayouts() as $layout) {
            foreach ($layout->getCustomFields() as $field) {
                if (in_array((int)$field->id, $fieldIds, true)) {
                    $relevantLayoutIds[] = (int)$layout->id;
                    break;
                }
            }
        }

        if (empty($relevantLayoutIds)) {
            return [];
        }

        return array_map(
            'intval',
            (new Query())
                ->select(['id'])
                ->from('{{%entrytypes}}')
                ->where(['fieldLayoutId' => array_values(array_unique($relevantLayoutIds))])
                ->column()
        );
    }

    /**
     * @param mixed $fieldValue
     * @return string
     */
    private function normalizeFieldValueToString(mixed $fieldValue): string
    {
        if ($fieldValue instanceof \craft\redactor\FieldData) {
            $fieldValue = $fieldValue->getRawContent();
        } elseif (is_object($fieldValue) && method_exists($fieldValue, '__toString')) {
            $fieldValue = (string)$fieldValue;
        }

        return is_string($fieldValue) ? $fieldValue : '';
    }

    /**
     * Extract referenced asset IDs from one content string.
     *
     * Resolution order:
     * 1. direct IDs (data-asset-id / #asset:)
     * 2. normalized path / URL candidates
     * 3. unique filename fallback
     *
     * @param string $content
     * @param array<int,bool> $scannedIds
     * @param array<string,array<int>> $pathLookup
     * @param array<string,array<int>> $filenameLookup
     * @return array<int>
     */
    private function extractReferencedAssetIds(
        string $content,
        array $scannedIds,
        array $pathLookup,
        array $filenameLookup
    ): array {
        $found = [];

        if (preg_match_all('/data-asset-id\s*=\s*["\']?(\d+)["\']?/i', $content, $matches)) {
            foreach ($matches[1] as $id) {
                $assetId = (int)$id;
                if (isset($scannedIds[$assetId])) {
                    $found[$assetId] = true;
                }
            }
        }

        if (preg_match_all('/#asset:(\d+)/i', $content, $matches)) {
            foreach ($matches[1] as $id) {
                $assetId = (int)$id;
                if (isset($scannedIds[$assetId])) {
                    $found[$assetId] = true;
                }
            }
        }

        $rawReferences = [];

        if (preg_match_all('/\b(?:src|href|poster)\s*=\s*["\']([^"\']+)["\']/i', $content, $matches)) {
            foreach ($matches[1] as $value) {
                $rawReferences[] = $value;
            }
        }

        if (preg_match_all('/\bsrcset\s*=\s*["\']([^"\']+)["\']/i', $content, $matches)) {
            foreach ($matches[1] as $srcset) {
                foreach (preg_split('/\s*,\s*/', $srcset) ?: [] as $candidate) {
                    $parts = preg_split('/\s+/', trim($candidate)) ?: [];
                    if (!empty($parts[0])) {
                        $rawReferences[] = $parts[0];
                    }
                }
            }
        }

        if (preg_match_all('/url\((["\']?)([^)"\']+)\1\)/i', $content, $matches)) {
            foreach ($matches[2] as $value) {
                $rawReferences[] = $value;
            }
        }

        foreach ($rawReferences as $reference) {
            $matched = false;

            foreach ($this->normalizePathCandidates((string)$reference) as $candidate) {
                $assetIds = $pathLookup[$candidate] ?? null;
                if (is_array($assetIds) && count($assetIds) === 1) {
                    $found[$assetIds[0]] = true;
                    $matched = true;
                }
            }

            if ($matched) {
                continue;
            }

            $basename = basename((string)parse_url((string)$reference, PHP_URL_PATH));
            $filenameKey = mb_strtolower(trim($basename));
            if ($filenameKey !== '' && isset($filenameLookup[$filenameKey]) && count($filenameLookup[$filenameKey]) === 1) {
                $found[$filenameLookup[$filenameKey][0]] = true;
            }
        }

        if (preg_match_all('/\b([A-Za-z0-9][A-Za-z0-9._-]*\.(?:jpe?g|png|gif|svg|pdf|webp|mp4|mp3|mov|docx?|xlsx?|pptx?|txt|zip))\b/i', $content, $matches)) {
            foreach ($matches[1] as $filename) {
                $key = mb_strtolower($filename);
                if (isset($filenameLookup[$key]) && count($filenameLookup[$key]) === 1) {
                    $found[$filenameLookup[$key][0]] = true;
                }
            }
        }

        return array_map('intval', array_keys($found));
    }

    /**
     * @param string $path
     * @return array<int,string>
     */
    private function normalizePathCandidates(string $path): array
    {
        $path = html_entity_decode(trim($path), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($path === '') {
            return [];
        }

        $parsedPath = parse_url($path, PHP_URL_PATH);
        $path = is_string($parsedPath) && $parsedPath !== '' ? $parsedPath : $path;
        $path = rawurldecode($path);
        $path = preg_replace('~/{2,}~', '/', $path) ?? $path;
        $path = trim($path);

        if ($path === '' || $path === '.' || $path === '..') {
            return [];
        }

        $variants = [];
        $trimmed = trim($path, '/');
        if ($trimmed !== '') {
            $variants[] = $trimmed;
            $variants[] = '/' . $trimmed;
        }

        if (str_starts_with($path, '/')) {
            $variants[] = $path;
            $variants[] = ltrim($path, '/');
        } else {
            $variants[] = $path;
            $variants[] = '/' . $path;
        }

        $variants = array_values(array_unique(array_filter($variants, static fn($value) => $value !== '')));

        return $variants;
    }

    /**
     * @param string $scanId
     * @return array<int>
     */
    private function readMergedUsedIds(string $scanId): array
    {
        $used = [];

        foreach ([$this->getUsedRelationsPath($scanId), $this->getUsedContentPath($scanId)] as $path) {
            if (!is_file($path)) {
                continue;
            }

            foreach ($this->iterateTextLines($path) as $line) {
                $id = (int)trim($line);
                if ($id > 0) {
                    $used[$id] = true;
                }
            }
        }

        $ids = array_map('intval', array_keys($used));
        sort($ids, SORT_NUMERIC);

        return $ids;
    }

    /**
     * @param string $scanId
     * @param array<int> $unusedIds
     * @return int
     * @throws Exception
     */
    private function writeUnusedResults(string $scanId, array $unusedIds): int
    {
        $path = $this->getUnusedResultsPath($scanId);
        $count = 0;

        if (empty($unusedIds)) {
            $this->writeNdjsonFile($path, []);
            return 0;
        }

        $fh = fopen($path, 'wb');
        if ($fh === false) {
            throw new Exception("Unable to open results file for scan '{$scanId}'.");
        }

        try {
            foreach (array_chunk($unusedIds, self::DEFAULT_RESULT_QUERY_CHUNK_SIZE) as $idChunk) {
                $assets = Asset::find()
                    ->id($idChunk)
                    ->status(null)
                    ->all();

                foreach ($assets as $asset) {
                    /** @var Asset $asset */
                    $row = $this->buildUnusedAssetRow($asset);
                    fwrite($fh, Json::encode($row) . PHP_EOL);
                    $count++;
                }
            }
        } finally {
            fclose($fh);
        }

        return $count;
    }

    /**
     * @param Asset $asset
     * @return array<string,mixed>
     */
    private function buildUnusedAssetRow(Asset $asset): array
    {
        $path = '';
        $folderPath = '';

        try {
            $folder = $asset->getFolder();
            if ($folder && $folder->path) {
                $folderPath = (string)$folder->path;
            }
        } catch (\Throwable) {
            $folderPath = '';
        }

        try {
            $volume = $asset->getVolume();
            if ($volume) {
                if (method_exists($volume, 'getRootPath')) {
                    $volumePath = $volume->getRootPath();
                    if ($volumePath) {
                        $path = (string)$volumePath;
                        if ($folderPath) {
                            $path = rtrim($path, '/\\') . '/' . ltrim($folderPath, '/\\');
                        }
                    }
                }

                if (empty($path) && !empty($volume->handle)) {
                    $path = '@volumes/' . $volume->handle;
                    if ($folderPath) {
                        $path .= '/' . ltrim($folderPath, '/\\');
                    }
                }
            }
        } catch (\Throwable) {
            $path = '';
        }

        return [
            'id' => (int)$asset->id,
            'title' => (string)$asset->title,
            'filename' => (string)$asset->filename,
            'url' => $asset->getUrl(),
            'cpUrl' => $asset->getCpEditUrl(),
            'volume' => $asset->volume->name ?? '',
            'volumeId' => (int)$asset->volumeId,
            'size' => (int)$asset->size,
            'path' => (string)$path,
            'kind' => (string)$asset->kind,
        ];
    }

    /**
     * @param string $scanId
     * @param int|null $completedAt
     * @return void
     */
    private function setLastScan(string $scanId, ?int $completedAt = null): void
    {
        $this->writeJsonFile($this->getLastScanFilePath(), [
            'scanId' => $scanId,
            'completedAt' => $completedAt ?? time(),
        ]);
    }

    /**
     * @param string $scanId
     * @param array $updates
     * @return void
     */
    private function updateProgress(string $scanId, array $updates): void
    {
        $progress = $this->getProgress($scanId) ?? [];
        $progress = array_merge($progress, $updates, [
            'updatedAt' => time(),
        ]);

        if (!isset($progress['stage'])) {
            $progress['stage'] = self::STAGE_SETUP;
        }

        $this->writeJsonFile($this->getProgressPath($scanId), $progress);
        $this->touchMeta($scanId, [
            'updatedAt' => $progress['updatedAt'],
            'status' => $progress['status'] ?? self::STATUS_RUNNING,
            'stage' => $progress['stage'],
        ]);
    }

    /**
     * @param string $scanId
     * @param array $updates
     * @return void
     */
    private function touchMeta(string $scanId, array $updates): void
    {
        $meta = $this->getMeta($scanId) ?? [];
        $meta = array_merge($meta, $updates, [
            'updatedAt' => time(),
        ]);

        $this->writeJsonFile($this->getMetaPath($scanId), $meta);
    }

    /**
     * @param string $scanId
     * @param array $updates
     * @return void
     */
    private function touchState(string $scanId, array $updates): void
    {
        $state = $this->readJsonFile($this->getStatePath($scanId));
        $state = is_array($state) ? $state : [];
        $state = array_merge($state, $updates);

        $this->writeJsonFile($this->getStatePath($scanId), $state);
    }

    /**
     * @param string $scanId
     * @return void
     */
    private function clearResults(string $scanId): void
    {
        FileHelper::createDirectory($this->getResultsDirectory($scanId));

        foreach ([$this->getUnusedIdsPath($scanId), $this->getUnusedResultsPath($scanId)] as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }

    /**
     * @param string $scanId
     * @return void
     */
    private function writeEmptyResults(string $scanId): void
    {
        $this->writeLines($this->getUsedRelationsPath($scanId), []);
        $this->writeLines($this->getUsedContentPath($scanId), []);
        $this->writeLines($this->getUsedFinalPath($scanId), []);
        $this->writeLines($this->getUnusedIdsPath($scanId), []);
        $this->writeNdjsonFile($this->getUnusedResultsPath($scanId), []);
    }

    /**
     * @param string $directory
     * @return void
     */
    private function clearDirectory(string $directory): void
    {
        if (is_dir($directory)) {
            FileHelper::removeDirectory($directory);
        }

        FileHelper::createDirectory($directory);
    }

    /**
     * @param string $path
     * @param array $payload
     * @return void
     */
    private function writeJsonFile(string $path, array $payload): void
    {
        FileHelper::createDirectory(dirname($path));
        $json = Json::encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $this->writeFileAtomically($path, $json . PHP_EOL);
    }

    /**
     * @param string $path
     * @return mixed
     */
    private function readJsonFile(string $path): mixed
    {
        if (!is_file($path)) {
            return null;
        }

        $contents = file_get_contents($path);
        if ($contents === false || trim($contents) === '') {
            return null;
        }

        try {
            return Json::decode($contents);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param string $path
     * @param array<int,mixed> $rows
     * @return void
     * @throws Exception
     */
    private function writeNdjsonFile(string $path, array $rows): void
    {
        FileHelper::createDirectory(dirname($path));

        $fh = fopen($path, 'wb');
        if ($fh === false) {
            throw new Exception("Unable to open '{$path}' for writing.");
        }

        try {
            foreach ($rows as $row) {
                fwrite($fh, Json::encode($row) . PHP_EOL);
            }
        } finally {
            fclose($fh);
        }
    }

    /**
     * @param string $path
     * @param array<int,string|int> $lines
     * @return void
     */
    private function writeLines(string $path, array $lines): void
    {
        FileHelper::createDirectory(dirname($path));
        $content = '';
        foreach ($lines as $line) {
            $content .= trim((string)$line) . PHP_EOL;
        }
        $this->writeFileAtomically($path, $content);
    }

    /**
     * @param string $path
     * @param string $contents
     * @return void
     */
    private function writeFileAtomically(string $path, string $contents): void
    {
        FileHelper::createDirectory(dirname($path));

        $tmpPath = $path . '.tmp-' . bin2hex(random_bytes(6));
        file_put_contents($tmpPath, $contents, LOCK_EX);
        rename($tmpPath, $path);
    }

    /**
     * @param string $path
     * @return \Generator<int,array>
     */
    private function iterateNdjsonFile(string $path): \Generator
    {
        if (!is_file($path)) {
            return;
        }

        $fh = fopen($path, 'rb');
        if ($fh === false) {
            return;
        }

        try {
            while (($line = fgets($fh)) !== false) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                try {
                    $row = Json::decode($line);
                } catch (\Throwable) {
                    continue;
                }

                if (is_array($row)) {
                    yield $row;
                }
            }
        } finally {
            fclose($fh);
        }
    }

    /**
     * @param string $path
     * @return \Generator<int,string>
     */
    private function iterateTextLines(string $path): \Generator
    {
        if (!is_file($path)) {
            return;
        }

        $fh = fopen($path, 'rb');
        if ($fh === false) {
            return;
        }

        try {
            while (($line = fgets($fh)) !== false) {
                $line = trim($line);
                if ($line !== '') {
                    yield $line;
                }
            }
        } finally {
            fclose($fh);
        }
    }

    /**
     * @param float $ratio
     * @param int $start
     * @param int $end
     * @return int
     */
    private function scaleProgress(float $ratio, int $start, int $end): int
    {
        $ratio = max(0.0, min(1.0, $ratio));
        return (int)round($start + (($end - $start) * $ratio));
    }
}
