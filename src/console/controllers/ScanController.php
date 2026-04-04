<?php

declare(strict_types=1);

namespace yann\assetcleaner\console\controllers;

use Craft;
use craft\console\Controller;
use craft\elements\Asset;
use craft\helpers\Console;
use yann\assetcleaner\helpers\Logger;
use yann\assetcleaner\Plugin;
use yii\console\ExitCode;

/**
 * Asset Cleaner console commands
 */
class ScanController extends Controller
{
    /**
     * @var string|null Comma-separated list of volume handles
     */
    public ?string $volumes = null;

    /**
     * @var bool Force delete without confirmation
     */
    public bool $force = false;

    /**
     * @var bool Dry run - preview only
     */
    public bool $dryRun = false;

    /**
     * @inheritdoc
     */
    public function options($actionID): array
    {
        $options = parent::options($actionID);

        switch ($actionID) {
            case 'index':
            case 'scan':
            case 'export':
                $options[] = 'volumes';
                break;
            case 'delete':
                $options[] = 'volumes';
                $options[] = 'force';
                $options[] = 'dryRun';
                break;
        }

        return $options;
    }

    /**
     * List available commands
     *
     * @return int
     */
    public function actionIndex(): int
    {
        $this->stdout("Asset Cleaner Commands:\n\n", Console::FG_GREEN);
        $this->stdout("  scan     Scan volumes and show asset usage counts\n");
        $this->stdout("  export   Export unused assets to CSV\n");
        $this->stdout("  delete   Delete unused assets\n\n");
        $this->stdout("Options:\n");
        $this->stdout("  --volumes=handle1,handle2   Filter by volume handles\n");
        $this->stdout("  --force                     Delete without confirmation\n");
        $this->stdout("  --dry-run                   Preview only, don't delete\n\n");

        return ExitCode::OK;
    }

    /**
     * Scan volumes and show counts
     *
     * @return int
     */
    public function actionScan(): int
    {
        $volumeIds = $this->getVolumeIds();

        $this->stdout("Scanning for unused assets...\n\n", Console::FG_CYAN);

        $service = Plugin::getInstance()->assetUsage;

        $usedCount = $service->countUsedAssets($volumeIds);
        $unusedCount = $service->countUnusedAssets($volumeIds);

        $this->stdout("Results:\n", Console::FG_GREEN);
        $this->stdout("  Used assets:   {$usedCount}\n");
        $this->stdout("  Unused assets: {$unusedCount}\n\n");

        if ($unusedCount > 0) {
            $this->stdout("Run 'php craft asset-cleaner/scan/export' to export unused assets to CSV.\n");
            $this->stdout("Run 'php craft asset-cleaner/scan/delete --dry-run' to preview deletion.\n");
        }

        return ExitCode::OK;
    }

    /**
     * Export unused assets to CSV
     *
     * @return int
     */
    public function actionExport(): int
    {
        $volumeIds = $this->getVolumeIds();

        $this->stdout("Exporting unused assets...\n\n", Console::FG_CYAN);

        $service = Plugin::getInstance()->assetUsage;
        $unusedAssets = $service->getUnusedAssets($volumeIds);

        if (empty($unusedAssets)) {
            $this->stdout("No unused assets found.\n", Console::FG_YELLOW);
            return ExitCode::OK;
        }

        $filename = 'unused-assets';

        if (!empty($this->volumes)) {
            $handles = array_filter(array_map([$this, 'sanitizeFilename'], array_map('trim', explode(',', $this->volumes))));
            if (!empty($handles)) {
                $filename .= '_' . implode('__', $handles);
            }
        }

        $filename .= '_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = Craft::getAlias('@storage') . '/' . $filename;

        $csv = "ID,Title,Filename,Volume,Size,Path,URL\n";

        foreach ($unusedAssets as $asset) {
            $csv .= sprintf(
                "%d,%s,%s,%s,%d,%s,%s\n",
                $asset['id'],
                '"' . str_replace('"', '""', $asset['title']) . '"',
                '"' . str_replace('"', '""', $asset['filename']) . '"',
                '"' . str_replace('"', '""', $asset['volume']) . '"',
                $asset['size'],
                '"' . str_replace('"', '""', $asset['path'] ?? '') . '"',
                '"' . str_replace('"', '""', $asset['url'] ?? '') . '"'
            );
        }

        file_put_contents($filepath, $csv);

        $this->stdout("Exported " . count($unusedAssets) . " unused assets to:\n", Console::FG_GREEN);
        $this->stdout("  {$filepath}\n\n");

        return ExitCode::OK;
    }

