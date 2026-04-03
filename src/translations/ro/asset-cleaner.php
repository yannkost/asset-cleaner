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
];
