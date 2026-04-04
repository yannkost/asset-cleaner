<?php

declare(strict_types=1);

namespace yann\assetcleaner\services;

use Craft;
use craft\base\Component;
use craft\db\Query;
use craft\db\Table;
use craft\elements\Asset;
use craft\elements\Entry;
use craft\elements\GlobalSet;
use yann\assetcleaner\helpers\Logger;
use yann\assetcleaner\models\Settings;
use yann\assetcleaner\Plugin;
use yann\assetcleaner\services\stores\DbScanStore;
use yann\assetcleaner\services\stores\FileScanStore;
use yann\assetcleaner\services\stores\ScanStoreInterface;

/**
 * Storage-agnostic scan coordinator.
 *
 * This service owns the scan pipeline orchestration while delegating
 * persistence to the configured scan store backend.
 */
class ScanService extends Component
{
    public const STATUS_PENDING = "pending";
    public const STATUS_RUNNING = "running";
    public const STATUS_COMPLETE = "complete";
    public const STATUS_FAILED = "failed";

    public const STAGE_SETUP = "setup";
    public const STAGE_RELATIONS = "relations";
    public const STAGE_CONTENT = "content";
    public const STAGE_FINALIZE = "finalize";

    private const DEFAULT_ASSET_CHUNK_SIZE = 100;
    private const DEFAULT_ENTRY_BATCH_SIZE = 200;
    private const DEFAULT_RESULT_QUERY_CHUNK_SIZE = 250;

    /**
     * @var array<string, ScanStoreInterface>
     */
    private array $stores = [];

    /**
     * Create a new scan and clear any previously retained scan from the active
     * storage backend.
     *
     * @param string $scanId
     * @param array $volumeIds
     * @param int $assetChunkSize
     * @param bool $includeDrafts
     * @param bool $includeRevisions
     * @param bool $countAllRelationsAsUsage
     * @param int|null $initiatorId
     * @return void
     */
    public function initializeScan(
        string $scanId,
        array $volumeIds = [],
        int $assetChunkSize = self::DEFAULT_ASSET_CHUNK_SIZE,
        bool $includeDrafts = false,
        bool $includeRevisions = false,
        bool $countAllRelationsAsUsage = true,
        ?int $initiatorId = null,
    ): void {
        $scanId = trim($scanId);
        if ($scanId === "") {
            throw new \InvalidArgumentException("Missing scan ID.");
        }

        $store = $this->getStore();
        $store->clearRetainedScans();
        $store->initializeScan(
            $scanId,
            $volumeIds,
            max(1, $assetChunkSize),
            self::DEFAULT_ENTRY_BATCH_SIZE,
            $includeDrafts,
            $includeRevisions,
            $countAllRelationsAsUsage,
            $initiatorId,
        );
    }

    /**
     * Whether the scan still exists in the active backend.
     *
     * Old queued jobs may outlive the retained scan and should exit quietly.
     *
     * @param string $scanId
     * @return bool
     */
    public function scanExists(string $scanId): bool
    {
        return $this->getStore()->scanExists($scanId);
    }

    /**
     * Resolve the default draft-inclusion policy for new scans.
     *
     * Config overrides the plugin setting.
     *
     * @return bool
     */
    public function getDefaultIncludeDrafts(): bool
    {
        $configuredValue = $this->getConfiguredIncludeDrafts();
        if ($configuredValue !== null) {
            return $configuredValue;
        }

        $settings = Plugin::getInstance()->getSettings();

        return $settings instanceof Settings
            ? $settings->shouldIncludeDraftsByDefault()
            : false;
    }

    /**
     * Resolve the default revision-inclusion policy for new scans.
     *
     * Config overrides the plugin setting.
     *
     * @return bool
     */
    public function getDefaultIncludeRevisions(): bool
    {
        $configuredValue = $this->getConfiguredIncludeRevisions();
        if ($configuredValue !== null) {
            return $configuredValue;
        }

        $settings = Plugin::getInstance()->getSettings();

        return $settings instanceof Settings
            ? $settings->shouldIncludeRevisionsByDefault()
            : false;
    }

    /**
     * Read progress data for a scan.
     *
     * @param string $scanId
     * @return array|null
     */
    public function getProgress(string $scanId): ?array
    {
        return $this->getStore()->getProgress($scanId);
    }

    /**
     * Read scan metadata.
     *
     * @param string $scanId
     * @return array|null
     */
    public function getMeta(string $scanId): ?array
    {
        return $this->getStore()->getMeta($scanId);
    }

    /**
     * Read final unused asset results for a scan.
     *
     * @param string $scanId
     * @return array|null
     */
    public function getResults(string $scanId): ?array
    {
        return $this->getStore()->getResults($scanId);
    }

    /**
     * Whether the scan has final results.
     *
     * @param string $scanId
     * @return bool
     */
    public function hasResults(string $scanId): bool
    {
        return $this->getStore()->hasResults($scanId);
    }

    /**
     * Read the latest retained completed scan metadata.
     *
     * @return array|null
     */
    public function getLastScan(): ?array
    {
        return $this->getStore()->getLastScan();
    }

