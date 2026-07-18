<?php

return [
    // General
    'Asset Cleaner' => 'Varlık Temizleyici',
    'An error occurred.' => 'Bir hata oluştu.',
    'Loading...' => 'Yükleniyor...',
    
    // View Usage
    'View Usage' => 'Kullanımı Görüntüle',
    'Used by Entries' => 'Girişlerde Kullanılıyor',
    'Used in Content Fields' => 'İçerik Alanlarında Kullanılıyor',
    'This asset is not used anywhere.' => 'Bu varlık hiçbir yerde kullanılmıyor.',
    
    // Utility Page
    'Scan Now' => 'Şimdi Tara',
    'Select Volumes' => 'Birimleri Seç',
    'Select All' => 'Tümünü Seç',
    'Results' => 'Sonuçlar',
    'Used Assets' => 'Kullanılan Varlıklar',
    'Unused Assets' => 'Kullanılmayan Varlıklar',
    'Scanning...' => 'Taranıyor...',
    
    // Bulk Actions
    'Bulk Actions' => 'Toplu İşlemler',
    'Bulk Actions (All Volumes)' => 'Toplu İşlemler (Tüm Birimler)',
    'Download CSV' => 'CSV İndir',
    'Download ZIP' => 'ZIP İndir',
    'Put into Trash' => 'Çöp Kutusuna Taşı',
    'Delete Permanently' => 'Kalıcı Olarak Sil',
    
    // Table Headers
    'Title' => 'Başlık',
    'Filename' => 'Dosya Adı',
    'Volume' => 'Birim',
    'Size' => 'Boyut',
    'Path' => 'Yol',
    'Date Created' => 'Oluşturma Tarihi',
    
    // Messages
    'No assets selected.' => 'Varlık seçilmedi.',
    'No assets found.' => 'Varlık bulunamadı.',
    'Could not create ZIP file.' => 'ZIP dosyası oluşturulamadı.',
    'No volumes selected.' => 'Birim seçilmedi.',
    
    // ZIP Download Dialog
    'ZIP Download Options' => 'ZIP İndirme Seçenekleri',
    'How would you like to organize the files in the ZIP?' => 'ZIP içindeki dosyaları nasıl düzenlemek istersiniz?',
    'Flat (all files in root)' => 'Düz (tüm dosyalar kök dizinde)',
    'Preserve folder structure' => 'Klasör yapısını koru',
    'Cancel' => 'İptal',
    'ZIP download initiated. Large files may take several minutes to prepare.' => 'ZIP indirmesi başlatıldı. Büyük dosyalar birkaç dakika sürebilir.',
    'Preparing ZIP file... This may take several minutes for large files. Please wait.' => 'ZIP dosyası hazırlanıyor... Büyük dosyalar için birkaç dakika sürebilir. Lütfen bekleyin.',
    
    // Trash/Delete Messages
    'Are you sure you want to move {count} assets to trash?' => '{count} varlığı çöp kutusuna taşımak istediğinizden emin misiniz?',
    'Moved {count} assets to trash.' => '{count} varlık çöp kutusuna taşındı.',
    'Permanently deleted {count} assets.' => '{count} varlık kalıcı olarak silindi.',
    'WARNING: You are about to permanently delete assets.' => 'UYARI: Varlıkları kalıcı olarak silmek üzeresiniz.',
    'This action CANNOT be undone!' => 'Bu işlem GERİ ALINAMAZ!',
    'We strongly recommend downloading the unused assets as a backup before proceeding.' => 'Devam etmeden önce kullanılmayan varlıkları yedek olarak indirmenizi şiddetle tavsiye ederiz.',
    'Are you absolutely sure you want to permanently delete these assets?' => 'Bu varlıkları kalıcı olarak silmek istediğinizden kesinlikle emin misiniz?',
    'Final confirmation: Permanently delete assets? This CANNOT be undone!' => 'Son onay: Varlıkları kalıcı olarak sil? GERİ ALINAMAZ!',
    
    // Volume Section
    'unused assets' => 'kullanılmayan varlıklar',
    'No assets selected in this volume.' => 'Bu birimde varlık seçilmedi.',
    
    // Errors
    'Failed to scan volumes.' => 'Birimler taranamadı.',
    'Failed to export CSV.' => 'CSV dışa aktarılamadı.',
    'Failed to create ZIP file.' => 'ZIP dosyası oluşturulamadı.',
    'Failed to move assets to trash.' => 'Varlıklar çöp kutusuna taşınamadı.',
    'Failed to delete assets.' => 'Varlıklar silinemedi.',
    'Failed to get asset usage.' => 'Varlık kullanımı alınamadı.',

    // Queue Scan
    'Scan queued...' => 'Tarama sıraya alındı...',
    'Scan failed.' => 'Tarama başarısız oldu.',
    'Scanning assets for usage' => 'Varlık kullanımı taranıyor',
    'The queue does not appear to be running. Make sure a queue worker is active (e.g. php craft queue/listen).' => 'Kuyruk çalışmıyor gibi görünüyor. Bir kuyruk işçisinin aktif olduğundan emin olun (örn: php craft queue/listen).',

    // Scan Time
    'Scanned on {date}' => '{date} tarihinde tarandı',
    'Restoring last scan...' => 'Son tarama geri yükleniyor...',

    // Usage Dialog / Scan Options
    'Check Asset Usage' => 'Varlık kullanımını kontrol et',
    'Choose how usage should be evaluated for this asset.' => 'Bu varlığın kullanımının nasıl değerlendirileceğini seçin.',
    'Choose the usage options you want to check, then confirm.' => 'Kontrol etmek istediğiniz kullanım seçeneklerini seçin ve ardından onaylayın.',
    'Include drafts' => 'Taslakları dahil et',
    'Include revisions' => 'Revizyonları dahil et',
    'Count all relational references as usage' => 'Tüm ilişkisel referansları kullanım olarak say',
    'Recommended for projects with plugin-defined or unknown element types that may store asset relations outside normal entry content.' => 'Normal giriş içeriğinin dışında varlık ilişkileri saklayabilen eklenti tanımlı veya bilinmeyen öğe türlerine sahip projeler için önerilir.',
    'Check Usage' => 'Kullanımı kontrol et',
    'Used by Relational Elements' => 'İlişkisel öğeler tarafından kullanılıyor',
    'Other Relational Elements' => 'Diğer ilişkisel öğeler',
    'Relational element #{id}' => 'İlişkisel öğe #{id}',
    'Relational element' => 'İlişkisel öğe',
    'Include drafts in this scan' => 'Bu taramada taslakları dahil et',
    'When enabled, assets referenced only in drafts may be treated as used.' => 'Etkinleştirildiğinde, yalnızca taslaklarda referans verilen varlıklar kullanılıyor olarak değerlendirilebilir.',
    'Include revisions in this scan' => 'Bu taramada revizyonları dahil et',
    'When enabled, assets referenced only in revisions may be treated as used.' => 'Etkinleştirildiğinde, yalnızca revizyonlarda referans verilen varlıklar kullanılıyor olarak değerlendirilebilir.',
    'When enabled, any row in Craft’s relations table will cause an asset to be treated as used, including references created by plugin-defined or unknown element types. Disable this for a stricter scan.' => 'Etkinleştirildiğinde, Craft’in ilişkiler tablosundaki herhangi bir satır, eklenti tanımlı veya bilinmeyen öğe türleri tarafından oluşturulan referanslar dahil olmak üzere, bir varlığın kullanılıyor olarak değerlendirilmesine neden olur. Daha katı bir tarama için bunu devre dışı bırakın.',

    // Settings - Scan performance
    'Scan performance' => 'Tarama performansı',
    'Relation batch size' => 'İlişki toplu iş boyutu',
    'Maximum number of assets loaded for relation scanning per queue execution. Lower this (e.g. to 500) on sites with heavy or deeply nested relations if scan jobs time out. You can also override this with `relationBatchSize` in `config/asset-cleaner.php` or the `ASSET_CLEANER_RELATION_BATCH_SIZE` environment variable.' => 'Her kuyruk çalıştırmasında ilişki taraması için yüklenen en fazla varlık sayısı. Tarama işleri zaman aşımına uğruyorsa, çok sayıda veya derin iç içe ilişkiye sahip sitelerde bu değeri düşürün (örn. 500). Bu değeri `config/asset-cleaner.php` içindeki `relationBatchSize` ile veya `ASSET_CLEANER_RELATION_BATCH_SIZE` ortam değişkeniyle de geçersiz kılabilirsiniz.',
    'Relation time budget (seconds)' => 'İlişki zaman bütçesi (saniye)',
    'Wall-clock budget for the relation stage of a single queue execution. Once exceeded, the job stops and re-queues to continue, keeping each execution safely under the queue’s time-to-reserve (TTR, 300s by default). Keep this comfortably below your TTR. You can also override this with `relationTimeBudgetSeconds` in `config/asset-cleaner.php` or the `ASSET_CLEANER_RELATION_TIME_BUDGET` environment variable.' => 'Tek bir kuyruk çalıştırmasının ilişki aşaması için gerçek zaman bütçesi. Aşıldığında iş durur ve devam etmek için kendini yeniden kuyruğa ekler; böylece her çalıştırma, kuyruğun time-to-reserve değerinin (TTR, varsayılan 300 sn) güvenle altında kalır. Bu değeri TTR\'nizin belirgin şekilde altında tutun. Bu değeri `config/asset-cleaner.php` içindeki `relationTimeBudgetSeconds` ile veya `ASSET_CLEANER_RELATION_TIME_BUDGET` ortam değişkeniyle de geçersiz kılabilirsiniz.',

    // Settings page, queue job descriptions, scan stage labels, usage labels
    'Scan storage mode' => 'Tarama depolama modu',
    'File-based' => 'Dosya tabanlı',
    'Database-based' => 'Veritabanı tabanlı',
    'Choose how Asset Cleaner stores transient scan state. File-based storage is the default and works well when web and queue workers share a filesystem. Database-based storage is better suited for containerized or cloud-style environments where shared filesystem access is not guaranteed.' => 'Asset Cleaner\'ın geçici tarama durumunu nasıl depolayacağını belirler. Dosya tabanlı depolama varsayılandır ve web ile kuyruk worker\'ları bir dosya sistemini paylaştığında iyi çalışır. Veritabanı tabanlı depolama, paylaşılan dosya sistemi erişiminin garanti edilmediği konteynerleştirilmiş veya bulut tarzı ortamlar için daha uygundur.',
    'File-based scan workspace path' => 'Dosya tabanlı tarama çalışma alanı yolu',
    'Optional. Only used when scan storage mode is set to File-based. Defaults to `@storage/asset-cleaner`. You can also override this with `scanWorkspacePath` in `config/asset-cleaner.php` or the `ASSET_CLEANER_SCAN_PATH` environment variable.' => 'İsteğe bağlı. Yalnızca depolama modu "Dosya tabanlı" olarak ayarlandığında kullanılır. Varsayılan `@storage/asset-cleaner`. Bu değeri `config/asset-cleaner.php` içindeki `scanWorkspacePath` ile veya `ASSET_CLEANER_SCAN_PATH` ortam değişkeniyle de geçersiz kılabilirsiniz.',
    'Include drafts by default' => 'Taslakları varsayılan olarak dahil et',
    'When enabled, assets referenced only in drafts may be treated as used during scans. This value acts as the default for new scans and can be overridden per scan from the utility page. You can also override this with `includeDraftsByDefault` in `config/asset-cleaner.php` or the `ASSET_CLEANER_INCLUDE_DRAFTS` environment variable.' => 'Etkinleştirildiğinde, yalnızca taslaklarda referans verilen varlıklar taramalar sırasında kullanılıyor sayılabilir. Bu değer yeni taramalar için varsayılan olarak işlev görür ve yardımcı araç sayfasından tarama başına geçersiz kılınabilir. Bu değeri `config/asset-cleaner.php` içindeki `includeDraftsByDefault` ile veya `ASSET_CLEANER_INCLUDE_DRAFTS` ortam değişkeniyle de geçersiz kılabilirsiniz.',
    'Include revisions by default' => 'Revizyonları varsayılan olarak dahil et',
    'When enabled, assets referenced only in revisions may be treated as used during scans. This value acts as the default for new scans and can be overridden per scan from the utility page. You can also override this with `includeRevisionsByDefault` in `config/asset-cleaner.php` or the `ASSET_CLEANER_INCLUDE_REVISIONS` environment variable.' => 'Etkinleştirildiğinde, yalnızca revizyonlarda referans verilen varlıklar taramalar sırasında kullanılıyor sayılabilir. Bu değer yeni taramalar için varsayılan olarak işlev görür ve yardımcı araç sayfasından tarama başına geçersiz kılınabilir. Bu değeri `config/asset-cleaner.php` içindeki `includeRevisionsByDefault` ile veya `ASSET_CLEANER_INCLUDE_REVISIONS` ortam değişkeniyle de geçersiz kılabilirsiniz.',
    'Notes:' => 'Notlar:',
    'Only the latest scan is retained for restore/export workflows.' => 'Geri yükleme/dışa aktarma iş akışları için yalnızca en son tarama saklanır.',
    'When using File-based storage in multi-container setups, make sure the configured workspace path is shared between web and queue workers.' => 'Çok konteynerli kurulumlarda dosya tabanlı depolama kullanırken, yapılandırılan çalışma alanı yolunun web ve kuyruk worker\'ları arasında paylaşıldığından emin olun.',
    'Config file values override these control panel settings.' => 'Yapılandırma dosyasındaki değerler bu kontrol paneli ayarlarını geçersiz kılar.',
    'Draft and revision handling can be configured globally here and overridden per scan from the utility page.' => 'Taslak ve revizyon işleme burada genel olarak yapılandırılabilir ve yardımcı araç sayfasından tarama başına geçersiz kılınabilir.',
    'Preparing asset scan' => 'Varlık taraması hazırlanıyor',
    'Scanning asset relations' => 'Varlık ilişkileri taranıyor',
    'Scanning content for asset references' => 'İçerik, varlık referansları için taranıyor',
    'Finalizing asset scan results' => 'Varlık taraması sonuçları tamamlanıyor',
    'Preparing asset snapshot...' => 'Varlık anlık görüntüsü hazırlanıyor...',
    'Scanning relations...' => 'İlişkiler taranıyor...',
    'Scanning content...' => 'İçerik taranıyor...',
    'Finalizing results...' => 'Sonuçlar tamamlanıyor...',
    'User profile picture' => 'Kullanıcı profil resmi',
    'User #{id}' => 'Kullanıcı #{id}',
    'Relational source #{id}' => 'İlişkisel kaynak #{id}',
    'Used by relational element #{id}' => 'İlişkisel öğe #{id} tarafından kullanılıyor',

    // Bulk delete confirmations and scan status messages
    'Are you sure you want to permanently delete {count} assets? This action CANNOT be undone! Download a backup (CSV or ZIP) before proceeding.' => '{count} varlığı kalıcı olarak silmek istediğinizden emin misiniz? Bu işlem GERİ ALINAMAZ! Devam etmeden önce bir yedek (CSV veya ZIP) indirin.',
    'Before permanently deleting' => 'Kalıcı olarak silmeden önce',
    'Bulk Actions - All Selected Volumes' => 'Toplu İşlemler - Tüm Seçili Birimler',
    'Final confirmation: Permanently delete {count} assets? This CANNOT be undone!' => 'Son onay: {count} varlık kalıcı olarak silinsin mi? Bu GERİ ALINAMAZ!',
    'Lost contact while polling scan progress. The scan may still be running.' => 'Tarama ilerlemesi sorgulanırken bağlantı kesildi. Tarama hâlâ çalışıyor olabilir.',
    'No unused assets found.' => 'Kullanılmayan varlık bulunamadı.',
    'Scan older than 24h — results may be outdated' => 'Tarama 24 saatten eski — sonuçlar güncel olmayabilir',
    'We recommend downloading a ZIP backup of the assets you plan to remove first, or using "Put into Trash" as a safer alternative. Permanent deletions cannot be undone.' => 'Önce kaldırmayı planladığınız varlıkların ZIP yedeğini indirmenizi veya daha güvenli bir alternatif olarak "Çöp kutusuna taşı" seçeneğini kullanmanızı öneririz. Kalıcı silmeler geri alınamaz.',
    '{count} unused assets — {size}' => '{count} kullanılmayan varlık — {size}',
];
