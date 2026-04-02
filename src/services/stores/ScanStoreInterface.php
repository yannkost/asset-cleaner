<?php

declare(strict_types=1);

namespace yann\assetcleaner\services\stores;

/**
 * Storage contract for Asset Cleaner scan state.
 *
 * Implementations may persist scan state to the filesystem, database, or
 * another backend, while the scan coordinator remains storage-agnostic.
 */
interface ScanStoreInterface
{
    /**
     * Clear any previously retained scans for this storage backend.
     *
     * The plugin only retains the latest scan for restore/export workflows.
     *
     * @return void
     */
    public function clearRetainedScans(): void;

    /**
     * Initialize a new scan record/workspace.
     *
     * @param string $scanId
     * @param array $volumeIds
     * @param int $assetChunkSize
     * @param int $entryBatchSize
     * @return void
     */
    public function initializeScan(string $scanId, array $volumeIds, int $assetChunkSize, int $entryBatchSize): void;

    /**
     * Whether the scan still exists in the active storage backend.
     *
     * @param string $scanId
     * @return bool
     */
    public function scanExists(string $scanId): bool;

    /**
     * Get scan metadata.
     *
     * @param string $scanId
     * @return array|null
     */
    public function getMeta(string $scanId): ?array;

    /**
     * Get scan progress.
     *
     * @param string $scanId
     * @return array|null
     */
    public function getProgress(string $scanId): ?array;

    /**
     * Merge updates into scan metadata.
     *
     * @param string $scanId
     * @param array $updates
     * @return void
     */
    public function updateMeta(string $scanId, array $updates): void;

    /**
     * Merge updates into scan progress.
     *
     * @param string $scanId
     * @param array $updates
     * @return void
     */
    public function updateProgress(string $scanId, array $updates): void;

    /**
     * Mark a scan as failed.
     *
     * @param string $scanId
     * @param string $message
     * @return void
     */
    public function failScan(string $scanId, string $message): void;

    /**
     * Read final unused asset results for a scan.
     *
     * @param string $scanId
     * @return array|null
     */
    public function getResults(string $scanId): ?array;

    /**
     * Whether the scan has finalized results.
     *
     * @param string $scanId
     * @return bool
     */
    public function hasResults(string $scanId): bool;

    /**
     * Get the latest retained completed scan, if any.
     *
     * @return array|null
     */
    public function getLastScan(): ?array;

    /**
     * Record the latest retained completed scan.
     *
     * @param string $scanId
     * @param int|null $completedAt
     * @return void
     */
    public function setLastScan(string $scanId, ?int $completedAt = null): void;

    /**
     * Remove any existing asset snapshot rows/files for the scan.
     *
     * @param string $scanId
     * @return void
     */
    public function resetAssetSnapshot(string $scanId): void;

    /**
     * Store one snapshot chunk of asset rows.
     *
     * @param string $scanId
     * @param int $chunkIndex
     * @param array $rows
     * @return void
     */
    public function storeAssetSnapshotChunk(string $scanId, int $chunkIndex, array $rows): void;

    /**
     * Iterate all stored asset snapshot rows for the scan.
     *
     * @param string $scanId
     * @return iterable
     */
    public function iterateAssetSnapshot(string $scanId): iterable;

    /**
     * Replace the stored used asset IDs for one source.
     *
     * Source values are expected to be things like `relations`, `content`,
     * or `final`.
     *
     * @param string $scanId
     * @param string $source
     * @param array $assetIds
     * @return void
     */
    public function replaceUsedIds(string $scanId, string $source, array $assetIds): void;

    /**
     * Get merged unique used asset IDs for the scan.
     *
     * @param string $scanId
     * @return array<int>
     */
    public function getMergedUsedIds(string $scanId): array;

    /**
     * Remove any previously stored final results for the scan.
     *
     * @param string $scanId
     * @return void
     */
    public function resetResults(string $scanId): void;

    /**
     * Append final unused asset result rows for the scan.
     *
     * @param string $scanId
     * @param array $rows
     * @return void
     */
    public function appendResultRows(string $scanId, array $rows): void;
}