    /**
     * Snapshot all assets in scope for the scan into the active scan store.
     *
     * @param string $scanId
     * @return array{totalAssets:int,totalChunks:int}
     * @throws \Throwable
     */
    public function snapshotAssets(string $scanId): array
    {
        $store = $this->getStore();
        $meta = $store->getMeta($scanId);

        if ($meta === null) {
            Logger::warning(
                "Skipping scan setup stage because scan metadata could not be loaded.",
                [
                    "scanId" => $scanId,
                    "storageMode" => $this->getStorageMode(),
                ],
            );

            return [
                "totalAssets" => 0,
                "totalChunks" => 0,
            ];
        }

        $volumeIds = array_map("intval", $meta["volumeIds"] ?? []);
        $chunkSize = max(
            1,
            (int) ($meta["assetChunkSize"] ?? self::DEFAULT_ASSET_CHUNK_SIZE),
        );

        $store->resetAssetSnapshot($scanId);
        $store->replaceUsedIds($scanId, "relations", []);
        $store->replaceUsedIds($scanId, "content", []);
        $store->replaceUsedIds($scanId, "final", []);
        $store->resetResults($scanId);

        $query = Asset::find()
            ->status(null)
            ->orderBy(["elements.id" => SORT_ASC]);

        if (!empty($volumeIds)) {
            $query->volumeId($volumeIds);
        }

        $chunkIndex = 0;
        $chunkCount = 0;
        $currentChunk = [];
        $totalAssets = 0;

        $store->updateProgress($scanId, [
            "status" => self::STATUS_RUNNING,
            "stage" => self::STAGE_SETUP,
            "progress" => 1,
            "processedAssets" => 0,
            "totalAssets" => 0,
            "usedCount" => 0,
            "unusedCount" => 0,
            "error" => null,
        ]);

        foreach ($query->each($chunkSize) as $asset) {
            /** @var Asset $asset */
            $currentChunk[] = $this->buildAssetSnapshotRecord($asset);
            $totalAssets++;

            if (count($currentChunk) >= $chunkSize) {
                $store->storeAssetSnapshotChunk(
                    $scanId,
                    $chunkIndex,
                    $currentChunk,
                );
                $chunkIndex++;
                $chunkCount++;
                $currentChunk = [];
            }
        }

        if (!empty($currentChunk)) {
            $store->storeAssetSnapshotChunk(
                $scanId,
                $chunkIndex,
                $currentChunk,
            );
            $chunkCount++;
        }

        $store->updateMeta($scanId, [
            "status" => self::STATUS_RUNNING,
            "stage" => self::STAGE_SETUP,
            "totalAssets" => $totalAssets,
            "totalChunks" => $chunkCount,
            "processedAssets" => 0,
            "usedCount" => 0,
            "unusedCount" => 0,
            "error" => null,
        ]);

        $progress = $totalAssets > 0 ? 10 : 100;
        $status =
            $totalAssets > 0 ? self::STATUS_RUNNING : self::STATUS_COMPLETE;

        $store->updateProgress($scanId, [
            "status" => $status,
            "stage" =>
                $totalAssets > 0 ? self::STAGE_SETUP : self::STAGE_FINALIZE,
            "progress" => $progress,
            "totalAssets" => $totalAssets,
            "processedAssets" => 0,
            "usedCount" => 0,
            "unusedCount" => 0,
            "error" => null,
        ]);

        if ($totalAssets === 0) {
            $completedAt = time();

            $store->replaceUsedIds($scanId, "relations", []);
            $store->replaceUsedIds($scanId, "content", []);
            $store->replaceUsedIds($scanId, "final", []);
            $store->resetResults($scanId);

            $store->updateMeta($scanId, [
                "status" => self::STATUS_COMPLETE,
                "stage" => self::STAGE_FINALIZE,
                "completedAt" => $completedAt,
                "totalAssets" => 0,
                "processedAssets" => 0,
                "usedCount" => 0,
                "unusedCount" => 0,
                "error" => null,
            ]);

            $store->updateProgress($scanId, [
                "status" => self::STATUS_COMPLETE,
                "stage" => self::STAGE_FINALIZE,
                "progress" => 100,
                "totalAssets" => 0,
                "processedAssets" => 0,
                "usedCount" => 0,
                "unusedCount" => 0,
                "error" => null,
            ]);

            $store->setLastScan($scanId, $completedAt);
        }

        return [
            "totalAssets" => $totalAssets,
            "totalChunks" => $chunkCount,
        ];
    }

    /**
     * Collect used asset IDs from the relations table in one pass over the
     * stored asset snapshot.
     *
     * @param string $scanId
     * @return int
     * @throws \Throwable
     */
    public function collectRelationsUsage(string $scanId): int
    {
        $store = $this->getStore();
        $meta = $store->getMeta($scanId);

        if ($meta === null) {
            Logger::warning(
                "Skipping relations stage because scan metadata could not be loaded.",
                [
                    "scanId" => $scanId,
                    "storageMode" => $this->getStorageMode(),
                ],
            );

            return 0;
        }

        $chunkSize = max(
            1,
            (int) ($meta["assetChunkSize"] ?? self::DEFAULT_ASSET_CHUNK_SIZE),
        );
        $totalChunks = max(1, (int) ($meta["totalChunks"] ?? 1));
        $includeDrafts = !empty($meta["includeDrafts"]);
        $includeRevisions = !empty($meta["includeRevisions"]);
        $countAllRelationsAsUsage = array_key_exists(
            "countAllRelationsAsUsage",
            $meta,
        )
            ? !empty($meta["countAllRelationsAsUsage"])
            : true;
        $initiatingUserId = isset($meta["initiatingUserId"])
            ? (int) $meta["initiatingUserId"]
            : null;

        $usedIds = [];
        $currentAssetIds = [];
        $chunkIndex = 0;

        $store->replaceUsedIds($scanId, "relations", []);

        foreach ($store->iterateAssetSnapshot($scanId) as $row) {
            if (!is_array($row) || !isset($row["id"])) {
                continue;
            }

            $currentAssetIds[] = (int) $row["id"];

            if (count($currentAssetIds) >= $chunkSize) {
                $this->collectRelationChunk(
                    $currentAssetIds,
                    $usedIds,
                    $includeDrafts,
                    $includeRevisions,
                    $countAllRelationsAsUsage,
                    $initiatingUserId,
                );
                $ratio = ++$chunkIndex / $totalChunks;

                $store->updateProgress($scanId, [
                    "status" => self::STATUS_RUNNING,
                    "stage" => self::STAGE_RELATIONS,
                    "progress" => $this->scaleProgress($ratio, 10, 25),
                    "usedCount" => count($usedIds),
                ]);

                $currentAssetIds = [];
            }
        }

        if (!empty($currentAssetIds)) {
            $this->collectRelationChunk(
                $currentAssetIds,
                $usedIds,
                $includeDrafts,
                $includeRevisions,
                $countAllRelationsAsUsage,
                $initiatingUserId,
            );
            $ratio = ++$chunkIndex / $totalChunks;

            $store->updateProgress($scanId, [
                "status" => self::STATUS_RUNNING,
                "stage" => self::STAGE_RELATIONS,
                "progress" => $this->scaleProgress($ratio, 10, 25),
                "usedCount" => count($usedIds),
            ]);
        }

        $uniqueIds = array_map("intval", array_keys($usedIds));
        sort($uniqueIds, SORT_NUMERIC);

        $store->replaceUsedIds($scanId, "relations", $uniqueIds);

        $store->updateMeta($scanId, [
            "status" => self::STATUS_RUNNING,
            "stage" => self::STAGE_RELATIONS,
            "usedCount" => count($uniqueIds),
        ]);

        $store->updateProgress($scanId, [
            "status" => self::STATUS_RUNNING,
            "stage" => self::STAGE_RELATIONS,
            "progress" => 25,
            "usedCount" => count($uniqueIds),
        ]);

        return count($uniqueIds);
    }

