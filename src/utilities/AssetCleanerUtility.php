<?php

declare(strict_types=1);

namespace yann\assetcleaner\utilities;

use Craft;
use craft\base\Utility;

/**
 * Asset Cleaner Utility
 *
 * Registers the Asset Cleaner utility in the CP
 */
class AssetCleanerUtility extends Utility
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('asset-cleaner', 'Asset Cleaner');
    }

    /**
     * @inheritdoc
     */
    public static function id(): string
    {
        return 'asset-cleaner';
    }

    /**
     * @inheritdoc
     */
    public static function icon(): ?string
    {
        return Craft::getAlias('@yann/assetcleaner/icon.svg');
    }

    /**
     * @inheritdoc
     */
    public static function contentHtml(): string
    {
        $volumes = Craft::$app->getVolumes()->getAllVolumes();
        $settings = \yann\assetcleaner\Plugin::getInstance()->getSettings();

        return Craft::$app->getView()->renderTemplate('asset-cleaner/_utility/index', [
            'volumes' => $volumes,
            'includeDraftsDefault' => (bool)($settings->includeDraftsByDefault ?? false),
            'includeRevisionsDefault' => (bool)($settings->includeRevisionsByDefault ?? false),
        ]);
    }
}
