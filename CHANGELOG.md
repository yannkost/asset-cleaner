# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Added configurable scan workspace path support via the `ASSET_CLEANER_SCAN_PATH` environment variable or `scanWorkspacePath` in `config/asset-cleaner.php`

### Changed
- Hardened scan workspace file writes with stricter validation, readability checks, and better failure handling

### Fixed
- Added path-aware diagnostics when scan metadata files are missing or unreadable, including resolved storage context details to help debug container and shared-storage setups

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
