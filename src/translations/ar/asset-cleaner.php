<?php

return [
    // General
    'Asset Cleaner' => 'منظف الأصول',
    'An error occurred.' => 'حدث خطأ.',
    'Loading...' => 'جاري التحميل...',
    
    // View Usage
    'View Usage' => 'عرض الاستخدام',
    'Used by Entries' => 'مستخدم في المدخلات',
    'Used in Content Fields' => 'مستخدم في حقول المحتوى',
    'This asset is not used anywhere.' => 'هذا الأصل غير مستخدم في أي مكان.',
    
    // Utility Page
    'Scan Now' => 'فحص الآن',
    'Select Volumes' => 'اختر المجلدات',
    'Select All' => 'تحديد الكل',
    'Results' => 'النتائج',
    'Used Assets' => 'الأصول المستخدمة',
    'Unused Assets' => 'الأصول غير المستخدمة',
    'Scanning...' => 'جاري الفحص...',
    
    // Bulk Actions
    'Bulk Actions' => 'إجراءات جماعية',
    'Bulk Actions (All Volumes)' => 'إجراءات جماعية (جميع المجلدات)',
    'Download CSV' => 'تحميل CSV',
    'Download ZIP' => 'تحميل ZIP',
    'Put into Trash' => 'نقل إلى سلة المهملات',
    'Delete Permanently' => 'حذف نهائي',
    
    // Table Headers
    'Title' => 'العنوان',
    'Filename' => 'اسم الملف',
    'Volume' => 'المجلد',
    'Size' => 'الحجم',
    'Path' => 'المسار',
    'Date Created' => 'تاريخ الإنشاء',
    
    // Messages
    'No assets selected.' => 'لم يتم تحديد أي أصول.',
    'No assets found.' => 'لم يتم العثور على أصول.',
    'Could not create ZIP file.' => 'تعذر إنشاء ملف ZIP.',
    'No volumes selected.' => 'لم يتم تحديد أي مجلدات.',
    
    // ZIP Download Dialog
    'ZIP Download Options' => 'خيارات تحميل ZIP',
    'How would you like to organize the files in the ZIP?' => 'كيف تريد تنظيم الملفات في ZIP؟',
    'Flat (all files in root)' => 'مسطح (جميع الملفات في الجذر)',
    'Preserve folder structure' => 'الحفاظ على هيكل المجلدات',
    'Cancel' => 'إلغاء',
    'ZIP download initiated. Large files may take several minutes to prepare.' => 'بدأ تحميل ZIP. قد تستغرق الملفات الكبيرة عدة دقائق.',
    'Preparing ZIP file... This may take several minutes for large files. Please wait.' => 'جاري تحضير ملف ZIP... قد يستغرق هذا عدة دقائق للملفات الكبيرة. يرجى الانتظار.',
    
    // Trash/Delete Messages
    'Are you sure you want to move {count} assets to trash?' => 'هل أنت متأكد من نقل {count} أصول إلى سلة المهملات؟',
    'Moved {count} assets to trash.' => 'تم نقل {count} أصول إلى سلة المهملات.',
    'Permanently deleted {count} assets.' => 'تم حذف {count} أصول نهائياً.',
    'WARNING: You are about to permanently delete assets.' => 'تحذير: أنت على وشك حذف الأصول نهائياً.',
    'This action CANNOT be undone!' => 'لا يمكن التراجع عن هذا الإجراء!',
    'We strongly recommend downloading the unused assets as a backup before proceeding.' => 'نوصي بشدة بتحميل الأصول غير المستخدمة كنسخة احتياطية قبل المتابعة.',
    'Are you absolutely sure you want to permanently delete these assets?' => 'هل أنت متأكد تماماً من حذف هذه الأصول نهائياً؟',
    'Final confirmation: Permanently delete assets? This CANNOT be undone!' => 'التأكيد النهائي: حذف الأصول نهائياً؟ لا يمكن التراجع!',
    
    // Volume Section
    'unused assets' => 'أصول غير مستخدمة',
    'No assets selected in this volume.' => 'لم يتم تحديد أصول في هذا المجلد.',
    
    // Errors
    'Failed to scan volumes.' => 'فشل فحص المجلدات.',
    'Failed to export CSV.' => 'فشل تصدير CSV.',
    'Failed to create ZIP file.' => 'فشل إنشاء ملف ZIP.',
    'Failed to move assets to trash.' => 'فشل نقل الأصول إلى سلة المهملات.',
    'Failed to delete assets.' => 'فشل حذف الأصول.',
    'Failed to get asset usage.' => 'فشل الحصول على استخدام الأصل.',

    // Queue Scan
    'Scan queued...' => 'الفحص في قائمة الانتظار...',
    'Scan failed.' => 'فشل الفحص.',
    'Scanning assets for usage' => 'فحص استخدام الأصول',
    'The queue does not appear to be running. Make sure a queue worker is active (e.g. php craft queue/listen).' => 'لا يبدو أن قائمة الانتظار تعمل. تأكد من أن عامل قائمة الانتظار نشط (مثال: php craft queue/listen).',
];
