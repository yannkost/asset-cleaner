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
use craft\fields\Assets as AssetsField;
use yann\assetcleaner\helpers\Logger;
use yann\assetcleaner\Plugin;

/**
 * Asset Usage Service
 *
 * Provides methods to check asset usage across the site
 */
class AssetUsageService extends Component
{
    /**
     * Get all entries using an asset
     *
     * @param int $assetId
     * @return array Array of usage data with entry info
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

        // Get asset for URL matching in content
        $asset = Asset::find()->id($assetId)->one();
        if (!$asset) {
            return $usage;
        }

        // 1. Check relations table for Asset field references
        $relationUsage = $this->getRelationUsageRecords(
            $assetId,
            $includeDrafts,
            $includeRevisions,
            $initiatingUserId,
            $countAllRelationsAsUsage,
        );
        $usage["relations"] = $relationUsage["entryRelations"];
        $usage["otherRelations"] = $relationUsage["genericRelations"];

        // 2. Check content tables for Redactor/CKEditor HTML fields
        $contentUsage = $this->findAssetInContent(
            $asset,
            $includeDrafts,
            $includeRevisions,
            $initiatingUserId,
        );
        $usage["content"] = $contentUsage;

        return $usage;
    }

    /**
     * Quick check if asset is used anywhere
     *
     * @param int $assetId
     * @return bool
     */
    public function isAssetUsed(
        int $assetId,
        ?bool $includeDrafts = null,
        ?bool $includeRevisions = null,
        ?int $initiatingUserId = null,
        ?bool $countAllRelationsAsUsage = true,
    ): bool {
        // Check relation usage first. In safe fallback mode, any relation row
        // counts as usage. In strict mode, only resolvable meaningful sources do.
        if (
            $this->hasResolvedRelationUsage(
                $assetId,
                $includeDrafts,
                $includeRevisions,
                $initiatingUserId,
                $countAllRelationsAsUsage,
            )
        ) {
            return true;
        }

        // Check content tables for richtext/CKEditor references
        $asset = Asset::find()->id($assetId)->one();
        if ($asset) {
            $contentUsage = $this->findAssetInContent(
                $asset,
                $includeDrafts,
                $includeRevisions,
                $initiatingUserId,
            );
            if (!empty($contentUsage)) {
                return true;
            }

            // Check global sets for asset references in richtext fields
            $globalUsage = $this->findAssetInGlobals($asset);
            if (!empty($globalUsage)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resolve the user ID whose provisional drafts should be considered.
     *
     * If no explicit initiating user ID is provided, this falls back to the
     * current authenticated control panel user when available.
     *
     * @param int|null $initiatingUserId
     * @return int|null
     */
    private function resolveDraftCreatorUserId(
        ?int $initiatingUserId = null,
    ): ?int {
        if ($initiatingUserId !== null && $initiatingUserId > 0) {
            return $initiatingUserId;
        }

        try {
            $currentUser = Craft::$app->getUser()->getIdentity();
            return $currentUser ? (int) $currentUser->id : null;
        } catch (\Throwable $e) {
            Logger::warning(
                "Could not resolve current user while resolving draft creator usage context.",
                [
                    "error" => $e->getMessage(),
                ],
            );
            return null;
        }
    }

    /**
     * Resolve whether draft usage should count for this check.
     *
     * Config/env overrides the plugin setting default.
     *
     * @param bool|null $includeDrafts
     * @return bool
     */
    private function resolveIncludeDrafts(?bool $includeDrafts): bool
    {
        if ($includeDrafts !== null) {
            return $includeDrafts;
        }

        try {
            $config = Craft::$app
                ->getConfig()
                ->getConfigFromFile("asset-cleaner");
            if (
                is_array($config) &&
                array_key_exists("includeDraftsByDefault", $config)
            ) {
                $configured = filter_var(
                    Craft::parseEnv((string) $config["includeDraftsByDefault"]),
                    FILTER_VALIDATE_BOOLEAN,
                    FILTER_NULL_ON_FAILURE,
                );

                if ($configured !== null) {
                    return $configured;
                }
            }
        } catch (\Throwable $e) {
            Logger::warning(
                "Could not load Asset Cleaner config while resolving draft usage defaults.",
                [
                    "error" => $e->getMessage(),
                ],
            );
        }

        $envValue = getenv("ASSET_CLEANER_INCLUDE_DRAFTS");
        if (is_string($envValue) && trim($envValue) !== "") {
            $configured = filter_var(
                trim($envValue),
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE,
            );
            if ($configured !== null) {
                return $configured;
            }
        }

        $settings = Plugin::getInstance()->getSettings();

        return (bool) ($settings->includeDraftsByDefault ?? false);
    }

    /**
     * Resolve whether revision usage should count for this check.
     *
     * Config/env overrides the plugin setting default.
     *
     * @param bool|null $includeRevisions
     * @return bool
     */
    private function resolveIncludeRevisions(?bool $includeRevisions): bool
    {
        if ($includeRevisions !== null) {
            return $includeRevisions;
        }

        try {
            $config = Craft::$app
                ->getConfig()
                ->getConfigFromFile("asset-cleaner");
            if (
                is_array($config) &&
                array_key_exists("includeRevisionsByDefault", $config)
            ) {
                $configured = filter_var(
                    Craft::parseEnv(
                        (string) $config["includeRevisionsByDefault"],
                    ),
                    FILTER_VALIDATE_BOOLEAN,
                    FILTER_NULL_ON_FAILURE,
                );

                if ($configured !== null) {
                    return $configured;
                }
            }
        } catch (\Throwable $e) {
            Logger::warning(
                "Could not load Asset Cleaner config while resolving revision usage defaults.",
                [
                    "error" => $e->getMessage(),
                ],
            );
        }

        $envValue = getenv("ASSET_CLEANER_INCLUDE_REVISIONS");
        if (is_string($envValue) && trim($envValue) !== "") {
            $configured = filter_var(
                trim($envValue),
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE,
            );
            if ($configured !== null) {
                return $configured;
            }
        }

        $settings = Plugin::getInstance()->getSettings();

        return (bool) ($settings->includeRevisionsByDefault ?? false);
    }

    /**
     * Resolve one entry for usage checks while applying the configured draft
     * and revision policy.
     *
     * This is used both by the asset usage inspector and by batch scan logic so
     * they apply the same editorial-state-aware entry resolution rules.
     *
     * @param Entry $entry
     * @param bool|null $includeDrafts
     * @return Entry|null
     */
    public function resolveUsageEntry(
        Entry $entry,
        ?bool $includeDrafts = null,
        ?bool $includeRevisions = null,
        ?int $initiatingUserId = null,
    ): ?Entry {
        $includeDrafts = $this->resolveIncludeDrafts($includeDrafts);
        $includeRevisions = $this->resolveIncludeRevisions($includeRevisions);

        if (
            !$this->shouldIncludeEntryForUsage(
                $entry,
                $includeDrafts,
                $includeRevisions,
                $initiatingUserId,
            )
        ) {
            return null;
        }

        $resolvedEntry = $this->resolveToTopLevelEntry($entry);
        if (!$resolvedEntry || !$this->hasUsableSection($resolvedEntry)) {
            return null;
        }

        if (
            !$this->shouldIncludeEntryForUsage(
                $resolvedEntry,
                $includeDrafts,
                $includeRevisions,
                $initiatingUserId,
            )
        ) {
            return null;
        }

        return $resolvedEntry;
    }

    /**
     * Determine whether an entry state should count toward asset usage.
     *
     * @param Entry $entry
     * @param bool $includeDrafts
     * @param bool $includeRevisions
     * @param int|null $initiatingUserId
     * @return bool
     */
    private function shouldIncludeEntryForUsage(
        Entry $entry,
        bool $includeDrafts,
        bool $includeRevisions,
        ?int $initiatingUserId = null,
    ): bool {
        if (
            !$includeRevisions &&
            method_exists($entry, "getIsRevision") &&
            $entry->getIsRevision()
        ) {
            return false;
        }

        if (
            method_exists($entry, "getIsProvisionalDraft") &&
            $entry->getIsProvisionalDraft()
        ) {
            if (!$includeDrafts) {
                return false;
            }

            $resolvedDraftCreatorUserId = $this->resolveDraftCreatorUserId(
                $initiatingUserId,
            );
            $entryDraftCreatorUserId = $this->getDraftCreatorUserIdForEntry(
                $entry,
            );

            if (
                $resolvedDraftCreatorUserId !== null &&
                $entryDraftCreatorUserId !== null &&
                $entryDraftCreatorUserId !== $resolvedDraftCreatorUserId
            ) {
                return false;
            }

            return true;
        }

        if (!$includeDrafts) {
            if (method_exists($entry, "getIsDraft") && $entry->getIsDraft()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Resolve the draft creator user ID for an entry when available.
     *
     * @param Entry $entry
     * @return int|null
     */
    private function getDraftCreatorUserIdForEntry(Entry $entry): ?int
    {
        try {
            if (method_exists($entry, "getDraftCreatorId")) {
                $draftCreatorId = $entry->getDraftCreatorId();
                return is_numeric($draftCreatorId)
                    ? (int) $draftCreatorId
                    : null;
            }

            if (
                isset($entry->draftCreatorId) &&
                is_numeric($entry->draftCreatorId)
            ) {
                return (int) $entry->draftCreatorId;
            }
        } catch (\Throwable $e) {
            Logger::warning(
                "Could not resolve draft creator while evaluating entry asset usage.",
                [
                    "entryId" => (int) ($entry->id ?? 0),
                    "error" => $e->getMessage(),
                ],
            );
        }

        return null;
    }

    /**
     * Find asset references in global sets
     *
     * @param Asset $asset
     * @return array
     */
    private function findAssetInGlobals(Asset $asset): array
    {
        $results = [];
        $assetUrl = $asset->getUrl();

        $searchPatterns = [$asset->filename];
        if ($assetUrl) {
            $searchPatterns[] = $assetUrl;
            $parsedUrl = parse_url($assetUrl);
            if (isset($parsedUrl["path"])) {
                $searchPatterns[] = $parsedUrl["path"];
            }
        }

        $globalSets = GlobalSet::find()->all();

        foreach ($globalSets as $globalSet) {
            $fieldLayout = $globalSet->getFieldLayout();
            if (!$fieldLayout) {
                continue;
            }

            foreach ($fieldLayout->getCustomFields() as $field) {
                $fieldClass = get_class($field);
                if (
                    !in_array(
                        $fieldClass,
                        ['craft\\redactor\\Field', "craft\\ckeditor\\Field"],
                        true,
                    )
                ) {
                    continue;
                }

                $fieldValue = $globalSet->getFieldValue($field->handle);

                if ($fieldValue instanceof \craft\redactor\FieldData) {
                    $fieldValue = $fieldValue->getRawContent();
                } elseif (
                    is_object($fieldValue) &&
                    method_exists($fieldValue, "__toString")
                ) {
                    $fieldValue = (string) $fieldValue;
                }

                if ($fieldValue && is_string($fieldValue)) {
                    $found = false;
                    foreach ($searchPatterns as $pattern) {
                        if (str_contains($fieldValue, $pattern)) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $found =
                            str_contains(
                                $fieldValue,
                                'data-asset-id="' . $asset->id . '"',
                            ) ||
                            str_contains($fieldValue, "#asset:" . $asset->id);
                    }
                    if ($found) {
                        $results[] = [
                            "type" => "global",
                            "handle" => $globalSet->handle,
                            "name" => $globalSet->name,
                            "field" => $field->name,
                        ];
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Get IDs of unused assets
     *
     * @param array $volumeIds Optional volume IDs to filter by
     * @return array Array of asset IDs
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
     * Count unused assets
     *
     * @param array $volumeIds Optional volume IDs to filter by
     * @return int
     */
    public function countUnusedAssets(array $volumeIds = []): int
    {
        return count($this->getUnusedAssetIds($volumeIds));
    }

    /**
     * Count used assets
     *
     * @param array $volumeIds Optional volume IDs to filter by
     * @return int
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
     * Get unused assets with full data
     *
     * @param array $volumeIds Optional volume IDs to filter by
     * @param int|null $limit Optional limit
     * @param int $offset Optional offset
     * @return array Array of asset data
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
            $folder = null;
            $folderPath = "";

            // Get folder path
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

            // Get volume and build path
            try {
                $volume = $asset->getVolume();
                if ($volume) {
                    // Try to get the volume's file system root path
                    if (method_exists($volume, "getRootPath")) {
                        $volumePath = $volume->getRootPath();
                        if ($volumePath) {
                            // Use actual file system path
                            $path = $volumePath;
                            if ($folderPath) {
                                $path =
                                    rtrim($path, "/") .
                                    "/" .
                                    ltrim($folderPath, "/");
                            }
                        }
                    }

                    // If no root path, use volume handle format
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
     * Only loads entries whose entry types have CKEditor/Redactor fields,
     * skipping entire sections that don't have rich text.
     *
     * @return array{entries: array<int, string>, globals: array<string, string>}
     */
    public function buildContentIndex(): array
    {
        $htmlFieldTypes = ['craft\\redactor\\Field', "craft\\ckeditor\\Field"];

        // 1. Find all rich text fields
        $allFields = Craft::$app->getFields()->getAllFields();
        $htmlFields = [];
        $htmlFieldIds = [];
        foreach ($allFields as $field) {
            if (in_array(get_class($field), $htmlFieldTypes, true)) {
                $htmlFields[] = $field;
                $htmlFieldIds[] = $field->id;
            }
        }

        if (empty($htmlFields)) {
            return ["entries" => [], "globals" => []];
        }

        // 2. Find entry types whose field layouts contain rich text fields
        $relevantTypeIds = $this->getEntryTypeIdsWithFields($htmlFieldIds);

        // 3. Collect volume base paths/URLs for pre-filtering
        $volumeIndicators = [];
        foreach (Craft::$app->getVolumes()->getAllVolumes() as $volume) {
            try {
                $fs = $volume->getFs();
                if (method_exists($fs, "getRootUrl") && $fs->getRootUrl()) {
                    $parsed = parse_url($fs->getRootUrl());
                    if (isset($parsed["path"])) {
                        $volumeIndicators[] = $parsed["path"];
                    }
                    $volumeIndicators[] = $fs->getRootUrl();
                }
                if (method_exists($fs, "getRootPath") && $fs->getRootPath()) {
                    $volumeIndicators[] = basename($fs->getRootPath());
                }
            } catch (\Throwable $e) {
                Logger::warning(
                    "Skipping volume while building content index because its filesystem metadata could not be read.",
                    [
                        "volumeId" => (int) ($volume->id ?? 0),
                        "volumeHandle" => (string) ($volume->handle ?? ""),
                        "error" => $e->getMessage(),
                    ],
                );
            }
        }
        $volumeIndicators = array_values(
            array_unique(array_filter($volumeIndicators)),
        );

        $fallbackPatterns = [
            ".jpg",
            ".jpeg",
            ".png",
            ".gif",
            ".svg",
            ".pdf",
            ".webp",
            ".mp4",
            ".mp3",
            "data-asset-id",
        ];

        // 4. Load only entries with relevant entry types
        $entryIndex = [];

        $entryQuery = Entry::find()->status(null);
        if (!empty($relevantTypeIds)) {
            $entryQuery->typeId($relevantTypeIds);
        } else {
            // No entry types have rich text fields — skip entries entirely
            return [
                "entries" => [],
                "globals" => $this->buildGlobalsIndex($htmlFieldTypes),
            ];
        }

        $batchSize = 200;
        foreach ($entryQuery->each($batchSize) as $entry) {
            $content = "";
            foreach ($htmlFields as $field) {
                try {
                    $fieldValue = $entry->getFieldValue($field->handle);
                } catch (\Throwable $e) {
                    Logger::warning(
                        "Skipping entry field while building content index because its value could not be read.",
                        [
                            "entryId" => (int) ($entry->id ?? 0),
                            "fieldHandle" => (string) ($field->handle ?? ""),
                            "error" => $e->getMessage(),
                        ],
                    );
                    continue;
                }

                if ($fieldValue instanceof \craft\redactor\FieldData) {
                    $fieldValue = $fieldValue->getRawContent();
                } elseif (
                    is_object($fieldValue) &&
                    method_exists($fieldValue, "__toString")
                ) {
                    $fieldValue = (string) $fieldValue;
                }

                if ($fieldValue && is_string($fieldValue)) {
                    $content .= $fieldValue . "\n";
                }
            }

            if (empty($content)) {
                continue;
            }

            // Pre-filter: does this content contain any asset-like references?
            $hasAssetReference = false;
            if (!empty($volumeIndicators)) {
                foreach ($volumeIndicators as $indicator) {
                    if (str_contains($content, $indicator)) {
                        $hasAssetReference = true;
                        break;
                    }
                }
            }

            if (!$hasAssetReference) {
                foreach ($fallbackPatterns as $pattern) {
                    if (str_contains($content, $pattern)) {
                        $hasAssetReference = true;
                        break;
                    }
                }
            }

            if ($hasAssetReference) {
                $entryIndex[$entry->id] = $content;
            }
        }

        return [
            "entries" => $entryIndex,
            "globals" => $this->buildGlobalsIndex($htmlFieldTypes),
        ];
    }

    /**
     * Get entry type IDs whose field layouts contain any of the given field IDs
     *
     * @param array $fieldIds
     * @return array
     */
    private function getEntryTypeIdsWithFields(array $fieldIds): array
    {
        if (empty($fieldIds)) {
            return [];
        }

        // Get all field layouts and check which ones contain our target fields
        $relevantLayoutIds = [];
        foreach (Craft::$app->getFields()->getAllLayouts() as $layout) {
            foreach ($layout->getCustomFields() as $field) {
                if (in_array($field->id, $fieldIds, true)) {
                    $relevantLayoutIds[] = $layout->id;
                    break;
                }
            }
        }

        if (empty($relevantLayoutIds)) {
            return [];
        }

        // Find entry types that use those field layout IDs
        return (new Query())
            ->select(["id"])
            ->from("{{%entrytypes}}")
            ->where(["fieldLayoutId" => $relevantLayoutIds])
            ->column();
    }

    /**
     * Build globals content index
     *
     * @param array $htmlFieldTypes
     * @return array<string, string>
     */
    private function buildGlobalsIndex(array $htmlFieldTypes): array
    {
        $globalIndex = [];
        $globalSets = GlobalSet::find()->all();

        foreach ($globalSets as $globalSet) {
            $fieldLayout = $globalSet->getFieldLayout();
            if (!$fieldLayout) {
                continue;
            }

            $content = "";
            foreach ($fieldLayout->getCustomFields() as $field) {
                if (!in_array(get_class($field), $htmlFieldTypes, true)) {
                    continue;
                }

                $fieldValue = $globalSet->getFieldValue($field->handle);
                if ($fieldValue instanceof \craft\redactor\FieldData) {
                    $fieldValue = $fieldValue->getRawContent();
                } elseif (
                    is_object($fieldValue) &&
                    method_exists($fieldValue, "__toString")
                ) {
                    $fieldValue = (string) $fieldValue;
                }

                if ($fieldValue && is_string($fieldValue)) {
                    $content .= $fieldValue . "\n";
                }
            }

            if (!empty($content)) {
                $globalIndex[$globalSet->handle] = $content;
            }
        }

        return $globalIndex;
    }

    /**
     * @param int $assetId
     * @param array $contentIndex Output from buildContentIndex()
     * @return bool
     */
    public function isAssetUsedWithIndex(
        int $assetId,
        array $contentIndex,
        ?bool $includeDrafts = null,
        ?bool $includeRevisions = null,
        ?int $initiatingUserId = null,
        ?bool $countAllRelationsAsUsage = true,
    ): bool {
        // 1. Check relation usage first. In safe fallback mode, any relation
        // row counts as usage. In strict mode, only resolvable meaningful
        // sources do.
        if (
            $this->hasResolvedRelationUsage(
                $assetId,
                $includeDrafts,
                $includeRevisions,
                $initiatingUserId,
                $countAllRelationsAsUsage,
            )
        ) {
            return true;
        }

        // 2. Load asset for pattern building
        $asset = Asset::find()->id($assetId)->one();
        if (!$asset) {
            return false;
        }

        $assetUrl = $asset->getUrl();
        $searchPatterns = [$asset->filename];
        if ($assetUrl) {
            $searchPatterns[] = $assetUrl;
            $parsedUrl = parse_url($assetUrl);
            if (isset($parsedUrl["path"])) {
                $searchPatterns[] = $parsedUrl["path"];
            }
        }
        $folderPath = $asset->folderPath ?? "";
        if ($folderPath) {
            $searchPatterns[] = $folderPath . $asset->filename;
        }
        $dataAssetPattern = 'data-asset-id="' . $assetId . '"';
        $hashAssetPattern = "#asset:" . $assetId;

        // 3. Search the pre-built entry content index
        foreach ($contentIndex["entries"] ?? [] as $entryContent) {
            foreach ($searchPatterns as $pattern) {
                if ($pattern && str_contains($entryContent, $pattern)) {
                    return true;
                }
            }
            if (
                str_contains($entryContent, $dataAssetPattern) ||
                str_contains($entryContent, $hashAssetPattern)
            ) {
                return true;
            }
        }

        // 4. Search globals index
        foreach ($contentIndex["globals"] ?? [] as $globalContent) {
            foreach ($searchPatterns as $pattern) {
                if ($pattern && str_contains($globalContent, $pattern)) {
                    return true;
                }
            }
            if (
                str_contains($globalContent, $dataAssetPattern) ||
                str_contains($globalContent, $hashAssetPattern)
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether an asset has any valid relation usage.
     *
     * In fallback mode, any qualifying relation row counts as usage. Entry
     * sources must still honor the configured draft and revision inclusion
     * rules. In strict mode, relation rows must resolve back to a meaningful
     * source context.
     *
     * @param int $assetId
     * @return bool
     */
    private function hasResolvedRelationUsage(
        int $assetId,
        ?bool $includeDrafts = null,
        ?bool $includeRevisions = null,
        ?int $initiatingUserId = null,
        ?bool $countAllRelationsAsUsage = null,
    ): bool {
        return !empty(
            $this->getResolvedRelationUsageIds(
                [$assetId],
                $includeDrafts,
                $includeRevisions,
                $initiatingUserId,
                $countAllRelationsAsUsage,
            )
        );
    }

    /**
     * Resolve relation usage for a batch of asset IDs.
     *
     * In fallback mode, any qualifying relation row counts as usage. Entry
     * sources still honor the configured draft and revision rules, while
     * generic non-entry sources continue to count as usage. In strict mode,
     * relation rows must resolve back to a meaningful source context while
     * honoring the configured draft and revision rules.
     *
     * @param array<int> $assetIds
     * @param bool|null $includeDrafts
     * @return array<int>
     */
    public function getResolvedRelationUsageIds(
        array $assetIds,
        ?bool $includeDrafts = null,
        ?bool $includeRevisions = null,
        ?int $initiatingUserId = null,
        ?bool $countAllRelationsAsUsage = null,
    ): array {
        $assetIds = array_values(
            array_unique(array_filter(array_map("intval", $assetIds))),
        );
        if (empty($assetIds)) {
            return [];
        }

        if ($this->resolveCountAllRelationsAsUsage($countAllRelationsAsUsage)) {
            return $this->getFallbackRelationUsageIds(
                $assetIds,
                $includeDrafts,
                $includeRevisions,
                $initiatingUserId,
            );
        }

        $relations = (new Query())
            ->select(["r.targetId", "r.sourceId"])
            ->from(["r" => Table::RELATIONS])
            ->where(["r.targetId" => $assetIds])
            ->all();

        $usedAssetIds = [];
        $resolvedSourceCache = [];

        foreach ($relations as $relation) {
            $sourceId = (int) ($relation["sourceId"] ?? 0);
            $targetId = (int) ($relation["targetId"] ?? 0);

            if ($sourceId <= 0 || $targetId <= 0) {
                continue;
            }

            if (!array_key_exists($sourceId, $resolvedSourceCache)) {
                $resolvedSourceCache[
                    $sourceId
                ] = $this->resolveRelationSourceEntry(
                    $sourceId,
                    $includeDrafts,
                    $includeRevisions,
                    $initiatingUserId,
                );
            }

            if ($resolvedSourceCache[$sourceId] instanceof Entry) {
                $usedAssetIds[$targetId] = true;
            }
        }

        $result = array_map("intval", array_keys($usedAssetIds));
        sort($result, SORT_NUMERIC);

        return $result;
    }

    /**
     * Build relation usage records for one asset.
     *
     * In fallback mode this also reports generic relational sources that do not
     * resolve to entries, which helps explain why an asset is treated as used.
     *
     * @param int $assetId
     * @return array{entryRelations: array<int, array<string, mixed>>, genericRelations: array<int, array<string, mixed>>}
     */
    private function getRelationUsageRecords(
        int $assetId,
        ?bool $includeDrafts = null,
        ?bool $includeRevisions = null,
        ?int $initiatingUserId = null,
        ?bool $countAllRelationsAsUsage = null,
    ): array {
        $entryRecords = [];
        $genericRecords = [];
        $relations = (new Query())
            ->select(["r.sourceId"])
            ->from(["r" => Table::RELATIONS])
            ->where(["r.targetId" => $assetId])
            ->column();

        foreach (
            $this->getResolvedRelationEntries(
                $assetId,
                $includeDrafts,
                $includeRevisions,
                $initiatingUserId,
            )
            as $entry
        ) {
            $entryRecords["entry-" . $entry->id] = [
                "id" => $entry->id,
                "title" => $entry->title,
                "url" => $entry->getCpEditUrl(),
                "status" => $entry->getStatus(),
                "section" => $this->getSafeSectionName($entry),
            ];
        }

        if ($this->resolveCountAllRelationsAsUsage($countAllRelationsAsUsage)) {
            foreach ($relations as $sourceId) {
                $sourceId = (int) $sourceId;
                if ($sourceId <= 0) {
                    continue;
                }

                $countsAsFallbackUsage = $this->sourceCountsForFallbackRelationUsage(
                    $sourceId,
                    $includeDrafts,
                    $includeRevisions,
                    $initiatingUserId,
                );

                if (!$countsAsFallbackUsage) {
                    Logger::debug(
                        "Skipping fallback relation record because the source does not count as usage under the current draft and revision policy.",
                        [
                            "assetId" => $assetId,
                            "sourceId" => $sourceId,
                            "includeDrafts" => $this->resolveIncludeDrafts(
                                $includeDrafts,
                            ),
                            "includeRevisions" => $this->resolveIncludeRevisions(
                                $includeRevisions,
                            ),
                            "initiatingUserId" => $initiatingUserId,
                        ],
                    );

                    continue;
                }

                $resolvedEntry = $this->resolveRelationSourceEntry(
                    $sourceId,
                    $includeDrafts,
                    $includeRevisions,
                    $initiatingUserId,
                );
                if ($resolvedEntry instanceof Entry) {
                    Logger::debug(
                        "Skipping generic fallback relation record because the source resolves to an entry under the current draft and revision policy.",
                        [
                            "assetId" => $assetId,
                            "sourceId" => $sourceId,
                            "resolvedEntryId" => (int) ($resolvedEntry->id ?? 0),
                            "resolvedEntryCanonicalId" => method_exists(
                                $resolvedEntry,
                                "getCanonicalId",
                            )
                                ? (int) $resolvedEntry->getCanonicalId()
                                : (isset($resolvedEntry->canonicalId)
                                    ? (int) $resolvedEntry->canonicalId
                                    : null),
                            "includeDrafts" => $this->resolveIncludeDrafts(
                                $includeDrafts,
                            ),
                            "includeRevisions" => $this->resolveIncludeRevisions(
                                $includeRevisions,
                            ),
                            "initiatingUserId" => $initiatingUserId,
                        ],
                    );

                    continue;
                }

                $record = $this->getGenericRelationUsageRecord(
                    $sourceId,
                    $initiatingUserId,
                );
                if ($record !== null) {
                    Logger::debug(
                        "Adding generic fallback relation record because the source counts as usage and does not resolve to an entry under the current draft and revision policy.",
                        [
                            "assetId" => $assetId,
                            "sourceId" => $sourceId,
                            "includeDrafts" => $this->resolveIncludeDrafts(
                                $includeDrafts,
                            ),
                            "includeRevisions" => $this->resolveIncludeRevisions(
                                $includeRevisions,
                            ),
                            "initiatingUserId" => $initiatingUserId,
                        ],
                    );

                    $genericRecords["generic-" . $sourceId] = $record;
                } else {
                    Logger::debug(
                        "No generic fallback relation record was created after fallback relation source evaluation.",
                        [
                            "assetId" => $assetId,
                            "sourceId" => $sourceId,
                            "includeDrafts" => $this->resolveIncludeDrafts(
                                $includeDrafts,
                            ),
                            "includeRevisions" => $this->resolveIncludeRevisions(
                                $includeRevisions,
                            ),
                            "initiatingUserId" => $initiatingUserId,
                        ],
                    );
                }
            }
        }

        return [
            "entryRelations" => array_values($entryRecords),
            "genericRelations" => array_values($genericRecords),
        ];
    }

    /**
     * Resolve the relation fallback policy.
     *
     * @param bool|null $countAllRelationsAsUsage
     * @return bool
     */
    private function resolveCountAllRelationsAsUsage(
        ?bool $countAllRelationsAsUsage,
    ): bool {
        return $countAllRelationsAsUsage ?? true;
    }

    /**
     * Get used asset IDs from fallback relation usage while still honoring the
     * draft and revision inclusion rules for entry sources.
     *
     * @param array<int> $assetIds
     * @param bool|null $includeDrafts
     * @param bool|null $includeRevisions
     * @param int|null $initiatingUserId
     * @return array<int>
     */
    private function getFallbackRelationUsageIds(
        array $assetIds,
        ?bool $includeDrafts = null,
        ?bool $includeRevisions = null,
        ?int $initiatingUserId = null,
    ): array {
        $relations = (new Query())
            ->select(["r.targetId", "r.sourceId"])
            ->from(["r" => Table::RELATIONS])
            ->where(["r.targetId" => $assetIds])
            ->all();

        $usedAssetIds = [];
        $sourceUsageCache = [];

        foreach ($relations as $relation) {
            $sourceId = (int) ($relation["sourceId"] ?? 0);
            $targetId = (int) ($relation["targetId"] ?? 0);

            if ($sourceId <= 0 || $targetId <= 0) {
                continue;
            }

            if (!array_key_exists($sourceId, $sourceUsageCache)) {
                $sourceUsageCache[
                    $sourceId
                ] = $this->sourceCountsForFallbackRelationUsage(
                    $sourceId,
                    $includeDrafts,
                    $includeRevisions,
                    $initiatingUserId,
                );
            }

            if ($sourceUsageCache[$sourceId]) {
                $usedAssetIds[$targetId] = true;
            }
        }

        $result = array_map("intval", array_keys($usedAssetIds));
        sort($result, SORT_NUMERIC);

        return $result;
    }

    /**
     * Determine whether a fallback relation source should count as usage.
     *
     * Generic non-entry element sources always count. Entry sources must still
     * honor the draft and revision inclusion rules.
     *
     * @param int $sourceId
     * @param bool|null $includeDrafts
     * @param bool|null $includeRevisions
     * @param int|null $initiatingUserId
     * @return bool
     */
    private function sourceCountsForFallbackRelationUsage(
        int $sourceId,
        ?bool $includeDrafts = null,
        ?bool $includeRevisions = null,
        ?int $initiatingUserId = null,
    ): bool {
        $resolvedIncludeDrafts = $this->resolveIncludeDrafts($includeDrafts);
        $resolvedIncludeRevisions = $this->resolveIncludeRevisions(
            $includeRevisions,
        );

        $elementRow = $this->fetchRelationSourceElementRow($sourceId);
        if (
            is_array($elementRow) &&
            !empty($elementRow["dateDeleted"])
        ) {
            Logger::debug(
                "Excluding fallback relation source because the source element is trashed.",
                [
                    "sourceId" => $sourceId,
                    "dateDeleted" => $elementRow["dateDeleted"],
                    "includeDrafts" => $resolvedIncludeDrafts,
                    "includeRevisions" => $resolvedIncludeRevisions,
                    "initiatingUserId" => $initiatingUserId,
                ],
            );

            return false;
        }

        $entry = $this->resolveRelationSourceEntryIgnoringUsagePolicy(
            $sourceId,
            $initiatingUserId,
        );
        if (!$entry) {
            Logger::debug(
                "Fallback relation source counts as generic usage because it could not be resolved to an entry ancestry.",
                [
                    "sourceId" => $sourceId,
                    "includeDrafts" => $resolvedIncludeDrafts,
                    "includeRevisions" => $resolvedIncludeRevisions,
                    "initiatingUserId" => $initiatingUserId,
                ],
            );

            return true;
        }

        $resolvedUsageEntry = $this->resolveUsageEntry(
            $entry,
            $includeDrafts,
            $includeRevisions,
            $initiatingUserId,
        );

        Logger::debug(
            "Evaluated fallback relation source against draft and revision usage policy.",
            [
                "sourceId" => $sourceId,
                "resolvedEntryId" => (int) ($entry->id ?? 0),
                "resolvedEntryCanonicalId" => method_exists(
                    $entry,
                    "getCanonicalId",
                )
                    ? (int) $entry->getCanonicalId()
                    : (isset($entry->canonicalId)
                        ? (int) $entry->canonicalId
                        : null),
                "resolvedEntryType" => get_class($entry),
                "isDraft" => method_exists($entry, "getIsDraft")
                    ? (bool) $entry->getIsDraft()
                    : null,
                "isProvisionalDraft" => method_exists(
                    $entry,
                    "getIsProvisionalDraft",
                )
                    ? (bool) $entry->getIsProvisionalDraft()
                    : null,
                "isRevision" => method_exists($entry, "getIsRevision")
                    ? (bool) $entry->getIsRevision()
                    : null,
                "includeDrafts" => $resolvedIncludeDrafts,
                "includeRevisions" => $resolvedIncludeRevisions,
                "initiatingUserId" => $initiatingUserId,
                "countsAsUsage" => $resolvedUsageEntry instanceof Entry,
                "usageEntryId" => $resolvedUsageEntry instanceof Entry
                    ? (int) ($resolvedUsageEntry->id ?? 0)
                    : null,
            ],
        );

        return $resolvedUsageEntry instanceof Entry;
    }

    /**
     * Resolve one relation source to an entry regardless of whether draft and
     * revision usage is currently enabled.
     *
     * This is used to determine whether a fallback relation source ultimately
     * belongs to an entry (directly or through owners) before applying the
     * configured draft and revision policy.
     *
     * @param int $sourceId
     * @param int|null $initiatingUserId
     * @return Entry|null
     */
    private function resolveRelationSourceEntryIgnoringUsagePolicy(
        int $sourceId,
        ?int $initiatingUserId = null,
    ): ?Entry {
        $element = $this->findRelationSourceElementById(
            $sourceId,
            $initiatingUserId,
        );
        if ($element !== null) {
            $entry = $this->extractEntryFromRelationSourceElement($element);
            if ($entry instanceof Entry) {
                return $entry;
            }
        }

        return $this->resolveRelationSourceEntryFromRawAncestry($sourceId);
    }

    /**
     * Resolve a relation source to an entry by walking canonical and owner
     * ancestry directly from the database when normal element lookups fail.
     *
     * @param int $sourceId
     * @return Entry|null
     */
    private function resolveRelationSourceEntryFromRawAncestry(
        int $sourceId,
    ): ?Entry {
        $pendingIds = [$sourceId => true];
        $visitedIds = [];
        $depth = 0;
        $maxDepth = 10;

        while (!empty($pendingIds) && $depth < $maxDepth) {
            $nextIds = [];

            foreach (array_keys($pendingIds) as $candidateId) {
                $candidateId = (int) $candidateId;
                if ($candidateId <= 0 || isset($visitedIds[$candidateId])) {
                    continue;
                }

                $visitedIds[$candidateId] = true;

                $entry = $this->findEntryByIdIgnoringUsagePolicy($candidateId);
                if ($entry instanceof Entry) {
                    Logger::debug(
                        "Resolved relation source entry via raw canonical/owner ancestry lookup.",
                        [
                            "sourceId" => $sourceId,
                            "resolvedFromId" => $candidateId,
                            "entryId" => (int) ($entry->id ?? 0),
                            "canonicalId" => method_exists(
                                $entry,
                                "getCanonicalId",
                            )
                                ? (int) $entry->getCanonicalId()
                                : (isset($entry->canonicalId)
                                    ? (int) $entry->canonicalId
                                    : null),
                            "isDraft" => method_exists($entry, "getIsDraft")
                                ? (bool) $entry->getIsDraft()
                                : null,
                            "isProvisionalDraft" => method_exists(
                                $entry,
                                "getIsProvisionalDraft",
                            )
                                ? (bool) $entry->getIsProvisionalDraft()
                                : null,
                            "isRevision" => method_exists(
                                $entry,
                                "getIsRevision",
                            )
                                ? (bool) $entry->getIsRevision()
                                : null,
                            "visitedIds" => array_map(
                                "intval",
                                array_keys($visitedIds),
                            ),
                        ],
                    );

                    return $entry;
                }

                $elementRow = $this->fetchRelationSourceElementRow($candidateId);
                if (is_array($elementRow)) {
                    $canonicalId = (int) ($elementRow["canonicalId"] ?? 0);
                    if (
                        $canonicalId > 0 &&
                        !isset($visitedIds[$canonicalId])
                    ) {
                        $nextIds[$canonicalId] = true;
                    }
                }

                foreach (
                    $this->findRelationSourceOwnerIds($candidateId)
                    as $ownerId
                ) {
                    if ($ownerId > 0 && !isset($visitedIds[$ownerId])) {
                        $nextIds[$ownerId] = true;
                    }
                }
            }

            $pendingIds = $nextIds;
            $depth++;
        }

        Logger::debug(
            "Raw canonical/owner ancestry lookup did not resolve relation source to an entry.",
            [
                "sourceId" => $sourceId,
                "visitedIds" => array_map("intval", array_keys($visitedIds)),
            ],
        );

        return null;
    }

    /**
     * Fetch the raw elements table row for a relation source.
     *
     * @param int $elementId
     * @return array<string, mixed>|null
     */
    private function fetchRelationSourceElementRow(int $elementId): ?array
    {
        $row = (new Query())
            ->select([
                "id",
                "canonicalId",
                "draftId",
                "revisionId",
                "type",
                "dateDeleted",
            ])
            ->from(Table::ELEMENTS)
            ->where(["id" => $elementId])
            ->one();

        return is_array($row) ? $row : null;
    }

    /**
     * Fetch all owner IDs for a relation source element.
     *
     * @param int $elementId
     * @return array<int>
     */
    private function findRelationSourceOwnerIds(int $elementId): array
    {
        $ownerIds = (new Query())
            ->select(["ownerId"])
            ->from(Table::ELEMENTS_OWNERS)
            ->where(["elementId" => $elementId])
            ->column();

        $ownerIds = array_values(
            array_unique(array_filter(array_map("intval", $ownerIds))),
        );
        sort($ownerIds, SORT_NUMERIC);

        return $ownerIds;
    }

    /**
     * Find an entry by element ID regardless of whether draft and revision
     * usage is currently enabled.
     *
     * @param int $sourceId
     * @return Entry|null
     */
    private function findEntryByIdIgnoringUsagePolicy(int $sourceId): ?Entry
    {
        $queryDefinitions = [
            [
                "label" => "direct entry",
                "query" => $this->buildRelationSourceEntryQuery($sourceId),
            ],
            [
                "label" => "saved draft",
                "query" => $this->buildRelationSourceEntryQuery($sourceId)
                    ->drafts()
                    ->savedDraftsOnly(),
            ],
            [
                "label" => "provisional draft",
                "query" => $this->buildRelationSourceEntryQuery($sourceId)
                    ->provisionalDrafts(),
            ],
            [
                "label" => "revision",
                "query" => $this->buildRelationSourceEntryQuery($sourceId)
                    ->revisions(),
            ],
        ];

        foreach ($queryDefinitions as $definition) {
            $entry = $definition["query"]->one();
            if ($entry instanceof Entry) {
                $this->logResolvedRelationSourceEntry(
                    (string) $definition["label"],
                    $entry,
                    $sourceId,
                );

                return $entry;
            }
        }

        Logger::debug(
            "Relation source entry lookup did not resolve to any direct, draft, provisional draft, or revision entry.",
            [
                "sourceId" => $sourceId,
            ],
        );

        return null;
    }

    /**
     * Build an entry query that searches broadly across sites and owner states.
     *
     * @param int $sourceId
     * @return \craft\elements\db\EntryQuery
     */
    private function buildRelationSourceEntryQuery(
        int $sourceId,
    ): \craft\elements\db\EntryQuery {
        return Entry::find()
            ->id($sourceId)
            ->site("*")
            ->status(null)
            ->allowOwnerDrafts(true)
            ->allowOwnerRevisions(true);
    }

    /**
     * Log how a relation source entry was resolved.
     *
     * @param string $lookupType
     * @param Entry $entry
     * @param int $sourceId
     * @return void
     */
    private function logResolvedRelationSourceEntry(
        string $lookupType,
        Entry $entry,
        int $sourceId,
    ): void {
        Logger::debug(
            "Resolved relation source entry via {$lookupType} lookup.",
            [
                "sourceId" => $sourceId,
                "entryId" => (int) ($entry->id ?? 0),
                "canonicalId" => method_exists($entry, "getCanonicalId")
                    ? (int) $entry->getCanonicalId()
                    : (isset($entry->canonicalId)
                        ? (int) $entry->canonicalId
                        : null),
                "isDraft" => method_exists($entry, "getIsDraft")
                    ? (bool) $entry->getIsDraft()
                    : null,
                "isProvisionalDraft" => method_exists(
                    $entry,
                    "getIsProvisionalDraft",
                )
                    ? (bool) $entry->getIsProvisionalDraft()
                    : null,
                "isRevision" => method_exists($entry, "getIsRevision")
                    ? (bool) $entry->getIsRevision()
                    : null,
                "primaryOwnerId" => isset($entry->primaryOwnerId)
                    ? (int) $entry->primaryOwnerId
                    : null,
                "ownerId" => isset($entry->ownerId)
                    ? (int) $entry->ownerId
                    : null,
                "siteId" => isset($entry->siteId)
                    ? (int) $entry->siteId
                    : null,
            ],
        );
    }

    /**
     * Resolve one raw relation source element by ID.
     *
     * @param int $sourceId
     * @param int|null $initiatingUserId
     * @return mixed
     */
    private function findRelationSourceElementById(
        int $sourceId,
        ?int $initiatingUserId = null,
    ): mixed {
        $entry = $this->findEntryByIdIgnoringUsagePolicy($sourceId);
        if ($entry instanceof Entry) {
            Logger::debug(
                "Resolved relation source directly via entry queries before generic element lookup.",
                [
                    "sourceId" => $sourceId,
                    "entryId" => (int) ($entry->id ?? 0),
                    "canonicalId" => method_exists($entry, "getCanonicalId")
                        ? (int) $entry->getCanonicalId()
                        : (isset($entry->canonicalId)
                            ? (int) $entry->canonicalId
                            : null),
                    "isDraft" => method_exists($entry, "getIsDraft")
                        ? (bool) $entry->getIsDraft()
                        : null,
                    "isProvisionalDraft" => method_exists(
                        $entry,
                        "getIsProvisionalDraft",
                    )
                        ? (bool) $entry->getIsProvisionalDraft()
                        : null,
                    "isRevision" => method_exists($entry, "getIsRevision")
                        ? (bool) $entry->getIsRevision()
                        : null,
                    "initiatingUserId" => $initiatingUserId,
                ],
            );

            return $entry;
        }

        try {
            $element = Craft::$app->getElements()->getElementById(
                $sourceId,
                null,
                "*",
                [
                    "allowOwnerDrafts" => true,
                    "allowOwnerRevisions" => true,
                ],
            );
            if ($element !== null) {
                Logger::debug(
                    "Resolved relation source via generic element lookup.",
                    [
                        "sourceId" => $sourceId,
                        "elementId" => (int) ($element->id ?? 0),
                        "elementType" => get_class($element),
                        "initiatingUserId" => $initiatingUserId,
                    ],
                );

                return $element;
            }
        } catch (\Throwable $e) {
            Logger::warning(
                "Could not resolve relation source element by ID.",
                [
                    "sourceId" => $sourceId,
                    "error" => $e->getMessage(),
                ],
            );
        }

        $fallbackEntry = $this->findEntryByIdIgnoringUsagePolicy($sourceId);
        if ($fallbackEntry instanceof Entry) {
            Logger::debug(
                "Resolved relation source via fallback entry lookup after generic element lookup.",
                [
                    "sourceId" => $sourceId,
                    "entryId" => (int) ($fallbackEntry->id ?? 0),
                    "canonicalId" => method_exists(
                        $fallbackEntry,
                        "getCanonicalId",
                    )
                        ? (int) $fallbackEntry->getCanonicalId()
                        : (isset($fallbackEntry->canonicalId)
                            ? (int) $fallbackEntry->canonicalId
                            : null),
                    "isDraft" => method_exists($fallbackEntry, "getIsDraft")
                        ? (bool) $fallbackEntry->getIsDraft()
                        : null,
                    "isProvisionalDraft" => method_exists(
                        $fallbackEntry,
                        "getIsProvisionalDraft",
                    )
                        ? (bool) $fallbackEntry->getIsProvisionalDraft()
                        : null,
                    "isRevision" => method_exists($fallbackEntry, "getIsRevision")
                        ? (bool) $fallbackEntry->getIsRevision()
                        : null,
                    "initiatingUserId" => $initiatingUserId,
                ],
            );
        } else {
            Logger::debug(
                "Relation source could not be resolved by either entry or generic element lookup.",
                [
                    "sourceId" => $sourceId,
                    "initiatingUserId" => $initiatingUserId,
                ],
            );
        }

        return $fallbackEntry;
    }

    /**
     * Resolve an entry from a relation source by traversing owner chains.
     *
     * @param mixed $element
     * @return Entry|null
     */
    private function extractEntryFromRelationSourceElement(mixed $element): ?Entry
    {
        $current = $element;
        $visitedObjectIds = [];
        $depth = 0;
        $maxDepth = 10;

        while ($current !== null && $depth < $maxDepth) {
            if ($current instanceof Entry) {
                $entryId = (int) ($current->id ?? 0);
                if ($entryId > 0) {
                    return $this->findEntryByIdIgnoringUsagePolicy($entryId) ??
                        $current;
                }

                return $current;
            }

            if (!is_object($current)) {
                return null;
            }

            $objectId = spl_object_id($current);
            if (isset($visitedObjectIds[$objectId])) {
                return null;
            }
            $visitedObjectIds[$objectId] = true;

            if (!method_exists($current, "getOwner")) {
                return null;
            }

            try {
                $owner = $current->getOwner();
            } catch (\Throwable $e) {
                Logger::warning(
                    "Could not resolve relation source owner while traversing usage ancestry.",
                    [
                        "elementId" => (int) ($current->id ?? 0),
                        "elementType" => get_class($current),
                        "error" => $e->getMessage(),
                    ],
                );

                return null;
            }

            if ($owner === null || $owner === $current) {
                return null;
            }

            if ($owner instanceof Entry) {
                $ownerId = (int) ($owner->id ?? 0);
                if ($ownerId > 0) {
                    return $this->findEntryByIdIgnoringUsagePolicy($ownerId) ??
                        $owner;
                }

                return $owner;
            }

            $current = $owner;
            $depth++;
        }

        return $current instanceof Entry ? $current : null;
    }

    /**
     * Build a generic relation usage record for non-entry or unresolved sources.
     *
     * @param int $sourceId
     * @param int|null $initiatingUserId
     * @return array<string, mixed>|null
     */
    private function getGenericRelationUsageRecord(
        int $sourceId,
        ?int $initiatingUserId = null,
    ): ?array
    {
        $title = Craft::t("asset-cleaner", "Relational source #{id}", [
            "id" => $sourceId,
        ]);
        $url = "#";
        $status = "live";
        $section = Craft::t("asset-cleaner", "Relational element");

        $elementRow = $this->fetchRelationSourceElementRow($sourceId);
        if (
            is_array($elementRow) &&
            !empty($elementRow["dateDeleted"])
        ) {
            Logger::debug(
                "Skipping generic fallback relation record because the source element is trashed.",
                [
                    "sourceId" => $sourceId,
                    "dateDeleted" => $elementRow["dateDeleted"],
                    "initiatingUserId" => $initiatingUserId,
                ],
            );

            return null;
        }

        try {
            $resolvedRelationEntry =
                $this->resolveRelationSourceEntryIgnoringUsagePolicy(
                    $sourceId,
                    $initiatingUserId,
                );

            if ($resolvedRelationEntry instanceof Entry) {
                Logger::debug(
                    "Skipping generic fallback relation record because the source resolves to an entry ancestry.",
                    [
                        "sourceId" => $sourceId,
                        "resolvedEntryId" => (int) ($resolvedRelationEntry->id ?? 0),
                        "canonicalId" => method_exists(
                            $resolvedRelationEntry,
                            "getCanonicalId",
                        )
                            ? (int) $resolvedRelationEntry->getCanonicalId()
                            : (isset($resolvedRelationEntry->canonicalId)
                                ? (int) $resolvedRelationEntry->canonicalId
                                : null),
                        "isDraft" => method_exists(
                            $resolvedRelationEntry,
                            "getIsDraft",
                        )
                            ? (bool) $resolvedRelationEntry->getIsDraft()
                            : null,
                        "isProvisionalDraft" => method_exists(
                            $resolvedRelationEntry,
                            "getIsProvisionalDraft",
                        )
                            ? (bool) $resolvedRelationEntry->getIsProvisionalDraft()
                            : null,
                        "isRevision" => method_exists(
                            $resolvedRelationEntry,
                            "getIsRevision",
                        )
                            ? (bool) $resolvedRelationEntry->getIsRevision()
                            : null,
                        "initiatingUserId" => $initiatingUserId,
                    ],
                );

                return null;
            }

            $element = $this->findRelationSourceElementById(
                $sourceId,
                $initiatingUserId,
            );

            if ($element instanceof Entry) {
                Logger::debug(
                    "Skipping generic fallback relation record because the source resolves directly to an entry.",
                    [
                        "sourceId" => $sourceId,
                        "elementId" => (int) ($element->id ?? 0),
                        "initiatingUserId" => $initiatingUserId,
                    ],
                );

                return null;
            }

            if ($element) {
                if (method_exists($element, "__toString")) {
                    $stringValue = trim((string) $element);
                    if ($stringValue !== "") {
                        $title = $stringValue;
                    }
                }

                if (method_exists($element, "displayName")) {
                    $section = $element::displayName();
                } else {
                    $section = (new \ReflectionClass($element))->getShortName();
                }

                if (method_exists($element, "getCpEditUrl")) {
                    $cpUrl = $element->getCpEditUrl();
                    if (is_string($cpUrl) && $cpUrl !== "") {
                        $url = $cpUrl;
                    }
                }

                if (method_exists($element, "getStatus")) {
                    $statusValue = $element->getStatus();
                    if (is_string($statusValue) && $statusValue !== "") {
                        $status = $statusValue;
                    }
                }
            }
        } catch (\Throwable $e) {
            Logger::warning(
                "Could not resolve generic relation source element.",
                [
                    "sourceId" => $sourceId,
                    "error" => $e->getMessage(),
                ],
            );
        }

        return [
            "id" => $sourceId,
            "title" => $title,
            "url" => $url,
            "status" => $status,
            "section" => $section,
        ];
    }

    /**
     * Resolve relation sources for an asset to unique top-level entries.
     *
     * @param int $assetId
     * @return array<int, Entry>
     */
    private function getResolvedRelationEntries(
        int $assetId,
        ?bool $includeDrafts = null,
        ?bool $includeRevisions = null,
        ?int $initiatingUserId = null,
    ): array {
        $relations = (new Query())
            ->select(["r.sourceId"])
            ->from(["r" => Table::RELATIONS])
            ->where(["r.targetId" => $assetId])
            ->column();

        $entries = [];
        foreach ($relations as $sourceId) {
            $entry = $this->resolveRelationSourceEntry(
                (int) $sourceId,
                $includeDrafts,
                $includeRevisions,
                $initiatingUserId,
            );
            if ($entry) {
                $entries[$entry->id] = $entry;
            }
        }

        return array_values($entries);
    }

    /**
     * Resolve one relation source ID to a top-level entry when possible.
     *
     * @param int $sourceId
     * @param bool|null $includeDrafts
     * @return Entry|null
     */
    private function resolveRelationSourceEntry(
        int $sourceId,
        ?bool $includeDrafts = null,
        ?bool $includeRevisions = null,
        ?int $initiatingUserId = null,
    ): ?Entry {
        $entry = $this->resolveRelationSourceEntryIgnoringUsagePolicy(
            $sourceId,
            $initiatingUserId,
        );
        if (!$entry) {
            return null;
        }

        return $this->resolveUsageEntry(
            $entry,
            $includeDrafts,
            $includeRevisions,
            $initiatingUserId,
        );
    }



    /**
     * Find an entry by element ID for usage resolution, optionally including
     * saved drafts, provisional drafts, and revisions.
     *
     * @param int $sourceId
     * @param bool|null $includeDrafts
     * @return Entry|null
     */
    private function findEntryByIdForUsage(
        int $sourceId,
        ?bool $includeDrafts = null,
        ?bool $includeRevisions = null,
        ?int $initiatingUserId = null,
    ): ?Entry {
        $entry = Entry::find()->id($sourceId)->status(null)->one();

        if ($entry) {
            return $entry;
        }

        if ($this->resolveIncludeDrafts($includeDrafts)) {
            $entry = Entry::find()
                ->id($sourceId)
                ->drafts()
                ->savedDraftsOnly()
                ->one();

            if ($entry) {
                return $entry;
            }

            $draftCreatorUserId = $this->resolveDraftCreatorUserId(
                $initiatingUserId,
            );
            $query = Entry::find()->id($sourceId)->provisionalDrafts();

            if ($draftCreatorUserId !== null) {
                $query->draftCreator($draftCreatorUserId);
            }

            $entry = $query->one();
            if ($entry) {
                return $entry;
            }
        }

        if ($this->resolveIncludeRevisions($includeRevisions)) {
            $entry = Entry::find()->id($sourceId)->revisions()->one();

            if ($entry) {
                return $entry;
            }
        }

        return null;
    }

    /**
     * Fetch all entries that should be scanned for content usage, optionally
     * including saved drafts and provisional drafts.
     *
     * @param array $relevantTypeIds
     * @param bool|null $includeDrafts
     * @return array<int, Entry>
     */
    private function getEntriesForContentUsage(
        array $relevantTypeIds,
        ?bool $includeDrafts = null,
        ?bool $includeRevisions = null,
        ?int $initiatingUserId = null,
    ): array {
        $entries = Entry::find()->typeId($relevantTypeIds)->status(null)->all();

        $allEntries = [];
        foreach ($entries as $entry) {
            $allEntries[$entry->id] = $entry;
        }

        if ($this->resolveIncludeDrafts($includeDrafts)) {
            foreach (
                Entry::find()
                    ->typeId($relevantTypeIds)
                    ->drafts()
                    ->savedDraftsOnly()
                    ->all()
                as $entry
            ) {
                $allEntries[$entry->id] = $entry;
            }

            $draftCreatorUserId = $this->resolveDraftCreatorUserId(
                $initiatingUserId,
            );
            $provisionalDraftsQuery = Entry::find()
                ->typeId($relevantTypeIds)
                ->provisionalDrafts();

            if ($draftCreatorUserId !== null) {
                $provisionalDraftsQuery->draftCreator($draftCreatorUserId);
            }

            foreach ($provisionalDraftsQuery->all() as $entry) {
                $allEntries[$entry->id] = $entry;
            }
        }

        if ($this->resolveIncludeRevisions($includeRevisions)) {
            foreach (
                Entry::find()->typeId($relevantTypeIds)->revisions()->all()
                as $entry
            ) {
                $allEntries[$entry->id] = $entry;
            }
        }

        return array_values($allEntries);
    }

    /**
     * Find asset references in content fields (Redactor/CKEditor)
     *
     * @param Asset $asset
     * @param bool|null $includeDrafts
     * @return array
     */
    private function findAssetInContent(
        Asset $asset,
        ?bool $includeDrafts = null,
        ?bool $includeRevisions = null,
        ?int $initiatingUserId = null,
    ): array {
        $results = [];
        $assetUrl = $asset->getUrl();

        // Build search patterns - filename is the most reliable
        $searchPatterns = [$asset->filename];

        if ($assetUrl) {
            $searchPatterns[] = $assetUrl;
            // Also check for relative URL path (e.g., /volumes/files/pdfs/file.pdf)
            $parsedUrl = parse_url($assetUrl);
            if (isset($parsedUrl["path"])) {
                $searchPatterns[] = $parsedUrl["path"];
            }
        }

        // Add the folder path + filename pattern
        $folderPath = $asset->folderPath ?? "";
        if ($folderPath) {
            $searchPatterns[] = $folderPath . $asset->filename;
        }

        // Get all text/HTML fields that might contain asset references
        $fields = Craft::$app->getFields()->getAllFields();
        $htmlFieldTypes = ['craft\\redactor\\Field', "craft\\ckeditor\\Field"];

        $htmlFields = [];
        foreach ($fields as $field) {
            if (in_array(get_class($field), $htmlFieldTypes, true)) {
                $htmlFields[] = $field;
            }
        }

        if (empty($htmlFields)) {
            return $results;
        }

        // Only query entries whose entry types have rich text fields
        $htmlFieldIds = array_map(fn($f) => $f->id, $htmlFields);
        $relevantTypeIds = $this->getEntryTypeIdsWithFields($htmlFieldIds);
        if (empty($relevantTypeIds)) {
            return $results;
        }

        $entries = $this->getEntriesForContentUsage(
            $relevantTypeIds,
            $includeDrafts,
            $includeRevisions,
            $initiatingUserId,
        );

        foreach ($entries as $entry) {
            $resolvedEntry = $this->resolveUsageEntry(
                $entry,
                $includeDrafts,
                $includeRevisions,
                $initiatingUserId,
            );
            if (!$resolvedEntry) {
                continue;
            }

            foreach ($htmlFields as $field) {
                try {
                    $fieldValue = $entry->getFieldValue($field->handle);
                } catch (\Throwable $e) {
                    Logger::warning(
                        "Skipping entry field while resolving asset content usage because its value could not be read.",
                        [
                            "assetId" => (int) ($asset->id ?? 0),
                            "entryId" => (int) ($entry->id ?? 0),
                            "fieldHandle" => (string) ($field->handle ?? ""),
                            "error" => $e->getMessage(),
                        ],
                    );
                    continue;
                }

                // Handle different field value types
                if ($fieldValue instanceof \craft\redactor\FieldData) {
                    $fieldValue = $fieldValue->getRawContent();
                } elseif (
                    is_object($fieldValue) &&
                    method_exists($fieldValue, "__toString")
                ) {
                    $fieldValue = (string) $fieldValue;
                }

                if (!$fieldValue || !is_string($fieldValue)) {
                    continue;
                }

                // Check for asset URL, path, data-asset-id attribute, or filename
                $found = false;
                foreach ($searchPatterns as $pattern) {
                    if ($pattern && str_contains($fieldValue, $pattern)) {
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    // Also check for data-asset-id attribute and #asset:ID fragment (CKEditor/Redactor)
                    $found =
                        str_contains(
                            $fieldValue,
                            'data-asset-id="' . $asset->id . '"',
                        ) || str_contains($fieldValue, "#asset:" . $asset->id);
                }

                if ($found) {
                    $results[] = [
                        "id" => $resolvedEntry->id,
                        "title" => $resolvedEntry->title,
                        "url" => $resolvedEntry->getCpEditUrl(),
                        "status" => $resolvedEntry->getStatus(),
                        "section" => $this->getSafeSectionName($resolvedEntry),
                        "field" => $field->name,
                    ];
                    break; // Found in this entry, no need to check other fields
                }
            }
        }

        // Remove duplicates
        $unique = [];
        foreach ($results as $result) {
            $key = $result["id"] . "-" . ($result["field"] ?? "");
            $unique[$key] = $result;
        }

        return array_values($unique);
    }

    /**
     * Resolve a nested entry to its top-level parent entry
     * For entries that don't belong to a section (e.g., Matrix block entries),
     * traverse up the owner chain to find the actual entry
     *
     * @param Entry $entry
     * @return Entry|null The top-level entry or null if not resolvable
     */
    private function resolveToTopLevelEntry(Entry $entry): ?Entry
    {
        // If entry has a section, it's already a top-level entry
        if ($this->hasUsableSection($entry)) {
            return $entry;
        }

        // Traverse up the owner chain to find the top-level entry
        $current = $entry;
        $maxDepth = 10;
        $depth = 0;
        $visitedIds = [$entry->id => true];

        while (
            $current &&
            !$this->hasUsableSection($current) &&
            $depth < $maxDepth
        ) {
            try {
                $owner = $current->getOwner();
                if (
                    $owner instanceof Entry &&
                    !isset($visitedIds[$owner->id])
                ) {
                    $visitedIds[$owner->id] = true;
                    $current = $owner;
                } else {
                    break;
                }
            } catch (\Throwable $e) {
                Logger::warning(
                    "Could not resolve owner while traversing entry usage ancestry.",
                    [
                        "entryId" => (int) ($current->id ?? $entry->id ?? 0),
                        "error" => $e->getMessage(),
                    ],
                );
                break;
            }
            $depth++;
        }

        // Return the resolved entry if it has a section
        return $current && $this->hasUsableSection($current) ? $current : null;
    }

    /**
     * Safely determine whether an entry resolves to a valid section.
     *
     * @param Entry $entry
     * @return bool
     */
    private function hasUsableSection(Entry $entry): bool
    {
        try {
            return $entry->getSection() !== null;
        } catch (\Throwable $e) {
            Logger::warning(
                "Could not resolve section while evaluating entry asset usage.",
                [
                    "entryId" => (int) ($entry->id ?? 0),
                    "error" => $e->getMessage(),
                ],
            );
            return false;
        }
    }

    /**
     * Safely get an entry section name.
     *
     * @param Entry $entry
     * @return string
     */
    private function getSafeSectionName(Entry $entry): string
    {
        try {
            return $entry->getSection()?->name ?? "";
        } catch (\Throwable $e) {
            Logger::warning(
                "Could not resolve section name while building entry asset usage data.",
                [
                    "entryId" => (int) ($entry->id ?? 0),
                    "error" => $e->getMessage(),
                ],
            );
            return "";
        }
    }
}