    /**
     * Scan relevant entry/global content and resolve asset references against
     * the stored asset snapshot lookup maps.
     *
     * @param string $scanId
     * @return int
     * @throws \Throwable
     */
    public function collectContentUsage(string $scanId): int
    {
        $store = $this->getStore();
        $meta = $store->getMeta($scanId);

        if ($meta === null) {
            Logger::warning(
                "Skipping content stage because scan metadata could not be loaded.",
                [
                    "scanId" => $scanId,
                    "storageMode" => $this->getStorageMode(),
                ],
            );

            return 0;
        }

        $includeDrafts = !empty($meta["includeDrafts"]);
        $includeRevisions = !empty($meta["includeRevisions"]);
        $initiatingUserId = isset($meta["initiatingUserId"])
            ? (int) $meta["initiatingUserId"]
            : null;

        $lookups = $this->buildAssetLookups($scanId);
        $scannedIds = $lookups["scannedIds"];
        $pathLookup = $lookups["pathLookup"];
        $filenameLookup = $lookups["filenameLookup"];

        $usedIds = [];
        $store->replaceUsedIds($scanId, "content", []);

        $htmlFields = $this->getHtmlFields();
        if (empty($htmlFields)) {
            $store->updateMeta($scanId, [
                "status" => self::STATUS_RUNNING,
                "stage" => self::STAGE_CONTENT,
            ]);

            $store->updateProgress($scanId, [
                "status" => self::STATUS_RUNNING,
                "stage" => self::STAGE_CONTENT,
                "progress" => 75,
            ]);

            return 0;
        }

        $fieldIds = array_values(
            array_filter(
                array_map(
                    static fn($field) => (int) ($field->id ?? 0),
                    $htmlFields,
                ),
            ),
        );
        $relevantTypeIds = $this->getEntryTypeIdsWithFields($fieldIds);

        $entryBatchSize = max(
            1,
            (int) ($meta["entryBatchSize"] ?? self::DEFAULT_ENTRY_BATCH_SIZE),
        );
        $totalEntries = $this->getContentScanEntryTotal(
            $relevantTypeIds,
            $includeDrafts,
            $includeRevisions,
            $initiatingUserId,
        );
        $processedEntries = 0;

        $store->updateProgress($scanId, [
            "status" => self::STATUS_RUNNING,
            "stage" => self::STAGE_CONTENT,
            "progress" => 25,
        ]);

        if ($totalEntries > 0) {
            foreach (
                $this->iterateEntriesForContentScan(
                    $relevantTypeIds,
                    $entryBatchSize,
                    $includeDrafts,
                    $includeRevisions,
                    $initiatingUserId,
                )
                as $entry
            ) {
                /** @var Entry $entry */
                if (
                    $processedEntries > 0 &&
                    $processedEntries % 25 === 0 &&
                    !$this->scanExists($scanId)
                ) {
                    Logger::info(
                        "Stopping content scan because the active scan was superseded.",
                        [
                            "scanId" => $scanId,
                            "processedEntries" => $processedEntries,
                        ],
                    );

                    return count($usedIds);
                }

                $resolvedEntry = Plugin::getInstance()->assetUsage->resolveUsageEntry(
                    $entry,
                    $includeDrafts,
                    $includeRevisions,
                    $initiatingUserId,
                );

                if ($resolvedEntry === null) {
                    $processedEntries++;
                    if (
                        $processedEntries % 25 === 0 ||
                        $processedEntries === $totalEntries
                    ) {
                        $ratio =
                            $totalEntries > 0
                                ? $processedEntries / $totalEntries
                                : 1.0;

                        $store->updateProgress($scanId, [
                            "status" => self::STATUS_RUNNING,
                            "stage" => self::STAGE_CONTENT,
                            "progress" => $this->scaleProgress($ratio, 25, 70),
                            "usedCount" => count($usedIds),
                        ]);
                    }
                    continue;
                }

                foreach ($this->getHtmlFieldsForElement($entry) as $field) {
                    try {
                        $fieldValue = $entry->getFieldValue($field->handle);
                    } catch (\Throwable $e) {
                        Logger::warning(
                            "Skipping field during content scan because its value could not be read.",
                            [
                                "scanId" => $scanId,
                                "entryId" => (int) ($entry->id ?? 0),
                                "fieldHandle" => (string) ($field->handle ?? ""),
                                "error" => $e->getMessage(),
                            ],
                        );
                        continue;
                    }

                    $content = $this->normalizeFieldValueToString($fieldValue);
                    if ($content === "") {
                        continue;
                    }

                    foreach (
                        $this->extractReferencedAssetIds(
                            $content,
                            $scannedIds,
                            $pathLookup,
                            $filenameLookup,
                        )
                        as $assetId
                    ) {
                        $usedIds[$assetId] = true;
                    }
                }

                $processedEntries++;
                if (
                    $processedEntries % 25 === 0 ||
                    $processedEntries === $totalEntries
                ) {
                    $ratio =
                        $totalEntries > 0
                            ? $processedEntries / $totalEntries
                            : 1.0;

                    $store->updateProgress($scanId, [
                        "status" => self::STATUS_RUNNING,
                        "stage" => self::STAGE_CONTENT,
                        "progress" => $this->scaleProgress($ratio, 25, 70),
                        "usedCount" => count($usedIds),
                    ]);
                }
            }
        }

        if (!$this->scanExists($scanId)) {
            Logger::info(
                "Stopping content scan before global set scanning because the active scan was superseded.",
                [
                    "scanId" => $scanId,
                    "processedEntries" => $processedEntries,
                ],
            );

            return count($usedIds);
        }

        foreach (GlobalSet::find()->all() as $globalSet) {
            /** @var GlobalSet $globalSet */
            foreach ($this->getHtmlFieldsForElement($globalSet) as $field) {

                try {
                    $fieldValue = $globalSet->getFieldValue($field->handle);
                } catch (\Throwable $e) {
                    Logger::warning(
                        "Skipping global set field during content scan because its value could not be read.",
                        [
                            "scanId" => $scanId,
                            "globalSetId" => (int) ($globalSet->id ?? 0),
                            "fieldHandle" => (string) ($field->handle ?? ""),
                            "error" => $e->getMessage(),
                        ],
                    );
                    continue;
                }

                $content = $this->normalizeFieldValueToString($fieldValue);
                if ($content === "") {
                    continue;
                }

                foreach (
                    $this->extractReferencedAssetIds(
                        $content,
                        $scannedIds,
                        $pathLookup,
                        $filenameLookup,
                    )
                    as $assetId
                ) {
                    $usedIds[$assetId] = true;
                }
            }
        }

        $uniqueIds = array_map("intval", array_keys($usedIds));
        sort($uniqueIds, SORT_NUMERIC);

        $store->replaceUsedIds($scanId, "content", $uniqueIds);

        $store->updateMeta($scanId, [
            "status" => self::STATUS_RUNNING,
            "stage" => self::STAGE_CONTENT,
            "usedCount" => count($uniqueIds),
        ]);

        $store->updateProgress($scanId, [
            "status" => self::STATUS_RUNNING,
            "stage" => self::STAGE_CONTENT,
            "progress" => 75,
            "usedCount" => count($uniqueIds),
        ]);

        return count($uniqueIds);
    }

