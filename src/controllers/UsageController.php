<?php

declare(strict_types=1);

namespace yann\assetcleaner\controllers;

use Craft;
use craft\web\Controller;
use yann\assetcleaner\helpers\Logger;
use yann\assetcleaner\Plugin;
use yii\web\Response;

/**
 * Usage Controller
 *
 * Handles AJAX requests for asset usage data
 */
class UsageController extends Controller
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

        return true;
    }

    /**
     * Get usage data for an asset
     *
     * @return Response
     */
    public function actionGet(): Response
    {
        $this->requireAcceptsJson();

        try {
            $request = Craft::$app->getRequest();
            $assetId = $request->getRequiredParam('assetId');

            $service = Plugin::getInstance()->assetUsage;
            $usage = $service->getAssetUsage((int)$assetId);

            $isUsed = !empty($usage['relations']) || !empty($usage['content']);

            return $this->asJson([
                'success' => true,
                'isUsed' => $isUsed,
                'usage' => $usage,
            ]);
        } catch (\Throwable $e) {
            Logger::exception('Failed to get asset usage', $e);
            return $this->asJson([
                'success' => false,
                'error' => Craft::t('asset-cleaner', 'Failed to get asset usage.'),
            ]);
        }
    }
}
