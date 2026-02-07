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
