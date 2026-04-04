# Next Steps for `bugfix/standardize-logging`

This file is a handoff note for a **new clean session**.

## Goal

Continue work on the `bugfix/standardize-logging` branch with three follow-up tasks:

1. **Split `AssetUsageService` into smaller focused services**
2. **Remove temporary debug logs that were only added for investigation**
3. **Fix the field-handle / field-layout issue in asset usage checks**

The current session grew too large, so this file should give enough context to resume safely.

---

## Current Branch

- Branch: `bugfix/standardize-logging`

## Important recent commits on this branch

These are the key commits already on the branch:

- `a0331e0` — Standardize logging and reduce scan lookup memory
- `00dd6d5` — Fix fallback relation usage for drafts and revisions
- `bf63650` — Add debug logging for fallback relation resolution
- `3a7ed71` — Resolve relation sources across sites and owner states
- `79a699e` — Exclude trashed relation sources from fallback usage
- `908f1fe` — Log fallback relation record decisions
- `1c9dac5` — Ignore trashed relation sources in usage queries

---

## What was fixed already

### 1. Logging consistency improvements
A dedicated plugin logger already exists and writes to:

- `storage/logs/asset-cleaner_YYYY-MM-DD.log`

This branch already improved logging consistency in a number of places.

### 2. Scan memory improvement
The content scan lookup representation was compacted so path and filename lookups do not store full arrays of matching IDs anymore.

### 3. Relation fallback draft/revision bug
The relation fallback logic was buggy.

Problem:
- `Alle relationalen Verweise als Nutzung zählen` counted some assets as used even when they were only referenced by drafts/revisions and the toggles for drafts/revisions were off.

Key finding:
- several problematic relation source IDs were actually **trashed elements** in the `elements` table
- they were being misclassified as generic fallback relation sources

Fix:
- relation usage queries now ignore trashed source elements by joining `relations` to `elements` and requiring:
  - `sourceElements.dateDeleted = null`

Result:
- this finally fixed the observed fallback relation issue

---

## Important confirmed findings

### A. `status(null)` is not enough
It does not include trashed elements.
That was an important discovery during debugging.

### B. The relation source bug was not mainly a draft/revision filter bug anymore
The draft/revision filter was already working for properly resolved revision IDs.

The real issue was:
- some source elements were trashed
- unresolved
- then incorrectly treated as generic fallback usage

### C. `AssetUsageService` has become too large
It now contains too many responsibilities:
- relation usage resolution
- content usage resolution
- entry draft/revision resolution
- field access behavior
- various helpers

This should be split before further fixes.

---

## Remaining tasks

## Task 1 — Split `AssetUsageService`

### Why
The file has become too big and too hard to reason about safely.

### Suggested split
The exact naming can vary, but the responsibilities should be separated roughly like this:

#### `EntryUsageResolver`
Responsible for:
- resolving whether an entry should count for usage
- draft/revision/provisional draft inclusion rules
- resolving top-level entries from nested entry ownership chains
- canonical / owner / draft creator related entry logic

Likely methods to move:
- `resolveUsageEntry()`
- `shouldIncludeEntryForUsage()`
- draft creator helper logic
- top-level/owner traversal helpers
- safe section helpers

#### `RelationUsageService` or `RelationUsageResolver`
Responsible for:
- relation table lookups
- fallback relation logic
- source resolution for relation records
- generic relation records

Likely methods to move:
- `getResolvedRelationUsageIds()`
- `getRelationUsageRecords()`
- fallback relation methods
- relation source entry resolution
- relation source raw ancestry logic
- relation source owner/canonical table helpers

#### `ContentUsageService` or `ContentUsageResolver`
Responsible for:
- rich text / content scanning
- entry content usage checks
- global set content usage checks
- asset filename/path/content matching
- content index handling if still needed

Likely methods to move:
- `findAssetInContent()`
- `buildContentIndex()`
- `buildGlobalsIndex()`
- content reference extraction helpers
- path/filename matching helpers

### Constraints
- Do not change behavior while splitting.
- Keep public API compatibility from the plugin/service layer if possible.
- Prefer a refactor that mainly moves methods and introduces composition rather than changing all call sites at once.

---

## Task 2 — Remove temporary debug logs

### Why
Many debug log lines were added only to diagnose the fallback relation bug.
Now that the issue is understood and fixed, the log is too noisy.

### Keep
Keep logs that are operationally useful:
- actual warnings
- actual exceptions
- meaningful info logs around failures or skipped processing
- file/storage scan diagnostics that help in production debugging

### Remove
Remove temporary investigation logs such as:
- detailed branch-by-branch fallback relation source resolution traces
- repeated relation-source lookup debug messages
- temporary decision logs that were only useful while chasing source IDs like `841`, `844`, etc.

### Goal
After cleanup, the dedicated log should be useful in production again and not flood with internal debugging details.

---

## Task 3 — Fix field-handle / field-layout issue

### Problem
The asset usage check currently logs many warnings like:

- `Invalid field handle: redactor`
- `Invalid field handle: richtext`
- `Invalid field handle: testRichText`

This indicates that the current content usage resolution is trying to access field handles that are not actually valid for the current entry context.

### Important product insight
This is not just a typo issue.

Entry type / field layout context can vary, and the same conceptual field can appear differently depending on the element context. Also, relying on a broad global field list and then calling `getFieldValue()` blindly is too aggressive.

### What should happen instead
The plugin should only inspect fields that are actually valid for the current element context.

### Recommended fix approach
Do **not** just guess alternate field handles.

Instead:
1. inspect the actual field layout/custom fields for the concrete entry being checked
2. only use field instances that really belong to that entry context
3. only call `getFieldValue()` for fields present in that actual context

This should solve both:
- invalid field-handle warnings
- context-specific field availability problems

### Good implementation direction
When checking an entry:
- get the entry’s actual field layout
- iterate that layout’s custom fields
- filter to HTML-capable fields
- read only those fields

This is better than:
- collecting all possible HTML fields globally
- then trying them against every entry

### Note
This field issue is important, but it should be tackled **after** the service split and log cleanup, because that will make the implementation much safer.

---

## Recommended order of work

### Step 1
Refactor / split `AssetUsageService` into smaller focused services without changing behavior.

### Step 2
Remove temporary debug logs that were only added during relation fallback debugging.

### Step 3
Fix the field-handle / field-layout issue using actual per-entry field layouts instead of a broad global field list.

---

## Recommended commit structure

Use separate commits for clarity:

1. **Refactor**: split `AssetUsageService` into smaller services
2. **Cleanup**: remove temporary debug logging
3. **Fix**: use actual field layout fields for content usage resolution

This will make review and rollback much easier.

---

## Things to be careful about

### Relation usage behavior
Do not regress the recent fallback relation fix:
- trashed relation sources must not count as usage
- they must not appear as generic fallback relation records

### Draft/revision behavior
Do not regress:
- revisions excluded when `includeRevisions` is off
- drafts excluded when `includeDrafts` is off

### Scan performance
Do not undo the recent scan memory improvements in the lookup structures.

---

## Helpful mental model

- **Relation usage** = what the `relations` table says, filtered by real source validity
- **Content usage** = what rich text / content scanning finds
- **Entry usage resolution** = whether a found source should count under draft/revision policy

Those three concepts should become separate code units.

---

## Session objective for the next clean run

A successful next session should:

- leave `bugfix/standardize-logging` cleaner and easier to maintain
- reduce debug noise
- preserve the fixed relation fallback behavior
- fix invalid field-handle warnings by using actual field layout context

---