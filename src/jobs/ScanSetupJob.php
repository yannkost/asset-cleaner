<?php

declare(strict_types=1);

namespace yann\assetcleaner\jobs;

use Craft;
use craft\queue\BaseJob;
use yann\assetcleaner\helpers\Logger;
use yann\assetcleaner\Plugin;

/**
 * Setup job that creates the file-backed scan workspace and snapshots
 * the assets in scope into chunk files.
 */
class ScanSetupJob extends BaseJob
{
    /**
     * @var string The scan ID
     */
    public string $scanId = '';



    /**
     * @inheritdoc
     */
    public function execute($queue): void
    {
        $scanService = Plugin::getInstance()->scanService;

        if (!$scanService->scanExists($this->scanId)) {
            return;
        }

        try {
            $scanService->snapshotAssets($this->scanId);
            $meta = $scanService->getMeta($this->scanId);
            if ($meta === null) {
                return;
            }

            $totalAssets = (int)($meta['totalAssets'] ?? 0);

            $this->setProgress($queue, $totalAssets > 0 ? 0.1 : 1.0);

            if ($totalAssets === 0) {
                return;
            }

            Craft::$app->getQueue()->push(new ScanRelationsJob([
                'scanId' => $this->scanId,
            ]));
        } catch (\Throwable $e) {
            Logger::exception('Scan setup failed', $e);
            $scanService->failScan($this->scanId, $e);
            throw $e;
        }
    }

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): ?string
    {
        return Craft::t('asset-cleaner', 'Preparing asset scan');
    }
}
