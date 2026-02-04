# Asset Cleaner for Craft CMS

Identify and clean up unused assets in Craft CMS 5.

## Features

- **View Usage Button** - See where any asset is used directly from the asset edit page
- **Utilities Page** - Scan volumes for unused assets with bulk actions
- **Export Options** - Download CSV or ZIP of unused assets
- **Bulk Delete** - Delete unused assets with a single click
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
4. Review the results and use bulk actions:
   - **Download CSV** - Export list of unused assets
   - **Download ZIP** - Download all unused assets as a ZIP file
   - **Delete** - Permanently delete selected assets

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

The plugin identifies unused assets by checking:

1. **Relations Table** - Asset field references stored in Craft's relations table
2. **Content Fields** - Redactor and CKEditor HTML content for embedded asset URLs

No database migrations are required. All queries are performed directly against Craft's existing tables.

## Permissions

The Asset Cleaner utility requires the `utility:asset-cleaner` permission. Assign this permission to user groups that should have access to the utility.

## License

MIT License. See [LICENSE](LICENSE) for details.

## Support

- [GitHub Issues](https://github.com/yannkost/craft-asset-cleaner/issues)
- [Documentation](https://github.com/yannkost/craft-asset-cleaner)

## Changelog

### 1.0.0
- Initial release
