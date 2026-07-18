<?php

declare(strict_types=1);

namespace yann\assetcleaner\console\controllers;

use Craft;
use craft\console\Controller;
use craft\elements\Asset;
use craft\helpers\Console;
use yann\assetcleaner\Plugin;
use yii\console\ExitCode;

/**
 * Asset Cleaner diagnostic commands
 */
class DiagnosticsController extends Controller
{
    /**
     * @var string|null Comma-separated list of volume handles to sample from
     */
    public ?string $volumes = null;

    /**
     * @var int Number of assets to sample
     */
    public int $limit = 500;

    /**
     * @var bool|null Include drafts as usage (defaults to configured value)
     */
    public ?bool $includeDrafts = null;

    /**
     * @var bool|null Include revisions as usage (defaults to configured value)
     */
    public ?bool $includeRevisions = null;

    /**
     * @inheritdoc
     */
    public function options($actionID): array
    {
        $options = parent::options($actionID);

        if ($actionID === 'relation-parity') {
            $options[] = 'volumes';
            $options[] = 'limit';
            $options[] = 'includeDrafts';
            $options[] = 'includeRevisions';
        }

        return $options;
    }

    /**
     * Compare bulk-primed relation verdicts against the per-source path.
     *
     * Runs relation usage resolution twice over the same sampled assets —
     * once with bulk priming forced off, once forced on — and diffs the
     * resulting used-asset ID sets for both scan modes. Any mismatch means
     * the bulk loader feeds different data into the usage policy than the
     * per-source path and must be fixed before trusting bulk resolution.
     *
     * @return int
     */
    public function actionRelationParity(): int
    {
        $assetQuery = Asset::find()->status(null)->limit($this->limit);

        $volumeIds = $this->getVolumeIds();
        if (!empty($volumeIds)) {
            $assetQuery->volumeId($volumeIds);
        }

        $assetIds = array_map('intval', $assetQuery->ids());
        if (empty($assetIds)) {
            $this->stdout("No assets found to sample.\n", Console::FG_YELLOW);
            return ExitCode::OK;
        }

        $this->stdout(
            'Comparing relation verdicts for ' . count($assetIds) . " assets...\n\n",
            Console::FG_CYAN,
        );

        $service = Plugin::getInstance()->assetUsage;
        $resolver = $service->getRelationUsageResolver();

        $hasMismatch = false;

        foreach ([true, false] as $countAllRelationsAsUsage) {
            $modeLabel = $countAllRelationsAsUsage
                ? 'countAllRelationsAsUsage=true (fallback mode)'
                : 'countAllRelationsAsUsage=false (strict mode)';

            $results = [];
            foreach ([false, true] as $bulk) {
                $resolver->forceBulkResolution = $bulk;
                $service->resetRelationResolutionCaches();

                $results[(int) $bulk] = $resolver->getResolvedRelationUsageIds(
                    $assetIds,
                    $this->includeDrafts,
                    $this->includeRevisions,
                    null,
                    $countAllRelationsAsUsage,
                );
            }

            $onlyPerSource = array_diff($results[0], $results[1]);
            $onlyBulk = array_diff($results[1], $results[0]);

            if (empty($onlyPerSource) && empty($onlyBulk)) {
                $this->stdout(
                    "  OK   {$modeLabel}: " . count($results[0]) . " used assets, identical\n",
                    Console::FG_GREEN,
                );
                continue;
            }

            $hasMismatch = true;
            $this->stdout("  FAIL {$modeLabel}:\n", Console::FG_RED);
            if (!empty($onlyPerSource)) {
                $this->stdout(
                    '       used only per-source: ' . implode(', ', $onlyPerSource) . "\n",
                );
            }
            if (!empty($onlyBulk)) {
                $this->stdout(
                    '       used only bulk:       ' . implode(', ', $onlyBulk) . "\n",
                );
            }
        }

        $resolver->forceBulkResolution = null;
        $service->resetRelationResolutionCaches();

        $this->stdout("\n");
        if ($hasMismatch) {
            $this->stdout(
                "Parity check FAILED — do not trust bulk resolution on this install.\n",
                Console::FG_RED,
            );
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout("Parity check passed.\n", Console::FG_GREEN);
        return ExitCode::OK;
    }

    /**
     * Resolve volume handles to IDs.
     *
     * @return array<int>
     */
    private function getVolumeIds(): array
    {
        if (empty($this->volumes)) {
            return [];
        }

        $volumeIds = [];
        foreach (array_map('trim', explode(',', $this->volumes)) as $handle) {
            if ($handle === '') {
                continue;
            }

            $volume = Craft::$app->getVolumes()->getVolumeByHandle($handle);
            if ($volume === null) {
                $this->stdout(
                    "Unknown volume handle: {$handle}\n",
                    Console::FG_YELLOW,
                );
                continue;
            }

            $volumeIds[] = (int) $volume->id;
        }

        return $volumeIds;
    }
}
