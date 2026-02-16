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
];
