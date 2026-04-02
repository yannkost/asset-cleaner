<?php

declare(strict_types=1);

namespace yann\assetcleaner\services\stores;

use Craft;
use craft\base\Component;
use craft\helpers\FileHelper;
use craft\helpers\Json;
use yann\assetcleaner\helpers\Logger;
use yann\assetcleaner\Plugin;
use yann\assetcleaner\services\ScanService;
use yii\base\Exception;

/**
 * File-backed scan store.
 *
 * Persists scan state under a shared filesystem path, defaulting to:
 * @storage/asset-cleaner/scans/<scanId>/
 */
class FileScanStore extends Component implements ScanStoreInterface
{
    private const LAST_SCAN_FILE = 'last-scan.json';

    /**
     * @inheritdoc
     */
    public function clearRetainedScans(): void
    {
        $root = $this->getScansRootPath();

        if (is_dir($root)) {
            FileHelper::removeDirectory($root);
        }

        $this->ensureBaseDirectories();

        $lastScanPath = $this->getLastScanFilePath();
        if (is_file($lastScanPath)) {
            @unlink($lastScanPath);
        }
    }

    /**
     * @inheritdoc
     */
    public function initializeScan(string $scanId, array $volumeIds, int $assetChunkSize, int $entryBatchSize, bool $includeDrafts, bool $includeRevisions, ?int $initiatorId = null): void
    {
        $scanId = trim($scanId);
        if ($scanId === '') {
            throw new Exception('Missing scan ID.');
        }

        $volumeIds = array_values(array_unique(array_map('intval', $volumeIds)));
        $assetChunkSize = max(1, $assetChunkSize);
        $entryBatchSize = max(1, $entryBatchSize);
        $includeDrafts = (bool)$includeDrafts;
        $includeRevisions = (bool)$includeRevisions;

        $this->ensureBaseDirectories();
        $this->ensureWritableDirectory($this->getScanPath($scanId));
        $this->ensureWritableDirectory($this->getAssetsDirectory($scanId));
        $this->ensureWritableDirectory($this->getUsedDirectory($scanId));
        $this->ensureWritableDirectory($this->getResultsDirectory($scanId));

        $now = time();
        $initiatingUserId = $initiatorId;

        $this->writeJsonFile($this->getMetaPath($scanId), [
            'scanId' => $scanId,
            'createdAt' => $now,
            'updatedAt' => $now,
            'completedAt' => null,
            'initiatingUserId' => $initiatingUserId !== null ? (int)$initiatingUserId : null,
            'volumeIds' => $volumeIds,
            'assetChunkSize' => $assetChunkSize,
            'entryBatchSize' => $entryBatchSize,
            'includeDrafts' => $includeDrafts,
            'includeRevisions' => $includeRevisions,
            'status' => ScanService::STATUS_PENDING,
            'stage' => ScanService::STAGE_SETUP,
            'totalAssets' => 0,
            'processedAssets' => 0,
            'usedCount' => 0,
            'unusedCount' => 0,
            'totalChunks' => 0,
            'error' => null,
        ]);

        $this->writeJsonFile($this->getProgressPath($scanId), [
            'status' => ScanService::STATUS_PENDING,
            'stage' => ScanService::STAGE_SETUP,
            'progress' => 0,
            'totalAssets' => 0,
            'processedAssets' => 0,
            'usedCount' => 0,
            'unusedCount' => 0,
            'error' => null,
            'updatedAt' => $now,
        ]);

        $this->assertReadableFile($this->getMetaPath($scanId), 'scan metadata file');
        $this->assertReadableFile($this->getProgressPath($scanId), 'scan progress file');

        Logger::debug('Initialized file-backed scan workspace.', $this->buildStorageDiagnostics($scanId));
    }

    /**
     * @inheritdoc
     */
    public function scanExists(string $scanId): bool
    {
        return is_dir($this->getScanPath($scanId));
    }

    /**
     * @inheritdoc
     */
    public function getMeta(string $scanId): ?array
    {
        $meta = $this->readJsonFile($this->getMetaPath($scanId));

        return is_array($meta) ? $meta : null;
    }

    /**
     * @inheritdoc
     */
    public function getProgress(string $scanId): ?array
    {
        $progress = $this->readJsonFile($this->getProgressPath($scanId));

        return is_array($progress) ? $progress : null;
    }

