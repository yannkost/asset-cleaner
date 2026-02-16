<?php

declare(strict_types=1);

namespace yann\assetcleaner\jobs;

use Craft;
use craft\elements\Asset;
use craft\queue\BaseJob;
use yann\assetcleaner\helpers\Logger;
use yann\assetcleaner\Plugin;

/**
 * Setup job that builds the content index, caches it, and queues the first batch job
 */
class ScanSetupJob extends BaseJob
{
    /**
     * @var string The scan ID (uniqid string)
     */
    public string $scanId = '';

    /**
     * @var array Volume IDs to scan
     */
    public array $volumeIds = [];

    /**
     * @var int Number of assets per batch
     */
    public int $batchSize = 50;

    /**
     * @inheritdoc
     */
    public function execute($queue): void
    {
        $cache = Craft::$app->getCache();

        try {
            // Mark scan as running
            $this->updateProgress($cache, ['status' => 'running']);

            // Build the content index once and cache it
            $contentIndex = Plugin::getInstance()->assetUsage->buildContentIndex();
            $cache->set("asset-cleaner-index-{$this->scanId}", $contentIndex, 3600);

            // Query all asset IDs to scan
            $assetQuery = Asset::find()->status(null);
            if (!empty($this->volumeIds)) {
                $assetQuery->volumeId($this->volumeIds);
            }

            $allAssetIds = $assetQuery->ids();
            $totalAssets = count($allAssetIds);

            // Update progress with total count
            $this->updateProgress($cache, [
                'status' => 'running',
                'totalAssets' => $totalAssets,
            ]);

            if ($totalAssets === 0) {
                // Nothing to scan — complete immediately
                $this->updateProgress($cache, [
                    'status' => 'complete',
                    'progress' => 100,
                ]);
                $cache->set("asset-cleaner-results-{$this->scanId}", [], 3600);
                return;
            }

            // Queue the first batch job
            Craft::$app->getQueue()->push(new ScanBatchJob([
                'scanId' => $this->scanId,
                'allAssetIds' => $allAssetIds,
                'offset' => 0,
                'batchSize' => $this->batchSize,
            ]));
        } catch (\Throwable $e) {
            Logger::exception('Scan setup failed', $e);
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
        return Craft::t('asset-cleaner', 'Scanning assets for usage');
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
}
