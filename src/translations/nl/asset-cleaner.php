<?php

return [
    // General
    'Asset Cleaner' => 'Asset Cleaner',
    'An error occurred.' => 'Er is een fout opgetreden.',
    'Loading...' => 'Laden...',
    
    // View Usage
    'View Usage' => 'Gebruik bekijken',
    'Used by Entries' => 'Gebruikt door items',
    'Used in Content Fields' => 'Gebruikt in inhoudsvelden',
    'This asset is not used anywhere.' => 'Dit asset wordt nergens gebruikt.',
    
    // Utility Page
    'Scan Now' => 'Nu scannen',
    'Select Volumes' => 'Volumes selecteren',
    'Select All' => 'Alles selecteren',
    'Results' => 'Resultaten',
    'Used Assets' => 'Gebruikte assets',
    'Unused Assets' => 'Ongebruikte assets',
    'Scanning...' => 'Scannen...',
    
    // Bulk Actions
    'Bulk Actions' => 'Bulkacties',
    'Bulk Actions (All Volumes)' => 'Bulkacties (Alle volumes)',
    'Download CSV' => 'CSV downloaden',
    'Download ZIP' => 'ZIP downloaden',
    'Put into Trash' => 'Naar prullenbak verplaatsen',
    'Delete Permanently' => 'Permanent verwijderen',
    
    // Table Headers
    'Title' => 'Titel',
    'Filename' => 'Bestandsnaam',
    'Volume' => 'Volume',
    'Size' => 'Grootte',
    'Path' => 'Pad',
    'Date Created' => 'Aanmaakdatum',
    
    // Messages
    'No assets selected.' => 'Geen assets geselecteerd.',
    'No assets found.' => 'Geen assets gevonden.',
    'Could not create ZIP file.' => 'Kon ZIP-bestand niet maken.',
    'No volumes selected.' => 'Geen volumes geselecteerd.',
    
    // ZIP Download Dialog
    'ZIP Download Options' => 'ZIP-downloadopties',
    'How would you like to organize the files in the ZIP?' => 'Hoe wilt u de bestanden in de ZIP organiseren?',
    'Flat (all files in root)' => 'Plat (alle bestanden in root)',
    'Preserve folder structure' => 'Mapstructuur behouden',
    'Cancel' => 'Annuleren',
    'ZIP download initiated. Large files may take several minutes to prepare.' => 'ZIP-download gestart. Grote bestanden kunnen enkele minuten duren.',
    'Preparing ZIP file... This may take several minutes for large files. Please wait.' => 'ZIP-bestand voorbereiden... Dit kan enkele minuten duren voor grote bestanden. Even geduld.',
    
    // Trash/Delete Messages
    'Are you sure you want to move {count} assets to trash?' => 'Weet u zeker dat u {count} assets naar de prullenbak wilt verplaatsen?',
    'Moved {count} assets to trash.' => '{count} assets naar prullenbak verplaatst.',
    'Permanently deleted {count} assets.' => '{count} assets permanent verwijderd.',
    'WARNING: You are about to permanently delete assets.' => 'WAARSCHUWING: U staat op het punt assets permanent te verwijderen.',
    'This action CANNOT be undone!' => 'Deze actie kan NIET ongedaan worden gemaakt!',
    'We strongly recommend downloading the unused assets as a backup before proceeding.' => 'We raden sterk aan om de ongebruikte assets als backup te downloaden voordat u doorgaat.',
    'Are you absolutely sure you want to permanently delete these assets?' => 'Weet u absoluut zeker dat u deze assets permanent wilt verwijderen?',
    'Final confirmation: Permanently delete assets? This CANNOT be undone!' => 'Laatste bevestiging: Assets permanent verwijderen? Dit kan NIET ongedaan worden gemaakt!',
    
    // Volume Section
    'unused assets' => 'ongebruikte assets',
    'No assets selected in this volume.' => 'Geen assets geselecteerd in dit volume.',
    
    // Errors
    'Failed to scan volumes.' => 'Scannen van volumes mislukt.',
    'Failed to export CSV.' => 'CSV-export mislukt.',
    'Failed to create ZIP file.' => 'Maken van ZIP-bestand mislukt.',
    'Failed to move assets to trash.' => 'Verplaatsen van assets naar prullenbak mislukt.',
    'Failed to delete assets.' => 'Verwijderen van assets mislukt.',
    'Failed to get asset usage.' => 'Ophalen van asset-gebruik mislukt.',

    // Queue Scan
    'Scan queued...' => 'Scan in wachtrij...',
    'Scan failed.' => 'Scan mislukt.',
    'Scanning assets for usage' => 'Assets scannen op gebruik',
    'The queue does not appear to be running. Make sure a queue worker is active (e.g. php craft queue/listen).' => 'De wachtrij lijkt niet actief te zijn. Zorg ervoor dat een queue worker actief is (bijv. php craft queue/listen).',

    // Scan Time
    'Scanned on {date}' => 'Gescand op {date}',
    'Restoring last scan...' => 'Laatste scan herstellen...',

    // Usage Dialog / Scan Options
    'Check Asset Usage' => 'Assetgebruik controleren',
    'Choose how usage should be evaluated for this asset.' => 'Kies hoe het gebruik van dit asset moet worden beoordeeld.',
    'Choose the usage options you want to check, then confirm.' => 'Kies de gebruiksopties die je wilt controleren en bevestig vervolgens.',
    'Include drafts' => 'Concepten opnemen',
    'Include revisions' => 'Revisies opnemen',
    'Count all relational references as usage' => 'Alle relationele verwijzingen als gebruik tellen',
    'Recommended for projects with plugin-defined or unknown element types that may store asset relations outside normal entry content.' => 'Aanbevolen voor projecten met door plugins gedefinieerde of onbekende elementtypes die asset-relaties buiten normale entry-inhoud kunnen opslaan.',
    'Check Usage' => 'Gebruik controleren',
    'Used by Relational Elements' => 'Gebruikt door relationele elementen',
    'Other Relational Elements' => 'Andere relationele elementen',
    'Relational element #{id}' => 'Relationeel element #{id}',
    'Relational element' => 'Relationeel element',
    'Include drafts in this scan' => 'Concepten opnemen in deze scan',
    'When enabled, assets referenced only in drafts may be treated as used.' => 'Wanneer ingeschakeld, kunnen assets die alleen in concepten worden gebruikt als gebruikt worden beschouwd.',
    'Include revisions in this scan' => 'Revisies opnemen in deze scan',
    'When enabled, assets referenced only in revisions may be treated as used.' => 'Wanneer ingeschakeld, kunnen assets die alleen in revisies worden gebruikt als gebruikt worden beschouwd.',
    'When enabled, any row in Craft’s relations table will cause an asset to be treated as used, including references created by plugin-defined or unknown element types. Disable this for a stricter scan.' => 'Wanneer ingeschakeld, zorgt elke rij in de relatietabel van Craft ervoor dat een asset als gebruikt wordt beschouwd, inclusief verwijzingen die zijn gemaakt door door plugins gedefinieerde of onbekende elementtypes. Schakel dit uit voor een strengere scan.',
];
