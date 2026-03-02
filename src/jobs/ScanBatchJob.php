<?php

declare(strict_types=1);

namespace yann\assetcleaner\jobs;

use Craft;
use craft\elements\Asset;
use craft\queue\BaseJob;
use yann\assetcleaner\helpers\Logger;
use yann\assetcleaner\Plugin;

/**
 * Processes one batch of assets, then re-queues itself for the next batch.
 * Each job completes quickly to keep the queue responsive.
 */
class ScanBatchJob extends BaseJob
{
    /**
     * @var string The scan ID
     */
    public string $scanId = '';

    /**
     * @var array All asset IDs to scan (full list, batched via offset)
     */
    public array $allAssetIds = [];

    /**
     * @var int Current offset into the asset ID list
     */
    public int $offset = 0;

    /**
     * @var int Number of assets to process in this batch
     */
    public int $batchSize = 50;

    /**
     * @inheritdoc
     */
    public function execute($queue): void
    {
        $cache = Craft::$app->getCache();

        try {
            // Read the cached content index
            $contentIndex = $cache->get("asset-cleaner-index-{$this->scanId}");
            if ($contentIndex === false) {
                throw new \RuntimeException('Content index not found in cache. Scan may have expired.');
            }

            // Get this batch of asset IDs
            $batchIds = array_slice($this->allAssetIds, $this->offset, $this->batchSize);
            $totalAssets = count($this->allAssetIds);

            // Read current progress
            $progress = $cache->get("asset-cleaner-progress-{$this->scanId}") ?: [];

            $processedAssets = (int)($progress['processedAssets'] ?? 0);
            $usedCount = (int)($progress['usedCount'] ?? 0);
            $unusedCount = (int)($progress['unusedCount'] ?? 0);

            // Check if scan was cancelled (a new scan overwrites the progress)
            if (($progress['status'] ?? '') === 'failed') {
                return;
            }

            $assetUsageService = Plugin::getInstance()->assetUsage;

            // Read accumulated unused IDs from cache
            $unusedIds = $cache->get("asset-cleaner-unused-{$this->scanId}") ?: [];

            // Process this batch
            foreach ($batchIds as $assetId) {
                $assetId = (int)$assetId;
                if ($assetUsageService->isAssetUsedWithIndex($assetId, $contentIndex)) {
                    $usedCount++;
                } else {
                    $unusedCount++;
                    $unusedIds[] = $assetId;
                }
                $processedAssets++;
            }

            // Save accumulated unused IDs
            $cache->set("asset-cleaner-unused-{$this->scanId}", $unusedIds, 3600);

            // Calculate progress percentage
            $progressPercent = $totalAssets > 0
                ? (int)round(($processedAssets / $totalAssets) * 100)
                : 100;

            // Update cached progress
            $this->updateProgress($cache, [
                'status' => 'running',
                'progress' => $progressPercent,
                'processedAssets' => $processedAssets,
                'usedCount' => $usedCount,
                'unusedCount' => $unusedCount,
            ]);

            $this->setProgress($queue, $processedAssets / max($totalAssets, 1));

            // More assets to process?
            $nextOffset = $this->offset + $this->batchSize;
            if ($nextOffset < $totalAssets) {
                // Queue the next batch
                Craft::$app->getQueue()->push(new self([
                    'scanId' => $this->scanId,
                    'allAssetIds' => $this->allAssetIds,
                    'offset' => $nextOffset,
                    'batchSize' => $this->batchSize,
                ]));
            } else {
                // All done — build results and finalize
                $this->finalize($cache, $usedCount, $unusedCount);
            }
        } catch (\Throwable $e) {
            Logger::exception('Scan batch failed', $e);
            $this->updateProgress($cache, [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): ?string
    {
        $total = count($this->allAssetIds);
        $current = min($this->offset + $this->batchSize, $total);
        return Craft::t('asset-cleaner', 'Scanning assets ({current}/{total})', [
            'current' => $current,
            'total' => $total,
        ]);
    }

    /**
     * Build the final unused assets data and write to cache
     */
    private function finalize($cache, int $usedCount, int $unusedCount): void
    {
        // Read accumulated unused IDs
        $unusedIds = $cache->get("asset-cleaner-unused-{$this->scanId}") ?: [];

        // Build full result data
        $unusedAssets = $this->buildUnusedAssetData($unusedIds);

        // Store results
        $cache->set("asset-cleaner-results-{$this->scanId}", $unusedAssets, 3600);

        // Mark complete (completedAt is used by the CSV export to stamp the filename)
        $this->updateProgress($cache, [
            'status' => 'complete',
            'progress' => 100,
            'processedAssets' => count($this->allAssetIds),
            'usedCount' => $usedCount,
            'unusedCount' => count($unusedIds),
            'completedAt' => time(),
        ]);

        // Store last-scan metadata for auto-restore on page load
        $cache->set('asset-cleaner-last-scan', [
            'scanId' => $this->scanId,
            'completedAt' => time(),
        ], 3600);

        // Clean up temporary cache keys
        $cache->delete("asset-cleaner-index-{$this->scanId}");
        $cache->delete("asset-cleaner-unused-{$this->scanId}");
    }

    /**
     * Merge updates into the cached progress
     */
    private function updateProgress($cache, array $updates): void
    {
        $progress = $cache->get("asset-cleaner-progress-{$this->scanId}") ?: [];
        $progress = array_merge($progress, $updates);
        $cache->set("asset-cleaner-progress-{$this->scanId}", $progress, 3600);
    }

    /**
     * Build the full data array for unused assets
     */
    private function buildUnusedAssetData(array $unusedIds): array
    {
        if (empty($unusedIds)) {
            return [];
        }

        $assets = Asset::find()
            ->id($unusedIds)
            ->status(null)
            ->all();

        $result = [];
        foreach ($assets as $asset) {
            $path = '';
            $folderPath = '';

            try {
                $folder = $asset->getFolder();
                if ($folder && $folder->path) {
                    $folderPath = $folder->path;
                }
            } catch (\Throwable $e) {
                // ignore
            }

            try {
                $volume = $asset->getVolume();
                if ($volume) {
                    if (method_exists($volume, 'getRootPath')) {
                        $volumePath = $volume->getRootPath();
                        if ($volumePath) {
                            $path = $volumePath;
                            if ($folderPath) {
                                $path = rtrim($path, '/') . '/' . ltrim($folderPath, '/');
                            }
                        }
                    }

                    if (empty($path) && $volume->handle) {
                        $path = '@volumes/' . $volume->handle;
                        if ($folderPath) {
                            $path .= '/' . ltrim($folderPath, '/');
                        }
                    }
                }
            } catch (\Throwable $e) {
                // ignore
            }

            $result[] = [
                'id' => $asset->id,
                'title' => $asset->title,
                'filename' => $asset->filename,
                'url' => $asset->getUrl(),
                'cpUrl' => $asset->getCpEditUrl(),
                'volume' => $asset->volume->name ?? '',
                'volumeId' => $asset->volumeId,
                'size' => $asset->size,
                'path' => $path,
                'kind' => $asset->kind,
            ];
        }

        return $result;
    }
}