    /**
     * Merge used IDs, classify the asset snapshot, and build final unused
     * results.
     *
     * @param string $scanId
     * @return array{usedCount:int,unusedCount:int}
     * @throws \Throwable
     */
    public function finalizeScan(string $scanId): array
    {
        $store = $this->getStore();
        $meta = $store->getMeta($scanId);

        if ($meta === null) {
            Logger::warning(
                "Skipping finalize stage because scan metadata could not be loaded.",
                [
                    "scanId" => $scanId,
                    "storageMode" => $this->getStorageMode(),
                ],
            );

            return [
                "usedCount" => 0,
                "unusedCount" => 0,
            ];
        }

        $totalAssets = (int) ($meta["totalAssets"] ?? 0);
        $usedIds = $store->getMergedUsedIds($scanId);
        $usedLookup = array_fill_keys($usedIds, true);

        $store->replaceUsedIds($scanId, "final", $usedIds);
        $store->resetResults($scanId);

        $unusedIds = [];
        $processedAssets = 0;

        foreach ($store->iterateAssetSnapshot($scanId) as $row) {
            if (!is_array($row) || !isset($row["id"])) {
                continue;
            }

            $assetId = (int) $row["id"];
            if (!isset($usedLookup[$assetId])) {
                $unusedIds[] = $assetId;
            }

            $processedAssets++;
            if (
                $processedAssets % 100 === 0 ||
                $processedAssets === $totalAssets
            ) {
                $ratio =
                    $totalAssets > 0 ? $processedAssets / $totalAssets : 1.0;

                $store->updateProgress($scanId, [
                    "status" => self::STATUS_RUNNING,
                    "stage" => self::STAGE_FINALIZE,
                    "progress" => $this->scaleProgress($ratio, 75, 95),
                    "processedAssets" => $processedAssets,
                    "usedCount" => count($usedIds),
                    "unusedCount" => count($unusedIds),
                ]);
            }
        }

        $unusedCount = 0;
        foreach (
            array_chunk($unusedIds, self::DEFAULT_RESULT_QUERY_CHUNK_SIZE)
            as $idChunk
        ) {
            $rows = $this->buildUnusedAssetRows($idChunk);
            $unusedCount += count($rows);
            $store->appendResultRows($scanId, $rows);
        }

        $completedAt = time();

        $store->updateMeta($scanId, [
            "status" => self::STATUS_COMPLETE,
            "stage" => self::STAGE_FINALIZE,
            "completedAt" => $completedAt,
            "processedAssets" => $totalAssets,
            "usedCount" => count($usedIds),
            "unusedCount" => $unusedCount,
            "error" => null,
        ]);

        $store->updateProgress($scanId, [
            "status" => self::STATUS_COMPLETE,
            "stage" => self::STAGE_FINALIZE,
            "progress" => 100,
            "processedAssets" => $totalAssets,
            "usedCount" => count($usedIds),
            "unusedCount" => $unusedCount,
            "error" => null,
        ]);

        $store->setLastScan($scanId, $completedAt);

        return [
            "usedCount" => count($usedIds),
            "unusedCount" => $unusedCount,
        ];
    }

    /**
     * Mark a scan as failed.
     *
     * @param string $scanId
     * @param \Throwable|string $error
     * @return void
     */
    public function failScan(string $scanId, \Throwable|string $error): void
    {
        $message =
            $error instanceof \Throwable
                ? $error->getMessage()
                : (string) $error;
        $this->getStore()->failScan($scanId, $message);
    }

    /**
     * Human-readable label for the current stage.
     *
     * @param string $stage
     * @return string
     */
    public function getStageLabel(string $stage): string
    {
        return match ($stage) {
            self::STAGE_SETUP => Craft::t(
                "asset-cleaner",
                "Preparing asset snapshot...",
            ),
            self::STAGE_RELATIONS => Craft::t(
                "asset-cleaner",
                "Scanning relations...",
            ),
            self::STAGE_CONTENT => Craft::t(
                "asset-cleaner",
                "Scanning content...",
            ),
            self::STAGE_FINALIZE => Craft::t(
                "asset-cleaner",
                "Finalizing results...",
            ),
            default => Craft::t("asset-cleaner", "Scanning..."),
        };
    }

    /**
     * Resolve the active scan store.
     *
     * @return ScanStoreInterface
     */
    private function getStore(): ScanStoreInterface
    {
        $mode = $this->getStorageMode();

        if (!isset($this->stores[$mode])) {
            $this->stores[$mode] = match ($mode) {
                Settings::STORAGE_MODE_DATABASE => new DbScanStore(),
                default => new FileScanStore(),
            };
        }

        return $this->stores[$mode];
    }

    /**
     * Resolve the configured storage mode with config override precedence.
     *
     * @return string
     */
    private function getStorageMode(): string
    {
        $configMode = $this->getConfiguredStorageMode();
        if ($configMode !== null) {
            return $configMode;
        }

        $settings = Plugin::getInstance()->getSettings();
        if ($settings instanceof Settings && $settings->isDatabaseMode()) {
            return Settings::STORAGE_MODE_DATABASE;
        }

        return Settings::STORAGE_MODE_FILE;
    }

