<?php

return [
    // General
    'Asset Cleaner' => 'Curățător de resurse',
    'An error occurred.' => 'A apărut o eroare.',
    'Loading...' => 'Se încarcă...',
    
    // View Usage
    'View Usage' => 'Vezi utilizarea',
    'Used by Entries' => 'Folosit de intrări',
    'Used in Content Fields' => 'Folosit în câmpuri de conținut',
    'This asset is not used anywhere.' => 'Această resursă nu este folosită nicăieri.',
    
    // Utility Page
    'Scan Now' => 'Scanează acum',
    'Select Volumes' => 'Selectează volume',
    'Select All' => 'Selectează tot',
    'Results' => 'Rezultate',
    'Used Assets' => 'Resurse folosite',
    'Unused Assets' => 'Resurse nefolosite',
    'Scanning...' => 'Se scanează...',
    
    // Bulk Actions
    'Bulk Actions' => 'Acțiuni în masă',
    'Bulk Actions (All Volumes)' => 'Acțiuni în masă (Toate volumele)',
    'Download CSV' => 'Descarcă CSV',
    'Download ZIP' => 'Descarcă ZIP',
    'Put into Trash' => 'Mută în coș',
    'Delete Permanently' => 'Șterge permanent',
    
    // Table Headers
    'Title' => 'Titlu',
    'Filename' => 'Nume fișier',
    'Volume' => 'Volum',
    'Size' => 'Dimensiune',
    'Path' => 'Cale',
    'Date Created' => 'Data creării',
    
    // Messages
    'No assets selected.' => 'Nu sunt selectate resurse.',
    'No assets found.' => 'Nu s-au găsit resurse.',
    'Could not create ZIP file.' => 'Nu s-a putut crea fișierul ZIP.',
    'No volumes selected.' => 'Nu sunt selectate volume.',
    
    // ZIP Download Dialog
    'ZIP Download Options' => 'Opțiuni descărcare ZIP',
    'How would you like to organize the files in the ZIP?' => 'Cum doriți să organizați fișierele în ZIP?',
    'Flat (all files in root)' => 'Plat (toate fișierele în rădăcină)',
    'Preserve folder structure' => 'Păstrează structura folderelor',
    'Cancel' => 'Anulează',
    'ZIP download initiated. Large files may take several minutes to prepare.' => 'Descărcarea ZIP a început. Fișierele mari pot dura câteva minute.',
    'Preparing ZIP file... This may take several minutes for large files. Please wait.' => 'Se pregătește fișierul ZIP... Poate dura câteva minute pentru fișiere mari. Vă rugăm așteptați.',
    
    // Trash/Delete Messages
    'Are you sure you want to move {count} assets to trash?' => 'Sigur doriți să mutați {count} resurse în coș?',
    'Moved {count} assets to trash.' => '{count} resurse mutate în coș.',
    'Permanently deleted {count} assets.' => '{count} resurse șterse permanent.',
    'WARNING: You are about to permanently delete assets.' => 'ATENȚIE: Sunteți pe cale să ștergeți permanent resurse.',
    'This action CANNOT be undone!' => 'Această acțiune NU poate fi anulată!',
    'We strongly recommend downloading the unused assets as a backup before proceeding.' => 'Vă recomandăm insistent să descărcați resursele nefolosite ca backup înainte de a continua.',
    'Are you absolutely sure you want to permanently delete these assets?' => 'Sunteți absolut sigur că doriți să ștergeți permanent aceste resurse?',
    'Final confirmation: Permanently delete assets? This CANNOT be undone!' => 'Confirmare finală: Ștergeți permanent resursele? NU poate fi anulat!',
    
    // Volume Section
    'unused assets' => 'resurse nefolosite',
    'No assets selected in this volume.' => 'Nu sunt selectate resurse în acest volum.',
    
    // Errors
    'Failed to scan volumes.' => 'Scanarea volumelor a eșuat.',
    'Failed to export CSV.' => 'Exportul CSV a eșuat.',
    'Failed to create ZIP file.' => 'Crearea fișierului ZIP a eșuat.',
    'Failed to move assets to trash.' => 'Mutarea resurselor în coș a eșuat.',
    'Failed to delete assets.' => 'Ștergerea resurselor a eșuat.',
    'Failed to get asset usage.' => 'Obținerea utilizării resursei a eșuat.',

    // Queue Scan
    'Scan queued...' => 'Scanare în așteptare...',
    'Scan failed.' => 'Scanarea a eșuat.',
    'Scanning assets for usage' => 'Scanarea utilizării resurselor',
    'The queue does not appear to be running. Make sure a queue worker is active (e.g. php craft queue/listen).' => 'Coada nu pare să ruleze. Asigurați-vă că un worker de coadă este activ (ex: php craft queue/listen).',

    // Scan Time
    'Scanned on {date}' => 'Scanat pe {date}',
    'Restoring last scan...' => 'Restaurarea ultimei scanări...',
    // Usage Dialog / Scan Options
    'Check Asset Usage' => 'Verifică utilizarea resursei',
    'Choose how usage should be evaluated for this asset.' => 'Alege cum ar trebui evaluată utilizarea acestei resurse.',
    'Choose the usage options you want to check, then confirm.' => 'Alege opțiunile de utilizare pe care vrei să le verifici, apoi confirmă.',
    'Include drafts' => 'Include ciornele',
    'Include revisions' => 'Include reviziile',
    'Count all relational references as usage' => 'Consideră toate referințele relaționale ca utilizare',
    'Recommended for projects with plugin-defined or unknown element types that may store asset relations outside normal entry content.' => 'Recomandat pentru proiecte cu tipuri de elemente definite de pluginuri sau necunoscute, care pot stoca relații ale resurselor în afara conținutului normal al intrărilor.',
    'Check Usage' => 'Verifică utilizarea',
    'Used by Relational Elements' => 'Folosit de elemente relaționale',
    'Other Relational Elements' => 'Alte elemente relaționale',
    'Relational element #{id}' => 'Element relațional #{id}',
    'Relational element' => 'Element relațional',
    'Include drafts in this scan' => 'Include ciornele în această scanare',
    'When enabled, assets referenced only in drafts may be treated as used.' => 'Când este activat, resursele la care se face referire doar în ciorne pot fi tratate ca folosite.',
    'Include revisions in this scan' => 'Include reviziile în această scanare',
    'When enabled, assets referenced only in revisions may be treated as used.' => 'Când este activat, resursele la care se face referire doar în revizii pot fi tratate ca folosite.',
    'When enabled, any row in Craft’s relations table will cause an asset to be treated as used, including references created by plugin-defined or unknown element types. Disable this for a stricter scan.' => 'Când este activat, orice rând din tabelul de relații al Craft va face ca o resursă să fie tratată ca folosită, inclusiv referințele create de tipuri de elemente definite de pluginuri sau necunoscute. Dezactivează această opțiune pentru o scanare mai strictă.',

    // Settings - Scan performance
    'Scan performance' => 'Performanța scanării',
    'Relation batch size' => 'Dimensiunea lotului de relații',
    'Maximum number of assets loaded for relation scanning per queue execution. Lower this (e.g. to 500) on sites with heavy or deeply nested relations if scan jobs time out. You can also override this with `relationBatchSize` in `config/asset-cleaner.php` or the `ASSET_CLEANER_RELATION_BATCH_SIZE` environment variable.' => 'Numărul maxim de resurse încărcate pentru scanarea relațiilor la o singură execuție din coadă. Reduceți această valoare (de ex. la 500) pe site-uri cu relații numeroase sau adânc imbricate dacă sarcinile de scanare expiră. O puteți suprascrie și cu `relationBatchSize` în `config/asset-cleaner.php` sau cu variabila de mediu `ASSET_CLEANER_RELATION_BATCH_SIZE`.',
    'Relation time budget (seconds)' => 'Buget de timp pentru relații (secunde)',
    'Wall-clock budget for the relation stage of a single queue execution. Once exceeded, the job stops and re-queues to continue, keeping each execution safely under the queue’s time-to-reserve (TTR, 300s by default). Keep this comfortably below your TTR. You can also override this with `relationTimeBudgetSeconds` in `config/asset-cleaner.php` or the `ASSET_CLEANER_RELATION_TIME_BUDGET` environment variable.' => 'Bugetul de timp real pentru etapa relațiilor unei singure execuții din coadă. Odată depășit, sarcina se oprește și se reprogramează în coadă pentru a continua, menținând fiecare execuție în siguranță sub time-to-reserve (TTR, implicit 300 s) al cozii. Păstrați această valoare clar sub TTR-ul dvs. O puteți suprascrie și cu `relationTimeBudgetSeconds` în `config/asset-cleaner.php` sau cu variabila de mediu `ASSET_CLEANER_RELATION_TIME_BUDGET`.',

    // Settings page, queue job descriptions, scan stage labels, usage labels
    'Scan storage mode' => 'Modul de stocare a scanărilor',
    'File-based' => 'Pe fișiere',
    'Database-based' => 'În baza de date',
    'Choose how Asset Cleaner stores transient scan state. File-based storage is the default and works well when web and queue workers share a filesystem. Database-based storage is better suited for containerized or cloud-style environments where shared filesystem access is not guaranteed.' => 'Determină cum stochează Asset Cleaner starea tranzitorie a scanărilor. Stocarea pe fișiere este implicită și funcționează bine când workerii web și de coadă partajează un sistem de fișiere. Stocarea în baza de date este mai potrivită pentru medii containerizate sau cloud, unde accesul partajat la sistemul de fișiere nu este garantat.',
    'File-based scan workspace path' => 'Calea spațiului de lucru pentru scanare pe fișiere',
    'Optional. Only used when scan storage mode is set to File-based. Defaults to `@storage/asset-cleaner`. You can also override this with `scanWorkspacePath` in `config/asset-cleaner.php` or the `ASSET_CLEANER_SCAN_PATH` environment variable.' => 'Opțional. Folosit doar când modul de stocare este setat la „Pe fișiere". Implicit este `@storage/asset-cleaner`. Îl puteți suprascrie și cu `scanWorkspacePath` în `config/asset-cleaner.php` sau cu variabila de mediu `ASSET_CLEANER_SCAN_PATH`.',
    'Include drafts by default' => 'Include ciornele în mod implicit',
    'When enabled, assets referenced only in drafts may be treated as used during scans. This value acts as the default for new scans and can be overridden per scan from the utility page. You can also override this with `includeDraftsByDefault` in `config/asset-cleaner.php` or the `ASSET_CLEANER_INCLUDE_DRAFTS` environment variable.' => 'Când este activat, resursele referențiate doar în ciorne pot fi considerate utilizate în timpul scanărilor. Această valoare servește ca implicită pentru scanările noi și poate fi suprascrisă per scanare din pagina utilitarului. O puteți suprascrie și cu `includeDraftsByDefault` în `config/asset-cleaner.php` sau cu variabila de mediu `ASSET_CLEANER_INCLUDE_DRAFTS`.',
    'Include revisions by default' => 'Include reviziile în mod implicit',
    'When enabled, assets referenced only in revisions may be treated as used during scans. This value acts as the default for new scans and can be overridden per scan from the utility page. You can also override this with `includeRevisionsByDefault` in `config/asset-cleaner.php` or the `ASSET_CLEANER_INCLUDE_REVISIONS` environment variable.' => 'Când este activat, resursele referențiate doar în revizii pot fi considerate utilizate în timpul scanărilor. Această valoare servește ca implicită pentru scanările noi și poate fi suprascrisă per scanare din pagina utilitarului. O puteți suprascrie și cu `includeRevisionsByDefault` în `config/asset-cleaner.php` sau cu variabila de mediu `ASSET_CLEANER_INCLUDE_REVISIONS`.',
    'Notes:' => 'Note:',
    'Only the latest scan is retained for restore/export workflows.' => 'Doar cea mai recentă scanare este păstrată pentru fluxurile de restaurare/export.',
    'When using File-based storage in multi-container setups, make sure the configured workspace path is shared between web and queue workers.' => 'Când folosiți stocarea pe fișiere în configurații multi-container, asigurați-vă că calea spațiului de lucru configurată este partajată între workerii web și de coadă.',
    'Config file values override these control panel settings.' => 'Valorile din fișierul de configurare au prioritate față de aceste setări din panoul de control.',
    'Draft and revision handling can be configured globally here and overridden per scan from the utility page.' => 'Gestionarea ciornelor și a reviziilor poate fi configurată global aici și suprascrisă per scanare din pagina utilitarului.',
    'Preparing asset scan' => 'Se pregătește scanarea resurselor',
    'Scanning asset relations' => 'Se scanează relațiile resurselor',
    'Scanning content for asset references' => 'Se scanează conținutul pentru referințe la resurse',
    'Finalizing asset scan results' => 'Se finalizează rezultatele scanării resurselor',
    'Preparing asset snapshot...' => 'Se pregătește instantaneul resurselor...',
    'Scanning relations...' => 'Se scanează relațiile...',
    'Scanning content...' => 'Se scanează conținutul...',
    'Finalizing results...' => 'Se finalizează rezultatele...',
    'User profile picture' => 'Fotografia de profil a utilizatorului',
    'User #{id}' => 'Utilizator #{id}',
    'Relational source #{id}' => 'Sursă relațională #{id}',
    'Used by relational element #{id}' => 'Utilizat de elementul relațional #{id}',

    // Bulk delete confirmations and scan status messages
    'Are you sure you want to permanently delete {count} assets? This action CANNOT be undone! Download a backup (CSV or ZIP) before proceeding.' => 'Sigur doriți să ștergeți definitiv {count} resurse? Această acțiune NU poate fi anulată! Descărcați o copie de rezervă (CSV sau ZIP) înainte de a continua.',
    'Before permanently deleting' => 'Înainte de ștergerea definitivă',
    'Bulk Actions - All Selected Volumes' => 'Acțiuni în masă - Toate volumele selectate',
    'Final confirmation: Permanently delete {count} assets? This CANNOT be undone!' => 'Confirmare finală: ștergeți definitiv {count} resurse? Aceasta NU poate fi anulată!',
    'Lost contact while polling scan progress. The scan may still be running.' => 'S-a pierdut conexiunea în timpul verificării progresului scanării. Scanarea poate fi încă în curs.',
    'No unused assets found.' => 'Nu au fost găsite resurse neutilizate.',
    'Scan older than 24h — results may be outdated' => 'Scanare mai veche de 24 h — rezultatele pot fi învechite',
    'We recommend downloading a ZIP backup of the assets you plan to remove first, or using "Put into Trash" as a safer alternative. Permanent deletions cannot be undone.' => 'Recomandăm să descărcați mai întâi o copie ZIP a resurselor pe care intenționați să le eliminați sau să folosiți „Mutare la coșul de gunoi" ca alternativă mai sigură. Ștergerile definitive nu pot fi anulate.',
    '{count} unused assets — {size}' => '{count} resurse neutilizate — {size}',
];
