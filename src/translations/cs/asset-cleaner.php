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
];
