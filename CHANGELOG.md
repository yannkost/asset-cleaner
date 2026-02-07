# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
