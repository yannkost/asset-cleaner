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
    public function getAssetUsage(int $assetId): array
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
        $relations = (new Query())
            ->select(['r.sourceId'])
            ->from(['r' => Table::RELATIONS])
            ->where(['r.targetId' => $assetId])
            ->column();

        $seenIds = [];
        foreach ($relations as $sourceId) {
            $entry = Entry::find()
                ->id($sourceId)
                ->status(null)
                ->one();

            if (!$entry) {
                continue;
            }

            // Resolve to top-level entry if this is a nested entry (e.g., Matrix block)
            $entry = $this->resolveToTopLevelEntry($entry);

            if ($entry && $entry->getSection() && !isset($seenIds[$entry->id])) {
                $seenIds[$entry->id] = true;
                $usage['relations'][] = [
                    'id' => $entry->id,
                    'title' => $entry->title,
                    'url' => $entry->getCpEditUrl(),
                    'status' => $entry->getStatus(),
                    'section' => $entry->getSection()?->name ?? '',
                ];
            }
        }

        // 2. Check content tables for Redactor/CKEditor HTML fields
        $contentUsage = $this->findAssetInContent($asset);
        $usage['content'] = $contentUsage;

        return $usage;
    }

    /**
     * Quick check if asset is used anywhere
     *
     * @param int $assetId
     * @return bool
     */
    public function isAssetUsed(int $assetId): bool
    {
        // Check relations table first (fastest) - this covers Asset fields in entries, globals, categories, etc.
        $hasRelation = (new Query())
            ->from(Table::RELATIONS)
            ->where(['targetId' => $assetId])
            ->exists();

        if ($hasRelation) {
            return true;
        }

        // Check content tables for richtext/CKEditor references
        $asset = Asset::find()->id($assetId)->one();
        if ($asset) {
            $contentUsage = $this->findAssetInContent($asset);
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
                if (!in_array($fieldClass, ['craft\\redactor\\Field', 'craft\\ckeditor\\Field', 'craft\\fields\\PlainText'], true)) {
                    continue;
                }
                
                $fieldValue = $globalSet->getFieldValue($field->handle);
                
                if ($fieldValue instanceof \craft\redactor\FieldData) {
                    $fieldValue = $fieldValue->getRawContent();
                } elseif (is_object($fieldValue) && method_exists($fieldValue, '__toString')) {
                    $fieldValue = (string)$fieldValue;
                }
                
                if ($fieldValue && is_string($fieldValue)) {
                    foreach ($searchPatterns as $pattern) {
                        if (str_contains($fieldValue, $pattern)) {
                            $results[] = [
                                'type' => 'global',
                                'handle' => $globalSet->handle,
                                'name' => $globalSet->name,
                                'field' => $field->name,
                            ];
                            break;
                        }
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
            ];
        }

        return $result;
    }

    /**
     * Build a content index for efficient batch asset scanning.
     *
     * Instead of loading all entries for every asset, this loads entries once,
     * pre-filters to only those with rich text content that might reference assets,
     * and returns a flat [entryId => concatenatedContent] map.
     *
     * @return array{entries: array<int, string>, globals: array<string, string>}
     */
    public function buildContentIndex(): array
    {
        $htmlFieldTypes = [
            'craft\\redactor\\Field',
            'craft\\ckeditor\\Field',
            'craft\\fields\\PlainText',
        ];

        // 1. Find field layouts that have rich text fields
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

        // 2. Collect volume base paths/URLs for pre-filtering
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
        // Deduplicate and remove empty strings
        $volumeIndicators = array_values(array_unique(array_filter($volumeIndicators)));

        // Fallback patterns if no volume paths available
        $fallbackPatterns = ['.jpg', '.jpeg', '.png', '.gif', '.svg', '.pdf', '.webp', '.mp4', '.mp3', 'data-asset-id'];

        // 3. Load all entries and build the index
        $entryIndex = [];

        // Process entries in batches to limit memory usage
        $entryQuery = Entry::find()->status(null);
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

        // 4. Build globals index with same approach
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

        return ['entries' => $entryIndex, 'globals' => $globalIndex];
    }

    /**
     * Check if an asset is used, using a pre-built content index instead of
     * loading all entries from scratch. Used during batch scanning.
     *
     * @param int $assetId
     * @param array $contentIndex Output from buildContentIndex()
     * @return bool
     */
    public function isAssetUsedWithIndex(int $assetId, array $contentIndex): bool
    {
        // 1. Check relations table first (fastest)
        $hasRelation = (new Query())
            ->from(Table::RELATIONS)
            ->where(['targetId' => $assetId])
            ->exists();

        if ($hasRelation) {
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

        // 3. Search the pre-built entry content index
        foreach ($contentIndex['entries'] ?? [] as $entryContent) {
            foreach ($searchPatterns as $pattern) {
                if ($pattern && str_contains($entryContent, $pattern)) {
                    return true;
                }
            }
            if (str_contains($entryContent, $dataAssetPattern)) {
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
            if (str_contains($globalContent, $dataAssetPattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find asset references in content fields (Redactor/CKEditor)
     *
     * @param Asset $asset
     * @return array
     */
    private function findAssetInContent(Asset $asset): array
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
            'craft\\fields\\PlainText',
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

        // Query ALL entries and check their content directly
        // This is more thorough than relying on Craft's search index
        $entries = Entry::find()
            ->status(null)
            ->all();

        foreach ($entries as $entry) {
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
                    // Also check for data-asset-id attribute
                    $found = str_contains($fieldValue, 'data-asset-id="' . $asset->id . '"');
                }
                
                if ($found) {
                    // Resolve to top-level entry if this is a nested entry
                    $resolvedEntry = $this->resolveToTopLevelEntry($entry);
                    if ($resolvedEntry && $resolvedEntry->getSection()) {
                        $results[] = [
                            'id' => $resolvedEntry->id,
                            'title' => $resolvedEntry->title,
                            'url' => $resolvedEntry->getCpEditUrl(),
                            'status' => $resolvedEntry->getStatus(),
                            'section' => $resolvedEntry->getSection()?->name ?? '',
                            'field' => $field->name,
                        ];
                    }
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