    /**
     * @inheritdoc
     */
    public function updateMeta(string $scanId, array $updates): void
    {
        $meta = $this->getMeta($scanId);
        if ($meta === null) {
            Logger::error('Scan metadata file is missing or unreadable.', $this->buildStorageDiagnostics($scanId));
            throw new Exception("Scan metadata not found for '{$scanId}'. Expected metadata file at '{$this->getMetaPath($scanId)}'.");
        }

        $meta = array_merge($meta, $updates, [
            'updatedAt' => time(),
        ]);

        $this->writeJsonFile($this->getMetaPath($scanId), $meta);
    }

    /**
     * @inheritdoc
     */
    public function updateProgress(string $scanId, array $updates): void
    {
        $progress = $this->getProgress($scanId) ?? [];

        $progress = array_merge($progress, $updates, [
            'updatedAt' => time(),
        ]);

        if (!isset($progress['stage'])) {
            $progress['stage'] = ScanService::STAGE_SETUP;
        }

        $this->writeJsonFile($this->getProgressPath($scanId), $progress);

        if ($this->scanExists($scanId) && $this->getMeta($scanId) !== null) {
            $this->updateMeta($scanId, [
                'status' => $progress['status'] ?? ScanService::STATUS_RUNNING,
                'stage' => $progress['stage'],
                'totalAssets' => (int)($progress['totalAssets'] ?? 0),
                'processedAssets' => (int)($progress['processedAssets'] ?? 0),
                'usedCount' => (int)($progress['usedCount'] ?? 0),
                'unusedCount' => (int)($progress['unusedCount'] ?? 0),
                'error' => $progress['error'] ?? null,
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function failScan(string $scanId, string $message): void
    {
        if (!$this->scanExists($scanId)) {
            return;
        }

        if ($this->getMeta($scanId) !== null) {
            $this->updateMeta($scanId, [
                'status' => ScanService::STATUS_FAILED,
                'error' => $message,
            ]);
        }

        $this->updateProgress($scanId, [
            'status' => ScanService::STATUS_FAILED,
            'error' => $message,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getResults(string $scanId): ?array
    {
        $path = $this->getResultsPath($scanId);
        if (!is_file($path)) {
            $meta = $this->getMeta($scanId);

            return (($meta['status'] ?? null) === ScanService::STATUS_COMPLETE) ? [] : null;
        }

        $results = [];
        foreach ($this->iterateNdjsonFile($path) as $row) {
            if (is_array($row)) {
                $results[] = $row;
            }
        }

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function hasResults(string $scanId): bool
    {
        if (is_file($this->getResultsPath($scanId))) {
            return true;
        }

        $meta = $this->getMeta($scanId);

        return (($meta['status'] ?? null) === ScanService::STATUS_COMPLETE);
    }

    /**
     * @inheritdoc
     */
    public function getLastScan(): ?array
    {
        $payload = $this->readJsonFile($this->getLastScanFilePath());
        if (!is_array($payload)) {
            return null;
        }

        if (empty($payload['scanId']) || empty($payload['completedAt'])) {
            return null;
        }

        if (!$this->hasResults((string)$payload['scanId'])) {
            return null;
        }

        return $payload;
    }

    /**
     * @inheritdoc
     */
    public function setLastScan(string $scanId, ?int $completedAt = null): void
    {
        $this->writeJsonFile($this->getLastScanFilePath(), [
            'scanId' => $scanId,
            'completedAt' => $completedAt ?? time(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function resetAssetSnapshot(string $scanId): void
    {
        $this->clearDirectory($this->getAssetsDirectory($scanId));
    }

    /**
     * @inheritdoc
     */
    public function storeAssetSnapshotChunk(string $scanId, int $chunkIndex, array $rows): void
    {
        $filename = sprintf('chunk-%06d.ndjson', $chunkIndex + 1);
        $path = $this->getAssetsDirectory($scanId) . DIRECTORY_SEPARATOR . $filename;

        $this->writeNdjsonFile($path, $rows);
    }

    /**
     * @inheritdoc
     */
    public function iterateAssetSnapshot(string $scanId): iterable
    {
        foreach ($this->getAssetChunkFiles($scanId) as $path) {
            foreach ($this->iterateNdjsonFile($path) as $row) {
                if (is_array($row)) {
                    yield $row;
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function replaceUsedIds(string $scanId, string $source, array $assetIds): void
    {
        $assetIds = array_values(array_unique(array_map('intval', $assetIds)));
        sort($assetIds, SORT_NUMERIC);

        $this->writeLines($this->getUsedIdsPath($scanId, $source), $assetIds);
    }

    /**
     * @inheritdoc
     */
    public function getMergedUsedIds(string $scanId): array
    {
        $used = [];
        $directory = $this->getUsedDirectory($scanId);

        if (!is_dir($directory)) {
            return [];
        }

        $files = glob($directory . DIRECTORY_SEPARATOR . '*.txt') ?: [];
        sort($files, SORT_NATURAL);

        foreach ($files as $path) {
            if (basename($path) === 'final.txt') {
                continue;
            }

            foreach ($this->iterateTextLines($path) as $line) {
                $id = (int)$line;
                if ($id > 0) {
                    $used[$id] = true;
                }
            }
        }

        $ids = array_map('intval', array_keys($used));
        sort($ids, SORT_NUMERIC);

        return $ids;
    }

    /**
     * @inheritdoc
     */
    public function resetResults(string $scanId): void
    {
        $this->ensureWritableDirectory($this->getResultsDirectory($scanId));

        $resultsPath = $this->getResultsPath($scanId);
        if (is_file($resultsPath)) {
            @unlink($resultsPath);
        }
    }

    /**
     * @inheritdoc
     */
    public function appendResultRows(string $scanId, array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        $path = $this->getResultsPath($scanId);
        $this->ensureWritableDirectory(dirname($path));

        $fh = fopen($path, 'ab');
        if ($fh === false) {
            $this->logStorageFailure('Unable to open results file for appending.', [
                'scanId' => $scanId,
                'path' => $path,
            ]);
            throw new Exception("Unable to open results file for scan '{$scanId}'.");
        }

        try {
            foreach ($rows as $row) {
                $written = fwrite($fh, Json::encode($row) . PHP_EOL);
                if ($written === false) {
                    $this->logStorageFailure('Unable to append unused asset result row.', [
                        'scanId' => $scanId,
                        'path' => $path,
                    ]);
                    throw new Exception("Unable to append unused asset results for scan '{$scanId}'.");
                }
            }

            if (!fflush($fh)) {
                $this->logStorageFailure('Unable to flush results file after appending.', [
                    'scanId' => $scanId,
                    'path' => $path,
                ]);
                throw new Exception("Unable to flush results file for scan '{$scanId}'.");
            }
        } finally {
            fclose($fh);
        }

        $this->assertReadableFile($path, 'unused asset results file');
    }

    /**
     * Resolve the base workspace path for file-backed scan storage.
     */
    public function getBaseStoragePath(): string
    {
        $configuredPath = $this->getConfiguredWorkspacePath();
        if ($configuredPath !== null) {
            return $configuredPath;
        }

        return Craft::getAlias('@storage') . DIRECTORY_SEPARATOR . 'asset-cleaner';
    }

    private function getConfiguredWorkspacePath(): ?string
    {
        $configuredPath = null;

        try {
            $config = Craft::$app->getConfig()->getConfigFromFile('asset-cleaner');
            if (is_array($config) && isset($config['scanWorkspacePath']) && is_string($config['scanWorkspacePath'])) {
                $configuredPath = trim($config['scanWorkspacePath']);
            }
        } catch (\Throwable $e) {
            Logger::warning('Could not load Asset Cleaner config while resolving the file-backed scan workspace path.', [
                'error' => $e->getMessage(),
            ]);
        }

        try {
            $settings = Plugin::getInstance()->getSettings();
            if (is_object($settings) && isset($settings->scanWorkspacePath) && is_string($settings->scanWorkspacePath) && trim($settings->scanWorkspacePath) !== '') {
                $configuredPath ??= trim($settings->scanWorkspacePath);
            }
        } catch (\Throwable $e) {
            Logger::warning('Could not load Asset Cleaner settings while resolving the file-backed scan workspace path.', [
                'error' => $e->getMessage(),
            ]);
        }

        $envPath = getenv('ASSET_CLEANER_SCAN_PATH');
        if (is_string($envPath) && trim($envPath) !== '') {
            $configuredPath = trim($envPath);
        }

        if ($configuredPath === null || $configuredPath === '') {
            return null;
        }

        $configuredPath = Craft::parseEnv($configuredPath);
        $resolvedAlias = Craft::getAlias($configuredPath, false);

        return $resolvedAlias !== false ? $resolvedAlias : $configuredPath;
    }

    private function getScansRootPath(): string
    {
        return $this->getBaseStoragePath() . DIRECTORY_SEPARATOR . 'scans';
    }

    private function getScanPath(string $scanId): string
    {
        return $this->getScansRootPath() . DIRECTORY_SEPARATOR . $scanId;
    }

    private function getMetaPath(string $scanId): string
    {
        return $this->getScanPath($scanId) . DIRECTORY_SEPARATOR . 'meta.json';
    }

    private function getProgressPath(string $scanId): string
    {
        return $this->getScanPath($scanId) . DIRECTORY_SEPARATOR . 'progress.json';
    }

    private function getAssetsDirectory(string $scanId): string
    {
        return $this->getScanPath($scanId) . DIRECTORY_SEPARATOR . 'assets';
    }

    private function getUsedDirectory(string $scanId): string
    {
        return $this->getScanPath($scanId) . DIRECTORY_SEPARATOR . 'used';
    }

    private function getResultsDirectory(string $scanId): string
    {
        return $this->getScanPath($scanId) . DIRECTORY_SEPARATOR . 'results';
    }

    private function getResultsPath(string $scanId): string
    {
        return $this->getResultsDirectory($scanId) . DIRECTORY_SEPARATOR . 'unused.ndjson';
    }

    private function getUsedIdsPath(string $scanId, string $source): string
    {
        return $this->getUsedDirectory($scanId) . DIRECTORY_SEPARATOR . $source . '.txt';
    }

    private function getLastScanFilePath(): string
    {
        return $this->getBaseStoragePath() . DIRECTORY_SEPARATOR . self::LAST_SCAN_FILE;
    }

    private function ensureBaseDirectories(): void
    {
        $this->ensureWritableDirectory($this->getBaseStoragePath());
        $this->ensureWritableDirectory($this->getScansRootPath());
    }

    private function getAssetChunkFiles(string $scanId): array
    {
        $directory = $this->getAssetsDirectory($scanId);
        if (!is_dir($directory)) {
            return [];
        }

        $files = glob($directory . DIRECTORY_SEPARATOR . 'chunk-*.ndjson') ?: [];
        sort($files, SORT_NATURAL);

        return array_values($files);
    }

    private function clearDirectory(string $directory): void
    {
        if (is_dir($directory)) {
            FileHelper::removeDirectory($directory);
        }

        $this->ensureWritableDirectory($directory);
    }

    private function writeJsonFile(string $path, array $payload): void
    {
        $this->ensureWritableDirectory(dirname($path));
        $json = Json::encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $this->writeFileAtomically($path, $json . PHP_EOL);
    }

    private function readJsonFile(string $path): mixed
    {
        if (!is_file($path)) {
            return null;
        }

        $contents = file_get_contents($path);
        if ($contents === false || trim($contents) === '') {
            return null;
        }

        try {
            return Json::decode($contents);
        } catch (\Throwable) {
            return null;
        }
    }

    private function writeNdjsonFile(string $path, array $rows): void
    {
        $this->ensureWritableDirectory(dirname($path));

        $tmpPath = $path . '.tmp-' . bin2hex(random_bytes(6));
        $fh = fopen($tmpPath, 'wb');
        if ($fh === false) {
            $this->logStorageFailure('Unable to open temporary NDJSON file for writing.', [
                'path' => $path,
                'tmpPath' => $tmpPath,
            ]);
            throw new Exception("Unable to open '{$path}' for writing.");
        }

        try {
            foreach ($rows as $row) {
                $written = fwrite($fh, Json::encode($row) . PHP_EOL);
                if ($written === false) {
                    $this->logStorageFailure('Unable to write NDJSON row to temporary scan file.', [
                        'path' => $path,
                        'tmpPath' => $tmpPath,
                    ]);
                    throw new Exception("Unable to write '{$path}'.");
                }
            }

            if (!fflush($fh)) {
                $this->logStorageFailure('Unable to flush temporary NDJSON scan file.', [
                    'path' => $path,
                    'tmpPath' => $tmpPath,
                ]);
                throw new Exception("Unable to flush '{$path}'.");
            }
        } finally {
            fclose($fh);
        }

        if (!@rename($tmpPath, $path)) {
            $error = error_get_last();
            @unlink($tmpPath);
            $this->logStorageFailure('Unable to move temporary NDJSON scan file into place.', [
                'path' => $path,
                'tmpPath' => $tmpPath,
                'renameError' => $error['message'] ?? 'Unknown rename error.',
            ]);
            throw new Exception("Unable to finalize '{$path}' after writing.");
        }

        $this->assertReadableFile($path, 'NDJSON file');
    }

    private function iterateNdjsonFile(string $path): \Generator
    {
        if (!is_file($path)) {
            return;
        }

        $fh = fopen($path, 'rb');
        if ($fh === false) {
            return;
        }

        try {
            while (($line = fgets($fh)) !== false) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                try {
                    $row = Json::decode($line);
                } catch (\Throwable) {
                    continue;
                }

                if (is_array($row)) {
                    yield $row;
                }
            }
        } finally {
            fclose($fh);
        }
    }

    private function writeLines(string $path, array $lines): void
    {
        $this->ensureWritableDirectory(dirname($path));

        $content = '';
        foreach ($lines as $line) {
            $content .= trim((string)$line) . PHP_EOL;
        }

        $this->writeFileAtomically($path, $content);
    }

    private function iterateTextLines(string $path): \Generator
    {
        if (!is_file($path)) {
            return;
        }

        $fh = fopen($path, 'rb');
        if ($fh === false) {
            return;
        }

        try {
            while (($line = fgets($fh)) !== false) {
                $line = trim($line);
                if ($line !== '') {
                    yield $line;
                }
            }
        } finally {
            fclose($fh);
        }
    }

    private function writeFileAtomically(string $path, string $contents): void
    {
        $this->ensureWritableDirectory(dirname($path));

        $tmpPath = $path . '.tmp-' . bin2hex(random_bytes(6));
        $bytesWritten = file_put_contents($tmpPath, $contents, LOCK_EX);
        if ($bytesWritten === false) {
            $this->logStorageFailure('Unable to write temporary scan file.', [
                'path' => $path,
                'tmpPath' => $tmpPath,
            ]);
            throw new Exception("Unable to write temporary file for '{$path}'.");
        }

        if (!@rename($tmpPath, $path)) {
            $error = error_get_last();
            @unlink($tmpPath);
            $this->logStorageFailure('Unable to move temporary scan file into place.', [
                'path' => $path,
                'tmpPath' => $tmpPath,
                'renameError' => $error['message'] ?? 'Unknown rename error.',
            ]);
            throw new Exception("Unable to finalize '{$path}' after writing.");
        }

        $this->assertReadableFile($path, 'scan file');
    }

    private function ensureWritableDirectory(string $directory): void
    {
        try {
            FileHelper::createDirectory($directory);
        } catch (\Throwable $e) {
            $this->logStorageFailure('Unable to create scan workspace directory.', [
                'directory' => $directory,
                'error' => $e->getMessage(),
            ]);
            throw new Exception("Unable to create scan workspace directory '{$directory}': " . $e->getMessage());
        }

        clearstatcache(true, $directory);

        if (!is_dir($directory)) {
            $this->logStorageFailure('Scan workspace directory does not exist after creation attempt.', [
                'directory' => $directory,
            ]);
            throw new Exception("Scan workspace directory '{$directory}' does not exist after creation.");
        }

        if (!is_writable($directory)) {
            $this->logStorageFailure('Scan workspace directory is not writable.', [
                'directory' => $directory,
            ]);
            throw new Exception("Scan workspace directory '{$directory}' is not writable.");
        }
    }

    private function assertReadableFile(string $path, string $label): void
    {
        clearstatcache(true, $path);

        if (!is_file($path)) {
            $this->logStorageFailure('Expected scan file was not found after writing.', [
                'path' => $path,
                'label' => $label,
            ]);
            throw new Exception("Expected {$label} at '{$path}' but it was not found after writing.");
        }

        if (!is_readable($path)) {
            $this->logStorageFailure('Expected scan file is not readable after writing.', [
                'path' => $path,
                'label' => $label,
            ]);
            throw new Exception("Expected {$label} at '{$path}' but it is not readable.");
        }
    }

    private function buildStorageDiagnostics(?string $scanId = null): array
    {
        $scanPath = $scanId !== null ? $this->getScanPath($scanId) : null;
        $metaPath = $scanId !== null ? $this->getMetaPath($scanId) : null;

        return [
            'scanId' => $scanId,
            'configuredWorkspacePath' => $this->getConfiguredWorkspacePath(),
            'resolvedBaseStoragePath' => $this->getBaseStoragePath(),
            'storageAlias' => Craft::getAlias('@storage', false),
            'scansRootPath' => $this->getScansRootPath(),
            'scanPath' => $scanPath,
            'scanPathExists' => $scanPath !== null ? is_dir($scanPath) : null,
            'metaPath' => $metaPath,
            'metaPathExists' => $metaPath !== null ? is_file($metaPath) : null,
            'phpSapi' => PHP_SAPI,
            'hostname' => function_exists('gethostname') ? gethostname() : null,
        ];
    }

    private function logStorageFailure(string $message, array $context = []): void
    {
        Logger::error($message, array_merge($this->buildStorageDiagnostics(), $context));
    }
}
