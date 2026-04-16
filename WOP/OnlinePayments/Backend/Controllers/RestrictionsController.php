<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Backend\Controllers;

use backend\controllers\ModulesController;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ServiceRegister;
use common\modules\orderPayment\WOP\OnlinePayments\Services\ModuleRestrictionsService;
use yii\web\Response;
class RestrictionsController extends ModulesController
{
    public function actionGetRestrictions(): array
    {
        /** @var ModuleRestrictionsService $service */
        $service = ServiceRegister::getService(ModuleRestrictionsService::class);
        global $languages_id;
        $result = $service->getAllAvailableOptions($languages_id);
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return $result;
    }
}
