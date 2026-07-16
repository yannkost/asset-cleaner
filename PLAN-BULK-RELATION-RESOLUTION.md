# Plan: Bulk relation source resolution ("B")

Status: planned, not implemented.
Branch context: builds on `fix/scan-relations-timeout` (time-budgeted batches + per-execution caches).

## Problem

With `countAllRelationsAsUsage: true`, every unique relation source element is
resolved **one at a time** (`sourceCountsForFallbackRelationUsage`), and each
resolution costs 2–7+ database queries (element row, up to 4 tiered entry
queries, lazy `getOwner()` calls during policy checks). A 2000-asset window
with thousands of unique sources produces tens of thousands of sequential
round trips. The per-execution caches (already shipped) remove *repeat* work,
but first-time resolution is still one-source-at-a-time.

## What this is — and what it is not

This is **not** a second, parallel implementation of the resolution logic.

It is a **priming layer**: a new method that bulk-loads the data for many
sources at once and writes the results into the same per-execution caches the
existing code already reads. The existing per-source code stays exactly as it
is and keeps running after the priming step:

- Sources the bulk pass primed → the existing loop finds their verdict in the
  cache (cache hit, zero queries).
- Sources the bulk pass skipped (non-entry sources, odd edge cases, or when
  the feature is disabled) → the existing loop resolves them exactly as today.

So there is only **one** decision-making implementation. The bulk pass is an
accelerator that pre-fills its cache. Disabling the flag doesn't switch to "an
old code path" — it just skips the acceleration, leaving the code that always
runs anyway to do all the work.

```
                         ┌─ flag ON ──► primeSourceVerdictsInBulk()   (new)
relations query ─────────┤              └─ fills verdict caches
                         └─ flag OFF ─► (nothing)

existing per-source loop (unchanged, always runs)
  └─ cache hit  → free
  └─ cache miss → legacy per-source resolution (unchanged)
```

## Why only entry-type sources are bulked

In Craft 5, Matrix/CKEditor nested entries are `craft\elements\Entry`, so the
vast majority of relation sources are entries. For an entry source, the
"resolve to an entry" step is trivially *the source itself* — all the complex
ancestry logic (object owner walks, raw-ancestry BFS) only exists for
**non-entry** sources (categories, users, commerce variants, ...). Those are
few, and they stay on the untouched per-source path. This keeps the risky
ancestry semantics out of the bulk code entirely.

The policy evaluation is not reimplemented either: the bulk pass calls the
unchanged `EntryUsageResolver::resolveUsageEntry()` on pre-loaded entries
whose owners have been pre-wired via `setOwner()`, so its lazy `getOwner()`
lookups become in-memory reads.

## Pipeline

New method on `RelationUsageResolver`:

```php
private function primeSourceVerdictsInBulk(
    array $sourceIds,
    ?bool $includeDrafts,
    ?bool $includeRevisions,
    ?int $initiatingUserId,
): void
```

Processes source IDs in groups of ~500 (memory bound). Per group:

1. **Classify** (1 query).
   Skip IDs already in the verdict cache.
   `SELECT id, type, dateDeleted, canonicalId FROM elements WHERE id IN (...)`.
   Soft-deleted → verdict `false` (parity with
   `sourceCountsForFallbackRelationUsage`). Non-entry types → leave unprimed.

2. **4-tier batch entry load** (≤4 queries).
   Replicates `findEntryByIdIgnoringUsagePolicy` tier order as batched
   queries: live → saved drafts → provisional drafts → revisions. Each tier:
   `Entry::find()->id($stillMissing)->site('*')->status(null)
   ->allowOwnerDrafts(true)->allowOwnerRevisions(true)->unique()` + tier
   modifier; IDs found in an earlier tier are excluded from later tiers.
   Hits prime `entryLookupCache`. **IDs missing from all four tiers get no
   verdict** — the legacy loop handles them (`getElementById` → BFS), as today.

3. **Owner-chain closure** (≤10 rounds, 1 batched load each).
   For loaded entries without a usable section: collect `getOwnerId()`,
   batch-load missing owners via the same tier loader, wire with
   `$entry->setOwner($owner)`. Matches the existing `maxDepth = 10`.

