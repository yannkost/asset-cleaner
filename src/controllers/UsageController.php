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
            $assetId = $request->getRequiredParam("assetId");
            $includeDraftsParam = $request->getParam("includeDrafts", null);
            $includeRevisionsParam = $request->getParam(
                "includeRevisions",
                null,
            );
            $countAllRelationsParam = $request->getParam(
                "countAllRelationsAsUsage",
                null,
            );
            $initiatingUserId = (int) (Craft::$app->getUser()->getId() ?? 0);

            $includeDrafts =
                $includeDraftsParam === null
                    ? null
                    : filter_var($includeDraftsParam, FILTER_VALIDATE_BOOLEAN);
            $includeRevisions =
                $includeRevisionsParam === null
                    ? null
                    : filter_var(
                        $includeRevisionsParam,
                        FILTER_VALIDATE_BOOLEAN,
                    );
            $countAllRelationsAsUsage =
                $countAllRelationsParam === null
                    ? true
                    : filter_var(
                        $countAllRelationsParam,
                        FILTER_VALIDATE_BOOLEAN,
                    );

            $service = Plugin::getInstance()->assetUsage;
            $usage = $service->getAssetUsage(
                (int) $assetId,
                $includeDrafts,
                $includeRevisions,
                $initiatingUserId,
                $countAllRelationsAsUsage,
            );

            $usage["otherRelations"] = $usage["otherRelations"] ?? [];
            $isUsed = !empty($usage["relations"]) || !empty($usage["otherRelations"]) || !empty($usage["content"]);

            return $this->asJson([
                "success" => true,
                "isUsed" => $isUsed,
                "usage" => $usage,
            ]);
        } catch (\Throwable $e) {
            Logger::exception("Failed to get asset usage", $e);
            return $this->asJson([
                "success" => false,
                "error" => Craft::t(
                    "asset-cleaner",
                    "Failed to get asset usage.",
                ),
            ]);
        }
    }
}
