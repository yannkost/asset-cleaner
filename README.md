# Asset Cleaner for Craft CMS

Identify and clean up unused assets in Craft CMS 5.

## Features

- **View Usage Button** - See where any asset is used directly from the asset edit page
- **Utilities Page** - Scan selected volumes for unused assets with bulk actions
- **Staged Background Scanning** - Scans run as chained queue jobs with dedicated setup, relations, content, and finalize stages
- **File-Backed Scan State** - Long-running scans store progress and intermediate data under `@storage/asset-cleaner/scans/<scanId>` instead of relying on volatile cache entries
- **Large Library Friendly** - Asset snapshots are chunked to disk and content is processed in batches for much better performance on large datasets
- **Per-Volume Results** - Results grouped by volume with individual file counts and total sizes
- **Export Options** - Download CSV or ZIP of unused assets with smart filenames
- **Folder Structure Option** - Choose to preserve folder structure or flatten files in ZIP downloads
- **Soft Delete (Trash)** - Move assets to trash for safe removal with recovery option
- **Permanent Delete** - Permanently delete assets with double confirmation
- **Progress Indicator** - Visual progress bar during scanning, including stage-aware progress messages
- **Memory-Efficient ZIP Creation** - Handles large files without loading them fully into memory
- **CLI Commands** - Scan, export, and delete from the command line

## Requirements

- Craft CMS 5.0.0 or later
- PHP 8.2 or later

## Installation

### Via Composer

```bash
composer require yann/craft-asset-cleaner
php craft plugin/install asset-cleaner
```

### Manual / local path repository

1. Copy the plugin to your `plugins/` directory, for example to `plugins/asset-cleaner`
2. Add a path repository to your project's `composer.json`:

```json
{
  "repositories": {
    "asset-cleaner": {
      "type": "path",
      "url": "plugins/asset-cleaner"
    }
  }
}
```

3. Require the package and install the plugin:

```bash
composer require yann/craft-asset-cleaner
php craft plugin/install asset-cleaner
```

## Usage

### Control Panel

1. Navigate to **Utilities → Asset Cleaner**
2. Select the volumes you want to scan
3. Click **Scan Now**
4. Wait for the background scan to complete
5. Review the results grouped by volume, showing:
   - Number of unused assets per volume
   - Total file size per volume
6. Use bulk actions (global or per-volume):
   - **Download CSV** - Export a list of unused assets
   - **Download ZIP** - Download unused assets, optionally preserving folder structure
   - **Put into Trash** - Soft delete assets
   - **Delete Permanently** - Permanently remove assets with confirmation

### Scan stages

The utility page shows progress while the scan moves through these stages:

1. **Preparing asset snapshot** - Builds the file-backed snapshot of assets in scope
2. **Scanning relations** - Collects asset usage from Craft's relations table
3. **Scanning content** - Scans relevant rich text fields and globals in batches
4. **Finalizing results** - Merges usage data and writes the final unused asset results

### ZIP Download Options

When downloading a ZIP file, you can choose:

- **Flat** - All files in the ZIP root
- **Preserve folder structure** - Files organized by `volume-handle/folder/path/filename`

### Smart Filenames

Exported files include meaningful names with timestamps:

- CSV: `unused-assets_volume-name_2024-01-15_14-30-00.csv`
- ZIP: `unused-assets_volume1__volume2_2024-01-15_14-30-00.zip`

### View Usage Button

On any asset edit page, click the **View Usage** button to see:

- Entries using the asset via Asset fields
- Entries referencing the asset in Redactor/CKEditor fields
- Global sets referencing the asset in supported rich text fields

### CLI Commands

```bash
# Scan all volumes
php craft asset-cleaner/scan

# Scan specific volumes
php craft asset-cleaner/scan --volumes=images,files

# Export unused assets to CSV
php craft asset-cleaner/scan/export

# Export specific volumes
php craft asset-cleaner/scan/export --volumes=images

# Delete unused assets (with confirmation)
php craft asset-cleaner/scan/delete

# Delete without confirmation
php craft asset-cleaner/scan/delete --force

# Dry run (preview only)
php craft asset-cleaner/scan/delete --dry-run
```

## How It Works

The plugin identifies unused assets by performing several checks across your Craft CMS site:

1. **Relations Table** - Asset field references stored in Craft's relations table
2. **Content Fields** - Scans Redactor and CKEditor fields for embedded asset references
3. **Global Sets** - Checks supported global set content fields for asset references
4. **Nested Entries** - Resolves nested entries to top-level entries for usage reporting

For large scans, the plugin uses a file-backed staged pipeline:

1. **Snapshot assets in scope** into chunk files under `@storage/asset-cleaner/scans/<scanId>/assets`
2. **Build usage sets once** from relations and content instead of rescanning all content for every asset
3. **Process entries in batches** rather than loading the full content dataset into memory
4. **Write final results to disk** so long-running scans are not interrupted by cache expiry

The matching strategy prefers stronger signals first:

- `data-asset-id` references
- `#asset:<id>` references
- normalized URL/path matches
- unique filename fallback matches

No database migrations are required. The plugin works against Craft's existing tables and stores transient scan state in the Craft storage folder.

## Technical Details

### File-backed scan workspaces

Each scan stores its transient data under a file-backed workspace. By default, the plugin uses:

```text
@storage/asset-cleaner/scans/<scanId>/
```

If your web and queue processes do not share the same Craft storage path, you can override the workspace base path with either:

- the `ASSET_CLEANER_SCAN_PATH` environment variable
- a `scanWorkspacePath` value in `config/asset-cleaner.php`

Example plugin config:

```php
<?php

return [
    'scanWorkspacePath' => '$ASSET_CLEANER_SCAN_PATH',
];
```

Typical files include:

- `meta.json`
- `progress.json`
- `state.json`
- asset chunk files
- relation/content usage ID files
- final unused asset results

This approach makes large scans more reliable than cache-backed scan state, especially on sites with large asset libraries. For containerized environments, make sure the configured scan workspace path is shared between web and queue workers.

### Memory-efficient ZIP creation

ZIP exports are built in a memory-friendly way:

- Files are streamed in chunks
- Temporary files are used instead of loading entire assets into memory
- Large files can be exported without exhausting PHP memory

### Permissions

The Asset Cleaner utility requires the `utility:asset-cleaner` permission. Assign this permission to user groups that should have access to the utility.

## License

The Craft License. See [LICENSE.md](LICENSE.md) for details.

## Support

- [GitHub Issues](https://github.com/yannkost/asset-cleaner/issues)
- [Source / Documentation](https://github.com/yannkost/asset-cleaner)

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for release notes.