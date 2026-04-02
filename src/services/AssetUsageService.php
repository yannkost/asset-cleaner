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
    public function getAssetUsage(int $assetId, ?bool $includeDrafts = null): array
    {
        $usage = [
            'relations' => [],
            'content' => [],
        ];

        // Get asset for URL matching in content
        $asset = Asset::find()->id($assetId)->one();
        if (!$asset) {
            return $usage;
        }

        // 1. Check relations table for Asset field references
        foreach ($this->getResolvedRelationEntries($assetId, $includeDrafts) as $entry) {
            $usage['relations'][] = [
                'id' => $entry->id,
                'title' => $entry->title,
                'url' => $entry->getCpEditUrl(),
                'status' => $entry->getStatus(),
                'section' => $entry->getSection()?->name ?? '',
            ];
        }

        // 2. Check content tables for Redactor/CKEditor HTML fields
        $contentUsage = $this->findAssetInContent($asset, $includeDrafts);
        $usage['content'] = $contentUsage;

        return $usage;
    }

    /**
     * Quick check if asset is used anywhere
     *
     * @param int $assetId
     * @return bool
     */
    public function isAssetUsed(int $assetId, ?bool $includeDrafts = null): bool
    {
        // Check valid relation sources first. This ignores stale derivative
        // relation rows that no longer resolve to a usable top-level entry.
        if ($this->hasResolvedRelationUsage($assetId, $includeDrafts)) {
            return true;
        }

        // Check content tables for richtext/CKEditor references
        $asset = Asset::find()->id($assetId)->one();
        if ($asset) {
            $contentUsage = $this->findAssetInContent($asset, $includeDrafts);
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
            $config = Craft::$app->getConfig()->getConfigFromFile('asset-cleaner');
            if (is_array($config) && array_key_exists('includeDraftsByDefault', $config)) {
                $configured = filter_var(
                    Craft::parseEnv((string)$config['includeDraftsByDefault']),
                    FILTER_VALIDATE_BOOLEAN,
                    FILTER_NULL_ON_FAILURE
                );

                if ($configured !== null) {
                    return $configured;
                }
            }
        } catch (\Throwable $e) {
            Logger::warning('Could not load Asset Cleaner config while resolving draft usage defaults.', [
                'error' => $e->getMessage(),
            ]);
        }

        $envValue = getenv('ASSET_CLEANER_INCLUDE_DRAFTS');
        if (is_string($envValue) && trim($envValue) !== '') {
            $configured = filter_var(trim($envValue), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($configured !== null) {
                return $configured;
            }
        }

        $settings = Plugin::getInstance()->getSettings();

        return (bool)($settings->includeDraftsByDefault ?? false);
    }

    /**
     * Resolve one entry for usage checks while applying the configured draft
     * policy. Revisions are always ignored.
     *
     * This is used both by the asset usage inspector and by batch scan logic so
     * they apply the same draft-aware entry resolution rules.
     *
     * @param Entry $entry
     * @param bool|null $includeDrafts
     * @return Entry|null
     */
    public function resolveUsageEntry(Entry $entry, ?bool $includeDrafts = null): ?Entry
    {
        $includeDrafts = $this->resolveIncludeDrafts($includeDrafts);

        if (!$this->shouldIncludeEntryForUsage($entry, $includeDrafts)) {
            return null;
        }

        $resolvedEntry = $this->resolveToTopLevelEntry($entry);
        if (!$resolvedEntry || !$resolvedEntry->getSection()) {
            return null;
        }

        if (!$this->shouldIncludeEntryForUsage($resolvedEntry, $includeDrafts)) {
            return null;
        }

        return $resolvedEntry;
    }

    /**
     * Determine whether an entry state should count toward asset usage.
     *
     * @param Entry $entry
     * @param bool $includeDrafts
     * @return bool
     */
    private function shouldIncludeEntryForUsage(Entry $entry, bool $includeDrafts): bool
    {
        if (method_exists($entry, 'getIsRevision') && $entry->getIsRevision()) {
            return false;
        }

        if (!$includeDrafts) {
            if (method_exists($entry, 'getIsDraft') && $entry->getIsDraft()) {
                return false;
            }

            if (method_exists($entry, 'getIsProvisionalDraft') && $entry->getIsProvisionalDraft()) {
                return false;
            }
        }

        return true;
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
            if (isset($parsedUrl['path'])) {
                $searchPatterns[] = $parsedUrl['path'];
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
                if (!in_array($fieldClass, ['craft\\redactor\\Field', 'craft\\ckeditor\\Field'], true)) {
                    continue;
                }

                $fieldValue = $globalSet->getFieldValue($field->handle);

                if ($fieldValue instanceof \craft\redactor\FieldData) {
                    $fieldValue = $fieldValue->getRawContent();
                } elseif (is_object($fieldValue) && method_exists($fieldValue, '__toString')) {
                    $fieldValue = (string)$fieldValue;
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
                        $found = str_contains($fieldValue, 'data-asset-id="' . $asset->id . '"')
                            || str_contains($fieldValue, '#asset:' . $asset->id);
                    }
                    if ($found) {
                        $results[] = [
                            'type' => 'global',
                            'handle' => $globalSet->handle,
                            'name' => $globalSet->name,
                            'field' => $field->name,
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
        $query = Asset::find()
            ->status(null);

        if (!empty($volumeIds)) {
            $query->volumeId($volumeIds);
        }

        $allAssets = $query->ids();
        $unusedIds = [];

        foreach ($allAssets as $assetId) {
            if (!$this->isAssetUsed((int)$assetId)) {
                $unusedIds[] = (int)$assetId;
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
        $query = Asset::find()
            ->status(null);

        if (!empty($volumeIds)) {
            $query->volumeId($volumeIds);
        }

        $totalAssets = $query->count();
        $unusedCount = $this->countUnusedAssets($volumeIds);

        return (int)$totalAssets - $unusedCount;
    }

    /**
     * Get unused assets with full data
     *
     * @param array $volumeIds Optional volume IDs to filter by
     * @param int|null $limit Optional limit
     * @param int $offset Optional offset
     * @return array Array of asset data
     */
    public function getUnusedAssets(array $volumeIds = [], ?int $limit = null, int $offset = 0): array
    {
        $unusedIds = $this->getUnusedAssetIds($volumeIds);

        if (empty($unusedIds)) {
            return [];
        }

        $query = Asset::find()
            ->id($unusedIds)
            ->status(null);

        if ($limit !== null) {
            $query->limit($limit)->offset($offset);
        }

        $assets = $query->all();
        $result = [];

        foreach ($assets as $asset) {
            $path = '';
            $folder = null;
            $folderPath = '';

            // Get folder path
            try {
                $folder = $asset->getFolder();
                if ($folder && $folder->path) {
                    $folderPath = $folder->path;
                }
            } catch (\Throwable $e) {
                // Folder might not be accessible
            }

            // Get volume and build path
            try {
                $volume = $asset->getVolume();
                if ($volume) {
                    // Try to get the volume's file system root path
                    if (method_exists($volume, 'getRootPath')) {
                        $volumePath = $volume->getRootPath();
                        if ($volumePath) {
                            // Use actual file system path
                            $path = $volumePath;
                            if ($folderPath) {
                                $path = rtrim($path, '/') . '/' . ltrim($folderPath, '/');
                            }
                        }
                    }

                    // If no root path, use volume handle format
                    if (empty($path) && $volume->handle) {
                        $path = '@volumes/' . $volume->handle;
                        if ($folderPath) {
                            $path .= '/' . ltrim($folderPath, '/');
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Volume might not be accessible
            }

            $result[] = [
                'id' => $asset->id,
                'title' => $asset->title,
                'filename' => $asset->filename,
                'url' => $asset->getUrl(),
                'cpUrl' => $asset->getCpEditUrl(),
                'volume' => $asset->volume->name ?? '',
                'volumeId' => $asset->volumeId,
                'size' => $asset->size,
                'path' => $path,
                'kind' => $asset->kind,
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
        $htmlFieldTypes = [
            'craft\\redactor\\Field',
            'craft\\ckeditor\\Field',
        ];

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
            return ['entries' => [], 'globals' => []];
        }

        // 2. Find entry types whose field layouts contain rich text fields
        $relevantTypeIds = $this->getEntryTypeIdsWithFields($htmlFieldIds);

        // 3. Collect volume base paths/URLs for pre-filtering
        $volumeIndicators = [];
        foreach (Craft::$app->getVolumes()->getAllVolumes() as $volume) {
            try {
                $fs = $volume->getFs();
                if (method_exists($fs, 'getRootUrl') && $fs->getRootUrl()) {
                    $parsed = parse_url($fs->getRootUrl());
                    if (isset($parsed['path'])) {
                        $volumeIndicators[] = $parsed['path'];
                    }
                    $volumeIndicators[] = $fs->getRootUrl();
                }
                if (method_exists($fs, 'getRootPath') && $fs->getRootPath()) {
                    $volumeIndicators[] = basename($fs->getRootPath());
                }
            } catch (\Throwable $e) {
                // skip inaccessible volumes
            }
        }
        $volumeIndicators = array_values(array_unique(array_filter($volumeIndicators)));

        $fallbackPatterns = ['.jpg', '.jpeg', '.png', '.gif', '.svg', '.pdf', '.webp', '.mp4', '.mp3', 'data-asset-id'];

        // 4. Load only entries with relevant entry types
        $entryIndex = [];

        $entryQuery = Entry::find()->status(null);
        if (!empty($relevantTypeIds)) {
            $entryQuery->typeId($relevantTypeIds);
        } else {
            // No entry types have rich text fields — skip entries entirely
            return ['entries' => [], 'globals' => $this->buildGlobalsIndex($htmlFieldTypes)];
        }

        $batchSize = 200;
        foreach ($entryQuery->each($batchSize) as $entry) {
            $content = '';
            foreach ($htmlFields as $field) {
                try {
                    $fieldValue = $entry->getFieldValue($field->handle);
                } catch (\Throwable $e) {
                    continue;
                }

                if ($fieldValue instanceof \craft\redactor\FieldData) {
                    $fieldValue = $fieldValue->getRawContent();
                } elseif (is_object($fieldValue) && method_exists($fieldValue, '__toString')) {
                    $fieldValue = (string)$fieldValue;
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

        return ['entries' => $entryIndex, 'globals' => $this->buildGlobalsIndex($htmlFieldTypes)];
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
            ->select(['id'])
            ->from('{{%entrytypes}}')
            ->where(['fieldLayoutId' => $relevantLayoutIds])
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

            $content = '';
            foreach ($fieldLayout->getCustomFields() as $field) {
                if (!in_array(get_class($field), $htmlFieldTypes, true)) {
                    continue;
                }

                $fieldValue = $globalSet->getFieldValue($field->handle);
                if ($fieldValue instanceof \craft\redactor\FieldData) {
                    $fieldValue = $fieldValue->getRawContent();
                } elseif (is_object($fieldValue) && method_exists($fieldValue, '__toString')) {
                    $fieldValue = (string)$fieldValue;
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
     * Check if an asset is used, using a pre-built content index instead of
     * loading all entries from scratch. Used during batch scanning.
     *
     * @param int $assetId
     * @param array $contentIndex Output from buildContentIndex()
     * @return bool
     */
    public function isAssetUsedWithIndex(int $assetId, array $contentIndex, ?bool $includeDrafts = null): bool
    {
        // 1. Check valid relation sources first. This ignores stale
        // derivative relation rows that no longer resolve to a usable
        // top-level entry.
        if ($this->hasResolvedRelationUsage($assetId, $includeDrafts)) {
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
            if (isset($parsedUrl['path'])) {
                $searchPatterns[] = $parsedUrl['path'];
            }
        }
        $folderPath = $asset->folderPath ?? '';
        if ($folderPath) {
            $searchPatterns[] = $folderPath . $asset->filename;
        }
        $dataAssetPattern = 'data-asset-id="' . $assetId . '"';
        $hashAssetPattern = '#asset:' . $assetId;

        // 3. Search the pre-built entry content index
        foreach ($contentIndex['entries'] ?? [] as $entryContent) {
            foreach ($searchPatterns as $pattern) {
                if ($pattern && str_contains($entryContent, $pattern)) {
                    return true;
                }
            }
            if (str_contains($entryContent, $dataAssetPattern) || str_contains($entryContent, $hashAssetPattern)) {
                return true;
            }
        }

        // 4. Search globals index
        foreach ($contentIndex['globals'] ?? [] as $globalContent) {
            foreach ($searchPatterns as $pattern) {
                if ($pattern && str_contains($globalContent, $pattern)) {
                    return true;
                }
            }
            if (str_contains($globalContent, $dataAssetPattern) || str_contains($globalContent, $hashAssetPattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether an asset has any valid relation usage.
     *
     * This intentionally ignores stale derivative sources such as old nested
     * elements, drafts, or revisions that leave relation rows behind but no
     * longer resolve to a top-level entry.
     *
     * @param int $assetId
     * @return bool
     */
    private function hasResolvedRelationUsage(int $assetId, ?bool $includeDrafts = null): bool
    {
        return !empty($this->getResolvedRelationUsageIds([$assetId], $includeDrafts));
    }

    /**
     * Resolve relation usage for a batch of asset IDs.
     *
     * This returns only asset IDs whose relation rows can be resolved back to
     * a real top-level entry, which makes it suitable for batch scans that
     * should ignore stale derivative relation rows.
     *
     * @param array<int> $assetIds
     * @param bool|null $includeDrafts
     * @return array<int>
     */
    public function getResolvedRelationUsageIds(array $assetIds, ?bool $includeDrafts = null): array
    {
        $assetIds = array_values(array_unique(array_filter(array_map('intval', $assetIds))));
        if (empty($assetIds)) {
            return [];
        }

        $relations = (new Query())
            ->select(['r.targetId', 'r.sourceId'])
            ->from(['r' => Table::RELATIONS])
            ->where(['r.targetId' => $assetIds])
            ->all();

        $usedAssetIds = [];
        $resolvedSourceCache = [];

        foreach ($relations as $relation) {
            $sourceId = (int)($relation['sourceId'] ?? 0);
            $targetId = (int)($relation['targetId'] ?? 0);

            if ($sourceId <= 0 || $targetId <= 0) {
                continue;
            }

            if (!array_key_exists($sourceId, $resolvedSourceCache)) {
                $resolvedSourceCache[$sourceId] = $this->resolveRelationSourceEntry($sourceId, $includeDrafts);
            }

            if ($resolvedSourceCache[$sourceId] instanceof Entry) {
                $usedAssetIds[$targetId] = true;
            }
        }

        $result = array_map('intval', array_keys($usedAssetIds));
        sort($result, SORT_NUMERIC);

        return $result;
    }

    /**
     * Resolve relation sources for an asset to unique top-level entries.
     *
     * @param int $assetId
     * @return array<int, Entry>
     */
    private function getResolvedRelationEntries(int $assetId, ?bool $includeDrafts = null): array
    {
        $relations = (new Query())
            ->select(['r.sourceId'])
            ->from(['r' => Table::RELATIONS])
            ->where(['r.targetId' => $assetId])
            ->column();

        $entries = [];
        foreach ($relations as $sourceId) {
            $entry = $this->resolveRelationSourceEntry((int)$sourceId, $includeDrafts);
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
    private function resolveRelationSourceEntry(int $sourceId, ?bool $includeDrafts = null): ?Entry
    {
        $entry = Entry::find()
            ->id($sourceId)
            ->status(null)
            ->one();

        if (!$entry) {
            return null;
        }

        return $this->resolveUsageEntry($entry, $includeDrafts);
    }

    /**
     * Find asset references in content fields (Redactor/CKEditor)
     *
     * @param Asset $asset
     * @param bool|null $includeDrafts
     * @return array
     */
    private function findAssetInContent(Asset $asset, ?bool $includeDrafts = null): array
    {
        $results = [];
        $assetUrl = $asset->getUrl();

        // Build search patterns - filename is the most reliable
        $searchPatterns = [
            $asset->filename,
        ];

        if ($assetUrl) {
            $searchPatterns[] = $assetUrl;
            // Also check for relative URL path (e.g., /volumes/files/pdfs/file.pdf)
            $parsedUrl = parse_url($assetUrl);
            if (isset($parsedUrl['path'])) {
                $searchPatterns[] = $parsedUrl['path'];
            }
        }

        // Add the folder path + filename pattern
        $folderPath = $asset->folderPath ?? '';
        if ($folderPath) {
            $searchPatterns[] = $folderPath . $asset->filename;
        }

        // Get all text/HTML fields that might contain asset references
        $fields = Craft::$app->getFields()->getAllFields();
        $htmlFieldTypes = [
            'craft\\redactor\\Field',
            'craft\\ckeditor\\Field',
        ];

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

        $entries = Entry::find()
            ->typeId($relevantTypeIds)
            ->status(null)
            ->all();

        foreach ($entries as $entry) {
            $resolvedEntry = $this->resolveUsageEntry($entry, $includeDrafts);
            if (!$resolvedEntry) {
                continue;
            }

            foreach ($htmlFields as $field) {
                try {
                    $fieldValue = $entry->getFieldValue($field->handle);
                } catch (\Throwable $e) {
                    continue;
                }

                // Handle different field value types
                if ($fieldValue instanceof \craft\redactor\FieldData) {
                    $fieldValue = $fieldValue->getRawContent();
                } elseif (is_object($fieldValue) && method_exists($fieldValue, '__toString')) {
                    $fieldValue = (string)$fieldValue;
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
                    $found = str_contains($fieldValue, 'data-asset-id="' . $asset->id . '"')
                        || str_contains($fieldValue, '#asset:' . $asset->id);
                }

                if ($found) {
                    $results[] = [
                        'id' => $resolvedEntry->id,
                        'title' => $resolvedEntry->title,
                        'url' => $resolvedEntry->getCpEditUrl(),
                        'status' => $resolvedEntry->getStatus(),
                        'section' => $resolvedEntry->getSection()?->name ?? '',
                        'field' => $field->name,
                    ];
                    break; // Found in this entry, no need to check other fields
                }
            }
        }

        // Remove duplicates
        $unique = [];
        foreach ($results as $result) {
            $key = $result['id'] . '-' . ($result['field'] ?? '');
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
        if ($entry->getSection()) {
            return $entry;
        }

        // Traverse up the owner chain to find the top-level entry
        $current = $entry;
        $maxDepth = 10;
        $depth = 0;
        $visitedIds = [$entry->id => true];

        while ($current && !$current->getSection() && $depth < $maxDepth) {
            try {
                $owner = $current->getOwner();
                if ($owner instanceof Entry && !isset($visitedIds[$owner->id])) {
                    $visitedIds[$owner->id] = true;
                    $current = $owner;
                } else {
                    break;
                }
            } catch (\Throwable $e) {
                Craft::warning('Error getting owner for entry: ' . $e->getMessage(), __METHOD__);
                break;
            }
            $depth++;
        }

        // Return the resolved entry if it has a section
        return ($current && $current->getSection()) ? $current : null;
    }
}