    /**
     * @return string|null
     */
    private function getConfiguredStorageMode(): ?string
    {
        $configuredMode = null;

        try {
            $config = Craft::$app
                ->getConfig()
                ->getConfigFromFile("asset-cleaner");
            if (
                is_array($config) &&
                isset($config["scanStorageMode"]) &&
                is_string($config["scanStorageMode"])
            ) {
                $configuredMode = trim(
                    Craft::parseEnv($config["scanStorageMode"]),
                );
            }
        } catch (\Throwable $e) {
            Logger::warning(
                "Could not load Asset Cleaner config while resolving scan storage mode.",
                [
                    "error" => $e->getMessage(),
                ],
            );
        }

        $envMode = getenv("ASSET_CLEANER_SCAN_STORAGE_MODE");
        if (is_string($envMode) && trim($envMode) !== "") {
            $configuredMode = trim($envMode);
        }

        return in_array(
            $configuredMode,
            [Settings::STORAGE_MODE_FILE, Settings::STORAGE_MODE_DATABASE],
            true,
        )
            ? $configuredMode
            : null;
    }

    /**
     * Resolve the configured default draft-inclusion policy, if any.
     *
     * Supported sources:
     * - config/asset-cleaner.php => includeDraftsByDefault
     * - ASSET_CLEANER_INCLUDE_DRAFTS environment variable
     *
     * @return bool|null
     */
    private function getConfiguredIncludeDrafts(): ?bool
    {
        $configuredValue = null;

        try {
            $config = Craft::$app
                ->getConfig()
                ->getConfigFromFile("asset-cleaner");
            if (
                is_array($config) &&
                array_key_exists("includeDraftsByDefault", $config)
            ) {
                $configuredValue = filter_var(
                    Craft::parseEnv((string) $config["includeDraftsByDefault"]),
                    FILTER_VALIDATE_BOOLEAN,
                    FILTER_NULL_ON_FAILURE,
                );
            }
        } catch (\Throwable $e) {
            Logger::warning(
                "Could not load Asset Cleaner config while resolving draft inclusion defaults.",
                [
                    "error" => $e->getMessage(),
                ],
            );
        }

        $envValue = getenv("ASSET_CLEANER_INCLUDE_DRAFTS");
        if (is_string($envValue) && trim($envValue) !== "") {
            $configuredValue = filter_var(
                trim($envValue),
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE,
            );
        }

        return $configuredValue;
    }

    /**
     * Resolve the configured default revision-inclusion policy, if any.
     *
     * Supported sources:
     * - config/asset-cleaner.php => includeRevisionsByDefault
     * - ASSET_CLEANER_INCLUDE_REVISIONS environment variable
     *
     * @return bool|null
     */
    private function getConfiguredIncludeRevisions(): ?bool
    {
        $configuredValue = null;

        try {
            $config = Craft::$app
                ->getConfig()
                ->getConfigFromFile("asset-cleaner");
            if (
                is_array($config) &&
                array_key_exists("includeRevisionsByDefault", $config)
            ) {
                $configuredValue = filter_var(
                    Craft::parseEnv(
                        (string) $config["includeRevisionsByDefault"],
                    ),
                    FILTER_VALIDATE_BOOLEAN,
                    FILTER_NULL_ON_FAILURE,
                );
            }
        } catch (\Throwable $e) {
            Logger::warning(
                "Could not load Asset Cleaner config while resolving revision inclusion defaults.",
                [
                    "error" => $e->getMessage(),
                ],
            );
        }

        $envValue = getenv("ASSET_CLEANER_INCLUDE_REVISIONS");
        if (is_string($envValue) && trim($envValue) !== "") {
            $configuredValue = filter_var(
                trim($envValue),
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE,
            );
        }

        return $configuredValue;
    }

    /**
     * @param Asset $asset
     * @return array
     */
    private function buildAssetSnapshotRecord(Asset $asset): array
    {
        $volumeHandle = null;
        $volumePathPrefixes = [];

        try {
            $volume = $asset->getVolume();
            if ($volume) {
                $volumeHandle = $volume->handle;
                $volumePathPrefixes = $this->buildVolumePathPrefixes(
                    $volume,
                    $volumeHandle,
                );
            }
        } catch (\Throwable $e) {
            Logger::warning(
                "Could not resolve asset volume metadata while building the scan snapshot record.",
                [
                    "assetId" => (int) ($asset->id ?? 0),
                    "filename" => (string) ($asset->filename ?? ""),
                    "error" => $e->getMessage(),
                ],
            );
            $volumeHandle = null;
            $volumePathPrefixes = [];
        }

        return [
            "id" => (int) $asset->id,
            "filename" => (string) $asset->filename,
            "volumeId" => (int) $asset->volumeId,
            "volumeHandle" => $volumeHandle,
            "folderPath" => (string) ($asset->folderPath ?? ""),
            "pathCandidates" => array_values(
                $this->buildAssetPathCandidates(
                    $asset,
                    $volumeHandle,
                    $volumePathPrefixes,
                ),
            ),
        ];
    }

    /**
     * @param Asset $asset
     * @param string|null $volumeHandle
     * @param array $volumePathPrefixes
     * @return array<string,string>
     */
    private function buildAssetPathCandidates(
        Asset $asset,
        ?string $volumeHandle,
        array $volumePathPrefixes = [],
    ): array {
        $candidates = [];

        $filename = trim((string) $asset->filename);
        if ($filename === "") {
            return [];
        }

        $folderPath = trim((string) ($asset->folderPath ?? ""), "/");
        $relativePath =
            $folderPath !== "" ? $folderPath . "/" . $filename : $filename;

        foreach ($this->normalizePathCandidates($relativePath) as $candidate) {
            $candidates[$candidate] = $candidate;
        }

        if ($volumeHandle) {
            $volumeRelative = trim($volumeHandle . "/" . $relativePath, "/");
            foreach (
                $this->normalizePathCandidates($volumeRelative)
                as $candidate
            ) {
                $candidates[$candidate] = $candidate;
            }
        }

        foreach ($volumePathPrefixes as $prefix) {
            $prefixedPath = trim($prefix . "/" . $relativePath, "/");
            foreach (
                $this->normalizePathCandidates($prefixedPath)
                as $candidate
            ) {
                $candidates[$candidate] = $candidate;
            }
        }

        return $candidates;
    }

