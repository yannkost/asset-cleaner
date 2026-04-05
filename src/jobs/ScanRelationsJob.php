<?php

declare(strict_types=1);

namespace yann\assetcleaner\jobs;

use Craft;
use craft\queue\BaseJob;
use yann\assetcleaner\helpers\Logger;
use yann\assetcleaner\Plugin;

/**
 * Queue stage that scans asset relations in resumable batches across multiple
 * queue executions.
 */
class ScanRelationsJob extends BaseJob
{
    /**
     * @var string The scan ID
     */
    public string $scanId = '';

    /**
     * @var int Number of assets already processed in previous relation batches
     */
    public int $processedAssets = 0;

    /**
     * @var int Number of assets to process per queue execution
     */
    public int $batchSize = 2000;

    /**
     * @inheritdoc
     */
    public function execute($queue): void
    {
        $scanService = Plugin::getInstance()->scanService;

        if (!$scanService->scanExists($this->scanId)) {
            return;
        }

        $meta = $scanService->getMeta($this->scanId);
        if ($meta === null) {
            return;
        }

        $status = (string) ($meta['status'] ?? '');
        $stage = (string) ($meta['stage'] ?? '');

        if (
            in_array($status, ['complete', 'failed'], true) ||
            in_array($stage, ['content', 'finalize'], true)
        ) {
            return;
        }

        try {
            $result = $scanService->collectRelationsUsageBatch(
                $this->scanId,
                $this->processedAssets,
                $this->batchSize,
            );

            if (!$scanService->scanExists($this->scanId)) {
                return;
            }

            $latestMeta = $scanService->getMeta($this->scanId);
            if ($latestMeta === null) {
                return;
            }

            $latestStatus = (string) ($latestMeta['status'] ?? '');
            $latestStage = (string) ($latestMeta['stage'] ?? '');

            if (
                in_array($latestStatus, ['complete', 'failed'], true) ||
                in_array($latestStage, ['content', 'finalize'], true)
            ) {
                return;
            }

            $this->setProgress(
                $queue,
                min(1.0, max(0.0, ((float) ($result['progress'] ?? 0)) / 100)),
            );

            if (!empty($result['stale'])) {
                return;
            }

            if (!empty($result['completed'])) {
                Craft::$app->getQueue()->push(new ScanContentJob([
                    'scanId' => $this->scanId,
                ]));
                return;
            }

            Craft::$app->getQueue()->push(new self([
                'scanId' => $this->scanId,
                'processedAssets' => (int) ($result['processedAssets'] ?? 0),
                'batchSize' => $this->batchSize,
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
