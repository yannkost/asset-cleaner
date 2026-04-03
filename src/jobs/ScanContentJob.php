<?php

declare(strict_types=1);

namespace yann\assetcleaner\jobs;

use Craft;
use craft\queue\BaseJob;
use yann\assetcleaner\helpers\Logger;
use yann\assetcleaner\Plugin;

/**
 * Content scan stage.
 *
 * Streams relevant entry/global content, resolves asset references against the
 * file-backed asset snapshot, then queues finalization.
 */
class ScanContentJob extends BaseJob
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
        try {
            $scanService = Plugin::getInstance()->scanService;

            if (!$scanService->scanExists($this->scanId)) {
                return;
            }

            $scanService->collectContentUsage($this->scanId);

            if (!$scanService->scanExists($this->scanId)) {
                return;
            }

            Craft::$app->getQueue()->push(new ScanFinalizeJob([
                'scanId' => $this->scanId,
            ]));
        } catch (\Throwable $e) {
            Logger::exception('Content scan stage failed', $e);

            try {
                Plugin::getInstance()->scanService->failScan($this->scanId, $e);
            } catch (\Throwable) {
                // Don't mask the original exception if failure reporting also fails
            }

            throw $e;
        }
    }

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): ?string
    {
        return Craft::t('asset-cleaner', 'Scanning content for asset references');
    }
}
