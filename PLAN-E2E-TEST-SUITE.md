# Plan: End-to-end test suite against the plugins-tester instance

Status: planned — not started. Blocked on nothing; deferred until after the 1.5.0 release.

## Goal

A scenario suite that asserts **exact used/unused asset ID sets** for the queue-based
scan, run against the `plugins-tester` DDEV instance (`/home/yann/craft/plugins-tester`),
where this repo is mounted into the container and symlinked as the installed plugin.

Asset Cleaner's failure mode is a wrong "unused" verdict leading to deletion of a
used asset. The verdict logic is where complexity accumulates (multi-site scanning,
provisional drafts, bulk relation resolution) — every recent bug lived there. This
suite pins the end-to-end verdict contract so refactors and perf work can't silently
change it.

## Architecture

Three layers, all living in this repo under `tests/e2e/` (git-ignored, see below):

1. **Fixture layer — MCP API curls.** The tester's `project-extension` plugin exposes
   an HTTP API (`POST /api/mcp/...`, `MCP_KEY` in the JSON body, docs in
   `plugins-tester/mcp-documentation/`) that can build everything from scratch:
   filesystems, volumes, generated gradient-SVG assets, sections, entry types, field
   layouts (assets / entries / matrix / ckeditor fields), and entry CRUD. All create
   endpoints are idempotent (existing handle → update).
   Each scenario builds its own world — own volume, own section, prefixed handles
   (e.g. `t_<scenario>_`) — and the scan is **scoped to that volume**, so the
   instance's manually-created content never interferes with assertions.

2. **Scan driver.** Extend `plugins-tester/scripts/run-asset-scan.php` (written
   2026-07-18): bootstraps Craft console, calls `ScanService::initializeScan()` with
   explicit options, pushes `ScanSetupJob`, then `php craft queue/run`, then dumps
   `{status, usedCount, unusedCount, unusedIds}` as JSON for assertion.

3. **Runner + assertions.** A thin bash (or PHP) runner per scenario:
   fixtures → scan → diff actual vs expected ID sets → cleanup. Plus one test that
   simply runs `php craft asset-cleaner/diagnostics/relation-parity` in all variants
   and asserts exit code 0.

## Missing fixture capability: drafts/revisions/trash

The MCP API has **no** draft/revision/provisional endpoints — exactly the scenarios
`includeDrafts`/`includeRevisions` and the initiating-user logic exist for. Some fall
out for free (updating an entry creates a revision; deleting one gives a trashed
source). The rest need **3–4 new endpoints in `project-extension`** (to be created,
following the existing `McpBaseController` pattern):

- `POST /api/mcp/entries/create-draft` — draft of an existing entry (or standalone
  draft), with field/relation values.
- `POST /api/mcp/entries/create-provisional-draft` — provisional draft **as a given
  user** (`userId`/`username` param), to cover the "other editor's WIP" fix from
  `b552559`.
- `POST /api/mcp/entries/restore` — restore a soft-deleted entry (delete already
  exists; restore closes the trash round-trip).
- Optional: `POST /api/mcp/entries/apply-draft` — apply a draft, which both removes
  the draft and creates a revision in one step.

## Scenario matrix

Each fixture world is scanned under every settings combination that should change its
verdict — assert the full ID set each time:

| Axis | Values |
|---|---|
| `includeDrafts` | on / off |
| `includeRevisions` | on / off |
| `countAllRelationsAsUsage` | true (fallback) / false (strict) |
| `ASSET_CLEANER_BULK_RELATION_RESOLUTION` | on / off (bulk-off = continuous legacy-path parity) |

Core scenario worlds:

- unrelated asset (baseline unused)
- asset related from a live entry
- asset related only from a draft / only from a provisional draft (other user) /
  only from a revision / only from a trashed entry
- asset related from a nested Matrix entry (owner resolution)
- asset referenced only in CKEditor content (URL and `{asset:ID:url}` ref tag),
  including only on the secondary (French) site — covers the multi-site fix
- asset used as a user photo (`users.photoId`)
- volume filtering: used asset in volume A, scan scoped to volume B
- resumable re-queue: small `assetChunkSize` (note: effective relation batch size is
  `max(assetChunkSize, relationBatchSize)` — the env var alone cannot force batches
  below the chunk size)
- both scan storage modes (file / db)

## Constraints and gotchas (learned 2026-07-18)

- `initializeScan()` calls `clearRetainedScans()` — only one retained scan at a time.
  Scenarios must run **strictly sequentially** and read results before the next scan.
- Queue is drained explicitly via `ddev exec php craft queue/run` — never rely on the
  web-request queue runner.
- Bulk-priming marker in `storage/logs/asset-cleaner_<date>.log`:
  `Primed relation source verdicts in bulk. {requested, primed, leftToPerSourcePath}`.
  Tests asserting the bulk path ran should grep this rather than infer from timing.
- The plugin log timestamps are not in the container's timezone — don't assert on them.

## Repo policy

- Suite lives in `tests/` in this repo but is **excluded via `.gitignore`** — it
  depends on the local plugins-tester instance and is not shipped or run in CI (yet).
- Later CI path: the MCP API can build the entire world from an empty Craft install,
  so a `craftcms/craft` container + project-extension + these fixtures could
  bootstrap from zero. That is the argument for HTTP fixtures over direct-PHP ones.
- Unit-level coverage (verdict policy functions, store serialization) is explicitly
  out of scope here; those deserve plain PHPUnit tests in-repo at some point.
