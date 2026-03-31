<?php

declare(strict_types=1);

namespace yann\assetcleaner\jobs;

use Craft;
use craft\queue\BaseJob;
use yann\assetcleaner\helpers\Logger;
use yann\assetcleaner\Plugin;

/**
 * Finalize scan stage:
 * - merge used IDs
 * - classify scanned assets as used/unused
 * - build final result files
 * - mark the scan complete
 */
class ScanFinalizeJob extends BaseJob
{
    /**
     * @var string The scan ID
     */
    public string $scanId = '';

    /**
     * Execute the finalize stage.
     *
     * @param mixed $queue
     * @return void
     * @throws \Throwable
     */
    public function execute($queue): void
    {
        try {
            Plugin::getInstance()->scanService->finalizeScan($this->scanId);
            $this->setProgress($queue, 1.0);
        } catch (\Throwable $e) {
            Logger::exception('Scan finalize stage failed', $e);

            try {
                Plugin::getInstance()->scanService->failScan($this->scanId, $e);
            } catch (\Throwable) {
                // Avoid masking the original failure.
            }

            throw $e;
        }
    }

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): ?string
    {
        return Craft::t('asset-cleaner', 'Finalizing asset scan results');
    }
}
