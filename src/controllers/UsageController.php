<?php

declare(strict_types=1);

namespace yann\assetcleaner\controllers;

use Craft;
use craft\web\Controller;
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
    }
}
