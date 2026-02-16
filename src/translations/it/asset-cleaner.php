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
];
