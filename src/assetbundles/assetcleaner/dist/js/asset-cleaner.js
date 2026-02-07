(function() {
    'use strict';

    const settings = window.AssetCleanerSettings || { translations: {} };
    const t = settings.translations;

    // State
    let selectedAssetIds = [];
    let unusedAssets = [];

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        initViewUsageButton();
        initUtilityPage();
    }

    // ========================================
    // View Usage Button (Asset Edit Page)
    // ========================================

    function initViewUsageButton() {
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.asset-cleaner-usage-btn');
            if (!btn) return;

            e.preventDefault();
            const assetId = btn.dataset.assetId;
            if (!assetId) return;

            showUsagePopover(btn, assetId);
        });
    }

    function showUsagePopover(btn, assetId) {
        // Remove existing popover
        const existing = document.querySelector('.asset-cleaner-popover');
        if (existing) {
            existing.remove();
        }

        // Create popover
        const popover = document.createElement('div');
        popover.className = 'asset-cleaner-popover';
        popover.innerHTML = '<div class="spinner"></div><span>' + (t.loading || 'Loading...') + '</span>';

        // Position popover - right-aligned to button
        const rect = btn.getBoundingClientRect();
        popover.style.position = 'fixed';
        popover.style.top = (rect.bottom + 8) + 'px';
        popover.style.right = (window.innerWidth - rect.right) + 'px';
        popover.style.zIndex = '1000';

        document.body.appendChild(popover);

        // Fetch usage data
        Craft.sendActionRequest('GET', 'asset-cleaner/usage/get', {
            params: { assetId: assetId }
        })
        .then(function(response) {
            renderUsagePopover(popover, response.data);
        })
        .catch(function(error) {
            popover.innerHTML = '<p class="error">' + (t.error || 'An error occurred.') + '</p>';
        });

        // Close on click outside
        setTimeout(function() {
            document.addEventListener('click', closePopoverOnClickOutside);
        }, 100);
    }

    function renderUsagePopover(popover, data) {
        if (!data.isUsed) {
            popover.innerHTML = '<p class="not-used">' + (t.notUsed || 'This asset is not used anywhere.') + '</p>';
            return;
        }

        let html = '';

        if (data.usage.relations && data.usage.relations.length > 0) {
            html += '<div class="usage-section">';
            html += '<h4>' + (t.usedByEntries || 'Used by Entries') + '</h4>';
            html += '<ul class="usage-list">';
            data.usage.relations.forEach(function(entry) {
                html += '<li>';
                html += '<a href="' + entry.url + '" target="_blank">';
                html += '<span class="status ' + entry.status + '"></span>';
                html += escapeHtml(entry.title);
                html += '<span class="section-name">(' + escapeHtml(entry.section) + ')</span>';
                html += '</a></li>';
            });
            html += '</ul></div>';
        }

        if (data.usage.content && data.usage.content.length > 0) {
            html += '<div class="usage-section">';
            html += '<h4>' + (t.usedInContentFields || 'Used in Content Fields') + '</h4>';
            html += '<ul class="usage-list">';
            data.usage.content.forEach(function(entry) {
                html += '<li>';
                html += '<a href="' + entry.url + '" target="_blank">';
                html += '<span class="status ' + entry.status + '"></span>';
                html += escapeHtml(entry.title);
                html += '<span class="field-name">(' + escapeHtml(entry.field) + ')</span>';
                html += '</a></li>';
            });
            html += '</ul></div>';
        }

        popover.innerHTML = html;
    }

    function closePopoverOnClickOutside(e) {
        const popover = document.querySelector('.asset-cleaner-popover');
        if (popover && !popover.contains(e.target) && !e.target.closest('.asset-cleaner-usage-btn')) {
            popover.remove();
            document.removeEventListener('click', closePopoverOnClickOutside);
        }
    }

    // ========================================
    // Utility Page
    // ========================================

    function initUtilityPage() {
        const utilityContainer = document.querySelector('.asset-cleaner-utility');
        if (!utilityContainer) return;

        // Scan button
        const scanBtn = utilityContainer.querySelector('.asset-cleaner-scan-btn');
        if (scanBtn) {
            scanBtn.addEventListener('click', handleScan);
        }

        // CSV button
        const csvBtn = utilityContainer.querySelector('.asset-cleaner-csv-btn');
        if (csvBtn) {
            csvBtn.addEventListener('click', handleExportCsv);
        }

        // ZIP button
        const zipBtn = utilityContainer.querySelector('.asset-cleaner-zip-btn');
        if (zipBtn) {
            zipBtn.addEventListener('click', handleExportZip);
        }

        // Trash button
        const trashBtn = utilityContainer.querySelector('.asset-cleaner-trash-btn');
        if (trashBtn) {
            trashBtn.addEventListener('click', handleTrash);
        }

        // Delete permanently button
        const deleteBtn = utilityContainer.querySelector('.asset-cleaner-delete-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', handleDeletePermanently);
        }
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction() {
            const context = this;
            const args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                func.apply(context, args);
            }, wait);
        };
    }

    function handleScan() {
        const container = document.querySelector('.asset-cleaner-utility');
        const loading = container.querySelector('.asset-cleaner-loading');
        const results = container.querySelector('.asset-cleaner-results');
        const progressBarContainer = loading.querySelector('.progress-bar-container');
        const progressBarFill = loading.querySelector('.progress-bar-fill');
        const progressText = loading.querySelector('.progress-text');

        // Get selected volumes
        const volumeIds = [];
        const volumeNames = {};
        container.querySelectorAll('.volume-checkboxes input[type="checkbox"]:checked').forEach(function(cb) {
            volumeIds.push(cb.value);
            const label = cb.closest('.checkbox-wrapper').querySelector('label');
            volumeNames[cb.value] = label ? label.textContent.trim() : 'Volume ' + cb.value;
        });

        // Show loading with progress bar
        loading.style.display = 'flex';
        progressBarContainer.style.display = 'block';
        results.style.display = 'none';
        
        // Simulate progress (since scan happens in one request)
        let progress = 0;
        const progressInterval = setInterval(function() {
            progress += 5;
            if (progress >= 90) {
                clearInterval(progressInterval);
            }
            progressBarFill.style.width = progress + '%';
            progressText.textContent = progress + '%';
        }, 100);

        Craft.sendActionRequest('POST', 'asset-cleaner/asset-cleaner/start-scan', {
            data: { volumeIds: volumeIds }
        })
        .then(function(response) {
            clearInterval(progressInterval);
            progressBarFill.style.width = '100%';
            progressText.textContent = '100%';
            
            setTimeout(function() {
                loading.style.display = 'none';
                progressBarContainer.style.display = 'none';
                progressBarFill.style.width = '0%';
                progressText.textContent = '0%';
                results.style.display = 'block';

                const data = response.data;
                container.querySelector('.used-count').textContent = data.usedCount;
                container.querySelector('.unused-count').textContent = data.unusedCount;

                unusedAssets = data.unusedAssets || [];
                selectedAssetIds = unusedAssets.map(function(a) { return a.id; });

                renderVolumesTables(container, unusedAssets, volumeNames);

                // Count unique volumes in results
                const uniqueVolumes = new Set();
                unusedAssets.forEach(function(asset) {
                    uniqueVolumes.add(asset.volumeId);
                });
                const volumeCount = uniqueVolumes.size;

                // Show/hide global actions based on volume count and unused assets
                const actions = container.querySelector('.asset-cleaner-actions');
                const separator = container.querySelector('.asset-cleaner-separator');
                
                if (unusedAssets.length > 0 && volumeCount > 1) {
                    // Multiple volumes: show global actions and separator
                    actions.style.display = 'block';
                    separator.style.display = 'block';
                } else {
                    // Single volume or no assets: hide global actions
                    actions.style.display = 'none';
                    separator.style.display = 'none';
                }
            }, 300);
        })
        .catch(function(error) {
            clearInterval(progressInterval);
            loading.style.display = 'none';
            progressBarContainer.style.display = 'none';
            Craft.cp.displayError(t.error || 'An error occurred.');
        });
    }

    function renderVolumesTables(container, assets, volumeNames) {
        const volumesContainer = container.querySelector('.asset-cleaner-volumes-container');
        volumesContainer.innerHTML = '';

        if (assets.length === 0) {
            volumesContainer.innerHTML = '<div style="text-align:center; padding:24px; color:var(--gray-500);">No unused assets found.</div>';
            return;
        }

        // Group assets by volume
        const assetsByVolume = {};
        assets.forEach(function(asset) {
            const volumeId = asset.volumeId || 'unknown';
            if (!assetsByVolume[volumeId]) {
                assetsByVolume[volumeId] = [];
            }
            assetsByVolume[volumeId].push(asset);
        });

        // Render a table for each volume
        const volumeKeys = Object.keys(assetsByVolume);
        volumeKeys.forEach(function(volumeId, index) {
            const volumeAssets = assetsByVolume[volumeId];
            const volumeName = volumeNames[volumeId] || volumeAssets[0].volume || 'Unknown Volume';
            
            // Calculate total size for this volume
            const totalVolumeSize = volumeAssets.reduce(function(sum, asset) {
                return sum + (asset.size || 0);
            }, 0);
            
            const volumeSection = document.createElement('div');
            volumeSection.className = 'asset-cleaner-volume-section';
            
            const header = document.createElement('h3');
            header.textContent = volumeName + ' (' + volumeAssets.length + ' unused assets — ' + formatBytes(totalVolumeSize) + ')';
            volumeSection.appendChild(header);

            const selectAllWrapper = document.createElement('div');
            selectAllWrapper.className = 'select-all-wrapper';
            selectAllWrapper.innerHTML = '<label><input type="checkbox" class="volume-select-all" data-volume-id="' + volumeId + '" checked> Select All</label>';
            volumeSection.appendChild(selectAllWrapper);

            // Add per-volume action buttons
            const volumeActions = document.createElement('div');
            volumeActions.className = 'asset-cleaner-volume-actions';
            volumeActions.innerHTML = 
                '<div class="buttons">' +
                    '<button type="button" class="btn btn-sm volume-csv-btn" data-volume-id="' + volumeId + '">Download CSV</button>' +
                    '<button type="button" class="btn btn-sm volume-zip-btn" data-volume-id="' + volumeId + '">Download ZIP</button>' +
                    '<button type="button" class="btn btn-sm volume-trash-btn" data-volume-id="' + volumeId + '">Put into Trash</button>' +
                    '<button type="button" class="btn btn-sm submit volume-delete-btn" data-volume-id="' + volumeId + '">Delete Permanently</button>' +
                '</div>';
            volumeSection.appendChild(volumeActions);

            const grid = document.createElement('div');
            grid.className = 'asset-cleaner-grid';
            grid.dataset.volumeId = volumeId;

            const gridHeader = document.createElement('div');
            gridHeader.className = 'asset-cleaner-grid-header';
            gridHeader.innerHTML = 
                '<div class="grid-cell col-checkbox"></div>' +
                '<div class="grid-cell col-title">Title</div>' +
                '<div class="grid-cell col-filename">Filename</div>' +
                '<div class="grid-cell col-size">Size</div>' +
                '<div class="grid-cell col-path">Path</div>';
            grid.appendChild(gridHeader);

            const gridBody = document.createElement('div');
            gridBody.className = 'asset-cleaner-grid-body';

            volumeAssets.forEach(function(asset) {
                const row = document.createElement('div');
                row.className = 'asset-cleaner-grid-row';
                row.dataset.assetId = asset.id;
                row.innerHTML = 
                    '<div class="grid-cell col-checkbox"><input type="checkbox" class="asset-checkbox" value="' + asset.id + '" checked></div>' +
                    '<div class="grid-cell col-title" title="' + escapeHtml(asset.title) + '"><a href="' + escapeHtml(asset.cpUrl) + '" target="_blank">' + escapeHtml(asset.title) + '</a></div>' +
                    '<div class="grid-cell col-filename" title="' + escapeHtml(asset.filename) + '">' + escapeHtml(asset.filename) + '</div>' +
                    '<div class="grid-cell col-size">' + formatBytes(asset.size) + '</div>' +
                    '<div class="grid-cell col-path" title="' + escapeHtml(asset.path) + '">' + escapeHtml(asset.path) + '</div>';
                gridBody.appendChild(row);
            });

            grid.appendChild(gridBody);
            volumeSection.appendChild(grid);
            
            // Add separator between volumes (except after the last one)
            if (index < volumeKeys.length - 1) {
                const separator = document.createElement('hr');
                separator.className = 'volume-separator';
                volumeSection.appendChild(separator);
            }
            
            volumesContainer.appendChild(volumeSection);

            // Add change listeners for checkboxes
            gridBody.querySelectorAll('.asset-checkbox').forEach(function(cb) {
                cb.addEventListener('change', updateSelectedAssets);
            });

            // Add select all listener for this volume
            const volumeSelectAll = selectAllWrapper.querySelector('.volume-select-all');
            volumeSelectAll.addEventListener('change', function(e) {
                const checked = e.target.checked;
                gridBody.querySelectorAll('.asset-checkbox').forEach(function(cb) {
                    cb.checked = checked;
                });
                updateSelectedAssets();
            });

            // Add per-volume action button listeners
            volumeActions.querySelector('.volume-csv-btn').addEventListener('click', function() {
                handleVolumeExportCsv(volumeId);
            });
            volumeActions.querySelector('.volume-zip-btn').addEventListener('click', function() {
                handleVolumeExportZip(volumeId);
            });
            volumeActions.querySelector('.volume-trash-btn').addEventListener('click', function() {
                handleVolumeTrash(volumeId);
            });
            volumeActions.querySelector('.volume-delete-btn').addEventListener('click', function() {
                handleVolumeDeletePermanently(volumeId);
            });
        });
    }

    function handleSelectAll(e) {
        const checked = e.target.checked;
        const container = document.querySelector('.asset-cleaner-utility');
        container.querySelectorAll('.asset-checkbox').forEach(function(cb) {
            cb.checked = checked;
        });
        updateSelectedAssets();
    }

    function updateSelectedAssets() {
        selectedAssetIds = [];
        document.querySelectorAll('.asset-checkbox:checked').forEach(function(cb) {
            selectedAssetIds.push(parseInt(cb.value, 10));
        });
    }

    function handleExportCsv() {
        const container = document.querySelector('.asset-cleaner-utility');
        const volumeIds = [];
        container.querySelectorAll('.volume-checkboxes input[type="checkbox"]:checked').forEach(function(cb) {
            volumeIds.push(cb.value);
        });

        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = Craft.getActionUrl('asset-cleaner/asset-cleaner/export');

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = Craft.csrfTokenName;
        csrfInput.value = Craft.csrfTokenValue;
        form.appendChild(csrfInput);

        volumeIds.forEach(function(id) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'volumeIds[]';
            input.value = id;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }

    function handleExportZip() {
        if (selectedAssetIds.length === 0) {
            Craft.cp.displayError('No assets selected.');
            return;
        }

        // Ask user about folder structure preference
        showFolderStructureDialog(function(preserveFolders) {
            submitZipDownload(selectedAssetIds, preserveFolders);
        });
    }

    function submitZipDownload(assetIds, preserveFolders) {
        // Show loading overlay
        showDownloadOverlay('Preparing ZIP file... This may take several minutes for large files. Please wait.');

        // Create form and submit directly (no iframe needed)
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = Craft.getActionUrl('asset-cleaner/asset-cleaner/zip');

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = Craft.csrfTokenName;
        csrfInput.value = Craft.csrfTokenValue;
        form.appendChild(csrfInput);

        // Add preserveFolders parameter
        const preserveFoldersInput = document.createElement('input');
        preserveFoldersInput.type = 'hidden';
        preserveFoldersInput.name = 'preserveFolders';
        preserveFoldersInput.value = preserveFolders ? '1' : '0';
        form.appendChild(preserveFoldersInput);

        assetIds.forEach(function(id) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'assetIds[]';
            input.value = id;
            form.appendChild(input);
        });

        // Show notification immediately before form submission
        Craft.cp.displayNotice('ZIP download initiated. Large files may take several minutes to prepare.');

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);

        // Hide overlay after short delay
        setTimeout(function() {
            hideDownloadOverlay();
        }, 2000);
    }

    function handleTrash() {
        if (selectedAssetIds.length === 0) {
            Craft.cp.displayError('No assets selected.');
            return;
        }

        if (!confirm('Are you sure you want to move ' + selectedAssetIds.length + ' assets to trash?')) {
            return;
        }

        Craft.sendActionRequest('POST', 'asset-cleaner/asset-cleaner/trash', {
            data: { assetIds: selectedAssetIds }
        })
        .then(function(response) {
            const data = response.data;
            if (data.success) {
                Craft.cp.displayNotice('Moved ' + data.trashedCount + ' assets to trash.');
                // Re-scan
                document.querySelector('.asset-cleaner-scan-btn').click();
            } else {
                Craft.cp.displayError(data.error || 'An error occurred.');
            }
        })
        .catch(function(error) {
            Craft.cp.displayError(t.error || 'An error occurred.');
        });
    }

    function handleDeletePermanently() {
        if (selectedAssetIds.length === 0) {
            Craft.cp.displayError('No assets selected.');
            return;
        }

        const warningMessage = 
            '⚠️ WARNING: You are about to permanently delete ' + selectedAssetIds.length + ' assets.\n\n' +
            'This action CANNOT be undone!\n\n' +
            'We strongly recommend downloading the unused assets as a backup before proceeding.\n\n' +
            'Click "Download CSV" or "Download ZIP" to backup your assets first.\n\n' +
            'Are you absolutely sure you want to permanently delete these assets?';

        if (!confirm(warningMessage)) {
            return;
        }

        // Double confirmation
        if (!confirm('Final confirmation: Permanently delete ' + selectedAssetIds.length + ' assets? This CANNOT be undone!')) {
            return;
        }

        Craft.sendActionRequest('POST', 'asset-cleaner/asset-cleaner/delete', {
            data: { assetIds: selectedAssetIds }
        })
        .then(function(response) {
            const data = response.data;
            if (data.success) {
                Craft.cp.displayNotice('Permanently deleted ' + data.deletedCount + ' assets.');
                // Re-scan
                document.querySelector('.asset-cleaner-scan-btn').click();
            } else {
                Craft.cp.displayError(data.error || 'An error occurred.');
            }
        })
        .catch(function(error) {
            Craft.cp.displayError(t.error || 'An error occurred.');
        });
    }

    // ========================================
    // Per-Volume Action Handlers
    // ========================================

    function getVolumeAssetIds(volumeId) {
        const assetIds = [];
        const grid = document.querySelector('.asset-cleaner-grid[data-volume-id="' + volumeId + '"]');
        if (grid) {
            grid.querySelectorAll('.asset-checkbox:checked').forEach(function(cb) {
                assetIds.push(parseInt(cb.value, 10));
            });
        }
        return assetIds;
    }

    function handleVolumeExportCsv(volumeId) {
        const container = document.querySelector('.asset-cleaner-utility');
        
        // Create form and submit with only this volume
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = Craft.getActionUrl('asset-cleaner/asset-cleaner/export');

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = Craft.csrfTokenName;
        csrfInput.value = Craft.csrfTokenValue;
        form.appendChild(csrfInput);

        const volumeInput = document.createElement('input');
        volumeInput.type = 'hidden';
        volumeInput.name = 'volumeIds[]';
        volumeInput.value = volumeId;
        form.appendChild(volumeInput);

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }

    function handleVolumeExportZip(volumeId) {
        const assetIds = getVolumeAssetIds(volumeId);
        
        if (assetIds.length === 0) {
            Craft.cp.displayError('No assets selected in this volume.');
            return;
        }

        // Ask user about folder structure preference
        showFolderStructureDialog(function(preserveFolders) {
            submitZipDownload(assetIds, preserveFolders);
        });
    }

    function handleVolumeTrash(volumeId) {
        const assetIds = getVolumeAssetIds(volumeId);
        
        if (assetIds.length === 0) {
            Craft.cp.displayError('No assets selected in this volume.');
            return;
        }

        if (!confirm('Are you sure you want to move ' + assetIds.length + ' assets to trash?')) {
            return;
        }

        Craft.sendActionRequest('POST', 'asset-cleaner/asset-cleaner/trash', {
            data: { assetIds: assetIds }
        })
        .then(function(response) {
            const data = response.data;
            if (data.success) {
                Craft.cp.displayNotice('Moved ' + data.trashedCount + ' assets to trash.');
                // Re-scan
                document.querySelector('.asset-cleaner-scan-btn').click();
            } else {
                Craft.cp.displayError(data.error || 'An error occurred.');
            }
        })
        .catch(function(error) {
            Craft.cp.displayError(t.error || 'An error occurred.');
        });
    }

    function handleVolumeDeletePermanently(volumeId) {
        const assetIds = getVolumeAssetIds(volumeId);
        
        if (assetIds.length === 0) {
            Craft.cp.displayError('No assets selected in this volume.');
            return;
        }

        const warningMessage = 
            '⚠️ WARNING: You are about to permanently delete ' + assetIds.length + ' assets from this volume.\n\n' +
            'This action CANNOT be undone!\n\n' +
            'We strongly recommend downloading the unused assets as a backup before proceeding.\n\n' +
            'Click "Download CSV" or "Download ZIP" to backup your assets first.\n\n' +
            'Are you absolutely sure you want to permanently delete these assets?';

        if (!confirm(warningMessage)) {
            return;
        }

        // Double confirmation
        if (!confirm('Final confirmation: Permanently delete ' + assetIds.length + ' assets? This CANNOT be undone!')) {
            return;
        }

        Craft.sendActionRequest('POST', 'asset-cleaner/asset-cleaner/delete', {
            data: { assetIds: assetIds }
        })
        .then(function(response) {
            const data = response.data;
            if (data.success) {
                Craft.cp.displayNotice('Permanently deleted ' + data.deletedCount + ' assets.');
                // Re-scan
                document.querySelector('.asset-cleaner-scan-btn').click();
            } else {
                Craft.cp.displayError(data.error || 'An error occurred.');
            }
        })
        .catch(function(error) {
            Craft.cp.displayError(t.error || 'An error occurred.');
        });
    }

    // ========================================
    // Helpers
    // ========================================

    function showFolderStructureDialog(callback) {
        // Remove existing dialog if any
        hideFolderStructureDialog();
        
        const overlay = document.createElement('div');
        overlay.id = 'asset-cleaner-folder-dialog';
        overlay.innerHTML = 
            '<div class="folder-dialog-content">' +
                '<h3>ZIP Download Options</h3>' +
                '<p>How would you like to organize the files in the ZIP?</p>' +
                '<div class="folder-dialog-buttons">' +
                    '<button type="button" class="btn" id="zip-flat-btn">Flat (all files in root)</button>' +
                    '<button type="button" class="btn submit" id="zip-folders-btn">Preserve folder structure</button>' +
                '</div>' +
                '<button type="button" class="btn small" id="zip-cancel-btn">Cancel</button>' +
            '</div>';
        document.body.appendChild(overlay);

        // Add event listeners
        document.getElementById('zip-flat-btn').addEventListener('click', function() {
            hideFolderStructureDialog();
            callback(false);
        });

        document.getElementById('zip-folders-btn').addEventListener('click', function() {
            hideFolderStructureDialog();
            callback(true);
        });

        document.getElementById('zip-cancel-btn').addEventListener('click', function() {
            hideFolderStructureDialog();
        });
    }

    function hideFolderStructureDialog() {
        const dialog = document.getElementById('asset-cleaner-folder-dialog');
        if (dialog) {
            dialog.remove();
        }
    }

    function showDownloadOverlay(message) {
        // Remove existing overlay if any
        hideDownloadOverlay();
        
        const overlay = document.createElement('div');
        overlay.id = 'asset-cleaner-download-overlay';
        overlay.innerHTML = 
            '<div class="download-overlay-content">' +
                '<div class="spinner"></div>' +
                '<p>' + escapeHtml(message) + '</p>' +
            '</div>';
        document.body.appendChild(overlay);
    }

    function hideDownloadOverlay() {
        const overlay = document.getElementById('asset-cleaner-download-overlay');
        if (overlay) {
            overlay.remove();
        }
    }

    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function formatBytes(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

})();
