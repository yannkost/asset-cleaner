<?php

return [
    // General
    'Asset Cleaner' => 'Asset Cleaner',
    'An error occurred.' => 'Wystąpił błąd.',
    'Loading...' => 'Ładowanie...',
    
    // View Usage
    'View Usage' => 'Zobacz użycie',
    'Used by Entries' => 'Używane przez wpisy',
    'Used in Content Fields' => 'Używane w polach treści',
    'This asset is not used anywhere.' => 'Ten zasób nie jest nigdzie używany.',
    
    // Utility Page
    'Scan Now' => 'Skanuj teraz',
    'Select Volumes' => 'Wybierz woluminy',
    'Select All' => 'Zaznacz wszystko',
    'Results' => 'Wyniki',
    'Used Assets' => 'Używane zasoby',
    'Unused Assets' => 'Nieużywane zasoby',
    'Scanning...' => 'Skanowanie...',
    
    // Bulk Actions
    'Bulk Actions' => 'Akcje masowe',
    'Bulk Actions (All Volumes)' => 'Akcje masowe (Wszystkie woluminy)',
    'Download CSV' => 'Pobierz CSV',
    'Download ZIP' => 'Pobierz ZIP',
    'Put into Trash' => 'Przenieś do kosza',
    'Delete Permanently' => 'Usuń na stałe',
    
    // Table Headers
    'Title' => 'Tytuł',
    'Filename' => 'Nazwa pliku',
    'Volume' => 'Wolumin',
    'Size' => 'Rozmiar',
    'Path' => 'Ścieżka',
    'Date Created' => 'Data utworzenia',
    
    // Messages
    'No assets selected.' => 'Nie wybrano zasobów.',
    'No assets found.' => 'Nie znaleziono zasobów.',
    'Could not create ZIP file.' => 'Nie można utworzyć pliku ZIP.',
    'No volumes selected.' => 'Nie wybrano woluminów.',
    
    // ZIP Download Dialog
    'ZIP Download Options' => 'Opcje pobierania ZIP',
    'How would you like to organize the files in the ZIP?' => 'Jak chcesz zorganizować pliki w archiwum ZIP?',
    'Flat (all files in root)' => 'Płasko (wszystkie pliki w katalogu głównym)',
    'Preserve folder structure' => 'Zachowaj strukturę folderów',
    'Cancel' => 'Anuluj',
    'ZIP download initiated. Large files may take several minutes to prepare.' => 'Pobieranie ZIP rozpoczęte. Duże pliki mogą wymagać kilku minut.',
    'Preparing ZIP file... This may take several minutes for large files. Please wait.' => 'Przygotowywanie pliku ZIP... Może to potrwać kilka minut dla dużych plików. Proszę czekać.',
    
    // Trash/Delete Messages
    'Are you sure you want to move {count} assets to trash?' => 'Czy na pewno chcesz przenieść {count} zasobów do kosza?',
    'Moved {count} assets to trash.' => 'Przeniesiono {count} zasobów do kosza.',
    'Permanently deleted {count} assets.' => 'Trwale usunięto {count} zasobów.',
    'WARNING: You are about to permanently delete assets.' => 'OSTRZEŻENIE: Zamierzasz trwale usunąć zasoby.',
    'This action CANNOT be undone!' => 'Ta akcja NIE może być cofnięta!',
    'We strongly recommend downloading the unused assets as a backup before proceeding.' => 'Zdecydowanie zalecamy pobranie nieużywanych zasobów jako kopii zapasowej przed kontynuowaniem.',
    'Are you absolutely sure you want to permanently delete these assets?' => 'Czy jesteś absolutnie pewien, że chcesz trwale usunąć te zasoby?',
    'Final confirmation: Permanently delete assets? This CANNOT be undone!' => 'Ostateczne potwierdzenie: Trwale usunąć zasoby? NIE można tego cofnąć!',
    
    // Volume Section
    'unused assets' => 'nieużywane zasoby',
    'No assets selected in this volume.' => 'Nie wybrano zasobów w tym woluminie.',
    
    // Errors
    'Failed to scan volumes.' => 'Nie udało się przeskanować woluminów.',
    'Failed to export CSV.' => 'Nie udało się wyeksportować CSV.',
    'Failed to create ZIP file.' => 'Nie udało się utworzyć pliku ZIP.',
    'Failed to move assets to trash.' => 'Nie udało się przenieść zasobów do kosza.',
    'Failed to delete assets.' => 'Nie udało się usunąć zasobów.',
    'Failed to get asset usage.' => 'Nie udało się pobrać informacji o użyciu zasobu.',

    // Queue Scan
    'Scan queued...' => 'Skanowanie w kolejce...',
    'Scan failed.' => 'Skanowanie nie powiodło się.',
    'Scanning assets for usage' => 'Skanowanie użycia zasobów',
    'The queue does not appear to be running. Make sure a queue worker is active (e.g. php craft queue/listen).' => 'Kolejka nie wydaje się być uruchomiona. Upewnij się, że worker kolejki jest aktywny (np. php craft queue/listen).',

    // Scan Time
    'Scanned on {date}' => 'Zeskanowano dnia {date}',
    'Restoring last scan...' => 'Przywracanie ostatniego skanowania...',

    // Usage Dialog / Scan Options
    'Check Asset Usage' => 'Sprawdź użycie zasobu',
    'Choose how usage should be evaluated for this asset.' => 'Wybierz, jak powinno być oceniane użycie tego zasobu.',
    'Choose the usage options you want to check, then confirm.' => 'Wybierz opcje użycia, które chcesz sprawdzić, a następnie potwierdź.',
    'Include drafts' => 'Uwzględnij szkice',
    'Include revisions' => 'Uwzględnij rewizje',
    'Count all relational references as usage' => 'Traktuj wszystkie powiązania relacyjne jako użycie',
    'Recommended for projects with plugin-defined or unknown element types that may store asset relations outside normal entry content.' => 'Zalecane dla projektów z typami elementów zdefiniowanymi przez wtyczki lub nieznanymi, które mogą przechowywać powiązania zasobów poza zwykłą treścią wpisów.',
    'Check Usage' => 'Sprawdź użycie',
    'Used by Relational Elements' => 'Używane przez elementy relacyjne',
    'Other Relational Elements' => 'Inne elementy relacyjne',
    'Relational element #{id}' => 'Element relacyjny #{id}',
    'Relational element' => 'Element relacyjny',
    'Include drafts in this scan' => 'Uwzględnij szkice w tym skanowaniu',
    'When enabled, assets referenced only in drafts may be treated as used.' => 'Po włączeniu zasoby, do których odwołują się wyłącznie szkice, mogą być traktowane jako używane.',
    'Include revisions in this scan' => 'Uwzględnij rewizje w tym skanowaniu',
    'When enabled, assets referenced only in revisions may be treated as used.' => 'Po włączeniu zasoby, do których odwołują się wyłącznie rewizje, mogą być traktowane jako używane.',
    'When enabled, any row in Craft’s relations table will cause an asset to be treated as used, including references created by plugin-defined or unknown element types. Disable this for a stricter scan.' => 'Po włączeniu każdy wiersz w tabeli relacji Craft sprawi, że zasób będzie traktowany jako używany, w tym odwołania utworzone przez typy elementów zdefiniowane przez wtyczki lub nieznane. Wyłącz tę opcję, aby skanowanie było bardziej rygorystyczne.',

    // Settings - Scan performance
    'Scan performance' => 'Wydajność skanowania',
    'Relation batch size' => 'Rozmiar partii relacji',
    'Maximum number of assets loaded for relation scanning per queue execution. Lower this (e.g. to 500) on sites with heavy or deeply nested relations if scan jobs time out. You can also override this with `relationBatchSize` in `config/asset-cleaner.php` or the `ASSET_CLEANER_RELATION_BATCH_SIZE` environment variable.' => 'Maksymalna liczba zasobów ładowanych do skanowania relacji podczas jednego wykonania kolejki. Zmniejsz tę wartość (np. do 500) w witrynach z licznymi lub głęboko zagnieżdżonymi relacjami, jeśli zadania skanowania przekraczają limit czasu. Wartość można też nadpisać przez `relationBatchSize` w `config/asset-cleaner.php` lub zmienną środowiskową `ASSET_CLEANER_RELATION_BATCH_SIZE`.',
    'Relation time budget (seconds)' => 'Budżet czasu relacji (sekundy)',
    'Wall-clock budget for the relation stage of a single queue execution. Once exceeded, the job stops and re-queues to continue, keeping each execution safely under the queue’s time-to-reserve (TTR, 300s by default). Keep this comfortably below your TTR. You can also override this with `relationTimeBudgetSeconds` in `config/asset-cleaner.php` or the `ASSET_CLEANER_RELATION_TIME_BUDGET` environment variable.' => 'Rzeczywisty budżet czasu dla etapu relacji pojedynczego wykonania kolejki. Po jego przekroczeniu zadanie zatrzymuje się i ponownie trafia do kolejki, aby kontynuować, dzięki czemu każde wykonanie pozostaje bezpiecznie poniżej time-to-reserve (TTR, domyślnie 300 s) kolejki. Utrzymuj tę wartość wyraźnie poniżej swojego TTR. Wartość można też nadpisać przez `relationTimeBudgetSeconds` w `config/asset-cleaner.php` lub zmienną środowiskową `ASSET_CLEANER_RELATION_TIME_BUDGET`.',

    // Settings page, queue job descriptions, scan stage labels, usage labels
    'Scan storage mode' => 'Tryb przechowywania skanów',
    'File-based' => 'Plikowy',
    'Database-based' => 'Bazodanowy',
    'Choose how Asset Cleaner stores transient scan state. File-based storage is the default and works well when web and queue workers share a filesystem. Database-based storage is better suited for containerized or cloud-style environments where shared filesystem access is not guaranteed.' => 'Określa, jak Asset Cleaner przechowuje tymczasowy stan skanowania. Przechowywanie plikowe jest domyślne i sprawdza się, gdy workery web i kolejki współdzielą system plików. Przechowywanie w bazie danych lepiej pasuje do środowisk kontenerowych lub chmurowych, gdzie współdzielony dostęp do systemu plików nie jest gwarantowany.',
    'File-based scan workspace path' => 'Ścieżka plikowego obszaru roboczego skanowania',
    'Optional. Only used when scan storage mode is set to File-based. Defaults to `@storage/asset-cleaner`. You can also override this with `scanWorkspacePath` in `config/asset-cleaner.php` or the `ASSET_CLEANER_SCAN_PATH` environment variable.' => 'Opcjonalne. Używane tylko, gdy tryb przechowywania jest ustawiony na „Plikowy". Domyślnie `@storage/asset-cleaner`. Wartość można też nadpisać przez `scanWorkspacePath` w `config/asset-cleaner.php` lub zmienną środowiskową `ASSET_CLEANER_SCAN_PATH`.',
    'Include drafts by default' => 'Domyślnie uwzględniaj szkice',
    'When enabled, assets referenced only in drafts may be treated as used during scans. This value acts as the default for new scans and can be overridden per scan from the utility page. You can also override this with `includeDraftsByDefault` in `config/asset-cleaner.php` or the `ASSET_CLEANER_INCLUDE_DRAFTS` environment variable.' => 'Gdy włączone, zasoby, do których odwołują się tylko szkice, mogą być traktowane jako używane podczas skanowania. Ta wartość jest domyślna dla nowych skanów i można ją nadpisać dla pojedynczego skanu na stronie narzędzia. Można ją też nadpisać przez `includeDraftsByDefault` w `config/asset-cleaner.php` lub zmienną środowiskową `ASSET_CLEANER_INCLUDE_DRAFTS`.',
    'Include revisions by default' => 'Domyślnie uwzględniaj rewizje',
    'When enabled, assets referenced only in revisions may be treated as used during scans. This value acts as the default for new scans and can be overridden per scan from the utility page. You can also override this with `includeRevisionsByDefault` in `config/asset-cleaner.php` or the `ASSET_CLEANER_INCLUDE_REVISIONS` environment variable.' => 'Gdy włączone, zasoby, do których odwołują się tylko rewizje, mogą być traktowane jako używane podczas skanowania. Ta wartość jest domyślna dla nowych skanów i można ją nadpisać dla pojedynczego skanu na stronie narzędzia. Można ją też nadpisać przez `includeRevisionsByDefault` w `config/asset-cleaner.php` lub zmienną środowiskową `ASSET_CLEANER_INCLUDE_REVISIONS`.',
    'Notes:' => 'Uwagi:',
    'Only the latest scan is retained for restore/export workflows.' => 'Do przywracania/eksportu zachowywany jest tylko ostatni skan.',
    'When using File-based storage in multi-container setups, make sure the configured workspace path is shared between web and queue workers.' => 'Przy przechowywaniu plikowym w konfiguracjach wielokontenerowych upewnij się, że skonfigurowana ścieżka obszaru roboczego jest współdzielona między workerami web i kolejki.',
    'Config file values override these control panel settings.' => 'Wartości z pliku konfiguracyjnego mają pierwszeństwo przed tymi ustawieniami panelu.',
    'Draft and revision handling can be configured globally here and overridden per scan from the utility page.' => 'Obsługę szkiców i rewizji można skonfigurować tu globalnie i nadpisać dla pojedynczego skanu na stronie narzędzia.',
    'Preparing asset scan' => 'Przygotowywanie skanowania zasobów',
    'Scanning asset relations' => 'Skanowanie relacji zasobów',
    'Scanning content for asset references' => 'Skanowanie treści w poszukiwaniu odwołań do zasobów',
    'Finalizing asset scan results' => 'Finalizowanie wyników skanowania zasobów',
    'Preparing asset snapshot...' => 'Przygotowywanie migawki zasobów...',
    'Scanning relations...' => 'Skanowanie relacji...',
    'Scanning content...' => 'Skanowanie treści...',
    'Finalizing results...' => 'Finalizowanie wyników...',
    'User profile picture' => 'Zdjęcie profilowe użytkownika',
    'User #{id}' => 'Użytkownik #{id}',
    'Relational source #{id}' => 'Źródło relacyjne #{id}',
    'Used by relational element #{id}' => 'Używany przez element relacyjny #{id}',

    // Bulk delete confirmations and scan status messages
    'Are you sure you want to permanently delete {count} assets? This action CANNOT be undone! Download a backup (CSV or ZIP) before proceeding.' => 'Czy na pewno chcesz trwale usunąć {count} zasobów? Tej akcji NIE można cofnąć! Przed kontynuowaniem pobierz kopię zapasową (CSV lub ZIP).',
    'Before permanently deleting' => 'Przed trwałym usunięciem',
    'Bulk Actions - All Selected Volumes' => 'Akcje zbiorcze - Wszystkie wybrane wolumeny',
    'Final confirmation: Permanently delete {count} assets? This CANNOT be undone!' => 'Ostateczne potwierdzenie: trwale usunąć {count} zasobów? Tego NIE można cofnąć!',
    'Lost contact while polling scan progress. The scan may still be running.' => 'Utracono połączenie podczas sprawdzania postępu skanowania. Skanowanie może nadal trwać.',
    'No unused assets found.' => 'Nie znaleziono nieużywanych zasobów.',
    'Scan older than 24h — results may be outdated' => 'Skan starszy niż 24 h — wyniki mogą być nieaktualne',
    'We recommend downloading a ZIP backup of the assets you plan to remove first, or using "Put into Trash" as a safer alternative. Permanent deletions cannot be undone.' => 'Zalecamy najpierw pobrać kopię ZIP zasobów, które planujesz usunąć, lub użyć „Przenieś do kosza" jako bezpieczniejszej alternatywy. Trwałych usunięć nie można cofnąć.',
    '{count} unused assets — {size}' => '{count} nieużywanych zasobów — {size}',
];
