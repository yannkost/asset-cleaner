<?php

return [
    // General
    'Asset Cleaner' => 'Очищувач ресурсів',
    'An error occurred.' => 'Сталася помилка.',
    'Loading...' => 'Завантаження...',
    
    // View Usage
    'View Usage' => 'Переглянути використання',
    'Used by Entries' => 'Використовується записами',
    'Used in Content Fields' => 'Використовується в полях контенту',
    'This asset is not used anywhere.' => 'Цей ресурс ніде не використовується.',
    
    // Utility Page
    'Scan Now' => 'Сканувати зараз',
    'Select Volumes' => 'Вибрати томи',
    'Select All' => 'Вибрати все',
    'Results' => 'Результати',
    'Used Assets' => 'Використовувані ресурси',
    'Unused Assets' => 'Невикористовувані ресурси',
    'Scanning...' => 'Сканування...',
    
    // Bulk Actions
    'Bulk Actions' => 'Масові дії',
    'Bulk Actions (All Volumes)' => 'Масові дії (Всі томи)',
    'Download CSV' => 'Завантажити CSV',
    'Download ZIP' => 'Завантажити ZIP',
    'Put into Trash' => 'Перемістити в кошик',
    'Delete Permanently' => 'Видалити назавжди',
    
    // Table Headers
    'Title' => 'Заголовок',
    'Filename' => 'Ім\'я файлу',
    'Volume' => 'Том',
    'Size' => 'Розмір',
    'Path' => 'Шлях',
    'Date Created' => 'Дата створення',
    
    // Messages
    'No assets selected.' => 'Ресурси не вибрано.',
    'No assets found.' => 'Ресурси не знайдено.',
    'Could not create ZIP file.' => 'Не вдалося створити ZIP-файл.',
    'No volumes selected.' => 'Томи не вибрано.',
    
    // ZIP Download Dialog
    'ZIP Download Options' => 'Параметри завантаження ZIP',
    'How would you like to organize the files in the ZIP?' => 'Як ви хочете організувати файли в ZIP?',
    'Flat (all files in root)' => 'Плоска структура (всі файли в корені)',
    'Preserve folder structure' => 'Зберегти структуру папок',
    'Cancel' => 'Скасувати',
    'ZIP download initiated. Large files may take several minutes to prepare.' => 'Завантаження ZIP розпочато. Великі файли можуть зайняти кілька хвилин.',
    'Preparing ZIP file... This may take several minutes for large files. Please wait.' => 'Підготовка ZIP-файлу... Це може зайняти кілька хвилин для великих файлів. Будь ласка, зачекайте.',
    
    // Trash/Delete Messages
    'Are you sure you want to move {count} assets to trash?' => 'Ви впевнені, що хочете перемістити {count} ресурсів у кошик?',
    'Moved {count} assets to trash.' => '{count} ресурсів переміщено в кошик.',
    'Permanently deleted {count} assets.' => '{count} ресурсів видалено назавжди.',
    'WARNING: You are about to permanently delete assets.' => 'УВАГА: Ви збираєтесь назавжди видалити ресурси.',
    'This action CANNOT be undone!' => 'Цю дію НЕ МОЖНА скасувати!',
    'We strongly recommend downloading the unused assets as a backup before proceeding.' => 'Ми наполегливо рекомендуємо завантажити невикористовувані ресурси як резервну копію перед продовженням.',
    'Are you absolutely sure you want to permanently delete these assets?' => 'Ви абсолютно впевнені, що хочете назавжди видалити ці ресурси?',
    'Final confirmation: Permanently delete assets? This CANNOT be undone!' => 'Фінальне підтвердження: Видалити ресурси назавжди? Це НЕ МОЖНА скасувати!',
    
    // Volume Section
    'unused assets' => 'невикористовувані ресурси',
    'No assets selected in this volume.' => 'У цьому томі не вибрано ресурсів.',
    
    // Errors
    'Failed to scan volumes.' => 'Не вдалося просканувати томи.',
    'Failed to export CSV.' => 'Не вдалося експортувати CSV.',
    'Failed to create ZIP file.' => 'Не вдалося створити ZIP-файл.',
    'Failed to move assets to trash.' => 'Не вдалося перемістити ресурси в кошик.',
    'Failed to delete assets.' => 'Не вдалося видалити ресурси.',
    'Failed to get asset usage.' => 'Не вдалося отримати інформацію про використання ресурсу.',

    // Queue Scan
    'Scan queued...' => 'Сканування в черзі...',
    'Scan failed.' => 'Сканування не вдалося.',
    'Scanning assets for usage' => 'Сканування використання ресурсів',
    'The queue does not appear to be running. Make sure a queue worker is active (e.g. php craft queue/listen).' => 'Черга, схоже, не запущена. Переконайтеся, що worker черги активний (наприклад, php craft queue/listen).',
];
