<?php

declare(strict_types=1);

namespace yann\assetcleaner\controllers;

use Craft;
use craft\elements\Asset;
use craft\helpers\FileHelper;
use craft\web\Controller;
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
        $this->requirePermission('utility:asset-cleaner');

        return true;
    }

    /**
     * Start scan - returns unused/used counts
     *
     * @return Response
     */
    public function actionStartScan(): Response
    {
        $this->requireAcceptsJson();

        $request = Craft::$app->getRequest();
        $volumeIds = $request->getBodyParam('volumeIds', []);

        if (!is_array($volumeIds)) {
            $volumeIds = [];
        }

        $volumeIds = array_map('intval', $volumeIds);

        $service = Plugin::getInstance()->assetUsage;

        $unusedCount = $service->countUnusedAssets($volumeIds);
        $usedCount = $service->countUsedAssets($volumeIds);
        $unusedAssets = $service->getUnusedAssets($volumeIds);

        return $this->asJson([
            'success' => true,
            'usedCount' => $usedCount,
            'unusedCount' => $unusedCount,
            'unusedAssets' => $unusedAssets,
        ]);
    }

    /**
     * Export unused assets to CSV
     *
     * @return Response
     */
    public function actionExport(): Response
    {
        $request = Craft::$app->getRequest();
        $volumeIds = $request->getBodyParam('volumeIds', []);

        if (!is_array($volumeIds)) {
            $volumeIds = [];
        }

        $volumeIds = array_map('intval', $volumeIds);

        $service = Plugin::getInstance()->assetUsage;
        $unusedAssets = $service->getUnusedAssets($volumeIds);

        $csv = "ID,Title,Filename,Volume,Size,Path,URL\n";

        foreach ($unusedAssets as $asset) {
            $csv .= sprintf(
                "%d,%s,%s,%s,%d,%s,%s\n",
                $asset['id'],
                '"' . str_replace('"', '""', $asset['title']) . '"',
                '"' . str_replace('"', '""', $asset['filename']) . '"',
                '"' . str_replace('"', '""', $asset['volume']) . '"',
                $asset['size'],
                '"' . str_replace('"', '""', $asset['path'] ?? '') . '"',
                '"' . str_replace('"', '""', $asset['url'] ?? '') . '"'
            );
        }

        // Build filename with volume names and timestamp
        $filename = 'unused-assets';
        
        // Add volume names if specific volumes were selected
        if (!empty($volumeIds)) {
            $volumes = \craft\elements\Asset::find()
                ->volumeId($volumeIds)
                ->limit(1)
                ->all();
            
            $volumeNames = [];
            foreach ($volumeIds as $volumeId) {
                $volume = Craft::$app->getVolumes()->getVolumeById($volumeId);
                if ($volume) {
                    $volumeNames[] = $this->sanitizeFilename($volume->handle);
                }
            }
            
            if (!empty($volumeNames)) {
                $filename .= '_' . implode('__', $volumeNames);
            }
        }
        
        // Add human-readable timestamp
        $filename .= '_' . date('Y-m-d_H-i-s') . '.csv';

        $response = Craft::$app->getResponse();
        $response->format = Response::FORMAT_RAW;
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->data = $csv;

        return $response;
    }

    /**
     * Download ZIP of selected assets
     *
     * @return Response
     */
    public function actionZip(): Response
    {
        $request = Craft::$app->getRequest();
        $assetIds = $request->getBodyParam('assetIds', []);
        $preserveFolders = (bool) $request->getBodyParam('preserveFolders', false);

        if (!is_array($assetIds) || empty($assetIds)) {
            return $this->asJson([
                'success' => false,
                'error' => Craft::t('asset-cleaner', 'No assets selected.'),
            ]);
        }

        $assetIds = array_map('intval', $assetIds);

        $assets = Asset::find()
            ->id($assetIds)
            ->status(null)
            ->all();

        if (empty($assets)) {
            return $this->asJson([
                'success' => false,
                'error' => Craft::t('asset-cleaner', 'No assets found.'),
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
        $filename = 'unused-assets';
        if (!empty($volumeNames)) {
            $filename .= '_' . implode('__', $volumeNames);
        }
        $filename .= '_' . date('Y-m-d_H-i-s') . '.zip';

        $tempDir = Craft::$app->getPath()->getTempPath() . '/asset-cleaner-' . uniqid();
        FileHelper::createDirectory($tempDir);
        
        // Create subdirectory for temporary asset files
        $tempAssetsDir = $tempDir . '/assets';
        FileHelper::createDirectory($tempAssetsDir);

        $zipPath = $tempDir . '/' . $filename;
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            return $this->asJson([
                'success' => false,
                'error' => Craft::t('asset-cleaner', 'Could not create ZIP file.'),
            ]);
        }

        // Process each asset using memory-efficient streaming
        $tempFiles = [];
        foreach ($assets as $asset) {
            try {
                // Create a unique temp filename to avoid collisions
                $tempFilename = $asset->id . '_' . $asset->filename;
                $tempFilePath = $tempAssetsDir . '/' . $tempFilename;
                
                // Stream the asset to a temp file in chunks (memory-efficient)
                $stream = $asset->getStream();
                if ($stream) {
                    $tempFile = fopen($tempFilePath, 'wb');
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
                            $volumeHandle = $volume ? $volume->handle : 'unknown';
                            $folderPath = '';
                            try {
                                $folder = $asset->getFolder();
                                if ($folder && $folder->path) {
                                    $folderPath = $folder->path;
                                }
                            } catch (\Throwable $e) {
                                // Folder might not be accessible
                            }
                            $zipEntryPath = $volumeHandle . '/' . ltrim($folderPath, '/') . $asset->filename;
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
                Craft::warning('Failed to add asset to ZIP: ' . $asset->filename . ' - ' . $e->getMessage(), 'asset-cleaner');
            }
        }

        $zip->close();

        $response = Craft::$app->getResponse();
        $response->sendFile($zipPath, $filename);

        // Clean up temp directory after sending
        register_shutdown_function(function() use ($tempDir) {
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

        $request = Craft::$app->getRequest();
        $assetIds = $request->getBodyParam('assetIds', []);

        if (!is_array($assetIds) || empty($assetIds)) {
            return $this->asJson([
                'success' => false,
                'error' => Craft::t('asset-cleaner', 'No assets selected.'),
            ]);
        }

        $assetIds = array_map('intval', $assetIds);

        $assets = Asset::find()
            ->id($assetIds)
            ->status(null)
            ->all();

        $trashedCount = 0;
        $errors = [];

        foreach ($assets as $asset) {
            try {
                if (Craft::$app->getElements()->deleteElement($asset, false)) {
                    $trashedCount++;
                } else {
                    $errors[] = $asset->filename;
                }
            } catch (\Throwable $e) {
                $errors[] = $asset->filename . ': ' . $e->getMessage();
            }
        }

        return $this->asJson([
            'success' => empty($errors),
            'trashedCount' => $trashedCount,
            'errors' => $errors,
        ]);
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

        $request = Craft::$app->getRequest();
        $assetIds = $request->getBodyParam('assetIds', []);

        if (!is_array($assetIds) || empty($assetIds)) {
            return $this->asJson([
                'success' => false,
                'error' => Craft::t('asset-cleaner', 'No assets selected.'),
            ]);
        }

        $assetIds = array_map('intval', $assetIds);

        $assets = Asset::find()
            ->id($assetIds)
            ->status(null)
            ->all();

        $deletedCount = 0;
        $errors = [];

        foreach ($assets as $asset) {
            try {
                if (Craft::$app->getElements()->deleteElement($asset, true)) {
                    $deletedCount++;
                } else {
                    $errors[] = $asset->filename;
                }
            } catch (\Throwable $e) {
                $errors[] = $asset->filename . ': ' . $e->getMessage();
            }
        }

        return $this->asJson([
            'success' => empty($errors),
            'deletedCount' => $deletedCount,
            'errors' => $errors,
        ]);
    }

    /**
     * Preview what will be deleted
     *
     * @return Response
     */
    public function actionPreviewDelete(): Response
    {
        $this->requireAcceptsJson();

        $request = Craft::$app->getRequest();
        $assetIds = $request->getBodyParam('assetIds', []);

        if (!is_array($assetIds) || empty($assetIds)) {
            return $this->asJson([
                'success' => false,
                'error' => Craft::t('asset-cleaner', 'No assets selected.'),
            ]);
        }

        $assetIds = array_map('intval', $assetIds);

        $assets = Asset::find()
            ->id($assetIds)
            ->status(null)
            ->all();

        $preview = [];
        $totalSize = 0;

        foreach ($assets as $asset) {
            $preview[] = [
                'id' => $asset->id,
                'title' => $asset->title,
                'filename' => $asset->filename,
                'volume' => $asset->volume->name ?? '',
                'size' => $asset->size,
            ];
            $totalSize += $asset->size;
        }

        return $this->asJson([
            'success' => true,
            'assets' => $preview,
            'count' => count($preview),
            'totalSize' => $totalSize,
        ]);
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
        $string = preg_replace('/[^a-z0-9]+/', '_', $string);
        $string = trim($string, '_');
        return $string;
    }
}