    /**
     * @param mixed $volume
     * @param string|null $volumeHandle
     * @return array<int,string>
     */
    private function buildVolumePathPrefixes(
        mixed $volume,
        ?string $volumeHandle,
    ): array {
        $prefixes = [];

        if ($volumeHandle) {
            $prefixes[] = $volumeHandle;
        }

        try {
            $fs = $volume?->getFs();
            if ($fs) {
                if (method_exists($fs, "getRootUrl")) {
                    $rootUrl = $fs->getRootUrl();
                    if (is_string($rootUrl) && $rootUrl !== "") {
                        $rootPath = parse_url($rootUrl, PHP_URL_PATH);
                        if (is_string($rootPath) && $rootPath !== "") {
                            $prefixes[] = trim($rootPath, "/");
                        }
                    }
                }

                if (method_exists($fs, "getRootPath")) {
                    $rootPath = $fs->getRootPath();
                    if (is_string($rootPath) && $rootPath !== "") {
                        $prefixes[] = trim(basename($rootPath), "/");
                    }
                }
            }
        } catch (\Throwable $e) {
            Logger::warning(
                "Could not resolve volume filesystem metadata while building scan path prefixes.",
                [
                    "volumeHandle" => $volumeHandle,
                    "error" => $e->getMessage(),
                ],
            );
        }

        return array_values(
            array_unique(
                array_filter($prefixes, static fn($prefix) => $prefix !== ""),
            ),
        );
    }

    /**
     * Build lookup maps from the stored asset snapshot.
     *
     * Path candidates in the stored snapshot are already normalized, so this
     * method keeps a compact map of either:
     * - one unique asset ID for a candidate, or
     * - `0` when the candidate is ambiguous across multiple assets.
     *
     * @param string $scanId
     * @return array{
     *     scannedIds: array<int,bool>,
     *     pathLookup: array<string,int>,
     *     filenameLookup: array<string,int>
     * }
     */
    private function buildAssetLookups(string $scanId): array
    {
        $scannedIds = [];
        $pathLookup = [];
        $filenameLookup = [];

        foreach ($this->getStore()->iterateAssetSnapshot($scanId) as $row) {
            if (!is_array($row) || !isset($row["id"])) {
                continue;
            }

            $assetId = (int) $row["id"];
            if ($assetId <= 0) {
                continue;
            }

            $scannedIds[$assetId] = true;

            $filename = trim((string) ($row["filename"] ?? ""));
            if ($filename !== "") {
                $key = mb_strtolower($filename);
                if (!isset($filenameLookup[$key])) {
                    $filenameLookup[$key] = $assetId;
                } elseif ($filenameLookup[$key] !== $assetId) {
                    $filenameLookup[$key] = 0;
                }
            }

            foreach ((array) ($row["pathCandidates"] ?? []) as $candidate) {
                $variant = trim((string) $candidate);
                if ($variant === "") {
                    continue;
                }

                if (!isset($pathLookup[$variant])) {
                    $pathLookup[$variant] = $assetId;
                } elseif ($pathLookup[$variant] !== $assetId) {
                    $pathLookup[$variant] = 0;
                }
            }
        }

        return [
            "scannedIds" => $scannedIds,
            "pathLookup" => $pathLookup,
            "filenameLookup" => $filenameLookup,
        ];
    }

    /**
     * @return array
     */
    private function getHtmlFields(): array
    {
        $fields = Craft::$app->getFields()->getAllFields();

        return array_values(
            array_filter($fields, fn($field) => $this->isHtmlField($field)),
        );
    }

    /**
     * @param object $element
     * @return array
     */
    private function getHtmlFieldsForElement(object $element): array
    {
        if (!method_exists($element, "getFieldLayout")) {
            return [];
        }

        try {
            $fieldLayout = $element->getFieldLayout();
        } catch (\Throwable $e) {
            Logger::warning(
                "Skipping element during content scan because its field layout could not be resolved.",
                [
                    "elementId" => (int) ($element->id ?? 0),
                    "elementType" => get_class($element),
                    "error" => $e->getMessage(),
                ],
            );

            return [];
        }

        if (!$fieldLayout) {
            return [];
        }

        try {
            $customFields = $fieldLayout->getCustomFields();
        } catch (\Throwable $e) {
            Logger::warning(
                "Skipping element during content scan because its field layout custom fields could not be resolved.",
                [
                    "elementId" => (int) ($element->id ?? 0),
                    "elementType" => get_class($element),
                    "fieldLayoutId" => (int) ($fieldLayout->id ?? 0),
                    "error" => $e->getMessage(),
                ],
            );

            return [];
        }

        return array_values(
            array_filter(
                $customFields,
                fn($field) => $this->isHtmlField($field),
            ),
        );
    }

    /**
     * @param mixed $field
     * @return bool
     */
    private function isHtmlField(mixed $field): bool
    {
        if (!is_object($field)) {
            return false;
        }

        return in_array(
            get_class($field),
            ['craft\\redactor\\Field', "craft\\ckeditor\\Field"],
            true,
        );
    }

    /**
     * @param array $fieldIds
     * @return array
     */
    private function getEntryTypeIdsWithFields(array $fieldIds): array
    {
        if (empty($fieldIds)) {
            return [];
        }

        $relevantLayoutIds = [];

        try {
            $layouts = Craft::$app->getFields()->getAllLayouts();
        } catch (\Throwable $e) {
            Logger::warning(
                "Skipping content scan entry type discovery because field layouts could not be loaded.",
                [
                    "error" => $e->getMessage(),
                ],
            );

            return [];
        }

        foreach ($layouts as $layout) {
            try {
                $customFields = $layout->getCustomFields();
            } catch (\Throwable $e) {
                Logger::warning(
                    "Skipping field layout during content scan entry type discovery because its custom fields could not be resolved.",
                    [
                        "fieldLayoutId" => (int) ($layout->id ?? 0),
                        "error" => $e->getMessage(),
                    ],
                );

                continue;
            }

            foreach ($customFields as $field) {
                if (in_array((int) $field->id, $fieldIds, true)) {
                    $relevantLayoutIds[] = (int) $layout->id;
                    break;
                }
            }
        }

        if (empty($relevantLayoutIds)) {
            return [];
        }

        return array_map(
            "intval",
            (new Query())
                ->select(["id"])
                ->from("{{%entrytypes}}")
                ->where([
                    "fieldLayoutId" => array_values(
                        array_unique($relevantLayoutIds),
                    ),
                ])
                ->column(),
        );
    }

