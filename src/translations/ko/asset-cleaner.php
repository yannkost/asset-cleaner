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
];
