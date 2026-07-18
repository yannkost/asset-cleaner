<?php

return [
    // General
    'Asset Cleaner' => 'Čistič súborov',
    'An error occurred.' => 'Vyskytla sa chyba.',
    'Loading...' => 'Načítava sa...',
    
    // View Usage
    'View Usage' => 'Zobraziť použitie',
    'Used by Entries' => 'Používané položkami',
    'Used in Content Fields' => 'Používané v poliach obsahu',
    'This asset is not used anywhere.' => 'Tento súbor nie je nikde používaný.',
    
    // Utility Page
    'Scan Now' => 'Skenovať teraz',
    'Select Volumes' => 'Vybrať zväzky',
    'Select All' => 'Vybrať všetko',
    'Results' => 'Výsledky',
    'Used Assets' => 'Používané súbory',
    'Unused Assets' => 'Nepoužívané súbory',
    'Scanning...' => 'Skenovanie...',
    
    // Bulk Actions
    'Bulk Actions' => 'Hromadné akcie',
    'Bulk Actions (All Volumes)' => 'Hromadné akcie (Všetky zväzky)',
    'Download CSV' => 'Stiahnuť CSV',
    'Download ZIP' => 'Stiahnuť ZIP',
    'Put into Trash' => 'Presunúť do koša',
    'Delete Permanently' => 'Trvalo odstrániť',
    
    // Table Headers
    'Title' => 'Názov',
    'Filename' => 'Názov súboru',
    'Volume' => 'Zväzok',
    'Size' => 'Veľkosť',
    'Path' => 'Cesta',
    'Date Created' => 'Dátum vytvorenia',
    
    // Messages
    'No assets selected.' => 'Nie sú vybrané žiadne súbory.',
    'No assets found.' => 'Nenašli sa žiadne súbory.',
    'Could not create ZIP file.' => 'Nepodarilo sa vytvoriť ZIP súbor.',
    'No volumes selected.' => 'Nie sú vybrané žiadne zväzky.',
    
    // ZIP Download Dialog
    'ZIP Download Options' => 'Možnosti sťahovania ZIP',
    'How would you like to organize the files in the ZIP?' => 'Ako chcete usporiadať súbory v ZIP?',
    'Flat (all files in root)' => 'Plochá štruktúra (všetky súbory v koreňovom adresári)',
    'Preserve folder structure' => 'Zachovať štruktúru priečinkov',
    'Cancel' => 'Zrušiť',
    'ZIP download initiated. Large files may take several minutes to prepare.' => 'Sťahovanie ZIP začalo. Veľké súbory môžu trvať niekoľko minút.',
    'Preparing ZIP file... This may take several minutes for large files. Please wait.' => 'Príprava ZIP súboru... Pre veľké súbory to môže trvať niekoľko minút. Prosím čakajte.',
    
    // Trash/Delete Messages
    'Are you sure you want to move {count} assets to trash?' => 'Naozaj chcete presunúť {count} súborov do koša?',
    'Moved {count} assets to trash.' => '{count} súborov presunutých do koša.',
    'Permanently deleted {count} assets.' => '{count} súborov trvalo odstránených.',
    'WARNING: You are about to permanently delete assets.' => 'VAROVANIE: Chystáte sa trvalo odstrániť súbory.',
    'This action CANNOT be undone!' => 'Túto akciu NEMOŽNO vrátiť späť!',
    'We strongly recommend downloading the unused assets as a backup before proceeding.' => 'Dôrazne odporúčame stiahnuť nepoužívané súbory ako zálohu pred pokračovaním.',
    'Are you absolutely sure you want to permanently delete these assets?' => 'Ste si absolútne istí, že chcete tieto súbory trvalo odstrániť?',
    'Final confirmation: Permanently delete assets? This CANNOT be undone!' => 'Finálne potvrdenie: Trvalo odstrániť súbory? NEMOŽNO to vrátiť späť!',
    
    // Volume Section
    'unused assets' => 'nepoužívané súbory',
    'No assets selected in this volume.' => 'V tomto zväzku nie sú vybrané žiadne súbory.',
    
    // Errors
    'Failed to scan volumes.' => 'Skenovanie zväzkov zlyhalo.',
    'Failed to export CSV.' => 'Export CSV zlyhal.',
    'Failed to create ZIP file.' => 'Vytvorenie ZIP súboru zlyhalo.',
    'Failed to move assets to trash.' => 'Presun súborov do koša zlyhal.',
    'Failed to delete assets.' => 'Odstránenie súborov zlyhalo.',
    'Failed to get asset usage.' => 'Získanie informácií o použití súboru zlyhalo.',

    // Queue Scan
    'Scan queued...' => 'Skenovanie vo fronte...',
    'Scan failed.' => 'Skenovanie zlyhalo.',
    'Scanning assets for usage' => 'Skenovanie využitia súborov',
    'The queue does not appear to be running. Make sure a queue worker is active (e.g. php craft queue/listen).' => 'Fronta zrejme nebeží. Uistite sa, že je worker fronty aktívny (napr. php craft queue/listen).',

    // Scan Time
    'Scanned on {date}' => 'Naskenované {date}',
    'Restoring last scan...' => 'Obnovovanie posledného skenu...',
    // Usage Dialog / Scan Options
    'Check Asset Usage' => 'Skontrolovať použitie assetu',
    'Choose how usage should be evaluated for this asset.' => 'Vyberte, ako sa má vyhodnotiť použitie tohto assetu.',
    'Choose the usage options you want to check, then confirm.' => 'Vyberte možnosti použitia, ktoré chcete skontrolovať, a potom potvrďte.',
    'Include drafts' => 'Zahrnúť koncepty',
    'Include revisions' => 'Zahrnúť revízie',
    'Count all relational references as usage' => 'Počítať všetky relačné odkazy ako použitie',
    'Recommended for projects with plugin-defined or unknown element types that may store asset relations outside normal entry content.' => 'Odporúčané pre projekty s typmi prvkov definovanými pluginmi alebo neznámymi typmi, ktoré môžu ukladať vzťahy assetov mimo bežného obsahu záznamov.',
    'Check Usage' => 'Skontrolovať použitie',
    'Used by Relational Elements' => 'Používané relačnými prvkami',
    'Other Relational Elements' => 'Ďalšie relačné prvky',
    'Relational element #{id}' => 'Relačný prvok č. {id}',
    'Relational element' => 'Relačný prvok',
    'Include drafts in this scan' => 'Zahrnúť koncepty do tohto skenovania',
    'When enabled, assets referenced only in drafts may be treated as used.' => 'Ak je táto možnosť zapnutá, assety odkazované iba v konceptoch sa môžu považovať za používané.',
    'Include revisions in this scan' => 'Zahrnúť revízie do tohto skenovania',
    'When enabled, assets referenced only in revisions may be treated as used.' => 'Ak je táto možnosť zapnutá, assety odkazované iba v revíziách sa môžu považovať za používané.',
    'When enabled, any row in Craft’s relations table will cause an asset to be treated as used, including references created by plugin-defined or unknown element types. Disable this for a stricter scan.' => 'Ak je táto možnosť zapnutá, akýkoľvek riadok v tabuľke vzťahov Craftu spôsobí, že asset bude považovaný za používaný, vrátane odkazov vytvorených pluginmi definovanými alebo neznámymi typmi prvkov. Pre prísnejšie skenovanie túto možnosť vypnite.',

    // Settings - Scan performance
    'Scan performance' => 'Výkon skenovania',
    'Relation batch size' => 'Veľkosť dávky relácií',
    'Maximum number of assets loaded for relation scanning per queue execution. Lower this (e.g. to 500) on sites with heavy or deeply nested relations if scan jobs time out. You can also override this with `relationBatchSize` in `config/asset-cleaner.php` or the `ASSET_CLEANER_RELATION_BATCH_SIZE` environment variable.' => 'Maximálny počet assetov načítaných na skenovanie relácií pri jednom behu fronty. Znížte túto hodnotu (napr. na 500) na weboch s početnými alebo hlboko vnorenými reláciami, ak úlohy skenovania vypršia. Hodnotu možno prepísať aj cez `relationBatchSize` v `config/asset-cleaner.php` alebo premennú prostredia `ASSET_CLEANER_RELATION_BATCH_SIZE`.',
    'Relation time budget (seconds)' => 'Časový rozpočet relácií (sekundy)',
    'Wall-clock budget for the relation stage of a single queue execution. Once exceeded, the job stops and re-queues to continue, keeping each execution safely under the queue’s time-to-reserve (TTR, 300s by default). Keep this comfortably below your TTR. You can also override this with `relationTimeBudgetSeconds` in `config/asset-cleaner.php` or the `ASSET_CLEANER_RELATION_TIME_BUDGET` environment variable.' => 'Reálny časový rozpočet pre fázu relácií jedného behu fronty. Po jeho prekročení sa úloha zastaví a znova zaradí do fronty, aby pokračovala, takže každý beh zostane bezpečne pod time-to-reserve (TTR, predvolene 300 s) fronty. Udržujte túto hodnotu s rezervou pod vaším TTR. Hodnotu možno prepísať aj cez `relationTimeBudgetSeconds` v `config/asset-cleaner.php` alebo premennú prostredia `ASSET_CLEANER_RELATION_TIME_BUDGET`.',
];
