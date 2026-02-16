<?php

declare(strict_types=1);

namespace yann\assetcleaner\jobs;

use Craft;
use craft\elements\Asset;
use craft\queue\BaseJob;
use yann\assetcleaner\helpers\Logger;
use yann\assetcleaner\Plugin;

/**
 * Queue job that scans assets in batches with real progress reporting
 */
class ScanAssetsJob extends BaseJob
{
    /**
     * @var int The scan record ID
     */
    public int $scanId;

    /**
     * @var array Volume IDs to scan
     */
    public array $volumeIds = [];

    /**
     * @var int Number of assets to process per batch
     */
    public int $batchSize = 50;

    /**
     * @inheritdoc
     */
    public function execute($queue): void
    {
        $scanService = Plugin::getInstance()->scan;

        try {
            // Mark scan as running
            $scanService->updateScan($this->scanId, ['status' => 'running']);

            // Build the content index once (the big optimization)
            $contentIndex = Plugin::getInstance()->assetUsage->buildContentIndex();

            // Query all assets to scan
            $assetQuery = Asset::find()->status(null);
            if (!empty($this->volumeIds)) {
                $assetQuery->volumeId($this->volumeIds);
            }

            $allAssetIds = $assetQuery->ids();
            $totalAssets = count($allAssetIds);

            $scanService->updateScan($this->scanId, [
                'totalAssets' => $totalAssets,
            ]);

            if ($totalAssets === 0) {
                $scanService->updateScan($this->scanId, [
                    'status' => 'complete',
                    'progress' => 100,
                    'results' => json_encode([]),
                ]);
                return;
            }

            $processedAssets = 0;
            $usedCount = 0;
            $unusedIds = [];
            $assetUsageService = Plugin::getInstance()->assetUsage;
            $batches = array_chunk($allAssetIds, $this->batchSize);

            foreach ($batches as $batch) {
                // Check if scan was cancelled
                $scan = $scanService->getScan($this->scanId);
                if (!$scan || $scan['status'] === 'failed') {
                    return;
                }

                foreach ($batch as $assetId) {
                    $assetId = (int)$assetId;
                    if ($assetUsageService->isAssetUsedWithIndex($assetId, $contentIndex)) {
                        $usedCount++;
                    } else {
                        $unusedIds[] = $assetId;
                    }
                    $processedAssets++;
                }

                // Update progress
                $progress = (int)round(($processedAssets / $totalAssets) * 100);
                $scanService->updateScan($this->scanId, [
                    'progress' => $progress,
                    'processedAssets' => $processedAssets,
                    'usedCount' => $usedCount,
                    'unusedCount' => count($unusedIds),
                ]);

                $this->setProgress($queue, $processedAssets / $totalAssets);

                // Be gentle on the system
                usleep(50000); // 50ms
            }

            // Build full result data for unused assets
            $unusedAssets = $this->buildUnusedAssetData($unusedIds);

            $scanService->updateScan($this->scanId, [
                'status' => 'complete',
                'progress' => 100,
                'processedAssets' => $totalAssets,
                'usedCount' => $usedCount,
                'unusedCount' => count($unusedIds),
                'results' => json_encode($unusedAssets),
            ]);
        } catch (\Throwable $e) {
            Logger::exception('Scan job failed', $e);
            $scanService->updateScan($this->scanId, [
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
        return Craft::t('asset-cleaner', 'Scanning assets for usage');
    }

    /**
     * Build the full data array for unused assets (same shape as the old sync response)
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
            ];
        }

        return $result;
    }
}
