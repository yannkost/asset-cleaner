<?php

return [
    // General
    'Asset Cleaner' => 'Asset Cleaner',
    'An error occurred.' => 'Произошла ошибка.',
    'Loading...' => 'Загрузка...',
    
    // View Usage
    'View Usage' => 'Просмотр использования',
    'Used by Entries' => 'Используется записями',
    'Used in Content Fields' => 'Используется в полях контента',
    'This asset is not used anywhere.' => 'Этот ресурс нигде не используется.',
    
    // Utility Page
    'Scan Now' => 'Сканировать сейчас',
    'Select Volumes' => 'Выбрать тома',
    'Select All' => 'Выбрать все',
    'Results' => 'Результаты',
    'Used Assets' => 'Используемые ресурсы',
    'Unused Assets' => 'Неиспользуемые ресурсы',
    'Scanning...' => 'Сканирование...',
    
    // Bulk Actions
    'Bulk Actions' => 'Массовые действия',
    'Bulk Actions (All Volumes)' => 'Массовые действия (Все тома)',
    'Download CSV' => 'Скачать CSV',
    'Download ZIP' => 'Скачать ZIP',
    'Put into Trash' => 'Переместить в корзину',
    'Delete Permanently' => 'Удалить навсегда',
    
    // Table Headers
    'Title' => 'Заголовок',
    'Filename' => 'Имя файла',
    'Volume' => 'Том',
    'Size' => 'Размер',
    'Path' => 'Путь',
    'Date Created' => 'Дата создания',
    
    // Messages
    'No assets selected.' => 'Ресурсы не выбраны.',
    'No assets found.' => 'Ресурсы не найдены.',
    'Could not create ZIP file.' => 'Не удалось создать ZIP-файл.',
    'No volumes selected.' => 'Тома не выбраны.',
    
    // ZIP Download Dialog
    'ZIP Download Options' => 'Параметры загрузки ZIP',
    'How would you like to organize the files in the ZIP?' => 'Как вы хотите организовать файлы в ZIP?',
    'Flat (all files in root)' => 'Плоская структура (все файлы в корне)',
    'Preserve folder structure' => 'Сохранить структуру папок',
    'Cancel' => 'Отмена',
    'ZIP download initiated. Large files may take several minutes to prepare.' => 'Загрузка ZIP начата. Большие файлы могут занять несколько минут.',
    'Preparing ZIP file... This may take several minutes for large files. Please wait.' => 'Подготовка ZIP-файла... Это может занять несколько минут для больших файлов. Пожалуйста, подождите.',
    
    // Trash/Delete Messages
    'Are you sure you want to move {count} assets to trash?' => 'Вы уверены, что хотите переместить {count} ресурсов в корзину?',
    'Moved {count} assets to trash.' => '{count} ресурсов перемещено в корзину.',
    'Permanently deleted {count} assets.' => '{count} ресурсов удалено навсегда.',
    'WARNING: You are about to permanently delete assets.' => 'ВНИМАНИЕ: Вы собираетесь навсегда удалить ресурсы.',
    'This action CANNOT be undone!' => 'Это действие НЕЛЬЗЯ отменить!',
    'We strongly recommend downloading the unused assets as a backup before proceeding.' => 'Мы настоятельно рекомендуем скачать неиспользуемые ресурсы как резервную копию перед продолжением.',
    'Are you absolutely sure you want to permanently delete these assets?' => 'Вы абсолютно уверены, что хотите навсегда удалить эти ресурсы?',
    'Final confirmation: Permanently delete assets? This CANNOT be undone!' => 'Финальное подтверждение: Удалить ресурсы навсегда? Это НЕЛЬЗЯ отменить!',
    
    // Volume Section
    'unused assets' => 'неиспользуемые ресурсы',
    'No assets selected in this volume.' => 'В этом томе не выбрано ресурсов.',
    
    // Errors
    'Failed to scan volumes.' => 'Не удалось просканировать тома.',
    'Failed to export CSV.' => 'Не удалось экспортировать CSV.',
    'Failed to create ZIP file.' => 'Не удалось создать ZIP-файл.',
    'Failed to move assets to trash.' => 'Не удалось переместить ресурсы в корзину.',
    'Failed to delete assets.' => 'Не удалось удалить ресурсы.',
    'Failed to get asset usage.' => 'Не удалось получить информацию об использовании ресурса.',

    // Queue Scan
    'Scan queued...' => 'Сканирование в очереди...',
    'Scan failed.' => 'Сканирование не удалось.',
    'Scanning assets for usage' => 'Сканирование использования ресурсов',
    'The queue does not appear to be running. Make sure a queue worker is active (e.g. php craft queue/listen).' => 'Очередь, похоже, не запущена. Убедитесь, что worker очереди активен (например, php craft queue/listen).',

    // Scan Time
    'Scanned on {date}' => 'Сканирование выполнено {date}',
    'Restoring last scan...' => 'Восстановление последнего сканирования...',
    // Usage Dialog / Scan Options
    'Check Asset Usage' => 'Проверить использование ассета',
    'Choose how usage should be evaluated for this asset.' => 'Выберите, как следует оценивать использование этого ассета.',
    'Choose the usage options you want to check, then confirm.' => 'Выберите параметры использования, которые нужно проверить, затем подтвердите.',
    'Include drafts' => 'Включить черновики',
    'Include revisions' => 'Включить ревизии',
    'Count all relational references as usage' => 'Считать все реляционные ссылки использованием',
    'Recommended for projects with plugin-defined or unknown element types that may store asset relations outside normal entry content.' => 'Рекомендуется для проектов с определёнными плагинами или неизвестными типами элементов, которые могут хранить связи ассетов вне обычного содержимого записей.',
    'Check Usage' => 'Проверить использование',
    'Used by Relational Elements' => 'Используется реляционными элементами',
    'Other Relational Elements' => 'Другие реляционные элементы',
    'Relational element #{id}' => 'Реляционный элемент #{id}',
    'Relational element' => 'Реляционный элемент',
    'Include drafts in this scan' => 'Включить черновики в это сканирование',
    'When enabled, assets referenced only in drafts may be treated as used.' => 'Если включено, ассеты, на которые есть ссылки только в черновиках, могут считаться используемыми.',
    'Include revisions in this scan' => 'Включить ревизии в это сканирование',
    'When enabled, assets referenced only in revisions may be treated as used.' => 'Если включено, ассеты, на которые есть ссылки только в ревизиях, могут считаться используемыми.',
    'When enabled, any row in Craft’s relations table will cause an asset to be treated as used, including references created by plugin-defined or unknown element types. Disable this for a stricter scan.' => 'Если включено, любая строка в таблице relations Craft приведёт к тому, что ассет будет считаться используемым, включая ссылки, созданные определёнными плагинами или неизвестными типами элементов. Отключите это для более строгого сканирования.',

    // Settings - Scan performance
    'Scan performance' => 'Производительность сканирования',
    'Relation batch size' => 'Размер пакета связей',
    'Maximum number of assets loaded for relation scanning per queue execution. Lower this (e.g. to 500) on sites with heavy or deeply nested relations if scan jobs time out. You can also override this with `relationBatchSize` in `config/asset-cleaner.php` or the `ASSET_CLEANER_RELATION_BATCH_SIZE` environment variable.' => 'Максимальное количество ассетов, загружаемых для сканирования связей за одно выполнение очереди. Уменьшите это значение (например, до 500) на сайтах с многочисленными или глубоко вложенными связями, если задачи сканирования завершаются по тайм-ауту. Значение также можно переопределить через `relationBatchSize` в `config/asset-cleaner.php` или переменную окружения `ASSET_CLEANER_RELATION_BATCH_SIZE`.',
    'Relation time budget (seconds)' => 'Лимит времени для связей (секунды)',
    'Wall-clock budget for the relation stage of a single queue execution. Once exceeded, the job stops and re-queues to continue, keeping each execution safely under the queue’s time-to-reserve (TTR, 300s by default). Keep this comfortably below your TTR. You can also override this with `relationTimeBudgetSeconds` in `config/asset-cleaner.php` or the `ASSET_CLEANER_RELATION_TIME_BUDGET` environment variable.' => 'Лимит реального времени для этапа связей одного выполнения очереди. При его превышении задача останавливается и снова ставится в очередь для продолжения, поэтому каждое выполнение гарантированно остаётся ниже time-to-reserve очереди (TTR, по умолчанию 300 с). Держите это значение заметно ниже вашего TTR. Значение также можно переопределить через `relationTimeBudgetSeconds` в `config/asset-cleaner.php` или переменную окружения `ASSET_CLEANER_RELATION_TIME_BUDGET`.',
];
