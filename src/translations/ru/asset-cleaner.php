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
];
