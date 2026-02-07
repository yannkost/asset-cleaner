# Asset Cleaner for Craft CMS

Identify and clean up unused assets in Craft CMS 5.

## Features

- **View Usage Button** - See where any asset is used directly from the asset edit page
- **Utilities Page** - Scan volumes for unused assets with bulk actions
- **Per-Volume Results** - Results grouped by volume with individual file counts and total sizes
- **Export Options** - Download CSV or ZIP of unused assets with smart filenames
- **Folder Structure Option** - Choose to preserve folder structure or flatten files in ZIP downloads
- **Soft Delete (Trash)** - Move assets to trash for safe removal with recovery option
- **Permanent Delete** - Permanently delete assets with double confirmation
- **Progress Indicator** - Visual progress bar during scanning
- **Memory-Efficient** - Handles large files (10GB+) without memory issues
- **CLI Commands** - Scan, export, and delete from the command line

## Requirements

- Craft CMS 5.0.0 or later
- PHP 8.2 or later

## Installation

### Via Composer (recommended)

```bash
composer require yannkost/craft-asset-cleaner
php craft plugin/install asset-cleaner
```

### Manual Installation

1. Copy the `asset-cleaner` folder to your `plugins/` directory
2. Add to your `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "plugins/asset-cleaner"
        }
    ]
}
```

3. Run:

```bash
composer require yannkost/craft-asset-cleaner
php craft plugin/install asset-cleaner
```

## Usage

### Control Panel

1. Navigate to **Utilities → Asset Cleaner**
2. Select the volumes you want to scan
3. Click **Scan Now**
4. Review the results grouped by volume, showing:
   - Number of unused assets per volume
   - Total file size per volume
5. Use bulk actions (global or per-volume):
   - **Download CSV** - Export list of unused assets
   - **Download ZIP** - Download unused assets (with folder structure option)
   - **Put into Trash** - Soft delete assets (recoverable)
   - **Delete Permanently** - Permanently remove assets (with double confirmation)

### ZIP Download Options

When downloading a ZIP file, you can choose:
- **Flat** - All files in the ZIP root
- **Preserve folder structure** - Files organized by `volume-handle/folder/path/filename`

### Smart Filenames

Exported files include meaningful names with timestamps:
- CSV: `unused-assets_volume-name_2024-01-15_14-30-00.csv`
- ZIP: `unused-assets_volume1__volume2_2024-01-15_14-30-00.zip`

### View Usage Button

On any asset edit page, click the **View Usage** button (eye icon) to see:
- Entries using the asset via Asset fields
- Entries referencing the asset in Redactor/CKEditor fields

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

The plugin identifies unused assets by performing comprehensive checks across your Craft CMS site:

1. **Relations Table** - Asset field references in entries, globals, categories, and other elements stored in Craft's relations table
2. **Content Fields** - Scans Redactor, CKEditor, and PlainText fields for embedded asset URLs, filenames, and paths
3. **Global Sets** - Checks global set content fields for asset references
4. **Nested Entries** - Resolves Matrix blocks and other nested entries to their parent entries for accurate reporting

The plugin uses multiple search patterns to ensure accurate detection:
- Asset filenames
- Full asset URLs
- Relative URL paths
- Folder paths + filenames
- `data-asset-id` attributes in HTML content

No database migrations are required. All queries are performed directly against Craft's existing tables.

## Technical Details

### Memory-Efficient ZIP Creation

The plugin uses streaming to handle large files without exhausting PHP memory:
- Files are processed in 8KB chunks
- Temporary files are used instead of loading entire files into memory
- Supports files of any size, limited only by disk space

### Permissions

The Asset Cleaner utility requires the `utility:asset-cleaner` permission. Assign this permission to user groups that should have access to the utility.

## License

The Craft License. See [LICENSE.md](LICENSE.md) for details.

## Support

- [GitHub Issues](https://github.com/yannkost/craft-asset-cleaner/issues)
- [Documentation](https://github.com/yannkost/craft-asset-cleaner)

## Changelog

### 1.0.0
- Initial release
- View Usage button on asset edit pages
- Utility page with volume scanning
- Per-volume result grouping with file counts and total sizes
- CSV and ZIP export with smart filenames
- ZIP folder structure option (flat or preserve)
- Soft delete (trash) and permanent delete with confirmations
- Progress bar during scanning
- Memory-efficient ZIP creation for large files
- CLI commands for scanning, exporting, and deleting