    /**
     * @param mixed $fieldValue
     * @return string
     */
    private function normalizeFieldValueToString(mixed $fieldValue): string
    {
        if ($fieldValue instanceof \craft\redactor\FieldData) {
            $fieldValue = $fieldValue->getRawContent();
        } elseif (
            is_object($fieldValue) &&
            method_exists($fieldValue, "__toString")
        ) {
            $fieldValue = (string) $fieldValue;
        }

        return is_string($fieldValue) ? $fieldValue : "";
    }

    /**
     * Extract referenced asset IDs from one content string.
     *
     * Resolution order:
     * 1. direct IDs (data-asset-id / #asset:)
     * 2. normalized path / URL candidates
     * 3. unique filename fallback
     *
     * Lookup maps store one unique asset ID per candidate, or `0` when the
     * candidate is ambiguous and should not be matched automatically.
     *
     * @param string $content
     * @param array<int,bool> $scannedIds
     * @param array<string,int> $pathLookup
     * @param array<string,int> $filenameLookup
     * @return array<int>
     */
    private function extractReferencedAssetIds(
        string $content,
        array $scannedIds,
        array $pathLookup,
        array $filenameLookup,
    ): array {
        $found = [];

        if (
            preg_match_all(
                '/data-asset-id\s*=\s*["\']?(\d+)["\']?/i',
                $content,
                $matches,
            )
        ) {
            foreach ($matches[1] as $id) {
                $assetId = (int) $id;
                if (isset($scannedIds[$assetId])) {
                    $found[$assetId] = true;
                }
            }
        }

        if (preg_match_all("/#asset:(\d+)/i", $content, $matches)) {
            foreach ($matches[1] as $id) {
                $assetId = (int) $id;
                if (isset($scannedIds[$assetId])) {
                    $found[$assetId] = true;
                }
            }
        }

        $rawReferences = [];

        if (
            preg_match_all(
                '/\b(?:src|href|poster)\s*=\s*["\']([^"\']+)["\']/i',
                $content,
                $matches,
            )
        ) {
            foreach ($matches[1] as $value) {
                $rawReferences[] = $value;
            }
        }

        if (
            preg_match_all(
                '/\bsrcset\s*=\s*["\']([^"\']+)["\']/i',
                $content,
                $matches,
            )
        ) {
            foreach ($matches[1] as $srcset) {
                foreach (preg_split("/\s*,\s*/", $srcset) ?: [] as $candidate) {
                    $parts = preg_split("/\s+/", trim($candidate)) ?: [];
                    if (!empty($parts[0])) {
                        $rawReferences[] = $parts[0];
                    }
                }
            }
        }

        if (
            preg_match_all('/url\((["\']?)([^)"\']+)\1\)/i', $content, $matches)
        ) {
            foreach ($matches[2] as $value) {
                $rawReferences[] = $value;
            }
        }

        foreach ($rawReferences as $reference) {
            $matched = false;

            foreach (
                $this->normalizePathCandidates((string) $reference)
                as $candidate
            ) {
                $assetId = (int) ($pathLookup[$candidate] ?? 0);
                if ($assetId > 0) {
                    $found[$assetId] = true;
                    $matched = true;
                }
            }

            if ($matched) {
                continue;
            }

            $basename = basename(
                (string) parse_url((string) $reference, PHP_URL_PATH),
            );
            $filenameKey = mb_strtolower(trim($basename));
            $assetId = (int) ($filenameLookup[$filenameKey] ?? 0);
            if ($filenameKey !== "" && $assetId > 0) {
                $found[$assetId] = true;
            }
        }

        if (
            preg_match_all(
                "/\b([A-Za-z0-9][A-Za-z0-9._-]*\.(?:jpe?g|png|gif|svg|pdf|webp|mp4|mp3|mov|docx?|xlsx?|pptx?|txt|zip))\b/i",
                $content,
                $matches,
            )
        ) {
            foreach ($matches[1] as $filename) {
                $key = mb_strtolower($filename);
                $assetId = (int) ($filenameLookup[$key] ?? 0);
                if ($assetId > 0) {
                    $found[$assetId] = true;
                }
            }
        }

        return array_map("intval", array_keys($found));
    }

    /**
     * Normalize path candidates for URL/path matching.
     *
     * @param string $path
     * @return array<int,string>
     */
    private function normalizePathCandidates(string $path): array
    {
        $path = html_entity_decode(
            trim($path),
            ENT_QUOTES | ENT_HTML5,
            "UTF-8",
        );
        if ($path === "") {
            return [];
        }

        $parsedPath = parse_url($path, PHP_URL_PATH);
        $path =
            is_string($parsedPath) && $parsedPath !== "" ? $parsedPath : $path;
        $path = rawurldecode($path);
        $path = preg_replace("~/{2,}~", "/", $path) ?? $path;
        $path = trim($path);

        if ($path === "" || $path === "." || $path === "..") {
            return [];
        }

        $variants = [];
        $trimmed = trim($path, "/");
        if ($trimmed !== "") {
            $variants[] = $trimmed;
            $variants[] = "/" . $trimmed;
        }

        if (str_starts_with($path, "/")) {
            $variants[] = $path;
            $variants[] = ltrim($path, "/");
        } else {
            $variants[] = $path;
            $variants[] = "/" . $path;
        }

        return array_values(
            array_unique(
                array_filter($variants, static fn($value) => $value !== ""),
            ),
        );
    }

    /**
     * @param array<int> $assetIds
     * @param array<int,bool> $usedIds
     * @param bool $includeDrafts
     * @param bool $includeRevisions
     * @param bool $countAllRelationsAsUsage
     * @param int|null $initiatingUserId
     * @return void
     */
    private function collectRelationChunk(
        array $assetIds,
        array &$usedIds,
        bool $includeDrafts,
        bool $includeRevisions,
        bool $countAllRelationsAsUsage,
        ?int $initiatingUserId = null,
    ): void {
        if (empty($assetIds)) {
            return;
        }

        $relationIds = Plugin::getInstance()->assetUsage->getResolvedRelationUsageIds(
            $assetIds,
            $includeDrafts,
            $includeRevisions,
            $initiatingUserId,
            $countAllRelationsAsUsage,
        );

        foreach ($relationIds as $relationId) {
            $usedIds[(int) $relationId] = true;
        }
    }

