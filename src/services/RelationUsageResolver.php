<?php

declare(strict_types=1);

namespace yann\assetcleaner\services;

use Craft;
use craft\base\Component;
use craft\db\Query;
use craft\db\Table;
use craft\elements\Entry;
use craft\elements\db\EntryQuery;
use yann\assetcleaner\helpers\Logger;

/**
 * Relation Usage Resolver
 *
 * Resolves asset usage through Craft relation records while honoring
 * draft/revision policy for entry-backed sources and preserving fallback
 * handling for generic non-entry sources.
 */
class RelationUsageResolver extends Component
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
     * Determine whether an asset has any valid relation usage.
     */
    public function hasResolvedRelationUsage(
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
            ->innerJoin(
                ["sourceElements" => Table::ELEMENTS],
                "[[sourceElements.id]] = [[r.sourceId]]",
            )
            ->where([
                "r.targetId" => $assetIds,
                "sourceElements.dateDeleted" => null,
            ])
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
                $resolvedSourceCache[$sourceId] = $this->resolveRelationSourceEntry(
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
     * @return array{entryRelations: array<int, array<string, mixed>>, genericRelations: array<int, array<string, mixed>>}
     */
    public function getRelationUsageRecords(
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
            ->innerJoin(
                ["sourceElements" => Table::ELEMENTS],
                "[[sourceElements.id]] = [[r.sourceId]]",
            )
            ->where([
                "r.targetId" => $assetId,
                "sourceElements.dateDeleted" => null,
            ])
            ->column();

        foreach (
            $this->getResolvedRelationEntries(
                $assetId,
                $includeDrafts,
                $includeRevisions,
                $initiatingUserId,
            ) as $entry
        ) {
            $entryRecords["entry-" . $entry->id] = [
                "id" => $entry->id,
                "title" => $entry->title,
                "url" => $entry->getCpEditUrl(),
                "status" => $entry->getStatus(),
                "section" => $this->getEntryUsageResolver()->getSafeSectionName(
                    $entry,
                ),
            ];
        }

        if ($this->resolveCountAllRelationsAsUsage($countAllRelationsAsUsage)) {
            foreach ($relations as $sourceId) {
                $sourceId = (int) $sourceId;
                if ($sourceId <= 0) {
                    continue;
                }

                if (
                    !$this->sourceCountsForFallbackRelationUsage(
                        $sourceId,
                        $includeDrafts,
                        $includeRevisions,
                        $initiatingUserId,
                    )
                ) {
                    continue;
                }

                $resolvedEntry = $this->resolveRelationSourceEntry(
                    $sourceId,
                    $includeDrafts,
                    $includeRevisions,
                    $initiatingUserId,
                );

                if ($resolvedEntry instanceof Entry) {
                    continue;
                }

                $record = $this->getGenericRelationUsageRecord(
                    $sourceId,
                    $initiatingUserId,
                );

                if ($record !== null) {
                    $genericRecords["generic-" . $sourceId] = $record;
                }
            }
        }

        return [
            "entryRelations" => array_values($entryRecords),
            "genericRelations" => array_values($genericRecords),
        ];
    }

    /**
     * Resolve one relation source ID to a top-level entry when possible.
     */
    public function resolveRelationSourceEntry(
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

        return $this->getEntryUsageResolver()->resolveUsageEntry(
            $entry,
            $includeDrafts,
            $includeRevisions,
            $initiatingUserId,
        );
    }

    /**
     * Resolve the relation fallback policy.
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
            ->innerJoin(
                ["sourceElements" => Table::ELEMENTS],
                "[[sourceElements.id]] = [[r.sourceId]]",
            )
            ->where([
                "r.targetId" => $assetIds,
                "sourceElements.dateDeleted" => null,
            ])
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
                $sourceUsageCache[$sourceId] = $this->sourceCountsForFallbackRelationUsage(
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
     */
    private function sourceCountsForFallbackRelationUsage(
        int $sourceId,
        ?bool $includeDrafts = null,
        ?bool $includeRevisions = null,
        ?int $initiatingUserId = null,
    ): bool {
        $elementRow = $this->fetchRelationSourceElementRow($sourceId);
        if (is_array($elementRow) && !empty($elementRow["dateDeleted"])) {
            return false;
        }

        $entry = $this->resolveRelationSourceEntryIgnoringUsagePolicy(
            $sourceId,
            $initiatingUserId,
        );

        if (!$entry) {
            return true;
        }

        $resolvedUsageEntry = $this->getEntryUsageResolver()->resolveUsageEntry(
            $entry,
            $includeDrafts,
            $includeRevisions,
            $initiatingUserId,
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
                    return $entry;
                }

                $elementRow = $this->fetchRelationSourceElementRow($candidateId);
                if (is_array($elementRow)) {
                    $canonicalId = (int) ($elementRow["canonicalId"] ?? 0);
                    if ($canonicalId > 0 && !isset($visitedIds[$canonicalId])) {
                        $nextIds[$canonicalId] = true;
                    }
                }

                foreach (
                    $this->findRelationSourceOwnerIds($candidateId) as $ownerId
                ) {
                    if ($ownerId > 0 && !isset($visitedIds[$ownerId])) {
                        $nextIds[$ownerId] = true;
                    }
                }
            }

            $pendingIds = $nextIds;
            $depth++;
        }

        return null;
    }

    /**
     * Fetch the raw elements table row for a relation source.
     *
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
     */
    private function findEntryByIdIgnoringUsagePolicy(int $sourceId): ?Entry
    {
        $queryDefinitions = [
            [
                "query" => $this->buildRelationSourceEntryQuery($sourceId),
            ],
            [
                "query" => $this->buildRelationSourceEntryQuery($sourceId)
                    ->drafts()
                    ->savedDraftsOnly(),
            ],
            [
                "query" => $this->buildRelationSourceEntryQuery($sourceId)
                    ->provisionalDrafts(),
            ],
            [
                "query" => $this->buildRelationSourceEntryQuery($sourceId)
                    ->revisions(),
            ],
        ];

        foreach ($queryDefinitions as $definition) {
            $entry = $definition["query"]->one();
            if ($entry instanceof Entry) {
                return $entry;
            }
        }

        return null;
    }

    /**
     * Build an entry query that searches broadly across sites and owner states.
     */
    private function buildRelationSourceEntryQuery(int $sourceId): EntryQuery
    {
        return Entry::find()
            ->id($sourceId)
            ->site("*")
            ->status(null)
            ->allowOwnerDrafts(true)
            ->allowOwnerRevisions(true);
    }

    /**
     * Resolve an element for a relation source ID.
     */
    private function findRelationSourceElementById(
        int $sourceId,
        ?int $initiatingUserId = null,
    ): mixed {
        $entry = $this->findEntryByIdIgnoringUsagePolicy($sourceId);
        if ($entry instanceof Entry) {
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
                return $element;
            }
        } catch (\Throwable $e) {
            Logger::warning(
                "Could not resolve relation source element by ID.",
                [
                    "sourceId" => $sourceId,
                    "initiatingUserId" => $initiatingUserId,
                    "error" => $e->getMessage(),
                ],
            );
        }

        return $this->findEntryByIdIgnoringUsagePolicy($sourceId);
    }

    /**
     * Resolve an entry from a relation source by traversing owner chains.
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
     * @return array<string, mixed>|null
     */
    private function getGenericRelationUsageRecord(
        int $sourceId,
        ?int $initiatingUserId = null,
    ): ?array {
        $title = Craft::t("asset-cleaner", "Relational source #{id}", [
            "id" => $sourceId,
        ]);
        $url = "#";
        $status = "live";
        $section = Craft::t("asset-cleaner", "Relational element");

        $elementRow = $this->fetchRelationSourceElementRow($sourceId);
        if (is_array($elementRow) && !empty($elementRow["dateDeleted"])) {
            return null;
        }

        try {
            $resolvedRelationEntry = $this->resolveRelationSourceEntryIgnoringUsagePolicy(
                $sourceId,
                $initiatingUserId,
            );

            if ($resolvedRelationEntry instanceof Entry) {
                return null;
            }

            $element = $this->findRelationSourceElementById(
                $sourceId,
                $initiatingUserId,
            );

            if ($element instanceof Entry) {
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
                    "initiatingUserId" => $initiatingUserId,
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
            ->innerJoin(
                ["sourceElements" => Table::ELEMENTS],
                "[[sourceElements.id]] = [[r.sourceId]]",
            )
            ->where([
                "r.targetId" => $assetId,
                "sourceElements.dateDeleted" => null,
            ])
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

    private function getEntryUsageResolver(): EntryUsageResolver
    {
        if ($this->entryUsageResolver === null) {
            $this->entryUsageResolver = new EntryUsageResolver();
        }

        return $this->entryUsageResolver;
    }
}