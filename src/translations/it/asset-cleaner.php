<?php

return [
    // General
    'Asset Cleaner' => 'Asset Cleaner',
    'An error occurred.' => 'Si è verificato un errore.',
    'Loading...' => 'Caricamento...',
    
    // View Usage
    'View Usage' => 'Visualizza utilizzo',
    'Used by Entries' => 'Utilizzato dalle voci',
    'Used in Content Fields' => 'Utilizzato nei campi contenuto',
    'This asset is not used anywhere.' => 'Questo asset non è utilizzato da nessuna parte.',
    
    // Utility Page
    'Scan Now' => 'Scansiona ora',
    'Select Volumes' => 'Seleziona volumi',
    'Select All' => 'Seleziona tutto',
    'Results' => 'Risultati',
    'Used Assets' => 'Asset utilizzati',
    'Unused Assets' => 'Asset non utilizzati',
    'Scanning...' => 'Scansione...',
    
    // Bulk Actions
    'Bulk Actions' => 'Azioni di massa',
    'Bulk Actions (All Volumes)' => 'Azioni di massa (Tutti i volumi)',
    'Download CSV' => 'Scarica CSV',
    'Download ZIP' => 'Scarica ZIP',
    'Put into Trash' => 'Sposta nel cestino',
    'Delete Permanently' => 'Elimina definitivamente',
    
    // Table Headers
    'Title' => 'Titolo',
    'Filename' => 'Nome file',
    'Volume' => 'Volume',
    'Size' => 'Dimensione',
    'Path' => 'Percorso',
    'Date Created' => 'Data creazione',
    
    // Messages
    'No assets selected.' => 'Nessun asset selezionato.',
    'No assets found.' => 'Nessun asset trovato.',
    'Could not create ZIP file.' => 'Impossibile creare il file ZIP.',
    'No volumes selected.' => 'Nessun volume selezionato.',
    
    // ZIP Download Dialog
    'ZIP Download Options' => 'Opzioni download ZIP',
    'How would you like to organize the files in the ZIP?' => 'Come vuoi organizzare i file nello ZIP?',
    'Flat (all files in root)' => 'Piatto (tutti i file nella root)',
    'Preserve folder structure' => 'Mantieni struttura cartelle',
    'Cancel' => 'Annulla',
    'ZIP download initiated. Large files may take several minutes to prepare.' => 'Download ZIP avviato. I file grandi potrebbero richiedere diversi minuti.',
    'Preparing ZIP file... This may take several minutes for large files. Please wait.' => 'Preparazione file ZIP... Potrebbe richiedere diversi minuti per file grandi. Attendere prego.',
    
    // Trash/Delete Messages
    'Are you sure you want to move {count} assets to trash?' => 'Sei sicuro di voler spostare {count} asset nel cestino?',
    'Moved {count} assets to trash.' => '{count} asset spostati nel cestino.',
    'Permanently deleted {count} assets.' => '{count} asset eliminati definitivamente.',
    'WARNING: You are about to permanently delete assets.' => 'ATTENZIONE: Stai per eliminare definitivamente degli asset.',
    'This action CANNOT be undone!' => 'Questa azione NON può essere annullata!',
    'We strongly recommend downloading the unused assets as a backup before proceeding.' => 'Ti consigliamo vivamente di scaricare gli asset non utilizzati come backup prima di procedere.',
    'Are you absolutely sure you want to permanently delete these assets?' => 'Sei assolutamente sicuro di voler eliminare definitivamente questi asset?',
    'Final confirmation: Permanently delete assets? This CANNOT be undone!' => 'Conferma finale: Eliminare definitivamente gli asset? NON può essere annullato!',
    
    // Volume Section
    'unused assets' => 'asset non utilizzati',
    'No assets selected in this volume.' => 'Nessun asset selezionato in questo volume.',
    
    // Errors
    'Failed to scan volumes.' => 'Scansione volumi fallita.',
    'Failed to export CSV.' => 'Esportazione CSV fallita.',
    'Failed to create ZIP file.' => 'Creazione file ZIP fallita.',
    'Failed to move assets to trash.' => 'Spostamento asset nel cestino fallito.',
    'Failed to delete assets.' => 'Eliminazione asset fallita.',
    'Failed to get asset usage.' => 'Recupero utilizzo asset fallito.',

    // Queue Scan
    'Scan queued...' => 'Scansione in coda...',
    'Scan failed.' => 'Scansione fallita.',
    'Scanning assets for usage' => 'Scansione degli asset in uso',
    'The queue does not appear to be running. Make sure a queue worker is active (e.g. php craft queue/listen).' => 'La coda non sembra essere in esecuzione. Assicurarsi che un worker della coda sia attivo (es: php craft queue/listen).',

    // Scan Time
    'Scanned on {date}' => 'Scansionato il {date}',
    'Restoring last scan...' => 'Ripristino dell\'ultima scansione...',
    // Usage Dialog / Scan Options
    'Check Asset Usage' => 'Verifica l’utilizzo dell’asset',
    'Choose how usage should be evaluated for this asset.' => 'Scegli come deve essere valutato l’utilizzo di questo asset.',
    'Choose the usage options you want to check, then confirm.' => 'Scegli le opzioni di utilizzo che vuoi verificare, poi conferma.',
    'Include drafts' => 'Includi le bozze',
    'Include revisions' => 'Includi le revisioni',
    'Count all relational references as usage' => 'Conta tutti i riferimenti relazionali come utilizzo',
    'Recommended for projects with plugin-defined or unknown element types that may store asset relations outside normal entry content.' => 'Consigliato per progetti con tipi di elemento definiti da plugin o sconosciuti che possono memorizzare relazioni degli asset al di fuori del normale contenuto delle voci.',
    'Check Usage' => 'Verifica utilizzo',
    'Used by Relational Elements' => 'Utilizzato da elementi relazionali',
    'Other Relational Elements' => 'Altri elementi relazionali',
    'Relational element #{id}' => 'Elemento relazionale #{id}',
    'Relational element' => 'Elemento relazionale',
    'Include drafts in this scan' => 'Includi le bozze in questa scansione',
    'When enabled, assets referenced only in drafts may be treated as used.' => 'Se abilitato, gli asset referenziati solo nelle bozze possono essere considerati utilizzati.',
    'Include revisions in this scan' => 'Includi le revisioni in questa scansione',
    'When enabled, assets referenced only in revisions may be treated as used.' => 'Se abilitato, gli asset referenziati solo nelle revisioni possono essere considerati utilizzati.',
    'When enabled, any row in Craft’s relations table will cause an asset to be treated as used, including references created by plugin-defined or unknown element types. Disable this for a stricter scan.' => 'Se abilitato, qualsiasi riga nella tabella delle relazioni di Craft farà considerare un asset come utilizzato, incluse le referenze create da tipi di elemento definiti da plugin o sconosciuti. Disattiva questa opzione per una scansione più rigorosa.',

    // Settings - Scan performance
    'Scan performance' => 'Prestazioni della scansione',
    'Relation batch size' => 'Dimensione del batch di relazioni',
    'Maximum number of assets loaded for relation scanning per queue execution. Lower this (e.g. to 500) on sites with heavy or deeply nested relations if scan jobs time out. You can also override this with `relationBatchSize` in `config/asset-cleaner.php` or the `ASSET_CLEANER_RELATION_BATCH_SIZE` environment variable.' => 'Numero massimo di asset caricati per la scansione delle relazioni per ogni esecuzione della coda. Riduci questo valore (ad es. a 500) su siti con relazioni numerose o profondamente annidate se i job di scansione vanno in timeout. Puoi anche sovrascriverlo con `relationBatchSize` in `config/asset-cleaner.php` o con la variabile d\'ambiente `ASSET_CLEANER_RELATION_BATCH_SIZE`.',
    'Relation time budget (seconds)' => 'Budget di tempo per le relazioni (secondi)',
    'Wall-clock budget for the relation stage of a single queue execution. Once exceeded, the job stops and re-queues to continue, keeping each execution safely under the queue’s time-to-reserve (TTR, 300s by default). Keep this comfortably below your TTR. You can also override this with `relationTimeBudgetSeconds` in `config/asset-cleaner.php` or the `ASSET_CLEANER_RELATION_TIME_BUDGET` environment variable.' => 'Budget di tempo reale per la fase delle relazioni di una singola esecuzione della coda. Una volta superato, il job si ferma e si riaccoda per continuare, mantenendo ogni esecuzione al sicuro sotto il time-to-reserve (TTR, 300 s per impostazione predefinita) della coda. Mantieni questo valore ben al di sotto del tuo TTR. Puoi anche sovrascriverlo con `relationTimeBudgetSeconds` in `config/asset-cleaner.php` o con la variabile d\'ambiente `ASSET_CLEANER_RELATION_TIME_BUDGET`.',

    // Settings page, queue job descriptions, scan stage labels, usage labels
    'Scan storage mode' => 'Modalità di archiviazione delle scansioni',
    'File-based' => 'Su file',
    'Database-based' => 'Su database',
    'Choose how Asset Cleaner stores transient scan state. File-based storage is the default and works well when web and queue workers share a filesystem. Database-based storage is better suited for containerized or cloud-style environments where shared filesystem access is not guaranteed.' => 'Determina come Asset Cleaner archivia lo stato transitorio delle scansioni. L\'archiviazione su file è quella predefinita e funziona bene quando i worker web e della coda condividono un filesystem. L\'archiviazione su database è più adatta ad ambienti containerizzati o cloud dove l\'accesso a un filesystem condiviso non è garantito.',
    'File-based scan workspace path' => 'Percorso dell\'area di lavoro delle scansioni su file',
    'Optional. Only used when scan storage mode is set to File-based. Defaults to `@storage/asset-cleaner`. You can also override this with `scanWorkspacePath` in `config/asset-cleaner.php` or the `ASSET_CLEANER_SCAN_PATH` environment variable.' => 'Facoltativo. Usato solo quando la modalità di archiviazione è impostata su "Su file". Il valore predefinito è `@storage/asset-cleaner`. Puoi anche sovrascriverlo con `scanWorkspacePath` in `config/asset-cleaner.php` o con la variabile d\'ambiente `ASSET_CLEANER_SCAN_PATH`.',
    'Include drafts by default' => 'Includi le bozze per impostazione predefinita',
    'When enabled, assets referenced only in drafts may be treated as used during scans. This value acts as the default for new scans and can be overridden per scan from the utility page. You can also override this with `includeDraftsByDefault` in `config/asset-cleaner.php` or the `ASSET_CLEANER_INCLUDE_DRAFTS` environment variable.' => 'Se attivato, gli asset referenziati solo nelle bozze possono essere considerati usati durante le scansioni. Questo valore funge da predefinito per le nuove scansioni e può essere sovrascritto per ogni scansione dalla pagina utilità. Puoi anche sovrascriverlo con `includeDraftsByDefault` in `config/asset-cleaner.php` o con la variabile d\'ambiente `ASSET_CLEANER_INCLUDE_DRAFTS`.',
    'Include revisions by default' => 'Includi le revisioni per impostazione predefinita',
    'When enabled, assets referenced only in revisions may be treated as used during scans. This value acts as the default for new scans and can be overridden per scan from the utility page. You can also override this with `includeRevisionsByDefault` in `config/asset-cleaner.php` or the `ASSET_CLEANER_INCLUDE_REVISIONS` environment variable.' => 'Se attivato, gli asset referenziati solo nelle revisioni possono essere considerati usati durante le scansioni. Questo valore funge da predefinito per le nuove scansioni e può essere sovrascritto per ogni scansione dalla pagina utilità. Puoi anche sovrascriverlo con `includeRevisionsByDefault` in `config/asset-cleaner.php` o con la variabile d\'ambiente `ASSET_CLEANER_INCLUDE_REVISIONS`.',
    'Notes:' => 'Note:',
    'Only the latest scan is retained for restore/export workflows.' => 'Solo l\'ultima scansione viene conservata per i flussi di ripristino/esportazione.',
    'When using File-based storage in multi-container setups, make sure the configured workspace path is shared between web and queue workers.' => 'Quando usi l\'archiviazione su file in configurazioni multi-container, assicurati che il percorso dell\'area di lavoro configurato sia condiviso tra i worker web e della coda.',
    'Config file values override these control panel settings.' => 'I valori del file di configurazione hanno la precedenza su queste impostazioni del pannello di controllo.',
    'Draft and revision handling can be configured globally here and overridden per scan from the utility page.' => 'La gestione di bozze e revisioni può essere configurata globalmente qui e sovrascritta per ogni scansione dalla pagina utilità.',
    'Preparing asset scan' => 'Preparazione della scansione degli asset',
    'Scanning asset relations' => 'Scansione delle relazioni degli asset',
    'Scanning content for asset references' => 'Scansione dei contenuti alla ricerca di riferimenti agli asset',
    'Finalizing asset scan results' => 'Finalizzazione dei risultati della scansione degli asset',
    'Preparing asset snapshot...' => 'Preparazione dello snapshot degli asset...',
    'Scanning relations...' => 'Scansione delle relazioni...',
    'Scanning content...' => 'Scansione dei contenuti...',
    'Finalizing results...' => 'Finalizzazione dei risultati...',
    'User profile picture' => 'Immagine del profilo utente',
    'User #{id}' => 'Utente #{id}',
    'Relational source #{id}' => 'Origine relazionale #{id}',
    'Used by relational element #{id}' => 'Usato dall\'elemento relazionale #{id}',

    // Bulk delete confirmations and scan status messages
    'Are you sure you want to permanently delete {count} assets? This action CANNOT be undone! Download a backup (CSV or ZIP) before proceeding.' => 'Vuoi davvero eliminare definitivamente {count} asset? Questa azione NON può essere annullata! Scarica un backup (CSV o ZIP) prima di procedere.',
    'Before permanently deleting' => 'Prima dell\'eliminazione definitiva',
    'Bulk Actions - All Selected Volumes' => 'Azioni di massa - Tutti i volumi selezionati',
    'Final confirmation: Permanently delete {count} assets? This CANNOT be undone!' => 'Conferma finale: eliminare definitivamente {count} asset? Questa azione NON può essere annullata!',
    'Lost contact while polling scan progress. The scan may still be running.' => 'Connessione persa durante il monitoraggio dell\'avanzamento della scansione. La scansione potrebbe essere ancora in corso.',
    'No unused assets found.' => 'Nessun asset inutilizzato trovato.',
    'Scan older than 24h — results may be outdated' => 'Scansione più vecchia di 24 h — i risultati potrebbero non essere aggiornati',
    'We recommend downloading a ZIP backup of the assets you plan to remove first, or using "Put into Trash" as a safer alternative. Permanent deletions cannot be undone.' => 'Consigliamo di scaricare prima un backup ZIP degli asset che intendi rimuovere, oppure di usare "Sposta nel cestino" come alternativa più sicura. Le eliminazioni definitive non possono essere annullate.',
    '{count} unused assets — {size}' => '{count} asset inutilizzati — {size}',
];
