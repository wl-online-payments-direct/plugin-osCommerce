<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Backend\Controllers;

use backend\controllers\ModulesController;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\AdminConfig\AdminAPI\AdminAPI;
use yii\web\Response;
class OrderStatusesController extends ModulesController
{
    public function actionGetStatuses(): array
    {
        $storeId = \Yii::$app->request->get('storeId');
        $result = AdminAPI::get()->store($storeId)->getStoreOrderStatuses();
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return $result->toArray();
    }
}
