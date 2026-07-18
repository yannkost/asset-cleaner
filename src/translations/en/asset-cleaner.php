<?php

return [
    // General
    'Asset Cleaner' => 'Asset Cleaner',
    'An error occurred.' => 'An error occurred.',
    'Loading...' => 'Loading...',
    
    // View Usage
    'View Usage' => 'View Usage',
    'Used by Entries' => 'Used by Entries',
    'Used in Content Fields' => 'Used in Content Fields',
    'This asset is not used anywhere.' => 'This asset is not used anywhere.',
    
    // Utility Page
    'Scan Now' => 'Scan Now',
    'Select Volumes' => 'Select Volumes',
    'Select All' => 'Select All',
    'Results' => 'Results',
    'Used Assets' => 'Used Assets',
    'Unused Assets' => 'Unused Assets',
    'Scanning...' => 'Scanning...',
    
    // Bulk Actions
    'Bulk Actions' => 'Bulk Actions',
    'Bulk Actions (All Volumes)' => 'Bulk Actions (All Volumes)',
    'Bulk Actions - All Selected Volumes' => 'Bulk Actions - All Selected Volumes',
    'Download CSV' => 'Download CSV',
    'Download ZIP' => 'Download ZIP',
    'Put into Trash' => 'Put into Trash',
    'Delete Permanently' => 'Delete Permanently',
    
    // Table Headers
    'Title' => 'Title',
    'Filename' => 'Filename',
    'Volume' => 'Volume',
    'Size' => 'Size',
    'Path' => 'Path',
    'Date Created' => 'Date Created',
    
    // Results warning
    'Before permanently deleting' => 'Before permanently deleting',
    'We recommend downloading a ZIP backup of the assets you plan to remove first, or using "Put into Trash" as a safer alternative. Permanent deletions cannot be undone.' => 'We recommend downloading a ZIP backup of the assets you plan to remove first, or using "Put into Trash" as a safer alternative. Permanent deletions cannot be undone.',
    'Scan older than 24h — results may be outdated' => 'Scan older than 24h — results may be outdated',

    // Messages
    'No assets selected.' => 'No assets selected.',
    'No assets found.' => 'No assets found.',
    'Could not create ZIP file.' => 'Could not create ZIP file.',
    'No volumes selected.' => 'No volumes selected.',
    
    // ZIP Download Dialog
    'ZIP Download Options' => 'ZIP Download Options',
    'How would you like to organize the files in the ZIP?' => 'How would you like to organize the files in the ZIP?',
    'Flat (all files in root)' => 'Flat (all files in root)',
    'Preserve folder structure' => 'Preserve folder structure',
    'Cancel' => 'Cancel',
    'ZIP download initiated. Large files may take several minutes to prepare.' => 'ZIP download initiated. Large files may take several minutes to prepare.',
    'Preparing ZIP file... This may take several minutes for large files. Please wait.' => 'Preparing ZIP file... This may take several minutes for large files. Please wait.',
    
    // Trash/Delete Messages
    'Are you sure you want to move {count} assets to trash?' => 'Are you sure you want to move {count} assets to trash?',
    'Moved {count} assets to trash.' => 'Moved {count} assets to trash.',
    'Permanently deleted {count} assets.' => 'Permanently deleted {count} assets.',
    'WARNING: You are about to permanently delete assets.' => 'WARNING: You are about to permanently delete assets.',
    'This action CANNOT be undone!' => 'This action CANNOT be undone!',
    'We strongly recommend downloading the unused assets as a backup before proceeding.' => 'We strongly recommend downloading the unused assets as a backup before proceeding.',
    'Are you absolutely sure you want to permanently delete these assets?' => 'Are you absolutely sure you want to permanently delete these assets?',
    'Final confirmation: Permanently delete assets? This CANNOT be undone!' => 'Final confirmation: Permanently delete assets? This CANNOT be undone!',
    'Are you sure you want to permanently delete {count} assets? This action CANNOT be undone! Download a backup (CSV or ZIP) before proceeding.' => 'Are you sure you want to permanently delete {count} assets? This action CANNOT be undone! Download a backup (CSV or ZIP) before proceeding.',
    'Final confirmation: Permanently delete {count} assets? This CANNOT be undone!' => 'Final confirmation: Permanently delete {count} assets? This CANNOT be undone!',
    
    // Volume Section
    'unused assets' => 'unused assets',
    '{count} unused assets — {size}' => '{count} unused assets — {size}',
    'No unused assets found.' => 'No unused assets found.',
    'No assets selected in this volume.' => 'No assets selected in this volume.',
    
    // Errors
    'Failed to scan volumes.' => 'Failed to scan volumes.',
    'Failed to export CSV.' => 'Failed to export CSV.',
    'Failed to create ZIP file.' => 'Failed to create ZIP file.',
    'Failed to move assets to trash.' => 'Failed to move assets to trash.',
    'Failed to delete assets.' => 'Failed to delete assets.',
    'Failed to get asset usage.' => 'Failed to get asset usage.',

    // Queue Scan
    'Scan queued...' => 'Scan queued...',
    'Scan failed.' => 'Scan failed.',
    'Lost contact while polling scan progress. The scan may still be running.' => 'Lost contact while polling scan progress. The scan may still be running.',
    'Scanning assets for usage' => 'Scanning assets for usage',
    'The queue does not appear to be running. Make sure a queue worker is active (e.g. php craft queue/listen).' => 'The queue does not appear to be running. Make sure a queue worker is active (e.g. php craft queue/listen).',

    // Scan Time
    'Scanned on {date}' => 'Scanned on {date}',
    'Restoring last scan...' => 'Restoring last scan...',

    // Usage Dialog / Scan Options
    'Check Asset Usage' => 'Check Asset Usage',
    'Choose how usage should be evaluated for this asset.' => 'Choose how usage should be evaluated for this asset.',
    'Choose the usage options you want to check, then confirm.' => 'Choose the usage options you want to check, then confirm.',
    'Include drafts' => 'Include drafts',
    'Include revisions' => 'Include revisions',
    'Count all relational references as usage' => 'Count all relational references as usage',
    'Recommended for projects with plugin-defined or unknown element types that may store asset relations outside normal entry content.' => 'Recommended for projects with plugin-defined or unknown element types that may store asset relations outside normal entry content.',
    'Check Usage' => 'Check Usage',
    'Used by Relational Elements' => 'Used by Relational Elements',
    'Other Relational Elements' => 'Other Relational Elements',
    'Relational element #{id}' => 'Relational element #{id}',
    'Relational element' => 'Relational element',
    'Include drafts in this scan' => 'Include drafts in this scan',
    'When enabled, assets referenced only in drafts may be treated as used.' => 'When enabled, assets referenced only in drafts may be treated as used.',
    'Include revisions in this scan' => 'Include revisions in this scan',
    'When enabled, assets referenced only in revisions may be treated as used.' => 'When enabled, assets referenced only in revisions may be treated as used.',
    'Count all relational references as usage' => 'Count all relational references as usage',
    'When enabled, any row in Craft’s relations table will cause an asset to be treated as used, including references created by plugin-defined or unknown element types. Disable this for a stricter scan.' => 'When enabled, any row in Craft’s relations table will cause an asset to be treated as used, including references created by plugin-defined or unknown element types. Disable this for a stricter scan.',

    // Settings - Scan performance
    'Scan performance' => 'Scan performance',
    'Relation batch size' => 'Relation batch size',
    'Maximum number of assets loaded for relation scanning per queue execution. Lower this (e.g. to 500) on sites with heavy or deeply nested relations if scan jobs time out. You can also override this with `relationBatchSize` in `config/asset-cleaner.php` or the `ASSET_CLEANER_RELATION_BATCH_SIZE` environment variable.' => 'Maximum number of assets loaded for relation scanning per queue execution. Lower this (e.g. to 500) on sites with heavy or deeply nested relations if scan jobs time out. You can also override this with `relationBatchSize` in `config/asset-cleaner.php` or the `ASSET_CLEANER_RELATION_BATCH_SIZE` environment variable.',
    'Relation time budget (seconds)' => 'Relation time budget (seconds)',
    'Wall-clock budget for the relation stage of a single queue execution. Once exceeded, the job stops and re-queues to continue, keeping each execution safely under the queue’s time-to-reserve (TTR, 300s by default). Keep this comfortably below your TTR. You can also override this with `relationTimeBudgetSeconds` in `config/asset-cleaner.php` or the `ASSET_CLEANER_RELATION_TIME_BUDGET` environment variable.' => 'Wall-clock budget for the relation stage of a single queue execution. Once exceeded, the job stops and re-queues to continue, keeping each execution safely under the queue’s time-to-reserve (TTR, 300s by default). Keep this comfortably below your TTR. You can also override this with `relationTimeBudgetSeconds` in `config/asset-cleaner.php` or the `ASSET_CLEANER_RELATION_TIME_BUDGET` environment variable.',

    // Settings page, queue job descriptions, scan stage labels, usage labels
    'Scan storage mode' => 'Scan storage mode',
    'File-based' => 'File-based',
    'Database-based' => 'Database-based',
    'Choose how Asset Cleaner stores transient scan state. File-based storage is the default and works well when web and queue workers share a filesystem. Database-based storage is better suited for containerized or cloud-style environments where shared filesystem access is not guaranteed.' => 'Choose how Asset Cleaner stores transient scan state. File-based storage is the default and works well when web and queue workers share a filesystem. Database-based storage is better suited for containerized or cloud-style environments where shared filesystem access is not guaranteed.',
    'File-based scan workspace path' => 'File-based scan workspace path',
    'Optional. Only used when scan storage mode is set to File-based. Defaults to `@storage/asset-cleaner`. You can also override this with `scanWorkspacePath` in `config/asset-cleaner.php` or the `ASSET_CLEANER_SCAN_PATH` environment variable.' => 'Optional. Only used when scan storage mode is set to File-based. Defaults to `@storage/asset-cleaner`. You can also override this with `scanWorkspacePath` in `config/asset-cleaner.php` or the `ASSET_CLEANER_SCAN_PATH` environment variable.',
    'Include drafts by default' => 'Include drafts by default',
    'When enabled, assets referenced only in drafts may be treated as used during scans. This value acts as the default for new scans and can be overridden per scan from the utility page. You can also override this with `includeDraftsByDefault` in `config/asset-cleaner.php` or the `ASSET_CLEANER_INCLUDE_DRAFTS` environment variable.' => 'When enabled, assets referenced only in drafts may be treated as used during scans. This value acts as the default for new scans and can be overridden per scan from the utility page. You can also override this with `includeDraftsByDefault` in `config/asset-cleaner.php` or the `ASSET_CLEANER_INCLUDE_DRAFTS` environment variable.',
    'Include revisions by default' => 'Include revisions by default',
    'When enabled, assets referenced only in revisions may be treated as used during scans. This value acts as the default for new scans and can be overridden per scan from the utility page. You can also override this with `includeRevisionsByDefault` in `config/asset-cleaner.php` or the `ASSET_CLEANER_INCLUDE_REVISIONS` environment variable.' => 'When enabled, assets referenced only in revisions may be treated as used during scans. This value acts as the default for new scans and can be overridden per scan from the utility page. You can also override this with `includeRevisionsByDefault` in `config/asset-cleaner.php` or the `ASSET_CLEANER_INCLUDE_REVISIONS` environment variable.',
    'Notes:' => 'Notes:',
    'Only the latest scan is retained for restore/export workflows.' => 'Only the latest scan is retained for restore/export workflows.',
    'When using File-based storage in multi-container setups, make sure the configured workspace path is shared between web and queue workers.' => 'When using File-based storage in multi-container setups, make sure the configured workspace path is shared between web and queue workers.',
    'Config file values override these control panel settings.' => 'Config file values override these control panel settings.',
    'Draft and revision handling can be configured globally here and overridden per scan from the utility page.' => 'Draft and revision handling can be configured globally here and overridden per scan from the utility page.',
    'Preparing asset scan' => 'Preparing asset scan',
    'Scanning asset relations' => 'Scanning asset relations',
    'Scanning content for asset references' => 'Scanning content for asset references',
    'Finalizing asset scan results' => 'Finalizing asset scan results',
    'Preparing asset snapshot...' => 'Preparing asset snapshot...',
    'Scanning relations...' => 'Scanning relations...',
    'Scanning content...' => 'Scanning content...',
    'Finalizing results...' => 'Finalizing results...',
    'User profile picture' => 'User profile picture',
    'User #{id}' => 'User #{id}',
    'Relational source #{id}' => 'Relational source #{id}',
    'Used by relational element #{id}' => 'Used by relational element #{id}',
];
