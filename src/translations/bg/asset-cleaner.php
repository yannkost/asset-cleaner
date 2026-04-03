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
];
