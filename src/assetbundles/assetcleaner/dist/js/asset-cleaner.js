(function () {
    "use strict";

    const settings = window.AssetCleanerSettings || { translations: {} };
    const t = settings.translations;
    const usageDefaults = settings.usageDefaults || {};

    // State
    let selectedAssetIds = [];
    let unusedAssets = [];
    let activeScanId = settings.lastScanId || null;
    let tooltipTimer = null;
    let hasWarnedPollFailure = false;

    // Initialize when DOM is ready
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
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
        document.addEventListener("click", function (e) {
            const btn = e.target.closest(".asset-cleaner-usage-btn");
            if (!btn) return;

            e.preventDefault();
            const assetId = btn.dataset.assetId;
            if (!assetId) return;

            showUsageDialog(assetId);
        });
    }

    function showUsageDialog(assetId) {
        closeUsageDialog();

        const dialog = document.createElement("div");
        dialog.id = "asset-cleaner-usage-dialog";
        dialog.style.position = "fixed";
        dialog.style.inset = "0";
        dialog.style.background = "rgba(0, 0, 0, 0.45)";
        dialog.style.zIndex = "2000";
        dialog.style.display = "flex";
        dialog.style.alignItems = "center";
        dialog.style.justifyContent = "center";
        dialog.style.padding = "24px";

        dialog.innerHTML =
            '<div class="asset-cleaner-usage-dialog-content" style="background: var(--white); width: 100%; max-width: 520px; max-height: 80vh; overflow: auto; border-radius: var(--large-border-radius); box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3); padding: 24px;">' +
            '<div style="display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:16px;">' +
            '<h2 style="margin:0;">' +
            (t.usageDialogTitle || t.viewUsage || "View Usage") +
            "</h2>" +
            '<button type="button" class="btn small asset-cleaner-usage-close-btn">' +
            (t.cancel || "Close") +
            "</button>" +
            "</div>" +
            '<p style="margin:0 0 16px; color: var(--gray-600);">' +
            (t.usageDialogText ||
                "Choose how usage should be evaluated for this asset.") +
            "</p>" +
            '<div class="asset-cleaner-usage-options" style="display:flex; flex-direction:column; gap:12px; margin-bottom:16px;">' +
            '<label style="display:flex; align-items:flex-start; gap:8px;">' +
            '<input type="checkbox" class="asset-cleaner-usage-include-drafts"' +
            (usageDefaults.includeDrafts ? " checked" : "") +
            ">" +
            "<span>" +
            (t.includeDrafts || "Include drafts") +
            "</span>" +
            "</label>" +
            '<label style="display:flex; align-items:flex-start; gap:8px;">' +
            '<input type="checkbox" class="asset-cleaner-usage-include-revisions"' +
            (usageDefaults.includeRevisions ? " checked" : "") +
            ">" +
            "<span>" +
            (t.includeRevisions || "Include revisions") +
            "</span>" +
            "</label>" +
            '<label style="display:flex; flex-direction:column; align-items:flex-start; gap:4px;">' +
            '<span style="display:flex; align-items:flex-start; gap:8px;">' +
            '<input type="checkbox" class="asset-cleaner-usage-count-relations"' +
            (usageDefaults.countAllRelationsAsUsage ? " checked" : "") +
            ">" +
            "<span>" +
            (t.countAllRelationsAsUsage ||
                "Count all relational references as usage") +
            "</span>" +
            "</span>" +
            '<span style="color: var(--gray-500); font-size: 12px; padding-left: 24px;">' +
            (t.countAllRelationsAsUsageHelp ||
                "Recommended for projects with plugin-defined or unknown element types that may store asset relations outside normal entry content.") +
            "</span>" +
            "</label>" +
            "</div>" +
            '<div style="display:flex; gap:8px; margin-bottom:16px;">' +
            '<button type="button" class="btn submit asset-cleaner-usage-check-btn">' +
            (t.checkUsage || "Check Usage") +
            "</button>" +
            '<button type="button" class="btn asset-cleaner-usage-cancel-btn">' +
            (t.cancel || "Cancel") +
            "</button>" +
            "</div>" +
            '<div class="asset-cleaner-usage-results asset-cleaner-popover">' +
            '<p class="not-used" style="font-style:normal;">' +
            (t.chooseUsageOptions ||
                "Choose the usage options you want to check, then confirm.") +
            "</p>" +
            "</div>" +
            "</div>";

        document.body.appendChild(dialog);

        dialog.addEventListener("click", function (e) {
            if (e.target === dialog) {
                closeUsageDialog();
            }
        });

        dialog
            .querySelector(".asset-cleaner-usage-close-btn")
            .addEventListener("click", closeUsageDialog);
        dialog
            .querySelector(".asset-cleaner-usage-cancel-btn")
            .addEventListener("click", closeUsageDialog);
        dialog
            .querySelector(".asset-cleaner-usage-check-btn")
            .addEventListener("click", function () {
                submitUsageDialog(dialog, assetId);
            });
    }

    function closeUsageDialog() {
        const dialog = document.getElementById("asset-cleaner-usage-dialog");
        if (dialog) {
            dialog.remove();
        }
    }

    function submitUsageDialog(dialog, assetId) {
        const results = dialog.querySelector(".asset-cleaner-usage-results");
        const checkBtn = dialog.querySelector(".asset-cleaner-usage-check-btn");
        const includeDrafts = !!dialog.querySelector(
            ".asset-cleaner-usage-include-drafts",
        ).checked;
        const includeRevisions = !!dialog.querySelector(
            ".asset-cleaner-usage-include-revisions",
        ).checked;
        const countAllRelationsAsUsage = !!dialog.querySelector(
            ".asset-cleaner-usage-count-relations",
        ).checked;

        checkBtn.disabled = true;
        results.innerHTML =
            '<div class="spinner"></div><span>' +
            (t.loading || "Loading...") +
            "</span>";

        Craft.sendActionRequest("GET", "asset-cleaner/usage/get", {
            params: {
                assetId: assetId,
                includeDrafts: includeDrafts,
                includeRevisions: includeRevisions,
                countAllRelationsAsUsage: countAllRelationsAsUsage,
            },
        })
            .then(function (response) {
                renderUsageDialogResults(results, response.data);
            })
            .catch(function () {
                results.innerHTML =
                    '<p class="error">' +
                    (t.error || "An error occurred.") +
                    "</p>";
            })
            .finally(function () {
                checkBtn.disabled = false;
            });
    }

    function renderUsageDialogResults(container, data) {
        if (!data || !data.success) {
            container.innerHTML =
                '<p class="error">' +
                ((data && data.error) || t.error || "An error occurred.") +
                "</p>";
            return;
        }

        if (!data.isUsed) {
            container.innerHTML =
                '<p class="not-used">' +
                (t.notUsed || "This asset is not used anywhere.") +
                "</p>";
            return;
        }

        let html = "";

        if (data.usage.relations && data.usage.relations.length > 0) {
            html += '<div class="usage-section">';
            html += "<h4>" + (t.usedByEntries || "Used by Entries") + "</h4>";
            html += '<ul class="usage-list">';
            data.usage.relations.forEach(function (entry) {
                html += "<li>";
                html += '<a href="' + entry.url + '" target="_blank">';

                if (entry.status) {
                    html +=
                        '<span class="status ' +
                        escapeHtml(entry.status) +
                        '"></span>';
                }

                html += escapeHtml(entry.title || entry.label || "Relation");

                const usageContextLabel = getUsageContextLabel(entry);
                if (usageContextLabel) {
                    html +=
                        '<span class="section-name">(' +
                        escapeHtml(usageContextLabel) +
                        ")</span>";
                }

                html += "</a>";
                html += "</li>";
            });
            html += "</ul></div>";
        }

        if (data.usage.otherRelations && data.usage.otherRelations.length > 0) {
            html += '<div class="usage-section">';
            html +=
                "<h4>" +
                (t.otherRelationalElements || "Other Relational Elements") +
                "</h4>";
            html += '<ul class="usage-list">';
            data.usage.otherRelations.forEach(function (entry) {
                html += "<li>";
                html += '<div class="usage-row">';

                if (entry.status) {
                    html +=
                        '<span class="status ' +
                        escapeHtml(entry.status) +
                        '"></span>';
                }

                html += escapeHtml(entry.title || entry.label || "Relation");

                const usageContextLabel = getUsageContextLabel(entry);
                if (usageContextLabel) {
                    html +=
                        '<span class="section-name">(' +
                        escapeHtml(usageContextLabel) +
                        ")</span>";
                }

                html += "</div>";
                html += "</li>";
            });
            html += "</ul></div>";
        }

        if (data.usage.content && data.usage.content.length > 0) {
            html += '<div class="usage-section">';
            html +=
                "<h4>" +
                (t.usedInContentFields || "Used in Content Fields") +
                "</h4>";
            html += '<ul class="usage-list">';
            data.usage.content.forEach(function (entry) {
                html += "<li>";
                html += '<a href="' + entry.url + '" target="_blank">';
                html += '<span class="status ' + entry.status + '"></span>';
                html += escapeHtml(entry.title);
                const contentUsageContextLabel = getUsageContextLabel(entry);
                const contentUsageLabel = contentUsageContextLabel
                    ? entry.field + " — " + contentUsageContextLabel
                    : entry.field;
                html +=
                    '<span class="field-name">(' +
                    escapeHtml(contentUsageLabel) +
                    ")</span>";
                html += "</a></li>";
            });
            html += "</ul></div>";
        }

        container.innerHTML = html;
    }

    function getUsageContextLabel(entry) {
        const baseLabel =
            entry && entry.section
                ? entry.section === "User"
                    ? "User profile picture"
                    : entry.section
                : entry && entry.sourceType
                  ? entry.sourceType
                  : "";

        const usageStateLabel = getUsageStateLabelFromUrl(
            entry && entry.url ? entry.url : "",
        );

        if (baseLabel && usageStateLabel) {
            return baseLabel + ", " + usageStateLabel;
        }

        return baseLabel || usageStateLabel || "";
    }

    function getUsageStateLabelFromUrl(url) {
        if (!url) {
            return "";
        }

        try {
            const parsedUrl = new URL(url, window.location.origin);
            const draftId = parsedUrl.searchParams.get("draftId");
            if (draftId) {
                return "draft #" + draftId;
            }

            const revisionId = parsedUrl.searchParams.get("revisionId");
            if (revisionId) {
                return "revision #" + revisionId;
            }
        } catch (e) {
            const draftMatch = String(url).match(/[?&]draftId=(\d+)/);
            if (draftMatch) {
                return "draft #" + draftMatch[1];
            }

            const revisionMatch = String(url).match(/[?&]revisionId=(\d+)/);
            if (revisionMatch) {
                return "revision #" + revisionMatch[1];
            }
        }

        return "";
    }

    // ========================================
    // Utility Page
    // ========================================

    function initUtilityPage() {
        const utilityContainer = document.querySelector(
            ".asset-cleaner-utility",
        );
        if (!utilityContainer) return;

        // Scan button
        const scanBtn = utilityContainer.querySelector(
            ".asset-cleaner-scan-btn",
        );
        if (scanBtn) {
            scanBtn.addEventListener("click", handleScan);
        }

        // CSV button
        const csvBtn = utilityContainer.querySelector(".asset-cleaner-csv-btn");
        if (csvBtn) {
            csvBtn.addEventListener("click", handleExportCsv);
        }

        // ZIP button
        const zipBtn = utilityContainer.querySelector(".asset-cleaner-zip-btn");
        if (zipBtn) {
            zipBtn.addEventListener("click", handleExportZip);
        }

        // Trash button
        const trashBtn = utilityContainer.querySelector(
            ".asset-cleaner-trash-btn",
        );
        if (trashBtn) {
            trashBtn.addEventListener("click", handleTrash);
        }

        // Delete permanently button
        const deleteBtn = utilityContainer.querySelector(
            ".asset-cleaner-delete-btn",
        );
        if (deleteBtn) {
            deleteBtn.addEventListener("click", handleDeletePermanently);
        }

        // Auto-restore last scan results
        if (settings.lastScanId) {
            restoreLastScan(
                utilityContainer,
                settings.lastScanId,
                settings.lastScanTime,
            );
        }
    }

    function restoreLastScan(container, scanId, scanTime) {
        var loading = container.querySelector(".asset-cleaner-loading");
        var results = container.querySelector(".asset-cleaner-results");
        var loadingText = loading.querySelector(".loading-text");

        loading.style.display = "flex";
        loadingText.textContent =
            t.restoringLastScan || "Restoring last scan...";

        Craft.sendActionRequest(
            "GET",
            "asset-cleaner/asset-cleaner/scan-results",
            {
                params: { scanId: scanId },
            },
        )
            .then(function (resultsResponse) {
                loading.style.display = "none";

                var data = resultsResponse.data;
                if (!data || !data.success) {
                    return;
                }

                try {
                    activeScanId = scanId;
                    results.style.display = "block";
                    container.querySelector(".used-count").textContent =
                        data.usedCount;
                    container.querySelector(".unused-count").textContent =
                        data.unusedCount;

                    unusedAssets = data.unusedAssets || [];
                    selectedAssetIds = unusedAssets.map(function (a) {
                        return a.id;
                    });

                    var volumeNames = {};
                    unusedAssets.forEach(function (asset) {
                        if (asset.volumeId && asset.volume) {
                            volumeNames[asset.volumeId] = asset.volume;
                        }
                    });

                    renderVolumesTables(container, unusedAssets, volumeNames);

                    var uniqueVolumes = new Set();
                    unusedAssets.forEach(function (asset) {
                        uniqueVolumes.add(asset.volumeId);
                    });
                    var volumeCount = uniqueVolumes.size;

                    var actions = container.querySelector(
                        ".asset-cleaner-actions",
                    );
                    var separator = container.querySelector(
                        ".asset-cleaner-separator",
                    );

                    if (unusedAssets.length > 0 && volumeCount > 1) {
                        actions.style.display = "block";
                        separator.style.display = "block";
                    } else {
                        actions.style.display = "none";
                        separator.style.display = "none";
                    }

                    showScanTime(container, scanTime);
                } catch (e) {
                    console.error(
                        "Asset Cleaner: failed to restore last scan UI.",
                        e,
                    );
                    if (Craft && Craft.cp && Craft.cp.displayWarning) {
                        Craft.cp.displayWarning(
                            t.error || "An error occurred.",
                        );
                    }
                }
            })
            .catch(function () {
                loading.style.display = "none";
            });
    }

    function showScanTime(container, isoTime) {
        var meta = container.querySelector(".asset-cleaner-scan-meta");
        var el = container.querySelector(".asset-cleaner-scan-time");
        if (!meta || !el || !isoTime) return;

        var date = new Date(isoTime);
        var formatted =
            date.toLocaleDateString(undefined, {
                year: "numeric",
                month: "short",
                day: "numeric",
            }) +
            " at " +
            date.toLocaleTimeString(undefined, {
                hour: "numeric",
                minute: "2-digit",
            });

        var template = t.scannedOn || "Scanned on {date}";
        el.textContent = template.replace("{date}", formatted);

        // Remove any previous stale badge
        var oldBadge = meta.querySelector(".asset-cleaner-scan-stale");
        if (oldBadge) oldBadge.remove();

        // Add stale badge if scan is older than 24 hours
        var ageHours = (Date.now() - date.getTime()) / (1000 * 60 * 60);
        if (ageHours > 24) {
            var badge = document.createElement("span");
            badge.className = "asset-cleaner-scan-stale";
            badge.textContent =
                t.scanStale || "Scan older than 24h — results may be outdated";
            meta.appendChild(badge);
        }

        meta.style.display = "flex";
    }

    function handleScan() {
        const container = document.querySelector(".asset-cleaner-utility");
        const loading = container.querySelector(".asset-cleaner-loading");
        const results = container.querySelector(".asset-cleaner-results");
        const progressBarContainer = loading.querySelector(
            ".progress-bar-container",
        );
        const progressBarFill = loading.querySelector(".progress-bar-fill");
        const progressText = loading.querySelector(".progress-text");
        const loadingText = loading.querySelector(".loading-text");
        const queueHint = container.querySelector(".asset-cleaner-queue-hint");
        const scanBtn = container.querySelector(".asset-cleaner-scan-btn");
        const includeDraftsInput = container.querySelector(
            'input[type="checkbox"][name="includeDrafts"]',
        );
        const includeDrafts = !!(
            includeDraftsInput && includeDraftsInput.checked
        );
        const includeRevisionsInput = container.querySelector(
            'input[type="checkbox"][name="includeRevisions"]',
        );
        const includeRevisions = !!(
            includeRevisionsInput && includeRevisionsInput.checked
        );
        const countAllRelationsAsUsageInput = container.querySelector(
            'input[type="checkbox"][name="countAllRelationsAsUsage"]',
        );
        const countAllRelationsAsUsage = !!(
            countAllRelationsAsUsageInput &&
            countAllRelationsAsUsageInput.checked
        );

        // Get selected volumes
        const volumeIds = [];
        const volumeNames = {};
        container
            .querySelectorAll(
                '.volume-checkboxes input[type="checkbox"]:checked',
            )
            .forEach(function (cb) {
                volumeIds.push(cb.value);
                const label = cb
                    .closest(".checkbox-wrapper")
                    .querySelector("label");
                volumeNames[cb.value] = label
                    ? label.textContent.trim()
                    : "Volume " + cb.value;
            });

        scanBtn.disabled = true;
        hasWarnedPollFailure = false;

        loading.style.display = "flex";
        progressBarContainer.style.display = "block";
        results.style.display = "none";
        if (queueHint) queueHint.style.display = "none";
        progressBarFill.style.width = "0%";
        progressText.textContent = "0%";
        loadingText.textContent = t.scanQueued || "Scan queued...";

        var pollTimer = null;
        var queueHintTimer = null;

        Craft.sendActionRequest(
            "POST",
            "asset-cleaner/asset-cleaner/start-scan",
            {
                data: {
                    volumeIds: volumeIds,
                    includeDrafts: includeDrafts,
                    includeRevisions: includeRevisions,
                    countAllRelationsAsUsage: countAllRelationsAsUsage,
                },
            },
        )
            .then(function (response) {
                if (!response.data.success) {
                    throw new Error(
                        response.data.error || "Failed to start scan.",
                    );
                }

                var scanId = response.data.scanId;

                // Show queue hint after 30s if still pending
                queueHintTimer = setTimeout(function () {
                    if (queueHint) queueHint.style.display = "block";
                }, 30000);

                // Poll for progress
                pollTimer = setInterval(function () {
                    Craft.sendActionRequest(
                        "GET",
                        "asset-cleaner/asset-cleaner/scan-progress",
                        {
                            params: { scanId: scanId },
                        },
                    )
                        .then(function (pollResponse) {
                            var d = pollResponse.data;
                            if (!d.success) return;

                            // Hide queue hint once running
                            if (d.status === "running" && queueHint) {
                                queueHint.style.display = "none";
                                if (queueHintTimer)
                                    clearTimeout(queueHintTimer);
                            }

                            // Update progress bar
                            if (
                                d.status === "running" ||
                                d.status === "complete"
                            ) {
                                progressBarFill.style.width = d.progress + "%";
                                if (d.totalAssets > 0) {
                                    progressText.textContent =
                                        d.progress +
                                        "% (" +
                                        d.processedAssets +
                                        "/" +
                                        d.totalAssets +
                                        " assets)";
                                } else {
                                    progressText.textContent = d.progress + "%";
                                }
                                loadingText.textContent =
                                    d.stageLabel || t.scanning || "Scanning...";
                            }

                            // Complete
                            if (d.status === "complete") {
                                clearInterval(pollTimer);
                                if (queueHintTimer)
                                    clearTimeout(queueHintTimer);
                                progressBarFill.style.width = "100%";
                                progressText.textContent = "100%";

                                // Fetch full results
                                Craft.sendActionRequest(
                                    "GET",
                                    "asset-cleaner/asset-cleaner/scan-results",
                                    {
                                        params: { scanId: scanId },
                                    },
                                )
                                    .then(function (resultsResponse) {
                                        var data = resultsResponse.data;
                                        if (!data.success) {
                                            throw new Error(
                                                data.error ||
                                                    "Failed to load results.",
                                            );
                                        }

                                        loading.style.display = "none";
                                        progressBarContainer.style.display =
                                            "none";
                                        if (queueHint)
                                            queueHint.style.display = "none";
                                        scanBtn.disabled = false;

                                        activeScanId = scanId;
                                        results.style.display = "block";
                                        container.querySelector(
                                            ".used-count",
                                        ).textContent = data.usedCount;
                                        container.querySelector(
                                            ".unused-count",
                                        ).textContent = data.unusedCount;

                                        unusedAssets = data.unusedAssets || [];
                                        selectedAssetIds = unusedAssets.map(
                                            function (a) {
                                                return a.id;
                                            },
                                        );

                                        renderVolumesTables(
                                            container,
                                            unusedAssets,
                                            volumeNames,
                                        );

                                        var uniqueVolumes = new Set();
                                        unusedAssets.forEach(function (asset) {
                                            uniqueVolumes.add(asset.volumeId);
                                        });
                                        var volumeCount = uniqueVolumes.size;

                                        var actions = container.querySelector(
                                            ".asset-cleaner-actions",
                                        );
                                        var separator = container.querySelector(
                                            ".asset-cleaner-separator",
                                        );

                                        if (
                                            unusedAssets.length > 0 &&
                                            volumeCount > 1
                                        ) {
                                            actions.style.display = "block";
                                            separator.style.display = "block";
                                        } else {
                                            actions.style.display = "none";
                                            separator.style.display = "none";
                                        }

                                        showScanTime(
                                            container,
                                            data.completedAt ||
                                                new Date().toISOString(),
                                        );
                                    })
                                    .catch(function () {
                                        loading.style.display = "none";
                                        scanBtn.disabled = false;
                                        Craft.cp.displayError(
                                            t.error || "An error occurred.",
                                        );
                                    });
                            }

                            // Failed
                            if (d.status === "failed") {
                                clearInterval(pollTimer);
                                if (queueHintTimer)
                                    clearTimeout(queueHintTimer);
                                loading.style.display = "none";
                                progressBarContainer.style.display = "none";
                                if (queueHint) queueHint.style.display = "none";
                                scanBtn.disabled = false;
                                Craft.cp.displayError(
                                    d.error || t.scanFailed || "Scan failed.",
                                );
                            }
                        })
                        .catch(function (error) {
                            console.warn(
                                "Asset Cleaner: failed to poll scan progress.",
                                error,
                            );

                            if (queueHint) {
                                queueHint.style.display = "block";
                            }

                            if (!hasWarnedPollFailure) {
                                hasWarnedPollFailure = true;
                                if (
                                    Craft &&
                                    Craft.cp &&
                                    Craft.cp.displayWarning
                                ) {
                                    Craft.cp.displayWarning(
                                        t.scanPollingIssue ||
                                            "Lost contact while polling scan progress. The scan may still be running.",
                                    );
                                }
                            }
                        });
                }, 1500);
            })
            .catch(function () {
                loading.style.display = "none";
                progressBarContainer.style.display = "none";
                if (queueHint) queueHint.style.display = "none";
                scanBtn.disabled = false;
                Craft.cp.displayError(t.error || "An error occurred.");
            });
    }

    function renderVolumesTables(container, assets, volumeNames) {
        const volumesContainer = container.querySelector(
            ".asset-cleaner-volumes-container",
        );
        volumesContainer.innerHTML = "";

        if (assets.length === 0) {
            volumesContainer.innerHTML =
                '<div style="text-align:center; padding:24px; color:var(--gray-500);">' +
                (t.noUnusedAssetsFound || "No unused assets found.") +
                "</div>";
            return;
        }

        // Group assets by volume
        const assetsByVolume = {};
        assets.forEach(function (asset) {
            const volumeId = asset.volumeId || "unknown";
            if (!assetsByVolume[volumeId]) {
                assetsByVolume[volumeId] = [];
            }
            assetsByVolume[volumeId].push(asset);
        });

        // Render a table for each volume
        const volumeKeys = Object.keys(assetsByVolume);
        volumeKeys.forEach(function (volumeId, index) {
            const volumeAssets = assetsByVolume[volumeId];
            const volumeName =
                volumeNames[volumeId] ||
                volumeAssets[0].volume ||
                "Unknown Volume";

            const totalVolumeSize = volumeAssets.reduce(function (sum, asset) {
                return sum + (asset.size || 0);
            }, 0);

            const volumeSection = document.createElement("div");
            volumeSection.className = "asset-cleaner-volume-section";

            const header = document.createElement("h3");
            var headerSummary = (
                t.volumeHeader || "{count} unused assets — {size}"
            )
                .replace("{count}", volumeAssets.length)
                .replace("{size}", formatBytes(totalVolumeSize));
            header.textContent = volumeName + " (" + headerSummary + ")";
            volumeSection.appendChild(header);

            const selectAllWrapper = document.createElement("div");
            selectAllWrapper.className = "select-all-wrapper";
            selectAllWrapper.innerHTML =
                '<label><input type="checkbox" class="volume-select-all" data-volume-id="' +
                volumeId +
                '" checked> ' +
                (t.selectAll || "Select All") +
                "</label>";
            volumeSection.appendChild(selectAllWrapper);

            // Add per-volume action buttons
            const volumeActions = document.createElement("div");
            volumeActions.className = "asset-cleaner-volume-actions";
            volumeActions.innerHTML =
                '<div class="buttons">' +
                '<button type="button" class="btn btn-sm volume-csv-btn" data-volume-id="' +
                volumeId +
                '">' +
                (t.downloadCsv || "Download CSV") +
                "</button>" +
                '<button type="button" class="btn btn-sm volume-zip-btn" data-volume-id="' +
                volumeId +
                '">' +
                (t.downloadZip || "Download ZIP") +
                "</button>" +
                '<button type="button" class="btn btn-sm volume-trash-btn" data-volume-id="' +
                volumeId +
                '">' +
                (t.putIntoTrash || "Put into Trash") +
                "</button>" +
                '<button type="button" class="btn btn-sm submit volume-delete-btn" data-volume-id="' +
                volumeId +
                '">' +
                (t.deletePermanently || "Delete Permanently") +
                "</button>" +
                "</div>";
            volumeSection.appendChild(volumeActions);

            const grid = document.createElement("div");
            grid.className = "asset-cleaner-grid";
            grid.dataset.volumeId = volumeId;

            const gridHeader = document.createElement("div");
            gridHeader.className = "asset-cleaner-grid-header";
            gridHeader.innerHTML =
                '<div class="grid-cell col-checkbox"></div>' +
                '<div class="grid-cell col-preview"></div>' +
                '<div class="grid-cell col-title">' +
                (t.colTitle || "Title") +
                "</div>" +
                '<div class="grid-cell col-filename">' +
                (t.colFilename || "Filename") +
                "</div>" +
                '<div class="grid-cell col-size">' +
                (t.colSize || "Size") +
                "</div>" +
                '<div class="grid-cell col-path">' +
                (t.colPath || "Path") +
                "</div>";
            grid.appendChild(gridHeader);

            const gridBody = document.createElement("div");
            gridBody.className = "asset-cleaner-grid-body";

            volumeAssets.forEach(function (asset) {
                const row = document.createElement("div");
                row.className = "asset-cleaner-grid-row";
                row.dataset.assetId = asset.id;

                var previewCell;
                if (asset.kind === "image" && asset.url) {
                    previewCell =
                        '<div class="grid-cell col-preview"><img class="asset-thumb" src="' +
                        escapeHtml(asset.url) +
                        '" alt="" loading="lazy" /></div>';
                } else {
                    previewCell =
                        '<div class="grid-cell col-preview"><span class="asset-kind-badge">' +
                        escapeHtml(asset.kind || "") +
                        "</span></div>";
                }

                row.innerHTML =
                    '<div class="grid-cell col-checkbox"><input type="checkbox" class="asset-checkbox" value="' +
                    asset.id +
                    '" checked></div>' +
                    previewCell +
                    '<div class="grid-cell col-title" title="' +
                    escapeHtml(asset.title) +
                    '"><a href="' +
                    escapeHtml(asset.cpUrl) +
                    '" target="_blank">' +
                    escapeHtml(asset.title) +
                    "</a></div>" +
                    '<div class="grid-cell col-filename" title="' +
                    escapeHtml(asset.filename) +
                    '">' +
                    escapeHtml(asset.filename) +
                    "</div>" +
                    '<div class="grid-cell col-size">' +
                    formatBytes(asset.size) +
                    "</div>" +
                    '<div class="grid-cell col-path" title="' +
                    escapeHtml(asset.path) +
                    '">' +
                    escapeHtml(asset.path) +
                    "</div>";

                // Hover preview tooltip for image assets
                if (asset.kind === "image" && asset.url) {
                    row.addEventListener("mouseenter", function (e) {
                        showPreviewTooltip(asset, e);
                    });
                    row.addEventListener("mouseleave", function () {
                        hidePreviewTooltip();
                    });
                }

                gridBody.appendChild(row);
            });

            grid.appendChild(gridBody);
            volumeSection.appendChild(grid);

            // Separator between volumes (except after the last one)
            if (index < volumeKeys.length - 1) {
                const separator = document.createElement("hr");
                separator.className = "volume-separator";
                volumeSection.appendChild(separator);
            }

            volumesContainer.appendChild(volumeSection);

            // Checkbox change listeners
            gridBody.querySelectorAll(".asset-checkbox").forEach(function (cb) {
                cb.addEventListener("change", updateSelectedAssets);
            });

            // Select-all listener for this volume
            const volumeSelectAll =
                selectAllWrapper.querySelector(".volume-select-all");
            volumeSelectAll.addEventListener("change", function (e) {
                const checked = e.target.checked;
                gridBody
                    .querySelectorAll(".asset-checkbox")
                    .forEach(function (cb) {
                        cb.checked = checked;
                    });
                updateSelectedAssets();
            });

            // Per-volume action button listeners
            volumeActions
                .querySelector(".volume-csv-btn")
                .addEventListener("click", function () {
                    handleVolumeExportCsv(volumeId);
                });
            volumeActions
                .querySelector(".volume-zip-btn")
                .addEventListener("click", function () {
                    handleVolumeExportZip(volumeId);
                });
            volumeActions
                .querySelector(".volume-trash-btn")
                .addEventListener("click", function () {
                    handleVolumeTrash(volumeId);
                });
            volumeActions
                .querySelector(".volume-delete-btn")
                .addEventListener("click", function () {
                    handleVolumeDeletePermanently(volumeId);
                });
        });
    }

    // ========================================
    // Preview Tooltip
    // ========================================

    function showPreviewTooltip(asset, e) {
        clearTimeout(tooltipTimer);
        tooltipTimer = setTimeout(function () {
            hidePreviewTooltip();

            var tooltip = document.createElement("div");
            tooltip.id = "asset-cleaner-preview-tooltip";

            var html = '<div class="preview-image-wrapper">';
            html += '<img src="' + escapeHtml(asset.url) + '" alt="" />';
            html += "</div>";
            html += '<div class="preview-meta">';
            html +=
                '<span class="preview-size">' +
                formatBytes(asset.size) +
                "</span>";
            html += "</div>";

            tooltip.innerHTML = html;
            document.body.appendChild(tooltip);

            // Position near cursor, keeping within viewport
            var x = e.clientX + 16;
            var y = e.clientY + 8;
            var w = tooltip.offsetWidth;
            var h = tooltip.offsetHeight;

            if (x + w > window.innerWidth - 12) {
                x = e.clientX - w - 16;
            }
            if (y + h > window.innerHeight - 12) {
                y = e.clientY - h - 8;
            }

            tooltip.style.left = x + "px";
            tooltip.style.top = y + "px";
        }, 300);
    }

    function hidePreviewTooltip() {
        clearTimeout(tooltipTimer);
        var existing = document.getElementById("asset-cleaner-preview-tooltip");
        if (existing) {
            existing.remove();
        }
    }

    function updateSelectedAssets() {
        selectedAssetIds = [];
        document
            .querySelectorAll(".asset-checkbox:checked")
            .forEach(function (cb) {
                selectedAssetIds.push(parseInt(cb.value, 10));
            });
    }

    // ========================================
    // CSV / ZIP Export
    // ========================================

    function handleExportCsv() {
        const container = document.querySelector(".asset-cleaner-utility");
        const volumeIds = [];
        container
            .querySelectorAll(
                '.volume-checkboxes input[type="checkbox"]:checked',
            )
            .forEach(function (cb) {
                volumeIds.push(cb.value);
            });

        submitCsvDownload(volumeIds, activeScanId);
    }

    function submitCsvDownload(volumeIds, scanId) {
        const form = document.createElement("form");
        form.method = "POST";
        form.action = Craft.getActionUrl("asset-cleaner/asset-cleaner/export");

        const csrfInput = document.createElement("input");
        csrfInput.type = "hidden";
        csrfInput.name = Craft.csrfTokenName;
        csrfInput.value = Craft.csrfTokenValue;
        form.appendChild(csrfInput);

        // Pass scanId so the server can use cached results instead of re-scanning
        if (scanId) {
            const scanIdInput = document.createElement("input");
            scanIdInput.type = "hidden";
            scanIdInput.name = "scanId";
            scanIdInput.value = scanId;
            form.appendChild(scanIdInput);
        }

        (volumeIds || []).forEach(function (id) {
            const input = document.createElement("input");
            input.type = "hidden";
            input.name = "volumeIds[]";
            input.value = id;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }

    function handleExportZip() {
        if (selectedAssetIds.length === 0) {
            Craft.cp.displayError(t.noAssetsSelected || "No assets selected.");
            return;
        }

        showFolderStructureDialog(function (preserveFolders) {
            submitZipDownload(selectedAssetIds, preserveFolders);
        });
    }

    function submitZipDownload(assetIds, preserveFolders) {
        showDownloadOverlay(
            t.zipPreparing ||
                "Preparing ZIP file... This may take several minutes for large files. Please wait.",
        );

        const form = document.createElement("form");
        form.method = "POST";
        form.action = Craft.getActionUrl("asset-cleaner/asset-cleaner/zip");

        const csrfInput = document.createElement("input");
        csrfInput.type = "hidden";
        csrfInput.name = Craft.csrfTokenName;
        csrfInput.value = Craft.csrfTokenValue;
        form.appendChild(csrfInput);

        const preserveFoldersInput = document.createElement("input");
        preserveFoldersInput.type = "hidden";
        preserveFoldersInput.name = "preserveFolders";
        preserveFoldersInput.value = preserveFolders ? "1" : "0";
        form.appendChild(preserveFoldersInput);

        assetIds.forEach(function (id) {
            const input = document.createElement("input");
            input.type = "hidden";
            input.name = "assetIds[]";
            input.value = id;
            form.appendChild(input);
        });

        Craft.cp.displayNotice(
            t.zipInitiated ||
                "ZIP download initiated. Large files may take several minutes to prepare.",
        );

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);

        setTimeout(function () {
            hideDownloadOverlay();
        }, 2000);
    }

    // ========================================
    // Trash / Delete
    // ========================================

    function handleTrash() {
        if (selectedAssetIds.length === 0) {
            Craft.cp.displayError(t.noAssetsSelected || "No assets selected.");
            return;
        }

        var msg = (
            t.confirmTrash ||
            "Are you sure you want to move {count} assets to trash?"
        ).replace("{count}", selectedAssetIds.length);
        if (!confirm(msg)) {
            return;
        }

        Craft.sendActionRequest("POST", "asset-cleaner/asset-cleaner/trash", {
            data: { assetIds: selectedAssetIds },
        })
            .then(function (response) {
                const data = response.data;
                if (data.success) {
                    var notice = (
                        t.movedToTrash || "Moved {count} assets to trash."
                    ).replace("{count}", data.trashedCount);
                    Craft.cp.displayNotice(notice);
                    document.querySelector(".asset-cleaner-scan-btn").click();
                } else {
                    Craft.cp.displayError(
                        data.error || t.error || "An error occurred.",
                    );
                }
            })
            .catch(function () {
                Craft.cp.displayError(t.error || "An error occurred.");
            });
    }

    function handleDeletePermanently() {
        if (selectedAssetIds.length === 0) {
            Craft.cp.displayError(t.noAssetsSelected || "No assets selected.");
            return;
        }

        var warning = (
            t.deleteWarning ||
            "Are you sure you want to permanently delete {count} assets? This action CANNOT be undone! Download a backup (CSV or ZIP) before proceeding."
        ).replace("{count}", selectedAssetIds.length);
        if (!confirm(warning)) {
            return;
        }

        var confirm2 = (
            t.deleteConfirm ||
            "Final confirmation: Permanently delete {count} assets? This CANNOT be undone!"
        ).replace("{count}", selectedAssetIds.length);
        if (!confirm(confirm2)) {
            return;
        }

        Craft.sendActionRequest("POST", "asset-cleaner/asset-cleaner/delete", {
            data: { assetIds: selectedAssetIds },
        })
            .then(function (response) {
                const data = response.data;
                if (data.success) {
                    var notice = (
                        t.permanentlyDeleted ||
                        "Permanently deleted {count} assets."
                    ).replace("{count}", data.deletedCount);
                    Craft.cp.displayNotice(notice);
                    document.querySelector(".asset-cleaner-scan-btn").click();
                } else {
                    Craft.cp.displayError(
                        data.error || t.error || "An error occurred.",
                    );
                }
            })
            .catch(function () {
                Craft.cp.displayError(t.error || "An error occurred.");
            });
    }

    // ========================================
    // Per-Volume Action Handlers
    // ========================================

    function getVolumeAssetIds(volumeId) {
        const assetIds = [];
        const grid = document.querySelector(
            '.asset-cleaner-grid[data-volume-id="' + volumeId + '"]',
        );
        if (grid) {
            grid.querySelectorAll(".asset-checkbox:checked").forEach(
                function (cb) {
                    assetIds.push(parseInt(cb.value, 10));
                },
            );
        }
        return assetIds;
    }

    function handleVolumeExportCsv(volumeId) {
        submitCsvDownload([volumeId], activeScanId);
    }

    function handleVolumeExportZip(volumeId) {
        const assetIds = getVolumeAssetIds(volumeId);

        if (assetIds.length === 0) {
            Craft.cp.displayError(
                t.noAssetsSelectedInVolume ||
                    "No assets selected in this volume.",
            );
            return;
        }

        showFolderStructureDialog(function (preserveFolders) {
            submitZipDownload(assetIds, preserveFolders);
        });
    }

    function handleVolumeTrash(volumeId) {
        const assetIds = getVolumeAssetIds(volumeId);

        if (assetIds.length === 0) {
            Craft.cp.displayError(
                t.noAssetsSelectedInVolume ||
                    "No assets selected in this volume.",
            );
            return;
        }

        var msg = (
            t.confirmTrash ||
            "Are you sure you want to move {count} assets to trash?"
        ).replace("{count}", assetIds.length);
        if (!confirm(msg)) {
            return;
        }

        Craft.sendActionRequest("POST", "asset-cleaner/asset-cleaner/trash", {
            data: { assetIds: assetIds },
        })
            .then(function (response) {
                const data = response.data;
                if (data.success) {
                    var notice = (
                        t.movedToTrash || "Moved {count} assets to trash."
                    ).replace("{count}", data.trashedCount);
                    Craft.cp.displayNotice(notice);
                    document.querySelector(".asset-cleaner-scan-btn").click();
                } else {
                    Craft.cp.displayError(
                        data.error || t.error || "An error occurred.",
                    );
                }
            })
            .catch(function () {
                Craft.cp.displayError(t.error || "An error occurred.");
            });
    }

    function handleVolumeDeletePermanently(volumeId) {
        const assetIds = getVolumeAssetIds(volumeId);

        if (assetIds.length === 0) {
            Craft.cp.displayError(
                t.noAssetsSelectedInVolume ||
                    "No assets selected in this volume.",
            );
            return;
        }

        var warning = (
            t.deleteWarning ||
            "Are you sure you want to permanently delete {count} assets? This action CANNOT be undone! Download a backup (CSV or ZIP) before proceeding."
        ).replace("{count}", assetIds.length);
        if (!confirm(warning)) {
            return;
        }

        var confirm2 = (
            t.deleteConfirm ||
            "Final confirmation: Permanently delete {count} assets? This CANNOT be undone!"
        ).replace("{count}", assetIds.length);
        if (!confirm(confirm2)) {
            return;
        }

        Craft.sendActionRequest("POST", "asset-cleaner/asset-cleaner/delete", {
            data: { assetIds: assetIds },
        })
            .then(function (response) {
                const data = response.data;
                if (data.success) {
                    var notice = (
                        t.permanentlyDeleted ||
                        "Permanently deleted {count} assets."
                    ).replace("{count}", data.deletedCount);
                    Craft.cp.displayNotice(notice);
                    document.querySelector(".asset-cleaner-scan-btn").click();
                } else {
                    Craft.cp.displayError(
                        data.error || t.error || "An error occurred.",
                    );
                }
            })
            .catch(function () {
                Craft.cp.displayError(t.error || "An error occurred.");
            });
    }

    // ========================================
    // Dialogs & Overlays
    // ========================================

    function showFolderStructureDialog(callback) {
        hideFolderStructureDialog();

        const overlay = document.createElement("div");
        overlay.id = "asset-cleaner-folder-dialog";
        overlay.innerHTML =
            '<div class="folder-dialog-content">' +
            "<h3>" +
            (t.zipDialogTitle || "ZIP Download Options") +
            "</h3>" +
            "<p>" +
            (t.zipDialogText ||
                "How would you like to organize the files in the ZIP?") +
            "</p>" +
            '<div class="folder-dialog-buttons">' +
            '<button type="button" class="btn" id="zip-flat-btn">' +
            (t.zipFlat || "Flat (all files in root)") +
            "</button>" +
            '<button type="button" class="btn submit" id="zip-folders-btn">' +
            (t.zipFolders || "Preserve folder structure") +
            "</button>" +
            "</div>" +
            '<button type="button" class="btn small" id="zip-cancel-btn">' +
            (t.cancel || "Cancel") +
            "</button>" +
            "</div>";
        document.body.appendChild(overlay);

        document
            .getElementById("zip-flat-btn")
            .addEventListener("click", function () {
                hideFolderStructureDialog();
                callback(false);
            });

        document
            .getElementById("zip-folders-btn")
            .addEventListener("click", function () {
                hideFolderStructureDialog();
                callback(true);
            });

        document
            .getElementById("zip-cancel-btn")
            .addEventListener("click", function () {
                hideFolderStructureDialog();
            });
    }

    function hideFolderStructureDialog() {
        const dialog = document.getElementById("asset-cleaner-folder-dialog");
        if (dialog) {
            dialog.remove();
        }
    }

    function showDownloadOverlay(message) {
        hideDownloadOverlay();

        const overlay = document.createElement("div");
        overlay.id = "asset-cleaner-download-overlay";
        overlay.innerHTML =
            '<div class="download-overlay-content">' +
            '<div class="spinner"></div>' +
            "<p>" +
            escapeHtml(message) +
            "</p>" +
            "</div>";
        document.body.appendChild(overlay);
    }

    function hideDownloadOverlay() {
        const overlay = document.getElementById(
            "asset-cleaner-download-overlay",
        );
        if (overlay) {
            overlay.remove();
        }
    }

    // ========================================
    // Helpers
    // ========================================

    function escapeHtml(str) {
        if (!str) return "";
        const div = document.createElement("div");
        div.textContent = str;
        return div.innerHTML;
    }

    function formatBytes(bytes) {
        if (bytes === 0) return "0 B";
        const k = 1024;
        const sizes = ["B", "KB", "MB", "GB"];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
    }
})();
