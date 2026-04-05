# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.3.2] - 2026-04-05

### Changed
- Improved the asset usage modal so draft and revision usage can be identified directly from the displayed entry links
- Renamed user photo usage labels in the usage modal to "User profile picture" for clearer presentation

### Fixed
- Split large relation scans into resumable queue batches so high-volume scans are less likely to exceed worker timeouts
- Hardened relation scan progress tracking so stale queued relation batches are less likely to overwrite later-stage scan state
- Fixed the scan progress display so the asset counter advances during long-running scans instead of remaining at `0`
- Counted asset references from the `users.photoId` column as usage during scans and usage checks

## [1.3.1] - 2026-04-04

### Changed
- Split `AssetUsageService` into focused collaborators for entry usage resolution, relation usage resolution, and content usage scanning while preserving the existing public plugin service API
- Standardized plugin logging so operational warnings remain useful without flooding the dedicated log with temporary relation-resolution investigation traces
- Reduced scan lookup memory usage during content scans

### Fixed
- Fixed relation fallback usage checks so draft-only and revision-only relation sources no longer count as usage when those inclusion options are disabled
- Fixed relation fallback handling to ignore trashed relation sources instead of treating them as generic usage
- Fixed relation source resolution across sites and owner states for relation-backed asset usage checks
- Fixed invalid field handle warnings during content usage checks by only reading HTML-capable fields from the concrete element field layout context
- Hardened content field layout discovery so problematic layouts fail safely with warnings instead of breaking scans

## [1.3.0] - 2026-04-03

### Added
- Added configurable scan workspace path support via the `ASSET_CLEANER_SCAN_PATH` environment variable or `scanWorkspacePath` in `config/asset-cleaner.php`
- Added a database-backed scan storage mode for containerized, cloud-style, and multi-worker environments where shared filesystem access is not guaranteed
- Added plugin settings for selecting the scan storage mode and configuring the file-based scan workspace path
- Added default settings for whether draft-only and revision-only asset references should count as used
- Added per-scan utility toggles for including drafts and revisions without changing the global defaults
- Added a per-scan relational fallback option that treats any row in Craft’s relations table as usage, helping preserve assets referenced by plugin-defined or otherwise unknown element types
- Added a configurable asset usage dialog on the asset edit page so draft, revision, and relational fallback behavior can be checked before reviewing usage results
- Added updated translations for the new scan and usage options

### Changed
- Refactored scan persistence into storage backends so the scan coordinator can run against either file-based or database-based storage
- Batched content entry processing during scans to avoid materializing all candidate entries in memory at once
- Hardened scan workspace file writes with stricter validation, readability checks, and better failure handling
- Retained only the latest scan for restore/export workflows while allowing stale queued jobs to exit quietly if their scan has been replaced
- Made draft and revision handling explicit in scan behavior so installations can choose between canonical/live-oriented cleanup and editorial-history-aware cleanup
- Defaulted scans to the safer relational fallback mode while still allowing stricter relation resolution when needed
- Updated the utility and usage UI to expose the new scan-safety controls more clearly

### Fixed
- Added path-aware diagnostics when scan metadata files are missing or unreadable, including resolved storage context details to help debug container and shared-storage setups
- Improved support for container-based installs by allowing scan state to be stored in the database instead of relying on shared local storage

## [1.2.1] - 2026-03-31

### Fixed
- Replaced the cache-backed asset scan flow with a file-backed staged scan pipeline stored under `@storage/asset-cleaner/scans/<scanId>`
- Fixed large scans failing when the cached content index expired before the queue finished processing
- Removed the need to pass the full asset ID list through each queued batch job

### Changed
- Split background scans into dedicated setup, relations, content, and finalize queue stages
- Snapshotted assets into chunk files and resolved usage from relations and content in one pass per scan
- Updated scan progress reporting to reflect the current scan stage
- Restored legacy CSV path formatting for unused asset exports

## [1.0.1] - 2026-02-07

### Added
- Plugin icon with broom and dust particles for control panel and plugin store
- Custom Logger helper class for dedicated error logging to `storage/logs/asset-cleaner_YYYY-MM-DD.log`
- Comprehensive error handling with try/catch blocks in all controller actions
- Translation support for 20 languages: Arabic, Bulgarian, Czech, German, English, Spanish, French, Hungarian, Italian, Japanese, Korean, Dutch, Polish, Portuguese, Romanian, Russian, Slovak, Turkish, Ukrainian, and Chinese

### Changed
- Updated utility icon to use custom SVG icon

## [1.0.0] - 2026-02-07

### Added
- Initial release
- Scan assets across multiple volumes to identify unused assets
- Per-volume results display with separate tables
- Bulk actions: Download CSV, Download ZIP, Move to Trash, Delete Permanently
- ZIP download with folder structure preservation option
- Memory-efficient ZIP creation for large files using chunked processing
- Smart filename generation with snake_case sanitization and timestamps
- Progress bar for scanning operations
- "View Usage" button on asset edit pages showing where assets are used
- Soft delete (trash) and permanent delete options with confirmation dialogs
- Asset usage detection in entry relations and content fields
- Support for Craft CMS 5.0+
