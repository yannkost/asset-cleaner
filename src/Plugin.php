<?php

declare(strict_types=1);

namespace yann\assetcleaner;

use Craft;
use craft\base\Plugin as BasePlugin;
use craft\elements\Asset;
use craft\events\DefineHtmlEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\TemplateEvent;
use craft\services\Utilities;
use craft\web\View;
use craft\console\Application as ConsoleApplication;
use yann\assetcleaner\assetbundles\assetcleaner\AssetCleanerAsset;
use yann\assetcleaner\helpers\Logger;
use yann\assetcleaner\models\Settings;
use yann\assetcleaner\services\AssetUsageService;
use yann\assetcleaner\services\ScanService;
use yann\assetcleaner\utilities\AssetCleanerUtility;
use yii\base\Event;

/**
 * Asset Cleaner Plugin
 *
 * Identify and clean up unused assets in Craft CMS
 *
 * @property-read AssetUsageService $assetUsage
 * @property-read ScanService $scanService
 *
 * @since 1.0.0
 */
class Plugin extends BasePlugin
{
    /**
     * @var string
     */
    public string $schemaVersion = "1.1.3";

    /**
     * @var bool
     */
    public bool $hasCpSettings = true;

    /**
     * @var bool
     */
    public bool $hasCpSection = false;

    /**
     * @var string|null
     */
    public ?string $icon = null;

