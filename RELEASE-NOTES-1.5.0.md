# Release Notes — 1.5.0

This release makes large scans dramatically faster and more resilient, and fixes two correctness issues that could cause used assets to be reported as unused.

## Scan performance

- **Bulk relation resolution.** Relation sources are now classified, loaded, and policy-checked with a handful of set-based queries per batch instead of several queries per source, reducing scan queries by orders of magnitude on relation-heavy sites. If needed, fall back to the original per-source path with `'bulkRelationResolution' => false` in `config/asset-cleaner.php` or `ASSET_CLEANER_BULK_RELATION_RESOLUTION=false`, and verify parity on your install with `php craft asset-cleaner/diagnostics/relation-parity`.
- **Per-execution caching.** Relation usage verdicts and entry lookups are cached for the duration of a queue execution, so repeated relation sources and shared owners in nested-element chains (Matrix, CKEditor) are only resolved once.
- **New "Relation batch size" setting** — lower how many assets each relation scan queue execution loads on sites with heavy or deeply nested relations. Overridable via `relationBatchSize` in `config/asset-cleaner.php` or `ASSET_CLEANER_RELATION_BATCH_SIZE`. Note the effective batch size is never lower than the scan's asset chunk size.
- **New "Relation time budget" setting** — the relation stage now stops and re-queues once a wall-clock budget is reached, keeping each queue execution safely under the queue's time-to-reserve (TTR). This fixes `ScanRelationsJob` failures with "exceeded the timeout of 300 seconds" on large or heavily related asset libraries. Overridable via `relationTimeBudgetSeconds` in `config/asset-cleaner.php` or `ASSET_CLEANER_RELATION_TIME_BUDGET`.

## Correctness fixes

- **Multi-site content is now fully scanned.** Content scanning previously only inspected the primary site's field content, so an asset referenced only in another site's CKEditor/Redactor content could be wrongly reported as unused.
- **Provisional drafts now count as usage for every user.** When "include drafts" is enabled, scans previously only counted provisional drafts belonging to the user who started the scan, so assets referenced in other editors' work-in-progress could be wrongly reported as unused.
