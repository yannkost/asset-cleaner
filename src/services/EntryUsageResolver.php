<?php

declare(strict_types=1);

namespace yann\assetcleaner\services;

use Craft;
use craft\base\Component;
use craft\elements\Entry;
use yann\assetcleaner\helpers\Logger;
use yann\assetcleaner\Plugin;

/**
 * Entry Usage Resolver
 *
 * Resolves whether entries should count toward asset usage while applying
 * draft/revision policy and normalizing nested entries to top-level entries.
 */
class EntryUsageResolver extends Component
{
    /**
     * Resolve the user ID whose provisional drafts should be considered.
     *
     * If no explicit initiating user ID is provided, this falls back to the
     * current authenticated control panel user when available.
     */
    public function resolveDraftCreatorUserId(
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
     */
    public function resolveIncludeDrafts(?bool $includeDrafts): bool
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
     */
    public function resolveIncludeRevisions(?bool $includeRevisions): bool
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
     */
    public function shouldIncludeEntryForUsage(
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

        if (
            !$includeDrafts &&
            method_exists($entry, "getIsDraft") &&
            $entry->getIsDraft()
        ) {
            return false;
        }

        return true;
    }

    /**
     * Resolve a nested entry to its top-level parent entry.
     *
     * For entries that don't belong to a section (for example nested/owned
     * entry contexts), this traverses the owner chain to find the actual
     * top-level entry.
     */
    public function resolveToTopLevelEntry(Entry $entry): ?Entry
    {
        if ($this->hasUsableSection($entry)) {
            return $entry;
        }

        $current = $entry;
        $maxDepth = 10;
        $depth = 0;
        $visitedIds = [];

        $entryId = (int) ($entry->id ?? 0);
        if ($entryId > 0) {
            $visitedIds[$entryId] = true;
        }

        while (
            $current &&
            !$this->hasUsableSection($current) &&
            $depth < $maxDepth
        ) {
            try {
                $owner = $current->getOwner();

                if (!$owner instanceof Entry) {
                    break;
                }

                $ownerId = (int) ($owner->id ?? 0);
                if ($ownerId > 0 && isset($visitedIds[$ownerId])) {
                    break;
                }

                if ($ownerId > 0) {
                    $visitedIds[$ownerId] = true;
                }

                $current = $owner;
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

        return $current && $this->hasUsableSection($current) ? $current : null;
    }

    /**
     * Safely determine whether an entry resolves to a valid section.
     */
    public function hasUsableSection(Entry $entry): bool
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
     */
    public function getSafeSectionName(Entry $entry): string
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

    /**
     * Resolve the draft creator user ID for an entry when available.
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
}