    /**
     * @inheritdoc
     */
    public static function config(): array
    {
        return [
            "components" => [
                "assetUsage" => AssetUsageService::class,
                "scanService" => ScanService::class,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();

        // Set plugin icon
        $this->icon = $this->getBasePath() . "/icon.svg";

        // Register console commands
        if (Craft::$app instanceof ConsoleApplication) {
            $this->controllerNamespace =
                "yann\\assetcleaner\\console\\controllers";
        }

        // Register utility
        Event::on(
            Utilities::class,
            Utilities::EVENT_REGISTER_UTILITIES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = AssetCleanerUtility::class;
            },
        );

        // Only register for CP requests
        $request = Craft::$app->getRequest();
        if ($request->getIsCpRequest() && !$request->getIsConsoleRequest()) {
            $this->registerCpUrlRules();
            $this->registerAssetBundle();
            $this->registerViewUsageButton();
        }
    }

    /**
     * Register CP URL rules for AJAX endpoints
     */
    private function registerCpUrlRules(): void
    {
        Craft::$app->getUrlManager()->addRules([
            "asset-cleaner/usage/get" => "asset-cleaner/usage/get",
            "asset-cleaner/scan" => "asset-cleaner/asset-cleaner/start-scan",
            "asset-cleaner/export" => "asset-cleaner/asset-cleaner/export",
            "asset-cleaner/zip" => "asset-cleaner/asset-cleaner/zip",
            "asset-cleaner/trash" => "asset-cleaner/asset-cleaner/trash",
            "asset-cleaner/delete" => "asset-cleaner/asset-cleaner/delete",
            "asset-cleaner/preview-delete" =>
                "asset-cleaner/asset-cleaner/preview-delete",
            "asset-cleaner/scan-progress" =>
                "asset-cleaner/asset-cleaner/scan-progress",
            "asset-cleaner/scan-results" =>
                "asset-cleaner/asset-cleaner/scan-results",
        ]);
    }

    /**
     * Register asset bundle for CP pages
     */
    private function registerAssetBundle(): void
    {
        Event::on(
            View::class,
            View::EVENT_BEFORE_RENDER_PAGE_TEMPLATE,
            function (TemplateEvent $event) {
                try {
                    $currentUser = Craft::$app->getUser()->getIdentity();
                    if (!$currentUser) {
                        return;
                    }

                    $view = Craft::$app->getView();
                    $view->registerAssetBundle(AssetCleanerAsset::class);

                    $jsSettings = [
                        "translations" => [
                            // View Usage popover / dialog
                            "viewUsage" => Craft::t(
                                "asset-cleaner",
                                "View Usage",
                            ),
                            "usedByEntries" => Craft::t(
                                "asset-cleaner",
                                "Used by Entries",
                            ),
                            "usedInContentFields" => Craft::t(
                                "asset-cleaner",
                                "Used in Content Fields",
                            ),
                            "usedByRelationalElements" => Craft::t(
                                "asset-cleaner",
                                "Used by Relational Elements",
                            ),
                            "otherRelationalElements" => Craft::t(
                                "asset-cleaner",
                                "Other Relational Elements",
                            ),
                            "notUsed" => Craft::t(
                                "asset-cleaner",
                                "This asset is not used anywhere.",
                            ),
                            "loading" => Craft::t(
                                "asset-cleaner",
                                "Loading...",
                            ),
                            "error" => Craft::t(
                                "asset-cleaner",
                                "An error occurred.",
                            ),
                            "usageDialogTitle" => Craft::t(
                                "asset-cleaner",
                                "Check Asset Usage",
                            ),
                            "usageDialogText" => Craft::t(
                                "asset-cleaner",
                                "Choose how usage should be evaluated for this asset.",
                            ),
                            "chooseUsageOptions" => Craft::t(
                                "asset-cleaner",
                                "Choose the usage options you want to check, then confirm.",
                            ),
                            "includeDrafts" => Craft::t(
                                "asset-cleaner",
                                "Include drafts",
                            ),
                            "includeRevisions" => Craft::t(
                                "asset-cleaner",
                                "Include revisions",
                            ),
                            "countAllRelationsAsUsage" => Craft::t(
                                "asset-cleaner",
                                "Count all relational references as usage",
                            ),
                            "countAllRelationsAsUsageHelp" => Craft::t(
                                "asset-cleaner",
                                "Recommended for projects with plugin-defined or unknown element types that may store asset relations outside normal entry content.",
                            ),
                            "checkUsage" => Craft::t(
                                "asset-cleaner",
                                "Check Usage",
                            ),
                            "otherRelationalElement" => Craft::t(
                                "asset-cleaner",
                                "Used by relational element #{id}",
                            ),
                            "scannedOn" => Craft::t(
                                "asset-cleaner",
                                "Scanned on {date}",
                            ),
                            "restoringLastScan" => Craft::t(
                                "asset-cleaner",
                                "Restoring last scan...",
                            ),
                            "scanStale" => Craft::t(
                                "asset-cleaner",
                                "Scan older than 24h — results may be outdated",
                            ),
                            // Scanning
                            "scanQueued" => Craft::t(
                                "asset-cleaner",
                                "Scan queued...",
                            ),
                            "scanning" => Craft::t(
                                "asset-cleaner",
                                "Scanning...",
                            ),
                            "scanFailed" => Craft::t(
                                "asset-cleaner",
                                "Scan failed.",
                            ),
                            "scanPollingIssue" => Craft::t(
                                "asset-cleaner",
                                "Lost contact while polling scan progress. The scan may still be running.",
                            ),
                            // Results & grid
                            "noUnusedAssetsFound" => Craft::t(
                                "asset-cleaner",
                                "No unused assets found.",
                            ),
                            "volumeHeader" => Craft::t(
                                "asset-cleaner",
                                "{count} unused assets — {size}",
                            ),
                            "selectAll" => Craft::t(
                                "asset-cleaner",
                                "Select All",
                            ),
                            "selectAtLeastOneVolume" => Craft::t(
                                "asset-cleaner",
                                "Select at least one volume.",
                            ),
                            "colTitle" => Craft::t("asset-cleaner", "Title"),
                            "colFilename" => Craft::t(
                                "asset-cleaner",
                                "Filename",
                            ),
                            "colSize" => Craft::t("asset-cleaner", "Size"),
                            "colPath" => Craft::t("asset-cleaner", "Path"),
                            // Action buttons
                            "downloadCsv" => Craft::t(
                                "asset-cleaner",
                                "Download CSV",
                            ),
                            "downloadZip" => Craft::t(
                                "asset-cleaner",
                                "Download ZIP",
                            ),
                            "putIntoTrash" => Craft::t(
                                "asset-cleaner",
                                "Put into Trash",
                            ),
                            "deletePermanently" => Craft::t(
                                "asset-cleaner",
                                "Delete Permanently",
                            ),
                            // Action messages
                            "noAssetsSelected" => Craft::t(
                                "asset-cleaner",
                                "No assets selected.",
                            ),
                            "noAssetsSelectedInVolume" => Craft::t(
                                "asset-cleaner",
                                "No assets selected in this volume.",
                            ),
                            "confirmTrash" => Craft::t(
                                "asset-cleaner",
                                "Are you sure you want to move {count} assets to trash?",
                            ),
                            "movedToTrash" => Craft::t(
                                "asset-cleaner",
                                "Moved {count} assets to trash.",
                            ),
                            "permanentlyDeleted" => Craft::t(
                                "asset-cleaner",
                                "Permanently deleted {count} assets.",
                            ),
                            "deleteWarning" => Craft::t(
                                "asset-cleaner",
                                "Are you sure you want to permanently delete {count} assets? This action CANNOT be undone! Download a backup (CSV or ZIP) before proceeding.",
                            ),
                            "deleteConfirm" => Craft::t(
                                "asset-cleaner",
                                "Final confirmation: Permanently delete {count} assets? This CANNOT be undone!",
                            ),
                            // ZIP dialog
                            "zipDialogTitle" => Craft::t(
                                "asset-cleaner",
                                "ZIP Download Options",
                            ),
                            "zipDialogText" => Craft::t(
                                "asset-cleaner",
                                "How would you like to organize the files in the ZIP?",
                            ),
                            "zipFlat" => Craft::t(
                                "asset-cleaner",
                                "Flat (all files in root)",
                            ),
                            "zipFolders" => Craft::t(
                                "asset-cleaner",
                                "Preserve folder structure",
                            ),
                            "cancel" => Craft::t("asset-cleaner", "Cancel"),
                            "zipInitiated" => Craft::t(
                                "asset-cleaner",
                                "ZIP download initiated. Large files may take several minutes to prepare.",
                            ),
                            "zipPreparing" => Craft::t(
                                "asset-cleaner",
                                "Preparing ZIP file... This may take several minutes for large files. Please wait.",
                            ),
                        ],
                        "usageDefaults" => [
                            "includeDrafts" => $this->scanService->getDefaultIncludeDrafts(),
                            "includeRevisions" => $this->scanService->getDefaultIncludeRevisions(),
                            "countAllRelationsAsUsage" => true,
                        ],
                    ];

                    // Check for last scan results to auto-restore
                    try {
                        $lastScan = $this->scanService->getLastScan();
                        if (
                            is_array($lastScan) &&
                            !empty($lastScan["scanId"]) &&
                            !empty($lastScan["completedAt"])
                        ) {
                            $jsSettings["lastScanId"] = $lastScan["scanId"];
                            $jsSettings["lastScanTime"] = date(
                                "c",
                                (int) $lastScan["completedAt"],
                            );
                        }
                    } catch (\Throwable $e) {
                        Logger::warning("Could not read last scan state.", [
                            "error" => $e->getMessage(),
                            "method" => __METHOD__,
                        ]);
                    }

                    $view->registerJs(
                        "window.AssetCleanerSettings = " .
                            json_encode($jsSettings) .
                            ";",
                        View::POS_HEAD,
                    );
                } catch (\Throwable $e) {
                    Logger::error(
                        "Error registering Asset Cleaner asset bundle.",
                        [
                            "error" => $e->getMessage(),
                            "method" => __METHOD__,
                        ],
                    );
                }
            },
        );
    }

    /**
     * Register "View Usage" button on asset edit pages
     */
    private function registerViewUsageButton(): void
    {
        Event::on(
            Asset::class,
            Asset::EVENT_DEFINE_ADDITIONAL_BUTTONS,
            function (DefineHtmlEvent $event) {
                try {
                    /** @var Asset $asset */
                    $asset = $event->sender;

                    if (!$asset || !$asset->id) {
                        return;
                    }

                    $buttonLabel = Craft::t("asset-cleaner", "View Usage");
                    $event->html =
                        '<button type="button" class="asset-cleaner-usage-btn btn" data-asset-id="' .
                        (int) $asset->id .
                        '" title="' .
                        htmlspecialchars($buttonLabel, ENT_QUOTES, "UTF-8") .
                        '">' .
                        '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">' .
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />' .
                        "</svg>" .
                        "</button>" .
                        $event->html;
                } catch (\Throwable $e) {
                    Logger::error("Error registering View Usage button.", [
                        "error" => $e->getMessage(),
                        "method" => __METHOD__,
                    ]);
                }
            },
        );
    }

    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): ?\craft\base\Model
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): ?string
    {
        return Craft::$app
            ->getView()
            ->renderTemplate("asset-cleaner/_settings", [
                "settings" => $this->getSettings(),
            ]);
    }
}
