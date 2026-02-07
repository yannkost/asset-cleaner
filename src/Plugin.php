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
use yann\assetcleaner\services\AssetUsageService;
use yann\assetcleaner\utilities\AssetCleanerUtility;
use yii\base\Event;

/**
 * Asset Cleaner Plugin
 *
 * Identify and clean up unused assets in Craft CMS
 *
 * @property-read AssetUsageService $assetUsage
 *
 * @since 1.0.0
 */
class Plugin extends BasePlugin
{
    /**
     * @var string
     */
    public string $schemaVersion = '1.0.0';

    /**
     * @var bool
     */
    public bool $hasCpSettings = false;

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
            'components' => [
                'assetUsage' => AssetUsageService::class,
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
        $this->icon = $this->getBasePath() . '/icon.svg';

        // Register console commands
        if (Craft::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'yann\\assetcleaner\\console\\controllers';
        }

        // Register utility
        Event::on(
            Utilities::class,
            Utilities::EVENT_REGISTER_UTILITIES,
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = AssetCleanerUtility::class;
            }
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
            'asset-cleaner/usage/get' => 'asset-cleaner/usage/get',
            'asset-cleaner/scan' => 'asset-cleaner/asset-cleaner/start-scan',
            'asset-cleaner/export' => 'asset-cleaner/asset-cleaner/export',
            'asset-cleaner/zip' => 'asset-cleaner/asset-cleaner/zip',
            'asset-cleaner/trash' => 'asset-cleaner/asset-cleaner/trash',
            'asset-cleaner/delete' => 'asset-cleaner/asset-cleaner/delete',
            'asset-cleaner/preview-delete' => 'asset-cleaner/asset-cleaner/preview-delete',
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
            function(TemplateEvent $event) {
                try {
                    $currentUser = Craft::$app->getUser()->getIdentity();
                    if (!$currentUser) {
                        return;
                    }

                    $view = Craft::$app->getView();
                    $view->registerAssetBundle(AssetCleanerAsset::class);

                    $view->registerJs(
                        'window.AssetCleanerSettings = ' . json_encode([
                            'translations' => [
                                'viewUsage' => Craft::t('asset-cleaner', 'View Usage'),
                                'usedByEntries' => Craft::t('asset-cleaner', 'Used by Entries'),
                                'usedInContentFields' => Craft::t('asset-cleaner', 'Used in Content Fields'),
                                'notUsed' => Craft::t('asset-cleaner', 'This asset is not used anywhere.'),
                                'loading' => Craft::t('asset-cleaner', 'Loading...'),
                                'error' => Craft::t('asset-cleaner', 'An error occurred.'),
                            ],
                        ]) . ';',
                        View::POS_HEAD
                    );
                } catch (\Throwable $e) {
                    Craft::error('Error registering Asset Cleaner asset bundle: ' . $e->getMessage(), __METHOD__);
                }
            }
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
            function(DefineHtmlEvent $event) {
                try {
                    /** @var Asset $asset */
                    $asset = $event->sender;

                    if (!$asset || !$asset->id) {
                        return;
                    }

                    $buttonLabel = Craft::t('asset-cleaner', 'View Usage');
                    $event->html = '<button type="button" class="asset-cleaner-usage-btn btn" data-asset-id="' . (int)$asset->id . '" title="' . htmlspecialchars($buttonLabel, ENT_QUOTES, 'UTF-8') . '">' .
                        '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">' .
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />' .
                        '</svg>' .
                        '</button>' . $event->html;
                } catch (\Throwable $e) {
                    Craft::error('Error registering View Usage button: ' . $e->getMessage(), __METHOD__);
                }
            }
        );
    }
}
