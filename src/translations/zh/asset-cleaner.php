<?php

return [
    // General
    'Asset Cleaner' => '资源清理器',
    'An error occurred.' => '发生错误。',
    'Loading...' => '加载中...',
    
    // View Usage
    'View Usage' => '查看使用情况',
    'Used by Entries' => '被条目使用',
    'Used in Content Fields' => '在内容字段中使用',
    'This asset is not used anywhere.' => '此资源未在任何地方使用。',
    
    // Utility Page
    'Scan Now' => '立即扫描',
    'Select Volumes' => '选择卷',
    'Select All' => '全选',
    'Results' => '结果',
    'Used Assets' => '已使用的资源',
    'Unused Assets' => '未使用的资源',
    'Scanning...' => '扫描中...',
    
    // Bulk Actions
    'Bulk Actions' => '批量操作',
    'Bulk Actions (All Volumes)' => '批量操作（所有卷）',
    'Download CSV' => '下载CSV',
    'Download ZIP' => '下载ZIP',
    'Put into Trash' => '移至回收站',
    'Delete Permanently' => '永久删除',
    
    // Table Headers
    'Title' => '标题',
    'Filename' => '文件名',
    'Volume' => '卷',
    'Size' => '大小',
    'Path' => '路径',
    'Date Created' => '创建日期',
    
    // Messages
    'No assets selected.' => '未选择资源。',
    'No assets found.' => '未找到资源。',
    'Could not create ZIP file.' => '无法创建ZIP文件。',
    'No volumes selected.' => '未选择卷。',
    
    // ZIP Download Dialog
    'ZIP Download Options' => 'ZIP下载选项',
    'How would you like to organize the files in the ZIP?' => '您希望如何组织ZIP中的文件？',
    'Flat (all files in root)' => '扁平（所有文件在根目录）',
    'Preserve folder structure' => '保留文件夹结构',
    'Cancel' => '取消',
    'ZIP download initiated. Large files may take several minutes to prepare.' => 'ZIP下载已开始。大文件可能需要几分钟。',
    'Preparing ZIP file... This may take several minutes for large files. Please wait.' => '正在准备ZIP文件...大文件可能需要几分钟。请稍候。',
    
    // Trash/Delete Messages
    'Are you sure you want to move {count} assets to trash?' => '确定要将{count}个资源移至回收站吗？',
    'Moved {count} assets to trash.' => '已将{count}个资源移至回收站。',
    'Permanently deleted {count} assets.' => '已永久删除{count}个资源。',
    'WARNING: You are about to permanently delete assets.' => '警告：您即将永久删除资源。',
    'This action CANNOT be undone!' => '此操作无法撤销！',
    'We strongly recommend downloading the unused assets as a backup before proceeding.' => '我们强烈建议在继续之前下载未使用的资源作为备份。',
    'Are you absolutely sure you want to permanently delete these assets?' => '您确定要永久删除这些资源吗？',
    'Final confirmation: Permanently delete assets? This CANNOT be undone!' => '最终确认：永久删除资源？此操作无法撤销！',
    
    // Volume Section
    'unused assets' => '未使用的资源',
    'No assets selected in this volume.' => '此卷中未选择资源。',
    
    // Errors
    'Failed to scan volumes.' => '扫描卷失败。',
    'Failed to export CSV.' => '导出CSV失败。',
    'Failed to create ZIP file.' => '创建ZIP文件失败。',
    'Failed to move assets to trash.' => '移动资源到回收站失败。',
    'Failed to delete assets.' => '删除资源失败。',
    'Failed to get asset usage.' => '获取资源使用情况失败。',

    // Queue Scan
    'Scan queued...' => '扫描已加入队列...',
    'Scan failed.' => '扫描失败。',
    'Scanning assets for usage' => '正在扫描资源使用情况',
    'The queue does not appear to be running. Make sure a queue worker is active (e.g. php craft queue/listen).' => '队列似乎未在运行。请确保队列工作进程处于活动状态（例如：php craft queue/listen）。',

    // Scan Time
    'Scanned on {date}' => '扫描于 {date}',
    'Restoring last scan...' => '正在恢复上次扫描...',

    // Usage Dialog / Scan Options
    'Check Asset Usage' => '检查资源使用情况',
    'Choose how usage should be evaluated for this asset.' => '选择如何评估此资源的使用情况。',
    'Choose the usage options you want to check, then confirm.' => '选择要检查的使用选项，然后确认。',
    'Include drafts' => '包含草稿',
    'Include revisions' => '包含修订',
    'Count all relational references as usage' => '将所有关系引用计为使用',
    'Recommended for projects with plugin-defined or unknown element types that may store asset relations outside normal entry content.' => '建议用于包含插件定义或未知元素类型的项目，这些类型可能会在普通条目内容之外存储资源关系。',
    'Check Usage' => '检查使用情况',
    'Used by Relational Elements' => '被关系元素使用',
    'Other Relational Elements' => '其他关系元素',
    'Relational element #{id}' => '关系元素 #{id}',
    'Relational element' => '关系元素',
    'Include drafts in this scan' => '在此次扫描中包含草稿',
    'When enabled, assets referenced only in drafts may be treated as used.' => '启用后，仅在草稿中被引用的资源也可能被视为已使用。',
    'Include revisions in this scan' => '在此次扫描中包含修订',
    'When enabled, assets referenced only in revisions may be treated as used.' => '启用后，仅在修订中被引用的资源也可能被视为已使用。',
    'When enabled, any row in Craft’s relations table will cause an asset to be treated as used, including references created by plugin-defined or unknown element types. Disable this for a stricter scan.' => '启用后，Craft 关系表中的任何一行都会使资源被视为已使用，包括由插件定义或未知元素类型创建的引用。若要进行更严格的扫描，请禁用此选项。',
];
