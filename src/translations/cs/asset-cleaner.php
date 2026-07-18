<?php

return [
    // General
    'Asset Cleaner' => 'Čistič souborů',
    'An error occurred.' => 'Došlo k chybě.',
    'Loading...' => 'Načítání...',
    
    // View Usage
    'View Usage' => 'Zobrazit použití',
    'Used by Entries' => 'Používáno položkami',
    'Used in Content Fields' => 'Používáno v polích obsahu',
    'This asset is not used anywhere.' => 'Tento soubor není nikde používán.',
    
    // Utility Page
    'Scan Now' => 'Skenovat nyní',
    'Select Volumes' => 'Vybrat svazky',
    'Select All' => 'Vybrat vše',
    'Results' => 'Výsledky',
    'Used Assets' => 'Používané soubory',
    'Unused Assets' => 'Nepoužívané soubory',
    'Scanning...' => 'Skenování...',
    
    // Bulk Actions
    'Bulk Actions' => 'Hromadné akce',
    'Bulk Actions (All Volumes)' => 'Hromadné akce (Všechny svazky)',
    'Download CSV' => 'Stáhnout CSV',
    'Download ZIP' => 'Stáhnout ZIP',
    'Put into Trash' => 'Přesunout do koše',
    'Delete Permanently' => 'Trvale smazat',
    
    // Table Headers
    'Title' => 'Název',
    'Filename' => 'Název souboru',
    'Volume' => 'Svazek',
    'Size' => 'Velikost',
    'Path' => 'Cesta',
    'Date Created' => 'Datum vytvoření',
    
    // Messages
    'No assets selected.' => 'Nejsou vybrány žádné soubory.',
    'No assets found.' => 'Nebyly nalezeny žádné soubory.',
    'Could not create ZIP file.' => 'Nelze vytvořit ZIP soubor.',
    'No volumes selected.' => 'Nejsou vybrány žádné svazky.',
    
    // ZIP Download Dialog
    'ZIP Download Options' => 'Možnosti stahování ZIP',
    'How would you like to organize the files in the ZIP?' => 'Jak chcete uspořádat soubory v ZIP?',
    'Flat (all files in root)' => 'Plochá struktura (všechny soubory v kořeni)',
    'Preserve folder structure' => 'Zachovat strukturu složek',
    'Cancel' => 'Zrušit',
    'ZIP download initiated. Large files may take several minutes to prepare.' => 'Stahování ZIP zahájeno. Velké soubory mohou trvat několik minut.',
    'Preparing ZIP file... This may take several minutes for large files. Please wait.' => 'Příprava ZIP souboru... Pro velké soubory to může trvat několik minut. Prosím čekejte.',
    
    // Trash/Delete Messages
    'Are you sure you want to move {count} assets to trash?' => 'Opravdu chcete přesunout {count} souborů do koše?',
    'Moved {count} assets to trash.' => '{count} souborů přesunuto do koše.',
    'Permanently deleted {count} assets.' => '{count} souborů trvale smazáno.',
    'WARNING: You are about to permanently delete assets.' => 'VAROVÁNÍ: Chystáte se trvale smazat soubory.',
    'This action CANNOT be undone!' => 'Tuto akci NELZE vrátit zpět!',
    'We strongly recommend downloading the unused assets as a backup before proceeding.' => 'Důrazně doporučujeme stáhnout nepoužívané soubory jako zálohu před pokračováním.',
    'Are you absolutely sure you want to permanently delete these assets?' => 'Jste si absolutně jisti, že chcete tyto soubory trvale smazat?',
    'Final confirmation: Permanently delete assets? This CANNOT be undone!' => 'Finální potvrzení: Trvale smazat soubory? NELZE to vrátit zpět!',
    
    // Volume Section
    'unused assets' => 'nepoužívané soubory',
    'No assets selected in this volume.' => 'V tomto svazku nejsou vybrány žádné soubory.',
    
    // Errors
    'Failed to scan volumes.' => 'Skenování svazků selhalo.',
    'Failed to export CSV.' => 'Export CSV selhal.',
    'Failed to create ZIP file.' => 'Vytvoření ZIP souboru selhalo.',
    'Failed to move assets to trash.' => 'Přesun souborů do koše selhal.',
    'Failed to delete assets.' => 'Smazání souborů selhalo.',
    'Failed to get asset usage.' => 'Získání informací o použití souboru selhalo.',

    // Queue Scan
    'Scan queued...' => 'Skenování ve frontě...',
    'Scan failed.' => 'Skenování selhalo.',
    'Scanning assets for usage' => 'Skenování využití souborů',
    'The queue does not appear to be running. Make sure a queue worker is active (e.g. php craft queue/listen).' => 'Fronta zřejmě neběží. Ujistěte se, že je worker fronty aktivní (např. php craft queue/listen).',

    // Scan Time
    'Scanned on {date}' => 'Naskenováno {date}',
    'Restoring last scan...' => 'Obnovování posledního skenu...',
    // Usage Dialog / Scan Options
    'Check Asset Usage' => 'Zkontrolovat použití assetu',
    'Choose how usage should be evaluated for this asset.' => 'Vyberte, jak má být použití tohoto assetu vyhodnoceno.',
    'Choose the usage options you want to check, then confirm.' => 'Vyberte možnosti použití, které chcete zkontrolovat, a poté potvrďte.',
    'Include drafts' => 'Zahrnout koncepty',
    'Include revisions' => 'Zahrnout revize',
    'Count all relational references as usage' => 'Počítat všechny relační odkazy jako použití',
    'Recommended for projects with plugin-defined or unknown element types that may store asset relations outside normal entry content.' => 'Doporučeno pro projekty s pluginem definovanými nebo neznámými typy prvků, které mohou ukládat vazby na assety mimo běžný obsah záznamů.',
    'Check Usage' => 'Zkontrolovat použití',
    'Used by Relational Elements' => 'Používáno relačními prvky',
    'Other Relational Elements' => 'Další relační prvky',
    'Relational element #{id}' => 'Relační prvek č. {id}',
    'Relational element' => 'Relační prvek',
    'Include drafts in this scan' => 'Zahrnout do tohoto skenování koncepty',
    'When enabled, assets referenced only in drafts may be treated as used.' => 'Je-li zapnuto, assety odkazované pouze v konceptech mohou být považovány za používané.',
    'Include revisions in this scan' => 'Zahrnout do tohoto skenování revize',
    'When enabled, assets referenced only in revisions may be treated as used.' => 'Je-li zapnuto, assety odkazované pouze v revizích mohou být považovány za používané.',
    'When enabled, any row in Craft’s relations table will cause an asset to be treated as used, including references created by plugin-defined or unknown element types. Disable this for a stricter scan.' => 'Je-li tato možnost zapnuta, jakýkoli řádek v tabulce relací Craftu způsobí, že asset bude považován za používaný, včetně odkazů vytvořených pluginem definovanými nebo neznámými typy prvků. Pro přísnější skenování tuto možnost vypněte.',

    // Settings - Scan performance
    'Scan performance' => 'Výkon skenování',
    'Relation batch size' => 'Velikost dávky relací',
    'Maximum number of assets loaded for relation scanning per queue execution. Lower this (e.g. to 500) on sites with heavy or deeply nested relations if scan jobs time out. You can also override this with `relationBatchSize` in `config/asset-cleaner.php` or the `ASSET_CLEANER_RELATION_BATCH_SIZE` environment variable.' => 'Maximální počet assetů načtených pro skenování relací při jednom běhu fronty. Snižte tuto hodnotu (např. na 500) na webech s mnoha nebo hluboce vnořenými relacemi, pokud úlohy skenování vyprší. Hodnotu lze také přepsat pomocí `relationBatchSize` v `config/asset-cleaner.php` nebo proměnné prostředí `ASSET_CLEANER_RELATION_BATCH_SIZE`.',
    'Relation time budget (seconds)' => 'Časový rozpočet relací (sekundy)',
    'Wall-clock budget for the relation stage of a single queue execution. Once exceeded, the job stops and re-queues to continue, keeping each execution safely under the queue’s time-to-reserve (TTR, 300s by default). Keep this comfortably below your TTR. You can also override this with `relationTimeBudgetSeconds` in `config/asset-cleaner.php` or the `ASSET_CLEANER_RELATION_TIME_BUDGET` environment variable.' => 'Reálný časový rozpočet pro fázi relací jednoho běhu fronty. Po jeho překročení se úloha zastaví a znovu zařadí do fronty, aby pokračovala, takže každý běh zůstane bezpečně pod time-to-reserve (TTR, výchozí 300 s) fronty. Udržujte tuto hodnotu s rezervou pod vaším TTR. Hodnotu lze také přepsat pomocí `relationTimeBudgetSeconds` v `config/asset-cleaner.php` nebo proměnné prostředí `ASSET_CLEANER_RELATION_TIME_BUDGET`.',

    // Settings page, queue job descriptions, scan stage labels, usage labels
    'Scan storage mode' => 'Režim ukládání skenů',
    'File-based' => 'Souborové',
    'Database-based' => 'Databázové',
    'Choose how Asset Cleaner stores transient scan state. File-based storage is the default and works well when web and queue workers share a filesystem. Database-based storage is better suited for containerized or cloud-style environments where shared filesystem access is not guaranteed.' => 'Určuje, jak Asset Cleaner ukládá dočasný stav skenování. Souborové úložiště je výchozí a funguje dobře, když web a fronta sdílejí souborový systém. Databázové úložiště je vhodnější pro kontejnerová nebo cloudová prostředí, kde sdílený přístup k souborovému systému není zaručen.',
    'File-based scan workspace path' => 'Cesta k souborovému pracovnímu prostoru skenování',
    'Optional. Only used when scan storage mode is set to File-based. Defaults to `@storage/asset-cleaner`. You can also override this with `scanWorkspacePath` in `config/asset-cleaner.php` or the `ASSET_CLEANER_SCAN_PATH` environment variable.' => 'Volitelné. Použije se pouze v případě, že režim ukládání je nastaven na „Souborové". Výchozí je `@storage/asset-cleaner`. Hodnotu lze také přepsat pomocí `scanWorkspacePath` v `config/asset-cleaner.php` nebo proměnné prostředí `ASSET_CLEANER_SCAN_PATH`.',
    'Include drafts by default' => 'Ve výchozím nastavení zahrnout koncepty',
    'When enabled, assets referenced only in drafts may be treated as used during scans. This value acts as the default for new scans and can be overridden per scan from the utility page. You can also override this with `includeDraftsByDefault` in `config/asset-cleaner.php` or the `ASSET_CLEANER_INCLUDE_DRAFTS` environment variable.' => 'Pokud je povoleno, assety odkazované pouze v konceptech mohou být při skenování považovány za používané. Tato hodnota slouží jako výchozí pro nové skeny a lze ji přepsat pro jednotlivé skeny na stránce nástroje. Lze ji také přepsat pomocí `includeDraftsByDefault` v `config/asset-cleaner.php` nebo proměnné prostředí `ASSET_CLEANER_INCLUDE_DRAFTS`.',
    'Include revisions by default' => 'Ve výchozím nastavení zahrnout revize',
    'When enabled, assets referenced only in revisions may be treated as used during scans. This value acts as the default for new scans and can be overridden per scan from the utility page. You can also override this with `includeRevisionsByDefault` in `config/asset-cleaner.php` or the `ASSET_CLEANER_INCLUDE_REVISIONS` environment variable.' => 'Pokud je povoleno, assety odkazované pouze v revizích mohou být při skenování považovány za používané. Tato hodnota slouží jako výchozí pro nové skeny a lze ji přepsat pro jednotlivé skeny na stránce nástroje. Lze ji také přepsat pomocí `includeRevisionsByDefault` v `config/asset-cleaner.php` nebo proměnné prostředí `ASSET_CLEANER_INCLUDE_REVISIONS`.',
    'Notes:' => 'Poznámky:',
    'Only the latest scan is retained for restore/export workflows.' => 'Pro obnovení/export se uchovává pouze poslední sken.',
    'When using File-based storage in multi-container setups, make sure the configured workspace path is shared between web and queue workers.' => 'Při souborovém úložišti ve vícekontejnerových sestavách se ujistěte, že nakonfigurovaná cesta pracovního prostoru je sdílená mezi web a frontou.',
    'Config file values override these control panel settings.' => 'Hodnoty z konfiguračního souboru mají přednost před těmito nastaveními v administraci.',
    'Draft and revision handling can be configured globally here and overridden per scan from the utility page.' => 'Zacházení s koncepty a revizemi lze nastavit globálně zde a přepsat pro jednotlivé skeny na stránce nástroje.',
    'Preparing asset scan' => 'Příprava skenování assetů',
    'Scanning asset relations' => 'Skenování relací assetů',
    'Scanning content for asset references' => 'Skenování obsahu kvůli odkazům na assety',
    'Finalizing asset scan results' => 'Dokončování výsledků skenování assetů',
    'Preparing asset snapshot...' => 'Příprava snímku assetů...',
    'Scanning relations...' => 'Skenování relací...',
    'Scanning content...' => 'Skenování obsahu...',
    'Finalizing results...' => 'Dokončování výsledků...',
    'User profile picture' => 'Profilový obrázek uživatele',
    'User #{id}' => 'Uživatel #{id}',
    'Relational source #{id}' => 'Relační zdroj #{id}',
    'Used by relational element #{id}' => 'Používáno relačním prvkem #{id}',

    // Bulk delete confirmations and scan status messages
    'Are you sure you want to permanently delete {count} assets? This action CANNOT be undone! Download a backup (CSV or ZIP) before proceeding.' => 'Opravdu chcete trvale smazat {count} assetů? Tuto akci NELZE vrátit zpět! Před pokračováním si stáhněte zálohu (CSV nebo ZIP).',
    'Before permanently deleting' => 'Před trvalým smazáním',
    'Bulk Actions - All Selected Volumes' => 'Hromadné akce - Všechny vybrané svazky',
    'Final confirmation: Permanently delete {count} assets? This CANNOT be undone!' => 'Poslední potvrzení: trvale smazat {count} assetů? Toto NELZE vrátit zpět!',
    'Lost contact while polling scan progress. The scan may still be running.' => 'Ztraceno spojení při dotazování na průběh skenování. Skenování možná stále běží.',
    'No unused assets found.' => 'Nebyly nalezeny žádné nepoužívané assety.',
    'Scan older than 24h — results may be outdated' => 'Sken starší než 24 h — výsledky mohou být zastaralé',
    'We recommend downloading a ZIP backup of the assets you plan to remove first, or using "Put into Trash" as a safer alternative. Permanent deletions cannot be undone.' => 'Doporučujeme nejprve stáhnout ZIP zálohu assetů, které plánujete odstranit, nebo použít „Přesunout do koše" jako bezpečnější alternativu. Trvalá smazání nelze vrátit zpět.',
    '{count} unused assets — {size}' => '{count} nepoužívaných assetů — {size}',
];
