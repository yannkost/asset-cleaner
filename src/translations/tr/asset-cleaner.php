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
];
