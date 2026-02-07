<?php

declare(strict_types=1);

namespace yann\assetcleaner\assetbundles\assetcleaner;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * Asset Cleaner Asset Bundle
 */
class AssetCleanerAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init(): void
    {
        $this->sourcePath = __DIR__ . '/dist';

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/asset-cleaner.js',
        ];

        $this->css = [
            'css/asset-cleaner.css',
        ];

        parent::init();
    }
}
