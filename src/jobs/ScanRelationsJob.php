<?php

declare(strict_types=1);

namespace yann\assetcleaner\jobs;

use Craft;
use craft\queue\BaseJob;
use yann\assetcleaner\helpers\Logger;
use yann\assetcleaner\Plugin;

/**
 * Queue stage that scans the relations table once for all assets in the scan.
 */
class ScanRelationsJob extends BaseJob
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

        try {
            $scanService->collectRelationsUsage($this->scanId);

            Craft::$app->getQueue()->push(new ScanContentJob([
                'scanId' => $this->scanId,
            ]));
        } catch (\Throwable $e) {
            Logger::exception('Scan relations stage failed', $e);
            $scanService->failScan($this->scanId, $e);
            throw $e;
        }
    }

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): ?string
    {
        return Craft::t('asset-cleaner', 'Scanning asset relations');
    }
}
