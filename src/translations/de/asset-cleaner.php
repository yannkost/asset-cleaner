<?php

return [
    // General
    'Asset Cleaner' => 'Asset Cleaner',
    'An error occurred.' => 'Ein Fehler ist aufgetreten.',
    'Loading...' => 'Laden...',
    
    // View Usage
    'View Usage' => 'Verwendung anzeigen',
    'Used by Entries' => 'Verwendet in Einträgen',
    'Used in Content Fields' => 'Verwendet in Inhaltsfeldern',
    'This asset is not used anywhere.' => 'Dieses Asset wird nirgendwo verwendet.',
    
    // Utility Page
    'Scan Now' => 'Jetzt scannen',
    'Select Volumes' => 'Volumes auswählen',
    'Select All' => 'Alle auswählen',
    'Results' => 'Ergebnisse',
    'Used Assets' => 'Verwendete Assets',
    'Unused Assets' => 'Ungenutzte Assets',
    'Scanning...' => 'Scannen...',
    
    // Bulk Actions
    'Bulk Actions' => 'Massenaktionen',
    'Bulk Actions (All Volumes)' => 'Massenaktionen (Alle Volumes)',
    'Bulk Actions - All Selected Volumes' => 'Massenaktionen – Alle ausgewählten Volumes',
    'Download CSV' => 'CSV herunterladen',
    'Download ZIP' => 'ZIP herunterladen',
    'Put into Trash' => 'In den Papierkorb verschieben',
    'Delete Permanently' => 'Endgültig löschen',
    
    // Table Headers
    'Title' => 'Titel',
    'Filename' => 'Dateiname',
    'Volume' => 'Volume',
    'Size' => 'Größe',
    'Path' => 'Pfad',
    'Date Created' => 'Erstellungsdatum',
    
    // Results warning
    'Before permanently deleting' => 'Vor dem endgültigen Löschen',
    'We recommend downloading a ZIP backup of the assets you plan to remove first, or using "Put into Trash" as a safer alternative. Permanent deletions cannot be undone.' => 'Wir empfehlen, zuerst ein ZIP-Backup der zu entfernenden Assets herunterzuladen oder stattdessen "In den Papierkorb" zu verwenden. Endgültige Löschungen können nicht rückgängig gemacht werden.',
    'Scan older than 24h — results may be outdated' => 'Scan älter als 24 Stunden — Ergebnisse könnten veraltet sein',

    // Messages
    'No assets selected.' => 'Keine Assets ausgewählt.',
    'No assets found.' => 'Keine Assets gefunden.',
    'Could not create ZIP file.' => 'ZIP-Datei konnte nicht erstellt werden.',
    'No volumes selected.' => 'Keine Volumes ausgewählt.',
    
    // ZIP Download Dialog
    'ZIP Download Options' => 'ZIP-Download-Optionen',
    'How would you like to organize the files in the ZIP?' => 'Wie möchten Sie die Dateien im ZIP organisieren?',
    'Flat (all files in root)' => 'Flach (alle Dateien im Stammverzeichnis)',
    'Preserve folder structure' => 'Ordnerstruktur beibehalten',
    'Cancel' => 'Abbrechen',
    'ZIP download initiated. Large files may take several minutes to prepare.' => 'ZIP-Download gestartet. Große Dateien können mehrere Minuten dauern.',
    'Preparing ZIP file... This may take several minutes for large files. Please wait.' => 'ZIP-Datei wird vorbereitet... Dies kann bei großen Dateien mehrere Minuten dauern. Bitte warten.',
    
    // Trash/Delete Messages
    'Are you sure you want to move {count} assets to trash?' => 'Möchten Sie wirklich {count} Assets in den Papierkorb verschieben?',
    'Moved {count} assets to trash.' => '{count} Assets in den Papierkorb verschoben.',
    'Permanently deleted {count} assets.' => '{count} Assets endgültig gelöscht.',
    'WARNING: You are about to permanently delete assets.' => 'WARNUNG: Sie sind dabei, Assets endgültig zu löschen.',
    'This action CANNOT be undone!' => 'Diese Aktion kann NICHT rückgängig gemacht werden!',
    'We strongly recommend downloading the unused assets as a backup before proceeding.' => 'Wir empfehlen dringend, die ungenutzten Assets als Backup herunterzuladen, bevor Sie fortfahren.',
    'Are you absolutely sure you want to permanently delete these assets?' => 'Sind Sie absolut sicher, dass Sie diese Assets endgültig löschen möchten?',
    'Final confirmation: Permanently delete assets? This CANNOT be undone!' => 'Letzte Bestätigung: Assets endgültig löschen? Dies kann NICHT rückgängig gemacht werden!',
    'Are you sure you want to permanently delete {count} assets? This action CANNOT be undone! Download a backup (CSV or ZIP) before proceeding.' => 'Möchten Sie wirklich {count} Assets endgültig löschen? Diese Aktion kann NICHT rückgängig gemacht werden! Bitte laden Sie zuerst ein Backup herunter (CSV oder ZIP).',
    'Final confirmation: Permanently delete {count} assets? This CANNOT be undone!' => 'Letzte Bestätigung: {count} Assets endgültig löschen? Dies kann NICHT rückgängig gemacht werden!',
    
    // Volume Section
    'unused assets' => 'ungenutzte Assets',
    '{count} unused assets — {size}' => '{count} ungenutzte Assets — {size}',
    'No unused assets found.' => 'Keine ungenutzten Assets gefunden.',
    'No assets selected in this volume.' => 'Keine Assets in diesem Volume ausgewählt.',
    
    // Errors
    'Failed to scan volumes.' => 'Volumes konnten nicht gescannt werden.',
    'Failed to export CSV.' => 'CSV-Export fehlgeschlagen.',
    'Failed to create ZIP file.' => 'ZIP-Datei konnte nicht erstellt werden.',
    'Failed to move assets to trash.' => 'Assets konnten nicht in den Papierkorb verschoben werden.',
    'Failed to delete assets.' => 'Assets konnten nicht gelöscht werden.',
    'Failed to get asset usage.' => 'Asset-Verwendung konnte nicht abgerufen werden.',

    // Queue Scan
    'Scan queued...' => 'Scan in Warteschlange...',
    'Scan failed.' => 'Scan fehlgeschlagen.',
    'Scanning assets for usage' => 'Assets auf Nutzung prüfen',
    'The queue does not appear to be running. Make sure a queue worker is active (e.g. php craft queue/listen).' => 'Die Warteschlange scheint nicht zu laufen. Stellen Sie sicher, dass ein Queue-Worker aktiv ist (z.B. php craft queue/listen).',

    // Scan Time
    'Scanned on {date}' => 'Gescannt am {date}',
    'Restoring last scan...' => 'Letzten Scan wiederherstellen...',

    // Usage Dialog / Scan Options
    'Check Asset Usage' => 'Asset-Nutzung prüfen',
    'Choose how usage should be evaluated for this asset.' => 'Wählen Sie, wie die Nutzung für dieses Asset bewertet werden soll.',
    'Choose the usage options you want to check, then confirm.' => 'Wählen Sie die Nutzungsoptionen, die Sie prüfen möchten, und bestätigen Sie dann.',
    'Include drafts' => 'Entwürfe einbeziehen',
    'Include revisions' => 'Revisionen einbeziehen',
    'Count all relational references as usage' => 'Alle relationalen Verweise als Nutzung zählen',
    'Recommended for projects with plugin-defined or unknown element types that may store asset relations outside normal entry content.' => 'Empfohlen für Projekte mit plugin-definierten oder unbekannten Elementtypen, die Asset-Beziehungen außerhalb normaler Inhaltseinträge speichern könnten.',
    'Check Usage' => 'Nutzung prüfen',
    'Used by Relational Elements' => 'Verwendet durch relationale Elemente',
    'Other Relational Elements' => 'Andere relationale Elemente',
    'Relational element #{id}' => 'Relationales Element #{id}',
    'Relational element' => 'Relationales Element',
    'Include drafts in this scan' => 'Entwürfe in diesem Scan einbeziehen',
    'When enabled, assets referenced only in drafts may be treated as used.' => 'Wenn aktiviert, können Assets, auf die nur in Entwürfen verwiesen wird, als verwendet behandelt werden.',
    'Include revisions in this scan' => 'Revisionen in diesem Scan einbeziehen',
    'When enabled, assets referenced only in revisions may be treated as used.' => 'Wenn aktiviert, können Assets, auf die nur in Revisionen verwiesen wird, als verwendet behandelt werden.',
    'When enabled, any row in Craft’s relations table will cause an asset to be treated as used, including references created by plugin-defined or unknown element types. Disable this for a stricter scan.' => 'Wenn aktiviert, führt jede Zeile in Crafts Relationstabelle dazu, dass ein Asset als verwendet behandelt wird, einschließlich Verweisen, die von plugin-definierten oder unbekannten Elementtypen erstellt wurden. Deaktivieren Sie dies für einen strengeren Scan.',
];
