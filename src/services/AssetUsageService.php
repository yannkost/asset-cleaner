<?php

declare(strict_types=1);

namespace yann\assetcleaner\services;

use craft\base\Component;
use craft\elements\Asset;
use craft\elements\Entry;
use yann\assetcleaner\helpers\Logger;

/**
 * Asset Usage Service
 *
 * Public facade for asset usage checks. This service preserves the existing
 * plugin API while delegating focused responsibilities to smaller internal
 * collaborators.
 */
class AssetUsageService extends Component
{
    private ?EntryUsageResolver $entryUsageResolver = null;
    private ?RelationUsageResolver $relationUsageResolver = null;
    private ?ContentUsageService $contentUsageService = null;

    /**
     * Get all entries using an asset.
     *
     * @return array{relations: array<int, array<string, mixed>>, otherRelations: array<int, array<string, mixed>>, content: array<int, array<string, mixed>>}
     */
    public function getAssetUsage(
        int $assetId,
        ?bool $includeDrafts = null,
        ?bool $includeRevisions = null,
        ?int $initiatingUserId = null,
        ?bool $countAllRelationsAsUsage = true,
    ): array {
        $usage = [
            "relations" => [],
            "otherRelations" => [],
            "content" => [],
        ];

        $asset = Asset::find()->id($assetId)->one();
        if (!$asset) {
            return $usage;
        }

        $relationUsage = $this->getRelationUsageResolver()->getRelationUsageRecords(
            $assetId,
            $includeDrafts,
            $includeRevisions,
            $initiatingUserId,
            $countAllRelationsAsUsage,
        );

        $usage["relations"] = $relationUsage["entryRelations"];
        $usage["otherRelations"] = $relationUsage["genericRelations"];
        $usage["content"] = $this->getContentUsageService()->findAssetInContent(
            $asset,
            $includeDrafts,
            $includeRevisions,
            $initiatingUserId,
        );

        return $usage;
    }

    /**
     * Quick check if asset is used anywhere.
     */
    public function isAssetUsed(
        int $assetId,
        ?bool $includeDrafts = null,
        ?bool $includeRevisions = null,
        ?int $initiatingUserId = null,
        ?bool $countAllRelationsAsUsage = true,
    ): bool {
        if (
            $this->getRelationUsageResolver()->hasResolvedRelationUsage(
                $assetId,
                $includeDrafts,
                $includeRevisions,
                $initiatingUserId,
                $countAllRelationsAsUsage,
            )
        ) {
            return true;
        }

        $asset = Asset::find()->id($assetId)->one();
        if (!$asset) {
            return false;
        }

        $contentUsage = $this->getContentUsageService()->findAssetInContent(
            $asset,
            $includeDrafts,
            $includeRevisions,
            $initiatingUserId,
        );
        if (!empty($contentUsage)) {
            return true;
        }

        $globalUsage = $this->getContentUsageService()->findAssetInGlobals(
            $asset,
        );

        return !empty($globalUsage);
    }

    /**
     * Resolve one entry for usage checks while applying the configured draft
     * and revision policy.
     */
    public function resolveUsageEntry(
        Entry $entry,
        ?bool $includeDrafts = null,
        ?bool $includeRevisions = null,
        ?int $initiatingUserId = null,
    ): ?Entry {
        return $this->getEntryUsageResolver()->resolveUsageEntry(
            $entry,
            $includeDrafts,
            $includeRevisions,
            $initiatingUserId,
        );
    }

    /**
     * Resolve relation usage for a batch of asset IDs.
     *
     * @param array<int> $assetIds
     * @return array<int>
     */
    public function getResolvedRelationUsageIds(
        array $assetIds,
        ?bool $includeDrafts = null,
        ?bool $includeRevisions = null,
        ?int $initiatingUserId = null,
        ?bool $countAllRelationsAsUsage = null,
    ): array {
        return $this->getRelationUsageResolver()->getResolvedRelationUsageIds(
            $assetIds,
            $includeDrafts,
            $includeRevisions,
            $initiatingUserId,
            $countAllRelationsAsUsage,
        );
    }

    /**
     * Get IDs of unused assets.
     *
     * @param array<int> $volumeIds
     * @return array<int>
     */
    public function getUnusedAssetIds(array $volumeIds = []): array
    {
        $query = Asset::find()->status(null);

        if (!empty($volumeIds)) {
            $query->volumeId($volumeIds);
        }

        $allAssets = $query->ids();
        $unusedIds = [];

        foreach ($allAssets as $assetId) {
            if (!$this->isAssetUsed((int) $assetId)) {
                $unusedIds[] = (int) $assetId;
            }
        }

        return $unusedIds;
    }

    /**
     * Count unused assets.
     *
     * @param array<int> $volumeIds
     */
    public function countUnusedAssets(array $volumeIds = []): int
    {
        return count($this->getUnusedAssetIds($volumeIds));
    }

