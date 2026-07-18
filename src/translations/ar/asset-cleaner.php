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

    // Scan Time
    'Scanned on {date}' => 'تم المسح في {date}',
    'Restoring last scan...' => 'استعادة آخر مسح...',

    // Usage Dialog / Scan Options
    'Check Asset Usage' => 'التحقق من استخدام الأصل',
    'Choose how usage should be evaluated for this asset.' => 'اختر كيفية تقييم استخدام هذا الأصل.',
    'Choose the usage options you want to check, then confirm.' => 'اختر خيارات الاستخدام التي تريد التحقق منها ثم أكّد.',
    'Include drafts' => 'تضمين المسودات',
    'Include revisions' => 'تضمين المراجعات',
    'Count all relational references as usage' => 'احتساب جميع المراجع العلائقية على أنها استخدام',
    'Recommended for projects with plugin-defined or unknown element types that may store asset relations outside normal entry content.' => 'يُنصح بهذا للمشاريع التي تحتوي على أنواع عناصر معرّفة عبر إضافات أو غير معروفة وقد تخزن علاقات الأصول خارج محتوى الإدخالات العادي.',
    'Check Usage' => 'التحقق من الاستخدام',
    'Used by Relational Elements' => 'مستخدم بواسطة عناصر علائقية',
    'Other Relational Elements' => 'عناصر علائقية أخرى',
    'Relational element #{id}' => 'عنصر علائقي رقم #{id}',
    'Relational element' => 'عنصر علائقي',
    'Include drafts in this scan' => 'تضمين المسودات في هذا الفحص',
    'When enabled, assets referenced only in drafts may be treated as used.' => 'عند التفعيل، قد تُعتبر الأصول المشار إليها فقط في المسودات مستخدمة.',
    'Include revisions in this scan' => 'تضمين المراجعات في هذا الفحص',
    'When enabled, assets referenced only in revisions may be treated as used.' => 'عند التفعيل، قد تُعتبر الأصول المشار إليها فقط في المراجعات مستخدمة.',
    'When enabled, any row in Craft’s relations table will cause an asset to be treated as used, including references created by plugin-defined or unknown element types. Disable this for a stricter scan.' => 'عند التفعيل، سيؤدي أي صف في جدول العلاقات في Craft إلى اعتبار الأصل مستخدمًا، بما في ذلك المراجع التي تنشئها أنواع عناصر معرّفة عبر إضافات أو غير معروفة. عطّل هذا الخيار للحصول على فحص أكثر صرامة.',

    // Settings - Scan performance
    'Scan performance' => 'أداء الفحص',
    'Relation batch size' => 'حجم دفعة العلاقات',
    'Maximum number of assets loaded for relation scanning per queue execution. Lower this (e.g. to 500) on sites with heavy or deeply nested relations if scan jobs time out. You can also override this with `relationBatchSize` in `config/asset-cleaner.php` or the `ASSET_CLEANER_RELATION_BATCH_SIZE` environment variable.' => 'الحد الأقصى لعدد الأصول التي يتم تحميلها لفحص العلاقات في كل تنفيذ لقائمة الانتظار. قم بخفض هذه القيمة (مثلاً إلى 500) في المواقع ذات العلاقات الكثيرة أو المتداخلة بعمق إذا انتهت مهلة مهام الفحص. يمكنك أيضًا تجاوز هذه القيمة عبر `relationBatchSize` في `config/asset-cleaner.php` أو متغير البيئة `ASSET_CLEANER_RELATION_BATCH_SIZE`.',
    'Relation time budget (seconds)' => 'الميزانية الزمنية للعلاقات (بالثواني)',
    'Wall-clock budget for the relation stage of a single queue execution. Once exceeded, the job stops and re-queues to continue, keeping each execution safely under the queue’s time-to-reserve (TTR, 300s by default). Keep this comfortably below your TTR. You can also override this with `relationTimeBudgetSeconds` in `config/asset-cleaner.php` or the `ASSET_CLEANER_RELATION_TIME_BUDGET` environment variable.' => 'الميزانية الزمنية الفعلية لمرحلة العلاقات في تنفيذ واحد لقائمة الانتظار. عند تجاوزها تتوقف المهمة وتعيد إدراج نفسها في قائمة الانتظار للمتابعة، بحيث يبقى كل تنفيذ بأمان دون مهلة time-to-reserve الخاصة بقائمة الانتظار (TTR، وهي 300 ثانية افتراضيًا). أبقِ هذه القيمة أقل بوضوح من TTR لديك. يمكنك أيضًا تجاوز هذه القيمة عبر `relationTimeBudgetSeconds` في `config/asset-cleaner.php` أو متغير البيئة `ASSET_CLEANER_RELATION_TIME_BUDGET`.',

    // Settings page, queue job descriptions, scan stage labels, usage labels
    'Scan storage mode' => 'وضع تخزين الفحوصات',
    'File-based' => 'قائم على الملفات',
    'Database-based' => 'قائم على قاعدة البيانات',
    'Choose how Asset Cleaner stores transient scan state. File-based storage is the default and works well when web and queue workers share a filesystem. Database-based storage is better suited for containerized or cloud-style environments where shared filesystem access is not guaranteed.' => 'يحدد كيفية تخزين Asset Cleaner لحالة الفحص المؤقتة. التخزين القائم على الملفات هو الافتراضي ويعمل جيدًا عندما يتشارك عمال الويب وقائمة الانتظار نظام ملفات واحدًا. التخزين في قاعدة البيانات أنسب للبيئات الحاوية أو السحابية حيث لا يكون الوصول المشترك إلى نظام الملفات مضمونًا.',
    'File-based scan workspace path' => 'مسار مساحة عمل الفحص القائم على الملفات',
    'Optional. Only used when scan storage mode is set to File-based. Defaults to `@storage/asset-cleaner`. You can also override this with `scanWorkspacePath` in `config/asset-cleaner.php` or the `ASSET_CLEANER_SCAN_PATH` environment variable.' => 'اختياري. يُستخدم فقط عندما يكون وضع التخزين "قائم على الملفات". الافتراضي هو `@storage/asset-cleaner`. يمكنك أيضًا تجاوز هذه القيمة عبر `scanWorkspacePath` في `config/asset-cleaner.php` أو متغير البيئة `ASSET_CLEANER_SCAN_PATH`.',
    'Include drafts by default' => 'تضمين المسودات افتراضيًا',
    'When enabled, assets referenced only in drafts may be treated as used during scans. This value acts as the default for new scans and can be overridden per scan from the utility page. You can also override this with `includeDraftsByDefault` in `config/asset-cleaner.php` or the `ASSET_CLEANER_INCLUDE_DRAFTS` environment variable.' => 'عند التفعيل، يمكن اعتبار الأصول المشار إليها في المسودات فقط مستخدمة أثناء الفحص. تعمل هذه القيمة كافتراضية للفحوصات الجديدة ويمكن تجاوزها لكل فحص من صفحة الأداة. يمكنك أيضًا تجاوزها عبر `includeDraftsByDefault` في `config/asset-cleaner.php` أو متغير البيئة `ASSET_CLEANER_INCLUDE_DRAFTS`.',
    'Include revisions by default' => 'تضمين المراجعات افتراضيًا',
    'When enabled, assets referenced only in revisions may be treated as used during scans. This value acts as the default for new scans and can be overridden per scan from the utility page. You can also override this with `includeRevisionsByDefault` in `config/asset-cleaner.php` or the `ASSET_CLEANER_INCLUDE_REVISIONS` environment variable.' => 'عند التفعيل، يمكن اعتبار الأصول المشار إليها في المراجعات فقط مستخدمة أثناء الفحص. تعمل هذه القيمة كافتراضية للفحوصات الجديدة ويمكن تجاوزها لكل فحص من صفحة الأداة. يمكنك أيضًا تجاوزها عبر `includeRevisionsByDefault` في `config/asset-cleaner.php` أو متغير البيئة `ASSET_CLEANER_INCLUDE_REVISIONS`.',
    'Notes:' => 'ملاحظات:',
    'Only the latest scan is retained for restore/export workflows.' => 'يُحتفظ فقط بأحدث فحص لعمليات الاستعادة/التصدير.',
    'When using File-based storage in multi-container setups, make sure the configured workspace path is shared between web and queue workers.' => 'عند استخدام التخزين القائم على الملفات في بيئات متعددة الحاويات، تأكد من أن مسار مساحة العمل المُعدّ مشترك بين عمال الويب وقائمة الانتظار.',
    'Config file values override these control panel settings.' => 'قيم ملف الإعدادات لها الأولوية على إعدادات لوحة التحكم هذه.',
    'Draft and revision handling can be configured globally here and overridden per scan from the utility page.' => 'يمكن ضبط التعامل مع المسودات والمراجعات هنا بشكل عام وتجاوزه لكل فحص من صفحة الأداة.',
    'Preparing asset scan' => 'جارٍ تحضير فحص الأصول',
    'Scanning asset relations' => 'جارٍ فحص علاقات الأصول',
    'Scanning content for asset references' => 'جارٍ فحص المحتوى بحثًا عن مراجع الأصول',
    'Finalizing asset scan results' => 'جارٍ إنهاء نتائج فحص الأصول',
    'Preparing asset snapshot...' => 'جارٍ تحضير لقطة الأصول...',
    'Scanning relations...' => 'جارٍ فحص العلاقات...',
    'Scanning content...' => 'جارٍ فحص المحتوى...',
    'Finalizing results...' => 'جارٍ إنهاء النتائج...',
    'User profile picture' => 'صورة الملف الشخصي للمستخدم',
    'User #{id}' => 'المستخدم #{id}',
    'Relational source #{id}' => 'مصدر علائقي #{id}',
    'Used by relational element #{id}' => 'مستخدم من قبل العنصر العلائقي #{id}',

    // Bulk delete confirmations and scan status messages
    'Are you sure you want to permanently delete {count} assets? This action CANNOT be undone! Download a backup (CSV or ZIP) before proceeding.' => 'هل أنت متأكد من أنك تريد حذف {count} من الأصول نهائيًا؟ لا يمكن التراجع عن هذا الإجراء! نزّل نسخة احتياطية (CSV أو ZIP) قبل المتابعة.',
    'Before permanently deleting' => 'قبل الحذف النهائي',
    'Bulk Actions - All Selected Volumes' => 'إجراءات جماعية - جميع وحدات التخزين المحددة',
    'Final confirmation: Permanently delete {count} assets? This CANNOT be undone!' => 'التأكيد النهائي: حذف {count} من الأصول نهائيًا؟ لا يمكن التراجع عن ذلك!',
    'Lost contact while polling scan progress. The scan may still be running.' => 'فُقد الاتصال أثناء متابعة تقدم الفحص. قد يكون الفحص لا يزال قيد التشغيل.',
    'No unused assets found.' => 'لم يتم العثور على أصول غير مستخدمة.',
    'Scan older than 24h — results may be outdated' => 'فحص أقدم من 24 ساعة — قد تكون النتائج قديمة',
    'We recommend downloading a ZIP backup of the assets you plan to remove first, or using "Put into Trash" as a safer alternative. Permanent deletions cannot be undone.' => 'نوصي أولًا بتنزيل نسخة ZIP احتياطية من الأصول التي تخطط لإزالتها، أو استخدام «النقل إلى سلة المهملات» كبديل أكثر أمانًا. لا يمكن التراجع عن الحذف النهائي.',
    '{count} unused assets — {size}' => '{count} أصول غير مستخدمة — {size}',
];
