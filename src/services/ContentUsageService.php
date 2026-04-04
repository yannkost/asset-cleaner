<?php

declare(strict_types=1);

namespace yann\assetcleaner\services;

use Craft;
use craft\base\Component;
use craft\db\Query;
use craft\elements\Asset;
use craft\elements\Entry;
use craft\elements\GlobalSet;
use yann\assetcleaner\helpers\Logger;

/**
 * Content Usage Service
 *
 * Resolves asset usage from HTML-capable content fields on entries and globals.
 * This service intentionally limits field access to the concrete element field
 * layout context so it does not call getFieldValue() for invalid field handles.
 */
class ContentUsageService extends Component
{
    private ?EntryUsageResolver $entryUsageResolver = null;

    public function __construct(
        ?EntryUsageResolver $entryUsageResolver = null,
        array $config = [],
    ) {
        parent::__construct($config);
        $this->entryUsageResolver = $entryUsageResolver;
    }

    /**
     * Find asset references in entry content fields.
     *
     * @return array<int, array<string, mixed>>
     */
    public function findAssetInContent(
        Asset $asset,
        ?bool $includeDrafts = null,
        ?bool $includeRevisions = null,
        ?int $initiatingUserId = null,
    ): array {
        $results = [];
        $searchPatterns = $this->buildAssetSearchPatterns($asset);

        $htmlFields = $this->getAllHtmlFields();
        if (empty($htmlFields)) {
            return $results;
        }

        $htmlFieldIds = [];
        foreach ($htmlFields as $field) {
            $fieldId = (int) ($field->id ?? 0);
            if ($fieldId > 0) {
                $htmlFieldIds[] = $fieldId;
            }
        }

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
            $resolvedEntry = $this->getEntryUsageResolver()->resolveUsageEntry(
                $entry,
                $includeDrafts,
                $includeRevisions,
                $initiatingUserId,
            );

            if (!$resolvedEntry) {
                continue;
            }

            foreach ($this->getHtmlFieldsForElement($entry) as $field) {
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

                $content = $this->normalizeFieldValueToString($fieldValue);
                if ($content === null) {
                    continue;
                }

                if (!$this->contentContainsAssetReference(
                    $content,
                    $searchPatterns,
                    (int) $asset->id,
                )) {
                    continue;
                }

                $results[] = [
                    "id" => $resolvedEntry->id,
                    "title" => $resolvedEntry->title,
                    "url" => $resolvedEntry->getCpEditUrl(),
                    "status" => $resolvedEntry->getStatus(),
                    "section" => $this->getEntryUsageResolver()->getSafeSectionName(
                        $resolvedEntry,
                    ),
                    "field" => (string) ($field->name ?? $field->handle ?? ""),
                ];

                break;
            }
        }

        $unique = [];
        foreach ($results as $result) {
            $key = (string) ($result["id"] ?? "") . "-" . (string) ($result["field"] ?? "");
            $unique[$key] = $result;
        }

