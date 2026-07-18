<?php

declare(strict_types=1);

namespace yann\assetcleaner\services;

use Craft;
use craft\base\Component;
use craft\db\Query;
use craft\db\Table;
use craft\elements\Entry;
use craft\elements\User;
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

    /**
     * @var int Maximum number of entries retained in the entry lookup cache.
     * Verdict caches hold booleans and need no cap; entries are full element
     * models, so growth must be bounded for long-running queue workers.
     */
    private const MAX_CACHED_ENTRIES = 10000;

    /**
     * @var array<int,bool> Fallback-mode source usage verdicts, keyed by
     * source element ID. Valid for the current cache options signature only.
     */
    private array $fallbackSourceVerdictCache = [];

    /**
     * @var array<int,bool> Strict-mode source usage verdicts, keyed by source
     * element ID. Valid for the current cache options signature only.
     */
    private array $strictSourceVerdictCache = [];

    /**
     * @var array<int,Entry|null> Entries resolved regardless of usage policy,
     * keyed by element ID. Policy-independent, so shared owners in nested
     * element chains are only ever fetched once per cache lifetime.
     */
    private array $entryLookupCache = [];

    /**
     * @var string|null Options signature the verdict caches were built for.
     */
    private ?string $verdictCacheSignature = null;

    /**
     * Clear all resolution caches.
     *
     * Call once per queue execution (or whenever underlying content may have
     * changed) so caches never leak stale verdicts across scans in
     * long-running queue workers.
     */
    public function resetResolutionCaches(): void
    {
        $this->fallbackSourceVerdictCache = [];
        $this->strictSourceVerdictCache = [];
        $this->entryLookupCache = [];
        $this->verdictCacheSignature = null;
    }

    /**
     * Invalidate the verdict caches when the usage options they were built
     * with change. The entry lookup cache is policy-independent and survives.
     */
    private function ensureVerdictCacheSignature(
        ?bool $includeDrafts,
        ?bool $includeRevisions,
        ?int $initiatingUserId,
    ): void {
        $signature = sprintf(
            "%s|%s|%s",
            $includeDrafts === null ? "n" : (int) $includeDrafts,
            $includeRevisions === null ? "n" : (int) $includeRevisions,
            $initiatingUserId ?? "n",
        );

        if ($this->verdictCacheSignature !== $signature) {
            $this->fallbackSourceVerdictCache = [];
            $this->strictSourceVerdictCache = [];
            $this->verdictCacheSignature = $signature;
        }
    }

    /**
     * @var int Number of source IDs resolved per bulk-prime chunk. Bounds
     * peak memory: at most this many entries (plus owners) are loaded at once.
     */
    private const BULK_PRIME_CHUNK_SIZE = 500;

    /**
     * @var bool|null Test/diagnostic override for bulk priming. Null defers
     * to config; true/false forces it on or off (used by the parity command).
     */
    public ?bool $forceBulkResolution = null;

    /**
     * Whether bulk source resolution is enabled.
     *
     * Enabled by default; disable via config/asset-cleaner.php
     * ('bulkRelationResolution' => false) or the
     * ASSET_CLEANER_BULK_RELATION_RESOLUTION env var. Disabling skips the
     * priming step entirely, so every source resolves through the original
     * per-source path.
     */
    private function isBulkRelationResolutionEnabled(): bool
    {
        if ($this->forceBulkResolution !== null) {
            return $this->forceBulkResolution;
        }

        $rawValue = null;

        try {
            $config = Craft::$app
                ->getConfig()
                ->getConfigFromFile("asset-cleaner");
            if (
                is_array($config) &&
                array_key_exists("bulkRelationResolution", $config)
            ) {
                $rawValue = $config["bulkRelationResolution"];
            }
        } catch (\Throwable $e) {
            Logger::warning(
                "Could not load Asset Cleaner config while resolving bulk relation resolution flag.",
                ["error" => $e->getMessage()],
            );
        }

        $envValue = getenv("ASSET_CLEANER_BULK_RELATION_RESOLUTION");
        if (is_string($envValue) && trim($envValue) !== "") {
            $rawValue = trim($envValue);
        }

        if ($rawValue === null) {
            return true;
        }

        return filter_var($rawValue, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Bulk-resolve usage verdicts for relation source elements and prime the
     * per-execution caches.
     *
     * This is a pure accelerator: it only fills the same caches the
     * per-source loops read, using set-based queries instead of per-source
     * ones. Sources it does not prime (non-entry elements, entries missing
     * from all lookup tiers) fall through to the unchanged per-source path.
     * Only entry-type sources are bulked because for them the "resolve to an
     * entry" step is the source itself — no ancestry logic is reimplemented.
     *
     * Verdicts must stay site- and user-independent (see
     * PLAN-BULK-RELATION-RESOLUTION.md); any new verdict input must honor
     * that or update this loader in the same change.
     *
     * @param array<int> $sourceIds
     */
    private function primeSourceVerdictsInBulk(
        array $sourceIds,
        ?bool $includeDrafts = null,
        ?bool $includeRevisions = null,
        ?int $initiatingUserId = null,
    ): void {
        if (!$this->isBulkRelationResolutionEnabled()) {
            return;
        }

        $this->ensureVerdictCacheSignature(
            $includeDrafts,
            $includeRevisions,
            $initiatingUserId,
        );

        $pendingIds = [];
        foreach ($sourceIds as $sourceId) {
            $sourceId = (int) $sourceId;
            if (
                $sourceId > 0 &&
                !array_key_exists($sourceId, $this->fallbackSourceVerdictCache)
            ) {
                $pendingIds[$sourceId] = true;
            }
        }

        if (empty($pendingIds)) {
            return;
        }

        $primedCount = 0;
        $legacyCount = 0;

        foreach (
            array_chunk(array_keys($pendingIds), self::BULK_PRIME_CHUNK_SIZE)
            as $chunkIds
        ) {
            try {
                [$chunkPrimed, $chunkLegacy] = $this->primeSourceVerdictChunk(
                    $chunkIds,
                    $includeDrafts,
                    $includeRevisions,
                    $initiatingUserId,
                );
                $primedCount += $chunkPrimed;
                $legacyCount += $chunkLegacy;
            } catch (\Throwable $e) {
                // A failed chunk is not fatal: its sources simply resolve
                // through the per-source path.
                Logger::warning(
                    "Bulk relation source priming failed for a chunk; falling back to per-source resolution.",
                    [
                        "chunkSize" => count($chunkIds),
                        "error" => $e->getMessage(),
                    ],
                );
                $legacyCount += count($chunkIds);
            }
        }

        Logger::debug("Primed relation source verdicts in bulk.", [
            "requested" => count($pendingIds),
            "primed" => $primedCount,
            "leftToPerSourcePath" => $legacyCount,
        ]);
    }

    /**
     * Prime one chunk of source IDs.
     *
     * @param array<int> $chunkIds
     * @return array{0:int,1:int} primed count, left-to-legacy count
     */
    private function primeSourceVerdictChunk(
        array $chunkIds,
        ?bool $includeDrafts,
        ?bool $includeRevisions,
        ?int $initiatingUserId,
    ): array {
        $primedCount = 0;
        $legacyCount = 0;

        $elementRows = (new Query())
            ->select(["id", "type", "dateDeleted"])
            ->from(Table::ELEMENTS)
            ->where(["id" => $chunkIds])
            ->all();

        $entrySourceIds = [];
        $seenIds = [];

        foreach ($elementRows as $row) {
            $elementId = (int) ($row["id"] ?? 0);
            if ($elementId <= 0) {
                continue;
            }
            $seenIds[$elementId] = true;

            if (!empty($row["dateDeleted"])) {
                // Parity with sourceCountsForFallbackRelationUsage: deleted
                // sources never count. Strict mode reaches the same verdict
                // because its relations query already excludes them.
                $this->fallbackSourceVerdictCache[$elementId] = false;
                $this->strictSourceVerdictCache[$elementId] = false;
                $primedCount++;
                continue;
            }

            if ((string) ($row["type"] ?? "") === Entry::class) {
                $entrySourceIds[] = $elementId;
            } else {
                // Non-entry sources keep the per-source path: their ancestry
                // semantics (owner walks, raw-ancestry BFS) are not
                // reimplemented here.
                $legacyCount++;
            }
        }

        // IDs with no elements row at all resolve (to nothing) via the
        // per-source path, same as today.
        $legacyCount += count($chunkIds) - count($seenIds);

        if (empty($entrySourceIds)) {
            return [$primedCount, $legacyCount];
        }

        $loadedEntries = $this->batchLoadEntriesIgnoringUsagePolicy(
            $entrySourceIds,
        );

        $this->primeOwnerChains($loadedEntries);

        foreach ($entrySourceIds as $sourceId) {
            $entry = $loadedEntries[$sourceId] ?? null;
            if (!$entry instanceof Entry) {
                // Missing from all lookup tiers: leave to the per-source
                // path, which additionally tries getElementById() and the
                // raw-ancestry BFS.
                $legacyCount++;
                continue;
            }

            // For entry sources, fallback mode and strict mode provably
            // reduce to the same expression:
            // resolveUsageEntry(...) instanceof Entry.
            $verdict =
                $this->getEntryUsageResolver()->resolveUsageEntry(
                    $entry,
                    $includeDrafts,
                    $includeRevisions,
                    $initiatingUserId,
                ) instanceof Entry;

            $this->fallbackSourceVerdictCache[$sourceId] = $verdict;
            $this->strictSourceVerdictCache[$sourceId] = $verdict;
            $primedCount++;
        }

        return [$primedCount, $legacyCount];
    }

    /**
     * Batch equivalent of findEntryByIdIgnoringUsagePolicy: load entries by
     * ID across the same four tiers (live, saved drafts, provisional drafts,
     * revisions), one batched query per tier instead of up to four queries
     * per entry. IDs found in an earlier tier are excluded from later tiers.
     * Results (hits only) also prime the entry lookup cache.
     *
     * @param array<int> $entryIds
     * @return array<int, Entry> Loaded entries keyed by element ID
     */
    private function batchLoadEntriesIgnoringUsagePolicy(array $entryIds): array
    {
        $loaded = [];

        $missingIds = [];
        foreach ($entryIds as $entryId) {
            $entryId = (int) $entryId;
            if ($entryId <= 0) {
                continue;
            }

            if (
                array_key_exists($entryId, $this->entryLookupCache) &&
                $this->entryLookupCache[$entryId] instanceof Entry
            ) {
                $loaded[$entryId] = $this->entryLookupCache[$entryId];
                continue;
            }

            $missingIds[$entryId] = true;
        }

        $tierModifiers = [
            fn(EntryQuery $query): EntryQuery => $query,
            fn(EntryQuery $query): EntryQuery => $query
                ->drafts()
                ->savedDraftsOnly(),
            fn(EntryQuery $query): EntryQuery => $query->provisionalDrafts(),
            fn(EntryQuery $query): EntryQuery => $query->revisions(),
        ];

        foreach ($tierModifiers as $applyTier) {
            if (empty($missingIds)) {
                break;
            }

            $query = Entry::find()
                ->id(array_keys($missingIds))
                ->site("*")
                ->unique()
                ->status(null)
                ->allowOwnerDrafts(true)
                ->allowOwnerRevisions(true);

            foreach ($applyTier($query)->all() as $entry) {
                if (!$entry instanceof Entry) {
                    continue;
                }

                $entryId = (int) ($entry->id ?? 0);
                if ($entryId <= 0 || isset($loaded[$entryId])) {
                    continue;
                }

                $loaded[$entryId] = $entry;
                unset($missingIds[$entryId]);

                if (count($this->entryLookupCache) < self::MAX_CACHED_ENTRIES) {
                    $this->entryLookupCache[$entryId] = $entry;
                }
            }
        }

        return $loaded;
    }

    /**
     * Pre-wire owner chains for loaded entries so the usage policy's
     * resolveToTopLevelEntry() walks getOwner() without lazy database
     * queries. Owners are loaded level by level in batches, up to the same
     * depth the per-source walk uses. Entries whose owner cannot be batch
     * loaded are left unwired — their getOwner() lazily queries exactly as
     * the per-source path would.
     *
     * @param array<int, Entry> $entries
     */
    private function primeOwnerChains(array $entries): void
    {
        $entryUsageResolver = $this->getEntryUsageResolver();
        $knownEntries = $entries;
        $frontier = $entries;
        $maxDepth = 10;

        for ($depth = 0; $depth < $maxDepth && !empty($frontier); $depth++) {
            $wantedOwnerIds = [];

            foreach ($frontier as $entry) {
                if ($entryUsageResolver->hasUsableSection($entry)) {
                    continue;
                }

                if (!method_exists($entry, "getOwnerId")) {
                    continue;
                }

                $ownerId = (int) ($entry->getOwnerId() ?? 0);
                if ($ownerId > 0 && !isset($knownEntries[$ownerId])) {
                    $wantedOwnerIds[$ownerId] = true;
                }
            }

            $newOwners = empty($wantedOwnerIds)
                ? []
                : $this->batchLoadEntriesIgnoringUsagePolicy(
                    array_keys($wantedOwnerIds),
                );

            foreach ($newOwners as $ownerId => $owner) {
                $knownEntries[$ownerId] = $owner;
            }

            foreach ($frontier as $entry) {
                if (
                    $entryUsageResolver->hasUsableSection($entry) ||
                    !method_exists($entry, "getOwnerId") ||
                    !method_exists($entry, "setOwner")
                ) {
                    continue;
                }

                $ownerId = (int) ($entry->getOwnerId() ?? 0);
                if ($ownerId > 0 && isset($knownEntries[$ownerId])) {
                    $entry->setOwner($knownEntries[$ownerId]);
                }
            }

            $frontier = $newOwners;
        }
    }

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
            $usedAssetIds = array_fill_keys(
                $this->getFallbackRelationUsageIds(
                    $assetIds,
                    $includeDrafts,
                    $includeRevisions,
                    $initiatingUserId,
                ),
                true,
            );
        } else {
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

            $this->ensureVerdictCacheSignature(
                $includeDrafts,
                $includeRevisions,
                $initiatingUserId,
            );

            $this->primeSourceVerdictsInBulk(
                array_column($relations, "sourceId"),
                $includeDrafts,
                $includeRevisions,
                $initiatingUserId,
            );

            $usedAssetIds = [];

            foreach ($relations as $relation) {
                $sourceId = (int) ($relation["sourceId"] ?? 0);
                $targetId = (int) ($relation["targetId"] ?? 0);

                if ($sourceId <= 0 || $targetId <= 0) {
                    continue;
                }

                if (!array_key_exists($sourceId, $this->strictSourceVerdictCache)) {
                    $this->strictSourceVerdictCache[$sourceId] =
                        $this->resolveRelationSourceEntry(
                            $sourceId,
                            $includeDrafts,
                            $includeRevisions,
                            $initiatingUserId,
                        ) instanceof Entry;
                }

                if ($this->strictSourceVerdictCache[$sourceId]) {
                    $usedAssetIds[$targetId] = true;
                }
            }
        }

        foreach ($this->getUserPhotoUsageIds($assetIds) as $userPhotoAssetId) {
            $usedAssetIds[(int) $userPhotoAssetId] = true;
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

        foreach ($this->getUserPhotoUsageRecords($assetId) as $record) {
            $genericRecords[
                "user-photo-" . (int) ($record["id"] ?? 0)
            ] = $record;
        }

        return [
            "entryRelations" => array_values($entryRecords),
            "genericRelations" => array_values($genericRecords),
        ];
    }

    /**
     * Resolve asset usage from the users table photoId column.
     *
     * @param array<int> $assetIds
     * @return array<int>
     */
    private function getUserPhotoUsageIds(array $assetIds): array
    {
        $assetIds = array_values(
            array_unique(array_filter(array_map("intval", $assetIds))),
        );

        if (empty($assetIds)) {
            return [];
        }

        $usedAssetIds = (new Query())
            ->select(["u.photoId"])
            ->distinct()
            ->from(["u" => "{{%users}}"])
            ->innerJoin(
                ["userElements" => Table::ELEMENTS],
                "[[userElements.id]] = [[u.id]]",
            )
            ->where([
                "u.photoId" => $assetIds,
                "userElements.dateDeleted" => null,
            ])
            ->column();

        $usedAssetIds = array_values(
            array_unique(array_filter(array_map("intval", $usedAssetIds))),
        );
        sort($usedAssetIds, SORT_NUMERIC);

        return $usedAssetIds;
    }

    /**
     * Build usage records for users whose photoId references the asset.
     *
     * @param int $assetId
     * @return array<int, array<string, mixed>>
     */
    private function getUserPhotoUsageRecords(int $assetId): array
    {
        $userIds = (new Query())
            ->select(["u.id"])
            ->distinct()
            ->from(["u" => "{{%users}}"])
            ->innerJoin(
                ["userElements" => Table::ELEMENTS],
                "[[userElements.id]] = [[u.id]]",
            )
            ->where([
                "u.photoId" => $assetId,
                "userElements.dateDeleted" => null,
            ])
            ->column();

        $userIds = array_values(
            array_unique(array_filter(array_map("intval", $userIds))),
        );

        if (empty($userIds)) {
            return [];
        }

        $records = [];
        foreach (User::find()->id($userIds)->status(null)->all() as $user) {
            $title = trim((string) ($user->fullName ?? ""));
            if ($title === "") {
                $title = trim((string) ($user->username ?? ""));
            }
            if ($title === "") {
                $title = trim((string) ($user->email ?? ""));
            }
            if ($title === "") {
                $title = Craft::t("asset-cleaner", "User #{id}", [
                    "id" => (int) ($user->id ?? 0),
                ]);
            }

            $records[] = [
                "id" => (int) ($user->id ?? 0),
                "title" => $title,
                "url" =>
                    method_exists($user, "getCpEditUrl") &&
                    is_string($user->getCpEditUrl()) &&
                    $user->getCpEditUrl() !== ""
                        ? $user->getCpEditUrl()
                        : "#",
                "status" =>
                    method_exists($user, "getStatus") &&
                    is_string($user->getStatus()) &&
                    $user->getStatus() !== ""
                        ? $user->getStatus()
                        : "live",
                "section" => Craft::t(
                    "asset-cleaner",
                    "User profile picture",
                ),
            ];
        }

        return $records;
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

        $this->ensureVerdictCacheSignature(
            $includeDrafts,
            $includeRevisions,
            $initiatingUserId,
        );

        $this->primeSourceVerdictsInBulk(
            array_column($relations, "sourceId"),
            $includeDrafts,
            $includeRevisions,
            $initiatingUserId,
        );

        $usedAssetIds = [];

        foreach ($relations as $relation) {
            $sourceId = (int) ($relation["sourceId"] ?? 0);
            $targetId = (int) ($relation["targetId"] ?? 0);

            if ($sourceId <= 0 || $targetId <= 0) {
                continue;
            }

            if (!array_key_exists($sourceId, $this->fallbackSourceVerdictCache)) {
                $this->fallbackSourceVerdictCache[$sourceId] = $this->sourceCountsForFallbackRelationUsage(
                    $sourceId,
                    $includeDrafts,
                    $includeRevisions,
                    $initiatingUserId,
                );
            }

            if ($this->fallbackSourceVerdictCache[$sourceId]) {
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
     *
     * Results (including misses) are cached by element ID, so shared owners
     * in nested-element chains are only queried once per cache lifetime.
     */
    private function findEntryByIdIgnoringUsagePolicy(int $sourceId): ?Entry
    {
        if (array_key_exists($sourceId, $this->entryLookupCache)) {
            return $this->entryLookupCache[$sourceId];
        }

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

        $result = null;

        foreach ($queryDefinitions as $definition) {
            $entry = $definition["query"]->one();
            if ($entry instanceof Entry) {
                $result = $entry;
                break;
            }
        }

        if (count($this->entryLookupCache) < self::MAX_CACHED_ENTRIES) {
            $this->entryLookupCache[$sourceId] = $result;
        }

        return $result;
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