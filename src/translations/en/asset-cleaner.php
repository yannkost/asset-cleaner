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
];