        return array_values($unique);
    }

    /**
     * Find asset references in global sets.
     *
     * @return array<int, array<string, mixed>>
     */
    public function findAssetInGlobals(Asset $asset): array
    {
        $results = [];
        $searchPatterns = $this->buildAssetSearchPatterns($asset);

        foreach (GlobalSet::find()->all() as $globalSet) {
            foreach ($this->getHtmlFieldsForElement($globalSet) as $field) {
                try {
                    $fieldValue = $globalSet->getFieldValue($field->handle);
                } catch (\Throwable $e) {
                    Logger::warning(
                        "Skipping global field while resolving asset content usage because its value could not be read.",
                        [
                            "assetId" => (int) ($asset->id ?? 0),
                            "globalSetId" => (int) ($globalSet->id ?? 0),
                            "globalSetHandle" => (string) ($globalSet->handle ?? ""),
                            "fieldHandle" => (string) ($field->handle ?? ""),
                            "error" => $e->getMessage(),
                        ],
                    );
                    continue;
                }

                $content = $this->normalizeFieldValueToString($fieldValue);
                if ($content === null) {
                    continue;
                }

                if (!$this->contentContainsAssetReference(
                    $content,
                    $searchPatterns,
                    (int) $asset->id,
                )) {
                    continue;
                }

                $results[] = [
                    "type" => "global",
                    "handle" => $globalSet->handle,
                    "name" => $globalSet->name,
                    "field" => (string) ($field->name ?? $field->handle ?? ""),
                ];
            }
        }

        return $results;
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
        $htmlFields = $this->getAllHtmlFields();
        if (empty($htmlFields)) {
            return [
                "entries" => [],
                "globals" => [],
            ];
        }

        $htmlFieldIds = [];
        foreach ($htmlFields as $field) {
            $fieldId = (int) ($field->id ?? 0);
            if ($fieldId > 0) {
                $htmlFieldIds[] = $fieldId;
            }
        }

        $relevantTypeIds = $this->getEntryTypeIdsWithFields($htmlFieldIds);

        $volumeIndicators = $this->getVolumeIndicators();
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

        if (empty($relevantTypeIds)) {
            return [
                "entries" => [],
                "globals" => $this->buildGlobalsIndex(),
            ];
        }

        $entryIndex = [];
        $entryQuery = Entry::find()->status(null)->typeId($relevantTypeIds);

        $batchSize = 200;
        foreach ($entryQuery->each($batchSize) as $entry) {
            $content = $this->collectHtmlContentFromElement($entry);
            if ($content === "") {
                continue;
            }

            $hasAssetReference = false;
            foreach ($volumeIndicators as $indicator) {
                if ($indicator !== "" && str_contains($content, $indicator)) {
                    $hasAssetReference = true;
                    break;
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
                $entryIndex[(int) $entry->id] = $content;
            }
        }

        return [
            "entries" => $entryIndex,
            "globals" => $this->buildGlobalsIndex(),
        ];
    }

    /**
     * Check whether an asset is present in a pre-built content index.
     */
    public function isAssetUsedWithIndex(int $assetId, array $contentIndex): bool
    {
        $asset = Asset::find()->id($assetId)->one();
        if (!$asset) {
            return false;
        }

        $searchPatterns = $this->buildAssetSearchPatterns($asset);
        $dataAssetPattern = 'data-asset-id="' . $assetId . '"';
        $hashAssetPattern = "#asset:" . $assetId;

        foreach ($contentIndex["entries"] ?? [] as $entryContent) {
            if (!is_string($entryContent)) {
                continue;
            }

            foreach ($searchPatterns as $pattern) {
                if ($pattern !== "" && str_contains($entryContent, $pattern)) {
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

        foreach ($contentIndex["globals"] ?? [] as $globalContent) {
            if (!is_string($globalContent)) {
                continue;
            }

            foreach ($searchPatterns as $pattern) {
                if ($pattern !== "" && str_contains($globalContent, $pattern)) {
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
     * Fetch all entries that should be scanned for content usage.
     *
     * @param array<int> $relevantTypeIds
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
            $allEntries[(int) $entry->id] = $entry;
        }

        if ($this->getEntryUsageResolver()->resolveIncludeDrafts($includeDrafts)) {
            foreach (
                Entry::find()
                    ->typeId($relevantTypeIds)
                    ->drafts()
                    ->savedDraftsOnly()
                    ->all()
                as $entry
            ) {
                $allEntries[(int) $entry->id] = $entry;
            }

            $draftCreatorUserId = $this->getEntryUsageResolver()->resolveDraftCreatorUserId(
                $initiatingUserId,
            );

            $provisionalDraftsQuery = Entry::find()
                ->typeId($relevantTypeIds)
                ->provisionalDrafts();

            if ($draftCreatorUserId !== null) {
                $provisionalDraftsQuery->draftCreator($draftCreatorUserId);
            }

            foreach ($provisionalDraftsQuery->all() as $entry) {
                $allEntries[(int) $entry->id] = $entry;
            }
        }

        if (
            $this->getEntryUsageResolver()->resolveIncludeRevisions(
                $includeRevisions,
            )
        ) {
            foreach (
                Entry::find()
                    ->typeId($relevantTypeIds)
                    ->revisions()
                    ->all()
                as $entry
            ) {
                $allEntries[(int) $entry->id] = $entry;
            }
        }

        return array_values($allEntries);
    }

    /**
     * Get entry type IDs whose field layouts contain any of the given field IDs.
     *
     * @param array<int> $fieldIds
     * @return array<int>
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
                "Could not load field layouts while determining content usage entry types.",
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
                    "Skipping field layout while determining content usage entry types because its custom fields could not be resolved.",
                    [
                        "fieldLayoutId" => (int) ($layout->id ?? 0),
                        "error" => $e->getMessage(),
                    ],
                );

                continue;
            }

            foreach ($customFields as $field) {
                $fieldId = (int) ($field->id ?? 0);
                if (in_array($fieldId, $fieldIds, true)) {
                    $layoutId = (int) ($layout->id ?? 0);
                    if ($layoutId > 0) {
                        $relevantLayoutIds[] = $layoutId;
                    }
                    break;
                }
            }
        }

        $relevantLayoutIds = array_values(array_unique($relevantLayoutIds));
        if (empty($relevantLayoutIds)) {
            return [];
        }

        $typeIds = (new Query())
            ->select(["id"])
            ->from("{{%entrytypes}}")
            ->where(["fieldLayoutId" => $relevantLayoutIds])
            ->column();

        $typeIds = array_values(
            array_unique(array_filter(array_map("intval", $typeIds))),
        );
        sort($typeIds, SORT_NUMERIC);

        return $typeIds;
    }

    /**
     * Build the globals content index.
     *
     * @return array<string, string>
     */
    private function buildGlobalsIndex(): array
    {
        $globalIndex = [];

        foreach (GlobalSet::find()->all() as $globalSet) {
            $content = $this->collectHtmlContentFromElement($globalSet);
            if ($content !== "") {
                $globalIndex[(string) $globalSet->handle] = $content;
            }
        }

        return $globalIndex;
    }

    /**
     * Collect concatenated HTML-capable field content for an element.
     */
    private function collectHtmlContentFromElement(object $element): string
    {
        $content = "";

        foreach ($this->getHtmlFieldsForElement($element) as $field) {
            try {
                $fieldValue = $element->getFieldValue($field->handle);
            } catch (\Throwable $e) {
                Logger::warning(
                    "Skipping field while building content index because its value could not be read.",
                    [
                        "elementId" => (int) ($element->id ?? 0),
                        "elementType" => get_class($element),
                        "fieldHandle" => (string) ($field->handle ?? ""),
                        "error" => $e->getMessage(),
                    ],
                );
                continue;
            }

            $normalizedValue = $this->normalizeFieldValueToString($fieldValue);
            if ($normalizedValue !== null && $normalizedValue !== "") {
                $content .= $normalizedValue . "\n";
            }
        }

        return $content;
    }

    /**
     * Get HTML-capable custom fields for the concrete element field layout.
     *
     * @return array<int, mixed>
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
                "Could not resolve field layout while evaluating asset content usage.",
                [
                    "elementId" => (int) ($element->id ?? 0),
                    "elementType" => get_class($element),
                    "error" => $e->getMessage(),
                ],
            );
            return [];
        }

        if ($fieldLayout === null) {
            return [];
        }

        $htmlFields = [];

        try {
            $customFields = $fieldLayout->getCustomFields();
        } catch (\Throwable $e) {
            Logger::warning(
                "Could not resolve custom fields from field layout while evaluating asset content usage.",
                [
                    "elementId" => (int) ($element->id ?? 0),
                    "elementType" => get_class($element),
                    "fieldLayoutId" => (int) ($fieldLayout->id ?? 0),
                    "error" => $e->getMessage(),
                ],
            );

            return [];
        }

        foreach ($customFields as $field) {
            if ($this->isSupportedHtmlField($field)) {
                $htmlFields[] = $field;
            }
        }

        return $htmlFields;
    }

    /**
     * Get all registered HTML-capable fields across the system.
     *
     * @return array<int, mixed>
     */
    private function getAllHtmlFields(): array
    {
        $htmlFields = [];
        foreach (Craft::$app->getFields()->getAllFields() as $field) {
            if ($this->isSupportedHtmlField($field)) {
                $htmlFields[] = $field;
            }
        }

        return $htmlFields;
    }

    /**
     * Determine whether a field class is supported for HTML asset scanning.
     */
    private function isSupportedHtmlField(mixed $field): bool
    {
        if (!is_object($field)) {
            return false;
        }

        return in_array(
            get_class($field),
            $this->getSupportedHtmlFieldTypes(),
            true,
        );
    }

    /**
     * @return array<int, string>
     */
    private function getSupportedHtmlFieldTypes(): array
    {
        return [
            "craft\\redactor\\Field",
            "craft\\ckeditor\\Field",
        ];
    }

    /**
     * Normalize supported field values to a raw string.
     */
    private function normalizeFieldValueToString(mixed $fieldValue): ?string
    {
        if ($fieldValue instanceof \craft\redactor\FieldData) {
            $fieldValue = $fieldValue->getRawContent();
        } elseif (
            is_object($fieldValue) &&
            method_exists($fieldValue, "__toString")
        ) {
            $fieldValue = (string) $fieldValue;
        }

        if (!is_string($fieldValue) || $fieldValue === "") {
            return null;
        }

        return $fieldValue;
    }

    /**
     * Build the string patterns used to detect an asset reference in content.
     *
     * @return array<int, string>
     */
    private function buildAssetSearchPatterns(Asset $asset): array
    {
        $patterns = [];
        $filename = trim((string) ($asset->filename ?? ""));
        if ($filename !== "") {
            $patterns[] = $filename;
        }

        $assetUrl = $asset->getUrl();
        if (is_string($assetUrl) && $assetUrl !== "") {
            $patterns[] = $assetUrl;

            $parsedUrl = parse_url($assetUrl);
            if (is_array($parsedUrl) && isset($parsedUrl["path"])) {
                $parsedPath = trim((string) $parsedUrl["path"]);
                if ($parsedPath !== "") {
                    $patterns[] = $parsedPath;
                }
            }
        }

        $folderPath = trim((string) ($asset->folderPath ?? ""));
        if ($folderPath !== "" && $filename !== "") {
            $patterns[] = $folderPath . $filename;
            $patterns[] = rtrim($folderPath, "/") . "/" . ltrim($filename, "/");
        }

        return array_values(
            array_unique(
                array_filter(
                    array_map(
                        static fn(string $pattern): string => trim($pattern),
                        $patterns,
                    ),
                    static fn(string $pattern): bool => $pattern !== "",
                ),
            ),
        );
    }

    /**
     * Determine whether a block of content references the given asset.
     *
     * @param array<int, string> $searchPatterns
     */
    private function contentContainsAssetReference(
        string $content,
        array $searchPatterns,
        int $assetId,
    ): bool {
        foreach ($searchPatterns as $pattern) {
            if ($pattern !== "" && str_contains($content, $pattern)) {
                return true;
            }
        }

        return str_contains($content, 'data-asset-id="' . $assetId . '"') ||
            str_contains($content, "#asset:" . $assetId);
    }

    /**
     * Collect volume-derived indicators that often appear in asset URLs/paths.
     *
     * @return array<int, string>
     */
    private function getVolumeIndicators(): array
    {
        $volumeIndicators = [];

        foreach (Craft::$app->getVolumes()->getAllVolumes() as $volume) {
            try {
                $fs = $volume->getFs();

                if (method_exists($fs, "getRootUrl")) {
                    $rootUrl = $fs->getRootUrl();
                    if (is_string($rootUrl) && $rootUrl !== "") {
                        $parsed = parse_url($rootUrl);
                        if (is_array($parsed) && isset($parsed["path"])) {
                            $path = trim((string) $parsed["path"]);
                            if ($path !== "") {
                                $volumeIndicators[] = $path;
                            }
                        }

                        $volumeIndicators[] = $rootUrl;
                    }
                }

                if (method_exists($fs, "getRootPath")) {
                    $rootPath = $fs->getRootPath();
                    if (is_string($rootPath) && $rootPath !== "") {
                        $basename = basename($rootPath);
                        if ($basename !== "") {
                            $volumeIndicators[] = $basename;
                        }
                    }
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

        return array_values(
            array_unique(
                array_filter(
                    array_map(
                        static fn(string $indicator): string => trim($indicator),
                        $volumeIndicators,
                    ),
                    static fn(string $indicator): bool => $indicator !== "",
                ),
            ),
        );
    }

    private function getEntryUsageResolver(): EntryUsageResolver
    {
        if ($this->entryUsageResolver === null) {
            $this->entryUsageResolver = new EntryUsageResolver();
        }

        return $this->entryUsageResolver;
    }
}