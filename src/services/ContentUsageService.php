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

        foreach (GlobalSet::find()->site("*")->all() as $globalSet) {
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

        // With site('*') the same global set is visited once per site — keep
        // one result row per set and field.
        $unique = [];
        foreach ($results as $result) {
            $key = (string) ($result["handle"] ?? "") . "-" . (string) ($result["field"] ?? "");
            $unique[$key] = $result;
        }

        return array_values($unique);
    }

    

    

    /**
     * Fetch all entries that should be scanned for content usage.
     *
     * Entries are fetched for every site (one instance per site, each with
     * that site's field content) and provisional drafts are included for all
     * users, not just the initiating one.
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
        $entries = Entry::find()
            ->typeId($relevantTypeIds)
            ->site("*")
            ->status(null)
            ->all();

        $allEntries = [];
        foreach ($entries as $entry) {
            $allEntries[$this->entrySiteKey($entry)] = $entry;
        }

        if ($this->getEntryUsageResolver()->resolveIncludeDrafts($includeDrafts)) {
            foreach (
                Entry::find()
                    ->typeId($relevantTypeIds)
                    ->site("*")
                    ->drafts()
                    ->savedDraftsOnly()
                    ->all()
                as $entry
            ) {
                $allEntries[$this->entrySiteKey($entry)] = $entry;
            }

            foreach (
                Entry::find()
                    ->typeId($relevantTypeIds)
                    ->site("*")
                    ->provisionalDrafts()
                    ->all()
                as $entry
            ) {
                $allEntries[$this->entrySiteKey($entry)] = $entry;
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
                    ->site("*")
                    ->revisions()
                    ->all()
                as $entry
            ) {
                $allEntries[$this->entrySiteKey($entry)] = $entry;
            }
        }

        return array_values($allEntries);
    }

    /**
     * Build a dedupe key that keeps one entry instance per entry AND site,
     * since each site instance carries its own field content.
     */
    private function entrySiteKey(Entry $entry): string
    {
        return (int) ($entry->id ?? 0) . "-" . (int) ($entry->siteId ?? 0);
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

    

    private function getEntryUsageResolver(): EntryUsageResolver
    {
        if ($this->entryUsageResolver === null) {
            $this->entryUsageResolver = new EntryUsageResolver();
        }

        return $this->entryUsageResolver;
    }
}