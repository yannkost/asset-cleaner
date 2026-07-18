<?php

return [
    // General
    'Asset Cleaner' => '에셋 클리너',
    'An error occurred.' => '오류가 발생했습니다.',
    'Loading...' => '로딩 중...',
    
    // View Usage
    'View Usage' => '사용 현황 보기',
    'Used by Entries' => '항목에서 사용 중',
    'Used in Content Fields' => '콘텐츠 필드에서 사용 중',
    'This asset is not used anywhere.' => '이 에셋은 어디에서도 사용되지 않습니다.',
    
    // Utility Page
    'Scan Now' => '지금 스캔',
    'Select Volumes' => '볼륨 선택',
    'Select All' => '모두 선택',
    'Results' => '결과',
    'Used Assets' => '사용 중인 에셋',
    'Unused Assets' => '미사용 에셋',
    'Scanning...' => '스캔 중...',
    
    // Bulk Actions
    'Bulk Actions' => '일괄 작업',
    'Bulk Actions (All Volumes)' => '일괄 작업 (모든 볼륨)',
    'Download CSV' => 'CSV 다운로드',
    'Download ZIP' => 'ZIP 다운로드',
    'Put into Trash' => '휴지통으로 이동',
    'Delete Permanently' => '영구 삭제',
    
    // Table Headers
    'Title' => '제목',
    'Filename' => '파일명',
    'Volume' => '볼륨',
    'Size' => '크기',
    'Path' => '경로',
    'Date Created' => '생성일',
    
    // Messages
    'No assets selected.' => '선택된 에셋이 없습니다.',
    'No assets found.' => '에셋을 찾을 수 없습니다.',
    'Could not create ZIP file.' => 'ZIP 파일을 생성할 수 없습니다.',
    'No volumes selected.' => '선택된 볼륨이 없습니다.',
    
    // ZIP Download Dialog
    'ZIP Download Options' => 'ZIP 다운로드 옵션',
    'How would you like to organize the files in the ZIP?' => 'ZIP 파일 내 파일을 어떻게 구성하시겠습니까?',
    'Flat (all files in root)' => '플랫 (모든 파일을 루트에)',
    'Preserve folder structure' => '폴더 구조 유지',
    'Cancel' => '취소',
    'ZIP download initiated. Large files may take several minutes to prepare.' => 'ZIP 다운로드가 시작되었습니다. 대용량 파일은 몇 분이 걸릴 수 있습니다.',
    'Preparing ZIP file... This may take several minutes for large files. Please wait.' => 'ZIP 파일 준비 중... 대용량 파일은 몇 분이 걸릴 수 있습니다. 잠시 기다려 주세요.',
    
    // Trash/Delete Messages
    'Are you sure you want to move {count} assets to trash?' => '{count}개의 에셋을 휴지통으로 이동하시겠습니까?',
    'Moved {count} assets to trash.' => '{count}개의 에셋을 휴지통으로 이동했습니다.',
    'Permanently deleted {count} assets.' => '{count}개의 에셋을 영구 삭제했습니다.',
    'WARNING: You are about to permanently delete assets.' => '경고: 에셋을 영구 삭제하려고 합니다.',
    'This action CANNOT be undone!' => '이 작업은 취소할 수 없습니다!',
    'We strongly recommend downloading the unused assets as a backup before proceeding.' => '계속하기 전에 미사용 에셋을 백업으로 다운로드하는 것을 강력히 권장합니다.',
    'Are you absolutely sure you want to permanently delete these assets?' => '이 에셋들을 정말로 영구 삭제하시겠습니까?',
    'Final confirmation: Permanently delete assets? This CANNOT be undone!' => '최종 확인: 에셋을 영구 삭제하시겠습니까? 취소할 수 없습니다!',
    
    // Volume Section
    'unused assets' => '미사용 에셋',
    'No assets selected in this volume.' => '이 볼륨에서 선택된 에셋이 없습니다.',
    
    // Errors
    'Failed to scan volumes.' => '볼륨 스캔에 실패했습니다.',
    'Failed to export CSV.' => 'CSV 내보내기에 실패했습니다.',
    'Failed to create ZIP file.' => 'ZIP 파일 생성에 실패했습니다.',
    'Failed to move assets to trash.' => '에셋을 휴지통으로 이동하는 데 실패했습니다.',
    'Failed to delete assets.' => '에셋 삭제에 실패했습니다.',
    'Failed to get asset usage.' => '에셋 사용 현황을 가져오는 데 실패했습니다.',

    // Queue Scan
    'Scan queued...' => '스캔 대기 중...',
    'Scan failed.' => '스캔에 실패했습니다.',
    'Scanning assets for usage' => '에셋 사용 현황 스캔 중',
    'The queue does not appear to be running. Make sure a queue worker is active (e.g. php craft queue/listen).' => '큐가 실행되고 있지 않은 것 같습니다. 큐 워커가 활성화되어 있는지 확인하세요 (예: php craft queue/listen).',

    // Scan Time
    'Scanned on {date}' => '{date}에 스캔됨',
    'Restoring last scan...' => '마지막 스캔 복원 중...',
    // Usage Dialog / Scan Options
    'Check Asset Usage' => '에셋 사용 여부 확인',
    'Choose how usage should be evaluated for this asset.' => '이 에셋의 사용 여부를 어떻게 평가할지 선택하세요.',
    'Choose the usage options you want to check, then confirm.' => '확인할 사용 옵션을 선택한 다음 확인하세요.',
    'Include drafts' => '드래프트 포함',
    'Include revisions' => '리비전 포함',
    'Count all relational references as usage' => '모든 관계형 참조를 사용으로 간주',
    'Recommended for projects with plugin-defined or unknown element types that may store asset relations outside normal entry content.' => '일반적인 엔트리 콘텐츠 외부에 에셋 관계를 저장할 수 있는 플러그인 정의 또는 알 수 없는 요소 타입이 있는 프로젝트에 권장됩니다.',
    'Check Usage' => '사용 여부 확인',
    'Used by Relational Elements' => '관계형 요소에서 사용됨',
    'Other Relational Elements' => '기타 관계형 요소',
    'Relational element #{id}' => '관계형 요소 #{id}',
    'Relational element' => '관계형 요소',
    'Include drafts in this scan' => '이 스캔에 드래프트 포함',
    'When enabled, assets referenced only in drafts may be treated as used.' => '활성화하면 드래프트에서만 참조되는 에셋도 사용 중으로 간주될 수 있습니다.',
    'Include revisions in this scan' => '이 스캔에 리비전 포함',
    'When enabled, assets referenced only in revisions may be treated as used.' => '활성화하면 리비전에서만 참조되는 에셋도 사용 중으로 간주될 수 있습니다.',
    'When enabled, any row in Craft’s relations table will cause an asset to be treated as used, including references created by plugin-defined or unknown element types. Disable this for a stricter scan.' => '활성화하면 Craft의 relations 테이블에 있는 모든 행이 에셋을 사용 중으로 간주하게 되며, 플러그인으로 정의되었거나 알 수 없는 요소 타입이 만든 참조도 포함됩니다. 더 엄격한 스캔을 원하면 이 옵션을 끄세요.',

    // Settings - Scan performance
    'Scan performance' => '스캔 성능',
    'Relation batch size' => '관계 배치 크기',
    'Maximum number of assets loaded for relation scanning per queue execution. Lower this (e.g. to 500) on sites with heavy or deeply nested relations if scan jobs time out. You can also override this with `relationBatchSize` in `config/asset-cleaner.php` or the `ASSET_CLEANER_RELATION_BATCH_SIZE` environment variable.' => '큐 실행 1회당 관계 스캔을 위해 로드되는 자산의 최대 수입니다. 관계가 많거나 깊게 중첩된 사이트에서 스캔 작업이 시간 초과되면 이 값을 낮추세요(예: 500). `config/asset-cleaner.php`의 `relationBatchSize` 또는 환경 변수 `ASSET_CLEANER_RELATION_BATCH_SIZE`로도 재정의할 수 있습니다.',
    'Relation time budget (seconds)' => '관계 시간 예산(초)',
    'Wall-clock budget for the relation stage of a single queue execution. Once exceeded, the job stops and re-queues to continue, keeping each execution safely under the queue’s time-to-reserve (TTR, 300s by default). Keep this comfortably below your TTR. You can also override this with `relationTimeBudgetSeconds` in `config/asset-cleaner.php` or the `ASSET_CLEANER_RELATION_TIME_BUDGET` environment variable.' => '단일 큐 실행의 관계 단계에 대한 실제 시간 예산입니다. 초과하면 작업이 중지되고 계속하기 위해 다시 큐에 등록되어 각 실행이 큐의 time-to-reserve(TTR, 기본 300초) 아래로 안전하게 유지됩니다. 이 값은 TTR보다 충분히 낮게 유지하세요. `config/asset-cleaner.php`의 `relationTimeBudgetSeconds` 또는 환경 변수 `ASSET_CLEANER_RELATION_TIME_BUDGET`로도 재정의할 수 있습니다.',

    // Settings page, queue job descriptions, scan stage labels, usage labels
    'Scan storage mode' => '스캔 저장 모드',
    'File-based' => '파일 기반',
    'Database-based' => '데이터베이스 기반',
    'Choose how Asset Cleaner stores transient scan state. File-based storage is the default and works well when web and queue workers share a filesystem. Database-based storage is better suited for containerized or cloud-style environments where shared filesystem access is not guaranteed.' => 'Asset Cleaner가 스캔의 임시 상태를 저장하는 방식을 결정합니다. 파일 기반 저장이 기본값이며 웹 워커와 큐 워커가 파일 시스템을 공유할 때 잘 작동합니다. 데이터베이스 저장은 공유 파일 시스템 접근이 보장되지 않는 컨테이너 또는 클라우드 환경에 더 적합합니다.',
    'File-based scan workspace path' => '파일 기반 스캔 작업 공간 경로',
    'Optional. Only used when scan storage mode is set to File-based. Defaults to `@storage/asset-cleaner`. You can also override this with `scanWorkspacePath` in `config/asset-cleaner.php` or the `ASSET_CLEANER_SCAN_PATH` environment variable.' => '선택 사항. 저장 모드가 "파일 기반"으로 설정된 경우에만 사용됩니다. 기본값은 `@storage/asset-cleaner`입니다. `config/asset-cleaner.php`의 `scanWorkspacePath` 또는 환경 변수 `ASSET_CLEANER_SCAN_PATH`로도 재정의할 수 있습니다.',
    'Include drafts by default' => '기본적으로 초안 포함',
    'When enabled, assets referenced only in drafts may be treated as used during scans. This value acts as the default for new scans and can be overridden per scan from the utility page. You can also override this with `includeDraftsByDefault` in `config/asset-cleaner.php` or the `ASSET_CLEANER_INCLUDE_DRAFTS` environment variable.' => '활성화하면 초안에서만 참조되는 자산이 스캔 중에 사용 중으로 처리될 수 있습니다. 이 값은 새 스캔의 기본값으로 작동하며 유틸리티 페이지에서 스캔별로 재정의할 수 있습니다. `config/asset-cleaner.php`의 `includeDraftsByDefault` 또는 환경 변수 `ASSET_CLEANER_INCLUDE_DRAFTS`로도 재정의할 수 있습니다.',
    'Include revisions by default' => '기본적으로 리비전 포함',
    'When enabled, assets referenced only in revisions may be treated as used during scans. This value acts as the default for new scans and can be overridden per scan from the utility page. You can also override this with `includeRevisionsByDefault` in `config/asset-cleaner.php` or the `ASSET_CLEANER_INCLUDE_REVISIONS` environment variable.' => '활성화하면 리비전에서만 참조되는 자산이 스캔 중에 사용 중으로 처리될 수 있습니다. 이 값은 새 스캔의 기본값으로 작동하며 유틸리티 페이지에서 스캔별로 재정의할 수 있습니다. `config/asset-cleaner.php`의 `includeRevisionsByDefault` 또는 환경 변수 `ASSET_CLEANER_INCLUDE_REVISIONS`로도 재정의할 수 있습니다.',
    'Notes:' => '참고:',
    'Only the latest scan is retained for restore/export workflows.' => '복원/내보내기 워크플로에는 최신 스캔만 유지됩니다.',
    'When using File-based storage in multi-container setups, make sure the configured workspace path is shared between web and queue workers.' => '멀티 컨테이너 환경에서 파일 기반 저장을 사용할 때는 구성된 작업 공간 경로가 웹 워커와 큐 워커 간에 공유되는지 확인하세요.',
    'Config file values override these control panel settings.' => '구성 파일의 값이 이 제어판 설정보다 우선합니다.',
    'Draft and revision handling can be configured globally here and overridden per scan from the utility page.' => '초안 및 리비전 처리는 여기에서 전역으로 구성할 수 있으며 유틸리티 페이지에서 스캔별로 재정의할 수 있습니다.',
    'Preparing asset scan' => '자산 스캔 준비 중',
    'Scanning asset relations' => '자산 관계 스캔 중',
    'Scanning content for asset references' => '자산 참조를 찾아 콘텐츠 스캔 중',
    'Finalizing asset scan results' => '자산 스캔 결과 마무리 중',
    'Preparing asset snapshot...' => '자산 스냅샷 준비 중...',
    'Scanning relations...' => '관계 스캔 중...',
    'Scanning content...' => '콘텐츠 스캔 중...',
    'Finalizing results...' => '결과 마무리 중...',
    'User profile picture' => '사용자 프로필 사진',
    'User #{id}' => '사용자 #{id}',
    'Relational source #{id}' => '관계 소스 #{id}',
    'Used by relational element #{id}' => '관계 요소 #{id}에서 사용 중',

    // Bulk delete confirmations and scan status messages
    'Are you sure you want to permanently delete {count} assets? This action CANNOT be undone! Download a backup (CSV or ZIP) before proceeding.' => '정말 {count}개의 자산을 영구 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다! 계속하기 전에 백업(CSV 또는 ZIP)을 다운로드하세요.',
    'Before permanently deleting' => '영구 삭제 전에',
    'Bulk Actions - All Selected Volumes' => '일괄 작업 - 선택한 모든 볼륨',
    'Final confirmation: Permanently delete {count} assets? This CANNOT be undone!' => '최종 확인: {count}개의 자산을 영구 삭제할까요? 되돌릴 수 없습니다!',
    'Lost contact while polling scan progress. The scan may still be running.' => '스캔 진행 상황을 조회하는 중 연결이 끊어졌습니다. 스캔이 아직 실행 중일 수 있습니다.',
    'No unused assets found.' => '사용되지 않는 자산을 찾을 수 없습니다.',
    'Scan older than 24h — results may be outdated' => '24시간 이상 지난 스캔 — 결과가 오래되었을 수 있습니다',
    'We recommend downloading a ZIP backup of the assets you plan to remove first, or using "Put into Trash" as a safer alternative. Permanent deletions cannot be undone.' => '먼저 제거할 자산의 ZIP 백업을 다운로드하거나 더 안전한 대안으로 "휴지통으로 이동"을 사용하는 것을 권장합니다. 영구 삭제는 되돌릴 수 없습니다.',
    '{count} unused assets — {size}' => '사용되지 않는 자산 {count}개 — {size}',
];
