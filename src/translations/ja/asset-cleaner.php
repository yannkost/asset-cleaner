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
    // Usage Dialog / Scan Options
    'Check Asset Usage' => 'アセットの使用状況を確認',
    'Choose how usage should be evaluated for this asset.' => 'このアセットの使用状況をどのように判定するか選択してください。',
    'Choose the usage options you want to check, then confirm.' => '確認したい使用条件を選択してから実行してください。',
    'Include drafts' => '下書きを含める',
    'Include revisions' => 'リビジョンを含める',
    'Count all relational references as usage' => 'すべての関連参照を使用中として扱う',
    'Recommended for projects with plugin-defined or unknown element types that may store asset relations outside normal entry content.' => '通常のエントリ内容以外にアセットの関連情報を保存する、プラグイン定義または不明な要素タイプを含むプロジェクトに推奨されます。',
    'Check Usage' => '使用状況を確認',
    'Used by Relational Elements' => '関連要素で使用中',
    'Other Relational Elements' => 'その他の関連要素',
    'Relational element #{id}' => '関連要素 #{id}',
    'Relational element' => '関連要素',
    'Include drafts in this scan' => 'このスキャンで下書きを含める',
    'When enabled, assets referenced only in drafts may be treated as used.' => '有効にすると、下書きだけで参照されているアセットも使用中として扱われる場合があります。',
    'Include revisions in this scan' => 'このスキャンでリビジョンを含める',
    'When enabled, assets referenced only in revisions may be treated as used.' => '有効にすると、リビジョンだけで参照されているアセットも使用中として扱われる場合があります。',
    'When enabled, any row in Craft’s relations table will cause an asset to be treated as used, including references created by plugin-defined or unknown element types. Disable this for a stricter scan.' => '有効にすると、Craft の relations テーブルにある任意の行によって、そのアセットは使用中として扱われます。これには、プラグイン定義または不明な要素タイプによって作成された参照も含まれます。より厳密に確認したい場合は無効にしてください。',

    // Settings - Scan performance
    'Scan performance' => 'スキャンのパフォーマンス',
    'Relation batch size' => 'リレーションのバッチサイズ',
    'Maximum number of assets loaded for relation scanning per queue execution. Lower this (e.g. to 500) on sites with heavy or deeply nested relations if scan jobs time out. You can also override this with `relationBatchSize` in `config/asset-cleaner.php` or the `ASSET_CLEANER_RELATION_BATCH_SIZE` environment variable.' => 'キュー実行1回あたりにリレーションスキャンのために読み込むアセットの最大数です。リレーションが多い、または深くネストされたサイトでスキャンジョブがタイムアウトする場合は、この値を下げてください(例: 500)。`config/asset-cleaner.php` の `relationBatchSize`、または環境変数 `ASSET_CLEANER_RELATION_BATCH_SIZE` でも上書きできます。',
    'Relation time budget (seconds)' => 'リレーションの時間バジェット(秒)',
    'Wall-clock budget for the relation stage of a single queue execution. Once exceeded, the job stops and re-queues to continue, keeping each execution safely under the queue’s time-to-reserve (TTR, 300s by default). Keep this comfortably below your TTR. You can also override this with `relationTimeBudgetSeconds` in `config/asset-cleaner.php` or the `ASSET_CLEANER_RELATION_TIME_BUDGET` environment variable.' => 'キュー実行1回のリレーション段階に割り当てる実時間のバジェットです。超過するとジョブは停止し、続行のために再度キューに登録されるため、各実行はキューの time-to-reserve(TTR、デフォルト300秒)を安全に下回ります。この値はTTRより十分小さく保ってください。`config/asset-cleaner.php` の `relationTimeBudgetSeconds`、または環境変数 `ASSET_CLEANER_RELATION_TIME_BUDGET` でも上書きできます。',

    // Settings page, queue job descriptions, scan stage labels, usage labels
    'Scan storage mode' => 'スキャンの保存モード',
    'File-based' => 'ファイルベース',
    'Database-based' => 'データベースベース',
    'Choose how Asset Cleaner stores transient scan state. File-based storage is the default and works well when web and queue workers share a filesystem. Database-based storage is better suited for containerized or cloud-style environments where shared filesystem access is not guaranteed.' => 'Asset Cleaner がスキャンの一時的な状態をどのように保存するかを決定します。ファイルベースの保存がデフォルトで、ウェブワーカーとキューワーカーがファイルシステムを共有している場合に適しています。データベース保存は、共有ファイルシステムへのアクセスが保証されないコンテナ環境やクラウド環境に適しています。',
    'File-based scan workspace path' => 'ファイルベーススキャンのワークスペースパス',
    'Optional. Only used when scan storage mode is set to File-based. Defaults to `@storage/asset-cleaner`. You can also override this with `scanWorkspacePath` in `config/asset-cleaner.php` or the `ASSET_CLEANER_SCAN_PATH` environment variable.' => '任意。保存モードが「ファイルベース」の場合にのみ使用されます。デフォルトは `@storage/asset-cleaner` です。`config/asset-cleaner.php` の `scanWorkspacePath`、または環境変数 `ASSET_CLEANER_SCAN_PATH` でも上書きできます。',
    'Include drafts by default' => 'デフォルトで下書きを含める',
    'When enabled, assets referenced only in drafts may be treated as used during scans. This value acts as the default for new scans and can be overridden per scan from the utility page. You can also override this with `includeDraftsByDefault` in `config/asset-cleaner.php` or the `ASSET_CLEANER_INCLUDE_DRAFTS` environment variable.' => '有効にすると、下書きでのみ参照されているアセットがスキャン時に使用中として扱われることがあります。この値は新しいスキャンのデフォルトとして機能し、ユーティリティページからスキャンごとに上書きできます。`config/asset-cleaner.php` の `includeDraftsByDefault`、または環境変数 `ASSET_CLEANER_INCLUDE_DRAFTS` でも上書きできます。',
    'Include revisions by default' => 'デフォルトでリビジョンを含める',
    'When enabled, assets referenced only in revisions may be treated as used during scans. This value acts as the default for new scans and can be overridden per scan from the utility page. You can also override this with `includeRevisionsByDefault` in `config/asset-cleaner.php` or the `ASSET_CLEANER_INCLUDE_REVISIONS` environment variable.' => '有効にすると、リビジョンでのみ参照されているアセットがスキャン時に使用中として扱われることがあります。この値は新しいスキャンのデフォルトとして機能し、ユーティリティページからスキャンごとに上書きできます。`config/asset-cleaner.php` の `includeRevisionsByDefault`、または環境変数 `ASSET_CLEANER_INCLUDE_REVISIONS` でも上書きできます。',
    'Notes:' => '注意事項:',
    'Only the latest scan is retained for restore/export workflows.' => '復元/エクスポートのワークフローでは最新のスキャンのみが保持されます。',
    'When using File-based storage in multi-container setups, make sure the configured workspace path is shared between web and queue workers.' => 'マルチコンテナ環境でファイルベースの保存を使用する場合は、設定したワークスペースパスがウェブワーカーとキューワーカーの間で共有されていることを確認してください。',
    'Config file values override these control panel settings.' => '設定ファイルの値は、これらのコントロールパネル設定より優先されます。',
    'Draft and revision handling can be configured globally here and overridden per scan from the utility page.' => '下書きとリビジョンの扱いはここでグローバルに設定でき、ユーティリティページからスキャンごとに上書きできます。',
    'Preparing asset scan' => 'アセットスキャンを準備中',
    'Scanning asset relations' => 'アセットのリレーションをスキャン中',
    'Scanning content for asset references' => 'アセット参照を求めてコンテンツをスキャン中',
    'Finalizing asset scan results' => 'アセットスキャン結果を確定中',
    'Preparing asset snapshot...' => 'アセットのスナップショットを準備中...',
    'Scanning relations...' => 'リレーションをスキャン中...',
    'Scanning content...' => 'コンテンツをスキャン中...',
    'Finalizing results...' => '結果を確定中...',
    'User profile picture' => 'ユーザープロフィール画像',
    'User #{id}' => 'ユーザー #{id}',
    'Relational source #{id}' => 'リレーションソース #{id}',
    'Used by relational element #{id}' => 'リレーション要素 #{id} によって使用中',

    // Bulk delete confirmations and scan status messages
    'Are you sure you want to permanently delete {count} assets? This action CANNOT be undone! Download a backup (CSV or ZIP) before proceeding.' => '本当に {count} 件のアセットを完全に削除しますか?この操作は元に戻せません!続行する前にバックアップ(CSV または ZIP)をダウンロードしてください。',
    'Before permanently deleting' => '完全に削除する前に',
    'Bulk Actions - All Selected Volumes' => '一括操作 - 選択したすべてのボリューム',
    'Final confirmation: Permanently delete {count} assets? This CANNOT be undone!' => '最終確認: {count} 件のアセットを完全に削除しますか?この操作は元に戻せません!',
    'Lost contact while polling scan progress. The scan may still be running.' => 'スキャン進行状況の取得中に接続が失われました。スキャンはまだ実行中の可能性があります。',
    'No unused assets found.' => '未使用のアセットは見つかりませんでした。',
    'Scan older than 24h — results may be outdated' => '24 時間以上前のスキャン — 結果が古い可能性があります',
    'We recommend downloading a ZIP backup of the assets you plan to remove first, or using "Put into Trash" as a safer alternative. Permanent deletions cannot be undone.' => 'まず削除予定のアセットの ZIP バックアップをダウンロードするか、より安全な代替手段として「ゴミ箱に入れる」を使用することをおすすめします。完全削除は元に戻せません。',
    '{count} unused assets — {size}' => '未使用アセット {count} 件 — {size}',
];
