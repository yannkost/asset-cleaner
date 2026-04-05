# Release Notes — 1.3.2

## Summary

This release focuses on reliability for large asset volumes and better clarity in usage reporting.

The main goals of `1.3.2` are:
- make large scans more resilient by splitting relation scanning into resumable batches
- improve scan progress reporting so long-running scans show meaningful asset counters
- include user profile picture references from the `users.photoId` column in usage detection
- make the usage modal easier to understand for drafts, revisions, and user photo usage

## Highlights

### More reliable large-volume scans
Relation scanning now runs in resumable queue batches instead of one long relation pass.

This helps reduce failures on very large volumes where the relation stage could previously exceed the queue worker timeout.

Additional hardening was added so stale relation batches are less likely to overwrite newer scan progress or later scan stages.

### Better scan progress reporting
The scan progress UI now updates the asset counter during long-running scans instead of leaving it at `0` while the percentage changes.

This makes it much easier to understand whether a scan is actively moving forward on large volumes.

### User profile pictures now count as asset usage
Asset usage detection now includes references stored in the `users.photoId` column.

That means assets used as user profile pictures are now:
- counted as used during scans
- included in direct usage checks
- shown in the usage modal

### Clearer usage modal details
The asset usage modal now derives draft and revision context directly from the displayed entry URLs.

This makes it easier to identify when an asset is used by:
- a draft
- a revision

User photo usage is also labeled more clearly as:

- `User profile picture`

instead of the more generic `User`.

## Changed
- Improved the asset usage modal so draft and revision usage can be identified directly from the displayed entry links
- Renamed user photo usage labels in the usage modal to `User profile picture` for clearer presentation

## Fixed
- Split large relation scans into resumable queue batches so high-volume scans are less likely to exceed worker timeouts
- Hardened relation scan progress tracking so stale queued relation batches are less likely to overwrite later-stage scan state
- Fixed the scan progress display so the asset counter advances during long-running scans instead of remaining at `0`
- Counted asset references from the `users.photoId` column as usage during scans and usage checks

## Upgrade Notes
No special migration steps are required for this release.

## Version
- Previous: `1.3.1`
- Current: `1.3.2`