    /**
     * Count the entries that should participate in content scanning without
     * materializing them all at once.
     *
     * @param array<int> $relevantTypeIds
     * @param bool $includeDrafts
     * @param bool $includeRevisions
     * @param int|null $initiatingUserId
     * @return int
     */
    private function getContentScanEntryTotal(
        array $relevantTypeIds,
        bool $includeDrafts,
        bool $includeRevisions,
        ?int $initiatingUserId = null,
    ): int {
        $total = 0;

        foreach (
            $this->getContentScanEntryQueries(
                $relevantTypeIds,
                $includeDrafts,
                $includeRevisions,
                $initiatingUserId,
            )
            as $query
        ) {
            $total += (int) $query->count();
        }

        return $total;
    }

    /**
     * Iterate entries that should participate in content scanning in batches.
     *
     * When draft usage is enabled, this includes canonical entries, saved drafts,
     * and provisional drafts created by the user who started the scan. When
     * revision usage is enabled, this also includes revisions.
     *
     * @param array<int> $relevantTypeIds
     * @param int $batchSize
     * @param bool $includeDrafts
     * @param bool $includeRevisions
     * @param int|null $initiatingUserId
     * @return iterable<Entry>
     */
    private function iterateEntriesForContentScan(
        array $relevantTypeIds,
        int $batchSize,
        bool $includeDrafts,
        bool $includeRevisions,
        ?int $initiatingUserId = null,
    ): iterable {
        if (empty($relevantTypeIds)) {
            return;
        }

        $seenEntryIds = [];
        $batchSize = max(1, $batchSize);

        foreach (
            $this->getContentScanEntryQueries(
                $relevantTypeIds,
                $includeDrafts,
                $includeRevisions,
                $initiatingUserId,
            )
            as $query
        ) {
            foreach ($query->batch($batchSize) as $entryBatch) {
                foreach ($entryBatch as $entry) {
                    if (!$entry instanceof Entry) {
                        continue;
                    }

                    $entryId = (int) ($entry->id ?? 0);
                    if ($entryId <= 0 || isset($seenEntryIds[$entryId])) {
                        continue;
                    }

                    $seenEntryIds[$entryId] = true;
                    yield $entry;
                }
            }
        }
    }

    /**
     * Build the element queries used for batched content scanning.
     *
     * @param array<int> $relevantTypeIds
     * @param bool $includeDrafts
     * @param bool $includeRevisions
     * @param int|null $initiatingUserId
     * @return array<int, \craft\elements\db\EntryQuery>
     */
    private function getContentScanEntryQueries(
        array $relevantTypeIds,
        bool $includeDrafts,
        bool $includeRevisions,
        ?int $initiatingUserId = null,
    ): array {
        if (empty($relevantTypeIds)) {
            return [];
        }

        $queries = [
            Entry::find()
                ->typeId($relevantTypeIds)
                ->status(null)
                ->orderBy(["elements.id" => SORT_ASC]),
        ];

        if ($includeDrafts) {
            $queries[] = Entry::find()
                ->typeId($relevantTypeIds)
                ->drafts()
                ->savedDraftsOnly()
                ->orderBy(["elements.id" => SORT_ASC]);

            if ($initiatingUserId !== null && $initiatingUserId > 0) {
                $queries[] = Entry::find()
                    ->typeId($relevantTypeIds)
                    ->provisionalDrafts()
                    ->draftCreator($initiatingUserId)
                    ->orderBy(["elements.id" => SORT_ASC]);
            }
        }

        if ($includeRevisions) {
            $queries[] = Entry::find()
                ->typeId($relevantTypeIds)
                ->revisions()
                ->orderBy(["elements.id" => SORT_ASC]);
        }

        return $queries;
    }

    /**
     * Build final unused asset result rows for one asset ID chunk.
     *
     * @param array<int> $unusedIds
     * @return array<int,array<string,mixed>>
     */
    private function buildUnusedAssetRows(array $unusedIds): array
    {
        if (empty($unusedIds)) {
            return [];
        }

        $assets = Asset::find()->id($unusedIds)->status(null)->all();

        $rows = [];
        foreach ($assets as $asset) {
            /** @var Asset $asset */
            $rows[] = $this->buildUnusedAssetRow($asset);
        }

        return $rows;
    }

    /**
     * Build a final unused asset result row.
     *
     * @param Asset $asset
     * @return array<string,mixed>
     */
    private function buildUnusedAssetRow(Asset $asset): array
    {
        $path = "";
        $folderPath = "";

        try {
            $folder = $asset->getFolder();
            if ($folder && $folder->path) {
                $folderPath = (string) $folder->path;
            }
        } catch (\Throwable $e) {
            Logger::warning(
                "Could not resolve asset folder metadata while building an unused asset result row.",
                [
                    "assetId" => (int) ($asset->id ?? 0),
                    "filename" => (string) ($asset->filename ?? ""),
                    "error" => $e->getMessage(),
                ],
            );
            $folderPath = "";
        }

        try {
            $volume = $asset->getVolume();
            if ($volume) {
                if (method_exists($volume, "getRootPath")) {
                    $volumePath = $volume->getRootPath();
                    if ($volumePath) {
                        $path = (string) $volumePath;
                        if ($folderPath) {
                            $path =
                                rtrim($path, "/\\") .
                                "/" .
                                ltrim($folderPath, "/\\");
                        }
                    }
                }

                if ($path === "" && !empty($volume->handle)) {
                    $path = "@volumes/" . $volume->handle;
                    if ($folderPath) {
                        $path .= "/" . ltrim($folderPath, "/\\");
                    }
                }
            }
        } catch (\Throwable) {
            $path = "";
        }

        return [
            "id" => (int) $asset->id,
            "title" => (string) $asset->title,
            "filename" => (string) $asset->filename,
            "url" => $asset->getUrl(),
            "cpUrl" => $asset->getCpEditUrl(),
            "volume" => $asset->volume->name ?? "",
            "volumeId" => (int) $asset->volumeId,
            "size" => (int) $asset->size,
            "path" => (string) $path,
            "kind" => (string) $asset->kind,
        ];
    }

    /**
     * @param float $ratio
     * @param int $start
     * @param int $end
     * @return int
     */
    private function scaleProgress(float $ratio, int $start, int $end): int
    {
        $ratio = max(0.0, min(1.0, $ratio));

        return (int) round($start + ($end - $start) * $ratio);
    }
}
