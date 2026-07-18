<?php

return [
    // General
    'Asset Cleaner' => 'Почистване на ресурси',
    'An error occurred.' => 'Възникна грешка.',
    'Loading...' => 'Зареждане...',
    
    // View Usage
    'View Usage' => 'Преглед на използване',
    'Used by Entries' => 'Използва се от записи',
    'Used in Content Fields' => 'Използва се в полета за съдържание',
    'This asset is not used anywhere.' => 'Този ресурс не се използва никъде.',
    
    // Utility Page
    'Scan Now' => 'Сканирай сега',
    'Select Volumes' => 'Избери томове',
    'Select All' => 'Избери всички',
    'Results' => 'Резултати',
    'Used Assets' => 'Използвани ресурси',
    'Unused Assets' => 'Неизползвани ресурси',
    'Scanning...' => 'Сканиране...',
    
    // Bulk Actions
    'Bulk Actions' => 'Масови действия',
    'Bulk Actions (All Volumes)' => 'Масови действия (Всички томове)',
    'Download CSV' => 'Изтегли CSV',
    'Download ZIP' => 'Изтегли ZIP',
    'Put into Trash' => 'Премести в кошчето',
    'Delete Permanently' => 'Изтрий завинаги',
    
    // Table Headers
    'Title' => 'Заглавие',
    'Filename' => 'Име на файл',
    'Volume' => 'Том',
    'Size' => 'Размер',
    'Path' => 'Път',
    'Date Created' => 'Дата на създаване',
    
    // Messages
    'No assets selected.' => 'Няма избрани ресурси.',
    'No assets found.' => 'Няма намерени ресурси.',
    'Could not create ZIP file.' => 'Не може да се създаде ZIP файл.',
    'No volumes selected.' => 'Няма избрани томове.',
    
    // ZIP Download Dialog
    'ZIP Download Options' => 'Опции за изтегляне на ZIP',
    'How would you like to organize the files in the ZIP?' => 'Как искате да организирате файловете в ZIP?',
    'Flat (all files in root)' => 'Плоска структура (всички файлове в корена)',
    'Preserve folder structure' => 'Запази структурата на папките',
    'Cancel' => 'Отказ',
    'ZIP download initiated. Large files may take several minutes to prepare.' => 'Изтеглянето на ZIP започна. Големите файлове може да отнемат няколко минути.',
    'Preparing ZIP file... This may take several minutes for large files. Please wait.' => 'Подготовка на ZIP файл... Това може да отнеме няколко минути за големи файлове. Моля, изчакайте.',
    
    // Trash/Delete Messages
    'Are you sure you want to move {count} assets to trash?' => 'Сигурни ли сте, че искате да преместите {count} ресурса в кошчето?',
    'Moved {count} assets to trash.' => '{count} ресурса преместени в кошчето.',
    'Permanently deleted {count} assets.' => '{count} ресурса изтрити завинаги.',
    'WARNING: You are about to permanently delete assets.' => 'ВНИМАНИЕ: На път сте да изтриете ресурси завинаги.',
    'This action CANNOT be undone!' => 'Това действие НЕ МОЖЕ да бъде отменено!',
    'We strongly recommend downloading the unused assets as a backup before proceeding.' => 'Силно препоръчваме да изтеглите неизползваните ресурси като резервно копие преди да продължите.',
    'Are you absolutely sure you want to permanently delete these assets?' => 'Абсолютно сигурни ли сте, че искате да изтриете тези ресурси завинаги?',
    'Final confirmation: Permanently delete assets? This CANNOT be undone!' => 'Финално потвърждение: Изтриване на ресурси завинаги? Това НЕ МОЖЕ да бъде отменено!',
    
    // Volume Section
    'unused assets' => 'неизползвани ресурси',
    'No assets selected in this volume.' => 'Няма избрани ресурси в този том.',
    
    // Errors
    'Failed to scan volumes.' => 'Неуспешно сканиране на томове.',
    'Failed to export CSV.' => 'Неуспешен експорт на CSV.',
    'Failed to create ZIP file.' => 'Неуспешно създаване на ZIP файл.',
    'Failed to move assets to trash.' => 'Неуспешно преместване на ресурси в кошчето.',
    'Failed to delete assets.' => 'Неуспешно изтриване на ресурси.',
    'Failed to get asset usage.' => 'Неуспешно получаване на информация за използване на ресурс.',

    // Queue Scan
    'Scan queued...' => 'Сканирането е в опашката...',
    'Scan failed.' => 'Сканирането се провали.',
    'Scanning assets for usage' => 'Сканиране на използването на ресурси',
    'The queue does not appear to be running. Make sure a queue worker is active (e.g. php craft queue/listen).' => 'Опашката изглежда не работи. Уверете се, че worker на опашката е активен (напр. php craft queue/listen).',

    // Scan Time
    'Scanned on {date}' => 'Сканирано на {date}',
    'Restoring last scan...' => 'Възстановяване на последното сканиране...',

    // Usage Dialog / Scan Options
    'Check Asset Usage' => 'Проверка на използването на актива',
    'Choose how usage should be evaluated for this asset.' => 'Изберете как да бъде оценено използването на този актив.',
    'Choose the usage options you want to check, then confirm.' => 'Изберете опциите за използване, които искате да проверите, след което потвърдете.',
    'Include drafts' => 'Включване на чернови',
    'Include revisions' => 'Включване на ревизии',
    'Count all relational references as usage' => 'Отчитане на всички релационни препратки като използване',
    'Recommended for projects with plugin-defined or unknown element types that may store asset relations outside normal entry content.' => 'Препоръчително за проекти с дефинирани от плъгини или неизвестни типове елементи, които може да съхраняват връзки към активи извън обичайното съдържание на записите.',
    'Check Usage' => 'Провери използването',
    'Used by Relational Elements' => 'Използва се от релационни елементи',
    'Other Relational Elements' => 'Други релационни елементи',
    'Relational element #{id}' => 'Релационен елемент №{id}',
    'Relational element' => 'Релационен елемент',
    'Include drafts in this scan' => 'Включи черновите в това сканиране',
    'When enabled, assets referenced only in drafts may be treated as used.' => 'Когато е активирано, активите, използвани само в чернови, могат да се считат за използвани.',
    'Include revisions in this scan' => 'Включи ревизиите в това сканиране',
    'When enabled, assets referenced only in revisions may be treated as used.' => 'Когато е активирано, активите, използвани само в ревизии, могат да се считат за използвани.',
    'When enabled, any row in Craft’s relations table will cause an asset to be treated as used, including references created by plugin-defined or unknown element types. Disable this for a stricter scan.' => 'Когато е активирано, всеки ред в таблицата с релации на Craft ще кара актива да се счита за използван, включително препратки, създадени от дефинирани от плъгини или неизвестни типове елементи. Изключете това за по-строго сканиране.',

    // Settings - Scan performance
    'Scan performance' => 'Производителност на сканирането',
    'Relation batch size' => 'Размер на партидата релации',
    'Maximum number of assets loaded for relation scanning per queue execution. Lower this (e.g. to 500) on sites with heavy or deeply nested relations if scan jobs time out. You can also override this with `relationBatchSize` in `config/asset-cleaner.php` or the `ASSET_CLEANER_RELATION_BATCH_SIZE` environment variable.' => 'Максимален брой активи, зареждани за сканиране на релации при едно изпълнение от опашката. Намалете тази стойност (напр. до 500) при сайтове с много или дълбоко вложени релации, ако задачите за сканиране изтичат по време. Можете също да я презапишете с `relationBatchSize` в `config/asset-cleaner.php` или променливата на средата `ASSET_CLEANER_RELATION_BATCH_SIZE`.',
    'Relation time budget (seconds)' => 'Времеви бюджет за релации (секунди)',
    'Wall-clock budget for the relation stage of a single queue execution. Once exceeded, the job stops and re-queues to continue, keeping each execution safely under the queue’s time-to-reserve (TTR, 300s by default). Keep this comfortably below your TTR. You can also override this with `relationTimeBudgetSeconds` in `config/asset-cleaner.php` or the `ASSET_CLEANER_RELATION_TIME_BUDGET` environment variable.' => 'Реален времеви бюджет за етапа на релациите при едно изпълнение от опашката. След превишаването му задачата спира и се нарежда отново на опашката, за да продължи, така че всяко изпълнение остава сигурно под time-to-reserve (TTR, по подразбиране 300 s) на опашката. Дръжте тази стойност значително под вашия TTR. Можете също да я презапишете с `relationTimeBudgetSeconds` в `config/asset-cleaner.php` или променливата на средата `ASSET_CLEANER_RELATION_TIME_BUDGET`.',

    // Settings page, queue job descriptions, scan stage labels, usage labels
    'Scan storage mode' => 'Режим на съхранение на сканиранията',
    'File-based' => 'Файлово',
    'Database-based' => 'В база данни',
    'Choose how Asset Cleaner stores transient scan state. File-based storage is the default and works well when web and queue workers share a filesystem. Database-based storage is better suited for containerized or cloud-style environments where shared filesystem access is not guaranteed.' => 'Определя как Asset Cleaner съхранява временното състояние на сканирането. Файловото съхранение е по подразбиране и работи добре, когато уеб и опашковите работници споделят файлова система. Съхранението в база данни е по-подходящо за контейнерни или облачни среди, където споделен достъп до файловата система не е гарантиран.',
    'File-based scan workspace path' => 'Път до файловото работно пространство за сканиране',
    'Optional. Only used when scan storage mode is set to File-based. Defaults to `@storage/asset-cleaner`. You can also override this with `scanWorkspacePath` in `config/asset-cleaner.php` or the `ASSET_CLEANER_SCAN_PATH` environment variable.' => 'Незадължително. Използва се само когато режимът на съхранение е „Файлово". По подразбиране е `@storage/asset-cleaner`. Можете също да го презапишете със `scanWorkspacePath` в `config/asset-cleaner.php` или променливата на средата `ASSET_CLEANER_SCAN_PATH`.',
    'Include drafts by default' => 'Включване на чернови по подразбиране',
    'When enabled, assets referenced only in drafts may be treated as used during scans. This value acts as the default for new scans and can be overridden per scan from the utility page. You can also override this with `includeDraftsByDefault` in `config/asset-cleaner.php` or the `ASSET_CLEANER_INCLUDE_DRAFTS` environment variable.' => 'Когато е активирано, активи, реферирани само в чернови, могат да се считат за използвани по време на сканиране. Тази стойност е по подразбиране за нови сканирания и може да бъде презаписана за всяко сканиране от страницата на инструмента. Можете също да я презапишете с `includeDraftsByDefault` в `config/asset-cleaner.php` или променливата на средата `ASSET_CLEANER_INCLUDE_DRAFTS`.',
    'Include revisions by default' => 'Включване на ревизии по подразбиране',
    'When enabled, assets referenced only in revisions may be treated as used during scans. This value acts as the default for new scans and can be overridden per scan from the utility page. You can also override this with `includeRevisionsByDefault` in `config/asset-cleaner.php` or the `ASSET_CLEANER_INCLUDE_REVISIONS` environment variable.' => 'Когато е активирано, активи, реферирани само в ревизии, могат да се считат за използвани по време на сканиране. Тази стойност е по подразбиране за нови сканирания и може да бъде презаписана за всяко сканиране от страницата на инструмента. Можете също да я презапишете с `includeRevisionsByDefault` в `config/asset-cleaner.php` или променливата на средата `ASSET_CLEANER_INCLUDE_REVISIONS`.',
    'Notes:' => 'Бележки:',
    'Only the latest scan is retained for restore/export workflows.' => 'За възстановяване/експорт се запазва само последното сканиране.',
    'When using File-based storage in multi-container setups, make sure the configured workspace path is shared between web and queue workers.' => 'При файлово съхранение в многоконтейнерни конфигурации се уверете, че конфигурираният път на работното пространство е споделен между уеб и опашковите работници.',
    'Config file values override these control panel settings.' => 'Стойностите от конфигурационния файл имат предимство пред тези настройки в контролния панел.',
    'Draft and revision handling can be configured globally here and overridden per scan from the utility page.' => 'Обработката на чернови и ревизии може да се конфигурира глобално тук и да се презапише за всяко сканиране от страницата на инструмента.',
    'Preparing asset scan' => 'Подготовка на сканирането на активи',
    'Scanning asset relations' => 'Сканиране на релациите на активите',
    'Scanning content for asset references' => 'Сканиране на съдържанието за препратки към активи',
    'Finalizing asset scan results' => 'Финализиране на резултатите от сканирането на активи',
    'Preparing asset snapshot...' => 'Подготовка на моментна снимка на активите...',
    'Scanning relations...' => 'Сканиране на релации...',
    'Scanning content...' => 'Сканиране на съдържание...',
    'Finalizing results...' => 'Финализиране на резултатите...',
    'User profile picture' => 'Профилна снимка на потребителя',
    'User #{id}' => 'Потребител #{id}',
    'Relational source #{id}' => 'Релационен източник #{id}',
    'Used by relational element #{id}' => 'Използва се от релационен елемент #{id}',

    // Bulk delete confirmations and scan status messages
    'Are you sure you want to permanently delete {count} assets? This action CANNOT be undone! Download a backup (CSV or ZIP) before proceeding.' => 'Сигурни ли сте, че искате да изтриете завинаги {count} активи? Това действие НЕ МОЖЕ да бъде отменено! Изтеглете резервно копие (CSV или ZIP), преди да продължите.',
    'Before permanently deleting' => 'Преди окончателното изтриване',
    'Bulk Actions - All Selected Volumes' => 'Масови действия - Всички избрани томове',
    'Final confirmation: Permanently delete {count} assets? This CANNOT be undone!' => 'Финално потвърждение: да се изтрият завинаги {count} активи? Това НЕ МОЖЕ да бъде отменено!',
    'Lost contact while polling scan progress. The scan may still be running.' => 'Загубена връзка при проверка на напредъка на сканирането. Сканирането може все още да се изпълнява.',
    'No unused assets found.' => 'Не са намерени неизползвани активи.',
    'Scan older than 24h — results may be outdated' => 'Сканиране отпреди повече от 24 ч — резултатите може да са остарели',
    'We recommend downloading a ZIP backup of the assets you plan to remove first, or using "Put into Trash" as a safer alternative. Permanent deletions cannot be undone.' => 'Препоръчваме първо да изтеглите ZIP резервно копие на активите, които планирате да премахнете, или да използвате „Преместване в кошчето" като по-безопасна алтернатива. Окончателните изтривания не могат да бъдат отменени.',
    '{count} unused assets — {size}' => '{count} неизползвани активи — {size}',
];