    /**
     * Delete unused assets
     *
     * @return int
     */
    public function actionDelete(): int
    {
        $volumeIds = $this->getVolumeIds();

        $service = Plugin::getInstance()->assetUsage;
        $unusedIds = $service->getUnusedAssetIds($volumeIds);

        if (empty($unusedIds)) {
            $this->stdout("No unused assets found.\n", Console::FG_YELLOW);
            return ExitCode::OK;
        }

        $assets = Asset::find()
            ->id($unusedIds)
            ->status(null)
            ->all();

        $this->stdout("Found " . count($assets) . " unused assets:\n\n", Console::FG_CYAN);

        $totalSize = 0;
        foreach ($assets as $asset) {
            $this->stdout("  - {$asset->filename} ({$asset->volume->name})\n");
            $totalSize += $asset->size;
        }

        $this->stdout("\nTotal size: " . $this->formatBytes($totalSize) . "\n\n");

        if ($this->dryRun) {
            $this->stdout("Dry run - no assets were deleted.\n", Console::FG_YELLOW);
            return ExitCode::OK;
        }

        if (!$this->force) {
            if (!$this->confirm('Are you sure you want to delete these assets?')) {
                $this->stdout("Aborted.\n", Console::FG_YELLOW);
                return ExitCode::OK;
            }
        }

        $deletedCount = 0;
        $errors = [];

        foreach ($assets as $asset) {
            try {
                if (Craft::$app->getElements()->deleteElement($asset)) {
                    $deletedCount++;
                    $this->stdout("  Deleted: {$asset->filename}\n", Console::FG_GREEN);
                } else {
                    $errors[] = $asset->filename;
                    Logger::warning("Failed to delete asset from CLI command.", [
                        'assetId' => (int)$asset->id,
                        'filename' => (string)$asset->filename,
                    ]);
                    $this->stdout("  Failed: {$asset->filename}\n", Console::FG_RED);
                }
            } catch (\Throwable $e) {
                $errors[] = $asset->filename . ': ' . $e->getMessage();
                Logger::exception(
                    "Error deleting asset from CLI command.",
                    $e,
                    [
                        'assetId' => (int)$asset->id,
                        'filename' => (string)$asset->filename,
                    ]
                );
                $this->stdout("  Error: {$asset->filename} - {$e->getMessage()}\n", Console::FG_RED);
            }
        }

        $this->stdout("\nDeleted {$deletedCount} assets.\n", Console::FG_GREEN);

        if (!empty($errors)) {
            $this->stdout("Failed to delete " . count($errors) . " assets.\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }

    /**
     * Get volume IDs from --volumes option
     *
     * @return array
     */
    private function getVolumeIds(): array
    {
        if (empty($this->volumes)) {
            return [];
        }

        $handles = array_map('trim', explode(',', $this->volumes));
        $volumeIds = [];

        foreach ($handles as $handle) {
            $volume = Craft::$app->getVolumes()->getVolumeByHandle($handle);
            if ($volume) {
                $volumeIds[] = $volume->id;
            } else {
                $this->stdout("Warning: Volume '{$handle}' not found.\n", Console::FG_YELLOW);
            }
        }

        return $volumeIds;
    }

    /**
     * Sanitize a string for use in filenames
     *
     * @param string $string
     * @return string
     */
    private function sanitizeFilename(string $string): string
    {
        $string = strtolower($string);
        $string = preg_replace('/[^a-z0-9]+/', '_', $string);
        $string = trim($string, '_');
        return $string;
    }

    /**
     * Format bytes to human readable
     *
     * @param int $bytes
     * @return string
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
