<?php

declare(strict_types=1);

namespace yann\assetcleaner\migrations;

/**
 * Creates the complete scan storage schema, regardless of the selected storage mode.
 */
class Install extends m260907_120000_repair_scan_storage
{
    /**
     * Remove plugin-owned scan storage when the plugin is uninstalled.
     */
    public function safeDown(): bool
    {
        $this->dropTableIfExists('{{%assetcleaner_scan_results}}');
        $this->dropTableIfExists('{{%assetcleaner_scan_usedassets}}');
        $this->dropTableIfExists('{{%assetcleaner_scan_assets}}');
        $this->dropTableIfExists('{{%assetcleaner_scans}}');

        return true;
    }
}