    /**
     * Count used assets.
     *
     * @param array<int> $volumeIds
     */
    public function countUsedAssets(array $volumeIds = []): int
    {
        $query = Asset::find()->status(null);

        if (!empty($volumeIds)) {
            $query->volumeId($volumeIds);
        }

        $totalAssets = $query->count();
        $unusedCount = $this->countUnusedAssets($volumeIds);

        return (int) $totalAssets - $unusedCount;
    }

    /**
     * Get unused assets with full data.
     *
     * @param array<int> $volumeIds
     * @return array<int, array<string, mixed>>
     */
    public function getUnusedAssets(
        array $volumeIds = [],
        ?int $limit = null,
        int $offset = 0,
    ): array {
        $unusedIds = $this->getUnusedAssetIds($volumeIds);

        if (empty($unusedIds)) {
            return [];
        }

        $query = Asset::find()->id($unusedIds)->status(null);

        if ($limit !== null) {
            $query->limit($limit)->offset($offset);
        }

        $assets = $query->all();
        $result = [];

        foreach ($assets as $asset) {
            $path = "";
            $folderPath = "";

            try {
                $folder = $asset->getFolder();
                if ($folder && $folder->path) {
                    $folderPath = $folder->path;
                }
            } catch (\Throwable $e) {
                Logger::warning(
                    "Could not resolve asset folder while building unused asset data.",
                    [
                        "assetId" => (int) ($asset->id ?? 0),
                        "filename" => (string) ($asset->filename ?? ""),
                        "error" => $e->getMessage(),
                    ],
                );
            }

            try {
                $volume = $asset->getVolume();
                if ($volume) {
                    if (method_exists($volume, "getRootPath")) {
                        $volumePath = $volume->getRootPath();
                        if ($volumePath) {
                            $path = $volumePath;
                            if ($folderPath) {
                                $path =
                                    rtrim($path, "/") .
                                    "/" .
                                    ltrim($folderPath, "/");
                            }
                        }
                    }

                    if (empty($path) && $volume->handle) {
                        $path = "@volumes/" . $volume->handle;
                        if ($folderPath) {
                            $path .= "/" . ltrim($folderPath, "/");
                        }
                    }
                }
            } catch (\Throwable $e) {
                Logger::warning(
                    "Could not resolve asset volume while building unused asset data.",
                    [
                        "assetId" => (int) ($asset->id ?? 0),
                        "filename" => (string) ($asset->filename ?? ""),
                        "volumeId" => (int) ($asset->volumeId ?? 0),
                        "error" => $e->getMessage(),
                    ],
                );
            }

            $result[] = [
                "id" => $asset->id,
                "title" => $asset->title,
                "filename" => $asset->filename,
                "url" => $asset->getUrl(),
                "cpUrl" => $asset->getCpEditUrl(),
                "volume" => $asset->volume->name ?? "",
                "volumeId" => $asset->volumeId,
                "size" => $asset->size,
                "path" => $path,
                "kind" => $asset->kind,
            ];
        }

        return $result;
    }

    /**
     * Build a content index for efficient batch asset scanning.
     *
     * @return array{entries: array<int, string>, globals: array<string, string>}
     */
    public function buildContentIndex(): array
    {
        return $this->getContentUsageService()->buildContentIndex();
    }

    /**
     * Check whether an asset is used with a pre-built content index.
     *
     * @param array{entries?: array<int, string>, globals?: array<string, string>} $contentIndex
     */
    public function isAssetUsedWithIndex(
        int $assetId,
        array $contentIndex,
        ?bool $includeDrafts = null,
        ?bool $includeRevisions = null,
        ?int $initiatingUserId = null,
        ?bool $countAllRelationsAsUsage = true,
    ): bool {
        if (
            $this->getRelationUsageResolver()->hasResolvedRelationUsage(
                $assetId,
                $includeDrafts,
                $includeRevisions,
                $initiatingUserId,
                $countAllRelationsAsUsage,
            )
        ) {
            return true;
        }

        return $this->getContentUsageService()->isAssetUsedWithIndex(
            $assetId,
            $contentIndex,
        );
    }

    private function getEntryUsageResolver(): EntryUsageResolver
    {
        if ($this->entryUsageResolver === null) {
            $this->entryUsageResolver = new EntryUsageResolver();
        }

        return $this->entryUsageResolver;
    }

    private function getRelationUsageResolver(): RelationUsageResolver
    {
        if ($this->relationUsageResolver === null) {
            $this->relationUsageResolver = new RelationUsageResolver(
                $this->getEntryUsageResolver(),
            );
        }

        return $this->relationUsageResolver;
    }

    private function getContentUsageService(): ContentUsageService
    {
        if ($this->contentUsageService === null) {
            $this->contentUsageService = new ContentUsageService(
                $this->getEntryUsageResolver(),
            );
        }

        return $this->contentUsageService;
    }
}