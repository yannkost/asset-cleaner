<?php

declare(strict_types=1);

namespace yann\assetcleaner\controllers;

use Craft;
use craft\elements\Asset;
use craft\helpers\FileHelper;
use craft\web\Controller;
use yann\assetcleaner\helpers\Logger;
use yann\assetcleaner\jobs\ScanSetupJob;
use yann\assetcleaner\Plugin;
use yii\web\Response;
use ZipArchive;

/**
 * Asset Cleaner Controller
 *
 * Handles the utilities page actions
 */
class AssetCleanerController extends Controller
{
    /**
     * @inheritdoc
     */
    protected array|bool|int $allowAnonymous = false;

    /**
     * @inheritdoc
     */
    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $this->requireCpRequest();
        $this->requirePermission("utility:asset-cleaner");

        return true;
    }

    /**
     * Start scan — queues a background job and returns the scan ID
     *
     * @return Response
     */
    public function actionStartScan(): Response
    {
        $this->requireAcceptsJson();

        try {
            $request = Craft::$app->getRequest();
            $volumeIds = $request->getBodyParam("volumeIds", []);
            $includeDraftsParam = $request->getBodyParam("includeDrafts", null);
            $includeRevisionsParam = $request->getBodyParam(
                "includeRevisions",
                null,
            );
            $countAllRelationsParam = $request->getBodyParam(
                "countAllRelationsAsUsage",
                null,
            );
            $initiatingUserId = (int) (Craft::$app->getUser()->getId() ?? 0);

            if (!is_array($volumeIds)) {
                $volumeIds = [];
            }

            $volumeIds = array_map("intval", $volumeIds);

            $scanService = Plugin::getInstance()->scanService;
            $includeDrafts =
                $includeDraftsParam === null
                    ? $scanService->getDefaultIncludeDrafts()
                    : filter_var($includeDraftsParam, FILTER_VALIDATE_BOOLEAN);
            $includeRevisions =
                $includeRevisionsParam === null
                    ? $scanService->getDefaultIncludeRevisions()
                    : filter_var(
                        $includeRevisionsParam,
                        FILTER_VALIDATE_BOOLEAN,
                    );
            $countAllRelations =
                $countAllRelationsParam === null
                    ? true
                    : filter_var(
                        $countAllRelationsParam,
                        FILTER_VALIDATE_BOOLEAN,
                    );

            // Generate a unique scan ID
            $scanId = uniqid("scan_", true);

            $scanService->initializeScan(
                $scanId,
                $volumeIds,
                100,
                $includeDrafts,
                $includeRevisions,
                $countAllRelations,
                $initiatingUserId,
            );

            // Push the setup job to the queue
            Craft::$app->getQueue()->push(
                new ScanSetupJob([
                    "scanId" => $scanId,
                ]),
            );

            return $this->asJson([
                "success" => true,
                "scanId" => $scanId,
            ]);
        } catch (\Throwable $e) {
            Logger::exception("Failed to start scan", $e);
            return $this->asJson([
                "success" => false,
                "error" => Craft::t("asset-cleaner", "Failed to scan volumes."),
            ]);
        }
    }

    /**
     * Poll scan progress
     *
     * @return Response
     */
    public function actionScanProgress(): Response
    {
        $this->requireAcceptsJson();

        $scanId = Craft::$app->getRequest()->getQueryParam("scanId", "");
        if (!$scanId) {
            return $this->asJson([
                "success" => false,
                "error" => "Missing scanId.",
            ]);
        }

        $scanService = Plugin::getInstance()->scanService;
        $progress = $scanService->getProgress($scanId);
        if (!$progress) {
            return $this->asJson([
                "success" => false,
                "error" => "Scan not found.",
            ]);
        }

        $stage = (string) ($progress["stage"] ?? "");

        return $this->asJson([
            "success" => true,
            "status" => (string) ($progress["status"] ?? "pending"),
            "stage" => $stage,
            "stageLabel" => $scanService->getStageLabel($stage),
            "progress" => (int) ($progress["progress"] ?? 0),
            "totalAssets" => (int) ($progress["totalAssets"] ?? 0),
            "processedAssets" => (int) ($progress["processedAssets"] ?? 0),
            "usedCount" => (int) ($progress["usedCount"] ?? 0),
            "unusedCount" => (int) ($progress["unusedCount"] ?? 0),
            "error" => $progress["error"] ?? null,
        ]);
    }

    /**
     * Get full results from a completed scan
     *
     * @return Response
     */
    public function actionScanResults(): Response
    {
        $this->requireAcceptsJson();

        $scanId = Craft::$app->getRequest()->getQueryParam("scanId", "");
        if (!$scanId) {
            return $this->asJson([
                "success" => false,
                "error" => "Missing scanId.",
            ]);
        }

        $scanService = Plugin::getInstance()->scanService;
        $progress = $scanService->getProgress($scanId);
        if (!$progress || ($progress["status"] ?? "") !== "complete") {
            return $this->asJson([
                "success" => false,
                "error" => "Scan not complete.",
            ]);
        }

        $unusedAssets = $scanService->getResults($scanId) ?? [];
        $meta = $scanService->getMeta($scanId) ?? [];

        return $this->asJson([
            "success" => true,
            "usedCount" => (int) ($progress["usedCount"] ?? 0),
            "unusedCount" => (int) ($progress["unusedCount"] ?? 0),
            "unusedAssets" => $unusedAssets,
            "completedAt" => !empty($meta["completedAt"])
                ? date("c", (int) $meta["completedAt"])
                : null,
        ]);
    }

    /**
     * Export unused assets to CSV
     *
     * @return Response
     */
    public function actionExport(): Response
    {
        try {
            $request = Craft::$app->getRequest();
            $volumeIds = $request->getBodyParam("volumeIds", []);
            $scanId = $request->getBodyParam("scanId", "");

            if (!is_array($volumeIds)) {
                $volumeIds = [];
            }

            $volumeIds = array_map("intval", $volumeIds);

            // Use file-backed scan results when available — avoids re-running the full
            // synchronous scan, which times out for large datasets on S3 volumes.
            $unusedAssets = null;
            $scanCompletedAt = null;
            if ($scanId) {
                $scanService = Plugin::getInstance()->scanService;
                $progress = $scanService->getProgress($scanId);
                $storedResults = $scanService->getResults($scanId);
                $meta = $scanService->getMeta($scanId);

                if (
                    $progress &&
                    ($progress["status"] ?? "") === "complete" &&
                    is_array($storedResults)
                ) {
                    $unusedAssets = $storedResults;
                    $scanCompletedAt = !empty($meta["completedAt"])
                        ? (int) $meta["completedAt"]
                        : null;

                    // Filter by the requested volumes
                    if (!empty($volumeIds)) {
                        $unusedAssets = array_values(
                            array_filter($unusedAssets, function ($asset) use (
                                $volumeIds,
                            ) {
                                return in_array(
                                    (int) ($asset["volumeId"] ?? 0),
                                    $volumeIds,
                                    true,
                                );
                            }),
                        );
                    }
                }
            }

            // Fall back to a live scan when no stored scan results are available
            if ($unusedAssets === null) {
                $service = Plugin::getInstance()->assetUsage;
                $unusedAssets = $service->getUnusedAssets($volumeIds);
            }

            $csv = "ID,Title,Filename,Volume,Size,Path,URL\n";

            foreach ($unusedAssets as $asset) {
                $csv .= sprintf(
                    "%d,%s,%s,%s,%d,%s,%s\n",
                    $asset["id"],
                    '"' . str_replace('"', '""', $asset["title"]) . '"',
                    '"' . str_replace('"', '""', $asset["filename"]) . '"',
                    '"' . str_replace('"', '""', $asset["volume"]) . '"',
                    $asset["size"],
                    '"' . str_replace('"', '""', $asset["path"] ?? "") . '"',
                    '"' . str_replace('"', '""', $asset["url"] ?? "") . '"',
                );
            }

            // Build filename with volume names and timestamp
            $filename = "unused-assets";

            if (!empty($volumeIds)) {
                $volumeNames = [];
                foreach ($volumeIds as $volumeId) {
                    $volume = Craft::$app
                        ->getVolumes()
                        ->getVolumeById($volumeId);
                    if ($volume) {
                        $volumeNames[] = $this->sanitizeFilename(
                            $volume->handle,
                        );
                    }
                }

                if (!empty($volumeNames)) {
                    $filename .= "_" . implode("__", $volumeNames);
                }
            }

            // Use the actual scan datetime when available, otherwise fall back to now
            $filename .=
                "_" .
                ($scanCompletedAt
                    ? date("Y-m-d_H-i-s", $scanCompletedAt)
                    : date("Y-m-d_H-i-s")) .
                ".csv";

            $response = Craft::$app->getResponse();
            $response->format = Response::FORMAT_RAW;
            $response->headers->set("Content-Type", "text/csv");
            $response->headers->set(
                "Content-Disposition",
                'attachment; filename="' . $filename . '"',
            );
            $response->data = $csv;

            return $response;
        } catch (\Throwable $e) {
            Logger::exception("Failed to export CSV", $e);
            return $this->asJson([
                "success" => false,
                "error" => Craft::t("asset-cleaner", "Failed to export CSV."),
            ]);
        }
    }

    /**
     * Download ZIP of selected assets
     *
     * @return Response
     */
    public function actionZip(): Response
    {
        $request = Craft::$app->getRequest();
        $assetIds = $request->getBodyParam("assetIds", []);
        $preserveFolders = (bool) $request->getBodyParam(
            "preserveFolders",
            false,
        );

        if (!is_array($assetIds) || empty($assetIds)) {
            return $this->asJson([
                "success" => false,
                "error" => Craft::t("asset-cleaner", "No assets selected."),
            ]);
        }

        $assetIds = array_map("intval", $assetIds);

        $assets = Asset::find()->id($assetIds)->status(null)->all();

        if (empty($assets)) {
            return $this->asJson([
                "success" => false,
                "error" => Craft::t("asset-cleaner", "No assets found."),
            ]);
        }

        // Collect unique volume names from selected assets
        $volumeNames = [];
        $volumeIds = [];
        foreach ($assets as $asset) {
            if ($asset->volumeId && !in_array($asset->volumeId, $volumeIds)) {
                $volumeIds[] = $asset->volumeId;
                $volume = $asset->getVolume();
                if ($volume) {
                    $volumeNames[] = $this->sanitizeFilename($volume->handle);
                }
            }
        }

        // Build filename with volume names and timestamp
        $filename = "unused-assets";
        if (!empty($volumeNames)) {
            $filename .= "_" . implode("__", $volumeNames);
        }
        $filename .= "_" . date("Y-m-d_H-i-s") . ".zip";

        $tempDir =
            Craft::$app->getPath()->getTempPath() .
            "/asset-cleaner-" .
            uniqid();
        FileHelper::createDirectory($tempDir);

        // Create subdirectory for temporary asset files
        $tempAssetsDir = $tempDir . "/assets";
        FileHelper::createDirectory($tempAssetsDir);

        $zipPath = $tempDir . "/" . $filename;
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            return $this->asJson([
                "success" => false,
                "error" => Craft::t(
                    "asset-cleaner",
                    "Could not create ZIP file.",
                ),
            ]);
        }

        // Process each asset using memory-efficient streaming
        $tempFiles = [];
        foreach ($assets as $asset) {
            try {
                // Create a unique temp filename to avoid collisions
                $tempFilename = $asset->id . "_" . $asset->filename;
                $tempFilePath = $tempAssetsDir . "/" . $tempFilename;

                // Stream the asset to a temp file in chunks (memory-efficient)
                $stream = $asset->getStream();
                if ($stream) {
                    $tempFile = fopen($tempFilePath, "wb");
                    if ($tempFile) {
                        // Copy in 8KB chunks to avoid memory issues
                        while (!feof($stream)) {
                            $chunk = fread($stream, 8192);
                            if ($chunk !== false) {
                                fwrite($tempFile, $chunk);
                            }
                        }
                        fclose($tempFile);
                        fclose($stream);

                        // Build the path inside the ZIP
                        $zipEntryPath = $asset->filename;
                        if ($preserveFolders) {
                            // Include volume handle and folder path
                            $volume = $asset->getVolume();
                            $volumeHandle = $volume
                                ? $volume->handle
                                : "unknown";
                            $folderPath = "";
                            try {
                                $folder = $asset->getFolder();
                                if ($folder && $folder->path) {
                                    $folderPath = $folder->path;
                                }
                            } catch (\Throwable $e) {
                                Logger::warning(
                                    "Could not resolve folder metadata while preparing ZIP entry.",
                                    [
                                        "assetId" => (int) ($asset->id ?? 0),
                                        "filename" => (string) ($asset->filename ?? ""),
                                        "error" => $e->getMessage(),
                                    ],
                                );
                            }
                            $zipEntryPath =
                                $volumeHandle .
                                "/" .
                                ltrim($folderPath, "/") .
                                $asset->filename;
                        }

                        // Add file to ZIP from disk (not memory)
                        $zip->addFile($tempFilePath, $zipEntryPath);
                        $tempFiles[] = $tempFilePath;
                    } else {
                        fclose($stream);
                    }
                }
            } catch (\Throwable $e) {
                // Log error but continue with other assets
                Logger::warning(
                    "Failed to add asset to ZIP: " . $asset->filename,
                    ["error" => $e->getMessage()],
                );
            }
        }

        $zip->close();

        $response = Craft::$app->getResponse();
        $response->sendFile($zipPath, $filename);

        // Clean up temp directory after sending
        register_shutdown_function(function () use ($tempDir) {
            FileHelper::removeDirectory($tempDir);
        });

        return $response;
    }

    /**
     * Move selected assets to trash
     *
     * @return Response
     */
    public function actionTrash(): Response
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        try {
            $request = Craft::$app->getRequest();
            $assetIds = $request->getBodyParam("assetIds", []);

            if (!is_array($assetIds) || empty($assetIds)) {
                return $this->asJson([
                    "success" => false,
                    "error" => Craft::t("asset-cleaner", "No assets selected."),
                ]);
            }

            $assetIds = array_map("intval", $assetIds);

            $assets = Asset::find()->id($assetIds)->status(null)->all();

            $trashedCount = 0;
            $errors = [];

            foreach ($assets as $asset) {
                try {
                    if (
                        Craft::$app->getElements()->deleteElement($asset, false)
                    ) {
                        $trashedCount++;
                    } else {
                        $errors[] = $asset->filename;
                        Logger::warning("Failed to trash asset", [
                            "filename" => $asset->filename,
                        ]);
                    }
                } catch (\Throwable $e) {
                    $errors[] = $asset->filename . ": " . $e->getMessage();
                    Logger::exception(
                        "Error trashing asset: " . $asset->filename,
                        $e,
                    );
                }
            }

            return $this->asJson([
                "success" => empty($errors),
                "trashedCount" => $trashedCount,
                "errors" => $errors,
            ]);
        } catch (\Throwable $e) {
            Logger::exception("Failed to move assets to trash", $e);
            return $this->asJson([
                "success" => false,
                "error" => Craft::t(
                    "asset-cleaner",
                    "Failed to move assets to trash.",
                ),
            ]);
        }
    }

    /**
     * Delete selected assets permanently
     *
     * @return Response
     */
    public function actionDelete(): Response
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        try {
            $request = Craft::$app->getRequest();
            $assetIds = $request->getBodyParam("assetIds", []);

            if (!is_array($assetIds) || empty($assetIds)) {
                return $this->asJson([
                    "success" => false,
                    "error" => Craft::t("asset-cleaner", "No assets selected."),
                ]);
            }

            $assetIds = array_map("intval", $assetIds);

            $assets = Asset::find()->id($assetIds)->status(null)->all();

            $deletedCount = 0;
            $errors = [];

            foreach ($assets as $asset) {
                try {
                    if (
                        Craft::$app->getElements()->deleteElement($asset, true)
                    ) {
                        $deletedCount++;
                    } else {
                        $errors[] = $asset->filename;
                        Logger::warning("Failed to delete asset", [
                            "filename" => $asset->filename,
                        ]);
                    }
                } catch (\Throwable $e) {
                    $errors[] = $asset->filename . ": " . $e->getMessage();
                    Logger::exception(
                        "Error deleting asset: " . $asset->filename,
                        $e,
                    );
                }
            }

            return $this->asJson([
                "success" => empty($errors),
                "deletedCount" => $deletedCount,
                "errors" => $errors,
            ]);
        } catch (\Throwable $e) {
            Logger::exception("Failed to delete assets", $e);
            return $this->asJson([
                "success" => false,
                "error" => Craft::t(
                    "asset-cleaner",
                    "Failed to delete assets.",
                ),
            ]);
        }
    }

    /**
     * Preview what will be deleted
     *
     * @return Response
     */
    public function actionPreviewDelete(): Response
    {
        $this->requireAcceptsJson();

        try {
            $request = Craft::$app->getRequest();
            $assetIds = $request->getBodyParam("assetIds", []);

            if (!is_array($assetIds) || empty($assetIds)) {
                return $this->asJson([
                    "success" => false,
                    "error" => Craft::t("asset-cleaner", "No assets selected."),
                ]);
            }

            $assetIds = array_map("intval", $assetIds);

            $assets = Asset::find()->id($assetIds)->status(null)->all();

            $preview = [];
            $totalSize = 0;

            foreach ($assets as $asset) {
                $preview[] = [
                    "id" => $asset->id,
                    "title" => $asset->title,
                    "filename" => $asset->filename,
                    "volume" => $asset->volume->name ?? "",
                    "size" => $asset->size,
                ];
                $totalSize += $asset->size;
            }

            return $this->asJson([
                "success" => true,
                "assets" => $preview,
                "count" => count($preview),
                "totalSize" => $totalSize,
            ]);
        } catch (\Throwable $e) {
            Logger::exception("Failed to preview delete", $e);
            return $this->asJson([
                "success" => false,
                "error" => Craft::t("asset-cleaner", "An error occurred."),
            ]);
        }
    }

    /**
     * Sanitize a string for use in filenames (convert to snake_case)
     *
     * @param string $string
     * @return string
     */
    private function sanitizeFilename(string $string): string
    {
        // Convert to lowercase and replace spaces/special chars with underscores
        $string = strtolower($string);
        $string = preg_replace("/[^a-z0-9]+/", "_", $string);
        $string = trim($string, "_");
        return $string;
    }
}
