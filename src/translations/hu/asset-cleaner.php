<?php

return [
    // General
    'Asset Cleaner' => 'Eszköz tisztító',
    'An error occurred.' => 'Hiba történt.',
    'Loading...' => 'Betöltés...',
    
    // View Usage
    'View Usage' => 'Használat megtekintése',
    'Used by Entries' => 'Bejegyzések használják',
    'Used in Content Fields' => 'Tartalmi mezőkben használva',
    'This asset is not used anywhere.' => 'Ez az eszköz sehol nincs használva.',
    
    // Utility Page
    'Scan Now' => 'Szkennelés most',
    'Select Volumes' => 'Kötetek kiválasztása',
    'Select All' => 'Összes kiválasztása',
    'Results' => 'Eredmények',
    'Used Assets' => 'Használt eszközök',
    'Unused Assets' => 'Nem használt eszközök',
    'Scanning...' => 'Szkennelés...',
    
    // Bulk Actions
    'Bulk Actions' => 'Tömeges műveletek',
    'Bulk Actions (All Volumes)' => 'Tömeges műveletek (Összes kötet)',
    'Download CSV' => 'CSV letöltése',
    'Download ZIP' => 'ZIP letöltése',
    'Put into Trash' => 'Kukába helyezés',
    'Delete Permanently' => 'Végleges törlés',
    
    // Table Headers
    'Title' => 'Cím',
    'Filename' => 'Fájlnév',
    'Volume' => 'Kötet',
    'Size' => 'Méret',
    'Path' => 'Útvonal',
    'Date Created' => 'Létrehozás dátuma',
    
    // Messages
    'No assets selected.' => 'Nincs kiválasztott eszköz.',
    'No assets found.' => 'Nem található eszköz.',
    'Could not create ZIP file.' => 'Nem sikerült létrehozni a ZIP fájlt.',
    'No volumes selected.' => 'Nincs kiválasztott kötet.',
    
    // ZIP Download Dialog
    'ZIP Download Options' => 'ZIP letöltési beállítások',
    'How would you like to organize the files in the ZIP?' => 'Hogyan szeretné rendezni a fájlokat a ZIP-ben?',
    'Flat (all files in root)' => 'Lapos (minden fájl a gyökérben)',
    'Preserve folder structure' => 'Mappastruktúra megőrzése',
    'Cancel' => 'Mégse',
    'ZIP download initiated. Large files may take several minutes to prepare.' => 'ZIP letöltés elindítva. A nagy fájlok több percet is igénybe vehetnek.',
    'Preparing ZIP file... This may take several minutes for large files. Please wait.' => 'ZIP fájl előkészítése... Nagy fájloknál ez több percig is tarthat. Kérem várjon.',
    
    // Trash/Delete Messages
    'Are you sure you want to move {count} assets to trash?' => 'Biztosan kukába helyezi a(z) {count} eszközt?',
    'Moved {count} assets to trash.' => '{count} eszköz kukába helyezve.',
    'Permanently deleted {count} assets.' => '{count} eszköz véglegesen törölve.',
    'WARNING: You are about to permanently delete assets.' => 'FIGYELEM: Eszközök végleges törlésére készül.',
    'This action CANNOT be undone!' => 'Ez a művelet NEM vonható vissza!',
    'We strongly recommend downloading the unused assets as a backup before proceeding.' => 'Erősen javasoljuk, hogy töltse le a nem használt eszközöket biztonsági mentésként a folytatás előtt.',
    'Are you absolutely sure you want to permanently delete these assets?' => 'Teljesen biztos benne, hogy véglegesen törölni szeretné ezeket az eszközöket?',
    'Final confirmation: Permanently delete assets? This CANNOT be undone!' => 'Végső megerősítés: Eszközök végleges törlése? NEM vonható vissza!',
    
    // Volume Section
    'unused assets' => 'nem használt eszközök',
    'No assets selected in this volume.' => 'Ebben a kötetben nincs kiválasztott eszköz.',
    
    // Errors
    'Failed to scan volumes.' => 'Kötetek szkennelése sikertelen.',
    'Failed to export CSV.' => 'CSV exportálás sikertelen.',
    'Failed to create ZIP file.' => 'ZIP fájl létrehozása sikertelen.',
    'Failed to move assets to trash.' => 'Eszközök kukába helyezése sikertelen.',
    'Failed to delete assets.' => 'Eszközök törlése sikertelen.',
    'Failed to get asset usage.' => 'Eszköz használati adatok lekérése sikertelen.',

    // Queue Scan
    'Scan queued...' => 'Vizsgálat sorba állítva...',
    'Scan failed.' => 'A vizsgálat sikertelen.',
    'Scanning assets for usage' => 'Fájlhasználat vizsgálata',
    'The queue does not appear to be running. Make sure a queue worker is active (e.g. php craft queue/listen).' => 'Úgy tűnik, a sor nem fut. Győződjön meg arról, hogy egy sor-feldolgozó aktív (pl. php craft queue/listen).',

    // Scan Time
    'Scanned on {date}' => 'Szkennelve: {date}',
    'Restoring last scan...' => 'Utolsó szkennelés visszaállítása...',
    // Usage Dialog / Scan Options
    'Check Asset Usage' => 'Eszközhasználat ellenőrzése',
    'Choose how usage should be evaluated for this asset.' => 'Válassza ki, hogyan értékelje a rendszer ennek az eszköznek a használatát.',
    'Choose the usage options you want to check, then confirm.' => 'Válassza ki az ellenőrizni kívánt használati beállításokat, majd erősítse meg.',
    'Include drafts' => 'Piszkozatok belefoglalása',
    'Include revisions' => 'Revíziók belefoglalása',
    'Count all relational references as usage' => 'Minden kapcsolati hivatkozás használatnak számítson',
    'Recommended for projects with plugin-defined or unknown element types that may store asset relations outside normal entry content.' => 'Ajánlott olyan projektekhez, ahol pluginek által definiált vagy ismeretlen elemtípusok az eszközkapcsolatokat a normál bejegyzéstartalmon kívül tárolhatják.',
    'Check Usage' => 'Használat ellenőrzése',
    'Used by Relational Elements' => 'Kapcsolódó elemek használják',
    'Other Relational Elements' => 'Egyéb kapcsolódó elemek',
    'Relational element #{id}' => 'Kapcsolódó elem #{id}',
    'Relational element' => 'Kapcsolódó elem',
    'Include drafts in this scan' => 'Piszkozatok belefoglalása ebbe a vizsgálatba',
    'When enabled, assets referenced only in drafts may be treated as used.' => 'Ha engedélyezve van, a csak piszkozatokban hivatkozott eszközök használatban lévőként lesznek kezelve.',
    'Include revisions in this scan' => 'Revíziók belefoglalása ebbe a vizsgálatba',
    'When enabled, assets referenced only in revisions may be treated as used.' => 'Ha engedélyezve van, a csak revíziókban hivatkozott eszközök használatban lévőként lesznek kezelve.',
    'When enabled, any row in Craft’s relations table will cause an asset to be treated as used, including references created by plugin-defined or unknown element types. Disable this for a stricter scan.' => 'Ha engedélyezve van, a Craft kapcsolati táblájának bármely sora miatt az eszköz használatban lévőnek számít, beleértve a pluginek által definiált vagy ismeretlen elemtípusok által létrehozott hivatkozásokat is. Szigorúbb vizsgálathoz kapcsolja ki.',
];