4. **Verdicts in memory** (0 queries).
   Per source entry, call the unchanged `resolveUsageEntry()`. For entry-type
   sources the fallback-mode and strict-mode verdicts are provably identical
   (both reduce to `resolveUsageEntry(...) instanceof Entry`), so one result
   primes both `fallbackSourceVerdictCache` and `strictSourceVerdictCache`.

**Integration** (2 call sites): `getFallbackRelationUsageIds` and the strict
branch of `getResolvedRelationUsageIds` call the prime method once after their
relations query, before their (unchanged) per-source loop. Popover/detail
paths (`getRelationUsageRecords`, ...) are untouched.

## Kill switch

Config-only flag (no CP setting):

- `config/asset-cleaner.php` → `'bulkRelationResolution' => false`, or
- env var `ASSET_CLEANER_BULK_RELATION_RESOLUTION=false` (pattern follows the
  existing `ASSET_CLEANER_INCLUDE_DRAFTS` config helpers).

Default: **enabled**. Disabling skips priming entirely → behavior is
byte-for-byte the current (post-cache) behavior. Rollback requires no
deploy — set the env var and restart the queue.

## Verification (no test suite exists)

1. **Parity console command**
   `php craft asset-cleaner/diagnostics/relation-parity --volumeId=X --limit=500`
   Samples assets, computes verdict maps with priming forced off then on
   (resetting caches in between), diffs, prints mismatching source IDs,
   exits non-zero on any mismatch. Run against a real content database
   before enabling by default.
2. **Debug log per bulk pass**: primed count, left-to-legacy count, per-tier
   hit counts — makes field diagnosis possible from logs alone.

## Safety invariants (added after review)

Two invariants now hold in the codebase and MUST be preserved by the bulk
implementation:

1. **All-sites usage**: an asset used in ANY site's content counts as used.
2. **No per-user verdicts**: the provisional-draft creator filter was removed
   from scan verdicts (see CHANGELOG). `initiatingUserId` no longer influences
   any verdict.

## Parity risk status: eliminated (not just mitigated)

The original plan's main technical risk — the bulk loader feeding subtly
different Entry objects into the shared policy code — no longer exists:

- **Draft creator**: the verdict no longer reads `draftCreatorId` at all
  (creator comparison removed, helpers deleted). A field no code reads cannot
  cause divergence. The bulk loader needs no drafts-table priming.
- **Multi-site row choice**: everything the relation verdict consults —
  `revisionId`, `draftId`, provisional flag, `sectionId`, `ownerId` — is an
  element-level attribute, identical on every site's row of the same element.
  Whichever site row `->one()` or `->unique()` returns, the verdict is the
  same by construction.

The parity console command is therefore a regression tripwire rather than a
prerequisite: its job is to catch anyone LATER adding a site-dependent or
user-dependent input to the verdict, which would break the proof above.
Guard this in review: any new verdict input must be element-level
(site-independent) and user-independent, or the bulk loader must be updated
in the same change.

## Craft 5 API points to confirm during implementation

- `Entry::setOwner()` signature (NestedElementTrait) in Craft 5.0.
- `->unique()` + default `preferSites` row choice vs `->one()` on multi-site
  installs (tier parity).

The parity command catches these empirically.

## Expected effect

Per 2000-asset window with *S* unique entry sources:

| | queries |
|---|---|
| today (with per-execution caches) | ~S × (2–7) |
| with bulk priming | flat ≤ ~50 (+ small non-entry residue) |

## Order of work

1. Tier loader + classify helpers (pure additions, dark code).
2. Owner closure + `setOwner` wiring.
3. `primeSourceVerdictsInBulk` + 2 integration calls + config flag.
4. Diagnostics command, CHANGELOG, README note for the flag.

~250–300 lines, almost entirely in `RelationUsageResolver`. Steps 1–3 can
merge dark (flag off) if desired; flip the default after the parity command
passes on a production-like database.
