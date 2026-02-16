<?php

return [
    // General
    'Asset Cleaner' => 'アセットクリーナー',
    'An error occurred.' => 'エラーが発生しました。',
    'Loading...' => '読み込み中...',
    
    // View Usage
    'View Usage' => '使用状況を表示',
    'Used by Entries' => 'エントリーで使用中',
    'Used in Content Fields' => 'コンテンツフィールドで使用中',
    'This asset is not used anywhere.' => 'このアセットはどこでも使用されていません。',
    
    // Utility Page
    'Scan Now' => '今すぐスキャン',
    'Select Volumes' => 'ボリュームを選択',
    'Select All' => 'すべて選択',
    'Results' => '結果',
    'Used Assets' => '使用中のアセット',
    'Unused Assets' => '未使用のアセット',
    'Scanning...' => 'スキャン中...',
    
    // Bulk Actions
    'Bulk Actions' => '一括操作',
    'Bulk Actions (All Volumes)' => '一括操作（全ボリューム）',
    'Download CSV' => 'CSVをダウンロード',
    'Download ZIP' => 'ZIPをダウンロード',
    'Put into Trash' => 'ゴミ箱に移動',
    'Delete Permanently' => '完全に削除',
    
    // Table Headers
    'Title' => 'タイトル',
    'Filename' => 'ファイル名',
    'Volume' => 'ボリューム',
    'Size' => 'サイズ',
    'Path' => 'パス',
    'Date Created' => '作成日',
    
    // Messages
    'No assets selected.' => 'アセットが選択されていません。',
    'No assets found.' => 'アセットが見つかりません。',
    'Could not create ZIP file.' => 'ZIPファイルを作成できませんでした。',
    'No volumes selected.' => 'ボリュームが選択されていません。',
    
    // ZIP Download Dialog
    'ZIP Download Options' => 'ZIPダウンロードオプション',
    'How would you like to organize the files in the ZIP?' => 'ZIP内のファイルをどのように整理しますか？',
    'Flat (all files in root)' => 'フラット（すべてのファイルをルートに）',
    'Preserve folder structure' => 'フォルダ構造を維持',
    'Cancel' => 'キャンセル',
    'ZIP download initiated. Large files may take several minutes to prepare.' => 'ZIPダウンロードを開始しました。大きなファイルは数分かかる場合があります。',
    'Preparing ZIP file... This may take several minutes for large files. Please wait.' => 'ZIPファイルを準備中...大きなファイルは数分かかる場合があります。お待ちください。',
    
    // Trash/Delete Messages
    'Are you sure you want to move {count} assets to trash?' => '{count}個のアセットをゴミ箱に移動してもよろしいですか？',
    'Moved {count} assets to trash.' => '{count}個のアセットをゴミ箱に移動しました。',
    'Permanently deleted {count} assets.' => '{count}個のアセットを完全に削除しました。',
    'WARNING: You are about to permanently delete assets.' => '警告：アセットを完全に削除しようとしています。',
    'This action CANNOT be undone!' => 'この操作は元に戻せません！',
    'We strongly recommend downloading the unused assets as a backup before proceeding.' => '続行する前に、未使用のアセットをバックアップとしてダウンロードすることを強くお勧めします。',
    'Are you absolutely sure you want to permanently delete these assets?' => 'これらのアセットを完全に削除してもよろしいですか？',
    'Final confirmation: Permanently delete assets? This CANNOT be undone!' => '最終確認：アセットを完全に削除しますか？元に戻せません！',
    
    // Volume Section
    'unused assets' => '未使用のアセット',
    'No assets selected in this volume.' => 'このボリュームでアセットが選択されていません。',
    
    // Errors
    'Failed to scan volumes.' => 'ボリュームのスキャンに失敗しました。',
    'Failed to export CSV.' => 'CSVのエクスポートに失敗しました。',
    'Failed to create ZIP file.' => 'ZIPファイルの作成に失敗しました。',
    'Failed to move assets to trash.' => 'アセットをゴミ箱に移動できませんでした。',
    'Failed to delete assets.' => 'アセットの削除に失敗しました。',
    'Failed to get asset usage.' => 'アセットの使用状況を取得できませんでした。',

    // Queue Scan
    'Scan queued...' => 'スキューに追加済み...',
    'Scan failed.' => 'スキャンに失敗しました。',
    'Scanning assets for usage' => 'アセットの使用状況をスキャン中',
    'The queue does not appear to be running. Make sure a queue worker is active (e.g. php craft queue/listen).' => 'キューが実行されていないようです。キューワーカーが有効であることを確認してください（例：php craft queue/listen）。',

    // Scan Time
    'Scanned on {date}' => '{date} にスキャン済み',
    'Restoring last scan...' => '前回のスキャンを復元中...',
];
